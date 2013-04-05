manager.addEvent('ready', function() {

	manager.element.addEvent('click', function(ev) {

		var target = ev.target
		, property = target.get('data-property')

		if (property != 'is_home_excluded') return

		new Request.API
		({
			url: manager.destination + '/' + target.value + '/' + (target.checked ? 'home_exclude' : 'home_include'),

			onSuccess: function(response)
			{
				if (!response.rc)
				{
					/*
					 * if for some reason the operation failed, we reset the checkbox
					 */

					target.checked = !target.checked
					target.fireEvent('change', {})
				}
			}
		})
		.post()
	})
})