<div class="btn-toolbar pull-right">
    <div class="btn-group">
        <button type="button" class="btn btn-success wl-visibility" data-visibility="visible" disabled="disabled" title="<?=t('View visible warnings')?>"><i class="fa fa-eye"></i></button>
        <button type="button" class="btn btn-default wl-visibility" data-visibility="hidden" disabled="disabled" title="<?=t('View hidden warnings')?>"><i class="fa fa-eye-slash"></i></button>
    </div>
    <div class="btn-group">
        <button type="button" class="btn btn-default wl-bulk" data-bulk-operation="show" disabled="disabled" title="<?=t('Mark selected warnings as visible')?>"><i class="fa fa-eye"></i></button>
        <button type="button" class="btn btn-default wl-bulk" data-bulk-operation="hide" disabled="disabled" title="<?=t('Mark selected warnings as hidden')?>"><i class="fa fa-eye-slash"></i></button>
        <button type="button" class="btn btn-default wl-bulk" data-bulk-operation="delete" disabled="disabled" title="<?=t('Delete selected warnings')?>"><i class="fa fa-remove"></i></button>
    </div>
    <div class="btn-group">
        <button type="button" class="btn btn-primary" id="wl-reload" title="<?=t('Reload list')?>"><i class="fa fa-refresh"></i></button>
    </div>
</div>
<br />
<div class="btn-toolbar pull-right">
    <div class="btn-group btn-group-xs">
        <button type="button" class="btn btn-default" id="wl-autoreload" title="<?=t('Auto-reload list')?>" disabled="disabled"><i class="fa fa-clock-o"></i></button>
        <?php
        foreach ([0.5, 1, 3, 5, 10, 20, 30, 60] as $s) {
            ?>
            <button type="button" class="btn <?=($s === 10) ? 'btn-primary' : 'btn-default'?> wl-autoreload-interval" data-autoreload-every="<?=$s * 1000?>" style="font-weight: normal"><?=Punic\Unit::format($s, 'second', 'narrow')?></button>
            <?php
        }
        ?>
    </div>
</div>
