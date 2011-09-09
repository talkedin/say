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
    
    public function add_new( $data = array() ){
        
        if( $this->db->insert( 'threads', $data ) )
            return $this->db->insert_id();
        
        return false;
    }
    
    public function update( $data = array(), $where = array() ){
        
        return $this->db->update('threads', $data, $where);
    }
    
    public function create_unque_name($name, $site_id){
        
        if( $thread = $this->find_one( array('name' => $name, 'site_id' => $site_id) ) ){
            
            $int = preg_replace('/[^0-9]/', '', $thread->name);
            
            if( $int > 0 )
                return $name.'-'.($int + 1);
            
            return rtrim($name, '-').'-2';
        }
        
        return $name;
    }
}