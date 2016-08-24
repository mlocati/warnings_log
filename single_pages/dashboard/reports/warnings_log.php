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

var $tbody = $('#wl-table tbody');
function textToHtml(text) {
	text = (text === null) ? '' : text.toString();
	if (text === '') {
		return '';
	}
	if (!('$div' in textToHtml)) {
		textToHtml.$div = $('<div />');
	}
	var lines = [];
	$.each(text.replace(/\r\n/g, '\n').replace(/\r/g, '\n').split('\n'), function(i, line) {
		lines.push(textToHtml.$div.text(line).html());
	});
	return lines.join('<br />');
}
function Item(d) {
	var me = this;
	$.each(d, function(k, v) {
		me[k] = v;
	});
	me.$row = $('<tr />')
		.append('<td><span class="ccm-search-results-checkbox"><input type="checkbox" class="ccm-flat-checkbox" ></span></td>')
		.append($('<td />').text(me.code))
		.append($('<td />').text((me.file === '') ? '' : (me.file + (me.line ? (':' + me.line) : ''))))
		.append($('<td />').html(textToHtml(me.message)))
		.append($('<td />').text(me.numSeen.toString()))
		.append($('<td />').text(me.firstSeen__view))
		.append($('<td />').text(me.lastSeen__view))
		.append($('<td />')
			.append($('<a href="#"><i class="fa fa-eye"></i>')
				.on('click', function() {
					var $dlg = $('<div />')
						.append($('<code />').html(textToHtml(me.callStack)))
					;
					$dlg.dialog({
						modal: true,
						title: <?=json_encode(t('Call Stack'))?>,
						width: Math.min(Math.max($(window).width() * .85, 200), 1500) + 'px',
						buttons: [
							{
								text: <?=json_encode(t('Close'))?>,
								click: function() {
									$dlg.dialog('close');
								}
							}
						],
						close: function() {
							$dlg.remove();
						}
					});
				})
			)
			.attr('title', me.callStack)
		)
	;
	me.$row.data('Item', me);
}
var List = {
	sortLinks: {},
	sortedBy: 'lastSeen',
	sortedByDirection: 'desc',
	items: [],
	sortBy: function(f) {
		this.sortLinks[this.sortedBy].closest('th').removeAttr('class');
		if (f === this.sortedBy) {
			this.sortedByDirection = (this.sortedByDirection === 'desc') ? 'asc' : 'desc';
		} else {
			this.sortedBy = f;
			this.sortedByDirection = 'asc';
		}
		f = f.split(',');
		var sign = (this.sortedByDirection === 'desc') ? 1 : -1;
		this.items.sort(function(a, b) {
			var i, r, af, bf;
			for(r = 0, i = 0; r === 0, i < f.length; i++) {
				af = a[f[i]];
				bf = b[f[i]];
				if (typeof af === 'string' && typeof bf === 'string') {
					af = af.toLowerCase();
					bf = bf.toLowerCase();
				}
				if (af < bf) {
					r = sign;
				} else if (af > bf) {
					r = -sign;
				}
			}
			return r;
		});
		this.sortLinks[this.sortedBy].closest('th').addClass('ccm-results-list-active-sort-' + this.sortedByDirection);
		this.populate();
	},
	remove: function(item) {
		var i = $.inArray(item, this.items);
		if (i >= 0) {
			this.items.splice(i, 1);
		}
	},
	populate: function() {
		$tbody.find('>tr').each(function() {
			$(this).detach();
		});
		var hidden = $('#wl-showhidden').is(':checked');
		$.each(this.items, function() {
			if (this.hide === hidden) {
				$tbody.append(this.$row);
			}
		});
		Bulk.updated();
	}
};
var Bulk = {
	updated: function() {
		if ($tbody.find('input[type="checkbox"]:checked').length === 0) {
			$('#wl-bulk').attr('disabled', 'disabled');
		} else {
			$('#wl-bulk').removeAttr('disabled');
		}
	},
	apply: function() {
		var operation = $('#wl-bulk').val();
		$('#wl-bulk').prop('selectedIndex', 0);
		if (operation === '') {
			return;
		}
		var items = [], itemIDs = [];
		$tbody.find('input[type="checkbox"]:checked').closest('tr').each(function() {
			var item = $(this).data('Item'), add = false;
			switch (operation) {
				case 'hide':
					if (item.hide === false) {
						add = true;
					}
					break;
				case 'show':
					if (item.hide === true) {
						add = true;
					}
					break;
				case 'delete':
					add = true;
					break;
			}
			if (add) {
				items.push(item);
				itemIDs.push(item.id);
			}
		});
		if (items.length === 0) {
			return;
		}
        new ConcreteAjaxRequest({
            url: <?=json_encode($view->action('bulk_operation'))?>,
            data: {
                ccm_token:<?=json_encode($token->generate('bulk_operation'))?>,
                operation: operation,
                itemIDs: itemIDs,
            },
            success: function(msg) {
                $.each(items, function() {
                    this.$row.find('input[type="checkbox"]').prop('checked', false);
                    switch (operation) {
                    	case 'hide':
                        	this.hide = true;
                        	break;
                    	case 'show':
                    		this.hide = false;
                    		break;
                    	case 'delete':
                        	List.remove(this);
                        	break;
                    }
                });
                List.populate();
                ConcreteAlert.notify({
                    message: msg
                });
            }
        });
	}
};
$('#wl-bulk').on('change', function() {
	Bulk.apply();
});

$('#wl-table thead input[type="checkbox"]').on('click', function() {
	var b = $(this).is(':checked');
	$tbody.find('input[type="checkbox"]').prop('checked', b);
	Bulk.updated();
});

$('#wl-table thead a[data-sortby]').each(function() {
	var $a = $(this);
	List.sortLinks[$a.data('sortby')] = $a;
	$a.on('click', function(e) {
		List.sortBy($a.data('sortby'));
		e.preventDefault();
	});
});
$tbody.on('change', Bulk.updated);
$.each(<?=json_encode($rows)?>, function() {
	List.items.push(new Item(this));
});
List.populate();
$('#wl-showhidden').on('change', function() {
	List.populate();
});

});</script>
