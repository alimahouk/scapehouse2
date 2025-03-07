$(document).ready(function() {

    $('.UIButton.submit').click(function() {
       
       if (!window.ajaxProcessing) {
           
        var email, fullname, msg;
            
        email = $('#email').val();
        fullname = $('#fullname').val();
        msg = $('#msg').val();
        
        popJelloon('affirmative', 'Please wait...');
        
        window.ajaxProcessing = true;
        
        $.ajax({
            type:"POST",
            url: "/corporate/contact",
            data: "email=" + encodeURIComponent(email) + "&fullname=" + fullname + "&msg=" + msg,
            success: function(responce) {
                
                window.ajaxProcessing = false;
                
                $('.jelloon').remove();
                
                switch(responce) {
                    case 'emailErr':
                        popJelloon('negative', 'The email address that you entered is invalid.');
                        break;
                    case 'fullnameErr':
                        popJelloon('negative', 'The name field cannot be left blank.');
                        break;
                    case 'msgErr':
                        popJelloon('negative', 'The message cannot be blank.');
                        break;
                }
                
                if (responce == 'done') {
                   
                    $('.envelope').animate({
                        marginLeft: '5000'
                    }, 1000, function() {
                        
                    });
		
                    $('#contentArea').attr('style', 'margin-right:0;width:781px;').animate({
                        marginLeft: '5000'
                    }, 1000, function() {
							$('body').css('overflow-x', 'hidden');
                    });
					
                }

            }
		
        });
	
       }
		
    });
        
});