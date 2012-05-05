window.addEvent
(
	'domready', function()
	{
		this.list = $(document.body).getElement('ul.widgets-selector');
		
		this.sortable = new Sortables
		(
			this.list,
			{
				clone: true,
				constrain: true,
				//revert: { duration: 500, transition: 'elastic:out' },
				opacity: 0.2,
		
				onStart: function(el, clone)
				{
					clone.setStyle('z-index', 10000);
				}
			}
		);
	}
);