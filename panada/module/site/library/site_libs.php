<?php defined('THISPATH') or die('Can\'t access directly!');

class Site_library_site_libs {
    
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
    
    public function location($location = ''){
        
        $validation = new Library_validation;
        $site_name  = $this->site_name();
        
        if( $validation->is_url($site_name) )
            $url = 'http://'.$site_name.'/';
        else
            $url = 'http://'.$site_name.'.talked.in/';
        
        $url .= $location;
        
        return $url;
    }
}