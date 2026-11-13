<?php
define('IN_SCRIPT',1);
define('HESK_PATH','../');

// Get all the required files and functions
require(HESK_PATH . 'hesk_settings.inc.php');
require(HESK_PATH . 'inc/common.inc.php');
require(HESK_PATH . 'inc/admin_functions.inc.php');
hesk_load_database_functions();
require(HESK_PATH . 'inc/email_functions.inc.php');
require(HESK_PATH . 'inc/posting_functions.inc.php');

hesk_session_start();
hesk_dbConnect();
hesk_isLoggedIn();

// We only allow POST requests from the HESK form to this file
if ( $_SERVER['REQUEST_METHOD'] != 'POST' )
{
	header('Location: admin_main.php');
	exit();
}

$tmpvar['ticket_track_id'] = $_POST['ticket_trackid'];
$tmpvar['item_id'] = $_POST['item_id'];
$tmpvar['vendor_name'] = $_POST['vendor_name'];
$tmpvar['message'] = $_POST['vendor_message'];
$tmpvar['quantity'] = $_POST['vendor_quantity'];
$tmpvar['unit'] = $_POST['vendor_unit'];
$tmpvar['rate'] = $_POST['vendor_rate'];
$tmpvar['amount'] = $_POST['vendor_amount'];

// Insert ticket to database
$vendor = hesk_newVendor($tmpvar);
hesk_process_messages($action,'admin_ticket.php?track='.$_POST['ticket_trackid'].'&Refresh='.rand(10000,99999),'SUCCESS');
//header('Location: admin_ticket.php?track='.$_POST['ticket_trackid']);
?>