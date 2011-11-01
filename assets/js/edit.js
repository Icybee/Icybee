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

	/*
	 * floating save box
	 */

	(function(){

		var target = $('section-save');
		var mirror = new Element('div#save-mirror.popup.above.black.sticky.right');
		var arrow = new Element('div.arrow');

		arrow.adopt(new Element('div', { html: '&nbsp;'}));
		arrow.inject(mirror);

		var trigger = target.getElement('button').clone();
		var triggerLabel = trigger.innerHTML;

		var mode = new Element('a[href=#mode]', { html: '[mode]'});

		mode.addEvent
		(
			'click', function(ev)
			{
				ev.stop();

				mirror.toggleClass('settings');
			}
		);

		var main = new Element('div.main');

		main.adopt(trigger, mode);

		var settings = new Element('div.settings');

		settings.adopt(target.getElement('.radio-group').clone());

		var choices = {};

		target.getElements('input').each
		(
			function(el)
			{
				choices[el.value] = {

					element: el,
					label: el.getNext().innerHTML.replace(triggerLabel, ' ').trim(),
					mirror: settings.getElement('[value=' + el.value + ']')
				};
			}
		);

		function updateModeLabel()
		{
			Object.each
			(
				choices, function(choice, value)
				{
					if (!choice.element.checked)
					{
						return;
					}

					mode.innerHTML = choice.label;
				}
			);
		}

		Object.each
		(
			choices, function(choice)
			{
				choice.element.addEvent
				(
					'click', function(ev)
					{
						choice.mirror.checked = true;

						updateModeLabel();
					}
				);

				choice.mirror.addEvent
				(
					'click', function(ev)
					{
						choice.element.checked = true;

						updateModeLabel();

						mirror.toggleClass('settings');
					}
				);
			}
		);

		main.getElement('button').addEvent
		(
			'click', function(ev)
			{
				target.getElement('button').click();
			}
		);

		mirror.adopt(main, settings);

		updateModeLabel();

		$('footer').addClass('sticky');

		/*
		 * visibility
		 */

		var fx = new Fx.Tween(mirror, { property: 'opacity', link: 'cancel' });

		//fx.set(.5);

		function updateVisibility()
		{
			var targetY = target.getPosition().y;
			var size = $(document.body).getSize();
			var scroll = $(document.body).getScroll();

			if (scroll.y + size.y >= targetY)
			{
				fx.start(0);
			}
			else
			{
				fx.start(1);
			}
		}

		window.addEvents({ resize: updateVisibility, scroll: updateVisibility });

		updateVisibility();

		document.body.appendChild(mirror);

	})();

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