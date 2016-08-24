<select id="wl-bulk" disabled="disabled" class="form-control">
	<option value="" selected="selected"><?=t('Items Selected')?></option>
    <option value="hide"><?=t('Hide')?></option>
    <option value="show"><?=t('Show')?></option>
    <option value="delete"><?=t('Delete')?></option>
</select>
<label style="font-weight: normal">
	<input type="checkbox" id="wl-showhidden" />
	<?=t('Show hidden warnings')?>
</label>