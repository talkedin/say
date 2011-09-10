<?php defined('THISPATH') or die('Can\'t access directly!');

class Controller_api extends Panada {
    
    public function __construct(){
        
        parent::__construct();
        
        $this->session  = new Library_session;
    }
    
    public function js(){
        
        // Pastikan file ini tidak dicache
        
        header('Expires: Mon, 1 Jul 1998 01:00:00 GMT');
        header('Cache-Control: no-store, no-cache, must-revalidate');
        header('Cache-Control: post-check=0, pre-check=0', false);
        header('Pragma: no-cache');
        header( 'Last-Modified: ' . gmdate( 'D, j M Y H:i:s' ) . ' GMT' );
        header('Content-type: text/javascript');
        
        if( $user_id = $this->session->get('user_id') ){
            echo 'top.location.href = \'http://talked.in/signin?next=\'+encodeURIComponent(location.href);';
        }
    }
}