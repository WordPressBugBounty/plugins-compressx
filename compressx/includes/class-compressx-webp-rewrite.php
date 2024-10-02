<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

class CompressX_Webp_Rewrite
{
    public function __construct()
    {

    }

    public function create_rewrite_rules()
    {
        $this->create_wp_content_rules();
        $this->create_uploads_rules();
    }

    public function transfer_path($path)
    {
        return preg_replace( '/(\/|\\\\)/', '/', rtrim( $path, '\/' ) );
    }

    public function get_wordpress_root()
    {
        if( defined('WP_CONTENT_DIR'))
        {
            $path=dirname(WP_CONTENT_DIR);
        }
        else
        {
            $path=ABSPATH;
        }

        return $this->transfer_path($path);
    }

    public function create_wp_content_rules()
    {
        $document_root=$this->transfer_path($_SERVER['DOCUMENT_ROOT']);
        $wordpress_root=$this->get_wordpress_root();
        $document_root2='%{DOCUMENT_ROOT}'.str_replace($document_root,'',$wordpress_root);
        $document_root3=str_replace($document_root,'',$wordpress_root);

        $path=WP_CONTENT_DIR;

        $upload_root  = wp_parse_url( content_url() );
        $upload_root  = $upload_root['path'].'/compressx-nextgen';
        $upload_root2=str_replace($document_root3,'',$upload_root);

        $htaccess_file=$path . '/.htaccess';

        $line[]='<IfModule mod_rewrite.c>';
        $line[]='RewriteEngine On';
        $line[]='RewriteOptions Inherit';
        $line[]='RewriteCond %{QUERY_STRING} original$';
        $line[]='RewriteCond %{REQUEST_FILENAME} -f';
        $line[]='RewriteRule . - [L]';

        $line[]='RewriteCond %{HTTP_ACCEPT} image/avif';
        $line[]='RewriteCond %{REQUEST_FILENAME} -f';

        if($document_root2=='%{DOCUMENT_ROOT}')
        {
            $line[]='RewriteCond %{DOCUMENT_ROOT}'.$upload_root.'/$1.avif -f';
            $line[]='RewriteRule (.+)\.avif '.$upload_root.'/$1.avif [NC,T=image/avif,L]';
        }
        else
        {
            $line[]='RewriteCond %{DOCUMENT_ROOT}'.$upload_root.'/$1.avif -f [OR]';
            $line[]='RewriteCond %{DOCUMENT_ROOT}'.$upload_root2.'/$1.avif -f';
            $line[]='RewriteRule (.+)\.avif '.$upload_root.'/$1.avif [NC,T=image/avif,L]';
        }

        $line[]='RewriteCond %{HTTP_ACCEPT} image/avif';
        $line[]='RewriteCond %{REQUEST_FILENAME} -f';

        if($document_root2=='%{DOCUMENT_ROOT}')
        {
            $line[]='RewriteCond %{DOCUMENT_ROOT}'.$upload_root.'/$1.webp.avif -f';
            $line[]='RewriteRule (.+)\.webp '.$upload_root.'/$1.webp.avif [NC,T=image/avif,L]';
        }
        else
        {
            $line[]='RewriteCond %{DOCUMENT_ROOT}'.$upload_root.'/$1.webp.avif -f [OR]';
            $line[]='RewriteCond %{DOCUMENT_ROOT}'.$upload_root2.'/$1.webp.avif -f';
            $line[]='RewriteRule (.+)\.webp '.$upload_root.'/$1.webp.avif [NC,T=image/avif,L]';
        }

        $line[]='RewriteCond %{HTTP_ACCEPT} image/avif';
        $line[]='RewriteCond %{REQUEST_FILENAME} -f';

        if($document_root2=='%{DOCUMENT_ROOT}')
        {
            $line[]='RewriteCond %{DOCUMENT_ROOT}'.$upload_root.'/$1.jpg.avif -f';
            $line[]='RewriteRule (.+)\.jpg$ '.$upload_root.'/$1.jpg.avif [NC,T=image/avif,L]';
        }
        else
        {
            $line[]='RewriteCond %{DOCUMENT_ROOT}'.$upload_root.'/$1.jpg.avif -f [OR]';
            $line[]='RewriteCond %{DOCUMENT_ROOT}'.$upload_root2.'/$1.jpg.avif -f';
            $line[]='RewriteRule (.+)\.jpg$ '.$upload_root.'/$1.jpg.avif [NC,T=image/avif,L]';
        }

        $line[]='RewriteCond %{HTTP_ACCEPT} image/avif';
        $line[]='RewriteCond %{REQUEST_FILENAME} -f';

        if($document_root2=='%{DOCUMENT_ROOT}')
        {
            $line[]='RewriteCond %{DOCUMENT_ROOT}'.$upload_root.'/$1.png.avif -f';
            $line[]='RewriteRule (.+)\.png$ '.$upload_root.'/$1.png.avif [NC,T=image/avif,L]';
        }
        else
        {
            $line[]='RewriteCond %{DOCUMENT_ROOT}'.$upload_root.'/$1.png.avif -f [OR]';
            $line[]='RewriteCond %{DOCUMENT_ROOT}'.$upload_root2.'/$1.png.avif -f';
            $line[]='RewriteRule (.+)\.png$ '.$upload_root.'/$1.png.avif [NC,T=image/avif,L]';
        }


        $line[]='RewriteCond %{HTTP_ACCEPT} image/avif';
        $line[]='RewriteCond %{REQUEST_FILENAME} -f';

        if($document_root2=='%{DOCUMENT_ROOT}')
        {
            $line[]='RewriteCond %{DOCUMENT_ROOT}'.$upload_root.'/$1.jpeg.avif -f';
            $line[]='RewriteRule (.+)\.jpeg$ '.$upload_root.'/$1.jpeg.avif [NC,T=image/avif,L]';
        }
        else
        {
            $line[]='RewriteCond %{DOCUMENT_ROOT}'.$upload_root.'/$1.jpeg.avif -f [OR]';
            $line[]='RewriteCond %{DOCUMENT_ROOT}'.$upload_root2.'/$1.jpeg.avif -f';
            $line[]='RewriteRule (.+)\.jpeg$ '.$upload_root.'/$1.jpeg.avif [NC,T=image/avif,L]';
        }


        $line[]='RewriteCond %{HTTP_ACCEPT} image/webp';
        $line[]='RewriteCond %{REQUEST_FILENAME} -f';
        if($document_root2=='%{DOCUMENT_ROOT}')
        {
            $line[]='RewriteCond %{DOCUMENT_ROOT}'.$upload_root.'/$1.webp -f';
            $line[]='RewriteRule (.+)\.webp '.$upload_root.'/$1.webp [NC,T=image/webp,L]';
        }
        else
        {
            $line[]='RewriteCond %{DOCUMENT_ROOT}'.$upload_root.'/$1.webp -f [OR]';
            $line[]='RewriteCond %{DOCUMENT_ROOT}'.$upload_root2.'/$1.webp -f';
            $line[]='RewriteRule (.+)\.webp '.$upload_root.'/$1.webp [NC,T=image/webp,L]';
        }


        $line[]='RewriteCond %{HTTP_ACCEPT} image/webp';
        $line[]='RewriteCond %{REQUEST_FILENAME} -f';

        if($document_root2=='%{DOCUMENT_ROOT}')
        {
            $line[]='RewriteCond %{DOCUMENT_ROOT}'.$upload_root.'/$1.jpg.webp -f';
            $line[]='RewriteRule (.+)\.jpg$ '.$upload_root.'/$1.jpg.webp [NC,T=image/webp,L]';
        }
        else
        {
            $line[]='RewriteCond %{DOCUMENT_ROOT}'.$upload_root.'/$1.jpg.webp -f [OR]';
            $line[]='RewriteCond %{DOCUMENT_ROOT}'.$upload_root2.'/$1.jpg.webp -f';
            $line[]='RewriteRule (.+)\.jpg$ '.$upload_root.'/$1.jpg.webp [NC,T=image/webp,L]';
        }

        $line[]='RewriteCond %{HTTP_ACCEPT} image/webp';
        $line[]='RewriteCond %{REQUEST_FILENAME} -f';

        if($document_root2=='%{DOCUMENT_ROOT}')
        {
            $line[]='RewriteCond %{DOCUMENT_ROOT}'.$upload_root.'/$1.png.webp -f';
            $line[]='RewriteRule (.+)\.png$ '.$upload_root.'/$1.png.webp [NC,T=image/webp,L]';
        }
        else
        {
            $line[]='RewriteCond %{DOCUMENT_ROOT}'.$upload_root.'/$1.png.webp -f [OR]';
            $line[]='RewriteCond %{DOCUMENT_ROOT}'.$upload_root2.'/$1.png.webp -f';
            $line[]='RewriteRule (.+)\.png$ '.$upload_root.'/$1.png.webp [NC,T=image/webp,L]';
        }

        $line[]='RewriteCond %{HTTP_ACCEPT} image/webp';
        $line[]='RewriteCond %{REQUEST_FILENAME} -f';

        if($document_root2=='%{DOCUMENT_ROOT}')
        {
            $line[]='RewriteCond %{DOCUMENT_ROOT}'.$upload_root.'/$1.jpeg.webp -f';
            $line[]='RewriteRule (.+)\.jpeg$ '.$upload_root.'/$1.jpeg.webp [NC,T=image/webp,L]';
        }
        else
        {
            $line[]='RewriteCond %{DOCUMENT_ROOT}'.$upload_root.'/$1.jpeg.webp -f [OR]';
            $line[]='RewriteCond %{DOCUMENT_ROOT}'.$upload_root2.'/$1.jpeg.webp -f';
            $line[]='RewriteRule (.+)\.jpeg$ '.$upload_root.'/$1.jpeg.webp [NC,T=image/webp,L]';
        }

        $options=get_option('compressx_general_settings',array());
        $disable_cache_control=isset($options['disable_cache_control'])?$options['disable_cache_control']:false;

        if(!$disable_cache_control)
        {
            $line[]='</IfModule>';
            $line[]='<IfModule mod_headers.c>';
            $line[]='<FilesMatch "(?i)\.(jpg|png|webp|jpeg)(\.(webp|avif))?$">';
            $line[]='Header always set Cache-Control "private"';
            $line[]='Header append Vary "Accept"';
            $line[]='</FilesMatch>';
            $line[]='</IfModule>';
        }

        insert_with_markers($htaccess_file,'CompressX',$line);
    }

    public function create_uploads_rules()
    {
        $document_root=$this->transfer_path($_SERVER['DOCUMENT_ROOT']);
        $wordpress_root=$this->get_wordpress_root();
        $document_root2='%{DOCUMENT_ROOT}'.str_replace($document_root,'',$wordpress_root);
        $document_root3=str_replace($document_root,'',$wordpress_root);

        $upload_dir = wp_upload_dir();
        $path=$upload_dir['basedir'];

        $upload_root  = wp_parse_url( content_url() );
        $upload_root  = $upload_root['path'].'/compressx-nextgen/uploads';
        $upload_root2=str_replace($document_root3,'',$upload_root);

        $htaccess_file=$path . '/.htaccess';

        $line[]='<IfModule mod_rewrite.c>';
        $line[]='RewriteEngine On';
        $line[]='RewriteOptions Inherit';
        $line[]='RewriteCond %{QUERY_STRING} original$';
        $line[]='RewriteCond %{REQUEST_FILENAME} -f';
        $line[]='RewriteRule . - [L]';

        $line[]='RewriteCond %{HTTP_ACCEPT} image/avif';
        $line[]='RewriteCond %{REQUEST_FILENAME} -f';
        if($document_root2=='%{DOCUMENT_ROOT}')
        {
            $line[]='RewriteCond %{DOCUMENT_ROOT}'.$upload_root.'/$1.avif -f';
            $line[]='RewriteRule (.+)\.avif '.$upload_root.'/$1.avif [NC,T=image/avif,L]';
        }
        else
        {
            $line[]='RewriteCond %{DOCUMENT_ROOT}'.$upload_root.'/$1.avif -f [OR]';
            $line[]='RewriteCond %{DOCUMENT_ROOT}'.$upload_root2.'/$1.avif -f';
            $line[]='RewriteRule (.+)\.avif '.$upload_root.'/$1.avif [NC,T=image/avif,L]';
        }

        $line[]='RewriteCond %{HTTP_ACCEPT} image/avif';
        $line[]='RewriteCond %{REQUEST_FILENAME} -f';

        if($document_root2=='%{DOCUMENT_ROOT}')
        {
            $line[]='RewriteCond %{DOCUMENT_ROOT}'.$upload_root.'/$1.webp.avif -f';
            $line[]='RewriteRule (.+)\.webp '.$upload_root.'/$1.webp.avif [NC,T=image/avif,L]';
        }
        else
        {
            $line[]='RewriteCond %{DOCUMENT_ROOT}'.$upload_root.'/$1.webp.avif -f [OR]';
            $line[]='RewriteCond %{DOCUMENT_ROOT}'.$upload_root2.'/$1.webp.avif -f';
            $line[]='RewriteRule (.+)\.webp '.$upload_root.'/$1.webp.avif [NC,T=image/avif,L]';
        }


        $line[]='RewriteCond %{HTTP_ACCEPT} image/avif';
        $line[]='RewriteCond %{REQUEST_FILENAME} -f';
        if($document_root2=='%{DOCUMENT_ROOT}')
        {
            $line[]='RewriteCond %{DOCUMENT_ROOT}'.$upload_root.'/$1.jpg.avif -f';
            $line[]='RewriteRule (.+)\.jpg$ '.$upload_root.'/$1.jpg.avif [NC,T=image/avif,L]';
        }
        else
        {
            $line[]='RewriteCond %{DOCUMENT_ROOT}'.$upload_root.'/$1.jpg.avif -f [OR]';
            $line[]='RewriteCond %{DOCUMENT_ROOT}'.$upload_root2.'/$1.jpg.avif -f';
            $line[]='RewriteRule (.+)\.jpg$ '.$upload_root.'/$1.jpg.avif [NC,T=image/avif,L]';
        }

        $line[]='RewriteCond %{HTTP_ACCEPT} image/avif';
        $line[]='RewriteCond %{REQUEST_FILENAME} -f';

        if($document_root2=='%{DOCUMENT_ROOT}')
        {
            $line[]='RewriteCond %{DOCUMENT_ROOT}'.$upload_root.'/$1.png.avif -f';
            $line[]='RewriteRule (.+)\.png$ '.$upload_root.'/$1.png.avif [NC,T=image/avif,L]';
        }
        else
        {
            $line[]='RewriteCond %{DOCUMENT_ROOT}'.$upload_root.'/$1.png.avif -f [OR]';
            $line[]='RewriteCond %{DOCUMENT_ROOT}'.$upload_root2.'/$1.png.avif -f';
            $line[]='RewriteRule (.+)\.png$ '.$upload_root.'/$1.png.avif [NC,T=image/avif,L]';
        }

        $line[]='RewriteCond %{HTTP_ACCEPT} image/avif';
        $line[]='RewriteCond %{REQUEST_FILENAME} -f';

        if($document_root2=='%{DOCUMENT_ROOT}')
        {
            $line[]='RewriteCond %{DOCUMENT_ROOT}'.$upload_root.'/$1.jpeg.avif -f';
            $line[]='RewriteRule (.+)\.jpeg$ '.$upload_root.'/$1.jpeg.avif [NC,T=image/avif,L]';
        }
        else
        {
            $line[]='RewriteCond %{DOCUMENT_ROOT}'.$upload_root.'/$1.jpeg.avif -f [OR]';
            $line[]='RewriteCond %{DOCUMENT_ROOT}'.$upload_root2.'/$1.jpeg.avif -f';
            $line[]='RewriteRule (.+)\.jpeg$ '.$upload_root.'/$1.jpeg.avif [NC,T=image/avif,L]';
        }

        $line[]='RewriteCond %{HTTP_ACCEPT} image/webp';
        $line[]='RewriteCond %{REQUEST_FILENAME} -f';
        if($document_root2=='%{DOCUMENT_ROOT}')
        {
            $line[]='RewriteCond %{DOCUMENT_ROOT}'.$upload_root.'/$1.webp -f';
            $line[]='RewriteRule (.+)\.webp '.$upload_root.'/$1.webp [NC,T=image/webp,L]';
        }
        else
        {
            $line[]='RewriteCond %{DOCUMENT_ROOT}'.$upload_root.'/$1.webp -f [OR]';
            $line[]='RewriteCond %{DOCUMENT_ROOT}'.$upload_root2.'/$1.webp -f';
            $line[]='RewriteRule (.+)\.webp '.$upload_root.'/$1.webp [NC,T=image/webp,L]';
        }

        $line[]='RewriteCond %{HTTP_ACCEPT} image/webp';
        $line[]='RewriteCond %{REQUEST_FILENAME} -f';

        if($document_root2=='%{DOCUMENT_ROOT}')
        {
            $line[]='RewriteCond %{DOCUMENT_ROOT}'.$upload_root.'/$1.jpg.webp -f';
            $line[]='RewriteRule (.+)\.jpg$ '.$upload_root.'/$1.jpg.webp [NC,T=image/webp,L]';
        }
        else
        {
            $line[]='RewriteCond %{DOCUMENT_ROOT}'.$upload_root.'/$1.jpg.webp -f [OR]';
            $line[]='RewriteCond %{DOCUMENT_ROOT}'.$upload_root2.'/$1.jpg.webp -f';
            $line[]='RewriteRule (.+)\.jpg$ '.$upload_root.'/$1.jpg.webp [NC,T=image/webp,L]';
        }

        $line[]='RewriteCond %{HTTP_ACCEPT} image/webp';
        $line[]='RewriteCond %{REQUEST_FILENAME} -f';

        if($document_root2=='%{DOCUMENT_ROOT}')
        {
            $line[]='RewriteCond %{DOCUMENT_ROOT}'.$upload_root.'/$1.png.webp -f';
            $line[]='RewriteRule (.+)\.png$ '.$upload_root.'/$1.png.webp [NC,T=image/webp,L]';
        }
        else
        {
            $line[]='RewriteCond %{DOCUMENT_ROOT}'.$upload_root.'/$1.png.webp -f [OR]';
            $line[]='RewriteCond %{DOCUMENT_ROOT}'.$upload_root2.'/$1.png.webp -f';
            $line[]='RewriteRule (.+)\.png$ '.$upload_root.'/$1.png.webp [NC,T=image/webp,L]';
        }

        $line[]='RewriteCond %{HTTP_ACCEPT} image/webp';
        $line[]='RewriteCond %{REQUEST_FILENAME} -f';
        if($document_root2=='%{DOCUMENT_ROOT}')
        {
            $line[]='RewriteCond %{DOCUMENT_ROOT}'.$upload_root.'/$1.jpeg.webp -f';
            $line[]='RewriteRule (.+)\.jpeg$ '.$upload_root.'/$1.jpeg.webp [NC,T=image/webp,L]';
        }
        else
        {
            $line[]='RewriteCond %{DOCUMENT_ROOT}'.$upload_root.'/$1.jpeg.webp -f [OR]';
            $line[]='RewriteCond %{DOCUMENT_ROOT}'.$upload_root2.'/$1.jpeg.webp -f';
            $line[]='RewriteRule (.+)\.jpeg$ '.$upload_root.'/$1.jpeg.webp [NC,T=image/webp,L]';
        }

        $line[]='</IfModule>';
        insert_with_markers($htaccess_file,'CompressX',$line);
    }

    public function remove_rewrite_rule()
    {
        $path=WP_CONTENT_DIR;
        $htaccess_file=$path . '/.htaccess';
        insert_with_markers($htaccess_file,'CompressX','');

        $upload_dir = wp_upload_dir();
        $path=$upload_dir['basedir'];
        $htaccess_file=$path . '/.htaccess';
        insert_with_markers($htaccess_file,'CompressX','');
    }
}