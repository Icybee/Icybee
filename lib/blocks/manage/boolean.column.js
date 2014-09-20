document.body.addEvent('click:relay(.listview [data-property][data-property-type="boolean"] input)', function(ev, target) {

	var property = target.getParent('[data-property]').get('data-property')

	new Request.API({

		url: target.form.elements[ICanBoogie.Operation.DESTINATION].value + '/' + target.value + '/' + property,

		onFailure: function() {

			target.checked = !target.checked

		}

	})[target.checked ? 'PUT' : 'DELETE']()
})