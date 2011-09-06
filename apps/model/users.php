<?php defined('THISPATH') or die('Can\'t access directly!');

class Model_users {
    
    public function __construct(){
        
        $this->cache = new Library_cache('memcached');
        $this->db = new Library_db;
    }
    
    /**
     * Mendapatan user berdasarkan kriteria utamanya.
     * Kriteria utamanya adalah: user_id, username dan email.
     * Pada saat melakukan query yg lebih dari satu kriteria,
     * salah satu dari ketiga field diatas harus diletakkan pada
     * awal array.
     *
     * @param array $args
     * @return object if true, otherwise will false.
     */
    public function find_one( $args = array() ){
        
        if( empty($args) )
            return false;
        
        $key = array_keys($args);
	$key = $key[0];
        
        $cache_key  = 'find_one_user_'.$args[$key];
        
        if( $cached = $this->cache->get_value($cache_key) )
            return $cached;
        
        if( ! $data = $this->db->find_one('users', $args ) )
            return false;
        
        $this->cache->set_value($cache_key, $data);
        
        return $data;
    }
    
    /**
     * Menghapus cache object dari data user.
     *
     * @param int $user_id
     * @return bool
     */
    public function flush_one($user_id = 0){
        
        $user = $this->find_one( array('user_id' => $user_id) );
        
        $this->cache->delete_value( 'find_one_user_'.$user->user_id );
        $this->cache->delete_value( 'find_one_user_'.$user->username );
        $this->cache->delete_value( 'find_one_user_'.$user->email );
        
        return true;
    }
    
    public function find_gravatar($email, $s = 48, $is_https = false){
        
        $hash = md5(strtolower($email));
        $http = 'http';
        
        if($is_https)
            $http = 'https';
        
        return $http.'://www.gravatar.com/avatar/'.$hash.'?s='.$s;
    }
}