-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: May 06, 2025 at 01:05 PM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `rcef`
--

DELIMITER $$
--
-- Procedures
--
CREATE DEFINER=`root`@`localhost` PROCEDURE `close_accounting_period` (IN `p_period_id` INT, IN `p_user_id` INT, IN `p_closing_entries_description` TEXT)   BEGIN
    DECLARE v_period_closed BOOLEAN;
    DECLARE v_current_date DATE;
    DECLARE v_total_debits DECIMAL(15,2);
    DECLARE v_total_credits DECIMAL(15,2);
    DECLARE v_retained_earnings DECIMAL(15,2);
    DECLARE v_income_summary_account INT;
    
    DECLARE EXIT HANDLER FOR SQLEXCEPTION
    BEGIN
        ROLLBACK;
        RESIGNAL;
    END;
    
    SET v_current_date = CURDATE();
    
    -- Start transaction
    START TRANSACTION;
    
    -- Check if period is already closed
    SELECT is_closed INTO v_period_closed
    FROM accounting_periods
    WHERE id = p_period_id;
    
    IF v_period_closed THEN
        SIGNAL SQLSTATE '45000' 
        SET MESSAGE_TEXT = 'Period is already closed';
    END IF;
    
    -- Verify current date is after period end date
    IF v_current_date < (SELECT end_date FROM accounting_periods WHERE id = p_period_id) THEN
        SIGNAL SQLSTATE '45000' 
        SET MESSAGE_TEXT = 'Cannot close period before its end date';
    END IF;
    
    -- Get totals for closing entries
    SELECT 
        SUM(CASE WHEN account_type IN ('asset', 'expense') THEN balance ELSE 0 END),
        SUM(CASE WHEN account_type IN ('liability', 'equity', 'revenue') THEN balance ELSE 0 END)
    INTO 
        v_total_debits,
        v_total_credits
    FROM (
        SELECT 
            a.account_type,
            CASE 
                WHEN a.account_type IN ('asset', 'expense') THEN 
                    b.debit_amount - b.credit_amount
                ELSE 
                    b.credit_amount - b.debit_amount
            END AS balance
        FROM account_balances b
        JOIN chart_of_accounts a ON b.account_id = a.id
        WHERE b.period_id = p_period_id
    ) AS account_balances;
    
    -- Calculate retained earnings (net income)
    SET v_retained_earnings = v_total_credits - v_total_debits;
    
    -- Get retained earnings account
    SELECT id INTO v_income_summary_account
    FROM chart_of_accounts
    WHERE account_type = 'equity' AND account_name LIKE '%Retained Earnings%'
    LIMIT 1;
    
    IF v_income_summary_account IS NULL THEN
        SIGNAL SQLSTATE '45000' 
        SET MESSAGE_TEXT = 'Retained earnings account not found in chart of accounts';
    END IF;
    
    -- Mark period as closed
    UPDATE accounting_periods
    SET 
        is_closed = TRUE,
        closed_by = p_user_id,
        closed_at = NOW()
    WHERE id = p_period_id;
    
    -- Record closing summary
    INSERT INTO period_closing_summaries (
        period_id, total_debits, total_credits, 
        retained_earnings, closing_entries_description, created_by
    ) VALUES (
        p_period_id, v_total_debits, v_total_credits,
        v_retained_earnings, p_closing_entries_description, p_user_id
    );
    
    COMMIT;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `create_accounting_period` (IN `p_name` VARCHAR(50), IN `p_start_date` DATE, IN `p_end_date` DATE, IN `p_user_id` INT)   BEGIN
    DECLARE v_overlap_exists INT;
    
    -- Check for date overlap with existing periods
    SELECT COUNT(*) INTO v_overlap_exists
    FROM accounting_periods
    WHERE (
        (p_start_date BETWEEN start_date AND end_date) OR
        (p_end_date BETWEEN start_date AND end_date) OR
        (start_date BETWEEN p_start_date AND p_end_date) OR
        (end_date BETWEEN p_start_date AND p_end_date)
    );
    
    IF v_overlap_exists > 0 THEN
        SIGNAL SQLSTATE '45000' 
        SET MESSAGE_TEXT = 'New period dates overlap with existing period';
    END IF;
    
    -- Validate date range
    IF p_start_date >= p_end_date THEN
        SIGNAL SQLSTATE '45000' 
        SET MESSAGE_TEXT = 'Start date must be before end date';
    END IF;
    
    -- Insert new period
    INSERT INTO accounting_periods (
        name, start_date, end_date, created_by
    ) VALUES (
        p_name, p_start_date, p_end_date, p_user_id
    );
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `post_journal_entry` (IN `p_transaction_id` INT, IN `p_user_id` INT)   BEGIN
    DECLARE v_debit_total DECIMAL(15,2) DEFAULT 0;
    DECLARE v_credit_total DECIMAL(15,2) DEFAULT 0;
    DECLARE v_period_id INT;
    DECLARE v_period_closed BOOLEAN;
    DECLARE v_error_msg TEXT;

    DECLARE EXIT HANDLER FOR SQLEXCEPTION
    BEGIN
        ROLLBACK;
        RESIGNAL;
    END;

    START TRANSACTION;

    -- Get current accounting period
    SELECT id, is_closed INTO v_period_id, v_period_closed
    FROM accounting_periods
    WHERE start_date <= CURDATE() AND end_date >= CURDATE()
    LIMIT 1;

    IF v_period_id IS NULL THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'No active accounting period found';
    END IF;

    IF v_period_closed THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Cannot post to a closed accounting period';
    END IF;

    -- Check debit/credit totals
    SELECT 
        SUM(debit), 
        SUM(credit)
    INTO 
        v_debit_total, 
        v_credit_total
    FROM transaction_lines
    WHERE transaction_id = p_transaction_id;

    IF ROUND(v_debit_total, 2) != ROUND(v_credit_total, 2) THEN
        SET v_error_msg = CONCAT('Journal entry unbalanced. Debits: ', 
                                  v_debit_total, ' Credits: ', v_credit_total);
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = v_error_msg;
    END IF;

    -- Update transaction status
    UPDATE transactions
    SET 
        status = 'posted',
        approved_by = p_user_id,
        approved_at = NOW(),
        period_id = v_period_id
    WHERE id = p_transaction_id;

    -- Update account balances
    INSERT INTO account_balances (account_id, period_id, debit_amount, credit_amount)
    SELECT 
        account_id,
        v_period_id,
        SUM(debit),
        SUM(credit)
    FROM transaction_lines
    WHERE transaction_id = p_transaction_id
    GROUP BY account_id
    ON DUPLICATE KEY UPDATE
        debit_amount = debit_amount + VALUES(debit_amount),
        credit_amount = credit_amount + VALUES(credit_amount);

    COMMIT;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `record_inventory_adjustment` (IN `p_item_id` INT, IN `p_quantity_change` DECIMAL(10,2), IN `p_cost_price` DECIMAL(10,2), IN `p_adjustment_id` INT, IN `p_user_id` INT, IN `p_notes` TEXT)   BEGIN
    DECLARE v_new_quantity DECIMAL(10,2);
    DECLARE v_movement_type VARCHAR(20);
    
    DECLARE EXIT HANDLER FOR SQLEXCEPTION
    BEGIN
        ROLLBACK;
        RESIGNAL;
    END;
    
    START TRANSACTION;
    
    -- Determine movement type based on quantity change
    IF p_quantity_change > 0 THEN
        SET v_movement_type = 'adjustment';
    ELSE
        SET v_movement_type = 'adjustment';
    END IF;
    
    -- Record inventory movement
    INSERT INTO inventory_movements (
        item_id, movement_type, quantity, reference_id, reference_type,
        date_time, cost_price, notes, created_by
    ) VALUES (
        p_item_id, v_movement_type, p_quantity_change, p_adjustment_id, 'adjustment',
        NOW(), p_cost_price, p_notes, p_user_id
    );
    
    -- Update inventory quantity
    UPDATE inventory_items
    SET quantity_on_hand = quantity_on_hand + p_quantity_change
    WHERE id = p_item_id;
    
    -- Get new quantity for the adjustment record
    SELECT quantity_on_hand INTO v_new_quantity FROM inventory_items WHERE id = p_item_id;
    
    -- Update adjustment item record
    UPDATE inventory_adjustment_items
    SET new_quantity = v_new_quantity
    WHERE adjustment_id = p_adjustment_id AND item_id = p_item_id;
    
    COMMIT;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `record_purchase_receipt` (IN `p_purchase_order_id` INT, IN `p_item_id` INT, IN `p_quantity` DECIMAL(10,2), IN `p_unit_cost` DECIMAL(10,2), IN `p_user_id` INT, IN `p_notes` TEXT)   BEGIN
    DECLARE EXIT HANDLER FOR SQLEXCEPTION
    BEGIN
        ROLLBACK;
        RESIGNAL;
    END;
    
    START TRANSACTION;
    
    -- Update received quantity in purchase order item
    UPDATE purchase_order_items
    SET received_quantity = received_quantity + p_quantity
    WHERE purchase_order_id = p_purchase_order_id AND item_id = p_item_id;
    
    -- Record inventory movement
    INSERT INTO inventory_movements (
        item_id, movement_type, quantity, reference_id, reference_type,
        date_time, cost_price, notes, created_by
    ) VALUES (
        p_item_id, 'purchase', p_quantity, p_purchase_order_id, 'purchase_order',
        NOW(), p_unit_cost, p_notes, p_user_id
    );
    
    -- Update inventory quantity
    UPDATE inventory_items
    SET quantity_on_hand = quantity_on_hand + p_quantity,
        last_cost_price = p_unit_cost
    WHERE id = p_item_id;
    
    COMMIT;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `verify_account_balances` (IN `p_period_id` INT)   BEGIN
    DECLARE v_total_debits DECIMAL(15,2) DEFAULT 0;
    DECLARE v_total_credits DECIMAL(15,2) DEFAULT 0;

    SELECT 
        IFNULL(SUM(debit_amount), 0),
        IFNULL(SUM(credit_amount), 0)
    INTO 
        v_total_debits,
        v_total_credits
    FROM account_balances
    WHERE period_id = p_period_id;

    IF ROUND(v_total_debits, 2) != ROUND(v_total_credits, 2) THEN
        SELECT 
            'Unbalanced' AS status,
            v_total_debits AS total_debits,
            v_total_credits AS total_credits,
            v_total_debits - v_total_credits AS difference;
    ELSE
        SELECT 
            'Balanced' AS status,
            v_total_debits AS total_debits,
            v_total_credits AS total_credits,
            0 AS difference;
    END IF;
END$$

--
-- Functions
--
CREATE DEFINER=`root`@`localhost` FUNCTION `generate_po_number` () RETURNS VARCHAR(50) CHARSET utf8mb4 COLLATE utf8mb4_general_ci  BEGIN
    DECLARE next_num INT;
    DECLARE po_prefix VARCHAR(10) DEFAULT 'PO-';
    DECLARE po_year VARCHAR(4) DEFAULT YEAR(CURDATE());
    DECLARE new_po_number VARCHAR(50);
    
    SELECT COALESCE(MAX(CAST(SUBSTRING(po_number, LENGTH(po_prefix) + 6) AS UNSIGNED)), 0) + 1 INTO next_num
    FROM purchase_orders
    WHERE po_number LIKE CONCAT(po_prefix, po_year, '%');
    
    SET new_po_number = CONCAT(po_prefix, po_year, '-', LPAD(next_num, 5, '0'));
    RETURN new_po_number;
END$$

DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `accounting_periods`
--

CREATE TABLE `accounting_periods` (
  `id` int(11) NOT NULL,
  `name` varchar(50) NOT NULL,
  `start_date` date NOT NULL,
  `end_date` date NOT NULL,
  `is_closed` tinyint(1) NOT NULL DEFAULT 0,
  `closed_by` int(11) DEFAULT NULL,
  `closed_at` datetime DEFAULT NULL,
  `is_active` tinyint(1) GENERATED ALWAYS AS (`start_date` <= curdate() and `end_date` >= curdate() and `is_closed` = 0) VIRTUAL,
  `created_by` varchar(200) NOT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `accounting_periods`
--

INSERT INTO `accounting_periods` (`id`, `name`, `start_date`, `end_date`, `is_closed`, `closed_by`, `closed_at`, `created_by`, `created_at`) VALUES
(2, 'FY2025', '2025-01-01', '2025-12-31', 0, NULL, NULL, '1', '2025-04-08 03:56:15');

-- --------------------------------------------------------

--
-- Table structure for table `account_balances`
--

CREATE TABLE `account_balances` (
  `id` int(11) NOT NULL,
  `account_id` int(11) NOT NULL,
  `period_id` int(11) NOT NULL,
  `debit_amount` decimal(15,2) NOT NULL DEFAULT 0.00,
  `credit_amount` decimal(15,2) NOT NULL DEFAULT 0.00
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `account_balances`
--

INSERT INTO `account_balances` (`id`, `account_id`, `period_id`, `debit_amount`, `credit_amount`) VALUES
(1, 6, 2, 1000.00, 100.00),
(2, 11, 2, 3000100.00, 100.00),
(4, 13, 2, 50000.00, 1100.00),
(5, 4, 2, 81170.82, 0.00),
(8, 5, 2, 100.00, 0.00),
(11, 1, 2, 0.00, 3150028800.00),
(12, 47, 2, 3015000000.00, 10230000.00),
(14, 10, 2, 5000000.00, 0.00),
(15, 12, 2, 2000000.00, 0.00),
(17, 48, 2, 150000000.00, 15000000.00),
(19, 310502, 2, 0.00, 1657776.00),
(20, 4200202, 2, 1500000.00, 0.00),
(22, 0, 2, 3488800.00, 60000.00),
(26, 98, 2, 1038953.20, 0.00),
(31, 51, 2, 0.00, 1913166.56),
(34, 64, 2, 5000.00, 0.00),
(36, 42, 2, 180000.00, 0.00),
(46, 3, 2, 265918.54, 0.00),
(48, 7, 2, 150000000.00, 0.00),
(49, 41, 2, 0.00, 150000000.00),
(52, 99, 2, 600000.00, 0.00),
(58, 95, 2, 0.00, 2000000.00),
(59, 96, 2, 0.00, 1760000.00),
(62, 53, 2, 440000.00, 0.00);

-- --------------------------------------------------------

--
-- Table structure for table `approval_workflows`
--

CREATE TABLE `approval_workflows` (
  `id` int(11) NOT NULL,
  `department_id` int(11) DEFAULT NULL,
  `item_category_id` int(11) DEFAULT NULL,
  `amount_threshold` decimal(15,2) DEFAULT NULL,
  `approval_level` int(11) NOT NULL,
  `approver_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `backup_logs`
--

CREATE TABLE `backup_logs` (
  `id` int(11) NOT NULL,
  `filename` varchar(255) DEFAULT NULL,
  `sent_to` varchar(255) DEFAULT NULL,
  `status` varchar(50) DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `backup_settings`
--

CREATE TABLE `backup_settings` (
  `id` int(11) NOT NULL,
  `email` varchar(255) NOT NULL,
  `frequency` enum('daily','weekly') DEFAULT 'daily',
  `time` time NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `bank_accounts`
--

CREATE TABLE `bank_accounts` (
  `id` int(11) NOT NULL,
  `account_name` varchar(100) NOT NULL,
  `account_number` varchar(50) NOT NULL,
  `bank_name` varchar(100) NOT NULL,
  `currency` varchar(3) DEFAULT 'USD',
  `opening_balance` decimal(15,2) NOT NULL,
  `current_balance` decimal(15,2) NOT NULL,
  `is_active` tinyint(1) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `bank_statements`
--

CREATE TABLE `bank_statements` (
  `id` int(11) NOT NULL,
  `bank_account_id` int(11) NOT NULL,
  `statement_date` date NOT NULL,
  `start_date` date NOT NULL,
  `end_date` date NOT NULL,
  `opening_balance` decimal(15,2) NOT NULL,
  `closing_balance` decimal(15,2) NOT NULL,
  `imported_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `imported_by` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `bank_transactions`
--

CREATE TABLE `bank_transactions` (
  `id` int(11) NOT NULL,
  `statement_id` int(11) NOT NULL,
  `transaction_date` date NOT NULL,
  `description` text NOT NULL,
  `reference` varchar(100) DEFAULT NULL,
  `amount` decimal(15,2) NOT NULL,
  `balance` decimal(15,2) NOT NULL,
  `status` enum('unreconciled','matched','reconciled') DEFAULT 'unreconciled',
  `system_transaction_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `borrower`
--

CREATE TABLE `borrower` (
  `id` int(11) NOT NULL,
  `id_number` varchar(20) NOT NULL,
  `first_name` varchar(50) NOT NULL,
  `last_name` varchar(50) NOT NULL,
  `address` varchar(255) DEFAULT NULL,
  `phone_number` varchar(20) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `borrower`
--

INSERT INTO `borrower` (`id`, `id_number`, `first_name`, `last_name`, `address`, `phone_number`, `email`, `created_at`) VALUES
(1, '42001R2', 'MUSABYIMANA ', 'Ange Rose', 'GASABO District', '0788', 'mus@gmail.com', '2025-04-23 11:50:54'),
(2, '4200102R', 'UWIMANA ', 'Clementine', 'Gasabo', '079', 'ucle@gmail.com', '2025-04-23 12:01:25'),
(3, '4200202R', 'pascal', 'Twizerimana', 'kigali Rwanda', '11100000', 'pascal@panatechrwanda.com', '2025-04-25 18:08:40'),
(4, '4200302R', 'Panatech', 'ltd', 'kigali Rwanda', '78607470', 'panatechrwanda@gmail.com', '2025-04-30 09:24:02');

-- --------------------------------------------------------

--
-- Table structure for table `borrowers`
--

CREATE TABLE `borrowers` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `company_name` varchar(100) DEFAULT NULL,
  `tax_id` varchar(50) DEFAULT NULL,
  `credit_score` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `chart_of_accounts`
--

CREATE TABLE `chart_of_accounts` (
  `id` int(11) NOT NULL,
  `account_code` varchar(20) NOT NULL,
  `account_name` varchar(100) NOT NULL,
  `account_type` enum('asset','liability','equity','revenue','expense','stock','finances','receivables','payables') NOT NULL,
  `parent_account` int(11) DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `chart_of_accounts`
--

INSERT INTO `chart_of_accounts` (`id`, `account_code`, `account_name`, `account_type`, `parent_account`, `is_active`) VALUES
(1, '100101P', 'Donation for Polyclinic', 'revenue', 76, 1),
(2, '100201P', 'Consultation fees from Polyclinic', 'revenue', 76, 1),
(3, '100301P', ' Medical acts', 'revenue', 76, 1),
(4, '100401P', 'Sales of drug', 'revenue', 76, 1),
(5, '100501P', 'Laboratory examinations', 'revenue', 76, 1),
(6, '100601P', 'Medical Imaging', 'revenue', 76, 1),
(7, '100102R', 'Donation for RCEF', 'revenue', 76, 1),
(8, '100103W', 'Gain on Exchange', 'revenue', 76, 1),
(9, '100203w', 'Other Revenues from church', 'revenue', 76, 1),
(10, '200102R', 'Net Salary  for Staff', 'expense', 83, 1),
(11, '200202R', 'Pension Contribution', 'expense', 83, 1),
(12, '200302R', 'Maternity Contributions', 'expense', 83, 1),
(13, '200104R', 'Mutuel Contributions', 'expense', 83, 1),
(14, '210102R', 'Office Supplies ', 'expense', 85, 1),
(15, '210202R', 'Office consumable', 'expense', 85, 1),
(16, '210302R', 'Rent of Office premises', 'expense', 85, 1),
(17, '210402R', 'Electricity', 'expense', 85, 1),
(18, '210502R', 'Water', 'expense', 85, 1),
(19, '210602R', 'Security services', 'expense', 85, 1),
(20, '210702R', 'Cleaning and waste collection', 'expense', 85, 1),
(21, '210802R', 'Internet ', 'expense', 85, 1),
(22, '210902R', 'communication  fees', 'expense', 85, 1),
(23, '230102R', 'National Transport ', 'expense', 86, 1),
(24, '230202R', 'Other Transport', 'expense', 86, 1),
(25, '230302R', 'Fuel', 'expense', 86, 1),
(26, '230402R', 'Rent of vehicle and other transport means', 'expense', 86, 1),
(27, '230502R', 'Rent of Machinery', 'expense', 86, 1),
(28, '230602R', ' international transport', 'expense', 86, 1),
(29, '230702R', 'Mission fees', 'expense', 86, 1),
(30, '240102R', 'Meals ', 'expense', 87, 1),
(31, '240202R', ' Drinks', 'expense', 87, 1),
(32, '240302R', 'Accomodation', 'expense', 87, 1),
(33, '240402R', 'Other Cost Related to guest reception', 'expense', 87, 1),
(34, '250102R', 'Managerial consultancies work', 'expense', 88, 1),
(35, '250202R', 'Construction Works', 'expense', 88, 1),
(36, '250302R', 'Audit Fees', 'expense', 88, 1),
(37, '211002R', 'Bank charges and Financial cost', 'expense', 85, 1),
(38, '250402R', 'Translation', 'expense', 88, 1),
(39, '260102R', 'Christmas Gift to Children', 'expense', 89, 1),
(40, '260202R', 'Gift to others in Kind', 'expense', 89, 1),
(41, '260302R', 'Donations and other support', 'expense', 89, 1),
(42, '270102R', 'School fees for supported children', 'expense', 91, 1),
(43, '270202R', 'CBHI Payment to supported children', 'expense', 91, 1),
(46, '270302R', 'Other school related support to children', 'expense', 91, 1),
(47, '310102R', ' Frw 0001432374137  Rwanda Children Educational Foundation', 'finances', 81, 1),
(48, '310202R', 'USD 0001432370883 Rwanda Children Educational Foundation', 'finances', 81, 1),
(49, '310302R', 'Access Frw  7002190104271501 RCEF', 'finances', 81, 1),
(50, '310402R', 'Access Frw 7002460204271503 RCEF Saving Account', 'finances', 81, 1),
(51, '310502R', 'SACCO KIMIHURURA Frw 4755 Rwanda  Children Educational Foundation', 'finances', 81, 1),
(52, '310602R', ' Petty Cash for RCEF', 'finances', 82, 1),
(53, '320102R', 'Stock in office in Kigali', 'stock', 79, 1),
(54, '320101P', 'Stock  for the polyclinic', 'stock', 79, 1),
(55, '510102R', 'Purchase of Office Chair', 'asset', 93, 1),
(56, '510202R', 'Purchase of Office Tables', 'asset', 93, 1),
(57, '510302R', 'Purchase of Computer Lap top and accessories', 'asset', 93, 1),
(58, '510402R', 'Purchase of Computer Desktop and accessories', 'asset', 93, 1),
(59, '510502R', 'Purchase of Printer', 'asset', 93, 1),
(60, '510602R', 'Purchase of Other ICT equipement', 'asset', 93, 1),
(62, '42001R2', 'MUSABYIMANA  Ange Rose', 'receivables', 78, 1),
(63, '280102R', 'Maintenance of Building', 'expense', 92, 1),
(64, '4200102R', 'UWIMANA  Clementine', 'receivables', 78, 1),
(65, '280202R', 'Maintenance of Vehicle and spare parts', 'expense', 92, 1),
(66, '280302R', 'MAintenance of ICT equipments and Spare parts', 'expense', 92, 1),
(67, '280402R', 'Maintenance of Office equipments and spare parts', 'expense', 92, 1),
(68, '280502R', 'Other Maintenance and spare parts', 'expense', 92, 1),
(69, '510702R', 'Purchase of Other equipment', 'asset', 93, 1),
(70, '6101', 'Adjustment on cash balances', 'finances', 80, 1),
(71, '6102', 'Adjustment on Receivable balances', 'receivables', 80, 1),
(72, '6103', 'Adjustment on payable balances', 'payables', 80, 1),
(73, '6104', 'Adjustment on Asset balances', 'asset', 0, 1),
(74, '6201', 'Regularization of revenue', 'revenue', 76, 1),
(75, '6202', 'Regularization of expense', 'expense', 0, 1),
(76, '100', 'REVENUE', 'revenue', 0, 1),
(78, '42', 'Account Receivable', 'receivables', 0, 1),
(79, '32', 'Stock', 'stock', 0, 1),
(80, '61', 'Adjustment on opening Balances', 'expense', 0, 1),
(81, '310', 'Bank account', 'finances', 0, 1),
(82, '311', 'Petty Cash', 'finances', 0, 1),
(83, '20', 'Salaries', 'expense', 0, 1),
(85, '21', 'Office expenses', 'expense', 0, 1),
(86, '23', 'Transport and Travel', 'expense', 0, 1),
(87, '24', 'Meeting and Reception', 'expense', 0, 1),
(88, '25', 'Contractual and professional works', 'expense', 0, 1),
(89, '26', 'Gift and Donation in Kind', 'expense', 0, 1),
(90, '260402R', 'Support to Vulnerable families', 'expense', 89, 1),
(91, '27', 'School fees and related support', 'expense', 0, 1),
(92, '28', 'Maintenance and spare parts', 'expense', 0, 1),
(93, '51', 'Purchase of asset', 'asset', 0, 1),
(94, '211202R', 'Exchange Loss', 'expense', 85, 1),
(95, '41001', 'Panatech Ltd', 'liability', 0, 1),
(96, '41002', 'Test Supplier', 'liability', 0, 1),
(98, '4200202R', 'pascal Twizerimana', 'receivables', 0, 1),
(99, '4200302R', 'Panatech ltd', 'receivables', 1, 1),
(100, '41003', 'Twizerimana Pascal', 'liability', 1, 1),
(101, '41004', 'IISS', 'liability', 1, 1),
(102, '41005', 'IISSlll', 'liability', 1, 1),
(103, '41006', 'NAME', 'liability', 1, 1);

-- --------------------------------------------------------

--
-- Table structure for table `customers`
--

CREATE TABLE `customers` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `email` varchar(100) DEFAULT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `address` text DEFAULT NULL,
  `tax_id` varchar(50) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `departments`
--

CREATE TABLE `departments` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `code` varchar(20) NOT NULL,
  `manager_id` int(11) DEFAULT NULL,
  `parent_id` int(11) DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `departments`
--

INSERT INTO `departments` (`id`, `name`, `code`, `manager_id`, `parent_id`, `is_active`, `created_at`, `updated_at`) VALUES
(1, 'Super Admin', 'Admin', NULL, NULL, 1, '2025-04-08 13:55:04', '2025-04-09 07:31:43'),
(2, 'IT Depertment', 'IT', NULL, NULL, 1, '2025-04-09 07:28:28', '2025-04-09 07:28:28');

-- --------------------------------------------------------

--
-- Table structure for table `donations`
--

CREATE TABLE `donations` (
  `id` int(11) NOT NULL,
  `donor_id` int(11) NOT NULL,
  `amount` decimal(15,2) NOT NULL,
  `currency` varchar(3) DEFAULT 'RWF',
  `donation_date` date NOT NULL,
  `payment_method` varchar(50) DEFAULT NULL,
  `purpose` text DEFAULT NULL,
  `project_id` int(11) DEFAULT NULL,
  `is_acknowledged` tinyint(1) DEFAULT 0,
  `receipt_number` varchar(50) DEFAULT NULL,
  `created_by` int(11) NOT NULL,
  `recieptdoc` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `donations`
--

INSERT INTO `donations` (`id`, `donor_id`, `amount`, `currency`, `donation_date`, `payment_method`, `purpose`, `project_id`, `is_acknowledged`, `receipt_number`, `created_by`, `recieptdoc`) VALUES
(1, 5, 155.00, 'RWF', '2025-04-09', 'dsf', 'sdfsfsd', NULL, 0, '5', 1, NULL),
(2, 5, 10000.00, 'RWF', '2025-04-09', 'cash', 'dcsdfs', NULL, 0, '10', 1, NULL),
(3, 7, 100000.00, 'RWF', '2025-04-14', 'BANK TRANSFER', 'Test', NULL, 0, 'RWD545', 1, NULL),
(4, 6, 10000.00, 'RWF', '2025-04-30', 'BANK TRANSFER', 'TEST', NULL, 0, 'RWD5454', 1, NULL),
(5, 5, 78000.00, 'RWF', '2025-04-23', 'CASH', 'TEST', NULL, 0, '10', 1, NULL),
(6, 9, 10000.00, 'RWF', '2025-04-22', 'BANK TRANSFER', 'To build Houses', NULL, 1, 'RWD5454', 7, NULL),
(7, 6, 10000.00, 'RWF', '2025-04-22', 'BANK TRANSFER', 'dsc', NULL, 0, 'RWD5454', 7, NULL),
(8, 6, 150000000.00, 'RWF', '2025-04-29', 'BANK TRANSFER', 'test', NULL, 0, 'RWD5454', 10, NULL),
(9, 6, 150000000.00, 'RWF', '2025-04-29', 'BANK TRANSFER', 'test', NULL, 0, 'RWD5454', 10, NULL),
(10, 6, 150000000.00, 'RWF', '2025-04-29', 'BANK TRANSFER', '', NULL, 0, 'RWD5454', 10, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `donation_projects`
--

CREATE TABLE `donation_projects` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `start_date` date DEFAULT NULL,
  `end_date` date DEFAULT NULL,
  `target_amount` decimal(15,2) DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `donation_receipt_templates`
--

CREATE TABLE `donation_receipt_templates` (
  `id` int(11) NOT NULL,
  `name` varchar(50) NOT NULL,
  `template_html` text NOT NULL,
  `is_default` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `donors`
--

CREATE TABLE `donors` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `type` enum('individual','organization') NOT NULL,
  `category_id` int(11) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `address` text DEFAULT NULL,
  `tax_id` varchar(50) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `donors`
--

INSERT INTO `donors` (`id`, `name`, `type`, `category_id`, `email`, `phone`, `address`, `tax_id`, `created_at`) VALUES
(5, 'Test Company XYZ', 'individual', 1, 'protegene@mail.com', '0786074570', '1277', '155', '2025-04-09 12:35:17'),
(6, 'Pascal Twizerimana', 'organization', 2, 'protegene@mail.com', '0786074570', 'somen', '123', '2025-04-14 10:17:28'),
(7, 'Test Donor name', 'organization', 2, 'email@mail.com', '0786074570', 'Test', '45', '2025-04-14 10:19:28'),
(8, 'Somen', 'individual', 1, 'SMA@gmail.com', '', 'ASD', '55', '2025-04-14 10:20:15'),
(9, 'Ufitumukiza Faustin', 'individual', 1, 'panatechrwanda@gmail.com', '78607470', 'kigali Rwanda', 'USA', '2025-04-22 17:18:56');

-- --------------------------------------------------------

--
-- Table structure for table `donor_categories`
--

CREATE TABLE `donor_categories` (
  `id` int(11) NOT NULL,
  `name` varchar(50) NOT NULL,
  `description` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `donor_categories`
--

INSERT INTO `donor_categories` (`id`, `name`, `description`) VALUES
(1, 'School fees', NULL),
(2, 'Projects', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `employees`
--

CREATE TABLE `employees` (
  `id` int(11) NOT NULL,
  `full_name` varchar(255) DEFAULT NULL,
  `email` varchar(255) DEFAULT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `hire_date` date DEFAULT NULL,
  `salary` decimal(10,2) DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `employees`
--

INSERT INTO `employees` (`id`, `full_name`, `email`, `phone`, `hire_date`, `salary`, `is_active`, `created_at`) VALUES
(7, 'Panatech Company ltd', 'panatechrwanda@gmail.com', '78607470', '2025-04-13', 250000.00, 1, '2025-04-14 03:24:00'),
(12, 'pascal Twizerimana', 'pascal@panatechrwanda.com', '11100000', '2025-04-13', 250000.00, 1, '2025-04-14 03:31:18'),
(20, 'gthg', 'po@gmail.com', '78607470', '2025-04-14', 5000.00, 1, '2025-04-14 12:39:32'),
(21, 'NKURIKIYIMANA Edmond', 'nkuredi@yahoo.fr', '0788359272', '2025-04-14', 728541.00, 1, '2025-04-14 12:40:46'),
(22, 'UWIMANA Jeanne', 'jeanneuwimana06@gmail.com', '0786584968', '2025-05-01', 383046.00, 1, '2025-04-14 12:42:06'),
(23, 'GAHIZI Gloria', 'glorigah12@gmail.com', '078387832', '2025-05-06', 320251.00, 1, '2025-04-14 12:43:17'),
(24, 'RCEF', 'rcefrw2013@gmail.com', '0787893208', '2025-04-14', 0.00, 1, '2025-04-14 12:44:54'),
(25, 'Admin Testing account', 'panatech@gmail.com', '0786074570', '2025-05-01', 500000.00, 1, '2025-04-14 15:06:16');

-- --------------------------------------------------------

--
-- Table structure for table `employee_permissions`
--

CREATE TABLE `employee_permissions` (
  `id` int(11) NOT NULL,
  `employee_id` int(11) NOT NULL,
  `permission_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `employee_permissions`
--

INSERT INTO `employee_permissions` (`id`, `employee_id`, `permission_id`) VALUES
(3, 25, 1),
(4, 25, 2),
(5, 25, 3),
(6, 25, 4),
(7, 25, 5),
(8, 25, 2),
(9, 25, 3),
(10, 25, 2),
(11, 25, 3),
(12, 25, 2),
(13, 25, 3),
(14, 25, 2),
(15, 25, 3),
(16, 25, 2),
(17, 25, 3),
(18, 25, 2),
(19, 25, 3);

-- --------------------------------------------------------

--
-- Table structure for table `employee_roles`
--

CREATE TABLE `employee_roles` (
  `id` int(11) NOT NULL,
  `employee_id` int(11) NOT NULL,
  `role_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `employee_roles`
--

INSERT INTO `employee_roles` (`id`, `employee_id`, `role_id`) VALUES
(1, 12, 3),
(2, 12, 1),
(3, 12, 8),
(4, 12, 7),
(5, 12, 2),
(6, 12, 4),
(7, 12, 5),
(8, 12, 6),
(13, 21, 3),
(14, 21, 8),
(15, 21, 7),
(16, 21, 2),
(17, 21, 4),
(18, 21, 5),
(19, 21, 6),
(23, 23, 3),
(24, 23, 8),
(25, 23, 7),
(26, 23, 4),
(27, 24, 3),
(28, 24, 1),
(29, 24, 8),
(30, 24, 7),
(31, 24, 2),
(32, 24, 4),
(33, 24, 5),
(34, 24, 6),
(43, 22, 3),
(44, 22, 1),
(45, 22, 8),
(46, 22, 7),
(47, 22, 2),
(48, 22, 4),
(49, 22, 5),
(50, 22, 6),
(122, 7, 3),
(123, 7, 1),
(124, 7, 8),
(125, 7, 7),
(126, 7, 2),
(127, 7, 4),
(128, 7, 5),
(129, 7, 6),
(130, 20, 3),
(164, 25, 3),
(165, 25, 1),
(166, 25, 8),
(167, 25, 7),
(168, 25, 2),
(169, 25, 4),
(170, 25, 5),
(171, 25, 6);

-- --------------------------------------------------------

--
-- Table structure for table `fee_payments`
--

CREATE TABLE `fee_payments` (
  `id` int(11) NOT NULL,
  `student_id` int(11) DEFAULT NULL,
  `term_id` int(11) DEFAULT NULL,
  `amount` decimal(10,2) DEFAULT NULL,
  `bank_name` varchar(100) DEFAULT NULL,
  `payment_date` date DEFAULT NULL,
  `reference_number` varchar(100) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `fixed_assets`
--

CREATE TABLE `fixed_assets` (
  `id` int(11) NOT NULL,
  `asset_name` varchar(255) DEFAULT NULL,
  `category` varchar(100) DEFAULT NULL,
  `purchase_date` date DEFAULT NULL,
  `cost` decimal(15,2) DEFAULT NULL,
  `useful_life` int(11) DEFAULT NULL,
  `depreciation_method` enum('straight_line','reducing_balance') DEFAULT 'straight_line',
  `salvage_value` decimal(15,2) DEFAULT 0.00,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `fixed_assets`
--

INSERT INTO `fixed_assets` (`id`, `asset_name`, `category`, `purchase_date`, `cost`, `useful_life`, `depreciation_method`, `salvage_value`, `created_at`) VALUES
(1, 'HP - 494', 'Electronic', '2025-04-02', 430000.00, 5, 'reducing_balance', 0.00, '2025-04-25 12:18:39');

-- --------------------------------------------------------

--
-- Table structure for table `internal_requisitions`
--

CREATE TABLE `internal_requisitions` (
  `id` int(11) NOT NULL,
  `requisition_number` varchar(20) NOT NULL,
  `requester_id` int(11) NOT NULL,
  `department_id` int(11) NOT NULL,
  `request_date` datetime NOT NULL DEFAULT current_timestamp(),
  `needed_by_date` date NOT NULL,
  `purpose` text NOT NULL,
  `status` enum('draft','submitted','approved','partially_fulfilled','fulfilled','rejected','cancelled') NOT NULL DEFAULT 'draft',
  `notes` text DEFAULT NULL,
  `updated_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `internal_requisitions`
--

INSERT INTO `internal_requisitions` (`id`, `requisition_number`, `requester_id`, `department_id`, `request_date`, `needed_by_date`, `purpose`, `status`, `notes`, `updated_at`) VALUES
(1, 'REQ-20250409-0001', 1, 1, '2025-04-09 03:20:45', '2025-04-16', 'tes', 'draft', NULL, '2025-04-09 04:09:47'),
(2, 'REQ-20250409-0002', 1, 1, '2025-04-09 03:21:35', '2025-04-16', 'tes', 'draft', NULL, '2025-04-09 04:09:27'),
(3, 'REQ-20250425-0001', 6, 2, '2025-04-25 16:01:30', '2025-05-02', ' Ditribution to children', 'submitted', NULL, '2025-04-25 16:01:30'),
(4, 'REQ-20250425-0002', 10, 2, '2025-04-25 16:32:25', '2025-05-02', 'Test', 'submitted', NULL, '2025-04-25 16:32:25'),
(5, 'REQ-20250427-0001', 10, 2, '2025-04-27 13:53:29', '2025-05-04', 'Test', 'approved', NULL, '2025-04-27 13:53:29');

-- --------------------------------------------------------

--
-- Table structure for table `internal_requisition_items`
--

CREATE TABLE `internal_requisition_items` (
  `id` int(11) NOT NULL,
  `requisition_id` int(11) NOT NULL,
  `item_id` int(11) NOT NULL,
  `quantity` decimal(10,2) NOT NULL,
  `unit_of_measure` varchar(20) NOT NULL,
  `purpose` text DEFAULT NULL,
  `status` enum('pending','approved','partially_fulfilled','fulfilled','rejected') NOT NULL DEFAULT 'pending',
  `fulfilled_quantity` decimal(10,2) NOT NULL DEFAULT 0.00
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `internal_requisition_items`
--

INSERT INTO `internal_requisition_items` (`id`, `requisition_id`, `item_id`, `quantity`, `unit_of_measure`, `purpose`, `status`, `fulfilled_quantity`) VALUES
(1, 1, 2, 1.00, 'pieses', '', 'pending', 0.00),
(2, 1, 1, 1.00, '', '', 'pending', 0.00),
(3, 2, 2, 52.00, 'pieses', 'tesr', 'pending', 0.00),
(4, 3, 2, 100.00, 'pieses', ' distribution to  Children', 'pending', 0.00),
(5, 4, 2, 10.00, 'pieses', '', 'pending', 0.00),
(6, 5, 2, 10.00, 'pieses', '10', 'pending', 0.00);

-- --------------------------------------------------------

--
-- Table structure for table `inventory_adjustments`
--

CREATE TABLE `inventory_adjustments` (
  `id` int(11) NOT NULL,
  `adjustment_date` datetime NOT NULL,
  `reference` varchar(100) DEFAULT NULL,
  `reason` text DEFAULT NULL,
  `created_by` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `inventory_categories`
--

CREATE TABLE `inventory_categories` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `parent_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `inventory_categories`
--

INSERT INTO `inventory_categories` (`id`, `name`, `parent_id`) VALUES
(1, 'Electronics', NULL),
(2, 'Office Supplies', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `inventory_items`
--

CREATE TABLE `inventory_items` (
  `id` int(11) NOT NULL,
  `item_code` varchar(50) NOT NULL,
  `barcode` varchar(50) DEFAULT NULL,
  `name` varchar(100) NOT NULL,
  `category_id` int(11) DEFAULT NULL,
  `supplier_id` int(11) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `unit_of_measure` varchar(20) DEFAULT NULL,
  `current_quantity` decimal(10,2) DEFAULT 0.00,
  `reorder_level` decimal(10,2) DEFAULT 0.00,
  `cost_price` decimal(10,2) DEFAULT NULL,
  `selling_price` decimal(10,2) DEFAULT NULL,
  `image_path` varchar(255) DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `inventory_items`
--

INSERT INTO `inventory_items` (`id`, `item_code`, `barcode`, `name`, `category_id`, `supplier_id`, `description`, `unit_of_measure`, `current_quantity`, `reorder_level`, `cost_price`, `selling_price`, `image_path`, `is_active`) VALUES
(1, 'Test', '0', 'Test', 1, 1, 'testasdfa', '', 0.00, 0.00, 1000.00, 1200.00, NULL, 1),
(2, '001', '', 'Shoes', 1, 1, 'test', 'pieses', 0.00, 0.00, 1000.00, 100.00, NULL, 1);

-- --------------------------------------------------------

--
-- Table structure for table `inventory_locations`
--

CREATE TABLE `inventory_locations` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `code` varchar(20) NOT NULL,
  `description` text DEFAULT NULL,
  `parent_id` int(11) DEFAULT NULL,
  `location_type` enum('warehouse','shelf','bin','room','other') NOT NULL DEFAULT 'warehouse',
  `address` text DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT NULL ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `inventory_locations`
--

INSERT INTO `inventory_locations` (`id`, `name`, `code`, `description`, `parent_id`, `location_type`, `address`, `is_active`, `created_at`, `updated_at`) VALUES
(1, 'Stock', '1', NULL, NULL, 'warehouse', NULL, 1, '2025-04-08 09:46:38', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `inventory_movements`
--

CREATE TABLE `inventory_movements` (
  `id` int(11) NOT NULL,
  `item_id` int(11) NOT NULL,
  `movement_type` enum('purchase','sale','adjustment','transfer_in','transfer_out','return') NOT NULL,
  `quantity` decimal(10,2) NOT NULL,
  `reference_id` int(11) DEFAULT NULL COMMENT 'ID of related transaction (purchase, sale, adjustment, etc)',
  `reference_type` varchar(50) DEFAULT NULL COMMENT 'Type of related transaction',
  `date_time` datetime NOT NULL,
  `cost_price` decimal(10,2) DEFAULT NULL,
  `selling_price` decimal(10,2) DEFAULT NULL,
  `location_id` int(11) DEFAULT NULL COMMENT 'If tracking multiple locations',
  `notes` text DEFAULT NULL,
  `created_by` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Stand-in structure for view `inventory_movement_report`
-- (See below for the actual view)
--
CREATE TABLE `inventory_movement_report` (
`id` int(11)
,`date_time` datetime
,`item_id` int(11)
,`item_code` varchar(50)
,`name` varchar(100)
,`movement_type` enum('purchase','sale','adjustment','transfer_in','transfer_out','return')
,`movement_type_name` varchar(17)
,`quantity` decimal(10,2)
,`unit_of_measure` varchar(20)
,`cost_price` decimal(10,2)
,`selling_price` decimal(10,2)
,`user_name` varchar(201)
,`reference_id` int(11)
,`reference_type` varchar(50)
,`notes` text
,`category_name` varchar(100)
,`supplier_name` varchar(100)
,`reference_display` varchar(50)
);

-- --------------------------------------------------------

--
-- Table structure for table `loans`
--

CREATE TABLE `loans` (
  `id` int(11) NOT NULL,
  `borrower_id` varchar(200) NOT NULL,
  `amount` decimal(12,2) NOT NULL,
  `interest_rate` decimal(5,2) NOT NULL,
  `term_months` int(11) NOT NULL,
  `start_date` date DEFAULT NULL,
  `status` enum('pending','approved','rejected','completed') DEFAULT 'pending',
  `purpose` text DEFAULT NULL,
  `created_by` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `loans`
--

INSERT INTO `loans` (`id`, `borrower_id`, `amount`, `interest_rate`, `term_months`, `start_date`, `status`, `purpose`, `created_by`, `created_at`) VALUES
(1, '4200102', 500000.00, 0.00, 6, '2025-04-25', 'approved', 'Loan disbursed', 6, '2025-04-25 13:07:04'),
(2, '1', 500000.00, 5.00, 12, '2025-04-25', 'approved', 'Loan Disbursement', 6, '2025-04-25 13:29:38'),
(3, '3', 1500000.00, 9.00, 12, '2025-04-25', 'approved', 'Testing Loan module', 10, '2025-04-25 18:13:52'),
(4, '3', 80000.00, 1.00, 6, '2025-04-25', 'approved', 'Account', 10, '2025-04-25 19:25:24'),
(5, '2', 60000.00, 1.00, 12, '2025-04-25', 'approved', 'test', 10, '2025-04-25 19:31:39'),
(6, '3', 60000.00, 5.00, 6, '2025-04-25', 'approved', 'g', 10, '2025-04-25 19:36:02'),
(7, '3', 8888.00, 1.00, 5, '2025-04-25', 'approved', 'fd', 10, '2025-04-25 19:38:13'),
(8, '3', 50000.00, 5.00, 5, '2025-04-25', 'approved', '1', 10, '2025-04-25 19:42:25'),
(9, '2', 5000.00, 1.00, 2, '2025-04-25', 'approved', 'test', 10, '2025-04-25 19:58:57'),
(10, '3', 180000.00, 1.00, 15, '2025-04-25', 'approved', 'test', 10, '2025-04-25 21:24:30'),
(11, '3', 600000.00, 1.00, 15, '2025-04-29', 'approved', 'Security', 10, '2025-04-29 08:41:46'),
(12, '4', 600000.00, 2.00, 15, '2025-04-30', 'approved', '', 10, '2025-04-30 09:24:28');

-- --------------------------------------------------------

--
-- Table structure for table `loan_documents`
--

CREATE TABLE `loan_documents` (
  `id` int(11) NOT NULL,
  `loan_id` int(11) NOT NULL,
  `document_type` varchar(100) DEFAULT NULL,
  `file_path` varchar(255) DEFAULT NULL,
  `uploaded_by` int(11) DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `uploaded_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `loan_repayments`
--

CREATE TABLE `loan_repayments` (
  `id` int(11) NOT NULL,
  `loan_id` int(11) NOT NULL,
  `due_date` date NOT NULL,
  `amount_due` decimal(12,2) NOT NULL,
  `amount_paid` decimal(12,2) DEFAULT 0.00,
  `payment_date` date DEFAULT NULL,
  `receipt_file` varchar(255) DEFAULT NULL,
  `payment_method` varchar(50) DEFAULT NULL,
  `reference_number` varchar(100) DEFAULT NULL,
  `status` enum('unpaid','partial','paid') DEFAULT 'unpaid',
  `is_paid` int(11) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `loan_repayments`
--

INSERT INTO `loan_repayments` (`id`, `loan_id`, `due_date`, `amount_due`, `amount_paid`, `payment_date`, `receipt_file`, `payment_method`, `reference_number`, `status`, `is_paid`) VALUES
(1, 2, '2025-05-25', 42803.74, 0.00, NULL, NULL, NULL, NULL, 'unpaid', 0),
(2, 2, '2025-06-25', 42803.74, 0.00, NULL, NULL, NULL, NULL, 'unpaid', 0),
(3, 2, '2025-07-25', 42803.74, 0.00, NULL, NULL, NULL, NULL, 'unpaid', 0),
(4, 2, '2025-08-25', 42803.74, 0.00, NULL, NULL, NULL, NULL, 'unpaid', 0),
(5, 2, '2025-09-25', 42803.74, 0.00, NULL, NULL, NULL, NULL, 'unpaid', 0),
(6, 2, '2025-10-25', 42803.74, 0.00, NULL, NULL, NULL, NULL, 'unpaid', 0),
(7, 2, '2025-11-25', 42803.74, 0.00, NULL, NULL, NULL, NULL, 'unpaid', 0),
(8, 2, '2025-12-25', 42803.74, 0.00, NULL, NULL, NULL, NULL, 'unpaid', 0),
(9, 2, '2026-01-25', 42803.74, 0.00, NULL, NULL, NULL, NULL, 'unpaid', 0),
(10, 2, '2026-02-25', 42803.74, 0.00, NULL, NULL, NULL, NULL, 'unpaid', 0),
(11, 2, '2026-03-25', 42803.74, 0.00, NULL, NULL, NULL, NULL, 'unpaid', 0),
(12, 2, '2026-04-25', 42803.74, 0.00, NULL, NULL, NULL, NULL, 'unpaid', 0),
(13, 3, '2025-05-25', 131177.22, 131177.22, '2025-04-30', NULL, 'cash', 'RCEF-20250430111310', 'paid', 1),
(14, 3, '2025-06-25', 131177.22, 131177.22, '2025-04-30', NULL, 'cash', 'RCEF-20250430113118', 'paid', 1),
(15, 3, '2025-07-25', 131177.22, 131177.20, '2025-04-30', NULL, 'cash', 'RCEF-20250430113506', 'partial', 0),
(16, 3, '2025-08-25', 131177.22, 0.00, NULL, NULL, NULL, NULL, 'unpaid', 0),
(17, 3, '2025-09-25', 131177.22, 0.00, NULL, NULL, NULL, NULL, 'unpaid', 0),
(18, 3, '2025-10-25', 131177.22, 0.00, NULL, NULL, NULL, NULL, 'unpaid', 0),
(19, 3, '2025-11-25', 131177.22, 0.00, NULL, NULL, NULL, NULL, 'unpaid', 0),
(20, 3, '2025-12-25', 131177.22, 0.00, NULL, NULL, NULL, NULL, 'unpaid', 0),
(21, 3, '2026-01-25', 131177.22, 0.00, NULL, NULL, NULL, NULL, 'unpaid', 0),
(22, 3, '2026-02-25', 131177.22, 0.00, NULL, NULL, NULL, NULL, 'unpaid', 0),
(23, 3, '2026-03-25', 131177.22, 0.00, NULL, NULL, NULL, NULL, 'unpaid', 0),
(24, 3, '2026-04-25', 131177.22, 0.00, NULL, NULL, NULL, NULL, 'unpaid', 0),
(25, 4, '2025-05-25', 13372.25, 0.00, NULL, NULL, NULL, NULL, 'unpaid', 0),
(26, 4, '2025-06-25', 13372.25, 0.00, NULL, NULL, NULL, NULL, 'unpaid', 0),
(27, 4, '2025-07-25', 13372.25, 0.00, NULL, NULL, NULL, NULL, 'unpaid', 0),
(28, 4, '2025-08-25', 13372.25, 0.00, NULL, NULL, NULL, NULL, 'unpaid', 0),
(29, 4, '2025-09-25', 13372.25, 0.00, NULL, NULL, NULL, NULL, 'unpaid', 0),
(30, 4, '2025-10-25', 13372.25, 0.00, NULL, NULL, NULL, NULL, 'unpaid', 0),
(31, 5, '2025-05-25', 5027.12, 0.00, NULL, NULL, NULL, NULL, 'unpaid', 0),
(32, 5, '2025-06-25', 5027.12, 0.00, NULL, NULL, NULL, NULL, 'unpaid', 0),
(33, 5, '2025-07-25', 5027.12, 0.00, NULL, NULL, NULL, NULL, 'unpaid', 0),
(34, 5, '2025-08-25', 5027.12, 0.00, NULL, NULL, NULL, NULL, 'unpaid', 0),
(35, 5, '2025-09-25', 5027.12, 0.00, NULL, NULL, NULL, NULL, 'unpaid', 0),
(36, 5, '2025-10-25', 5027.12, 0.00, NULL, NULL, NULL, NULL, 'unpaid', 0),
(37, 5, '2025-11-25', 5027.12, 0.00, NULL, NULL, NULL, NULL, 'unpaid', 0),
(38, 5, '2025-12-25', 5027.12, 0.00, NULL, NULL, NULL, NULL, 'unpaid', 0),
(39, 5, '2026-01-25', 5027.12, 0.00, NULL, NULL, NULL, NULL, 'unpaid', 0),
(40, 5, '2026-02-25', 5027.12, 0.00, NULL, NULL, NULL, NULL, 'unpaid', 0),
(41, 5, '2026-03-25', 5027.12, 0.00, NULL, NULL, NULL, NULL, 'unpaid', 0),
(42, 5, '2026-04-25', 5027.12, 0.00, NULL, NULL, NULL, NULL, 'unpaid', 0),
(43, 7, '2025-05-25', 1782.05, 1782.05, '2025-04-29', NULL, 'bank_transfer', 'RCEF-20250429105957', 'paid', 1),
(44, 7, '2025-06-25', 1782.05, 0.00, NULL, NULL, NULL, NULL, 'unpaid', 0),
(45, 7, '2025-07-25', 1782.05, 0.00, NULL, NULL, NULL, NULL, 'unpaid', 0),
(46, 7, '2025-08-25', 1782.05, 0.00, NULL, NULL, NULL, NULL, 'unpaid', 0),
(47, 7, '2025-09-25', 1782.05, 0.00, NULL, NULL, NULL, NULL, 'unpaid', 0),
(48, 7, '2025-05-25', 1782.05, 1782.05, '2025-04-30', NULL, 'bank_transfer', 'RCEF-20250430110949', 'paid', 1),
(49, 7, '2025-06-25', 1782.05, 0.00, NULL, NULL, NULL, NULL, 'unpaid', 0),
(50, 7, '2025-07-25', 1782.05, 0.00, NULL, NULL, NULL, NULL, 'unpaid', 0),
(51, 7, '2025-08-25', 1782.05, 0.00, NULL, NULL, NULL, NULL, 'unpaid', 0),
(52, 7, '2025-09-25', 1782.05, 0.00, NULL, NULL, NULL, NULL, 'unpaid', 0),
(53, 6, '2025-05-25', 10146.34, 0.00, NULL, NULL, NULL, NULL, 'unpaid', 0),
(54, 6, '2025-06-25', 10146.34, 0.00, NULL, NULL, NULL, NULL, 'unpaid', 0),
(55, 6, '2025-07-25', 10146.34, 0.00, NULL, NULL, NULL, NULL, 'unpaid', 0),
(56, 6, '2025-08-25', 10146.34, 0.00, NULL, NULL, NULL, NULL, 'unpaid', 0),
(57, 6, '2025-09-25', 10146.34, 0.00, NULL, NULL, NULL, NULL, 'unpaid', 0),
(58, 6, '2025-10-25', 10146.34, 0.00, NULL, NULL, NULL, NULL, 'unpaid', 0),
(59, 8, '2025-05-25', 10125.35, 0.00, NULL, NULL, NULL, NULL, 'unpaid', 0),
(60, 8, '2025-06-25', 10125.35, 0.00, NULL, NULL, NULL, NULL, 'unpaid', 0),
(61, 8, '2025-07-25', 10125.35, 0.00, NULL, NULL, NULL, NULL, 'unpaid', 0),
(62, 8, '2025-08-25', 10125.35, 0.00, NULL, NULL, NULL, NULL, 'unpaid', 0),
(63, 8, '2025-09-25', 10125.35, 0.00, NULL, NULL, NULL, NULL, 'unpaid', 0),
(64, 9, '2025-05-25', 2503.13, 0.00, NULL, NULL, NULL, NULL, 'unpaid', 0),
(65, 9, '2025-06-25', 2503.13, 0.00, NULL, NULL, NULL, NULL, 'unpaid', 0),
(66, 10, '2025-05-25', 12080.16, 0.00, NULL, NULL, NULL, NULL, 'unpaid', 0),
(67, 10, '2025-06-25', 12080.16, 0.00, NULL, NULL, NULL, NULL, 'unpaid', 0),
(68, 10, '2025-07-25', 12080.16, 0.00, NULL, NULL, NULL, NULL, 'unpaid', 0),
(69, 10, '2025-08-25', 12080.16, 0.00, NULL, NULL, NULL, NULL, 'unpaid', 0),
(70, 10, '2025-09-25', 12080.16, 0.00, NULL, NULL, NULL, NULL, 'unpaid', 0),
(71, 10, '2025-10-25', 12080.16, 0.00, NULL, NULL, NULL, NULL, 'unpaid', 0),
(72, 10, '2025-11-25', 12080.16, 0.00, NULL, NULL, NULL, NULL, 'unpaid', 0),
(73, 10, '2025-12-25', 12080.16, 0.00, NULL, NULL, NULL, NULL, 'unpaid', 0),
(74, 10, '2026-01-25', 12080.16, 0.00, NULL, NULL, NULL, NULL, 'unpaid', 0),
(75, 10, '2026-02-25', 12080.16, 0.00, NULL, NULL, NULL, NULL, 'unpaid', 0),
(76, 10, '2026-03-25', 12080.16, 0.00, NULL, NULL, NULL, NULL, 'unpaid', 0),
(77, 10, '2026-04-25', 12080.16, 0.00, NULL, NULL, NULL, NULL, 'unpaid', 0),
(78, 10, '2026-05-25', 12080.16, 0.00, NULL, NULL, NULL, NULL, 'unpaid', 0),
(79, 10, '2026-06-25', 12080.16, 0.00, NULL, NULL, NULL, NULL, 'unpaid', 0),
(80, 10, '2026-07-25', 12080.16, 0.00, NULL, NULL, NULL, NULL, 'unpaid', 0),
(81, 11, '2025-05-29', 40267.18, 0.00, NULL, NULL, NULL, NULL, 'unpaid', 0),
(82, 11, '2025-06-29', 40267.18, 0.00, NULL, NULL, NULL, NULL, 'unpaid', 0),
(83, 11, '2025-07-29', 40267.18, 0.00, NULL, NULL, NULL, NULL, 'unpaid', 0),
(84, 11, '2025-08-29', 40267.18, 0.00, NULL, NULL, NULL, NULL, 'unpaid', 0),
(85, 11, '2025-09-29', 40267.18, 0.00, NULL, NULL, NULL, NULL, 'unpaid', 0),
(86, 11, '2025-10-29', 40267.18, 0.00, NULL, NULL, NULL, NULL, 'unpaid', 0),
(87, 11, '2025-11-29', 40267.18, 0.00, NULL, NULL, NULL, NULL, 'unpaid', 0),
(88, 11, '2025-12-29', 40267.18, 0.00, NULL, NULL, NULL, NULL, 'unpaid', 0),
(89, 11, '2026-01-29', 40267.18, 0.00, NULL, NULL, NULL, NULL, 'unpaid', 0),
(90, 11, '2026-03-01', 40267.18, 0.00, NULL, NULL, NULL, NULL, 'unpaid', 0),
(91, 11, '2026-03-29', 40267.18, 0.00, NULL, NULL, NULL, NULL, 'unpaid', 0),
(92, 11, '2026-04-29', 40267.18, 0.00, NULL, NULL, NULL, NULL, 'unpaid', 0),
(93, 11, '2026-05-29', 40267.18, 0.00, NULL, NULL, NULL, NULL, 'unpaid', 0),
(94, 11, '2026-06-29', 40267.18, 0.00, NULL, NULL, NULL, NULL, 'unpaid', 0),
(95, 11, '2026-07-29', 40267.18, 0.00, NULL, NULL, NULL, NULL, 'unpaid', 0),
(96, 12, '2025-05-30', 40535.41, 40535.41, '2025-04-30', NULL, 'cash', 'RCEF-20250430112619', 'paid', 1),
(97, 12, '2025-06-30', 40535.41, 40535.41, '2025-04-30', NULL, 'cash', 'RCEF-20250430112921', 'paid', 1),
(98, 12, '2025-07-30', 40535.41, 0.00, NULL, NULL, NULL, NULL, 'unpaid', 0),
(99, 12, '2025-08-30', 40535.41, 0.00, NULL, NULL, NULL, NULL, 'unpaid', 0),
(100, 12, '2025-09-30', 40535.41, 0.00, NULL, NULL, NULL, NULL, 'unpaid', 0),
(101, 12, '2025-10-30', 40535.41, 0.00, NULL, NULL, NULL, NULL, 'unpaid', 0),
(102, 12, '2025-11-30', 40535.41, 0.00, NULL, NULL, NULL, NULL, 'unpaid', 0),
(103, 12, '2025-12-30', 40535.41, 0.00, NULL, NULL, NULL, NULL, 'unpaid', 0),
(104, 12, '2026-01-30', 40535.41, 0.00, NULL, NULL, NULL, NULL, 'unpaid', 0),
(105, 12, '2026-03-02', 40535.41, 0.00, NULL, NULL, NULL, NULL, 'unpaid', 0),
(106, 12, '2026-03-30', 40535.41, 0.00, NULL, NULL, NULL, NULL, 'unpaid', 0),
(107, 12, '2026-04-30', 40535.41, 0.00, NULL, NULL, NULL, NULL, 'unpaid', 0),
(108, 12, '2026-05-30', 40535.41, 0.00, NULL, NULL, NULL, NULL, 'unpaid', 0),
(109, 12, '2026-06-30', 40535.41, 0.00, NULL, NULL, NULL, NULL, 'unpaid', 0),
(110, 12, '2026-07-30', 40535.41, 0.00, NULL, NULL, NULL, NULL, 'unpaid', 0);

-- --------------------------------------------------------

--
-- Table structure for table `payment_types`
--

CREATE TABLE `payment_types` (
  `id` int(11) NOT NULL,
  `name` varchar(50) NOT NULL,
  `is_cash` tinyint(1) DEFAULT 0,
  `requires_authorization` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `payment_types`
--

INSERT INTO `payment_types` (`id`, `name`, `is_cash`, `requires_authorization`) VALUES
(1, 'Cash', 1, 0),
(2, 'M-Pesa', 0, 0),
(3, 'Credit Card', 0, 0);

-- --------------------------------------------------------

--
-- Table structure for table `payroll`
--

CREATE TABLE `payroll` (
  `id` int(11) NOT NULL,
  `employee_id` int(11) DEFAULT NULL,
  `gross_salary` decimal(10,2) DEFAULT NULL,
  `total_reduction` decimal(10,2) DEFAULT NULL,
  `net_salary` decimal(10,2) DEFAULT NULL,
  `pay_date` date DEFAULT NULL,
  `generated_by` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `payrolls`
--

CREATE TABLE `payrolls` (
  `id` int(11) NOT NULL,
  `employee_id` int(11) NOT NULL,
  `gross_salary` decimal(12,2) NOT NULL,
  `transport` decimal(12,2) DEFAULT 0.00,
  `emp_pension` decimal(12,2) DEFAULT NULL,
  `emp_rama` decimal(12,2) DEFAULT NULL,
  `emp_maternity` decimal(12,2) DEFAULT NULL,
  `emp_cbhi` decimal(12,2) DEFAULT NULL,
  `total_deductions` decimal(12,2) DEFAULT NULL,
  `employer_pension` decimal(12,2) DEFAULT NULL,
  `employer_occupational` decimal(12,2) DEFAULT NULL,
  `employer_rama` decimal(12,2) DEFAULT NULL,
  `employer_maternity` decimal(12,2) DEFAULT NULL,
  `employer_cbhi` decimal(12,2) DEFAULT NULL,
  `total_employer_contribution` decimal(12,2) DEFAULT NULL,
  `net_salary` decimal(12,2) DEFAULT NULL,
  `month` varchar(20) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `payrolls`
--

INSERT INTO `payrolls` (`id`, `employee_id`, `gross_salary`, `transport`, `emp_pension`, `emp_rama`, `emp_maternity`, `emp_cbhi`, `total_deductions`, `employer_pension`, `employer_occupational`, `employer_rama`, `employer_maternity`, `employer_cbhi`, `total_employer_contribution`, `net_salary`, `month`, `created_at`) VALUES
(1, 7, 250000.00, 30000.00, 15000.00, 18750.00, 750.00, 750.00, 35250.00, 15000.00, 4400.00, 18750.00, 750.00, 750.00, 39650.00, 214750.00, '2025-01', '2025-04-22 16:03:35'),
(2, 13, 150000.00, 0.00, 9000.00, 11250.00, 450.00, 450.00, 21150.00, 9000.00, 3000.00, 11250.00, 450.00, 450.00, 24150.00, 128850.00, '2025-01', '2025-04-22 16:07:14'),
(3, 21, 721456.00, 0.00, 43287.36, 54109.20, 2164.37, 2164.37, 101725.30, 43287.36, 14429.12, 54109.20, 2164.37, 2164.37, 116154.42, 619730.70, '2025-04', '2025-04-24 07:17:46'),
(4, 23, 285000.00, 0.00, 17100.00, 85500.00, 855.00, 855.00, 104310.00, 17100.00, 5700.00, 21375.00, 855.00, 855.00, 45885.00, 180690.00, '2025-04', '2025-04-25 13:45:42'),
(5, 23, 300000.00, 1000.00, NULL, 54000.00, 900.00, 900.00, 55800.00, 18000.00, 5980.00, 22500.00, 900.00, 900.00, 48280.00, 244200.00, '2025-03', '2025-04-29 08:26:54'),
(6, 25, 20000.00, 0.00, NULL, 0.00, 60.00, 60.00, 120.00, 1200.00, 400.00, 1500.00, 60.00, 60.00, 3220.00, 19880.00, '2025-04', '2025-04-29 08:34:49'),
(7, 20, 500000.00, 1000.00, NULL, 114000.00, 1500.00, 1500.00, 117000.00, 30000.00, 9980.00, 37500.00, 1500.00, 1500.00, 80480.00, 383000.00, '2025-12', '2025-04-29 08:38:46'),
(8, 12, 500000.00, 1000.00, NULL, 114000.00, 1500.00, 1500.00, 117000.00, 30000.00, 9980.00, 37500.00, 1500.00, 1500.00, 80480.00, 383000.00, '2025-11', '2025-04-29 08:40:03'),
(9, 7, 200000.00, 0.00, 12000.00, 24000.00, 600.00, 600.00, 37200.00, 12000.00, 4000.00, 0.00, 600.00, 600.00, 17200.00, 162800.00, '2025-12', '2025-04-29 12:16:38'),
(10, 23, 500000.00, 0.00, 30000.00, 114000.00, 1500.00, 1500.00, 147000.00, 30000.00, 10000.00, 0.00, 1500.00, 1500.00, 43000.00, 353000.00, '2025-11', '2025-04-29 12:18:24'),
(11, 22, 500000.00, 0.00, 30000.00, 114000.00, 1500.00, 1500.00, 147000.00, 30000.00, 10000.00, 0.00, 1500.00, 1500.00, 43000.00, 353000.00, '2025-11', '2025-04-29 12:22:50'),
(12, 25, 500000.00, 0.00, 30000.00, 114000.00, 1500.00, 1500.00, 147000.00, 30000.00, 10000.00, 0.00, 1500.00, 1500.00, 43000.00, 353000.00, '2025-12', '2025-04-29 12:30:08'),
(13, 25, 300000.00, 10.00, 18000.00, 54000.00, 900.00, 900.00, 73800.00, 18000.00, 5999.80, 0.00, 900.00, 900.00, 25799.80, 226200.00, '2025-12', '2025-04-29 12:32:43'),
(14, 23, 500000.00, 0.00, 30000.00, 114000.00, 1500.00, 1500.00, 147000.00, 30000.00, 10000.00, 0.00, 1500.00, 1500.00, 43000.00, 353000.00, '2025-08', '2025-04-29 12:36:43'),
(15, 23, 500000.00, 0.00, 30000.00, 114000.00, 1500.00, 1500.00, 147000.00, 30000.00, 10000.00, 0.00, 1500.00, 1500.00, 43000.00, 353000.00, '2025-08', '2025-04-29 12:39:29'),
(16, 23, 500000.00, 0.00, 30000.00, 114000.00, 1500.00, 1500.00, 147000.00, 30000.00, 10000.00, 0.00, 1500.00, 1500.00, 43000.00, 353000.00, '2025-08', '2025-04-29 12:41:38'),
(17, 7, 500000.00, 1.00, 30000.00, 114000.00, 1500.00, 1500.00, 147000.00, 30000.00, 9999.98, 0.00, 1500.00, 1500.00, 42999.98, 353000.00, '2025-10', '2025-04-29 12:49:39'),
(18, 7, 500000.00, 0.00, 30000.00, 114000.00, 1500.00, 1500.00, 147000.00, 30000.00, 10000.00, 0.00, 1500.00, 1500.00, 43000.00, 353000.00, '2025-10', '2025-04-29 12:50:57'),
(19, 7, 500000.00, 0.00, 30000.00, 114000.00, 1500.00, 1500.00, 147000.00, 30000.00, 10000.00, 0.00, 1500.00, 1500.00, 43000.00, 353000.00, '2025-10', '2025-04-29 12:54:26'),
(20, 12, 300000.00, 0.00, 18000.00, 54000.00, 900.00, 0.00, 72900.00, 24000.00, 6000.00, 0.00, 900.00, 0.00, 30900.00, 227100.00, '2025-05', '2025-05-01 20:52:56');

-- --------------------------------------------------------

--
-- Table structure for table `payroll_allowance_types`
--

CREATE TABLE `payroll_allowance_types` (
  `id` int(11) NOT NULL,
  `name` varchar(50) NOT NULL,
  `is_taxable` tinyint(1) DEFAULT 1,
  `is_percentage` tinyint(1) DEFAULT 0,
  `default_amount` decimal(10,2) DEFAULT 0.00
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `payroll_deduction_types`
--

CREATE TABLE `payroll_deduction_types` (
  `id` int(11) NOT NULL,
  `name` varchar(50) NOT NULL,
  `is_pretax` tinyint(1) DEFAULT 0,
  `is_percentage` tinyint(1) DEFAULT 0,
  `default_amount` decimal(10,2) DEFAULT 0.00
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `payroll_items`
--

CREATE TABLE `payroll_items` (
  `id` int(11) NOT NULL,
  `payroll_id` int(11) NOT NULL,
  `employee_id` int(11) NOT NULL,
  `basic_salary` decimal(15,2) NOT NULL,
  `allowances` decimal(15,2) DEFAULT 0.00,
  `deductions` decimal(15,2) DEFAULT 0.00,
  `tax_amount` decimal(15,2) DEFAULT 0.00,
  `net_pay` decimal(15,2) NOT NULL,
  `status` enum('draft','approved','paid') DEFAULT 'draft',
  `payment_date` date DEFAULT NULL,
  `payment_reference` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `payroll_periods`
--

CREATE TABLE `payroll_periods` (
  `id` int(11) NOT NULL,
  `name` varchar(50) NOT NULL,
  `start_date` date NOT NULL,
  `end_date` date NOT NULL,
  `status` enum('draft','processing','completed') DEFAULT 'draft',
  `created_by` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `period_closing_summaries`
--

CREATE TABLE `period_closing_summaries` (
  `id` int(11) NOT NULL,
  `period_id` int(11) NOT NULL,
  `total_debits` decimal(15,2) NOT NULL,
  `total_credits` decimal(15,2) NOT NULL,
  `retained_earnings` decimal(15,2) NOT NULL,
  `closing_entries_description` text DEFAULT NULL,
  `created_by` int(11) NOT NULL,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `permissions`
--

CREATE TABLE `permissions` (
  `id` int(11) NOT NULL,
  `name` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `permissions`
--

INSERT INTO `permissions` (`id`, `name`) VALUES
(4, 'approve'),
(3, 'delete'),
(2, 'edit'),
(1, 'read'),
(5, 'reject');

-- --------------------------------------------------------

--
-- Table structure for table `projects`
--

CREATE TABLE `projects` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `budgeted_amount` decimal(15,2) NOT NULL,
  `revised_budget` decimal(15,2) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `projects`
--

INSERT INTO `projects` (`id`, `name`, `description`, `budgeted_amount`, `revised_budget`, `created_at`) VALUES
(1, 'Panatech Company ltd', 'asrsad', 1000000.00, 0.00, '2025-04-22 13:42:31'),
(2, 'Iduka Platform', 'Ecomerce Platform', 1000000.00, 0.00, '2025-04-22 13:44:41'),
(3, 'Test Project', 'Test Project to test formulars', 1000000.00, 1000000.00, '2025-04-22 14:30:51'),
(4, 'Test Project', 'test', 100000.00, 50000.00, '2025-04-23 07:27:53');

-- --------------------------------------------------------

--
-- Table structure for table `project_activities`
--

CREATE TABLE `project_activities` (
  `id` int(11) NOT NULL,
  `project_id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `budgeted_amount` decimal(15,2) NOT NULL,
  `actual_expense` decimal(15,2) NOT NULL DEFAULT 0.00
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `project_activities`
--

INSERT INTO `project_activities` (`id`, `project_id`, `name`, `budgeted_amount`, `actual_expense`) VALUES
(1, 2, 'Build Platform', 150000.00, 140000.00),
(2, 2, 'Domain Name', 12000.00, 15000.00),
(3, 1, 'Employee salary ', 1500000.00, 1500000.00),
(4, 3, 'Test Activity 1 ', 200000.00, 150000.00),
(5, 3, 'Test Activity 2', 300000.00, 250000.00),
(6, 4, 'Test Activity 1', 20000.00, 15000.00);

-- --------------------------------------------------------

--
-- Table structure for table `purchase_orders`
--

CREATE TABLE `purchase_orders` (
  `id` int(11) NOT NULL,
  `supplier_id` int(11) DEFAULT NULL,
  `order_date` date DEFAULT NULL,
  `created_by` varchar(255) DEFAULT NULL,
  `purpose` text DEFAULT NULL,
  `status` enum('draft','submitted','approved','rejected','received') DEFAULT 'draft',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `delivery_note` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `purchase_orders`
--

INSERT INTO `purchase_orders` (`id`, `supplier_id`, `order_date`, `created_by`, `purpose`, `status`, `created_at`, `delivery_note`) VALUES
(3, 1, '2025-04-28', '1', 'Testing', 'received', '2025-04-28 15:14:12', ''),
(4, 3, '2025-04-28', '10', 'Test', 'received', '2025-04-28 21:47:20', ''),
(7, 1, '2025-04-29', '10', 'Testing', 'received', '2025-04-29 00:09:02', ''),
(8, 1, '2025-04-29', '10', 'test', 'received', '2025-04-29 09:33:03', 'uploads/delivery_notes/1745925096_sale_summary _ CSPK.pdf'),
(9, 1, '2025-04-29', '10', 'Test', 'received', '2025-04-29 10:39:53', ''),
(10, 1, '2025-04-30', '10', 'sdc', 'received', '2025-04-30 14:07:24', ''),
(11, 1, '2025-04-30', '10', 'ads', 'received', '2025-04-30 14:12:44', ''),
(12, 1, '2025-04-30', '10', 'dxsd', 'received', '2025-04-30 14:14:26', ''),
(13, 3, '2025-04-30', '10', 'ds', 'received', '2025-04-30 14:15:30', ''),
(14, 4, '2025-05-06', '10', 'rr', 'approved', '2025-05-06 10:35:56', '');

-- --------------------------------------------------------

--
-- Table structure for table `purchase_order_items`
--

CREATE TABLE `purchase_order_items` (
  `id` int(11) NOT NULL,
  `purchase_order_id` int(11) DEFAULT NULL,
  `item_id` int(11) DEFAULT NULL,
  `quantity` decimal(10,2) DEFAULT NULL,
  `price` varchar(200) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `purchase_order_items`
--

INSERT INTO `purchase_order_items` (`id`, `purchase_order_id`, `item_id`, `quantity`, `price`) VALUES
(13, 3, 2, 15.00, ''),
(14, 3, 1, 8.00, ''),
(15, 4, 1, 5000.00, ''),
(19, 7, 2, 1000.00, ''),
(20, 8, 2, 5000.00, ''),
(21, 9, 2, 15.00, '5500'),
(22, 10, 2, 444.00, '2000'),
(23, 11, 2, 144.00, '200'),
(24, 12, 2, 500.00, '4000'),
(25, 13, 2, 44.00, '10000'),
(26, 14, 2, 4555.00, '120');

-- --------------------------------------------------------

--
-- Table structure for table `receipts`
--

CREATE TABLE `receipts` (
  `id` int(11) NOT NULL,
  `sale_id` int(11) NOT NULL,
  `receipt_number` varchar(50) NOT NULL,
  `printed_at` datetime DEFAULT NULL,
  `printed_by` int(11) DEFAULT NULL,
  `template` varchar(50) DEFAULT 'default'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `reconciliation_logs`
--

CREATE TABLE `reconciliation_logs` (
  `id` int(11) NOT NULL,
  `bank_account_id` int(11) NOT NULL,
  `reconciled_by` int(11) NOT NULL,
  `start_date` date NOT NULL,
  `end_date` date NOT NULL,
  `reconciled_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `reductions`
--

CREATE TABLE `reductions` (
  `id` int(11) NOT NULL,
  `employee_id` int(11) DEFAULT NULL,
  `tax` decimal(10,2) DEFAULT NULL,
  `pension` decimal(10,2) DEFAULT NULL,
  `contributions` decimal(10,2) DEFAULT NULL,
  `others` decimal(10,2) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `report_schedules`
--

CREATE TABLE `report_schedules` (
  `id` int(11) NOT NULL,
  `report_id` int(11) NOT NULL,
  `frequency` enum('daily','weekly','monthly','quarterly','yearly') NOT NULL,
  `recipients` text NOT NULL,
  `last_sent` datetime DEFAULT NULL,
  `next_send` datetime DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `requisitions`
--

CREATE TABLE `requisitions` (
  `id` int(11) NOT NULL,
  `request_date` date DEFAULT NULL,
  `requested_by` varchar(255) DEFAULT NULL,
  `purpose` text DEFAULT NULL,
  `status` enum('draft','submitted','approved','rejected') DEFAULT 'draft',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `requisitions`
--

INSERT INTO `requisitions` (`id`, `request_date`, `requested_by`, `purpose`, `status`, `created_at`) VALUES
(2, NULL, NULL, 'tet', 'draft', '2025-04-28 23:25:59'),
(3, NULL, NULL, 'For testing', 'approved', '2025-04-28 23:27:00');

-- --------------------------------------------------------

--
-- Table structure for table `requisition_approvals`
--

CREATE TABLE `requisition_approvals` (
  `id` int(11) NOT NULL,
  `requisition_id` int(11) NOT NULL,
  `approver_id` int(11) NOT NULL,
  `approval_level` int(11) NOT NULL,
  `status` enum('pending','approved','rejected') NOT NULL DEFAULT 'pending',
  `comments` text DEFAULT NULL,
  `action_date` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `requisition_fulfillments`
--

CREATE TABLE `requisition_fulfillments` (
  `id` int(11) NOT NULL,
  `requisition_item_id` int(11) NOT NULL,
  `fulfilled_by` int(11) NOT NULL,
  `fulfillment_date` datetime NOT NULL DEFAULT current_timestamp(),
  `quantity` decimal(10,2) NOT NULL,
  `from_location_id` int(11) DEFAULT NULL,
  `notes` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `requisition_items`
--

CREATE TABLE `requisition_items` (
  `id` int(11) NOT NULL,
  `requisition_id` int(11) DEFAULT NULL,
  `item_id` int(11) DEFAULT NULL,
  `quantity` decimal(10,2) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `requisition_items`
--

INSERT INTO `requisition_items` (`id`, `requisition_id`, `item_id`, `quantity`) VALUES
(3, 2, 2, 4.00),
(6, 3, 1, 552.00),
(7, 3, 2, 444.00);

-- --------------------------------------------------------

--
-- Table structure for table `roles`
--

CREATE TABLE `roles` (
  `id` int(11) NOT NULL,
  `role_name` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `roles`
--

INSERT INTO `roles` (`id`, `role_name`) VALUES
(3, 'accountant'),
(1, 'Admin'),
(8, 'budgeting'),
(7, 'donations'),
(2, 'hr'),
(4, 'inventory'),
(5, 'loan'),
(6, 'school');

-- --------------------------------------------------------

--
-- Table structure for table `role_permissions`
--

CREATE TABLE `role_permissions` (
  `id` int(11) NOT NULL,
  `role_id` int(11) NOT NULL,
  `permission_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `role_permissions`
--

INSERT INTO `role_permissions` (`id`, `role_id`, `permission_id`) VALUES
(4, 2, 1),
(3, 2, 2),
(2, 2, 3),
(1, 2, 4),
(5, 2, 5),
(9, 3, 1),
(8, 3, 2),
(7, 3, 3),
(6, 3, 4),
(10, 3, 5),
(14, 4, 1),
(13, 4, 2),
(12, 4, 3),
(11, 4, 4),
(15, 4, 5),
(19, 5, 1),
(18, 5, 2),
(17, 5, 3),
(16, 5, 4),
(20, 5, 5),
(24, 6, 1),
(23, 6, 2),
(22, 6, 3),
(21, 6, 4),
(25, 6, 5),
(29, 7, 1),
(28, 7, 2),
(27, 7, 3),
(26, 7, 4),
(30, 7, 5),
(34, 8, 1),
(33, 8, 2),
(32, 8, 3),
(31, 8, 4),
(35, 8, 5);

-- --------------------------------------------------------

--
-- Table structure for table `sales`
--

CREATE TABLE `sales` (
  `id` int(11) NOT NULL,
  `customer_id` int(11) DEFAULT NULL,
  `sale_date` datetime NOT NULL,
  `reference` varchar(50) DEFAULT NULL,
  `total_amount` decimal(10,2) NOT NULL,
  `tax_amount` decimal(10,2) DEFAULT 0.00,
  `discount_amount` decimal(10,2) DEFAULT 0.00,
  `payment_method` int(11) DEFAULT NULL,
  `payment_status` enum('pending','partial','paid') DEFAULT 'paid',
  `created_by` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `sale_items`
--

CREATE TABLE `sale_items` (
  `id` int(11) NOT NULL,
  `sale_id` int(11) NOT NULL,
  `item_id` int(11) NOT NULL,
  `quantity` decimal(10,2) NOT NULL,
  `unit_price` decimal(10,2) NOT NULL,
  `discount` decimal(5,2) DEFAULT 0.00,
  `tax_rate` decimal(5,2) DEFAULT 0.00
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `saved_reports`
--

CREATE TABLE `saved_reports` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `module` varchar(50) NOT NULL,
  `report_type` varchar(50) NOT NULL,
  `parameters` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL CHECK (json_valid(`parameters`)),
  `created_by` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `sponsors`
--

CREATE TABLE `sponsors` (
  `id` int(11) NOT NULL,
  `name` varchar(150) DEFAULT NULL,
  `email` varchar(150) DEFAULT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `address` varchar(150) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `sponsors`
--

INSERT INTO `sponsors` (`id`, `name`, `email`, `phone`, `address`, `created_at`) VALUES
(2, 'Pascal Twizerimana', 'protegene@mail.com', '0786074570', 'ss', '2025-04-14 21:53:25'),
(3, 'TEST', 'protegene@mail.com', '0786074570', 'Test', '2025-04-14 22:01:44'),
(4, 'pascalss', 'panatechrwandsa@gmail.com', '78607470', 'asdasc', '2025-04-21 21:22:52');

-- --------------------------------------------------------

--
-- Table structure for table `stock_items`
--

CREATE TABLE `stock_items` (
  `id` int(11) NOT NULL,
  `item_name` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `unit` varchar(50) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `stock_items`
--

INSERT INTO `stock_items` (`id`, `item_name`, `description`, `unit`, `created_at`) VALUES
(1, 'Shoes', 'Test', 'Item', '2025-04-28 12:33:10'),
(2, 'Paper', 'Paper', 'Package', '2025-04-28 13:34:15');

-- --------------------------------------------------------

--
-- Table structure for table `stock_movements`
--

CREATE TABLE `stock_movements` (
  `id` int(11) NOT NULL,
  `item_id` int(11) DEFAULT NULL,
  `date` date DEFAULT NULL,
  `quantity_in` decimal(10,2) DEFAULT 0.00,
  `quantity_out` decimal(10,2) DEFAULT 0.00,
  `reference_type` enum('requisition','purchase_order','manual_adjustment') DEFAULT NULL,
  `reference_id` int(11) DEFAULT NULL,
  `remarks` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `stock_movements`
--

INSERT INTO `stock_movements` (`id`, `item_id`, `date`, `quantity_in`, `quantity_out`, `reference_type`, `reference_id`, `remarks`, `created_at`) VALUES
(1, 1, '2025-04-28', 0.00, 552.00, 'requisition', 3, 'Requisition Approved', '2025-04-28 23:27:49'),
(2, 2, '2025-04-28', 0.00, 444.00, 'requisition', 3, 'Requisition Approved', '2025-04-28 23:27:50'),
(3, 1, '2025-04-29', 8.00, 0.00, '', 3, 'PO Receiving', '2025-04-29 00:04:37'),
(4, 2, '2025-04-29', 15.00, 0.00, '', 3, 'PO Receiving', '2025-04-29 00:04:37'),
(5, 1, '2025-04-29', 8.00, 0.00, '', 3, 'PO Receiving', '2025-04-29 00:06:08'),
(6, 2, '2025-04-29', 15.00, 0.00, '', 3, 'PO Receiving', '2025-04-29 00:06:08'),
(7, 1, '2025-04-29', 8.00, 0.00, '', 3, 'PO Receiving', '2025-04-29 00:07:57'),
(8, 2, '2025-04-29', 15.00, 0.00, '', 3, 'PO Receiving', '2025-04-29 00:07:57'),
(9, 1, '2025-04-29', 5000.00, 0.00, '', 4, 'PO Receiving', '2025-04-29 00:08:18'),
(10, 2, '2025-04-29', 1000.00, 0.00, '', 7, 'PO Receiving', '2025-04-29 00:10:19'),
(11, 2, '2025-04-29', 5000.00, 0.00, '', 8, 'PO Receiving', '2025-04-29 11:11:36'),
(12, 2, '2025-04-30', 15.00, 0.00, '', 9, 'PO Receiving', '2025-04-30 13:59:19'),
(13, 2, '2025-04-30', 15.00, 0.00, '', 9, 'PO Receiving', '2025-04-30 14:02:19'),
(14, 2, '2025-04-30', 15.00, 0.00, '', 9, 'PO Receiving', '2025-04-30 14:04:32'),
(15, 2, '2025-04-30', 15.00, 0.00, '', 9, 'PO Receiving', '2025-04-30 14:06:10'),
(16, 2, '2025-04-30', 444.00, 0.00, '', 10, 'PO Receiving', '2025-04-30 14:07:45'),
(17, 2, '2025-04-30', 444.00, 0.00, '', 10, 'PO Receiving', '2025-04-30 14:10:01'),
(18, 2, '2025-04-30', 444.00, 0.00, '', 10, 'PO Receiving', '2025-04-30 14:10:33'),
(19, 2, '2025-04-30', 444.00, 0.00, '', 10, 'PO Receiving', '2025-04-30 14:11:15'),
(20, 2, '2025-04-30', 144.00, 0.00, '', 11, 'PO Receiving', '2025-04-30 14:13:05'),
(21, 2, '2025-04-30', 500.00, 0.00, '', 12, 'PO Receiving', '2025-04-30 14:14:44'),
(22, 2, '2025-04-30', 44.00, 0.00, '', 13, 'PO Receiving', '2025-04-30 14:15:56'),
(23, 2, '2025-04-30', 44.00, 0.00, '', 13, 'PO Receiving', '2025-04-30 14:16:29'),
(24, 2, '2025-04-30', 44.00, 0.00, '', 13, 'PO Receiving', '2025-04-30 14:17:05'),
(25, 2, '2025-04-30', 44.00, 0.00, '', 13, 'PO Receiving', '2025-04-30 14:17:59');

-- --------------------------------------------------------

--
-- Table structure for table `students`
--

CREATE TABLE `students` (
  `id` int(11) NOT NULL,
  `first_name` varchar(100) DEFAULT NULL,
  `last_name` varchar(100) DEFAULT NULL,
  `gender` enum('Male','Female','Other') DEFAULT NULL,
  `dob` date DEFAULT NULL,
  `email` varchar(150) DEFAULT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `address` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `school_name` varchar(200) NOT NULL,
  `fees_payment` varchar(200) NOT NULL,
  `bank_name` varchar(200) NOT NULL,
  `bank_account` varchar(200) NOT NULL,
  `father_name` varchar(200) NOT NULL,
  `mother_name` varchar(20) DEFAULT NULL,
  `guardian_name` varchar(200) NOT NULL,
  `is_active` int(11) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `students`
--

INSERT INTO `students` (`id`, `first_name`, `last_name`, `gender`, `dob`, `email`, `phone`, `address`, `created_at`, `school_name`, `fees_payment`, `bank_name`, `bank_account`, `father_name`, `mother_name`, `guardian_name`, `is_active`) VALUES
(1, 'Pascal ', 'Twizerimana', 'Male', '2025-04-25', NULL, '0786074570', 'Kigali Rwanda', '2025-04-25 06:40:35', 'Muhabura ', '50000', 'BANK OF KIGALI', '78852220000144', 'FATHER NAME', 'MOTHER NAME', 'NONE', 1),
(2, 'Kalisa', 'Paul', 'Male', '2025-04-26', NULL, '0786074571', 'TEST', '2025-04-25 07:11:56', 'Kigali', '15000', 'AB BANK', '782354555220', 'FATHER NAME', 'MOTHER NAME', 'NONE', 1);

-- --------------------------------------------------------

--
-- Table structure for table `student_documents`
--

CREATE TABLE `student_documents` (
  `id` int(11) NOT NULL,
  `student_id` int(11) DEFAULT NULL,
  `document_name` varchar(255) DEFAULT NULL,
  `filetype` varchar(255) DEFAULT NULL,
  `uploaded_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `student_documents`
--

INSERT INTO `student_documents` (`id`, `student_id`, `document_name`, `filetype`, `uploaded_at`) VALUES
(1, 23, '1745316517_index.php', 'application/octet-stream', '2025-04-22 10:08:37'),
(2, 24, '1745316574_resto (22).sql', 'application/octet-stream', '2025-04-22 10:09:34'),
(4, 26, '1745322392_resto (24).sql', 'application/octet-stream', '2025-04-22 11:46:32'),
(5, 27, '1745322449_New Order _ CSPKh.pdf', 'application/pdf', '2025-04-22 11:47:29'),
(6, 28, '1745322521_sample-1.pdf', 'application/pdf', '2025-04-22 11:48:41');

-- --------------------------------------------------------

--
-- Table structure for table `student_payments`
--

CREATE TABLE `student_payments` (
  `id` int(11) NOT NULL,
  `student_id` int(11) NOT NULL,
  `term_id` int(11) NOT NULL,
  `payment_date` varchar(200) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `student_payments`
--

INSERT INTO `student_payments` (`id`, `student_id`, `term_id`, `payment_date`) VALUES
(1, 1, 2, '2025-04-25 15:08:43'),
(2, 1, 2, '2025-04-25 16:39:08'),
(3, 2, 2, '2025-04-25 16:39:09'),
(10, 1, 2, '2025-04-27 13:45:17'),
(11, 2, 2, '2025-04-27 13:45:17'),
(12, 1, 2, '2025-04-29 01:13:30');

-- --------------------------------------------------------

--
-- Table structure for table `student_sponsor`
--

CREATE TABLE `student_sponsor` (
  `id` int(11) NOT NULL,
  `student_id` int(11) DEFAULT NULL,
  `sponsor_id` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
<br />
<b>Fatal error</b>:  Maximum execution time of 300 seconds exceeded in <b>C:\xampp\phpMyAdmin\libraries\classes\Dbal\DbiMysqli.php</b> on line <b>209</b><br />
