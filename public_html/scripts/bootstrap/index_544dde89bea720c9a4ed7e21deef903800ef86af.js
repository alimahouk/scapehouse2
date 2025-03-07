var currentIndex = 0;
var wriggleTimer;
var wriggling;
var tbIcon;
var allWriggling;
var wriggleTimerWeak_row1_bunch1;
var wriggleTimerWeak_row1_bunch2;
var wriggleTimerWeak_row2_bunch1;
var wriggleTimerWeak_row2_bunch2;
var wriggleTimerWeak_row3_bunch1;
var wriggleTimerWeak_row3_bunch2;
var wriggleTimerWeak_row4_bunch1;
var wriggleTimerWeak_row4_bunch2;
var wriggleTimerWeak_dock_bunch1;
var wriggleTimerWeak_dock_bunch2;
var icons_row1_bunch1;
var icons_row1_bunch2;
var icons_row2_bunch1;
var icons_row2_bunch2;
var icons_row3_bunch1;
var icons_row3_bunch2;
var icons_row4_bunch1;
var icons_row4_bunch2;
var icons_dock_bunch1;
var icons_dock_bunch2;
var tappedIcon;
var boxExploded = false;

function updateTip() {
    
    $('#screenieBox img:eq(' + currentIndex + ')').fadeOut('fast', function() {
        $('#screenieBox img:eq(' + currentIndex + ')').fadeIn('fast');
    });

    var internalIndex = currentIndex + 1;
   
    if (internalIndex == 1) {
        $('#screenieBox .desc.tips').hide();
        $('#screenieBox .desc.useful').hide();
        $('#screenieBox .desc.topic').hide();
        $('#screenieBox .desc.genius').hide();
        $('#screenieBox .desc.topic').fadeIn('fast');
    } else if (internalIndex == 2) {
        $('#screenieBox .desc.tips').hide();
        $('#screenieBox .desc.useful').hide();
        $('#screenieBox .desc.topic').hide();
        $('#screenieBox .desc.genius').hide();
    } else if (internalIndex == 3) {
        $('#screenieBox .desc.tips').hide();
        $('#screenieBox .desc.useful').hide();
        $('#screenieBox .desc.topic').hide();
        $('#screenieBox .desc.genius').hide();
        $('#screenieBox .desc.genius').fadeIn('fast');
    } else if (internalIndex == 4) {
        $('#screenieBox .desc.tips').hide();
        $('#screenieBox .desc.useful').hide();
        $('#screenieBox .desc.topic').hide();
        $('#screenieBox .desc.genius').hide();
    } else if (internalIndex == 5) {
        $('#screenieBox .desc.tips').hide();
        $('#screenieBox .desc.useful').hide();
        $('#screenieBox .desc.topic').hide();
        $('#screenieBox .desc.genius').hide();
        $('#screenieBox .desc.tips').fadeIn('fast');
        $('#screenieBox .desc.useful').fadeIn('fast');
    }
	
    currentIndex++;
	
    if (currentIndex == $('#screenieBox img:last').index() + 1) {
        currentIndex = 0;
    }
}

function bounceBox() {
	$('.boxContainer').animate({
        marginTop: -50
    }, 150, function() {
		$('.boxContainer').animate({
	        marginTop: 0
	    }, 100, function() {
			$('.boxContainer').animate({
		        marginTop: -10
		    }, 70, function() {
				$('.boxContainer').animate({
			        marginTop: 0
			    }, 40, function() {

			    });
			
				$('.feature.discover').animate({
			        bottom: 245
			    }, 50, function() {

			    });

				$('.feature.social').animate({
			       	bottom: 220
			    }, 60, function() {

			    });

				$('.feature.useful').animate({
			        bottom: 195
			    }, 70, function() {

			    });

				$('.feature.genius').animate({
			        bottom: 170
			    }, 80, function() {

			    });

				$('.feature.push').animate({
			        bottom: 145
			    }, 90, function() {

			    });
		    });
		
			$('.feature.discover').animate({
		        bottom: 260
		    }, 80, function() {

		    });

			$('.feature.social').animate({
		       	bottom: 235
		    }, 90, function() {

		    });

			$('.feature.useful').animate({
		        bottom: 210
		    }, 100, function() {

		    });

			$('.feature.genius').animate({
		        bottom: 190
		    }, 110, function() {

		    });

			$('.feature.push').animate({
		        bottom: 160
		    }, 120, function() {

		    });
	    });
		
		$('.feature.discover').animate({
	        bottom: 245
	    }, 60, function() {

	    });

		$('.feature.social').animate({
	       	bottom: 220
	    }, 70, function() {

	    });

		$('.feature.useful').animate({
	        bottom: 195
	    }, 80, function() {

	    });

		$('.feature.genius').animate({
	        bottom: 175
	    }, 90, function() {

	    });

		$('.feature.push').animate({
	        bottom: 145
	    }, 100, function() {

	    });
    });

	$('.feature.discover').animate({
        bottom: 305
    }, 110, function() {

    });

	$('.feature.social').animate({
       	bottom: 280
    }, 120, function() {

    });

	$('.feature.useful').animate({
        bottom: 255
    }, 130, function() {

    });

	$('.feature.genius').animate({
        bottom: 230
    }, 140, function() {

    });

	$('.feature.push').animate({
        bottom: 205
    }, 150, function() {
		
    });
}

function explodeBox() {
	if (!boxExploded) {
		$('.boxContainer').animate({
	        marginTop: 800
	    }, 400, function() {

	    });

		$('.feature.discover').animate({
	        bottom: 1050,
			left: 0,
			width: 400
	    }, 400, function() {

	    });
	
		$('.feature.social').animate({
	        bottom: 1050,
			left: 590,
			width: 400
	    }, 400, function() {

	    });
	
		$('.feature.useful').animate({
	        bottom: 780,
			left: 70,
			width: 400
	    }, 400, function() {

	    });
	
		$('.feature.genius').animate({
	        bottom: 780,
			left: 520,
			width: 400
	    }, 400, function() {

	    });
	
		$('.feature.push').animate({
	        bottom: 510,
			left: 290,
			width: 400
	    }, 400, function() {
			
	    });
	
		boxExploded = true;
	}
}

function showNotificationCenter() {
    wriggle(false, 0);
	
    $('#notificationCenter').removeClass('hidden').animate({
        top: 0
    }, 400, function() {
        $('.touchSensor').removeClass('hidden');
    });
}

function hideNotificationCenter() {
    $('.touchSensor').addClass('hidden');
    wriggling = false;
    wriggle(true, 1500);
	
    $('#notificationCenter').animate({
        top: -674
    }, 400, function() {
        $('#notificationCenter').addClass('hidden');
    });
}

function clock() {
    
    var today = new Date();
    var hours = today.getHours();
    
    if (today.getHours() > 12) { // 12 hour formatter.
        hours = today.getHours() - 12;
    }
    
    var outStr = hours + ':' + today.getMinutes();
        
    // We don't want crap like "2:9" for 2:09.
    if (today.getMinutes() < 10) {
        outStr = hours + ':0' + today.getMinutes();
    }

    if (today.getHours() > 12) {
        $('.clock').html(outStr + ' PM');
    } else {
        $('.clock').html(outStr + ' AM');
    }

    setTimeout('clock()', 1000);
        
}

// This is the animation that first plays (juggling the Tipbox icon).
function wriggle(toggle, duration) {
	
    clearInterval(wriggleTimer);
	
    if (toggle && !wriggling) {
        setTimeout(
            "tbIcon.addClass('rightWriggle');"
            , 0);

        setTimeout(
            "tbIcon.removeClass('rightWriggle');"
            , 100);

        setTimeout(
            "tbIcon.addClass('leftWriggle');"
            , 100);

        setTimeout(
            "tbIcon.removeClass('leftWriggle');"
            , 200);

        setTimeout(
            "tbIcon.addClass('rightWriggle');"
            , 200);

        setTimeout(
            "tbIcon.removeClass('rightWriggle');"
            , 300);

        setTimeout(
            "tbIcon.addClass('leftWriggle');"
            , 300);

        setTimeout(
            "tbIcon.removeClass('leftWriggle');"
            , 400);
		
        wriggling = false;
        wriggleTimer = setInterval("wriggle(true, 1500)", duration);
    } else {
        wriggling = true;
        tbIcon.removeClass('rightWriggle').removeClass('leftWriggle');
    }
	
}

// What follows is a list of wriggle-controlling functions.
function wriggle1(toggle, duration) {
	
    clearInterval(wriggleTimerWeak_row1_bunch1);
	
    if (toggle) {
        setTimeout(
            "icons_row1_bunch1.addClass('rightWriggle_weak');"
            , 0);

        setTimeout(
            "icons_row1_bunch1.removeClass('rightWriggle_weak');"
            , 100);

        setTimeout(
            "icons_row1_bunch1.addClass('leftWriggle_weak');"
            , 100);

        setTimeout(
            "icons_row1_bunch1.removeClass('leftWriggle_weak');"
            , 200);

        setTimeout(
            "icons_row1_bunch1.addClass('rightWriggle_weak');"
            , 200);

        setTimeout(
            "icons_row1_bunch1.removeClass('rightWriggle_weak');"
            , 300);

        setTimeout(
            "icons_row1_bunch1.addClass('leftWriggle_weak');"
            , 300);

        setTimeout(
            "icons_row1_bunch1.removeClass('leftWriggle_weak');"
            , 400);
		
        wriggleTimerWeak_row1_bunch1 = setInterval("wriggle1(true, 400)", duration);
    } else {
        wriggleTimerWeak_row1_bunch1.removeClass('rightWriggle').removeClass('leftWriggle');
    }
	
}

function wriggle2(toggle, duration) {
	
    clearInterval(wriggleTimerWeak_row1_bunch2);
	
    if (toggle) {
        setTimeout(
            "icons_row1_bunch2.addClass('leftWriggle_weak');"
            , 0);

        setTimeout(
            "icons_row1_bunch2.removeClass('leftWriggle_weak');"
            , 100);

        setTimeout(
            "icons_row1_bunch2.addClass('rightWriggle_weak');"
            , 100);

        setTimeout(
            "icons_row1_bunch2.removeClass('rightWriggle_weak');"
            , 200);

        setTimeout(
            "icons_row1_bunch2.addClass('leftWriggle_weak');"
            , 200);

        setTimeout(
            "icons_row1_bunch2.removeClass('leftWriggle_weak');"
            , 300);

        setTimeout(
            "icons_row1_bunch2.addClass('rightWriggle_weak');"
            , 300);

        setTimeout(
            "icons_row1_bunch2.removeClass('rightWriggle_weak');"
            , 400);
		
        wriggleTimerWeak_row1_bunch2 = setInterval("wriggle2(true, 400)", duration);
    } else {
        wriggleTimerWeak_row1_bunch2.removeClass('rightWriggle').removeClass('leftWriggle');
    }
	
}

function wriggle3(toggle, duration) {
	
    clearInterval(wriggleTimerWeak_row2_bunch1);
	
    if (toggle) {
        setTimeout(
            "icons_row2_bunch1.addClass('rightWriggle_weak');"
            , 0);

        setTimeout(
            "icons_row2_bunch1.removeClass('rightWriggle_weak');"
            , 100);

        setTimeout(
            "icons_row2_bunch1.addClass('leftWriggle_weak');"
            , 100);

        setTimeout(
            "icons_row2_bunch1.removeClass('leftWriggle_weak');"
            , 200);

        setTimeout(
            "icons_row2_bunch1.addClass('rightWriggle_weak');"
            , 200);

        setTimeout(
            "icons_row2_bunch1.removeClass('rightWriggle_weak');"
            , 300);

        setTimeout(
            "icons_row2_bunch1.addClass('leftWriggle_weak');"
            , 300);

        setTimeout(
            "icons_row2_bunch1.removeClass('leftWriggle_weak');"
            , 400);
		
        wriggleTimerWeak_row2_bunch1 = setInterval("wriggle3(true, 400)", duration);
    } else {
        wriggleTimerWeak_row2_bunch1.removeClass('rightWriggle').removeClass('leftWriggle');
    }
	
}

function wriggle4(toggle, duration) {
	
    clearInterval(wriggleTimerWeak_row2_bunch2);
	
    if (toggle) {
        setTimeout(
            "icons_row2_bunch2.addClass('leftWriggle_weak');"
            , 0);

        setTimeout(
            "icons_row2_bunch2.removeClass('leftWriggle_weak');"
            , 100);

        setTimeout(
            "icons_row2_bunch2.addClass('rightWriggle_weak');"
            , 100);

        setTimeout(
            "icons_row2_bunch2.removeClass('rightWriggle_weak');"
            , 200);

        setTimeout(
            "icons_row2_bunch2.addClass('leftWriggle_weak');"
            , 200);

        setTimeout(
            "icons_row2_bunch2.removeClass('leftWriggle_weak');"
            , 300);

        setTimeout(
            "icons_row2_bunch2.addClass('rightWriggle_weak');"
            , 300);

        setTimeout(
            "icons_row2_bunch2.removeClass('rightWriggle_weak');"
            , 400);
		
        wriggleTimerWeak_row2_bunch2 = setInterval("wriggle4(true, 400)", duration);
    } else {
        wriggleTimerWeak_row2_bunch2.removeClass('rightWriggle').removeClass('leftWriggle');
    }
	
}

function wriggle5(toggle, duration) {
	
    clearInterval(wriggleTimerWeak_row3_bunch1);
	
    if (toggle) {
        setTimeout(
            "icons_row3_bunch1.addClass('rightWriggle_weak');"
            , 0);

        setTimeout(
            "icons_row3_bunch1.removeClass('rightWriggle_weak');"
            , 100);

        setTimeout(
            "icons_row3_bunch1.addClass('leftWriggle_weak');"
            , 100);

        setTimeout(
            "icons_row3_bunch1.removeClass('leftWriggle_weak');"
            , 200);

        setTimeout(
            "icons_row3_bunch1.addClass('rightWriggle_weak');"
            , 200);

        setTimeout(
            "icons_row3_bunch1.removeClass('rightWriggle_weak');"
            , 300);

        setTimeout(
            "icons_row3_bunch1.addClass('leftWriggle_weak');"
            , 300);

        setTimeout(
            "icons_row3_bunch1.removeClass('leftWriggle_weak');"
            , 400);
		
        wriggleTimerWeak_row3_bunch1 = setInterval("wriggle5(true, 400)", duration);
    } else {
        wriggleTimerWeak_row3_bunch1.removeClass('rightWriggle').removeClass('leftWriggle');
    }
	
}

function wriggle6(toggle, duration) {
	
    clearInterval(wriggleTimerWeak_row3_bunch2);
	
    if (toggle) {
        setTimeout(
            "icons_row3_bunch2.addClass('leftWriggle_weak');"
            , 0);

        setTimeout(
            "icons_row3_bunch2.removeClass('leftWriggle_weak');"
            , 100);

        setTimeout(
            "icons_row3_bunch2.addClass('rightWriggle_weak');"
            , 100);

        setTimeout(
            "icons_row3_bunch2.removeClass('rightWriggle_weak');"
            , 200);

        setTimeout(
            "icons_row3_bunch2.addClass('leftWriggle_weak');"
            , 200);

        setTimeout(
            "icons_row3_bunch2.removeClass('leftWriggle_weak');"
            , 300);

        setTimeout(
            "icons_row3_bunch2.addClass('rightWriggle_weak');"
            , 300);

        setTimeout(
            "icons_row3_bunch2.removeClass('rightWriggle_weak');"
            , 400);
		
        wriggleTimerWeak_row3_bunch2 = setInterval("wriggle6(true, 400)", duration);
    } else {
        wriggleTimerWeak_row3_bunch2.removeClass('rightWriggle').removeClass('leftWriggle');
    }
	
}

function wriggle7(toggle, duration) {
	
    clearInterval(wriggleTimerWeak_row4_bunch1);
	
    if (toggle) {
        setTimeout(
            "icons_row4_bunch1.addClass('rightWriggle_weak');"
            , 0);

        setTimeout(
            "icons_row4_bunch1.removeClass('rightWriggle_weak');"
            , 100);

        setTimeout(
            "icons_row4_bunch1.addClass('leftWriggle_weak');"
            , 100);

        setTimeout(
            "icons_row4_bunch1.removeClass('leftWriggle_weak');"
            , 200);

        setTimeout(
            "icons_row4_bunch1.addClass('rightWriggle_weak');"
            , 200);

        setTimeout(
            "icons_row4_bunch1.removeClass('rightWriggle_weak');"
            , 300);

        setTimeout(
            "icons_row4_bunch1.addClass('leftWriggle_weak');"
            , 300);

        setTimeout(
            "icons_row4_bunch1.removeClass('leftWriggle_weak');"
            , 400);
		
        wriggleTimerWeak_row4_bunch1 = setInterval("wriggle7(true, 400)", duration);
    } else {
        wriggleTimerWeak_row4_bunch1.removeClass('rightWriggle').removeClass('leftWriggle');
    }
	
}

function wriggle8(toggle, duration) {
	
    clearInterval(wriggleTimerWeak_row4_bunch2);
	
    if (toggle) {
        setTimeout(
            "icons_row4_bunch2.addClass('leftWriggle_weak');"
            , 0);

        setTimeout(
            "icons_row4_bunch2.removeClass('leftWriggle_weak');"
            , 100);

        setTimeout(
            "icons_row4_bunch2.addClass('rightWriggle_weak');"
            , 100);

        setTimeout(
            "icons_row4_bunch2.removeClass('rightWriggle_weak');"
            , 200);

        setTimeout(
            "icons_row4_bunch2.addClass('leftWriggle_weak');"
            , 200);

        setTimeout(
            "icons_row4_bunch2.removeClass('leftWriggle_weak');"
            , 300);

        setTimeout(
            "icons_row4_bunch2.addClass('rightWriggle_weak');"
            , 300);

        setTimeout(
            "icons_row4_bunch2.removeClass('rightWriggle_weak');"
            , 400);
		
        wriggleTimerWeak_row4_bunch2 = setInterval("wriggle8(true, 400)", duration);
    } else {
        wriggleTimerWeak_row4_bunch2.removeClass('rightWriggle').removeClass('leftWriggle');
    }
	
}

function wriggle9(toggle, duration) {
	
    clearInterval(wriggleTimerWeak_dock_bunch1);
	
    if (toggle) {
        setTimeout(
            "icons_dock_bunch1.addClass('rightWriggle_weak');"
            , 0);

        setTimeout(
            "icons_dock_bunch1.removeClass('rightWriggle_weak');"
            , 100);

        setTimeout(
            "icons_dock_bunch1.addClass('leftWriggle_weak');"
            , 100);

        setTimeout(
            "icons_dock_bunch1.removeClass('leftWriggle_weak');"
            , 200);

        setTimeout(
            "icons_dock_bunch1.addClass('rightWriggle_weak');"
            , 200);

        setTimeout(
            "icons_dock_bunch1.removeClass('rightWriggle_weak');"
            , 300);

        setTimeout(
            "icons_dock_bunch1.addClass('leftWriggle_weak');"
            , 300);

        setTimeout(
            "icons_dock_bunch1.removeClass('leftWriggle_weak');"
            , 400);
		
        wriggleTimerWeak_dock_bunch1 = setInterval("wriggle9(true, 400)", duration);
    } else {
        wriggleTimerWeak_dock_bunch1.removeClass('rightWriggle').removeClass('leftWriggle');
    }
	
}

function wriggle10(toggle, duration) {
	
    clearInterval(wriggleTimerWeak_dock_bunch2);
	
    if (toggle) {
        setTimeout(
            "icons_dock_bunch2.addClass('leftWriggle_weak');"
            , 0);

        setTimeout(
            "icons_dock_bunch2.removeClass('leftWriggle_weak');"
            , 100);

        setTimeout(
            "icons_dock_bunch2.addClass('rightWriggle_weak');"
            , 100);

        setTimeout(
            "icons_dock_bunch2.removeClass('rightWriggle_weak');"
            , 200);

        setTimeout(
            "icons_dock_bunch2.addClass('leftWriggle_weak');"
            , 200);

        setTimeout(
            "icons_dock_bunch2.removeClass('leftWriggle_weak');"
            , 300);

        setTimeout(
            "icons_dock_bunch2.addClass('rightWriggle_weak');"
            , 300);

        setTimeout(
            "icons_dock_bunch2.removeClass('rightWriggle_weak');"
            , 400);
		
        wriggleTimerWeak_dock_bunch2 = setInterval("wriggle10(true, 400)", duration);
    } else {
        wriggleTimerWeak_dock_bunch2.removeClass('rightWriggle').removeClass('leftWriggle');
    }
	
}

// WRIGGLE BEHAVIOR END

function startWrigglingAll() {
    icons_row1_bunch1 = $('.row1 .icon:nth-child(2n)');
    icons_row1_bunch2 = $('.row1 .icon:nth-child(2n+1)');
    icons_row2_bunch1 = $('.row2 .icon:nth-child(2n+1)');
    icons_row2_bunch2 = $('.row2 .icon:nth-child(2n)');
    icons_row3_bunch1 = $('.row3 .icon:nth-child(2n)');
    icons_row3_bunch2 = $('.row3 .icon:nth-child(2n+1)');
    icons_row4_bunch1 = $('.row4 .icon:nth-child(2n+1)');
    icons_row4_bunch2 = $('.row4 .icon:nth-child(2n)');
    icons_dock_bunch1 = $('.dock .icon:nth-child(2n)');
    icons_dock_bunch2 = $('.dock .icon:nth-child(2n+1)');
	
    wriggle1(true, 400);
    wriggle2(true, 400);
    wriggle3(true, 400);
    wriggle4(true, 400);
    wriggle5(true, 400);
    wriggle6(true, 400);
    wriggle7(true, 400);
    wriggle8(true, 400);
    wriggle9(true, 400);
    wriggle10(true, 400);
}

function stopWrigglingAll() {
    $('.touchSensor').addClass('hidden');
	
    clearInterval(wriggleTimerWeak_row1_bunch1);
    clearInterval(wriggleTimerWeak_row1_bunch2);
    clearInterval(wriggleTimerWeak_row2_bunch1);
    clearInterval(wriggleTimerWeak_row2_bunch2);
    clearInterval(wriggleTimerWeak_row3_bunch1);
    clearInterval(wriggleTimerWeak_row3_bunch2);
    clearInterval(wriggleTimerWeak_row4_bunch1);
    clearInterval(wriggleTimerWeak_row4_bunch2);
    clearInterval(wriggleTimerWeak_dock_bunch1);
    clearInterval(wriggleTimerWeak_dock_bunch2);
	
    allWriggling = false;
    $('.homeScreen .icon').removeClass('rightWriggle').removeClass('leftWriggle');
}

$(document).ready(function() {
	
    var tipTimer, pressTimer, bounceTimer, day, today = new Date();
    tbIcon = $('.icon.tipbox');
	
    clock();
	
    wriggling = false;
    wriggle(true, 1500);
	
    switch(today.getDay()) {
        case 0:
            day = "Sunday";
            break;
        case 1:
            day = "Monday";
            break;
        case 2:
            day = "Tuesday";
            break;
        case 3:
            day = "Wednesday";
            break;
        case 4:
            day = "Thursday";
            break;
        case 5:
            day = "Friday";
            break;
        case 6:
            day = "Saturday";
            break;
    }
	
    $('.icon.calendar .day').html(day);
    $('.icon.calendar .date').html(today.getDate());

    $('.statusBar, .notificationCenterLaunchPad').click(function() {
        showNotificationCenter();
    });
	
    $('#notificationCenter .grabber').click(function() {
        hideNotificationCenter();
    });
	
    $('#notificationCenter .reminders .UITableViewCell').click(function() {
        hideNotificationCenter();
        $('.icon.reminders').trigger('click');
        return false;
    });
	
    $('.icon').hover(function() {
        if ($(this).hasClass('showsVignette')) {
            $('.homeScreenOverlay').removeClass('hidden');
            $('.vignette').removeClass('hidden');
        }
    }, function() {
        if ($(this).hasClass('showsVignette')) {
            $('.homeScreenOverlay, .vignette').addClass('hidden');
        }
    });
	
    $('.icon.showsNotification').hover(function() {
        if ($(this).hasClass('photos')) {
            $('.notificationBanner .photos').removeClass('hidden');
        }
		
        if ($(this).hasClass('videos')) {
            $('.notificationBanner .videos').removeClass('hidden');
        }
		
        if ($(this).hasClass('store')) {
            $('.notificationBanner .store').removeClass('hidden');
        }
		
        if ($(this).hasClass('twitter')) {
            $('.notificationBanner .twitter').removeClass('hidden');
        }

		if ($(this).hasClass('hire')) {
            $('.notificationBanner .hire').removeClass('hidden');
        }
		
        $('.notificationBanner').stop().animate({
            top: 0
        }, 200, function() {
		    
            });
    }, function() {
        $('.notificationBanner *').addClass('hidden');
		
        $('.notificationBanner').stop().animate({
            top: -55
        }, 200, function() {
		    
            });
		
    });

    // Wriggling handler. Needs a long press.
    $('.icon').mouseup(function() {
        tappedIcon.removeClass('underFinger');
        clearTimeout(pressTimer); // Clear timeout.

        if (allWriggling) {
            $('.touchSensor').removeClass('hidden');
        }
		
        return false;
    }).mousedown(function() {
        tappedIcon = $(this);
        pressTimer = window.setTimeout(function() {
            $(tappedIcon).addClass('underFinger');
            startWrigglingAll();
            allWriggling = true;
        }, 500);
		
        return false; 
    });

    $('.icon').click(function() {
        if (allWriggling) {
            return false;
        }
    });
	
    $('.icon.tipbox').click(function() {
        if (!$(this).hasClass('open') && !allWriggling) {
            wriggle(false, 0);
			
            $('.touchSensor, #tipbox').removeClass('hidden');
            $('.homeScreenOverlay, .vignette, .reflection').addClass('hidden');
            $('.icon.tipbox').removeAttr('style').addClass('open').removeClass('showsVignette');

            $('.statusBarPlaceholder, .statusBar, .notificationCenterLaunchPad').animate({
                marginTop: -300
            }, 400, function() {

                });
			
            $('.pageControl, .notificationBadge').animate({
                opacity: 0
            }, 400, function() {

                });
			
            $('.UIScrollView, .dock .icon').animate({
                opacity: 0.4
            }, 400, function() {

                });

            $('.touchSensor, .homeScreen, .wallpaper').animate({
                marginTop: -300
            }, 400, function() {
                clearInterval(bounceTimer);
				if (!boxExploded) {
					bounceTimer = setInterval(function() { // Start the box bouncing timer.
	                    bounceBox();
	                }, 2500);
				}
            });

            $('#facade, .header, .divider.upper, .features, .storeButton, .social_big').removeClass('hidden');
            $('#footer.custom').css('display', 'block');
        }
		
        return false;
    });
	
    $('.icon.reminders').click(function() {
        if (!$(this).hasClass('open') && !allWriggling) {
            wriggle(false, 0);
			
            $('.touchSensor').removeClass('hidden');
            $('.icon.reminders').addClass('open');

            $('.statusBarPlaceholder, .statusBar, .notificationCenterLaunchPad').animate({
                marginTop: -300
            }, 400, function() {

            });
			
            $('.pageControl, .notificationBadge').animate({
                opacity: 0
            }, 400, function() {

            });
			
            $('.UIScrollView, .dock .icon').animate({
                opacity: 0.4
            }, 400, function() {

            });

            $('.touchSensor, .homeScreen, .wallpaper').animate({
                marginTop: -300
            }, 400, function() {

            });

            $('#facade, #remindersSection').removeClass('hidden');
            $('#footer.custom').css('display', 'block');
        }
		
        return false;
    });

	$('.icon.photos').click(function() {
        if (!$(this).hasClass('open') && !allWriggling) {
            wriggle(false, 0);
			
            $('.touchSensor').removeClass('hidden');
            $('.icon.photos').addClass('open');
			
            $('.statusBarPlaceholder, .statusBar, .notificationCenterLaunchPad').animate({
                marginTop: -300
            }, 400, function() {

            });
			
            $('.pageControl, .notificationBadge').animate({
                opacity: 0
            }, 400, function() {

            });
			
            $('.UIScrollView, .dock .icon').animate({
                opacity: 0.4
            }, 400, function() {

            });

            $('.touchSensor, .homeScreen, .wallpaper').animate({
                marginTop: -300
            }, 400, function() {
				clearInterval(tipTimer);
                tipTimer = setInterval(function() { // Start the tip timer.
                    updateTip();
                }, 5000);
            });

            $('#facade, .tipPackage, .storeButton, .social_big').removeClass('hidden');
            $('#footer.custom').css('display', 'block');
        }
		
        return false;
    });

	$('.icon.newsstand').click(function() {
        if (!$(this).hasClass('open') && !allWriggling) {
            wriggle(false, 0);
			
            $('.touchSensor').removeClass('hidden');
            $('.icon.newsstand').addClass('open');
			
            $('.statusBarPlaceholder, .statusBar, .notificationCenterLaunchPad').animate({
                marginTop: -300
            }, 400, function() {

            });
			
            $('.pageControl, .notificationBadge').animate({
                opacity: 0
            }, 400, function() {

            });
			
            $('.UIScrollView, .dock .icon').animate({
                opacity: 0.4
            }, 400, function() {

            });

            $('.touchSensor, .wallpaper.upper').animate({
                marginTop: -300
            }, 400, function() {
				
            });
			
			$('.touchSensor.lower, .wallpaper.lower').animate({
                marginTop: 209
            }, 400, function() {
				
            });
			
			$('.homeScreen').animate({
				marginTop: -300,
                height: $('.homeScreen').height() + 509
            }, 400, function() {
				
            });
			
			$('#facade').removeClass('hidden').attr('style', 'display:none');
			$('.folder_newsstand, .folder_newsstand_scaffolding').slideDown(400);
        }
		
        return false;
    });
	
    $('.touchSensor').click(function() {
		if (!$('#facade').hasClass('hidden')) {
            clearInterval(tipTimer);
            clearInterval(tipTimer); // Clear the tip timer.
			
			clearInterval(bounceTimer);
            clearInterval(bounceTimer); // Clear the box bouncing timer.
			
            $('html, body').animate({
                scrollTop: $('#mainWrapper').offset().top
            }, 300	, function() {
                $('.reflection').removeClass('hidden');
				
                $('.statusBarPlaceholder, .statusBar, .notificationCenterLaunchPad').animate({
                    marginTop: 0
                }, 400, function() {

                });

                $('.UIScrollView, .dock .icon').animate({
                    opacity: 1
                }, 400, function() {

                });

                $('.pageControl, .notificationBadge').animate({
                    opacity: 1
                }, 400, function() {

                });
				
				// Special case for the Newsstand animation.
				if ($('.icon.newsstand').hasClass('open')) {
					$('.homeScreen').animate({
						marginTop: 0,
		                height: $('.homeScreen').height() - 509
		            }, 400, function() {

		            });

					$('.folder_newsstand, .folder_newsstand_scaffolding').slideUp(400);
					
					$('.touchSensor, .wallpaper').animate({
	                    marginTop: 0
	                }, 400, function() {
	                    wriggle(true, 1500);
	                    wriggling = false;
	                });
				} else {
					$('.touchSensor, .homeScreen, .wallpaper').animate({
	                    marginTop: 0
	                }, 400, function() {
	                    wriggle(true, 1500);
	                    wriggling = false;
	                });
				}

                $('.touchSensor, .wallpaper').animate({
                    marginTop: 0
                }, 400, function() {
                    wriggle(true, 1500);
                    wriggling = false;
                });
				
                $('#facade').animate({
                    opacity: 0
                }, 400, function() {
                    $('#facade').addClass('hidden').removeAttr('style');
                    $('.tipPackage, .header, .divider.upper, .features, .storeButton, .social_big, #remindersSection').addClass('hidden');
                });
				
				$('.touchSensor').addClass('hidden');
	            $('.icon').removeClass('open');
	            $('.icon.tipbox').addClass('showsVignette');
                $('#footer.custom').removeAttr('style');
            });
        } else if (!$('#notificationCenter').hasClass('hidden')) {
            hideNotificationCenter();
        } else {
            stopWrigglingAll();
        }
		
        return false;
    });
	
    $('.boxContainer').click(function() {
		clearInterval(bounceTimer);
        clearInterval(bounceTimer); // Clear the box bouncing timer.
		explodeBox();
        return false;
    });
	
    $('#addEmail').click(function() {
        
        if (!window.ajaxProcessing) {
           
            var email = $("#email").val();
			
            popJelloon('affirmative', 'Please wait...');
            window.ajaxProcessing = true;
        
            $.ajax({
                type:"POST",
                url: "/index/index",
                data: "email=" + encodeURIComponent(email),
                success: function(responce) {
                
                    window.ajaxProcessing = false;
                
                    $(".jelloon").remove();
                
                    switch(responce) {
                        case 'emailErr':
                            popJelloon('negative', 'The email address that you have entered is incorrect.');
                            break;
                    }
                
                    if (responce == 'done') {
                        popJelloon('affirmative', 'Thanks! We will inform you as soon as Tipbox is out.');
                        $('#email').val('');
                    }

                }
		
            });
		
        }
		
    });
});