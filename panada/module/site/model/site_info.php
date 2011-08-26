<?php defined('THISPATH') or die('Can\'t access directly!');

class Site_model_site_info {
    
    public function __construct(){
        
        $this->db = new Library_db;
    }
    
    public function data($field = false){
        
        $library_site = new Site_library_site;
        
        if( ! $data = $this->db->find_one('sites', array('name' => $library_site->site_name()) ) )
            return false;
        
        if( $field )
            return $data->$field;
        
        return $data;
    }
    
}