/*
 * This file is part of the Icybee package.
 *
 * (c) Olivier Laviale <olivier.laviale@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

window.addEvent('domready', function() {

	var form = document.id('editor')
	, PERIOD = 30 * 1000

	if (!form) return

	var destination = document.id(form.elements[ICanBoogie.Operation.DESTINATION])
	, key = document.id(form.elements[ICanBoogie.Operation.KEY])

	/*
	 * unload warning
	 */

	if (destination && key)
	{
		var url = destination.value + '/' + key.value + '/lock'
		, op = new Request.API({

			url: url
		})

		op.put.periodical(PERIOD, op)

		window.addEvent('unload', function() {

			new Request.API({

				url: url,
				async: false,
				method: 'delete'

			}).send()
		})
	}
	else
	{
		/*
		 * For new entries, we use the core/ping method in order to keep the user's session alive.
		 */

		var op = new Request.API({ url: 'core/ping' })

		op.send.periodical(PERIOD, op)
	}
})