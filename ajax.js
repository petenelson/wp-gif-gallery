jQuery(document).ready(function() {

	// fill in variables from the wp_localize_script call
	jQuery('.forum-pics-search-form').attr('action', pn_wpaustin_wpajax.admin_ajax_url);
	jQuery('.forum-pics-search-form .ajax-action').val(pn_wpaustin_wpajax.ajax_action);
	jQuery('.forum-pics-search-form .parent-post-id').val(pn_wpaustin_wpajax.parent_post_id);

	jQuery('.forum-pics-search-form .search-for').focus();


	jQuery('.forum-pics-upload-form').attr('action', pn_wpaustin_wpajax.admin_ajax_url);
	jQuery('.forum-pics-upload-form .ajax-action').val(pn_wpaustin_wpajax.ajax_action_upload);
	jQuery('.forum-pics-upload-form .parent-post-id').val(pn_wpaustin_wpajax.parent_post_id);

	jQuery('.forum-pics-search-form .use-api').change(function() {
		if ( jQuery(this).is(':checked') ) {
			jQuery( '.forum-pics-search-form').attr('action', pn_wpaustin_wpajax.ajax_action_api )
		} else {
			jQuery( '.forum-pics-search-form').attr('action', pn_wpaustin_wpajax.admin_ajax_url )
		}
	});


	jQuery('.forum-pics-show-upload').click(function(e) {
		e.preventDefault();
		jQuery(this).hide();
		jQuery('.forum-pics-upload-form').show();
		jQuery('.forum-pics-upload-form .upload-file-name').focus();
	});

	// Ajax-ify the form
	jQuery('.forum-pics-search-form').ajaxForm({

		beforeSubmit: function() { jQuery('.forum-pics-search-form .ajax-loading').show(); },
		success: function(response) {
			if (response) {

				if (response.number_of_matches == 1)
					document.location = response.permalink;
				else {
					jQuery('.forum-pics-search-form .matches').html('Matches: ' + response.number_of_matches);
					jQuery('.entry-content .gallery').html(response.gallery_html);
				}
			}

			jQuery('.forum-pics-search-form .ajax-loading').hide();
		}

	});


	// Ajax-ify the form
	jQuery('.forum-pics-upload-form').ajaxForm({

		beforeSubmit: function() { jQuery('.forum-pics-search-form .ajax-loading').show(); },
		success: function(response) {
			if (response && !response.error) {
				document.location = response.permalink;
			} else {
				alert('Unable to upload file');
			}

			jQuery('.forum-pics-search-form .ajax-loading').hide();
		}

	});


});
