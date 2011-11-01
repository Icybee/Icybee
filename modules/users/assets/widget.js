
BrickRouge.Widget.Login = new Class({

	Extends: BrickRouge.Form,

	options: {

		useXHR: true
	},

	onSuccess: function(response)
	{
		if (response.location)
		{
			window.location = response.location;
		}
		else
		{
			window.location.reload();
		}
	}
});

BrickRouge.Widget.NonceRequest = new Class({

	Extends: BrickRouge.Form,

	options: {

		useXHR: true
	}
});

BrickRouge.Widget.LoginCombo = new Class({

	initialize: function(el, options)
	{
		this.element = el = $(el);

		var forms = el.getElements('form');

		var login = forms[0];
		var nonce = forms[1];

		var loginSlide = new Fx.Slide(login, { duration: 'short', wrapper: login.getParent(), resetHeight: true });
		var nonceSlide = new Fx.Slide(nonce, { duration: 'short', wrapper: nonce.getParent(), resetHeight: true });

		function nonceIn()
		{
			nonce.get('widget').clearAlert();

			loginSlide.slideOut().chain
			(
				function()
				{
					nonceSlide.slideIn();
				}
			);

			return nonceSlide;
		};

		function nonceOut()
		{
			nonceSlide.slideOut().chain
			(
				function()
				{
					loginSlide.slideIn();
				}
			);

			return loginSlide;
		};

		login.getElement('a').addEvent
		(
			'click', function(ev)
			{
				ev.stop();

				nonceIn();
			}
		);

		nonce.getElement('a').addEvent
		(
			'click', function(ev)
			{
				ev.stop();

				nonceOut();
			}
		);

		var shake = ( function (target, amplitude, duration)
		{
			target = $(target);
			target.setStyle('position', 'relative');

			var fx = new Fx.Tween(target, { property: 'left', duration: duration/5 });

			return function()
			{
				fx.start(-amplitude).chain
				(
					function () { this.start(amplitude); },
					function () { this.start(-amplitude); },
					function () { this.start(amplitude); },
					function () { this.start(0); }
				);
			};

		}) (el.getParent('shakable') || el, 50, 200);

		login.get('widget').addEvent('failure', shake);

		nonce.get('widget').addEvent
		(
			'success', function()
			{
				console.log('nonnc', arguments);

				nonceOut.delay(4000);
			}
		);
	}

});

/*
window.addEvent
(
	'domready', function()
	{
		var container = $('login');

		var shake = ( function (target, amplitude, duration)
		{
			target = $(target);
			target.setStyle('position', 'relative');

			var fx = new Fx.Tween(target, { property: 'left', duration: duration/5 });

			return function()
			{
				fx.start(-amplitude).chain
				(
					function () { this.start(amplitude); },
					function () { this.start(-amplitude); },
					function () { this.start(amplitude); },
					function () { this.start(0); }
				);
			};

		//}) ('contents', 50, 200);
		}) (container.getParent('shakable') || container, 50, 200);

		var loginElement = container.getElement('form[name="users/login"]');
		var loginSlideFx = new Fx.Slide(loginElement, { duration: 'short', wrapper: loginElement.getParent(), resetHeight: true });

		//if (document.body.hasClass('admin'))
		{
			new Core.Element.Form
			(
				loginElement,
				{
					onSuccess: function(response)
					{
						window.location.reload();
					},

					onFailure: function()
					{
						shake();
					}
				}
			);
		}

		var passwordElement = container.getElement('form[name="users/nonce-request"]');
		var passwordSlideFx = new Fx.Slide(passwordElement, { duration: 'short', wrapper: passwordElement.getParent(), resetHeight: true });

		passwordSlideFx.hide();

		//
		// password form handling
		//

		var password = new Core.Element.Form
		(
			passwordElement,
			{
				onSubmit: function()
				{
					this.element.action = Request.API.encode('nonce-login-request');
				},

				onFailure: function(response)
				{
					shake();
				},

				onSuccess: function(response)
				{
					passwordOut.delay(4000);
				}
			}
		);

		function passwordIn()
		{
			password.resetFeedback();

			loginSlideFx.slideOut().chain
			(
				function()
				{
					passwordSlideFx.slideIn();
				}
			);

			return passwordSlideFx;
		};

		function passwordOut()
		{
			passwordSlideFx.slideOut().chain
			(
				function()
				{
					loginSlideFx.slideIn();
				}
			);

			return loginSlideFx;
		};

		loginElement.getElement('a').addEvent
		(
			'click', function(ev)
			{
				ev.stop();

				passwordIn();
			}
		);

		var passwordCancel = passwordElement.getElement('a');

		if (passwordCancel)
		{
			passwordCancel.addEvent
			(
				'click', function(ev)
				{
					ev.stop();

					passwordOut();
				}
			);
		}
	}
);
*/