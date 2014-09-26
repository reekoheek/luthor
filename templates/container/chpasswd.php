<h2>Change password (<?php echo @$entry->format() ?>)</h2>

<form action="" method="POST">
    <div class="form-group">
        <label>New password</label>
        <input type="password" name="password" class="form-control">
    </div>
    <div class="form-group">
        <label>Retype password</label>
        <input type="password" name="password_confirmation" class="form-control">
    </div>

    <p>
        <input type="submit" value="Change" class="btn btn-default">
    </p>

</form>