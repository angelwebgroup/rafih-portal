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

define('IN_SCRIPT',1);
define('HESK_PATH','../');

/* Get all the required files and functions */
require(HESK_PATH . 'hesk_settings.inc.php');
require(HESK_PATH . 'inc/common.inc.php');
require(HESK_PATH . 'inc/admin_functions.inc.php');
hesk_load_database_functions();

hesk_session_start();
hesk_dbConnect();
hesk_isLoggedIn();

/* Check permissions for this feature */
hesk_checkPermission('can_view_tickets');

/* A security check */
//hesk_token_check();

// Load statuses
require_once(HESK_PATH . 'inc/statuses.inc.php');

/* New status */
$status = intval( hesk_REQUEST('s') );
if ( ! isset($hesk_settings['statuses'][$status]))
{
	hesk_process_messages($hesklang['instat'],'admin_ticket.php?track='.$trackingID.'&Refresh='.mt_rand(10000,99999),'NOTICE');
}

hesk_dbQuery("UPDATE `".hesk_dbEscape($hesk_settings['db_pfix'])."vendors` SET `approved`='1', `approved_by`='".intval($_SESSION['id'])."', `approved_on`=NOW() WHERE `id`='".$_GET['vid']."'");

hesk_process_messages($action,'admin_ticket.php?track='.$_GET['track'].'&Refresh='.rand(10000,99999),'SUCCESS');
