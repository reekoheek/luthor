<h2><?php echo $_controller->clazz ?></h2>

<form action="" method="POST">
    <fieldset>
        <?php echo $_form->renderFields(@$entry) ?>
    </fieldset>
    <div class="row">
        <input type="submit" value="Save" class="button">
        <a href="<?php echo \Bono\Helper\URL::site($_controller->getBaseUri()) ?>" class="button">Back to List</a>
    </div>
</form>