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

// Auto-focus first empty or error field
define('AUTOFOCUS', true);

/* Get all the required files and functions */
require(HESK_PATH . 'hesk_settings.inc.php');
require(HESK_PATH . 'inc/common.inc.php');
require(HESK_PATH . 'inc/admin_functions.inc.php');
hesk_load_database_functions();

hesk_session_start();
hesk_dbConnect();
hesk_isLoggedIn();

// Load custom fields
require_once(HESK_PATH . 'inc/custom_fields.inc.php');

// Load statuses
require_once(HESK_PATH . 'inc/statuses.inc.php');

// Load calendar JS and CSS
define('CALENDAR', 1);
define('ATTACHMENTS', 1);

if ($hesk_settings['staff_ticket_formatting'] == 2) {
    define('WYSIWYG', 1);
}

$hesk_settings['datepicker'] = array();

// Pre-populate fields

// First, reset data if any query string value is present
if (
    isset($_REQUEST['name']) ||
    isset($_REQUEST['dealership']) ||
    isset($_REQUEST['items_options']) ||
    isset($_REQUEST['email']) ||
    isset($_REQUEST['first_tag']) ||
    isset($_REQUEST['second_tag']) ||
    isset($_REQUEST['priority']) ||
    isset($_REQUEST['status']) ||
    isset($_REQUEST['subject']) ||
    isset($_REQUEST['message']) ||
    isset($_REQUEST['due_date']) ||
    isset($_REQUEST['ticket_language'])
) {
    hesk_new_ticket_reset_data();
}

foreach ($hesk_settings['custom_fields'] as $k => $v) {
    if ($v['use'] && isset($_REQUEST[$k])) {
        hesk_new_ticket_reset_data();
    }
}

// Customer name
if (isset($_REQUEST['name'])) {
    $_SESSION['as_name'] = $_REQUEST['name'];
}

if (isset($_REQUEST['dealership'])) {
    $_SESSION['as_dealership'] = $_REQUEST['dealership'];
}

// Customer email address
if (isset($_REQUEST['email'])) {
    $_SESSION['as_email']  = $_REQUEST['email'];
    $_SESSION['as_email2'] = $_REQUEST['email'];
}



// Category ID
if (isset($_REQUEST['catid'])) {
    $_SESSION['as_category'] = intval($_REQUEST['catid']);
}
if (isset($_REQUEST['category'])) {
    $_SESSION['as_category'] = intval($_REQUEST['category']);
}

if (isset($_REQUEST['first_tag'])) {
    $_SESSION['as_first_tag'] = intval($_REQUEST['first_tag']);
}
if (isset($_REQUEST['second_tag'])) {
    $_SESSION['as_second_tag'] = intval($_REQUEST['second_tag']);
}



// Priority
if (isset($_REQUEST['priority'])) {
    $_SESSION['as_priority'] = intval($_REQUEST['priority']);
}

// Items options
if (isset($_REQUEST['items_options'])) {
    $_SESSION['as_items_options'] = $_REQUEST['items_options'];
}

// Status
if (isset($_REQUEST['status'])) {
    $_SESSION['as_status'] = intval($_REQUEST['status']);
}

// Subject
/*if (isset($_REQUEST['subject'])) {
	$_SESSION['as_subject'] = $_REQUEST['subject'];
}*/

// Message
if (isset($_REQUEST['message'])) {
    $_SESSION['as_message'] = $_REQUEST['message'];
}

// Add session variable handling for new fields at the top
if (isset($_REQUEST['capex_person'])) {
    $_SESSION['as_capex_person'] = $_REQUEST['capex_person'];
}
if (isset($_REQUEST['is_replacement'])) {
    $_SESSION['as_is_replacement'] = intval($_REQUEST['is_replacement']);
}
if (isset($_REQUEST['replacement_person'])) {
    $_SESSION['as_replacement_person'] = $_REQUEST['replacement_person'];
}
if (isset($_REQUEST['asset_tag'])) {
    $_SESSION['as_asset_tag'] = $_REQUEST['asset_tag'];
}


// Custom fields
foreach ($hesk_settings['custom_fields'] as $k => $v) {
    if ($v['use'] && isset($_REQUEST[$k])) {
        $_SESSION['as_' . $k] = $_REQUEST[$k];
    }
}
//echo "<pre>";print_r($_SESSION);
// Due date
$can_due_date = hesk_checkPermission('can_due_date', 0);
if ($can_due_date && isset($_REQUEST['due_date'])) {
    // Should be in one of valid formats
    // - in the datepicker format
    if (($dd = hesk_datepicker_get_date($_REQUEST['due_date']))) {
        $_SESSION['as_due_date'] = $_REQUEST['due_date'];
        $hesk_settings['datepicker']['#due_date']['timestamp'] = $dd->getTimestamp();
    }
    // - in a valid datetime format: https://www.php.net/manual/en/datetime.formats.date.php
    else {
        try {
            $current_date = new DateTime($_REQUEST['due_date']);
            $hesk_settings['datepicker']['#due_date']['timestamp'] = $current_date->getTimestamp();
            $_REQUEST['due_date'] = hesk_datepicker_format_date($current_date->getTimestamp());
            $_SESSION['as_due_date'] = $_REQUEST['due_date'];
        } catch (Exception $e) {
            $_SESSION['HESK_2ND_NOTICE']  = true;
            $_SESSION['HESK_2ND_MESSAGE'] = $hesklang['epdd'] . ' ' . $e->getMessage();
        }
    }
}

// Ticket language
if (isset($_REQUEST['ticket_language'])) {
    $_SESSION['as_language'] = $_REQUEST['ticket_language'];
}

/* Varibles for coloring the fields in case of errors */
if (!isset($_SESSION['iserror'])) {
    $_SESSION['iserror'] = array();
}

if (!isset($_SESSION['isnotice'])) {
    $_SESSION['isnotice'] = array();
}

/* Print header */
require_once(HESK_PATH . 'inc/header.inc.php');

/* Print admin navigation */
require_once(HESK_PATH . 'inc/show_admin_nav.inc.php');

// Get categories
$hesk_settings['categories'] = array();

if (hesk_checkPermission('can_submit_any_cat', 0)) {
    $res = hesk_dbQuery("SELECT `id`, `name`, `priority` FROM `" . hesk_dbEscape($hesk_settings['db_pfix']) . "categories` ORDER BY `cat_order` ASC");
} else {
    $res = hesk_dbQuery("SELECT `id`, `name`, `priority` FROM `" . hesk_dbEscape($hesk_settings['db_pfix']) . "categories`  WHERE " . hesk_myCategories('id') . "  OR id IN (8, 9, 10) ORDER BY `cat_order` ASC");
}

while ($row = hesk_dbFetchAssoc($res)) {
    $hesk_settings['categories'][$row['id']] = array('name' => $row['name'], 'priority' => $row['priority']);
}

$number_of_categories = count($hesk_settings['categories']);

if ($number_of_categories == 0) {
    $category = 1;
} elseif ($number_of_categories == 1) {
    $category = current(array_keys($hesk_settings['categories']));
} else {
    $category = isset($_GET['catid']) ? hesk_REQUEST('catid') : hesk_REQUEST('category');

    // Force the customer to select a category?
    if (!isset($hesk_settings['categories'][$category])) {
        return print_select_category($number_of_categories);
    }
}

$is_credit_app_usa = ($category == 9 || (isset($hesk_settings['categories'][$category]['name']) && $hesk_settings['categories'][$category]['name'] == 'Credit Application-USA'));
$is_credit_app_canada = ($category == 10 || (isset($hesk_settings['categories'][$category]['name']) && $hesk_settings['categories'][$category]['name'] == 'Credit Application-CANADA'));
$is_credit_app = ($is_credit_app_usa || $is_credit_app_canada);

// List of users whom this ticket can be assigned to
$admins = array();
$res = hesk_dbQuery("SELECT `id`,`name`,`isadmin`,`categories`,`heskprivileges` FROM `" . hesk_dbEscape($hesk_settings['db_pfix']) . "users` ORDER BY `name` ASC");
while ($row = hesk_dbFetchAssoc($res)) {
    // Is this an administrator?
    if ($row['isadmin']) {
        $admins[$row['id']] = $row['name'];
        continue;
    }

    // Not admin, is user allowed to view tickets?
    if (strpos($row['heskprivileges'], 'can_view_tickets') !== false) {
        // Is user allowed to access this category?
        $cat = substr($row['categories'], 0);
        $row['categories'] = explode(',', $cat);
        if (in_array($category, $row['categories'])) {
            $admins[$row['id']] = $row['name'];
            continue;
        }
    }
}

// Set the default category priority
if (!isset($_SESSION['as_priority'])) {
    $_SESSION['as_priority'] = intval($hesk_settings['categories'][$category]['priority']);
}

// Set the default ticket status
if (!isset($_SESSION['as_status'])) {
    $_SESSION['as_status'] = 0;
}

function hesk_getCatDescription($id)
{
    global $hesk_settings, $hesklang, $priorities;
    $res = hesk_dbQuery("SELECT `description` FROM `" . hesk_dbEscape($hesk_settings['db_pfix']) . "categories` WHERE `id`=" . hesk_dbEscape($id) . " LIMIT 1");
    $row = hesk_dbFetchRow($res);
    return $row[0];
}
?>
<div class="main__content categories ticket-create">


    <div class="table-wrap">

        <?php
        if (!isset($_SESSION['HESK_ERROR'])) { //echo $_GET['category'].'kkk';exit;
            //echo $_SERVER['REQUEST_URI'];

            $exp = explode("?", basename($_SERVER['REQUEST_URI']));
            if (!strpos($_SERVER['REQUEST_URI'], "?")) {
                $direct = "yes";
            }

            if ($exp[0] == "new_ticket.php" && $_SESSION['usertype'] != 'employee') { //echo $_SESSION['usertype'];exit;
                //echo $_SESSION['categories']; echo "<pre>"; print_r($_SESSION);exit; 
                //error_reporting(E_ALL);
                //  echo "<pre>";
                //print_r($_SESSION);exit;
                if ($_SESSION['categories'] != "" && $_SESSION['usertype'] != 'admin') { //echo 2;exit;
                    hesk_show_info(hesk_getCatDescription($_SESSION['categories'][0]), ' ', false);
                } elseif ($_SESSION['categories'] != "" && $_SESSION['usertype'] == 'admin') {
                    hesk_show_info($hesklang['nti3'], ' ', false);
                } else { //echo 3;exit;
                    hesk_show_info(hesk_getCatDescription($_GET['category']), ' ', false);
                }
            } elseif ($direct == "yes") { //echo 2;exit;
                if (count($_SESSION['categories']) == 1) {
                    hesk_show_info(hesk_getCatDescription($_SESSION['categories'][0]), ' ', false);
                } else {
                    hesk_show_info($hesklang['nti3'], ' ', false);
                }
            } else { //echo 2;exit;
                //hesk_show_info($hesklang['nti3'], ' ', false);
                hesk_show_info(hesk_getCatDescription($_SESSION['categories'][0]), ' ', false);
            }
        }

        /* This will handle error, success and notice messages */
        hesk_handle_messages();
        ?>
       <!--  <div class="notification red"> 
  <b>Note:</b> Deanna is on vacation from June 20 – July 7, 2025. She will not approve any requests until she returns.
</div>  -->
        <h3 style="font-size: 1.3rem; margin-top: 10px"><?php echo $hesklang['nti2']; ?></h3>
        <h4><?php echo $hesklang['req_marked_with']; ?> <span class="important">*</span></h4>

        <form method="post" class="form was-validated  <?php echo isset($_SESSION['iserror']) && count($_SESSION['iserror']) ? 'invalid' : ''; ?>" action="admin_submit_ticket.php" name="form1" id="submit-ticket" enctype="multipart/form-data">

            <?php if ($number_of_categories > 1) : ?>
                <div class="form-group" style="margin-bottom: 0px;">
                    <label for="create_name" style="display: inline;">
                        <?php echo $hesklang['category']; ?>:
                    </label>
                    &nbsp; 
                    <button type="button" class="btn btn--blue-border change_category" name="cc-btn" id="cc-btn" title="<?php echo $hesklang['chg_cat']; ?>"><?php echo hesk_getCategoryName($category); ?>
                        &nbsp;
                        <svg class="icon icon-edit">
                            <use xlink:href="../img/sprite.svg#icon-edit"></use>
                        </svg>
                    </button>
                    <input type="hidden" name="change_category" id="change_category" value="<?php echo $category; ?>">
                    <script>
                        $("#cc-btn").click(function() {
                            $("#change_category").val(1);
                            $("#submit-ticket").submit();
                        });
                    </script>
                </div>
            <?php endif; ?>
            <?php //echo is_int($_GET['category']);
            if (isset($_GET['category']) && $_GET['category'] != 7) { //echo 'exit';exit;
            ?>
                <div class="main-group">
                    <div class="form-group">

                        <label for="create_name">
                            <?php echo $hesklang['name']; ?>: <span class="important">*</span>
                        </label>
                        <input type="text" id="create_name" name="name" class="form-control <?php if (in_array('name', $_SESSION['iserror'])) {
                                                                                                echo 'isError';
                                                                                            } ?>" maxlength="50" value="<?php echo stripslashes(hesk_input($_SESSION['name'])); ?>" readonly>


                        <!--<label for="create_dealership">
                    <?php echo "Dealership"; //$hesklang['name']; 
                    ?>: <span class="important">*</span>
                </label>
                <input type="text" id="create_dealership" name="dealership" class="form-control <?php if (in_array('dealership', $_SESSION['iserror'])) {
                                                                                                    echo 'isError';
                                                                                                } ?>" maxlength="50" value="<?php if (isset($_SESSION['as_dealership'])) {
                                                                                                                                                                                                        echo stripslashes(hesk_input($_SESSION['as_dealership']));
                                                                                                                                                                                                    } ?>">
			</div>-->
                    </div>
                    <?php
                    //if(isset($_GET['category']) && is_int($_GET['category']) && $_GET['category'] != 7){
                    ?>
                    <div class="form-group">
                        <label for="email">
                            <?php echo $hesklang['email'] . ':' . ($hesk_settings['require_email'] ? ' <span class="important">*</span>' : ''); ?>
                        </label>
                        <input type="<?php echo ($hesk_settings['multi_eml'] ? 'text' : 'email'); ?>" class="form-control <?php if (in_array('email', $_SESSION['iserror'])) {
                                                                                                                                echo 'isError';
                                                                                                                            } elseif (in_array('email', $_SESSION['isnotice'])) {
                                                                                                                                echo 'isNotice';
                                                                                                                            } ?>" name="email" id="email" readonly maxlength="1000" value="<?php echo stripslashes(hesk_input($_SESSION['email'])); ?>" <?php if ($hesk_settings['detect_typos']) {
                                                                                                                                                                                                                                                                                                                                                                                                echo ' onblur="Javascript:hesk_suggestEmail(\'email\', \'email_suggestions\', 1, 1)"';
                                                                                                                                                                                                                                                                                                                                                                                            } ?>>
                    </div>

                    <div id="email_suggestions"></div>
                </div>
                <div class="main-group">
                    <div class="form-group">
                        <label class="priority <?php if (in_array('priority', $_SESSION['iserror'])) {
                                                    echo 'isErrorStr';
                                                } ?>"><?php echo $hesklang['priority']; ?>: <?php if ($hesk_settings['select_pri']) {
                                                                                                                                                                        echo '<span class="important">*</span>';
                                                                                                                                                                    } ?></label>
                        <div class="dropdown-select center out-close priority">
                            <select name="priority">
                                <?php
                                // Show the "Click to select"?
                                if ($hesk_settings['select_pri']) {
                                    echo '<option value="">' . $hesklang['select'] . '</option>';
                                }
                                ?>
                                <option value="3" <?php if (isset($_SESSION['as_priority']) && $_SESSION['as_priority'] == 3) {
                                                        echo 'selected';
                                                    } ?>><?php echo $hesklang['low']; ?></option>
                                <option value="2" <?php if (isset($_SESSION['as_priority']) && $_SESSION['as_priority'] == 2) {
                                                        echo 'selected';
                                                    } ?>><?php echo $hesklang['medium']; ?></option>
                                <option value="1" <?php if (isset($_SESSION['as_priority']) && $_SESSION['as_priority'] == 1) {
                                                        echo 'selected';
                                                    } ?>><?php echo $hesklang['high']; ?></option>
                              
                            </select>
                        </div>
                    </div>
                    <div class="form-group ts left-alig" style="margin-left: 60px !important;" id="ticket-status-div">
                        <label><?php echo $hesklang['status']; ?>:</label>
                        <div class="dropdown-select center out-close">
                            <select id="status-select" name="status" onchange="hesk_update_status_color(this.value)">
                                <?php echo hesk_get_status_select('', hesk_checkPermission('can_resolve', 0), $_SESSION['as_status']); ?>
                            </select>
                        </div>
                    </div>
                    <?php if ($_GET['category'] == 2) { ?>
                    <div class="form-group ts" id="items-options-div">
                         <label><?php echo "Request Type"; //$hesklang['status']; 
                            ?>:</label>
                         <div class="dropdown-select center out-close">
                        <?php if (isset($_SESSION['as_items_options'])) {

                            //hesk_show_items_option($_SESSION['as_items_options']);
                        }
                        ?>
                        <select id="items-options-select" name="items_options" onchange="hesk_show_items_option(this.value)" required>
                            <option value="0" selected>Select</option>
                            <option value="1" <?php if (isset($_SESSION['as_items_options']) && $_SESSION['as_items_options'] == "1") {
                                                    echo 'selected';
                                                } ?>>IT Request</option>
                            <option value="2" <?php if (isset($_SESSION['as_items_options']) && $_SESSION['as_items_options'] == "2") {
                                                    echo 'selected';
                                                } ?>>Toner Request</option>
                            <option value="3" <?php if (isset($_SESSION['as_items_options']) && $_SESSION['as_items_options'] == "3") {
                                                    echo 'selected';
                                                } ?>>Stationary Request</option>
                            <option value="4" <?php if (isset($_SESSION['as_items_options']) && $_SESSION['as_items_options'] == "4") {
                                                    echo 'selected';
                                                } ?>>Others</option>
                        </select>
                        </div>
                    </div>
                    <?php } ?>
					  
                </div>
                
            <?php
            }
            ?>
           
               
           
            <!-- START CUSTOM BEFORE -->
            <?php if ($_GET['category'] == 4) { ?>
          
                 <!-- <div class="main-group">
                <div class="form-group">
                    <label for="second_tag" style="margin-top: 21px;"> Manufacture :</label>
                    <label for="create_name">First Tag </label>
                    <input type="text" id="first_tag" name="first_tag" class="form-control " maxlength="50" value="">
                </div>
                <div class="form-group">
                    <label for="second_tag" style="margin-top: 44px;"></label>
                    <label for="second_tag" style="margin-top: 21px;">Second Tag<span class="important"></span></label>
                    <input type="text" class="form-control" name="second_tag" id="second_tag" maxlength="1000" value="">
                </div>

            </div> -->
            <?php } ?>
			
            <?php if ($_GET['category'] == 8) { ?>
            <div class="main-group">
                <div class="form-group" style="width:50%">
             
                    <label for="create_name">First Name </label>
                    <input type="text" id="first_name" required name="first_name" class="form-control " maxlength="50" value="">
                </div>
            </div>  
            <div class="main-group">
                <div class="form-group" style="width:50%">
                   
                    <label for="second_tag" >Last Name<span class="important"></span></label>
                    <input type="text" class="form-control" required name="last_name" id="second_tag" maxlength="1000" value="">
                </div>
                </div>  
                <div class="main-group">
                <div class="form-group" style="width:50%">
              
                    <label for="second_tag" >Title<span class="important"></span></label>
                    <input type="text" class="form-control" required name="title" id="second_tag" maxlength="1000" value="">
                </div>
                </div>  
                <div class="main-group">
                <div class="form-group" style="width:50%">
                
                    <label for="second_tag" >Manufacture logo<span class="important"></span></label>
                    <div class="custom-file">
    <input type="file" class="custom-file-input" id="manufacture_logo" name="manufacture_logo" >
    
  </div>
                            </div>
                            </div>  
                               <div class="main-group">
                    <div class="form-group" style="width:50%">

                        <label for="second_tag" class="form-label"> Reason code<span class="important"></span></label>
                        <input type="text" class="form-control"  placeholder="Enter Reason code " required name="reason_code" id="reason_code" maxlength="1000" >
						( 'New Employee', 'Title Change', 'Lost/Replacement' )
                      
                    </div>
                </div>
            <?php } ?>

            <?php if ($is_credit_app) { 
                $canada_dealerships = array(
                    array('name' => 'Countryside Chrysler', 'col' => 1),
                    array('name' => 'Eastway Toyota/Lexus', 'col' => 2),
                    array('name' => 'Jaguar Land Rover of London', 'col' => 3),
                    array('name' => 'Leamington GM', 'col' => 1),
                    array('name' => 'Midtown Honda', 'col' => 2),
                    array('name' => 'Jaguar Land Rover of Windsor', 'col' => 3),
                    array('name' => 'Oakridge Ford', 'col' => 1),
                    array('name' => 'Performance Ford', 'col' => 2),
                    array('name' => 'Overseas Mercedes-Benz', 'col' => 3),
                    array('name' => 'Windsor Mazda', 'col' => 1),
                    array('name' => 'Whiteoak Ford', 'col' => 2),
                    array('name' => 'Overseas BMW', 'col' => 3)
                );
                $usa_dealerships = array(
                    array('name' => 'Audi of Rochester Hills', 'col' => 1),
                    array('name' => 'BMW of Rochester Hills', 'col' => 2),
                    array('name' => 'Mercedes-Benz of North Olmsted', 'col' => 3),
                    array('name' => 'Motor City Mini', 'col' => 1),
                    array('name' => 'Porsche Detroit North', 'col' => 2),
                    array('name' => 'Porsche of North Olmsted', 'col' => 3),
                    array('name' => '', 'col' => 1),
                    array('name' => '', 'col' => 2),
                    array('name' => 'Toyota of Warren', 'col' => 3)
                );
                $active_dealerships = $is_credit_app_usa ? $usa_dealerships : $canada_dealerships;
            ?>
            <style>
            .credit-app-panel {
                background: #ffffff;
                border: 1px solid #d2d6dc;
                border-radius: 8px;
                padding: 24px;
                margin: 20px 0 30px 0;
                box-shadow: 0 4px 14px rgba(0,0,0,0.06);
            }
            .credit-app-header {
                display: flex;
                align-items: center;
                justify-content: space-between;
                border-bottom: 2px solid #0c783c;
                padding-bottom: 15px;
                margin-bottom: 22px;
                flex-wrap: wrap;
                gap: 15px;
            }
            .credit-app-header .logo-area img {
                max-height: 52px;
                width: auto;
                border-radius: 4px;
            }
            .credit-app-header .title-area {
                text-align: right;
            }
            .credit-app-header h2 {
                font-size: 1.8rem;
                font-weight: 700;
                margin: 0;
                color: #1a202c;
                font-family: Georgia, serif;
            }
            .credit-app-header .country-badge {
                display: inline-block;
                background: #0c783c;
                color: #ffffff;
                font-size: 0.8rem;
                font-weight: 600;
                padding: 4px 14px;
                border-radius: 20px;
                margin-top: 5px;
                letter-spacing: 0.5px;
            }
            .credit-dealership-table {
                width: 100%;
                border-collapse: collapse;
                margin-bottom: 24px;
                background: #fbfdfb;
                border: 1.5px solid #2d3748;
            }
            .credit-dealership-table td {
                border: 1px solid #2d3748;
                padding: 10px 14px;
                width: 33.33%;
                vertical-align: middle;
            }
            .credit-dealership-label {
                display: flex;
                align-items: center;
                gap: 10px;
                cursor: pointer;
                font-size: 0.93rem;
                font-weight: 500;
                color: #1a202c;
                margin: 0;
            }
            .credit-dealership-label input[type="checkbox"] {
                width: 18px;
                height: 18px;
                accent-color: #0c783c;
                cursor: pointer;
            }
            .credit-section-heading {
                font-size: 1rem;
                font-weight: 700;
                color: #0c783c;
                text-transform: uppercase;
                letter-spacing: 0.5px;
                margin: 20px 0 12px 0;
                padding-bottom: 6px;
                border-bottom: 1.5px solid #e2e8f0;
            }
            .credit-row {
                display: flex;
                flex-wrap: wrap;
                gap: 16px;
                margin-bottom: 14px;
            }
            .credit-col-12 { flex: 0 0 100%; width: 100%; }
            .credit-col-8 { flex: 0 0 calc(66.666% - 11px); width: calc(66.666% - 11px); }
            .credit-col-6 { flex: 0 0 calc(50% - 8px); width: calc(50% - 8px); }
            .credit-col-4 { flex: 0 0 calc(33.333% - 11px); width: calc(33.333% - 11px); }
            .credit-col-3 { flex: 0 0 calc(25% - 12px); width: calc(25% - 12px); }
            .credit-input-group label {
                display: block;
                font-size: 0.82rem;
                font-weight: 700;
                text-transform: uppercase;
                color: #2d3748;
                margin-bottom: 5px;
            }
            .credit-input-group label span.req {
                color: #e53e3e;
            }
            .credit-input-group input[type="text"],
            .credit-input-group input[type="email"],
            .credit-input-group input[type="date"],
            .credit-input-group select {
                width: 100%;
                height: 40px;
                padding: 6px 12px;
                border: 1px solid #cbd5e0;
                border-radius: 4px;
                font-size: 0.95rem;
                background-color: #fbfdfb;
                box-sizing: border-box;
                transition: border-color 0.2s, box-shadow 0.2s;
            }
            .credit-input-group input:focus,
            .credit-input-group select:focus {
                border-color: #0c783c;
                background-color: #ffffff;
                box-shadow: 0 0 0 3px rgba(12, 120, 60, 0.15);
                outline: none;
            }
            .credit-radio-box {
                background: #f7fafc;
                border: 1px solid #e2e8f0;
                border-radius: 6px;
                padding: 10px 14px;
                display: flex;
                flex-wrap: wrap;
                align-items: center;
                gap: 16px;
            }
            .credit-radio-item {
                display: inline-flex;
                align-items: center;
                gap: 6px;
                font-size: 0.92rem;
                font-weight: 500;
                color: #2d3748;
                cursor: pointer;
                margin: 0;
            }
            .credit-radio-item input[type="radio"] {
                accent-color: #0c783c;
                width: 16px;
                height: 16px;
                cursor: pointer;
            }
            .credit-ref-table {
                width: 100%;
                border-collapse: collapse;
                margin: 12px 0 24px 0;
                background: #ffffff;
                border: 1.5px solid #2d3748;
            }
            .credit-ref-table th {
                background: #f0f4f0;
                color: #1a202c;
                border: 1px solid #2d3748;
                padding: 10px;
                font-size: 0.85rem;
                font-weight: 700;
                text-align: left;
                text-transform: uppercase;
            }
            .credit-ref-table td {
                border: 1px solid #2d3748;
                padding: 6px 8px;
            }
            .credit-ref-table input {
                width: 100%;
                height: 36px;
                padding: 4px 8px;
                border: 1px solid #cbd5e0;
                border-radius: 3px;
                font-size: 0.9rem;
                box-sizing: border-box;
            }
            .credit-ref-table input:focus {
                border-color: #0c783c;
                outline: none;
            }
            .credit-declaration-card {
                background: #fafdfa;
                border: 1.5px solid #a7d3b5;
                border-left: 5px solid #0c783c;
                border-radius: 6px;
                padding: 18px;
                margin: 20px 0 24px 0;
            }
            .credit-declaration-text {
                font-size: 0.86rem;
                line-height: 1.65;
                color: #2d3748;
                margin: 0 0 14px 0;
                text-align: justify;
            }
            .credit-sign-area {
                background: #f9fafb;
                border: 1px solid #e5e7eb;
                border-radius: 6px;
                padding: 18px;
                margin-top: 15px;
            }
            .sig-canvas-container {
                display: inline-block;
                border: 1.5px dashed #0c783c;
                border-radius: 6px;
                background: #ffffff;
                padding: 2px;
            }
            #credit-sig-canvas {
                display: block;
                cursor: crosshair;
                background: #ffffff;
            }
            @media (max-width: 768px) {
                .credit-col-8, .credit-col-6, .credit-col-4, .credit-col-3 {
                    flex: 0 0 100%;
                    width: 100%;
                }
                .credit-dealership-table td {
                    display: block;
                    width: 100%;
                }
            }
            </style>

            <div class="credit-app-panel">
                <!-- Header -->
                <div class="credit-app-header">
                    <div class="logo-area">
                        <img src="<?php echo HESK_PATH; ?>img/rafih-logo.jpg" alt="Rafih Auto Group">
                    </div>
                    <div class="title-area">
                        <h2>Business Credit Application</h2>
                        <span class="country-badge">
                            <?php echo $is_credit_app_usa ? 'UNITED STATES' : 'CANADA'; ?>
                        </span>
                    </div>
                </div>

                <!-- Dealerships Selection Table -->
                <div class="credit-section-heading">
                    Dealerships Requested <span style="font-size: 0.8rem; font-weight: normal; color: #718096;">(Select one or more dealerships)</span>
                </div>
                <table class="credit-dealership-table">
                    <tbody>
                        <?php 
                        $rowCount = ceil(count($active_dealerships) / 3);
                        for ($r = 0; $r < $rowCount; $r++): 
                        ?>
                        <tr>
                            <?php for ($c = 0; $c < 3; $c++): 
                                $idx = $r * 3 + $c;
                                $d = isset($active_dealerships[$idx]) ? $active_dealerships[$idx]['name'] : '';
                            ?>
                            <td>
                                <?php if (!empty($d)): ?>
                                <label class="credit-dealership-label">
                                    <input type="checkbox" name="credit_dealerships[]" value="<?php echo htmlspecialchars($d); ?>">
                                    <span><?php echo htmlspecialchars($d); ?></span>
                                </label>
                                <?php endif; ?>
                            </td>
                            <?php endfor; ?>
                        </tr>
                        <?php endfor; ?>
                    </tbody>
                </table>

                <!-- Company Information -->
                <div class="credit-section-heading">Company Information</div>

                <div class="credit-row">
                    <div class="credit-col-12 credit-input-group">
                        <label for="credit_company_name">NAME OF COMPANY: <span class="req">*</span></label>
                        <input type="text" id="credit_company_name" name="credit_company_name" required maxlength="255" placeholder="Enter full legal company name">
                    </div>
                </div>

                <div class="credit-row">
                    <div class="credit-col-8 credit-input-group">
                        <label for="credit_street_address">STREET ADDRESS: <span class="req">*</span></label>
                        <input type="text" id="credit_street_address" name="credit_street_address" required maxlength="255" placeholder="Street address">
                    </div>
                    <div class="credit-col-4 credit-input-group">
                        <?php if ($is_credit_app_usa): ?>
                        <label for="credit_postal_zip">ZIP CODE: <span class="req">*</span></label>
                        <input type="text" id="credit_postal_zip" name="credit_postal_zip" required maxlength="20" placeholder="e.g. 48307">
                        <?php else: ?>
                        <label for="credit_postal_zip">POSTAL CODE: <span class="req">*</span></label>
                        <input type="text" id="credit_postal_zip" name="credit_postal_zip" required maxlength="20" placeholder="e.g. N8W 1K8">
                        <?php endif; ?>
                    </div>
                </div>

                <div class="credit-row">
                    <div class="credit-col-6 credit-input-group">
                        <label for="credit_phone">PHONE #: <span class="req">*</span></label>
                        <input type="text" id="credit_phone" name="credit_phone" required maxlength="50" placeholder="Telephone number">
                    </div>
                    <div class="credit-col-6 credit-input-group">
                        <label for="credit_statement_email">STATEMENT EMAIL: <span class="req">*</span></label>
                        <input type="email" id="credit_statement_email" name="credit_statement_email" required maxlength="255" placeholder="Email for statements & invoicing">
                    </div>
                </div>

                <div class="credit-row">
                    <div class="credit-col-6 credit-input-group">
                        <label for="credit_owner_name">OWNER NAME: <span class="req">*</span></label>
                        <input type="text" id="credit_owner_name" name="credit_owner_name" required maxlength="255" placeholder="Owner / Principal name">
                    </div>
                    <div class="credit-col-6 credit-input-group">
                        <label for="credit_operating_since">OPERATING SINCE:</label>
                        <input type="text" id="credit_operating_since" name="credit_operating_since" maxlength="100" placeholder="Year or date established">
                    </div>
                </div>

                <div class="credit-row">
                    <div class="credit-col-6 credit-input-group">
                        <label for="credit_type_of_business">TYPE OF BUSINESS:</label>
                        <input type="text" id="credit_type_of_business" name="credit_type_of_business" maxlength="255" placeholder="Nature of business">
                    </div>
                    <div class="credit-col-6 credit-input-group">
                        <label for="credit_limit">CREDIT LIMIT REQUESTED: $</label>
                        <input type="text" id="credit_limit" name="credit_limit" maxlength="50" placeholder="Amount in dollars">
                    </div>
                </div>

                <div class="credit-row">
                    <div class="credit-col-12 credit-input-group">
                        <label>Business Structure (Please select one):</label>
                        <div class="credit-radio-box">
                            <label class="credit-radio-item">
                                <input type="radio" name="credit_business_structure" value="Sole Proprietorship">
                                Sole Proprietorship
                            </label>
                            <label class="credit-radio-item">
                                <input type="radio" name="credit_business_structure" value="Partnership">
                                Partnership
                            </label>
                            <label class="credit-radio-item">
                                <input type="radio" name="credit_business_structure" value="Corporation">
                                Corporation
                            </label>
                            <label class="credit-radio-item">
                                <input type="radio" name="credit_business_structure" value="Incorporated">
                                Incorporated
                            </label>
                        </div>
                    </div>
                </div>

                <div class="credit-row">
                    <div class="credit-col-6 credit-input-group">
                        <label>Are purchase orders issued?</label>
                        <div class="credit-radio-box">
                            <label class="credit-radio-item">
                                <input type="radio" name="credit_po_issued" value="Yes"> Yes
                            </label>
                            <label class="credit-radio-item">
                                <input type="radio" name="credit_po_issued" value="No" checked> No
                            </label>
                        </div>
                    </div>
                    <div class="credit-col-6 credit-input-group">
                        <label>Is this for (Please select one):</label>
                        <div class="credit-radio-box">
                            <label class="credit-radio-item">
                                <input type="radio" name="credit_application_for" value="Parts"> Parts
                            </label>
                            <label class="credit-radio-item">
                                <input type="radio" name="credit_application_for" value="Service"> Service
                            </label>
                            <label class="credit-radio-item">
                                <input type="radio" name="credit_application_for" value="Parts & Service" checked> Parts & Service
                            </label>
                        </div>
                    </div>
                </div>

                <div class="credit-row">
                    <div class="credit-col-4 credit-input-group">
                        <?php if ($is_credit_app_usa): ?>
                        <label for="credit_tax_id">Business Tax ID:</label>
                        <input type="text" id="credit_tax_id" name="credit_tax_id" maxlength="100" placeholder="Federal Tax ID / EIN">
                        <?php else: ?>
                        <label for="credit_tax_id">GST Number:</label>
                        <input type="text" id="credit_tax_id" name="credit_tax_id" maxlength="100" placeholder="GST / HST Registration #">
                        <?php endif; ?>
                    </div>
                    <div class="credit-col-4 credit-input-group">
                        <label for="credit_bank_name">Bank Name:</label>
                        <input type="text" id="credit_bank_name" name="credit_bank_name" maxlength="255" placeholder="Financial Institution">
                    </div>
                    <div class="credit-col-4 credit-input-group">
                        <label for="credit_bank_address">Bank Address:</label>
                        <input type="text" id="credit_bank_address" name="credit_bank_address" maxlength="255" placeholder="Branch Address">
                    </div>
                </div>

                <!-- Credit References -->
                <div class="credit-section-heading">CREDIT REFERENCES</div>
                <table class="credit-ref-table">
                    <thead>
                        <tr>
                            <th style="width: 25%;">Business Name</th>
                            <th style="width: 30%;">Address</th>
                            <th style="width: 20%;">Phone #:</th>
                            <th style="width: 25%;">Controller/Acct. Contact</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php for ($i = 0; $i < 4; $i++): ?>
                        <tr>
                            <td><input type="text" name="credit_ref_name[]" placeholder="Reference <?php echo ($i+1); ?> Business Name"></td>
                            <td><input type="text" name="credit_ref_address[]" placeholder="Street Address, City"></td>
                            <td><input type="text" name="credit_ref_phone[]" placeholder="Phone Number"></td>
                            <td><input type="text" name="credit_ref_contact[]" placeholder="Contact Name"></td>
                        </tr>
                        <?php endfor; ?>
                    </tbody>
                </table>

                <!-- Legal Declaration -->
                <div class="credit-declaration-card">
                    <div class="credit-declaration-text">
                        ***I/We declare and formally request an account with <strong>The Rafih Auto Group</strong> for the dealership(s) requested above. 
                        I declare that I am legally authorized to bind this company on the application for all credit. We warrant and confirm that the information 
                        given herein is true and correct and authorize such information to be used to determine credit. We understand that a 3% per month service 
                        charge is levied on all overdue accounts. Note that credit may be terminated at any time. No credit will be extended on any account over sixty (60) days. 
                        If any payment obligation under this agreement is not paid when due, the Borrower agrees to pay all collection costs, including attorney fees, 
                        court costs as well as any fees/charges resulting from the collection.
                    </div>
                    <label style="display: flex; align-items: center; gap: 8px; font-weight: 600; color: #1a202c; cursor: pointer;">
                        <input type="checkbox" name="credit_declaration_agree" id="credit_declaration_agree" required style="width: 18px; height: 18px; accent-color: #0c783c;">
                        <span>I understand, warrant, and agree to the declaration and terms stated above. <span class="req" style="color: #e53e3e;">*</span></span>
                    </label>
                </div>

                <!-- Applicant Sign-off & Digital Signature -->
                <div class="credit-sign-area">
                    <div class="credit-row">
                        <div class="credit-col-6 credit-input-group">
                            <label for="credit_applicant_name">Name of Applicant: <span class="req">*</span></label>
                            <input type="text" id="credit_applicant_name" name="credit_applicant_name" required maxlength="255" placeholder="Full name of authorized applicant">
                        </div>
                        <div class="credit-col-6 credit-input-group">
                            <label for="credit_date">Date: <span class="req">*</span></label>
                            <input type="date" id="credit_date" name="credit_date" value="<?php echo date('Y-m-d'); ?>" required>
                        </div>
                    </div>

                    <div class="credit-row" style="margin-top: 10px;">
                        <div class="credit-col-12">
                            <label style="font-size: 0.85rem; font-weight: 700; text-transform: uppercase; color: #2d3748; margin-bottom: 6px; display: block;">
                                Applicant Signature: <span class="req" style="color: #e53e3e;">*</span>
                            </label>
                            <div style="display: flex; gap: 20px; flex-wrap: wrap; align-items: flex-start;">
                                <div>
                                    <div class="sig-canvas-container">
                                        <canvas id="credit-sig-canvas" width="420" height="130"></canvas>
                                    </div>
                                    <div style="margin-top: 6px; display: flex; align-items: center; gap: 10px;">
                                        <button type="button" class="btn btn--blue-border btn--small" id="clear-sig-btn" style="padding: 4px 10px; font-size: 11px;">Clear Signature</button>
                                        <span style="font-size: 11px; color: #718096;">Sign with your mouse, trackpad, or touch screen</span>
                                    </div>
                                    <input type="hidden" name="signature_data" id="signature_data" value="">
                                </div>
                                <div style="flex: 1; min-width: 250px;">
                                    <div class="credit-input-group">
                                        <label for="credit_typed_signature" style="text-transform: none; font-size: 0.8rem; color: #4a5568;">Or Type Legal Signature (Full Name):</label>
                                        <input type="text" id="credit_typed_signature" name="credit_typed_signature" placeholder="/s/ Full Name" style="font-family: 'Brush Script MT', cursive, sans-serif; font-size: 1.2rem; height: 42px;">
                                        <span style="font-size: 11px; color: #718096; display: block; margin-top: 4px;">Electronic signature legally binding pursuant to Uniform Electronic Transactions Act (UETA) and ESIGN.</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <script>
            (function() {
                var canvas = document.getElementById('credit-sig-canvas');
                if (!canvas) return;
                var ctx = canvas.getContext('2d');
                var drawing = false;
                var hasDrawn = false;
                ctx.lineWidth = 2.5;
                ctx.lineCap = 'round';
                ctx.lineJoin = 'round';
                ctx.strokeStyle = '#1a365d';

                function getPos(e) {
                    var rect = canvas.getBoundingClientRect();
                    var clientX = e.clientX;
                    var clientY = e.clientY;
                    if (e.touches && e.touches.length > 0) {
                        clientX = e.touches[0].clientX;
                        clientY = e.touches[0].clientY;
                    }
                    return {
                        x: (clientX - rect.left) * (canvas.width / rect.width),
                        y: (clientY - rect.top) * (canvas.height / rect.height)
                    };
                }

                function startDrawing(e) {
                    e.preventDefault();
                    drawing = true;
                    hasDrawn = true;
                    var p = getPos(e);
                    ctx.beginPath();
                    ctx.moveTo(p.x, p.y);
                }

                function draw(e) {
                    if (!drawing) return;
                    e.preventDefault();
                    var p = getPos(e);
                    ctx.lineTo(p.x, p.y);
                    ctx.stroke();
                }

                function stopDrawing(e) {
                    if (!drawing) return;
                    drawing = false;
                    document.getElementById('signature_data').value = canvas.toDataURL('image/png');
                }

                canvas.addEventListener('mousedown', startDrawing);
                canvas.addEventListener('mousemove', draw);
                canvas.addEventListener('mouseup', stopDrawing);
                canvas.addEventListener('mouseleave', stopDrawing);

                canvas.addEventListener('touchstart', startDrawing, { passive: false });
                canvas.addEventListener('touchmove', draw, { passive: false });
                canvas.addEventListener('touchend', stopDrawing, { passive: false });

                document.getElementById('clear-sig-btn').addEventListener('click', function() {
                    ctx.clearRect(0, 0, canvas.width, canvas.height);
                    document.getElementById('signature_data').value = '';
                    hasDrawn = false;
                });

                // Validate form submission
                var form = document.getElementById('submit-ticket');
                if (form) {
                    form.addEventListener('submit', function(e) {
                        // Check at least one dealership is checked
                        var dealerships = form.querySelectorAll('input[name="credit_dealerships[]"]:checked');
                        if (dealerships.length === 0) {
                            e.preventDefault();
                            alert('Please select at least one Dealership for the Credit Application.');
                            return false;
                        }
                        // Check declaration agreed
                        var decl = document.getElementById('credit_declaration_agree');
                        if (decl && !decl.checked) {
                            e.preventDefault();
                            alert('Please acknowledge and agree to the credit declaration terms.');
                            decl.focus();
                            return false;
                        }
                        // Check signature
                        var sigData = document.getElementById('signature_data').value;
                        var typedSig = document.getElementById('credit_typed_signature').value.trim();
                        if (!hasDrawn && !typedSig) {
                            e.preventDefault();
                            alert('Please provide an applicant signature (draw on canvas or type full name).');
                            return false;
                        }
                        if (hasDrawn) {
                            document.getElementById('signature_data').value = canvas.toDataURL('image/png');
                        }
                    });
                }
            })();
            </script>
            <?php } ?>
			
            <?php
            if (!$is_credit_app) {
                //echo "<pre>";print_r($hesk_settings['custom_fields']);
                foreach ($hesk_settings['custom_fields'] as $k => $v) {
                if ($v['name'] == "Predictive index completed") {
                    continue;
                }
                
                if ($k == 'custom22' && $category == 6) {
                    $v['req'] = $v['req'] == 2 ? '<span class="important">*</span>' : '';
                    
                    echo '
                        <div class="form-group">
                            <label for="custom43">Current Pay Plan ' . $v['req'] . '</label>
                            <input type="text" name="custom43" id="custom43" class="form-control" maxlength="255" value="' . (isset($_SESSION['as_custom43']) ? stripslashes(hesk_input($_SESSION['as_custom43'])) : 'N/A') . '">
                        </div>';
                    
                    echo '
                        <div class="form-group">
                            <label for="custom22">Proposed Pay Plan ' . $v['req'] . '</label>
                            <input type="text" name="custom22" id="custom22" class="form-control" maxlength="255" value="' . (isset($_SESSION['as_custom22']) ? stripslashes(hesk_input($_SESSION['as_custom22'])) : '') . '">
                        </div>';
                    continue;
                }
				
                if ($v['use'] && $v['place'] == 0 && hesk_is_custom_field_in_category($k, $category)) {
                    $v['req'] = $v['req'] == 2 ? '<span class="important">*</span>' : '';

                    if ($v['type'] == 'checkbox') {
                        $k_value = array();
                        if (isset($_SESSION["as_$k"]) && is_array($_SESSION["as_$k"])) {
                            foreach ($_SESSION["as_$k"] as $myCB) {
                                $k_value[] = stripslashes(hesk_input($myCB));
                            }
                        }
                    } elseif (isset($_SESSION["as_$k"])) {
                        $k_value  = stripslashes(hesk_input($_SESSION["as_$k"]));
                    } else {
                        $k_value  = '';
                    }
                    $readonly = "";
                    switch ($v['type']) {
                            /* Radio box */
                        case 'radio':
                            $cls = in_array($k, $_SESSION['iserror']) ? 'isError' : '';

                            echo '
                                <div class="form-group ' . $cls . '">
                                    <label>' . $v['name:'] . ' ' . $v['req'] . '</label>
                                    <div class="radio-list">';

                            $index = 0;
                            foreach ($v['value']['radio_options'] as $option) {
                                if (strlen($k_value) == 0) {
                                    $k_value = $option;
                                    $checked = empty($v['value']['no_default']) ? 'checked' : '';
                                } elseif ($k_value == $option) {
                                    $k_value = $option;
                                    $checked = 'checked';
                                } else {
                                    $checked = '';
                                }

                                $chkBoxArray = array("Replacement Equipment", "Revenue Generating", "If recommended for lease rather than purchase");

                                if ($_GET['category'] == 3 && in_array($v['name'], $chkBoxArray)) {

                                    echo "
                                            <div class='radio-custom' style='margin-bottom: 5px'>
                                                <input type='radio' onclick=show_attachment('" . str_replace(" ", "", $v['name']) . "','" . $option . "') id=edit_" . $k . $index . " name='" . $k . "' value='" . $option . "' " . $checked . ">
                                                <label for=edit_" . $k . $index . ">" . $option . "</label>
                                            </div>";
                                } else {
                                    echo '
                                            <div class="radio-custom" style="margin-bottom: 5px">
                                                <input type="radio" id="edit_' . $k . $index . '" name="' . $k . '" value="' . $option . '" ' . $checked . '>
                                                <label for="edit_' . $k . $index . '">' . $option . '</label>
                                            </div>';
                                }
                                $index++;
                            }

                            /* $file_upld = "as_file_".str_replace(" ","",$v['name']);
							if(isset($_SESSION[$file_upld])) {
								$file_upld_val = $_SESSION[$file_upld];
							}else{ 
								$file_upld_val = ""; 
							} */

                            echo "<div id='" . str_replace(" ", "", $v['name']) . "' >
							<input type='file' name='file_" . str_replace(" ", "", $v['name']) . "' id=''  >
							</div>";
                            echo '</div>
                                </div>';
                            break;

                            /* Select drop-down box */
                        case 'select':

                            $cls = in_array($k, $_SESSION['iserror']) ? ' class="isError" ' : '';

                            echo '
                                <div class="form-group">
                                    <label for="edit_">' . $v['name:'] . ' ' . $v['req'] . '</label>
                                        <select name="' . $k . '" id="' . $k . '" ' . $cls . '>';
                            // Show "Click to select"?
                            if (!empty($v['value']['show_select'])) {
                                echo '<option value="">' . $hesklang['select'] . '</option>';
                            }

                            if ($k == 'custom29') {
                                $custom29_options = array();
                                $res29 = hesk_dbQuery("SELECT `dealership` FROM `hesk_dealerships` ORDER BY `id`");
                                while ($r29 = hesk_dbFetchAssoc($res29)) {
                                    $custom29_options[] = $r29['dealership'];
                                }
                                if (empty($custom29_options)) {
                                    $custom29_options = $v['value']['select_options'];
                                }
                                foreach ($custom29_options as $option) {
                                    if ($k_value == $option) {
                                        $k_value = $option;
                                        $selected = 'selected';
                                    } else {
                                        $selected = '';
                                    }
                                    echo '<option ' . $selected . '>' . $option . '</option>';
                                }
                            } else {
                                foreach ($v['value']['select_options'] as $option) {
                                    if ($k_value == $option) {
                                        $k_value = $option;
                                        $selected = 'selected';
                                    } else {
                                        $selected = '';
                                    }

                                    echo '<option ' . $selected . '>' . $option . '</option>';
                                }
                            }
                            echo '</select>
                                </div>
                                <script>
                                    $(\'#' . $k . '\').selectize();
                                </script>
                                ';
                            break;

                            /* Checkbox */
                        case 'checkbox':
                            $cls = in_array($k, $_SESSION['iserror']) ? 'isError' : '';

                            echo '
                                <div class="form-group ' . $cls . '">
                                    <label>' . $v['name:'] . ' ' . $v['req'] . '</label>';

                            $index = 0;
                            foreach ($v['value']['checkbox_options'] as $option) {
                                if (in_array($option, $k_value)) {
                                    $checked = 'checked';
                                } else {
                                    $checked = '';
                                }

                                echo '
                                    <div class="checkbox-custom">
                                        <input type="checkbox" id="edit_' . $k . $index . '" name="' . $k . '[]" value="' . $option . '" ' . $checked . '>
                                        <label for="edit_' . $k . $index . '"> ' . $option . '</label>
                                    </div>';
                                $index++;
                            }

                            echo '</div>';
                            break;

                            /* Large text box */
                        case 'textarea':
                            $cls = in_array($k, $_SESSION['iserror']) ? ' isError" ' : '';
                            $k_value = hesk_msgToPlain($k_value, 0, 0);

                            echo '
                                <div class="form-group">
                                    <label>' . $v['name:'] . ' ' . $v['req'] . '</label>
                                    <textarea name="' . $k . '" class="form-control' . $cls . '" style="height: inherit" rows="' . intval($v['value']['rows']) . '" cols="' . intval($v['value']['cols']) . '" >' . $k_value . '</textarea>
                                </div>';
                            break;

                            // Date
                        case 'date':
                            $cls = in_array($k, $_SESSION['iserror']) ? 'isErrorStr' : '';
                            if (is_string($k_value) && ($dd = hesk_datepicker_get_date($k_value))) {
                                $hesk_settings['datepicker']['#' . $k]['timestamp'] = $dd->getTimestamp();
                            }
                            echo '
                                <section class="param calendar">
                                    <label class="' . $cls . '">' . $v['name:'] . ' ' . $v['req'] . '</label>
                                    <div class="calendar--button">
                                        <button type="button">
                                            <svg class="icon icon-calendar">
                                                <use xlink:href="' . HESK_PATH . 'img/sprite.svg#icon-calendar"></use>
                                            </svg>
                                        </button>
                                        <input name="' . $k . '" id="' . $k . '"
                                               value="' . $k_value . '"
                                               type="text" class="datepicker">
                                    </div>
                                    <div class="calendar--value" ' . ($k_value ? 'style="display: block"' : '') . '>
                                        <span class="' . ($cls && !empty($k_value) ? $cls : '') . '"><i>' . $k_value . '</i></span>
                                        <i class="close">
                                            <svg class="icon icon-close">
                                                <use xlink:href="' . HESK_PATH . 'img/sprite.svg#icon-close"></use>
                                            </svg>
                                        </i>
                                    </div>
                                </section>';
                            break;

                            // Email
                        case 'email':
                            $cls = in_array($k, $_SESSION['iserror']) ? 'isError' : '';

                            $suggest = $hesk_settings['detect_typos'] ? 'onblur="Javascript:hesk_suggestEmail(\'' . $k . '\', \'' . $k . '_suggestions\', 0, 1' . ($v['value']['multiple'] ? ',1' : '') . ')"' : '';

                            echo '
                                <div class="form-group">
                                    <label>' . $v['name:'] . ' ' . $v['req'] . '</label>
                                    <input class="form-control ' . $cls . '" type="' . ($v['value']['multiple'] ? 'text' : 'email') . '" name="' . $k . '" id="' . $k . '" value="' . $k_value . '" size="40" ' . $suggest . '>
                                </div>
                                <div id="' . $k . '_suggestions"></div>';
                            break;

                            // Hidden
                            // Handle as text fields for staff

                            /* Default text input */
                        default:
                            if ($v['name'] == "Dealership") {
                                $v['value']['default_value'] = $_SESSION['dealership'];
                                $readonly = "readonly";
                            } elseif ($v['name'] == "Department") {
                                $v['value']['default_value'] = $_SESSION['department'];
                                $readonly = "readonly";
                            } else {
                                if (strlen($k_value) != 0 || isset($_SESSION["as_$k"])) {
                                    $v['value']['default_value'] = $k_value;
                                    $readonly = "";
                                }
                            }


                            $cls = in_array($k, $_SESSION['iserror']) ? 'isError' : '';

                              
                            // Original code restricted Dealership dropdown to category 6 only:
                            // if (isset($_SESSION['as_category']) && $_SESSION['as_category'] == 6 && $v['name'] == "Dealership") {
                            if ($v['name'] == "Dealership") {

                                $dealerships = array();
                                if (!empty($_SESSION['isadmin'])) {
                                    $res = hesk_dbQuery("SELECT * FROM `".hesk_dbEscape($hesk_settings['db_pfix'])."dealerships` ");
                                    while ($row=hesk_dbFetchAssoc($res)) {
                                        $dealerships[] = $row['dealership'];
                                    }
                                } else {
                                    if (!empty($_SESSION['dealerships'])) {
                                        $dealerships = explode(',', $_SESSION['dealerships']);
                                        $dealerships = array_map('trim', $dealerships);
                                    } elseif (!empty($_SESSION['dealership'])) {
                                        $dealerships[] = $_SESSION['dealership'];
                                    }
                                }

                                //$dealership_query = hesk_dbQuery("SELECT id,dealerships as name FROM `" . hesk_dbEscape($hesk_settings['db_pfix']) . "dealerships` ORDER BY dealerships ASC");
                                     
                                     $cls = in_array($k, $_SESSION['iserror']) ? 'isError' : ''; 
                                     
                                     echo '<div class="form-group" style="width:50%">'; 
                                     echo '<label>' . $v['name:'] . ' ' . $v['req'] . '</label>'; 
                                     echo '<select name="' . $k . '" class="form-control ' . $cls . '">'; 
                                     echo '<option value="">Select Dealership</option>'; 
                                     
                                     foreach ($dealerships as $dealership):
                                         $selected = ($dealership == $v['value']['default_value']) ? 'selected="selected"' : '';
                                         $display_name = str_replace('_', ' ', $dealership);
                                         echo '<option value="' . $dealership . '" ' . $selected . '>' . $display_name . '</option>';
                                    endforeach;
                                     
                                     echo '</select></div>';



  }
 else{

                                echo '<div class="form-group" style="width:50%">
                                    <label>' . $v['name:'] . ' ' . $v['req'] . '</label>
                                    <input  ' . $readonly . ' class="form-control ' . $cls . '" type="text" name="' . $k . '" size="40" maxlength="' . intval($v['value']['max_length']) . '" value="' . $v['value']['default_value'] . '" >
                                </div>';

 }

                    }
                }
            }
            ?>
            <!-- END CUSTOM BEFORE -->
			<?php if (isset($_SESSION['as_category']) && $_SESSION['as_category'] == 3): ?>
    <div class="form-group">
        <label for="capex_person"><b>Person this needs to go to:</b></label>
        <input type="text" name="capex_person" class="form-control" 
               value="<?php if (isset($_SESSION['as_capex_person'])) {
                   echo stripslashes(hesk_input($_SESSION['as_capex_person']));
               } ?>">
    </div>
    
    <div class="form-group">
        <label><b>Is this a replacement?</b></label>
        <div class="radio-list">
            <div class="radio-custom">
                <input type="radio" name="is_replacement" id="replacement_yes" value="1" 
                    <?php if (isset($_SESSION['as_is_replacement']) && $_SESSION['as_is_replacement'] == 1) echo 'checked'; ?>>
                <label for="replacement_yes">Yes</label>
            </div>
            <div class="radio-custom">
                <input type="radio" name="is_replacement" id="replacement_no" value="0" 
                    <?php if (!isset($_SESSION['as_is_replacement']) || (isset($_SESSION['as_is_replacement']) && $_SESSION['as_is_replacement'] == 0)) echo 'checked'; ?>>
                <label for="replacement_no">No</label>
            </div>
        </div>
    </div>
    
    <div id="replacement_fields" style="<?php if (!isset($_SESSION['as_is_replacement']) || $_SESSION['as_is_replacement'] == 0) echo 'display:none;'; ?>">
        <div class="form-group">
            <label for="replacement_person"><b>Person assigned to this equipment:</b></label>
            <input type="text" name="replacement_person" class="form-control" 
                   value="<?php if (isset($_SESSION['as_replacement_person'])) {
                       echo stripslashes(hesk_input($_SESSION['as_replacement_person']));
                   } ?>">
        </div>
        
        <div class="form-group">
            <label for="asset_tag"><b>Asset Tag Number:</b></label>
            <input type="text" name="asset_tag" class="form-control" 
                   value="<?php if (isset($_SESSION['as_asset_tag'])) {
                       echo stripslashes(hesk_input($_SESSION['as_asset_tag']));
                   } ?>">
        </div>
    </div>
    
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const replacementRadios = document.querySelectorAll('input[name="is_replacement"]');
        
        replacementRadios.forEach(radio => {
            radio.addEventListener('change', function() {
                document.getElementById('replacement_fields').style.display = 
                    this.value === '1' ? 'block' : 'none';
            });
        });
    });
    </script>
<?php endif; ?>
            <!--
            <?php
            // Lets handle ticket templates
            $can_options = '';

            // Get ticket templates from the database
            $res = hesk_dbQuery("SELECT * FROM `" . hesk_dbEscape($hesk_settings['db_pfix']) . "ticket_templates` ORDER BY `tpl_order` ASC");

            // If we have any templates print them out
            if (hesk_dbNumRows($res)) {
            ?>
                <script language="javascript" type="text/javascript"><!--
                    // -->
            var myMsgTxt = new Array();
            var mySubjectTxt = new Array();
            myMsgTxt[0]='';
            mySubjectTxt[0]='';

            <?php
                while ($mysaved = hesk_dbFetchAssoc($res)) {
                    $can_options .= '<option value="' . $mysaved['id'] . '">' . $mysaved['title'] . "</option>\n";
                    $message_text = $hesk_settings['staff_ticket_formatting'] == 2 ? $mysaved['message_html'] : $mysaved['message'];
                    echo 'myMsgTxt[' . $mysaved['id'] . ']=\'' . preg_replace("/\r?\n|\r/", "\\r\\n' + \r\n'", addslashes($message_text)) . "';\n";
                    echo 'mySubjectTxt[' . $mysaved['id'] . ']=\'' . preg_replace("/\r?\n|\r/", "\\r\\n' + \r\n'", addslashes($mysaved['title'])) . "';\n";
                }

            ?>

            function setMessage(msgid)
            {
            var myMsg=myMsgTxt[msgid];
            var mySubject=mySubjectTxt[msgid];

            if (myMsg == '')
            {
            if (document.form1.mode[1].checked)
            {
            <?php if ($hesk_settings['staff_ticket_formatting'] == 2) : ?>
                tinymce.get("message").setContent('');
            <?php else : ?>
                document.getElementById('message').value = '';
            <?php endif; ?>
            document.getElementById('subject').value = '';
            }
            return true;
            }
            if (document.getElementById)
            {
            if (document.getElementById('moderep').checked)
            {
            <?php if ($hesk_settings['staff_ticket_formatting'] == 2) : ?>
                tinymce.get("message").setContent('');
                tinymce.get("message").execCommand('mceInsertRawHTML', false, myMsg);
            <?php else : ?>
                document.getElementById('HeskMsg').innerHTML='<textarea style="height: inherit" class="form-control" name="message" id="message" rows="12" cols="60">'+myMsg+'</textarea>';
            <?php endif; ?>
            document.getElementById('HeskSub').innerHTML='<input class="form-control" type="text" name="subject" id="subject" maxlength="70" value="'+mySubject+'">';
            }
            else
            {
            <?php if ($hesk_settings['staff_ticket_formatting'] == 2) : ?>
                var oldMsg = tinymce.get("message").getContent();
                tinymce.get("message").setContent('');
                tinymce.get("message").execCommand('mceInsertRawHTML', false, oldMsg + myMsg);
            <?php else : ?>
                var oldMsg = escapeHtml(document.getElementById('message').value);
                document.getElementById('HeskMsg').innerHTML='<textarea style="height: inherit" class="form-control" name="message" id="message" rows="12" cols="60">'+oldMsg+myMsg+'</textarea>';
            <?php endif; ?>
            if (document.getElementById('subject').value == '')
            {
            document.getElementById('HeskSub').innerHTML='<input class="form-control" type="text" name="subject" id="subject" maxlength="70" value="'+mySubject+'">';
            }
            }
            }
            else
            {
            if (document.form1.mode[0].checked)
            {
            document.form1.message.value=myMsg;
            document.form1.subject.value=mySubject;
            }
            else
            {
            var oldMsg = document.form1.message.value;
            document.form1.message.value=oldMsg+myMsg;
            if (document.form1.subject.value == '')
            {
            document.form1.subject.value=mySubject;
            }
            }
            }

            }
            //-->
            </script>
        <?php
            } // END fetchrows

            // Print templates
            if (strlen($can_options)) {
        ?>
		
            <div class="form-group">
                <label>
                    <?php echo $hesklang['ticket_tpl']; ?>
                    <?php echo hesk_checkPermission('can_man_ticket_tpl', 0) ? '(<a class="link" href="manage_ticket_templates.php">' . $hesklang['ticket_tpl_man'] . '</a>)' : ''; ?>
                </label>
                <div class="radio-list">
                    <div class="radio-custom" style="margin-bottom: 5px">
                        <input type="radio" name="mode" id="modeadd" value="1" checked="checked">
                        <label for="modeadd"><?php echo $hesklang['madd']; ?></label>
                    </div>
                    <div class="radio-custom" style="margin-bottom: 5px">
                        <input type="radio" name="mode" id="moderep" value="0">
                        <label for="moderep"><?php echo $hesklang['mrep']; ?></label>
                    </div>
                </div>
            </div>
            <div class="form-group">
                <label><?php echo $hesklang['select_ticket_tpl']; ?>:</label>
                <div class="dropdown-select center out-close">
                    <select name="saved_replies" onchange="setMessage(this.value)">
                        <option value="0"> - <?php echo $hesklang['select_empty']; ?> - </option>
                        <?php echo $can_options; ?>
                    </select>
                </div>
            </div>
        <?php
            } // END printing templates
            elseif (hesk_checkPermission('can_man_ticket_tpl', 0)) {
        ?>
            <div class="form-group">
                <label><a href="manage_ticket_templates.php" class="link"><?php echo $hesklang['ticket_tpl_man']; ?></a></label>
            </div>
        <?php
            }
        ?>

        <?php if ($_GET['category'] == 2) { ?>
            <!-- Add more starts -->

            <!------ Include the above in your HEAD tag ---------->

            <div class="panel panel-default" id="item-option-1" <?php if (isset($_SESSION['as_items_options']) && $_SESSION['as_items_options'] == "1") {
                                                                    echo "style='display:block;'";
                                                                } else {
                                                                    echo "style='display:none;'";
                                                                }  ?>>
                <div class="panel-heading">Add Items Computer</div>
                <div class="panel-body">

                    <div id="education_fields_item_one" style="display:grid">

                    </div>
                    <div class="row">
                        <div class="col-sm-8">
                            <div class="row">

                                <div class="col-sm-3 nopadding">
                                    <div class="form-group">

                                        <select class="form-control <?php if (in_array('computer_type', $_SESSION['iserror'])) {
                                                                        echo 'isError';
                                                                    } ?>" name="computer_type[0]" id="computer_type" style=" height: 40px; background-color: #f1f9ff; font-size:12px; border: solid 1px #0c783c;">
                                            <option value="">Computer Type</option>
                                            <option value="Laptop">Laptop</option>
                                            <option value="Desktop">Desktop</option>
                                            <option value="Workstation">Workstation</option>
                                        </select>
                                    </div>
                                </div>

                                <div class="col-sm-3 nopadding">
                                    <div class="form-group">
                                        <select class="form-control <?php if (in_array('computer_operating_system', $_SESSION['iserror'])) {
                                                                        echo 'isError';
                                                                    } ?>" name="computer_operating_system[0]" id="computer_operating_system" style=" height: 40px; background-color: #f1f9ff; font-size:12px; border: solid 1px #0c783c;">
                                            <option value="">Operating Sys</option>
                                            <option value="Windows">Windows</option>
                                            <option value="Linux">Linux</option>
                                            <option value="Mac">Mac</option>
                                        </select>
                                    </div>
                                </div>

                                <div class="col-sm-3 nopadding">
                                    <div class="form-group">
                                        <input type="text" class="form-control <?php if (in_array('computer_processor', $_SESSION['iserror'])) {
                                                                                    echo 'isError';
                                                                                } ?>" id="computer_processor" name="computer_processor[0]" value="" placeholder="Processor Type">
                                    </div>
                                </div>

                                <div class="col-sm-3 nopadding">
                                    <div class="form-group">
                                        <input type="number" class="form-control <?php if (in_array('computer_ram', $_SESSION['iserror'])) {
                                                                                        echo 'isError';
                                                                                    } ?>" id="computer_ram" name="computer_ram[0]" value="" placeholder="RAM">
                                    </div>
                                </div>

                            </div>

                            <div class="row">
                                <div class="col-sm-3 nopadding">
                                    <div class="form-group">
                                        <input type="text" class="form-control <?php if (in_array('computer_storage', $_SESSION['iserror'])) {
                                                                                    echo 'isError';
                                                                                } ?>" id="computer_storage" name="computer_storage[0]" value="" placeholder="Storage Capacity">
                                    </div>
                                </div>

                                <div class="col-sm-3 nopadding">
                                    <div class="form-group">
                                        <input type="text" class="form-control <?php if (in_array('computer_graphics', $_SESSION['iserror'])) {
                                                                                    echo 'isError';
                                                                                } ?>" id="computer_graphics" name="computer_graphics[0]" value="" placeholder="Graphics Card">
                                    </div>
                                </div>

                                <div class="col-sm-3 nopadding">
                                    <div class="form-group">
                                        <input type="text" class="form-control <?php if (in_array('computer_screen', $_SESSION['iserror'])) {
                                                                                    echo 'isError';
                                                                                } ?>" id="computer_screen" name="computer_screen[0]" value="" placeholder="Screen Size">
                                    </div>
                                </div>

                                <div class="col-sm-3 nopadding">
                                    <div class="form-group">
                                        <input type="number" class="form-control <?php if (in_array('computer_budget', $_SESSION['iserror'])) {
                                                                                        echo 'isError';
                                                                                    } ?>" id="computer_budget" name="computer_budget[0]" value="" placeholder="Budget">
                                    </div>
                                </div>

                            </div>

                            <div class="row">
                                <div class="col-sm-4 nopadding">
                                    <div class="form-group <?php if (in_array('computer_keyboard', $_SESSION['iserror'])) {
                                                                echo 'isError';
                                                            } ?>">Keyboard: <br>
                                        <input type="radio" name="computer_keyboard[0]" value="Yes" id="computer_keyboard" style="margin:0px 10px 3px 0px">Yes
                                        <input type="radio" name="computer_keyboard[0]" value="No" id="computer_keyboard" style="margin:0px 10px 3px 5px">No
                                    </div>
                                </div>

                                <div class="col-sm-4 nopadding">
                                    <div class="form-group <?php if (in_array('computer_mouse', $_SESSION['iserror'])) {
                                                                echo 'isError';
                                                            } ?>">Mouse: <br>
                                        <input type="radio" name="computer_mouse[0]" value="Yes" style="margin:0px 10px 3px 0px">Yes
                                        <input type="radio" name="computer_mouse[0]" value="No" style="margin:0px 10px 3px 5px">No
                                    </div>
                                </div>

                                <div class="col-sm-4 nopadding">
                                    <div class="form-group <?php if (in_array('computer_monitor', $_SESSION['iserror'])) {
                                                                echo 'isError';
                                                            } ?>">Monitor: <br>
                                        <input type="radio" name="computer_monitor[0]" value="Yes" style="margin:0px 10px 3px 0px">Yes
                                        <input type="radio" name="computer_monitor[0]" value="No" style="margin:0px 10px 3px 5px">No
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-sm-4">
                            <div class="row">
                                <div class="col-sm-12 nopadding" style="width:90%">
                                    <div class="form-group">
                                        <div class="input-group">
                                            <textarea class="form-control <?php if (in_array('computer_comments', $_SESSION['iserror'])) {
                                                                                echo 'isError';
                                                                            } ?>" id="computer_comments" name="computer_comments[0]" value="" placeholder="Comments" style=" height: 150px; background-color: #f1f9ff; font-size:12px; border: solid 1px #0c783c;"></textarea>
                                            <!--<input type="text" class="form-control" id="computer_comments" name="computer_comments[]" value="" placeholder="Comments">-->
                                            <div class="input-group-btn">
                                                <button class="btn btn-success addon-btn" type="button" onclick="education_fields_item_one();"> <span class="glyphicon glyphicon-plus" aria-hidden="true"></span> </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="clear"></div>
                        </div>
                    </div>
                </div>
                <div class="panel-footer"><small>Press <span class="glyphicon glyphicon-plus gs"></span> to add another form field :)</small>, <small>Press <span class="glyphicon glyphicon-minus gs"></span> to remove form field :)</small></div>
            </div>

            <div class="panel panel-default" id="item-option-2" <?php if (isset($_SESSION['as_items_options']) && $_SESSION['as_items_options'] == "2") {
                                                                    echo "style='display:block;'";
                                                                } else {
                                                                    echo "style='display:none;'";
                                                                }  ?>>
                <div class="panel-heading">Add Items Toner</div>
                <div class="panel-body">

                    <div id="education_fields_item_two" style="display:grid">

                    </div>
                    <div class="row">
                        <div class="col-sm-12">
                            <div class="row">
                                <div class="col-sm-3 nopadding">
                                    <div class="form-group">
                                        <input type="text" class="form-control <?php if (in_array('printer_make', $_SESSION['iserror'])) {
                                                                                    echo 'isError';
                                                                                } ?>" id="printer_make" name="printer_make[]" value="" placeholder="Make">
                                    </div>
                                </div>

                                <div class="col-sm-3 nopadding">
                                    <div class="form-group">
                                        <input type="text" class="form-control <?php if (in_array('printer_model', $_SESSION['iserror'])) {
                                                                                    echo 'isError';
                                                                                } ?>" id="printer_model" name="printer_model[]" value="" placeholder="Model">
                                    </div>
                                </div>

                                <div class="col-sm-2 nopadding">
                                    <div class="form-group">
                                        <input type="text" class="form-control <?php if (in_array('printer_used_by', $_SESSION['iserror'])) {
                                                                                    echo 'isError';
                                                                                } ?>" id="printer_used_by" name="printer_used_by[]" value="" placeholder="Used BY">
                                    </div>
                                </div>

                                <div class="col-sm-2 nopadding">
                                    <div class="form-group">
                                        <input type="text" class="form-control <?php if (in_array('printer_catridge_make', $_SESSION['iserror'])) {
                                                                                    echo 'isError';
                                                                                } ?>" id="printer_catridge_make" name="printer_catridge_make[]" value="" placeholder="Catridge Make">
                                    </div>
                                </div>

                                <div class="col-sm-2 nopadding">
                                    <div class="form-group">
                                        <input type="text" class="form-control <?php if (in_array('printer_catridge_model', $_SESSION['iserror'])) {
                                                                                    echo 'isError';
                                                                                } ?>" id="printer_catridge_model" name="printer_catridge_model[]" value="" placeholder="Catridge Model">
                                    </div>
                                </div>
                            </div>

                            <div class="row">


                                <div class="col-sm-3 nopadding">
                                    <div class="form-group">
                                        <input type="number" class="form-control <?php if (in_array('printer_quantity', $_SESSION['iserror'])) {
                                                                                        echo 'isError';
                                                                                    } ?>" id="printer_quantity" name="printer_quantity[]" value="" placeholder="Quantity">
                                    </div>
                                </div>

                                <div class="col-sm-3 nopadding">
                                    <div class="form-group">
                                        <input type="number" class="form-control <?php if (in_array('printer_budget', $_SESSION['iserror'])) {
                                                                                        echo 'isError';
                                                                                    } ?>" id="printer_budget" name="printer_budget[]" value="" placeholder="Budget">
                                    </div>
                                </div>


                                <div class="col-sm-6">
                                    <div class="row">
                                        <div class="col-sm-11 nopadding">
                                            <div class="form-group">
                                                <div class="input-group">
                                                    <!--<input type="text" class="form-control" id="printer_comments" name="printer_comments[]" value="" placeholder="Comments" style="height:60px">-->
                                                    <textarea class="form-control <?php if (in_array('printer_comments', $_SESSION['iserror'])) {
                                                                                        echo 'isError';
                                                                                    } ?>" id="printer_comments" name="printer_comments[]" value="" placeholder="Comments" style="height:60px"></textarea>
                                                    <div class="input-group-btn">
                                                        <button class="btn btn-success" type="button" onclick="education_fields_item_two();"> <span class="glyphicon glyphicon-plus" aria-hidden="true" style="color:000;"></span> </button>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                            </div>
                        </div>



                    </div>
                    <div class="clear"></div>

                </div>
                <div class="panel-footer"><small>Press <span class="glyphicon glyphicon-plus gs"></span> to add another form field :)</small>, <small>Press <span class="glyphicon glyphicon-minus gs"></span> to remove form field :)</small></div>
            </div>


            <div class="panel panel-default" id="item-option-3" <?php if (isset($_SESSION['as_items_options']) && $_SESSION['as_items_options'] == "3") {
                                                                    echo "style='display:block;'";
                                                                } else {
                                                                    echo "style='display:none;'";
                                                                }  ?>>
                <div class="panel-heading">Add Items Stationary</div>
                <div class="panel-body">

                    <div id="education_fields_item_three" style="display:grid">

                    </div>

                    <div class="col-sm-3 nopadding">
                        <div class="form-group">
                            <input type="text" class="form-control <?php if (in_array('stationary_item', $_SESSION['iserror'])) {
                                                                        echo 'isError';
                                                                    } ?>" id="stationary_item" name="stationary_item[]" value="" placeholder="Item Name">
                        </div>
                    </div>

                    <div class="col-sm-3 nopadding">
                        <div class="form-group">
                            <input type="number" class="form-control <?php if (in_array('stationary_quantity', $_SESSION['iserror'])) {
                                                                            echo 'isError';
                                                                        } ?>" id="stationary_quantity" name="stationary_quantity[]" value="" placeholder="Quantity">
                        </div>
                    </div>

                    <div class="col-sm-3 nopadding">
                        <div class="form-group">
                            <input type="number" class="form-control <?php if (in_array('stationary_budget', $_SESSION['iserror'])) {
                                                                            echo 'isError';
                                                                        } ?>" id="stationary_budget" name="stationary_budget[]" value="" placeholder="Budget">
                        </div>
                    </div>

                    <div class="col-sm-3 nopadding">
                        <div class="form-group">
                            <div class="input-group">
                                <input type="text" class="form-control <?php if (in_array('stationary_remarks', $_SESSION['iserror'])) {
                                                                            echo 'isError';
                                                                        } ?>" id="stationary_remarks" name="stationary_remarks[]" value="" placeholder="Remarks">
                                <div class="input-group-btn">
                                    <button class="btn btn-success" type="button" onclick="education_fields_item_three();"> <span class="glyphicon glyphicon-plus" aria-hidden="true"></span> </button>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="clear"></div>

                </div>
                <div class="panel-footer"><small>Press <span class="glyphicon glyphicon-plus gs"></span> to add another form field :)</small>, <small>Press <span class="glyphicon glyphicon-minus gs"></span> to remove form field :)</small></div>
            </div>

            <div class="panel panel-default" id="item-option-4" <?php if (isset($_SESSION['as_items_options']) && $_SESSION['as_items_options'] == "4") {
                                                                    echo "style='display:block;'";
                                                                } else {
                                                                    echo "style='display:none;'";
                                                                }  ?>>
                <div class="panel-heading">Add Items</div>
                <div class="panel-body">

                    <div id="education_fields">

                    </div>

                    <div class="row">
                        <div class="col-sm-8">
                            <div class="row">
                                <div class="col-sm-6 nopadding">
                                    <div class="form-group">
                                        <input type="text" class="form-control <?php if (in_array('description', $_SESSION['iserror'])) {
                                                                                    echo 'isError';
                                                                                } ?>" id="description" name="description[]" value="" placeholder="Description">
                                    </div>
                                </div>
                                <div class="col-sm-6 nopadding">
                                    <div class="form-group">
                                        <input type="number" class="form-control <?php if (in_array('budget', $_SESSION['iserror'])) {
                                                                                        echo 'isError';
                                                                                    } ?>" id="budget" name="budget[]" value="" placeholder="Budget">
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-sm-6 nopadding">
                                    <div class="form-group">
                                        <input type="number" class="form-control <?php if (in_array('quantity', $_SESSION['iserror'])) {
                                                                                        echo 'isError';
                                                                                    } ?>" id="quantity" name="quantity[]" value="" placeholder="Quantity">
                                    </div>
                                </div>

                                <div class="col-sm-6 nopadding">
                                    <div class="form-group">
                                        <div class="input-group">
                                            <select class="form-control <?php if (in_array('unit', $_SESSION['iserror'])) {
                                                                            echo 'isError';
                                                                        } ?>" name="unit[]">
                                                <option value="Box">Box</option>
                                                <option value="Peice">Peice</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-sm-4">
                            <div class="row">


                                <div class="col-sm-11 nopadding">
                                    <div class="form-group">
                                        <div class="input-group"><textarea style="height: inherit" class="form-control" id="remarks" name="remarks[]" rows="5" cols="30" placeholder="Remarks"></textarea>
                                            <div class="input-group-btn"> <button class="btn btn-success" type="button" onclick="education_fields();"> <span class="glyphicon glyphicon-plus" aria-hidden="true"></span> </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>



                            </div>
                        </div>
                    </div>
                    <div class="clear"></div>

                </div>
                <div class="panel-footer"><small>Press <span class="glyphicon glyphicon-plus gs"></span> to add another form field :)</small>, <small>Press <span class="glyphicon glyphicon-minus gs"></span> to remove form field :)</small></div>
            </div>

            <!-- Add more ends -->
        <?php
            }
        }
        ?>

        <?php if ($_GET['category'] == 4) { ?>
            <!-- Add more starts -->

            <div class="panel panel-default" id="business-cards-panel">
                <div class="panel-heading" style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 8px;">
                    <span style="font-weight: 600; font-size: 14px;">Add Business Cards Details</span>
                    <div>
                        <button type="button" class="btn btn-success btn-xs" onclick="toggleSpreadsheetPasteBox()" style="font-weight: 600; padding: 4px 12px; font-size: 12px;">
                            📋 Paste from Spreadsheet
                        </button>
                    </div>
                </div>
                <div class="panel-body">

                    <!-- Quick Spreadsheet Paste & Auto-fill Tool -->
                    <div id="spreadsheet-paste-box" style="display: none; background: #f0fdf4; border: 1px solid #bbf7d0; border-radius: 6px; padding: 14px; margin-bottom: 20px;">
                        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 8px;">
                            <strong style="color: #166534; font-size: 13px;">
                                📋 Fast Spreadsheet Import (Excel / Google Sheets)
                            </strong>
                            <button type="button" class="close" onclick="toggleSpreadsheetPasteBox(false)" style="color: #166534; opacity: 0.8;">&times;</button>
                        </div>
                        <p style="font-size: 12px; color: #374151; margin-bottom: 10px;">
                            Copy row(s) from your tracking spreadsheet and click <strong>"Read from Clipboard"</strong>, or paste into the box below. Columns are automatically mapped:
                            <br><code style="background: #e2e8f0; color: #1e293b; padding: 2px 5px; font-size: 11px;">First Name | Last Name | Title | Business Phone | Email | Extension | Cell Phone</code>
                        </p>
                        
                        <div style="margin-bottom: 10px; display: flex; gap: 10px; flex-wrap: wrap;">
                            <button type="button" class="btn btn-success btn-sm" onclick="readClipboardAndFill()" style="font-weight: 600;">
                                📋 Read from Clipboard (1-Click)
                            </button>
                            <label style="font-weight: 500; font-size: 12px; display: flex; align-items: center; gap: 5px; margin: 0; cursor: pointer;">
                                <input type="checkbox" id="paste-replace-checkbox" style="margin: 0;"> Replace existing cards
                            </label>
                        </div>

                        <textarea id="spreadsheet-paste-textarea" class="form-control" rows="3" placeholder="Or paste spreadsheet rows here (Ctrl+V / Cmd+V)..." style="font-size: 12px; font-family: monospace; background: #fff;"></textarea>

                        <div style="margin-top: 8px; display: flex; justify-content: flex-end; gap: 8px;">
                            <button type="button" class="btn btn-default btn-sm" onclick="toggleSpreadsheetPasteBox(false)">Cancel</button>
                            <button type="button" class="btn btn-primary btn-sm" onclick="fillFromPasteTextarea()" style="font-weight: 600;">Populate Business Cards</button>
                        </div>
                    </div>

                    <div class="business-card-row static-card-row" style="margin-bottom: 10px;">
                        <div class="col-sm-3 nopadding">
                            <div class="form-group">
                                <input type="text" class="form-control <?php if (in_array('card_name', $_SESSION['iserror'])) {
                                                                            echo 'isError';
                                                                        } ?>" id="card_name" name="card_name[]" value="" placeholder="First Name">
                            </div>
                        </div>

                        <div class="col-sm-3 nopadding">
                            <div class="form-group">
                                <input type="text" class="form-control <?php if (in_array('lastname', $_SESSION['iserror'])) {
                                                                            echo 'isError';
                                                                        } ?>" id="lastname" name="lastname[]" value="" placeholder="Last Name">
                            </div>
                        </div>

                        <div class="col-sm-3 nopadding">
                            <div class="form-group">
                                <input type="text" class="form-control <?php if (in_array('card_title', $_SESSION['iserror'])) {
                                                                            echo 'isError';
                                                                        } ?>" id="card_title" name="card_title[]" value="" placeholder="Title">

                            </div>
                        </div>

                        <div class="col-sm-3 nopadding">
                            <div class="form-group">
                                <input type="text" class="form-control <?php if (in_array('business_phone', $_SESSION['iserror'])) {
                                                                            echo 'isError';
                                                                        } ?>" id="business_phone" name="business_phone[]" value="" placeholder="Business Phone">
                            </div>
                        </div>

                        <div class="col-sm-3 nopadding">
                            <div class="form-group">
                                <input type="text" class="form-control <?php if (in_array('card_email', $_SESSION['iserror'])) {
                                                                            echo 'isError';
                                                                        } ?>" id="card_email" name="card_email[]" value="" placeholder="Email">
                            </div>
                        </div>

                        <div class="col-sm-3 nopadding">
                            <div class="form-group">
                                <input type="text" class="form-control <?php if (in_array('extension', $_SESSION['iserror'])) {
                                                                            echo 'isError';
                                                                        } ?>" id="extension" name="extension[]" value="" placeholder="Extension">
                            </div>
                        </div>

                        <div class="col-sm-3 nopadding">
                            <div class="form-group" style="display:flex;">
                                <input type="text" class="form-control <?php if (in_array('card_cell', $_SESSION['iserror'])) {
                                                                            echo 'isError';
                                                                        } ?>" id="card_cell" name="card_cell[]" value="" placeholder="Cell Phone">
                                <div class="input-group-btn"><button class="btn btn-success" type="button" onclick="education_fields2();" title="Add another business card"> <span class="glyphicon glyphicon-plus" aria-hidden="true"></span> </button>
                                </div>
                            </div>
                        </div>

                        <div class="clear"></div>
                    </div>

                    <div id="education_fields2">

                    </div>

                </div>
                <div class="panel-footer" style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 8px;">
                    <div>
                        <small>💡 <strong>Tip:</strong> You can copy rows directly from Excel or Google Sheets and paste (Ctrl+V / Cmd+V) into the <strong>First Name</strong> field to auto-fill!</small>
                    </div>
                    <div>
                        <small>Press <span class="glyphicon glyphicon-plus gs"></span> to add card, <span class="glyphicon glyphicon-minus gs"></span> to remove card</small>
                    </div>
                </div>
            </div>
            <!-- Add more ends -->

<script>
function showNewTicketToast(msg) {
    var toast = document.getElementById('hesk-new-ticket-toast');
    if (!toast) {
        toast = document.createElement('div');
        toast.id = 'hesk-new-ticket-toast';
        toast.style.cssText = 'position: fixed; bottom: 24px; right: 24px; z-index: 99999; background: #1e293b; color: #fff; padding: 10px 18px; border-radius: 6px; box-shadow: 0 4px 12px rgba(0,0,0,0.15); font-size: 13px; font-weight: 500; display: flex; align-items: center; gap: 8px; transition: opacity 0.2s ease, transform 0.2s ease; opacity: 0; transform: translateY(10px); pointer-events: none;';
        document.body.appendChild(toast);
    }
    toast.innerHTML = '<span style="color:#22c55e;font-size:16px;">✓</span> ' + msg;
    toast.style.opacity = '1';
    toast.style.transform = 'translateY(0)';
    if (window._newTicketToastTimeout) clearTimeout(window._newTicketToastTimeout);
    window._newTicketToastTimeout = setTimeout(function() {
        toast.style.opacity = '0';
        toast.style.transform = 'translateY(10px)';
    }, 3200);
}

function toggleSpreadsheetPasteBox(forceState) {
    var box = document.getElementById('spreadsheet-paste-box');
    if (!box) return;
    if (typeof forceState === 'boolean') {
        box.style.display = forceState ? 'block' : 'none';
    } else {
        box.style.display = box.style.display === 'none' ? 'block' : 'none';
    }
    if (box.style.display === 'block') {
        var ta = document.getElementById('spreadsheet-paste-textarea');
        if (ta) ta.focus();
    }
}

function parseSpreadsheetData(text) {
    if (!text || !text.trim()) return [];
    var lines = text.split(/\r\n|\n|\r/);
    var results = [];
    for (var i = 0; i < lines.length; i++) {
        var line = lines[i].trim();
        if (!line) continue;
        
        var cols = line.indexOf('\t') !== -1 ? line.split('\t') : line.split(',');
        cols = cols.map(function(c) {
            return c.trim().replace(/^["']|["']$/g, '');
        });
        
        // Skip header line if detected
        if (i === 0 && (
            cols[0].toLowerCase().indexOf('first') !== -1 ||
            cols[0].toLowerCase().indexOf('name') !== -1 ||
            cols[0].toLowerCase() === 'fn'
        )) {
            continue;
        }

        var card = {
            card_name: cols[0] || '',
            lastname: cols[1] || '',
            card_title: cols[2] || '',
            business_phone: cols[3] || '',
            card_email: '',
            extension: '',
            card_cell: ''
        };

        if (cols.length >= 7) {
            if (cols[4].indexOf('@') !== -1) {
                card.card_email = cols[4];
                card.extension = cols[5];
            } else if (cols[5].indexOf('@') !== -1) {
                card.extension = cols[4];
                card.card_email = cols[5];
            } else {
                card.card_email = cols[4];
                card.extension = cols[5];
            }
            card.card_cell = cols[6] || '';
        } else if (cols.length === 6) {
            if (cols[4].indexOf('@') !== -1) {
                card.card_email = cols[4];
                card.card_cell = cols[5];
            } else {
                card.card_email = cols[4];
                card.extension = cols[5];
            }
        } else if (cols.length === 5) {
            card.card_email = cols[4];
        }

        results.push(card);
    }
    return results;
}

function getBusinessCardRows() {
    var rows = [];
    var staticRow = document.querySelector('.static-card-row');
    if (staticRow) rows.push(staticRow);
    var dynRows = document.querySelectorAll('#education_fields2 > div.business-card-row');
    dynRows.forEach(function(r) { rows.push(r); });
    return rows;
}

function fillRowWithCardData(rowEl, card) {
    if (!rowEl || !card) return;
    var fn = rowEl.querySelector('input[name="card_name[]"]');
    var ln = rowEl.querySelector('input[name="lastname[]"]');
    var title = rowEl.querySelector('input[name="card_title[]"]');
    var phone = rowEl.querySelector('input[name="business_phone[]"]');
    var email = rowEl.querySelector('input[name="card_email[]"]');
    var ext = rowEl.querySelector('input[name="extension[]"]');
    var cell = rowEl.querySelector('input[name="card_cell[]"]');

    if (fn && card.card_name !== undefined) fn.value = card.card_name;
    if (ln && card.lastname !== undefined) ln.value = card.lastname;
    if (title && card.card_title !== undefined) title.value = card.card_title;
    if (phone && card.business_phone !== undefined) phone.value = card.business_phone;
    if (email && card.card_email !== undefined) email.value = card.card_email;
    if (ext && card.extension !== undefined) ext.value = card.extension;
    if (cell && card.card_cell !== undefined) cell.value = card.card_cell;
}

function populateBusinessCards(cards, replaceExisting) {
    if (!cards || !cards.length) return;
    
    if (replaceExisting) {
        var dynContainer = document.getElementById('education_fields2');
        if (dynContainer) dynContainer.innerHTML = '';
        var staticRow = document.querySelector('.static-card-row');
        if (staticRow) {
            staticRow.querySelectorAll('input').forEach(function(inp) { inp.value = ''; });
        }
    }

    var rows = getBusinessCardRows();
    var startIdx = 0;
    
    if (!replaceExisting) {
        for (var i = 0; i < rows.length; i++) {
            var fn = rows[i].querySelector('input[name="card_name[]"]');
            if (fn && !fn.value.trim()) {
                startIdx = i;
                break;
            }
            if (i === rows.length - 1) {
                startIdx = rows.length;
            }
        }
    }

    for (var c = 0; c < cards.length; c++) {
        var targetIdx = startIdx + c;
        while (rows.length <= targetIdx) {
            education_fields2();
            rows = getBusinessCardRows();
        }
        fillRowWithCardData(rows[targetIdx], cards[c]);
    }

    showNewTicketToast('Successfully populated ' + cards.length + ' business card(s) from spreadsheet data!');
}

function fillFromPasteTextarea() {
    var ta = document.getElementById('spreadsheet-paste-textarea');
    if (!ta || !ta.value.trim()) {
        alert('Please paste spreadsheet data into the text box first.');
        return;
    }
    var cards = parseSpreadsheetData(ta.value);
    if (!cards.length) {
        alert('Could not detect business card data from input.');
        return;
    }
    var replace = document.getElementById('paste-replace-checkbox') ? document.getElementById('paste-replace-checkbox').checked : false;
    populateBusinessCards(cards, replace);
    ta.value = '';
    toggleSpreadsheetPasteBox(false);
}

function readClipboardAndFill() {
    if (navigator.clipboard && navigator.clipboard.readText) {
        navigator.clipboard.readText().then(function(text) {
            if (!text || !text.trim()) {
                alert('Clipboard is empty or does not contain text.');
                return;
            }
            var cards = parseSpreadsheetData(text);
            if (!cards.length) {
                alert('No spreadsheet rows recognized in clipboard.');
                return;
            }
            var replace = document.getElementById('paste-replace-checkbox') ? document.getElementById('paste-replace-checkbox').checked : false;
            populateBusinessCards(cards, replace);
            toggleSpreadsheetPasteBox(false);
        }).catch(function(err) {
            var ta = document.getElementById('spreadsheet-paste-textarea');
            if (ta) ta.focus();
            showNewTicketToast('Please paste (Ctrl+V) into the text box below.');
        });
    } else {
        var ta = document.getElementById('spreadsheet-paste-textarea');
        if (ta) ta.focus();
    }
}

// Global smart paste listener on card fields
document.addEventListener('paste', function(e) {
    var target = e.target;
    if (!target || !target.name) return;
    var name = target.name;
    var validFields = ['card_name[]', 'lastname[]', 'card_title[]', 'business_phone[]', 'card_email[]', 'extension[]', 'card_cell[]'];
    if (validFields.indexOf(name) === -1) return;

    var clipData = (e.clipboardData || window.clipboardData);
    if (!clipData) return;
    var text = clipData.getData('text');
    if (!text) return;

    if (text.indexOf('\t') !== -1 || text.indexOf('\n') !== -1) {
        var cards = parseSpreadsheetData(text);
        if (cards && cards.length > 0) {
            e.preventDefault();
            var parentRow = target.closest('.business-card-row') || target.closest('.form-group');
            var rows = getBusinessCardRows();
            var targetRowIdx = rows.indexOf(parentRow);
            if (targetRowIdx === -1) targetRowIdx = 0;

            for (var c = 0; c < cards.length; c++) {
                var idx = targetRowIdx + c;
                while (rows.length <= idx) {
                    education_fields2();
                    rows = getBusinessCardRows();
                }
                fillRowWithCardData(rows[idx], cards[c]);
            }

            showNewTicketToast('Auto-filled ' + cards.length + ' business card(s) from spreadsheet!');
        }
    }
});
</script>
        <?php
        }
        ?>


        <!--<div class="form-group">
                <label><?php echo $hesklang['subject'] . ': ' . ($hesk_settings['require_subject'] == 1 ? '<span class="important">*</span>' : ''); ?></label>
                <span id="HeskSub"><input class="form-control <?php if (in_array('subject', $_SESSION['iserror'])) {
                                                                    echo 'isError';
                                                                } ?>" type="text" name="subject" id="subject" maxlength="70" value="<?php if (isset($_SESSION['as_subject'])) {
                                                                                                                                                                                                            echo stripslashes(hesk_input($_SESSION['as_subject']));
                                                                                                                                                                                                        } ?>"></span>
            </div>-->
        <?php if ($is_credit_app) { ?>
            <div class="form-group" style="display:none;">
                <textarea name="message" id="message">Credit Application</textarea>
            </div>
        <?php } else { ?>
        <div class="form-group">
            <?php
            if ($_GET['category'] == 6) {
            ?>
                <label><?php echo "Comments/Feedback" . ': ' . ($hesk_settings['require_message'] == 1 ? '<span class="important">*</span>' : ''); ?></label>
            <?php
            } else {
            ?>
                <label><?php echo $hesklang['message'] . ': ' . ($hesk_settings['require_message'] == 1 ? '<span class="important">*</span>' : ''); ?></label>
            <?php
            }
            ?>
            <span id="HeskMsg">
                <textarea style="height: inherit" class="form-control <?php if (in_array('message', $_SESSION['iserror'])) {
                                                                            echo 'isError';
                                                                        } ?>" name="message" id="message" rows="12" cols="60"><?php if (isset($_SESSION['as_message'])) {
                                                                                                                                                                                                    echo stripslashes(hesk_input($_SESSION['as_message']));
                                                                                                                                                                                                } ?></textarea>
            </span>
        </div>
        <?php
        if ($hesk_settings['staff_ticket_formatting'] == 2) {
            hesk_tinymce_init('#message');
        }
        } // end of else from if ($is_credit_app)

        /* custom fields AFTER comments */
        if (!$is_credit_app) {
        foreach ($hesk_settings['custom_fields'] as $k => $v) {
            if ($v['use'] && $v['place'] == 1 && hesk_is_custom_field_in_category($k, $category)) {
                $v['req'] = $v['req'] == 2 ? '<span class="important">*</span>' : '';

                if ($v['type'] == 'checkbox') {
                    $k_value = array();
                    if (isset($_SESSION["as_$k"]) && is_array($_SESSION["as_$k"])) {
                        foreach ($_SESSION["as_$k"] as $myCB) {
                            $k_value[] = stripslashes(hesk_input($myCB));
                        }
                    }
                } elseif (isset($_SESSION["as_$k"])) {
                    $k_value  = stripslashes(hesk_input($_SESSION["as_$k"]));
                } else {
                    $k_value  = '';
                }

                switch ($v['type']) {
                        /* Radio box */
                    case 'radio':
                        echo '
                                <div class="form-group">
                                    <label>' . $v['name:'] . ' ' . $v['req'] . '</label>
                                    <div class="radio-list">';

                        $cls = in_array($k, $_SESSION['iserror']) ? ' class="isError" ' : '';

                        $index = 0;
                        foreach ($v['value']['radio_options'] as $option) {
                            if (strlen($k_value) == 0) {
                                $k_value = $option;
                                $checked = empty($v['value']['no_default']) ? 'checked' : '';
                            } elseif ($k_value == $option) {
                                $k_value = $option;
                                $checked = 'checked';
                            } else {
                                $checked = '';
                            }

                            echo '
                                            <div class="radio-custom" style="margin-bottom: 5px">
                                                <input type="radio" id="edit_' . $k . $index . '" name="' . $k . '" value="' . $option . '" ' . $checked . ' ' . $cls . '>
                                                <label for="edit_' . $k . $index . '">' . $option . '</label>
                                            </div>';
                            $index++;
                        }
                        echo '</div>
                                </div>';
                        break;

                        /* Select drop-down box */
                    case 'select':

                        $cls = in_array($k, $_SESSION['iserror']) ? ' class="isError" ' : '';

                        echo '
                                <div class="form-group">
                                    <label for="edit_">' . $v['name:'] . ' ' . $v['req'] . '</label>
                                        <select name="' . $k . '" id="' . $k . '" ' . $cls . '>';
                        // Show "Click to select"?
                        if (!empty($v['value']['show_select'])) {
                            echo '<option value="">' . $hesklang['select'] . '</option>';
                        }

                        foreach ($v['value']['select_options'] as $option) {
                            if ($k_value == $option) {
                                $k_value = $option;
                                $selected = 'selected';
                            } else {
                                $selected = '';
                            }

                            echo '<option ' . $selected . '>' . $option . '</option>';
                        }
                        echo '</select>
                                </div>
                                <script>
                                    $(\'#' . $k . '\').selectize();
                                </script>
                                ';
                        break;

                        /* Checkbox */
                    case 'checkbox':
                        echo '
                                <div class="form-group">
                                    <label>' . $v['name:'] . ' ' . $v['req'] . '</label>';

                        $cls = in_array($k, $_SESSION['iserror']) ? ' class="isError" ' : '';

                        $index = 0;
                        foreach ($v['value']['checkbox_options'] as $option) {
                            if (in_array($option, $k_value)) {
                                $checked = 'checked';
                            } else {
                                $checked = '';
                            }

                            echo '
                                    <div class="checkbox-custom">
                                        <input type="checkbox" id="edit_' . $k . $index . '" name="' . $k . '[]" value="' . $option . '" ' . $checked . ' ' . $cls . '>
                                        <label for="edit_' . $k . $index . '"> ' . $option . '</label>
                                    </div>';
                            $index++;
                        }

                        echo '</div>';
                        break;

                        /* Large text box */
                    case 'textarea':
                        $cls = in_array($k, $_SESSION['iserror']) ? ' isError" ' : '';
                        $k_value = hesk_msgToPlain($k_value, 0, 0);

                        echo '
                                <div class="form-group">
                                    <label>' . $v['name:'] . ' ' . $v['req'] . '</label>
                                    <textarea name="' . $k . '" class="form-control' . $cls . '" style="height: inherit" rows="' . intval($v['value']['rows']) . '" cols="' . intval($v['value']['cols']) . '" >' . $k_value . '</textarea>
                                </div>';
                        break;

                        // Date
                    case 'date':
                        $cls = in_array($k, $_SESSION['iserror']) ? 'isErrorStr' : '';
                        if (is_string($k_value) && ($dd = hesk_datepicker_get_date($k_value))) {
                            $hesk_settings['datepicker']['#' . $k]['timestamp'] = $dd->getTimestamp();
                        }
                        echo '
                                <section class="param calendar">
                                    <label>' . $v['name:'] . ' ' . $v['req'] . '</label>
                                    <div class="calendar--button">
                                        <button type="button">
                                            <svg class="icon icon-calendar">
                                                <use xlink:href="' . HESK_PATH . 'img/sprite.svg#icon-calendar"></use>
                                            </svg>
                                        </button>
                                        <input name="' . $k . '" id="' . $k . '"
                                               value="' . $k_value . '"
                                               type="text" class="datepicker">
                                    </div>
                                    <div class="calendar--value" ' . ($k_value ? 'style="display: block"' : '') . '>
                                        <span class="' . $cls . '"><i>' . $k_value . '</i></span>
                                        <i class="close">
                                            <svg class="icon icon-close">
                                                <use xlink:href="' . HESK_PATH . 'img/sprite.svg#icon-close"></use>
                                            </svg>
                                        </i>
                                    </div>
                                </section>';
                        break;

                        // Email
                    case 'email':
                        $cls = in_array($k, $_SESSION['iserror']) ? 'isError' : '';

                        $suggest = $hesk_settings['detect_typos'] ? 'onblur="Javascript:hesk_suggestEmail(\'' . $k . '\', \'' . $k . '_suggestions\', 0, 1' . ($v['value']['multiple'] ? ',1' : '') . ')"' : '';

                        echo '
                                <div class="form-group">
                                    <label>' . $v['name:'] . ' ' . $v['req'] . '</label>
                                    <input class="form-control ' . $cls . '" type="' . ($v['value']['multiple'] ? 'text' : 'email') . '" name="' . $k . '" id="' . $k . '" value="' . $k_value . '" size="40" ' . $suggest . '>
                                </div>
                                <div id="' . $k . '_suggestions"></div>';
                        break;

                        // Hidden
                        // Handle as text fields for staff

                        /* Default text input */
                    default:
                        if (strlen($k_value) != 0 || isset($_SESSION["as_$k"])) {
                            $v['value']['default_value'] = $k_value;
                        }

                        $cls = in_array($k, $_SESSION['iserror']) ? 'isError' : '';

                        echo '
                                <div class="form-group">
                                    <label>' . $v['name:'] . ' ' . $v['req'] . '</label>
                                    <input class="form-control ' . $cls . '" type="text" name="' . $k . '" size="40" maxlength="' . intval($v['value']['max_length']) . '" value="' . $v['value']['default_value'] . '">
                                </div>';
                }
            }
        }
        }
        ?>
        <!-- END CUSTOM AFTER -->

        <?php
        /* attachments */

        if ($hesk_settings['attachments']['use']) {
            require(HESK_PATH . 'inc/attachments.inc.php');
        ?>
            <div class="attachments">
                <div class="block--attach">
                    <svg class="icon icon-attach">
                        <use xlink:href="<?php echo HESK_PATH; ?>img/sprite.svg#icon-attach"></use>
                    </svg>
                    <div>
                        <?php if ($_GET['category'] == 6) { ?>
                            <?php echo "Resume, Predictive Index or Other Attachment"; ?>
                        <?php } else { ?>
                            <?php echo $hesklang['attachments']; ?>:
                        <?php } ?>
                    </div>
                </div>
                <?php
                build_dropzone_markup(true);
                display_dropzone_field(HESK_PATH . 'upload_attachment.php', true);
                dropzone_display_existing_files(hesk_SESSION_array('as_attachments'));
                ?>
            </div>
        <?php

        }

        // Admin options
        if (!isset($_SESSION['as_notify'])) {
            $_SESSION['as_notify'] = $_SESSION['notify_customer_new'] ? 1 : 0;
        }
        ?>

        <?php if ($can_due_date) : ?>
            <!--<section class="param calendar">
                <?php
                // Default due date
                $default_due_date_info = hesk_getCategoryDueDateInfo($category);

                $due_date = isset($_SESSION['as_due_date']) ? $_SESSION['as_due_date'] : null;
                if ($due_date && ($dd = hesk_datepicker_get_date($due_date))) {
                    $hesk_settings['datepicker']['#due_date']['timestamp'] = $dd->getTimestamp();
                } elseif ($default_due_date_info !== null && $due_date === null) {
                    $current_date = new DateTime('today midnight');
                    $current_date->add(DateInterval::createFromDateString("+{$default_due_date_info['amount']} {$default_due_date_info['unit']}s"));
                    $hesk_settings['datepicker']['#due_date']['timestamp'] = $current_date->getTimestamp();
                    $due_date = hesk_datepicker_format_date($current_date->getTimestamp());
                }
                ?>
                <label><?php echo $hesklang['due_date']; ?>:</label>
                <div class="calendar--button">
                    <button type="button">
                        <svg class="icon icon-calendar">
                            <use xlink:href="<?php echo HESK_PATH; ?>img/sprite.svg#icon-calendar"></use>
                        </svg>
                    </button>
                    <input name="due_date" id="due_date"
                           value="<?php if (isset($due_date)) {
                                        echo stripslashes(hesk_input($due_date));
                                    } ?>"
                           type="text" class="datepicker">
                </div>
                <div class="calendar--value" style="<?php echo empty($due_date) ? '' : 'display: block'; ?>">
                <span><?php echo isset($due_date) ? stripslashes($due_date) : ''; ?></span>
                <i class="close">
                    <svg class="icon icon-close">
                        <use xlink:href="<?php echo HESK_PATH; ?>img/sprite.svg#icon-close"></use>
                    </svg>
                </i>
                </div>
            </section>-->
            <br>
        <?php endif; ?>
        <?php if ($hesk_settings['can_sel_lang']) : ?>
            <div class="form-group">
                <label for="as_language"><?php echo $hesklang['tlan']; ?>:</label>
                <select name="as_language" id="as_language">
                    <?php
                    if (isset($_SESSION['as_language']) && isset($hesk_settings['languages'][$_SESSION['as_language']])) {
                        $hesk_settings['language_copy'] = $hesk_settings['language'];
                        $hesk_settings['language'] = $_SESSION['as_language'];
                        hesk_listLanguages();
                        $hesk_settings['language'] = $hesk_settings['language_copy'];
                    } else {
                        hesk_listLanguages();
                    }
                    ?>
                </select>
            </div>
            <script>
                $('#as_language').selectize();
            </script>
        <?php endif; ?>
        <?php if (hesk_checkPermission('can_assign_others', 0)) { ?>
            <!-- hidden input to force auto-assign since dropdown is commented out -->
            <?php if ($hesk_settings['autoassign']) { echo '<input type="hidden" name="owner" value="-2">'; } ?>
            <!-- <div class="form-group">
                    <label><?php echo $hesklang['asst2']; ?>:</label>
                        <select name="owner" id="owner-select" <?php if (in_array('owner', $_SESSION['iserror'])) {
                                                                    echo ' class="isError" ';
                                                                } ?>>
                            <option value="-1"> &gt; <?php echo $hesklang['unas']; ?> &lt; </option>
                            <?php

                            if ($hesk_settings['autoassign']) {
                                echo '<option value="-2"> &gt; ' . $hesklang['aass'] . ' &lt; </option>';
                            }

                            $owner = isset($_SESSION['as_owner']) ? intval($_SESSION['as_owner']) : 0;

                            foreach ($admins as $k => $v) {
                                if ($k == $owner) {
                                    echo '<option value="' . $k . '" selected="selected">' . $v . '</option>';
                                } else {
                                    echo '<option value="' . $k . '">' . $v . '</option>';
                                }
                            }
                            ?>
                        </select>
                        <script>
                            $('#owner-select').selectize();
                        </script>
                </div>-->
        <?php
        } elseif (hesk_checkPermission('can_assign_self', 0)) {
            $checked = (!isset($_SESSION['as_owner']) || !empty($_SESSION['as_owner'])) ? 'checked' : '';
        ?>
            <!-- <div class="form-group">
                    <label><?php //echo $hesklang['owner']; 
                            ?></label>
                    <div class="checkbox-custom">
                        <input type="checkbox" id="create_assing_to_self1" name="assing_to_self" value="1" <?php //echo $checked; 
                                                                                                            ?>>
                        <label for="create_assing_to_self1"><?php // echo $hesklang['asss2']; 
                                                            ?></label>
                    </div>
                </div> -->
        <?php
        }
        ?>

        <!--<div class="form-group" style="margin-top: 20px">
                <label><?php //echo $hesklang['addop']; 
                        ?>:</label>
                <div class="checkbox-list">
                    <div class="checkbox-custom">
                        <input type="checkbox" id="create_notify1" name="notify" value="1" <?php //echo empty($_SESSION['as_notify']) ? '' : 'checked'; 
                                                                                            ?>>
                        <label for="create_notify1"><?php //echo $hesklang['seno']; 
                                                    ?></label>
                    </div>
                    <?php //if (hesk_checkPermission('can_view_tickets',0)): 
                    ?>
                    <div class="checkbox-custom">
                        <input type="checkbox" id="create_show1" name="show" value="1" <?php //echo (!isset($_SESSION['as_show']) || !empty($_SESSION['as_show'])) ? 'checked' : ''; 
                                                                                        ?>>
                        <label for="create_show1"><?php // echo $hesklang['otas']; 
                                                    ?></label>
                    </div>
                    <?php //endif; 
                    ?>
                </div>
            </div>-->

        <?php if (defined('HESK_DEMO')) : ?>
            <?php hesk_show_notice(sprintf($hesklang['antdemo'], 'https://www.hesk.com/demo/index.php?a=add')); ?>
            <button class="btn btn-full"><?php echo $hesklang['sub_ticket']; ?></button>
        <?php else : ?>
            <button type="submit" class="btn btn-full"><?php echo $hesklang['sub_ticket']; ?></button>
        <?php endif; ?>
        <input type="hidden" name="token" value="<?php hesk_token_echo(); ?>">
        <input type="hidden" name="category" value="<?php echo $category; ?>">
        </form>
        <p>&nbsp;</p>
        <p>&nbsp;</p>
        <p>&nbsp;</p>
        <p>&nbsp;</p>
        <p>&nbsp;</p>
    </div>
</div>
<?php

hesk_cleanSessionVars('iserror');
hesk_cleanSessionVars('isnotice');

// Clearing it out, otherwise users could delete an attachment, refresh, and it'll "supposedly" be back
hesk_cleanSessionVars('as_attachments');

$hesk_settings['print_status_select_box_jquery'] = true;

require_once(HESK_PATH . 'inc/footer.inc.php');
exit();


/*** START FUNCTIONS ***/


function print_select_category($number_of_categories)
{
    global $hesk_settings, $hesklang;

    // A categoy needs to be selected
    if (isset($_GET['category']) && empty($_GET['category'])) {
        hesk_process_messages($hesklang['sel_app_cat'], 'NOREDIRECT', 'NOTICE');
    }

    /* This will handle error, success and notice messages */
    hesk_handle_messages();
?>
    <div class="main__content categories">
        <?php //echo $number_of_categories;
        // Print a select box if number of categories is large
        if ($number_of_categories > $hesk_settings['cat_show_select']) { //echo 1;
        ?>
            <div class="table-wrap">
                <h2 class="select__title-alt"><?php echo $hesklang['select_category_staff']; ?></h2>
                <form action="new_ticket.php" method="get" class="form">
                    <select class="form-control" name="category" id="select_category">
                        <?php
                        if ($hesk_settings['select_cat']) {
                            echo '<option value="">' . $hesklang['select'] . '</option>';
                        }
                        foreach ($hesk_settings['categories'] as $k => $v) {
                            echo '<option value="' . $k . '">' . $v['name'] . '</option>';
                        }
                        ?>
                    </select>
                    <button style="margin-top: 10px" type="submit" class="btn btn-full"><?php echo $hesklang['c2c']; ?></button>
                </form>
                <script>
                    $(document).ready(function() {
                        $('#select_category').selectize();
                    });
                </script>
            </div>
        <?php
        }
        // Otherwise print quick links
        else {
            //print_r($hesk_settings['categories']);
            //echo $hesk_settings['categories'];
            //echo 2;
        ?>
            <h2 class="select__title"><?php echo $hesklang['select_category_staff']; ?></h2>
            <div class="nav">
                <?php 
                $visible_cat_count = 0;
                foreach ($hesk_settings['categories'] as $cat_item) {
                    if ($cat_item['name'] != 'General') {
                        $visible_cat_count++;
                    }
                }
                foreach ($hesk_settings['categories'] as $k => $v) :
                    
                    if($v['name'] == 'General')
                    continue;
                    ?>
                    <a href="new_ticket.php?a=add&amp;category=<?php echo $k; ?>" class="navlink <?php if ($visible_cat_count > 8) echo "navlink-condensed"; ?>">
                        <div class="icon-in-circle">
                            <svg class="icon icon-chevron-right">
                                <use xlink:href="<?php echo HESK_PATH; ?>img/sprite.svg#icon-chevron-right"></use>
                            </svg>
                        </div>
                        <div>
                            <h5 class="navlink__title"><!--[if IE]> &raquo; <![endif]--><?php echo $v['name']; ?></h5>

                            <label><?php echo hesk_getCatDescription($k); ?></label>

                        </div>
                    </a>
                <?php endforeach; ?>
            </div>
        <?php
        }
        ?>
    </div>
<?php

    hesk_cleanSessionVars('iserror');
    hesk_cleanSessionVars('isnotice');
    hesk_cleanSessionVars('as_priority');

    require_once(HESK_PATH . 'inc/footer.inc.php');
    exit();
} // END print_select_category()


function hesk_new_ticket_reset_data()
{
    global $hesk_settings;

    // Already reset
    if (isset($hesk_settings['POPULATE_DATA_RESET'])) {
        return true;
    }

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
    hesk_cleanSessionVars('as_items_options');
    foreach ($hesk_settings['custom_fields'] as $k => $v) {
        hesk_cleanSessionVars("as_$k");
    }

    $hesk_settings['POPULATE_DATA_RESET'] = true;

    return true;
} // END hesk_new_ticket_reset_data()
?>