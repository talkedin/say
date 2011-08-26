<?php defined('THISPATH') or die('Can\'t access directly!');

class Controller_create extends Panada {
    
    public function __construct(){
        
        parent::__construct();
        
        $this->session  = new Library_session;
        $this->requst   = new Library_request;
        $this->db       = new Library_db;
    }
    
    public function index(){
        
        $views = array(
            'page_title' => 'Create New Forum'
        );
        
        if( $this->requst->post('submit') ){
            
            $data['name']           = $this->requst->post('forum_name');
            $data['author_id']      = $this->session->get('user_id');
            $data['description']    = $this->requst->post('description');
            $data['visibility']     = $this->requst->post('visibility');
            $data['create_date']    = date('Y-m-d H:i:s');
            
            $this->db->insert('sites', $data);
            
            $this->redirect( 'create?done&forum_id='.$this->db->insert_id() );
        }
        
        $this->output('dashboard/site_create', $views);
    }
}