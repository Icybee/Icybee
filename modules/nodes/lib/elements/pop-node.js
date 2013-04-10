
Brickrouge.Widget.PopNode = new Class
({

	Extends: Brickrouge.Widget.Spinner,

	Implements: [ Options, Events ],

	options:
	{
		placeholder: 'Select an entry',
		constructor: 'nodes',
		adjust: 'adjust-node',
		previewWidth: 64,
		previewHeight: 64
	},

	initialize: function(el, options)
	{
		this.parent(el, options)

		this.popover = null
		this.fetchAdjustOperation = new Request.Widget
		(
			this.options.adjust + '/popup', this.setupAdjust.bind(this)
		)
	},

	open: function()
	{
		var value = this.getValue()

		this.resetValue = value

		if (this.popover)
		{
			this.popover.show({ selected: value })

			return
		}

		this.fetchAdjustOperation.get({ selected: value, constructor: this.options.constructor })
	},

	setupAdjust: function(popElement)
	{
		this.popover = this.popup = new Icybee.Widget.AdjustPopover(popElement, {

			anchor: this.element

		})

		this.popover.show()

		/*
		 * The adjust object is available after the `brickrouge.construct` event has been fired.
		 * The event is fired when the popup is opened.
		 */

		this.popover.adjust.addEvent('change', this.change.bind(this))
		this.popover.addEvent('action', this.onAction.bind(this))
	},

	onAction: function(ev)
	{
		switch (ev.action)
		{
			case 'cancel':
				this.cancel()
				break
			case 'remove':
				this.remove() // continue
			case 'use':
				this.use()
		}

		this.popover.hide()
	},

	change: function(ev)
	{
		this.setValue(ev.target.get('data-nid'))
	},

	cancel: function()
	{
		this.setValue(this.resetValue)
	},

	remove: function()
	{
		this.setValue('')
	},

	use: function()
	{
		this.element.fireEvent('change', {});
	},

	reset: function()
	{

	}
});