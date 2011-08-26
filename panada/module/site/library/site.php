<?php defined('THISPATH') or die('Can\'t access directly!');

class Site_library_site {
    
    public function site_name(){
        
        $host       = strtolower($_SERVER['HTTP_HOST']);
        $arr        = explode('.', $host);
        $max_key    = count($arr) - 1;
        
        if( $arr[$max_key -1].'.'.$arr[$max_key] != 'talked.in' )
            return $host;
        
        //if( ! preg_match('/[^a-zA-Z0-9_.-]/', $host) )
        //  return false;
        
        return $arr[0];
    }
    
    public function location($location = '', $is_rtrim = false, $trim_str = '/'){
        
        $validation = new Library_validation;
        $site_name  = $this->site_name();
        
        if( $validation->is_url($site_name) )
            $url = 'http://'.$site_name.'/';
        else
            $url = 'http://'.$site_name.'.talked.in/';
        
        $url .= $location;
        
        if($is_rtrim)
            return rtrim($url, $trim_str);
        
        return $url;
    }
    
    public function curent_location( $is_urlencode = true ){
        
        $location = 'http://' .$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];
        
        if( $is_urlencode )
            return urlencode( $location );
        
        return $location;
    }
    
    /**
     * Buat url thread berdasarkan data object-nya
     *
     * @param object
     * @return string
     */
    public function thread_url($thread_obj){
        
        $formatting = new Library_formatting;
        
        return $this->location( $formatting->mysql2date($thread_obj->create_date, 'Y/m/d/') . $thread_obj->name );
    }
}