<?php
use Bono\App;
use \ROH\BonoComponent\PlainForm;

$app = App::getInstance();
$c = $app->controller;
$f = new PlainForm($c->clazz, array());
?>
<h2><?php echo $c->clazz ?></h2>

<fieldset>
    <?php echo $f->renderReadonlyFields(@$entry) ?>
</fieldset>
<div class="row">
<a href="<?php echo \Bono\Helper\URL::site($c->getBaseUri()) ?>" class="button">Back to List</a>
</div>