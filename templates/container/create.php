<h2><?php echo $_controller->clazz ?></h2>

<form action="" method="POST" enctype="multipart/form-data">
    <fieldset>
        <div class="row">
            <div class="span-12">
                <label>Template*</label>
                <select name="template">
                    <option value="">---</option>
                    <?php foreach($_templates as $id => $template): ?>
                    <option value="<?php echo $id ?>"><?php echo $template ?></option>
                    <?php endforeach ?>
                </select>
            </div>
        </div>
        <?php echo $_form->renderFields(@$entry) ?>
    </fieldset>
    <div class="row">
        <input type="submit" value="Save" class="button">
        <a href="<?php echo \Bono\Helper\URL::site($_controller->getBaseUri()) ?>" class="button">Back to List</a>
    </div>
</form>