<?php include VIEWPATH."/frame/partialHead.php"; ?>

    <div data-role="page" id="page_1">
        <div data-role="header"><h1>欢迎您</h1></div>
        <div data-role="main" class="ui-content">
            <p><?php echo $this->session->fullName; ?></p>
            <p><a href="<?php echo site_url('user/info'); ?>">查看/修改资料</a></p>
            <p><a href="<?php echo site_url('user/freights'); ?>">我的货源</a></p>
            <p><a data-ajax="false" href="<?php echo site_url('freight/publish'); ?>">我要发货</a></p>
            <p><a href="<?php echo site_url('user/hopes'); ?>">我想接单</a></p>
            <p><a href="<?php echo site_url('user/orders'); ?>">我的运单</a></p>
            <p><a data-ajax="false" href="<?php echo site_url('freight/lists'); ?>">查找货源</a></p>
        </div>
        <div data-role="footer"><h1>找车配货就用神来神往</h1></div>
    </div>

<?php include VIEWPATH."/frame/partialFoot.php"; ?>