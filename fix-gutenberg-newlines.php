<?php
$wp_root = getenv('CERAMIC_WP_ROOT') ?: '/Users/nagrajyr/Desktop/ceramic pro delta';
require rtrim($wp_root, '/') . '/wp-load.php';
$posts = get_posts(['post_type' => ['page', 'wp_template_part'], 'numberposts' => -1, 'post_status' => 'any']);
foreach ($posts as $post) {
    $content = preg_replace('/(>)n(?=<|<!--)/', "$1\n", $post->post_content);
    if ($content !== $post->post_content) wp_update_post(['ID' => $post->ID, 'post_content' => $content]);
}
echo "Fixed block separators.\n";
