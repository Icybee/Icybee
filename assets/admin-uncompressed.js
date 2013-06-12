/*
 * This file is part of the Icybee package.
 *
 * (c) Olivier Laviale <olivier.laviale@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

String.implement
({
	/**
	 * Shortens a string to given length from a given position.
	 *
	 * Example:
	 *
	 * var str = "Raccourcir une chaine de caractères à des endroits divers et variés.";
	 *
	 * console.log(str.shorten(32, 0)); // remove characters from the beginning of the string
	 * console.log(str.shorten(32, .25));
	 * console.log(str.shorten(32, .5)); // remove characters from the middle of the string
	 * console.log(str.shorten(32, .75));
	 * console.log(str.shorten(32, 1)); // remove characters from the end of the string
	 *
	 * @param int length
	 * @param float position
	 *
	 * @return string A string shortened.
	 */
	shorten: function(length, position)
	{
		if (length === undefined)
		{
			length = 32
		}

		if (position === undefined)
		{
			position = .75
		}

		var l = this.length

		if (l <= length) return this

		length--
		position = Math.round(position * length)

		if (position == 0)
		{
			return '…' + this.substring(l - length)
		}
		else if (position == length)
		{
			return this.substring(0, length) + '…'
		}

		return this.substring(0, position) + '…' + this.substring(l - (length - position))
	}
})/*
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
})/*
 * This file is part of the Icybee package.
 *
 * (c) Olivier Laviale <olivier.laviale@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Provides a notice for long XHR.
 */
;!function() {

	var dummy = null
	, dummyTween = null
	, message = null
	, messageTween = null

	ICanBoogie.XHR.NOTICE_DELAY = 1000

	window.addEvent('icanboogie.xhr.shownotice', function() {

		if (!dummy)
		{
			dummy = new Element('div.xhr-dummy')
			message = new Element('div.xhr-message')
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

} ()/*
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
})/*
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
window.addEvent('brickrouge.update', function(ev) {

	ev.target.getElements('textarea.code').each(function(el) {

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
})/**
 * Changes the state of the wrapper when the input is clicked.
 */
window.addEvent('click:relay(label.checkbox-wrapper)', function(ev, el) {

	var target = ev.target

	if (target.getParent() != el) return

	el[target.checked ? 'addClass' : 'removeClass']('checked')

})

/**
 * Sets the initial state of the wrapper according to the state of the input.
 */
window.addEvent('brickrouge.update', function(ev) {

	ev.target.getElements('label.checkbox-wrapper').each(function(el) {

		var input = el.getElement('input')

		if (input.checked)
		{
			el.addClass('checked')
		}

		if (input.disabled)
		{
			el.addClass('disabled')
		}

		if (input.readonly)
		{
			el.addClass('readonly')
		}
	})
})
;!function() {

	var PopoverImage = new Class
	({
		initialize: function(el, src)
		{
			this.src = src
			this.element = document.id(el)
			this.element.addEvents({
				mouseenter: this.onMouseEnter.bind(this),
				mouseleave: this.onMouseLeave.bind(this)
			})
		},

		onMouseEnter: function()
		{
			this.cancel = false

			;(this.popover ? this.show : this.load).delay(this.element.get('data-popover-delay') || 100, this)
		},

		onMouseLeave: function()
		{
			this.cancel = true
			this.hide()
		},

		load: function()
		{
			if (this.cancel || this.popover) return

			new Asset.image(this.src, {

				onload: function(popover)
				{
					var targetSelector = this.element.get('data-popover-target')
					, target = this.element
					, coord

					if (targetSelector)
					{
						target = this.element.getParent(targetSelector) || target
					}

					coord = target.getCoordinates()

					popover.id = 'popover-image'
					popover.setStyles
					(
						{
							top: coord.top + (coord.height - popover.height) / 2 - 2,
							left: coord.left + coord.width + 20,
							opacity: 0
						}
					)
					popover.set('tween', { duration: 'short', link: 'cancel' })
					popover.addEvent('mouseenter', this.onMouseLeave.bind(this))

					// check concurrency

					if (this.popover)
					{
						popover.destroy()

						return
					}

					this.popover = popover
					this.show()
				}
				.bind(this)
			})
		},

		show: function()
		{
			if (this.cancel) return

			var popover = this.popover

			document.body.appendChild(popover)

			popover.fade('in')
		},

		hide: function()
		{
			var popover = this.popover

			if (!popover || !popover.getParent()) return

			this.popover = null

			popover.get('tween').start('opacity', 0).chain(function() {

				document.body.removeChild(popover)

				delete popover
			})
		}
	})

	, popovers = []

	document.body.addEvent('mouseenter:relay([data-popover-image])', function(ev, el) {

		var uniqueNumber = el.uniqueNumber
		, popover

		if (popovers[uniqueNumber]) return

		popover = new PopoverImage(el, el.get('data-popover-image'))
		popover.load()

		popovers[uniqueNumber] = popover

	})

} ()/*
 * This file is part of the Icybee package.
 *
 * (c) Olivier Laviale <olivier.laviale@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Reset button for default values
 */
;!function() {

	var controls = []

	function resetToDefault(reset)
	{
		var target = reset.retrieve('target')

		target.set('value', target.get('data-default-value'))
		target.fireEvent('change')

		reset.addClass('hidden')
	}

	document.body.addEvent('click:relay(.reset-default-value)', function(ev, reset) {

		resetToDefault(reset)

	})

	document.body.addEvent('keypress:relay(.reset-default-value)', function(ev, reset) {

		if (ev.key != 'enter' && ev.key != 'space') return

		resetToDefault(reset)

	})

	window.addEvent('brickrouge.update', function(ev) {

		ev.target.getElements('[data-default-value]').each(function(el) {

			if (controls.indexOf(el) !== -1) return

			controls.push(el)

			var reset = new Element('span.btn.btn-warning.reset-default-value[tabindex="0"]', { html: '<i class="icon-edit icon-white"></i> Reset' })
			, container = el.getParent('.controls')

			reset.store('target', el)

			if (el.get('value') == el.get('data-default-value'))
			{
				reset.addClass('hidden')
			}

			el.addEvent('change', function() {

				reset[this.get('value') == this.get('data-default-value') ? 'addClass' : 'removeClass']('hidden')

			})

			if (container)
			{
				reset.inject(container)
			}
			else
			{
				reset.inject(el, 'after')
			}
		})
	})

} ()/*
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
})/*
 * This file is part of the Icybee package.
 *
 * (c) Olivier Laviale <olivier.laviale@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

Brickrouge.Widget.Spinner = new Class
({
	Implements: [ Options, Events ],

	initialize: function(el, options)
	{
		this.element = el = document.id(el)
		this.setOptions(options)

		this.control = el.getElement('input')
		this.content = el.getElement('.spinner-content')
		this.popover = null
		this.resetValue = null
		this.resetContent = null

		el.addEvent('click', function(ev) {

			ev.stop()
			this.open()

		}.bind(this))
	},

	open: function()
	{

	},

	/**
	 * Translate the internal representation of the value into a string
	 */
	setValue: function(value)
	{
		if (this.content)
		{
			var formatedValue = this.formatValue(value)
			, type = typeOf(formatedValue)

			this.content.empty()

			if (type == 'element' || type == 'elements')
			{
				this.content.adopt(formatedValue)
			}
			else if (type == 'string')
			{
				this.content.innerHTML = formatedValue
			}
		}

		this.element[value ? 'removeClass' : 'addClass']('placeholder')

		this.control.set('value', this.encodeValue(value))
	},

	/**
	 * Get the string value for the input and translate it into its internal representation.
	 */
	getValue: function()
	{
		return this.decodeValue(this.control.get('value'))
	},

	/**
	 * Encodes the internal representation of the value into a string.
	 *
	 * @param value
	 *
	 * @return string
	 */
	encodeValue: function(value)
	{
		return value
	},

	/**
	 * Decode the string encoded value into its internal representation.
	 *
	 * @param value
	 *
	 * @return mixed
	 */
	decodeValue: function(value)
	{
		return value
	},

	formatValue: function(value)
	{
		return value
	},

	attachAdjust: function(adjust)
	{

	}
})/*
 * This file is part of the Icybee package.
 *
 * (c) Olivier Laviale <olivier.laviale@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

this.Icybee = {

	Widget: {

		AdjustPopover: new Class
		({
			Extends: Brickrouge.Popover,
			Implements: [ Options, Events ],

			initialize: function(el, options)
			{
				this.parent(el, options)

				this.adjust = null
				this.selected = null
			},

			getAdjust: function()
			{
				return this.element.getElement('.popover-content :first-child').get('widget')
			},

			show: function()
			{
				this.parent()

				this.adjust = this.getAdjust()

				if (this.adjust)
				{
					this.adjust.addEvent('results', this.repositionCallback)
					this.adjust.addEvent('adjust', this.quickRepositionCallback)
				}
			}
		})
	}
}