jQuery(document).ready( function() {
	jQuery("#agency_search-sbm").click( function(event) {
		var mot = jQuery('#agency_lettre').attr('value');
		var limit = jQuery("#agency_hidden_limit_lettre").val();
		if(mot.length < limit){
			jQuery(".agency_error").show();
			jQuery("#agency_lettre").addClass("Erreur");
			return  false;
		} else {
			jQuery(".agency_error").hide();
			jQuery("#agency_lettre").removeClass("Erreur");
		}
	});
});