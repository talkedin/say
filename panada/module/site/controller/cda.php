<?php defined('THISPATH') or die('Can\'t access directly!');

class Site_controller_cda extends Panada_module {
    
    public function __construct(){
        
        parent::__construct();
        
        $this->users        = new Model_users;
        $this->request      = new Library_request;
        $this->validation   = new Library_validation;
        $this->session      = new Library_session;
    }
    
    public function index(){
        
        $auth_key = $this->request->get('ak');
        
        if( $user = $this->users->find_one( array('user_id' => $auth_key) ) ){
            
            $this->session->set(
                array(
                    'username' => $user->username,
                    'user_id' => $user->user_id,
                    'avatar' => $this->users->find_gravatar($user->email),
                )
            );
            
            $location = ( $this->request->get('next') ) ? urldecode($this->request->get('next')) : 'http://talked.in/';
            
            $this->redirect($location);
        }
    }
    
    public function signout(){
        
        $location = ( $this->request->get('next') ) ? urldecode($this->request->get('next')) : 'http://talked.in/';
        
        $this->session->session_clear_all();
        
        $this->redirect($location);
    }
}