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
if ( $_SERVER['REQUEST_METHOD'] != 'GET' )
{
	header('Location: admin_main.php');
	exit();
}


$id = $_GET['item_id'];


// Insert ticket to database
$vendor = hesk_delVendor($id);

hesk_process_messages($action,'admin_ticket.php?track='.$_GET['track'].'&Refresh='.rand(10000,99999),'SUCCESS');
//header('Location: admin_ticket.php?track='.$_POST['ticket_trackid']);
?>