<?php $this->output('dashboard/header');?>
<div class="wrap_content">
    <?php $this->output('dashboard/side_menu');?>
    <div class="content">
        <div style="margin-left:20px;">
            <h2>Sites</h2>
            <?php foreach($sites as $sites): ?>
                <p><a href="http://<?php echo $sites->name;?>.talked.in/panel"><?php echo $sites->name;?></a></p>
            <?php endforeach; ?>
        </div>
    </div>
</div>
</body>
</html>