
Brickrouge.Widget.TitleSlugCombo = new Class
({
	initialize: function(el, options)
	{
		var reminder = el.getElement('.slug-reminder');
		var target = el.getElement('.slug');

		var expand = el.getElement('a[href$=slug-edit');
		var collapse = el.getElement('a[href$=slug-collapse]');
		var del = el.getElement('a[href$=slug-delete]');

		var input = target.getElement('input');

		var toggleState = false;

		function toggle(ev)
		{
			ev.stop();

			toggleState = !toggleState;

			target.setStyle('display', toggleState ? 'block' : 'none');
			reminder.setStyle('display', toggleState ? 'none' : 'inline');
			collapse.setStyle('display', toggleState ? 'inline' : 'none');
		};

		expand.addEvent('click', toggle);
		collapse.addEvent('click', toggle);

		function checkInput()
		{
			var value = input.get('value');
			var type = value ? 'text' : 'html';

			if (value)
			{
				value = value.shorten();
				del.getParent('span').setStyle('display', 'inline');
			}
			else
			{
				value = el.get('data-auto-label');
				del.getParent('span').setStyle('display', 'none');
			}

			reminder.getElement('a').set(type, value);
		}

		input.addEvent('change', checkInput);

		del.addEvent
		(
			'click', function(ev)
			{
				ev.stop();

				input.value = '';
				input.fireEvent('change', {});
			}
		);

		checkInput();
	}
});