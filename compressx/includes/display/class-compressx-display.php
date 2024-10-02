<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

class CompressX_Display
{
    public $dashboard;
    public $bulk_action;
    public $custom_bulk_action;
    public $log;
    public $system_info;
    public $cdn;

    public function __construct()
    {
        include_once COMPRESSX_DIR . '/includes/display/class-compressx-dashboard.php';
        include_once COMPRESSX_DIR . '/includes/display/class-compressx-bulk-action.php';
        include_once COMPRESSX_DIR . '/includes/display/class-compressx-custom-bulk-action.php';
        include_once COMPRESSX_DIR . '/includes/display/class-compressx-cdn.php';
        include_once COMPRESSX_DIR . '/includes/display/class-compressx-logs.php';
        include_once COMPRESSX_DIR . '/includes/display/class-compressx-system-info.php';

        $this->dashboard=new CompressX_Dashboard();
        $this->bulk_action=new CompressX_Bulk_Action();
        $this->custom_bulk_action=new CompressX_Custom_Bulk_Action();
        $this->cdn=new CompressX_CDN();
        $this->log=new CompressX_Logs();
        $this->system_info=new CompressX_System_Info();

        add_action('admin_enqueue_scripts',array( $this,'enqueue_styles'));
        add_action('admin_enqueue_scripts',array( $this,'enqueue_scripts'));

        add_action('compressx_output_nav',array( $this,'output_nav'));
        add_action('compressx_output_header',array( $this,'output_header'));
        add_action('compressx_output_footer',array( $this,'output_footer'));

        if(is_multisite())
        {
            add_action('network_admin_menu',array( $this,'add_plugin_network_admin_menu'));
        }
        else
        {
            add_action('admin_menu',array( $this,'add_plugin_admin_menu'));
        }
    }

    public function enqueue_styles()
    {
        if(is_multisite())
        {
            $screen_ids[]='toplevel_page_CompressX-network';
        }
        else
        {
            $screen_ids[]='toplevel_page_'.COMPRESSX_SLUG;
            $screen_ids[]='compressx_page_info-compressx';
            $screen_ids[]='compressx_page_logs-compressx';
            $screen_ids[]='compressx_page_cdn-compressx';
        }


        if(in_array(get_current_screen()->id,$screen_ids))
        {
            wp_enqueue_style(COMPRESSX_SLUG.'jstree', COMPRESSX_URL . '/includes/display/js/jstree/dist/themes/default/style.min.css', array(), COMPRESSX_VERSION, 'all');
            wp_enqueue_style(COMPRESSX_SLUG, COMPRESSX_URL . '/includes/display/css/compressx-style.css', array(), COMPRESSX_VERSION, 'all');
            wp_enqueue_style(COMPRESSX_SLUG.'-percentage-circle-style', COMPRESSX_URL . '/includes/display/css/compressx-percentage-circle-style.css', array(), COMPRESSX_VERSION, 'all');
        }
        else if(get_current_screen()->id=='upload'||get_current_screen()->id=='attachment')
        {
            wp_enqueue_style(COMPRESSX_SLUG, COMPRESSX_URL . '/includes/display/css/compressx-media.css', array(), COMPRESSX_VERSION, 'all');
        }
    }

    public function enqueue_scripts()
    {
        $screen_ids[]='toplevel_page_'.COMPRESSX_SLUG;
        $screen_ids[]='compressx_page_info-compressx';
        $screen_ids[]='compressx_page_logs-compressx';
        $screen_ids[]='compressx_page_cdn-compressx';
        if(in_array(get_current_screen()->id,$screen_ids))
        {
            wp_enqueue_script(COMPRESSX_SLUG, COMPRESSX_URL . '/includes/display/js/compressx.js', array('jquery'), COMPRESSX_VERSION, false);
            wp_enqueue_script(COMPRESSX_SLUG.'jstree', COMPRESSX_URL . '/includes/display/js/jstree/dist/jstree.min.js', array('jquery'), COMPRESSX_VERSION, false);

            wp_localize_script(COMPRESSX_SLUG, 'compressx_ajax_object', array('ajax_url' => admin_url('admin-ajax.php'),'ajax_nonce'=>wp_create_nonce('compressx_ajax')));

            wp_enqueue_script('plupload-all');
        }
        else if(get_current_screen()->id=='upload'||get_current_screen()->id=='attachment')
        {
            wp_enqueue_script(COMPRESSX_SLUG, COMPRESSX_URL . '/includes/display/js/compressx.js', array('jquery'), COMPRESSX_VERSION, false);
            wp_enqueue_script(COMPRESSX_SLUG.'_media', COMPRESSX_URL . '/includes/display/js/optimize.js', array('jquery'), COMPRESSX_VERSION, true);
            wp_localize_script(COMPRESSX_SLUG, 'compressx_ajax_object', array('ajax_url' => admin_url('admin-ajax.php'),'ajax_nonce'=>wp_create_nonce('compressx_ajax')));
        }

        if(get_current_screen()->id=='toplevel_page_'.COMPRESSX_SLUG)
        {
            $arg=array();
            $arg['in_footer']=true;

            $upload_dir = wp_upload_dir();
            $path = $upload_dir['basedir'];
            $path = str_replace('\\','/',$path);
            $uploads_path = $path.'/';

            $path = WP_CONTENT_DIR;
            $path = str_replace('\\','/',$path);
            $custom_root_path = $path.'/';

            wp_localize_script(COMPRESSX_SLUG, 'compressx_uploads_root', array('path' => $uploads_path,'custom_path'=>$custom_root_path));
            wp_enqueue_script(COMPRESSX_SLUG.'_setting', COMPRESSX_URL . '/includes/display/js/compressx_setting.js', array('jquery'), COMPRESSX_VERSION, $arg);
            wp_enqueue_script(COMPRESSX_SLUG.'_custom_bulk', COMPRESSX_URL . '/includes/display/js/compressx_custom_bulk.js', array('jquery'), COMPRESSX_VERSION, $arg);
        }

        if(get_current_screen()->id=='compressx_page_cdn-compressx')
        {
            $arg=array();
            $arg['in_footer']=true;

            wp_enqueue_script(COMPRESSX_SLUG.'_setting', COMPRESSX_URL . '/includes/display/js/compressx_setting.js', array('jquery'), COMPRESSX_VERSION, $arg);
        }

        if(get_current_screen()->id=='compressx_page_logs-compressx')
        {
            $arg=array();
            $arg['in_footer']=true;

            wp_enqueue_script(COMPRESSX_SLUG.'_logs', COMPRESSX_URL . '/includes/display/js/compressx_log.js', array('jquery'), COMPRESSX_VERSION, $arg);
        }

        if(get_current_screen()->id=='compressx_page_info-compressx')
        {
            $arg=array();
            $arg['in_footer']=true;
            wp_enqueue_script(COMPRESSX_SLUG.'_systeminfo', COMPRESSX_URL . '/includes/display/js/compressx_system_info.js', array('jquery'), COMPRESSX_VERSION, $arg);
        }
    }

    public function add_plugin_network_admin_menu()
    {
        $menu['page_title']= 'CompressX';
        $menu['menu_title']= 'CompressX';
        $menu['capability']='manage_options';
        $menu['menu_slug']=COMPRESSX_SLUG;
        $menu['function']=array($this, 'mu_display');
        $menu['icon_url']='dashicons-images-alt2';
        $menu['position']=100;

        add_menu_page( $menu['page_title'],$menu['menu_title'], $menu['capability'], $menu['menu_slug'], $menu['function'], $menu['icon_url'], $menu['position']);
    }

    public function add_plugin_admin_menu()
    {
        $menu['page_title']= 'CompressX';
        $menu['menu_title']= 'CompressX';
        $menu['capability']='manage_options';
        $menu['menu_slug']=COMPRESSX_SLUG;
        $menu['function']=array($this->dashboard, 'display');
        $menu['icon_url']='dashicons-images-alt2';
        $menu['position']=100;

        add_menu_page( $menu['page_title'],$menu['menu_title'], $menu['capability'], $menu['menu_slug'], $menu['function'], $menu['icon_url'], $menu['position']);

        $submenu['parent_slug']=COMPRESSX_SLUG;
        $submenu['page_title']="Settings";
        $submenu['menu_title']="Settings";
        $submenu['capability']="administrator";
        $submenu['menu_slug']=COMPRESSX_SLUG;
        $submenu['function']=array($this->dashboard, 'display');

        add_submenu_page
        (
            $submenu['parent_slug'],
            $submenu['page_title'],
            $submenu['menu_title'],
            $submenu['capability'],
            $submenu['menu_slug'],
            $submenu['function']
        );

        /*
        $submenu['parent_slug']=COMPRESSX_SLUG;
        $submenu['page_title']="Bulk CompressX";
        $submenu['menu_title']="Bulk CompressX";
        $submenu['capability']="administrator";
        $submenu['menu_slug']="bulk-compressx";
        $submenu['function']=array($this->bulk_action, 'display');

        add_submenu_page
        (
            $submenu['parent_slug'],
            $submenu['page_title'],
            $submenu['menu_title'],
            $submenu['capability'],
            $submenu['menu_slug'],
            $submenu['function']
        );

        $submenu['parent_slug']=COMPRESSX_SLUG;
        $submenu['page_title']="Custom CompressX";
        $submenu['menu_title']="Custom CompressX";
        $submenu['capability']="administrator";
        $submenu['menu_slug']="custom-compressx";
        $submenu['function']=array($this->custom_bulk_action, 'display');

        add_submenu_page
        (
            $submenu['parent_slug'],
            $submenu['page_title'],
            $submenu['menu_title'],
            $submenu['capability'],
            $submenu['menu_slug'],
            $submenu['function']
        );*/

        $submenu['parent_slug']=COMPRESSX_SLUG;
        $submenu['page_title']="CDN Integration";
        $submenu['menu_title']="CDN Integration";
        $submenu['capability']="administrator";
        $submenu['menu_slug']="cdn-compressx";
        $submenu['function']=array($this->cdn, 'display');

        add_submenu_page
        (
            $submenu['parent_slug'],
            $submenu['page_title'],
            $submenu['menu_title'],
            $submenu['capability'],
            $submenu['menu_slug'],
            $submenu['function']
        );

        $submenu['parent_slug']=COMPRESSX_SLUG;
        $submenu['page_title']="Logs";
        $submenu['menu_title']="Logs";
        $submenu['capability']="administrator";
        $submenu['menu_slug']="logs-compressx";
        $submenu['function']=array($this->log, 'display');

        add_submenu_page
        (
            $submenu['parent_slug'],
            $submenu['page_title'],
            $submenu['menu_title'],
            $submenu['capability'],
            $submenu['menu_slug'],
            $submenu['function']
        );

        $submenu['parent_slug']=COMPRESSX_SLUG;
        $submenu['page_title']="System Information";
        $submenu['menu_title']="System Information";
        $submenu['capability']="administrator";
        $submenu['menu_slug']="info-compressx";
        $submenu['function']=array($this->system_info, 'display');

        add_submenu_page
        (
            $submenu['parent_slug'],
            $submenu['page_title'],
            $submenu['menu_title'],
            $submenu['capability'],
            $submenu['menu_slug'],
            $submenu['function']
        );
    }

    public function mu_display()
    {
        ?>
        <div id="compressx-root">
            <div id="compressx-wrap">
                <?php
                $this->output_nav();
                $this->output_header();
                ?>
                <section style="display:block;">
                    <div class="compressx-container compressx-section">
                        <div class="compressx-notification">
                            <p><span class="dashicons dashicons-warning" style="color:#FF3951;"></span>
                                <span>Currently, AVIF, WebP Converter Plugin (CompressX.io)  does not support WordPress Multisite.</span>
                            </p>
                        </div>
                    </div>
                </section>
            </div>
        </div>
        <?php
    }

    public function output_nav()
    {
        ?>
        <nav>
            <div class="compressx-container compressx-menu">
                <h2>Compress<span style="color:#175cff;">X</span><span style="font-size:1.2rem;">.io</span></h2>
                <ul class="cx-menu-ul-large">
                    <li><a href="https://compressx.io/docs/compressx-overview/"><strong><?php esc_html_e('Documentation','compressx')?></strong></a></li>
                    <li><a href="https://compressx.io/docs/troubleshooting/"><strong><?php esc_html_e('Troubleshooting','compressx')?></strong></a></li>
                    <li><a href="https://wordpress.org/support/plugin/compressx/"><strong><?php esc_html_e('Support','compressx')?></strong></a></li>
                </ul>
                <ul class="cx-menu-ul-mini">
                    <li><a href=""><?php esc_html_e('Documentation','compressx')?></a></li>
                </ul>
            </div>
        </nav>
        <?php
    }

    public function output_header()
    {
        ?>
        <header>
            <div class="compressx-container compressx-header">
                <div class="compressx-servers">
                    <div class="compressx-servers-header">
                        <div class="compressx-servers-header-api">
                            <span id="cx_apiserver" class="cx-apiserver">
                                <span><strong><?php esc_html_e('Server: ','compressx')?></strong></span>
                                <span>
                                    <span id="cx_apiserver_text">Localhost</span>
                                    <!--span id="cx_edit_apiserver"><a style="cursor: pointer">Edit</a></span-->
                                </span>
                            </span>
                        </div>
                        <!--div>
                            <span>
                                <span class="cx-tokeninput">
                                    <span>
                                        <input type="password" style="width:300px;" placeholder="Provide a valid token to access the PRO API...">
                                    </span>
                                    <input class="button-primary cx-button" id="" type="submit" value="Activate">
                                    <span style="display: none">
                                        <img src="../wp-admin/images/loading.gif" alt="">
                                    </span>
                                </span>
                            </span>
                            <span style="display: none;">
                                <span>The token is not available, please <a href="#">try it again</a></span>
                            </span>
                        </div-->
                    </div>
                </div>
            </div>
        </header>
        <?php
    }

    public function output_footer()
    {
        ?>
        <footer>
            <div class="compressx-container compressx-menu">
                <div style="margin: auto;"><strong><span>If you like our plugin, a <a href=""><span class="dashicons dashicons-star-filled" style="color:#ffb900;"></span>
                    <span class="dashicons dashicons-star-filled" style="color:#ffb900;"></span>
                    <span class="dashicons dashicons-star-filled" style="color:#ffb900;"></span>
                    <span class="dashicons dashicons-star-filled" style="color:#ffb900;"></span>
                    <span class="dashicons dashicons-star-filled" style="color:#ffb900;"></span></a>
                    <span>will help us a lot, thanks in advance!</span></strong>
                </div>
            </div>
        </footer>
        <?php
    }
}