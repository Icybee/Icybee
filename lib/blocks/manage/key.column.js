!function() {

	var disableCheckCheckboxes = false

	function getCheckboxes()
	{
		return document.body.getElements('.listview tbody .cell-key input')
	}

	function checkCheckboxes()
	{
		if (disableCheckCheckboxes) return

		var checkboxes = getCheckboxes()
		, keys = []

		checkboxes.each(function(checkbox) {

			if (!checkbox.checked || checkbox.disabled) return

			keys.push(checkbox.value)

		})

		Icybee.manager.fireEvent('select', { target: manager, selected: keys })
	}

	window.addEvent('click:relay(.listview thead .cell-key input)', function(ev, el) {

		if (ev.rightClick) return

		var checked = el.checked
		, checkboxes = getCheckboxes()

		disableCheckCheckboxes = true

		if (ev.alt)
		{
			checkboxes.each(function(checkbox) {

				checkbox.click()

			})
		}
		else
		{
			checkboxes.each(function(checkbox) {

				if (checkbox.checked == checked)
				{
					return
				}

				checkbox.click()

			})
		}

		disableCheckCheckboxes = false

		checkCheckboxes()
	})

	window.addEvent('click:relay(.listview tbody .cell-key input)', function(ev, el) {

		checkCheckboxes()

	})

	window.addEvent('click:relay(.listview tbody tr)', function(ev, el) {

		var target = ev.target
		, key = el.getElement('.cell-key input')

		if (ev.rightClick || !key) return

		if (target.tagName.match(/^a|button|input|label$/gi)) return
		if (target.hasClass('trigger') || target.getParent('.trigger')) return
		if (target.getParent('a') || target.hasClass('.btn-group') || target.getParent('.btn-group')) return

		key.click()

	})

} ()