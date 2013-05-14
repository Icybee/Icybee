/*
 * This file is part of the Icybee package.
 *
 * (c) Olivier Laviale <olivier.laviale@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Reset button for default values
 */
;!function() {

	var controls = []

	function resetToDefault(reset)
	{
		var target = reset.retrieve('target')

		target.set('value', target.get('data-default-value'))
		target.fireEvent('change')

		reset.addClass('hidden')
	}

	document.body.addEvent('click:relay(.reset-default-value)', function(ev, reset) {

		resetToDefault(reset)

	})

	document.body.addEvent('keypress:relay(.reset-default-value)', function(ev, reset) {

		if (ev.key != 'enter' && ev.key != 'space') return

		resetToDefault(reset)

	})

	window.addEvent('brickrouge.update', function(ev) {

		ev.target.getElements('[data-default-value]').each(function(el) {

			if (controls.indexOf(el) !== -1) return

			controls.push(el)

			var reset = new Element('span.btn.btn-warning.reset-default-value[tabindex="0"]', { html: '<i class="icon-edit icon-white"></i> Reset' })
			, container = el.getParent('.controls')

			reset.store('target', el)

			if (el.get('value') == el.get('data-default-value'))
			{
				reset.addClass('hidden')
			}

			el.addEvent('change', function() {

				reset[this.get('value') == this.get('data-default-value') ? 'addClass' : 'removeClass']('hidden')

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