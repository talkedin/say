<?php defined('THISPATH') or die('Can\'t access directly!');

class Site_controller_alias extends Panada_module {
    
    private $site_info;
    
    public function __construct(){
        
        parent::__construct();
        
        $this->session          = new Library_session;
        $this->request          = new Library_request;
        $this->library_site     = new Site_library_site;
        $this->model_forums     = new Site_model_forums;
        $this->model_threads    = new Site_model_threads;
        $this->model_site_info  = new Site_model_site_info;
    }
    
    public function index(){
        
        /*
         Paging array
        $array = array(1,2,3,4,5,6,7,8,9,10,11,12,13,14,15,16,17);
        
        $limit = 5;
        $page = 4;
        $total = count($array);
        $offset = ($limit * $page) - $limit;
        
        print_r($array);
        
        echo '<br /><br />';
        
        print_r( array_slice($array, $offset, $limit) );
        
        exit;
        */
        
        $args = func_get_args();
        $total = count($args);
        
        if( $total < 1 )
            Library_error::_404();
        
        if( ! $this->site_info = $this->model_site_info->data() )
            Library_error::_404();
        
        // Jika format urlnya lengkap dengan tahun, bulan, tanggal dan name, maka ini adalah
        // detail thread.
        if( isset($args[2], $args[1]) && ( is_numeric($args[1]) && is_numeric($args[2]) && is_numeric($args[0]) ) ){
            $this->thread($args);
            return;
        }
        
        Library_error::_404();
    }
    
    /**
     * Penggunaan databasenya masih perlu di tuning 021-41442217
     */
    private function thread( $args ){
        
        $this->formatting   = new Library_formatting;
        $this->posts_lib    = new Library_posts;
        $this->model_replies= new Site_model_replies;
        
        $name               = urlencode(urldecode(strtolower($args[3])));
        $date               = $this->formatting->date2mysql($args[1], $args[2], $args[0]);
        $views['thread']    = $this->model_threads->find_one(
                                            array(
                                                'site_id' => (int) $this->site_info->site_id,
                                                "DATE_FORMAT( create_date, '%Y-%m-%d' )" => $date,
                                                'name' => $name
                                            )
                                        );
        
        if( ! $views['thread'] )
            Library_error::_404();
        
        $forum = $this->model_forums->find_one(
                                            array(
                                                'site_id' => (int) $this->site_info->site_id,
                                                'forum_id' => $views['thread']->forum_id
                                            )
                                        );
        
        if( ! $forum )
            Library_error::_404();
        
        $views['site']      = $this->site_info;
        $views['curent_url']= $curent_url = $this->library_site->thread_url( $views['thread'] );
        
        $page = 1;
        
        if( isset($args[4]) && $args[4] > 0 ){
            $views['curent_url'] .= '/'.$args[4];
            $page = $args[4];
        }
        
        $this->pagination       = new Library_pagination;
        
        $criteria = array(
                    'site_id' => $this->site_info->site_id,
                    'thread_id' => $views['thread']->thread_id,
                    'forum_id' => $views['thread']->forum_id,
                    'page' => $page,
                    'limit' => 5,
                );
        
        $views['replies']       = $this->build_replies($criteria, $views['curent_url']);//$this->model_replies->find_all($criteria);
        //print_r($views['replies']);exit;
        $this->pagination->limit= 5;
        $this->pagination->base = $curent_url.'/%#%';
	$this->pagination->total= $this->model_replies->find_total($criteria);
	$this->pagination->current= $page;
        $this->pagination->no_href = true;
        $this->pagination->prev_next = false;
        
        $views['page_links']    = $this->pagination->get_url();
        
        if( $this->request->post('submit') ){
            
            // Belum login? bawa ke halaman login dulu
            if( ! $this->session->get('user_id') )
                $this->redirect( 'signin?next=' . urlencode($views['curent_url']) );
            
            // Siapkan data yg akan disimpan ke database
            $post['author_id']  = $this->session->get('user_id');
            $post['thread_id']  = $views['thread']->thread_id;
            $post['forum_id']   = $views['thread']->forum_id;
            $post['site_id']    = $views['thread']->site_id;
            $post['date']       = date('Y-m-d H:i:s');
            $post['content']    = $this->request->post('content');
            
            // Redirect jika berhasil insert
            if( $this->model_replies->add_new($post) ){
                
                $this->pagination->total= $this->model_replies->find_total($criteria);
                $page_links             = $this->pagination->get_url();
                $end_page               = end($page_links);
                
                $this->redirect( $end_page['link'].'?replied#reply' );
            }
        }
        
        $views['thread_title']  = $this->posts_lib->filters_the_title($views['thread']->title);
        $views['thread_date']   = $this->formatting->mysql2date($views['thread']->create_date, 'd M Y H:i A');
        $views['thread_desc']   = $this->formatting->teaser(30, $views['thread']->content);
        $views['thread_content']= $this->posts_lib->the_content($views['thread']->content);
        $views['bredcump']      = $this->model_forums->bredcump($this->site_info->site_id, $forum->forum_id);
        
        $this->output('template/thread', $views);
        
    }
    
    private function build_replies($criteria, $curent_url){
        
        $replies = $this->model_replies->find_all($criteria);
        
        foreach($replies as $key => $obj){
            
            $replies[$key]->sub_replies = false;
            
            if($obj->total_replied > 0){
                
                $criteria['parent_id'] = $obj->reply_id;
                $criteria['page'] = 1;
                $criteria['limit'] = 2;
                
                $replies[$key]->sub_replies = $this->model_replies->find_all($criteria);
                
                $this->pagination->limit= 2;
                $this->pagination->base = $curent_url.'/'.$obj->reply_id.'/%#%';
                $this->pagination->total= $obj->total_replied;
                $this->pagination->current= 1;
                $this->pagination->no_href = true;
                $this->pagination->prev_next = false;
                $replies[$key]->page_links = $this->pagination->get_url();
            }
        }
        
        return $replies;
    }
}