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
                
                <div class="fRight"><a href="<?php echo $this->library_site->curent_location(false);?>/create"><strong>Create New Thread</strong></a></div>
            </div>
            <div class="main_box_lists">
            <?php if($threads): ?>
            <?php foreach($threads as $thread): ?>
                <h4><a href="<?php echo $this->library_site->thread_url($thread);?>"><?php echo $thread->title;?></a></h4>
                <p>
                <?php echo $this->formatting->teaser(30, $thread->content);?> ...
                </p>
                <span>33,735 topics and 168,174 posts</span>
            <?php endforeach; ?>
            <?php else: ?>
            <p>There are no thread yet! Why don't you <a href="<?php echo $this->library_site->curent_location(false);?>/create">Create</a> a new one?</p>
            <?php endif; ?>
            </div>
        </div>
        
    </div>
    
<?php $this->output('template/footer'); ?>