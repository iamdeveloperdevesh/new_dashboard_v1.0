$.ajaxSetup({
        beforeSend: function (xhr, settings) {
            if (settings.type == 'POST' || settings.type == 'PUT' || settings.type == 'DELETE')
            {
                function getCookie(name) {
                    var cookieValue = null;
                    if (document.cookie && document.cookie != '')
                    {
                        var cookies = document.cookie.split(';');
                        for (var i = 0; i < cookies.length; i++)
                        {
                            var cookie = jQuery.trim(cookies[i]);
                            // Does this cookie string begin with the name we want?
                            if (cookie.substring(0, name.length + 1) == (name + '='))
                            {
                                cookieValue = decodeURIComponent(cookie.substring(name.length + 1));
                                break;
                            }
                        }
                    }
                    return cookieValue;
                }

                if (!(/^http:.*/.test(settings.url) || /^https:.*/.test(settings.url)))
                {
                    xhr.setRequestHeader("X-Csrf-Token", getCookie('csrf_cookie_name'));
                }
            }
        }
    });
	function ajaxindicatorstart(text) {
    text = (typeof text !== "undefined") ? text : "Processing your request..";
    var res = "";
    if (($("body").find("#resultLoading").attr("id")) != "resultLoading") {
        res += "<div id='resultLoading' style='display: none'>";
        res += "<div id='resultcontent'>";
        res += "<div id='ajaxloader' class='txt'>";
        res += '<svg class="lds-curve-bars" width="100%" height="100%" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" viewBox="0 0 100 100" preserveAspectRatio="xMidYMid"><g transform="translate(50,50)"><circle cx="0" cy="0" r="8.333333333333334" fill="none" stroke="#28292f" stroke-width="4" stroke-dasharray="26.179938779914945 26.179938779914945" transform="rotate(2.72337)"><animateTransform attributeName="transform" type="rotate" values="0 0 0;360 0 0" times="0;1" dur="1s" calcMode="spline" keySplines="0.2 0 0.8 1" begin="0" repeatCount="indefinite"></animateTransform></circle><circle cx="0" cy="0" r="16.666666666666668" fill="none" stroke="#0a0a0a" stroke-width="4" stroke-dasharray="52.35987755982989 52.35987755982989" transform="rotate(64.7343)"><animateTransform attributeName="transform" type="rotate" values="0 0 0;360 0 0" times="0;1" dur="1s" calcMode="spline" keySplines="0.2 0 0.8 1" begin="-0.2" repeatCount="indefinite"></animateTransform></circle><circle cx="0" cy="0" r="25" fill="none" stroke="#ffffff" stroke-width="4" stroke-dasharray="78.53981633974483 78.53981633974483" transform="rotate(150.07)"><animateTransform attributeName="transform" type="rotate" values="0 0 0;360 0 0" times="0;1" dur="1s" calcMode="spline" keySplines="0.2 0 0.8 1" begin="-0.4" repeatCount="indefinite"></animateTransform></circle><circle cx="0" cy="0" r="33.333333333333336" fill="none" stroke="#f9ae5c" stroke-width="4" stroke-dasharray="104.71975511965978 104.71975511965978" transform="rotate(239.433)"><animateTransform attributeName="transform" type="rotate" values="0 0 0;360 0 0" times="0;1" dur="1s" calcMode="spline" keySplines="0.2 0 0.8 1" begin="-0.6" repeatCount="indefinite"></animateTransform></circle><circle cx="0" cy="0" r="41.666666666666664" fill="none" stroke="#e4812f" stroke-width="4" stroke-dasharray="130.89969389957471 130.89969389957471" transform="rotate(320.34)"><animateTransform attributeName="transform" type="rotate" values="0 0 0;360 0 0" times="0;1" dur="1s" calcMode="spline" keySplines="0.2 0 0.8 1" begin="-0.8" repeatCount="indefinite"></animateTransform></circle></g></svg>';
        res += "<br/>";
        res += "<span id='loadingMsg'></span>";
        res += "</div>";
        res += "</div>";
        res += "</div>";
        $("body").append(res);
    }
    $("#loadingMsg").html(text);
    $("#resultLoading").find("#resultcontent > #ajaxloader").css({
        "position": "absolute",
        "width": "500px",
        "height": "75px"
    });
    $("#resultLoading").css({
        "width": "100%",
        "height": "100%",
        "position": "fixed",
        "z-index": "10000000",
        "top": "0",
        "left": "0",
        "right": "0",
        "bottom": "0",
        "margin": "auto"
    });
    $("#resultLoading").find("#resultcontent").css({
        "background": "#ffffff",
        "opacity": "0.7",
        "width": "100%",
        "height": "100%",
        "text-align": "center",
        "vertical-align": "middle",
        "position": "fixed",
        "top": "0",
        "left": "0",
        "right": "0",
        "bottom": "0",
        "margin": "auto",
        "font-size": "16px",
        "z-index": "10",
        "color": "#000000"
    });
    $("#resultLoading").find(".txt").css({
        "position": "absolute",
        "top": "-25%",
        "bottom": "0",
        "left": "0",
        "right": "0",
        "margin": "auto"
    });
    $("#resultLoading").fadeIn(300);
    $("body").css("cursor", "wait");
}

function ajaxindicatorstop() {
    $("#resultLoading").fadeOut(300);
    $("body").css("cursor", "default");
}
