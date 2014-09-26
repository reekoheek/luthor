<h2>Attach (<?php echo $entry->format() ?>)</h2>

<p>
    <a href="<?php echo URL::current().'/../..' ?>" class="btn btn-default">Back</a>
</p>
<div>
    <form method="get">
        <div class="form-group">
            <input class="form-input" type="text" name="cmd" value="<?php echo $app->request->get('cmd') ?>" id="cmd">
        </div>
        <div class="form-group">
            <pre class="field form-input"><?php echo @$result ?></pre>
        </div>
    </form>
</div>

<script type="text/javascript">
    $(function() {
        $('#cmd').focus();
    });
</script>