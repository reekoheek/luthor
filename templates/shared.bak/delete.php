<?php
use Bono\App;

$app = App::getInstance();
$c = $app->controller;

?>
<h2><?php echo $c->clazz ?></h2>

<form action="?confirm" method="POST">
    <fieldset>
        Are you sure want to delete?
    </fieldset>

    <input type="submit" value="OK">
    <a href="<?php echo \Bono\Helper\URL::site($c->getBaseUri()) ?>" class="button">Cancel</a>
</form>
