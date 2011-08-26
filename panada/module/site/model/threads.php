<?php defined('THISPATH') or die('Can\'t access directly!');

class Site_model_threads {
    
    public function __construct(){
        
        $this->db = new Library_db;
    }
    
    public function find_all( $args = array() ){
        
        $defaults = array(
            'site_id' => 0
        );
        
        $args = array_merge($defaults, $args);
        
        if( ! $args['site_id'] )
            return false;
        
        if( ! $data = $this->db->find_all('threads', $args ) )
            return false;
        
        return $data;
    }
    
    public function find_one( $args = array() ){
        
        $defaults = array(
            'site_id' => 0
        );
        
        $args = array_merge($defaults, $args);
        
        if( ! $args['site_id'] )
            return false;
        
        if( ! $data = $this->db->find_one('threads', $args ) )
            return false;
        
        return $data;
    }
}