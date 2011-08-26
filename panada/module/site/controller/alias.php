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
        
        $this->sub_forum($args);
        return;
    }
    
    private function forum( $args = array() ){
        
        $name                   = end($args);
        $views['site']          = $this->site_info;
        $views['forum']         = $this->model_forums->find_one( array('site_id' => $this->site_info->site_id, 'name' => $name) );
        $views['bredcump']      = $this->model_forums->bredcump($this->site_info->site_id, $views['forum']->forum_id);
        $views['sub_forums']    = $this->model_forums->find_all( array('site_id' => $this->site_info->site_id, 'parent_id' => $views['forum']->forum_id) );
        
        $this->output('template/forum', $views);
    }
    
    /**
     * Melakukan pengecekan sebelum dikirim ke method thread
     */
    private function sub_forum( $args = array() ){
        
        $name = end($args);
        
        // Jika sudah tidak ada di table forum, beri 404
        if( ! $forum = $this->model_forums->find_one( array('site_id' => $this->site_info->site_id, 'name' => $name) ) )
            Library_error::_404();
            
        
        // Waktunya untuk menampilkan list thread jika is_parent-nya sudah 0
        if($forum->is_parent == 0){
            $this->threads($forum, $args);
            return;
        }
        
        // Sebaliknya, bawa kembali ke method forum jika is_parentnya 1
        else{
            $this->forum($args);
            return;
        }
    }
    
    /**
     * Dapatkan semua listing thread dari sebuah forum/sub-forum
     */
    private function threads( $forum, $args ){
        
        $name               = end($args);
        $views['prefix_url']= implode('/', $args);
        $views['forum']     = $forum;
        $views['site']      = $this->site_info;
        $views['threads']   = $this->model_threads->find_all( array('site_id' => $this->site_info->site_id, 'forum_id' => $forum->forum_id) );
        $views['bredcump']  = $this->model_forums->bredcump($this->site_info->site_id, $forum->forum_id);
        
        $this->output('template/threads', $views);
    }
    
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
        $views['curent_url']= $this->library_site->thread_url( $views['thread'] );
        
        if( isset($args[4]) && $args[4] > 0 )
            $views['curent_url'] .= '/'.$args[4];
        
        if( $this->request->post('submit') ){
            
            $post['author_id']  = $this->session->get('user_id');
            $post['thread_id']  = $views['thread']->thread_id;
            $post['forum_id']   = $views['thread']->forum_id;
            $post['site_id']    = $views['thread']->site_id;
            $post['date']       = date('Y-m-d H:i:s');
            $post['content']    = $this->request->post('content');
            
            if( $this->model_replies->add_new($post) ){
                $this->redirect( $views['curent_url'].'?replied#reply' );
            }
        }
        
        $views['thread_title']  = $this->posts_lib->filters_the_title($views['thread']->title);
        $views['thread_date']   = $this->formatting->mysql2date($views['thread']->create_date, 'd M Y H:i A');
        $views['thread_desc']   = $this->formatting->teaser(30, $views['thread']->content);
        $views['thread_content']= $this->posts_lib->the_content($views['thread']->content);
        $views['bredcump']      = $this->model_forums->bredcump($this->site_info->site_id, $forum->forum_id);
        
        $this->output('template/thread', $views);
        
    }
}