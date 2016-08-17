<?php
/**
 * template_scripts.php
 *
 * Author: pixelcave
 *
 * All vital JS scripts are included here
 *
 */

if ( !isset($ROW[0]) ) $ROW[0]=array();

?>

<!-- Include Jquery library from Google's CDN but if something goes wrong get Jquery from local file (Remove 'http:' if you have SSL) -->
<script src="http://ajax.googleapis.com/ajax/libs/jquery/1.11.1/jquery.min.js"></script>
<script>!window.jQuery && document.write(decodeURI('%3Cscript src="<?php echo BASEURL; ?>js/vendor/jquery-1.11.1.min.js"%3E%3C/script%3E'));</script>

<!-- Bootstrap.js, Jquery plugins and Custom JS code -->
<script src="<?php echo BASEURL; ?>js/vendor/bootstrap.min.js"></script>
<script src="<?php echo BASEURL; ?>js/plugins.js"></script>
<script src="<?php echo BASEURL; ?>js/app.js"></script>

<script>

	$(document).ready(function(){

		$(".payment-option").click(function(){
			$(".payment-option").removeAttr("checked")
			$(this).attr("checked",true);
		});

		// Search based on Tag 
		$('#certificate-search').click(function(e){
			document.location.href="<?php echo BASEURL; ?>search/"+$('#usn-search').val()+"/"+$('#roll-no-search').val(); 
			return false;
		});

		$('#send-certificate-email').click(function(){
			var str = $("#nfc-certificate-email-form").serialize();
			var usn='<?php echo get_arg($ROW[0],'usn'); ?>';
			var certificate_id='<?php echo get_arg($ROW[0],'certificate_id'); ?>';
			$('#nfc-modal-certificate-email #send-certificate-email span').html('Sending... <span class="fa fa-asterisk fa-spin"><span>');
			$.getJSON('<?php echo BASEURL; ?>index.php?mode=j&mod=certificate&go=show&report_mode=EMAIL&usn='+usn+'&'+str+'&certificate_id='+certificate_id) 
			.done( function( response ) {
					if ( response.status == 'OK' ) {
						$('#nfc-modal-certificate-email .has-success .control-label').html('Successfully sent to '+$('#email_to').val());
						$('#nfc-modal-certificate-email fieldset').hide();
						$('#nfc-modal-certificate-email #send-certificate-email').hide();
					} else {
						$('#nfc-modal-certificate-email #send-certificate-email span:first-child').text('Send').remove('.fa');
						$('#message-dialog').modal('toggle');
						$("#modal-message").text(response.message);
					}
			});
			return false;
		});

		$(".delete-row").click(function(){
			$('#delete-dialog').attr('delete_id',$(this).closest('tr').attr('delete_id'));
			$('#delete-dialog').attr('delete_url',$(this).closest('tr').attr('delete_url'));
		});

		$(".delete-certtag").click(function(){
			$('#certtag-delete-dialog').attr('certificate_id',$(this).closest('tr').attr('certificate_id'));
			$('#certtag-delete-dialog').attr('tag_id',$(this).closest('tr').attr('tag_id'));
		});


		$('#delete-confirm').click(function(){
			$.post($('#delete-dialog').attr('delete_url'),{
				'do' : 'remove',
				id : $('#delete-dialog').attr('delete_id')
			},
			delete_callback,
			"json");
		});

		$('#certtag-delete-confirm').click(function(){
			$.post('<?php echo BASEURL; ?>certtag/delete',{
				'do' : 'certtag_remove',
				certificate_id	: $('#certtag-delete-dialog').attr('certificate_id'),
				tag_id			: $('#certtag-delete-dialog').attr('tag_id')
			},
			certtag_delete_callback,
			"json");
		});

		/*******************************************************/
		/**************      CERTTAG          ******************/
		/*******************************************************/
		// Add new tag
		$('.add-certtag').click(function(){
			var tag_name=$('#tag-name').val();
			var tag_value=$('#tag-value').val();
			var certificate_id=$('#tag-value').val();

			$.post('<?php echo BASEURL; ?>certtag/add',{
				'do'			: 'certtag_add',
				certificate_id	: '<?php echo get_arg($_GET,'certificate_id'); ?>',
				tag_name		: tag_name,
				tag_value		: tag_value
				},
			add_device_tag_callback,
			'json');
		});

		/*******************************************************/
		/**************      STANDARD TAG     ******************/
		/*******************************************************/
		// Add new tag
		$('.add-tag').click(function(){
			var tag_name=$('#add-tag-name').val();
			var tag_value=$('#add-tag-value').val();
			var is_tag_sync=$('#add-is-tag-sync').is(':checked') == true ? 1 : 0;
			var tag_type=$('#add-tag-type').val();
			var is_active=$('#add-is-active').is(':checked') == true ? 1 : 0;
			var params = {
				'do'			: 'tag_add',
				tag_name		: tag_name,
				tag_value		: tag_value,
				is_tag_sync		: is_tag_sync,
				'type'			: tag_type,
				is_active		: is_active
			}

			$.ajax({
				url: "<?php echo BASEURL; ?>tag/add",
				type: 'post',
				dataType: 'json',
				success: function (response) {
					if ( response.status === "ERROR" ) {
						$('#message-dialog').modal('toggle');
						$("#modal-message").text(response.message);
					} else {

						var is_tag_sync_checked="";
						var is_active_checked="";
						$('#add-tag-name').val('');
						$('#add-tag-value').val('');
						$('#add-is-tag-sync').removeAttr('checked');
						$('#add-tag-type').val('PUBLIC');
						$('#add-is-active').removeAttr('checked');
						if ( response.is_tag_sync == 1 ) is_tag_sync_checked="checked";
						if ( response.is_active == 1 ) is_active_checked="checked";
						$('.add-tag-row').prepend('<tr id="row-'+response.tag_id+'" certificate_id="'+response.certificate_id+'" tag_id="'+response.tag_id+'">'+
														'<td>'+response.tag_name+'</td>'+
														'<td>'+response.tag_value+'</td>'+
														'<td><input type="checkbox" class="is_tag_sync" name="is_tag_sync" '+is_tag_sync_checked+'/></td>'+
														'<td>'+
															'<select class="tag_type form-control">'+
																'<option value="PUBLIC">PUBLIC</option>'+
																'<option value="PRIVATE">PRIVATE</option>'+
																'<option value="INTERNAL">INTERNAL</option>'+
															'</select>'+
														'</td>'+
														'<td><input type="checkbox" class="is_active" name="is_active" '+is_active_checked+'/></td>'+
														//'<td><a href="#tag-delete-dialog" id="certtag-delete-'+response.tag_id+'" class="fa fa-trash" data-toggle="modal"></a></td>'+
													'</tr>'
												);

						$("#tag-delete-"+response.tag_id).click(function(){
							$('#tag-delete-dialog').attr('tag_id',$(this).closest('tr').attr('tag_id'));
						});

						//Active and is sync for standard tag ajax function
						// Tabs
						$(".is_active").click(function(){
							var tag_id=$(this).closest("tr").attr("id").split('-')[1];
							var is_active=$(this).is(':checked') == true ? 1 : 0;
							var params = {
								"do"			: "tag_update",
								"is_active"		: is_active, 
								"tag_id"		: tag_id,
							}

							$.ajax({
								url: "<?php echo BASEURL; ?>tag/update",
								type: 'post',
								dataType: 'json',
								success: function (response) {
									if ( response.status === "ERROR" ) {
										alert( response.message );
									} else {
										$('#message-dialog').modal('toggle');
										$("#modal-message").text(response.message);
									}
								},
								data: params
							});
						});

						// Tag Type
						$(".tag_type").change(function(){
							var tag_id=$(this).closest("tr").attr("id").split('-')[1];
							var type=$(this).val();
							var params = {
								"do"		: "tag_update",
								"tag_id"	: tag_id, 
								"type"		: type,
							}

							$.ajax({
								url: "<?php echo BASEURL; ?>tag/update",
								type: 'post',
								dataType: 'json',
								success: function (response) {
									if ( response.status === "ERROR" ) {
										alert( response.message );
									} else {
										$('#message-dialog').modal('toggle');
										$("#modal-message").text(response.message);
									}
								},
								data: params
							});
						});


						// Tabs
						$(".tag_id").click(function(){
							var tag_name=$(this).closest("tr").attr("id").split('-')[1];
							var is_tag_sync=$(this).is(':checked') == true ? 1 : 0;
							var params = {
								"do"			: "tag_update",
								"is_tag_sync"	: is_tag_sync, 
								"tag_id"		: tag_id,
							}

							$.ajax({
								url: "<?php echo BASEURL; ?>tag/update",
								type: 'post',
								dataType: 'json',
								success: function (response) {
									if ( response.status === "ERROR" ) {
										alert( response.message );
									} else {
										$('#message-dialog').modal('toggle');
										$("#modal-message").text(response.message);
									}
								},
								data: params
							});
						});

						$('#message-dialog').modal('toggle');
						$("#modal-message").text(response.message);
					}
				},
				data: params
			});
			return false;
		});

		//Active and is sync for standard tag ajax function
		// Tabs
		$(".is_active").click(function(){
			var tag_id=$(this).closest("tr").attr("id").split('-')[1];
			var is_active=$(this).is(':checked') == true ? 1 : 0;
			var params = {
				"do"			: "tag_update",
				"is_active"		: is_active, 
				"tag_id"		: tag_id,
			}

			$.ajax({
				url: "<?php echo BASEURL; ?>tag/update",
				type: 'post',
				dataType: 'json',
				success: function (response) {
					if ( response.status === "ERROR" ) {
						alert( response.message );
					} else {
						$('#message-dialog').modal('toggle');
						$("#modal-message").text(response.message);
					}
				},
				data: params
			});
		});

		// Tag Type
		$(".tag_type").change(function(){
			var tag_id=$(this).closest("tr").attr("id").split('-')[1];
			var type=$(this).val();
			var params = {
				"do"		: "tag_update",
				"tag_id"	: tag_id, 
				"type"		: type,
			}

			$.ajax({
				url: "<?php echo BASEURL; ?>tag/update",
				type: 'post',
				dataType: 'json',
				success: function (response) {
					if ( response.status === "ERROR" ) {
						alert( response.message );
					} else {
						$('#message-dialog').modal('toggle');
						$("#modal-message").text(response.message);
					}
				},
				data: params
			});
		});


		// Tabs
		$(".tag_id").click(function(){
			var tag_name=$(this).closest("tr").attr("id").split('-')[1];
			var is_tag_sync=$(this).is(':checked') == true ? 1 : 0;
			var params = {
				"do"			: "tag_update",
				"is_tag_sync"	: is_tag_sync, 
				"tag_id"		: tag_id,
			}

			$.ajax({
				url: "<?php echo BASEURL; ?>tag/update",
				type: 'post',
				dataType: 'json',
				success: function (response) {
					if ( response.status === "ERROR" ) {
						alert( response.message );
					} else {
						$('#message-dialog').modal('toggle');
						$("#modal-message").text(response.message);
					}
				},
				data: params
			});
		});

		$(".delete-tag").click(function(){
			$('#tag-delete-dialog').attr('tag_id',$(this).closest('tr').attr('tag_id'));
		});

		// Delete tag
		$('#tag-delete-confirm').click(function(){
			var params = {
				'do' : 'tag_remove',
				tag_id			: $('#tag-delete-dialog').attr('tag_id')
			}

			$.ajax({
				url: "<?php echo BASEURL; ?>tag/delete",
				type: 'post',
				dataType: 'json',
				success: function (response) {
					if ( response.status === "ERROR" ) {
						$('#message-dialog').modal('toggle');
						$("#modal-message").val(response.message);
					} else {
						$('#tag-delete-dialog').modal('toggle');
						if ( response.status != 'OK' ) {
							$('#message-dialog').modal('toggle');
							$("#modal-message").text(response.message);
						} else {
							$('#row-'+response.tag_id).remove();
						}
					}
				},
				data: params
			});
			return false;
		});


		// Delete user image
		$("#delete-img").click(function() {
			if ( $('input:file').val() == 'no_image.jpg' ) return;
			$.post('<?php echo BASEURL; ?>user/delete_photo',{
				'do'		: 'remove_image_json',
				user_id		: $('#user_id').val()
			},
			function( response ) {
				if ( response.status != 'OK' ) {
					$('#message-dialog').modal('toggle');
					$("#modal-message").text(response.message);
				} else {
					$('#user-photo').attr('src','<?php echo IMG_PATH.'org/'.get_arg($_SESSION,'org_id'); ?>/user/no_image.jpg');
					$('input:file').val('no_image.jpg');
				}
			},
			'json');
			return false;
		});

	});

	function delete_callback(response) {
		$('#delete-dialog').modal('toggle');
		if ( response.status != 'OK' ) {
			$('#message-dialog').modal('toggle');
			$("#modal-message").text(response.message);
		} else {
			$('#row-'+response.id).remove();
		}
	}

	function certtag_delete_callback(response) {
		$('#certtag-delete-dialog').modal('toggle');
		if ( response.status != 'OK' ) {
			$('#message-dialog').modal('toggle');
			$("#modal-message").text(response.message);
		} else {
			$('#row-'+response.tag_id).remove();
		}
	}

	function add_device_tag_callback(response) {
		if ( response.status != 'OK' ) {
			$('#message-dialog').modal('toggle');
			$("#modal-message").text(response.message);
		} else {
			$('#tag-name').val('');
			$('#tag-value').val('');
			$('.add-certtag-row').prepend('<tr id="row-'+response.tag_id+'" certificate_id="'+response.certificate_id+'" tag_id="'+response.tag_id+'">'+
												'<td>'+response.tag_name+'</td>'+
												'<td>'+response.tag_value+'</td>'+
												'<td><a href="#certtag-delete-dialog" id="certtag-delete-'+response.tag_id+'" class="fa fa-trash" data-toggle="modal"></a></td>'+
											'</tr>'
									);

			$("#certtag-delete-"+response.tag_id).click(function(){
				$('#certtag-delete-dialog').attr('certificate_id',$(this).closest('tr').attr('certificate_id'));
				$('#certtag-delete-dialog').attr('tag_id',$(this).closest('tr').attr('tag_id'));
			});

		}
	}

</script>