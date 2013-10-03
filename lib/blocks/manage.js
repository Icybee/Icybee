Icybee.Manager = {

}

/**
 * `A` child elements with an `href` starting with "?" are considered as commands to update the
 * manager element. This is also true for `A` elements with a `rel` equal to "manager", wherever
 * they are.
 */
var WdManager = new Class
({
	Implements: [ Events ],

	initialize: function()
	{
		this.op = null
	},

	update: function(params)
	{
		if (!this.op)
		{
			this.op = new Request({

				url: document.location.href,

				onSuccess: function(response)
				{
					var el = this.element

					el.innerHTML = response

					this.attach(el)

					Brickrouge.updateDocument(el)
				}
				.bind(this)
			})
		}

		if (typeOf(params) == 'string')
		{
			params = params.parseQueryString()
		}

		params.decorate_flags = 0

		this.op.get(params)
	},

	attach: function(el)
	{
		el = document.id(el)

		this.element = el
//		this.parentElement = el.getParent() // FIXME: WHAT FOR ? is this supposed to be the wrapper ?
		this.destination = el.getElement('[name="' + ICanBoogie.Operation.DESTINATION + '"]').value
		this.blockName = el.getElement('[name="#manager-block"]').value

		//
		// filters
		//

		el.addEvent('click:relay(a[href^="?"])', function(ev, el) {

			ev.preventDefault()

			this.update(el.get('href').substring(1))

		}.bind(this))

		this.fireEvent('ready', {})
	}
})

var manager = new WdManager()

Icybee.manager = manager

window.addEvent('domready', function() {

	manager.attach(document.body.getElement('.block--manage'))

})

window.addEvent('click:relay(a[rel="manager"])', function(ev, el) {

	if (ev.rightClick) return

	ev.preventDefault()

	manager.update(el.get('href').substring(1))
})

/**
 * Browse through pages using arrow keys
 */
window.addEvent('keypress', function(ev) {

	if (ev.target != document.body) return

	if (ev.key == 'left')
	{
		manager.update('start=previous')
	}
	else if (ev.key == 'right')
	{
		manager.update('start=next')
	}

})

window.addEvent('brickrouge.update', function() {

	var actionbarActions = document.body.getElement('.actionbar-actions')
	, controls = document.body.getElement('.listview .listview-controls')
	, currentControls = document.body.getElement('.actionbar .listview-controls')
	, startControl = controls.getElement('[name="start"]')
	, limitControl = controls.getElement('[name="limit"]')

	;[ startControl, limitControl].each(function(control) {

		if (!control) return

		control.addEvent('change', function() {

			manager.update(control.name + '=' + control.get('value'))

		})
	})

	if (currentControls)
	{
		controls.replaces(currentControls)
	}
	else
	{
		controls.inject(actionbarActions)
	}
})

/**
 * Setting up the search box.
 */
window.addEvent('domready', function() {

	var container = document.body.getElement('.listview-search')
	, search = container.getElement('input')
	, searchLast = null
	, label = search.get('data-placeholder') || 'Search'

	if (!search.value)
	{
		search.addClass('placeholder')
		search.value = label
	}

	search.addEvents
	({
		focus: function()
		{
			container.addClass('focus')

			if (search.hasClass('placeholder'))
			{
				search.removeClass('placeholder')
				search.value = ''
			}
		},

		blur: function()
		{
			container.removeClass('focus')

			if (!search.value)
			{
				search.addClass('placeholder')
				search.value = label
			}
		},

		keydown: function(ev)
		{
			if (ev.key == 'enter')
			{
				ev.stop()
			}
		},

		keyup: function(ev)
		{
			if (ev.key == 'esc')
			{
				ev.target.value = ''
			}

			var value = ev.target.value

			container[value ? 'addClass' : 'removeClass']('active')

			if (value != searchLast)
			{
				Icybee.manager.update({ q: value })
			}

			searchLast = value
		}
	})

	container.getElement('button').addEvent('click', function(ev) {

		ev.key = 'esc'
		ev.target = search

		search.fireEvent('keyup', ev)
		search.fireEvent('blur', ev)
	})

	window.addEvent('click:relay([rel="manager/search"][data-action="reset"])', function(ev, el) {

		if (ev.rightClick) return

		ev.stop()

		ev.key = 'esc'
		ev.target = search
		search.fireEvent('keyup', ev)
		search.fireEvent('blur', ev)
	})
})