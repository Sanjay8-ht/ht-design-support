<?php
/*
Plugin Name: HT Design Assets Server
Description: Serves HT Design assets to fix 403 errors on staging sites
Version: 1.0
Author: HoliThemes
*/

if ( ! defined( 'ABSPATH' ) ) exit;

class HT_Design_Assets_Server {
    
    public function __construct() {
        add_action( 'init', array( $this, 'serve_assets' ) );
    }
    
    public function serve_assets() {
        if ( strpos( $_SERVER['REQUEST_URI'], '/ht-design-assets/' ) === false ) return;

        $file = basename( strtok( explode( '/ht-design-assets/', $_SERVER['REQUEST_URI'] )[1], '?' ) );
        $ext = pathinfo( $file, PATHINFO_EXTENSION );

        if ( ! in_array( $ext, ['js', 'css'] ) ) { status_header(403); exit; }

        // Try multiple possible paths
        $paths = [
            WP_CONTENT_DIR . '/ht-design/' . $file,
            wp_upload_dir()['basedir'] . '/ht-design/' . $file,
            wp_upload_dir()['basedir'] . '/ht-design-assets/' . $file
        ];
        
        $found_path = null;
        foreach ($paths as $path) {
            if (file_exists($path)) {
                $found_path = $path;
                break;
            }
        }
        
        if (!$found_path) {
            // Debug: Log attempted paths
            error_log('HT Design Assets Server: File not found. Tried: ' . implode(', ', $paths));
            status_header(404); 
            exit;
        }

        header( 'Content-Type: ' . ($ext === 'js' ? 'application/javascript' : 'text/css') );
        header( 'Cache-Control: public, max-age=2592000' );
        readfile( $found_path );
        exit;
    }
}

new HT_Design_Assets_Server();