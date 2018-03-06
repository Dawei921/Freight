<?php include VIEWPATH."/frame/partialHead.php"; ?>

<div data-role="page" id="publish_1">
    <div data-role="header"><h1>我要发货</h1></div>
    <div data-role="main" class="ui-content">
        <form data-ajax="false" action="" method="post" enctype="multipart/form-data" id="form_publish">
            <div class="ui-field-contain">
                <fieldset data-role="controlgroup" data-type="horizontal">
                        <label for="vehicleLongness">所需车长</label>
                            <select name="vehicleLongness" id="vehicleLongness">
                                <option value="">所需车长</option>
                                <?php
                                    foreach ($vehicleLongnessArray as $vehicleLongness)
                                    {
                                        echo "<option value='$vehicleLongness->aID'>$vehicleLongness->longness $vehicleLongness->unit</option>";
                                    }
                                ?>
                            </select>
                        <label for="vehicleType">车型</label>
                            <select name="vehicleType" id="vehicleType">
                                <option value="">车型</option>
                                <?php
                                    foreach ($vehicleTypeArray as $vehicleType)
                                    {
                                        echo "<option value='$vehicleType->code'>$vehicleType->describ</option>";
                                    }
                                ?>
                            </select>
                </fieldset>
                <fieldset data-role="controlgroup" data-type="horizontal">
                    <legend>出发</legend>
                        <label for="beginProvince">省</label><select name="beginProvince" id='beginProvince' style="width:100px" onchange='search(this,document.getElementById("beginCity"))'></select>
                        <label for="beginCity">市</label><select name="beginCity" id='beginCity' style="width:100px" onchange='search(this,document.getElementById("beginDistrict"))'></select>
                        <label for="beginDistrict">县/区</label><select name="beginDistrict" id='beginDistrict' style="width:100px" onchange=''></select>
                </fieldset>
                <fieldset>
                    <label for="beginStreet"></label>
					<input type="text" name="beginStreet" placeholder="发货具体村庄/街道" id="beginStreet" /><a class="ui-icon-search ui-btn-icon-right" href="#publish_3" onclick="changeFillIDPrefix('begin')">定位</a>
                </fieldset>

                <input type="hidden" name="beginPosition" id="beginPosition" />

                <fieldset data-role="controlgroup" data-type="horizontal">
                    <legend>卸货</legend>
                        <label for="endProvince">省</label><select name="endProvince" id='endProvince' style="width:100px" onchange='search(this,document.getElementById("endCity"))'></select>
                        <label for="endCity">市</label><select name="endCity" id='endCity' style="width:100px" onchange='search(this,document.getElementById("endDistrict"))'></select>
                        <label for="endDistrict">县/区</label><select name="endDistrict" id='endDistrict' style="width:100px" onchange=''></select>
                </fieldset>
                <fieldset>
                    <label for="endStreet"></label>
					<input type="text" name="endStreet" placeholder="卸货具体村庄/街道" id="endStreet" /><a class="ui-icon-search ui-btn-icon-right" href="#publish_3" onclick="changeFillIDPrefix('end')">定位</a>
                </fieldset>

                <input type="hidden" name="endPosition" id="endPosition" />

                <fieldset>
                    <label for="expenses">运费</label><input type="text" name="expenses" id="expenses" />
                    <label for="distance">相距公里</label><input type="text" name="distance" id="distance" />
                </fieldset>

        <!-- 结束div用于分页 -->
            </div>
        </form>
    </div>
    <div data-role="footer" class="textCenter"><a data-role="button" href="#publish_2">下一步</a></div>
</div>

<!-- 第二页 -->
<div data-role="page" id="publish_2">
    <div data-role="header"><h1>我要发货</h1></div>
    <div data-role="main" class="ui-content">
        <div data-role="fieldcontain">
            <fieldset data-role="controlgroup" data-type="horizontal">
                <legend>是否需要棉被</legend>
                    <label for="covering1">是</label><input type="radio" name="covering" value="棉被" form="form_publish" id="covering1" />
                    <label for="covering2">否</label><input type="radio" name="covering" value="" form="form_publish" id="covering2" />
            </fieldset>
            <fieldset>
                <label for="cubage">所占立方</label><input type="text" name="cubage" form="form_publish" id="cubage" />
                <label for="weight">货重</label><input type="text" name="weight" form="form_publish" id="weight" />吨
            </fieldset>

            <fieldset><label for="screen">上传货物照片</label><input type="file" name="screen[]" multiple="multiple" form="form_publish" id="screen" /></fieldset>
            <fieldset><label for="goodsType">货物类型</label><input type="text" name="goodsType" form="form_publish" id="goodsType" /></fieldset>
            <fieldset><label for="describ">其它要求描述</label><textarea name="describ" form="form_publish" id="describ"></textarea></fieldset>
            <fieldset><label for="phone">装货责任人号码</label><input type="text" name="phone" form="form_publish" id="phone" /></fieldset>
        </div>

    </div>
    <div data-role="footer" class="textCenter"><input type="submit" name="publishSubmit" value="发布货源" form="form_publish" /><a href="#publish_1">返回</a></div>
</div>

<!-- 第三页 -- 一般不会显示，专用于地图显示 -->
<div data-role="page" id="publish_3">
    <div data-role="header"><h1>地图</h1><a href="#publish_1" class="ui-btn ui-btn-right ui-corner-all ui-shadow ui-icon-home ui-btn-icon-left" onclick="fillRegecoder();">确定</a></div>
    <div data-role="main" class="ui-content">
        <div id="mapContainer" style="height:480px;width:100%;"></div>
    </div>
    <div data-role="footer"><h1> </h1></div>
</div>

<!-- 地图功能 -->
<script src="http://webapi.amap.com/maps?v=1.3&key=2d41b7d8e025224e75366c24862efda1"></script>
<script>

    //自动定位 -- 注意：要求必须有一个html标签作地图容器
    var map = new AMap.Map('mapContainer');
    var beginPosition,endPosition,currentPosition;

    var geolocation;
    AMap.plugin('AMap.Geolocation', function(){
        geolocation = new AMap.Geolocation({
            timeout: 10000,          //超过10秒后停止定位，默认：无穷大
            showCircle:false
        });
        map.addControl(geolocation);
        geolocation.getCurrentPosition();
        //注意回调函数没有参数，实际隐含一个绑定事件对象的结果参数，所以实现函数要有一个参数对应
        AMap.event.addListener(geolocation, 'complete', onComplete);//返回定位信息
        AMap.event.addListener(geolocation, 'error', onError);      //返回定位出错信息
    });

    //解析定位成功填充各区域值到发货列表
    function onComplete(data)
    {
        beginPosition=data.position;
        bindList(data.addressComponent,'begin');
    }

    //解析定位出错构建发货地区域选择列表
    function onError(data)
    {
        //调用上面创建行政区划列表对象
        district.search('中国', function(status, result){
            if(status=='complete')
            {
                getData(result.districtList[0],document.getElementById('beginProvince'));
            }
        });
    }

    //解析地址信息填充到地理位置列表
    function bindList(addressComponent1,IDPrefix){
        var option1=new Option(addressComponent1.province);
        //option1.value='province';
        option1.setAttribute('adcode',addressComponent1.adcode);
        //为释放内存先清除列表
        document.getElementById(IDPrefix+'Province').options.length=0;
        document.getElementById(IDPrefix+'Province').add(option1);

        var option2=new Option(addressComponent1.city);
        //option2.value='city';
        option2.setAttribute('adcode',addressComponent1.adcode);
        //为释放内存先清除列表
        document.getElementById(IDPrefix+'City').options.length=0;
        document.getElementById(IDPrefix+'City').add(option2);

        var option3=new Option(addressComponent1.district);
        //option3.value='district';
        option3.setAttribute('adcode',addressComponent1.adcode);
        //为释放内存先清除列表
        document.getElementById(IDPrefix+'District').options.length=0;
        document.getElementById(IDPrefix+'District').add(option3);
    }

    //fillSelect -- 要填充的列表
    function getData(data,fillSelect)
    {
        //先清除列表以释放内存
        fillSelect.options.length=0;
        var subList = data.districtList;
        if (subList)
        {
            var contentSub =new Option('请选择...');
            contentSub.value="";    //默认为option.text值所以基于业务需要提交为空值
            for(var i = 0, l = subList.length; i < l; i++)
            {
                var name = subList[i].name;
                //var levelSub = subList[i].level;
                if(i==0)
                {
                    fillSelect.add(contentSub);
                }
                contentSub=new Option(name);
                //contentSub.setAttribute("value", levelSub);
                contentSub.adcode = subList[i].adcode;
                fillSelect.add(contentSub);
            }

            //同步更新：清空下一级别的下拉列表 -- 待完成
            /*if (level === 'province') {
             nextLevel = 'city';
             citySelect.innerHTML = '';
             districtSelect.innerHTML = '';
             areaSelect.innerHTML = '';
             } else if (level === 'city') {
             nextLevel = 'district';
             districtSelect.innerHTML = '';
             areaSelect.innerHTML = '';
             } else if (level === 'district') {
             nextLevel = 'street';
             areaSelect.innerHTML = '';
             }*/
        }
    }

    //层级行政区划列表
    var opts = {
        subdistrict: 1,   //返回下一级行政区
        level: 'city',
        showbiz:false  //查询行政级别为 市
    };
    var district;
    AMap.plugin('AMap.DistrictSearch',function (){
        district = new AMap.DistrictSearch(opts);
        district.search('中国', function(status, result){
            if(status=='complete')
            {
                getData(result.districtList[0],document.getElementById('endProvince'));
            }
        });
    });

    //关键字检索下级行政区划列表
    function search(obj,targetSelect)
    {
        var option = obj[obj.options.selectedIndex];
        //var keyword = option.text; //关键字
        var adcode = option.adcode;
        //district.setLevel(option.value); //行政区级别
        //行政区查询
        //按照adcode进行查询可以保证数据返回的唯一性
        district.search(adcode, function(status, result){
            if(status === 'complete')
            {
                getData(result.districtList[0],targetSelect);
            }
        });
    }

    //双击地图定位标记当前位置
    var marker=new AMap.Marker();
    marker.setMap(map);
    map.on('dblclick', function(e){
        marker.setPosition(e.lnglat); //基点位置
        currentPosition=e.lnglat;
    });
    //逆地理编码
    var geocoder;
    AMap.plugin('AMap.Geocoder',function (){
        geocoder = new AMap.Geocoder({
            extensions: "all"
        });
    });
    //解析位置地址调用填充地址列表功能
    function regeocoder(position,IDPrefix){
        geocoder.getAddress(position, function (status, result){
            if (status === 'complete' && result.info === 'OK')
            {
                bindList(result.regeocode.addressComponent,IDPrefix);
            }
        });
    }

    var fillIDPrefix;
    //点击定位改变填充地址列表ID前缀
    function changeFillIDPrefix(IDPrefix) {
        fillIDPrefix=IDPrefix;
    }

    //调用 位置地址转换 以解析填充地址列表并计算距离填充表单
    function fillRegecoder()
    {
        regeocoder(currentPosition,fillIDPrefix);
        if(fillIDPrefix=='begin')
        {
            beginPosition=currentPosition;
            document.getElementById('beginPosition').value=currentPosition;
        }
        else if(fillIDPrefix=='end')
        {
            endPosition=currentPosition;
            document.getElementById('endPosition').value=currentPosition;
        }
        //计算里程
        if(typeof(beginPosition)!=="undefined" && typeof(endPosition)!=="undefined"){
            document.getElementById('distance').value= Math.floor(endPosition.distance(beginPosition)/1000);
        }
    }

</script>

<?php include VIEWPATH."/frame/partialFoot.php"; ?>