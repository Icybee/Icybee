/*
 * This file is part of the Icybee package.
 *
 * (c) Olivier Laviale <olivier.laviale@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Changes the `enabled` class of the `.group-toggler` element according to the state of its
 * checkbox child.
 */
window.addEvent('click:relay(.group-toggler input[type="checkbox"])', function(ev, el) {

	var parent = el.getParent('.group-toggler')

	parent[el.checked ? 'addClass' : 'removeClass']('enabled')
})

/**
 * Provides a notice for long XHR.
 */
!function() {

	var dummy = null
	, dummyTween = null
	, message = null
	, messageTween = null

	ICanBoogie.XHR.NOTICE_DELAY = 1000

	window.addEvent('icanboogie.xhr.shownotice', function() {

		if (!dummy)
		{
			dummy = new Element('div.xhr-dummy')
			message = new Element('div.xhr-message', { html: 'Loadin...' })
			dummyTween = new Fx.Tween(dummy, { property: 'opacity', duration: 'short', link: 'cancel' })
			dummyTween.set(0)
			messageTween = new Fx.Tween(message, { property: 'opacity', duration: 'short', link: 'cancel' })
			messageTween.set(0)
		}

		document.body.appendChild(dummy)
		document.body.appendChild(message)

		dummyTween.start(1)
		messageTween.start(1)
	})

	window.addEvent('icanboogie.xhr.hidenotice', function() {

		if (!dummy || !dummy.getParent()) return

		messageTween.start(0)
		dummyTween.start(0).chain(function() {

			dummy.dispose()
			message.dispose()

		})
	})

} ()/*
 * This file is part of the Icybee package.
 *
 * (c) Olivier Laviale <olivier.laviale@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

window.addEvent('domready', function()
{
	var actionbar = document.id(document.body).getElement('.actionbar')
	, y

	if (!actionbar) return

	y = actionbar.getPosition().y

	function updateActionBar()
	{
		var bodyY = document.html.scrollTop || document.body.scrollTop

		actionbar[y < bodyY ? 'addClass' : 'removeClass']('fixed')
	}

	window.addEvents({
		load: updateActionBar,
		resize: updateActionBar,
		scroll: updateActionBar
	})

	actionbar.addEvent('click:relay([data-target])', function(ev) {

		var target = document.id(document.body).getElement(ev.target.get('data-target'))

		if (!target || target.tagName != 'FORM') return

		target.submit()

	})
})/*
 * This file is part of the Icybee package.
 *
 * (c) Olivier Laviale <olivier.laviale@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

this.Icybee = {

	Widget: {

		AdjustPopover: new Class
		({
			Extends: Brickrouge.Popover,
			Implements: [ Options, Events ],

			initialize: function(el, options)
			{
				this.parent(el, options)

				this.adjust = null
				this.selected = null
			},

			show: function()
			{
				this.parent()

				this.adjust = this.element.getElement('.popover-content :first-child').get('widget')

				if (this.adjust)
				{
					this.adjust.addEvent('results', this.repositionCallback)
					this.adjust.addEvent('adjust', this.quickRepositionCallback)
				}
			}
		})
	}
}/*
 * This file is part of the Icybee package.
 *
 * (c) Olivier Laviale <olivier.laviale@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

Brickrouge.Widget.Spinner = new Class
({
	Implements: [ Options, Events ],

	initialize: function(el, options)
	{
		this.element = el = document.id(el)
		this.setOptions(options)

		this.control = el.getElement('input')
		this.content = el.getElement('.spinner-content')
		this.popover = null
		this.resetValue = null
		this.resetContent = null

		el.addEvent('click', function(ev) {

			ev.stop()
			this.open()

		}.bind(this))
	},

	open: function()
	{

	},

	/**
	 * Translate the internal representation of the value into a string
	 */
	setValue: function(value)
	{
		if (this.content)
		{
			var formatedValue = this.formatValue(value)
			, type = typeOf(formatedValue)

			this.content.empty()

			if (type == 'element' || type == 'elements')
			{
				this.content.adopt(formatedValue)
			}
			else if (type == 'string')
			{
				this.content.innerHTML = formatedValue
			}
		}

		this.element[value ? 'removeClass' : 'addClass']('placeholder')

		this.control.set('value', this.encodeValue(value))
	},

	/**
	 * Get the string value for the input and translate it into its internal representation.
	 */
	getValue: function()
	{
		return this.decodeValue(this.control.get('value'))
	},

	/**
	 * Encodes the internal representation of the value into a string.
	 *
	 * @param value
	 *
	 * @return string
	 */
	encodeValue: function(value)
	{
		return value
	},

	/**
	 * Decode the string encoded value into its internal representation.
	 *
	 * @param value
	 *
	 * @return mixed
	 */
	decodeValue: function(value)
	{
		return value
	},

	formatValue: function(value)
	{
		return value
	},

	attachAdjust: function(adjust)
	{

	}
})

;