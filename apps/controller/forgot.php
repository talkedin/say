<?php defined('THISPATH') or die('Can\'t access directly!');

class Controller_forgot extends Panda {
    
    public function __construct(){
        
        parent::__construct();
        
        $this->users = new Model_users;
        $this->cache = new Library_cache('memcached');
        $this->validation = new Library_validation;
        $this->requset = new Library_requst;
    }
    
    public function index(){
        
        if( $uname = $this->requset->post('uname') && $this->requset->post('submit') ){
            
            $args = array('username' => $uname);
            
            if( $email = $this->validation->is_email($uname) ){
                $args = array('email' => $email);
            }
            
            if( $user = $this->users->find_one($args) ){
                
                $access_key = md5(session_id . time() . $user->user_id . $user->email);
                
                $this->cache->set_value($access_key, $user, 7200);
                
                // kemudian kirim email yg berisi kode url unik untuk merubah password
                // http://talked.in/forgot/activate?ak=$access_key
                // Url ini hanya berlaku untuk 2 jam
            }
        }
    }
    
    public function activate(){
        
        $access_key = $this->requset->get('ak');
        
        // Beri 404 jika access key tidak valid.
        if( ! $user = $this->cache->get_value($access_key) )
            Library_error::_404();
        
        // Pastikan data tidak expired setelah user melakukan aktivasi
        $this->cache->set_value($access_key, $user, 7200);
        
        // Tampilkan form merubah password
    }
}