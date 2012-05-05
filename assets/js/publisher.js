/*
 * This file is part of the Icybee package.
 *
 * (c) Olivier Laviale <olivier.laviale@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

String.implement
({
	/**
	 * Shortens a string to given length from a given position.
	 *
	 * Example:
	 *
	 * var str = "Raccourcir une chaine de caractères à des endroits divers et variés.";
	 *
	 * console.log(str.shorten(32, 0)); // remove characters from the beginning of the string
	 * console.log(str.shorten(32, .25));
	 * console.log(str.shorten(32, .5)); // remove characters from the middle of the string
	 * console.log(str.shorten(32, .75));
	 * console.log(str.shorten(32, 1)); // remove characters from the end of the string
	 *
	 * @param int length
	 * @param float position
	 * @return string A string shortened.
	 */
	shorten: function(length, position)
	{
		if (length === undefined)
		{
			length = 32;
		}

		if (position === undefined)
		{
			position = .75;
		}

		var l = this.length;

		if (l <= length)
		{
			return this;
		}

		length--;
		position = Math.round(position * length);

		if (position == 0)
		{
			return '…' + this.substring(l - length);
		}
		else if (position == length)
		{
			return this.substring(0, length) + '…';
		}
		else
		{
			return this.substring(0, position) + '…' + this.substring(l - (length - position));
		}
	}
});

var spinner = null;

window.addEvent
(
	'domready', function()
	{
		//
		// disabled Firefox's spellchecking for textarea elements with the 'code' class
		//

		$$('textarea.code').each
		(
			function(el)
			{
				if (el.spellcheck)
				{
					el.spellcheck = false;
				}
			}
		);

		if ($('loader'))
		{
			spinner = new WdSpinner('loader');
		}
	}
);

window.addEvent
(
	'domready', function()
	{
		$$('label.checkbox-wrapper').each
		(
			function (el)
			{
				var checkbox = el.getElement('input');

				if (checkbox.checked)
				{
					el.addClass('checked');
				}

				if (checkbox.disabled)
				{
					el.addClass('disabled');
				}

				if (checkbox.readonly)
				{
					el.addClass('readonly');
				}

				checkbox.addEvent
				(
					'change', function()
					{
						this.checked ? el.addClass('checked') : el.removeClass('checked');
					}
				);
			}
		);
	}
);

(function() {

var init = function(ev)
{
	ev.target.getElements('input.search').each
	(
		function(el)
		{
			if (el.retrieve('widget-search'))
			{
				return;
			}

			var label = el.get('data-placeholder') || 'Search';

			if (!el.value)
			{
				el.addClass('placeholder');
				el.value = label;
			}

			el.addEvents
			({
				focus: function()
				{
					if (this.hasClass('placeholder'))
					{
						this.value = '';
						this.removeClass('placeholder');
					}
				},

				blur: function()
				{
					if (!this.value)
					{
						this.addClass('placeholder');
						this.value = label;
					}
				}
			});

			el.store('widget-search', true);
		}
	);

	ev.target.getElements('.autofocus').each
	(
		function(el)
		{
			el.focus();
		}
	);

	//
	//
	//

	$$('.popup.info[data-target').each
	(
		function(el)
		{
			var target = el.get('data-target');

			if (target)
			{
				target = document.getElement(target);
			}

			//el.adopt(new Element('div.arrow').adopt(new Element('div')));

			el.setStyles({ padding: '1em', top: 10, left: 10 });
			//el.addClass('below');

			( function() { new Fx.Tween(el, { property: 'opacity' }).start(0).chain(el.destroy.bind(el)); } ).delay(2000);
		}
	);
};

document.addEvent('elementsready', init);

})();

window.addEvent
(
	'load', function()
	{
		(
			function()
			{
				$$('ul.wddebug.done').slide('out');
			}
		)
		.delay(4000);
	}
);

window.addEvent
(
	'domready', function()
	{
		var form = document.forms['change-working-site'];

		if (!form) return;

		form = $(form);

		form.addEvent
		(
			'submit', function()
			{
				form.action = form.getElement('select').get('value');
			}
		);
	}
);


/**
 * Reset button for default values
 */
!function() {

	var controls = []

	window.addEvent('domready', function() {

		$(document.body).getElements('[data-default-value]').each(function(el) {

			if (controls.indexOf(el) !== -1) return;

			controls.push(el)

			var reset = new Element('span.btn.btn-warning.reset-default-value', { html: '<i class="icon-edit icon-white"></i> Reset' })
			, defaultValue = el.get('data-default-value')
			, container = el.getParent('.controls')

			if (el.get('value') == defaultValue) reset.addClass('hidden')

			reset.addEvent('click', function() {

				el.set('value', defaultValue)
				reset.addClass('hidden')

			})

			el.addEvent('change', function () {

				reset[el.get('value') == defaultValue ? 'addClass' : 'removeClass']('hidden');
			})

			if (container)
			{
				reset.inject(container)
			}
			else
			{
				reset.inject(el, 'after')
			}
		})
	})

} ()


/**
 * Provides a notice for long XHR.
 */
!function() {

	var dummy = null
	, dummyTween = null
	, message = null
	, messageTween = null

	ICanBoogie.XHR.NOTICE_DELAY = 500;

	window.addEvent('icanboogie.xhr.shownotice', function() {

		if (!dummy)
		{
			dummy = new Element('div.xhr-dummy')
			message = new Element('div.xhr-message', { html: 'Loadin...' })
			dummyTween = new Fx.Tween(dummy, { property: 'opacity', duration: 'short', link: 'cancel' })
			messageTween = new Fx.Tween(message, { property: 'opacity', duration: 'short', link: 'cancel' })
			dummyTween.set(0)
			messageTween.set(0)
		}

		document.body.appendChild(dummy)
		document.body.appendChild(message)

		dummyTween.start(1)
		messageTween.start(1)
	})

	window.addEvent('icanboogie.xhr.hidenotice', function() {

		if (!dummy || !dummy.parentNode) return

		messageTween.start(0)
		dummyTween.start(0).chain(function() {

			dummy.dispose()
			message.dispose()

		})

	})

} ()