var imageIndex = 0;

$(document).ready(function() {
	$('.feature.chatCloud .dotIndicators a').click(function() {
        if ( !$(this).hasClass('active') )
        {
            var index = $(this).index();
            
            $( '.feature.chatCloud .images img' ).eq(imageIndex).fadeOut( "fast", function() {
                $('.feature.chatCloud .images img').addClass('hidden');
                $('.feature.chatCloud .images img').eq(index).removeClass('hidden').fadeIn( "fast", function() {
                    
                });
            });
            
            imageIndex = index;
            
            $('.feature.chatCloud .dotIndicators a').removeClass('active');
            $(this).addClass('active');
        }
		
        return false;
    });
});