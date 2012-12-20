window.addEvent('domready', function() {

	var form = document.body.getElement('form[name="change-working-site"]')

	if (!form) return

	form.addEvent('submit', function() {

		form.action = form.getElement('select').get('value')

	})
})
