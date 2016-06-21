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

],

/**
 * @param {Brickrouge} Brickrouge
 */
(Brickrouge) => {

	/**
	 * @property {Element} element
	 * @property {Object} options
	 * @property {HTMLInputElement} control
	 * @property {Element|null} content
	 * @property {Brickrouge.Popover} popover
	 * @property {string|number|null} resetValue
	 */
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

			this.control.value = this.encodeValue(value)
		}

		/**
		 * Get the string value for the input and translate it into its internal representation.
		 *
		 * @returns {*}
		 */
		get value()
		{
			return this.decodeValue(this.control.value)
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

		/**
		 * Opens the popover.
		 */
		open()
		{
			this.resetValue = this.value

			if (this.popover)
			{
				this.popover.adjust.value = this.resetValue
				this.popover.show()
			}
			else
			{
				this.createPopover(popover => {

					this.popover = popover

					popover.show()

					/**
					 * @param {Icybee.AdjustPopover.ActionEvent} ev
					 */
					popover.observeAction(ev => this.action(ev.action))

					/**
					 * @param {Icybee.Adjust.ChangeEvent} ev
					 */
					popover.adjust.observeChange(ev => this.change(ev.value))

				})
			}
		}

		/**
		 * Close the popover.
		 */
		close()
		{
			if (!this.popover)
			{
				return
			}

			this.popover.hide()
		}

		/**
		 * Creates popover with adjust element.
		 *
		 * @param {function} callback Callback to call when the popover has been created.
		 */
		createPopover(callback)
		{
			throw new Error("The method must be implemented by sub-classes.")
		}

		/**
		 *
		 * @param {string|number|null} value
		 */
		change(value)
		{
			this.value = value

			if (this.popover)
			{
				this.popover.reposition()
			}
		}

		/**
		 * @param {string} action
		 */
		action(action)
		{
			switch (action)
			{
				case 'cancel':
					this.reset()
					break

				case 'remove':
					this.remove()
					break

				case 'use':
					this.use(this.popover.value)
					break
			}

			this.close()

		}

		/**
		 * Reset to the original value.
		 */
		reset()
		{
			this.value = this.resetValue
		}

		/**
		 * Remove the value.
		 */
		remove()
		{
			this.value = null
		}

		/**
		 * Use a value.
		 *
		 * @param {string|number|null} value
		 */
		use(value)
		{
			this.value = value
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
