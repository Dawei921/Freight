<?php include VIEWPATH."/frame/partialHead.php"; ?>

    <!-- begin 以下用于触屏翻页效果 -->
    <style>
        /*圆：实心/空心*/
        .circleBackground{display:block;border:1px solid blue;float:left;margin:0 10px;width:30px;height:30px;border-radius:15px;background-color:blue;}
        .circle{display:block;border:1px solid blue;float:left;margin:0 10px;width:30px;height:30px;border-radius:15px;}
    </style>
    <script>
        $(document).on("pagecreate",function()
        {
            $("#page_1").on("swipe", function()
            {
                $("#a1").click();
            });

            $("#page_2").on("swipe", function()
            {
                $("#a2").click();
            });
        });
    </script>
    <!-- end 翻页效果代码结束 -->

    <!-- 第一页 -->
    <div data-role="page" id="page_1">
        <div data-role="header"><h1>欢迎使用</h1></div>
        <div data-role="main" class="ui-content">
            <h2>找车配货<br />就用<br />神来神往</h2>
        </div>
        <div data-role="footer"><h1><a class="circleBackground" id="a1" href="#page_2"></a><a class="circle" id="a2" href="#page_3"></a><a class="circle" id="a3"></a></h1></div>
    </div>

    <!-- 第二页 -->
    <div data-role="page" id="page_2">
        <div data-role="header"><h1>欢迎使用</h1></div>
        <div data-role="main" class="ui-content">
            <h2>抢占先机 赢取未来</h2>
            <p>将该软件分享给好友，好友通过该链接注册后将成为你的成员。并且你的成员今后在该平台交易都将给予你一定的奖励，如此积累，邀请越多赚取的越多。</p>
        </div>
        <div data-role="footer"><h1><a class="circle" id="a11" href="#page_2"></a><a class="circleBackground" id="a22" href="#page_3"></a><a class="circle" id="a33"></a></h1></div>
    </div>

    <!-- 第三页 -->
    <div data-role="page" id="page_3">
        <div data-role="header"><h1>欢迎使用</h1></div>
        <div data-role="main" class="ui-content">
            <h3><a data-ajax="false" href="<?php echo site_url('user/register'); ?>">注册</a></h3>
            <h3><a data-ajax="false" href="<?php echo site_url('user/login'); ?>">登录</a></h3>
        </div>
        <div data-role="footer"><h1>找车配货就用神来神往</h1></div>
    </div>

<?php include VIEWPATH."/frame/partialFoot.php"; ?>