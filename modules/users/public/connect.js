/*
 * This file is part of the Publishr package.
 *
 * (c) Olivier Laviale <olivier.laviale@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

var Core = {

	Element: {}
};

Core.Element.Form = new Class
({
	Implements: [ Options, Events ],

	options:
	{

	},

	initialize: function(el, options)
	{
		this.element = $(el);
		this.setOptions(options);

		if (options && (options.onRequest || options.onComplete || options.onFailure || options.onSuccess))
		{
			this.element.addEvent
			(
				'submit', function(ev)
				{
					ev.stop();

					this.submit();
				}
				.bind(this)
			);
		}
	},

	log: function(messages, type)
	{
		var target = this.element.getElement('div.alert-message.' + type) || new Element('div.alert-message.' + type, { html: '<a href="#close" class="close">×</a>'});

		if (typeOf(messages) == 'string')
		{
			messages = [ messages ];
		}
		else if (typeOf(messages) == 'object')
		{
			var original = messages;

			messages = [];

			Object.each
			(
				original, function(message, id)
				{
					var el = this.element.elements[id];

					if (el)
					{
						el.getParent('.field').addClass('error');
					}

					if (!message || message === true)
					{
						return;
					}

					messages.push(message);
				},

				this
			);
		}

		if (!messages.length)
		{
			return;
		}

		messages.each
		(
			function(message)
			{
				target.adopt(new Element('p', { html: message }));
			}
		);

		if (!target.parentNode)
		{
			target.inject(this.element, 'top');
		}
	},

	resetFeedback: function()
	{
		var alerts = this.element.getElements('div.alert-message');

		if (alerts)
		{
			alerts.destroy();
		}

		this.element.getElements('.error').removeClass('error');
	},

	submit: function()
	{
		this.fireEvent('submit', {});
		this.getOperation().send(this.element/*.toQueryString()*/);
	},

	getOperation: function()
	{
		var self = this;

		if (this.operation)
		{
			return this.operation;
		}

		return this.operation = new Request.JSON
		({
			url: this.element.action,

			onRequest: function()
			{
				self.resetFeedback();
				self.fireEvent('request', arguments);
			},

			onComplete: function()
			{
				self.fireEvent('complete', arguments);
			},

			onSuccess: function(response)
			{
				if (response.log.done)
				{
					self.log(response.log.done, 'success');
				}

				self.fireEvent('success', arguments);
			},

			onFailure: function(xhr)
			{
				var response = JSON.decode(xhr.responseText);

				if (response && response.errors)
				{
					self.log(response.errors, 'error');
				}

				//self.log(response ? (response.exception || response.log.form || response.errors) : xhr.responseText, 'missing');
				self.fireEvent('failure', arguments);
			}
		});
	}
});

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