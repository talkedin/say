<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN"
    "http://www.w3.org/TR/html4/strict.dtd"
    >
<html lang="en">
<head>
<?php if( ! $this->session->get('user_id') ):?>
<script type="text/javascript" src="http://talked.in/api/js/cda.js"></script>
<?php endif; ?>
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
                <strong><?php echo $this->signed_in->username;?></strong> - <a href="<?php echo $this->location('signout?next='.$this->library_site->location());?>">Sign Out</a>
                <?php else:?>
                <a href="<?php echo $this->location('signin?next='.$this->library_site->curent_location());?>">Sign In</a> | <a href="<?php echo $this->location('signup?next='.$this->library_site->curent_location());?>">Sign Up</a>
                <?php endif;?>
            </div>
        </div>
    </div>