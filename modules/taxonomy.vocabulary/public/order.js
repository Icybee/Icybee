window.addEvent
(
	'domready', function()
	{
		new Sortables
		(
			$('taxonomy-order').getElement('ol'),
			{
				clone: true,
				constrain: true,
				opacity: 0.2,

				onStart: function(el, clone)
				{
					clone.setStyle('z-index', 10000);
				},
				
				onComplete: function()
				{
					this.fireEvent('change', {});
				}
				.bind(this)
			}
		);
	}
);