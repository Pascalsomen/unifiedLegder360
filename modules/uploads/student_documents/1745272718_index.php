<?php
    require_once ("../inc/config.php");
if($logging_ID == '' or $USER_ID == ''){
    echo"<script>window.location.replace('../index')</script>";
}

$page='Manage_CheckIn_guest.php';
$view = (isset($_GET['resto']) && $_GET['resto'] != '') ? $_GET['resto'] : '';

switch ($view) {

	case 'home' :
        $title="Dashboard";
		$page='Manage_CheckIn_guest.php';
		break;

	case 'view_reservation_details' :
        $title="View Reservation";
		$page='reserve_details.php';
		break;

		case 'edit' :
        $title="edit";
		$page='edit.php';
		break;


		case 'detailedsmumary' :
        $title="detailedsmumary";
		$page='details_summary.php';
		break;



	     case 'collections' :
        $title="collections";
		$page='collections.php';
		break;

			case 'sale_summary' :
        $title="sale_summary";
		$page='sale_summary.php';
		break;


			case 'days' :
        $title="days";
		$page='days.php';
		break;

	case 'mngeResrv' :
        $title="Manage Reservation";
		$page='Oug_reservAll.php';
		break;
		case 'request' :
			$title="requests";
			$page='requests.php';
			break;

		case 'requests' :
			$title="requests";
			$page='requests.php';
			break;

			case 'transfer' :
        $title="Transfer Reservation";
		$page='transfer.php';
		break;


	case 'chk_in_excel' :
        $title="Check In";
		$page='chkIn_excel.php';
		break;

	case 'chk_out_excel' :
        $title="Check Out";
		$page='chkOut_excel.php';
		break;

	case 'report' :
		$title="Report";
		$page='sales_report.php';
		break;

		case 'reports' :
			$title="Reports";
			$page='sale_by_category.php';
			break;

			    case 'requests' :
				$title="Reports";
				$page='requests.php';
				break;


	case 'reportb' :
		$title="Report";
		$page='sales_report.php';
		break;

	case 'servedReport' :
		$title="Report";
		$page='report_details.php';
		break;

	case 'generalreport' :
		$title="General Report";
		$page='general_report.php';
		break;

	 case 'OurGste' :
        $title="Guest List";
		$page='Manage_CheckIn_guest.php';
		break;

	case 'gstDet' :
        $title="Order Datails";
		$page='View_OrdderDet.php';
		break;

	case 'OurGste_view' :
        $title="Guest List";
		$page='view_reserv_details.php';
		break;

	case 'gstInvce' :
        $title="Guest Invoice";
    	$page='GetGste_Invoice.php';
    	break;

	case 'ebm' :
        $title="EBM Receipts";
    	$page='ebm.php';
    	break;

	default :
	    $title="Home";
		$page ='Manage_CheckIn_guest.php';
     }

    require_once("../inc/ontouch.php");
?>

