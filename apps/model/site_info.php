<?php defined('THISPATH') or die('Can\'t access directly!');

class Model_site_info {
    
    public function __construct(){
        
        $this->db = new Library_db;
    }
    
    public function data($site_name, $field = false){
        
        if( ! $data = $this->find_one(array('name' => $site_name)) )
            return false;
        
        if( $field )
            return $data->$field;
        
        return $data;
    }
    
    public function find_one( $args = array() ){
        
        if( ! $data = $this->db->find_one('sites', $args ) )
            return false;
        
        return $data;
    }
    
    public function find_in( $site_ids = array() ){
        
        $return = array();
        
        foreach($site_ids as $id)
            $return[] = $this->find_one( array('site_id' => $id) );
        
        return $return;
    }
}