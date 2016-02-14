/*
 * This file is part of the Icybee package.
 *
 * (c) Olivier Laviale <olivier.laviale@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

!function (Brickrouge) {

	Brickrouge.Widget.ActionBar = new Class({

		Implements: [ Events ],

		initialize: function(el)
		{
			this.element = el = document.id(el)

			this.setUpAnchoring()

			el.addEvent('click:relay([data-target])', function(ev) {

				var target = document.id(document.body).getElement(ev.target.get('data-target'))

				if (!target || target.tagName != 'FORM') return

				target.submit()

			})

			this.fireEvent('icybee.actionbar.ready', this)
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

		changeContext: function(what)
		{
			this.element.set('data-context', what || '')
		}

	})

	Brickrouge.register('action-bar', function (element, options) {

		return new Brickrouge.Widget.ActionBar(element, options)

	})

} (Brickrouge);
