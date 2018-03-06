<?php include VIEWPATH."/frame/partialHead.php"; ?>

<div data-role="page">
    <div data-role="header"><h1>我的货源</h1></div>
    <div data-role="main" class="ui-content">

        <?php if(count($runningFreights)): ?>
            <label>进行中</label>
            <ul>
                <?php foreach ($runningFreights as $freight): ?>
                    <?php $dateTimeArray=explode(' ',$freight->publishTime); ?>
                    <li>
                        <a data-ajax="false" href="<?php echo site_url('freight/view/'.$freight->freightID); ?>">
                            <?php echo $freight->beginProvince.' '.$freight->beginCity.' --> '.$freight->endProvince.' '.$freight->endCity.' '.$dateTimeArray[0]; ?>
                        </a>
                        <button>撤消</button>
                    </li>
                <?php endforeach; ?>
            </ul>
        <?php endif; ?>

        <?php if(count($finishiedFreights)): ?>
            <label>已完成</label>
            <ul>
                <?php foreach ($finishiedFreights as $freight): ?>
                    <?php $dateTimeArray=explode(' ',$freight->publishTime); ?>
                    <li>
                        <a data-ajax="false" href="<?php echo site_url('freight/view/'.$freight->freightID); ?>">
                            <?php echo $freight->beginProvince.' '.$freight->beginCity.' --> '.$freight->endProvince.' '.$freight->endCity.' '.$dateTimeArray[0]; ?>
                        </a>
                    </li>
                <?php endforeach; ?>
            </ul>
        <?php endif; ?>

    </div>
    <div data-role="footer" class="textCenter"><h1> </h1></div>
</div>

<?php include VIEWPATH."/frame/partialFoot.php"; ?>