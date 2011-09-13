document.addEvent
(
	'elementsready', function(ev)
	{
		ev.target.getElements('div.view-editor').each
		(
			function(editor)
			{
				if (editor.retrieve('editor'))
				{
					return;
				}

				editor.store('editor', 'inline');

				var categories = editor.getElements('td.view-editor-categories li');
				var subcategories = editor.getElements('td.view-editor-subcategories ul');
				var subcategoriesEntries = editor.getElements('td.view-editor-subcategories li');
				var views = editor.getElements('td.view-editor-views ul');

				function setCategory(index)
				{
					var category = categories[index];

					//console.log('category: %a', category);

					if (category.hasClass('active'))
					{
						return;
					}

					categories.removeClass('active');
					category.addClass('active');

					clearSubCategories();

					setSubCategories(index);
				}

				function clearSubCategories()
				{
					subcategories.removeClass('active');

					clearSubCategoriesEntries();
				}

				function clearSubCategoriesEntries()
				{
					subcategoriesEntries.removeClass('active');

					clearViews();
				}

				function clearViews()
				{
					views.removeClass('active');
				}

				//
				//
				//

				function setSubCategories(index)
				{
					var target = subcategories[index];

					//console.log('subcategories: %a', target);

					if (target.hasClass('active'))
					{
						return;
					}

					clearSubCategories();

					target.addClass('active');

					/*
					var i = subcategoriesEntries.indexOf(target.getFirst());

					console.log('first is: %d', i);

					setSubCategory(i);
					*/
				}

				function setSubCategory(index)
				{
					var target = subcategoriesEntries[index];

					if (target.hasClass('active'))
					{
						return;
					}

					clearSubCategoriesEntries();

					target.addClass('active');

					setViews(index);
				}

				function setViews(index)
				{
					var target = views[index];

					if (target.hasClass('active'))
					{
						return;
					}

					clearViews();

					target.addClass('active');
				}

				editor.addEvent
				(
					'click', function(ev)
					{
						var target = ev.target;

						if (target.get('tag') == 'a')
						{
							target = target.getParent('li');
						}

						var i = categories.indexOf(target);

						if (i != -1)
						{
							setCategory(i);

							return;
						}

						i = subcategoriesEntries.indexOf(target);

						if (i != -1)
						{
							setSubCategory(i);

							return;
						}

						//console.log('click on: %a, ev: %a', target, ev);
					}
				);
			}
		);
	}
);