/**
 * This file is part of the Publishr software
 *
 * @author Olivier Laviale <olivier.laviale@gmail.com>
 * @link http://www.wdpublisher.com/
 * @copyright Copyright (c) 2007-2011 Olivier Laviale
 * @license http://www.wdpublisher.com/license.html
 */

document.addEvent
(
	'elementsready', function()
	{
		var selector = $(document.body).getElement('[name=template]');

		if (!selector)
		{
			return;
		}

		var form = selector.form;

		if (selector.retrieve('loader'))
		{
			return;
		}

		selector.store('loader', true);

		var req = new Request.Element
		({
			url: '/api/pages/template-editors',

			onSuccess: function(el)
			{
				var previous_hiddens = form.getElements('input[type=hidden][name^="contents["][name$="editor]"]');

				previous_hiddens.destroy();

				var after = $('section-title-contents');
				var next = after.getNext();
				var remove = null;

				while (next)
				{
					remove = next;

					if (!remove || remove.tagName == 'H3')
					{
						break;
					}

					next = remove.getNext();
					remove.destroy();
				}

				el.getElement('h3').destroy();

				var insert = el.getChildren();

				var i = insert.length;

				while (i--)
				{
					var insertElement = insert[i];

					if (insertElement.match('input[type=hidden]'))
					{
						insertElement.inject(form);

						continue;
					}

					insert[i].inject(after, 'after');
				}

				document.fireEvent('elementsready', { target: form });
				document.fireEvent('editors');
			}
		});

		selector.addEvent
		(
			'change', function(ev)
			{
				var form = selector.form;
				var pageid = form.elements['#key'] ? form.elements['#key'].value : null;

				req.get({ pageid: pageid, template: selector.get('value') });
			}
		);
	}
);

window.addEvent
(
	'domready', function()
	{
		$$('.panel.inherit-toggle a[href="#edit"]').each
		(
			function(el)
			{
				el.addEvent
				(
					'click', function(ev)
					{
						ev.stop();

						el.getParent('.panel').toggleClass('edit');
					}
				);
			}
		);
	}
);