
BrickRouge.Widget.PopNode = new Class
({
	Implements: [ Options, Events ],

	options:
	{
		placeholder: 'Select an entry',
		constructor: 'system.nodes',
		adjust: 'adjust-node',
		previewWidth: 64,
		previewHeight: 64
	},

	initialize: function(el, options)
	{
		this.element = $(el);

		this.setOptions(options);

		this.element.addEvent
		(
			'click', this.fetchAdjust.bind(this)
		);

		this.title_el = this.element.getElement('span.title');
		this.key_el = this.element.getElement('input.key');
		this.preview_el = this.element.getElement('img');
	},

	fetchAdjust: function()
	{
		this.title_back = this.title_el.get('html');
		this.key_back = this.key_el.value;

		var preview_el = this.preview_el;

		if (preview_el)
		{
			this.preview_back = preview_el.get('src');
		}

		if (this.popup)
		{
			this.popup.open({ selected: this.key_el.value });

			return;
		}

		if (preview_el)
		{
			preview_el.addEvent
			(
				'load', function()
				{
					if (!this.popup)
					{
						return;
					}

					this.popup.reposition();
				}
				.bind(this)
			);
		}

		if (!this.fetchAdjustOperation)
		{
			/*
			this.fetchAdjustOperation = new Request.Element
			({
				url: '/api/components/' + this.options.adjust + '/popup',
				onSuccess: this.setupAdjust.bind(this)
			});
			*/

			this.fetchAdjustOperation = new Request.Widget
			(
				this.options.adjust + '/popup', this.setupAdjust.bind(this)
			);

//			this.fetchAdjustOperation = new Request.Widget(this.options.adjust + '/popup', this.setupAdjust.bind(this));
		}

		this.fetchAdjustOperation.get({ selected: this.key_el.value, constructor: this.options.constructor });
	},

	setupAdjust: function(popElement)
	{
		this.popup = new BrickRouge.Widget.Popup.Adjust
		(
			popElement,
			{
				anchor: this.element
			}
		);

		this.popup.open();

		/*
		 * The adjust object is available after the `elementsready` event has been fired. The event
		 * is fired when the popup is opened.
		 */

		this.popup.adjust.addEvent('change', this.onChange.bind(this));
		this.popup.addEvent('closeRequest', this.onCloseRequest.bind(this));
	},

	onChange: function(ev)
	{
		var entry = ev.target;

		var entry_nid = entry.get('data-nid');
		var entry_title = entry.get('data-title');
		var entry_path = entry.get('data-path');

		var title_el = this.title_el;
		var key_el = this.key_el;
		var preview_el = this.preview_el;

		title_el.set('text', entry_title);
		title_el.set('title', entry_title);
		key_el.set('value', entry_nid);

		this.element.removeClass('empty');

		if (preview_el && entry_nid)
		{
			preview_el.src = '/api/resources.images/' + entry_nid + '/thumbnail?w=' + this.options.previewWidth + '&h=' + this.options.previewHeight + '&m=surface&f=png';
		}
		else
		{
			this.popup.reposition();
		}
	},

	onCloseRequest: function(ev)
	{
		var title_el = this.title_el;
		var key_el = this.key_el;
		var preview_el = this.preview_el;

		switch (ev.mode)
		{
			case 'cancel':
			{
				this.cancel();
			}
			break;

			case 'none':
			{
				title_el.set('html', '<em>' + this.options.placeholder + '</em>');
				key_el.value = '';

				if (preview_el)
				{
					preview_el.set('src', '');
				}
			}
			// continue

			case 'continue':
			{
				this.use();
			}
			break;
		}

		this.element[(0 + key_el.value.toInt() ? 'remove' : 'add') + 'Class']('empty');

		this.popup.close();
	},

	cancel: function()
	{
		this.title_el.set('html', this.title_back);
		this.key_el.value = this.key_back;

		if (this.preview_el)
		{
			this.preview_el.set('src', this.preview_back);
		}
	},

	use: function()
	{
		this.element.fireEvent('change', {});
		this.key_el.fireEvent('change', {});
	},

	reset: function()
	{

	}
});