<?php include VIEWPATH."/frame/partialHead.php"; ?>

<div data-role="page">
    <div data-role="header"><h1>管理控制台</h1></div>
    <div data-role="main" class="ui-content">
        <form data-ajax="false" action="" method="post">
            <div class="ui-field-contain">
                <fieldset><label for="userName">用户名</label><input type="text" name="userName" id="userName" /></fieldset>
                <fieldset><label for="userPassword">密码</label><input type="password" name="userPassword" id="userPassword" /></fieldset>
                <input name="loginSubmit" type="submit" value="登录" />
            </div>
        </form>
    </div>
    <div data-role="footer"><h1>神来神往</h1></div>
</div>

<?php include VIEWPATH."/frame/partialFoot.php"; ?>