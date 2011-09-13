window.addEvent
(
	'domready', function()
	{
		var form = $(document.body).getElement('form.edit');

		var el = form.getElement('div.wd-adjustnodeslist');
		var adjust = el.retrieve('adjust');
		var scope = $(form.elements['scope']);

		scope.addEvent
		(
			'change', function()
			{
				adjust.setScope(this.get('value'));
				adjust.getResults();
			}
		);
	}
);