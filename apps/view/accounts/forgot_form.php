<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN"
    "http://www.w3.org/TR/html4/strict.dtd"
    >
<html lang="en">
<head>
    <title>talked.in! - Sign In</title>
</head>
<body>
    <?php if($is_error): ?>
        <p><?php echo $is_error;?></p>
    <?php endif; ?>
    <form action="" method="post">
        Email or Username:<br />
        <input type="text" id="uname" name="uname" />
        <input type="submit" value="Submit" name="submit" />
    </form>
    <script type="text/javascript">
        document.getElementById('uname').focus();
    </script>
</body>
</html>