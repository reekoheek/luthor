<form action="?confirm" method="POST">
    <p>Are you sure want to delete?</p>

    <input type="submit" value="OK">
    <a href="<?php echo \Bono\Helper\URL::site($_controller->getBaseUri()) ?>" class="button">Cancel</a>
</form>
