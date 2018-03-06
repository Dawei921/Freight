<?php include VIEWPATH."/frame/partialHead.php"; ?>

<!-- 第一页 -->
<div data-role="page" id="page_1">
    <div data-role="header"><h1>用户信息</h1></div>
    <div data-role="main" class="ui-content">
        <p>用户名称 <?php echo $user->fullName; ?> <a href="#page_2">修改密码</a></p>
        <p>证件类型 <?php echo $user->credentialType; ?></p>
        <p>证件编号 <?php echo $user->credentialNumber; ?></p>
        <p>联系方式 <?php echo $user->mobileNumber; ?></p>
        <label for="drivingLicense">驾驶证信息</label>
        <?php if($user->drivingLicense==''): ?>
            <a href="#page_3">添加驾驶证</a>
        <?php endif; ?>
        <ul id="drivingLicense">
            <?php if($user->drivingLicense!=''): ?>
                <li>驾驶证号 <?php echo $user->drivingLicense; ?> <button type="button" onclick="modifyDrivingLicense(<?php echo $user->drivingLicenseID; ?>)">修改</button></li>
            <?php endif; ?>
        </ul>
        <label for="vehicles">车辆信息</label><a href="#page_4">增加车辆</a>
        <ul id="vehicles">
            <?php if(isset($vehicleArray)): ?>
                <?php foreach ($vehicleArray as $vehicle): ?>
                    <li>行驶证号 <?php echo $vehicle->licensePlate; ?> <button type="button" onclick="repealVehicle(<?php echo $vehicle->aID; ?>)">撤消</button></li>
                <?php endforeach; ?>
            <?php endif; ?>
        </ul>
    </div>
    <div data-role="footer" class="textCenter"><a data-role="button" onclick="history.back()">返回</a></div>
</div>

<!-- 修改密码页 -->
<div data-role="page" id="page_2">
    <form name="form1">
        <fieldset><label for="userPassword">输入旧密码</label><input type="password" name="userPassword" id="userPassword" /></fieldset>
        <fieldset><label for="newPassword">输入新密码</label><input type="password" name="newPassword" id="newPassword" /></fieldset>
        <fieldset><label for="ensurePassword">确认新密码</label><input type="password" name="ensurePassword" id="ensurePassword" /></fieldset>
        <input type="hidden" name="credentialNumber" value="<?php echo $user->credentialNumber; ?>" />
        <a href="#page_1">返回</a><button type="button" onclick="changePassword(form1)">修改</button>
    </form>
</div>

<!-- 添加驾照信息页 -->
<div data-role="page" id="page_3">
    <form enctype="multipart/form-data" name="form2">
        <fieldset><label for="drivingLicense">驾驶证号</label><input type="text" name="drivingLicense" id="drivingLicense" /></fieldset>
        <fieldset><label for="licensePhoto">上传驾驶证照片</label><input type="file" name="licensePhoto" id="licensePhoto" /></fieldset>
        <a href="#page_1">返回</a><button type="button" onclick="addDrivingLicense(form2)">增加</button>
    </form>
</div>

<!-- 增加车辆信息页 -->
<div data-role="page" id="page_4">
    <form enctype="multipart/form-data" name="form3">
        <fieldset><label for="licensePlate">行驶证号</label><input type="text" name="licensePlate" id="licensePlate" /></fieldset>
        <fieldset><label for="licensePlatePhoto">上传行驶证照片</label><input type="file" name="licensePlatePhoto" id="licensePlatePhoto" /></fieldset>
        <fieldset><label for="vehiclePhotos">上传货车照片</label><input type="file" multiple="true" name="vehiclePhotos[]" id="vehiclePhotos" /></fieldset>
        <a href="#page_1">返回</a><button type="button" onclick="addVehicle(form3)">添加</button>
    </form>
</div>
<div id="test"></div>
<script>

    var xmlhttp=new XMLHttpRequest();
    //记录url用于页内跳转ajax操作后仍回到当前页
    var mainPage="<?php echo site_url('user/info'); ?>";

    //修改密码
    function changePassword(formObj){
        xmlhttp.onreadystatechange=function()
        {
            if (xmlhttp.readyState==4 && xmlhttp.status==200)
            {
                //如果成功提示并回到主页面
                if(xmlhttp.responseText=='')
                {
                    alert("修改成功");
                    location.href=mainPage;
                }
                else{
                    alert(xmlhttp.responseText);
                }
            }
        };
        xmlhttp.open("post","<?php echo site_url('user/changePassword'); ?>",true);
        xmlhttp.send(new FormData(formObj));
    }

    //添加驾驶证信息
    function addDrivingLicense(formObj){
        xmlhttp.onreadystatechange=function()
        {
            if (xmlhttp.readyState==4 && xmlhttp.status==200)
            {
                //如果成功刷新页面显示驾驶证信息
                if(xmlhttp.responseText=='')
                {
                    location.href=mainPage;
                }//否则显示失败提示
                else{
                    alert(xmlhttp.responseText);
                    //alert("添加失败，您可刷新重试。");
                }
            }
        };
        xmlhttp.open("post","<?php echo site_url('vehicle/addDrivingLicense'); ?>",true);
        xmlhttp.send(new FormData(formObj));
    }

    //修改驾驶证
    function modifyDrivingLicense(drivingLicenseID) {
        var result=confirm("修改之前要先撤消当前驾驶证，是否撤消？");
        //如果选择：是，则进行撤消工作
        if(result){
            xmlhttp.onreadystatechange=function()
            {
                if (xmlhttp.readyState==4 && xmlhttp.status==200)
                {
                    //如果撤消成功则跳转到添加驾驶证页面
                    if(xmlhttp.responseText=='')
                    {
                        location.reload();
                        location.href=mainPage+'#page_3';
                    }
                    else{
                        alert("未能撤消，您可返回重试。");
                    }
                }
            };
            xmlhttp.open("post","<?php echo site_url('vehicle/repealDrivingLicense/'); ?>"+drivingLicenseID,true);
            xmlhttp.send();
        }
    }

    //增加车辆信息
    function addVehicle(formObj){
        xmlhttp.onreadystatechange=function()
        {
            document.getElementById('test').innerHTML+=xmlhttp.responseText;
            if (xmlhttp.readyState==4 && xmlhttp.status==200)
            {
                //如果成功刷新页面显示车辆信息
                if(xmlhttp.responseText=='')
                {
                    location.href=mainPage;
                }
                else{
                    alert(xmlhttp.responseText);
                }
            }
        };
        xmlhttp.open("post","<?php echo site_url('vehicle/addVehicle'); ?>",true);
        //利用html5 new FormData()异步提交整个表单数据
        xmlhttp.send(new FormData(formObj));
    }

    //撤消行驶证（车辆）
    function repealVehicle(vehicleID) {
        var result=confirm("确定要撤消吗？");
        //如果选择：是，则进行撤消工作
        if(result){
            xmlhttp.onreadystatechange=function()
            {
                if (xmlhttp.readyState==4 && xmlhttp.status==200)
                {
                    //如果撤消成功则刷新页面显示
                    if(xmlhttp.responseText=='')
                    {
                        location.reload();
                    }
                    else{
                        alert("未能撤消，您可返回重试。");
                    }
                }
            };
            xmlhttp.open("post","<?php echo site_url('vehicle/repealVehicle/'); ?>"+vehicleID,true);
            xmlhttp.send();
        }
    }

</script>

<?php include VIEWPATH."/frame/partialFoot.php"; ?>