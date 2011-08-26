<?php $this->output('panel/header');?>
<div class="wrap_content">
    <?php $this->output('panel/side_menu');?>
    <div class="content">
        <div style="margin-left:20px;">
            <h2>Forums</h2>
            <p><a href="<?php echo $this->library_site->location('panel/forums');?>">All</a> - <strong>Create</strong></p>
            
            <form action="" method="post">
                Title:<br />
                <input type="text" name="title" /><br /><br />
                Description:<br />
                <textarea name="description"></textarea><br /><br />
                Parent: <select name="parent">
                            <option>1</option>
                        </select>
                        <br /><br />
                Visibility:<br />
                <input type="radio" name="visibility" value="1" /> Public <input type="radio" name="visibility" value="0" /> Private<br /><br />
                <input type="submit" name="submit" value="Create" />
            </form>
            
        </div>
    </div>
</div>
</body>
</html>