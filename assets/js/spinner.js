var WdSpinner = new Class
({
	options:
	{
		delay: 150
	},

	initialize: function(el)
	{
		this.element = $(el);
		this.element.set('tween', { property: 'opacity', duration: 'short', link: 'cancel' });

		this.nestCount = 0;
		this.timer = null;
	},

	start: function()
	{
		this.nestCount++;

		//console.log('start: nestcount: %d', this.nestCount);

		if (this.nestCount > 1)
		{
			return;
		}

		this.timer = this.show.delay(this.options.delay, this);
	},

	finish: function()
	{
		this.nestCount--;

		//console.log('finish: nestcount: %d', this.nestCount);

		if (this.nestCount)
		{
			return;
		}

		if (this.timer)
		{
			clearTimeout(this.timer);

			this.timer = null;
		}

		this.hide();
	},

	show: function()
	{
		this.element.fade('in');
	},

	hide: function()
	{
		this.element.fade('out');
	}
});