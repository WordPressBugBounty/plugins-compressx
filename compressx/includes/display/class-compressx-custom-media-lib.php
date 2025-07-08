<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

class CompressX_Custom_Media_Lib
{
    public function __construct()
    {
        if(is_multisite())
            return;

        add_filter( 'manage_media_columns', array($this,'optimize_columns'));
        add_action( 'manage_media_custom_column', array($this, 'optimize_column_display'),10,2);
        add_action( 'attachment_submitbox_misc_actions',  array( $this,'submitbox') );
        add_filter( 'wp_prepare_attachment_for_js', array($this,'attachment_fields_to_edit'), 10, 2 );

        //wp_prepare_attachment_for_js

        add_action( 'restrict_manage_posts', array($this,'add_dropdown') );
        add_action( 'pre_get_posts', array( $this, 'filter_posts' ) );
    }

    public function optimize_columns($defaults)
    {
        $defaults['compressx'] = 'CompressX';
        return $defaults;
    }

    public function optimize_column_display($column_name, $id )
    {
        if ( 'compressx' === $column_name )
        {
            echo wp_kses_post( $this->optimize_action_columns( $id ) );
        }
    }

    public function optimize_action_columns($id)
    {
        $allowed_mime_types = CompressX_Image_Opt_Method::supported_mime_types_ex();

        if ( ! wp_attachment_is_image( $id ) || ! in_array( get_post_mime_type( $id ),$allowed_mime_types ) )
        {
            return __('Not support','compressx');
        }

        $meta=CompressX_Image_Meta::get_image_meta($id);
        $html='<div class="cx-media-item" data-id="'.$id.'">';

        if(!CompressX_Image_Meta::is_image_optimized($id))
        {
            if($this->is_image_progressing($id))
            {
                $html.="<a  class='cx-media-progressing button' data-id='".esc_attr($id)."'>".__('Converting...','compressx')."</a>";
            }
            else
            {
                $html.="<a  class='cx-media button' data-id='".esc_attr($id)."'>".__('Convert','compressx')."</a>";
                if($this->is_image_processing_failed($id))
                {
                    foreach ($meta['size'] as $size_key => $size_data)
                    {
                        if(!empty($size_data['error']))
                        {
                            $html.='<p style="border-bottom:1px solid #D2D3D6;margin-top: 12px;"></p>';
                            $html.="<span>".esc_html($size_data['error'])."</span>";
                            break;
                        }
                    }
                }
            }
        }
        else
        {
            $convert_size=CompressX_Image_Meta::get_webp_converted_size($id);
            $og_size=CompressX_Image_Meta::get_og_size($id);
            if($og_size>0)
            {
                if($convert_size>0)
                {
                    $webp_percent = round(100 - ($convert_size / $og_size) * 100, 2);
                }
                else if(CompressX_Image_Meta::is_webp_image($id))
                {
                    $convert_size=CompressX_Image_Meta::get_compressed_size($id);
                    if($convert_size>0)
                    {
                        $webp_percent = round(100 - ($convert_size / $og_size) * 100, 2);
                    }
                    else
                    {
                        $webp_percent=0;
                    }
                }
                else if(CompressX_Image_Meta::is_avif_image($id))
                {
                    $webp_percent=0;
                }
                else
                {
                    $webp_percent=0;
                }
            }
            else
            {
                $webp_percent=0;
            }

            $avif_size=CompressX_Image_Meta::get_avif_converted_size($id);
            if($og_size>0)
            {
                if($avif_size>0)
                {
                    $avif_percent = round(100 - ($avif_size / $og_size) * 100, 2);
                }
                else if(CompressX_Image_Meta::is_avif_image($id))
                {
                    $avif_size=CompressX_Image_Meta::get_compressed_size($id);
                    if($avif_size>0)
                    {
                        $avif_percent = round(100 - ($avif_size / $og_size) * 100, 2);
                    }
                    else
                    {
                        $avif_percent=0;
                    }
                }
                else
                {
                    $avif_percent=0;
                }
            }
            else
            {
                $avif_percent=0;
            }

            $meta=CompressX_Image_Meta::get_image_meta($id);
            $thumbnail_counts=count($meta['size']);

            $html.='<ul>';
            $html.= '<li><span>'.__('Original','compressx').' : </span><strong>'.size_format($og_size,2).'</strong></li>';
            $html.= '<li><span>'.__('Webp','compressx').' : </span><strong>'.size_format($convert_size,2).'</strong><span> '.'Saved'.' : </span><strong>'.$webp_percent.'%</strong></li>';
            $html.= '<li><span>'.__('AVIF','compressx').' : </span><strong>'.size_format($avif_size,2).'</strong><span> '.'Saved'.' : </span><strong>'.$avif_percent.'%</strong></li>';
            $html.= '<li><span>'.__('Thumbnails generated','compressx').' : </span><strong>'.$thumbnail_counts.'</strong></li>';
            $html.="<li><a class='cx-media-delete button' data-id='".esc_attr($id)."'>".__('Delete','compressx')."</a>
<span class='compressx-dashicons-help compressx-tooltip'>
                                    <a href='#'><span class='dashicons dashicons-editor-help' style='padding-top: 3px;'></span></a>
                                    <div class='compressx-bottom'>
                                        <!-- The content you need -->
                                        <p>
                                            <span>".__('Delete the WebP and AVIF images generated by CompressX.','compressx')."</span><br>
                                        </p>
                                        <i></i> <!-- do not delete this line -->
                                    </div>
                                </span>
                                </li>";
            $html.='</ul>';
        }

        $html.='</div>';


        return $html;
    }

    public function add_dropdown()
    {
        $scr = get_current_screen();

        if ( 'upload' !== $scr->base )
        {
            return;
        }

        $filter = filter_input( INPUT_GET, 'compressx-filter', FILTER_SANITIZE_SPECIAL_CHARS );

        ?>
        <label for="compressx_filter" class="screen-reader-text">
            <?php esc_html_e( 'CompressX Filter', 'compressx' ); ?>
        </label>
        <select class="compressx-filters" name="compressx-filter" id="compressx_filter">
            <option value="" <?php selected( $filter, '' ); ?>><?php esc_html_e( 'CompressX: All images', 'compressx' ); ?></option>
            <option value="optimized" <?php selected( $filter, 'optimized' ); ?>><?php esc_html_e( 'CompressX: Optimized', 'compressx' ); ?></option>
            <option value="unoptimized" <?php selected( $filter, 'unoptimized' ); ?>><?php esc_html_e( 'CompressX: Unoptimized', 'compressx' ); ?></option>
            <option value="failed_optimized" <?php selected( $filter, 'failed_optimized' ); ?>><?php esc_html_e( 'CompressX: Optimization Failed', 'compressx' ); ?></option>
        </select>
        <?php
    }

    public function filter_posts($query)
    {
        global $current_screen;

        // Filter only media screen.
        if (! is_admin() || ( ! empty( $current_screen ) && 'upload' !== $current_screen->base ) || 'attachment' !== $query->get( 'post_type' )
        )
        {
            return $query;
        }

        $filter = filter_input( INPUT_GET, 'compressx-filter', FILTER_SANITIZE_SPECIAL_CHARS );

        if ( 'optimized' === $filter )
        {
            $query->set( 'meta_query', $this->query_optimized() );
            return $query;
        }
        else if ( 'unoptimized' === $filter )
        {
            $query->set( 'meta_query', $this->query_unoptimized() );
            return $query;
        }
        else if ( 'failed_optimized' === $filter )
        {
            $query->set( 'meta_query', $this->query_failed_optimized() );
            return $query;
        }

        return $query;
    }

    public function query_optimized()
    {
        $meta_query =  array(
            array(
                'key'     => 'compressx_image_meta_status',
                'value' => 'optimized',
            ),
        );

        return $meta_query;
    }

    public function query_unoptimized()
    {
        $meta_query =  array(
            'relation' => 'OR',
            array(
                'key'     => 'compressx_image_meta_status',
                'value' => 'pending',
            ),
            array(
                'key'     => 'compressx_image_meta_status',
                'compare' => 'NOT EXISTS',
            ),
        );

        return $meta_query;
    }

    public function query_failed_optimized()
    {
        $meta_query =  array(
            array(
                'key'     => 'compressx_image_meta_status',
                'value' => 'failed',
            ),
        );

        return $meta_query;
    }

    public function is_image_progressing($post_id)
    {
        $progressing=CompressX_Image_Meta::get_image_progressing($post_id);

        if(empty($progressing))
        {
            return false;
        }
        else
        {
            $current_time=time();
            if(($current_time-$progressing)>180)
            {
                return false;
            }
            else
            {
                return true;
            }
        }
    }

    public function is_image_processing_failed($post_id)
    {
        $status=CompressX_Image_Meta::get_image_meta_status($post_id);

        if(empty($status))
        {
            return false;
        }
        else
        {
            if($status=='failed')
            {
                return true;
            }
            else
            {
                return false;
            }
        }
    }

    public function submitbox()
    {
        global $post;
        $html='';

        $allowed_mime_types = CompressX_Image_Opt_Method::supported_mime_types_ex();

        if ( ! wp_attachment_is_image( $post->ID ) || ! in_array( get_post_mime_type( $post->ID ),$allowed_mime_types ) )
        {
            echo  esc_html__('Not support','compressx');
        }
        else
        {
            echo '<div class="misc-pub-section misc-pub-cx" data-id="' . esc_attr($post->ID) . '"><h4>' . esc_html__('CompressX','compressx') . '</h4>';

            if (!CompressX_Image_Meta::is_image_optimized($post->ID)) {
                if ($this->is_image_progressing($post->ID)) {
                    echo "<a  class='cx-media-progressing button' data-id='" . esc_attr($post->ID) . "'>" . esc_html__('Converting...','compressx') . "</a>";
                } else {
                    echo "<a  class='cx-media button' data-id='" . esc_attr($post->ID) . "'>" . esc_html__('Convert','compressx') . "</a>";
                }
            } else {

                $convert_size=CompressX_Image_Meta::get_webp_converted_size($post->ID);
                $og_size=CompressX_Image_Meta::get_og_size($post->ID);
                if($og_size>0&&$convert_size>0)
                {
                    $webp_percent = round(100 - ($convert_size / $og_size) * 100, 2);
                }
                else
                {
                    $webp_percent=0;
                }

                $avif_size=CompressX_Image_Meta::get_avif_converted_size($post->ID);
                if($og_size>0&&$avif_size>0)
                {
                    $avif_percent = round(100 - ($avif_size / $og_size) * 100, 2);
                }
                else
                {
                    $avif_percent=0;
                }

                $meta=CompressX_Image_Meta::get_image_meta($post->ID);
                $thumbnail_counts=count($meta['size']);

                echo '<ul>';
                echo '<li><span>' . esc_html__('Original','compressx') . ' : </span><strong>' . esc_html(size_format($og_size, 2)) . '</strong></li>';
                echo '<li><span>' . esc_html__('Webp','compressx') . ' : </span><strong>' . esc_html(size_format($convert_size,2)) . '</strong><span> '.'Saved'.' : </span><strong>'.esc_html($webp_percent).'%</strong></li>';
                echo '<li><span>' . esc_html__('AVIF','compressx'). ' : </span><strong>' . esc_html(size_format($avif_size,2)) . '</strong><span> '.'Saved'.' : </span><strong>'.esc_html($avif_percent).'%</strong></li>';
                echo '<li><span>'.esc_html__('Thumbnails generated','compressx').' : </span><strong>'.esc_html($thumbnail_counts).'</strong></li>';
                echo "<li><a class='cx-media-delete button' data-id='" . esc_attr($post->ID) . "'>". esc_html__('Delete','compressx') ."</a>
<span class='compressx-dashicons-help compressx-tooltip'>
                                    <a href='#'><span class='dashicons dashicons-editor-help' style='padding-top: 3px;'></span></a>
                                    <div class='compressx-bottom'>
                                        <!-- The content you need -->
                                        <p>
                                            <span>". esc_html__('Delete the WebP and AVIF images generated by CompressX.','compressx') ."</span><br>
                                        </p>
                                        <i></i> <!-- do not delete this line -->
                                    </div>
                                </span>
</li>";
                echo '</ul>';
            }

            echo '</div>';
        }
    }

    public function attachment_fields_to_edit(array $response, \WP_Post $attachment)
    {
        $source_post_id = (string) isset( $_REQUEST['post_id'])?$_REQUEST['post_id']:'';
        if ( $source_post_id !== '0' )
        {
            return $response;
        }

        $allowed_mime_types = CompressX_Image_Opt_Method::supported_mime_types_ex();

        if ( ! wp_attachment_is_image( $attachment->ID ) || ! in_array( get_post_mime_type( $attachment->ID ),$allowed_mime_types ) )
        {
            $html= 'Not support';
        }
        else
        {
            $html='<div class="cx-media-attachment" data-id="'.$attachment->ID.'">';

            if(!CompressX_Image_Meta::is_image_optimized($attachment->ID))
            {
                if($this->is_image_progressing($attachment->ID))
                {
                    $html.= "<a  class='cx-media cx-media-progressing button' data-id='{$attachment->ID}'>".__('Converting...','compressx')."</a>";
                }
                else
                {
                    $html.= "<a  class='cx-media button' data-id='{$attachment->ID}'>".__('Convert','compressx')."</a>";
                }
            }
            else
            {
                $convert_size=CompressX_Image_Meta::get_webp_converted_size($attachment->ID);
                $og_size=CompressX_Image_Meta::get_og_size($attachment->ID);
                if($og_size>0&&$convert_size>0)
                {
                    $webp_percent = round(100 - ($convert_size / $og_size) * 100, 2);
                }
                else
                {
                    $webp_percent=0;
                }

                $avif_size=CompressX_Image_Meta::get_avif_converted_size($attachment->ID);
                if($og_size>0&&$avif_size>0)
                {
                    $avif_percent = round(100 - ($avif_size / $og_size) * 100, 2);
                }
                else
                {
                    $avif_percent=0;
                }

                $meta=CompressX_Image_Meta::get_image_meta($attachment->ID);
                $thumbnail_counts=count($meta['size']);

                $html.='<ul>';
                $html.= '<li><span>'.__('Original','compressx').' : </span><strong>'.size_format($og_size,2).'</strong></li>';
                $html.= '<li><span>'.__('Webp','compressx').' : </span><strong>'.size_format($convert_size,2).'</strong><span> '.'Saved'.' : </span><strong>'.$webp_percent.'%</strong></li>';
                $html.= '<li><span>'.__('AVIF','compressx').' : </span><strong>'.size_format($avif_size,2).'</strong><span> '.'Saved'.' : </span><strong>'.$avif_percent.'%</strong></li>';
                $html.= '<li><span>'.__('Thumbnails generated','compressx').' : </span><strong>'.$thumbnail_counts.'</strong></li>';
                $html.="<li><a class='cx-media-delete button' data-id='".esc_attr($attachment->ID)."'>".__('Delete','compressx')."</a></li>";
            }
            $html.='</div>';
        }

        $response['compat']['meta'] .=$html;
        return $response;
    }
}