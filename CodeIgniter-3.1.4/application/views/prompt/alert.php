<?php echo $content; ?>

<script>
    //使用定时显示内容2秒后自动跳转到相应页
    //注意：定时只能弹框不能关框必须点击，所以直接在网页内显示内容
    setTimeout(function(){
        location.href="<?php echo $url; ?>";
    },2000);
</script>