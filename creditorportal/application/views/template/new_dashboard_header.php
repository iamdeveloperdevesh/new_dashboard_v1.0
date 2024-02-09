<?php
//Get Login user details
$ludata = array();
$ludata['utoken'] = $_SESSION['webpanel']['utoken'];
$ludata['id'] = $_SESSION['webpanel']['employee_id'];
$luserDetails = curlFunction(SERVICE_URL . '/api/getLoginUserDetails', $ludata);
$luserDetails = json_decode($luserDetails, true);
//echo "here<pre>";print_r($luserDetails);exit;
//echo $luserDetails['Data']['user_data'][0]['employee_id'];exit;
$last_login = date("d-m-Y H:i:s A", strtotime($luserDetails['Data']['user_data'][0]['last_login']));
$logo=$_SESSION['webpanel']['creditor_logo'];

?>
<!DOCTYPE html>
<html class="no-js" lang="en">

<head>
    <meta charset="utf-8">
    <meta http-equiv="x-ua-compatible" content="ie=edge">
    <title>Elephant.in</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- <style type="text/css">
		@font-face {
			font-family: 'Titillium';
			src: url(assets/fonts/TitilliumWeb-Regular.ttf);
		}
	</style> -->

    <!-- Font Awesome-->
    <link rel="stylesheet" type="text/css" href="<?php echo base_url('assets/css/font-awesome.min.css', PROTOCOL); ?>">

    <link rel="shortcut icon" href="<?php echo base_url(); ?>assets/images/favicon.ico">

    <!-- Flag icon-->
    <?php /**?>
    <link rel="stylesheet" type="text/css" href="<?php echo base_url(); ?>assets/css/flag-icon.css">
    <link rel="stylesheet" type="text/css" href="<?php echo base_url(); ?>assets/css/icofont.css">
    link href="<?php echo base_url(); ?>assets/css/daterangepicker.css" rel="stylesheet">
    <script type="text/javascript" src="<?php echo base_url(); ?>assets/js/bootstrap.min.v3.3.6.js"></script>
    <?php **/ ?>
    <!-- ico-font-->
    <link href="<?php echo base_url('assets/css/jquery-ui.css', PROTOCOL); ?>" rel="stylesheet">
    <link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.css" />
    <link href="<?php echo base_url('assets/css/noty_theme_default.css', PROTOCOL); ?>" rel="stylesheet">

    <!-- Bootstrap css-->
    <link rel="stylesheet" href="<?php echo base_url('assets/css/themify-icons.css', PROTOCOL); ?>">
    <link rel="stylesheet" href="<?php echo base_url('assets/css/metisMenu.css', PROTOCOL); ?>">
    <link rel="stylesheet" href="<?php echo base_url('assets/css/owl.carousel.min.css', PROTOCOL); ?>">
    <link rel="stylesheet" href="<?php echo base_url('assets/css/slicknav.min.css', PROTOCOL); ?>">
    <link rel="stylesheet" href="<?php echo base_url('assets/css/typography.css', PROTOCOL); ?>">
    <link rel="stylesheet" href="<?php echo base_url('assets/css/default-css.css', PROTOCOL); ?>">
    <link rel="stylesheet" href="<?php echo base_url('assets/css/responsive.css', PROTOCOL); ?>">

    <!-- Bootstrap 5 CSS -->
    <link rel="stylesheet" type="text/css"
        href="<?php echo base_url('assets/css/bootstrap-5/bootstrap.min.css', PROTOCOL); ?>">

    <!-- New Dashboard CSS -->
    <link rel="stylesheet" href="<?php echo base_url('assets/css/new-dashboard.css', PROTOCOL); ?>">
    <!-- SwiperJS CSS -->
    <link rel="stylesheet" href="<?php echo base_url('assets/css/swiper-bundle.min.css', PROTOCOL); ?>">

    <link href="<?PHP echo base_url('assets/css/jquery.dataTables.css', PROTOCOL); ?>" rel="stylesheet" type="text/css">
    <link href="<?PHP echo base_url('assets/css/jquery.noty.css', PROTOCOL); ?>" rel="stylesheet" type="text/css">

    <link href="<?php echo base_url('assets/css/select2.css', PROTOCOL); ?>" type="text/css" rel="stylesheet" />

    <link type="text/css" rel="stylesheet"
        href="<?php echo base_url('assets/css/bootstrap-material-datetimepicker.css', PROTOCOL); ?>" />

    <link href="<?php echo base_url('assets/css/bootstrap-datetimepicker.min.css', PROTOCOL); ?>" type="text/css"
        rel="stylesheet" />

    <script type="text/javascript" src="<?php echo base_url('assets/js/jquery-2.1.4.min.js', PROTOCOL); ?>"></script>
    <script type="text/javascript" src="<?php echo base_url('assets/js/plugins/jquery-ui.custom.min.js', PROTOCOL); ?>">
    </script>
    <script type="text/javascript" src="<?php echo base_url('assets/js/essential-plugins.js', PROTOCOL); ?>"></script>
    <script type="text/javascript" src="<?php echo base_url('assets/js/jquery-ui.js', PROTOCOL); ?>"></script>

    <!-- CK Editor plugins -->
    <script type="text/javascript" src="<?php echo base_url('assets/js/ckeditor/ckeditor.js', PROTOCOL); ?>"></script>
    <script type="text/javascript" src="<?php echo base_url('assets/js/ckeditor/adapters/jquery.js', PROTOCOL); ?>">
    </script>

    <script src="<?php echo base_url('assets/js/jquery.form.js', PROTOCOL); ?>"></script>
    <script src="<?php echo base_url('assets/js/jquery.validate.js', PROTOCOL); ?>"></script>
    <script src="<?php echo base_url('assets/js/additional-methods.js', PROTOCOL); ?>"></script>

    <script type="text/javascript" src="<?php echo base_url('assets/js/jquery.colorbox.js', PROTOCOL); ?>"></script>

    <!-- Datatable plugin-->
    <script type="text/javascript" src='<?PHP echo base_url(' assets/js/jquery.dataTables.min.js', PROTOCOL); ?>
    '>
    </script>
    <script type="text/javascript" src='<?PHP echo base_url(' assets/js/datatable.js', PROTOCOL); ?>
    '>
    </script>
    <script type="text/javascript" src="<?PHP echo base_url('assets/js/jquery.noty.js', PROTOCOL); ?>"></script>
    <script type="text/javascript" src="<?PHP echo base_url('assets/js/select2.min.js', PROTOCOL); ?>"></script>
    <script type="text/javascript" src="<?PHP echo base_url('assets/js/moment-with-locales.js', PROTOCOL); ?>"></script>
    <script type="text/javascript"
        src="<?PHP echo base_url('assets/js/bootstrap-material-datetimepicker.js', PROTOCOL); ?>"></script>

    <!-- Date Range Picker -->
    <script type="text/javascript" src="<?PHP echo base_url('assets/js/moment-with-locales.js', PROTOCOL); ?>"></script>
    <script type="text/javascript" src="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.min.js"></script>

    <!-- Bootstrap JS -->
    <script type="text/javascript" src='<?PHP echo base_url('assets/js/bootstrap-5/bootstrap.bundle.min.js', PROTOCOL);
        ?>
    '>
    </script>

    <!-- Apex Chart JS -->
    <script type="text/javascript" src='<?PHP echo base_url('assets/js/apexcharts.js', PROTOCOL); ?>
    '>
    </script>

    <!-- SwiperJS CSS -->
    <script type="text/javascript" src='<?PHP echo base_url('assets/js/swiper-bundle.min.js', PROTOCOL); ?>
    '>
    </script>

    <!-- Google Translate JS -->
    <script type="text/javascript" src="//translate.google.com/translate_a/element.js?cb=googleTranslateElementInit">
    </script>
</head>

<!-- Header Style -->
<style>
    @import url('https://fonts.googleapis.com/css2?family=Titillium+Web:wght@200;300;400;500;600;700;900&display=swap');

    :root {
        /* ===== Colors ===== */
        --body-color: #E4E9F7;
        --sidebar-color: #FFF;
        --primary-color: #0182FF;
        --primary-color-light: #F6F5FF;
        --toggle-color: #DDD;
        --text-color: #707070;

        /* ====== Transition ====== */
        --tran-03: all 0.2s ease;
        --tran-03: all 0.3s ease;
        --tran-04: all 0.3s ease;
        --tran-05: all 0.3s ease;
    }

    body {
        margin: 0;
        padding: 0;
        font-family: "Titillium Web", sans-serif !important;
        overflow-x: hidden;
    }

    /* Header Section */
    .custom-navbar {
        margin-left: 60px;
        z-index: 99;
    }

    .dropdown-menu {
        width: 285px;
    }

    .drop-divider {
        border-bottom: 1px solid #d6d6d6;
    }

    img.logo {
        height: 40px;
        max-width: 100%;
    }

    ::selection {
        background-color: var(--primary-color);
        color: #fff;
    }

    /* ===== Sidebar ===== */
    .sidebar {
        position: fixed;
        top: 0;
        /* left: 0; */
        height: 100%;
        width: 250px;
        padding: 10px 0px;
        background: var(--sidebar-color);
        transition: var(--tran-05);
        border: 1px solid #e8e8e8;
        z-index: 100;
    }

    .sidebar.close {
        width: 60px;
    }

    /* ===== Reusable code - Here ===== */
    .sidebar li {
        height: 50px;
        list-style: none;
        display: flex;
        align-items: center;
        margin-top: 10px;
    }

    .sidebar .icon {
        min-width: 60px;
        border-radius: 6px;
        height: 100%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.1rem;
    }

    .sidebar .text,
    .sidebar .icon {
        color: var(--text-color);
        transition: var(--tran-03);
    }

    .sidebar .text {
        font-size: 15px;
        font-weight: 500;
        white-space: nowrap;
        opacity: 1;
    }

    .sidebar.close .text {
        opacity: 0;
    }

    /* =========================== */

    .sidebar header {
        position: relative;
    }

    .sidebar header .logo-text {
        display: flex;
        flex-direction: column;
    }

    .sidebar header .toggle {
        display: block;
        font-size: 24px;
        min-width: 58px;
        text-align: center;
        line-height: 23px;
        margin-top: 10px;
        cursor: pointer;
        color: black;
        transition: var(--tran-05);
    }

    .ti-close {
        font-weight: bolder;
        font-size: 16px !important;
        text-align: right !important;
        margin: 5px 15px 0px 0px;
        .
    }


    .sidebar .menu {
        margin-top: 20px;
    }

    .sidebar li a {
        list-style: none;
        height: 100%;
        background-color: transparent;
        display: flex;
        align-items: center;
        height: 100%;
        width: 100%;
        border-radius: 6px;
        text-decoration: none;
        transition: var(--tran-03);
    }

    .sidebar li a:hover {
        background-color: var(--primary-color);
    }

    .sidebar li a:hover .icon,
    .sidebar li a:hover .text {
        color: var(--sidebar-color);
    }

    .menu-links {
        padding: 0;
    }

    .sidebar .menu-bar {
        height: calc(100% - 30px);
        display: flex;
        flex-direction: column;
        justify-content: space-between;
        overflow-y: scroll;
    }

    .menu-bar::-webkit-scrollbar {
        display: none;
    }

    .drop-profile {
        display: none;
    }

    /* CSS for Loader */
    /* Loader Css Start */

    .sk-circle-wrapper {
        background: rgb(57 57 57);
        opacity: 0.7;
        width: 100%;
        height: 100%;
        text-align: center;
        vertical-align: middle;
        position: fixed;
        inset: 0px;
        margin: auto;
        font-size: 16px;
        z-index: 101;
        color: rgb(0, 0, 0);
    }
    .sk-circle {
        width: 120px;
        height: 120px;
        position: absolute;
        inset: 5% 0px 0px;
        margin: auto;
    }
    .sk-circle .sk-child {
        width: 100%;
        height: 100%;
        position: absolute;
        left: 0;
        top: 0;
    }
    .sk-circle .sk-child:before {
        content: '';
        display: block;
        margin: 0 auto;
        width: 15%;
        height: 15%;
        background-color:#2cd44a;;
        border-radius: 100%;
        -webkit-animation: sk-circleBounceDelay 1.2s infinite ease-in-out both;
        animation: sk-circleBounceDelay 1.2s infinite ease-in-out both;
    }
    .sk-circle .sk-circle2 {
        -webkit-transform: rotate(30deg);
        -ms-transform: rotate(30deg);
        transform: rotate(30deg); }
    .sk-circle .sk-circle3 {
        -webkit-transform: rotate(60deg);
        -ms-transform: rotate(60deg);
        transform: rotate(60deg); }
    .sk-circle .sk-circle4 {
        -webkit-transform: rotate(90deg);
        -ms-transform: rotate(90deg);
        transform: rotate(90deg); }
    .sk-circle .sk-circle5 {
        -webkit-transform: rotate(120deg);
        -ms-transform: rotate(120deg);
        transform: rotate(120deg); }
    .sk-circle .sk-circle6 {
        -webkit-transform: rotate(150deg);
        -ms-transform: rotate(150deg);
        transform: rotate(150deg); }
    .sk-circle .sk-circle7 {
        -webkit-transform: rotate(180deg);
        -ms-transform: rotate(180deg);
        transform: rotate(180deg); }
    .sk-circle .sk-circle8 {
        -webkit-transform: rotate(210deg);
        -ms-transform: rotate(210deg);
        transform: rotate(210deg); }
    .sk-circle .sk-circle9 {
        -webkit-transform: rotate(240deg);
        -ms-transform: rotate(240deg);
        transform: rotate(240deg); }
    .sk-circle .sk-circle10 {
        -webkit-transform: rotate(270deg);
        -ms-transform: rotate(270deg);
        transform: rotate(270deg); }
    .sk-circle .sk-circle11 {
        -webkit-transform: rotate(300deg);
        -ms-transform: rotate(300deg);
        transform: rotate(300deg); }
    .sk-circle .sk-circle12 {
        -webkit-transform: rotate(330deg);
        -ms-transform: rotate(330deg);
        transform: rotate(330deg); }
    .sk-circle .sk-circle2:before {
        -webkit-animation-delay: -1.1s;
        animation-delay: -1.1s;
        background-color:#0182ff; }
    .sk-circle .sk-circle3:before {
        -webkit-animation-delay: -1s;
        animation-delay: -1s;
        background-color:#2cd44a; }
    .sk-circle .sk-circle4:before {
        -webkit-animation-delay: -0.9s;
        animation-delay: -0.9s;
        background-color:#2cd44a; }
    .sk-circle .sk-circle5:before {
        -webkit-animation-delay: -0.8s;
        animation-delay: -0.8s;
        background-color:#0182ff; }
    .sk-circle .sk-circle6:before {
        -webkit-animation-delay: -0.7s;
        animation-delay: -0.7s;
        background-color:#2cd44a; }
    .sk-circle .sk-circle7:before {
        -webkit-animation-delay: -0.6s;
        animation-delay: -0.6s;
        background-color:#0182ff; }
    .sk-circle .sk-circle8:before {
        -webkit-animation-delay: -0.5s;
        animation-delay: -0.5s;
        background-color:#2cd44a; }
    .sk-circle .sk-circle9:before {
        -webkit-animation-delay: -0.4s;
        animation-delay: -0.4s;
        background-color:#0182ff; }
    .sk-circle .sk-circle10:before {
        -webkit-animation-delay: -0.3s;
        animation-delay: -0.3s;
        background-color:#2cd44a; }
    .sk-circle .sk-circle11:before {
        -webkit-animation-delay: -0.2s;
        animation-delay: -0.2s;
        background-color:#0182ff; }
    .sk-circle .sk-circle12:before {
        -webkit-animation-delay: -0.1s;
        animation-delay: -0.1s;
        background-color:#2cd44a; }
    @-webkit-keyframes sk-circleBounceDelay {
        0%, 80%, 100% {
            -webkit-transform: scale(0);
            transform: scale(0);
        } 40% {
            -webkit-transform: scale(1);
            transform: scale(1);
        }
    }
    @keyframes sk-circleBounceDelay {
        0%, 80%, 100% {
            -webkit-transform: scale(0);
            transform: scale(0);
        } 40% {
            -webkit-transform: scale(1);
            transform: scale(1);
        }
    }

    /* Loader CSS End */

    /* Media Queries for responsive css */
    @media (max-width: 767px) {       
        .sidebar.close {
            width: 0px;
        }

        .custom-navbar {
            margin-left: 0px;
        }

        .logo {
            margin-left: 1.5rem;
        }

        .navbar-end-section {
            width: 100%;
        }

        .custom-dropdown {
            display: flex;
            justify-content: end;
        }

        .custom-dropdown button span {
            display: none;
        }

        .drop-profile {
            display: block;
        }

        .drop-profile span{
            font-weight: bold;
        }
    }
</style>

<body class="body-container main-content-inner p-0" style="min-height: calc(100vh - 100px);">
    <!-- Header/Navbar Section -->
    <nav class="custom-navbar navbar sticky-top bg-body-tertiary p-2 shadow">
        <div class="d-flex w-100">
            <div class="w-75">
                <div class="col logo">
                <?php
                    if ($logo == "" || is_null($logo)) { ?>
                        <a href="<?php echo base_url('home', PROTOCOL); ?>"><img class="welcomeLogo"
                                src="<?php echo base_url('assets/images/logo.png', PROTOCOL); ?>" alt="logo" width="150"></a>
                    <?php } else { ?>
                        <a href="<?php echo base_url('home', PROTOCOL); ?>"><img class="welcomeLogo" width="150"
                                src="<?php echo base_url($logo); ?>" alt="logo"></a>
                    <?php }
                ?>
                </div>
            </div>
            <div class="w-25 navbar-end-section">
                <div class="d-flex justify-content-end align-items-end nav-side">
                    <div class="align-items-end google-translate me-4">
                        <div id="google_translate_element"></div>
                    </div>
                    <div class="align-items-end dropdown">
                        <div class="dropdown custom-dropdown">
                            <button
                                class="btn bg-white dropdown-toggle rounded-pill shadow-lg d-flex justify-content-center align-items-center"
                                type="button" id="dropdownMenuButton1" data-bs-toggle="dropdown" aria-expanded="false">
                                <svg width="30px" height="35px" viewBox="0 0 24 24" fill="none"
                                    xmlns="http://www.w3.org/2000/svg">
                                    <circle cx="12" cy="6" r="4" fill="#1C274C" />
                                    <path
                                        d="M20 17.5C20 19.9853 20 22 12 22C4 22 4 19.9853 4 17.5C4 15.0147 7.58172 13 12 13C16.4183 13 20 15.0147 20 17.5Z"
                                        fill="#1C274C" />
                                </svg>
                                <span
                                    class="mx-2"><?php echo $_SESSION['webpanel']['employee_fname'] . ' ' . $_SESSION['webpanel']['employee_lname']; ?></span>
                            </button>
                            <ul class="dropdown-menu dropdown-menu-lg-end" aria-labelledby="dropdownMenuButton1">
                                <div class="d-flex flex-column">
                                    <div class="d-flex flex-column justify-content-center align-items-end">
                                        <li class="w-100 drop-profile"><span class="dropdown-item text-start"><?php echo $_SESSION['webpanel']['employee_fname'] . ' ' . $_SESSION['webpanel']['employee_lname']; ?></span></li>
                                        <li class="w-100"><a class="dropdown-item text-start" href="#">Action</a></li>
                                        <li class="w-100"><a class="dropdown-item text-start" href="#">Need Help</a></li>
                                        <li class="w-100"><a class="dropdown-item text-start" href="#">Log Out</a></li>
                                    </div>
                                    <div class="drop-divider my-1"></div>
                                    <div class="w-100 text-center">
                                        <p class="m-0 mt-2">Last Login: (<?php echo $last_login; ?>)</p>
                                    </div>
                                </div>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </nav>

    <!-- Sidebar Section -->
    <nav class="sidebar close" id="toggle-sidebar">
        <header>
            <div class="image-text">
                <div class="text logo-text">
                </div>
            </div>

            <i class='toggle ti-menu' id="toggle-menu"></i>
        </header>

        <div class="menu-bar">
            <div class="menu">

                <ul class="menu-links">
                    <?php if (in_array('ApplicationLogs', $this->RolePermission)) { ?>
                    <li class="nav-link"><a href="<?php echo base_url(); ?>applicationlogs"><i class="ti-book icon"></i>
                            <span class="text nav-text">Application Logs</span></a></li>
                    <?php } ?>
                    <?php if (in_array('BulkUpload', $this->RolePermission)) { ?>
                    <li class="nav-link"><a href="<?php echo base_url(); ?>bulkUpload"><i class="ti-dashboard icon"></i> <span class="text nav-text">Bulk
                                Upload</span></a></li>
                    <?php } ?>
                    <?php if (in_array('cdbalance', $this->RolePermission)) { ?>
                    <li class="nav-link"><a href="<?php echo base_url(); ?>dashboarddetails/cdBalance"><i class="ti-money icon"></i> <span class="text nav-text">CD
                                Balance</span></a></li>
                    <?php } ?>

                    <?php if (in_array('CoverLimit', $this->RolePermission)) { ?>
                    <li class="nav-link"><a href="<?php echo base_url(); ?>dashboarddetails/coverbalance"><i class="ti-money icon"></i>
                            <span class="text nav-text">Cover Limit</span></a></li>
                    <?php } ?>

                    <?php if (in_array('endorsement', $this->RolePermission)) {
                                      //  echo 123;die;
                                        ?>
                    <li class="nav-link"><a href="<?php echo base_url(); ?>endorsement"><i class="ti-money icon"></i>
                            <span class="text nav-text">Endorsement</span></a></li>
                    <?php } ?>
                    <?php if (in_array('Claim', $this->RolePermission)) { ?>
                    <li class="nav-link">
                        <a href="javascript:void(0)" aria-expanded="true"><i
                                class="ti-layout-tab icon"></i><span class="text nav-text">Claim</span></a>
                        <ul class="collapse">
                            <?php if (in_array('LodgeClaims', $this->RolePermission)) { ?>
                            <li class=""><a href="<?php echo base_url(); ?>Lodgeclaim"><span class="text nav-text">Lodge Claims</span></a>
                            </li>
                            <?php } ?>
                            <?php if (in_array('TrackClaims', $this->RolePermission)) { ?>
                            <li class=""><a href="<?php echo base_url(); ?>Trackclaim"><span class="text nav-text">Track Claims</span></a>
                            </li>
                            <?php } ?>
                            <?php if (in_array('RaiseBulkClaims', $this->RolePermission)) { ?>
                            <li class=""><a href="<?php echo base_url(); ?>Raisebulkupload"><span class="text nav-text">Raise Bulk
                                        Claims</span></a></li>
                            <?php } ?>
                            <?php if (in_array('Reports', $this->RolePermission)) { ?>
                            <li class=""><a href="<?php echo base_url(); ?>Reports"><span class="text nav-text">Reports</span></a></li>
                            <?php } ?>
                        </ul>
                    </li>

                    <?php } ?>
                    <?php if (in_array('SmDashboard', $this->RolePermission)) { ?>
                    <li class="nav-link"><a href="<?php echo base_url(); ?>smdashboard"><i class="ti-dashboard icon"></i> <span class="text nav-text">SM
                                Dashboard</span></a></li>
                    <?php } ?>

                    <?php if (in_array('MyProfile', $this->RolePermission)) { ?>
                    <li class="nav-link"><a href="<?php echo base_url(); ?>myprofile/addEdit"><i class="ti-face-smile icon"></i>
                            <span class="text nav-text">Profile</span></a></li>
                    <?php } ?>
                    <?php if (in_array('EnrollmentFormList', $this->RolePermission)) { ?>
                    <li class="nav-link"><a href="<?php echo base_url(); ?>enrollmentforms"><i class="ti-file icon"></i><span class="text nav-text">Enrollment
                                Forms</a></span></li>
                    <?php } ?>
                    <?php if (in_array('SingleJourney', $this->RolePermission)) { ?>
                    <li class="nav-link"><a href="<?php echo base_url(); ?>singlejourney"><i class="ti-file icon"></i><span class="text nav-text">Single
                                Journey</a></span></li>
                    <?php } ?>
                    <?php if (in_array('CommunicationTemplate', $this->RolePermission)) { ?>
                    <li class="nav-link"><a href="<?php echo base_url(); ?>communicationtemplate"><i
                                class="ti-file icon"></i><span class="text nav-text">Communication Template</a></span></li>
                    <?php } ?>
                    <?php if (in_array('PermissionList', $this->RolePermission) || in_array('RoleList', $this->RolePermission) || in_array('UserList', $this->RolePermission)) { ?>

                    <li class="nav-link">
                        <a href="javascript:void(0)" aria-expanded="true"><i
                                class="ti-layout-tab icon"></i><span class="text nav-text">UAC</span></a>
                        <ul class="collapse">
                            <?php if (in_array('PermissionList', $this->RolePermission)) { ?>
                            <li class="active"><a
                                    href="<?php echo base_url(); ?>permission"><span class="text nav-text">Permissions</span></a></li>
                            <?php } ?>
                            <?php if (in_array('RoleList', $this->RolePermission)) { ?>
                            <li><a href="<?php echo base_url(); ?>roles"><span class="text nav-text">Roles</a></span></li>
                            <?php } ?>
                            <?php if (in_array('UserList', $this->RolePermission)) { ?>
                            <li><a href="<?php echo base_url(); ?>users"><span class="text nav-text">Users</span></a></li>
                            <?php } ?>
                        </ul>
                    </li>
                    <?php } ?>


                    <?php if (in_array('CreditorList', $this->RolePermission) || in_array('CreditorBranchList', $this->RolePermission)) { ?>
                    <li class="nav-link">
                        <a href="javascript:void(0)" aria-expanded="true"><i class="ti-user icon"></i><span class="text nav-text">Partner
                                Management</span></a>
                        <ul class="collapse">
                            <?php if (in_array('CreditorList', $this->RolePermission)) { ?>
                            <li class="active"><a href="<?php echo base_url(); ?>creditors"><span class="text nav-text">Partners</span></a>
                            </li>
                            <?php } ?>
                            <?php if (in_array('CreditorBranchList', $this->RolePermission)) { ?>
                            <li class="active"><a href="<?php echo base_url(); ?>creditorbranches"><span class="text nav-text">Partner
                                        Branches</span></a></li>
                            <?php } ?>
                        </ul>
                    </li>
                    <?php } ?>

                    <?php if (in_array('SMCreditorMappingList', $this->RolePermission)) { ?>
                    <li class="nav-link"><a href="<?php echo base_url(); ?>smcreditors"><i class="ti-map icon"></i> <span class="text nav-text">SM Partner
                                Mapping</span></a></li>
                    <?php } ?>

                    <?php if (in_array('DiscrepancyTypeList', $this->RolePermission) || in_array('DiscrepancySubTypeList', $this->RolePermission) || in_array('LocationList', $this->RolePermission) || in_array('AssignmentDeclarationList', $this->RolePermission) || in_array('PaymentWorkFlowList', $this->RolePermission)) { ?>
                    <li class="nav-link">
                        <a href="javascript:void(0)" aria-expanded="true"><i
                                class="ti-harddrives icon"></i><span class="text nav-text">Master's</span></a>
                        <ul class="collapse">
                            <?php if (in_array('PaymentWorkFlowList', $this->RolePermission)) { ?>
                            <li class="active"><a href="<?php echo base_url(); ?>paymentworkflowmaster"><span class="text nav-text">Payment
                                        Workflow Master</span></a></li>
                            <?php } ?>
                            <?php if (in_array('CompanyList', $this->RolePermission)) { ?>
                            <li class="active"><a href="<?php echo base_url(); ?>companymst"><span class="text nav-text">Companies</span></a>
                            </li>
                            <?php } ?>
                            <?php if (in_array('LocationList', $this->RolePermission)) { ?>
                            <li class="active"><a href="<?php echo base_url(); ?>locationmst"><span class="text nav-text">Location</span></a>
                            </li>
                            <?php } ?>
                            <?php if (in_array('DiscrepancyTypeList', $this->RolePermission)) { ?>
                            <li class="active"><a href="<?php echo base_url(); ?>discrepancytype"><span class="text nav-text">Discrepancy
                                        Type</span></a></li>
                            <?php } ?>
                            <?php if (in_array('DiscrepancySubTypeList', $this->RolePermission)) { ?>
                            <li><a href="<?php echo base_url(); ?>discrepancysubtype"><span class="text nav-text">Discrepancy
                                        Subtype</a></span></li>
                            <?php } ?>
                            <?php if (in_array('AssignmentDeclarationList', $this->RolePermission)) { ?>
                            <li><a href="<?php echo base_url(); ?>assignmentdeclaration"><span class="text nav-text">Declaration</a></span>
                            </li>
                            <?php } ?>
                            <?php if (in_array('ThemeConfiguaration', $this->RolePermission)) { ?>
                            <li><a href="<?php echo base_url(); ?>ThemeConfiguaration"><span class="text nav-text">Theme
                                        Configuaration</a></span></li>
                            <?php } ?>
                            <?php if (in_array('Linkuiconfiguration', $this->RolePermission)) { ?>
                            <li><a href="<?php echo base_url(); ?>Linkuiconfiguration"><span class="text nav-text">Link UI
                                        Configuaration</a></span></li>
                            <?php } ?>
                        </ul>
                    </li>
                    <?php } ?>


                    <!-- new added by Jiten -->
                    <?php if (in_array('FamilyConstructList', $this->RolePermission) || in_array('InsurerList', $this->RolePermission) || in_array('PolicySubtypeList', $this->RolePermission) || in_array('SumInsuredList', $this->RolePermission) || in_array('ProductsList', $this->RolePermission) || in_array('FeatureList', $this->RolePermission)  || in_array('BranchIMDList', $this->RolePermission)) { ?>
                    <li class="nav-link mb-3">
                        <a href="javascript:void(0)" aria-expanded="true"><i
                                class="ti-package icon"></i><span class="text nav-text">Products</span></a>
                        <ul class="collapse">
                            <?php if (in_array('BranchIMDList', $this->RolePermission)) { ?>
                            <li class="active"><a href="<?php echo base_url(); ?>branchimd"><span class="text nav-text">Branch IMD
                                        Mapping</span></a></li>
                            <?php } ?>
                            <?php if (in_array('FamilyConstructList', $this->RolePermission)) { ?>
                            <li class="active"><a href="<?php echo base_url(); ?>familyconstruct"><span class="text nav-text">Family
                                        Construct</span></a></li>
                            <?php } ?>
                            <?php if (in_array('InsurerList', $this->RolePermission)) { ?>
                            <li class="active"><a href="<?php echo base_url(); ?>insurer"><span class="text nav-text">Insurer</span></a></li>
                            <?php } ?>
                            <?php if (in_array('PolicySubtypeList', $this->RolePermission)) { ?>
                            <li class="active"><a href="<?php echo base_url(); ?>policysubtype"><span class="text nav-text">Policy
                                        Subtype</span></a></li>
                            <?php } ?>
                            <?php if (in_array('SumInsuredList', $this->RolePermission)) { ?>
                            <li class="active"><a href="<?php echo base_url(); ?>suminsured"><span class="text nav-text">Sum
                                        Insured</span></a></li>
                            <?php } ?>
                            <?php if (in_array('ProductsList', $this->RolePermission)) { ?>
                            <li class="active"><a href="<?php echo base_url(); ?>products"><span class="text nav-text">Products</span></a>
                            </li>
                            <?php } ?>
                            <?php if (in_array('FeatureList', $this->RolePermission)) { ?>
                            <li class="active"><a href="<?php echo base_url(); ?>features"><span class="text nav-text">Config
                                        Feature</span></a></li>

                            <?php } ?>
                        </ul>
                    </li>
                    <?php } ?>
                    <!-- new started -->

                    <!-- Danish-->
                    <?php if (in_array('LeadList', $this->RolePermission)) { ?>
                    <!--<li><a href="<?php echo base_url(); ?>customerleads"><i class="ti-user icon"></i> <span class="text nav-text">Leads</span></a></li>-->
                    <?php } ?>

                    <?php if (in_array('LeadList', $this->RolePermission) || in_array('CustomerProposalsList', $this->RolePermission) || in_array('DiscrepancyProposalList', $this->RolePermission) || in_array('BOProposalList', $this->RolePermission) || in_array('COProposalList', $this->RolePermission) || in_array('UWProposalList', $this->RolePermission)) { ?>
                    <li class="nav-link mb-3">
                        <a href="javascript:void(0)" aria-expanded="true"><i class="ti-user icon"></i>
                            <?php if ($_SESSION['webpanel']['role_id'] == 3) {
                                                    echo '<span class="text nav-text">Sales Portal</span>';
                                                } else if ($_SESSION['webpanel']['role_id'] == 5) {
                                                    echo '<span class="text nav-text">BO Proposals</span>';
                                                } else if ($_SESSION['webpanel']['role_id'] == 6) {
                                                    echo '<span class="text nav-text">All Proposals</span>';
                                                } else if ($_SESSION['webpanel']['role_id'] == 7) {
                                                    echo '<span class="text nav-text">UW Proposals</span>';
                                                } else {
                                                    echo '<span class="text nav-text">Proposals</span>';
                                                } ?>

                        </a>
                        <ul class="collapse">
                            <?php if (in_array('LeadList', $this->RolePermission)) { ?>
                            <li class="active"><a href="<?php echo base_url(); ?>customerleads"><span class="text nav-text">Leads</span></a>
                            </li>
                            <?php } ?>
                            <?php if (in_array('CustomerProposalsList', $this->RolePermission)) { ?>
                            <li class="active"><a href="<?php echo base_url(); ?>customerproposals"><span class="text nav-text">Customer
                                        Proposals</span></a></li>
                            <?php } ?>
                            <?php if (in_array('DiscrepancyProposalList', $this->RolePermission)) { ?>
                            <li class="active"><a href="<?php echo base_url(); ?>discrepancyproposals"><span class="text nav-text">Discrepancy
                                        Proposals</span></a></li>
                            <?php } ?>
                            <?php if (in_array('BOProposalList', $this->RolePermission)) { ?>
                            <li class="active"><a href="<?php echo base_url(); ?>boproposals"><span class="text nav-text">BO
                                        Proposals</span></a></li>
                            <?php } ?>
                            <?php if (in_array('COProposalList', $this->RolePermission)) { ?>
                            <li class="active"><a href="<?php echo base_url(); ?>coproposals"><span class="text nav-text">CO
                                        Proposals</span></a></li>
                            <?php } ?>
                            <?php if (in_array('UWProposalList', $this->RolePermission)) { ?>
                            <li class="active"><a href="<?php echo base_url(); ?>uwproposals"><span class="text nav-text">UW
                                        Proposals</span></a></li>
                            <?php } ?>
                        </ul>
                    </li>
                    <?php } ?>

                    <!-- end-->

                    <!-- <li class="nav-link">
                            <a href="#">
                                <i class="ti-layout-tab icon"></i>
                                <span class="text nav-text">Wallets</span>
                            </a>
                        </li> -->

                </ul>

            </div>
        </div>
    </nav>

    <!-- Loader -->
    <div class="sk-circle-wrapper" id="sk-circle-loader" style="display: none">
        <div class="sk-circle">
        <div class="sk-circle1 sk-child"></div>
        <div class="sk-circle2 sk-child"></div>
        <div class="sk-circle3 sk-child"></div>
        <div class="sk-circle4 sk-child"></div>
        <div class="sk-circle5 sk-child"></div>
        <div class="sk-circle6 sk-child"></div>
        <div class="sk-circle7 sk-child"></div>
        <div class="sk-circle8 sk-child"></div>
        <div class="sk-circle9 sk-child"></div>
        <div class="sk-circle10 sk-child"></div>
        <div class="sk-circle11 sk-child"></div>
        <div class="sk-circle12 sk-child"></div>
        </div>
    </div>

    <script>
    //Implementing Google Translate 
    function googleTranslateElementInit() {
        new google.translate.TranslateElement({
            pageLanguage: 'en'
        }, 'google_translate_element');
    }

    //Implementing Sidebar Toggle
    $('#toggle-menu').on("click", function() {
        $('#toggle-sidebar').toggleClass("close");

        //These code is for toggling menu-bar and close-menu when sidebar is open/closed
        $("#toggle-menu").hasClass("ti-menu") ? $("#toggle-menu").removeClass("ti-menu").addClass("ti-close") :
            $("#toggle-menu").removeClass("ti-close").addClass("ti-menu");
    });
    </script>