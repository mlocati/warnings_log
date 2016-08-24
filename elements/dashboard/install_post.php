<?php
use Concrete\Core\Support\Facade\Url;

defined('C5_EXECUTE') or die('Access Denied.');

?>
<p><?= t("%s has been installed, but it is not yet active.", t('Warnings Log')); ?></p>
<p><?= t('In order to activate it, please go to <a href="%s">this page</a>.', Url::to('/dashboard/reports/warnings_log/settings')); ?></p>
