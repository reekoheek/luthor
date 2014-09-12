<?php

use \Bono\App;
use \Bono\Helper\URL;
use \ROH\BonoComponent\PlainTable;

$c = App::getInstance()->controller;
$t = new PlainTable($c->clazz, array());
?>

<h2></h2>

<div class="button-group">
    <a href="#" class="button"><?php echo $c->clazz ?></a>
    <a href="<?php echo URL::site($c->getBaseUri().'/null/create') ?>" class="button">Add</a>
</div>
<?php echo $t->show($entries) ?>
