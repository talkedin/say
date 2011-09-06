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
            <a href="<?php echo $last_url;?>">Reply</a> | <a href="report/">Report</a>
        </div>
        <a name="replies"></a>
        <?php if($replies): ?>
        <?php foreach($replies as $replies): ?>
            <div class="main_box_wraper" id="p<?php echo $replies->reply_id;?>">
                <div class="main_box_header">
                    <?php echo $this->formatting->mysql2date($replies->date, 'j M Y H:i A');?>
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
                            <a href="<?php echo $replies->post_reply;?>">Reply</a>
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
                                    <span class="author"><a href="http://talked.in/iskandar">Iskandar Soesman</a> - <?php echo $this->formatting->mysql2date($sub_replies->date, 'j M Y H:i A');?></span>
                                    <?php echo $this->posts_lib->the_content($sub_replies->content);?>
                                    
                                    <div class="mt10">
                                        <a href="<?php echo $replies->post_reply;?>">Reply</a>
                                    </div>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                        <?php endif; ?>
                        
                        <?php if($post_form == $replies->reply_id): ?>
                            <?php if( isset($reply_preview[$replies->reply_id]['content']) ): ?>
                            <div class="clearfix thread_replied_list" id="form<?php echo $replies->reply_id; ?>">
                                <div class="thread_usr_info">
                                    <a href="http://talked.in/iskandar">
                                        <img alt="Kandar" src="http://www.gravatar.com/avatar/b43a722e4a4f91ef193fbe18bb659f5d.jpg?s=48&amp;d=mm">
                                    </a>
                                </div>
                                <div class="thread_content_replied">
                                    <span class="author"><a href="http://talked.in/iskandar">Iskandar Soesman</a></span>
                                    <?php echo $reply_preview[$replies->reply_id]['content'];?>
                                    <div class="mt10">
                                        <form action="#form<?php echo $replies->reply_id; ?>" method="post">
                                            <input type="hidden" name="f" value="c" />
                                            <input type="submit" value="Post" name="submit" />
                                            <input type="submit" value="Edit" name="edit" />
                                            <input type="submit" value="Clear" name="clear" />
                                        </form>
                                    </div>
                                </div>
                            </div>
                            <?php else: ?>
                            <div class="main_box_wraper form_box" id="form<?php echo $replies->reply_id; ?>">
                                <div style="padding:5px;">
                                    <strong>Reply this post</strong>
                                </div>
                                <div class="form_wrap">
                                    <form action="#form<?php echo $replies->reply_id; ?>" method="post">
                                        <textarea name="content"><?php echo $sub_content[$replies->reply_id];?></textarea>
                                        <br /><br />
                                        <input type="hidden" name="f" value="c" />
                                        <input type="submit" value="submit" name="submit" />
                                        <input type="submit" value="Preview" name="preview" />
                                        <input type="submit" value="Clear" name="clear" />
                                    </form>
                                </div>
                            </div>
                            <?php endif; ?>
                        <?php endif; ?>
                        
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
        
        <?php if($is_editor):?>
        <?php if($reply_preview['main_form']['content']): ?>
        <div id="reply" class="main_box_wraper" style="margin-top: 30px;">
            <div class="main_box_header">
                <strong>Post Preview</strong>
            </div>
            <div class="clearfix main_box_lists">
                <div class="thread_usr_info">
                    <a href="http://talked.in/iskandar">
                        <img alt="Kandar" src="http://www.gravatar.com/avatar/b43a722e4a4f91ef193fbe18bb659f5d.jpg?s=48&amp;d=mm">
                    </a>
                </div>
                <div class="thread_content">
                    <span class="author"><a href="http://talked.in/iskandar">Iskandar Soesman</a></span>
                    <?php echo $reply_preview['main_form']['content'];?>
                    <div class="mt10">
                        <form action="#reply" method="post">
                            <input type="hidden" name="f" value="p" />
                            <input type="submit" value="Post" name="submit" />
                            <input type="submit" value="Edit" name="edit" />
                            <input type="submit" value="Clear" name="clear" />
                        </form>
                    </div>
                </div>
            </div>
        </div>
        <?php else: ?>
        <div class="main_box_wraper form_box" id="reply">
            <div style="padding:5px;">
                <strong>Reply this thread</strong>
            </div>
            <div class="form_wrap">
                <form action="#reply" method="post">
                    <textarea name="content"><?php echo $post_content['main_form']['content'];?></textarea>
                    <br /><br />
                    <input type="hidden" name="f" value="p" />
                    <input type="submit" value="Post" name="submit" />
                    <input type="submit" value="Preview" name="preview" />
                    <input type="submit" value="Clear" name="clear" />
                </form>
            </div>
        </div>
        <?php endif; ?>
        <?php endif; ?>
    </div>

<?php $this->output('template/footer'); ?>