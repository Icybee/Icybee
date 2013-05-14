/*
 * This file is part of the Icybee package.
 *
 * (c) Olivier Laviale <olivier.laviale@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Creates a link between the _save mode_ group at the end of the editor form and the one in the
 * action bar.
 */
window.addEvent('domready', function() {

	var mirror = document.body.getElement('.actionbar .record-save-mode')
	, container = document.body.getElement('.form-actions .save-mode')
	, modes = container ? container.getElements('input[type="radio"]') : []
	, primaryButton = document.body.getElement('.form-actions .btn-primary')

	if (!mirror || !modes) return

	mirror.addEvent('click', function(ev) {

		var target = ev.target
		, mode = target.get('data-key')

		if (target.match('.btn-primary:first-child'))
		{
			ev.stop()
			primaryButton.click()
			return
		}

		if (!mode) return

		ev.stop()

		modes.each(function(el) {

			el.checked = (el.value == mode)

		})

		primaryButton.click()
	})

	container.addEvent('click', function(ev) {

		var mode = ev.target.get('value')

		if (!mode) return

		mirror.getElements('li').each(function(el) {

			var anchor = el.getElement('a')
			, anchorMode = anchor.get('data-key')

			if (mode == anchorMode)
			{
				el.addClass('active')
				mirror.getElement('.btn').set('html', anchor.get('html'))
			}
			else
			{
				el.removeClass('active')
			}
		})
	})
})