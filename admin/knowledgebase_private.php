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
require(HESK_PATH . 'inc/knowledgebase_functions.inc.php');
hesk_load_database_functions();

hesk_session_start();
hesk_dbConnect();
hesk_isLoggedIn();

/* Is Knowledgebase enabled? */
if ( ! $hesk_settings['kb_enable'])
{
	hesk_error($hesklang['kbdis']);
}

/* Can this user manage Knowledgebase or just view it? */
$can_man_kb = hesk_checkPermission('can_man_kb',0);

/* Any category ID set? */
$catid = intval( hesk_GET('category', 1) );
$artid = intval( hesk_GET('article', 0) );

if (isset($_GET['search']))
{
	$query = hesk_input( hesk_GET('search') );
}
else
{
	$query = 0;
}

$hesk_settings['kb_link'] = ($artid || $catid != 1 || $query) ? '<a href="knowledgebase_private.php" class="smaller">'.$hesklang['gopr'].'</a>' : ($can_man_kb ? $hesklang['gopr'] : '');

if ($hesk_settings['kb_search'] && $query)
{
    hesk_kb_search($query);
}
elseif ($artid)
{
	// Show drafts only to staff who can manage knowledgebase
	if ($can_man_kb)
	{
		$result = hesk_dbQuery("SELECT t1.*, t2.`name` AS `cat_name`, t2.`type` AS `cat_type`
		FROM `".hesk_dbEscape($hesk_settings['db_pfix'])."kb_articles` AS `t1`
		LEFT JOIN `".hesk_dbEscape($hesk_settings['db_pfix'])."kb_categories` AS `t2` ON `t1`.`catid` = `t2`.`id`
		WHERE `t1`.`id` = '{$artid}'
		");
	}
	else
	{
		$result = hesk_dbQuery("SELECT t1.*, t2.`name` AS `cat_name`, t2.`type` AS `cat_type`
		FROM `".hesk_dbEscape($hesk_settings['db_pfix'])."kb_articles` AS `t1`
		LEFT JOIN `".hesk_dbEscape($hesk_settings['db_pfix'])."kb_categories` AS `t2` ON `t1`.`catid` = `t2`.`id`
		WHERE `t1`.`id` = '{$artid}' AND `t1`.`type` IN ('0', '1')
		");
	}

    $article = hesk_dbFetchAssoc($result) or hesk_error($hesklang['kb_art_id']);
    $article['views_formatted'] = number_format($article['views'], 0, null, $hesklang['sep_1000']);
    $article['votes_formatted'] = number_format($article['votes'], 0, null, $hesklang['sep_1000']);
    if ($article['catid'] == 1)
    {
        $article['cat_name'] = $hesklang['kb_text'];
    }
    hesk_show_kb_article($artid);
}
else
{
	hesk_show_kb_category($catid);
}

require_once(HESK_PATH . 'inc/footer.inc.php');
exit();


/*** START FUNCTIONS ***/

function hesk_kb_header()
{
    // They may be unused here, but they're used down the line. Don't delete
	global $hesk_settings, $hesklang, $can_man_kb;

	/* Print admin navigation */
	require_once(HESK_PATH . 'inc/show_admin_nav.inc.php');

    //hesk_kbSearchLarge(1);
} // END hesk_kb_header()


function hesk_kb_search($query)
{
	global $hesk_settings, $hesklang;

	/* Print header */
	require_once(HESK_PATH . 'inc/header.inc.php');
	hesk_kb_header();

	$res = hesk_dbQuery('SELECT t1.`id`, t1.`subject`, LEFT(`t1`.`content`, '.max(200, $hesk_settings['kb_substrart'] * 2).') AS `content`, t1.`rating` FROM `'.hesk_dbEscape($hesk_settings['db_pfix']).'kb_articles` AS t1 LEFT JOIN `'.hesk_dbEscape($hesk_settings['db_pfix']).'kb_categories` AS t2 ON t1.`catid` = t2.`id` '." WHERE t1.`type` IN ('0','1') AND MATCH(`subject`,`content`,`keywords`) AGAINST ('".hesk_dbEscape($query)."') LIMIT ".intval($hesk_settings['kb_search_limit']));
    $num = hesk_dbNumRows($res);

    ?>



	<?php
	if ($num == 0)
	{
		hesk_show_info($hesklang['nosr']);
        hesk_show_kb_category(1,1);
	}
    else
    {
?>

    <?php
    } // END else

} // END hesk_kb_search()


function hesk_show_kb_article($artid)
{
	global $hesk_settings, $hesklang, $article, $can_man_kb;

    // We should style <code> elemenets here
    define('STYLE_CODE',1);
    define('TIMEAGO',1);

	// Print header
    $hesk_settings['tmp_title'] = $article['subject'];
	require_once(HESK_PATH . 'inc/header.inc.php');
	hesk_kb_header();

    // Update views by 1
	hesk_dbQuery('UPDATE `'.hesk_dbEscape($hesk_settings['db_pfix'])."kb_articles` SET `views`=`views`+1 WHERE `id`={$artid}");
    $article['views']++;
    $article['views_formatted'] = number_format($article['views'], 0, null, $hesklang['sep_1000']);
?>
    
    <?php
} // END hesk_show_kb_article()


function hesk_show_kb_category($catid, $is_search = 0) {
	global $hesk_settings, $hesklang;

    if ($is_search == 0)
    {
		/* Print header */
		require_once(HESK_PATH . 'inc/header.inc.php');
		hesk_kb_header();

		if ($catid == 1)
	    {
	    	//echo '<span style="padding-left: 16px">' . $hesklang['priv'] . '</span>';
	    }
    }

	$res = hesk_dbQuery("SELECT `name`,`parent` FROM `".hesk_dbEscape($hesk_settings['db_pfix'])."kb_categories` WHERE `id`='".intval($catid)."' LIMIT 1");
    $thiscat = hesk_dbFetchAssoc($res) or hesk_error($hesklang['kb_cat_inv']);

	if ($thiscat['parent'])
	{
		$link = ($thiscat['parent'] == 1) ? 'knowledgebase_private.php' : 'knowledgebase_private.php?category='.$thiscat['parent'];
		echo '<span class="homepageh3" style="font-size: 1.4rem; padding-left: 16px">'.$hesklang['kb_cat'].': '.$thiscat['name'].'
        &nbsp;(<a style="display: inline" class="link" href="javascript:history.go(-1)">'.$hesklang['back'].'</a>)</span>
		';
	}

    ?>
    
<?php
} // END hesk_show_kb_category()
?>
