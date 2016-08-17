/*
 *  Document   : login.js
 *  Author     : pixelcave
 *  Description: Custom javascript code used in Login page
 */

var Login = function() {

	// Function for switching form views (login, reminder and register forms)
	var switchView = function(viewHide, viewShow, viewHash){
		viewHide.slideUp(250);
		viewShow.slideDown(250, function(){
			$('input').placeholder();
		});

		if ( viewHash ) {
			window.location = '#' + viewHash;
		} else {
			window.location = '#';
		}
	};

	return {
		init: function() {
			/* Switch Login, Reminder and Register form views */
			var formLogin       = $('#form-login'),
				formReminder    = $('#form-forgot-pass'),
				formRegister    = $('#form-register');

			$('#link-register-login').click(function(){
				switchView(formLogin, formRegister, 'register');
			});

			$('#link-register').click(function(){
				switchView(formRegister, formLogin, '');
			});

			$('#link-reminder-login').click(function(){
				switchView(formLogin, formReminder, 'reminder');
			});

			$('#link-reminder').click(function(){
				switchView(formReminder, formLogin, '');
			});

			// If the link includes the hashtag 'register', show the register form instead of login
			if (window.location.hash === '#register') {
				formLogin.hide();
				formRegister.show();
			}

			// If the link includes the hashtag 'reminder', show the reminder form instead of login
			if (window.location.hash === '#reminder') {
				formLogin.hide();
				formReminder.show();
			}

			/*
			 *  Jquery Validation, Check out more examples and documentation at https://github.com/jzaefferer/jquery-validation
			 */

			/* Login form - Initialize Validation */
			$('#form-login').validate({
				errorClass: 'help-block animation-slideDown', // You can change the animation class for a different entrance animation - check animations page
				errorElement: 'div',
				errorPlacement: function(error, e) {
					e.parents('.form-group > div').append(error);
				},
				highlight: function(e) {
					$(e).closest('.form-group').removeClass('has-success has-error').addClass('has-error');
					$(e).closest('.help-block').remove();
				},
				success: function(e) {
					e.closest('.form-group').removeClass('has-success has-error');
					e.closest('.help-block').remove();
				},
				rules: {
					'login-email': {
						required: true,
						email: true
					},
					'login-password': {
						required: true,
						minlength: 5
					}
				},
				messages: {
					'login-email': 'Please enter your account\'s email',
					'login-password': {
						required: 'Please provide your password',
						minlength: 'Your password must be at least 5 characters long'
					}
				},
				submitHandler: function(form) {
					// Register
					$('#form-login-button').click(function(e){
						var email_id=$('#login-email').val();
						var password=$('#login-password').val();
						$('#form-login #form-login-button span').html('Logging in... <span class="fa fa-asterisk fa-spin"><span>');
						$.post('index.php?mode=j&mod=user',{
							'do'		: 'user_login',
							email_id	: email_id,
							pass		: password
						},
						function( response ) {
							if ( response.status == 'OK' ) {
								$('#form-login .has-success .control-label').html(response.message);
								$('.hide-on-success').hide();
								document.location.href="/";
							} else {
								$('#form-login #form-login-button span').html(' Login to Dashboard').remove('.fa');
								alert(response.message);
							}
						},
						'json');
						return false
					});
				}
			});

			/* Forgot Password form - Initialize Validation */
			$('#form-forgot-pass').validate({
				errorClass: 'help-block animation-slideDown', // You can change the animation class for a different entrance animation - check animations page
				errorElement: 'div',
				errorPlacement: function(error, e) {
					e.parents('.form-group > div').append(error);
				},
				highlight: function(e) {
					$(e).closest('.form-group').removeClass('has-success has-error').addClass('has-error');
					$(e).closest('.help-block').remove();
				},
				success: function(e) {
					e.closest('.form-group').removeClass('has-success has-error');
					e.closest('.help-block').remove();
				},
				rules: {
					'email_id': {
						required: true,
						email: true
					}
				},
				messages: {
					'email_id': 'Please enter your account\'s email'
				},
				submitHandler: function(form) {
					// Forgot
					$('#form-forgot-pass-button').click(function(e){
						$('#form-forgot-pass .has-success .control-label').hide();
						$('#form-forgot-pass .has-error .control-label').hide();
						var email_id=$('#forgot-pass-email').val();
						$('#form-forgot-pass #form-forgot-pass-button').html('Please Wait... <span class="fa fa-asterisk fa-spin"><span>');
						$.post('index.php?mode=j&mod=user',{
							'do'		: 'forgot_pass',
							email_id	: email_id
						},
						function( response ) {
							if ( response.status == 'OK' ) {
								$('#form-forgot-pass .has-success .control-label').html(response.message).show();
								$('#form-forgot-pass .forgot-pass-button').hide();
								$('#form-forgot-pass .verify-field').show();

								// Forgot
								$('#form-verify-button').click(function(e){
									$('#form-forgot-pass .has-success .control-label').hide();
									$('#form-forgot-pass .has-error .control-label').hide();
									var email_id=$('#forgot-pass-email').val();
									var otp=$('#forgot-pass-otp').val();
									var password=$('#forgot-pass-new-pass').val();
									$('#form-forgot-pass #form-verify-button').html('Please wait... <span class="fa fa-asterisk fa-spin"><span>');
									$.post('index.php?mode=j&mod=user',{
										'do'		: 'verify_otp',
										email_id	: email_id,
										otp			: otp,
										password	: password
									},
									function( response ) {
										if ( response.status == 'OK' ) {
											$('#form-forgot-pass .has-success .control-label').html(response.message).show();
											$('#form-forgot-pass .hide-on-success').hide();
											//document.location.href="/";
										} else {
											$('#form-forgot-pass #form-verify-button').html('<i class="fa fa-angle-right"></i> Reset Password').remove('.fa');
											$('#form-forgot-pass .has-error .control-label').html(response.message).show();
										}
									},
									'json');
									return false;
								});
								//$('#form-forgot-pass .hide-on-success').hide();
								//document.location.href="/";
							} else {
								$('#form-forgot-pass #form-forgot-pass-button').html('<i class="fa fa-angle-right"></i> Submit').remove('.fa');
								$('#form-forgot-pass .has-error .control-label').html(response.message).show();
							}
						},
						'json');
						return false;
					});
				}
			});

			/* Register form - Initialize Validation */
			$('#form-register').validate({
				errorClass: 'help-block animation-slideDown', // You can change the animation class for a different entrance animation - check animations page
				errorElement: 'div',
				errorPlacement: function(error, e) {
					e.parents('.form-group > div').append(error);
				},
				highlight: function(e) {
					$(e).closest('.form-group').removeClass('has-success has-error').addClass('has-error');
					$(e).closest('.help-block').remove();
				},
				success: function(e) {
					if (e.closest('.form-group').find('.help-block').length === 2) {
						e.closest('.help-block').remove();
					} else {
						e.closest('.form-group').removeClass('has-success has-error');
						e.closest('.help-block').remove();

					}
				},
				rules: {
					'name': {
						required: true,
						minlength: 2
					},
					'email_id': {
						required: true,
						email: true
					},
					'password': {
						required: true,
						minlength: 5
					}
				},
				messages: {
					'name': {
						required: 'Please enter your name',
						minlength: 'Please enter your name'
					},
					'email_id': 'Please enter a valid email address',
					'password': {
						required: 'Please provide a password',
						minlength: 'Your password must be at least 5 characters long'
					}
				},
				submitHandler: function(form) {
					// Register
					$('#form-register-button').click(function(e){
						var name=$('#register-firstname').val();
						var email_id=$('#register-email').val();
						var password=$('#register-password').val();
						$('#form-register #form-register-button span').html('Registering... <span class="fa fa-asterisk fa-spin"><span>');
						$.post('index.php?mode=j&mod=user',{
							'do'		: 'user_register',
							name		: name,
							email_id	: email_id,
							password	: password
						},
						function( response ) {
							if ( response.status == 'OK' ) {
								$('#form-register .has-success .control-label').html(response.message);
								$('.hide-on-success').hide();
							} else {
								$('#form-register #form-register-button span').html('Register Account').remove('.fa');
								alert(response.message);
							}
						},
						'json');
						return false
					});
				}
			});
		}
	};
}();