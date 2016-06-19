/*
 * This file is part of the Icybee package.
 *
 * (c) Olivier Laviale <olivier.laviale@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

define('icybee/pop-adjust', [

	'brickrouge',
	'icybee/spinner'

], (Brickrouge, Spinner) => {

	return class extends Spinner {

		constructor(element, options) {

			super(element, options)

			this.popover = null

		}

		/**
		 * Opens the popover.
		 */
		open() {

			this.resetValue = this.value

			if (this.popover) {
				this.popover.adjust.value = this.resetValue
				this.popover.show()
			}
			else {
				this.createPopover(popover => {

					this.popover = popover

					popover.show()
					popover.observeAction(this.onAction.bind(this))
					popover.adjust.observeUpdate(this.onUpdate.bind(this))

				})
			}
		}

		/**
		 * Close the popover.
		 */
		close()
		{
			if (!this.popover) {
				return
			}

			this.popover.hide()
		}

		/**
		 * Creates popover with adjust element.
		 *
		 * @param {function} callback Callback to call when the popover has been created.
		 */
		createPopover(callback) {

			throw new Error("The method must be implemented by sub-classes.")

		}

		/**
		 *
		 * @param {Icybee.Adjust.ChangeEvent} ev
		 */
		onUpdate(ev)
		{
			this.value = ev.value
			this.popover.reposition()
		}

		/**
		 * @param {Icybee.AdjustPopover.ActionEvent} ev
		 */
		onAction(ev) {

			switch (ev.action)
			{
				case 'use':
					this.value = ev.popover.adjust.value
					break

				case 'remove':
					this.value = null
					break

				case 'cancel':
					this.value = this.resetValue
					break
			}

			this.close()

		}

	}

})
