<?php defined('THISPATH') or die('Can\'t access directly!');

/**
 * Cookie seperti _cd (cross domain) hanya digunakan pada saat
 * signout, agar lebih efisien, deklarasi cookie _cd ini diberlakukan
 * hanya untuk path /acconts. Dengan alasan inilah semua method
 * di bawah berada dalam satu class ini.
 */
class Controller_accounts extends Panada {
    
    private $cookie_site_ids = '_cd';
    
    public function __construct(){
        
        parent::__construct();
        
        $this->users        = new Model_users;
        $this->request      = new Library_request;
        $this->validation   = new Library_validation;
        $this->cache        = new Library_cache('memcached');
        $this->session      = new Library_session;
        $this->site_info    = new Model_site_info;
    }
    
    /**
     * Method untuk proses signin
     */
    public function signin(){
        
        $views['is_error'] = false;
        $auth_key = 'auth_key_'.md5(session_id().time());
        
        
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
        
        $this->cache->set_value($auth_key, $user, 120);
        
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
    
    private function html_redirect($location, $signout_other = false, $is_top = true){
        
        $this->output('redirect', array('location' => $location, 'is_top' => $is_top, 'signout_other' => $signout_other) );
        exit;
    }
    
    /**
     * Mendaftarkan setiap site id untuk site yang
     * di mana user sudah signin.
     *
     * @param int $site_id
     * @return void
     */
    private function defined_signined_cooke($site_id){
        
        $sites = array();
        
        if( isset($_COOKIE[$this->cookie_site_ids] ) )
            $sites = explode(',', $_COOKIE[$this->cookie_site_ids]);
        
        if( in_array($site_id, $sites) )
            return;
        
        $sites[] = $site_id;
        
        $sites = implode(',', $sites);
        
        setcookie($this->cookie_site_ids, $sites, 0, '/accounts/', 'talked.in');
    }
    
    /**
     * Mendapatkan informasi site-site mana saja yang sudah
     * signin bagi user, dimana informasi ini akan digunakan untuk
     * melakukan proses signout sekaligus untuk semua site
     * yang sudah terdaftar. Setelah informasi ini didapat,
     * kemudian cookie akan dihapus.
     */
    private function undefined_signed_cookie(){
        
        if( ! isset($_COOKIE[$this->cookie_site_ids] ) )
            return false;
        
        $sites          = explode(',', $_COOKIE[$this->cookie_site_ids]);
        $signout_other  = $this->site_info->find_in($sites);
        
        setcookie($this->cookie_site_ids, null, time() - 3600, '/accounts/', 'talked.in');
        
        return $signout_other;
    }
    
    /**
     * Method untuk proses signout.
     */
    public function signout(){
        
        $this->session->session_clear_all();
        $signout_other = $this->undefined_signed_cookie();
        
        $location = ( $this->request->get('next') ) ? urldecode($this->request->get('next')) : 'http://talked.in/';
        
        $host       = parse_url($location, PHP_URL_HOST);
        $arr        = explode('.', $host);
        $max_key    = count($arr) - 1;
        
        if( $arr[$max_key -1].'.'.$arr[$max_key] == 'talked.in' )
            $this->html_redirect($location, $signout_other, false);
        
        $location = 'http://'.$host.'/cda/signout?next='.urlencode($location);
        $this->html_redirect($location, $signout_other, false);
    }
    
    /**
     * Method untuk proses signup.
     */
    public function signup(){
        
        // Apakah user sudah dalam keadaaan signedin?
        if( $user_id = $this->session->get('user_id') ){
            $this->sso_signin($user_id, $auth_key);
            return;
        }
        
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
    
    /**
     * Method untuk proses lupa password/username.
     */
    public function forgot(){
        
        // Apakah user sudah dalam keadaaan signedin?
        if( $user_id = $this->session->get('user_id') ){
            $this->sso_signin($user_id, $auth_key);
            return;
        }
        
        if( $uname = $this->requset->post('uname') && $this->requset->post('submit') ){
            
            $args = array('username' => $uname);
            
            if( $email = $this->validation->is_email($uname) ){
                $args = array('email' => $email);
            }
            
            if( $user = $this->users->find_one($args) ){
                
                $access_key = md5(session_id . time() . $user->user_id . $user->email);
                
                $this->cache->set_value($access_key, $user, 7200);
                
                // kemudian kirim email yg berisi kode url unik untuk merubah password
                // http://talked.in/accounts/forgot/recovery?ak=$access_key
                // Url ini hanya berlaku untuk 2 jam
            }
        }
    }
    
    /**
     * Method untuk proses recovery password/input passsword baru.
     */
    public function recovery(){
        
        $access_key = $this->requset->get('ak');
        
        // Beri 404 jika access key tidak valid.
        if( ! $user = $this->cache->get_value($access_key) )
            Library_error::_404();
        
        // Pastikan data tidak expired setelah user membuka halaman ini.
        $this->cache->set_value($access_key, $user, 7200);
        
        // Tampilkan form merubah password
    }
}