<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

class CompressX_System_Info
{
    public function __construct()
    {
        add_action('wp_ajax_compressx_create_debug_package',array( $this,'create_debug_package'));
        add_action('wp_ajax_compressx_send_debug_info',array( $this,'send_debug_info'));

        //
    }

    public function display()
    {
        ?>
        <div id="compressx-root">
            <div id="compressx-wrap">
                <?php
                $this->output_nav();
                $this->output_header();
                $this->output_debug();
                $this->output_footer();
                ?>
            </div>
        </div>
        <script>
            <?php
            if(isset($_REQUEST['check_environment']))
            {
                ?>
            jQuery(document).ready(function ($)
            {
                var top = jQuery("#compressx_download_debug_info").offset().top-jQuery("#compressx_download_debug_info").height();
                jQuery('html, body').animate({scrollTop:top}, 'slow');
            });
                <?php
            }
            ?>
        </script>
        <?php
    }

    public function output_nav()
    {
        do_action('compressx_output_nav');
    }

    public function output_header()
    {
        do_action('compressx_output_header');
    }

    public function output_debug()
    {
        ?>
        <section>
            <div class="compressx-container compressx-section ">
                <div class="compressx-bulk-log">
                    <article>
                        <div style="padding: 0 0 20px 10px;">
                            <?php esc_html_e('There are two ways available to send us the debug information. The first one is recommended.', 'compressx'); ?>
                        </div>
                        <div style="padding-left: 10px;">
                            <strong><?php esc_html_e('Method 1.', 'compressx'); ?></strong> <?php esc_html_e('If you have configured SMTP on your site, enter your email address and click the button below to send us the relevant information (website info and errors logs) when you are encountering errors. This will help us figure out what happened. Once the issue is resolved, we will inform you by your email address.', 'compressx'); ?>
                        </div>
                        <div style="padding:10px 10px 0">
                    <span>
                        <?php esc_html_e('CompressX support email:', 'compressx'); ?>
                    </span><input type="text" id="compressx_support_mail" value="support@compressx.io" readonly />
                            <span>
                        <?php esc_html_e('Your email:', 'compressx'); ?>
                    </span>
                            <input type="text" id="compressx_user_mail" />
                        </div>
                        <div style="padding:10px 10px;">
                            <textarea id="compressx_debug_comment" class="wp-editor-area" style="width:100%; height: 200px;" autocomplete="off" cols="60" placeholder="<?php esc_attr_e('Please describe your problem here.', 'compressx'); ?>" ></textarea>
                        </div>
                        <div style="padding:10px 10px;">
                            <input id="compressx_debug_submit" class="button-primary" type="submit" value="<?php esc_attr_e( 'Send Debug Information to Us', 'compressx' ); ?>" />
                            <span id="compressx_send_success_text" class="success hidden" aria-hidden="true" style="color:#007017"><?php esc_html_e('Send succeeded.','compressx')?></span>
                        </div>
                        <div style="clear:both;"></div>
                        <div style="padding-left: 10px;">
                            <strong><?php esc_html_e('Method 2.', 'compressx'); ?></strong> <?php esc_html_e('If you didn’t configure SMTP on your site, click the button below to download the relevant information (website info and error logs) to your PC when you are encountering some errors. Sending the files to us will help us diagnose what happened.', 'compressx'); ?>
                        </div>
                        <div style="padding: 10px 10px;">
                            <input class="button-primary" id="compressx_download_debug_info" type="submit" value="<?php esc_attr_e( 'Download', 'compressx' ); ?>" />
                        </div>
                    </article>
                </div>
            </div>
        </section>
        <section>
            <div class="compressx-container compressx-section ">
                <div class="compressx-bulk-log" style="padding-bottom: 0;">
                    <?php
                    $this->output_debug_info();
                    ?>
                </div>
            </div>
        </section>
        <?php
    }

    public function output_debug_info()
    {
        ?>
        <article>
            <div class="cx-title">
                <span><?php esc_html_e('Debug Information', 'compressx'); ?>: </span>
            </div>
            <div class="cx-table-overflow">
                <table class="wp-list-table widefat striped">
                    <thead class="website-info-head">
                    <tr>
                        <th class="row-title" style="min-width: 260px;" colspan="3"><?php esc_html_e('Debug Information', 'compressx'); ?></th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php
                    $debug_info=$this->get_debug_info();
                    if(!empty($debug_info))
                    {
                        foreach ($debug_info as $key=>$value)
                        {
                            if($key=='setting')
                                continue;

                            $website_value='';
                            if (is_array($value))
                            {
                                foreach ($value as $arr_value)
                                {
                                    if (empty($website_value))
                                    {
                                        $website_value = $website_value . $arr_value;
                                    } else {
                                        $website_value = $website_value . ', ' . $arr_value;
                                    }
                                }
                            }
                            else
                            {
                                if($value === true || $value === false)
                                {
                                    if($value === true) {
                                        $website_value = 'true';
                                    }
                                    else{
                                        $website_value = 'false';
                                    }
                                }
                                else {
                                    $website_value = $value;
                                }
                            }
                            ?>
                            <tr>
                                <td class="row-title tablelistcolumn"><label for="tablecell"><?php echo esc_html($key); ?></label></td>
                                <td class="tablelistcolumn"><?php echo esc_html($website_value); ?></td>
                            </tr>
                            <?php
                        }
                    }
                    ?>
                    </tbody>
                </table>
            </div>
        </article>
        <?php
    }

    public function get_debug_info()
    {
        global $wp_version;
        $debug_info=array();

        $debug_info['home']=$this->get_domain();
        $debug_info['Server name']=$_SERVER['SERVER_NAME'];
        $debug_info['Web Server'] = sanitize_text_field($_SERVER["SERVER_SOFTWARE"]);
        $debug_info['CompressX version'] = COMPRESSX_VERSION;
        $debug_info['Wordpress version'] = $wp_version;

        $debug_info['PHP version'] = phpversion();

        if( !function_exists( 'gd_info' ))
        {
            $debug_info['GD extension']=__("GD extension is not installed or enabled.",'compressx');
        }
        else
        {
            $debug_info['GD extension']=__("The GD extension has been properly installed.",'compressx');

            $info=gd_info();
            $debug_info['GD Version']=isset($info['GD Version'])?$info['GD Version']:'';
            $debug_info['GD WebP Support']=isset($info['WebP Support'])?$info['WebP Support']:false;
            $debug_info['GD AVIF Support']=isset($info['AVIF Support'])?$info['AVIF Support']:false;
        }

        if ( extension_loaded( 'imagick' ) && class_exists( '\Imagick' ) )
        {
            $debug_info['Imagick extension']=__("The imagick extension has been properly installed.",'compressx');
            $debug_info['Imagick Version']=\Imagick::getVersion();

            if( \Imagick::queryformats( 'WEBP' ))
            {
                $debug_info['Imagick WebP Support']=true;
            }
            else
            {
                $debug_info['Imagick WebP Support']=false;
            }

            if( \Imagick::queryformats( 'AVIF' ))
            {
                $debug_info['Imagick AVIF Support']=true;
            }
            else
            {
                $debug_info['Imagick AVIF Support']=false;
            }

        }
        else
        {
            $debug_info['Imagick extension']=__("Imagick extension is not installed or enabled.",'compressx');
        }

        $debug_info['active_plugins'] = get_option('active_plugins');
        $debug_info['active_theme'] = wp_get_theme()->get_template();

        $debug_info['memory_current'] = size_format(memory_get_usage(),2);
        $debug_info['memory_peak'] = size_format(memory_get_peak_usage(),2);
        $debug_info['memory_limit'] = ini_get('memory_limit');

        include_once COMPRESSX_DIR . '/includes/class-compressx-rewrite-checker.php';
        $test=new CompressX_Rewrite_Checker();
        $result=$test->test_ex();
        if($result['result']=='success')
        {
            $debug_info['Rewrite rules test'] = 'success';
        }
        else
        {
            $debug_info['Rewrite rules test'] = $result['error'];
        }

        $options=get_option('compressx_general_settings',array());
        $debug_info['setting']=$options;

        $quality_options=get_option('compressx_quality',array());
        $converter_method=get_option('compressx_converter_method',false);
        $output_format_webp=get_option('compressx_output_format_webp',1);
        $output_format_avif=get_option('compressx_output_format_avif',1);
        $debug_info['setting']['quality']=$quality_options;
        $debug_info['setting']['output_format_webp']=$output_format_webp;
        $debug_info['setting']['output_format_avif']=$output_format_avif;
        $debug_info['setting']['converter_method']=$converter_method;
        $debug_info['setting']=json_encode($debug_info['setting']);
        return $debug_info;
    }

    public function get_domain()
    {
        global $wpdb;
        $home_url = home_url();
        $db_home_url = home_url();
        $home_url_sql = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM $wpdb->options WHERE option_name = %s", 'home' ) );
        foreach ( $home_url_sql as $home )
        {
            $db_home_url = untrailingslashit($home->option_value);
        }

        if($home_url === $db_home_url)
        {
            $domain = $home_url;
        }
        else
        {
            $domain = $db_home_url;
        }

        return strtolower($domain);
    }

    public function output_footer()
    {
        do_action('compressx_output_footer');
    }

    public function get_website_info()
    {
        try
        {
            $version = COMPRESSX_VERSION;
            $ret['result'] = 'success';
            $ret['data']['version'] = $version;
            $ret['data']['home_url'] = get_home_url();
            $ret['data']['abspath'] = ABSPATH;
            $ret['data']['wp_content_path'] = WP_CONTENT_DIR;
            $ret['data']['wp_plugin_path'] = WP_PLUGIN_DIR;
            $ret['data']['active_plugins'] = get_option('active_plugins');

            global $wp_version;
            $ret['wp_version'] = $wp_version;
            if (is_multisite()) {
                $ret['data']['multisite'] = 'enable';
            } else {
                $ret['data']['multisite'] = 'disable';
            }
            $ret['data']['web_server'] = sanitize_text_field($_SERVER["SERVER_SOFTWARE"]);
            $ret['data']['php_version'] = phpversion();
            global $wpdb;
            $ret['data']['mysql_version'] = $wpdb->db_version();
            if (defined('WP_DEBUG')) {
                $ret['data']['wp_debug'] = WP_DEBUG;
            } else {
                $ret['wp_debug'] = false;
            }
            $ret['data']['language'] = get_bloginfo('language');
            $ret['data']['upload_max_filesize'] = ini_get("upload_max_filesize");

            $current_offset = get_option( 'gmt_offset' );
            $timezone       = get_option( 'timezone_string' );

            if ( false !== strpos( $timezone, 'Etc/GMT' ) ) {
                $timezone = '';
            }

            if ( empty( $timezone ) ) {
                if ( 0 == $current_offset ) {
                    $timezone = 'UTC+0';
                } elseif ( $current_offset < 0 ) {
                    $timezone = 'UTC' . $current_offset;
                } else {
                    $timezone = 'UTC+' . $current_offset;
                }
            }

            $ret['data']['max_execution_time'] = ini_get("max_execution_time");
            $ret['data']['max_input_vars'] = ini_get("max_input_vars");
            $ret['data']['max_input_vars'] = ini_get("max_input_vars");
            $ret['data']['timezone'] = $timezone;//date_default_timezone_get();
            if(function_exists('php_uname'))
            {
                $ret['data']['OS'] = php_uname();
            }
            $ret['data']['memory_current'] = size_format(memory_get_usage(),2);
            $ret['data']['memory_peak'] = size_format(memory_get_peak_usage(),2);
            $ret['data']['memory_limit'] = ini_get('memory_limit');
            $ret['data']['post_max_size'] = ini_get('post_max_size');
            $ret['data']['allow_url_fopen'] = ini_get('allow_url_fopen');
            $ret['data']['safe_mode'] = ini_get('safe_mode');
            $ret['data']['pcre.backtrack_limit'] = ini_get('pcre.backtrack_limit');
            $extensions = get_loaded_extensions();
            if (array_search('exif', $extensions)) {
                $ret['data']['exif'] = 'support';
            } else {
                $ret['data']['exif'] = 'not support';
            }

            if (array_search('xml', $extensions)) {
                $ret['data']['xml'] = 'support';
            } else {
                $ret['data']['xml'] = 'not support';
            }

            if (array_search('suhosin', $extensions)) {
                $ret['data']['suhosin'] = 'support';
            } else {
                $ret['data']['suhosin'] = 'not support';
            }

            if (array_search('gd', $extensions)) {
                $ret['data']['IPTC'] = 'support';
            } else {
                $ret['data']['IPTC'] = 'not support';
            }

            $ret['data']['extensions'] = $extensions;

            if (function_exists('apache_get_modules')) {
                $ret['data']['apache_modules'] = apache_get_modules();
            } else {
                $ret['data']['apache_modules'] = array();
            }

            if (array_search('pdo_mysql', $extensions))
            {
                $ret['data']['pdo_mysql'] = 'support';
            } else {
                $ret['data']['pdo_mysql'] = 'not support';
            }

            $ret['data']['mysql_mode'] = '';
            global $wpdb;
            $result = $wpdb->get_results('SELECT @@SESSION.sql_mode', ARRAY_A);
            foreach ($result as $row)
            {
                $ret['data']['mysql_mode'] = $row["@@SESSION.sql_mode"];
            }

            if (!class_exists('PclZip')) include_once(ABSPATH . '/wp-admin/includes/class-pclzip.php');
            if (!class_exists('PclZip'))
            {
                $ret['data']['PclZip'] = 'not support';
            } else {
                $ret['data']['PclZip'] = 'support';
            }

            if (is_multisite() && !defined('MULTISITE')) {
                $prefix = $wpdb->base_prefix;
            } else {
                $prefix = $wpdb->get_blog_prefix(0);
            }

            $ret['data']['wp_prefix'] = $prefix;

            $sapi_type = php_sapi_name();

            if ($sapi_type == 'cgi-fcgi' || $sapi_type == ' fpm-fcgi') {
                $ret['data']['fast_cgi'] = 'On';
            } else {
                $ret['data']['fast_cgi'] = 'Off';
            }
        }
        catch (Exception $error) {
            $message = 'An exception has occurred. class: '.get_class($error).';msg: '.$error->getMessage().';code: '.$error->getCode().';line: '.$error->getLine().';in_file: '.$error->getFile().';';
            error_log($message);
            return array('result'=>'failed','error'=>$message);
        }
        return $ret;
    }

    public function create_debug_package()
    {
        global $compressx;
        $compressx->ajax_check_security('compressx-can-use-system-info');

        try
        {
            if (!class_exists('PclZip'))
                include_once(ABSPATH . '/wp-admin/includes/class-pclzip.php');

            $backup_path="compressx";

            $path = WP_CONTENT_DIR . DIRECTORY_SEPARATOR . $backup_path . DIRECTORY_SEPARATOR . 'compressx_debug.zip';
            if(!is_dir(WP_CONTENT_DIR.DIRECTORY_SEPARATOR.$backup_path))
            {
                @mkdir(WP_CONTENT_DIR.DIRECTORY_SEPARATOR.$backup_path,0777,true);
                @fopen(WP_CONTENT_DIR.DIRECTORY_SEPARATOR.$backup_path.DIRECTORY_SEPARATOR.'index.html', 'x');
                $tempfile=@fopen(WP_CONTENT_DIR.DIRECTORY_SEPARATOR.$backup_path.DIRECTORY_SEPARATOR.'.htaccess', 'x');
                if($tempfile)
                {
                    $text="deny from all";
                    fwrite($tempfile,$text );
                    fclose($tempfile);
                }
            }

            if (file_exists($path)) {
                @wp_delete_file($path);
            }


            $archive = new PclZip($path);

            $server_info = wp_json_encode($this->get_debug_info());
            $server_file_path = WP_CONTENT_DIR . DIRECTORY_SEPARATOR . $backup_path . DIRECTORY_SEPARATOR . 'compressx_server_info.json';
            if (file_exists($server_file_path)) {
                @wp_delete_file($server_file_path);
            }
            $server_file = fopen($server_file_path, 'x');
            fclose($server_file);
            file_put_contents($server_file_path, $server_info);
            if (!$archive->add($server_file_path, PCLZIP_OPT_REMOVE_ALL_PATH))
            {
                exit;
            }
            @wp_delete_file($server_file_path);

            $log=new CompressX_Log();
            $files=$log->get_logs();
            if(!empty($files))
            {
                if(!$archive->add($files,PCLZIP_OPT_REMOVE_ALL_PATH))
                {
                    exit;
                }
            }

            if (session_id())
                session_write_close();

            $size = filesize($path);
            if (!headers_sent()) {
                header('Content-Description: File Transfer');
                header('Content-Type: application/zip');
                header('Content-Disposition: attachment; filename="' . basename($path) . '"');
                header('Cache-Control: must-revalidate');
                header('Content-Length: ' . $size);
                header('Content-Transfer-Encoding: binary');
            }


            ob_end_clean();
            readfile($path);
            @wp_delete_file($path);
        }
        catch (Exception $error) {
            $message = 'An exception has occurred. class: '.get_class($error).';msg: '.$error->getMessage().';code: '.$error->getCode().';line: '.$error->getLine().';in_file: '.$error->getFile().';';
            error_log($message);
            echo wp_json_encode(array('result'=>'failed','error'=>$message));
            die();
        }
        exit;
    }

    public function send_debug_info()
    {
        global $compressx;
        $compressx->ajax_check_security('compressx-can-use-system-info');

        try
        {
            if (!isset($_POST['user_mail']) || empty($_POST['user_mail']))
            {
                $ret['result'] = 'failed';
                $ret['error'] = __('User\'s email address is required.', 'compressx');
            }
            else
            {
                $pattern = '/^[a-z0-9]+([._-][a-z0-9]+)*@([0-9a-z]+\.[a-z]{2,14}(\.[a-z]{2})?)$/i';
                if (!preg_match($pattern, $_POST['user_mail']))
                {
                    $ret['result'] = 'failed';
                    $ret['error'] = __('Please enter a valid email address.', 'compressx');
                }
                else
                {
                    $user_mail=sanitize_email($_POST['user_mail']);
                    $comment=sanitize_text_field($_POST['comment']);

                    $ret =$this->_send_debug_info($user_mail,$comment);
                }
            }
            echo wp_json_encode($ret);
            die();
        }
        catch (Exception $error)
        {
            $message = 'An exception has occurred. class: '.get_class($error).';msg: '.$error->getMessage().';code: '.$error->getCode().';line: '.$error->getLine().';in_file: '.$error->getFile().';';
            error_log($message);
            echo wp_json_encode(array('result'=>'failed','error'=>$message));
            die();
        }
    }

    public function _send_debug_info($user_email,$comment)
    {
        $send_to = 'support@compressx.io';
        $subject = 'Debug Information';
        $body = '<div>User\'s email: '.$user_email.'.</div>';
        $body .= '<div>Comment: '.$comment.'.</div>';
        $headers = array('Content-Type: text/html; charset=UTF-8');

        if (!class_exists('PclZip'))
            include_once(ABSPATH . '/wp-admin/includes/class-pclzip.php');

        $backup_path="compressx";

        $path = WP_CONTENT_DIR . DIRECTORY_SEPARATOR . $backup_path . DIRECTORY_SEPARATOR . 'compressx_debug.zip';
        if(!is_dir(WP_CONTENT_DIR.DIRECTORY_SEPARATOR.$backup_path))
        {
            @mkdir(WP_CONTENT_DIR.DIRECTORY_SEPARATOR.$backup_path,0777,true);
            @fopen(WP_CONTENT_DIR.DIRECTORY_SEPARATOR.$backup_path.DIRECTORY_SEPARATOR.'index.html', 'x');
            $tempfile=@fopen(WP_CONTENT_DIR.DIRECTORY_SEPARATOR.$backup_path.DIRECTORY_SEPARATOR.'.htaccess', 'x');
            if($tempfile)
            {
                $text="deny from all";
                fwrite($tempfile,$text );
                fclose($tempfile);
            }
        }

        if (file_exists($path)) {
            @wp_delete_file($path);
        }

        $archive = new PclZip($path);

        $server_info = wp_json_encode($this->get_debug_info());
        $server_file_path = WP_CONTENT_DIR . DIRECTORY_SEPARATOR . $backup_path . DIRECTORY_SEPARATOR . 'compressx_server_info.json';
        if (file_exists($server_file_path))
        {
            @wp_delete_file($server_file_path);
        }
        $server_file = fopen($server_file_path, 'x');
        fclose($server_file);
        file_put_contents($server_file_path, $server_info);
        $archive->add($server_file_path, PCLZIP_OPT_REMOVE_ALL_PATH);
        @wp_delete_file($server_file_path);

        $log=new CompressX_Log();
        $files=$log->get_logs();
        if(!empty($files))
        {
            $archive->add($files,PCLZIP_OPT_REMOVE_ALL_PATH);
        }

        $attachments[] = $path;

        if(wp_mail( $send_to, $subject, $body,$headers,$attachments)===false)
        {
            $ret['result']='failed';
            $ret['error']=__('Unable to send email. Please check the configuration of email server.', 'compressx');
        }
        else
        {
            $ret['result']='success';
        }

        @wp_delete_file($path);
        return $ret;
    }
}