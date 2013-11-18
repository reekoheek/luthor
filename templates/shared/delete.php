<h2><?php echo $_controller->clazz ?></h2>

<form action="?confirm" method="POST">
    <fieldset>
        Are you sure want to delete?
    </fieldset>

    <input type="submit" value="OK">
    <a href="<?php echo \Bono\Helper\URL::site($_controller->getBaseUri()) ?>" class="button">Cancel</a>
</form>
