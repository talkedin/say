<?php defined('THISPATH') or die('Can\'t access directly!');

class Site_controller_alias extends Panada_module {
    
    private $site_info;
    private $url_args = array();
    
    public function __construct(){
        
        parent::__construct();
        
        $this->session          = new Library_session;
        $this->request          = new Library_request;
        $this->cache            = new Library_cache('memcached');
        $this->library_site     = new Site_library_site;
        $this->model_forums     = new Site_model_forums;
        $this->model_threads    = new Site_model_threads;
        $this->model_site_info  = new Site_model_site_info;
        $this->users            = new Model_users;
    }
    
    public function index(){
        
        $this->url_args = func_get_args();
        $total = count($this->url_args);
        
        if( $total < 1 )
            Library_error::_404();
        
        if( ! $this->site_info = $this->model_site_info->data() )
            Library_error::_404();
        
        // Jika format urlnya lengkap dengan tahun, bulan, tanggal dan name, maka ini adalah
        // detail thread.
        if( isset($this->url_args[2], $this->url_args[1]) && ( is_numeric($this->url_args[1]) && is_numeric($this->url_args[2]) && is_numeric($this->url_args[0]) ) ){
            $this->thread();
            return;
        }
        
        Library_error::_404();
    }
    
    /**
     * Penggunaan databasenya masih perlu di tuning 021-41442217
     */
    private function thread(){
        
        // Load class-class yg dibutuhkan.
        $this->formatting   = new Library_formatting;
        $this->posts_lib    = new Library_posts;
        $this->write_lib    = new Library_write;
        $this->model_replies= new Site_model_replies;
        $this->pagination   = new Library_pagination;
        
        
        // Dapatkan nilai parameter permalink url friendly dan kemudian diolah.
        $name               = urlencode(urldecode(strtolower($this->url_args[3])));
        $date               = $this->formatting->date2mysql($this->url_args[1], $this->url_args[2], $this->url_args[0]);
        
        
        // Setelah nilai permalink didapat, gunakan sebagai kriteria untuk mendapatkan thread.
        $views['thread']    = $this->model_threads->find_one(
                                            array(
                                                'site_id' => (int) $this->site_info->site_id,
                                                "DATE_FORMAT( create_date, '%Y-%m-%d' )" => $date,
                                                'name' => $name
                                            )
                                        );
        
        // Jika thread tidak ditemukan, stop proses dan beri 404.
        if( ! $views['thread'] )
            Library_error::_404();
        
        
        // Dapatkan data forum yg menjadi parent dari thread ini.
        $forum = $this->model_forums->find_one(
                                            array(
                                                'site_id' => (int) $this->site_info->site_id,
                                                'forum_id' => $views['thread']->forum_id
                                            )
                                        );
        
        // Jika forum tidak ada, stop proses dan beri 404
        // OPTIMASI: Pada DB SQL bisa dilakukan query thread dan forum sekaligus.
        if( ! $forum )
            Library_error::_404();
        
        $views['site']      = $this->site_info;
        $views['curent_url']= $curent_url = $this->library_site->thread_url( $views['thread'] );
        
        
        // Paging default, page 1
        $page = 1;
        
        
        // Jika ada parameter tambahan pada url, maka tambahkan kedalam var curent url.
        if( isset($this->url_args[4]) && $this->url_args[4] > 0 ){
            $views['curent_url'] .= '/'.$this->url_args[4];
            $page = $this->url_args[4];
        }
        
        
        // Buat kriteria untuk mendapatkan data reply thread.
        $criteria = array(
                    'site_id'   => $this->site_info->site_id,
                    'thread_id' => $views['thread']->thread_id,
                    'forum_id'  => $views['thread']->forum_id,
                    'page'      => $page,
                    'limit'     => 5,
                );
        
        
        // Passing informasi yg dibutuhkan ke method build_replies() untuk menyusun ulang urutan reply dan subreply.
        //$this->model_replies->find_all($criteria);
        $views['replies']           = $this->build_replies($criteria, $curent_url);
        //print_r($views['replies']);exit;
        
        // Tentukan paramter2 yg dibutuhkan untuk paging.
        $this->pagination->limit    = $criteria['limit'];
        $this->pagination->base     = $curent_url.'/%#%#replies';
	$this->pagination->total    = $views['thread']->total_replied_parent;//$this->model_replies->find_total($criteria);
	$this->pagination->current  = $page;
        $this->pagination->no_href  = true;
        $this->pagination->prev_next= false;
        $views['page_links']        = $this->pagination->get_url();
        
        $last_url                   = $this->pagination->last_url(true);
        $views['last_url']          = $this->reparse_url($last_url['str_url'], array('fragment' => 'reply') );
        
        
        // Tampilkan form submit hanya di paging terakhir.
        $views['is_editor'] = false;
        if($page == $last_url['integer_page'])
            $views['is_editor'] = true;
        
        
        // Flag untuk menentukan mode preview reply.
        $views['reply_preview']['main_form']['content'] = false;
        
        
        // Cache key untuk penyimpanan sementara reply yg akan dipreview atau, pada user yg belum login.
        $cache_namespace = 'reply_preview_' . session_id();
        $cache_main_key = 'main_form';
        
        
        // Submit reply untuk preview.
        if( $this->request->post('preview') ){
            
            $content = $content_ori = $this->request->post('content');
            
            // Bersihkan dari tag2 yg tidak diizinkan.
            $content = $this->write_lib->sanitize_post_content( $content );
            
            // Rapihkan tag2 (jika ada) dan format menjadi siap ditampilkan ke HTML
            $content = $this->posts_lib->the_content( $content );
            
            // Jika variable f diset, maka POST request berasal dari form utama.
            if( $this->request->post('f') == 'p' ){
                $views['reply_preview']['main_form']['content'] = $content;
                $this->cache->set_value( $cache_main_key, $content_ori, 300, $cache_namespace );
            }
            
            // Sebaliknya, request datang dari sub form.
            else{
                $views['reply_preview'][$this->url_args[6]]['content'] = $content;
                $this->cache->set_value( 'sub_form_'.$this->url_args[6], $content_ori, 300, $cache_namespace );
            }
            
        }
        
        
        // Isikan nilai post_content jika ada data dari memcache.
        $views['post_content']['main_form']['content'] = $this->cache->get_value( $cache_main_key, $cache_namespace );
        
        
        // Clear data yg ada di memcache
        if( $this->request->post('clear') ){
            $this->cache->delete_value($cache_namespace);
            
            // Cek apakah form submitnya berasal dari reply form
            if( $this->request->post('f') == 'c' )
                $views['curent_url'] = $this->reparse_url($views['curent_url'], array('fragment' => 'p'.$this->url_args[6]) );
            
            $this->redirect( $views['curent_url'] );
        }
        
        // Submit reply ke database
        if( $this->request->post('submit') ){
            
            
            $post['parent_id']  = 0;
            
            // Cek apakah form submitnya berasal dari main form atau reply form
            if( $this->request->post('f') == 'c' ){
                $post['parent_id']  = $this->url_args[6];
                $cache_main_key     = 'sub_form_'.$this->url_args[6];
                $views['curent_url']= $this->library_site->curent_location().'#form'.$post['parent_id'];
            }
            
            // Belum login? bawa ke halaman login dulu
            if( ! $this->session->get('user_id') ){
                
                // Simpan dulu content aslinya ke memcache selama 300 second
                if( $post['content'] = $this->request->post('content') )
                    $this->cache->set_value( $cache_main_key, $post['content'], 300, $cache_namespace );
                
                $this->redirect( 'signin?next=' . urlencode($views['curent_url']) );
            }
            
            
            // Siapkan data yg akan disimpan ke database
            $post['author_id']  = $this->session->get('user_id');
            $post['thread_id']  = $views['thread']->thread_id;
            $post['forum_id']   = $views['thread']->forum_id;
            $post['site_id']    = $views['thread']->site_id;
            $post['date']       = date('Y-m-d H:i:s');
            
            // Jika data tidak ada dari submit post, maka ambil dari memcache
            if( ! $post['content'] = $this->request->post('content') )
                $post['content']  = $this->cache->get_value($cache_main_key, $cache_namespace);
            
            
            // Bersihkan dari tag2 yg tidak diizinkan.
            $post['content']    = $this->write_lib->sanitize_post_content( $post['content'] );
            
            
            // Redirect jika berhasil insert.
            // NOTICE: Yang belum adalah halaman/notifikasi jika gagal insert.
            if( $this->model_replies->add_new($post) ){
                
                if( $post['parent_id'] > 0 ){
                    
                    $total = $this->model_replies->find_total( array('parent_id' => $post['parent_id'], 'site_id' => $post['site_id']) );
                    
                    $max_page = ceil($total/1);
                    
                    $this->model_replies->update( array('total_replied' => $total), array('reply_id' => $post['parent_id']) );
                    
                    $views['curent_url'] = $curent_url.'/'.$this->url_args[4].'/reply/'.$post['parent_id'].'/'.$max_page.'?replied#p'.$post['parent_id'];
                }
                else{
                    
                    $this->pagination->total = $this->model_replies->find_total($criteria);
                    
                    // Update jumlah total reply
                    $this->model_threads->update( array('total_replied_parent' => $this->pagination->total), array('thread_id' => $post['thread_id']) );
                    
                    // Struktur ulang url sebelum diredirect.
                    $views['curent_url'] = $this->reparse_url( $this->pagination->last_url(), array('query' => 'replied', 'fragment' => 'reply') );
                }
                
                // hapus preview yg ada di memcache
                $this->cache->delete_value($cache_namespace);
                
                $this->redirect( $views['curent_url'] );
            }
            
        }
        
        
        // Siapkan data2 yg akan ditampilkan ke dalam view.
        $views['thread_title']  = $this->posts_lib->filters_the_title($views['thread']->title);
        $views['thread_date']   = $this->formatting->mysql2date($views['thread']->create_date, 'j M Y H:i A');
        $views['thread_desc']   = $this->formatting->teaser(30, $views['thread']->content);
        $views['thread_content']= $this->posts_lib->the_content($views['thread']->content);
        $views['bredcump']      = $this->model_forums->bredcump($this->site_info->site_id, $forum->forum_id);
        
        $views['post_form']     = 0;
        if( isset($this->url_args[8]) && $this->url_args[8] == 'post' ){
            
            $views['post_form'] = $this->url_args[6];
            
            // Isikan nilai post_content jika ada data dari memcache.
            $views['sub_content'][$this->url_args[6]] = $this->cache->get_value( 'sub_form_'.$this->url_args[6], $cache_namespace );
        }
        
        $this->output('template/thread', $views);
    }
    
    /**
     * Struktur ulang url.
     *
     * @param string
     * @param array
     * @return string | array
     */
    private function reparse_url( $url_str, $component = array() ){
        
        $url_str = parse_url($url_str);
        $url_str = array_merge($url_str, $component);
        
        $url = $url_str['scheme'] .'://' . $url_str['host'];
        
        if( isset($url_str['path']) )
            $url .= $url_str['path'];
        
        if( isset($url_str['query']) )
            $url .= '?'.$url_str['query'];
        
        if( isset($url_str['fragment']) )
            $url .= '#'.$url_str['fragment'];
        
        return $url;
        
    }
    
    /**
     * Menyusun ulang struktur reply dan subreply.
     */
    private function build_replies($criteria, $curent_url){
        
        if( ! isset($this->url_args[4]) )
            $this->url_args[4] = 1;
        
        
        if( ! $replies = $this->model_replies->find_all($criteria) )
            return false;
        
        // Tambahkan setiap object dengan reply dan paginnya, jika ada
        foreach($replies as $key => $obj){
            
            $replies[$key]->sub_replies = false;
            $replies[$key]->page_links  = false;
            
            $replies[$key]->author          = $this->users->find_one( array('user_id' => $obj->author_id) );
            $replies[$key]->author->avatar  = $this->users->find_gravatar( $replies[$key]->author->email );
            
            // hilangkan object2 yg tidak perlu.
            unset(
                $replies[$key]->author->user_id,
                $replies[$key]->author->password,
                $replies[$key]->author->salt
            );
            
            $replies[$key]->post_reply  = $curent_url.'/'.$this->url_args[4].'/reply/'.$obj->reply_id.'/1/post#form'.$obj->reply_id;
            
            if($obj->total_replied > 0){
                
                $sub = $this->build_sub_replies($obj, $criteria, $curent_url);
                $replies[$key]->sub_replies = $sub->sub_replies;
                $replies[$key]->page_links  = $sub->page_links;
                
                if( isset($sub->post_reply) )
                    $replies[$key]->post_reply  = $sub->post_reply;
            }
        }
        
        return $replies;
    }
    
    /**
     * OPTIMASI: bagian ini sepertinya  bisa dicache
     */
    private function build_sub_replies($obj, $criteria, $curent_url){
        
        $return = new stdClass;
        
        // Pastikan key-key yang dibutuhkan tersedia
        if( ! isset($this->url_args[5]) ){
            $this->url_args[5] = 'reply';
            $this->url_args[6] = 1;
            $this->url_args[7] = 1;
        }
        
        $criteria['parent_id']      = $obj->reply_id;
        
        // Berapa jumlah post yg akan ditampilkan dalam satu subreply
        $criteria['limit']          = 3;
        $criteria['page']           = 1;
        $this->pagination->limit    = $criteria['limit'];
        $this->pagination->base     = $curent_url.'/'.$this->url_args[4].'/reply/'.$obj->reply_id.'/%#%#p'.$obj->reply_id;
        $this->pagination->current  = $criteria['page'];
        
        if( $this->url_args[7] > 1 && $obj->reply_id == $this->url_args[6] ){
            $criteria['page']           = $this->url_args[7];
            $this->pagination->base     = $curent_url.'/'.$this->url_args[4].'/reply/'.$obj->reply_id.'/%#%#p'.$obj->reply_id;
            $this->pagination->current  = $criteria['page'];
        }
        
        $return->sub_replies        = $this->model_replies->find_all($criteria);
        
        // Ambil data penulis reply ini.
        foreach($return->sub_replies as $key => $val){
            $return->sub_replies[$key]->author          = $this->users->find_one( array('user_id' => $val->author_id) );
            $return->sub_replies[$key]->author->avatar  = $this->users->find_gravatar( $return->sub_replies[$key]->author->email );
            
            // hilangkan object2 yg tidak perlu.
            unset(
                $return->sub_replies[$key]->author->user_id,
                $return->sub_replies[$key]->author->password,
                $return->sub_replies[$key]->author->salt
            );
        }
        
        $this->pagination->total    = $obj->total_replied;
        
        $this->pagination->no_href  = true;
        $this->pagination->prev_next= false;
        
        if( $return->page_links = $this->pagination->get_url() ){
            
            $post_reply         = $this->pagination->last_url(true);
            $return->post_reply = $curent_url.'/'.$this->url_args[4].'/reply/'.$obj->reply_id.'/'.$post_reply['integer_page'].'/post#form'.$obj->reply_id;
            
        }
        
        return $return;
    }
}