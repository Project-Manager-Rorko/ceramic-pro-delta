<?php
$content = file_get_contents("C:/Users/shanm/Local Sites/ceramic-pro-new/_compare/db-export/home-html-blocks.html");
$content = preg_replace("/^\xEF\xBB\xBF/", "", $content);
$esc = str_replace(
  ["\\", "\x00", "\n", "\r", "'", "\x1a"],
  ["\\\\", "\\0", "\\n", "\\r", "\\'", "\\Z"],
  $content
);
$sql = "UPDATE wp_posts SET post_content = '" . $esc . "', post_modified = NOW(), post_modified_gmt = UTC_TIMESTAMP() WHERE ID = 48;\n";
file_put_contents("C:/Users/shanm/Local Sites/ceramic-pro-new/_compare/db-export/update-home.sql", $sql);
echo "content_len=" . strlen($content) . "\n";