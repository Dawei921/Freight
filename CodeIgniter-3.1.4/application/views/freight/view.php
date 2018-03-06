<?php include VIEWPATH."/frame/partialHead.php"; ?>

<div data-role="page" id="page_1">
    <div data-role="header"><h1>货源信息</h1></div>
    <div data-role="main" class="ui-content">
        <div>
            <?php if(isset($freight)): ?>
                <p><?php echo $freight->beginProvince.' '.$freight->endProvince; ?></p>
                <p><?php echo $freight->beginCity.' '.$freight->beginDistrict.' --> '.$freight->endCity.' '.$freight->endDistrict; ?></p>
                <p><?php echo $freight->vehicleLongness.' '.$freight->longnessUnit.' '.$freight->vehicleType.' '.$freight->weight.' 吨'; ?></p>
                <p><?php echo '占 '.$freight->cubage.' 方 '.$freight->distance.' 公里 '.$freight->goodsType; ?></p>
                <p><?php echo '运费 '.$freight->expenses.' 元'; ?> <a href="#page_3">查看地图</a></p>

                <!-- 如果是货主 -->
                <?php if($this->session->userID==$freight->publishUser): ?>
                    <!-- 如果车主已接单 -->
                    <?php if($freight->recordStatus==2): ?>
                        <!-- 订单信息：待完成 -->
                        <p>承运人 <?php echo $freight->franchiserUser; ?></p>
                        <p>承运人车牌号 <?php echo $freight->vehicle; ?></p>
                        <p>联系方式 <?php echo $freight->mobileNumber; ?></p>
                    <!-- 未接单但已同意车主接单 -->
                    <?php elseif($freight->recordStatus==1): ?>
                        <p>承运人 <?php echo $freight->franchiserUser; ?></p>
                        <p>承运人车牌号 <?php echo $freight->vehicle; ?></p>
                        <p>联系方式 <?php echo $freight->mobileNumber; ?></p>
                    <!-- 车主想接单处理 -->
                    <?php elseif($freight->recordStatus==0): ?>
                        <?php foreach ($franchisers as $franchiser): ?>
                            <?php $franchiserTimeArray=explode(' ',$franchiser->franchiserTime); ?>
                            <p><?php echo $franchiserTimeArray[0].' '.$franchiser->franchiser; ?>
                                <?php echo $franchiser->franchiserUser; ?>
                                <?php if($franchiser->franchiser=='我想接单'): ?>
                                    <a onclick="freightReply(<?php echo $freight->freightID; ?>,<?php echo $franchiser->franchiserID; ?>);">同意接单</a>
                                <?php endif; ?>
                            </p>
                        <?php endforeach; ?>
                    <?php endif; ?>
                <!-- 否则如果是车主 -->
                <?php else: ?>
                    <!-- 如果货主已同意接单 -->
                    <?php if($freight->recordStatus==1): ?>
                        <fieldset><label for="agree">同意来拉</label><input type="checkbox" value="同意来拉" id="agree" /></fieldset>
                        <p><a onclick="pay(<?php echo $freight->freightID; ?>,100)">支付平台佣金换取联系方式</a></p>
                    <!-- 我想接单反馈 -->
                    <?php elseif($freight->recordStatus==0): ?>
                        <p><a href="#page_2">我想接单</a><a onclick="freightFranchiser(<?php echo $freight->freightID; ?>,-1)">运费太低</a></p>
                    <?php endif; ?>
                <?php endif; ?>
                <script>
                    var beginPositionStr="<?php echo $freight->beginPosition; ?>";
                    var endPositionStr="<?php echo $freight->endPosition; ?>";
                    var beginPositionArray=beginPositionStr.split(',');
                    var endPositionArray=endPositionStr.split(',');
                </script>
            <?php endif; ?>
        </div>
    </div>
    <div data-role="footer" class="textCenter"><a onclick="history.back();">返回</a></div>
</div>

<!-- 第二页 -- 接单车辆信息填写 -->
<div data-role="page" id="page_2">
    <div data-role="header"><h1></h1></div>
    <div data-role="main" class="ui-content">
        <form name="form1">
            <div class="ui-field-contain">
                <fieldset><label for="vehicle">选择车辆</label>
                    <select name="vehicle" id="vehicle">
                        <option value="">请选择...</option>
                        <?php foreach ($vehicles as $vehicle): ?>
                            <option value="<?php echo $vehicle->aID; ?>"><?php echo $vehicle->licensePlate; ?></option>
                        <?php endforeach; ?>
                    </select>
                </fieldset>
                <fieldset>
                    <legend>驾驶员信息</legend>
                    <div id="drivers"></div>
                    <a onclick="addDriver()">+</a>
                </fieldset>
            </div>
        </form>
    </div>
    <div data-role="footer"><h1><a onclick="freightFranchiser(<?php echo $freight->freightID; ?>,1,form1)">确定</a></h1></div>
</div>

<!-- 第三页 -- 正常不显示，专用于显示地图 -->
<div data-role="page" id="page_3">
    <div data-role="header"><h1>地图</h1><a href="#page_1" class="ui-btn ui-btn-right ui-corner-all ui-shadow ui-icon-home ui-btn-icon-left">返回</a></div>
    <div data-role="main" class="ui-content">
        <div id="mapContainer" style="height:480px;width:100%;"></div>
    </div>
    <div data-role="footer"><h1> </h1></div>
</div>

<!-- 地图功能 -->
<script src="http://webapi.amap.com/maps?v=1.3&key=2d41b7d8e025224e75366c24862efda1"></script>
<script>
    //路线导航功能
    var map = new AMap.Map('mapContainer');

    //如果存在发货信息，并已取出坐标信息 -- 则执行起终点路线导航处理
    if(typeof(beginPositionArray)!="undefined" && typeof(endPositionArray)!="undefined")
    {
        var beginPosition=new AMap.LngLat(beginPositionArray[0],beginPositionArray[1]);
        var endPosition=new AMap.LngLat(endPositionArray[0],endPositionArray[1]);

        //路线导航类
        AMap.plugin('AMap.Driving',function(){
            var driving = new AMap.Driving({
                map: map,
            });

            // 根据起终点经纬度规划驾车导航路线
            driving.search(beginPosition, endPosition);
        });
    }

    //全局变量：添加的驾驶员数
    var driverCount=1;
    //增加驾驶员
    function addDriver() {
        var container=document.getElementById('drivers');
        var label=document.createElement('label');
        label.textContent="驾驶员";
        label.htmlFor="driver"+driverCount;
        var input=document.createElement('input');
        input.type="text";
        input.name="driver[]";
        input.id="driver"+driverCount;
        var label2=document.createElement('label');
        label2.textContent="驾驶证";
        label2.htmlFor="drivingLicense"+driverCount;
        var input2=document.createElement('input');
        input2.type="text";
        input2.name="drivingLicense[]";
        input2.id="drivingLicense"+driverCount;
        var a=document.createElement("a");
        a.textContent="-";
        a.setAttribute('count',driverCount);
        a.addEventListener('click',function(){removeDriver(this.getAttribute('count'));});
        var div=document.createElement('div');
        div.appendChild(label);
        div.appendChild(input);
        div.appendChild(label2);
        div.appendChild(input2);
        div.appendChild(a);
        container.appendChild(div);
        driverCount+=1;
    }

    //移除驾驶员信息
    function removeDriver(sequenceNumber) {
        var container=document.getElementById('drivers');
        container.removeChild(container.childNodes[sequenceNumber-1]);
    }

    //全局异步处理对象：
    var xmlhttp=new XMLHttpRequest();

    //接单意愿反馈
    function freightFranchiser(freightID,franchiser,formObj)
    {
        xmlhttp.onreadystatechange=function()
        {
            if (xmlhttp.readyState==4 && xmlhttp.status==200)
            {
                //返回空表示执行成功
                if(xmlhttp.responseText==""){
                    alert("已反馈您的意愿给货主，谢谢！");
                    history.back();
                }//如果失败提示
                else{
                    alert(xmlhttp.responseText);
                }
            }
        };
        xmlhttp.open("post","<?php echo site_url('freight/franchiser/'); ?>"+freightID+"/"+franchiser,true);
        if(typeof(formObj)!=="undifined"){
            xmlhttp.send(new FormData(formObj));
        }
        else{
            xmlhttp.send();
        }
    }

    //同意接单
    function freightReply(freightID,franchiserID) {
        xmlhttp.onreadystatechange=function()
        {
            if (xmlhttp.readyState==4 && xmlhttp.status==200)
            {
                //返回空表示执行成功
                if(xmlhttp.responseText==""){
                    //刷新页面显示接单人信息，避免再点击“同意接单”
                    location.reload();
                }//否则什么也不做
            }
        };
        xmlhttp.open("post","<?php echo site_url('freight/reply/'); ?>"+freightID+"/"+franchiserID,true);
        xmlhttp.send();
    }

    //支付
    function pay(freightID,brokerage) {
        xmlhttp.onreadystatechange=function()
        {
            if (xmlhttp.readyState==4 && xmlhttp.status==200)
            {
                //返回空表示执行成功
                if(xmlhttp.responseText==""){
                    alert("您已支付成功。")
                    //刷新页面显示支付后信息，避免再次点击“支付”
                    location.reload();
                }//否则什么也不做
            }
        };
        xmlhttp.open("post","<?php echo site_url('order/apply/'); ?>"+freightID+"/"+brokerage,true);
        xmlhttp.send();
    }

</script>

<?php include VIEWPATH."/frame/partialFoot.php"; ?>