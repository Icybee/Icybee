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

], function (Brickrouge) {

	const Subject = Brickrouge.Subject

	/**
	 * @event Icybee.Adjust#change
	 */
	const ChangeEvent = Subject.createEvent(function (target, value) {

		this.target = target
		this.value = value

	})

	const Adjust = class extends Brickrouge.mixin(Object, Subject) {

		/**
		 * Observes change event.
		 *
		 * @param {function} callback
		 */
		observeChange(callback) {

			this.observe(ChangeEvent, callback)

		}

	}

	Object.defineProperties(Adjust, {

		ChangeEvent: { value: ChangeEvent }

	})

	return Adjust

})
