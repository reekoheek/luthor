<?php
use \Bono\Helper\URL;
use \Bono\App;

$c = App::getInstance()->controller;

?>
<h2>Change Password</h2>

<form action="" method="POST">
    <fieldset>
        <div class="row">
            <label>New password</label>
            <input type="password" name="password">
        </div>
        <div class="row">
            <label>Retype password</label>
            <input type="password" name="password_confirmation">
        </div>
    </fieldset>
    <div class="row">
        <input type="submit" value="Save" class="button">
        <a href="<?php echo URL::site($c->getBaseUri()) ?>" class="button">Back to List</a>
    </div>

</form>