//(function($) {
(($, Drupal, drupalSettings) => {
    function to_position(divid){
        $('html, body').animate({scrollTop:$(divid).position().top - 50 }, 'slow');
    }

    jQuery.expr[":"].Contains = jQuery.expr.createPseudo(function(arg) {
        return function( elem ) {
            return jQuery(elem).text().toUpperCase().indexOf(arg.toUpperCase()) >= 0;
        };
    });

    $(document).ready(function() {

        p = window.location.pathname;

        // Set faq to automatically open for selected faq from query string
        if(p=='/faq') {
            var urlParams = new URLSearchParams(window.location.search);
            if(urlParams.has('term')) {
                let t = urlParams.get('term').replace('-', ' ');
                $("legend:Contains(" + t + ")").parents('fieldset').addClass("show");
                $("legend:Contains(" + t + ")").parent().children('div').slideToggle("slow");
                $("legend:Contains(" + t + ")").parent().addClass("show");
                to_position('.show');
            }
        }

        $('fieldset.faq-parent > legend').click(function (){
            $(this).parent().children('div').slideToggle("slow");
            $(this).parent().toggleClass("show");
        });

        $('fieldset.faq-child > legend').click(function (){
            $(this).parent().find('div').slideToggle("slow");
            $(this).parent().toggleClass("show");
        });

        //Hide empty views - sometimes empty views are not being hidden and the wrapping div is still showing
        //this is happening where a contextual filter is present using a value from the URL
        //this code will check if there is no descendant HTML then to hide that wrapping div/view
        $(".view:not(:has(div))").each(function(index) {
            $(this).addClass('hide');
        });

        /*
        $("#details summary").click(
            function(event) {
                event.preventDefault();
                //alert('hi');
                //alert('Picked: '+ $(this).attr('id').slice(4));
                $('#details').attr('open', true);
            }
        );
         */


        $(".path-supplier-details .supplier-menu a:not('.no-link')").click(function (){
            var link = $(this).attr("href").substring(1);
            //console.log(link);
            to_position("." + link);
        })

    });
//})(jQuery);
})(jQuery, Drupal, drupalSettings);
