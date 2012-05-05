var TaxonomyTagsElement = new Class
({
	initialize: function(el)
	{
		this.element = $(el);

		this.input = this.element.getElement('input[type=text]');
		this.cloud = this.element.getElement('ul.cloud');
		this.tags = this.cloud.getElements('li');

		/*
		this.toggler = this.element.getElements('button')[0];

		this.toggler.addEvent
		(
			'click', function(ev)
			{
				ev.stop();
				
				this.toggle();
			}
			.bind(this)
		);
		*/

		this.tags.each
		(
			function(el)
			{
				el.addEvent
				(
					'click', function(ev)
					{
						ev.stop();

						ev.target.toggleClass('selected');

						this.toInput();
					}
					.bind(this)
				);
			},

			this
		);
		
		this.toTags();
	},

	toTags: function()
	{
		var current = this.input.value.split(',').map(String.trim);

		this.tags.each
		(
			function (el)
			{
				if (current.indexOf(el.innerHTML) != -1)
				{
					el.addClass('selected');
				}
			}
		)
	},

	toInput: function()
	{
		var current = this.input.value.split(',').map(String.trim);

		this.tags.each
		(
			function (el)
			{
				var tag = el.innerHTML;

				if (el.hasClass('selected'))
				{
					current = current.include(tag);
				}
				else
				{
					current = current.erase(tag);
				}
			}
		);

		//
		// remove empty trash thingy
		//

		current = current.erase("");

		this.input.value = current.join(', ');
	}/*,
	
	toggle: function()
	{
		this.element.toggleClass('expanded');
		
		var cl_coords = this.cloud.getCoordinates();
		var in_coords = this.input.getCoordinates();
		
		//console.info('cloud: %a, %a', this.cloud, cl_coords);
		
		cl_h = cl_coords.height;
		
		if (!cl_h)
		{
			return;
		}
		
		this.cloud.setStyle('margin-top', - (cl_h + in_coords.height + 10));
	}
	*/
});

window.addEvent
(
	'domready', function()
	{
		$$('div.taxonomy-tags').each
		(
			function(el)
			{
				new TaxonomyTagsElement(el);
			}
		);
	}
);