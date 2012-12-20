document.body.addEvent('click:relay([data-property="status"] .dropdown-menu a)', function(ev, el) {

	var input = el.getParent('.btn-group')
	, classNames = JSON.decode(input.get('data-classes'))
	, toggle = input.getElement('.dropdown-toggle')
	, siteId = input.get('data-site-id')
	, status = el.get('data-key')
	, label = el.get('text')

	toggle.addClass('disabled')
	input.removeClass('open')
	ev.stop()

	new Request.API({

		url: 'sites/' + siteId + '/status',

		onComplete: function()
		{
			toggle.removeClass('disabled')
		},

		onSuccess: function()
		{
			input.getElement('.text').set('text', label)

			el.getParent('ul').getElements('li').removeClass('active')
			el.getParent('li').addClass('active')

			Object.each(classNames, function(className) { toggle.removeClass(className) })
			toggle.addClass(classNames[status])
		}

	}).put({ status: status })

})