Brickrouge.Widget.Pop = new Class
({
	Implements: [ Options, Events ],

	initialize: function(el, options)
	{
		this.element = $(el);
		this.setOptions(options);

		this.element.addEvent
		(
			'click', function(ev)
			{
				ev.stop();

				this.pop();
			}
			.bind(this)
		);
	},

	pop: function()
	{

	},

	setValue: function(value)
	{

	},

	getValue: function()
	{

	},

	attachAdjust: function(adjust)
	{

	}
});

Brickrouge.Widget.PopThumbnailVersion = new Class
({
	Extends: Brickrouge.Widget.Pop,

	pop: function()
	{
		this.resetValue = this.getValue();

		if (this.popover)
		{
			this.popover.adjust.setValue(this.resetValue);
			this.popover.show();
		}
		else
		{
			new Request.Widget
			(
				'adjust-thumbnail-version/popup', function(widget)
				{
					this.attachAdjust(widget);

					this.popover.show();

					/*
					 * The adjust object is available after the `elementsready` event has been fired. The event
					 * is fired when the popover is opened.
					 */

					//this.popover.adjust.addEvent('change', this.change.bind(this));
					this.popover.addEvent('action', this.onAction.bind(this));
				}
				.bind(this)
			)
			.get({ value: this.getValue() });
		}
	},

	setValue: function(value)
	{
		if (typeOf(value) == 'object')
		{
			value = JSON.encode(value)
		}

		this.element.set('value', value)
	},

	getValue: function()
	{
		return this.element.get('value')
	},

	attachAdjust: function(adjust)
	{
		this.popover = new Icybee.Widget.AdjustPopover(adjust, { anchor: this.element })
	},

	change: function(ev)
	{
		console.log('change: ', ev);
	},

	onAction: function(ev)
	{
		console.log('action:', ev)

		switch (ev.action)
		{
			case 'continue':
				var value = this.element.toQueryString().parseQueryString()

				if (!value.w && !value.h)
				{
					value = null
				}

				this.setValue(value)
				break

			case 'none':
				this.setValue(null)
				break

			case 'cancel':
				this.setValue(this.resetValue)
				break
		}

		this.popover.hide()
	}
});