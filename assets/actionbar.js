window.addEvent('domready', function()
{
	var actionbar = document.id(document.body).getElement('.actionbar')
	, y = actionbar.getPosition().y

	function updateActionBar()
	{
		var bodyY = document.html.scrollTop

		actionbar[y < bodyY ? 'addClass' : 'removeClass']('fixed')
	}

	window.addEvents({
		load: updateActionBar,
		resize: updateActionBar,
		scroll: updateActionBar
	})
})