<?php
use Bono\App;
use \ROH\BonoComponent\PlainForm;

$app = App::getInstance();
$c = $app->controller;
$f = new PlainForm($c->clazz, array());
?>

<h2><?php echo $c->clazz ?></h2>

<?php $entry = ($entry instanceof \Norm\Model) ? $entry->toArray() : $entry ?>
<form action="" method="POST">
    <fieldset>
        <?php echo $f->renderFields(@$entry) ?>
    </fieldset>
    <div class="row">
        <input type="submit" value="Save" class="button">
        <a href="<?php echo \Bono\Helper\URL::site($c->getBaseUri()) ?>" class="button">Back to List</a>
    </div>

</form>