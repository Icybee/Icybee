/*
 * This file is part of the Icybee package.
 *
 * (c) Olivier Laviale <olivier.laviale@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

!function() {

	var ActionBar = new Class({

		initialize: function(el)
		{
			this.element = el = document.id(el)
			this.faces = el.getElement('.actionbar-faces')

			this.setUpAnchoring()

			el.addEvent('click:relay([data-target])', function(ev) {

				var target = document.id(document.body).getElement(ev.target.get('data-target'))

				if (!target || target.tagName != 'FORM') return

				target.submit()

			})
		},

		toElement: function()
		{
			return this.element
		},

		setUpAnchoring: function()
		{
			var el = this.element
			, y = el.getPosition().y

			function updateActionBar()
			{
				var bodyY = document.html.scrollTop || document.body.scrollTop

				el[y < bodyY ? 'addClass' : 'removeClass']('fixed')
			}

			window.addEvents({
				load: updateActionBar,
				resize: updateActionBar,
				scroll: updateActionBar
			})
		},

		display: function(what)
		{
			if (!what)
			{

				this.faces.removeClass('flipped')
				;( function() { this.set('data-display', '') }).delay(500, this.element)
			}
			else
			{
				this.element.set('data-display', what)
				this.faces.addClass('flipped')
			}
		}
	})

	window.addEvent('domready', function() {

		var actionbar = document.body.getElement('.actionbar')

		if (!actionbar) return

		Icybee.actionbar = new ActionBar(actionbar)

	})

} ()