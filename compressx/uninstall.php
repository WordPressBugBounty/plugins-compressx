<?php

// If uninstall not called from WordPress, then exit.
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
    exit;
}

delete_option("compressx_general_settings");
delete_option("compressx_auto_optimize");
delete_option("compressx_output_format_webp");
delete_option("compressx_output_format_avif");
delete_option("compressx_converter_method");
delete_option("compressx_quality");
delete_option("compressx_custom_includes");
delete_option("compressx_custom_image_opt_task");
delete_option("compressx_need_optimized_custom_images");
delete_option("compressx_image_opt_task");
delete_option("compressx_global_stats");
delete_option("compressx_media_excludes");
delete_option("compressx_hide_notice");
delete_option("compressx_need_optimized_images");
delete_option("compressx_dissmiss_conflict_notice");
delete_option('compressx_rating_dismiss');
delete_option('compressx_show_review');
