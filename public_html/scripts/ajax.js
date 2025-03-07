//*********************************************************
// ajax.js - Contains functions that utilize Ajax.
//*********************************************************
// WORD OF NOTE: event.PreventDefault() fucks up IE and FF.
//=========================================================


function editpm(content) {

    if (!checkTextLen(content, 450)) { // Disallow posting of text greater than whats allowed.		
        return false;
    }
    
    $.ajax({
        type:"POST",
        url: "/ajax/editpm",
        data: "content=" + encodeURIComponent(content),
        success: function() {

			

        }
		
    });
	
}