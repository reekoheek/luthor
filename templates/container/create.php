<?php
use Bono\App;
use \ROH\BonoComponent\PlainForm;

$app = App::getInstance();
$c = $app->controller;
$f = new PlainForm($c->clazz, array());

?>

<h2><?php echo $c->clazz ?></h2>

<form action="" method="POST" enctype="multipart/form-data">
    <fieldset>
        <div class="row">
            <div class="span-12">
                <label>Template*</label>
                <select name="template">
                    <option value="">---</option>
                    <?php foreach($_templates as $id => $template): ?>
                    <option value="<?php echo $id ?>" <?php echo @$_POST['template'] == $id ? 'selected' : '' ?>><?php echo $template ?></option>
                    <?php endforeach ?>
                </select>
            </div>
        </div>
        <?php echo $f->renderFields(@$entry) ?>
    </fieldset>
    <div class="row">
        <input type="submit" value="Save" class="button">
        <a href="<?php echo \Bono\Helper\URL::site($c->getBaseUri()) ?>" class="button">Back to List</a>
    </div>
</form>