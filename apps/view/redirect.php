<html lang="en">
<head>
<title>Redirecting</title>
<?php if($is_top):?>
<meta http-equiv="refresh" content="0; url=&#39;<?php echo $location;?>&#39;">
<script type="text/javascript">
top.location.href= '<?php echo $location;?>';
</script>
<?php endif; ?>
</head>
<body>
<?php if( $signout_other ): ?>
<?php foreach($signout_other as $site):?>
    <img src="http://<?php echo $site->name;?>/cda/lso" width="0" height="0" style="display:none" alt="" />
<?php endforeach;?>
<?php endif; ?>
<noscript>
<meta http-equiv="refresh" content="0; url=&#39;<?php echo $location;?>&#39;">
</noscript>
<script type="text/javascript">
top.location.href= '<?php echo $location;?>';
</script>
</body>
</html>