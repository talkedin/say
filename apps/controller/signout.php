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
        
        $this->redirect($location);
    }
}