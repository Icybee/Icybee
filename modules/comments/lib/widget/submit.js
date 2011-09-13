var WdTextMarkPreview = new Class
({
	initialize: function(source)
	{
		this.source = $(source);

		this.source.addEvent('keypress', this.handle.bind(this));

		this.lastValue = null;

		if (this.source.value)
		{
			this.update();
		}
	},

	show: function()
	{
		this.target = new Element('div', { 'class': 'preview' });

		var header = new Element('h5', { 'html':  'Apperçu de votre commentaire' });
		this.wrapper = new Element('div', { 'class': 'preview-wrapper' });

		this.wrapper.appendChild(header);
		this.wrapper.appendChild(this.target);

		this.wrapper.inject(this.source.getParent('div.clearfix'), 'after');
	},

	hide: function()
	{
		if (!this.wrapper)
		{
			return;
		}

		this.wrapper.destroy();
		this.target.destroy();

		this.wrapper = null;
		this.target = null;
	},

	handle: function(ev)
	{
		if (this.timer)
		{
			clearTimeout(this.timer);
		}

		this.timer = this.update.delay(500, this);
	},

	update: function()
	{
		var value = this.source.value;

		if (value == this.lastValue)
		{
			return;
		}

		this.lastValue = value;

		var op = new Request.API
		({
			url: 'comments/preview',
			onSuccess: function(response)
			{
				if (!response.rc)
				{
					this.hide();

					return;
				}

				if (!this.target)
				{
					this.show();
				}

				this.target.innerHTML = response.rc;
			}
			.bind(this)
		});

		op.get({contents: value});
	}
});

window.addEvent
(
	'domready', function()
	{
		new WdTextMarkPreview($(document.body).getElement('form.wd-feedback-comments textarea'));
	}
);