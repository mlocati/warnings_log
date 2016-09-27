/* jshint unused:vars, undef:true, browser:true, jquery:true */
/* global ConcreteAjaxRequest, ConcreteAlert */
(function() {

var i18n, actions, tokens;

function textToHtml(text, asList, replace) {
	text = (text === null) ? '' : $.trim(text.toString());
	if (text === '') {
		return '';
	}
	if (!('$div' in textToHtml)) {
		textToHtml.$div = $('<div />');
	}
	var lines = [];
	$.each(text.replace(/\r\n/g, '\n').replace(/\r/g, '\n').split('\n'), function(i, line) {
		if (replace) {
			line = line.replace(replace.search, replace.replace);
		}
		line = textToHtml.$div.text(line).html();
		lines.push(line);
	});
	return asList ? ('<' + asList + '><li>' + lines.join('</li><li>') + '</li></' + asList + '>') : lines.join('<br />');
}

var Autoreloader = (function() {
	var hTimer = null;
	return {
		set: function() {
			if (UI.$autoreload.is(':checked')) {
				if (hTimer === null) {
					hTimer = setTimeout(
						function() {
							hTimer = null;
							List.reload(Autoreloader.set);
						},
						5000
					);
				}
			} else {
				if (hTimer !== null) {
					clearTimeout(hTimer);
					hTimer = null;
				}
			}
		}
	};
})();

var UI = (function() {
	var disabled = false;
	function setDisabled($i, disabled)
	{
		if (disabled) {
			$i.attr('disabled', 'disabled');
		} else {
			$i.removeAttr('disabled');
		}
	}
	function refresh()
	{
		var d;
		setDisabled(UI.$visibility, disabled);
		if (disabled)  {
			d = true;
		} else {
			d = UI.$tbody.find('input[type="checkbox"]:checked').length === 0;
		}
		setDisabled(UI.$bulk, d);
		setDisabled(UI.$reload, disabled);
		setDisabled(UI.$autoreload, disabled);
	}
	return {
		disable: function () {
			disabled = true;
			refresh();
		},
		enable: function () {
			disabled = false;
			refresh();
		},
		isDisabled: function() {
			return disabled;
		},
		refresh: refresh,
		initialize: function() {
			UI.$tbody = $('#wl-table tbody');
			UI.$visibility = $('#wl-visibility');
			UI.$bulk = $('#wl-bulk');
			UI.$reload = $('#wl-reload');
			UI.$autoreload = $('#wl-autoreload');
			UI.$bulk.on('change', function() {
				Bulk.apply();
			});
			$('#wl-table thead input[type="checkbox"]').on('click', function() {
				var b = $(this).is(':checked');
				UI.$tbody.find('input[type="checkbox"]').prop('checked', b);
				UI.refresh();
			});
			$('#wl-table thead a[data-sortby]').each(function() {
				var $a = $(this);
				List.sortLinks[$a.data('sortby')] = $a;
				$a.on('click', function(e) {
					List.sortBy($a.data('sortby'));
					e.preventDefault();
				});
			});
			UI.$tbody.on('change', UI.refresh);
			UI.$visibility.on('change', function() {
				List.populate();
			});
			UI.$reload.on('click', function() {
				List.reload();
			});
			UI.$autoreload.on('click', function() {
				Autoreloader.set();
			});
			delete UI.initialize;
		}
	};
})();

function Item(d) {
	var me = this;
	var UI = me.UI = {};
	$.each(d, function(k, v) {
		me[k] = v;
	});
	UI.$row = $('<tr />')
		.append('<td><span class="ccm-search-results-checkbox"><input type="checkbox" class="ccm-flat-checkbox" ></span></td>')
		.append($('<td />').text(me.code))
		.append($('<td />').text((me.file === '') ? '' : (me.file + (me.line ? (':' + me.line) : ''))))
		.append($('<td />').html(textToHtml(me.message)))
		.append(UI.$numSeen = $('<td />').text(me.numSeen.toString()))
		.append($('<td />').text(me.firstSeen__view))
		.append(UI.$lastSeen = $('<td />').text(me.lastSeen__view))
		.append($('<td />')
			.append($('<a href="#"><i class="fa fa-eye"></i>')
				.on('click', function() {
					var $dlg = $('<div />')
						.append($('<code />').html(textToHtml(me.callStack, 'ol', {search: /^#\d+\s+/, replace: ''})))
					;
					$dlg.dialog({
						modal: true,
						title: i18n.CallStact,
						width: Math.min(Math.max($(window).width() * 0.85, 200), 1500) + 'px',
						buttons: [
							{
								text: i18n.Close,
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
	UI.$row.data('Item', me);
}
Item.prototype = {
	refresh: function(d) {
		if (this.numSeen !== d.numSeen) {
			this.numSeen = d.numSeen;
			this.UI.$numSeen.text(this.numSeen.toString());
			this.blink(this.UI.$numSeen);
		}
		this.lastSeen = d.lastSeen;
		if (this.lastSeen__view !== d.lastSeen__view) {
			this.lastSeen__view = d.lastSeen__view;
			this.UI.$lastSeen.text(this.lastSeen__view.toString());
			this.blink(this.UI.$lastSeen);
		}
	},
	shownInTable: function() {
		return this.hide === (UI.$visibility.val() === '1');
	},
	blink: function($i) {
		if (!this.shownInTable()) {
			return;
		}
		$i.animate({backgroundColor: '#f00'}, 500).animate({backgroundColor: 'transparent'}, 500);
	}
};
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
		UI.$tbody.find('>tr').each(function() {
			$(this).detach();
		});
		var hidden = UI.$visibility.val() === '1';
		$.each(this.items, function() {
			if (this.shownInTable()) {
				UI.$tbody.append(this.UI.$row);
			}
		});
		UI.refresh();
	},
	reload: function(cb) {
		if (UI.isDisabled()) {
			if (cb) {
				cb();
			}
			return;
		}
		UI.disable();
		var done = function() {
			UI.enable();
			if (cb) {
				cb();
			}
		};
		new ConcreteAjaxRequest({
			url: actions.get_warnings_list,
			data: {
				ccm_token: tokens.get_warnings_list,
			},
			success: function(items) {
				List.refreshItems(items);
				done();
			},
			error: function(r) {
				var msg = r.responseText;
				if (r.responseJSON) {
					var json = r.responseJSON;
					if ($.isArray(json.errors) && json.errors.length > 0 && typeof json.errors[0] === 'string') {
						msg = json.errors.join('\n');
					} else if (typeof json.error === 'string' && json.error !== '') {
						msg = json.error;
					}
				}
				var $div = $('<div id="ccm-popup-alert" class="ccm-ui"><div id="ccm-popup-alert-message" class="alert alert-danger">' + textToHtml(msg) + '</div></div>');
				$div.dialog({
					title: i18n.Error,
					//width: 500,
					//height: 'auto',
					modal: true,
					close: function() {
						$div.remove();
						done();
					}
				});
			}
		});
	},
	refreshItems: function(newData) {
		var remove = [], refresh = [], add = [];
		$.each(newData, function() {
			var data = this, item = null;
			$.each(List.items, function() {
				if (this.id === data.id) {
					item = this;
					return false;
				}
			});
			if (item === null) {
				add.push(data);
			} else {
				refresh.push({item: item, data: data});
			}
		});
		$.each(List.items, function() {
			var item = this, data = null;
			$.each(newData, function() {
				if (this.id === item.id) {
					data = this;
					return false;
				}
			});
			if (data === null) {
				remove.push(item);
			}
		});
		$.each(add, function() {
			var item = new Item(this);
			List.items.push(item);
			if (item.shownInTable()) {
				UI.$tbody.append(item.UI.$row.hide());
				item.UI.$row.show('fast');
			}
		});
		$.each(refresh, function() {
			this.item.refresh(this.data);
		});
		$.each(remove, function() {
			var item = this;
			List.remove(item);
			item.UI.$row.hide('fast', function() {
				item.UI.$row.remove();
			});
		});
	}
};
var Bulk = {
	apply: function() {
		var operation = UI.$bulk.val();
		UI.$bulk.prop('selectedIndex', 0);
		if (operation === '' || UI.isDisabled()) {
			return;
		}
		var items = [], itemIDs = [];
		UI.$tbody.find('input[type="checkbox"]:checked').closest('tr').each(function() {
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
		UI.disable();
		var ar;
		ar = new ConcreteAjaxRequest({
			url: actions.bulk_operation,
			data: {
				ccm_token: tokens.bulk_operation,
				operation: operation,
				itemIDs: itemIDs,
			},
			success: function(msg) {
				UI.enable();
				$.each(items, function() {
					this.UI.$row.find('input[type="checkbox"]').prop('checked', false);
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
			},
			error: function(r) {
				UI.enable();
				ar.error(r, ar);
			}
		});
	}
};

window.warningsLogInitialize = function(d) {
	i18n = d.i18n;
	actions = d.actions;
	tokens = d.tokens;

	UI.initialize();

	$.each(d.warningsList, function() {
		List.items.push(new Item(this));
	});
	List.populate();
	delete window.warningsLogInitialize;
};

})();
