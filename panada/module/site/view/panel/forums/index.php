<?php $this->output('panel/header');?>
<div class="wrap_content">
    <?php $this->output('panel/side_menu');?>
    <div class="content">
        <div style="margin-left:20px;">
            <h2>Forums</h2>
            <span><strong>All</strong> - <a href="<?php echo $this->library_site->location('panel/forums/create');?>">Create</a></span>
        </div>
    </div>
</div>
</body>
</html>