<!-- Home Section  -->
<div class="home-container" id="home-container">

    <!-- Dashboard Heading Section  -->
    <!-- <div class="row card-bg py-4 pt-1 rounded">
        <div class="row">
            <div class="col-md-2 p-0">
                <div class="dashboard-title" style="box-sizing: border-box;">
                    <div class="d-flex justify-content-start">
                        <div class="header-label px-1 py-2 me-1 rounded-top rounded-bottom ms-1"
                            style="background-color: #2CD44A; height: 25px;"></div>
                        <div class="heading">
                            <h4 class="heading-blue-color">Dashboard</h4>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div> -->

    <div class="row">
        <!-- Filter & Slider Section  -->
        <div class="col-lg-8 col-md-12 custom-gap">
            <div class="row">
                <!-- Filter Section -->
                <div class="col-md-12 shadow-lg bg-white rounded-pill px-4 filter-container">
                    <div class="filter-group-row">
                            <div class="filter-group divider py-2">
                                <div class="row">
                                    <div class="col mx-1 px-2 custom-width">
                                        <h5 class="text-blue-color ms-1">Client </h5>
                                        <select class="filter-select text-style" id="client_dropdown" onchange="getPlanName(this.value);">
                                        </select>   
                                    </div>
                                </div>
                            </div>
                            <div class="filter-group divider py-2">
                                <div class="row">
                                    <div class="col mx-1 custom-width">
                                        <h5 class="text-blue-color ms-1">Plan Name </h5>
                                        <select class="filter-select text-style" id="plan_name">
                                        </select>
                                    </div>
                                </div>
                            </div>
                            <div class="filter-group divider py-2">
                                <div class="row">
                                    <div class="col mx-1 custom-width">
                                        <h5 class="text-blue-color ms-1">Cover Type</h5>
                                        <select class="filter-select text-style" id="cover_type">
                                        </select>
                                    </div>
                                </div>
                            </div>
                            <div class="filter-group divider py-2">
                                <div class="row">
                                    <div class="col mx-1 custom-width">
                                        <h5 class="text-blue-color ms-1">Insurer Filter</h5>
                                        <select class="filter-select text-style" id="insurer_name">
                                        </select>
                                    </div>
                                </div>
                            </div>
                            <div class="filter-group divider py-2 pe-2">
                                <div class="row align-items-end">
                                    <div class="custom-width custom-width d-flex align-items-end">
                                        <div>
                                            <h5 class="text-blue-color mb-1">Date</h5>
                                            <div class="date_picker"> 
                                                <input type="text" name="daterange" id="daterange" value="DD/ MM / YY"> 
                                            </div>
                                        </div>
                                        <div>
                                            <i class="fa fa-calendar" style="color: #A7D3FD" id="date_range_img"
                                                aria-hidden="true"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="filter-group d-flex align-items-end justify-content-center py-2">
                                <p class="mb-0 text-style px-3">More</p>
                            </div>
                    </div>
                </div>
                <!-- Slider Section -->
                <div class="col-md-12 p-0 shadow-lg bg-white rounded my-2 d-flex align-items-center" style="height: 200px;">
                        <!-- <div class="left-icon swiper-prev-button">
                        </div> -->
                        <div class="card-container swiper" id="slider-card">
                            <div class="slide-content">
                                <div class="card-wrapper swiper-wrapper" id="cardBody">
                                    <!-- <div class="swiper-slide">
                                        <div class="cards-body d-flex flex-column">
                                            <h5 class="card-title text-center m-1">Group Personal Accident
                                            </h5>
                                            <div class="card-row d-flex justify-content-between m-2 ms-3 my-2">
                                                <div class="card-col d-flex style=" width: 50%;">
                                                    <div class="header-label py-2 me-1 my-1 ms-1" style="background-color: #0054a7"></div>
                                                    <div>
                                                        <div class="col heading m-1">
                                                            <h5 class="slider-head-text">Premium</h5>
                                                        </div>
                                                        <div class="col heading m-1">
                                                            <h6 class="slider-text">0/-</h6>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="card-col d-flex" style="width: 50%;">
                                                    <div class="header-label my-1 py-2 me-1 ms-1" style="background-color: #0054a7"></div>
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
                                                    <div class="header-label my-1 py-2 me-1 ms-1" style="background-color: #0054a7"></div>
                                                    <div>
                                                        <div class="col heading m-1">
                                                            <h5 class="fw-medium slider-head-text">Policies</h5>
                                                        </div>
                                                        <div class="col heading m-1">
                                                            <h6 class="fw-medium slider-text">0</h6>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="card-col d-flex" style="width: 50%;">
                                                    <div class="header-label my-1 py-2 me-1 ms-1" style="background-color: #0054a7"></div>
                                                    <div>
                                                        <div class="col heading m-1">
                                                            <h5 class="fw-medium slider-head-text" style="font-size: 13px;">
                                                                Average Ticket</h5>
                                                        </div>
                                                        <div class="col heading m-1">
                                                            <h6 class="fw-medium slider-text">0</h6>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="swiper-slide">
                                        <div class="cards-body d-flex flex-column">
                                            <h5 class="card-title text-center m-1">Hospi Cash
                                            </h5>
                                            <div class="card-row d-flex justify-content-between m-2 ms-3 my-2">
                                                <div class="card-col d-flex style=" width: 50%;">
                                                    <div class="header-label py-2 me-1 my-1 ms-1" style="background-color: #0054a7"></div>
                                                    <div>
                                                        <div class="col heading m-1">
                                                            <h5 class="slider-head-text">Premium</h5>
                                                        </div>
                                                        <div class="col heading m-1">
                                                            <h6 class="slider-text">0/-</h6>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="card-col d-flex" style="width: 50%;">
                                                    <div class="header-label my-1 py-2 me-1 ms-1" style="background-color: #0054a7"></div>
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
                                                    <div class="header-label my-1 py-2 me-1 ms-1" style="background-color: #0054a7"></div>
                                                    <div>
                                                        <div class="col heading m-1">
                                                            <h5 class="fw-medium slider-head-text">Policies</h5>
                                                        </div>
                                                        <div class="col heading m-1">
                                                            <h6 class="fw-medium slider-text">0</h6>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="card-col d-flex" style="width: 50%;">
                                                    <div class="header-label my-1 py-2 me-1 ms-1" style="background-color: #0054a7"></div>
                                                    <div>
                                                        <div class="col heading m-1">
                                                            <h5 class="fw-medium slider-head-text" style="font-size: 13px;">
                                                                Average Ticket</h5>
                                                        </div>
                                                        <div class="col heading m-1">
                                                            <h6 class="fw-medium slider-text">0</h6>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="swiper-slide">
                                        <div class="cards-body d-flex flex-column">
                                            <h5 class="card-title text-center m-1">Group Critical Illness
                                            </h5>
                                            <div class="card-row d-flex justify-content-between m-2 ms-3 my-2">
                                                <div class="card-col d-flex style=" width: 50%;">
                                                    <div class="header-label py-2 me-1 my-1 ms-1" style="background-color: #0054a7"></div>
                                                    <div>
                                                        <div class="col heading m-1">
                                                            <h5 class="slider-head-text">Premium</h5>
                                                        </div>
                                                        <div class="col heading m-1">
                                                            <h6 class="slider-text">0/-</h6>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="card-col d-flex" style="width: 50%;">
                                                    <div class="header-label my-1 py-2 me-1 ms-1" style="background-color: #0054a7"></div>
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
                                                    <div class="header-label my-1 py-2 me-1 ms-1" style="background-color: #0054a7"></div>
                                                    <div>
                                                        <div class="col heading m-1">
                                                            <h5 class="fw-medium slider-head-text">Policies</h5>
                                                        </div>
                                                        <div class="col heading m-1">
                                                            <h6 class="fw-medium slider-text">0</h6>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="card-col d-flex" style="width: 50%;">
                                                    <div class="header-label my-1 py-2 me-1 ms-1" style="background-color: #0054a7"></div>
                                                    <div>
                                                        <div class="col heading m-1">
                                                            <h5 class="fw-medium slider-head-text" style="font-size: 13px;">
                                                                Average Ticket</h5>
                                                        </div>
                                                        <div class="col heading m-1">
                                                            <h6 class="fw-medium slider-text">0</h6>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="swiper-slide">
                                        <div class="cards-body d-flex flex-column">
                                            <h5 class="card-title text-center m-1">Super Topup
                                            </h5>
                                            <div class="card-row d-flex justify-content-between m-2 ms-3 my-2">
                                                <div class="card-col d-flex style=" width: 50%;">
                                                    <div class="header-label py-2 me-1 my-1 ms-1" style="background-color: #0054a7"></div>
                                                    <div>
                                                        <div class="col heading m-1">
                                                            <h5 class="slider-head-text">Premium</h5>
                                                        </div>
                                                        <div class="col heading m-1">
                                                            <h6 class="slider-text">0/-</h6>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="card-col d-flex" style="width: 50%;">
                                                    <div class="header-label my-1 py-2 me-1 ms-1" style="background-color: #0054a7"></div>
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
                                                    <div class="header-label my-1 py-2 me-1 ms-1" style="background-color: #0054a7"></div>
                                                    <div>
                                                        <div class="col heading m-1">
                                                            <h5 class="fw-medium slider-head-text">Policies</h5>
                                                        </div>
                                                        <div class="col heading m-1">
                                                            <h6 class="fw-medium slider-text">0</h6>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="card-col d-flex" style="width: 50%;">
                                                    <div class="header-label my-1 py-2 me-1 ms-1" style="background-color: #0054a7"></div>
                                                    <div>
                                                        <div class="col heading m-1">
                                                            <h5 class="fw-medium slider-head-text" style="font-size: 13px;">
                                                                Average Ticket</h5>
                                                        </div>
                                                        <div class="col heading m-1">
                                                            <h6 class="fw-medium slider-text">0</h6>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div> -->
                                </div>
                            </div>
                            <div class="swiper-pagination"></div>
                        </div>
                        <!-- <div class="right-icon swiper-next-button">
                        </div> -->
                </div>
            </div>
        </div>

        <!-- Perfomance Section -->
        <div class="col-lg-4 col-md-12 p-0 pe-2">
            <div class="rounded filter-partial">
                    <!-- <div style="box-sizing: border-box;">
                        <div class="d-flex justify-content-start p-2">
                            <div class="header-label px-1 py-2 me-1 rounded-top rounded-bottom mx-4"></div>
                            <div class="heading">
                                <h5 class="heading-blue-color ms-2 mb-0">Portal Performance</h5>
                            </div>
                        </div>
                    </div> -->
                    <div class="performance-row">
                        <div class="performance-col me-2">
                            <div class="rectangle-blue"></div>
                            <div class="rectangle-white"></div>
                            <div class="policy-container">
                                <div class="policy-header">Total Policies Issued</div>
                                <div class="custom-divider"></div>
                                <div class="policy-number py-2" id="policies_issued">0</div>
                            </div>
                        </div>
                        <div class="performance-col">
                            <div class="rectangle-blue"></div>
                            <div class="rectangle-white"></div>
                            <div class="policy-container">
                                <div class="policy-header">Total Premium Collected</div>
                                <div class="custom-divider"></div>
                                <div class="policy-number py-2" id="premium_collected">0</div>
                            </div>
                        </div>
                    </div>
                    <div class="performance-row">
                        <div class="performance-col me-2">
                            <div class="rectangle-blue"></div>
                            <div class="rectangle-white"></div>
                            <div class="policy-container">
                                <div class="policy-header">Average Daily Policies</div>
                                <div class="custom-divider"></div>
                                <div class="policy-number py-2" id="average_daily_policies">0</div>
                            </div>
                        </div>
                        <div class="performance-col">
                            <div class="rectangle-blue"></div>
                            <div class="rectangle-white"></div>
                            <div class="policy-container">
                                <div class="policy-header">Avg. Daily issues Premium Collected</div>
                                <div class="custom-divider"></div>
                                <div class="policy-number py-2" id="average_daily_premium">0</div>
                            </div>
                        </div>
                    </div>
            </div>
        </div>

        <!-- Charts Section -->
        <!-- Endorsement Section  -->
        <div class="col-lg-4 col-md-6 p-0 my-2">
                <div class="card-section shadow-lg bg-white m-2 ms-0">
                    <div class="w-100 d-flex justify-content-between align-items-end p-1 py-2 dotted-border">
                        <div class="d-flex justify-content-start align-items-end">
                            <div class="header-label px-1 py-2 me-1 rounded-top rounded-bottom ms-1"
                                style="background-color: #2CD44A; height: 20px;"></div>
                            <h5 class="heading-blue-color m-0 ms-1 fs-6">Endorsement</h5>
                        </div>
                        <div class="d-flex justify-content-start align-items-end">
                            <div class="zoom-out mx-1">
                                <svg width="20px" height="20px" viewBox="0 0 32 32" version="1.1"
                                    xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink"
                                    xmlns:sketch="http://www.bohemiancoding.com/sketch/ns" fill="#000000">
                                    <g id="SVGRepo_bgCarrier" stroke-width="0"></g>
                                    <g id="SVGRepo_tracerCarrier" stroke-linecap="round" stroke-linejoin="round">
                                    </g>
                                    <g id="SVGRepo_iconCarrier">
                                        <title>zoom-out</title>
                                        <desc>Created with Sketch Beta.</desc>
                                        <defs> </defs>
                                        <g id="Page-1" stroke="none" stroke-width="1" fill="none" fill-rule="evenodd"
                                            sketch:type="MSPage">
                                            <g id="Icon-Set" sketch:type="MSLayerGroup"
                                                transform="translate(-360.000000, -1139.000000)" fill="#0182FF">
                                                <path
                                                    d="M373.46,1163.45 C367.17,1163.45 362.071,1158.44 362.071,1152.25 C362.071,1146.06 367.17,1141.04 373.46,1141.04 C379.75,1141.04 384.85,1146.06 384.85,1152.25 C384.85,1158.44 379.75,1163.45 373.46,1163.45 L373.46,1163.45 Z M391.688,1169.25 L383.429,1161.12 C385.592,1158.77 386.92,1155.67 386.92,1152.25 C386.92,1144.93 380.894,1139 373.46,1139 C366.026,1139 360,1144.93 360,1152.25 C360,1159.56 366.026,1165.49 373.46,1165.49 C376.672,1165.49 379.618,1164.38 381.932,1162.53 L390.225,1170.69 C390.629,1171.09 391.284,1171.09 391.688,1170.69 C392.093,1170.3 392.093,1169.65 391.688,1169.25 L391.688,1169.25 Z M378.689,1151.41 L368.643,1151.41 C368.102,1151.41 367.663,1151.84 367.663,1152.37 C367.663,1152.9 368.102,1153.33 368.643,1153.33 L378.689,1153.33 C379.23,1153.33 379.669,1152.9 379.669,1152.37 C379.669,1151.84 379.23,1151.41 378.689,1151.41 L378.689,1151.41 Z"
                                                    id="zoom-out" sketch:type="MSShapeGroup"> </path>
                                            </g>
                                        </g>
                                    </g>
                                </svg>
                            </div>
                            <div class="zoom-in mx-1">
                                <svg width="20px" height="20px" viewBox="0 0 32 32" version="1.1"
                                    xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink"
                                    xmlns:sketch="http://www.bohemiancoding.com/sketch/ns" fill="#000000">
                                    <g id="SVGRepo_bgCarrier" stroke-width="0"></g>
                                    <g id="SVGRepo_tracerCarrier" stroke-linecap="round" stroke-linejoin="round">
                                    </g>
                                    <g id="SVGRepo_iconCarrier">
                                        <title>zoom-in</title>
                                        <desc>Created with Sketch Beta.</desc>
                                        <defs> </defs>
                                        <g id="Page-1" stroke="none" stroke-width="1" fill="none" fill-rule="evenodd"
                                            sketch:type="MSPage">
                                            <g id="Icon-Set" sketch:type="MSLayerGroup"
                                                transform="translate(-308.000000, -1139.000000)" fill="#0182FF">
                                                <path
                                                    d="M321.46,1163.45 C315.17,1163.45 310.07,1158.44 310.07,1152.25 C310.07,1146.06 315.17,1141.04 321.46,1141.04 C327.75,1141.04 332.85,1146.06 332.85,1152.25 C332.85,1158.44 327.75,1163.45 321.46,1163.45 L321.46,1163.45 Z M339.688,1169.25 L331.429,1161.12 C333.592,1158.77 334.92,1155.67 334.92,1152.25 C334.92,1144.93 328.894,1139 321.46,1139 C314.026,1139 308,1144.93 308,1152.25 C308,1159.56 314.026,1165.49 321.46,1165.49 C324.672,1165.49 327.618,1164.38 329.932,1162.53 L338.225,1170.69 C338.629,1171.09 339.284,1171.09 339.688,1170.69 C340.093,1170.3 340.093,1169.65 339.688,1169.25 L339.688,1169.25 Z M326.519,1151.41 L322.522,1151.41 L322.522,1147.41 C322.522,1146.85 322.075,1146.41 321.523,1146.41 C320.972,1146.41 320.524,1146.85 320.524,1147.41 L320.524,1151.41 L316.529,1151.41 C315.978,1151.41 315.53,1151.59 315.53,1152.14 C315.53,1152.7 315.978,1153.41 316.529,1153.41 L320.524,1153.41 L320.524,1157.41 C320.524,1157.97 320.972,1158.41 321.523,1158.41 C322.075,1158.41 322.522,1157.97 322.522,1157.41 L322.522,1153.41 L326.519,1153.41 C327.07,1153.41 327.518,1152.96 327.518,1152.41 C327.518,1151.86 327.07,1151.41 326.519,1151.41 L326.519,1151.41 Z"
                                                    id="zoom-in" sketch:type="MSShapeGroup"> </path>
                                            </g>
                                        </g>
                                    </g>
                                </svg>
                            </div>
                        </div>
                    </div>
                    <div class="col-12">
                        <div id="endorsementChart"></div>
                    </div>
                </div>
        </div>

        <!-- Client Performance Section -->
        <div class="col-lg-4 col-md-6 p-0 my-2">
            <div class="card-section shadow-lg bg-white m-2 ms-0 d-flex align-items-center">
                    <div class="w-100 d-flex justify-content-between align-items-end p-1 py-2 dotted-border">
                        <div class="d-flex justify-content-start align-items-end">
                            <div class="header-label px-1 py-2 me-1 rounded-top rounded-bottom ms-1"
                                style="background-color: #2CD44A; height: 20px;"></div>
                            <h5 class="heading-blue-color m-0 ms-1 fs-6">Client Performance</h5>
                        </div>
                        <div class="d-flex justify-content-start align-items-end me-2">
                            <svg fill="#0182FF" width="20px" height="25px" viewBox="0 0 32.00 32.00" version="1.1"
                                xmlns="http://www.w3.org/2000/svg" stroke="#0182FF" stroke-width="0.00032">
                                <g id="SVGRepo_bgCarrier" stroke-width="0"></g>
                                <g id="SVGRepo_tracerCarrier" stroke-linecap="round" stroke-linejoin="round"
                                    stroke="#CCCCCC" stroke-width="0.128"></g>
                                <g id="SVGRepo_iconCarrier">
                                    <path
                                        d="M8.502 9.999h-7.002c-0.552 0-1 0.447-1 1v20.001c0 0.552 0.448 1 1 1h7.002c0.553 0 1-0.448 1-1v-20c0-0.553-0.447-1-1-1zM7.502 30h-5.002v-18h5.002v18zM19.492 15.945h-7.003c-0.553 0-1 0.448-1 1v14.055c0 0.552 0.447 1 1 1h7.003c0.552 0 1-0.448 1-1v-14.055c0-0.553-0.447-1-1-1zM18.492 30h-5.003v-12.055h5.003v12.055zM30.5 0h-6.992c-0.552 0-1 0.448-1 1v30c0 0.552 0.448 1 1 1h6.992c0.552 0 1-0.448 1-1v-30c0-0.552-0.448-1-1-1zM29.5 30h-4.992v-28h4.992v28z">
                                    </path>
                                </g>
                            </svg>
                        </div>
                    </div>
                    <div class="w-100">
                        <div id="clientPerformanceChart"></div>
                    </div>
            </div>
        </div>

        <!-- Status Section  -->
        <div class="col-lg-4 col-md-6 p-0 my-2">
            <div class="card-section status-cards shadow-lg bg-white m-2 ms-0 d-flex align-items-center">
                <div class="w-100 d-flex justify-content-between align-items-end p-1 py-2 dotted-border">
                    <div class="d-flex justify-content-start align-items-end">
                        <div class="header-label px-1 py-2 me-1 rounded-top rounded-bottom ms-1"
                            style="background-color: #2CD44A; height: 20px;"></div>
                        <h5 class="heading-blue-color m-0 ms-1 fs-6">Status</h5>
                    </div>
                </div>
                <div class="d-flex justify-content-start align-items-end">
                    <div class="discrepancy-card-container custom-margin">
                        <div class="discrepancy-card shadow-lg rounded d-flex align-items-center py-2">
                            <div class="header-label px-1 py-2 me-1 rounded-top rounded-bottom h-100"
                                style="background-color: #0054a7"></div>
                            <div class="heading w-50 ms-1">
                                <h6 class="discrepancy-text">Proposal Pending</h6>
                            </div>
                            <div id="discrepancyCircleChart1"></div>
                        </div>
                        <div class="discrepancy-card shadow-lg rounded d-flex align-items-center py-2">
                            <div class="header-label px-1 py-2 me-1 rounded-top rounded-bottom h-100"
                                style="background-color: #0054a7"></div>
                            <div class="heading w-50 ms-1">
                                <h6 class="discrepancy-text">Payment Pending</h6>
                            </div>
                            <div id="discrepancyCircleChart2"></div>
                        </div>
                        <div class="discrepancy-card shadow-lg rounded d-flex align-items-center py-2">
                            <div class="header-label px-1 py-2 me-1 rounded-top rounded-bottom h-100"
                                style="background-color: #0054a7"></div>
                            <div class="heading w-50 ms-1">
                                <h6 class="discrepancy-text">COI Issuance Pending</h6>
                            </div>
                            <div id="discrepancyCircleChart3"></div>
                        </div>
                        <div class="discrepancy-card shadow-lg rounded d-flex align-items-center py-2">
                            <div class="header-label px-1 py-2 me-1 rounded-top rounded-bottom h-100"
                                style="background-color: #0054a7"></div>
                            <div class="heading w-50 ms-1">
                                <h6 class="discrepancy-text">COI Pdf Not Generated</h6>
                            </div>
                            <div id="discrepancyCircleChart4"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Claim Section  -->
        <div class="col-lg-8 col-md-6 p-0 my-2">
                <div class="card-section bg-white shadow-lg me-2 overflow-auto">
                    <div class="w-100 d-flex justify-content-between align-items-end p-1 py-2 dotted-border">
                        <div class="d-flex justify-content-start align-items-end">
                            <div class="header-label px-1 py-2 me-1 rounded-top rounded-bottom ms-1"
                                style="background-color: #2CD44A; height: 20px;"></div>
                            <h5 class="heading-blue-color m-0 ms-1 fs-6">Claim</h5>
                        </div>
                    </div>
                    <div class="col-md-12 table-responsive scroll-table mt-1" style="height: 240px;">
                        <table class="table custom-table">
                            <thead class="table-head">
                                <tr>
                                    <th scope="col" style="border-top-left-radius: 10px; border-bottom-left-radius: 10px;">
                                        Claim
                                        ID</th>
                                    <th scope="col">Employee Na..</th>
                                    <th scope="col">Employee Co..</th>
                                    <th scope="col">Corporate Na</th>
                                    <th scope="col">Policy No.</th>
                                    <th scope="col">Claim Status</th>
                                    <th scope="col">TAT</th>
                                    <th scope="col"
                                        style="border-top-right-radius: 10px; border-bottom-right-radius: 10px;">
                                        Details</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>2548</td>
                                    <td>Affinity</td>
                                    <td>EMP1011</td>
                                    <td>FynTune De...</td>
                                    <td>Flex Policy 1</td>
                                    <td>Deficiency</td>
                                    <td>110 Days</td>
                                    <td><button type="button" class="details-btn btn btn-primary btn-sm">Details</button>
                                    </td>
                                </tr>
                                <tr>
                                    <td>2548</td>
                                    <td>Affinity</td>
                                    <td>EMP1011</td>
                                    <td>FynTune De...</td>
                                    <td>Flex Policy 1</td>
                                    <td>Deficiency</td>
                                    <td>110 Days</td>
                                    <td><button type="button" class="details-btn btn btn-primary btn-sm">Details</button>
                                    </td>
                                </tr>
                                <tr>
                                    <td>2548</td>
                                    <td>Affinity</td>
                                    <td>EMP1011</td>
                                    <td>FynTune De...</td>
                                    <td>Flex Policy 1</td>
                                    <td>Deficiency</td>
                                    <td>110 Days</td>
                                    <td><button type="button" class="details-btn btn btn-primary btn-sm">Details</button>
                                    </td>
                                </tr>
                                <tr>
                                    <td>2548</td>
                                    <td>Affinity</td>
                                    <td>EMP1011</td>
                                    <td>FynTune De...</td>
                                    <td>Flex Policy 1</td>
                                    <td>Deficiency</td>
                                    <td>110 Days</td>
                                    <td><button type="button" class="details-btn btn btn-primary btn-sm">Details</button>
                                    </td>
                                </tr>
                                <tr>
                                    <td>2548</td>
                                    <td>Affinity</td>
                                    <td>EMP1011</td>
                                    <td>FynTune De...</td>
                                    <td>Flex Policy 1</td>
                                    <td>Deficiency</td>
                                    <td>110 Days</td>
                                    <td><button type="button" class="details-btn btn btn-primary btn-sm">Details</button>
                                    </td>
                                </tr>
                                <tr>
                                    <td>2548</td>
                                    <td>Affinity</td>
                                    <td>EMP1011</td>
                                    <td>FynTune De...</td>
                                    <td>Flex Policy 1</td>
                                    <td>Deficiency</td>
                                    <td>110 Days</td>
                                    <td><button type="button" class="details-btn btn btn-primary btn-sm">Details</button>
                                    </td>
                                </tr>
                                <tr>
                                    <td>2548</td>
                                    <td>Affinity</td>
                                    <td>EMP1011</td>
                                    <td>FynTune De...</td>
                                    <td>Flex Policy 1</td>
                                    <td>Deficiency</td>
                                    <td>110 Days</td>
                                    <td><button type="button" class="details-btn btn btn-primary btn-sm">Details</button>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
    
                    </div>
                </div>
        </div>

        <!-- All Claims Section  -->
        <div class="col-lg-4 col-md-12 p-0 my-2">
                <div class="card-section bg-white shadow-lg me-2">
                    <div class="w-100 d-flex justify-content-between align-items-end p-1 py-2 dotted-border">
                        <div class="d-flex justify-content-start align-items-end">
                            <div class="header-label px-1 py-2 me-1 rounded-top rounded-bottom ms-1"
                                style="background-color: #2CD44A; height: 20px;"></div>
                            <h5 class="heading-blue-color m-0 ms-1 fs-6">All Claims</h5>
                        </div>
                    </div>
                    <div class="w-100 h-100 my-2">
                        <div id="allClaimsChart"></div>
                    </div>
                </div>
        </div>
    </div>
</div>

<!-- Custom JS for New Dashboard -->
<script type="text/javascript" src='<?PHP echo base_url('assets/js/new-dashboard-script.js', PROTOCOL); ?>'>
</script>