<?php $this->output('template/header'); ?>
    
    <div class="wrap_content">
        
        <div class="main_box_wraper">
            <div class="main_box_header">
                <a href="<?php echo $this->library_site->location();?>">Forums</a>
                <?php $name = 'forum/';?>
                <?php foreach($bredcump as $key => $title): ?>
                    <?php $name .= $key.'/'; ?>
                    > <a href="<?php echo $this->library_site->location($name);?>"><?php echo $title;?></a>
                <?php endforeach; ?>
            </div>
            <?php foreach($sub_forums as $sub_forum): ?>
            <div class="main_box_lists">
                <h4><a href="<?php echo $this->library_site->location('forum/'.$forum->name.'/'.$sub_forum->name);?>"><?php echo $sub_forum->title;?></a></h4>
                <p>
                bagaimana kalo untuk pada bagian2 driver dibuat interfacenya.. biar ada panduan penamaan dan konsistensi driver lebih baik
                </p>
                <span>33,735 topics and 168,174 posts</span>
            </div>
            <?php endforeach; ?>
        </div>
        
    </div>
    
<?php $this->output('template/footer'); ?>