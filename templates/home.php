<?php

use App\Helper\Common;

?>

<div class="row">
    <div class="span-6">
        <label>Uptime</label>
        <?php $v = Common::secsToV($info['uptime']['up']) ?>
        <span class="field">
            <?php foreach($v as $key => $value): ?>
                <?php if ($value): ?>
                    <?php echo $value.($key[0]) ?>
                <?php endif ?>
            <?php endforeach ?>
        </span>
    </div>
    <div class="span-6">
        <label>Idle</label>
        <?php $v = Common::secsToV($info['uptime']['idle']) ?>
        <span class="field">
            <?php foreach($v as $key => $value): ?>
                <?php if ($value): ?>
                    <?php echo $value.($key[0]) ?>
                <?php endif ?>
            <?php endforeach ?>
        </span>
    </div>
</div>

<div class="row">
    <?php $count = count($info['cpus']) - 1 ?>
    <?php foreach($info['cpus'] as $index => $cpu): ?>
        <?php if ($index !== 'cpu'): ?>
            <div class="span-<?php echo (12 / $count) ?>">
                <label><?php echo $cpu['name'] ?></label>
                <span class="field" style="text-align: right"><?php echo sprintf('%3.2f', $cpu['usage'] * 100) ?>%</span>
            </div>
        <?php endif ?>
    <?php endforeach ?>
</div>

<div class="row">
    <div class="span-4">
        <label>Mem Usage</label>
        <span class="field" style="text-align: right">
        <?php echo sprintf('%.2f', $info['mem']['used'] / $info['mem']['total'] * 100) ?>%
        </span>
    </div>
    <div class="span-4">
        <label>Cached Usage</label>
        <span class="field" style="text-align: right">
        <?php echo sprintf('%.2f', $info['mem']['cached'] / $info['mem']['total'] * 100) ?>%
        </span>
    </div>
    <div class="span-4">
        <label>Total Usage</label>
        <span class="field" style="text-align: right">
        <?php echo sprintf('%.2f', ($info['mem']['used'] + $info['mem']['cached']) / $info['mem']['total'] * 100) ?>%
        </span>
    </div>
</div>

<div class="row">
    <div class="span-3">
        <label>Disk Size</label>
        <span class="field" style="text-align: right"><?php echo $info['disk']['size'] ?></span>
    </div>
    <div class="span-3">
        <label>Disk Used</label>
        <span class="field" style="text-align: right"><?php echo $info['disk']['used'] ?></span>
    </div>
    <div class="span-3">
        <label>Disk Available</label>
        <span class="field" style="text-align: right"><?php echo $info['disk']['available'] ?></span>
    </div>
    <div class="span-3">
        <label>Disk %</label>
        <span class="field" style="text-align: right"><?php echo $info['disk']['percent'] ?></span>
    </div>
</div>

