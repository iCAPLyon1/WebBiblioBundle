$(document).ready(function() {
    if(typeof searchTagsUrl != "undefined" && searchTagsUrl != null){
        $("#icap_bundle_webbibliobundle_weblinktype_tags").tokenInput(searchTagsUrl,{
            theme:'facebook',
            allowFreeTagging: true,
            preventDuplicates: true,
            tokenValue: "name"
        });
    }
});