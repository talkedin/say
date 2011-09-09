<?php $this->output('template/header'); ?>
<div class="wrap_content">
    <?php if($errors):?>
    <div class="error">
        <ul>
        <?php foreach($errors as $errors): ?>
        <li><?php echo $errors;?></li>
        <?php endforeach; ?>
        </ul>
    </div>
    <?php endif; ?>
    <?php if($is_editor): ?>
    <?php if($title == 'Untitled'):?>
        <div class="notice">Please enter your thread title.</div>
    <?php endif; ?>
    <div class="main_box_wraper">
        <div class="main_box_header">
            <a href="<?php echo $this->library_site->location();?>">Forums</a>
            <?php $name = 'forum/';?>
            <?php foreach($bredcump as $key => $value): ?>
                <?php $name .= $key.'/'; ?>
                > <a href="<?php echo $this->library_site->location($name);?>"><?php echo $value;?></a>
            <?php endforeach; ?>
            > Thread Preview
        </div>
            <div class="clearfix main_box_lists">
                <div class="thread_usr_info">
                    <a href="http://talked.in/<?php echo $this->signed_in->username;?>">
                        <img alt="<?php echo $this->signed_in->username;?>" src="<?php echo $this->signed_in->avatar;?>">
                    </a>
                </div>
                <div class="thread_content">
                    <h2 style="margin: 0pt;"><?php echo $title;?></h2>
                    <span class="author"><a href="http://talked.in/<?php echo $this->signed_in->username;?>"><?php echo $this->signed_in->username;?></a></span>
                    <?php echo $post;?>
                    <form method="post" action="">
                    <input type="submit" name="submit" value="Post">
                    <input type="submit" name="edit" value="Edit">
                    <input type="submit" name="clear" value="Clear">
                    </form>
                </div>
            </div>
        </div>
    <?php else: ?>
    <div class="main_box_wraper form_box">
        <div class="main_box_header">
            <a href="<?php echo $this->library_site->location();?>">Forums</a>
            <?php $name = 'forum/';?>
            <?php foreach($bredcump as $key => $value): ?>
                <?php $name .= $key.'/'; ?>
                > <a href="<?php echo $this->library_site->location($name);?>"><?php echo $value;?></a>
            <?php endforeach; ?>
            > New Thread
        </div>
        <div class="form_wrap">
            <form method="post" action="">
                <label for="title">Title:</label>
                <input type="text" name="title" id="title" value="<?php echo $title;?>" />
                <label for="post">Post:</label>
                <textarea name="post" id="post"><?php echo $post;?></textarea>
                <br><br>
                <input type="submit" name="submit" value="Post">
                <input type="submit" name="preview" value="Preview">
                <input type="submit" name="clear" value="Clear">
            </form>
        </div>
    </div>
    <?php endif; ?>
</div>
<?php $this->output('template/footer'); ?>