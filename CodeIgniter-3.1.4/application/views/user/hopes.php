<?php include VIEWPATH."/frame/partialHead.php"; ?>

<div data-role="page">
    <div data-role="header"><h1>我想接单</h1></div>
    <div data-role="main" class="ui-content">
        <ul>
            <?php foreach ($hopeFreights as $freight): ?>
                <?php $dateTimeArray=explode(' ',$freight->publishTime); ?>
                <li>
                    <a data-ajax="false" href="<?php echo site_url('freight/view/'.$freight->freightID); ?>">
                        <?php echo $freight->beginProvince.' '.$freight->beginCity.' --> '.$freight->endProvince.' '.$freight->endCity.' '.$dateTimeArray[0]; ?>
                    </a>
                </li>
            <?php endforeach; ?>
        </ul>
    </div>
    <div data-role="footer" class="textCenter"><h1> </h1></div>
</div>

<?php include VIEWPATH."/frame/partialFoot.php"; ?>