/*
 * This file is part of the Icybee package.
 *
 * (c) Olivier Laviale <olivier.laviale@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

window.addEvent('domready', function()
{
	var actionbar = document.id(document.body).getElement('.actionbar')
	, y

	if (!actionbar) return

	y = actionbar.getPosition().y

	function updateActionBar()
	{
		var bodyY = document.html.scrollTop || document.body.scrollTop

		actionbar[y < bodyY ? 'addClass' : 'removeClass']('fixed')
	}

	window.addEvents({
		load: updateActionBar,
		resize: updateActionBar,
		scroll: updateActionBar
	})

	actionbar.addEvent('click:relay([data-target])', function(ev) {

		var target = document.id(document.body).getElement(ev.target.get('data-target'))

		if (!target || target.tagName != 'FORM') return

		target.submit()

	})
})