//(function($) {
(($, Drupal, drupalSettings) => {
    function to_position(divid){
        $('html, body').animate({scrollTop:$(divid).position().top - 50 }, 'slow');
    }

    $(document).ready(function() {

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
