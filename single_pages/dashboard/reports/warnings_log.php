<?php
defined('C5_EXECUTE') or die('Access Denied.');

?>

<div class="ccm-dashboard-content-full">
	<table class="ccm-search-results-table" id="wl-table">
		<thead>
			<tr>
				<th width="1">
					<span class="ccm-search-results-checkbox">
						<input type="checkbox" class="ccm-flat-checkbox">
					</span>
				</th>
				<th><a href="#" data-sortby="code"><?=t('Code')?></a></th>
				<th><a href="#" data-sortby="file,line"><?=t('File')?></a></th>
				<th><a href="#" data-sortby="message"><?=t('Message')?></a></th>
				<th><a href="#" data-sortby="numSeen"><?=t('# Seen')?></a></th>
				<th><a href="#" data-sortby="firstSeen"><?=t('First Seen')?></a></th>
				<th class="ccm-results-list-active-sort-desc"><a href="#" data-sortby="lastSeen"><?=t('Last Seen')?></a></th>
				<th><?=t('Call stack')?></th>
			</tr>
		</thead>
		<tbody>
		</tbody>
	</table>
</div>
<script>$(document).ready(function() {
window.warningsLogInitialize(<?=json_encode([
    'i18n' => [
        'CallStact' => t('Call Stack'),
        'Close' => t('Close'),
        'Error' => t('Error'),
    ],
    'actions' => [
        'bulk_operation' => $view->action('bulk_operation'),
        'get_warnings_list' => $view->action('get_warnings_list'),
    ],
    'tokens' => [
        'bulk_operation' => $token->generate('bulk_operation'),
        'get_warnings_list' => $token->generate('get_warnings_list'),
    ],
    'warningsList' => $warningsList
])?>);
});</script>
