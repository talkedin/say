<?php defined('THISPATH') or die('Can\'t access directly!');

class Site_controller_home extends Panada_module {
    
    public $curent_location;
    
    public function __construct(){
        
        parent::__construct();
        
        $this->session          = new Library_session;
        $this->db               = new Library_db;
        $this->curent_location  = urlencode('http://' .$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI']);
    }
    
    private function site_name(){
        
        $host       = strtolower($_SERVER['HTTP_HOST']);
        $arr        = explode('.', $host);
        $max_key    = count($arr) - 1;
        
        if( $arr[$max_key -1].'.'.$arr[$max_key] != 'talked.in' )
            return $host;
        
        return $arr[0];
    }
    
    public function index(){
        
        $views['site'] = $this->db->find_one('sites', array('name' => $this->site_name(), 'status' => 1));
        
        $this->output('template/index', $views);
    }
}