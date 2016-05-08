/*
 * This file is part of the Icybee package.
 *
 * (c) Olivier Laviale <olivier.laviale@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

define('icybee/spinner', [

], () => {

	return class
	{
		constructor(el, options)
		{
			this.element = el
			this.options = options
			this.control = el.querySelector('input')
			this.content = el.querySelector('.spinner-content')
			this.popover = null
			this.resetValue = null
			this.resetContent = null

			el.addEventListener('click', ev => {

				ev.preventDefault()
				this.open()

			})
		}

		/**
		 * Translate the internal representation of the value into a string
		 */
		setValue(value)
		{
			if (this.content)
			{
				const formattedValue = this.formatValue(value)
				const type = typeOf(formattedValue)

				this.content.empty()

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
		 */
		getValue()
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
		 * @param value
		 *
		 * @return mixed
		 */
		decodeValue(value)
		{
			return value
		}

		formatValue(value)
		{
			return value
		}

		attachAdjust(adjust)
		{

		}

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
