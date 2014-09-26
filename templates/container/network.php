<h2><?php echo f('controller.name') ?> Networks (<?php echo $entry->format() ?>)</h2>

<p>
    <a href="<?php echo f('controller.url') ?>" class="btn btn-default">Back</a>
</p>

<div class="table-placeholder">

    <table class="table table-striped table-bordered">
        <thead>
            <tr>
                <th>Data</th>
                <th>&nbsp;</th>
            </tr>
        </thead>
        <tbody>

            <?php if (count($entry['networks'])): ?>
            <?php foreach($entry['networks'] as $networkIndex => $network): ?>

            <tr>
                <td>
                    <?php foreach($network as $k => $v): ?>
                        <?php echo '<label style="width: 80px">'.$k.'</label> : '.$v."<br>\n" ?>
                    <?php endforeach ?>
                </td>
                <td>
                    <a href="<?php echo URL::current().'/'.$networkIndex.'/remove' ?>">[delete]</a>
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

<h3>Create new network</h3>

<form method="post" role="form">

    <div class="form-group">
        <label>Type</label>
        <input type="text" class="form-control" placeholder="none, empty, veth, vlan, macvlan, phys" name="type">
    </div>

    <div class="form-group">
        <label>Flags</label>
        <input type="text" class="form-control" placeholder="up" name="flags">
    </div>

    <div class="form-group">
        <label>Link</label>
        <input type="text" class="form-control" placeholder="virbr0, lxcbr0, eth0" name="link">
    </div>

    <div class="form-group">
        <label>Name</label>
        <input type="text" class="form-control" placeholder="eth0, eth1, eth2" name="name">
    </div>

    <div class="form-group">
        <label>HW Address</label>
        <input type="text" class="form-control" placeholder="xx:xx:xx:xx:xx:xx" name="hwaddr" id="hwaddr">
    </div>

    <p>
        <input type="submit" value="Save" class="btn btn-primary">
        <input type="reset" class="btn btn-default">
    </p>

</form>

<script type="text/javascript">
function genMAC(){
    var hexDigits = "0123456789ABCDEF";
    var macAddress="";
    for (var i=0; i<6; i++) {
        macAddress+=a=hexDigits.charAt(Math.round(Math.random()*15));
        macAddress+=b=hexDigits.charAt(Math.round(Math.random()*15));
        if (i != 5) macAddress+=":";
    }

    return macAddress;
}

$(function() {
    $('#hwaddr').val(genMAC());
});
</script>