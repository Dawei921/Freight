<?php include VIEWPATH."/frame/partialHead.php"; ?>

<!-- 第一页 -->
<div data-role="page" id="page_1">
    <div data-role="header"><h1>管理控制台</h1></div>
    <div data-role="main" class="ui-content">
        <label for="users">注册用户列表</label>
        <ul id="users">
            <?php if(isset($users)): ?>
                <?php foreach ($users as $user): ?>
                    <li><?php echo $user->fullName.' '.$user->credentialsType.' '.$user->credentialsNumber; ?> <a href="<?php echo site_url('admin/verify/'.$user->userID); ?>" target="_blank">审核</a></li>
                <?php endforeach; ?>
            <?php endif; ?>
        </ul>
    </div>
    <div data-role="footer" class="textCenter"><a data-role="button" onclick="history.back()">返回</a></div>
</div>

<?php include VIEWPATH."/frame/partialFoot.php"; ?>