<?php defined('THISPATH') or die('Can\'t access directly!');

class Controller_home extends Panada {
    
    public function __construct(){
        
        parent::__construct();
        
        $this->session = new Library_session;
    }
    
    public function index(){
        
        $views = array(
            'page_title' => 'Home',
            'body' => 'This is hello world body!',
        );
        
        if( ! $this->session->get('user_id') ){
            $this->output('index', $views);
            return;
        }
        
        
        $this->dashboard();
    }
    
    private function dashboard(){
        
        $this->db = new Library_db;
        
        $views = array(
            'page_title' => 'Home',
            'body' => 'This is hello world body!',
        );
        
        $views['sites'] = $this->db->find_all('sites', array('author_id' => $this->session->get('user_id') ) );
        
        $this->output('dashboard/home', $views);
    }
}
