/*
 * This file is part of the Icybee package.
 *
 * (c) Olivier Laviale <olivier.laviale@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

define('icybee/adjust', [

	'brickrouge'

],

/**
 * @param {Brickrouge} Brickrouge
 *
 * @returns {Icybee.Adjust}
 */
Brickrouge => {

	const Subject = Brickrouge.Subject

	/**
	 * Fired when the value changes.
	 *
	 * @event Icybee.Adjust#change
	 * @type {Icybee.Adjust.ChangeEvent|Function}
	 */
	const ChangeEvent = Subject.createEvent(function (target, value) {

		this.target = target
		this.value = value

	})

	/**
	 * Fired when the element layout changes.
	 *
	 * @event Icybee.Adjust#layout
	 * @type {Icybee.Adjust.LayoutEvent|Function}
	 */
	const LayoutEvent = Subject.createEvent(function (target) {

		this.target = target

	})

	return class extends Brickrouge.mixin(Object, Subject) {

		/**
		 * @returns {Icybee.Adjust.ChangeEvent}
		 * @constructor
		 */
		static get ChangeEvent()
		{
			return ChangeEvent
		}

		/**
		 * @returns {Icybee.Adjust.LayoutEvent}
		 * @constructor
		 */
		static get LayoutEvent()
		{
			return LayoutEvent
		}

		/**
		 * @param {Element} element
		 * @param {Object} options
		 */
		constructor(element, options)
		{
			super()

			this.element = element
			this.options = options
		}

		/**
		 * Observe change event.
		 *
		 * @param {Function} callback
		 */
		observeChange(callback) {

			this.observe(ChangeEvent, callback)

		}

		/**
		 * Observe layout event.
		 *
		 * @param {Function} callback
		 */
		observeLayout(callback) {

			this.observe(LayoutEvent, callback)

		}

	}

})
