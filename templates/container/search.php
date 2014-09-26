<h2><?php echo ROH\Util\Inflector::pluralize(f('controller.name')) ?></h2>

<p>
    <a href="<?php echo f('controller.url', '/null/create') ?>" class="btn btn-primary">Create</a>
    <a href="<?php echo f('controller.url', '/null/populate') ?>" class="btn btn-default">Populate</a>
</p>

<style type="text/css">
    .indicator {
        width: 15px;
        height: 15px;
        border-radius: 50%;
        display: inline-block;
        background-color: yellow;
        box-shadow: 0px 0px 5px #ccc;
    }

    .indicator.off {
        background-color: red;
    }

    .indicator.on {
        background-color: green;
    }
</style>

<div class="table-placeholder">

    <table class="table table-striped table-bordered">
        <thead>
            <tr>
                <th>&nbsp;</th>
                <th>PID</th>
                <th>IP</th>
                <th>Mem</th>
                <th>&nbsp;</th>

            </tr>
        </thead>
        <tbody>

            <?php if (count($entries)): ?>
            <?php foreach($entries as $entry): ?>

            <tr>

                <td>
                    <a href="<?php echo f('controller.url', '/'.$entry['$id']) ?>">
                        <div class="indicator <?php echo ($entry->format('plain', 'state') === 'RUNNING') ? 'on' : 'off' ?>"></div>
                        <?php echo $entry['name'] ?>
                    </a>
                </td>

                <td><?php echo $entry->format('plain', 'pid') ?></td>
                <td><?php echo $entry->format('plain', 'ip') ?></td>
                <td><?php echo $entry->format('plain', 'memory_use') ?></td>

                <td>
                    <a href="<?php echo f('controller.url', '/'.$entry['$id'].'/start') ?>">[start]</a>
                    <a href="<?php echo f('controller.url', '/'.$entry['$id'].'/stop') ?>">[stop]</a>
                    <a href="<?php echo f('controller.url', '/'.$entry['$id'].'/network') ?>">[network]</a>
                    <a href="<?php echo f('controller.url', '/'.$entry['$id'].'/attach') ?>">[attach]</a>
                    <a href="<?php echo f('controller.url', '/'.$entry['$id'].'/chpasswd') ?>">[chpasswd]</a>
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