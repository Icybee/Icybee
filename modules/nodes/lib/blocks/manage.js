manager.addEvent
(
	'ready', function()
	{
		manager.element.addEvent
		(
			'click', function(ev)
			{
				var target = ev.target,
				property = target.get('data-property')

				if (property != 'is_online') return

				operation = new Request.API
				({
					url: manager.destination + '/' + target.value + '/' + (target.checked ? 'online' : 'offline'),
					onSuccess: function(response)
					{
						if (!response.rc)
						{
							//
							// if for some reason the operation failed,
							// we reset the checkbox
							//

							target.checked = !target.checked
							target.fireEvent('change', {})
						}
					}

				})
				.post();
			}
		)
	}
)