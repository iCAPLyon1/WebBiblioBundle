$(document).ready(function() {
    $("#icap_bundle_webbibliobundle_weblinktype_tags").tokenInput(searchTagsUrl+"?format=json",{
    	theme:'facebook',
    	allowFreeTagging: true,
    	preventDuplicates: true,
    	tokenValue: "name"
    });
});