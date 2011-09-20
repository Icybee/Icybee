
BrickRouge.Widget.SearchCombo = new Class ({

	initialize: function(el, options)
	{
		this.element = $(el);
		this.element.getChildren().addEvents ({

			focus: function()
			{
				el.addClass('focus');
			},

			blur: function()
			{
				el.removeClass('focus');
			}

		});
	}

});
