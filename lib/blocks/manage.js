/*
 * This file is part of the Icybee package.
 *
 * (c) Olivier Laviale <olivier.laviale@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

window.addEvent('brickrouge.update', function(ev) {

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
					this.value = ''
					this.removeClass('placeholder')
				}
			},

			blur: function() {

				if (!this.value) return

				this.addClass('placeholder')
				this.value = label
			}
		})

		el.store('widget-search', true)
	})

	ev.target.getElements('.autofocus').each(function(el) {

		el.focus()

	})
})

/*
window.addEvent('domready', function() {

	var actionbarSearch = document.body.getElement('.actionbar-search')
	, managerLimiter = document.body.getElement('#manager tfoot .limiter')

	managerLimiter.inject(actionbarSearch, 'before')

})
*/