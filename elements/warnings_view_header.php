<select id="wl-visibility" disabled="disabled" class="form-control" style="display: inline-block; width: auto;">
    <option value="0" selected="selected"><?=t('Shown Items')?></option>
    <option value="1"><?=t('Hidden Items')?></option>
</select>
<select id="wl-bulk" disabled="disabled" class="form-control" style="display: inline-block; width: auto;">
	<option value="" selected="selected"><?=t('Items Selected')?></option>
    <option value="hide"><?=t('Hide')?></option>
    <option value="show"><?=t('Show')?></option>
    <option value="delete"><?=t('Delete')?></option>
</select>
<button id="wl-reload" class="btn btn-default" disabled="disabled"><?php echo t('Reload list'); ?></button>
<br>
<label style="font-weight: normal; float: right">
    <input type="checkbox" id="wl-autoreload" disabled="disabled" />
    <?php
    $secs = [];
    foreach ([0.5, 1, 3, 5, 10, 20, 30, 60] as $s) {
        $secs[] = '<a href="#" class="wl-autoreload-interval label '.(($s === 10) ? 'label-primary' : 'label-default').'" data-ms="'.($s * 1000).'" onclick="return false">'.Punic\Unit::format($s, 'second', 'narrow').'</a>';
    }
    echo t('Auto-reload list every %s', implode(' ', $secs));
    ?>
</label>
