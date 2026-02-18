<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

class CompressX_i18n
{
    /**
     * Load the plugin text domain for translation.
     *
     *
     */
    public function load_plugin_textdomain() {

        //load_plugin_textdomain() has been discouraged since WordPress version 4.6.
        /*load_plugin_textdomain(
            'compressx',
            false,
            dirname( dirname( plugin_basename( __FILE__ ) ) ) . '/languages/'
        );
        */

    }
}