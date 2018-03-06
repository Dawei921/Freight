<?php include VIEWPATH."/frame/partialHead.php"; ?>

<!-- 第一页 -->
<div data-role="page" id="page_1">
    <div data-role="header"><h1>注册</h1></div>
    <div data-role="main" class="ui-content">
        <form id="form1" data-ajax="false" action="" method="post" enctype="multipart/form-data">
            <div class="ui-field-contain">
                <fieldset><label for="credentialType">证件类型</label>
                    <select name="credentialType" id="credentialType" onchange="switchDisplay(this.value)">
                        <?php foreach ($credentialTypeArray as $credentialType): ?>
                            <option value="<?php echo $credentialType->aID; ?>"><?php echo $credentialType->typeName; ?></option>
                        <?php endforeach; ?>
                    </select>
                </fieldset>
                <fieldset><label for="credentialNumber" id="credentialNumber_title">身份证号</label><input type="text" name="credentialNumber" id="credentialNumber" /></fieldset>
                <fieldset><label for="fullName" id="fullName_title">真实姓名</label><input type="text" name="fullName" id="fullName" /></fieldset>
                <fieldset><label for="mobileNumber" id="mobileNumber_title">手机号</label><input type="text" name="mobileNumber" id="mobileNumber" /><a href="">获取验证码</a></fieldset>
                <fieldset><label for="checkCode">输入验证码</label><input type="text" name="checkCode" id="checkCode" /></fieldset>
                <fieldset><label for="userPassword">输入密码</label><input type="password" name="userPassword" id="userPassword" /></fieldset>
                <fieldset><label for="ensurePassword">确认密码</label><input type="password" name="ensurePassword" id="ensurePassword" /></fieldset>

        <!-- 结束div用于分页 -->
            </div>
        </form>
    </div>
    <div data-role="footer" class="textCenter"><a data-role="button" href="#page_2">下一步</a></div>
</div>

<!-- 第二页 -->
<div data-role="page" id="page_2">
    <div data-role="header"><h1>注册</h1></div>
    <div data-role="main" class="ui-content">
        <fieldset><label for="photo1" id="photo1_title">请上传身份证正面照片</label><input form="form1" type="file" name="photo1" id="photo1" /></fieldset>
        <fieldset id="photo2_container"><label for="photo2">请上传身份证反面照片</label><input form="form1" type="file" name="photo2" id="photo2" /></fieldset>
        <fieldset id="photo3_container"><label for="photo3">请上传本人手持身份证照片</label><input form="form1" type="file" name="photo3" id="photo3" /></fieldset>
    </div>
    <div data-role="footer" class="textCenter"><a data-role="button" href="#page_1">返回</a><input form="form1" type="submit" name="registerSubmit" value="完成注册" /></div>
</div>

<script>

    function switchDisplay(credentialType) {
        //如果是身份证认证
        if(credentialType==1){
            $('#credentialNumber_title').text("身份证号");
            $('#fullName_title').text("真实姓名");
            $('#mobileNumber_title').text("手机号");
            $('#photo1_title').text("请上传身份证正面照片");
            $('#photo2_container').css('display','block');
            $('#photo3_container').css('display','block');
        }//否则如果是营业执照
        else if(credentialType==2){
            $('#credentialNumber_title').text("营业执照编号");
            $('#fullName_title').text("公司名称");
            $('#mobileNumber_title').text("电话");
            $('#photo1_title').text("请上传营业执照正面图片");
            //清空不需要上传的文件
            $('#photo2').val('');
            $('#photo3').val('');
            $('#photo2_container').css('display','none');
            $('#photo3_container').css('display','none');
        }
    }

    //返回时仍然显示原来选择
    window.addEventListener("load",init,false);
    function init()
    {
        switchDisplay(document.getElementById('credentialType').value);
    }
</script>

<?php include VIEWPATH."/frame/partialFoot.php"; ?>