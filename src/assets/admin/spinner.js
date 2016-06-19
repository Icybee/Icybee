/*
 * This file is part of the Icybee package.
 *
 * (c) Olivier Laviale <olivier.laviale@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

define('icybee/spinner', [

	'brickrouge'

], (Brickrouge) => {

	return class
	{
		/**
		 * @param {Element} el
		 * @param {{}} options
		 */
		constructor(el, options)
		{
			this.element = el
			this.options = options
			this.control = el.querySelector('input')
			this.content = el.querySelector('.spinner-content')
			this.popover = null
			this.resetValue = null
			this.resetContent = null

			this.listenToClick()
		}

		/**
		 * Invokes `this.open()` when the element is clicked.
		 */
		listenToClick()
		{
			this.element.addEventListener('click', ev => {

				ev.preventDefault()
				this.open()

			})
		}

		/**
		 * Translate the internal representation of the value into a string
		 *
		 * @param {*} value
		 */
		set value(value)
		{
			if (this.content)
			{
				const formattedValue = this.formatValue(value)
				const type = typeOf(formattedValue)

				Brickrouge.empty(this.content)

				if (type == 'element' || type == 'elements')
				{
					this.content.adopt(formattedValue)
				}
				else if (type == 'string')
				{
					this.content.innerHTML = formattedValue
				}
			}

			this.element[value ? 'removeClass' : 'addClass']('placeholder')

			this.control.set('value', this.encodeValue(value))
		}

		/**
		 * Get the string value for the input and translate it into its internal representation.
		 *
		 * @returns {*}
		 */
		get value()
		{
			return this.decodeValue(this.control.get('value'))
		}

		/**
		 * Encodes the internal representation of the value into a string.
		 *
		 * @param value
		 *
		 * @return string
		 */
		encodeValue(value)
		{
			return value
		}

		/**
		 * Decode the string encoded value into its internal representation.
		 *
		 * @param {*} value
		 *
		 * @return {*}
		 */
		decodeValue(value)
		{
			return value
		}

		/**
		 * @param {*} value
		 *
		 * @returns {*}
		 */
		formatValue(value)
		{
			return value
		}

		attachAdjust(adjust)
		{

		}

		/**
		 * Displays the adjust element.
		 */
		open()
		{

		}
	}
})

!function (Brickrouge) {

	let Constructor

	Brickrouge.register('spinner', (element, options) => {

		if (!Constructor)
		{
			Constructor = require('icybee/spinner')
		}

		return new Constructor(element, options)

	})

} (Brickrouge)
