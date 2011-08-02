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
        <div class="container">
            <h1><?php echo $site->title;?></h1>
            <div class="user_info">
                <?php if($this->session->get('user_id') > 0 ): ?>
                <strong><?php echo $this->session->get('username');?></strong> - <a href="<?php echo $this->location('signout?next='.$this->curent_location);?>">Sign Out</a>
                <?php else:?>
                <a href="<?php echo $this->location('signin?next='.$this->curent_location);?>">Sign In</a>
                <?php endif;?>
            </div>
        </div>
    </div>
    <div class="nav_header">
        
    </div>
    <div class="wrap">
        
    </div>
</body>
</html>