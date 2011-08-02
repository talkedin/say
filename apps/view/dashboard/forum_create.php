<?php $this->output('dashboard/header');?>
<div class="wrap_content">
    <?php $this->output('dashboard/side_menu');?>
    <div class="content">
        <div style="margin-left:20px;">
        <form action="" method="post">
            Forum Name:<br />
            <input type="text" name="forum_name" /><br /><br />
            Description:<br />
            <textarea name="description"></textarea><br /><br />
            Visibility:<br />
            <input type="radio" name="visibility" value="1" /> Public <input type="radio" name="visibility" value="0" /> Private<br /><br />
            <input type="submit" name="submit" value="Create" />
        </form>
        </div>
    </div>
</div>
</body>
</html>