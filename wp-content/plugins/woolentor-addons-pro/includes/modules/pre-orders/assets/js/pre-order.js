;(function($){
    "use strict";
    
    // Countdown
    $('.woolentor-pre-order-countdown').each(function() {
        var $this       = $(this), 
            endDate     = $(this).data('countdown'),
            // endDate     = '2022/1/4',
            customlavel = $(this).data('customlavel');
        $this.countdown( endDate, function(event) {
            $this.html(event.strftime('<div class="woolentor-countdown-single"><div class="woolentor-countdown-single-inner"><h3>%D</h3><p>'+customlavel.daytxt+'</p></div></div><div class="woolentor-countdown-single"><div class="woolentor-countdown-single-inner"><h3>%H</h3><p>'+customlavel.hourtxt+'</p></div></div><div class="woolentor-countdown-single"><div class="woolentor-countdown-single-inner"><h3>%M</h3><p>'+customlavel.minutestxt+'</p></div></div><div class="woolentor-countdown-single"><div class="woolentor-countdown-single-inner"><h3>%S</h3><p>'+customlavel.secondstxt+'</p></div></div>'));
        });
    });
    
})(jQuery);