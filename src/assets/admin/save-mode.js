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
Brickrouge.observeRunning(ev => {

	const mirror = document.body.querySelector('.actionbar .record-save-mode')
	const container = document.body.querySelector('.form-actions .save-mode')
	const modes = container ? container.querySelectorAll('input[type="radio"]') : []
	const primaryButton = document.body.querySelector('.form-actions .btn-primary')

	if (!mirror || !modes.length) return

	mirror.addEventListener('click', ev => {

		const target = ev.target
		const mode = target.getAttribute('data-key')

		if (target.match('.btn-primary:first-child'))
		{
			ev.preventDefault()
			primaryButton.click()
			return
		}

		if (!mode) return

		ev.preventDefault()

		Array.prototype.forEach.call(modes, radio => {

			radio.checked = (radio.value === mode)

		})

		primaryButton.click()
	})

	container.addDelegatedEventListener('[type="radio"]', 'click', (ev, radio) => {

		const mode = radio.value

		mirror.querySelectorAll('.dropdown-item').forEach(item => {

			const itemMode = item.getAttribute('data-key')

			if (mode === itemMode)
			{
				item.classList.add('active')
				mirror.querySelector('.btn').innerHTML = item.innerHTML
			}
			else
			{
				item.classList.remove('active')
			}
		})
	})
})
