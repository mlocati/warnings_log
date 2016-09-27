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
    <?=t('Auto-reload list')?>
</label>
