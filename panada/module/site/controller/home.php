<?php defined('THISPATH') or die('Can\'t access directly!');

class Site_controller_home extends Panada_module {
    
    public $curent_location;
    
    public function __construct(){
        
        parent::__construct();
        
        $this->session          = new Library_session;
        $this->db               = new Library_db;
        $this->curent_location  = urlencode('http://' .$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI']);
        $this->request          = new Library_request;
    }
    
    public function index(){
        
        if( ! $views['site'] = $this->db->find_one('sites', array('name' => $this->request->site_name(), 'status' => 1)) )
            Library_error::_404();
        
        $this->output('template/index', $views);
    }
}