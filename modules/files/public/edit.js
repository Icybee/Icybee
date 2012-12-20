window.addEvent('load', function() {

	var form = $(document.body).getElement('.form-primary.edit')
	, constructor = form.getElement('[name="#destination"]').value
	, emptyControls = []

	form.getElements('input[type=text]').each(function (el) {

		if (!el.value)
		{
			emptyControls.push(el)
		}
	})

	form.getElements('.widget-file').each(function(el) {

		var widget = el.get('widget')

		widget.options.uploadUrl = '/api/' + constructor + '/upload'

		widget.addEvent('change', function(response) {

			Object.each(response.properties, function(value, key) {

				var input = document.id(form.elements[key])

				if (!input || emptyControls.indexOf(input) == -1)
				{
					return
				}

				input.value = value
			})
		})
	})
})