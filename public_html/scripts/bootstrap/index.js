$(document).ready(function() {
	$('.UISegmentControl a').click(function() {
        if ( !$(this).hasClass('active') )
        {
            $('.products .section').addClass('hidden');

            if ( $(this).hasClass('mobile') )
            {
                $('.products .section.mobile').removeClass('hidden');
            }
            else if ( $(this).hasClass('desktop') )
            {
                $('.products .section.desktop').removeClass('hidden');
            }

            $('.UISegmentControl a').removeClass('active');
            $(this).addClass('active');
        }
		
        return false;
    });
});