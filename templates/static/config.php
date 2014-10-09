<table>
    <tbody>
        <?php foreach ($app->config('lxc') as $key => $value): ?>
        <tr>
            <td><?php echo $key ?></td>
            <td><?php echo $value ?></td>
        </tr>
        <?php endforeach ?>
    </tbody>
</table>