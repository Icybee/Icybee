document.body.addEvent('click:relay(#manager [data-property][data-property-type="boolean"])', function(ev, target) {

	new Request.API({

		url: target.form.elements[ICanBoogie.Operation.DESTINATION].value + '/' + target.value + '/' + target.get('data-property'),

		onFailure: function() {

			target.checked = !target.checked

		},

		onSuccess: function(response)
		{
			target.fireEvent('change', ev)
		}

	})[target.checked ? 'PUT' : 'DELETE']()
})