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
        <input type="text" id="uname" name="uname" /><br />
        Password:<br />
        <input type="password" name="pass" /><br />
        Remember me: <input type="checkbox" name="rem" value="1" /><br />
        <input type="submit" value="Sign In" name="signin" />
    </form>
    <script type="text/javascript">
        document.getElementById('uname').focus();
    </script>
</body>
</html>