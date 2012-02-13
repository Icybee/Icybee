
Brickrouge.Widget.PopNode = new Class
({
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
		this.element = $(el)
		this.popover = null
		this.fetchAdjustOperation = null
		this.title = this.element.getElement('span.title')
		this.preview = this.element.getElement('img')

		this.setOptions(options)

		this.element.addEvent('click', this.onClick.bind(this))
	},

	onClick: function()
	{
		this.fetchAdjust()
	},

	fetchAdjust: function()
	{
		var preview = this.preview
		, value = this.element.get('value')

		this.title_back = this.title.get('html')
		this.key_back = value

		if (preview)
		{
			this.preview_back = preview.get('src')
		}

		if (this.popover)
		{
			this.popover.show({ selected: value })

			return
		}

		if (preview)
		{
			preview.addEvent
			(
				'load', function()
				{
					if (!this.popover) return

					this.popover.reposition()
				}
				.bind(this)
			);
		}

		if (!this.fetchAdjustOperation)
		{
			this.fetchAdjustOperation = new Request.Widget
			(
				this.options.adjust + '/popup', this.setupAdjust.bind(this)
			)
		}

		this.fetchAdjustOperation.get({ selected: value, constructor: this.options.constructor })
	},

	setupAdjust: function(popElement)
	{
		this.popover = this.popup = new Icybee.Widget.AdjustPopover
		(
			popElement,
			{
				anchor: this.element
			}
		);

		this.popover.show()

		/*
		 * The adjust object is available after the `elementsready` event has been fired. The event
		 * is fired when the popup is opened.
		 */

		this.popover.adjust.addEvent('change', this.onChange.bind(this))
		this.popover.addEvent('action', this.onAction.bind(this))
	},

	onAction: function(ev)
	{
		switch (ev.action)
		{
			case 'cancel': this.cancel(); break
			case 'remove': this.remove() // continue
			case 'use': this.use()
		}

		this.element[(0 + this.element.get('value').toInt() ? 'remove' : 'add') + 'Class']('placeholder')

		this.popover.hide()
	},

	onChange: function(ev)
	{
		var entry = ev.target
		, nid = entry.get('data-nid')
		, title = entry.get('data-title')
		, preview = this.preview

		this.title.set('text', title).set('title', title)
		this.element.set('value', nid)

		this.element.removeClass('placeholder')

		if (preview && nid)
		{
			preview.src = '/api/resources.images/' + nid + '/' + this.options.previewWidth + 'x' + this.options.previewHeight + '?m=surface&f=png'
		}
		else
		{
			this.popover.reposition()
		}
	},

	cancel: function()
	{
		this.title.set('html', this.title_back)
		this.element.set('value', this.key_back)

		if (this.preview)
		{
			this.preview.set('src', this.preview_back)
		}
	},

	remove: function()
	{
		this.title.set('html', '<em>' + this.options.placeholder + '</em>');
		this.element.set('value', '');

		if (this.preview)
		{
			this.preview.set('src', '');
		}
	},

	use: function()
	{
		this.element.fireEvent('change', {});
	},

	reset: function()
	{

	}
});