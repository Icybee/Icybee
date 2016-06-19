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

	const ChangeEvent = Subject.createEvent(function (target, value) {

		this.target = target
		this.value = value

	})

	const Adjust = class extends Brickrouge.mixin(Object, Subject) {

		/**
		 * Observes update event.
		 *
		 * @param {function} callback
		 */
		observeUpdate(callback) {

			this.observe(ChangeEvent, callback)

		}

	}

	Object.defineProperties(Adjust, {

		'ChangeEvent': { value: ChangeEvent }

	})

	return Adjust

})
