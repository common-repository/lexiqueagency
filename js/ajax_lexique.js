jQuery(document).ready( function() {
	jQuery("a.search_ajax").click( function(event) {
		event.preventDefault();
		// Remove content of attribute style
		jQuery("a.search_ajax").attr('style','');
		var current =  jQuery(this);
		var lettre = jQuery(this).attr('rel');
		var lettre_style = jQuery("#agency_back_current_letter").attr('value');
		jQuery.ajax({
			type : 'POST',
			dataType : 'html',
			url : myAjax.ajaxurl,
			data : {action: 'search_ajax', lettre : lettre},
			success: function(data) {
				jQuery('.allresult').hide();
				jQuery('.lexique_agency_result').html(data);
				jQuery(current).attr('style','font-size: 25px; background:'+lettre_style);
			},
			error: function(xhr, ajaxOptions, thrownError){
				alert(xhr.status);
                alert(xhr.responseText);
                alert(xhr.statusText);
			}

		});
	});

	jQuery("#agency_search-sbm").click( function(event) {
		event.preventDefault();
		var mot = jQuery('#agency_lettre').attr('value');
		var limit = jQuery("#agency_hidden_limit_lettre").val();
		if(mot.length >= limit) {
			// Remove Style of Errors
			jQuery(".agency_error").hide();
			jQuery("#agency_lettre").removeClass("Erreur");
			// Ajax
			jQuery.ajax({
				type : 'POST',
				dataType : 'html',
				url : myAjax.ajaxurl,
				data : {action: 'searchMot_ajax', mot : mot},
				success: function(data) {
					jQuery('.allresult').hide();
					jQuery('.lexique_agency_result').html(data);
				}
			});
			// Remove content of attribute style
			jQuery("a.search_ajax").attr('style','');
		} else {
			jQuery(".agency_error").show();
			jQuery("#agency_lettre").addClass("Erreur");
		}
	});
});