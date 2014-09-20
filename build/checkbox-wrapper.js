/**
 * Changes the state of the wrapper when the input is clicked.
 */
window.addEvent('click:relay(label.checkbox-wrapper)', function(ev, el) {

	var target = ev.target

	if (target.getParent() != el) return

	el[target.checked ? 'addClass' : 'removeClass']('checked')

})

/**
 * Sets the initial state of the wrapper according to the state of the input.
 */
window.addEvent('brickrouge.update', function(fragment) {

	fragment.getElements('label.checkbox-wrapper').each(function(el) {

		var input = el.getElement('input')

		if (input.checked)
		{
			el.addClass('checked')
		}

		if (input.disabled)
		{
			el.addClass('disabled')
		}

		if (input.readonly)
		{
			el.addClass('readonly')
		}
	})
})
