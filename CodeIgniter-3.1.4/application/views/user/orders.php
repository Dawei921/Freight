<?php include VIEWPATH."/frame/partialHead.php"; ?>

<div data-role="page">
    <div data-role="header"><h1>我的运单</h1></div>
    <div data-role="main" class="ui-content">

        <?php if(count($runningOrders)): ?>
            <label>进行中</label>
            <ul>
                <?php foreach ($runningOrders as $order): ?>
                    <?php $orderTime=explode(' ',$order->orderTime); ?>
                    <li>
                        <a data-ajax="false" href="<?php echo site_url('freight/view/'.$order->freightID); ?>">
                            <?php echo $order->beginProvince.' '.$order->beginCity.' --> '.$order->endProvince.' '.$order->endCity.' '.$orderTime[0]; ?>
                        </a>
                        <button>撤消</button>
                    </li>
                <?php endforeach; ?>
            </ul>
        <?php endif; ?>

        <?php if(count($finishiedOrders)): ?>
            <label>已完成</label>
            <ul>
                <?php foreach ($finishiedOrders as $order): ?>
                    <?php $orderTime=explode(' ',$order->orderTime); ?>
                    <li>
                        <a data-ajax="false" href="<?php echo site_url('freight/view/'.$order->freightID); ?>">
                            <?php echo $order->beginProvince.' '.$order->beginCity.' --> '.$order->endProvince.' '.$order->endCity.' '.$orderTime[0]; ?>
                        </a>
                    </li>
                <?php endforeach; ?>
            </ul>
        <?php endif; ?>

    </div>
    <div data-role="footer" class="textCenter"><h1> </h1></div>
</div>

<?php include VIEWPATH."/frame/partialFoot.php"; ?>