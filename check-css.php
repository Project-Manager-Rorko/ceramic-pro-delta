<?php
require 'c:\Users\shanm\Local Sites\ceramic-pro-new\app\public\wp-load.php';
echo 'Active theme: ' . get_option('stylesheet') . "\n";
echo 'Custom CSS length: ' . strlen(wp_get_custom_css()) . "\n";
