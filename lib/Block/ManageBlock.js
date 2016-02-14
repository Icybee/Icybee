!function (Brickrouge) {

	/**
	 * Events:
	 *
	 * - `update`: Fired after the element was updated by the `update()` method.
	 *
	 * Also, the event `icybee.manageblock.ready` is fired on the window when the widget is ready,
	 * which happens when it is first initialized and when it is updated.
	 */
	Brickrouge.Widget.ManageBlock = new Class({

		Implements: [ Events, Options ],

		options: {

			/* onUpdate: function(){} */

		},

		initialize: function(el, options)
		{
			this.element = el = document.id(el)
			this.destination = null
			this.blockName = null
			this.updateRequest = null

			this.setOptions(options)

			/**
			 * Browse through pages using arrow keys
			 */
			document.body.addEvent('keypress', function(ev) {

				if (ev.target != document.body) return

				if (ev.key == 'left')
				{
					this.update('start=previous')
				}
				else if (ev.key == 'right')
				{
					this.update('start=next')
				}

			}.bind(this))

			/**
			 * Update the element according to the selected conditions
			 */
			el.addEvent('click:relay(a[href^="?"])', function(ev, target) {

				ev.preventDefault()

				this.update(target.get('href').substring(1))

			}.bind(this))

			this.attach(el.getElement('form'))
		},

		getUpdateRequest: function()
		{
			return this.updateRequest || (this.updateRequest = new Request({

				onSuccess: function(response)
				{
					var el = Elements.from(response)
					, form = el.getElement('form')

					form.replaces(this.element.getElement('form'))

					this.fireEvent('update', form)
				}
				.bind(this)
			}))
		},

		update: function(params)
		{
			if (typeOf(params) == 'string')
			{
				if (params[0] == '?')
				{
					params = params.substring(1)
				}

				params = params.parseQueryString()
			}

			params = params || {}
			params.decorate_flags = 0

			this.getUpdateRequest().get(params)
		},

		/**
		 * Attache an element to the widget.
		 *
		 * `A` child elements with an `href` starting with "?" are considered as commands to update the
		 * manager element. This is also true for `A` elements with a `rel` equal to "manager", wherever
		 * they are.
		 */
		attach: function(form)
		{
			this.destination = form.getElement('[name="' + ICanBoogie.Operation.DESTINATION + '"]').value
			this.blockName = form.getElement('[name="#manager-block"]').value

			window.fireEvent('icybee.manageblock.ready', this)
		}
	})

	Brickrouge.register('ManageBlock', function (element, options) {

		return new Brickrouge.Widget.ManageBlock(element, options)

	})

} (Brickrouge);

/*
 * Extra
 */

!function() {

	var manager = null

	/**
	 * Move listview controls to the ActionBar.
	 */
	function updateControls(fragment)
	{
		var actionbarControls = document.body.getElement('.actionbar-controls')
		, currentControls = document.body.getElement('.actionbar .listview-controls')
		, controls = fragment.getElement('.listview .listview-controls')
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
			controls.inject(actionbarControls)
		}
	}

	/**
	 * Updates the `manager` value and setup a listener on the `update` event to update
	 * the controls.
	 */
	window.addEvent('icybee.manageblock.ready', function(m) {

		manager = m
		manager.addEvent('update', updateControls)

		updateControls(manager.element)

	})

	/**
	 * Setting up the search box.
	 */
	window.addEvent('domready', function() {

		var container = document.body.getElement('.listview-search')
		, search = container.getElement('input')
		, searchLast = null

		function rethinkDisplay()
		{
			container[search.value ? 'addClass' : 'removeClass']('active')
		}

		search.addEvents
		({
			focus: function()
			{
				container.addClass('focus')
			},

			blur: function()
			{
				container.removeClass('focus')
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

				rethinkDisplay()

				if (value != searchLast)
				{
					manager.update({ q: value })
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

		rethinkDisplay()
	})

	document.body.addEvent('click:relay([href][rel="manager"])', function(ev, el) {

		ev.stop()

		manager.update(el.get('href'))

	})

	var OperationHandler = new Class({

		Implements: [ Options, Events ],

		options: { /*

			onComplete: function(){},
			onFailure: function(){},
			onSuccess: function(){},*/

		},

		initialize: function(operationName, keys)
		{
			this.operationName = operationName
			this.keys = keys
			this.widget = null
		},

		run: function()
		{
			new Request.Element({

				url: 'query-operation/' + manager.destination + '/' + this.operationName,

				onSuccess: function(element, response)
				{
					var managerEl = manager.element

					managerEl.set('slide', { duration: 'short', resetHeight: true })
					managerEl.get('slide').slideOut().chain(function() {

						element.set('slide', { duration: 'short', resetHeight: true })
						element.getParent().inject(managerEl.getParent(), 'after')
						element.slide('hide').slide('in')

						this.widget = Brickrouge.from(element)
						this.widget.addEvent('cancel', this.cancel.bind(this))
						this.widget.addEvent('success', this.success.bind(this))

					}.bind(this))

				}.bind(this)

			}).get({ keys: this.keys.join('|') })
		},

		cancel: function()
		{
			var widget = this.widget
			, el = widget.element

			this.widget = null

			el.get('slide').slideOut().chain(function() {

				el.destroy()

				manager.element.slide('in')

				this.fireEvent('complete').fireEvent('cancel')

			}.bind(this))
		},

		success: function()
		{
			var widget = this.widget
			, el = widget.element

			this.widget = null

			manager.selectNone()

			el.get('slide').slideOut().chain(function() {

				function show() {

					manager.removeEvent('update', show)
					manager.element.slide('in')

				}

				el.destroy()

				manager.addEvent('update', show)
				manager.update()

				this.fireEvent('complete').fireEvent('success')

			}.bind(this))
		}
	})

	Brickrouge.Widget.ActionBarOperations = new Class({

		Implements: Options,

		options:
		{
			patternOne: "One item selected",
			patternOther: ":count items selected"
		},

		initialize: function(el, options)
		{
			this.element = el = document.id(el)
			this.wrapper = new Element('div.actionbar-actions-wrapper')
			this.wrapper.wraps(el)
			this.setOptions(options)
			this.selected = []

			window.addEvent('icybee.manageblock.ready', function(manager) {

				manager.addEvent('select', function(selected) {

					this.updateSelection(selected)

				}.bind(this))

			}.bind(this))

			el.addEvent('click:relay([data-operation])', function(ev, el) {

				this.queryOperation(el.get('data-operation'))

			}.bind(this))

			el.addEvent('click:relay([data-dismiss="selection"])', function() {

				manager.selectNone()

			})
		},

		formatCount: function(count)
		{
			var pattern = this.options[count == 1 ? 'patternOne' : 'patternOther' ]

			return pattern.replace(':count', count)
		},

		updateSelection: function(selected)
		{
			var el = this.element
			, count = selected.length

			if (count)
			{
				el.getElement('.count').innerHTML = this.formatCount(count)
			}

			this.selected = selected
			this.wrapper[count ? 'addClass' : 'removeClass']('show')
		},

		queryOperation: function(operation)
		{
			var handler = new OperationHandler(operation, this.selected)
			, el = this.element

			function done()
			{
				el.removeClass('working')
			}

			handler.addEvent('complete', done)

			el.addClass('working')

			handler.run()
		}

	})

	Brickrouge.register('ActionBarOperations', function (element, options) {

		return new Brickrouge.Widget.ActionBarOperations(element, options)

	})

} ()
