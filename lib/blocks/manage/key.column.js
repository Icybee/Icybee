!function() {

	var manager = null
	, body = document.body

	function getMaster()
	{
		return manager.element.getElement('.listview thead .cell-key input')
	}

	function change()
	{
		getMaster().checked = !manager.getUnselectedKeys().length

		manager.fireEvent('select', [ manager.getSelectedKeys() ] )
	}

	/**
	 * Toggle all the entries according to the state of the master.
	 */
	body.addEvent('click:relay(.listview thead .cell-key input)', function(ev, el) {

		el.checked ? manager.selectAll() : manager.selectNone()

	})

	/**
	 * The selection has changed.
	 */
	body.addEvent('click:relay(.listview tbody .cell-key input)', change)

	/**
	 * Toogle a checkbox when its parent TR is clicked.
	 */
	body.addEvent('click:relay(.listview tbody tr)', function(ev, el) {

		var target = ev.target
		, key = el.getElement('.cell-key input')

		if (ev.rightClick || !key) return
		if (target.tagName.match(/^a|button|input|label$/gi)) return
		if (target.hasClass('trigger') || target.getParent('.trigger')) return
		if (target.getParent('a') || target.hasClass('.btn-group') || target.getParent('.btn-group')) return

		key.click()

	})

	/**
	 * Setup the ManagerBlock widget.
	 *
	 * The following methods are added:
	 *
	 * - `getSelectedBoxes()`: Return the selected boxes.
	 * - `getSelectedKeys()`: Return the selected keys.
	 * - `selectNone()`: Reset the selection.
	 * - `selectAll()`: Select all selectable entries.
	 */
	window.addEvent('icybee.manageblock.ready', function(widget) {

		manager = widget

		Object.append(manager, {

			getSelectedBoxes: function() {

				return this.element.getElements('tbody .cell-key input:not(:disabled):checked')

			},

			getUnSelectedBoxes: function() {

				return this.element.getElements('tbody .cell-key input:not(:disabled):not(:checked)')

			},

			getSelectedKeys: function() {

				var keys = []

				this.getSelectedBoxes().each(function(box) {

					keys.push(box.value)

				})

				return keys

			},

			getUnselectedKeys: function() {

				var keys = []

				this.getUnSelectedBoxes().each(function(box) {

					keys.push(box.value)

				})

				return keys
			},

			selectNone: function() {

				this.getSelectedBoxes().each(function(box) {

					box.checked = false

				})

				getMaster().checked = false

				change()

			},

			selectAll: function() {

				this.getUnSelectedBoxes().each(function(box) {

					box.checked = true

				})

				getMaster().checked = true

				change()

			}

		})

	})

} ()