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

window.addEvent('domready', function() {

	//
	// disabled Firefox's spellchecking for textarea elements with the 'code' class
	//

	$$('textarea.code').each(function(el) {
		if (el.spellcheck)
		{
			el.spellcheck = false
		}
	})
})

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

	function init(ev)
	{
		ev.target.getElements('input.search').each(function(el) {

			if (el.retrieve('widget-search')) return

			var label = el.get('data-placeholder') || 'Search'

			if (!el.value)
			{
				el.addClass('placeholder')
				el.value = label
			}

			el.addEvents
			({
				focus: function() {

					if (this.hasClass('placeholder'))
					{
						this.value = '';
						this.removeClass('placeholder');
					}
				},

				blur: function() {

					if (!this.value)
					{
						this.addClass('placeholder');
						this.value = label;
					}
				}
			})

			el.store('widget-search', true)
		})

		ev.target.getElements('.autofocus').each
		(
			function(el)
			{
				el.focus();
			}
		);
	}

	window.addEvent('brickrouge.update', init);

})();

/**
 * Reset button for default values
 */
!function() {

	var controls = []

	window.addEvent('domready', function() {

		$(document.body).getElements('[data-default-value]').each(function(el) {

			if (controls.indexOf(el) !== -1) return;

			controls.push(el)

			var reset = new Element('span.btn.btn-warning.reset-default-value[tabindex="0"]', { html: '<i class="icon-edit icon-white"></i> Reset' })
			, defaultValue = el.get('data-default-value')
			, container = el.getParent('.controls')

			if (el.get('value') == defaultValue) reset.addClass('hidden')

			function go() {

				el.set('value', defaultValue)
				reset.addClass('hidden')
			}

			reset.addEvent('click', go)
			reset.addEvent('keypress', function(ev) {

				if (ev.key != 'enter' && ev.key != 'space') return

				go()
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