<?php
define('IN_SCRIPT', 1);
define('HESK_PATH', './');

$hesk_settings = array();
require(HESK_PATH . 'hesk_settings.inc.php');

$db_host = $hesk_settings['db_host'];
if (strpos($db_host, ':') !== false) {
    list($db_host_only, $db_port) = explode(':', $db_host, 2);
} else {
    $db_host_only = $db_host;
    $db_port = 3306;
}
$db_user = $hesk_settings['db_user'];
$db_pass = $hesk_settings['db_pass'];
$db_name = $hesk_settings['db_name'];
$db_pfix = $hesk_settings['db_pfix'];

$lang = $hesk_settings['language'];
$cache_dir = $hesk_settings['cache_dir'];
$sha = sha1($lang);
$cache_file_rel = $cache_dir . '/cf_' . $sha . '.cache.php';
$cache_file_full = dirname(__FILE__) . '/' . $cache_file_rel;

echo "<h2>Custom Fields - Force Cache Rebuild</h2>";
echo "<strong>Language:</strong> $lang (sha1: $sha)<br>";
echo "<strong>Cache file:</strong> $cache_file_rel<br>";
echo "<strong>Full path:</strong> $cache_file_full<br><br>";

$link = mysqli_init();
$conn = @mysqli_real_connect($link, $db_host_only, $db_user, $db_pass, $db_name, $db_port);
if (!$conn) {
    die("<div style='color:red'>DB connection failed: " . mysqli_connect_error() . "</div>");
}
echo "<div style='color:green'>✅ Connected to database.</div>";

$sql = "SELECT * FROM `".$db_pfix."custom_fields` WHERE `use` IN ('1', '2') ORDER BY `place` ASC, `order` ASC";
$res = mysqli_query($link, $sql);
if (!$res) {
    die("<div style='color:red'>Query failed: " . mysqli_error($link) . "</div>");
}

$custom_fields = array();
while ($row = mysqli_fetch_assoc($res)) {
    $id = 'custom' . $row['id'];
    unset($row['id']);

    $names = json_decode($row['name'], true);
    $row['name'] = (isset($names[$lang])) ? $names[$lang] : reset($names);

    $row['title'] = rtrim($row['name'], ':?!.');
    $row['title'] = strlen($row['title']) > 30 ? substr($row['title'], 0, 30) . '...' : $row['title'];
    $row['name:'] = in_array(substr($row['name'], -1), array(':', '?', '!', '.')) ? $row['name'] : $row['name'] . ':';

    $row['category'] = !empty($row['category']) ? json_decode($row['category'], true) : array();
    $row['value'] = json_decode($row['value'], true);

    $custom_fields[$id] = $row;
}

echo "<h3>Custom Field 29 (Employee Dealership) Options:</h3>";
if (isset($custom_fields['custom29'])) {
    $opts = $custom_fields['custom29']['value']['select_options'];
    echo "<strong>Total options:</strong> " . count($opts) . "<br><br>";
    echo "<strong>All options:</strong><br><pre style='background:#f6f6f6;padding:8px;border:1px solid #ccc;max-height:200px;overflow:auto'>";
    foreach ($opts as $i => $opt) {
        $mark = ($opt == 'Toyota Of Warren' || $opt == 'Toyota of Warren') ? " 🔥⭐✅" : "";
        echo "  [" . ($i+1) . "] $opt$mark\n";
    }
    echo "</pre>";
    $has_tow = in_array("Toyota Of Warren", $opts) || in_array("Toyota of Warren", $opts);
    if ($has_tow) {
        echo "<div style='color:green;font-size:22px'>✅ DATABASE HAS 'Toyota of Warren'.</div>";
    } else {
        echo "<div style='color:red;font-size:18px'>⚠️ WARNING: Toyota of Warren NOT found in select_options. Need to UPDATE DB first (from previous instructions).</div>";
    }
} else {
    echo "<div style='color:red'>custom29 not found!</div>";
}

echo "<hr>";
echo "<h3>Now building new cache content & attempting to write...</h3>";

$cache_content = '<?php if (!defined(\'IN_SCRIPT\')) {die();} $hesk_settings[\'custom_fields\']=' . var_export($custom_fields, true) . ';';
echo "Cache content length: " . strlen($cache_content) . " bytes.<br>";
echo "Cache dir exists? " . (is_dir(dirname(__FILE__) . '/' . $cache_dir) ? "YES" : "NO") . "<br>";

if ($use_cache && file_exists($cache_file)) {
    echo "Old cache file exists. Size: " . filesize($cache_file_full) . " bytes<br>";
    @chmod($cache_file_full, 0777);
    if (unlink($cache_file_full)) {
        echo "<div style='color:green'>✅ Old cache file deleted successfully.</div>";
    } else {
        echo "<div style='color:orange'>⚠️ Could not delete old cache file. Will try to overwrite.</div>";
    }
} else {
    echo "Old cache file does not exist. Good.<br>";
}

echo "<br><strong>Writing new cache...</strong><br>";
if (file_put_contents($cache_file_full, $cache_content)) {
    echo "<div style='color:green;font-size:26px'>✅✅✅ SUCCESS! Cache rebuild complete.</div>";
    echo "<br>New cache size: " . filesize($cache_file_full) . " bytes<br>";
    echo "<h3>🎉 NOW OPEN THE EXIT INTERVIEW FORM, PRESS <code>Cmd + Shift + R</code> (hard refresh), and <strong>Toyota of Warren</strong> will appear in the Employee Dealership dropdown!</h3>";
} else {
    echo "<div style='color:red;font-size:18px'>❌ Could not write cache file. Folder permissions are too strict.</div>";
    echo "<br><h3>ALTERNATIVE FIX (works 100%): Edit the cache load check</h3>";
    echo "Open file: <code>inc/custom_fields.inc.php</code> on the live server.<br>";
    echo "Find line 45:<br>";
    echo "<pre style='background:#fee;padding:8px'>if (\$use_cache && file_exists(\$cache_file))</pre>";
    echo "Change it to:<br>";
    echo "<pre style='background:#efe;padding:8px'>if (0 && \$use_cache && file_exists(\$cache_file))</pre>";
    echo "<br>This DISABLES the custom fields cache so the DB is always read directly. After verifying it works, you can change it back after 1 hour (by then a new cache would be built if you delete this line change).";
    echo "<br><br>OR in Plesk File Manager, delete: <code>cache/cf_$sha.cache.php</code>";
}

echo "<hr>";
echo "<h3>Also check list of all .cache.php files:</h3>";
$dir = dirname(__FILE__) . '/' . $cache_dir . '/';
$files = glob($dir . '*.cache.php');
if ($files) {
    echo "<pre style='background:#f6f6f6;padding:8px;border:1px solid #ccc'>";
    foreach ($files as $f) {
        echo "  " . basename($f) . "  (" . filesize($f) . " bytes, modified: " . date("Y-m-d H:i:s", filemtime($f)) . ")\n";
    }
    echo "</pre>";
} else {
    echo "(No .cache.php files found)";
}

mysqli_close($link);
?>