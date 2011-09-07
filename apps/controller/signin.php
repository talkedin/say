<?php defined('THISPATH') or die('Can\'t access directly!');

class Controller_signin extends Panada {
    
    public function __construct(){
        
        parent::__construct();
        
        $this->users        = new Model_users;
        $this->request      = new Library_request;
        $this->validation   = new Library_validation;
        $this->session      = new Library_session;
    }
    
    public function index(){
        
        $views['is_error'] = false;
        
        if( $this->request->post('signin') ){
            
            $password = trim( $this->request->post('pass') );
            $username = trim( $this->request->post('uname') );
            
            if( $password && $username ){
                
                $args = array();
                
                if( $email = $this->validation->is_email($username) )
                    $args['email'] = $email;
                else
                    $args['username'] = $username;
                
                $user = $this->users->find_one($args);
                
                $hashed_password = md5( md5($password) . $user->salt );
                
                if( $hashed_password == $user->password){
                    
                    $this->session->set(
                        array(
                            'username' => $user->username,
                            'user_id' => $user->user_id,
                            'avatar' => $this->users->find_gravatar($user->email),
                        )
                    );
                    
                    $location = ( $this->request->get('next') ) ? urldecode($this->request->get('next')) : '';
                    $this->redirect($location);
                }
                else{
                    $views['is_error'] = 'Wrong Username/Email and password combination.';
                }
            }
        }
        
        $this->output('accounts/signin', $views);
    }
}
