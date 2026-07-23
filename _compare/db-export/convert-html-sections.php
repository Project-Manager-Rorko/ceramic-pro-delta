<?php
// Convert raw HTML page into one <!-- wp:html --> block per <section> (exact markup).
// Design stays 100% identical; editor shows separate section blocks.
$in = $argv[1];
$out = $argv[2];
$html = file_get_contents($in);
$html = preg_replace("/^\xEF\xBB\xBF/", "", $html);
$html = preg_replace('/^\x{FEFF}/u', '', $html);
$html = trim($html);

if (preg_match('/<!--\s*wp:/', $html)) {
  // already blocks - write as-is
  file_put_contents($out, $html);
  echo "already_blocks\n";
  exit(0);
}

if (!preg_match_all('/<section\b[^>]*>.*?<\/section>/is', $html, $matches)) {
  $blocks = "<!-- wp:html -->\n" . $html . "\n<!-- /wp:html -->\n";
  file_put_contents($out, $blocks);
  echo "single_html_block\n";
  exit(0);
}

$blocks = "";
foreach ($matches[0] as $i => $section) {
  $section = trim($section);
  $blocks .= "<!-- wp:html -->\n" . $section . "\n<!-- /wp:html -->\n\n";
}
file_put_contents($out, rtrim($blocks) . "\n");
echo "sections=" . count($matches[0]) . " bytes=" . strlen($blocks) . "\n";