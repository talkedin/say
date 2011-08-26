<?php defined('THISPATH') or die('Can\'t access directly!');

class Site_model_forums {
    
    public function __construct(){
        
        $this->db       = new Library_db;
        $this->cache    = new Library_cache;
    }
    
    public function bredcump($site_id, $forum_id){
        
        $cache_key = 'bredcump_'.$site_id.'_'.$forum_id;
        
        if( $cached = $this->cache->get_value($cache_key) )
            return $cached;
        
        $forums     = $this->find_all( array('site_id' => $site_id) );
        $bredcump   = $this->forum_to_breadcump($forum_id, $forums);
        $this->cache->set_value($cache_key, $bredcump);
        
        return $bredcump;
        
    }
    
    /**
     * @param int $forum_id
     * @param object $forum_obj
     * @return array
     */
    private function forum_to_breadcump($forum_id, $forum_obj){
        
        $return = array();
        
        foreach($forum_obj as $key => $forum){
            
            if($forum_id == $forum->forum_id){
                
                $return[$forum->name] = $forum->title;
                
                if($forum->parent_id > 0){
                    
                    // Biar loopingnya lebih sedikit
                    unset($forum_obj[$key]);
                    
                    $sub = $this->forum_to_breadcump($forum->parent_id, $forum_obj);
                    
                    $key = array_keys($sub);
                    
                    $return[$key[0]] = $sub[$key[0]];
                }
            }
        }
        
        return array_reverse($return);
    }
    
    public function find_all( $args = array() ){
        
        $defaults = array(
            'site_id' => 0
        );
        
        $args = array_merge($defaults, $args);
        
        if( ! $args['site_id'] )
            return false;
        
        if( ! $data = $this->db->order_by('forum_id')->find_all('forums', $args) )
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
        
        if( ! $data = $this->db->find_one('forums', $args ) )
            return false;
        
        return $data;
    }
    
    public function add_new( $data = array() ){
        
       return $this->db->insert( 'forums', $data );
    }
}