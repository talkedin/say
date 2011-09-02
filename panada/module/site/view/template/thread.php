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
                > <?php echo $thread_title;?>
            </div>
            <div class="clearfix main_box_lists">
                <div class="thread_usr_info">
                    <a href="http://talked.in/iskandar">
                        <img src="http://www.gravatar.com/avatar/b43a722e4a4f91ef193fbe18bb659f5d.jpg?s=48&d=mm" alt="Kandar" />
                    </a>
                </div>
                <div class="thread_content">
                    <h2 style="margin:0;"><?php echo $thread_title;?></h2>
                    <span class="author"><a href="http://talked.in/iskandar">Iskandar Soesman</a> - <?php echo $thread_date;?></span>
                   
                    <?php echo $thread_content;?>
                    
                </div>
                
            </div>
        </div>
        
        <div class="thread_reply">
            <a href="#reply">Reply</a> | <a href="report/">Report</a>
        </div>
        
        <?php if($replies): ?>
        <?php foreach($replies as $replies): ?>
            <div class="main_box_wraper" id="p<?php echo $replies->reply_id;?>">
                <div class="main_box_header">
                    <?php echo $this->formatting->mysql2date($replies->date, 'd M Y H:i A');?>
                </div>
                <div class="clearfix main_box_lists">
                    <div class="thread_usr_info">
                        <a href="http://talked.in/iskandar">
                            <img src="http://www.gravatar.com/avatar/b43a722e4a4f91ef193fbe18bb659f5d.jpg?s=48&d=mm" alt="Kandar" />
                        </a>
                    </div>
                    <div class="thread_content">
                        <span class="author"><a href="http://talked.in/iskandar">Iskandar Soesman</a></span>
                        <?php echo $this->posts_lib->the_content($replies->content);?>
                        
                        <div class="mt10">
                            <a href="<?php echo $replies->post_reply;?>">Reply</a><!-- | <a href="report/">Report</a>-->
                        </div>
                        
                        <?php if($replies->sub_replies): ?>
                        <div>
                            <?php foreach($replies->sub_replies as $sub_replies): ?>
                            <div class="clearfix thread_replied_list">
                                <div class="thread_usr_info">
                                    <a href="http://talked.in/iskandar">
                                        <img src="http://www.gravatar.com/avatar/b43a722e4a4f91ef193fbe18bb659f5d.jpg?s=48&d=mm" alt="Kandar" />
                                    </a>
                                </div>
                                <div class="thread_content_replied">
                                    <span class="author"><a href="http://talked.in/iskandar">Iskandar Soesman</a> - <?php echo $this->formatting->mysql2date($sub_replies->date, 'd M Y H:i A');?></span>
                                    <?php echo $this->posts_lib->the_content($sub_replies->content);?>
                                    
                                    <div class="mt10">
                                        <a href="<?php echo $replies->post_reply;?>">Reply</a><!-- | <a href="report/">Report</a>-->
                                    </div>
                                </div>
                            </div>
                            <?php endforeach; ?>
                            <?php if($replies->page_links):?>
                            <div class="paging" style="border-top:1px solid #E5E5E5;">
                                <ul>
                                <?php foreach($replies->page_links as $paging):?>
                                    <li>
                                        <?php if( empty($paging['link']) ): ?>
                                        <a class="selected"><?php echo $paging['value'];?></a>
                                        <?php else: ?>
                                        <a href="<?php echo $paging['link'];?>"><?php echo $paging['value'];?></a>
                                        <?php endif; ?>
                                    </li>
                                <?php endforeach;?>
                                </ul>
                            </div>
                            <?php endif;?>
                            
                        </div>
                        <?php endif; ?>
                        
                        <?php if($post_form == $replies->reply_id): ?>
                        <div class="main_box_wraper form_box" id="reply<?php echo $replies->reply_id; ?>">
                            <div style="padding:5px;">
                                <strong>Reply this thread</strong>
                                <div class="fRight"><a href="#">Write</a> | <a href="#">Preview</a></div>
                            </div>
                            <div class="form_wrap">
                                <form action="" method="post">
                                    <textarea name="content"></textarea>
                                    <br /><br />
                                    <input type="submit" value="submit" name="submit" />
                                </form>
                            </div>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
                
            </div>
        <?php endforeach;?>
        <?php else: ?>
            <p>No post yet!</p>
        <?php endif; ?>
        
        <div class="paging">
            
            <?php if($page_links):?>
            <ul>
            <?php foreach($page_links as $paging):?>
                <li>
                    <?php if( empty($paging['link']) ): ?>
                    <a class="selected"><?php echo $paging['value'];?></a>
                    <?php else: ?>
                    <a href="<?php echo $paging['link'];?>"><?php echo $paging['value'];?></a>
                    <?php endif; ?>
                </li>
            <?php endforeach;?>
            </ul>
            <?php endif;?>
        </div>
       
        
        <div class="main_box_wraper form_box" id="reply">
            <div style="padding:5px;">
                <strong>Reply this thread</strong>
                <div class="fRight"><a href="#">Write</a> | <a href="#">Preview</a></div>
            </div>
            <div class="form_wrap">
                <form action="" method="post">
                    <textarea name="content"></textarea>
                    <br /><br />
                    <input type="submit" value="submit" name="submit" />
                </form>
            </div>
        </div>
        
    </div>

<?php $this->output('template/footer'); ?>