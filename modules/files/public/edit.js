window.addEvent
(
	'load', function()
	{
		var form = $(document.body).getElement('form.edit');

		form.getElements('input[type=text]').each
		(
			function (el)
			{
				if (!el.value)
				{
					el.addClass('was-empty');
				}
			}
		);

		var form = $(document.body).getElement('form.edit');
		var constructor = $(form.elements['#destination']).get('value');

		$$('.widget-file').each
		(
			function(el)
			{
				var widget = el.get('widget');

				widget.options.uploadUrl = '/api/' + constructor + '/upload';

				widget.addEvent
				(
					'change', function(response)
					{
						Object.each
						(
							response.properties, function(value, key)
							{
								var input = $(form.elements[key]);

								if (!input || !input.hasClass('was-empty'))
								{
									return;
								}

								input.value = value;
							}
						);
					}
				);
			}
		);
	}
);