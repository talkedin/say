<?php defined('THISPATH') or die('Can\'t access directly!');

class Controller_signin extends Panada {
    
    public function __construct(){
        
        parent::__construct();
        
        $this->users        = new Model_users;
        $this->site_info    = new Model_site_info;
        $this->request      = new Library_request;
        $this->validation   = new Library_validation;
        $this->session      = new Library_session;
        $this->cache        = new Library_cache('memcached');
    }
    
    public function index(){
        
        $views['is_error'] = false;
        $auth_key = 'auth_key_'.md5(session_id().time());
        $site_name = false;
        
        if( $site_name = $this->request->get('next') ){
            $site_name = parse_url($site_name, PHP_URL_HOST);
        }
        
        
        // Apakah user sudah dalam keadaaan signedin?
        if( $user_id = $this->session->get('user_id') ){
            $this->sso_signin($user_id, $auth_key);
            return;
        }
        
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
                    
                    $this->sso_signin($user, $auth_key);
                }
                else{
                    $views['is_error'] = 'Wrong Username/Email and password combination.';
                }
            }
        }
        
        $this->output('accounts/signin', $views);
    }
    
    private function sso_signin($user, $auth_key){
        
        if( is_numeric($user) )
            $user = $this->users->find_one( array('user_id' => $user) );
        
        $this->cache->set_value($auth_key, $user, 300);
        
        $location   = ( $this->request->get('next') ) ? urldecode($this->request->get('next')) : 'http://talked.in/';
        $host       = parse_url($location, PHP_URL_HOST);
        $arr        = explode('.', $host);
        $max_key    = count($arr) - 1;
        
        if( $arr[$max_key -1].'.'.$arr[$max_key] == 'talked.in' )
            $this->html_redirect($location);
        
        $site_id = $this->site_info->data($host, 'site_id');
        $this->defined_signined_cooke($site_id);
        
        $location = 'http://'.$host.'/cda?next='.urlencode($location).'&ak='.$auth_key;
        
        $this->html_redirect($location);
    }
    
    private function html_redirect($location){
        
        $this->output('redirect', array('location' => $location, 'is_top' => true, 'signout_other' => false) );
        exit;
    }
    
    private function defined_signined_cooke($site_id){
        
        $cookie_name = 'DSC';
        
        $sites = array();
        
        if( isset($_COOKIE[$cookie_name] ) )
            $sites = explode(',', $_COOKIE[$cookie_name]);
        
        if( in_array($site_id, $sites) )
            return;
        
        $sites[] = $site_id;
        
        $sites = implode(',', $sites);
        
        setcookie($cookie_name, $sites, 0, '/', 'talked.in');
    }
}
