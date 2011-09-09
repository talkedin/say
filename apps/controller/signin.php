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
                    
                    $location   = ( $this->request->get('next') ) ? urldecode($this->request->get('next')) : 'http://talked.in/';
                    $host       = parse_url($location, PHP_URL_HOST);
                    $arr        = explode('.', $host);
                    $max_key    = count($arr) - 1;
                    
                    if( $arr[$max_key -1].'.'.$arr[$max_key] == 'talked.in' )
                        $this->html_redirect($location);
                    
                    $location = 'http://'.$host.'/cda?next='.urlencode($location).'&ak='.$user->user_id;
                    $this->html_redirect($location);
                }
                else{
                    $views['is_error'] = 'Wrong Username/Email and password combination.';
                }
            }
        }
        
        $this->output('accounts/signin', $views);
    }
    
    private function html_redirect($location){
        
        $this->output('signin', array('location' => $location) );
        exit;
    }
}
