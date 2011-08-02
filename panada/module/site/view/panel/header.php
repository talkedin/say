<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN"
    "http://www.w3.org/TR/html4/strict.dtd"
    >
<html lang="en">
<head>
<title>talked.in! - <?php echo isset($page_title) ? $page_title:'Home';?></title>
<link rel="stylesheet" href="<?php echo $this->location('statics/css/dashboard.css');?>" type="text/css" media="screen" />
</head>
<body>
<div class="header">
    <div class="wrap_menu">
        <div class="float_l">
            <span>Home</span>
        </div>
        <div class="float_r">
            <span><strong><?php echo $this->session->get('username');?></strong> | <a href="<?php echo $this->location('signout?next='.$this->site_libs->location());?>">Sign Out</a></span>
        </div>
    </div>
</div>