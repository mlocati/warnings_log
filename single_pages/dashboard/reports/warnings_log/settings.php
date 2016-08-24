<?php
defined('C5_EXECUTE') or die('Access Denied.');

/* @var Concrete\Core\Validation\CSRF\Token $token */
/* @var Concrete\Core\Form\Service\Form $form */
/* @var string $default_provider */
/* @var string $warningslog_provider */
/* @var string $current_provider */
?>


<form method="post" class="ccm-dashboard-content-form" action="<?=$view->action('update_settings')?>">
	<?=$token->output('update_settings')?>

	<fieldset>
		<legend><?=t('Error Handling')?></legend>
		<div class="form-group">
			<div class="radio">
				<label>
					<?=$form->radio('errorhandler', 'default', $current_provider === $default_provider)?>
					<?=t('Use default error handler')?>
				</label>
			</div>
			<div class="radio">
				<label>
					<?=$form->radio('errorhandler', 'warningslog', $current_provider === $warningslog_provider)?>
					<?=t('Activate Warnings Log')?>
				</label>
			</div>
			<?
			if (!in_array($current_provider, [$default_provider, $warningslog_provider], true)) {
			    ?>
			    <div class="radio">
			    	<label>
			    		<?=$form->radio('errorhandler', 'current', true)?>
			    		<?=t('Custom provider (%s)', h($current_provider))?>
			    	</label>
			    </div>
				<?php
			}
			?>
		</div>
	</fieldset>

	<div class="alert alert-info">
		<p><?=t('Since the error handling is initialized at an early stage of the execution, warnings may be intercepted even before the database is configured (and even before concrete5 is installed).')?></p>
		<p><?=t('For these reasons, the database used to save the errors/warnings is not the one used by this concrete5 installation, but it\'s a SQLite file called %1$s created in your %2$s directory', '<code>WarningsLog.sqlite</code>', '<code>application/files</code>')?></p>
	</div>

	<div class="ccm-dashboard-form-actions-wrapper">
		<div class="ccm-dashboard-form-actions">
			<button class="pull-right btn btn-primary" type="submit" ><?=t('Save')?></button>
		</div>
	</div>
</form>
