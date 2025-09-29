<?php
/**
 * @link              https://compressx.io
 * @since             0.9.1
 *
 * @wordpress-plugin
 * Plugin Name: CompressX
 * Description: Convert JPG and PNG images to WebP and AVIF, compress WebP and AVIF.
 * Version: 0.9.32
 * Author: WPvivid Team
 * Author URI: https://compressx.io
 * License:           GPL-3.0+
 * License URI:       http://www.gnu.org/copyleft/gpl.html
 * Text Domain:       compressx
 * Domain Path:       /languages
 **/

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) )
{
    die;
}

define( 'COMPRESSX_VERSION', '0.9.32' );

define( 'COMPRESSX_SLUG', 'CompressX' );
define( 'COMPRESSX_NAME', plugin_basename( __FILE__ ) );
define( 'COMPRESSX_URL', plugins_url( '', __FILE__ ) );
define( 'COMPRESSX_DIR', dirname( __FILE__ ) );

if ( isset( $compressx ) && is_a( $compressx, 'CompressX' ) )
{
    return;
}

function compressx_actvation_action()
{
    include_once COMPRESSX_DIR . '/includes/class-compressx-default-folder.php';
    $dir=new CompressX_default_folder();
    $dir->create_uploads_dir();

    $options=get_option('compressx_general_settings',array());
    $image_load=isset($options['image_load'])?$options['image_load']:'htaccess';
    if ($image_load == "htaccess")
    {
        include_once COMPRESSX_DIR . '/includes/class-compressx-webp-rewrite.php';

        $rewrite=new CompressX_Webp_Rewrite();
        $rewrite->create_rewrite_rules();
    }
    else if($image_load == "compat_htaccess")
    {
        include_once COMPRESSX_DIR . '/includes/class-compressx-webp-rewrite.php';

        $rewrite=new CompressX_Webp_Rewrite();
        $rewrite->create_rewrite_rules_ex();
    }

}

function compressx_deactivation_action()
{
    include_once COMPRESSX_DIR . '/includes/class-compressx-webp-rewrite.php';

    $rewrite=new CompressX_Webp_Rewrite();
    $rewrite->remove_rewrite_rule();
}

register_activation_hook(__FILE__, 'compressx_actvation_action');
register_deactivation_hook(__FILE__, 'compressx_deactivation_action');
require plugin_dir_path( __FILE__ ) . 'includes/class-compressx.php';

function run_compressx()
{
    $compressx            = new CompressX();
    $GLOBALS['compressx'] = $compressx;
}
run_compressx();