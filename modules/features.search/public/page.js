window.addEvent
(
	'domready', function()
	{
		var el = $('search-quick').getElement('input');

		el.form.addEvent
		(
			'submit', function()
			{
				if (el.hasClass('empty'))
				{
					el.value = '';
				}
			}
		);

		var label = el.get('data-placeholder') || 'Search';

		if (!el.value)
		{
			el.addClass('empty');
			el.value = label;
		}

		el.addEvents
		({
			focus: function()
			{
				if (this.hasClass('empty'))
				{
					this.value = '';
					this.removeClass('empty');
				}
			},

			blur: function()
			{
				if (!this.value)
				{
					this.addClass('empty');
					this.value = label;
				}
			}
		});
	}
);