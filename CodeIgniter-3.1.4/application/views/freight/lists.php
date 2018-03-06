<?php include VIEWPATH."/frame/partialHead.php"; ?>

<div data-role="page">
    <div data-role="header"><h1>查看货源</h1></div>
    <div data-role="main" class="ui-content">

        <div id="mapContainer" style="display: none;"></div>

        <div class="ui-field-contain">
            <fieldset data-role="controlgroup" data-type="horizontal">
                <label for="beginCity"></label><select name="beginCity" id="beginCity" style="width:100px" onchange="getFreights(document.getElementById('beginProvince').value,this.value);"></select>
                <label for="beginProvince"></label><select name="beginProvince" id="beginProvince" style="width:100px" onchange="search(this,document.getElementById('beginCity'));"></select>
                <label for="vehicleLongness">车长</label>
                    <select name="vehicleLongness" id="vehicleLongness" onchange="filterFreights()">
                        <option value="">请选择...</option>
                        <?php
                            foreach ($vehicleLongnessArray as $vehicleLongness)
                            {
                                echo "<option>$vehicleLongness->longness $vehicleLongness->unit</option>";
                            }
                        ?>
                    </select>
                <label for="vehicleType">车型</label>
                    <select name="vehicleType" id="vehicleType" onchange="filterFreights()">
                        <option value="">请选择...</option>
                        <?php
                            foreach ($vehicleTypeArray as $vehicleType)
                            {
                                echo "<option>$vehicleType->describ</option>";
                            }
                        ?>
                    </select>
            </fieldset>
        </div>

        <div id="freightsList"></div>

    </div>
    <div data-role="footer" class="textCenter"><h1> </h1></div>
</div>
<div id="test"></div>
<!-- 地图功能 -->
<script src="http://webapi.amap.com/maps?v=1.3&key=2d41b7d8e025224e75366c24862efda1"></script>
<script>

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
                getData(result.districtList[0],document.getElementById('beginProvince'));
            }
        });
    })

    //fillSelect -- 要填充的列表
    function getData(data,fillSelect)
    {
        //先清空列表以释放内存
        fillSelect.options.length=0;

        var subList = data.districtList;
        if (subList){
            if(fillSelect.id=='beginProvince'){
                var contentSub =new Option('全国');
            }
            else{
                var contentSub =new Option('请选择...');
            }
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
        }
    }

    //targetSelect -- 目标填充列表
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

    //定位当前城市 -- 以填充城市列表
    //注意：要求必须有一个html标签作地图容器
    var map = new AMap.Map('mapContainer');
    var geolocation;
    AMap.plugin('AMap.Geolocation', function(){
        geolocation = new AMap.Geolocation({
            timeout: 10000,          //超过10秒后停止定位，默认：无穷大
            showCircle:false
        });
        map.addControl(geolocation);
        geolocation.getCurrentPosition();
        AMap.event.addListener(geolocation, 'complete', onComplete);//返回定位信息
    });

    //解析定位成功填充各区域值到发货列表
    function onComplete(data)
    {
        var addressComponent1=data.addressComponent;
        var option2=new Option(addressComponent1.city);
        option2.setAttribute('adcode',addressComponent1.adcode);
        document.getElementById('beginCity').add(option2);
        //定位成功显示货源列表
        getFreights(addressComponent1.province,addressComponent1.city);
    }

    var urlPrefix="<?php echo site_url(); ?>";  //用于js跳转地址
    var freightsJson;   //缓存返回json数据用于关键字过滤

    //根据省市名ajax获取相应发货数据以json格式返回，生成列表
    function getFreights(beginProvince,beginCity)
    {
        var container=document.getElementById('freightsList');
        container.innerHTML=''; //先清除当前列表

        var xmlhttp=new XMLHttpRequest();
        xmlhttp.onreadystatechange=function()
        {
            if (xmlhttp.readyState==4 && xmlhttp.status==200)
            {
                freightsJson=eval('('+xmlhttp.responseText+')');
                if(freightsJson!='')
                {
                    var htmlString='';
                    for(var key in freightsJson)
                    {
                        var publishTime=freightsJson[key].publishTime.split(' ');
                        htmlString+='<p><a data-ajax="false" href="'+urlPrefix+'/freight/view/'+freightsJson[key].freightID+'">'+freightsJson[key].beginProvince+' '+freightsJson[key].beginCity+' --> '+freightsJson[key].endProvince+' '+freightsJson[key].endCity
                            +'<br />'+freightsJson[key].vehicleLongness+' '+freightsJson[key].longnessUnit+' '+freightsJson[key].vehicleType+' '+freightsJson[key].weight+' 吨 '+publishTime[0]+'</a></p>';
                    }
                    container.innerHTML=htmlString;
                }
            }
        };
        xmlhttp.open("post","<?php echo site_url('freight/lists'); ?>"+"?beginProvince="+beginProvince+"&beginCity="+beginCity,true);
        xmlhttp.send();
    }

    //车长车型过滤货源列表
    function filterFreights(){
        var vehicleLongness=document.getElementById('vehicleLongness').value;
        var vehicleType=document.getElementById('vehicleType').value;
        //存在过滤值时检索
        if(vehicleLongness!="" && vehicleType!="") {
            if (freightsJson != '') {
                var container = document.getElementById('freightsList');
                container.innerHTML = ''; //先清除当前列表
                var htmlString = '';
                for (var key in freightsJson) {
                    if (freightsJson[key].vehicleLongness == vehicleLongness && freightsJson[key].vehicleType == vehicleType) {
                        var publishTime = freightsJson[key].publishTime.split(' ');
                        htmlString += '<p><a data-ajax="false" href="' + urlPrefix + '/freight/view/' + freightsJson[key].freightID + '">' + freightsJson[key].beginProvince + ' ' + freightsJson[key].beginCity + ' --> ' + freightsJson[key].endProvince + ' ' + freightsJson[key].endCity
                            + '<br />' + freightsJson[key].vehicleLongness + ' ' + freightsJson[key].longnessUnit + ' ' + freightsJson[key].vehicleType + ' ' + freightsJson[key].weight + ' 吨 ' + publishTime[0] + '</a></p>';
                    }
                }
                container.innerHTML = htmlString;
            }
        }
    }

</script>

<?php include VIEWPATH."/frame/partialFoot.php"; ?>