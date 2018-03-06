<?php
    echo validation_errors();
    if(isset($errors)){echo $errors;}
?>
<script>
    //confirm 会先显示内容再显示对话框，且在点击 确定 按钮时回退到先前页
    //使用定时来显示页面内容后再显示对话框，否则web特性决定的会先显示对话框而致页面空白
    setTimeout(function() {
        var result = confirm("<?php echo $content; ?>");
        if (result) {
            history.back();
        }
    },2000);
</script>