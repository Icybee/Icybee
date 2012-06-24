/*
 * This file is part of the Icybee package.
 *
 * (c) Olivier Laviale <olivier.laviale@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

(function() {

	var form = $('editor');

	if (!form)
	{
		throw "Unable to get form";

		return;
	}

	var destination = $(form.elements['#destination']);
	var key = $(form.elements['#key']);

	/*
	 * unload warning
	 */

	(function() {

		if (destination && key)
		{
			var base = destination.value + '/' + key.value + '/';

			window.addEvent
			(
				'domready', function()
				{
					var op = new Request.API
					(
						{
							url: base + 'lock'
						}
					);

					( function() { op.put(); }).periodical(30 * 1000);
				}
			);

			window.addEvent
			(
				'unload', function()
				{
					var op = new Request.API
					(
						{
							url: base + 'lock',
							async: false,
							method: 'delete'
						}
					);

					op.send();
				}
			);
		}
		else
		{
			/*
			 * For new entries, we use the core/ping method in order to keep the user's session alive.
			 */

			window.addEvent
			(
				'domready', function()
				{
					var op = new Request.API({ url: 'core/ping' });

					op.send.bind(op).periodical(30 * 1000);
				}
			);
		}

		/*
		 * The following code looks for changes in elements' values between the 'domready' event and
		 * the 'onbeforeunload' event. If there are changes, the user is asked to confirm page unload.
		 */

		function toQueryString(el)
		{
			var elements = el.getElements('[name]');
			var keys = [];
			var values = [];
			var assoc = {};

			elements.each
			(
				function(el)
				{
					if (el.disabled)
					{
						//console.log('el: %a is disabled', el);

						return;
					}

					var key = el.get('name');
					var value = el.get('value');

					keys.push(key);
					values.push(value);

					assoc[key] = value;
				}
			);

			var sorted_keys = keys.slice(0);

			sorted_keys.sort();

			//console.log('elements (%d): %a, active: %a, concat: %s', elements.length, elements, actives, concat);

			//console.log('keys: %a, values: %a', keys, values);

			var sorted_values = {};

			for (var i = 0; i < sorted_keys.length ; i++)
			{
				var key = sorted_keys[i];

				sorted_values[key] = assoc[key];
			}

			var hash = new Hash(sorted_values);

			//console.log('sorted keys: %a, values: %a', sorted_keys, sorted_values);
			//console.log('queryString: %s', hash.toQueryString());

			return hash.toQueryString();
		}

		var values_init=null;

		window.addEvent
		(
			'domready', function()
			{
				values_init = toQueryString(form);
			}
		);

		window.addEvent
		(
			'load', function()
			{
				values_init = toQueryString(form);
			}
		);

		var skip = false;

		window.onbeforeunload = function()
		{
			if (skip)
			{
				skip = false;

				return;
			}

			var values_now = toQueryString(form);

			//console.log('values_now: %s', values_now);

			if (values_init == values_now)
			{
				return;
			}

			return "Des changements ont été fait sur la page. Si vous changez de page maintenant, ils seront perdus.";
		};

		form.addEvent
		(
			'submit', function(ev)
			{
				skip = true;
				values_init = toQueryString(form);
			}
		);

	})();

	var navSaveModeElement = $(document.body).getElement('.actionbar .record-save-mode')
	, saveModesContainer = $(document.body).getElement('.form-actions .save-mode')
	, saveModes = saveModesContainer ? saveModesContainer.getElements('input[type="radio"]') : []
	, primaryButton = $(document.body).getElement('.form-actions .btn-primary')

	if (navSaveModeElement && saveModes)
	{
		navSaveModeElement.addEvent('click', function(ev) {

			var target = ev.target
			, mode = target.get('data-key')

			if (target.match('.btn-primary:first-child')) {
				ev.stop()
				primaryButton.click()
				return
			}

			if (!mode) return

			ev.stop()

			saveModes.each(function(el) {
				el.checked = (el.value == mode)
			})

			primaryButton.click()
		})

		saveModesContainer.addEvent('click', function(ev) {

			var mode = ev.target.get('value')

			if (!mode) return

			navSaveModeElement.getElements('li').each(function(el) {

				var anchor = el.getElement('a')
				, anchorMode = anchor.get('data-key')

				if (mode == anchorMode)
				{
					el.addClass('active')
					navSaveModeElement.getElement('.btn').set('html', anchor.get('html'))
				}
				else
				{
					el.removeClass('active')
				}
			})
		})
	}

})();


window.addEvent
(
	'domready', function()
	{
		$(document.body).getElements('form.edit div.form-section').each
		(
			function(section)
			{
				var trigger = section.getPrevious();

				if (trigger.tagName != 'H3')
				{
					return;
				}

				function fold()
				{
					var panels = section.getChildren('div.panel');

					trigger.addClass('folded');

//					console.log('panels: %a', panels);

					var summary = new Element('div.form-section-sumary');

					panels.each
					(
						function(panel)
						{
							var label = panel.getElement('div.form-label');
							var element = panel.getElement('div.form-element');

							if (!label)
							{
								label = element.getElement('label span.label');
							}

							var value = null;

							var test = element.getElement('input[name]');

							if (test)
							{
								value = test.get('value');
							}

//							console.log('label: %a, element: %a, value: %a', label ? label.innerHTML : '<no label>', element, value);

							if (!value)
							{
								return;
							}

							summary.innerHTML += label ? label.innerHTML : '<no label>';
							summary.innerHTML += value;
						}
					);

//					console.log('summary: %a', summary);

					summary.inject(section, 'after');
				}

//				fold();

				trigger.addEvent
				(
					'click', function()
					{
						if (!section.hasClass('folded'))
						{
							//fold();
						}

						trigger.toggleClass('folded');
						section.toggleClass('folded');
					}
				);
			}
		);
	}
);