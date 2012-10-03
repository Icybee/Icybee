Brickrouge.Tabbable = new Class({

	/**
	 * Initialize the `nav` and `content` properties with the `.nav-tabs` and `.tab-content`
	 * descendants.
	 *
	 * @param Element el A `.tabbable` element.
	 * @param object options
	 */
	initialize: function(el)
	{
		this.element = el = document.id(el)

		this.nav = el.getFirst('.nav-tabs')
		this.content = el.getFirst('.tab-content')

		this.element.addEvent('keydown', this.onKeyDown.bind(this))

		this.onChange()
	},

	/**
	 * Returns the tab at the specified position.
	 *
	 * @param number|Element i The position can be specified as an index offset, a tab element or
	 * a pane element.
	 *
	 * @return Element
	 */
	resolveTab: function(i)
	{
		if (typeOf(i) == 'number')
		{
			return this.nav.getChildren()[i]
		}

		if (this.nav.contains(i))
		{
			return i
		}

		i = this.content.getChildren().indexOf(i)

		if (i == -1)
		{
			return
		}

		return this.nav.getChildren()[i]
	},

	/**
	 * Returns the index position of the specified tab.
	 *
	 * @param tab[optional] If the tab is not specified the active tab is as instead.
	 *
	 * @return number
	 */
	getPosition: function(tab)
	{
		if (!tab)
		{
			tab = this.nav.getElement('.active')
		}

		return this.nav.getChildren().indexOf(tab)
	},

	onChange: function()
	{
		this.element[this.content.getChildren().length == 1 ? 'addClass' : 'removeClass']('lonely-tab')
	},

	onKeyDown: function(ev)
	{
		var i

		switch (ev.key)
		{
			case 'left':
				ev.stop()
				i = this.getPosition()
				this.activateTab(i ? i - 1 : this.content.getChildren().length - 1)
				break
			case 'right':
				ev.stop()
				i = this.getPosition()
				this.activateTab(i == this.content.getChildren().length - 1 ? 0 : i + 1)
				break
		}
	},

	/**
	 * Activates a tab.
	 *
	 * @param number|Element i The tab to activate. If an element is provided it can either be a
	 * tab or a pane.
	 */
	activateTab: function(i)
	{
		var nav = this.nav
		, content = this.content
		, tabs = this.nav.getChildren()
		, panes = this.content.getChildren()

		if (typeOf(i) == 'element')
		{
			var el = i

			i = tabs.indexOf(el)

			if (i == -1)
			{
				i = panes.indexOf(el)
			}

			if (i == -1)
			{
				throw new Error('The element provided is not a tab nor a pane.', el)
			}
		}

		if (i < 0 || i > panes.length)
		{
			throw new Error('Position is out of range.', i)
		}

		nav.getFirst('.active').removeClass('active')
		content.getFirst('.active').removeClass('active')

		tabs[i].addClass('active')
		panes[i].addClass('active')
	},

	/**
	 * Removes a tab.
	 *
	 * @param number|Element i The tab to remove.
	 */
	removeTab: function(i)
	{
		var tabs = this.nav.getChildren()
		, panes = this.content.getChildren()
		, tab = this.resolveTab(i)

		if (tabs.length === 2)
		{
			alert('The last tab cannot be removed.')

			return
		}

		i = tabs.indexOf(tab)

		this.activateTab(i ? i - 1 : 1)

		tabs[i].destroy()
		panes[i].destroy()

		this.onChange()
	}
})

/**
 *
 */
Brickrouge.Widget.TabbableEditor = new Class({

	Extends: Brickrouge.Tabbable,

	Implements: [ Options ],

	options: {

		controlName: null

	},

	initialize: function(el, options)
	{
		this.parent($(el).getFirst('.tabbable'))
		this.setOptions(options)
		this.addTabTrigger = this.nav.getElement('a[data-create="tab"]')
		this.addTabTriggerContainer = this.addTabTrigger.getParent('li')
		this.controlAnchorMap = {}
		this.attachedControls = {}

		this.addTabTrigger.addEvent('click', function(ev) {

			ev.stop()

			this.addTab()

		}.bind(this))

		this.nav.addEvent('click:relay([data-removes="tab"])', function(ev, el) {

			ev.stop()

			this.removeTab(el.getParent('li'))

		}.bind(this))

		this.attach()
	},

	updateOrders: function()
	{
		this.tabsOrder = this.nav.getChildren()
		this.tabsOrder.pop() // remove addTabTrigger

		this.panesOrder = this.content.getChildren()
	},

	attach: function()
	{
		var el = this.element
		, sortable = new Sortables(this.nav, {

			handle: 'a',
			unDraggableTags: [],
			onComplete: function()
			{
				this.addTabTriggerContainer.inject(this.nav, 'bottom')

				var tabs = this.nav.getChildren()
				, order = []
				, changes = 0

				tabs.each(function(el, y) {

					var i = this.tabsOrder.indexOf(el)

					if (i == -1) return
					if (i != y) changes++

					order.push(this.panesOrder[i])

				}, this)

				if (changes)
				{
					this.content.adopt(order)
					this.updateOrders()
				}
			}
			.bind(this)
		})

		this.updateOrders();

		sortable.removeItems(this.addTabTriggerContainer)

		//
		//
		//

		this.content.getElements('.tab-pane [data-provides="title"]').each(function(control) {

			var container = control.getParent('.tab-content')
			, uniqueNumber = control.uniqueNumber

			if (container != this.content || this.attachedControls[uniqueNumber] !== undefined) return

			this.attachedControls[uniqueNumber] = control

			control.addEvents({

				change: this.onTitleChange.bind(this),
				keyup: this.onTitleChange.bind(this)

			})

		}, this)
	},

	onTitleChange: function(ev)
	{
		var control = ev.target
		, value = control.get('value')
		, anchor = this.getTitleReciever(control)

		anchor.set('text', value ? value : '?')
	},

	/**
	 * Returns the element that should be updated when the title of the tab is modified.
	 *
	 * @param control The control used to edit the title of the tab.
	 *
	 * @returns Element
	 */
	getTitleReciever: function(control)
	{
		var uniqueNumber = control.uniqueNumber

		reciever = this.controlAnchorMap[uniqueNumber]

		if (reciever)
		{
			return reciever
		}

		var tabPane = control.getParent('.tab-pane')
		, index = this.content.getElements('.tab-pane').indexOf(tabPane)
		, recievers = this.nav.getElements('[data-recieves="title"]')
		, reciever = recievers[index]

		this.controlAnchorMap[uniqueNumber] = reciever

		return reciever
	},

	addTab: function()
	{
		var tempPane = new Element('div.tab-pane', { html: "<em>Loading pane editor…</em>" })
		, anchor = new Element('a', { html: '<span data-recieves="text">?</span><span class="close" data-removes="tab">&times;</span>', 'data-toggle': 'tab', 'href': '#' })
		, tempTab = new Element('li').adopt(anchor)
		, i = this.getPosition()

		try
		{
			new Request.Element({

				url: 'editors/tabbable/new-pane',
				onSuccess: function(pane, response)
				{
					if (response.tab)
					{
						var tab = Elements.from(response.tab).shift()

						tab.replaces(tempTab)
					}
					else
					{
						anchor.set('text', pane.getElement('[data-provides="title"]').get('value'))
					}

					pane.replaces(tempPane)

					this.attach()

					Brickrouge.updateDocument(pane)

				}.bind(this)

			}).get({ control_name: this.options.controlName })
		}
		catch (e)
		{
			alert('Unable to load pane.')

			console.log(e)
		}


		tempTab.inject(this.nav.getChildren()[i], 'after')
		tempPane.inject(this.content.getChildren()[i], 'after')

		this.activateTab(tempTab)
		this.onChange()
	}
})
