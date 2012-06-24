Brickrouge.Widget.PopThumbnailVersion = new Class
({
	Extends: Brickrouge.Widget.Spinner,

	initialize: function(el, options)
	{
		this.parent(el, options)

		this.control = this.element.getElement('input')
	},

	open: function()
	{
		this.resetValue = this.getValue()

		if (this.popover)
		{
			this.popover.adjust.setValue(this.resetValue)
			this.popover.show()
		}
		else
		{
			new Request.Widget('adjust-thumbnail-version/popup', function(widget) {

				this.attachAdjust(widget);

				this.popover.show();

				/*
				 * The adjust object is available after the `elementsready` event has been fired. The event
				 * is fired when the popover is opened.
				 */

				//this.popover.adjust.addEvent('change', this.change.bind(this));
				this.popover.addEvent('action', this.onAction.bind(this));

			}.bind(this)).get({ value: this.getValue() })
		}
	},

	encodeValue: function(value)
	{
		if (value && typeOf(value) == 'object')
		{
			value = JSON.encode(value)
		}

		return value
	},

	decodeValue: function(value)
	{
		try
		{
			return JSON.decode(value)
		}
		catch (e) { return null }
	},

	formatValue: function(value)
	{
		if (!value)
		{
			return ''
		}

		if (typeOf(value) == 'string')
		{
			value = value.parseQueryString()
		}

		return '' + (value.w || '<em>auto</em>') + '×' + (value.h || '<em>auto</em>') + ' ' + value.method + ' .' + value.format
	},

	attachAdjust: function(adjust)
	{
		this.popover = new Icybee.Widget.AdjustPopover(adjust, { anchor: this.element })
	},

	change: function(ev)
	{
//		console.log('change: ', ev);
	},

	onAction: function(ev)
	{
		switch (ev.action)
		{
			case 'use':
			{
				var value = ev.popover.element.toQueryString().parseQueryString()

				if (!value.w && !value.h)
				{
					value = null
				}

				this.setValue(value)
			}
			break

			case 'remove':
			{
				this.setValue(null)
			}
			break

			case 'cancel':
			{
				this.setValue(this.resetValue)
			}
			break
		}

		this.popover.hide()
	}
});