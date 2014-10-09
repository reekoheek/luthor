<h2><?php echo ROH\Util\Inflector::pluralize(f('controller.name')) ?></h2>

<p>
    <a href="<?php echo f('controller.url', '/null/create') ?>" class="btn btn-primary">Create</a>
    <a href="<?php echo f('controller.url', '/null/populate') ?>" class="btn btn-default">Populate</a>
</p>

<div class="table-placeholder">

    <table class="table table-striped table-bordered">
        <thead>
            <tr>
                <th>Name</th>
                <th>UUID</th>
                <th>State</th>
                <th>Autostart</th>
                <th>&nbsp;</th>
            </tr>
        </thead>
        <tbody>

            <?php if (count($entries)): ?>
            <?php foreach($entries as $entry): ?>

            <tr>
                <td>
                    <a href="<?php echo f('controller.url', '/'.$entry['$id']) ?>">
                    <?php echo $entry->format('plain', 'name') ?>
                    </a>
                </td>
                <td>
                    <a href="<?php echo f('controller.url', '/'.$entry['$id']) ?>">
                    <?php echo $entry->format('plain', 'uuid') ?>
                    </a>
                </td>
                <td>
                    <a href="<?php echo f('controller.url', '/'.$entry['$id']) ?>">
                    <?php echo $entry->format('plain', 'state') ?>
                    </a>
                </td>
                <td>
                    <a href="<?php echo f('controller.url', '/'.$entry['$id']) ?>">
                    <?php echo $entry->format('plain', 'autostart') ?>
                    </a>
                </td>
                <td>
                    <a href="<?php echo f('controller.url', '/'.$entry['$id'].'/start') ?>">[start]</a>
                    <a href="<?php echo f('controller.url', '/'.$entry['$id'].'/stop') ?>">[stop]</a>
                    <a href="<?php echo f('controller.url', '/'.$entry['$id'].'/autostart') ?>">[autostart]</a>
                    <a href="<?php echo f('controller.url', '/'.$entry['$id'].'/delete') ?>">[delete]</a>
                </td>
            </tr>

            <?php endforeach ?>
            <?php else: ?>

            <tr>
                <td colspan="100">no record!</td>
            </tr>

            <?php endif ?>

        </tbody>
    </table>
</div>