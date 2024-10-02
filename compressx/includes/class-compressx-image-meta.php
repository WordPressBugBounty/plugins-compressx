<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

class CompressX_Image_Meta
{
    public static function get_image_meta($image_id)
    {
        return get_post_meta( $image_id, 'compressx_image_meta', true );
    }

    public static function update_images_meta($image_id,$meta)
    {
        update_post_meta($image_id,'compressx_image_meta',$meta);
    }

    public static function generate_images_meta($image_id,$options)
    {
        $file_path = get_attached_file( $image_id );
        $image_optimize_meta=array();

        update_post_meta($image_id,'compressx_image_meta_status','unoptimized');
        update_post_meta($image_id,'compressx_image_meta_webp_converted',0);
        update_post_meta($image_id,'compressx_image_meta_avif_converted',0);
        update_post_meta($image_id,'compressx_image_meta_compressed',0);

        $image_optimize_meta['resize_status']=0;
        $image_optimize_meta['compress_status']=0;
        $image_optimize_meta['quality']=$options['quality'];

        $og_size=$image_optimize_meta['og_file_size']=filesize($file_path);

        $meta = wp_get_attachment_metadata( $image_id, true );
        if(empty($meta['sizes']))
        {
            $image_optimize_meta['size']['og']['compress_status']=0;
            $image_optimize_meta['size']['og']['convert_webp_status']=0;
            $image_optimize_meta['size']['og']['convert_avif_status']=0;
            $image_optimize_meta['size']['og']['status']="unoptimized";
            $image_optimize_meta['size']['og']['error']="";
            $image_optimize_meta['size']['og']['file'] = get_post_meta( $image_id, '_wp_attached_file', true );
        }
        else
        {
            $image_optimize_meta['size']['og']['compress_status']=0;
            $image_optimize_meta['size']['og']['convert_webp_status']=0;
            $image_optimize_meta['size']['og']['convert_avif_status']=0;
            $image_optimize_meta['size']['og']['status']="unoptimized";
            $image_optimize_meta['size']['og']['error']="";
            $image_optimize_meta['size']['og']['file']= get_post_meta( $image_id, '_wp_attached_file', true );

            foreach ( $meta['sizes'] as $size_key => $size_data )
            {
                $image_optimize_meta['size'][$size_key]['compress_status']=0;
                $image_optimize_meta['size'][$size_key]['convert_webp_status']=0;
                $image_optimize_meta['size'][$size_key]['convert_avif_status']=0;
                $image_optimize_meta['size'][$size_key]['status']="unoptimized";
                $image_optimize_meta['size'][$size_key]['error']="";

                $image_optimize_meta['size'][$size_key]['file']=$size_data['file'];
            }
        }

        update_post_meta($image_id,'compressx_image_meta_og_file_size',$og_size);
        update_post_meta($image_id,'compressx_image_meta_webp_converted_size',0);
        update_post_meta($image_id,'compressx_image_meta_avif_converted_size',0);
        update_post_meta($image_id,'compressx_image_meta_compressed_size',0);
        update_post_meta($image_id,'compressx_image_meta',$image_optimize_meta);

        return $image_optimize_meta;
    }

    public static function delete_image_mete($image_id)
    {
        delete_post_meta($image_id,'compressx_image_meta_status');
        delete_post_meta($image_id,'compressx_image_meta_webp_converted');
        delete_post_meta($image_id,'compressx_image_meta_avif_converted');
        delete_post_meta($image_id,'compressx_image_meta_og_file_size');
        delete_post_meta($image_id,'compressx_image_meta_webp_converted_size');
        delete_post_meta($image_id,'compressx_image_meta_avif_converted_size');
        delete_post_meta($image_id,'compressx_image_meta');
        delete_post_meta($image_id,'compressx_image_progressing');
    }

    public static function get_image_progressing($image_id)
    {
        return get_post_meta( $image_id, 'compressx_image_progressing', true );
    }

    public static function update_image_progressing($image_id)
    {
        update_post_meta($image_id,'compressx_image_progressing',time());
    }

    public static function delete_image_progressing($image_id)
    {
        delete_post_meta($image_id,'compressx_image_progressing');
    }

    public static function get_image_meta_status($image_id)
    {
        return get_post_meta( $image_id, 'compressx_image_meta_status', true );
    }

    public static function update_image_meta_status($image_id,$status)
    {
        update_post_meta($image_id,'compressx_image_meta_status',$status);
    }

    public static function get_image_meta_webp_converted($image_id)
    {
        $converted=get_post_meta($image_id,'compressx_image_meta_webp_converted',true);
        if(empty($converted))
        {
            return 0;
        }
        else
        {
            return $converted;
        }
    }

    public static function get_image_meta_avif_converted($image_id)
    {
        $converted=get_post_meta($image_id,'compressx_image_meta_avif_converted',true);
        if(empty($converted))
        {
            return 0;
        }
        else
        {
            return $converted;
        }
    }

    public static function get_image_meta_compressed($image_id)
    {
        $compressed=get_post_meta($image_id,'compressx_image_meta_compressed',true);
        if(empty($compressed))
        {
            return 0;
        }
        else
        {
            return $compressed;
        }
    }

    public static function update_image_meta_webp_converted($image_id,$converted)
    {
        update_post_meta($image_id,'compressx_image_meta_webp_converted',$converted);
    }

    public static function update_image_meta_avif_converted($image_id,$converted)
    {
        update_post_meta($image_id,'compressx_image_meta_avif_converted',$converted);
    }

    public static function update_image_meta_compressed($image_id,$compressed)
    {
        update_post_meta($image_id,'compressx_image_meta_compressed',$compressed);
    }

    public static function update_webp_converted_size($image_id,$convert_size)
    {
        update_post_meta($image_id,'compressx_image_meta_webp_converted_size',$convert_size);
    }

    public static function update_avif_converted_size($image_id,$convert_size)
    {
        update_post_meta($image_id,'compressx_image_meta_avif_converted_size',$convert_size);
    }

    public static function update_compressed_size($image_id,$compressed_size)
    {
        update_post_meta($image_id,'compressx_image_meta_compressed_size',$compressed_size);
    }

    public static function update_og_size($image_id,$size)
    {
        update_post_meta($image_id,'compressx_image_meta_og_file_size',$size);
    }

    public static function get_og_size($image_id)
    {
        $og_size=get_post_meta($image_id,'compressx_image_meta_og_file_size',true);
        if(empty($og_size))
        {
            $og_size= 0;
        }

        return $og_size;
    }

    public static function get_webp_converted_size($image_id)
    {
        $convert_size=get_post_meta($image_id,'compressx_image_meta_webp_converted_size',true);
        if(empty($convert_size))
        {
            $convert_size= 0;
        }

        return $convert_size;
    }

    public static function get_avif_converted_size($image_id)
    {
        $convert_size=get_post_meta($image_id,'compressx_image_meta_avif_converted_size',true);
        if(empty($convert_size))
        {
            $convert_size= 0;
        }

        return $convert_size;
    }

    public static function get_compressed_size($image_id)
    {
        $convert_size=get_post_meta($image_id,'compressx_image_meta_compressed_size',true);
        if(empty($convert_size))
        {
            $convert_size= 0;
        }

        return $convert_size;
    }

    public static function get_global_stats()
    {
        $update=get_transient( 'compressx_set_global_stats' );
        $stats=get_option('compressx_global_stats',array());
        if(empty($stats)||empty($update))
        {
            $stats=array();
            $stats['webp_converted']=0;
            $stats['webp_compressed']=0;
            $stats['webp_saved']=0;
            $stats['webp_total']=0;

            $stats['avif_converted']=0;
            $stats['avif_compressed']=0;
            $stats['avif_saved']=0;
            $stats['avif_total']=0;

            global $wpdb;
            $result=$wpdb->get_results( "SELECT COUNT(*) FROM $wpdb->postmeta WHERE meta_key='compressx_image_meta_webp_converted' AND meta_value = 1",ARRAY_N);
            if($result && sizeof($result)>0)
            {
                $count = $result[0][0];
                $stats['webp_converted']=intval($count);
            }

            $result=$wpdb->get_results("SELECT COUNT(*) FROM $wpdb->postmeta WHERE meta_key='compressx_image_meta_compressed' AND meta_value = 1 AND `post_id` IN (SELECT `ID` FROM $wpdb->posts WHERE `post_mime_type`='image/webp')",ARRAY_N);
            if($result && sizeof($result)>0)
            {
                $count = $result[0][0];
                $stats['webp_compressed']=intval($count);
            }

            $result=$wpdb->get_results("SELECT SUM(`meta_value`) FROM $wpdb->postmeta WHERE meta_key='compressx_image_meta_og_file_size' AND `post_id` IN (SELECT `post_id` FROM $wpdb->postmeta WHERE `meta_key`='compressx_image_meta_webp_converted' AND `meta_value`=1)",ARRAY_N);
            if($result && sizeof($result)>0)
            {
                $total = $result[0][0];
                $stats['webp_total']=$total;
            }

            $result=$wpdb->get_results("SELECT SUM(`meta_value`) FROM $wpdb->postmeta WHERE meta_key='compressx_image_meta_og_file_size' AND `post_id` IN (SELECT `post_id` FROM $wpdb->postmeta WHERE `meta_key`='compressx_image_meta_compressed' AND `meta_value`=1) AND `post_id` IN (SELECT `ID` FROM $wpdb->posts WHERE `post_mime_type`='image/webp')",ARRAY_N);
            if($result && sizeof($result)>0)
            {
                $total = $result[0][0];
                $stats['webp_total']+=$total;
            }

            $result=$wpdb->get_results("SELECT SUM(`meta_value`) FROM $wpdb->postmeta WHERE meta_key='compressx_image_meta_webp_converted_size'",ARRAY_N);
            if($result && sizeof($result)>0)
            {
                $saved = $result[0][0];
                if(!is_null($saved))
                {
                    $stats['webp_saved']=intval($saved);
                }
            }

            $result=$wpdb->get_results("SELECT SUM(`meta_value`) FROM $wpdb->postmeta WHERE meta_key='compressx_image_meta_compressed_size' AND `post_id` IN (SELECT `ID` FROM $wpdb->posts WHERE `post_mime_type`='image/webp')",ARRAY_N);
            if($result && sizeof($result)>0)
            {
                $saved = $result[0][0];
                if(!is_null($saved))
                {
                    $stats['webp_saved']+=intval($saved);
                }
            }

            //avif

            $result=$wpdb->get_results("SELECT COUNT(*) FROM $wpdb->postmeta WHERE meta_key='compressx_image_meta_avif_converted' AND meta_value = 1",ARRAY_N);
            if($result && sizeof($result)>0)
            {
                $count = $result[0][0];
                $stats['avif_converted']=$count;
            }

            $result=$wpdb->get_results( "SELECT COUNT(*) FROM $wpdb->postmeta WHERE meta_key='compressx_image_meta_compressed' AND meta_value = 1 AND `post_id` IN (SELECT `ID` FROM $wpdb->posts WHERE `post_mime_type`='image/avif')",ARRAY_N);
            if($result && sizeof($result)>0)
            {
                $count = $result[0][0];
                $stats['avif_compressed']=intval($count);
            }

            $result=$wpdb->get_results("SELECT SUM(`meta_value`) FROM $wpdb->postmeta WHERE meta_key='compressx_image_meta_og_file_size' AND `post_id` IN (SELECT `post_id` FROM $wpdb->postmeta WHERE `meta_key`='compressx_image_meta_avif_converted' AND `meta_value`=1)",ARRAY_N);
            if($result && sizeof($result)>0)
            {
                $total = $result[0][0];
                $stats['avif_total']=$total;
            }

            $result=$wpdb->get_results("SELECT SUM(`meta_value`) FROM $wpdb->postmeta WHERE meta_key='compressx_image_meta_og_file_size' AND `post_id` IN (SELECT `post_id` FROM $wpdb->postmeta WHERE `meta_key`='compressx_image_meta_compressed' AND `meta_value`=1) AND `post_id` IN (SELECT `ID` FROM $wpdb->posts WHERE `post_mime_type`='image/avif')",ARRAY_N);
            if($result && sizeof($result)>0)
            {
                $total = $result[0][0];
                $stats['avif_total']+=$total;
            }

            $result=$wpdb->get_results("SELECT SUM(`meta_value`) FROM $wpdb->postmeta WHERE meta_key='compressx_image_meta_avif_converted_size' ",ARRAY_N);
            if($result && sizeof($result)>0)
            {
                $saved = $result[0][0];
                if(!is_null($saved))
                {
                    $stats['avif_saved']=intval($saved);
                }
            }

            $result=$wpdb->get_results("SELECT SUM(`meta_value`) FROM $wpdb->postmeta WHERE meta_key='compressx_image_meta_compressed_size' AND `post_id` IN (SELECT `ID` FROM $wpdb->posts WHERE `post_mime_type`='image/avif')",ARRAY_N);
            if($result && sizeof($result)>0)
            {
                $saved = $result[0][0];
                if(!is_null($saved))
                {
                    $stats['avif_saved']+=intval($saved);
                }
            }
            //
            delete_transient('compressx_set_global_stats');
            update_option('compressx_global_stats',$stats,false);
            return $stats;
        }
        else
        {
            return $stats;
        }
    }

    public static function get_failed_images_count()
    {
        global $wpdb;
        $result=$wpdb->get_results( "SELECT COUNT(*) FROM $wpdb->postmeta WHERE meta_key='compressx_image_meta_status' AND meta_value = 'failed'",ARRAY_N);
        if($result && sizeof($result)>0)
        {
            $count = $result[0][0];
            if(is_null($count))
            {
                return 0;
            }
            else
            {
                return $count;
            }
        }

        return 0;
    }

    public static function is_image_optimized($image_id)
    {
        if(CompressX_Image_Meta::get_image_meta_status($image_id)==='optimized')
        {
            return true;
        }
        else
        {
            return false;
        }
    }

    public static function is_webp_image($image_id)
    {
        $file_path = get_attached_file($image_id);
        $type=pathinfo($file_path, PATHINFO_EXTENSION);

        if($type=='webp')
        {
            return true;
        }
        else
        {
            return false;
        }
    }

    public static function is_avif_image($image_id)
    {
        $file_path = get_attached_file($image_id);
        $type=pathinfo($file_path, PATHINFO_EXTENSION);

        if($type=='avif')
        {
            return true;
        }
        else
        {
            return false;
        }
    }
}