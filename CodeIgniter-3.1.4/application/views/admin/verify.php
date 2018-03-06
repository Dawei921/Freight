<?php include VIEWPATH."/frame/partialHead.php"; ?>

<!-- 第一页 -->
<div data-role="page" id="page_1">
    <div data-role="header"><h1>用户信息</h1></div>
    <div data-role="main" class="ui-content">
        <?php if(isset($user)): ?>
            <p>用户名称 <?php echo $user->fullName; ?></p>
            <p>联系方式 <?php echo $user->mobileNumber; ?></p>
            <p>证件类型 <?php echo $user->credentialsType; ?></p>
            <p>证件编号 <?php echo $user->credentialsNumber; ?></p>
            <p>证件照片
                <?php $imgArray=explode(',',$user->credentialsPhotos); ?>
                <?php foreach ($imgArray as $img): ?>
                    <img src="<?php echo base_url($img); ?>" />
                <?php endforeach; ?>
            </p>
            <!-- 如果未审核则显示审核操作，否则不显示 -->
            <?php if($user->recordStatus==0): ?>
                <form name="form1">
                    <fieldset><label for="reason">不通过请填写原因</label><textarea name="reason" id="reason"></textarea></fieldset>
                    <fieldset>
                        <button type="button" value="不通过" onclick="verify(this.value,form1)">不通过</button>
                        <button type="button" value="通过" onclick="verify(this.value,form1)">通过</button>
                    </fieldset>
                    <input type="hidden" name="userID" value="<?php echo $user->userID; ?>">
                    <input type="hidden" name="verifySubmit" />
                </form>
            <?php endif; ?>
        <?php endif; ?>
    </div>
    <div data-role="footer" class="textCenter"><a data-role="button" onclick="history.back()">返回</a></div>
</div>
<script>

    //审核提交
    function verify(operate,formObj){
        //如果不通过必须填写原因
        var reasonObj=document.getElementById('reason');
        if(operate=='不通过' && reasonObj.value.length<1){
            alert('请填写原因。');
            reasonObj.focus();
            return;
        }
        //设置模拟提交按钮值并异步ajax提交
        formObj.verifySubmit.value=operate;
        var xmlhttp=new XMLHttpRequest();
        xmlhttp.onreadystatechange=function()
        {
            if (xmlhttp.readyState==4 && xmlhttp.status==200)
            {
                //如果成功刷新页面显示
                if(xmlhttp.responseText=='')
                {
                    location.reload();
                }//否则提示失败消息
                else{
                    alert(xmlhttp.responseText);
                }
            }
        };
        xmlhttp.open("post","<?php echo site_url('admin/verify'); ?>",true);
        xmlhttp.send(new FormData(formObj));
    }

</script>
<?php include VIEWPATH."/frame/partialFoot.php"; ?>