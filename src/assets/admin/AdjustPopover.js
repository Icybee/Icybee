/*
 * This file is part of the Icybee package.
 *
 * (c) Olivier Laviale <olivier.laviale@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

define('icybee/adjust-popover', [

	'brickrouge'

],

/**
 * @param {Brickrouge} Brickrouge
 *
 * @returns {Icybee.AdjustPopover}
 */
Brickrouge => {

	const Subject = Brickrouge.Subject

	/**
	 * @event Icybee.AdjustPopover#update
	 * @type {Icybee.AdjustPopover.UpdateEvent|Function}
	 */
	const UpdateEvent = Subject.createEvent(function (value) {

		this.value = value

	})

	/**
	 * @event Icybee.AdjustPopover#layout
	 * @type {Icybee.AdjustPopover.LayoutEvent|Function}
	 */
	const LayoutEvent = Subject.createEvent(function () {

	})

	return class extends Brickrouge.Popover
	{
		/**
		 * @returns {Icybee.AdjustPopover.UpdateEvent}
		 * @constructor
		 */
		static get UpdateEvent()
		{
			return UpdateEvent
		}

		/**
		 * @returns {Icybee.AdjustPopover.LayoutEvent}
		 * @constructor
		 */
		static get LayoutEvent()
		{
			return LayoutEvent
		}

		/**
		 * @returns {Icybee.Adjust}
		 */
		get adjust()
		{
			return Brickrouge.from(this.element.querySelector('.popover-content :first-child'))
		}

		/**
		 * Return adjust value.
		 *
		 * @returns {string|number|null}
		 */
		get value()
		{
			return this.adjust.value
		}

		/**
		 * Set adjust value.
		 *
		 * @param {string|number|null} value
		 */
		set value(value)
		{
			this.adjust.value = value
		}

		/**
		 * @inheritdoc
		 *
		 * Reposition the popover when the content of the adjust element is updated.
		 */
		show()
		{
			super.show()

			const adjust = this.adjust

			if (!adjust)
			{
				return
			}

			try
			{
				adjust.observeLayout(() => {
					this.repositionCallback()
					this.notify(new LayoutEvent(this))
				})

				/**
				 * @param {Icybee.Adjust.ChangeEvent} ev
				 */
				adjust.observeChange(ev => {
					this.quickRepositionCallback()
					this.notify(new UpdateEvent(ev.value))
				})
			}
			catch (e)
			{
				console.error(e)
			}
		}

		/**
		 * @param {Function} callback
		 */
		observeUpdate(callback)
		{
			this.observe(UpdateEvent, callback)
		}

		/**
		 * @param {Function} callback
		 */
		observeLayout(callback)
		{
			this.observe(LayoutEvent, callback)
		}
	}

})
