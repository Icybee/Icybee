/*
 * This file is part of the Icybee package.
 *
 * (c) Olivier Laviale <olivier.laviale@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Changes the `enabled` class of the `.group-toggler` element according to the state of its
 * checkbox child.
 */
window.addEvent('click:relay(.group-toggler input[type="checkbox"])', function(ev, el) {

	var parent = el.getParent('.group-toggler')

	parent[el.checked ? 'addClass' : 'removeClass']('enabled')
})

/**
 * Provides a notice for long XHR.
 */
!function() {

	var dummy = null
	, dummyTween = null
	, message = null
	, messageTween = null

	ICanBoogie.XHR.NOTICE_DELAY = 1000

	window.addEvent('icanboogie.xhr.shownotice', function() {

		if (!dummy)
		{
			dummy = new Element('div.xhr-dummy')
			message = new Element('div.xhr-message', { html: 'Loadin...' })
			dummyTween = new Fx.Tween(dummy, { property: 'opacity', duration: 'short', link: 'cancel' })
			dummyTween.set(0)
			messageTween = new Fx.Tween(message, { property: 'opacity', duration: 'short', link: 'cancel' })
			messageTween.set(0)
		}

		document.body.appendChild(dummy)
		document.body.appendChild(message)

		dummyTween.start(1)
		messageTween.start(1)
	})

	window.addEvent('icanboogie.xhr.hidenotice', function() {

		if (!dummy || !dummy.getParent()) return

		messageTween.start(0)
		dummyTween.start(0).chain(function() {

			dummy.dispose()
			message.dispose()

		})
	})

} ()