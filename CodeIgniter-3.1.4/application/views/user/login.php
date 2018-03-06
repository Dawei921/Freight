<?php include VIEWPATH."/frame/partialHead.php"; ?>

<div data-role="page">
    <div data-role="header"><h1>登录</h1></div>
    <div data-role="main" class="ui-content">
        <form data-ajax="false" action="" method="post">
            <div class="ui-field-contain">
                <fieldset><label for="credentialNumber">身份证号/营业执照编号</label><input type="text" name="credentialNumber" id="credentialNumber" /></fieldset>
                <fieldset><label for="userPassword">密码</label><input type="password" name="userPassword" id="userPassword" /></fieldset>
                <input name="loginSubmit" type="submit" value="登录" />
            </div>
        </form>
        <a href="">忘记密码</a>
    </div>
    <div data-role="footer"><h1>欢迎使用神来神往</h1></div>
</div>

<?php include VIEWPATH."/frame/partialFoot.php"; ?>