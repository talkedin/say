<?php defined('THISPATH') or die('Can\'t access directly!');
/**
 * Panada Request/input hendler.
 * 
 * @package	Panada
 * @subpackage	Library
 * @author	Iskandar Soesman
 * @since	Version 0.1
 */

class Library_request {
    
    public function get($key, $filter_type = false, $flags = false){
        
        if( isset($_GET[$key]) ) {
            
            if( $filter_type != false)
               return filter_input(INPUT_GET, $key, $filter_type, $flags);
            else
                return $_GET[$key];
        }
        else {
            return false;
        }
    }
    
    public function post($key, $filter_type = false, $flags = false){
        
        if( isset($_POST[$key]) ) {
            
            if( $filter_type != false)
               return filter_input(INPUT_POST, $key, $filter_type, $flags);
            else
                return $_POST[$key];
        }
        else {
            return false;
        }
    }
    
    public function cookie($key, $filter_type = false, $flags = false){
        
        if( isset($_COOKIE[$key]) ) {
            
            if( $filter_type != false)
               return filter_input(INPUT_COOKIE, $key, $filter_type, $flags);
            else
                return $_COOKIE[$key];
        }
        else {
            return false;
        }
    }
    
    
    public function strip_tags_attributes($str, $allowtags = null, $allowattributes = null){
        
        /**
         * ID:  Ada kemungkinan dimana string yang diinput diconvert dulu menjadi htmlentities.
         *      Untuk menghindari hal ini, maka semua format htmlentities dikembalikan (docode) dulu ke format aslinya.
         *
         *      $str = html_entity_decode($str, ENT_QUOTES);
         */
        
        /**
         * ID:  Jika string < diikuti dengan tanda non-alpha selain tanda ?, maka ubah menjadi &lt; (htmlentities)
         *      Ini berguna jika string yang diinput berupa emotion code seperpti <*_*> atau tanda panah <=
         */
        // Original $str = preg_replace(array('/<\*/', '/<=/', '/_/'), '&lt;\\1', $str);
        $str = preg_replace(array('/<\*/', '/<=/'), '&lt;\\1', $str);
        
        /**
         * ID:  Hapus semua tag html dan php yang tidak didefinisikan dari input string.
         */
        $str = strip_tags($str, $allowtags);
        
        /**
         * ID:  Kembalikan string &lt; menjadi <
         */
        $str = str_replace('&lt;', '<', $str);
        
        /**
         * EN:  See original function at http://php.net/manual/en/function.strip-tags.php#91498
         */
        if ( ! is_null($allowattributes) ) {
            
            if( ! is_array($allowattributes) )
                $allowattributes = explode(",", $allowattributes);
                
            if( is_array($allowattributes) )
                $allowattributes = implode(")(?<!",$allowattributes);
                
            if ( strlen($allowattributes) > 0 )
                $allowattributes = "(?<!".$allowattributes.")";
                
            $str = preg_replace_callback("/<[^>]*>/i",create_function(
                '$matches',
                'return preg_replace("/ [^ =]*'.$allowattributes.'=(\"[^\"]*\"|\'[^\']*\')/i", "", $matches[0]);'   
            ),$str);
        }
        
        return $str;
    }
    
    public function site_name(){
        
        $host       = strtolower($_SERVER['HTTP_HOST']);
        $arr        = explode('.', $host);
        $max_key    = count($arr) - 1;
        
        if( $arr[$max_key -1].'.'.$arr[$max_key] != 'talked.in' )
            return $host;
        
//        if( ! preg_match('/[^a-zA-Z0-9_.-]/', $host) )
//	    return false;
        
        return $arr[0];
    }
    
} // End Request Class