/*
 * This file is part of the Icybee package.
 *
 * (c) Olivier Laviale <olivier.laviale@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

define([

	'brickrouge'

],

/**
 * @param {Brickrouge} Brickrouge
 */
Brickrouge => {

	/**
	 * Disable spellchecking for textarea with the `code` class.
	 */
	Brickrouge.observeUpdate(ev => {

		ev.fragment.querySelectorAll('textarea.code').forEach(el => {

			if (!el.spellcheck) {
				return
			}

			el.spellcheck = false

		})
	})

	/**
	 * Ask the user before losing changes made to the primary form.
	 *
	 * The primary form is indicated by the `.form-primary` class.
	 */
	Brickrouge.observeRunning(() => {

		/*
		 * The following code looks for changes in elements' values between the 'domready' event and
		 * the 'onbeforeunload' event. If there are changes, the user is asked to confirm page unload.
		 */

		function toQueryString(el)
		{
			const keys = []
			const values = []
			const assoc = {}

			el.querySelectorAll('[name]').forEach(el => {

				if (el.disabled) {
					return
				}

				const key = el.getAttribute('name')
				const value = el.value

				keys.push(key)
				values.push(value)
				assoc[key] = value

			})

			const sorted_keys = keys.slice(0)
			const sorted_values = {}

			sorted_keys.sort()

			//console.log('elements (%d): %a, active: %a, concat: %s', elements.length, elements, actives, concat);
			//console.log('keys: %a, values: %a', keys, values);

			for (let i = 0 ; i < sorted_keys.length ; i++)
			{
				const key = sorted_keys[i]

				sorted_values[key] = assoc[key]
			}

			const hash = new Hash(sorted_values)

			//console.log('sorted keys: %a, values: %a', sorted_keys, sorted_values);
			//console.log('queryString: %s', hash.toQueryString());

			return hash.toQueryString()
		}

		let skip = false
		const form = document.body.querySelector('.form-primary')

		if (!form) {
			return
		}

		let initValues = toQueryString(form)

		window.addEvent('load', () => {

			initValues = toQueryString(form)

		})

		window.onbeforeunload = () => {

			if (skip)
			{
				skip = false

				return null
			}

			const values = toQueryString(form)

			//console.log('values_now: %s', values_now);

			if (initValues == values) {
				return null
			}

			return "Des changements ont été fait sur la page. Si vous changez de page maintenant, ils seront perdus."
		}

		form.addEvent('submit', ev => {

			skip = true
			initValues = toQueryString(form)

		})
	})

})
