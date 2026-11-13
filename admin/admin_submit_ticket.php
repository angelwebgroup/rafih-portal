<?php

/**
 *
 * This file is part of HESK - PHP Help Desk Software.
 *
 * (c) Copyright Klemen Stirn. All rights reserved.
 * https://www.hesk.com
 *
 * For the full copyright and license agreement information visit
 * https://www.hesk.com/eula.php
 *
 */



define('IN_SCRIPT', 1);
define('HESK_PATH', '../');

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
$hesk_error_buffer = array();

// We only allow POST requests from the HESK form to this file
if ($_SERVER['REQUEST_METHOD'] != 'POST') {
	header('Location: admin_main.php');
	exit();
}

// Check for POST requests larger than what the server can handle
if (empty($_POST) && !empty($_SERVER['CONTENT_LENGTH'])) {
	hesk_error($hesklang['maxpost']);
}

//print_r($_REQUEST);
//echo hesk_POST('change_category') == 0;
//die;
// Changing category? Remember data and redirect to category select page
if (hesk_POST('change_category') == 1) {

	$_SESSION['as_name']     = hesk_POST('name');
	$_SESSION['as_dealership']  = hesk_POST('dealership');
	$_SESSION['as_email']    = hesk_POST('email');
	$_SESSION['as_priority'] = hesk_POST('priority');
	$_SESSION['as_email']    = hesk_POST('email');
	$_SESSION['as_first_tag'] = hesk_POST('first_tag');
	$_SESSION['as_second_tag']   = hesk_POST('second_tag');
	//$_SESSION['as_subject']  = hesk_POST('subject');
	$_SESSION['as_message']  = hesk_POST('message');
	$_SESSION['as_due_date'] = hesk_POST('due_date');
	$_SESSION['as_owner']    = hesk_POST('owner');
	$_SESSION['as_notify']   = hesk_POST('notify');
	$_SESSION['as_show']     = hesk_POST('show');
	$_SESSION['as_language'] = hesk_POST('as_language');

	foreach ($hesk_settings['custom_fields'] as $k => $v) {
		if ($v['name'] == "Predictive index completed") {
			continue;
		}
		if ($v['use'] && !in_array($v['type'], array('date', 'email'))) {
			$_SESSION["as_$k"] = ($v['type'] == 'checkbox') ? hesk_POST_array($k) : hesk_POST($k);
		}
	}
    if (isset($_POST['custom43'])) {
        $_SESSION['as_custom43'] = hesk_POST('custom43');
    }
	//echo "<pre>";
	//print_r($_REQUEST);

	header('Location: new_ticket.php');
	exit();
}




if (hesk_POST('category') == 7) {

	$tmpvar['name'] = $_SESSION['name'];
	$tmpvar['email'] = $_SESSION['email'];
} 
else {



	$tmpvar['name']	    = hesk_input(hesk_POST('name')) or $hesk_error_buffer['name'] = $hesklang['enter_your_name'];
	//echo hesk_select( hesk_POST('items_options') );exit;
	if (hesk_POST('category') == 2) {
		$tmpvar['items_options']	    = hesk_select(hesk_POST('items_options')) or $hesk_error_buffer['items_options'] = "Select request type";
	}
	//$tmpvar['dealership']	    = hesk_input( hesk_POST('dealership') ) or $hesk_error_buffer['dealership']=$hesklang['enter_your_dealership'];

	$email_available = true;

	if ($hesk_settings['require_email']) {
		$tmpvar['email'] = hesk_validateEmail(hesk_POST('email'), 'ERR', 0) or $hesk_error_buffer['email'] = $hesklang['enter_valid_email'];
	} else {
		$tmpvar['email'] = hesk_validateEmail(hesk_POST('email'), 'ERR', 0);

		// Not required, but must be valid if it is entered
		if ($tmpvar['email'] == '') {
			$email_available = false;

			if (strlen(hesk_POST('email'))) {
				$hesk_error_buffer['email'] = $hesklang['not_valid_email'];
			}
		}
	}
}


$tmpvar['category'] = intval(hesk_POST('category')) or $hesk_error_buffer['category'] = $hesklang['sel_app_cat'];
$tmpvar['priority'] = hesk_POST('priority');
$tmpvar['priority'] = strlen($tmpvar['priority']) ? intval($tmpvar['priority']) : -1;

if ($tmpvar['priority'] < 0 || $tmpvar['priority'] > 3) {
	// If we are showing "Click to select" priority needs to be selected
	if ($hesk_settings['select_pri']) {
		$tmpvar['priority'] = -1;
		$hesk_error_buffer['priority'] = $hesklang['select_priority'];
	} else {
		$tmpvar['priority'] = 3;
	}
}

$tmpvar['status'] = intval(hesk_POST('status', 0));
if (!isset($hesk_settings['statuses'][$tmpvar['status']])) {
	$tmpvar['status'] = 0;
}
/*
$tmpvar['subject'] = hesk_input( hesk_POST('subject') );
if ($hesk_settings['require_subject'] == 1 && $tmpvar['subject'] == '')
{
    $hesk_error_buffer['subject'] = $hesklang['enter_ticket_subject'];
}
*/
$tmpvar['message']  = hesk_input(hesk_POST('message'));
if ($hesk_settings['require_message'] == 1 && $tmpvar['message'] == '') {
	$hesk_error_buffer['message'] = $hesklang['enter_message'];
}




// Is category a valid choice?
if ($tmpvar['category'] && !in_array(intval(hesk_POST('category')), array(8, 9, 10))) {
	if (!hesk_checkPermission('can_submit_any_cat', 0) && !hesk_okCategory($tmpvar['category'], 0)) {
		hesk_process_messages($hesklang['noauth_submit'], 'new_ticket.php');
	}

	hesk_verifyCategory(1);

	// Is auto-assign of tickets disabled in this category?
	if (empty($hesk_settings['category_data'][$tmpvar['category']]['autoassign'])) {
		$hesk_settings['autoassign'] = false;
	}
}

if (hesk_POST('category') == 3) {
	//echo "<pre>";
	//print_r($_POST);
	//print_r($_FILES);
	//exit;
	if (hesk_POST('custom8') == 'Yes' && $_FILES['file_ReplacementEquipment']['error'] == 4) {
		$hesk_error_buffer['custom8'] = "Please select file for Replacement Equipment";
	}

	if (hesk_POST('custom9') == 'Yes' && $_FILES['file_RevenueGenerating']['error'] == 4) {
		$hesk_error_buffer['custom9'] = "Please select file for Revenue Generating";
	}

	if (hesk_POST('custom10') == 'Yes' && $_FILES['file_Ifrecommendedforleaseratherthanpurchase']['error'] == 4) {
		$hesk_error_buffer['custom10'] = "Please select file If recommended for lease rather than purchase";
	}
	//exit;
}

if (hesk_POST('category') == 4) {

	if (count($_POST['card_name'])) {

		$cv = $_POST['card_name'];
		foreach ($cv as $fv) {
			if ($fv == "") {
				echo $hesk_error_buffer['card_name'] = $hesklang['fill_all'] . "  : " . $hesklang['card_name'];
				break;
			}
		}
	}

	if (count($_POST['card_title'])) {

		$cv = $_POST['card_title'];
		foreach ($cv as $fv) {
			if ($fv == "") {
				echo $hesk_error_buffer['card_title'] = $hesklang['fill_all'] . "  : " . $hesklang['card_title'];
				break;
			}
		}
	}


	if (count($_POST['lastname'])) {

		$cv = $_POST['lastname'];
		foreach ($cv as $fv) {
			if ($fv == "") {
				echo $hesk_error_buffer['lastname'] = $hesklang['fill_all'] . "  : " . $hesklang['lastname'];
				break;
			}
		}
	}


	if (count($_POST['business_phone'])) {

		$cv = $_POST['business_phone'];
		foreach ($cv as $fv) {
			if ($fv == "") {
				echo $hesk_error_buffer['business_phone'] = $hesklang['fill_all'] . " : " . $hesklang['business_phone'];
				break;
			}
		}
	}

	if (count($_POST['card_email'])) {

		$cv = $_POST['card_email'];
		foreach ($cv as $fv) {
			if ($fv == "") {
				echo $hesk_error_buffer['card_email'] = $hesklang['fill_all'] . " : " . $hesklang['card_email'];
				break;
			}
		}
	}


	if (count($_POST['extension'])) {

		$cv = $_POST['extension'];
		foreach ($cv as $fv) {
			if ($fv == "") {
				echo $hesk_error_buffer['extension'] = $hesklang['fill_all'] . " : " . $hesklang['extension'];
				break;
			}
		}
	}



	if (count($_POST['card_cell'])) {

		$cv = $_POST['card_cell'];
		foreach ($cv as $fv) {
			if ($fv == "") {
				echo $hesk_error_buffer['card_cell'] = $hesklang['fill_all'] . " : " . $hesklang['card_cell'];
				break;
			}
		}
	}


	//exit;
}

if (in_array(intval(hesk_POST('category')), array(9, 10))) {
	if (empty($_POST['credit_company_name'])) {
		$hesk_error_buffer['credit_company_name'] = "Please enter Name of Company";
	}
	if (empty($_POST['credit_dealerships']) || !is_array($_POST['credit_dealerships']) || count($_POST['credit_dealerships']) == 0) {
		$hesk_error_buffer['credit_dealerships'] = "Please select at least one Dealership";
	}
	if (empty($_POST['credit_street_address'])) {
		$hesk_error_buffer['credit_street_address'] = "Please enter Street Address";
	}
	if (empty($_POST['credit_postal_zip'])) {
		$hesk_error_buffer['credit_postal_zip'] = (hesk_POST('category') == 9) ? "Please enter Zip Code" : "Please enter Postal Code";
	}
	if (empty($_POST['credit_phone'])) {
		$hesk_error_buffer['credit_phone'] = "Please enter Phone #";
	}
	if (empty($_POST['credit_statement_email'])) {
		$hesk_error_buffer['credit_statement_email'] = "Please enter Statement Email";
	}
	if (empty($_POST['credit_owner_name'])) {
		$hesk_error_buffer['credit_owner_name'] = "Please enter Owner Name";
	}
	if (empty($_POST['credit_applicant_name'])) {
		$hesk_error_buffer['credit_applicant_name'] = "Please enter Name of Applicant";
	}
	if (empty($_POST['credit_declaration_agree'])) {
		$hesk_error_buffer['credit_declaration_agree'] = "Please agree to the credit declaration terms";
	}
}



// Custom fields
foreach ($hesk_settings['custom_fields'] as $k => $v) {

	if ($v['name'] == "Predictive index completed") {
		continue;
	}
	if ($v['use'] && hesk_is_custom_field_in_category($k, $tmpvar['category'])) {

		if ($v['type'] == 'checkbox') {
			$tmpvar[$k] = '';

			if (isset($_POST[$k]) && is_array($_POST[$k])) {
				foreach ($_POST[$k] as $myCB) {
					$tmpvar[$k] .= (is_array($myCB) ? '' : hesk_input($myCB)) . '<br />';;
				}
				$tmpvar[$k] = substr($tmpvar[$k], 0, -6);
			} else {
				if ($v['req'] == 2) {
					$hesk_error_buffer[$k] = $hesklang['fill_all'] . ': ' . $v['name'];
				}
				$_POST[$k] = '';
			}
		} elseif ($v['type'] == 'date') {
			$tmpvar[$k] = hesk_POST($k);
			$_SESSION["as_$k"] = '';

			if ($date = hesk_datepicker_get_date($tmpvar[$k], false, 'UTC')) {
				$_SESSION["as_$k"] = $tmpvar[$k];

				$date->setTime(0, 0);
				$dmin = strlen($v['value']['dmin']) ? new DateTime($v['value']['dmin'] . ' t00:00:00 UTC') : false;
				$dmax = strlen($v['value']['dmax']) ? new DateTime($v['value']['dmax'] . ' t00:00:00 UTC') : false;

				if ($dmin && $dmin->format('Y-m-d') > $date->format('Y-m-d')) {
					$hesk_error_buffer[$k] = sprintf($hesklang['d_emin'], $v['name'], hesk_translate_date_string($dmin->format($hesk_settings['format_datepicker_php'])));
				} elseif ($dmax && $dmax->format('Y-m-d') < $date->format('Y-m-d')) {
					$hesk_error_buffer[$k] = sprintf($hesklang['d_emax'], $v['name'], hesk_translate_date_string($dmax->format($hesk_settings['format_datepicker_php'])));
				} else {
					$tmpvar[$k] = $date->getTimestamp();
				}
			} else {
				$tmpvar[$k] = '';

				if ($v['req'] == 2) {
					$hesk_error_buffer[$k] = $hesklang['fill_all'] . ': ' . $v['name'];
				}
			}
		} elseif ($v['type'] == 'email') {
			$tmp = $hesk_settings['multi_eml'];
			$hesk_settings['multi_eml'] = $v['value']['multiple'];
			$tmpvar[$k] = hesk_validateEmail(hesk_POST($k), 'ERR', 0);
			$hesk_settings['multi_eml'] = $tmp;

			if ($tmpvar[$k] != '') {
				$_SESSION["as_$k"] = hesk_input($tmpvar[$k]);
			} else {
				$_SESSION["as_$k"] = '';

				if ($v['req'] == 2) {
					$hesk_error_buffer[$k] = $v['value']['multiple'] ? sprintf($hesklang['cf_noem'], $v['name']) : sprintf($hesklang['cf_noe'], $v['name']);
				}
			}
		} elseif ($v['req'] == 2) {
			$tmpvar[$k] = hesk_makeURL(nl2br(hesk_input(hesk_POST($k))));
			if ($tmpvar[$k] == '') {
				$hesk_error_buffer[$k] = $hesklang['fill_all'] . ': ' . $v['name'];
			}
		} else {
			if ($tmpvar[$k] == '') {
				$hesk_error_buffer[$k] = $hesklang['fill_all'] . ': ' . $v['name'];
			} else {
			}
		}
	} else {
		$tmpvar[$k] = '';
	}
}

if ($tmpvar['category'] == 6 && isset($_POST['custom43'])) {
    $tmpvar['custom43'] = hesk_makeURL(nl2br(hesk_input(hesk_POST('custom43'))));
}


if (hesk_POST('category') == 7) {
	$tmpvar['custom1'] = $_SESSION['dealership'];
	$tmpvar['custom2'] = $_SESSION['department'];
}

if (in_array(intval($tmpvar['category']), array(9, 10))) {
	$dealerships_arr = isset($_POST['credit_dealerships']) && is_array($_POST['credit_dealerships']) ? $_POST['credit_dealerships'] : array();
	$tmpvar['custom1'] = implode(', ', array_map('trim', $dealerships_arr));
}

if ($tmpvar['category'] == 11 || (isset($hesk_settings['categories'][$tmpvar['category']]['name']) && (stripos($hesk_settings['categories'][$tmpvar['category']]['name'], 'Vehicle Order') !== false || stripos($hesk_settings['categories'][$tmpvar['category']]['name'], 'Order Intake') !== false))) {
	if (!empty($_POST['voi_dealership'])) {
		$tmpvar['custom1'] = hesk_input($_POST['voi_dealership']);
	}
}
//print_r($tmpvar);exit;

// If use doesn't have permission to set due dates, try using the category default due date
if (hesk_checkPermission('can_due_date', 0)) {
	$tmpvar['due_date'] = hesk_input(hesk_POST('due_date'));
	if ($tmpvar['due_date'] != '') {
		$date = hesk_datepicker_get_date($tmpvar['due_date']);
		if ($date === false) {
			$hesk_error_buffer['due_date'] = $hesklang['invalid_due_date'];
		}
	}
} else {
	$tmpvar['due_date'] = '';
	if (($default_due_date_info = hesk_getCategoryDueDateInfo($tmpvar['category'])) !== null) {
		$due_date = new DateTime('today midnight');
		$due_date->add(DateInterval::createFromDateString("+{$default_due_date_info['amount']} {$default_due_date_info['unit']}s"));
		$tmpvar['due_date'] = hesk_datepicker_format_date($due_date->getTimestamp());

		// Don't set a due date if any unexpected errors
		if ($tmpvar['due_date'] === false) {
			$tmpvar['due_date'] = '';
		}
	}
}


// Generate tracking ID
$tmpvar['trackid'] = hesk_createID();

// Log who submitted ticket
$tmpvar['history'] = sprintf($hesklang['thist7'], hesk_date(), addslashes($_SESSION['name']) . ' (' . $_SESSION['user'] . ')');
$tmpvar['openedby'] = $_SESSION['id'];

// Was the ticket submitted as "Resolved"?
if ($tmpvar['status'] == 3) {
	// Check permission
	if (!hesk_checkPermission('can_resolve', 0)) {
		$hesk_error_buffer['status'] = $hesklang['noauth_resolve'];
	}

	$tmpvar['history'] .= sprintf($hesklang['thist3'], hesk_date(), addslashes($_SESSION['name']) . ' (' . $_SESSION['user'] . ')');

	if ($hesk_settings['custopen'] != 1) {
		$tmpvar['locked'] = 1;
	}

	// Log who marked the ticket resolved
	$tmpvar['closedat'] = 1;
	$tmpvar['closedby'] = intval($_SESSION['id']);
} else if ($tmpvar['status'] != 0) {
	// Status set to something different than "New" or "Resolved", let's log it
	$status_name = hesk_get_status_name($tmpvar['status']);
	$tmpvar['history'] .= sprintf($hesklang['thist9'], hesk_date(), addslashes($status_name), addslashes($_SESSION['name']) . ' (' . $_SESSION['user'] . ')');
}

// Owner
$tmpvar['owner'] = 0;
if (hesk_checkPermission('can_assign_others', 0)) {
	$tmpvar['owner'] = intval(hesk_POST('owner'));

	// If ID is -1 the ticket will be unassigned
	if ($tmpvar['owner'] == -1) {
		$tmpvar['owner'] = 0;
	}
	// Automatically assign owner?
	elseif ($tmpvar['owner'] == -2 && $hesk_settings['autoassign'] == 1) {
		$autoassign_owner = hesk_autoAssignTicket($tmpvar['category']);
		if ($autoassign_owner) {
			$tmpvar['owner']    = intval($autoassign_owner['id']);
			$tmpvar['history'] .= sprintf($hesklang['thist10'], hesk_date(), addslashes($autoassign_owner['name']) . ' (' . $autoassign_owner['user'] . ')');
		} else {
			$tmpvar['owner'] = 0;
		}
	}
	// Check for invalid owner values
	elseif ($tmpvar['owner'] < 1) {
		$tmpvar['owner'] = 0;
	} else {
		// Has the new owner access to the selected category?
		$res = hesk_dbQuery("SELECT `name`,`user`,`isadmin`,`categories` FROM `" . hesk_dbEscape($hesk_settings['db_pfix']) . "users` WHERE `id`='{$tmpvar['owner']}' LIMIT 1");
		if (hesk_dbNumRows($res) == 1) {
			$row = hesk_dbFetchAssoc($res);
			if (!$row['isadmin']) {
				$row['categories'] = explode(',', $row['categories']);
				if (!in_array($tmpvar['category'], $row['categories'])) {
					$_SESSION['isnotice'][] = 'category';
					$hesk_error_buffer['owner'] = $hesklang['onasc'];
				}
			}
			$tmpvar['history'] .= sprintf($hesklang['thist2'], hesk_date(), addslashes($row['name']) . ' (' . $row['user'] . ')', addslashes($_SESSION['name']) . ' (' . $_SESSION['user'] . ')');
		} else {
			$_SESSION['isnotice'][] = 'category';
			$hesk_error_buffer['owner'] = $hesklang['onasc'];
		}
	}
} elseif (hesk_checkPermission('can_assign_self', 0) && hesk_okCategory($tmpvar['category'], 0) && !empty($_POST['assing_to_self'])) {
	$tmpvar['owner'] = intval($_SESSION['id']);
}

// FORCE AUTO-ASSIGN: If owner is still 0 (Unassigned) and auto-assign is globally enabled, run auto-assign.
// This ensures that even staff members who don't have 'can_assign_others' permission will still trigger
// auto-assignment when they create new tickets.
// (Will not trigger for HR or Interview because those have auto-assign disabled natively in HESK)
if ($tmpvar['owner'] == 0 && $hesk_settings['autoassign'] == 1) {
    $autoassign_owner = hesk_autoAssignTicket($tmpvar['category']);
    error_log("Triggered force autoassign. Category: {$tmpvar['category']}. Result: " . print_r($autoassign_owner, true));
    if ($autoassign_owner) {
        $tmpvar['owner']    = intval($autoassign_owner['id']);
        $tmpvar['history'] .= sprintf($hesklang['thist10'], hesk_date(), addslashes($autoassign_owner['name']) . ' (' . $autoassign_owner['user'] . ')');
    }
}

// Notify customer of the ticket?
//$notify = ! empty($_POST['notify']) ? 1 : 0;

if ($_SESSION['notify_when_request_by_me'] == 1) {
	$notify_by_me = 1;
} else {
	$notify_by_me = 0;
}

if ($_SESSION['notify_category_users_when_request_created'] == 1) {
	$notify_category_users = 1;
} else {
	$notify_category_users = 0;
}
//echo $notify_category_users;exit;
// Show ticket after submission?
$show = !empty($_POST['show']) ? 1 : 0;

// Is the ticket language different than current language?
if ($hesk_settings['can_sel_lang']) {
	$new_lang = hesk_POST('as_language');
	if (isset($hesk_settings['languages'][$new_lang])) {
		$hesklang['LANGUAGE'] = $new_lang;
	}
}

// Attachments
$use_legacy_attachments = hesk_POST('use-legacy-attachments', 0);
if ($hesk_settings['attachments']['use']) {
	require_once(HESK_PATH . 'inc/attachments.inc.php');

	$attachments = array();
	$trackingID  = $tmpvar['trackid'];

	if ($use_legacy_attachments) {
		for ($i = 1; $i <= $hesk_settings['attachments']['max_number']; $i++) {
			$att = hesk_uploadFile($i);
			if ($att !== false && !empty($att)) {
				$attachments[$i] = $att;
			}
		}
	} else {
		// The user used the new drag-and-drop system.
		$temp_attachment_names = hesk_POST_array('attachments');
		foreach ($temp_attachment_names as $temp_attachment_name) {
			$temp_attachment = hesk_getTemporaryAttachment($temp_attachment_name);

			if ($temp_attachment !== null) {
				$attachments[] = $temp_attachment;
			}
		}
	}
}
$tmpvar['attachments'] = '';



if (hesk_POST('items_options') == "1") {

	if (count($_POST['computer_type'])) {

		$cv = $_POST['computer_type'];
		foreach ($cv as $fv) {
			if ($fv == "") {
				echo $hesk_error_buffer['computer_type'] = $hesklang['fill_all'] . " : " . $hesklang['computer_type'];
				break;
			}
		}
	}

	if (count($_POST['computer_operating_system'])) {

		$cv = $_POST['computer_operating_system'];
		foreach ($cv as $fv) {
			if ($fv == "") {
				//echo $hesk_error_buffer['computer_operating_system'] = $hesklang['fill_all'] . " : " . $hesklang['computer_operating_system'];
				break;
			}
		}
	}

	if (count($_POST['computer_processor'])) {

		$cv = $_POST['computer_processor'];
		foreach ($cv as $fv) {
			if ($fv == "") {
				//echo $hesk_error_buffer['computer_processor'] = $hesklang['fill_all'] . " : " . $hesklang['computer_processor'];
				break;
			}
		}
	}

	if (count($_POST['computer_ram'])) {

		$cv = $_POST['computer_ram'];
		foreach ($cv as $fv) {
			if ($fv == "") {
				//echo $hesk_error_buffer['computer_ram'] = $hesklang['fill_all'] . " : " . $hesklang['computer_ram'];
				break;
			}
		}
	}

	if (count($_POST['computer_type'])) {

		$cv = $_POST['computer_type'];
		foreach ($cv as $fv) {
			if ($fv == "") {
				echo $hesk_error_buffer['computer_type'] = $hesklang['fill_all'] . "  : " . $hesklang['computer_type'];
				break;
			}
		}
	}

	if (count($_POST['computer_storage'])) {

		$cv = $_POST['computer_storage'];
		foreach ($cv as $fv) {
			if ($fv == "") {
				//echo $hesk_error_buffer['computer_storage'] = $hesklang['fill_all'] . "  : " . $hesklang['computer_storage'];
				break;
			}
		}
	}

	if (count($_POST['computer_graphics'])) {

		$cv = $_POST['computer_graphics'];
		foreach ($cv as $fv) {
			if ($fv == "") {
				//echo $hesk_error_buffer['computer_graphics'] = $hesklang['fill_all'] . "  : " . $hesklang['computer_graphics'];
				break;
			}
		}
	}

	if (count($_POST['computer_comments'])) {

		$cv = $_POST['computer_comments'];
		foreach ($cv as $fv) {
			if ($fv == "") {
				//echo $hesk_error_buffer['computer_comments'] = $hesklang['fill_all'] . "  : " . $hesklang['computer_comments'];
				break;
			}
		}
	}

	if (count($_POST['computer_keyboard'])) {

		$cv = $_POST['computer_keyboard'];
		foreach ($cv as $fv) {
			if ($fv == "") {
				//echo $hesk_error_buffer['computer_keyboard'] = $hesklang['fill_all'] . "  : " . $hesklang['computer_keyboard'];
				break;
			}
		}
	}


	if (count($_POST['computer_mouse'])) {

		$cv = $_POST['computer_mouse'];
		foreach ($cv as $fv) {
			if ($fv == "") {
				//echo $hesk_error_buffer['computer_mouse'] = $hesklang['fill_all'] . "  : " . $hesklang['computer_mouse'];
				break;
			}
		}
	}

	if (count($_POST['computer_monitor'])) {

		$cv = $_POST['computer_monitor'];
		foreach ($cv as $fv) {
			if ($fv == "") {
				//echo $hesk_error_buffer['computer_monitor'] = $hesklang['fill_all'] . "  : " . $hesklang['computer_monitor'];
				break;
			}
		}
	}

	if (count($_POST['computer_screen'])) {

		$cv = $_POST['computer_screen'];
		foreach ($cv as $fv) {
			if ($fv == "") {
				//echo $hesk_error_buffer['computer_screen'] = $hesklang['fill_all'] . "  : " . $hesklang['computer_screen'];
				break;
			}
		}
	}

	if (count($_POST['computer_budget'])) {

		$cv = $_POST['computer_budget'];
		foreach ($cv as $fv) {
			if ($fv == "") {
				//echo $hesk_error_buffer['computer_budget'] = $hesklang['fill_all'] . "  : " . $hesklang['computer_budget'];
				break;
			}
		}
	}
}




if (hesk_POST('items_options') == "2") {

	if (count($_POST['printer_make'])) {

		$cv = $_POST['printer_make'];
		foreach ($cv as $fv) {
			if ($fv == "") {
				echo $hesk_error_buffer['printer_make'] = $hesklang['fill_all'] . "  : " . $hesklang['printer_make'];
				break;
			}
		}
	}

	if (count($_POST['printer_model'])) {

		$cv = $_POST['printer_model'];
		foreach ($cv as $fv) {
			if ($fv == "") {
				echo $hesk_error_buffer['printer_model'] = $hesklang['fill_all'] . "  : " . $hesklang['printer_model'];
				break;
			}
		}
	}

	if (count($_POST['printer_used_by'])) {

		$cv = $_POST['printer_used_by'];
		foreach ($cv as $fv) {
			if ($fv == "") {
				echo $hesk_error_buffer['printer_used_by'] = $hesklang['fill_all'] . " : " . $hesklang['printer_used_by'];
				break;
			}
		}
	}

	if (count($_POST['printer_catridge_make'])) {

		$cv = $_POST['printer_catridge_make'];
		foreach ($cv as $fv) {
			if ($fv == "") {
				echo $hesk_error_buffer['printer_catridge_make'] = $hesklang['fill_all'] . " : " . $hesklang['printer_catridge_make'];
				break;
			}
		}
	}

	if (count($_POST['printer_catridge_model'])) {

		$cv = $_POST['printer_catridge_model'];
		foreach ($cv as $fv) {
			if ($fv == "") {
				echo $hesk_error_buffer['printer_catridge_model'] = $hesklang['fill_all'] . "  : " . $hesklang['printer_catridge_model'];
				break;
			}
		}
	}

	if (count($_POST['printer_quantity'])) {

		$cv = $_POST['printer_quantity'];
		foreach ($cv as $fv) {
			if ($fv == "") {
				echo $hesk_error_buffer['printer_quantity'] = $hesklang['fill_all'] . "  : " . $hesklang['printer_quantity'];
				break;
			}
		}
	}

	if (count($_POST['printer_budget'])) {

		$cv = $_POST['printer_budget'];
		foreach ($cv as $fv) {
			if ($fv == "") {
				echo $hesk_error_buffer['printer_budget'] = $hesklang['fill_all'] . "  : " . $hesklang['printer_budget'];
				break;
			}
		}
	}

	if (count($_POST['printer_comments'])) {

		$cv = $_POST['printer_comments'];
		foreach ($cv as $fv) {
			if ($fv == "") {
				echo $hesk_error_buffer['printer_comments'] = $hesklang['fill_all'] . "  : " . $hesklang['printer_comments'];
				break;
			}
		}
	}
}


if (hesk_POST('items_options') == "3") {

	if (count($_POST['stationary_item'])) {

		$cv = $_POST['stationary_item'];
		foreach ($cv as $fv) {
			if ($fv == "") {
				echo $hesk_error_buffer['stationary_item'] = $hesklang['fill_all'] . "  : " . $hesklang['stationary_item'];
				break;
			}
		}
	}

	if (count($_POST['stationary_quantity'])) {

		$cv = $_POST['stationary_quantity'];
		foreach ($cv as $fv) {
			if ($fv == "") {
				echo $hesk_error_buffer['stationary_quantity'] = $hesklang['fill_all'] . "  : " . $hesklang['stationary_quantity'];
				break;
			}
		}
	}

	if (count($_POST['stationary_budget'])) {

		$cv = $_POST['stationary_budget'];
		foreach ($cv as $fv) {
			if ($fv == "") {
				echo $hesk_error_buffer['stationary_budget'] = $hesklang['fill_all'] . " : " . $hesklang['stationary_budget'];
				break;
			}
		}
	}

	if (count($_POST['stationary_remarks'])) {

		$cv = $_POST['stationary_remarks'];
		foreach ($cv as $fv) {
			if ($fv == "") {
				echo $hesk_error_buffer['stationary_remarks'] = $hesklang['fill_all'] . " : " . $hesklang['stationary_remarks'];
				break;
			}
		}
	}
}

if (hesk_POST('items_options') == "4") {

	if (count($_POST['description'])) {

		$cv = $_POST['description'];
		foreach ($cv as $fv) {
			if ($fv == "") {
				echo $hesk_error_buffer['description'] = $hesklang['fill_all'] . "  : " . $hesklang['description'];
				break;
			}
		}
	}

	if (count($_POST['budget'])) {

		$cv = $_POST['budget'];
		foreach ($cv as $fv) {
			if ($fv == "") {
				echo $hesk_error_buffer['budget'] = $hesklang['fill_all'] . "  : " . $hesklang['budget'];
				break;
			}
		}
	}

	if (count($_POST['quantity'])) {

		$cv = $_POST['quantity'];
		foreach ($cv as $fv) {
			if ($fv == "") {
				echo $hesk_error_buffer['quantity'] = $hesklang['fill_all'] . " : " . $hesklang['quantity'];
				break;
			}
		}
	}

	if (count($_POST['unit'])) {

		$cv = $_POST['unit'];
		foreach ($cv as $fv) {
			if ($fv == "") {
				echo $hesk_error_buffer['unit'] = $hesklang['fill_all'] . " : " . $hesklang['unit'];
				break;
			}
		}
	}

	if (count($_POST['remarks'])) {

		$cv = $_POST['remarks'];
		foreach ($cv as $fv) {
			if ($fv == "") {
				echo $hesk_error_buffer['remarks'] = $hesklang['fill_all'] . " : " . $hesklang['remarks'];
				break;
			}
		}
	}
}




// If we have any errors lets store info in session to avoid re-typing everything
if (count($hesk_error_buffer) != 0) {
	$_SESSION['iserror'] = array_keys($hesk_error_buffer);

	$_SESSION['as_name']     = hesk_POST('name');
	$_SESSION['as_email']    = hesk_POST('email');
	$_SESSION['as_priority'] = $tmpvar['priority'];
	$_SESSION['as_status']   = $tmpvar['status'];
	$_SESSION['as_items_options']  = hesk_POST('items_options');
	//$_SESSION['as_file_ReplacementEquipment'] = $_FILES['file_ReplacementEquipment'];
	$_SESSION['as_subject']  = hesk_POST('subject');
	$_SESSION['as_message']  = hesk_POST('message');
	$_SESSION['as_due_date'] = hesk_POST('due_date');
	$_SESSION['as_owner']    = $tmpvar['owner'];
	//$_SESSION['as_notify']   = $notify;
	$_SESSION['as_show'] = $show;
	$_SESSION['as_language'] = hesk_POST('as_language');
	//echo "<pre>";print_r($_SESSION);exit;
	foreach ($hesk_settings['custom_fields'] as $k => $v) {
		if ($v['name'] == "Predictive index completed") {
			continue;
		}
		if ($v['use'] && !in_array($v['type'], array('date', 'email'))) {
			$_SESSION["as_$k"] = ($v['type'] == 'checkbox') ? hesk_POST_array($k) : hesk_POST($k);
		}
	}

	$tmp = '';
	foreach ($hesk_error_buffer as $error) {
		$tmp .= "<li>$error</li>\n";
	}
	$hesk_error_buffer = $tmp;

	// Remove any successfully uploaded attachments
	if ($hesk_settings['attachments']['use']) {
		if ($use_legacy_attachments) {
			hesk_removeAttachments($attachments);
		} else {
			$_SESSION['as_attachments'] = $attachments;
		}
	}
	//print_r($_SESSION);exit;
	$hesk_error_buffer = $hesklang['pcer'] . '<br /><br /><ul>' . $hesk_error_buffer . '</ul>';
	hesk_process_messages($hesk_error_buffer, 'new_ticket.php?category=' . $tmpvar['category']);
}

if ($hesk_settings['attachments']['use'] && !empty($attachments)) {
	// Delete temp attachment records and set the new filename
	if (!$use_legacy_attachments) {
		$attachments = hesk_migrateTempAttachments($attachments, $tmpvar['trackid']);
	}

	foreach ($attachments as $myatt) {
		hesk_dbQuery("INSERT INTO `" . hesk_dbEscape($hesk_settings['db_pfix']) . "attachments` (`ticket_id`,`saved_name`,`real_name`,`size`) VALUES ('" . hesk_dbEscape($tmpvar['trackid']) . "','" . hesk_dbEscape($myatt['saved_name']) . "','" . hesk_dbEscape($myatt['real_name']) . "','" . intval($myatt['size']) . "')");
		$tmpvar['attachments'] .= hesk_dbInsertID() . '#' . $myatt['real_name'] . ',';
	}
}

$tmpvar['message_html'] = $tmpvar['message'];

if ($hesk_settings['staff_ticket_formatting'] == 2) {
	// Decode the message we encoded earlier
	$tmpvar['message_html'] = hesk_html_entity_decode($tmpvar['message_html']);

	// Clean the HTML code and set the plaintext version
	require(HESK_PATH . 'inc/htmlpurifier/HeskHTMLPurifier.php');
	require(HESK_PATH . 'inc/html2text/html2text.php');
	$purifier = new HeskHTMLPurifier($hesk_settings['cache_dir']);
	$tmpvar['message_html'] = $purifier->heskPurify($tmpvar['message_html']);

	$tmpvar['message'] = convert_html_to_text($tmpvar['message_html']);
	$tmpvar['message'] = fix_newlines($tmpvar['message']);

	// Prepare plain message for storage as HTML
	$tmpvar['message'] = hesk_htmlspecialchars($tmpvar['message']);
	$tmpvar['message'] = nl2br($tmpvar['message']);
} else {
	// `message` already contains a HTML friendly version. May as well just re-use it
	$tmpvar['message'] = hesk_makeURL($tmpvar['message']);
	$tmpvar['message'] = nl2br($tmpvar['message']);
	$tmpvar['message_html'] = $tmpvar['message'];
}

// Track who assigned the ticket
if ($tmpvar['owner'] > 0) {
	$tmpvar['assignedby'] = !empty($autoassign_owner) ? -1 : $_SESSION['id'];
}

// Demo mode
if (defined('HESK_DEMO')) {
	//hesk_process_messages(sprintf($hesklang['antdemo'], 'https://www.hesk.com/demo/index.php?a=add'), 'new_ticket.php?category=' . $tmpvar['category']);
}

if ($_FILES["file_ReplacementEquipment"]["name"] != "") {
	$target_dir = "../attachments/";
	$target_file = $target_dir . basename($_FILES["file_ReplacementEquipment"]["name"]);
	$uploadOk = 1;
	$imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));
	// Check if image file is a actual image or fake image
	//echo $imageFileType;exit;
	$check = getimagesize($_FILES["file_ReplacementEquipment"]["tmp_name"]);
	if ($check !== false) {
		echo "File is an image - " . $check["mime"] . ".";
		$uploadOk = 1;
	} else {
		echo "File is not an image.";
		$uploadOk = 0;
	}
	//$newName = now().$imageFileType;
	$uploadOk = 1;
	// Check if $uploadOk is set to 0 by an error
	if ($uploadOk == 0) {
		echo "Sorry, your file was not uploaded.";
		// if everything is ok, try to upload file
	} else { //echo "kkk";
		if (move_uploaded_file($_FILES["file_ReplacementEquipment"]["tmp_name"], $target_file)) {
			hesk_dbQuery("INSERT INTO `" . hesk_dbEscape($hesk_settings['db_pfix']) . "custom_attachments` (`ticket_track_id`,`saved_name`,`real_name`,`field_id`) VALUES ('" . hesk_dbEscape($tmpvar['trackid']) . "','" . hesk_dbEscape($target_file) . "','" . hesk_dbEscape($target_file) . "', 'ReplacementEquipment')");
			//echo $uploadOk."iamin";exit;
			echo "The file " . htmlspecialchars(basename($_FILES["file_ReplacementEquipment"]["name"])) . " has been uploaded.";
		} else {
			echo "Sorry, there was an error uploading your file.";
		}
	}
}

if ($_FILES["file_RevenueGenerating"]["name"] != "") {
	$target_dir = "../attachments/";
	$target_file = $target_dir . basename($_FILES["file_RevenueGenerating"]["name"]);
	$uploadOk = 1;
	$imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));
	// Check if image file is a actual image or fake image
	//echo $imageFileType;exit;
	$check = getimagesize($_FILES["file_RevenueGenerating"]["tmp_name"]);
	if ($check !== false) {
		echo "File is an image - " . $check["mime"] . ".";
		$uploadOk = 1;
	} else {
		echo "File is not an image.";
		$uploadOk = 0;
	}
	//$newName = now().$imageFileType;
	$uploadOk = 1;
	// Check if $uploadOk is set to 0 by an error
	if ($uploadOk == 0) {
		echo "Sorry, your file was not uploaded.";
		// if everything is ok, try to upload file
	} else { //echo "kkk";
		if (move_uploaded_file($_FILES["file_RevenueGenerating"]["tmp_name"], $target_file)) {
			hesk_dbQuery("INSERT INTO `" . hesk_dbEscape($hesk_settings['db_pfix']) . "custom_attachments` (`ticket_track_id`,`saved_name`,`real_name`,`field_id`) VALUES ('" . hesk_dbEscape($tmpvar['trackid']) . "','" . hesk_dbEscape($target_file) . "','" . hesk_dbEscape($target_file) . "', 'RevenueGenerating')");
			//echo $uploadOk."iamin";exit;
			echo "The file " . htmlspecialchars(basename($_FILES["file_RevenueGenerating"]["name"])) . " has been uploaded.";
		} else {
			echo "Sorry, there was an error uploading your file.";
		}
	}
}


if ($_FILES["file_Ifrecommendedforleaseratherthanpurchase"]["name"] != "") {
	$target_dir = "../attachments/";
	$target_file = $target_dir . basename($_FILES["file_Ifrecommendedforleaseratherthanpurchase"]["name"]);
	$uploadOk = 1;
	$imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));
	// Check if image file is a actual image or fake image
	//echo $imageFileType;exit;
	$check = getimagesize($_FILES["file_Ifrecommendedforleaseratherthanpurchase"]["tmp_name"]);
	if ($check !== false) {
		echo "File is an image - " . $check["mime"] . ".";
		$uploadOk = 1;
	} else {
		echo "File is not an image.";
		$uploadOk = 0;
	}
	//$newName = now().$imageFileType;
	$uploadOk = 1;
	// Check if $uploadOk is set to 0 by an error
	if ($uploadOk == 0) {
		echo "Sorry, your file was not uploaded.";
		// if everything is ok, try to upload file
	} else { //echo "kkk";
		if (move_uploaded_file($_FILES["file_Ifrecommendedforleaseratherthanpurchase"]["tmp_name"], $target_file)) {
			hesk_dbQuery("INSERT INTO `" . hesk_dbEscape($hesk_settings['db_pfix']) . "custom_attachments` (`ticket_track_id`,`saved_name`,`real_name`,`field_id`) VALUES ('" . hesk_dbEscape($tmpvar['trackid']) . "','" . hesk_dbEscape($target_file) . "','" . hesk_dbEscape($target_file) . "', 'Ifrecommendedforleaseratherthanpurchase')");
			//echo $uploadOk."iamin";exit;
			echo "The file " . htmlspecialchars(basename($_FILES["file_Ifrecommendedforleaseratherthanpurchase"]["name"])) . " has been uploaded.";
		} else {
			echo "Sorry, there was an error uploading your file.";
		}
	}
}

if ($_FILES["manufacture_logo"]["name"] != "") {
	$target_dir = "../attachments/";
	$target_file = $target_dir . basename($_FILES["manufacture_logo"]["name"]);
	$uploadOk = 1;
	$imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));
	// Check if image file is a actual image or fake image
	//echo $imageFileType;exit;
	$check = getimagesize($_FILES["manufacture_logo"]["tmp_name"]);
	if ($check !== false) {
		echo "File is an image - " . $check["mime"] . ".";
		$uploadOk = 1;
	} else {
		echo "File is not an image.";
		$uploadOk = 0;
	}
	//$newName = now().$imageFileType;
	$uploadOk = 1;
	// Check if $uploadOk is set to 0 by an error
	if ($uploadOk == 0) {
		echo "Sorry, your file was not uploaded.";
		// if everything is ok, try to upload file
	} else { //echo "kkk";
		if (move_uploaded_file($_FILES["manufacture_logo"]["tmp_name"], $target_file)) {
			hesk_dbQuery("INSERT INTO `" . hesk_dbEscape($hesk_settings['db_pfix']) . "custom_attachments` (`ticket_track_id`,`saved_name`,`real_name`,`field_id`) VALUES ('" . hesk_dbEscape($tmpvar['trackid']) . "','" . hesk_dbEscape($target_file) . "','" . hesk_dbEscape($target_file) . "', 'manufacture_logo')");
			//echo $uploadOk."iamin";exit;
			echo "The file " . htmlspecialchars(basename($_FILES["manufacture_logo"]["name"])) . " has been uploaded.";
		} else {
			echo "Sorry, there was an error uploading your file.";
		}
	}
}



//echo "<pre>";
//print_r($_REQUEST);
//ini_set('display_errors', 1);
//ini_set('display_startup_errors', 1);
//error_reporting(E_ALL);

$tmpvar['subject'] = isset($_POST['subject'] ) ? $_POST['subject'] : "";
$tmpvar['description'] = isset($_POST['description'] ) ? $_POST['description'] : "";
$tmpvar['quantity'] = isset($_POST['quantity'] ) ? $_POST['quantity'] : "";
$tmpvar['budget'] = isset($_POST['budget'] ) ? $_POST['budget'] : "";
$tmpvar['remarks'] = isset($_POST['remarks']) ? $_POST['remarks'] : "";
$tmpvar['unit'] = isset($_POST['unit']) ? $_POST['unit'] : "";
$tmpvar['card_name'] = isset($_POST['card_name'] ) ? $_POST['card_name'] : "";
$tmpvar['lastname'] = isset($_POST['lastname'] ) ? $_POST['lastname'] : "";
$tmpvar['card_title'] = isset($_POST['card_title'] ) ? $_POST['card_title'] : "";
$tmpvar['business_phone'] = isset($_POST['business_phone'] ) ? $_POST['business_phone'] : "";
$tmpvar['extension'] = isset($_POST['extension'] ) ? $_POST['extension'] : "";
$tmpvar['card_email'] = isset($_POST['card_email'] ) ? $_POST['card_email'] : "";
$tmpvar['cell'] = isset($_POST['card_cell'] ) ? $_POST['card_cell'] : "";
$tmpvar['items_options'] = isset($_POST['items_options'] ) ? $_POST['items_options'] : "";
$tmpvar['computer_operating_system'] = isset($_POST['computer_operating_system'] ) ? $_POST['computer_operating_system'] : "";
$tmpvar['computer_processor'] = isset($_POST['computer_processor'] ) ? $_POST['computer_processor'] : "";
$tmpvar['computer_ram'] = isset($_POST['computer_ram'] ) ? $_POST['computer_ram'] : "";
$tmpvar['computer_storage'] = isset($_POST['computer_storage'] ) ? $_POST['computer_storage'] : "";
$tmpvar['computer_graphics'] = isset($_POST['computer_graphics'] ) ? $_POST['computer_graphics'] : "";
$tmpvar['computer_screen'] = isset($_POST['computer_screen'] ) ? $_POST['computer_screen'] : "";
$tmpvar['computer_keyboard'] = isset($_POST['computer_keyboard'] ) ? $_POST['computer_keyboard'] : "";
$tmpvar['computer_mouse'] = isset($_POST['computer_mouse'] ) ? $_POST['computer_mouse'] : "";
$tmpvar['computer_monitor'] = isset($_POST['computer_monitor'] ) ? $_POST['computer_monitor'] : "";
$tmpvar['computer_budget'] = isset($_POST['computer_budget'] ) ? $_POST['computer_budget'] : "";
$tmpvar['computer_comments'] = isset($_POST['computer_comments'] ) ? $_POST['computer_comments'] : "";
$tmpvar['printer_make'] = isset($_POST['printer_make'] ) ? $_POST['printer_make'] : "";
$tmpvar['printer_model'] = isset($_POST['printer_model'] ) ? $_POST['printer_model'] : "";
$tmpvar['printer_used_by'] = isset($_POST['printer_used_by'] ) ? $_POST['printer_used_by'] : "";
$tmpvar['printer_catridge_make'] = isset($_POST['printer_catridge_make'] ) ? $_POST['printer_catridge_make'] : "";
$tmpvar['printer_catridge_model'] = isset($_POST['printer_catridge_model'] ) ? $_POST['printer_catridge_model'] : "";
$tmpvar['printer_quantity'] = isset($_POST['printer_quantity'] ) ? $_POST['printer_quantity'] : "";
$tmpvar['printer_budget'] = isset($_POST['printer_budget'] ) ? $_POST['printer_budget'] : "";
$tmpvar['printer_comments'] = isset($_POST['printer_comments'] ) ? $_POST['printer_comments'] : "";
$tmpvar['stationary_item'] = isset($_POST['stationary_item'] ) ? $_POST['stationary_item'] : "";
$tmpvar['stationary_quantity'] = isset($_POST['stationary_quantity'] ) ? $_POST['stationary_quantity'] : "";
$tmpvar['stationary_budget'] = isset($_POST['stationary_budget'] ) ? $_POST['stationary_budget'] : "";
$tmpvar['stationary_remarks'] = isset($_POST['stationary_remarks'] ) ? $_POST['stationary_remarks'] : "";
$tmpvar['first_tag'] = isset($_POST['first_tag'] ) ? $_POST['first_tag'] : "";
$tmpvar['second_tag'] = isset($_POST['second_tag'] ) ? $_POST['second_tag'] : "";
$tmpvar['first_name'] = isset($_POST['first_name'] ) ? $_POST['first_name'] : "";
$tmpvar['last_name'] = isset($_POST['last_name'] ) ? $_POST['last_name'] : "";
$tmpvar['title'] = isset($_POST['title'] ) ? $_POST['title'] : "";
$tmpvar['reason_code'] = isset($_POST['reason_code'] ) ? $_POST['reason_code'] : "";

if (in_array(intval($tmpvar['category']), array(9, 10))) {
	$cat_name = ($tmpvar['category'] == 9) ? 'Credit Application-USA' : 'Credit Application-CANADA';
	$company_name = isset($_POST['credit_company_name']) ? hesk_input($_POST['credit_company_name']) : '';
	$tmpvar['subject'] = $cat_name . ' - ' . $company_name;

	$dealerships_arr = isset($_POST['credit_dealerships']) && is_array($_POST['credit_dealerships']) ? $_POST['credit_dealerships'] : array();
	$dealerships_str = implode(', ', array_map('htmlspecialchars', $dealerships_arr));
	
	$street_address = isset($_POST['credit_street_address']) ? hesk_input($_POST['credit_street_address']) : '';
	$postal_zip = isset($_POST['credit_postal_zip']) ? hesk_input($_POST['credit_postal_zip']) : '';
	$phone = isset($_POST['credit_phone']) ? hesk_input($_POST['credit_phone']) : '';
	$stmt_email = isset($_POST['credit_statement_email']) ? hesk_input($_POST['credit_statement_email']) : '';
	$owner_name = isset($_POST['credit_owner_name']) ? hesk_input($_POST['credit_owner_name']) : '';
	$operating_since = isset($_POST['credit_operating_since']) ? hesk_input($_POST['credit_operating_since']) : '';
	$type_of_business = isset($_POST['credit_type_of_business']) ? hesk_input($_POST['credit_type_of_business']) : '';
	$credit_limit = isset($_POST['credit_limit']) ? hesk_input($_POST['credit_limit']) : '';
	$business_structure = isset($_POST['credit_business_structure']) ? hesk_input($_POST['credit_business_structure']) : '';
	$po_issued = isset($_POST['credit_po_issued']) ? hesk_input($_POST['credit_po_issued']) : '';
	$application_for = isset($_POST['credit_application_for']) ? hesk_input($_POST['credit_application_for']) : '';
	$tax_id = isset($_POST['credit_tax_id']) ? hesk_input($_POST['credit_tax_id']) : '';
	$bank_name = isset($_POST['credit_bank_name']) ? hesk_input($_POST['credit_bank_name']) : '';
	$bank_address = isset($_POST['credit_bank_address']) ? hesk_input($_POST['credit_bank_address']) : '';
	$applicant_name = isset($_POST['credit_applicant_name']) ? hesk_input($_POST['credit_applicant_name']) : '';
	$app_date = isset($_POST['credit_date']) && !empty($_POST['credit_date']) ? hesk_input($_POST['credit_date']) : date('Y-m-d');
	$tax_label = ($tmpvar['category'] == 9) ? 'Business Tax ID' : 'GST Number';
	$zip_label = ($tmpvar['category'] == 9) ? 'ZIP CODE' : 'POSTAL CODE';
	$country_label = ($tmpvar['category'] == 9) ? 'UNITED STATES' : 'CANADA';

	$ref_rows_html = '';
	if (isset($_POST['credit_ref_name']) && is_array($_POST['credit_ref_name'])) {
		for ($i = 0; $i < count($_POST['credit_ref_name']); $i++) {
			$r_name = htmlspecialchars(hesk_input($_POST['credit_ref_name'][$i]));
			$r_addr = isset($_POST['credit_ref_address'][$i]) ? htmlspecialchars(hesk_input($_POST['credit_ref_address'][$i])) : '';
			$r_phone = isset($_POST['credit_ref_phone'][$i]) ? htmlspecialchars(hesk_input($_POST['credit_ref_phone'][$i])) : '';
			$r_cont = isset($_POST['credit_ref_contact'][$i]) ? htmlspecialchars(hesk_input($_POST['credit_ref_contact'][$i])) : '';
			if (!empty($r_name) || !empty($r_addr) || !empty($r_phone) || !empty($r_cont)) {
				$ref_rows_html .= '<tr>
					<td style="border:1px solid #cbd5e0;padding:8px 10px;">' . ($i + 1) . '. ' . $r_name . '</td>
					<td style="border:1px solid #cbd5e0;padding:8px 10px;">' . $r_addr . '</td>
					<td style="border:1px solid #cbd5e0;padding:8px 10px;">' . $r_phone . '</td>
					<td style="border:1px solid #cbd5e0;padding:8px 10px;">' . $r_cont . '</td>
				</tr>';
			}
		}
	}
	if (empty($ref_rows_html)) {
		$ref_rows_html = '<tr><td colspan="4" style="border:1px solid #cbd5e0;padding:10px;text-align:center;color:#718096;">No credit references provided</td></tr>';
	}

	$sig_html = '';
	if (!empty($_POST['signature_data']) && strpos($_POST['signature_data'], 'data:image') === 0) {
		$sig_html = '<img src="' . htmlspecialchars($_POST['signature_data']) . '" alt="Applicant Signature" style="max-height:80px;max-width:300px;border-bottom:1px solid #2d3748;display:block;">';
	} elseif (!empty($_POST['credit_typed_signature'])) {
		$sig_html = '<span style="font-family:\'Brush Script MT\',cursive,sans-serif;font-size:1.6rem;color:#1a365d;border-bottom:1px solid #2d3748;display:inline-block;padding:2px 15px;">' . htmlspecialchars($_POST['credit_typed_signature']) . '</span>';
	} else {
		$sig_html = '<span style="font-style:italic;color:#718096;">Signature on file</span>';
	}

	$app_html = '
	<div style="font-family:Arial,sans-serif;background:#ffffff;padding:24px;border:1px solid #e2e8f0;border-radius:8px;max-width:900px;margin:0 auto;color:#2d3748;">
		<div style="display:flex;justify-content:space-between;align-items:center;border-bottom:2px solid #0c783c;padding-bottom:12px;margin-bottom:18px;">
			<div>
				<img src="' . $hesk_settings['hesk_url'] . '/img/rafih-logo.jpg" alt="Rafih Auto Group" style="max-height:48px;">
			</div>
			<div style="text-align:right;">
				<h2 style="margin:0;font-size:1.5rem;color:#1a202c;font-family:Georgia,serif;">Business Credit Application</h2>
				<span style="display:inline-block;background:#0c783c;color:#ffffff;font-size:0.75rem;font-weight:bold;padding:3px 10px;border-radius:12px;margin-top:4px;">' . $country_label . '</span>
			</div>
		</div>

		<div style="margin-bottom:16px;">
			<strong style="color:#0c783c;font-size:0.9rem;text-transform:uppercase;">Dealership(s) Requested:</strong>
			<div style="background:#f7faf7;border:1px solid #c6e2cf;border-radius:4px;padding:10px 14px;margin-top:6px;font-weight:600;color:#2d3748;">
				' . ($dealerships_str ? $dealerships_str : 'None selected') . '
			</div>
		</div>

		<h3 style="color:#0c783c;font-size:0.95rem;text-transform:uppercase;border-bottom:1px solid #e2e8f0;padding-bottom:4px;margin:18px 0 10px 0;">Company Information</h3>
		<table style="width:100%;border-collapse:collapse;margin-bottom:16px;">
			<tr>
				<td style="padding:6px 0;width:50%;"><strong>NAME OF COMPANY:</strong> ' . htmlspecialchars($company_name) . '</td>
				<td style="padding:6px 0;width:50%;"><strong>OWNER NAME:</strong> ' . htmlspecialchars($owner_name) . '</td>
			</tr>
			<tr>
				<td style="padding:6px 0;"><strong>STREET ADDRESS:</strong> ' . htmlspecialchars($street_address) . '</td>
				<td style="padding:6px 0;"><strong>' . $zip_label . ':</strong> ' . htmlspecialchars($postal_zip) . '</td>
			</tr>
			<tr>
				<td style="padding:6px 0;"><strong>PHONE #:</strong> ' . htmlspecialchars($phone) . '</td>
				<td style="padding:6px 0;"><strong>STATEMENT EMAIL:</strong> ' . htmlspecialchars($stmt_email) . '</td>
			</tr>
			<tr>
				<td style="padding:6px 0;"><strong>OPERATING SINCE:</strong> ' . htmlspecialchars($operating_since) . '</td>
				<td style="padding:6px 0;"><strong>TYPE OF BUSINESS:</strong> ' . htmlspecialchars($type_of_business) . '</td>
			</tr>
			<tr>
				<td style="padding:6px 0;"><strong>CREDIT LIMIT REQUESTED:</strong> $' . htmlspecialchars($credit_limit) . '</td>
				<td style="padding:6px 0;"><strong>BUSINESS STRUCTURE:</strong> ' . htmlspecialchars($business_structure) . '</td>
			</tr>
			<tr>
				<td style="padding:6px 0;"><strong>PURCHASE ORDERS ISSUED?</strong> ' . htmlspecialchars($po_issued) . '</td>
				<td style="padding:6px 0;"><strong>IS THIS FOR:</strong> ' . htmlspecialchars($application_for) . '</td>
			</tr>
			<tr>
				<td style="padding:6px 0;"><strong>' . $tax_label . ':</strong> ' . htmlspecialchars($tax_id) . '</td>
				<td style="padding:6px 0;"><strong>BANK NAME:</strong> ' . htmlspecialchars($bank_name) . '</td>
			</tr>
			<tr>
				<td colspan="2" style="padding:6px 0;"><strong>BANK ADDRESS:</strong> ' . htmlspecialchars($bank_address) . '</td>
			</tr>
		</table>

		<h3 style="color:#0c783c;font-size:0.95rem;text-transform:uppercase;border-bottom:1px solid #e2e8f0;padding-bottom:4px;margin:18px 0 10px 0;">Credit References</h3>
		<table style="width:100%;border-collapse:collapse;margin-bottom:18px;">
			<thead>
				<tr style="background:#f0f4f0;">
					<th style="border:1px solid #cbd5e0;padding:8px 10px;text-align:left;font-size:0.8rem;text-transform:uppercase;">Business Name</th>
					<th style="border:1px solid #cbd5e0;padding:8px 10px;text-align:left;font-size:0.8rem;text-transform:uppercase;">Address</th>
					<th style="border:1px solid #cbd5e0;padding:8px 10px;text-align:left;font-size:0.8rem;text-transform:uppercase;">Phone #</th>
					<th style="border:1px solid #cbd5e0;padding:8px 10px;text-align:left;font-size:0.8rem;text-transform:uppercase;">Controller/Acct. Contact</th>
				</tr>
			</thead>
			<tbody>
				' . $ref_rows_html . '
			</tbody>
		</table>

		<div style="background:#f9fafb;border:1px solid #e2e8f0;border-left:4px solid #0c783c;border-radius:4px;padding:12px;margin:18px 0;font-size:0.82rem;line-height:1.55;text-align:justify;">
			***I/We declare and formally request an account with The Rafih Auto Group for the dealership(s) requested above. I declare that I am legally authorized to bind this company on the application for all credit. We warrant and confirm that the information given herein is true and correct and authorize such information to be used to determine credit. We understand that a 3% per month service charge is levied on all overdue accounts. Note that credit may be terminated at any time. No credit will be extended on any account over sixty (60) days. If any payment obligation under this agreement is not paid when due, the Borrower agrees to pay all collection costs, including attorney fees, court costs as well as any fees/charges resulting from the collection.
		</div>

		<table style="width:100%;border-collapse:collapse;margin-top:16px;">
			<tr>
				<td style="padding:10px 0;width:50%;vertical-align:bottom;">
					<strong>Name of Applicant:</strong> ' . htmlspecialchars($applicant_name) . '<br>
					<strong>Date:</strong> ' . htmlspecialchars($app_date) . '
				</td>
				<td style="padding:10px 0;width:50%;vertical-align:bottom;text-align:right;">
					<div style="display:inline-block;text-align:left;">
						<strong>Signature:</strong><br>
						' . $sig_html . '
					</div>
				</td>
			</tr>
		</table>
	</div>';

	$tmpvar['message_html'] = $app_html;
	$tmpvar['message'] = strip_tags(str_replace(array('</tr>', '<br>', '</div>'), "\n", $app_html));
}

// Vehicle Order Intake Form Processing
if ($tmpvar['category'] == 11 || (isset($hesk_settings['categories'][$tmpvar['category']]['name']) && (stripos($hesk_settings['categories'][$tmpvar['category']]['name'], 'Vehicle Order') !== false || stripos($hesk_settings['categories'][$tmpvar['category']]['name'], 'Order Intake') !== false))) {
	$voi_dealership = isset($_POST['voi_dealership']) ? hesk_input($_POST['voi_dealership']) : '';
	$voi_order_month = isset($_POST['voi_order_month']) ? hesk_input($_POST['voi_order_month']) : '';
	$voi_brand = isset($_POST['voi_brand']) ? hesk_input($_POST['voi_brand']) : '';
	$voi_target_months = isset($_POST['voi_target_months']) ? floatval($_POST['voi_target_months']) : 2.0;
	if ($voi_target_months <= 0) $voi_target_months = 2.0;
	$voi_notes = isset($_POST['voi_notes']) ? hesk_input($_POST['voi_notes']) : '';

	$tmpvar['subject'] = 'Vehicle Order Intake - ' . ($voi_dealership ? $voi_dealership : 'Dealership') . ' (' . ($voi_order_month ? $voi_order_month : date('M Y')) . ')';

	// Parse line items JSON
	$rows_data = array();
	if (!empty($_POST['voi_data_json'])) {
		$decoded = json_decode($_POST['voi_data_json'], true);
		if (is_array($decoded)) {
			$rows_data = $decoded;
		}
	}

	// Fallback to POST arrays if JSON empty
	if (empty($rows_data) && isset($_POST['voi_m']) && is_array($_POST['voi_m'])) {
		for ($i = 0; $i < count($_POST['voi_m']); $i++) {
			$m = trim(hesk_input($_POST['voi_m'][$i]));
			if (empty($m)) continue;
			$t = isset($_POST['voi_t'][$i]) ? trim(hesk_input($_POST['voi_t'][$i])) : '';
			$c = isset($_POST['voi_c'][$i]) ? floatval($_POST['voi_c'][$i]) : 0;
			$d = isset($_POST['voi_d'][$i]) ? floatval($_POST['voi_d'][$i]) : 0;
			$e = isset($_POST['voi_e'][$i]) ? floatval($_POST['voi_e'][$i]) : 0;
			$g = isset($_POST['voi_g'][$i]) ? floatval($_POST['voi_g'][$i]) : 0;
			$j = isset($_POST['voi_j'][$i]) ? floatval($_POST['voi_j'][$i]) : 0;
			$f = $d + $e;
			$h = $f + $g;
			$i_val = $c > 0 ? ($h / $c) : 0;
			$k_val = $c > 0 ? ($i_val - $j) : 0;
			$l_val = $c > 0 ? max(0, round($voi_target_months * $c - ($f - $c * $j))) : 0;
			$q_val = (isset($_POST['voi_q'][$i]) && strlen(trim($_POST['voi_q'][$i])) > 0) ? floatval($_POST['voi_q'][$i]) : $l_val;
			$m_val = $f + $q_val;
			$n_val = $c > 0 ? (($m_val - $c * $j) / $c) : 0;
			$cm = isset($_POST['voi_cm'][$i]) ? trim(hesk_input($_POST['voi_cm'][$i])) : '';
			$flag = $n_val > ($voi_target_months + 0.05) ? 'Over' : ($n_val < 1.0 ? 'Short' : 'Normal');

			$rows_data[] = array(
				'model' => $m,
				'trim' => $t,
				'avg_sold' => $c,
				'stock' => $d,
				'on_order' => $e,
				'total_supply_land' => $f,
				'mfr_suggested' => $g,
				'supply_incl_mfr' => $h,
				'gross_months_mfr' => $c > 0 ? number_format($i_val, 1) : '–',
				'months_until_arrive' => $j,
				'net_months_mfr' => $c > 0 ? number_format($k_val, 1) : '–',
				'rec_dealer_order' => $l_val,
				'dealer_order_qty' => $q_val,
				'total_supply_dealer' => $m_val,
				'net_months_dealer' => $c > 0 ? number_format($n_val, 1) : '–',
				'status_flag' => $flag,
				'comment' => $cm
			);
		}
	}

	// Build rows HTML and calculate totals
	$table_rows_html = '';
	$tot_c = 0; $tot_d = 0; $tot_e = 0; $tot_f = 0;
	$tot_g = 0; $tot_h = 0; $tot_l = 0; $tot_q = 0; $tot_m = 0;

	foreach ($rows_data as $row) {
		$m = htmlspecialchars($row['model']);
		$t = htmlspecialchars($row['trim']);
		$c = floatval($row['avg_sold']);
		$d = floatval($row['stock']);
		$e = floatval($row['on_order']);
		$f = floatval($row['total_supply_land']);
		$g = floatval($row['mfr_suggested']);
		$h = floatval($row['supply_incl_mfr']);
		$gross_mfr = htmlspecialchars($row['gross_months_mfr']);
		$j = floatval($row['months_until_arrive']);
		$net_mfr = htmlspecialchars($row['net_months_mfr']);
		$rec_order = floatval($row['rec_dealer_order']);
		$q = floatval($row['dealer_order_qty']);
		$m_dealer = floatval($row['total_supply_dealer']);
		$net_dealer = htmlspecialchars($row['net_months_dealer']);
		$flag = isset($row['status_flag']) ? $row['status_flag'] : 'Normal';
		$cm = htmlspecialchars($row['comment']);

		$tot_c += $c; $tot_d += $d; $tot_e += $e; $tot_f += $f;
		$tot_g += $g; $tot_h += $h; $tot_l += $rec_order; $tot_q += $q; $tot_m += $m_dealer;

		$badge_html = '';
		if ($flag == 'Over') {
			$badge_html = ' <span style="display:inline-block;background:#fef3c7;color:#b4540a;border:1px solid #fde68a;font-size:10px;font-weight:bold;padding:1px 6px;border-radius:10px;">OVER</span>';
		} elseif ($flag == 'Short') {
			$badge_html = ' <span style="display:inline-block;background:#fee2e2;color:#dc2626;border:1px solid #fca5a5;font-size:10px;font-weight:bold;padding:1px 6px;border-radius:10px;">SHORT</span>';
		}

		$table_rows_html .= '<tr>
			<td style="border:1px solid #cbd5e1;padding:7px 8px;font-weight:600;color:#0f172a;">' . $m . '</td>
			<td style="border:1px solid #cbd5e1;padding:7px 8px;color:#475569;">' . ($t ? $t : '–') . '</td>
			<td style="border:1px solid #cbd5e1;padding:7px 8px;text-align:right;">' . number_format($c, 1) . '</td>
			<td style="border:1px solid #cbd5e1;padding:7px 8px;text-align:right;">' . $d . '</td>
			<td style="border:1px solid #cbd5e1;padding:7px 8px;text-align:right;">' . $e . '</td>
			<td style="border:1px solid #cbd5e1;padding:7px 8px;text-align:right;background:#fefce8;color:#854d0e;font-weight:600;">' . $f . '</td>
			<td style="border:1px solid #cbd5e1;padding:7px 8px;text-align:right;">' . $g . '</td>
			<td style="border:1px solid #cbd5e1;padding:7px 8px;text-align:right;background:#fefce8;color:#854d0e;font-weight:600;">' . $h . '</td>
			<td style="border:1px solid #cbd5e1;padding:7px 8px;text-align:right;background:#fefce8;color:#854d0e;">' . $gross_mfr . '</td>
			<td style="border:1px solid #cbd5e1;padding:7px 8px;text-align:right;">' . number_format($j, 1) . '</td>
			<td style="border:1px solid #cbd5e1;padding:7px 8px;text-align:right;background:#fefce8;color:#854d0e;">' . $net_mfr . '</td>
			<td style="border:1px solid #cbd5e1;padding:7px 8px;text-align:right;background:#fefce8;color:#854d0e;font-weight:600;">' . $rec_order . '</td>
			<td style="border:1px solid #cbd5e1;padding:7px 8px;text-align:right;font-weight:700;color:#0c783c;background:#f0fdf4;">' . $q . '</td>
			<td style="border:1px solid #cbd5e1;padding:7px 8px;text-align:right;background:#fefce8;color:#854d0e;font-weight:600;">' . $m_dealer . '</td>
			<td style="border:1px solid #cbd5e1;padding:7px 8px;text-align:right;background:#fefce8;color:#854d0e;font-weight:600;">' . $net_dealer . $badge_html . '</td>
			<td style="border:1px solid #cbd5e1;padding:7px 8px;color:#334155;font-size:11px;">' . ($cm ? $cm : '–') . '</td>
		</tr>';
	}

	$overall_gross = $tot_c > 0 ? number_format($tot_h / $tot_c, 1) : '–';

	$voi_html = '
	<div id="vehicle-order-intake-content" style="font-family:-apple-system,BlinkMacSystemFont,\'Segoe UI\',Roboto,Helvetica,Arial,sans-serif;background:#ffffff;padding:24px;border:1px solid #e2e8f0;border-radius:8px;max-width:100%;margin:0 auto;color:#1e293b;">
		<div style="display:flex;justify-content:space-between;align-items:center;border-bottom:2px solid #0c783c;padding-bottom:14px;margin-bottom:18px;flex-wrap:wrap;gap:12px;">
			<div>
				<img src="' . $hesk_settings['hesk_url'] . '/img/rafih-logo.jpg" alt="Rafih Auto Group" style="max-height:48px;">
			</div>
			<div style="text-align:right;">
				<h2 style="margin:0;font-size:1.5rem;color:#1a202c;font-family:Georgia,serif;">New Vehicle Order Intake</h2>
				<span style="display:inline-block;background:#0c783c;color:#ffffff;font-size:0.75rem;font-weight:bold;padding:3px 12px;border-radius:12px;margin-top:4px;">Allocation & Supply Planning</span>
			</div>
		</div>

		<table style="width:100%;border-collapse:collapse;margin-bottom:20px;background:#f8faf9;border:1.5px solid #cbd5e1;border-radius:6px;">
			<tr>
				<td style="padding:10px 14px;border-right:1px solid #e2e8f0;width:25%;">
					<div style="font-size:0.72rem;font-weight:bold;color:#64748b;text-transform:uppercase;">Dealership</div>
					<div style="font-size:0.95rem;font-weight:700;color:#0c783c;">' . ($voi_dealership ? htmlspecialchars($voi_dealership) : 'N/A') . '</div>
				</td>
				<td style="padding:10px 14px;border-right:1px solid #e2e8f0;width:25%;">
					<div style="font-size:0.72rem;font-weight:bold;color:#64748b;text-transform:uppercase;">Allocation Month</div>
					<div style="font-size:0.95rem;font-weight:600;color:#1e293b;">' . ($voi_order_month ? htmlspecialchars($voi_order_month) : date('F Y')) . '</div>
				</td>
				<td style="padding:10px 14px;border-right:1px solid #e2e8f0;width:25%;">
					<div style="font-size:0.72rem;font-weight:bold;color:#64748b;text-transform:uppercase;">Brand / Manufacturer</div>
					<div style="font-size:0.95rem;font-weight:600;color:#1e293b;">' . ($voi_brand ? htmlspecialchars($voi_brand) : '–') . '</div>
				</td>
				<td style="padding:10px 14px;width:25%;">
					<div style="font-size:0.72rem;font-weight:bold;color:#64748b;text-transform:uppercase;">Target Supply</div>
					<div style="font-size:0.95rem;font-weight:600;color:#1e293b;">' . number_format($voi_target_months, 1) . ' Months</div>
				</td>
			</tr>
		</table>

		<div style="overflow-x:auto;-webkit-overflow-scrolling:touch;border:1px solid #cbd5e1;border-radius:6px;margin-bottom:18px;">
			<table style="width:100%;min-width:1450px;border-collapse:collapse;font-size:12px;font-variant-numeric:tabular-nums;">
				<thead>
					<tr style="background:#f1f5f9;color:#334155;text-transform:uppercase;font-size:10.5px;letter-spacing:0.03em;">
						<th style="padding:9px 8px;border-bottom:2px solid #cbd5e1;text-align:left;">Vehicle Model</th>
						<th style="padding:9px 8px;border-bottom:2px solid #cbd5e1;text-align:left;">Trim</th>
						<th style="padding:9px 8px;border-bottom:2px solid #cbd5e1;text-align:right;">Avg Sold</th>
						<th style="padding:9px 8px;border-bottom:2px solid #cbd5e1;text-align:right;">In Stock</th>
						<th style="padding:9px 8px;border-bottom:2px solid #cbd5e1;text-align:right;">On Order</th>
						<th style="padding:9px 8px;border-bottom:2px solid #cbd5e1;text-align:right;background:#fefce8;color:#854d0e;">Total Supply</th>
						<th style="padding:9px 8px;border-bottom:2px solid #cbd5e1;text-align:right;">Mfr Sugg</th>
						<th style="padding:9px 8px;border-bottom:2px solid #cbd5e1;text-align:right;background:#fefce8;color:#854d0e;">Supply w/ Mfr</th>
						<th style="padding:9px 8px;border-bottom:2px solid #cbd5e1;text-align:right;background:#fefce8;color:#854d0e;">Gross Mos</th>
						<th style="padding:9px 8px;border-bottom:2px solid #cbd5e1;text-align:right;">Mos Arrive</th>
						<th style="padding:9px 8px;border-bottom:2px solid #cbd5e1;text-align:right;background:#fefce8;color:#854d0e;">Net Mos (Mfr)</th>
						<th style="padding:9px 8px;border-bottom:2px solid #cbd5e1;text-align:right;background:#fefce8;color:#854d0e;">Rec Dealer</th>
						<th style="padding:9px 8px;border-bottom:2px solid #cbd5e1;text-align:right;font-weight:bold;color:#0c783c;background:#f0fdf4;">Dealer Qty</th>
						<th style="padding:9px 8px;border-bottom:2px solid #cbd5e1;text-align:right;background:#fefce8;color:#854d0e;">Supply (Dealer)</th>
						<th style="padding:9px 8px;border-bottom:2px solid #cbd5e1;text-align:right;background:#fefce8;color:#854d0e;">Net Mos (Dealer)</th>
						<th style="padding:9px 8px;border-bottom:2px solid #cbd5e1;text-align:left;width:220px;">Justification / Notes</th>
					</tr>
				</thead>
				<tbody>
					' . $table_rows_html . '
				</tbody>
				<tfoot>
					<tr style="background:#f8fafc;font-weight:bold;border-top:2px solid #cbd5e1;">
						<td style="padding:9px 8px;border:1px solid #cbd5e1;">TOTAL</td>
						<td style="padding:9px 8px;border:1px solid #cbd5e1;"></td>
						<td style="padding:9px 8px;border:1px solid #cbd5e1;text-align:right;">' . number_format($tot_c, 1) . '</td>
						<td style="padding:9px 8px;border:1px solid #cbd5e1;text-align:right;">' . $tot_d . '</td>
						<td style="padding:9px 8px;border:1px solid #cbd5e1;text-align:right;">' . $tot_e . '</td>
						<td style="padding:9px 8px;border:1px solid #cbd5e1;text-align:right;background:#fef9c3;color:#854d0e;">' . $tot_f . '</td>
						<td style="padding:9px 8px;border:1px solid #cbd5e1;text-align:right;">' . $tot_g . '</td>
						<td style="padding:9px 8px;border:1px solid #cbd5e1;text-align:right;background:#fef9c3;color:#854d0e;">' . $tot_h . '</td>
						<td style="padding:9px 8px;border:1px solid #cbd5e1;text-align:right;background:#fef9c3;color:#854d0e;">' . $overall_gross . '</td>
						<td style="padding:9px 8px;border:1px solid #cbd5e1;text-align:right;"></td>
						<td style="padding:9px 8px;border:1px solid #cbd5e1;text-align:right;"></td>
						<td style="padding:9px 8px;border:1px solid #cbd5e1;text-align:right;background:#fef9c3;color:#854d0e;">' . $tot_l . '</td>
						<td style="padding:9px 8px;border:1px solid #cbd5e1;text-align:right;color:#0c783c;background:#dcfce7;font-weight:700;">' . $tot_q . '</td>
						<td style="padding:9px 8px;border:1px solid #cbd5e1;text-align:right;background:#fef9c3;color:#854d0e;">' . $tot_m . '</td>
						<td style="padding:9px 8px;border:1px solid #cbd5e1;text-align:right;"></td>
						<td style="padding:9px 8px;border:1px solid #cbd5e1;"></td>
					</tr>
				</tfoot>
			</table>
		</div>';

	if ($voi_notes) {
		$voi_html .= '
		<div style="margin-top:14px;background:#f8fafc;border:1px solid #e2e8f0;border-radius:6px;padding:12px 16px;">
			<strong style="color:#0c783c;font-size:0.85rem;text-transform:uppercase;">General Notes / Instructions:</strong>
			<div style="margin-top:6px;color:#334155;font-size:0.9rem;white-space:pre-wrap;">' . nl2br(htmlspecialchars($voi_notes)) . '</div>
		</div>';
	}

	$voi_html .= '
	</div>';

	$tmpvar['message_html'] = $voi_html;
	$tmpvar['message'] = strip_tags(str_replace(array('</tr>', '<br>', '</div>'), "\n", $voi_html));
}


//print_r($tmpvar);
// Insert ticket to database
$ticket = hesk_newTicket($tmpvar);

if (in_array(intval($tmpvar['category']), array(9, 10))) {
	$cat_type = ($tmpvar['category'] == 9) ? 'USA' : 'CANADA';
	$dealerships_arr = isset($_POST['credit_dealerships']) && is_array($_POST['credit_dealerships']) ? $_POST['credit_dealerships'] : array();
	$dealerships_str = implode(', ', array_map('trim', $dealerships_arr));
	$company_name = isset($_POST['credit_company_name']) ? hesk_input($_POST['credit_company_name']) : '';
	$street_address = isset($_POST['credit_street_address']) ? hesk_input($_POST['credit_street_address']) : '';
	$postal_zip = isset($_POST['credit_postal_zip']) ? hesk_input($_POST['credit_postal_zip']) : '';
	$phone = isset($_POST['credit_phone']) ? hesk_input($_POST['credit_phone']) : '';
	$stmt_email = isset($_POST['credit_statement_email']) ? hesk_input($_POST['credit_statement_email']) : '';
	$owner_name = isset($_POST['credit_owner_name']) ? hesk_input($_POST['credit_owner_name']) : '';
	$operating_since = isset($_POST['credit_operating_since']) ? hesk_input($_POST['credit_operating_since']) : '';
	$type_of_business = isset($_POST['credit_type_of_business']) ? hesk_input($_POST['credit_type_of_business']) : '';
	$credit_limit = isset($_POST['credit_limit']) ? hesk_input($_POST['credit_limit']) : '';
	$business_structure = isset($_POST['credit_business_structure']) ? hesk_input($_POST['credit_business_structure']) : '';
	$po_issued = isset($_POST['credit_po_issued']) ? hesk_input($_POST['credit_po_issued']) : '';
	$application_for = isset($_POST['credit_application_for']) ? hesk_input($_POST['credit_application_for']) : '';
	$tax_id = isset($_POST['credit_tax_id']) ? hesk_input($_POST['credit_tax_id']) : '';
	$bank_name = isset($_POST['credit_bank_name']) ? hesk_input($_POST['credit_bank_name']) : '';
	$bank_address = isset($_POST['credit_bank_address']) ? hesk_input($_POST['credit_bank_address']) : '';
	$applicant_name = isset($_POST['credit_applicant_name']) ? hesk_input($_POST['credit_applicant_name']) : '';
	$sig_data = isset($_POST['signature_data']) && !empty($_POST['signature_data']) ? $_POST['signature_data'] : (isset($_POST['credit_typed_signature']) ? hesk_input($_POST['credit_typed_signature']) : '');
	$app_date = isset($_POST['credit_date']) && !empty($_POST['credit_date']) ? hesk_input($_POST['credit_date']) : date('Y-m-d');

	$insertAppSql = "INSERT INTO `" . hesk_dbEscape($hesk_settings['db_pfix']) . "credit_applications` (
		`ticket_id`, `category_type`, `dealerships`, `company_name`, `street_address`,
		`postal_zip_code`, `phone`, `statement_email`, `owner_name`, `operating_since`,
		`type_of_business`, `credit_limit`, `business_structure`, `po_issued`,
		`application_for`, `tax_id`, `bank_name`, `bank_address`, `applicant_name`,
		`signature_data`, `application_date`
	) VALUES (
		'" . hesk_dbEscape($ticket['trackid']) . "',
		'" . hesk_dbEscape($cat_type) . "',
		'" . hesk_dbEscape($dealerships_str) . "',
		'" . hesk_dbEscape($company_name) . "',
		'" . hesk_dbEscape($street_address) . "',
		'" . hesk_dbEscape($postal_zip) . "',
		'" . hesk_dbEscape($phone) . "',
		'" . hesk_dbEscape($stmt_email) . "',
		'" . hesk_dbEscape($owner_name) . "',
		'" . hesk_dbEscape($operating_since) . "',
		'" . hesk_dbEscape($type_of_business) . "',
		'" . hesk_dbEscape($credit_limit) . "',
		'" . hesk_dbEscape($business_structure) . "',
		'" . hesk_dbEscape($po_issued) . "',
		'" . hesk_dbEscape($application_for) . "',
		'" . hesk_dbEscape($tax_id) . "',
		'" . hesk_dbEscape($bank_name) . "',
		'" . hesk_dbEscape($bank_address) . "',
		'" . hesk_dbEscape($applicant_name) . "',
		'" . hesk_dbEscape($sig_data) . "',
		'" . hesk_dbEscape($app_date) . "'
	)";
	hesk_dbQuery($insertAppSql);
	$credit_app_id = hesk_dbInsertID();

	if (isset($_POST['credit_ref_name']) && is_array($_POST['credit_ref_name'])) {
		for ($r = 0; $r < count($_POST['credit_ref_name']); $r++) {
			$ref_name = hesk_input($_POST['credit_ref_name'][$r]);
			$ref_addr = isset($_POST['credit_ref_address'][$r]) ? hesk_input($_POST['credit_ref_address'][$r]) : '';
			$ref_phone = isset($_POST['credit_ref_phone'][$r]) ? hesk_input($_POST['credit_ref_phone'][$r]) : '';
			$ref_contact = isset($_POST['credit_ref_contact'][$r]) ? hesk_input($_POST['credit_ref_contact'][$r]) : '';
			if (!empty($ref_name) || !empty($ref_addr) || !empty($ref_phone) || !empty($ref_contact)) {
				$refSql = "INSERT INTO `" . hesk_dbEscape($hesk_settings['db_pfix']) . "credit_references` (
					`ticket_id`, `credit_app_id`, `reference_num`, `business_name`, `address`, `phone`, `contact_person`
				) VALUES (
					'" . hesk_dbEscape($ticket['trackid']) . "',
					" . intval($credit_app_id) . ",
					" . intval($r + 1) . ",
					'" . hesk_dbEscape($ref_name) . "',
					'" . hesk_dbEscape($ref_addr) . "',
					'" . hesk_dbEscape($ref_phone) . "',
					'" . hesk_dbEscape($ref_contact) . "'
				)";
				hesk_dbQuery($refSql);
			}
		}
	}
}

// Notify the customer about the ticket?
/*
if ($notify && $email_available)
{
    if ($tmpvar['status'] == 3) {
        hesk_notifyCustomer('ticket_closed');
    } else {
        hesk_notifyCustomer('new_ticket_by_staff');
    }
}
*/
if ($notify_by_me == 1 && $email_available) {
	hesk_notifyCustomer('new_ticket_by_staff');
}

if ($notify_category_users == 1) {
	//hesk_notifyStaffCategory('new_ticket_staff', " `id` != ".intval($_SESSION['id'])." AND `notify_category_users_when_request_created` = '1' ");
}

// If ticket is assigned to someone notify them?
/*
if ($ticket['owner'] && $ticket['owner'] != intval($_SESSION['id']))
{
	// If we don't have info from auto-assign get it from database
    if ( ! isset($autoassign_owner['email']) )
    {
		hesk_notifyAssignedStaff(false, 'ticket_assigned_to_you');
	}
    else
    {
		hesk_notifyAssignedStaff($autoassign_owner, 'ticket_assigned_to_you');
    }
}

// Ticket unassigned, notify everyone that selected to be notified about unassigned tickets
elseif ( ! $ticket['owner'])
{
	hesk_notifyStaff('new_ticket_staff', " `id` != ".intval($_SESSION['id'])." AND `notify_new_unassigned` = '1' ");
}
*/

// Unset temporary variables
unset($tmpvar);
hesk_cleanSessionVars('tmpvar');
hesk_cleanSessionVars('as_name');
hesk_cleanSessionVars('as_email');
hesk_cleanSessionVars('as_category');
hesk_cleanSessionVars('as_priority');
hesk_cleanSessionVars('as_status');
hesk_cleanSessionVars('as_subject');
hesk_cleanSessionVars('as_message');
hesk_cleanSessionVars('as_owner');
hesk_cleanSessionVars('as_notify');
hesk_cleanSessionVars('as_show');
hesk_cleanSessionVars('as_due_date');
hesk_cleanSessionVars('as_language');
hesk_cleanSessionVars('as_attachments');
foreach ($hesk_settings['custom_fields'] as $k => $v) {
	if ($v['name'] == "Predictive index completed") {
		continue;
	}
	hesk_cleanSessionVars("as_$k");
}
hesk_cleanSessionVars('as_custom43');

// If ticket has been assigned to the person submitting it lets show a message saying so
if ($ticket['owner'] && $ticket['owner'] == intval($_SESSION['id'])) {
	$hesklang['new_ticket_submitted'] .= '<br />&nbsp;<br />
    <b>' . (isset($autoassign_owner) ? $hesklang['taasy'] : $hesklang['tasy']) . '</b>';
}

// Show the ticket or just the success message
if ($show) {
	hesk_process_messages($hesklang['new_ticket_submitted'], 'admin_ticket.php?track=' . $ticket['trackid'] . '&Refresh=' . mt_rand(10000, 99999), 'SUCCESS');
} else {
	$link = hesk_checkPermission('can_view_tickets', 0) ? '<a href="admin_ticket.php?track=' . $ticket['trackid'] . '&Refresh=' . mt_rand(10000, 99999) . '">' . $hesklang['view_ticket'] . '</a>' : '';
	hesk_process_messages($hesklang['new_ticket_submitted'] . '. ' . $link, 'new_ticket.php', 'SUCCESS');
}
