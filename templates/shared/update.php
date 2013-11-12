<?php $entry = ($entry instanceof \Norm\Model) ? $entry->toArray() : $entry ?>
<form action="" method="POST">
    <fieldset>
        <legend>Container</legend>
        <?php foreach ($_schema as $field): ?>
        <div class="row">
            <div class="span-12">
                <label><?php echo $field['label'] ?></label>
                <?php echo $field->input(@$entry[$field['name']], @$entry) ?>
            </div>
        </div>
        <?php endforeach ?>
    </fieldset>
    <div class="row">
        <input type="submit" value="Save" class="button">
        <a href="<?php echo \Bono\Helper\URL::site($_controller->getBaseUri()) ?>" class="button">Back to List</a>
    </div>

</form>