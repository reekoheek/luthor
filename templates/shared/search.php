<?php
use \Bono\Helper\URL;
?>

<div>
    <a href="<?php echo URL::site($_controller->getBaseUri().'/null/create') ?>" class="button">Add</a>
</div>

<table class="table table-nowrap table-stripped">
    <thead>
        <tr class="grid-head-row">
            <?php foreach ($_schema as $field): ?>
            <th><?php echo $field['label'] ?></th>
            <?php endforeach ?>

            <?php if (isset($_actions)): ?>
                <th style="width:1px">&nbsp;</th>
            <?php endif ?>
        </tr>
    </thead>
    <tbody>
        <?php if (empty($entries)): ?>
        <tr>
            <td colspan="<?php echo (count($_schema) + 1) ?>" style="text-align: center">No row available</td>
        </tr>
        <?php else: ?>
        <?php foreach ($entries as $key => $entry): ?>
        <tr>
            <?php foreach ($_schema as $field): ?>
            <td><?php echo $field->cell(@$entry[$field['name']]) ?></td>
            <?php endforeach ?>
            <?php if (isset($_actions)): ?>
            <td>
                <?php foreach ($_actions as $name => $action): ?>
                    <?php echo \App\Helper\SCRUD::actionButton($name, $action, $entry) ?>
                <?php endforeach ?>
            </td>
            <?php endif ?>
        </tr>
        <?php endforeach ?>
        <?php endif ?>
    </tbody>
</table>


