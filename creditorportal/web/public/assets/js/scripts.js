//input validation on all texbox Vapt point

$(document).ready(function () {
	debugger;
	$("#ajaxHeader").prepend("<marquee behavior='alternate' style=\"font-size: 12px; font-weight: bold; color: red;\"> Only following Special characters allowed : <table style='display: inline;vertical-align: middle;'><tr><td style='border: 2px solid #dddddd;padding: 0px 2px 0px 2px'>0 to 9</td><td style='border: 2px solid #dddddd;padding: 0px 2px 0px 2px'>a to z</td><td style='border: 2px solid #dddddd;padding: 0px 2px 0px 2px'>A to Z</td><td style='border: 2px solid #dddddd;padding: 0px 2px 0px 2px'>-</td><td style='border: 2px solid #dddddd;padding: 0px 2px 0px 2px'>@</td><td style='border: 2px solid #dddddd;padding: 0px 2px 0px 2px'>%</td><td style='border: 2px solid #dddddd;padding: 0px 2px 0px 2px'>_</td><td style='border: 2px solid #dddddd;padding: 0px 2px 0px 2px'>,</td><td style='border: 2px solid #dddddd;padding: 0px 2px 0px 2px'>.</td></tr></table></marquee>");
	//var validation_regex = new RegExp("^[0-9a-zA-Z\-@%_,.&*]");
	var validation_regex = new RegExp("^[0-9a-zA-Z\-@#%_,.&*]");
	$("input").attr("autocomplete", "off");
	// $("input").attr("maxlength", "255");
	$(document).on("keypress", "input", function (event) {
		validate_input_characters(event);
	});
	$(document).on("paste", "input", function (event) {  
		validate_input_characters_paste(event);
	});
	//setTimeout used sinnce commonLayout takes time to load
	setTimeout(function () {
		$("input").attr("autocomplete", "off");
		// $("input").attr("maxlength", "255");
	}, 2000);

	function validate_input_characters(event) {
		let inputs = Array.from(document.querySelectorAll('[id^=chassis_number]')).map(({id}) => id);
		if (inputs.length) {
			return true;
		}
		
		if (event.keyCode == '13' || event.keyCode == '32') {
			return true;
		}
		// var validation_regex = new RegExp("^[0-9a-zA-Z\-!@#$%&*?_]");
		// var validation_regex = new RegExp("^[0-9a-zA-Z\-@%_,.]");
		var key = String.fromCharCode(event.charCode ? event.which : event.charCode);
		if (!validation_regex.test(key)) {
			event.preventDefault();
			return false;
		}
	}

	function validate_input_characters_paste(event) {
		let inputs = Array.from(document.querySelectorAll('[id^=chassis_number]')).map(({id}) => id);
		if (inputs.length) {
			return true;
		}
		var pastedData = event.originalEvent.clipboardData.getData('text');
        var inputType = event.originalEvent.input;

        // console.log(event.originalEvent);

        // console.log('Coming');
         console.log(inputType);
debugger;
		var validation_regex = new RegExp("^[0-9a-zA-Z\-@%_,.]");
		if (!validation_regex.test(pastedData)) {
            console.log('#'+event.target.id);
            alert("Special characters found in pasted string");
			// window.ajaxStatus.showStatus("Special characters found in pasted string ");
			// setTimeout(function () { window.ajaxStatus.hideStatus() }, 2000);
			if(event.target.id != ''){
            $('#'+event.target.id).val('');
			}
			$('.'+event.originalEvent.target.classList[1]).val('');
			event.preventDefault();
			return false;
		}
	}
});
(function($) {
    "use strict";

    /*================================
    Preloader
    ==================================*/

    var preloader = $('#preloader');
    $(window).on('load', function() {
        preloader.fadeOut('slow', function() { $(this).remove(); });
    });

    /*================================
    sidebar collapsing
    ==================================*/
    $('.nav-btn').on('click', function() {
        $('.page-container').toggleClass('sbar_collapsed');
    });

    /*================================
    Start Footer resizer
    ==================================*/
	
    var e = function() {
        var e = (window.innerHeight > 0 ? window.innerHeight : this.screen.height) - 5;
        (e -= 67) < 1 && (e = 1), e > 67 && $(".main-content").css("min-height", e + "px")
    };
    $(window).ready(e), $(window).on("resize", e);

    /*================================
    sidebar menu
    ==================================*/
    $("#menu").metisMenu();

    /*================================
    slimscroll activation
    ==================================*/
    $('.menu-inner').slimScroll({
        height: 'auto'
    });
    $('.nofity-list').slimScroll({
        height: '435px'
    });
    $('.timeline-area').slimScroll({
        height: '500px'
    });
    $('.recent-activity').slimScroll({
        height: 'calc(100vh - 114px)'
    });
    $('.settings-list').slimScroll({
        height: 'calc(100vh - 158px)'
    });

    /*================================
    stickey Header
    ==================================*/
    $(window).on('scroll', function() {
        var scroll = $(window).scrollTop(),
            mainHeader = $('#sticky-header'),
            mainHeaderHeight = mainHeader.innerHeight();

        /* console.log(mainHeader.innerHeight());*/
        if (scroll > 1) {
            $("#sticky-header").addClass("sticky-menu");
        } else {
            $("#sticky-header").removeClass("sticky-menu");
        }
    });

    /*================================
    form bootstrap validation
    ==================================*/
    $('[data-toggle="popover"]').popover()

    /*------------- Start form Validation -------------*/
    window.addEventListener('load', function() {
       /*Fetch all the forms we want to apply custom Bootstrap validation styles to*/
        var forms = document.getElementsByClassName('needs-validation');
        /* Loop over them and prevent submission*/
        var validation = Array.prototype.filter.call(forms, function(form) {
            form.addEventListener('submit', function(event) {
                if (form.checkValidity() === false) {
                    event.preventDefault();
                    event.stopPropagation();
                }
                form.classList.add('was-validated');
            }, false);
        });
    }, false);

    /*================================
    datatable active
    ==================================*/
    if ($('#dataTable').length) {
        $('#dataTable').DataTable({
            responsive: true
        });
    }
    if ($('#dataTable2').length) {
        $('#dataTable2').DataTable({
            responsive: true
        });
    }
    if ($('#dataTable3').length) {
        $('#dataTable3').DataTable({
            responsive: true
        });
    }


    /*================================
    Slicknav mobile menu
    ==================================*/
    $('ul#nav_menu').slicknav({
        prependTo: "#mobile_menu"
    });

    /*================================
    login form
    ==================================*/
    $('.form-gp input').on('focus', function() {
        $(this).parent('.form-gp').addClass('focused');
    });
    $('.form-gp input').on('focusout', function() {
        if ($(this).val().length === 0) {
            $(this).parent('.form-gp').removeClass('focused');
        }
    });

    /*================================
    slider-area background setting
    ==================================*/
    $('.settings-btn, .offset-close').on('click', function() {
        $('.offset-area').toggleClass('show_hide');
        $('.settings-btn').toggleClass('active');
    });

    /*================================
    Owl Carousel
    ==================================*/
    function slider_area() {
        var owl = $('.testimonial-carousel').owlCarousel({
            margin: 50,
            loop: true,
            autoplay: false,
            nav: false,
            dots: true,
            responsive: {
                0: {
                    items: 1
                },
                450: {
                    items: 1
                },
                768: {
                    items: 2
                },
                1000: {
                    items: 2
                },
                1360: {
                    items: 1
                },
                1600: {
                    items: 2
                }
            }
        });
    }
    slider_area();

    /*================================
    Fullscreen Page
    ==================================*/

    if ($('#full-view').length) {

        var requestFullscreen = function(ele) {
            if (ele.requestFullscreen) {
                ele.requestFullscreen();
            } else if (ele.webkitRequestFullscreen) {
                ele.webkitRequestFullscreen();
            } else if (ele.mozRequestFullScreen) {
                ele.mozRequestFullScreen();
            } else if (ele.msRequestFullscreen) {
                ele.msRequestFullscreen();
            } else {
                console.log('Fullscreen API is not supported.');
            }
        };

        var exitFullscreen = function() {
            if (document.exitFullscreen) {
                document.exitFullscreen();
            } else if (document.webkitExitFullscreen) {
                document.webkitExitFullscreen();
            } else if (document.mozCancelFullScreen) {
                document.mozCancelFullScreen();
            } else if (document.msExitFullscreen) {
                document.msExitFullscreen();
            } else {
                console.log('Fullscreen API is not supported.');
            }
        };

        var fsDocButton = document.getElementById('full-view');
        var fsExitDocButton = document.getElementById('full-view-exit');

        fsDocButton.addEventListener('click', function(e) {
            e.preventDefault();
            requestFullscreen(document.documentElement);
            $('body').addClass('expanded');
        });

        fsExitDocButton.addEventListener('click', function(e) {
            e.preventDefault();
            exitFullscreen();
            $('body').removeClass('expanded');
        });
    }

})(jQuery);
/* submenu */
$('.dropdown-menu a.dropdown-toggle').on('click', function(e) {
  if (!$(this).next().hasClass('show')) {
    $(this).parents('.dropdown-menu').first().find('.show').removeClass("show");
  }
  var $subMenu = $(this).next(".dropdown-menu");
  $subMenu.toggleClass('show');


  $(this).parents('li.nav-item.dropdown.show').on('hidden.bs.dropdown', function(e) {
    $('.dropdown-submenu .show').removeClass("show");
  });


  return false;
});

function goBack() {
  window.history.back();
}

var myIndex = 0;
carousel();

function carousel() {
  var i;
  var x = document.getElementsByClassName("mySlides-logo");
  for (i = 0; i < x.length; i++) {
    x[i].style.display = "none";  
  }
  myIndex++;
  if (myIndex > x.length) {myIndex = 1}  
 
	if (x.length > 0) {
		if (typeof x[myIndex-1].style !== "undefined") {
			x[myIndex-1].style.display = "block";  
		}		 
	}
	
  setTimeout(carousel, 1500); /* Change image every 1 seconds*/
}

function openNav() {
  document.getElementById("mySidenav-eb").style.width = "250px";
}

function closeNav() {
  document.getElementById("mySidenav-eb").style.width = "0";
}

// $(document).ready(function () {

// 	//$("#ajaxHeader").prepend("<marquee behavior='alternate' style=\"font-size: 12px; font-weight: bold; color: red;\"> Only following Special characters allowed : <table style='display: inline;vertical-align: middle;'><tr><td style='border: 2px solid #dddddd;padding: 0px 2px 0px 2px'>0 to 9</td><td style='border: 2px solid #dddddd;padding: 0px 2px 0px 2px'>a to z</td><td style='border: 2px solid #dddddd;padding: 0px 2px 0px 2px'>A to Z</td><td style='border: 2px solid #dddddd;padding: 0px 2px 0px 2px'>-</td><td style='border: 2px solid #dddddd;padding: 0px 2px 0px 2px'>@</td><td style='border: 2px solid #dddddd;padding: 0px 2px 0px 2px'>%</td><td style='border: 2px solid #dddddd;padding: 0px 2px 0px 2px'>_</td><td style='border: 2px solid #dddddd;padding: 0px 2px 0px 2px'>,</td><td style='border: 2px solid #dddddd;padding: 0px 2px 0px 2px'>.</td></tr></table></marquee>");
// 	//var validation_regex = new RegExp("^[0-9a-zA-Z\-@%_,.&*]");
// 	var validation_regex = new RegExp("^[0-9a-zA-Z\-@#%_,.&*()]");
// 	$("input").attr("autocomplete", "off");
// 	$("input").attr("maxlength", "255");
// 	$(document).on("keypress", "input", function (event) {
// 		validate_input_characters(event);
// 	});
// 	$(document).on("paste", "input", function (event) {
// 		validate_input_characters_paste(event);
// 	});
// 	//setTimeout used sinnce commonLayout takes time to load
// 	setTimeout(function () {
// 		$("input").attr("autocomplete", "off");
// 		$("input").attr("maxlength", "255");
// 	}, 2000);

// 	// function validate_input_characters(event) {
// 	// 	let inputs = Array.from(document.querySelectorAll('[id^=chassis_number]')).map(({id}) => id);
// 	// 	if (inputs.length) {
// 	// 		return true;
// 	// 	}
		
// 	// 	if (event.keyCode == '13' || event.keyCode == '32') {
// 	// 		return true;
// 	// 	}
// 	// 	// var validation_regex = new RegExp("^[0-9a-zA-Z\-!@#$%&*?_]");
// 	// 	// var validation_regex = new RegExp("^[0-9a-zA-Z\-@%_,.]");
// 	// 	var key = String.fromCharCode(event.charCode ? event.which : event.charCode);
// 	// 	if (!validation_regex.test(key)) {
// 	// 		event.preventDefault();
// 	// 		return false;
// 	// 	}
// 	// }

// 	// function validate_input_characters_paste(event) {
// 	// 	let inputs = Array.from(document.querySelectorAll('[id^=chassis_number]')).map(({id}) => id);
// 	// 	if (inputs.length) {
// 	// 		return true;
// 	// 	}
// 	// 	var pastedData = event.originalEvent.clipboardData.getData('text');
// 	// 	// var validation_regex = new RegExp("^[0-9a-zA-Z\-@%_,.]");
// 	// 	if (!validation_regex.test(pastedData)) {
// 	// 		window.ajaxStatus.showStatus("Special characters found in pasted string ");
// 	// 		setTimeout(function () { window.ajaxStatus.hideStatus() }, 2000);
// 	// 		event.preventDefault();
// 	// 		return false;
// 	// 	}
// 	// }
// });


