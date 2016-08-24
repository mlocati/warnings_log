<?php
defined('C5_EXECUTE') or die('Access Denied.');

$fs = new \Illuminate\Filesystem\Filesystem();

$greenIcon = '<i class="fa fa-check" style="color: green"></i>';
$redIcon = '<i class="fa fa-exclamation-circle" style="color: red"></i>';
$someRed = false;

?>
<table class="table" id="wl-table">
	<col>
	<col width="100%">
	<tbody>
    	<?php
    	$appSrcWritable = $fs->isDirectory(DIR_APPLICATION.'/src') && $fs->isWritable(DIR_APPLICATION.'/src');
    	if (!$appSrcWritable) {
    	    $someRed = true;
    	}
    	?>
    	<tr>
    		<td><?php echo $appSrcWritable ? $greenIcon : $redIcon; ?></td>
    		<td><?php echo t('Folder %s is writable', '<code>application/src</code>'); ?></td>
    		<td><?php if (!$appSrcWritable) { ?><i class="fa fa-question-circle launch-tooltip" title="<?= h(t("This package must be able to write to the %s directory.", 'application/src')); ?>"></i><?php } ?></td>
    	</tr>
    	<?php
    	$pdoInstalled = class_exists('PDO') && is_callable('PDO::getAvailableDrivers');
    	if (!$pdoInstalled) {
    	    $someRed = true;
    	}
    	?>
		<tr>
			<td><?php echo $pdoInstalled ? $greenIcon : $redIcon; ?></td>
			<td><?php echo t('%s PHP extension installed', '<code>PDO</code>'); ?></td>
			<td><?php if (!$pdoInstalled) { ?><i class="fa fa-question-circle launch-tooltip" title="<?= h(t("This package needs the %s PHP extension.", 'PDO')); ?>"></i><?php } ?></td>
		</tr>
		<?php
		if ($pdoInstalled) {
		    $pdoSQLiteInstalled = in_array('sqlite', PDO::getAvailableDrivers(), true);
		    if (!$pdoSQLiteInstalled) {
		        $someRed = true;
		    }
		    ?>
			<tr>
				<td><?php echo $pdoSQLiteInstalled ? $greenIcon : $redIcon; ?></td>
				<td><?php echo t('%s PDO driver installed', '<code>SQLite</code>'); ?></td>
				<td><?php if (!$pdoSQLiteInstalled) { ?><i class="fa fa-question-circle launch-tooltip" title="<?= h(t("This package needs the %s PDO driver.", 'SQLite')); ?>"></i><?php } ?></td>
			</tr>
			<?php
        }
    	?>
	</tbody>
</table>
<?php
if ($someRed) {
    ?>
    <div class="alert alert-warning">
    	<p><?= t("Before installing this package, please fix all the above errors."); ?></p>
    	<a href="#" class="btn btn-primary" onclick="window.location.reload(); return false"><?= t('Repeat checks'); ?></a>
    </div>
    <script>
    $(document).ready(function() {
        $('#wl-table')
        	.closest('form')
        	.on('submit', function() { return false; })
        	.find('input[type="submit"]')
        		.attr('disabled', 'disabled')
        ;
    });
    </script>
    <input type="checkbox" style="visibility: hidden" required="required" />
    <?php
}
