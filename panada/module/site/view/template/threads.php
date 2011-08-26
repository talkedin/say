<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN"
    "http://www.w3.org/TR/html4/strict.dtd"
    >
<html lang="en">
<head>
<title><?php echo $site->title;?></title>
<link rel="stylesheet" href="<?php echo $this->location('statics/forum/css/forum.css');?>" type="text/css" media="screen" />
</head>
<body>
    
    <div class="header">
        <div class="wrap_menu">
            <div class="float_l">
                <span><?php echo $site->title;?></span>
            </div>
            <div class="float_r">
                <?php if($this->session->get('user_id') > 0 ): ?>
                <strong><?php echo $this->session->get('username');?></strong> - <a href="<?php echo $this->location('signout?next='.$this->curent_location);?>">Sign Out</a>
                <?php else:?>
                <a href="<?php echo $this->location('signin?next='.$this->curent_location);?>">Sign In</a>
                <?php endif;?>
            </div>
        </div>
    </div>
    
    <div class="wrap_content">
        
        <div class="main_box_wraper">
            <div class="main_box_header">
                <a href="<?php echo $this->library_site->location();?>">Forums</a>
                <?php $name = '';?>
                <?php foreach($bredcump as $key => $title): ?>
                    <?php $name .= $key.'/'; ?>
                    > <a href="<?php echo $this->library_site->location($name);?>"><?php echo $title;?></a>
                <?php endforeach; ?>
            </div>
            <div class="main_box_lists">
            <?php if($threads): ?>
            <?php foreach($threads as $thread): ?>
                <h4><a href="<?php echo $this->library_site->thread_url($thread);?>"><?php echo $thread->title;?></a></h4>
                <p>
                bagaimana kalo untuk pada bagian2 driver dibuat interfacenya.. biar ada panduan penamaan dan konsistensi driver lebih baik
                </p>
                <span>33,735 topics and 168,174 posts</span>
            <?php endforeach; ?>
            <?php else: ?>
            <p>There are no thread yet! Why don't you <a href="#">Create</a> a new one?</p>
            <?php endif; ?>
            </div>
        </div>
        
    </div>
    
</body>
</html>