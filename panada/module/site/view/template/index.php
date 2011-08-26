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
                                <?php echo $forum->description;?>
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
    
</body>
</html>