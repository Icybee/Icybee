/*
 * This file is part of the Icybee package.
 *
 * (c) Olivier Laviale <olivier.laviale@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Disable spellchecking for textarea with the `code` class.
 */
Brickrouge.observe(Brickrouge.EVENT_UPDATE, ev => {

	ev.fragment.querySelectorAll('textarea.code').forEach(el => {

		if (!el.spellcheck) return

		el.spellcheck = false

	})
})

/**
 * Ask the user before losing changes made to the primary form.
 *
 * The primary form is indicated by the `.form-primary` class.
 */
window.addEvent('domready', function() {

	/*
	 * The following code looks for changes in elements' values between the 'domready' event and
	 * the 'onbeforeunload' event. If there are changes, the user is asked to confirm page unload.
	 */

	function toQueryString(el)
	{
		var keys = []
		, values = []
		, assoc = {}

		el.getElements('[name]').each(function(el) {

			if (el.disabled) return

			var key = el.get('name')
			, value = el.get('value')

			keys.push(key)
			values.push(value)
			assoc[key] = value

		})

		var sorted_keys = keys.slice(0)
		, sorted_values = {}

		sorted_keys.sort()

		//console.log('elements (%d): %a, active: %a, concat: %s', elements.length, elements, actives, concat);
		//console.log('keys: %a, values: %a', keys, values);

		for (var i = 0 ; i < sorted_keys.length ; i++)
		{
			var key = sorted_keys[i]

			sorted_values[key] = assoc[key]
		}

		var hash = new Hash(sorted_values)

		//console.log('sorted keys: %a, values: %a', sorted_keys, sorted_values);
		//console.log('queryString: %s', hash.toQueryString());

		return hash.toQueryString()
	}

	var initValues = null
	, skip = false
	, form = document.body.getElement('.form-primary')

	if (!form) return

	initValues = toQueryString(form)

	window.addEvent('load', function() {

		initValues = toQueryString(form)

	})

	window.onbeforeunload = function() {

		if (skip)
		{
			skip = false

			return
		}

		var values = toQueryString(form)

		//console.log('values_now: %s', values_now);

		if (initValues == values) return

		return "Des changements ont été fait sur la page. Si vous changez de page maintenant, ils seront perdus."
	}

	form.addEvent('submit', function(ev) {

		skip = true
		initValues = toQueryString(form)

	})
})
