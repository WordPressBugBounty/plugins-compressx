<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

class CompressX_Advanced_Compression_Level_Display
{
    public function __construct()
    {
        //add_action('wp_ajax_compressx_save_compression', array($this, 'save_compression'));
    }

    public function display()
    {
        ?>
        <div id="compressx-root">
            <div id="compressx-wrap">
                <?php
                $this->output_nav();
                $this->output_header();
                $this->output_advanced_custom_compression_level();
                $this->output_footer();
                ?>
            </div>
        </div>
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

    public function output_footer()
    {
        do_action('compressx_output_footer');
    }

    public function output_advanced_custom_compression_level()
    {
        $options=CompressX_Options::get_option('compressx_quality',array());
        $quality=isset($options['quality'])?$options['quality']:'lossy';
        $quality_webp=isset($options['quality_webp'])?$options['quality_webp']: 80;
        $quality_avif=isset($options['quality_avif'])?$options['quality_avif']: 60;
        ?>
        <section>
            <div class="compressx-container compressx-section ">
                <div class="compressx-general-settings">
                    <div class="compressx-general-settings-header"  style="position: relative;">
                        <h5><span>Advanced Custom Compression Level Settings</span></h5>
                    </div>
                    <div class="compressx-general-settings-body">
                        <div class="compressx-general-settings-body-grid">
                            <div>
                                <span>
                                    <span class="dashicons dashicons-admin-site"></span><strong>Global compression level</strong>
                                </span>
                                <p style="padding-left: 2rem;">
                                    <span>Webp<input id="cx_quality_webp" type="text" style="width: 2.5rem;" value="<?php echo esc_attr($quality_webp); ?>"></span><span style="padding: 0 0.5rem;"></span>
                                    <span>AVIF<input id="cx_quality_avif" type="text" style="width: 2.5rem;" value="<?php echo esc_attr($quality_avif); ?>"></span>
                                </p>
                            </div>
                            <div>
                                <div class="compressing-converting-information">

                                        <span><strong>Compression Levels by Categories</strong> have <strong>higher</strong> priority than the <strong>global compression level.</strong>
                                        This means that images assigned to specific categories will use their category's compression level to be processed, while all other images will fall back to the global compression setting.</span><br>
                                    <span>You can choose which image categories should receive special compression treatment.</span>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div>
                        <p>
                            <strong><span class="dashicons dashicons-wordpress" style="color:#00749C"></span><span>Compression Levels by Category (WordPress Standard Image Types)</span></strong>
                            <span><a href="https://compressx.io">(Pro only)</a></span>
                        </p>
                        <div class="compressx-table" style="padding-bottom: 2rem;">
                            <div class="compressx-table-3-cols-header">
                                <div><strong><input type="checkbox" title="Check all" class="cx-table-all-check" disabled>Image Usage Type</strong></div>
                                <div><strong>Webp/AVIF</strong></div>
                                <div><strong>Recommended Compression Level</strong></div>
                            </div>
                            <?php
                            $list=$this->get_advanced_custom_compression_list();
                            foreach ($list as $item)
                            {
                                if($item['enable'])
                                {
                                    $quality_enbale="checked";
                                }
                                else
                                {
                                    $quality_enbale="";
                                }
                                ?>
                                <div class="compressx-table-3-cols-body">
                                    <div>
                                        <input class="cx-table-check" type="checkbox" <?php echo esc_attr($quality_enbale); ?> option="cx_advanced_quality_enable" name="<?php echo esc_attr($item['type']); ?>" disabled>
                                        <span><strong><?php echo esc_html($item['display']); ?></strong></span>
                                    </div>
                                    <div>
                                        <input type="text" style="width: 2.5rem;" option="cx_advanced_webp_quality" name="<?php echo esc_attr($item['type']); ?>" value="<?php echo esc_attr($item['webp']); ?>" disabled>
                                        <input type="text" style="width: 2.5rem;" option="cx_advanced_avif_quality" name="<?php echo esc_attr($item['type']); ?>" value="<?php echo esc_attr($item['avif']); ?>" disabled>
                                    </div>
                                    <div>
                                    <span class="compressx-compression-level-items">
                                        <?php echo $item['description']; ?>
                                    </span>
                                    </div>
                                </div>
                                <?php
                            }
                            ?>
                            <div class="compressx-table-3-cols-footer">
                                <div><strong><input type="checkbox" title="Check all" class="cx-table-all-check" disabled>Image Usage Type</strong></div>
                                <div><strong>Webp/AVIF</strong></div>
                                <div><strong>Recommended Compression Level</strong></div>
                            </div>
                        </div>
                        <p>
                            <strong><span class="dashicons dashicons-cart" style="color:#96588a"></span>Compression Levels by Category (WooCommerce Image Type)</strong>
                            <span><a href="https://compressx.io">(Pro only)</a></span>
                        </p>
                        <div class="compressx-table">
                            <div class="compressx-table-3-cols-header">
                                <div><strong><input type="checkbox" title="Check all" class="cx-woo-table-all-check" disabled>Image Usage Type</strong></div>
                                <div><strong>Webp/AVIF</strong></div>
                                <div><strong>Recommended Compression Level</strong></div>
                            </div>
                            <?php
                            $list=$this->get_advanced_custom_woocommerce_compression_list();
                            foreach ($list as $item)
                            {
                                if($item['enable'])
                                {
                                    $quality_enbale="checked";
                                }
                                else
                                {
                                    $quality_enbale="";
                                }
                                ?>
                                <div class="compressx-table-3-cols-body">
                                    <div>
                                        <input class="cx-woo-table-check" type="checkbox" <?php echo esc_attr($quality_enbale); ?> option="cx_advanced_quality_enable" name="<?php echo esc_attr($item['type']); ?>" disabled>
                                        <span><strong><?php echo esc_html($item['display']); ?></strong></span>
                                    </div>
                                    <div>
                                        <input type="text" style="width: 2.5rem;" option="cx_advanced_webp_quality" name="<?php echo esc_attr($item['type']); ?>" value="<?php echo esc_attr($item['webp']); ?>" disabled>
                                        <input type="text" style="width: 2.5rem;" option="cx_advanced_avif_quality" name="<?php echo esc_attr($item['type']); ?>" value="<?php echo esc_attr($item['avif']); ?>" disabled>
                                    </div>
                                    <div>
                                    <span class="compressx-compression-level-items">
                                        <?php echo $item['description']; ?>
                                    </span>
                                    </div>
                                </div>
                                <?php
                            }
                            ?>
                            <div class="compressx-table-3-cols-footer">
                                <div><strong><input type="checkbox" title="Check all" class="cx-woo-table-all-check" disabled>Image Usage Type</strong></div>
                                <div><strong>Webp</strong></div>
                                <div><strong>AVIF</strong></div>
                            </div>
                            <div class="compressx-compression-level">
                                <div class="compressx-compression-level-items"><span class="compressx-compression-level-item-high" style="background-color:#4caf50"></span><span><strong>High Quality</strong></span></div>
                                <div class="compressx-compression-level-items"><span class="compressx-compression-level-item-high" style="background-color:#ffeb3b"></span><span><strong>Balanced Quality</strong></span></div>
                                <div class="compressx-compression-level-items"><span class="compressx-compression-level-item-high" style="background-color:#ff9800"></span><span><strong>Medium Quality</strong></span></div>
                                <div class="compressx-compression-level-items"><span class="compressx-compression-level-item-high" style="background-color:#f44336"></span><span><strong>Aggressive Quality</strong></span></div>
                            </div>
                        </div>
                        <div class="compressx-general-settings-footer">
                            <input class="button-primary cx-button" id="compressx_save_advance_compression" type="submit" value="Save Changes">
                            <span style="padding:0 0.2rem;"></span>
                            <span id="compressx_save_advance_progress" style="display: none">
                            <img src="../wp-admin/images/loading.gif" alt="">
                        </span>
                            <span id="compressx_save_advance_text" class="success hidden" aria-hidden="true" style="color:#007017"><?php esc_html_e('Saved!','compressx')?></span>
                        </div>
                    </div>
                </div>
            </div>
        </section>
        <script>
            //
            jQuery('.cx-table-all-check').click(function()
            {
                var check=true;
                if(jQuery(this).prop('checked'))
                {
                    check = true;
                }
                else {
                    check = false;
                }

                jQuery('input[type="checkbox"].cx-table-check').each(function()
                {
                    jQuery(this).prop('checked',check);
                });
            });

            jQuery('.cx-woo-table-all-check').click(function()
            {
                var check=true;
                if(jQuery(this).prop('checked'))
                {
                    check = true;
                }
                else {
                    check = false;
                }

                jQuery('input[type="checkbox"].cx-woo-table-check').each(function()
                {
                    jQuery(this).prop('checked',check);
                });
            });

            jQuery('#compressx_save_advance_compression').click(function()
            {
                compressx_save_advance_compression();
            });

            function compressx_save_advance_compression()
            {
                var webp_quality = jQuery('#cx_quality_webp').val();
                var avif_quality = jQuery('#cx_quality_avif').val();

                var ajax_data = {
                    'action': 'compressx_save_compression',
                    'webp_quality': webp_quality,
                    'avif_quality': avif_quality
                };
                jQuery('#compressx_save_advance_compression').css({'pointer-events': 'none', 'opacity': '0.4'});
                jQuery('#compressx_save_advance_progress').show();

                compressx_post_request(ajax_data, function (data)
                {
                    try
                    {
                        var jsonarray = jQuery.parseJSON(data);

                        jQuery('#compressx_save_advance_compression').css({'pointer-events': 'auto', 'opacity': '1'});
                        jQuery('#compressx_save_advance_progress').hide();
                        if (jsonarray.result === 'success')
                        {
                            jQuery('#compressx_save_advance_text').removeClass("hidden");
                            setTimeout(function ()
                            {
                                jQuery('#compressx_save_advance_text').addClass( 'hidden' );
                            }, 3000);
                        }

                    }
                    catch (err)
                    {
                        alert(err);
                        jQuery('#compressx_save_advance_compression').css({'pointer-events': 'auto', 'opacity': '1'});
                        jQuery('#compressx_save_advance_progress').hide();
                    }
                }, function (XMLHttpRequest, textStatus, errorThrown)
                {
                    jQuery('#compressx_save_advance_compression').css({'pointer-events': 'auto', 'opacity': '1'});
                    jQuery('#compressx_save_advance_progress').hide();
                    var error_message = compressx_output_ajaxerror('changing settings', textStatus, errorThrown);
                    alert(error_message);
                });
            }
        </script>
        <?php
    }

    public function get_advanced_custom_compression_list()
    {
        $advanced_quality_list=CompressX_Options::get_default_advanced_quality_option();
        $display_list=array();
        foreach ($advanced_quality_list as $type=>$item)
        {
            $item['type']=$type;
            $item['display']=$this->get_advanced_quality_display_name($type);
            $item['description']=$this->get_advanced_quality_description($type);
            $display_list[]=$item;
        }

        return $display_list;
    }

    public function get_advanced_custom_woocommerce_compression_list()
    {
        $advanced_quality_list=CompressX_Options::get_default_advanced_woocommerce_quality_option();
        $display_list=array();
        foreach ($advanced_quality_list as $type=>$item)
        {
            $item['type']=$type;
            $item['display']=$this->get_advanced_quality_display_name($type);
            $item['description']=$this->get_advanced_quality_description($type);
            $display_list[]=$item;
        }

        return $display_list;
    }

    public function get_advanced_quality_display_name($type)
    {
        $list= [
            // core
            'site_logo'            => ['display' => 'Site logo'],
            'header_background'    => ['display' => 'Header background image'],
            'featured_post'        => ['display' => 'Featured image (Post)'],
            'featured_page'        => ['display' => 'Featured image (Page)'],
            'sidebar_widget'       => ['display' => 'Image in sidebar'],
            'acf_image_fields'      => ['display' => 'Image field used via ACF(Advanced Custom Fields)'],

            // WooCommerce
            'wc_product_featured_images'   => ['display' => 'Product featured image'],
            'wc_product_gallery_images'    => ['display' => 'Product gallery image'],
            'wc_variation_images'          => ['display' => 'Variation-specific image'],
            'wc_product_category_images'   => ['display' => 'Category image (taxonomy thumbnail)'],
        ];
        return isset($list[$type]['display'])?$list[$type]['display']:'';
    }

    public function get_advanced_quality_description($type)
    {
        $list= [
            // core
            'site_logo'            => ['level' => 'high','text'=>'Webp (90+), AVIF (90+)---branding clarity is essential'],
            'header_background'    => ['level' => 'mid','text'=>'Webp (75-85), AVIF (75-85)---may span full width, retain clarity'],
            'featured_post'        => ['level' => 'high','text'=>'Webp (85-90), AVIF (85-90)---visually impactful, often shared'],
            'featured_page'        => ['level' => 'high','text'=>'Webp (85-90), AVIF (85-90)---visually impactful, often shared'],
            'sidebar_widget'       => ['level' => 'aggressive','text'=>'Webp (60-75), AVIF (60-75)---small visual element'],
            'acf_image_fields'      => ['level' => 'balanced','text'=>'Webp (70-80), AVIF (70-80)---depends on context, often supportive'],

            // WooCommerce
            'wc_product_featured_images'   => ['level' => 'high','text'=>'Webp (85-90), AVIF (85-90)---brand/visual integrity important'],
            'wc_product_gallery_images'    => ['level' => 'mid','text'=>'Webp (75-85), AVIF (75-85)---visual but secondary to main image'],
            'wc_variation_images'          => ['level' => 'mid','text'=>'Webp (75-85), AVIF (75-85)---often similar to gallery use'],
            'wc_product_category_images'   => ['level' => 'balanced','text'=>'Webp (70-80), AVIF (70-80)---smaller, not full focus'],
        ];

        if(isset($list[$type]))
        {
            $html='';
            if($list[$type]['level']=='high')
            {
                $html.='<span class="compressx-compression-level-item-high" style="background-color:#4caf50"></span>';
                $html.='<span><strong>High</strong>';
            }
            else if($list[$type]['level']=='mid')
            {
                $html.='<span class="compressx-compression-level-item-high" style="background-color:#ff9800"></span>';
                $html.='<span><strong>Medium</strong>';
            }
            else if($list[$type]['level']=='aggressive')
            {
                $html.='<span class="compressx-compression-level-item-high" style="background-color:#f44336"></span>';
                $html.='<span><strong>Aggressive</strong>';
            }
            else if($list[$type]['level']=='balanced')
            {
                $html.='<span class="compressx-compression-level-item-high" style="background-color:#ffeb3b"></span>';
                $html.='<span><strong>Balanced</strong>';
            }

            $html.='<span>: '.$list[$type]['text'].'</span></span>';
            return $html;
        }
        else
        {
            return '';
        }
    }

    public function save_compression()
    {
        global $compressx;
        $compressx->ajax_check_security('compressx-can-use-general-settings');

        if(!isset($_POST['webp_quality']))
        {
            die();
        }

        if(!isset($_POST['avif_quality']))
        {
            die();
        }

        $webp_quality = sanitize_text_field($_POST['webp_quality']);
        $avif_quality = sanitize_text_field($_POST['avif_quality']);

        $options=CompressX_Options::get_option('compressx_quality',array());

        $options['quality_webp']=intval($webp_quality);
        $options['quality_avif']=intval($avif_quality);

        CompressX_Options::update_option('compressx_quality',$options);

        $ret['result']='success';
        echo wp_json_encode($ret);
        die();
    }
}