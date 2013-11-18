<?php
use \Bono\Helper\URL;
?>

<div class="button-group">
    <?php foreach($config as $key => $button): ?>
    <a href="<?php echo URL::site($controller->getBaseUri().'/null/'.$key) ?>" class="button"><?php echo \Reekoheek\Util\Inflector::classify($key) ?></a>
    <?php endforeach ?>
</div>