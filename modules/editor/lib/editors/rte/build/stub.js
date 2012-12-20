;window.addEvent('brickrouge.update', function() {

	$$('textarea.moo').each(function(el) {

		if (el.retrieve('mooeditable')) return

		var options = el.get('dataset')

		if (options.externalCss)
		{
			options.externalCSS = JSON.decode(options.externalCss)
		}

		if (options.baseUrl)
		{
			options.baseURL = options.baseUrl
		}

		el.mooEditable(options)

		el.store('mooeditable', true)
	})
})