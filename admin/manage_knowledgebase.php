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

/* Get all the required files and functions */
require(HESK_PATH . 'hesk_settings.inc.php');
require(HESK_PATH . 'inc/common.inc.php');
require(HESK_PATH . 'inc/admin_functions.inc.php');
hesk_load_database_functions();

// Check for POST requests larger than what the server can handle
if ($_SERVER['REQUEST_METHOD'] == 'POST' && empty($_POST) && !empty($_SERVER['CONTENT_LENGTH'])) {
    hesk_error($hesklang['maxpost']);
}

// For convenience allow adding at least 3 attachments at once in the KB
if ($hesk_settings['attachments']['max_number'] < 3) {
    $hesk_settings['attachments']['max_number'] = 3;
}

hesk_session_start();
hesk_dbConnect();
hesk_isLoggedIn();

/* Check permissions for this feature */
if (!hesk_checkPermission('can_man_kb', 0)) {
    /* This person can't manage the knowledgebase, but can read it */
    header('Location: knowledgebase_private.php');
    exit();
}

/* Is Knowledgebase enabled? */
if (!$hesk_settings['kb_enable']) {
    hesk_error($hesklang['kbdis']);
}

/* This will tell the header to include WYSIWYG editor Javascript */
define('WYSIWYG', 1);
define('ATTACHMENTS', 1);
if (isset($_POST['Submit']) && $_POST['Submit'] == "Send")
{
    $target_file ="";
	if(isset($_FILES["perk_image"]) && $_FILES["perk_image"]["name"] !=""){

			$t=time();
			$target_dir = "img/perk_image/";
			$file_name=$t.'_'.basename($_FILES["perk_image"]["name"]);
			$target_file = './../'.$target_dir.$file_name ;
			$uploadOk = 1;
			$imageFileType = strtolower(pathinfo($target_file,PATHINFO_EXTENSION));
			move_uploaded_file($_FILES['perk_image']['tmp_name'], $target_file);
	}

	$res = hesk_dbQuery("SELECT * FROM `".hesk_dbEscape($hesk_settings['db_pfix'])."users` WHERE `activated` = 0");

    if (hesk_dbNumRows($res) > 0)
    {
        while ($myuser = hesk_dbFetchAssoc($res))
        {

             $user_id=$myuser['id'];
			//echo "INSERT INTO `".hesk_dbEscape($hesk_settings['db_pfix'])."mail` (`from`,`to`,`subject`,`message`,`image`,`dt`,`read`) VALUES ('".intval($_SESSION['id'])."','222','".hesk_dbEscape($_POST['name'])."','".hesk_dbEscape($_POST['description'])."','".$file_name."',NOW(),'0')";
				hesk_dbQuery("INSERT INTO `".hesk_dbEscape($hesk_settings['db_pfix'])."mail` (`from`,`to`,`subject`,`message`,`image`,`dt`,`read`) VALUES ('".intval($_SESSION['id'])."',$user_id,'".hesk_dbEscape($_POST['name'])."','".hesk_dbEscape($_POST['description'])."','".$file_name."',NOW(),'0')");
	

        }
    }
	
	//echo "INSERT INTO `".hesk_dbEscape($hesk_settings['db_pfix'])."mail` (`from`,`to`,`subject`,`message`,`image`,`dt`,`read`) VALUES ('".intval($_SESSION['id'])."','222','".hesk_dbEscape($_POST['name'])."','".hesk_dbEscape($_POST['description'])."','".$file_name."',NOW(),'0')";
	//hesk_dbQuery("INSERT INTO `".hesk_dbEscape($hesk_settings['db_pfix'])."mail` (`from`,`to`,`subject`,`message`,`image`,`dt`,`read`) VALUES ('".intval($_SESSION['id'])."','235','".hesk_dbEscape($_POST['name'])."','".hesk_dbEscape($_POST['description'])."','".$file_name."',NOW(),'0')");
	
	
	hesk_process_messages($hesklang['m_perk'],'./manage_knowledgebase.php','SUCCESS');

}

// Part of a trick to prevent duplicate article submissions by reloading pages
hesk_cleanSessionVars('article_submitted');

/* Print header */
require_once(HESK_PATH . 'inc/header.inc.php');

/* Print main manage users page */
require_once(HESK_PATH . 'inc/show_admin_nav.inc.php');

/* This will handle error, success and notice messages */
hesk_handle_messages();

// Total number of KB articles
$total_articles = 0;

// Get number of sub-categories for each parent category
$parent = array(0 => 1);
$result = hesk_dbQuery('SELECT `parent`, COUNT(*) AS `num` FROM `' . hesk_dbEscape($hesk_settings['db_pfix']) . 'kb_categories` GROUP BY `parent`');
while ($row = hesk_dbFetchAssoc($result)) {
    $parent[$row['parent']] = $row['num'];
}
$parent_copy = $parent;





//print_r($parent);

// Get Knowledgebase structure
$kb_cat = array();
$result = hesk_dbQuery('SELECT * FROM `' . hesk_dbEscape($hesk_settings['db_pfix']) . 'kb_categories` ORDER BY `parent` ASC, `cat_order` ASC');
while ($cat = hesk_dbFetchAssoc($result)) {
    // Can this category be moved at all?
    if (
        $cat['id'] == 1                  || // Main category cannot be moved
        !isset($parent[$cat['parent']]) || // if the parent category isn't set
        $parent[$cat['parent']] < 2         // Less than 2 articles in category
    ) {
        $cat['move_up']   = false;
        $cat['move_down'] = false;
    } else {
        $cat['move_up']   = true;
        $cat['move_down'] = true;
    }

    $kb_cat[] = $cat;
}

//print_r($kb_cat);

/* Translate main category "Knowledgebase" if needed */
$kb_cat[0]['name'] = $hesklang['kb_text'];

require(HESK_PATH . 'inc/treemenu/TreeMenu.php');
$icon         = 'icon-chevron-right';
$expandedIcon = 'icon-knowledge';
$menu          = new HTML_TreeMenu();

$thislevel = array('0');
$nextlevel = array();
$i = 1;
$j = 1;

if (isset($_SESSION['KB_CATEGORY'])) {
    $selected_catid = intval($_SESSION['KB_CATEGORY']);
} else {
    $selected_catid = 0;
}

while (count($kb_cat) > 0) {

    foreach ($kb_cat as $k => $cat) {

        if (in_array($cat['parent'], $thislevel)) {
            $arrow = ($i - 2) % 10;
            $arrow_colors = array(
                0 => '#5ac05a',
                1 => '#a3a0ff',
                2 => '#ff8184',
                3 => '#e686ff',
                4 => '#e19900',
                5 => '#a9a9a9',
                6 => '#db9696',
                7 => '#b7ab00',
                8 => '#a2602d',
                9 => '#aff600'
            );

            $up = $cat['parent'];
            $my = $cat['id'];
            $type = $cat['type'] ? '*' : '';
            $selected = ($selected_catid == $my) ? 1 : 0;
            $cls = (isset($_SESSION['newcat']) && $_SESSION['newcat'] == $my) ? ' class="kbCatListON"' : '';

            $text = str_replace('\\', '\\\\', '<span id="c_' . $my . '"' . $cls . '><a href="manage_knowledgebase.php?a=manage_cat&catid=' . $my . '">' . $cat['name'] . '</a>') . $type . '</span> (<span class="kb_published">' . $cat['articles'] . '</span>, <span class="kb_private">' . $cat['articles_private'] . '</span>, <span class="kb_draft">' . $cat['articles_draft'] . '</span>) ';                  /* ' */

            $text_short = $cat['name'] . $type . ' (' . $cat['articles'] . ', ' . $cat['articles_private'] . ', ' . $cat['articles_draft'] . ')';

            $total_articles += $cat['articles'];

            // Generate KB menu icons
            $menu_icons =
                '<a class="tooltip" style="display: inline-flex;" data-ztt_vertical_offset="0" href="manage_knowledgebase.php?a=add_article&amp;catid=' . $my . '" onclick="document.getElementById(\'option' . $j . '\').selected=true;return true;" title="' . $hesklang['kb_i_art'] . '">' .
                '<svg style="fill: #9c9c9c" class="icon icon-add">' .
                '<use xlink:href="' . HESK_PATH . 'img/sprite.svg#icon-add"></use>' .
                '</svg>' .
                '</a>&nbsp;&nbsp;&nbsp;'
                . '<a class="tooltip" style="display: inline-flex;" data-ztt_vertical_offset="0" href="manage_knowledgebase.php?a=add_category&amp;parent=' . $my . '" onclick="document.getElementById(\'option' . $j . '_2\').selected=true;return true;" title="' . $hesklang['kb_i_cat'] . '">' .
                '<svg style="fill: #9c9c9c" class="icon icon-categories">' .
                '<use xlink:href="' . HESK_PATH . 'img/sprite.svg#icon-categories"></use>' .
                '</svg>' .
                '</a>&nbsp;&nbsp;&nbsp;'
                . '<a class="tooltip" style="display: inline-flex;" data-ztt_vertical_offset="0" href="manage_knowledgebase.php?a=manage_cat&amp;catid=' . $my . '" title="' . $hesklang['kb_p_man'] . '">' .
                '<svg style="fill: #9c9c9c" class="icon icon-settings">' .
                '<use xlink:href="' . HESK_PATH . 'img/sprite.svg#icon-settings"></use>' .
                '</svg>' .
                '</a> ';

            // Can this category be moved up?
            if ($cat['move_up'] == false || ($cat['move_up'] && $parent_copy[$cat['parent']] == $parent[$cat['parent']])) {
                $menu_icons .= '<a href="#" style="visibility: hidden;width: 11px; display: inline-block">' .
                    '<svg class="icon icon-chevron-up" style="font-size: 8px">' .
                    '<use xlink:href="' . HESK_PATH . 'img/sprite.svg#icon-chevron-down"></use>' .
                    '</svg>' .
                    '</a> ';
            } else {
                $menu_icons .= '<a class="tooltip" data-ztt_vertical_offset="0" style="width: 11px; display: inline-block" href="manage_knowledgebase.php?a=order_cat&amp;catid=' . $my . '&amp;move=-15&amp;token=' . hesk_token_echo(0) . '" title="' . $hesklang['move_up'] . '">' .
                    '<svg class="icon icon-chevron-up" style="fill: ' . $arrow_colors[$arrow] . '; font-size: 8px">' .
                    '<use xlink:href="' . HESK_PATH . 'img/sprite.svg#icon-chevron-down"></use>' .
                    '</svg>' .
                    '</a> ';
            }

            // Can this category be moved down?
            if ($cat['move_down'] == false || ($cat['move_down'] && $parent_copy[$cat['parent']] == 1)) {
                $menu_icons .= '<a href="#" style="visibility: hidden; width: 11px; display: inline-block">' .
                    '<svg class="icon icon-chevron-down">' .
                    '<use xlink:href="' . HESK_PATH . 'img/sprite.svg#icon-chevron-down"></use>' .
                    '</svg>' .
                    '</a> ';
            } else {
                $menu_icons .= '<a class="tooltip" data-ztt_vertical_offset="0" style="width: 11px; display: inline-block" href="manage_knowledgebase.php?a=order_cat&amp;catid=' . $my . '&amp;move=15&amp;token=' . hesk_token_echo(0) . '" title="' . $hesklang['move_dn'] . '">' .
                    '<svg class="icon icon-chevron-down" style="fill: ' . $arrow_colors[$arrow] . '">' .
                    '<use xlink:href="' . HESK_PATH . 'img/sprite.svg#icon-chevron-down"></use>' .
                    '</svg>' .
                    '</a> ';
            }

            if (isset($node[$up])) {
                $HTML_TreeNode[$my] = new HTML_TreeNode(array('hesk_selected' => $selected, 'text' => $text, 'text_short' => $text_short, 'menu_icons' => $menu_icons, 'hesk_catid' => $cat['id'], 'hesk_select' => 'option' . $j, 'icon' => $icon, 'expandedIcon' => $expandedIcon, 'expanded' => true));
                $node[$my] = &$node[$up]->addItem($HTML_TreeNode[$my]);
            } else {
                $node[$my] = new HTML_TreeNode(array('hesk_selected' => $selected, 'text' => $text, 'text_short' => $text_short, 'menu_icons' => $menu_icons, 'hesk_catid' => $cat['id'], 'hesk_select' => 'option' . $j, 'icon' => $icon, 'expandedIcon' => $expandedIcon, 'expanded' => true));
            }

            $nextlevel[] = $cat['id'];
            $parent_copy[$cat['parent']]--;
            $j++;
            unset($kb_cat[$k]);
        }
    }

    $thislevel = $nextlevel;
    $nextlevel = array();

    /* Break after 20 recursions to avoid hang-ups in case of any problems */
    if ($i > 20) {
        break;
    }
    $i++;
}

$menu->addItem($node[1]);

hesk_cleanSessionVars(array('hide', 'new_article', 'new_category', 'KB_CATEGORY', 'manage_cat', 'edit_article', 'newcat'));
?>
<script>
$(function(){
  $('#upload').change(function(){
    var input = this;
   
    var url = $(this).val();
    var ext = url.substring(url.lastIndexOf('.') + 1).toLowerCase();
    var reader = new FileReader();

        reader.onload = function (e) {
           $('#img').attr('src', e.target.result);
        }
       reader.readAsDataURL(input.files[0]);
  });

});
</script>


<div class="main__content categories category-create">
    <section class="categories__head">
        <h2>
            Perks (Send Broadcast Notification) </h2>
    </section>
    <div class="table-wrap">
        <form data-toggle="validator"  enctype="multipart/form-data" role="form" method="post" class="form" style="padding-top: 35px !important; padding-bottom: 60px !important;" action="manage_knowledgebase.php" name="create-form">
            <div class="form-group">
                <label  for="name" class="control-label">
                    Title: <span class="important">*</span>
                </label>
                <input type="text" name="name" class="form-control" placeholder="Title" id="name" required maxlength="100" value="" fdprocessedid="iy226">
            </div>
            <div class="form-group">
                <label for="description" class="control-label">
                    Description: ( Optional )
                </label>
                <textarea style="height:150px;" rows="4" cols="50" placeholder="Description" name="description" class="form-control" id="description" maxlength="1000" value="" fdprocessedid="x2ai9"> </textarea>
            </div>
           
            <div class="category-create__select">
             <label for="description" class="control-label">
                   Image: ( Optional )
                </label>
               
                <div style="margin-left:40px;" > 
                <img id="img" src="./../img/No-Image.png" name="perk_image" alt="your image" style="max-width:200px; height:auto;" />
				<input type='file' id="upload" name="perk_image"  />
    			
                </div>
                
            </div>
            <div class="category-create__select">
                <span>Type:</span>
                <div class="dropdown-select center out-close" data-value="0">
                    <select name="type">
                        <option value="0" selected="">
                            Public </option>
                        <option value="1">
                            Private </option>
                    </select>
                  
                  
                </div>
            </div>
          
            <input type="hidden" name="id" value="0">
            <input type="hidden" name="token" value="<?php hesk_token_echo(); ?>" />
            <button class="btn btn-full" style="float:right;" type="submit" name ="Submit" ripple="ripple" value="Send" >Send <div class="ripple--container"></div></button>
        </form>
        
    </div>
</div>






<?php
require_once(HESK_PATH . 'inc/footer.inc.php');
exit();





?>
