<?php defined('THISPATH') or die('Can\'t access directly!');

class Site_model_replies {
    
    public function __construct(){
        
        $this->db       = new Library_db;
        $this->cache    = new Library_cache;
    }
    
    public function find_all(){
        
    }
    
    public function find_one(){
        
    }
    
    public function add_new( $data = array() ){
        
       return $this->db->insert( 'replies', $data );
    }
    
    public function update(){
        
    }
}