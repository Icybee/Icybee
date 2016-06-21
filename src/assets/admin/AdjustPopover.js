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

], (Brickrouge) => {

	/**
	 * @event Icybee.AdjustPopover#update
	 */
	const UpdateEvent = Brickrouge.Subject.createEvent(function (value) {

		this.value = value

	})

	const AdjustPopover = class extends Brickrouge.Popover {

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

			if (!adjust) {
				return
			}

			try {
				if ('observeResult' in adjust) {
					adjust.observeResult(this.repositionCallback)
				}
				if ('observeChange' in adjust) {
					adjust.observeChange(ev => {
						this.quickRepositionCallback()
						this.notify(new UpdateEvent(this.adjust.value))
					})
				}
				if ('addEvent' in adjust) {
					console.warn('adjust should implement observeChange:', adjust)
					adjust.addEvent('change', this.quickRepositionCallback)
				}
			} catch (e) {
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

	}

	Object.defineProperties(AdjustPopover, {

		UpdateEvent: { value: UpdateEvent }

	})

	return AdjustPopover

})
