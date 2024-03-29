<?php $this->output('template/header'); ?>
    
    <div class="wrap_content">
        <?php if($forums): $sub_forums = $forums; ?>
            <?php foreach($forums as $forum): ?>
                <?php if( $forum->parent_id == 0 ): ?>
                    <div class="main_box_wraper">
                        <div class="main_box_header">
                            <a href="<?php echo $this->library_site->location('forum/'.$forum->name);?>"><?php echo $forum->title;?></a>
                        </div>
                        <?php if($forum->is_parent == 1):?>
                            <?php foreach($sub_forums as $sub_forum): ?>
                                <?php if($sub_forum->parent_id == $forum->forum_id): ?>
                                <div class="main_box_lists">
                                    <h4><a href="<?php echo $this->library_site->location('forum/'.$forum->name.'/'.$sub_forum->name);?>"><?php echo $sub_forum->title;?></a></h4>
                                    <p>
                                    <?php echo $sub_forum->description;?>
                                    </p>
                                    <span>33,735 topics and 168,174 posts</span>
                                </div>
                                <?php endif; ?>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <div class="main_box_lists">
                                <p>
                                <?php echo $this->formatting->teaser(50, $forum->description);?>
                                </p>
                                <span>33,735 topics and 168,174 posts</span>
                            </div>
                        <?php endif; ?>
                        
                    </div>
                <?php endif; ?>
            <?php endforeach;?>
        <?php else: ?>
            There are no forum yet at this moment.
        <?php endif; ?>
        
    </div>
    
<?php $this->output('template/footer'); ?>