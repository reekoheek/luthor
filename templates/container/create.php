<form action="" method="POST" enctype="multipart/form-data">
    <fieldset>
        <legend>Container</legend>
        <div class="row">
            <div class="span-12">
                <label><?php echo $_('Name') ?></label>
                <input type="text" name="name" value="<?php echo @$entry['name'] ?>" />
            </div>
        </div>
        <div class="row">
            <div class="span-12">
                <label><?php echo $_('Template') ?></label>
                <select name="template">
                    <?php foreach($_templates as $id => $template): ?>
                    <option value="<?php echo $id ?>"><?php echo $template ?></option>
                    <?php endforeach ?>
                </select>
            </div>
        </div>
    </fieldset>
    <div class="row">
        <input type="submit" value="Save" class="button">
        <a href="<?php echo \Bono\Helper\URL::site($_controller->getBaseUri()) ?>" class="button">Back to List</a>
    </div>
</form>