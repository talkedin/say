<?php defined('THISPATH') or die('Can\'t access directly!');

class Site_model_replies {
    
    public function __construct(){
        
        $this->db       = new Library_db;
        $this->cache    = new Library_cache;
    }
    
    public function find_all($args){
        
        $defaults = array(
            'site_id' => 0,
            'page' => 1,
            'limit' => 20,
            'parent_id' => 0
        );
        
        $args = array_merge($defaults, $args);
        
        if( ! $args['site_id'] )
            return false;
        
        // Pastikan tidak ada nilai yg minus
        if($args['limit'] < 1)
            $args['limit'] = 1;
        
        if($args['page'] < 1)
            $args['page'] = 1;
        
        $offset = ($args['limit'] * $args['page']) - $args['limit'];
        $this->db->order_by('date', 'ASC')->limit($args['limit'], $offset);
        
        unset($args['limit'], $args['page']);
        
        if( ! $data = $this->db->find_all('replies', $args ) )
            return false;
        
        return $data;
    }
    
    public function find_total($args){
        
        $defaults = array(
            'site_id' => 0,
            'page' => 1,
            'limit' => 20,
            'parent_id' => 0
        );
        
        $args = array_merge($defaults, $args);
        
        if( ! $args['site_id'] )
            return false;
        
        unset($args['limit'], $args['page']);
        
        $this->db->select('COUNT(*)')->from('replies');
        
        foreach($args as $key => $val)
            $this->db->where($key, '=', $val, 'and');
        
        if( ! $data = $this->db->find_var() )
            return false;
        
        return $data;
    }
    
    public function find_one(){
        
    }
    
    public function add_new( $data = array() ){
        
       return $this->db->insert( 'replies', $data );
    }
    
    public function update( $data = array(), $where = array() ){
        
        return $this->db->update('replies', $data, $where);
    }
}