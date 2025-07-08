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
    public $compression;
    public $addons;

    public function __construct()
    {
        include_once COMPRESSX_DIR . '/includes/display/class-compressx-dashboard.php';
        include_once COMPRESSX_DIR . '/includes/display/class-compressx-bulk-action.php';
        include_once COMPRESSX_DIR . '/includes/display/class-compressx-custom-bulk-action.php';
        include_once COMPRESSX_DIR . '/includes/display/class-compressx-cdn.php';
        include_once COMPRESSX_DIR . '/includes/display/class-compressx-logs.php';
        include_once COMPRESSX_DIR . '/includes/display/class-compressx-system-info.php';
        include_once COMPRESSX_DIR . '/includes/display/class-compressx-addons.php';

        $this->dashboard=new CompressX_Dashboard();
        $this->bulk_action=new CompressX_Bulk_Action();
        $this->custom_bulk_action=new CompressX_Custom_Bulk_Action();
        $this->cdn=new CompressX_CDN();
        $this->log=new CompressX_Logs();
        $this->system_info=new CompressX_System_Info();
        $this->addons=new CompressX_Addons();

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
            $screen_ids[]='compressx_page_addons-compressx';
            $screen_ids[]='compressx_page_compression-level-compressx';
        }

        $screen_ids=apply_filters('compressx_get_screen_ids',$screen_ids);

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
        $screen_ids[]='compressx_page_compression-level-compressx';
        $screen_ids=apply_filters('compressx_get_screen_ids',$screen_ids);

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

        $toplevel_screen_id='toplevel_page_'.COMPRESSX_SLUG;
        $toplevel_screen_id=apply_filters('compressx_get_screen_id',$toplevel_screen_id);
        if(get_current_screen()->id==$toplevel_screen_id)
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

            if(CompressX_Image_Opt_Method::check_imagick_avif())
            {
                wp_localize_script(COMPRESSX_SLUG, 'compressx_alert', array('imagick_avif' => false));
            }
            else
            {
                wp_localize_script(COMPRESSX_SLUG, 'compressx_alert', array('imagick_avif' => true));
            }

            wp_localize_script(COMPRESSX_SLUG, 'compressx_uploads_root', array('path' => $uploads_path,'custom_path'=>$custom_root_path));
            wp_enqueue_script(COMPRESSX_SLUG.'_setting', COMPRESSX_URL . '/includes/display/js/compressx_setting.js', array('jquery'), COMPRESSX_VERSION, $arg);
            wp_enqueue_script(COMPRESSX_SLUG.'_custom_bulk', COMPRESSX_URL . '/includes/display/js/compressx_custom_bulk.js', array('jquery'), COMPRESSX_VERSION, $arg);
        }

        $cdn_screen_id='compressx_page_cdn-compressx';
        $cdn_screen_id=apply_filters('compressx_get_screen_id',$cdn_screen_id);
        if(get_current_screen()->id=='compressx_page_cdn-compressx')
        {
            $arg=array();
            $arg['in_footer']=true;

            wp_enqueue_script(COMPRESSX_SLUG.'_setting', COMPRESSX_URL . '/includes/display/js/compressx_setting.js', array('jquery'), COMPRESSX_VERSION, $arg);
        }

        $logs_screen_id='compressx_page_logs-compressx';
        $logs_screen_id=apply_filters('compressx_get_screen_id',$logs_screen_id);
        if(get_current_screen()->id==$logs_screen_id)
        {
            $arg=array();
            $arg['in_footer']=true;

            wp_enqueue_script(COMPRESSX_SLUG.'_logs', COMPRESSX_URL . '/includes/display/js/compressx_log.js', array('jquery'), COMPRESSX_VERSION, $arg);
        }

        $info_screen_id='compressx_page_info-compressx';
        $info_screen_id=apply_filters('compressx_get_screen_id',$info_screen_id);
        if(get_current_screen()->id==$info_screen_id)
        {
            $arg=array();
            $arg['in_footer']=true;
            wp_enqueue_script(COMPRESSX_SLUG.'_systeminfo', COMPRESSX_URL . '/includes/display/js/compressx_system_info.js', array('jquery'), COMPRESSX_VERSION, $arg);
        }
    }

    public function add_plugin_network_admin_menu()
    {
        if(apply_filters('compressx_support_mu',false))
        {
            return;
        }
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
        $menu = apply_filters('compressx_get_main_admin_menus', $menu);

        add_menu_page( $menu['page_title'],$menu['menu_title'], $menu['capability'], $menu['menu_slug'], $menu['function'], $menu['icon_url'], $menu['position']);

        $submenu['parent_slug']=COMPRESSX_SLUG;
        $submenu['page_title']="Settings";
        $submenu['menu_title']="Settings";
        $submenu['capability']="administrator";
        $submenu['index']=1;
        $submenu['menu_slug']=COMPRESSX_SLUG;
        $submenu['function']=array($this->dashboard, 'display');

        $submenus[ $submenu['menu_slug']]=$submenu;

        if(apply_filters('compressx_current_user_can',true,'compressx-can-use-cdn'))
        {
            $submenu['parent_slug']=COMPRESSX_SLUG;
            $submenu['page_title']="CDN Integration";
            $submenu['menu_title']="CDN Integration";
            $submenu['capability']="administrator";
            $submenu['menu_slug']="cdn-compressx";
            $submenu['index']=3;
            $submenu['function']=array($this->cdn, 'display');

            $submenus[$submenu['menu_slug']]=$submenu;
        }

        if(apply_filters('compressx_current_user_can',true,'compressx-can-use-logs'))
        {
            $submenu['parent_slug']=COMPRESSX_SLUG;
            $submenu['page_title']="Logs";
            $submenu['menu_title']="Logs";
            if(apply_filters('compressx_current_user_has',false,'compressx-can-use-logs'))
            {
                $submenu['capability']="compressx-can-use-logs";
            }
            else
            {
                $submenu['capability']="administrator";
            }
            $submenu['menu_slug']="logs-compressx";
            $submenu['index']=19;
            $submenu['function']=array($this->log, 'display');

            $submenus[$submenu['menu_slug']]=$submenu;
        }

        if(apply_filters('compressx_current_user_can',true,'compressx-can-use-system-info'))
        {
            $submenu['parent_slug']=COMPRESSX_SLUG;
            $submenu['page_title']="System Information";
            $submenu['menu_title']="System Information";
            if(apply_filters('compressx_current_user_has',false,'compressx-can-use-system-info'))
            {
                $submenu['capability']="compressx-can-use-system-info";
            }
            else
            {
                $submenu['capability']="administrator";
            }
            $submenu['menu_slug']="info-compressx";
            $submenu['index']=20;
            $submenu['function']=array($this->system_info, 'display');

            $submenus[$submenu['menu_slug']]=$submenu;
        }

        $submenu['parent_slug']=COMPRESSX_SLUG;
        $submenu['page_title']="Addons";
        $submenu['menu_title']="Addons";
        $submenu['capability']="administrator";
        $submenu['menu_slug']="addons-compressx";
        $submenu['index']=21;
        $submenu['function']=array($this->addons, 'display');

        $submenus[$submenu['menu_slug']]=$submenu;

        $submenus = apply_filters('compressx_get_admin_menus', $submenus);
        usort($submenus, function ($a, $b)
        {
            if ($a['index'] == $b['index'])
                return 0;

            if ($a['index'] > $b['index'])
                return 1;
            else
                return -1;
        });

        foreach ($submenus as $submenu)
        {
            add_submenu_page(
                $submenu['parent_slug'],
                $submenu['page_title'],
                $submenu['menu_title'],
                $submenu['capability'],
                $submenu['menu_slug'],
                $submenu['function']);
        }
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
                <h2>Compress<span style="color:#175cff;">X</span><span style="font-size:1.2rem;">.io</span>
                <span class="compressx-pro-version" style="font-size:0.7rem;">
                    <span><?php echo COMPRESSX_VERSION;?></span><span> FREE</span>
                    <?php
                    if($this->check_update())
                    {
                        $url=get_admin_url().'plugins.php?s=compressx&plugin_status=all';
                        ?><span style="padding:0 0.2rem"></span><a href="<?php echo esc_url($url)?>">(Latest Version: <?php echo esc_html( $this->latest_version());?>)</a><?php
                    }
                    ?>
                </span>
                </h2>
                <ul class="cx-menu-ul-large">
                    <li><a href="https://compressx.io/docs/compressx-overview/"><strong><?php esc_html_e('Documentation','compressx')?></strong></a></li>
                    <li><a href="https://compressx.io/docs/troubleshooting/"><strong><?php esc_html_e('Troubleshooting','compressx')?></strong></a></li>
                    <li><a href="https://wordpress.org/support/plugin/compressx/"><strong><?php esc_html_e('Support','compressx')?></strong></a></li>
                </ul>
                <ul class="cx-menu-ul-mini">
                    <li><a href="">Documentation</a></li>
                </ul>
            </div>
        </nav>
        <?php
    }

    public function check_update()
    {
        $update_data = wp_get_update_data();
        if (isset($update_data['counts']['plugins']))
        {
            $plugins = get_site_transient('update_plugins');
            //var_dump($plugins);
            if (isset($plugins->response[COMPRESSX_NAME]))
            {
                $plugin_data = $plugins->response[COMPRESSX_NAME];
                $latest_version = $plugin_data->new_version;
                if(version_compare($latest_version,COMPRESSX_VERSION,'>'))
                {
                    return true;
                }
            }
        }


        return false;
    }

    public function latest_version()
    {
        $plugins = get_site_transient('update_plugins');
        if (isset($plugins->response[COMPRESSX_NAME]))
        {
            $plugin_data = $plugins->response[COMPRESSX_NAME];
            return $plugin_data->new_version;
        }
        else
        {
            return '';
        }
    }

    public function output_header()
    {
        ?>
        <header>
            <div class="compressx-container compressx-header">

            </div>
        </header>
        <?php
    }

    public function output_footer()
    {
        ?>
        <footer>
            <div class="compressx-container compressx-menu">
                <div style="margin: auto;"><strong><span>If you like our plugin, a <a href="https://wordpress.org/support/plugin/compressx/reviews/?filter=5#new-post"><span class="dashicons dashicons-star-filled" style="color:#ffb900;"></span>
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