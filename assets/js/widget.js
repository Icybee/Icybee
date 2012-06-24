/*
 * This file is part of the Icybee package.
 *
 * (c) Olivier Laviale <olivier.laviale@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

var Icybee = {

	Widget: {

		AdjustPopover: new Class
		({
			Implements: [ Options, Events ],

			Extends: Brickrouge.Popover,

			initialize: function(el, options)
			{
				this.parent(el, options)

				this.adjust = null
				this.selected = null
			},

			show: function()
			{
				this.element.setStyle('display', 'none')
				document.body.appendChild(this.element)

				this.parent()

				this.adjust = this.element.getElement('.popover-content :first-child').get('widget')

				if (this.adjust)
				{
					this.adjust.addEvent('results', this.repositionCallback)
					this.adjust.addEvent('adjust', this.quickRepositionCallback)
				}
			},

			close: function()
			{
				this.parent()

				this.element.dispose()
			}
		})
	}
}

Brickrouge.Widget.Spinner = new Class
({
	Implements: [ Options, Events ],

	initialize: function(el, options)
	{
		this.element = $(el)
		this.setOptions(options)

		this.control = this.element.getElement('input')
		this.content = this.element.getElement('.spinner-content')
		this.popover = null
		this.resetValue = null
		this.resetContent = null

		this.element.addEvent('click', function(ev) {

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