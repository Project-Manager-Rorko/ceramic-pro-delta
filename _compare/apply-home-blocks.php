<?php
// Load WordPress
define("ABSPATH", "C:/Users/shanm/Local Sites/ceramic-pro-new/app/public/");
require ABSPATH . "wp-load.php";

$converted = file_get_contents("C:/Users/shanm/Local Sites/ceramic-pro-new/_compare/db-export/home-converted.html");
// strip BOM if any
$converted = preg_replace('/^\xEF\xBB\xBF/', '', $converted);
$converted = preg_replace('/^\x{FEFF}/u', '', $converted);

// Also convert any other pages still raw HTML with sections
$q = new WP_Query([
  "post_type" => "page",
  "post_status" => "publish",
  "posts_per_page" => -1,
]);

require_once __DIR__ . "/convert-sections.php"; // will redeclare - better inline

// Just update home for now via include functions - redefine by reading convert file differently
function cp_esc_json_attr($s) {
    return str_replace(['\\', '"'], ['\\\\', '\\"'], $s);
}

// reuse converted file for home
$home_id = 48;
$before = get_post_field("post_content", $home_id);
file_put_contents("C:/Users/shanm/Local Sites/ceramic-pro-new/_compare/db-export/home-before-update.txt", $before);

$r = wp_update_post([
  "ID" => $home_id,
  "post_content" => $converted,
], true);

if (is_wp_error($r)) {
  fwrite(STDERR, "ERROR: " . $r->get_error_message() . "\n");
  exit(1);
}

// clear caches
clean_post_cache($home_id);
if (function_exists("wp_cache_flush")) wp_cache_flush();

// Verify
$after = get_post_field("post_content", $home_id);
preg_match_all('/<!-- wp:group /', $after, $g);
echo "Updated home ID=$home_id groups=" . count($g[0]) . " len=" . strlen($after) . "\n";

// Count root blocks
$depth = 0; $roots = 0;
if (preg_match_all('/<!--\s*(\/?)wp:([a-z0-9\-\/]+)/', $after, $mm, PREG_SET_ORDER)) {
  foreach ($mm as $m) {
    if ($m[1] === "/") { $depth = max(0, $depth - 1); }
    else { if ($depth === 0) $roots++; $depth++; }
  }
}
echo "root_blocks=$roots\n";

// Scan all pages for raw HTML still needing conversion
foreach ($q->posts as $p) {
  $c = $p->post_content;
  $is_raw = (strpos($c, "<!-- wp:") === false) && (stripos($c, "<section") !== false);
  if ($is_raw && (int)$p->ID !== 48) {
    echo "RAW_NEEDS_CONVERT id={$p->ID} slug={$p->post_name} len=" . strlen($c) . "\n";
  }
}
echo "DONE\n";
