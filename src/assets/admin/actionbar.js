/*
 * This file is part of the Icybee package.
 *
 * (c) Olivier Laviale <olivier.laviale@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

!function (Brickrouge) {

	class ActionBar
	{
		constructor(el)
		{
			this.element = el
			this.setUpAnchoring()

			el.addDelegatedEventListener('[data-target]', 'click', ev => {

				const target = document.body.querySelector(ev.target.getAttribute('data-target'))

				if (!target || target.tagName != 'FORM') return

				target.submit()

			})
		}

		toElement()
		{
			return this.element
		}

		setUpAnchoring()
		{
			const el = this.element

			let y = el.getPosition().y

			function updateActionBar()
			{
				const bodyY = document.html.scrollTop || document.body.scrollTop

				el[y < bodyY ? 'addClass' : 'removeClass']('fixed')
			}

			window.addEvents({

				load: updateActionBar,
				resize: updateActionBar,
				scroll: updateActionBar

			})
		}

		changeContext(context)
		{
			this.element.set('data-context', context || '')
		}
	}

	Brickrouge.register('action-bar', (element, options) => new ActionBar(element, options))

} (Brickrouge);
