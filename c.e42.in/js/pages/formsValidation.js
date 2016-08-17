/*
 *  Document   : formsValidation.js
 *  Author     : pixelcave
 *  Description: Custom javascript code used in Forms Validation page
 */

var FormsValidation = function() {

    return {
        init: function() {
            /*
             *  Jquery Validation, Check out more examples and documentation at https://github.com/jzaefferer/jquery-validation
             */


			/* Initialize Certificate Form Validation */
            $('#nfc-form-wallet-validation').validate({
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
                    // You can use the following if you would like to highlight with green color the input after successful validation!
                    e.closest('.form-group').removeClass('has-success has-error'); // e.closest('.form-group').removeClass('has-success has-error').addClass('has-success');
                    e.closest('.help-block').remove();
                },
                rules: {
                    created_dt: {
                        required: true
					},
                    description: {
                        minlength: 0,
                        maxlength: 500
					},
                    transaction_type: {
                        required: true,
                        minlength: 1,
                        maxlength: 10
					},
                    amount: {
                        minlength: 0,
                        maxlength: 13
					},
                    balance_amount: {
                        minlength: 0,
                        maxlength: 13
					}
                }
            });


			/* Initialize Certificate Form Validation */
            $('#nfc-form-certificate-validation').validate({
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
                    // You can use the following if you would like to highlight with green color the input after successful validation!
                    e.closest('.form-group').removeClass('has-success has-error'); // e.closest('.form-group').removeClass('has-success has-error').addClass('has-success');
                    e.closest('.help-block').remove();
                },
                rules: {
                    university_id: {
                        required: true,
                        minlength: 1,
                        maxlength: 200
                    },
                    nfc_tag_id: {
                        required: true,
                        minlength: 1,
                        maxlength: 45
                    },
                    serial_no: {
                        required: false,
                        minlength: 0,
                        maxlength: 45
					},
                    name: {
                        required: true,
                        minlength: 1,
                        maxlength: 200
					},
                    dob: {
                        required: true
					},
                    college: {
                        required: true,
                        minlength: 1,
                        maxlength: 200
					},
                    roll_no: {
                        required: true,
                        minlength: 1,
                        maxlength: 45
					},
                    course: {
                        required: true,
                        minlength: 1,
                        maxlength: 200
					},
                    grade: {
                        required: true,
                        minlength: 1,
                        maxlength: 100
					},
                    date_of_issue: {
                        required: true,
                        minlength: 1,
                        maxlength: 45
					},
                    place_of_issue: {
                        required: true,
                        minlength: 1,
                        maxlength: 100
					}
                }
            });


			/* Initialize Certificate Form Validation */
            $('#nfc-form-user-validation').validate({
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
                    // You can use the following if you would like to highlight with green color the input after successful validation!
                    e.closest('.form-group').removeClass('has-success has-error'); // e.closest('.form-group').removeClass('has-success has-error').addClass('has-success');
                    e.closest('.help-block').remove();
                },
                rules: {
                    name: {
                        required: true,
                        minlength: 1,
                        maxlength: 100
					},
                    email_id: {
                        required: true
					},
                    user_type: {
                        required: true
					}
                }
            });

            // Initialize Masked Inputs
            // a - Represents an alpha character (A-Z,a-z)
            // 9 - Represents a numeric character (0-9)
            // * - Represents an alphanumeric character (A-Z,a-z,0-9)
            $('#masked_date').mask('99/99/9999');
            $('#masked_date2').mask('99-99-9999');
            $('#masked_phone').mask('(999) 999-9999');
            $('#masked_phone_ext').mask('(999) 999-9999? x99999');
            $('#masked_taxid').mask('99-9999999');
            $('#masked_ssn').mask('999-99-9999');
            $('#masked_pkey').mask('a*-999-a999');
        }
    };
}();