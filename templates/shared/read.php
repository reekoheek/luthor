<fieldset>
    <legend>Container</legend>
    <?php foreach($entry as $key => $value): ?>
    <div class="row">
        <div class="span-12">
            <label><?php echo $key ?></label>
            <span class="field"><?php echo $value ?></span>
        </div>
    </div>
    <?php endforeach ?>
</fieldset>
<div class="row">
    <a href="<?php echo \Bono\Helper\URL::site($_controller->getBaseUri()) ?>" class="button">Back to List</a>
</div>