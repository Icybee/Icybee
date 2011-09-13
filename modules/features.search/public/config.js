window.addEvent
(
	'domready', function()
	{
		$$('ul.sortable').each
		(
			function(el)
			{
				new Sortables
				(
					el,
					{
						clone: true,
						constrain: true,
						//revert: { duration: 500, transition: 'elastic:out' },
						opacity: 0.2,
						//handle: 'span.handle',

						onStart: function(el, clone)
						{
							clone.setStyle('z-index', 10000);
						}
					}
				);
			}
		);
	}
);