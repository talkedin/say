<?php defined('THISPATH') or die('Can\'t access directly!');

class Controller_signout extends Panada {
    
    public function __construct(){
        
        parent::__construct();
        
        $this->session = new Library_session;
        $this->request = new Library_request;
    }
    
    public function index(){
        
        $this->session->session_clear_all();
        
        $location = ( $this->request->get('next') ) ? urldecode($this->request->get('next')) : '';
        
        $host       = parse_url($location, PHP_URL_HOST);
        $arr        = explode('.', $host);
        $max_key    = count($arr) - 1;
        
        if( $arr[$max_key -1].'.'.$arr[$max_key] == 'talked.in' )
            $this->redirect($location);
        
        $location = 'http://'.$host.'/cda/signout?next='.urlencode($location);
        $this->redirect($location);
    }
}