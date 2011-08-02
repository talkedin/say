<?php defined('THISPATH') or die('Can\'t access directly!');

class Controller_signin extends Panada {
    
    public function __construct(){
        
        parent::__construct();
        
        $this->db           = new Library_db;
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
                
                $this->db->select()->from('users');
                
                if( $email = $this->validation->is_email($username) )
                    $this->db->where('email', '=', $email);
                else
                    $this->db->where('username', '=', $username);
                
                $user = $this->db->find_one();
                
                $hashed_password = md5( md5($password) . $user->salt );
                
                if( $hashed_password == $user->password){
                    
                    $this->session->set(
                        array(
                            'username' => $user->username,
                            'user_id' => $user->user_id,
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
