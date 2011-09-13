Widget.Pop = new Class
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

	getValue: function()
	{

	},

	attachAdjust: function(adjust)
	{

	}
});

Widget.PopThumbnailVersion = new Class
({
	Extends: Widget.Pop,

	pop: function()
	{
		this.resetValue = this.getValue();

		if (this.popup)
		{
			this.popup.adjust.setValue(this.resetValue);
			this.popup.open();
		}
		else
		{
			new Request.Widget
			(
				'adjust-thumbnail-version/popup', function(widget)
				{
					this.attachAdjust(widget);

					this.popup.open();

					/*
					 * The adjust object is available after the `elementsready` event has been fired. The event
					 * is fired when the popup is opened.
					 */

					//this.popup.adjust.addEvent('change', this.change.bind(this));
					this.popup.addEvent('closeRequest', this.onCloseRequest.bind(this));
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
			value = JSON.encode(value);
		}

		this.element.getElement('input').set('value', value);
	},

	getValue: function()
	{
		return this.element.getElement('input').get('value');
	},

	attachAdjust: function(adjust)
	{
		this.popup = new Widget.Popup.Adjust
		(
			adjust,
			{
				anchor: this.element
			}
		);

		this.popup.element.addClass('black');
	},

	change: function(ev)
	{
		console.log('change: ', ev);
	},

	onCloseRequest: function(ev)
	{
		switch (ev.mode)
		{
			case 'continue':
				var value = ev.target.element.toQueryString().parseQueryString();

				if (!value.w && !value.h)
				{
					value = null;
				}

				this.setValue(value);
				break;

			case 'none':
				this.setValue(null);
				break;

			case 'cancel':
				this.setValue(this.resetValue);
				break;
		}

		this.popup.close();
	}
});