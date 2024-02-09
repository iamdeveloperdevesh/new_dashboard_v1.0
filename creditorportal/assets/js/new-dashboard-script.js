/*!
* JS for new dashboard - v1.0.0
*
* Made by Devesh Ukalkar (https://github.com/iamcoderdevesh)
*/

//Endorsement Chart
const endorsementChart = () => {
    const labels = ["Jan", "Feb", "Mar", "Apr", "May", "Jun", "July", "Aug", "Sept", "Oct", "Nov", "Dec"];

    var options = {
        series: [{
            name: 'Addition',
            data: [21, 16, 30, 25, 42, 48, 42, 41, 30, 35, 55, 70],
        }, {
            name: 'Updation',
            data: [1, 22, 15, 20, 25, 18, 26, 28, 30, 28, 35, 20],
        },
        {
            name: 'Deletion',
            data: [2, 6, 3, 5, 4, 7, 8, 5, 10, 15, 12, 10],
        }],
        chart: {
            type: 'area',
            height: 260,
            width: '100%',
            stacked: false,
            toolbar: {
                show: false
            }
        },
        colors: ['#3C9FFF', '#74BBFF', '#A8D4FF'],
        dataLabels: {
            enabled: false
        },
        stroke: {
            show: true, // Hide lines
            width: 2
        },
        fill: {
            type: 'gradient',
            gradient: {
                opacityFrom: 1.5, // Decrease opacity
                opacityTo: 1, // Decrease opacity
            }
        },
        legend: {
            position: 'top',
            horizontalAlign: 'left'
        },
        xaxis: {
            categories: labels
        }
    };

    var chart = new ApexCharts(document.getElementById("endorsementChart"), options);
    chart.render();
}

//Client Performance Chart
const clientPerfomanceChart = (series, labels, data) => {

    // var colorPalette = ['#5B9BD5', '#8D63FF', '#9B9B9B', '#FFC000', '#ED7D31']
    var optionDonut = {
        chart: {
            type: 'donut',
        },
        dataLabels: {
            enabled: false,
        },
        plotOptions: {
            pie: {
                customScale: 0.8,
                donut: {
                    size: '75%',
                },
                offsetY: 20,
            },
            stroke: {
                colors: undefined
            }
        },
        stroke: {
            lineCap: 'round'
        },
        // colors: colorPalette,
        series: series,
        labels: labels,
        legend: {
            position: 'right',
            offsetX: -20,
            offsetY: 50,
            fontSize: '10px'
        },
        tooltip: {
            custom: function ({ seriesIndex }) {
                const item = data[seriesIndex];
                return '<div class="arrow_box">' +
                    '<span>Net: ' + parseInt(item.premium) + '</span><br>' +
                    '<span>Policies: ' + item.certificate + '</span><br>' +
                    '<span>Rank: ' + (seriesIndex + 1) + '</span>' +
                    '</div>'
            }
        }
    }

    var donut = new ApexCharts(
        document.querySelector("#clientPerformanceChart"),
        optionDonut
    )
    donut.render();

}

//All Claims Chart
const allClaimsChart = () => {
    var optionsCircle4 = {
        chart: {
            type: 'radialBar',
            height: 250,
        },
        plotOptions: {
            radialBar: {
                size: undefined,
                inverseOrder: true,
                hollow: {
                    margin: 5,
                    size: '48%',
                    background: 'transparent',

                },
                track: {
                    show: false,
                },
            },
        },
        stroke: {
            lineCap: 'round'
        },
        series: [71, 63, 77],
        labels: ['June', 'May', 'April'],
        legend: {
            show: true,
            position: 'right',
            containerMargin: {
                right: 0
            }
        },
    }

    var chartCircle4 = new ApexCharts(document.querySelector('#allClaimsChart'), optionsCircle4);
    chartCircle4.render();
}

//Discrepancy Perfomance Chart
const discrepancyCircleChart = (pecentage = 50, value = 100, id) => {
    var options = {
        series: [pecentage],
        chart: {
            height: 140,
            width: 120,
            type: 'radialBar',
        },
        plotOptions: {
            radialBar: {
                hollow: {
                    size: `50%`,
                },
                trackedOn: 'value', // center the text vertically
                dataLabels: {
                    name: {
                        show: false,
                    },
                    value: {
                        formatter: function () {
                            return value; // add the % symbol to the text
                        },
                        offsetY: 5, // adjust the horizontal position of the text
                        color: '#333', // set the text color
                    },
                },
            },
        },
        labels: [''],
    };

    var chart = new ApexCharts(document.getElementById(id), options);
    chart.render();
}

//Dashboard Slider using Swiper JS
const slider = () => {
    var swiper = new Swiper(".slide-content", {
        slidesPerView: 3,
        spaceBetween: 20,
        loop: false,
        centerSlide: 'true',
        fade: 'true',
        grabCursor: 'true',
        pagination: {
            el: ".swiper-pagination",
            clickable: true,
            dynamicBullets: true,
        },
        navigation: {
            nextEl: ".swiper-next-button",
            prevEl: ".swiper-prev-button",
        },

        breakpoints: {
            0: {
                slidesPerView: 1,
            },
            646: {
                slidesPerView: 2,
            },
            1268: {
                slidesPerView: 3,
            },
        },
    });
}

//Getting Client Names for Client Dropdown Filter
const getClientName = () => {
    $.ajax({
        url: "/newdashboard/getClientData",
        type: "POST",
        dataType: 'json',
        success: function (response) {
            //<a class="dropdown-item" href="#">Booking Pending</a>
            var html = '<option value=0>Select</option>';
            $.each(response.data, function (i) {
                //    console.log(response.data[i].creaditor_name);
                html += '<option value="' + response.data[i].creditor_id + '">' + response.data[i].creaditor_name + '</option>';
            });
            $('#client_dropdown').html(html);
            // alert();

        }
    });
}

//Getting Conver Type for CoverType Dropdown Filter
const getCoverType = () => {
    $.ajax({
        url: "/newdashboard/getCoverType",
        type: "POST",
        dataType: 'json',
        success: function (response) {
            //<a class="dropdown-item" href="#">Booking Pending</a>
            var html = '<option selected disabled>Select</option>';
            $.each(response.data, function (i) {
                //    console.log(response.data[i].creaditor_name);
                html += '<option value="' + response.data[i].policy_sub_type_id + '">' + response.data[i].code + '</option>';
            });
            $('#cover_type').html(html);
            // $("#cover_type").select2({
            //     placeholder: "Select"
            // });
        }
    });
}

//Getting Plan Name for Dropdown Filter
const getPlanName = () => {
    $('#plan_name').val('');
    var partner_id = $('#client_dropdown').val();
    $.ajax({
        url: "/newdashboard/getPlanName",
        type: "POST",
        dataType: 'json',
        data: { partner_id },
        success: function (response) {
            //<a class="dropdown-item" href="#">Booking Pending</a>
            var html = '<option value="0" selected >Select</option>';
            $.each(response.data, function (i) {
                //    console.log(response.data[i].creaditor_name);
                html += '<option value="' + response.data[i].plan_id + '">' + response.data[i].plan_name + '</option>';
            });
            $('#plan_name').html(html);
            // $('#plan_name').select2();
            // $('#plan_name').val(0).trigger('change.select2');
        }
    });
}

//Getting Insurer Name for Dropdown Filter
const getInsurerName = () => {
    $.ajax({
        url: "/newdashboard/getInsurerName",
        type: "POST",
        dataType: 'json',
        success: function (response) {
            //<a class="dropdown-item" href="#">Booking Pending</a>
            var html = '<option value=0>Select</option>';
            $.each(response.data, function (i) {
                //    console.log(response.data[i].creaditor_name);
                html += '<option value="' + response.data[i].insurer_id + '">' + response.data[i].insurer_name + '</option>';
            });
            $('#insurer_name').html(html);
            // $('#insurer_name').select2();
            // $('#insurer_name').val(0).trigger('change.select2');
        }
    });
}

//Getting Partner data from api for partner data slider
const getpartnerData = (type_id = 1) => {
    var partner_id = $('#client_dropdown').val();
    var plan_name = $('#plan_name').val();
    var cover_type = $('#cover_type').val();
    var daterange = $('#daterange').val();
    var insurer_name = $('#insurer_name').val();
    var type_id = type_id;
    let stateNameNew = '';

    $.ajax({
        url: "/newdashboard/getPartenrWiseData",
        type: "POST",
        dataType: 'json',
        data: { partner_id, plan_name, cover_type, stateNameNew, insurer_name, daterange, type_id },
        success: function (response) {
            $("#ghipremium").html(0);
            $("#gpapremium").html(0);
            $("#gcipremium").html(0);
            $("#ghipolicycount").html(0);
            $("#gpapolicycount").html(0);
            $("#gcipolicycount").html(0);

            var html = '';
            var cnt1 = 0;

            $('#cardBody').html('');
            $.each(response.data, function (i) {
                //policy_type_id
                if (response.data[i].policy_type_id == 1) {
                    html += `<div class="swiper-slide">
                        <div class="cards-body d-flex flex-column">
                            <h5 class="card-title text-center m-1">${response.data[i].policy_sub_type_name}</h5>
                            <div class="card-row d-flex justify-content-between m-2 ms-3 my-2">
                                <div class="card-col d-flex style="width: 50%;">
                                    <div class="header-label py-2 me-1 my-1 ms-1"
                                        style="background-color: #0054a7"></div>
                                    <div>
                                        <div class="col heading m-1">
                                            <h5 class="slider-head-text">Premium</h5>
                                        </div>
                                        <div class="col heading m-1">
                                            <h6 class="slider-text">${numDifferentiation(response.data[i].gross_premium)}/-</h6>
                                        </div>
                                    </div>
                                </div>
                                <div class="card-col d-flex" style="width: 50%;">
                                    <div class="header-label my-1 py-2 me-1 ms-1"
                                        style="background-color: #0054a7"></div>
                                    <div>
                                        <div class="col heading m-1">
                                            <h5 class="slider-head-text">Cover</h5>
                                        </div>
                                        <div class="col heading m-1">
                                            <h6 class="slider-text">0</h6>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="card-row d-flex justify-content-between m-2 ms-3 my-1">
                                <div class="card-col d-flex" style="width: 50%;">
                                    <div class="header-label my-1 py-2 me-1 ms-1"
                                        style="background-color: #0054a7"></div>
                                    <div>
                                        <div class="col heading m-1">
                                            <h5 class="fw-medium slider-head-text">Policies</h5>
                                        </div>
                                        <div class="col heading m-1">
                                            <h6 class="fw-medium slider-text">${response.data[i].certificate_number}</h6>
                                        </div>
                                    </div>
                                </div>
                                <div class="card-col d-flex" style="width: 50%;">
                                    <div class="header-label my-1 py-2 me-1 ms-1"
                                        style="background-color: #0054a7"></div>
                                    <div>
                                        <div class="col heading m-1">
                                            <h5 class="fw-medium slider-head-text" style="font-size: 13px;">Average Ticket</h5>
                                        </div>
                                        <div class="col heading m-1">
                                            <h6 class="fw-medium slider-text">${((response.data[i].gross_premium) / (response.data[i].certificate_number)).toFixed(2)}</h6>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>`;
                    cnt1++;
                }
                // else {
                //     html2 += '  <div class="userInfo_card card_bgColor mobile_row" style="">\n' +
                //         '                                                <div class="cardHeader">\n' +
                //         '                                                    <img src="' + response.data[i].logo + '">\n' +
                //         '                                                    <h2>\n' +
                //         '                                                        ' + response.data[i].policy_sub_type_name + ' </h1>\n' +
                //         '                                                </div>\n' +
                //         '                                                <ul>\n' +
                //         '                                                    <li> <span> Premium :</span> <span>  <label class="mobile_search_data"> <i class="fa fa-rupee"></i> <span id="ghipremium">' + numDifferentiation(response.data[i].gross_premium) + '</span> </label> </span> </li>\n' +
                //         '                                                    <li> <span> Policies : </span> <span>  <label class="mobile_search_data"><span id="ghipolicycount">' + response.data[i].certificate_number + '</span></label> </span> </li>\n' +
                //         '                                                    <li> <span> Average Ticket Size : </span> <span>  <label class="mobile_search_data">' + ((response.data[i].gross_premium) / (response.data[i].certificate_number)).toFixed(2) + '</label> </span> </li>\n' +
                //         '                                                </ul>\n' +
                //         '                                            </div>';
                //     cnt2++;
                // }
            });
            var hh = '<div class="userInfo_card card_bgColor mobile_row" style=""><div class="cardHeader mt-3" style="    text-align: center;"> <h2>  No data Found..</h1></div> <ul>  </ul> </div>';
            if (cnt1 > 0) {
                $('#cardBody').html(html);
            } else {
                $('#cardBody').html(hh);
            }
            // if(cnt2 > 0){
            //     $('#cardBody2').html(html2);
            // }else{
            //     $('#cardBody2').html(hh);
            //     //$('#cardBody2').html("<span class='ml-3'>No data Found..</span>");
            // }
        }
    });

}

const getPerformanceData = () => {

    // $('#sk-circle-loader').bind('ajaxStart', function(){
    //     $(this).show();
    // }).bind('ajaxStop', function(){
    //     $(this).hide();
    // });
    $.ajax({
        url: "/newdashboard/getPerformanceData",
        type: "POST",
        dataType: 'json',
        success: function (response) {
            //<a class="dropdown-item" href="#">Booking Pending</a>
            $('#policies_issued').text(response.data?.total_policies || 0);
            $('#premium_collected').text(numDifferentiation(response.data?.total_premium || 0) + '/-');
            $('#average_daily_policies').text(response.data?.average_daily_policies || 0);
            $('#average_daily_premium').text(response.data?.average_daily_premium + '/-' || 0);
        }
    });
}

const numDifferentiation = (value) => {
    var val = Math.abs(value)
    if (val >= 10000000) {
        val = (val / 10000000).toFixed(2) + ' Cr';
    } else if (val >= 100000) {
        val = (val / 100000).toFixed(2) + ' Lac';
    }
    return val;
}

const getPendingStatus = () => {
    $.ajax({
        url: "/newdashboard/getPendingStatus",
        type: "POST",
        dataType: 'json',
        success: function (response) {
            //Initilizing the charts with data
            discrepancyCircleChart(60, response.data?.pending_proposal || 0, "discrepancyCircleChart1");
            discrepancyCircleChart(50, response.data?.payment_pending || 0, "discrepancyCircleChart2");
            discrepancyCircleChart(25, response.data?.insurance_pending || 0, "discrepancyCircleChart3");
            discrepancyCircleChart(35, response.data?.policy_pdf || 0, "discrepancyCircleChart4");
        }
    });
}

const getClientPerformanceData = (date = '', policy_type = 1) => {
    $('#sk-circle-loader').show();
    $('#home-container').hide();
    $('footer').hide();
    var date = $('#daterangeTable').val();
    $.ajax({
        url: "/newdashboard/getClientPerformance",
        type: "POST",
        dataType: 'json',
        data: { date, policy_type },
        success: function (response) {
            const series = response?.data?.map(item => parseInt(item?.certificate));
            const labels = response?.data?.map(item => (item?.creaditor_name.split(' ').length > 2 ? item?.creaditor_name.split(' ').slice(0, 2).join(' ') + '.....' : item?.creaditor_name));
            clientPerfomanceChart(series, labels, response?.data);
            $('#sk-circle-loader').hide();
            $('#home-container').show();
            $('footer').show();
        }
    });
}

$(document).ready(function () {

    $('#client_dropdown').html('<option selected value="0" selected>Select</option>');
    $('#cover_type').html('<option selected value="0" selected>Select</option>');
    $('#plan_name').html('<option selected value="0" selected>Select</option>');
    $('#insurer_name').html('<option selected value="0" selected>Select</option>');
    
    //Initialise Date range picker 
    $('#daterange').daterangepicker({
        opens: 'left',
        locale: {
            format: 'DD/MM/YYYY'
        }
    }, function (start, end, label) {
        console.log("A new date selection was made: " + start.format('DD/MM/YYYY') + ' to ' + end.format('DD/MM/YYYY'));
    });

    $('#daterange').val('');
    $('.daterange').val('');
    $('#daterange').attr("placeholder", "Select Date");
    $('.daterange').attr("placeholder", "Select Date");

    //Charts Section
    slider();
    endorsementChart();
    // clientPerfomanceChart();
    allClaimsChart();

    //Fetching Dropdown Data
    getClientName();
    getCoverType();
    getInsurerName();

    //Statistics Data
    getpartnerData();
    getPerformanceData();
    getPendingStatus();
    getClientPerformanceData();

});