<?php defined('THISPATH') or die('Can\'t access directly!');

class Controller_signup extends Panada {
    
    public function __construct(){
        
        parent::__construct();
        
        $this->request = new Library_request;
        $this->users = new Model_users;
    }
    
    public function index(){
        
        if( $this->request->post('submit') ){
            
            $username = trim($this->request->post('username'));
            $email = strtolower(trim($this->request->post('email')));
            $password = trim($this->request->post('password'));
            
            $salt = rand();
            $password = md5( md5($password) . $salt );
            
            $data = array(
                'username' => $username,
                'email' => $email,
                'password' => $password,
                'salt' => $salt,
            );
            
            $this->users->add_new($data);
        }
        
        $this->output('accounts/signup');
    }
}