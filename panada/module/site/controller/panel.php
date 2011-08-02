<?php defined('THISPATH') or die('Can\'t access directly!');

class Site_controller_panel extends Panada_module {
    
    public function __construct(){
        
        parent::__construct();
        
        $this->session  = new Library_session;
        $this->db       = new Library_db;
        $this->site_libs= new Site_library_site_libs;
        
    }
    
    public function index(){
        
        $this->output('panel/index');
    }
}