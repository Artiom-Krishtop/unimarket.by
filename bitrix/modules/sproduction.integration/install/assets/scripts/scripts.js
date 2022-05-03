
function parse_url(url){
    var parser = document.createElement('a');
    parser.href = url;
    return parser;
}

$(function() {

    /**
     * Resize parent iframe when content is changed
     */
    parent.iframeResize();
    $('.iframe-wrapper').bind("DOMSubtreeModified",function(){
        parent.iframeResize();
    }); 

});
