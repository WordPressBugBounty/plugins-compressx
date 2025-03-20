<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

class CompressX_Image_Opt_Method
{
    public static function is_support_webp()
    {
        if( function_exists( 'gd_info' ) && function_exists( 'imagewebp' )  )
        {
            return true;
        }

        if ( extension_loaded( 'imagick' ) && class_exists( '\Imagick' ) )
        {
            if( \Imagick::queryformats( 'WEBP' ))
            {
                return true;
            }
        }

        return false;
    }

    public static function is_support_avif()
    {
        if( function_exists( 'gd_info' ) )
        {
            $info=gd_info();
            if(isset($info["AVIF Support"])&&$info["AVIF Support"])
            {
                return true;
            }
        }

        if ( extension_loaded( 'imagick' ) && class_exists( '\Imagick' ) )
        {
            if( \Imagick::queryformats( 'AVIF' ))
            {
                return true;
            }
        }

        return false;
    }

    public static function is_support_gd()
    {
        if( function_exists( 'gd_info' ) )
        {
            return true;
        }
        else
        {
            return false;
        }
    }

    public static function is_support_gd_webp()
    {
        if( function_exists( 'gd_info' ) && function_exists( 'imagewebp' )  )
        {
            return true;
        }
        else
        {
            return false;
        }
    }

    public static function is_support_gd_avif()
    {
        if( function_exists( 'gd_info' ) && function_exists( 'imageavif' ))
        {
            $info=gd_info();
            if(isset($info["AVIF Support"])&&$info["AVIF Support"])
            {
                return true;
            }
        }

        return false;
    }

    public static function is_support_imagick()
    {
        if ( extension_loaded( 'imagick' ) && class_exists( '\Imagick' ) )
        {
            if( \Imagick::queryformats( 'WEBP' ))
            {
                return true;
            }
        }

        return false;
    }

    public static function is_support_imagick_webp()
    {
        if ( extension_loaded( 'imagick' ) && class_exists( '\Imagick' ) )
        {
            if( \Imagick::queryformats( 'WEBP' ))
            {
                return true;
            }
        }

        return false;
    }

    public static function is_support_imagick_avif()
    {
        if ( extension_loaded( 'imagick' ) && class_exists( '\Imagick' ) )
        {
            if( \Imagick::queryformats( 'AVIF' ))
            {
                return true;
            }
        }

        return false;
    }

    public static function compress_image_gd_ex($in,$out,$options)
    {
        if(!CompressX_Image_Opt_Method::is_support_gd())
        {
            $ret['result']='failed';
            $ret['error']='not support gd';
            return $ret;
        }

        $type=pathinfo($in, PATHINFO_EXTENSION);

        if($options['quality']=="custom")
        {
            if($type=='webp')
            {
                $quality=isset($options['quality_webp'])?$options['quality_webp']: 80;
            }
            else
            {
                $quality=isset($options['quality_avif'])?$options['quality_avif']: 60;
            }
        }
        else if($options['quality']=='lossless')
        {
            $quality=99;
        }
        else if($options['quality']=='lossy_minus')
        {
            $quality=90;
        }
        else if($options['quality']=='lossy')
        {
            $quality=80;
        }
        else if($options['quality']=='lossy_plus')
        {
            $quality=70;
        }
        else if($options['quality']=='lossy_super')
        {
            $quality=60;
        }
        else
        {
            $quality=80;
        }

        if($type=='webp')
        {
            $image = imagecreatefromwebp($in);
            if(imagewebp($image, $out, $quality))
            {
                imagedestroy($image);
                if(filesize($out)==0)
                {
                    @unlink($out);
                    $ret['result']='failed';
                    $ret['error']='imagewebp failed';
                    return $ret;
                }

                $ret['result']='success';
                return $ret;
            }
            else
            {
                imagedestroy($image);
                $ret['result']='failed';
                $ret['error']='imagewebp failed';
                return $ret;
            }
        }
        else if($type=='avif')
        {
            $image = imagecreatefromavif($in);
            if(imageavif($image, $out, $quality))
            {
                imagedestroy($image);

                if(filesize($out)==0)
                {
                    @unlink($out);
                    $ret['result']='failed';
                    $ret['error']='Converted image is 0 KB and has been deleted. This usually happens because your AVIF library is not working properly. Please check the AVIF library.';
                    return $ret;
                }

                $ret['result']='success';
                return $ret;
            }
            else
            {
                imagedestroy($image);
                $ret['result']='failed';
                $ret['error']='imageavif failed';
                return $ret;
            }
        }
        else
        {
            $ret['result']='failed';
            $ret['error']='type not support';
            return $ret;
        }
    }

    public static function compress_image_imagick_ex($in,$out,$options)
    {
        if(!CompressX_Image_Opt_Method::is_support_imagick())
        {
            $ret['result']='failed';
            $ret['error']='not support imagick';
            return $ret;
        }

        $type=pathinfo($in, PATHINFO_EXTENSION);

        if($options['quality']=="custom")
        {
            if($type=='webp')
            {
                $quality=isset($options['quality_webp'])?$options['quality_webp']: 80;
            }
            else
            {
                $quality=isset($options['quality_avif'])?$options['quality_avif']: 60;
            }
        }
        else if($options['quality']=='lossless')
        {
            $quality=99;
        }
        else if($options['quality']=='lossy_minus')
        {
            $quality=90;
        }
        else if($options['quality']=='lossy')
        {
            $quality=80;
        }
        else if($options['quality']=='lossy_plus')
        {
            $quality=70;
        }
        else if($options['quality']=='lossy_super')
        {
            $quality=60;
        }
        else
        {
            $quality=80;
        }

        try
        {
            $image    = new Imagick($in);

            if($type=='webp')
            {
                $image->setImageFormat( "WEBP" );
                $image->setImageCompressionQuality( $quality );
                if(isset($options['remove_exif'])&&$options['remove_exif'])
                {
                    $profiles = $image->getImageProfiles("icc", true);
                    $image->stripImage();

                    if(!empty($profiles))
                    {
                        if(isset($profiles['icm']))
                            $image->profileImage("icm", $profiles['icm']);
                        $image->profileImage("icc", $profiles['icc']);
                    }
                }

                if($image->writeImage( $out ))
                {
                    $image->clear();
                    $image->destroy();

                    $ret['result']='success';
                    return $ret;
                }
                else
                {
                    $image->clear();
                    $image->destroy();
                    $ret['result']='failed';
                    $ret['error']='writeImage failed';
                    return $ret;
                }
            }
            else if($type=='avif')
            {
                if($options['quality']=="custom")
                {
                    $quality=isset($options['quality_avif'])?$options['quality_avif']: 60;
                }
                else if($options['quality']=='lossless')
                {
                    $quality=80;
                }
                else if($options['quality']=='lossy_minus')
                {
                    $quality=75;
                }
                else if($options['quality']=='lossy')
                {
                    $quality=60;
                }
                else if($options['quality']=='lossy_plus')
                {
                    $quality=50;
                }
                else if($options['quality']=='lossy_super')
                {
                    $quality=40;
                }
                else
                {
                    $quality=80;
                }

                $image->setImageFormat( "AVIF" );
                $image->setCompressionQuality( $quality );
                if(isset($options['remove_exif'])&&$options['remove_exif'])
                {
                    $profiles = $image->getImageProfiles("icc", true);
                    $image->stripImage();

                    if(!empty($profiles))
                    {
                        if(isset($profiles['icm']))
                            $image->profileImage("icm", $profiles['icm']);
                        $image->profileImage("icc", $profiles['icc']);
                    }
                }

                if($image->writeImage( $out ))
                {
                    $image->clear();
                    $image->destroy();

                    $ret['result']='success';
                    return $ret;
                }
                else
                {
                    $image->clear();
                    $image->destroy();
                    $ret['result']='failed';
                    $ret['error']='writeImage failed';
                    return $ret;
                }
            }
            else
            {
                $image->clear();
                $image->destroy();

                $ret['result']='failed';
                $ret['error']='type not support';
                return $ret;
            }
        }
        catch ( Exception $e )
        {
            $ret['result']='failed';
            $ret['error']=$e->getMessage();
            return $ret;
        }
    }

    public static function convert_webp_gd_ex($in,$out,$options)
    {
        if(!CompressX_Image_Opt_Method::is_support_gd())
        {
            $ret['result']='failed';
            $ret['error']='not support gd';
            return $ret;
        }

        if($options['quality']=="custom")
        {
            $quality=isset($options['quality_webp'])?$options['quality_webp']: 80;
        }
        else if($options['quality']=='lossless')
        {
            $quality=99;
        }
        else if($options['quality']=='lossy_minus')
        {
            $quality=90;
        }
        else if($options['quality']=='lossy')
        {
            $quality=80;
        }
        else if($options['quality']=='lossy_plus')
        {
            $quality=70;
        }
        else if($options['quality']=='lossy_super')
        {
            $quality=60;
        }
        else
        {
            $quality=80;
        }

        $type=pathinfo($in, PATHINFO_EXTENSION);

        if (file_exists($out))
        {
            @wp_delete_file($out);
        }

        if ($type == 'jpeg' ||$type == 'jpg')
        {
            $img = imagecreatefromjpeg($in);
            if(!$img)
            {
                $ret['result']='failed';
                $ret['error']='imagecreatefromjpeg failed';
                return $ret;
            }
            else
            {
                $result = imagewebp($img, $out, $quality);
                imagedestroy($img);
                if (!$result)
                {
                    $ret['result']='failed';
                    $ret['error']='imagewebp failed';
                    return $ret;
                }
                else
                {
                    if (filesize($out)==0)
                    {
                        @unlink($out);
                        $ret['result']='failed';
                        $ret['error']='imagewebp failed';
                        return $ret;
                    }

                    $ret['result']='success';
                    return $ret;
                }
            }
        }
        else if ($type== 'png')
        {
            $img = imagecreatefrompng($in);
            if(!$img)
            {
                $ret['result']='failed';
                $ret['error']='imagecreatefrompng failed';
                return $ret;
            }
            else
            {
                if(imagepalettetotruecolor($img))
                {
                    if(imagealphablending($img, true))
                    {
                        if(imagesavealpha($img, true))
                        {
                            $result = imagewebp($img, $out, $quality);
                            imagedestroy($img);

                            if (!$result)
                            {
                                $ret['result']='failed';
                                $ret['error']='imagewebp failed';
                                return $ret;
                            }
                            else
                            {
                                if (filesize($out)==0)
                                {
                                    @unlink($out);
                                    $ret['result']='failed';
                                    $ret['error']='imagewebp failed';
                                    return $ret;
                                }

                                $ret['result']='success';
                                return $ret;
                            }
                        }
                        else
                        {
                            $ret['result']='failed';
                            $ret['error']='imagesavealpha failed';
                            return $ret;
                        }
                    }
                    else
                    {
                        $ret['result']='failed';
                        $ret['error']='imagealphablending failed';
                        return $ret;
                    }
                }
                else
                {
                    $ret['result']='failed';
                    $ret['error']='imagepalettetotruecolor failed';
                    return $ret;
                }
            }
        }
        else
        {
            $ret['result']='failed';
            $ret['error']='type not support';
            return $ret;
        }
    }

    public static function convert_webp_imagick_ex($in,$out,$options)
    {
        if(!CompressX_Image_Opt_Method::is_support_imagick())
        {
            $ret['result']='failed';
            $ret['error']='not support imagick';
            return $ret;
        }

        if($options['quality']=="custom")
        {
            $quality=isset($options['quality_webp'])?$options['quality_webp']: 80;
        }
        else if($options['quality']=='lossless')
        {
            $quality=99;
        }
        else if($options['quality']=='lossy_minus')
        {
            $quality=90;
        }
        else if($options['quality']=='lossy')
        {
            $quality=80;
        }
        else if($options['quality']=='lossy_plus')
        {
            $quality=70;
        }
        else if($options['quality']=='lossy_super')
        {
            $quality=60;
        }
        else
        {
            $quality=80;
        }

        if (file_exists($out))
        {
            @wp_delete_file($out);
        }

        try
        {
            $image    = new Imagick($in);
            if ( ! in_array( "WEBP", $image->queryFormats() ) )
            {
                $ret['result']='failed';
                $ret['error']="not support webp";
                return $ret;
            }

            $image->setImageFormat( "WEBP" );
            if(isset($options['remove_exif'])&&$options['remove_exif'])
            {
                $profiles = $image->getImageProfiles("icc", true);
                $image->stripImage();

                if(!empty($profiles))
                {
                    if(isset($profiles['icm']))
                        $image->profileImage("icm", $profiles['icm']);
                    $image->profileImage("icc", $profiles['icc']);
                }
            }
            $image->setImageCompressionQuality( $quality );
            $blob = $image->getImageBlob();
            if ( ! file_put_contents( $out, $blob ) )
            {
                $ret['result']='failed';
                $ret['error']="convert webp failed";
                return $ret;
            }

            $ret['result']='success';
            return $ret;
        }
        catch ( Exception $e )
        {
            $ret['result']='failed';
            $ret['error']=$e->getMessage();
            return $ret;
        }
    }

    public static function convert_avif_gd_ex($in,$out,$options)
    {
        if(!CompressX_Image_Opt_Method::is_support_gd())
        {
            $ret['result']='failed';
            $ret['error']='not support gd';
            return $ret;
        }

        if($options['quality']=="custom")
        {
            $quality=isset($options['quality_avif'])?$options['quality_avif']: 60;
        }
        else if($options['quality']=='lossless')
        {
            $quality=80;
        }
        else if($options['quality']=='lossy_minus')
        {
            $quality=75;
        }
        else if($options['quality']=='lossy')
        {
            $quality=60;
        }
        else if($options['quality']=='lossy_plus')
        {
            $quality=50;
        }
        else if($options['quality']=='lossy_super')
        {
            $quality=40;
        }
        else
        {
            $quality=80;
        }

        $type=pathinfo($in, PATHINFO_EXTENSION);

        if (file_exists($out))
        {
            @wp_delete_file($out);
        }

        if ($type == 'jpeg' ||$type == 'jpg')
        {
            $img = imagecreatefromjpeg($in);
            if(!$img)
            {
                $ret['result']='failed';
                $ret['error']='imagecreatefromjpeg failed';
                return $ret;
            }
            else
            {
                $result = imageavif($img, $out, $quality);
                imagedestroy($img);
                if (!$result)
                {
                    $ret['result']='failed';
                    $ret['error']='imageavif failed';
                    return $ret;
                }
                else
                {
                    if (filesize($out)==0)
                    {
                        @unlink($out);
                        $ret['result']='failed';
                        $ret['error']='Converted image is 0 KB and has been deleted. This usually happens because your AVIF library is not working properly. Please check the AVIF library.';
                        return $ret;
                    }

                    $ret['result']='success';
                    return $ret;
                }
            }
        }
        else if ($type== 'png')
        {
            $img = imagecreatefrompng($in);
            if(!$img)
            {
                $ret['result']='failed';
                $ret['error']='imagecreatefrompng failed';
                return $ret;
            }
            else
            {
                if(imagepalettetotruecolor($img))
                {
                    if(imagealphablending($img, true))
                    {
                        if(imagesavealpha($img, true))
                        {
                            $result = imageavif($img, $out, $quality);
                            imagedestroy($img);

                            if (!$result)
                            {
                                $ret['result']='failed';
                                $ret['error']='imageavif failed';
                                return $ret;
                            }
                            else
                            {
                                if (filesize($out)==0)
                                {
                                    @unlink($out);
                                    $ret['result']='failed';
                                    $ret['error']='Converted image is 0 KB and has been deleted. This usually happens because your AVIF library is not working properly. Please check the AVIF library.';
                                    return $ret;
                                }

                                $ret['result']='success';
                                return $ret;
                            }
                        }
                        else
                        {
                            $ret['result']='failed';
                            $ret['error']='imagesavealpha failed';
                            return $ret;
                        }
                    }
                    else
                    {
                        $ret['result']='failed';
                        $ret['error']='imagealphablending failed';
                        return $ret;
                    }
                }
                else
                {
                    $ret['result']='failed';
                    $ret['error']='imagepalettetotruecolor failed';
                    return $ret;
                }
            }
        }
        else if ($type == 'webp')
        {
            $img = imagecreatefromwebp($in);
            if(!$img)
            {
                $ret['result']='failed';
                $ret['error']='imagecreatefromwebp failed';
                return $ret;
            }
            else
            {
                $result = imageavif($img, $out, $quality);
                imagedestroy($img);
                if (!$result)
                {
                    $ret['result']='failed';
                    $ret['error']='imageavif failed';
                    return $ret;
                }
                else
                {
                    if (filesize($out)==0)
                    {
                        @unlink($out);
                        $ret['result']='failed';
                        $ret['error']='Converted image is 0 KB and has been deleted. This usually happens because your AVIF library is not working properly. Please check the AVIF library.';
                        return $ret;
                    }

                    $ret['result']='success';
                    return $ret;
                }
            }
        }
        else
        {
            $ret['result']='failed';
            $ret['error']='type not support';
            return $ret;
        }
    }

    public static function convert_avif_imagick_ex($in,$out,$options)
    {
        if(!CompressX_Image_Opt_Method::is_support_imagick())
        {
            $ret['result']='failed';
            $ret['error']='not support imagick';
            return $ret;
        }

        if($options['quality']=="custom")
        {
            $quality=isset($options['quality_avif'])?$options['quality_avif']: 60;
        }
        else if($options['quality']=='lossless')
        {
            $quality=80;
        }
        else if($options['quality']=='lossy_minus')
        {
            $quality=75;
        }
        else if($options['quality']=='lossy')
        {
            $quality=60;
        }
        else if($options['quality']=='lossy_plus')
        {
            $quality=50;
        }
        else if($options['quality']=='lossy_super')
        {
            $quality=40;
        }
        else
        {
            $quality=80;
        }

        if (file_exists($out))
        {
            @wp_delete_file($out);
        }

        try
        {
            $image    = new Imagick($in);
            if ( ! in_array( "AVIF", $image->queryFormats() ) )
            {
                $ret['result']='failed';
                $ret['error']="not support webp";
                return $ret;
            }
            $image->setImageFormat( "AVIF" );

            if(isset($options['remove_exif'])&&$options['remove_exif'])
            {
                $profiles = $image->getImageProfiles("icc", true);
                $image->stripImage();

                if(!empty($profiles))
                {
                    if(isset($profiles['icm']))
                        $image->profileImage("icm", $profiles['icm']);
                    $image->profileImage("icc", $profiles['icc']);
                }
            }
            $image->setCompressionQuality($quality);
            $blob = $image->getImageBlob();
            if ( ! file_put_contents( $out, $blob ) )
            {
                $ret['result']='failed';
                $ret['error']="convert avif failed";
                return $ret;
            }

            $ret['result']='success';
            return $ret;
        }
        catch ( Exception $e )
        {
            $ret['result']='failed';
            $ret['error']=$e->getMessage();
            return $ret;
        }
    }

    public static function remove_exif($in)
    {
        $image    = new Imagick($in);
        $profiles = $image->getImageProfiles("icc", true);
        $image->stripImage();

        if(!empty($profiles))
        {
            //
            if(isset($profiles['icm']))
                $image->profileImage("icm", $profiles['icm']);
            $image->profileImage("icc", $profiles['icc']);
        }

        if($image->writeImage( $in ))
        {
            $image->clear();
            $image->destroy();
            $ret['result']='success';
            return $ret;
        }
        else
        {
            $image->clear();
            $image->destroy();
            $ret['result']='failed';
            $ret['error']='writeImage failed';
            return $ret;
        }
    }

    public static function scan_unoptimized_image($limit,$offset,$convert_to_webp,$convert_to_avif,$exclude_regex_folder,$force=false)
    {
        global $wpdb;

        $supported_mime_types = array(
            "image/jpg",
            "image/jpeg",
            "image/png",
            "image/webp",
            "image/avif");

        $args  = $supported_mime_types;

        $query="SELECT ID FROM $wpdb->posts WHERE post_type = 'attachment' AND post_mime_type IN (%s,%s,%s,%s,%s) ";

        if($limit>0)
        {
            $query.=" LIMIT %d";
            $args[]=$limit;
        }

        if($offset>0)
        {
            $query.=" OFFSET %d";
            $args[]=$offset;
        }

        //$query = "SELECT ID FROM $wpdb->posts WHERE post_type = 'attachment' AND post_mime_type IN (".$mime_types.") ".$limit.$offset;

        $need_optimize_images=array();

        if(!$convert_to_webp&&!$convert_to_avif)
        {
            return $need_optimize_images;
        }

        $result=$wpdb->get_results( $wpdb->prepare( $query, $args ), OBJECT_K );
        if(!empty($result))
        {
            foreach ( $result as $image )
            {
                $need_opt=false;
                if($force)
                {
                    $sub_task['id']=$image->ID;
                    $sub_task['finished']=0;
                    $need_optimize_images[$image->ID]=$sub_task;
                    wp_cache_delete( $image->ID, 'post_meta' );
                    continue;
                }

                wp_cache_delete( $image->ID, 'post_meta' );

                $file_path = get_attached_file($image->ID);
                $type=pathinfo($file_path, PATHINFO_EXTENSION);

                if($convert_to_webp)
                {
                    if($type!='webp'&&$type!='avif')
                    {
                        if(CompressX_Image_Opt_Method::need_convert_to_webp($image->ID,$exclude_regex_folder))
                        {
                            $need_opt=true;
                        }
                    }
                    else if($type=='webp')
                    {
                        if(CompressX_Image_Meta::get_image_meta_compressed($image->ID)==0)
                        {
                            $need_opt=true;
                        }
                    }
                }

                if($convert_to_avif)
                {
                    if($type!='avif')
                    {
                        if(CompressX_Image_Opt_Method::need_convert_to_avif($image->ID,$exclude_regex_folder))
                        {
                            $need_opt=true;
                        }
                    }
                    else
                    {
                        if(CompressX_Image_Meta::get_image_meta_compressed($image->ID)==0)
                        {
                            $need_opt=true;
                        }
                    }

                }

                if($need_opt)
                {
                    $sub_task['id']=$image->ID;
                    $sub_task['finished']=0;
                    $need_optimize_images[$image->ID]=$sub_task;
                }

                wp_cache_delete( $image->ID, 'post_meta' );
            }
        }

        return $need_optimize_images;
    }

    public static function need_convert_to_webp($post_id,$exclude_regex_folder)
    {
        if(!CompressX_Image_Meta::get_image_meta_webp_converted($post_id))
        {
            if(!CompressX_Image_Opt_Method::exclude_path($post_id,$exclude_regex_folder))
            {
                return true;
            }
            else
            {
                return false;
            }
        }
        else
        {
            return false;
        }
    }

    public static function need_convert_to_avif($post_id,$exclude_regex_folder)
    {
        if(!CompressX_Image_Meta::get_image_meta_avif_converted($post_id))
        {
            if(!CompressX_Image_Opt_Method::exclude_path($post_id,$exclude_regex_folder))
            {
                return true;
            }
            else
            {
                return false;
            }
        }
        else
        {
            return false;
        }
    }

    public static function exclude_path($post_id,$exclude_regex_folder)
    {
        if(empty($exclude_regex_folder))
        {
            return false;
        }

        $file_path = get_attached_file( $post_id );
        $file_path = CompressX_Image_Opt_Method::transfer_path($file_path);
        if (CompressX_Image_Opt_Method::regex_match($exclude_regex_folder, $file_path))
        {
            return true;
        }
        else
        {
            return false;
        }
    }

    public static function regex_match($regex_array,$string)
    {
        if(empty($regex_array))
        {
            return false;
        }

        foreach ($regex_array as $regex)
        {
            if(preg_match($regex,$string))
            {
                return true;
            }
        }

        return false;
    }

    public static function supported_mime_types()
    {
        $supported_mime_types = array(
            "image/jpg",
            "image/jpeg",
            "image/png",
            "image/webp",
            "image/avif");

        $supported_mime_types=apply_filters('compressx_supported_mime_types',$supported_mime_types);

        $supported_mime_types = sprintf("'%s'", implode("','", $supported_mime_types ) );
        return $supported_mime_types;
    }

    public static function supported_mime_types_ex()
    {
        $supported_mime_types = array(
            "image/jpg",
            "image/jpeg",
            "image/png",
            "image/webp",
            "image/avif");

        $supported_mime_types=apply_filters('compressx_supported_mime_types',$supported_mime_types);

        return $supported_mime_types;
    }

    public static function transfer_path($path)
    {
        $path = str_replace('\\','/',$path);
        $values = explode('/',$path);
        return implode(DIRECTORY_SEPARATOR,$values);
    }

    public static function compress_image($image_id,$options,$log=false)
    {
        $success=true;
        $has_compress=true;
        $image_optimize_meta =CompressX_Image_Meta::get_image_meta($image_id);
        $uploads=wp_get_upload_dir();
        if(CompressX_Image_Opt_Method::need_compress($image_id,$options))
        {
            if(CompressX_Image_Meta::get_image_meta_compressed($image_id)==0)
            {
                $file_path = get_attached_file($image_id);

                foreach ($image_optimize_meta['size'] as $size_key => $size_meta)
                {
                    if(CompressX_Image_Opt_Method::skip_size($size_key,$options))
                    {
                        continue;
                    }

                    if ($size_meta['compress_status'] == 0)
                    {
                        if ($size_key == "og")
                        {
                            $filename = $file_path;
                        }
                        else if(CompressX_Image_Opt_Method::is_elementor_thumbs($size_key))
                        {
                            $filename = $uploads['basedir'].'/'.$size_meta['file'];
                        }
                        else
                        {
                            $filename = path_join(dirname($file_path), $size_meta['file']);
                        }

                        $image_optimize_meta['size'][$size_key]['compress_status']=0;
                        $output_path=CompressX_Image_Opt_Method::get_output_path($filename);

                        CompressX_Image_Opt_Method::WriteLog($log,'Start Compression '.basename($filename),'notice');

                        if(!file_exists($filename))
                        {
                            CompressX_Image_Opt_Method::WriteLog($log,'File '.basename($filename).' not exist,so skip compress.','notice');
                            $image_optimize_meta['size'][$size_key]['compress_status']=1;
                            $image_optimize_meta['size'][$size_key]['status']='optimized';
                            continue;
                        }

                        if($options['converter_method']=='gd')
                        {
                            $ret=CompressX_Image_Opt_Method::compress_image_gd_ex($filename,$output_path,$options);
                        }
                        else if($options['converter_method']=='imagick')
                        {
                            $ret=CompressX_Image_Opt_Method::compress_image_imagick_ex($filename,$output_path,$options);
                        }
                        else
                        {
                            $ret=CompressX_Image_Opt_Method::compress_image_gd_ex($filename,$output_path,$options);
                        }

                        if($ret['result']=='success')
                        {
                            CompressX_Image_Opt_Method::WriteLog($log,'Compression '.basename($filename).' succeeded.','notice');
                            $image_optimize_meta['size'][$size_key]['compress_status']=1;
                            $image_optimize_meta['size'][$size_key]['status']='optimized';

                            if($options['auto_remove_larger_format'])
                            {
                                if(filesize($output_path)>filesize($filename))
                                {
                                    CompressX_Image_Opt_Method::WriteLog($log,'Compressed size larger than original size, deleting file '.basename($output_path).'.','notice');
                                    @wp_delete_file($output_path);
                                    if($size_key=="og")
                                    {
                                        CompressX_Image_Meta::update_compressed_size($image_id,filesize($filename));
                                    }
                                }
                                else
                                {
                                    if($size_key=="og")
                                    {
                                        CompressX_Image_Meta::update_compressed_size($image_id,filesize($output_path));
                                    }
                                }
                            }
                            else
                            {
                                if($size_key=="og")
                                {
                                    CompressX_Image_Meta::update_compressed_size($image_id,filesize($output_path));
                                }
                            }


                            CompressX_Image_Meta::update_images_meta($image_id,$image_optimize_meta);
                        }
                        else
                        {
                            CompressX_Image_Opt_Method::WriteLog($log,'Compression '.basename($filename).' failed. Error:'.$ret['error'],'notice');
                            $image_optimize_meta['size'][$size_key]['compress_status']=0;
                            $image_optimize_meta['size'][$size_key]['status']='failed';
                            $image_optimize_meta['size'][$size_key]['error']=$ret['error'];
                            CompressX_Image_Meta::update_images_meta($image_id,$image_optimize_meta);

                            $success=false;
                            $has_compress=false;
                        }

                    }
                }

                if($has_compress)
                    CompressX_Image_Meta::update_image_meta_compressed($image_id,1);
            }
        }

        return $success;
    }

    public static function convert_to_webp($image_id,$options,$log=false)
    {
        $success=true;
        $has_convert=true;
        $image_optimize_meta =CompressX_Image_Meta::get_image_meta($image_id);
        $uploads=wp_get_upload_dir();
        if(CompressX_Image_Opt_Method::need_convert_to_webp_ex($image_id,$options))
        {
            if(CompressX_Image_Meta::get_image_meta_webp_converted($image_id)==0)
            {
                $file_path = get_attached_file( $image_id );

                foreach ($image_optimize_meta['size'] as $size_key=>$size_meta)
                {
                    if(CompressX_Image_Opt_Method::skip_size($size_key,$options))
                    {
                        continue;
                    }

                    if($size_meta['convert_webp_status']==0)
                    {
                        if($size_key=="og")
                        {
                            $filename= $file_path;
                        }
                        else if(CompressX_Image_Opt_Method::is_elementor_thumbs($size_key))
                        {
                            $filename = $uploads['basedir'].'/'.$size_meta['file'];
                        }
                        else
                        {
                            $filename= path_join( dirname( $file_path ), $size_meta['file'] );
                        }

                        $output_path=CompressX_Image_Opt_Method::get_output_path($filename);
                        $output_path=$output_path.'.webp';
                        $image_optimize_meta['size'][$size_key]['convert_webp_status']=0;

                        CompressX_Image_Opt_Method::WriteLog($log,'Start WebP conversion '.basename($filename),'notice');

                        if(!file_exists($filename))
                        {
                            CompressX_Image_Opt_Method::WriteLog($log,'File '.basename($filename).' not exist,so skip convert.','notice');
                            $image_optimize_meta['size'][$size_key]['convert_webp_status']=1;
                            $image_optimize_meta['size'][$size_key]['status']='optimized';
                            continue;
                        }

                        if($options['converter_method']=='gd')
                        {
                            $ret=CompressX_Image_Opt_Method::convert_webp_gd_ex($filename,$output_path,$options);
                        }
                        else if($options['converter_method']=='imagick')
                        {
                            $ret=CompressX_Image_Opt_Method::convert_webp_imagick_ex($filename,$output_path,$options);
                        }
                        else
                        {
                            $ret=CompressX_Image_Opt_Method::convert_webp_gd_ex($filename,$output_path,$options);
                        }

                        if($ret['result']=='success')
                        {
                            CompressX_Image_Opt_Method::WriteLog($log,'Converting '.basename($filename).' to WebP succeeded.','notice');
                            $image_optimize_meta['size'][$size_key]['convert_webp_status']=1;
                            $image_optimize_meta['size'][$size_key]['status']='optimized';

                            if($options['auto_remove_larger_format'])
                            {
                                if(filesize($output_path)>filesize($filename))
                                {
                                    CompressX_Image_Opt_Method::WriteLog($log,'WebP size larger than original size, deleting file '.basename($output_path).'.','notice');
                                    @wp_delete_file($output_path);
                                    if($size_key=="og")
                                    {
                                        CompressX_Image_Meta::update_webp_converted_size($image_id,filesize($filename));
                                    }
                                }
                                else
                                {
                                    if($size_key=="og")
                                    {
                                        CompressX_Image_Meta::update_webp_converted_size($image_id,filesize($output_path));
                                    }
                                }
                            }
                            else
                            {
                                if($size_key=="og")
                                {
                                    CompressX_Image_Meta::update_webp_converted_size($image_id,filesize($output_path));
                                }
                            }


                            CompressX_Image_Meta::update_images_meta($image_id,$image_optimize_meta);
                        }
                        else
                        {
                            CompressX_Image_Opt_Method::WriteLog($log,'Converting '.basename($filename).' to WebP failed. Error:'.$ret['error'],'notice');
                            $image_optimize_meta['size'][$size_key]['convert_webp_status']=0;
                            $image_optimize_meta['size'][$size_key]['status']='failed';
                            $image_optimize_meta['size'][$size_key]['error']=$ret['error'];
                            CompressX_Image_Meta::update_images_meta($image_id,$image_optimize_meta);

                            $success=false;
                            $has_convert=false;
                        }
                    }
                }

                if($has_convert)
                    CompressX_Image_Meta::update_image_meta_webp_converted($image_id,1);
            }
        }

        return $success;
    }

    public static function convert_to_avif($image_id,$options,$log=false)
    {
        $success=true;
        $has_convert=true;
        $image_optimize_meta =CompressX_Image_Meta::get_image_meta($image_id);
        $uploads=wp_get_upload_dir();
        if(CompressX_Image_Opt_Method::need_convert_to_avif_ex($image_id,$options))
        {
            if(CompressX_Image_Meta::get_image_meta_avif_converted($image_id)==0)
            {
                $file_path = get_attached_file( $image_id );

                foreach ($image_optimize_meta['size'] as $size_key=>$size_meta)
                {
                    if(CompressX_Image_Opt_Method::skip_size($size_key,$options))
                    {
                        continue;
                    }

                    if($size_meta['convert_avif_status']==0)
                    {
                        if($size_key=="og")
                        {
                            $filename= $file_path;
                        }
                        else if(CompressX_Image_Opt_Method::is_elementor_thumbs($size_key))
                        {
                            $filename = $uploads['basedir'].'/'.$size_meta['file'];
                        }
                        else
                        {
                            $filename= path_join( dirname( $file_path ), $size_meta['file'] );
                        }

                        $output_path=CompressX_Image_Opt_Method::get_output_path($filename);
                        $output_path=$output_path.'.avif';
                        $image_optimize_meta['size'][$size_key]['convert_avif_status']=0;
                        CompressX_Image_Opt_Method::WriteLog($log,'Start AVIF conversion:'.basename($filename),'notice');

                        if(!file_exists($filename))
                        {
                            CompressX_Image_Opt_Method::WriteLog($log,'File '.basename($filename).' not exist,so skip convert.','notice');
                            $image_optimize_meta['size'][$size_key]['convert_avif_status']=1;
                            $image_optimize_meta['size'][$size_key]['status']='optimized';
                            continue;
                        }

                        if($options['converter_method']=='gd')
                        {
                            $ret=CompressX_Image_Opt_Method::convert_avif_gd_ex($filename,$output_path,$options);
                        }
                        else if($options['converter_method']=='imagick')
                        {
                            CompressX_Image_Opt_Method::WriteLog($log,'test imagick '.basename($filename),'notice');
                            $ret=CompressX_Image_Opt_Method::convert_avif_imagick_ex($filename,$output_path,$options);
                        }
                        else
                        {
                            $ret=CompressX_Image_Opt_Method::convert_avif_gd_ex($filename,$output_path,$options);
                        }

                        if($ret['result']=='success')
                        {
                            CompressX_Image_Opt_Method::WriteLog($log,'Converting '.basename($filename).' to AVIF succeeded.','notice');
                            $image_optimize_meta['size'][$size_key]['convert_avif_status']=1;
                            $image_optimize_meta['size'][$size_key]['status']='optimized';

                            if($options['auto_remove_larger_format'])
                            {
                                if(filesize($output_path)>filesize($filename))
                                {
                                    CompressX_Image_Opt_Method::WriteLog($log,'AVIF size larger than original size, deleting file '.basename($output_path).'.','notice');
                                    @wp_delete_file($output_path);
                                    if($size_key=="og")
                                    {
                                        CompressX_Image_Meta::update_avif_converted_size($image_id,filesize($filename));
                                    }
                                }
                                else
                                {
                                    if($size_key=="og")
                                    {
                                        CompressX_Image_Meta::update_avif_converted_size($image_id,filesize($output_path));
                                    }
                                }
                            }
                            else
                            {
                                if($size_key=="og")
                                {
                                    CompressX_Image_Meta::update_avif_converted_size($image_id,filesize($output_path));
                                }
                            }

                            CompressX_Image_Meta::update_images_meta($image_id,$image_optimize_meta);
                        }
                        else
                        {
                            CompressX_Image_Opt_Method::WriteLog($log,'Converting '.basename($filename).' to AVIF failed. Error:'.$ret['error'],'notice');
                            $image_optimize_meta['size'][$size_key]['convert_avif_status']=0;
                            $image_optimize_meta['size'][$size_key]['status']='failed';
                            $image_optimize_meta['size'][$size_key]['error']=$ret['error'];
                            CompressX_Image_Meta::update_images_meta($image_id,$image_optimize_meta);

                            $success=false;
                            $has_convert=false;
                        }
                    }
                }

                if($has_convert)
                    CompressX_Image_Meta::update_image_meta_avif_converted($image_id,1);
            }
        }

        return $success;
    }

    public static function custom_convert_to_webp($filename,$options,$log=false)
    {
        $success=true;

        if(CompressX_Image_Opt_Method::custom_need_convert_to_webp_ex($filename,$options))
        {
            if(CompressX_Custom_Image_Meta::is_convert_webp($filename)==0)
            {
                $output_path=CompressX_Image_Opt_Method::get_custom_output_path($filename);
                $output_path=$output_path.'.webp';

                CompressX_Image_Opt_Method::WriteLog($log,'Start WebP conversion '.basename($filename),'notice');

                if($options['converter_method']=='gd')
                {
                    $ret=CompressX_Image_Opt_Method::convert_webp_gd_ex($filename,$output_path,$options);
                }
                else if($options['converter_method']=='imagick')
                {
                    $ret=CompressX_Image_Opt_Method::convert_webp_imagick_ex($filename,$output_path,$options);
                }
                else
                {
                    $ret=CompressX_Image_Opt_Method::convert_webp_gd_ex($filename,$output_path,$options);
                }

                if($ret['result']=='success')
                {
                    CompressX_Image_Opt_Method::WriteLog($log,'Converting '.basename($filename).' to WebP succeeded.','notice');
                    if($options['auto_remove_larger_format'])
                    {
                        if(filesize($output_path)>filesize($filename))
                        {
                            CompressX_Image_Opt_Method::WriteLog($log,'WebP size larger than original size, deleting file '.basename($output_path).'.','notice');
                            @wp_delete_file($output_path);
                            $convert_webp_size=filesize($filename);
                        }
                        else
                        {
                            $convert_webp_size=filesize($output_path);
                        }
                    }
                    else
                    {
                        $convert_webp_size=filesize($output_path);
                    }

                    $image_optimize_meta['convert_webp_status']=1;
                    $image_optimize_meta['convert_webp_size']=$convert_webp_size;
                    CompressX_Custom_Image_Meta::update_custom_image_meta($filename,$image_optimize_meta);
                }
                else
                {
                    $image_optimize_meta['convert_webp_status']=0;
                    $image_optimize_meta['convert_webp_size']=0;
                    $image_optimize_meta['error']=$ret['error'];
                    CompressX_Custom_Image_Meta::update_custom_image_meta($filename,$image_optimize_meta);
                    CompressX_Image_Opt_Method::WriteLog($log,'Converting '.basename($filename).' to WebP failed. Error:'.$ret['error'],'notice');
                    $success=false;
                }
            }
        }

        return $success;
    }

    public static function custom_convert_to_avif($filename,$options,$log=false)
    {
        $success=true;

        if(CompressX_Image_Opt_Method::custom_need_convert_to_avif_ex($filename,$options))
        {
            if(CompressX_Custom_Image_Meta::is_convert_avif($filename)==0)
            {
                $output_path=CompressX_Image_Opt_Method::get_custom_output_path($filename);
                $output_path=$output_path.'.avif';

                CompressX_Image_Opt_Method::WriteLog($log,'Start AVIF conversion:'.basename($filename),'notice');

                if($options['converter_method']=='gd')
                {
                    $ret=CompressX_Image_Opt_Method::convert_avif_gd_ex($filename,$output_path,$options);
                }
                else if($options['converter_method']=='imagick')
                {
                    CompressX_Image_Opt_Method::WriteLog($log,'test imagick '.basename($filename),'notice');
                    $ret=CompressX_Image_Opt_Method::convert_avif_imagick_ex($filename,$output_path,$options);
                }
                else
                {
                    $ret=CompressX_Image_Opt_Method::convert_avif_gd_ex($filename,$output_path,$options);
                }

                if($ret['result']=='success')
                {
                    CompressX_Image_Opt_Method::WriteLog($log,'Converting '.basename($filename).' to AVIF succeeded.','notice');

                    if($options['auto_remove_larger_format'])
                    {
                        if(filesize($output_path)>filesize($filename))
                        {
                            CompressX_Image_Opt_Method::WriteLog($log,'AVIF size larger than original size, deleting file '.basename($output_path).'.','notice');
                            @wp_delete_file($output_path);
                            $avif_converted_size=filesize($filename);
                        }
                        else
                        {
                            $avif_converted_size=filesize($output_path);
                        }
                    }
                    else
                    {
                        $avif_converted_size=filesize($output_path);
                    }

                    $image_optimize_meta['convert_avif_status']=1;
                    $image_optimize_meta['convert_avif_size']=$avif_converted_size;
                    CompressX_Custom_Image_Meta::update_custom_image_meta($filename,$image_optimize_meta);
                }
                else
                {
                    $image_optimize_meta['convert_avif_status']=0;
                    $image_optimize_meta['convert_avif_size']=0;
                    $image_optimize_meta['error']=$ret['error'];
                    CompressX_Custom_Image_Meta::update_custom_image_meta($filename,$image_optimize_meta);
                    CompressX_Image_Opt_Method::WriteLog($log,'Converting '.basename($filename).' to AVIF failed. Error:'.$ret['error'],'notice');

                    $success=false;
                }
            }
        }

        return $success;
    }

    public static function resize($image_id,$options,$log=false)
    {
        if($options['resize_enable']===false)
        {
            return true;
        }


        $image_meta = wp_get_attachment_metadata($image_id);
        $original_file_path = wp_get_original_image_path( $image_id );

        if(empty($original_file_path))
            return false;

        $imagesize  = wp_getimagesize( $original_file_path );
        $og_width  = $imagesize[0];
        $og_height = $imagesize[1];


        $max_width=isset($options['resize_width'])?$options['resize_width']:2560;
        $max_height=isset($options['resize_height'])?$options['resize_height']:2560;

        if ( ( $og_width < $max_width ) || ( $og_height < $max_height) )
        {
            return true;
        }

        /*
        $resize_method=isset($resize['method'])?$resize['method']:'auto';
        if($resize_method==='width')
        {
            $max_height='0';
        }
        else if($resize_method==='height')
        {
            $max_width='0';
        }*/

        CompressX_Image_Opt_Method::WriteLog($log,'Start resizing image id:'.$image_id,'notice');

        $saved_data=image_make_intermediate_size($original_file_path,$max_width,$max_height);
        if($saved_data===false)
        {
            return false;
        }

        $resize_path = path_join( dirname( $original_file_path ),$saved_data['file']);
        if (!file_exists($resize_path))
        {
            return false;
        }

        $suffix='scaled';
        $dir = pathinfo( $original_file_path, PATHINFO_DIRNAME );
        $ext = pathinfo( $original_file_path, PATHINFO_EXTENSION );
        $name = wp_basename( $original_file_path, ".$ext" );
        $scaled_file_path=trailingslashit( $dir ) . "{$name}-{$suffix}.{$ext}";

        @copy($resize_path,$scaled_file_path);

        if(!empty($image_meta['sizes']))
        {
            $path_parts = pathinfo($resize_path );
            $filename   = ! empty( $path_parts['basename'] ) ? $path_parts['basename'] : $path_parts['filename'];
            $unlink=true;
            foreach ( $image_meta['sizes'] as $image_size )
            {
                if ( false === strpos( $image_size['file'], $filename ) )
                {
                    continue;
                }
                $unlink = false;
            }

            if($unlink)
            {
                @wp_delete_file($resize_path );
            }
        }
        else
        {
            @wp_delete_file( $resize_path );
        }


        // Update the attached file meta.
        update_attached_file( $image_id, _wp_relative_upload_path( $scaled_file_path ) );

        // Width and height of the new image.
        $image_meta['width']  = $saved_data['width'];
        $image_meta['height'] = $saved_data['height'];

        // Make the file path relative to the upload dir.
        $image_meta['file'] = _wp_relative_upload_path( $scaled_file_path );

        // Add image file size.
        $image_meta['filesize'] = wp_filesize( $scaled_file_path );

        // Store the original image file name in image_meta.
        $image_meta['original_image'] = wp_basename( $original_file_path );

        update_post_meta( $image_id, '_wp_attachment_metadata', $image_meta );
        CompressX_Image_Meta::update_og_size($image_id,$image_meta['filesize']);
        return true;
    }

    public static function WriteLog($log,$text,$type)
    {
        if (is_a($log, 'CompressX_Log'))
        {
            $log->WriteLog($text,$type);
        }
        else
        {
            $log=new CompressX_Log();
            $log->OpenLogFile();
            $log->WriteLog($text,$type);
        }
    }

    public static function need_convert_to_webp_ex($image_id,$options)
    {
        $file_path = get_attached_file($image_id);
        $type=pathinfo($file_path, PATHINFO_EXTENSION);

        if($type=='webp'||$type=='avif')
        {
            return false;
        }

        return $options['convert_to_webp'];
    }

    public static function custom_need_convert_to_webp_ex($filename,$options)
    {
        $type=pathinfo($filename, PATHINFO_EXTENSION);

        if($type=='webp'||$type=='avif')
        {
            return false;
        }

        return $options['convert_to_webp'];
    }

    public static function need_convert_to_avif_ex($image_id,$options)
    {
        $file_path = get_attached_file($image_id);
        $type=pathinfo($file_path, PATHINFO_EXTENSION);

        if($type=='avif')
        {
            return false;
        }

        return $options['convert_to_avif'];
    }

    public static function custom_need_convert_to_avif_ex($filename,$options)
    {
        $type=pathinfo($filename, PATHINFO_EXTENSION);

        if($type=='avif')
        {
            return false;
        }

        return $options['convert_to_avif'];
    }

    public static function need_compress($image_id,$options)
    {
        $file_path = get_attached_file($image_id);
        $type=pathinfo($file_path, PATHINFO_EXTENSION);

        if($type=='webp')
        {
            return $options['compressed_webp'];
        }
        else if($type=='avif')
        {
            return $options['compressed_avif'];
        }

        return false;
    }

    public static function skip_size($size_key,$options)
    {
        if(isset($options['skip_size'])&&isset($options['skip_size'][$size_key]))
        {
            return $options['skip_size'][$size_key];
        }

        return false;
    }

    public static function is_elementor_thumbs($size_key)
    {
        if(substr($size_key, 0,strlen('elementor_custom'))=='elementor_custom')
        {
            return true;
        }
        else
        {
            return false;
        }
    }

    public static function get_output_path($og_path)
    {
        $compressx_path=WP_CONTENT_DIR."/compressx-nextgen/uploads";

        if(is_multisite())
        {
            $tmp_id=get_current_blog_id();
            $main_site_id = get_main_site_id();
            switch_to_blog($main_site_id);
            $upload_dir = wp_get_upload_dir();
            switch_to_blog($tmp_id);
        }
        else
        {
            $upload_dir = wp_get_upload_dir();
        }

        $upload_root=CompressX_Image_Opt_Method::transfer_path($upload_dir['basedir']);
        $attachment_dir=dirname($og_path);
        $attachment_dir=CompressX_Image_Opt_Method::transfer_path($attachment_dir);
        $sub_dir=str_replace($upload_root,'',$attachment_dir);
        $sub_dir=untrailingslashit($sub_dir);
        $sub_dir=ltrim( $sub_dir, '/\\' );
        $real_path=$compressx_path.'/'.$sub_dir;

        if(!file_exists($real_path))
        {
            @mkdir($real_path,0777,true);
        }

        return $real_path.'/'.basename($og_path);
    }

    public static function get_custom_output_path($og_path)
    {
        $compressx_path=WP_CONTENT_DIR."/compressx-nextgen";

        $upload_root=CompressX_Image_Opt_Method::transfer_path(WP_CONTENT_DIR.'/');
        $attachment_dir=dirname($og_path);
        $attachment_dir=CompressX_Image_Opt_Method::transfer_path($attachment_dir);
        $sub_dir=str_replace($upload_root,'',$attachment_dir);
        $sub_dir=untrailingslashit($sub_dir);
        $real_path=$compressx_path.'/'.$sub_dir;

        if(!file_exists($real_path))
        {
            @mkdir($real_path,0777,true);
        }

        return $real_path.'/'.basename($og_path);
    }

    public static function get_nextgen_folder()
    {
        //compressx-nextgen
        $options=get_option('compressx_general_settings',array());
        $custom_folder=isset($options['custom_folder'])?$options['custom_folder']:'';
        if(empty($custom_folder))
        {
            return "compressx-nextgen";
        }
        else
        {
            return $custom_folder;
        }
    }

    public static function delete_image($image_id)
    {
        $files=array();
        $file_path = get_attached_file( $image_id );
        $meta = wp_get_attachment_metadata( $image_id, true );

        if ( ! empty( $meta['sizes'] ) )
        {
            foreach ( $meta['sizes'] as $size_key => $size_data )
            {
                $filename= path_join( dirname( $file_path ), $size_data['file'] );
                $files[$size_key] =$filename;
            }

            if(!in_array($file_path,$files))
            {
                $files['og']=$file_path;
            }
        }
        else
        {
            $files['og']=$file_path;
        }

        foreach ($files as $size_key=>$file)
        {
            $file=CompressX_Image_Opt_Method::get_output_path($file);
            $webp_file =$file.'.webp';
            $avif_file =$file.'.avif';
            if(file_exists($webp_file))
                @wp_delete_file($webp_file);

            if(file_exists($avif_file))
                @wp_delete_file($avif_file);

            if(file_exists($file))
                @wp_delete_file($file);

        }

        delete_post_meta($image_id,'compressx_image_meta_status');
        delete_post_meta($image_id,'compressx_image_meta_webp_converted');
        delete_post_meta($image_id,'compressx_image_meta_avif_converted');
        delete_post_meta($image_id,'compressx_image_meta_compressed');

        delete_post_meta($image_id,'compressx_image_meta_og_file_size');
        delete_post_meta($image_id,'compressx_image_meta_webp_converted_size');
        delete_post_meta($image_id,'compressx_image_meta_avif_converted_size');
        delete_post_meta($image_id,'compressx_image_meta_compressed_size');
        delete_post_meta($image_id,'compressx_image_meta');
        delete_post_meta($image_id,'compressx_image_progressing');

        do_action('compressx_delete_image',$image_id);
    }

    public static function set_default_compress_server()
    {
       if( function_exists( 'gd_info' ) )
        {
            update_option('compressx_converter_method','gd');
            return 'gd';
        }
        else if ( extension_loaded( 'imagick' ) && class_exists( '\Imagick' ) )
        {
            update_option('compressx_converter_method','imagick');
            return 'imagick';
        }
        else
        {
            return false;
        }
    }

    public static function set_default_output_format_webp()
    {
        $converter_method=get_option('compressx_converter_method',false);

        if(empty($converter_method))
        {
            $converter_method=CompressX_Image_Opt_Method::set_default_compress_server();
        }

        if($converter_method=="gd")
        {
            if(CompressX_Image_Opt_Method::is_support_gd_webp())
            {
                update_option('compressx_output_format_webp',1);
                return 1;
            }
            else
            {
                update_option('compressx_output_format_webp',0);
                return 0;
            }
        }
        else if($converter_method=="imagick")
        {
            if(CompressX_Image_Opt_Method::is_support_imagick_webp())
            {
                update_option('compressx_output_format_webp',1);
                return 1;
            }
            else
            {
                update_option('compressx_output_format_webp',0);
                return 0;
            }
        }
        else
        {
           return false;
        }
    }

    public static function set_default_output_format_avif()
    {
        $converter_method=get_option('compressx_converter_method',false);

        if(empty($converter_method))
        {
            $converter_method=CompressX_Image_Opt_Method::set_default_compress_server();
        }

        if($converter_method=="gd")
        {
            if(CompressX_Image_Opt_Method::is_support_gd_avif())
            {
                update_option('compressx_output_format_avif',1);
                return 1;
            }
            else
            {
                update_option('compressx_output_format_avif',0);
                return 0;
            }
        }
        else if($converter_method=="imagick")
        {
            if(CompressX_Image_Opt_Method::is_support_imagick_avif())
            {
                if(CompressX_Image_Opt_Method::check_imagick_avif())
                {
                    update_option('compressx_output_format_avif',1);
                    return 1;
                }
                else
                {
                    update_option('compressx_output_format_avif',0);
                    return 0;
                }
            }
            else
            {
                update_option('compressx_output_format_avif',0);
                return 0;
            }
        }
        else
        {
            return false;
        }
    }

    public static function check_imagick_avif()
    {
        if ( extension_loaded( 'imagick' ) && class_exists( '\Imagick' ) )
        {
            $info = \Imagick::getVersion();
            $versionString = $info['versionString'];
            $version="7.0";
            if (preg_match('/^ImageMagick (\d+\.\d+\.\d+)(?:-\d+)?/', $versionString, $matches))
            {
                $mainVersion = $matches[1];
                if(version_compare($mainVersion,$version,'>'))
                {
                    return true;
                }
                else
                {
                    return false;
                }
            }
            else
            {
                return false;
            }
        }
        else
        {
            return false;
        }
    }

    public static function is_current_support_webp()
    {
        $converter_method=get_option('compressx_converter_method',false);

        if(empty($converter_method))
        {
            $converter_method=CompressX_Image_Opt_Method::set_default_compress_server();
        }

        if($converter_method=="gd")
        {
            if(CompressX_Image_Opt_Method::is_support_gd_webp())
            {
               return true;
            }
            else
            {
                return false;
            }
        }
        else if($converter_method=="imagick")
        {
            if(CompressX_Image_Opt_Method::is_support_imagick_webp())
            {
                return true;
            }
            else
            {
                return false;
            }
        }
        else
        {
            return false;
        }
    }

    public static function is_current_support_avif()
    {
        $converter_method=get_option('compressx_converter_method',false);

        if(empty($converter_method))
        {
            $converter_method=CompressX_Image_Opt_Method::set_default_compress_server();
        }

        if($converter_method=="gd")
        {
            if(CompressX_Image_Opt_Method::is_support_gd_avif())
            {
                return true;
            }
            else
            {
                return false;
            }
        }
        else if($converter_method=="imagick")
        {
            if(CompressX_Image_Opt_Method::is_support_imagick_avif())
            {
                return true;
            }
            else
            {
                return false;
            }
        }
        else
        {
            return false;
        }
    }

    public static function get_convert_to_webp()
    {
        $converter_method=get_option('compressx_converter_method',false);
        if(empty($converter_method))
        {
            $converter_method=CompressX_Image_Opt_Method::set_default_compress_server();
        }

        $convert_to_webp=get_option('compressx_output_format_webp','not init');
        if($convert_to_webp=='not init')
        {
            $convert_to_webp=CompressX_Image_Opt_Method::set_default_output_format_webp();
        }

        if($converter_method=='gd')
        {
            if($convert_to_webp&&CompressX_Image_Opt_Method::is_support_gd_webp())
            {
                return true;
            }
            else
            {
                return false;
            }
        }
        else
        {
            if($convert_to_webp&&CompressX_Image_Opt_Method::is_support_imagick_webp())
            {
                return true;
            }
            else
            {
                return false;
            }
        }
    }

    public static function get_convert_to_avif()
    {
        $converter_method=get_option('compressx_converter_method',false);
        if(empty($converter_method))
        {
            $converter_method=CompressX_Image_Opt_Method::set_default_compress_server();
        }

        $convert_to_avif=get_option('compressx_output_format_avif','not init');
        if($convert_to_avif=='not init')
        {
            $convert_to_avif=CompressX_Image_Opt_Method::set_default_output_format_avif();
        }

        if($converter_method=='gd')
        {
            if($convert_to_avif&&CompressX_Image_Opt_Method::is_support_gd_avif())
            {
                return true;
            }
            else
            {
                return false;
            }
        }
        else
        {
            if($convert_to_avif&&CompressX_Image_Opt_Method::is_support_imagick_avif())
            {
                return true;
            }
            else
            {
                return false;
            }
        }
    }

    public static function get_compress_to_webp()
    {
        $converter_method=get_option('compressx_converter_method',false);
        if(empty($converter_method))
        {
            $converter_method=CompressX_Image_Opt_Method::set_default_compress_server();
        }

        $convert_to_webp=get_option('compressx_output_format_webp','not init');
        if($convert_to_webp=='not init')
        {
            $convert_to_webp=CompressX_Image_Opt_Method::set_default_output_format_webp();
        }

        if($converter_method=='gd')
        {
            if($convert_to_webp&&CompressX_Image_Opt_Method::is_support_gd_webp())
            {
                return true;
            }
            else
            {
                return false;
            }
        }
        else
        {
            if($convert_to_webp&&CompressX_Image_Opt_Method::is_support_imagick_webp())
            {
                return true;
            }
            else
            {
                return false;
            }
        }
    }

    public static function get_compress_to_avif()
    {
        $converter_method=get_option('compressx_converter_method',false);
        if(empty($converter_method))
        {
            $converter_method=CompressX_Image_Opt_Method::set_default_compress_server();
        }

        $convert_to_avif=get_option('compressx_output_format_avif','not init');
        if($convert_to_avif=='not init')
        {
            $convert_to_avif=CompressX_Image_Opt_Method::set_default_output_format_avif();
        }

        if($converter_method=='gd')
        {
            if($convert_to_avif&&CompressX_Image_Opt_Method::is_support_gd_avif())
            {
                return true;
            }
            else
            {
                return false;
            }
        }
        else
        {
            if($convert_to_avif&&CompressX_Image_Opt_Method::is_support_imagick_avif())
            {
                return true;
            }
            else
            {
                return false;
            }
        }
    }

    public static function get_converter_method()
    {
        $converter_method=get_option('compressx_converter_method',false);
        if(empty($converter_method))
        {
            $converter_method=CompressX_Image_Opt_Method::set_default_compress_server();
        }

        return $converter_method;
    }
}