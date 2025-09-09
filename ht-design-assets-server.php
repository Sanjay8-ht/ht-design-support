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
        add_action( 'parse_request', array( $this, 'serve_assets' ) );
        register_activation_hook( __FILE__, array( $this, 'activate' ) );
        register_deactivation_hook( __FILE__, array( $this, 'deactivate' ) );
    }
    
    public function activate() {
        update_option( 'htd_use_server_mode', true );
    }
    
    public function deactivate() {
        delete_option( 'htd_use_server_mode' );
    }
    
    public function serve_assets() {
        error_log('HT Design Assets Server: Hook fired. REQUEST_URI: ' . $_SERVER['REQUEST_URI']);
        
        if ( strpos( $_SERVER['REQUEST_URI'], '/ht-design-assets/' ) === false ) {
            error_log('HT Design Assets Server: Not an ht-design-assets request');
            return;
        }

        error_log('HT Design Assets Server: Processing ht-design-assets request');
        
        $file = basename( strtok( explode( '/ht-design-assets/', $_SERVER['REQUEST_URI'] )[1], '?' ) );
        $ext = pathinfo( $file, PATHINFO_EXTENSION );
        
        error_log('HT Design Assets Server: File: ' . $file . ', Extension: ' . $ext);

        if ( ! in_array( $ext, ['js', 'css'] ) ) { 
            error_log('HT Design Assets Server: Invalid file extension');
            status_header(403); 
            exit; 
        }

        // Try multiple possible paths
        $paths = [
            WP_CONTENT_DIR . '/ht-design/' . $file,
            wp_upload_dir()['basedir'] . '/ht-design/' . $file,
            wp_upload_dir()['basedir'] . '/ht-design-assets/' . $file
        ];
        
        error_log('HT Design Assets Server: Checking paths: ' . implode(', ', $paths));
        
        $found_path = null;
        foreach ($paths as $path) {
            if (file_exists($path)) {
                $found_path = $path;
                error_log('HT Design Assets Server: Found file at: ' . $path);
                break;
            }
        }
        
        if (!$found_path) {
            error_log('HT Design Assets Server: File not found in any path');
            status_header(404); 
            exit;
        }

        error_log('HT Design Assets Server: Serving file: ' . $found_path);
        header( 'Content-Type: ' . ($ext === 'js' ? 'application/javascript' : 'text/css') );
        header( 'Cache-Control: public, max-age=2592000' );
        readfile( $found_path );
        exit;
    }
}

new HT_Design_Assets_Server();