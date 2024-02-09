<?php

$child_count = 0;
$adult_count = 0;

/*$self_dob = $leaddetails->customer_details[0]->dob;
$self_dob = date('Ymd', strtotime($self_dob));

$current_date = date('Ymd');

$self_age = $current_date - $self_dob;
$self_age = str_replace(substr($self_age, -4), '', $self_age);*/

//$proposal_member_id = json_decode(json_encode($proposal_member_id), true);

if (!empty($leaddetails->plan_details)) {
    foreach ($leaddetails->plan_details as $key => $value) {
        if ($value->policy_sub_type_id == 1) {
            foreach ($leaddetails->plan_details[0]->family_construct as $key => $value) {
                $arr_member_type_id = array(1, 2, 3, 4);

                if (in_array($value->member_type_id, $arr_member_type_id)) {
                    $adult_count++;
                } else {
                    $child_count++;
                }
            }
        }
    }
}

$main_tab_customer_arr = [];

if (isset($leaddetails->customer_details) && !empty($leaddetails->customer_details)) {

    for ($i = 0; $i < count($leaddetails->customer_details); $i++) {

        $main_tab_customer_arr['applicant_id_' . $i] = $leaddetails->customer_details[$i]->customer_id;
        $main_tab_customer_arr['assignment_declartaion'] = $leaddetails->customer_details[$i]->assignment_declaration;
        $main_tab_customer_arr['mode_of_payment'] = $leaddetails->customer_details[$i]->mode_of_payment;
    }
}

$member_count = ['adult_count' => $adult_count, 'child_count' => $child_count];
$insured_member_data['member_count'] = $member_count;

if (isset($leaddetails->member_details)) {

    $insured_member_data['member_details'] = $leaddetails->member_details;
}

$coapplicant_count = $leaddetails->customer_details[0]->coapplicant_no;
//exit;

$tab_disabled = 'disabled';

if (isset($is_only_previewable) && $is_only_previewable) {

    $tab_disabled = '';
}

?>
<style>
    .select2-container-multi .select2-choices .select2-search-field input {
        padding: 0px !important;
        margin: 1px 0 !important;
        font-family: sans-serif;
        font-size: 100%;
        color: #666;
        outline: 0;
        border: 0;
        -webkit-box-shadow: none;
        box-shadow: none;
        background: transparent !important;
    }

    .totalpremium {
        cursor: pointer;
        font-size: 20px;
        line-height: 40px;
        margin-right: 15px;
        color: red;
    }

    .error,
    .moberror {
        color: red;
    }

    .error,
    .moberror_customer,
    .moberror_gender {
        color: red;
    }

    .moberror_customer,
    .moberror_gender {
        position: absolute;
        width: 100%;
        left: 0;
        top: 100%;
    }

    .label-primary {
        padding: 5px;
    }

    .collapse.in {
        display: block;
    }

    .collapse {
        display: none;
        /* padding: 15px; */
    }

    .card .card-header {
        background-color: #e6e6e6;
        padding: 5px;
    }

    .select2-container-multi .select2-choices {
        background-image: none !important;
        border: 0 !important;
    }

    .agefield {
        margin-left: 15px;
        width: 120px;
        float: left;
    }

    /* for jqueryui autocomplete starts here*/
    .ui-autocomplete {
        max-height: 100px;
        overflow-y: auto;
        /* prevent horizontal scrollbar */
        overflow-x: hidden;
        /* add padding to account for vertical scrollbar */
        padding-right: 20px;
    }

    /* IE 6 doesn't support max-height
    * we use height instead, but this forces the menu to always be this tall
    */
    * html .ui-autocomplete {
        height: 100px;
    }

    /* for jqueryui autocomplete ends here*/
</style>
<div class="col-lg-10 mt-2">
    <div class="content-section mt-3">
        <div class="card">
            <div class="cre-head">
                <div class="row">
                    <div class="col-md-10 col-10">
                        <p>Endorsement - <i class="ti-user"></i></p>
                    </div>
                    <div class="col-md-2 col-2">
                    </div>
                </div>
            </div>
            <div class="card-body">
                <div class="row">

                    <div class="col-md-3 mb-3">
                        <label for="validationCustomUsername" class="col-form-label">Partner Name</label>
                        <div class="dataTables_filter input-group inp-frame">
                            <select class="form-control" id = "creditor"">
                                <option value="" selected disabled>Select Partner Name</option>
                                <?php
                                foreach ($creditors as $Key=>$creditorsN){
                                    $selected='';

                                    echo $option='<option '.$selected.' value="'.$creditorsN['creditor_id'].'">'.$creditorsN['creaditor_name'].'</option>';
                                }
                                ?>
                            </select>
                        </div>
                    </div>

                    <div class="col-md-3 mb-3">
                        <label for="validationCustomUsername" class="col-form-label">Plan</label>
                        <div class="dataTables_filter input-group inp-frame">
                            <select class="form-control" id= "plans" onchange="getPlanDataTable(this.value)">
                                <option value="" selected disabled>Select Plan</option>

                            </select>
                        </div>
                    </div>




                </div>
            </div>
        </div>
    </div>
    <div class="content-section mt-3">
        <div class="card">
            <div class="cre-head">
                <div class="row">
                    <div class="col-md-10 col-10">
                        <p> Details - <i class="ti-user"></i></p>
                    </div>
                    <div class="col-md-2 col-2">
                    </div>
                </div>

            </div>
            <div class="card-body">
                <ul class="nav nav-tabs mb-scroll" id="myTab" role="tablist">
                    <li class="nav-item">
                        <a class="nav-link main-tab active mb-link" id="app1-tab" data-toggle="tab" href="#app1" role="tab" aria-controls="app1" aria-selected="true" data_id="">Add </a>
                    </li>
                    <li>

                        <a class="nav-link main-tab assignment-declaration mb-link" id="app2-tab" data_id="" data-toggle="tab" href="#app2" role="tab" aria-controls="app2" aria-selected="false">Correction</a>

                    </li>
                    <li class="nav-item">


                        <a class="nav-link main-tab mb-link payment-tab" id="app3-tab" data_id="" data-toggle="tab" href="#app3" role="tab" aria-controls="app3" aria-selected="false">Remove</a>

                    </li>

                </ul>
                <div class="tab-content mt-3" id="myTabContent">

                <div class="tab-pane fade show active" id="app1" role="tabpanel" aria-labelledby="app1-tab">
                    Add Info
<br>
                    <label class="col-form-label"> Upload File</label>
                    <form class="form-horizontal" id="form-bulkuploadMarine" method="post" enctype="multipart/form-data">
                        <div class="row">

                            <div class="col-md-3 mb-3">
                                <div class="dataTables_filter input-group">
                                    <input id="uploadfileMarine" name="uploadfileMarine" type="file" class=" form-control"  >
                                    <div class="input-group-prepend">
                                        <span class="input-group-text" id="inputGroupPrepend"><span class="material-icons">account_circle</span></span>
                                    </div>
                                </div>
                            </div>
                            <div>
                                <button class="btn smt-btn ulbtn" >Upload</button>
                            </div>
                            <div class="dlbtn">
                                <button class="btn btn-link">
                                    <a href="../assets/marine insurance parameter.xlsx" type="button" class="btn btn-danger btn-lg download-btn" download="" style="background-color:#2cd44a;border:none;">Download Format </a>
                                </button>
                            </div>

                        </div>

                    </form>
                    <hr>

                </div>
                <div class="tab-pane fade" id="app2" role="tabpanel" aria-labelledby="app2-tab">
                    Correction Info
                    <br>
                    <label class="col-form-label"> Upload File</label>
                    <form class="form-horizontal" id="form-uploadCorrection" method="post" enctype="multipart/form-data">
                        <div class="row">
<input type ="hidden" name = "creditor_id" id="creditor_name" value="">
                            <input type ="hidden" id = "plan_name" name = "plan_id" value="">
                            <div class="col-md-3 mb-3">
                                <div class="dataTables_filter input-group">
                                    <input id="uploadfileCorrection" name="uploadfileCorrection" type="file" class=" form-control">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text" id="inputGroupPrepend"><span class="material-icons">account_circle</span></span>
                                    </div>
                                </div>
                            </div>


                            <div>
                                <button class="btn smt-btn ulbtn"  onclick ="uploadFiles();">Upload</button>
                            </div>
                            <div class="dlbtn">
                                <button class="btn btn-link">
                                    <a href="../assets/Download_correction_format.xlsx" type="button"  class="btn btn-danger btn-lg download-btn " data-id ="correction" download="" style="background-color:#2cd44a;border:none;">Download Format </a>
                                </button>
                            </div>

                        </div>

                    </form>
                    <hr>


            </div>
                <div class="tab-pane fade" id="app3" role="tabpanel" aria-labelledby="app3-tab">

                    <label class="col-form-label"> Upload File</label>
                    <form class="form-horizontal" id="form-bulkuploadMarine" method="post" enctype="multipart/form-data">
                        <div class="row">

                            <div class="col-md-3 mb-3">
                                <div class="dataTables_filter input-group">
                                    <input id="uploadfileMarine" name="uploadfileMarine" type="file" class=" form-control"  >
                                    <div class="input-group-prepend">
                                        <span class="input-group-text" id="inputGroupPrepend"><span class="material-icons">account_circle</span></span>
                                    </div>
                                </div>
                            </div>


                            <div>
                                <button class="btn smt-btn ulbtn" >Upload</button>
                            </div>
                            <div class="dlbtn">
                                <button class="btn btn-link">
                                    <a href="../assets/marine insurance parameter.xlsx" type="button" class="btn btn-danger btn-lg download-btn" download="" style="background-color:#2cd44a;border:none;">Download Format </a>
                                </button>
                            </div>

                        </div>

                    </form>
                    <hr>
                                   </div>
        </div>
            </div>
    </div>

</div>
    <div class="content-section mt-3">
        <div class="card">
            <div class="cre-head">
                <div class="row">
                    <div class="col-md-8 col-6">
                        <p>Endorsement Details</p>
                    </div>
                    <div class="col-md-4 col-6">

                    </div>
                </div>
            </div>
            <div class="card-body">
                <div class="col-md-12 table-responsive scroll-table" style="height:300px;">
                    <table data-search="false" class=" table table-bordered non-bootstrap display pt-3 pb-3" id="table_lists">
                        <thead class="tbl-cre">
                        <tr>
                            <th data-bSortable="false">Partner Name</th>
                            <th data-bSortable="false">Plan Name</th>
                            <th data-bSortable="false">Trace Id</th>
                            <th data-bSortable="false">Customer Name</th>
                            <th data-bSortable="false">Endorsement Type</th>
                            <th data-bSortable="false">Uploaded Status</th>
                            <th data-bSortable="false">Original File</th>
                            <th data-bSortable="false">Error Document</th>
                            <th data-bSortable="false">Uploaded At</th>
                            <th data-bSortable="false">Status</th>
                        </tr>
                        </thead>
                        <tbody>

                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>


<!-- end here -->
<script>
    $(document).on("change","#creditor",function(){
        var creditor_id = $(this).val();
        $.ajax({
            url: "/features/fetch_plans",
            type: "POST",
            async: false,
            data: {"creditor_id":creditor_id},
            dataType: "json",
            success: function(response) {
                // debugger;
                console.log(response);
                $("#plans").html('').append(response.html);

            }
        });
    });


    $("#form-uploadCorrection").validate({
        submitHandler: function(form) {
            var creditor_id = $('#creditor').val();
            $('#creditor_name').val(creditor_id);

            var plan_id = $('#plans').val();
            $('#plan_name').val(plan_id);

            var act = "<?php echo base_url(); ?>endorsement/correctionbulk";
            $("#form-uploadCorrection").ajaxSubmit({
                url: act,
                type: 'post',
                dataType: 'json',
                cache: false,
                clearForm: false,
                success: function(response) {
                    if (response.success) {
                        displayMsg("success", response.msg);
                    } else {
                        displayMsg("error", response.msg);
                        return false;
                    }
                }
            });
        }
    })
    function getPlanDataTable(value) {
        /*let formData = new FormData();
        formData.set("company_name", company_name);
        formData.set("branch_id", branch_id);*/


        $.ajax({
            url: "/endorsement/fetchendorsement",
            type: "POST",
            data: {
                'plan_id': value,
                'cid': $('#creditor').val(),

            },
            dataType: 'json',
            success: function(res) {

                $("#table_lists").DataTable({

                    destroy: true,
                    order: [],
                    data:res.aaData,
                    "pagingType": "simple_numbers",

                    columns:[

                        {data: 0},
                        {data: 1},
                        {data: 2},
                        {data: 3},
                        {data: 4},
                        {data: 5},
                        {data: 6},
                        {data: 7},
                        {data: 8},
                        {data: 9},

                    ]
                });

            }
        });
    }


   function myfileDownload(e){
debugger;
var path = e.dataset['target'];
                    var link=document.createElement('a');
                    //i used the base url to target a file on mi proyect folder
                    link.href=window.URL = path;
                    //download the name with a different name given in the php method
                    link.download="original_file.xlsx";
                    link.click();




    }










  //----------------------------------------------------

    var panForm = '';

    function enableNextAccordion(id) {

        elem = $(id).closest('.according').nextAll('.according:first');
        elem.find('a.no-collapsable').removeClass('no-collapsable');
        elem.find('.accord-data').collapse('show');
    }

    function rejectProposal(id) {
        var r = confirm("Are you sure you want to reject this proposal?");
        if (r == true) {
            $.ajax({
                url: "<?php echo base_url() ?>/boproposals/rejectProposal/" + id,
                async: false,
                type: "POST",
                success: function(data2) {
                    data2 = $.trim(data2);
                    if (data2 == "1") {
                        displayMsg("success", "Record has been Rejected!");
                        setTimeout("location.reload(true);", 1000);

                    } else {
                        displayMsg("error", "Oops something went wrong!");
                        setTimeout("location.reload(true);", 1000);
                    }
                }
            });
        }
    }

    function acceptProposal(id) {
        var r = confirm("Are you sure you want to accept this record?");
        if (r == true) {
            $.ajax({
                url: "<?php echo base_url(); ?>/boproposals/acceptProposal/" + id,
                async: false,
                type: "POST",
                dataType: 'json',
                success: function(response) {
                    //console.log(response);
                    //console.log(response['success']);
                    //return false;
                    //data2 = $.trim(data2);
                    if (response['success'] == "1") {
                        displayMsg("success", "Record has been Accepted!");
                        //setTimeout("location.reload(true);",1000);
                        passLeadsToInsurance(id);

                        setTimeout("location.reload(true);", 5000);

                    } else if (response['success'] == "3") {
                        displayMsg("success", "Record has been moved to UW approval!");
                        setTimeout("location.reload(true);", 2000);
                    } else {
                        displayMsg("error", "Oops something went wrong!");
                        setTimeout("location.reload(true);", 1000);
                    }
                }
            });
        }
    }

    function passLeadsToInsurance(lead_id) {
        //alert(lead_id);return false;
        //check array not empty.
        $.ajax({
            url: "<?php echo base_url(); ?>boproposals/passLeadsToInsurance",
            data: {
                lead_id: lead_id
            },
            type: 'post',
            dataType: 'json',
            success: function(res) {

            }
        });

    }



    $('body').on('submit', '#pan-form', function() {

        lead_id = $('input[name="lead_id"]').val();
        formArr = $(this).serializeArray();
        formAction = $(this).attr('action');
        customer_pan_data = {};
        $('.age-diff-modal .msg').removeClass('text-danger').text('');

        for (var i = 0; i < formArr.length; i++) {

            name = formArr[i]['name'];
            customer_id = $('input[name="' + name + '"]').attr('data-customer-id');
            pan = formArr[i]['value'];

            if ($.trim(pan) != '') {

                if (!(/^[A-Z]{5}\d{4}[A-Z]{1}$/.test(pan))) {

                    $('.' + name + '.msg').addClass('text-danger').text('Invalid PAN');
                    customer_pan_data = {};
                    break;
                }

                customer_pan_data[i] = customer_id + ':' + pan;
            }
        }

        if (Object.keys(customer_pan_data).length) {

            $.ajax({

                url: formAction,
                data: {
                    'customer_data': JSON.stringify(customer_pan_data),
                    'lead_id': lead_id
                },
                dataType: 'JSON',
                method: 'post',
                async: false,
                beforeSend: function() {

                    $('.submit-btn-pan').attr('disabled', true);
                },
                success: function(response) {

                    $('.age-diff-modal').modal('hide');
                    $('.submit-btn-pan').attr('disabled', false);
                    if (response.success) {

                        $('.pan_added').val('y');
                        displayMsg('success', 'PAN added in records');

                    } else {

                        displayMsg('error', 'Something went wrong. Please try again');
                    }
                }
            });
        }

        return false;
    });

    /*function submitPan(formObj) {

        /*proposer_pan = $('input[name="proposer_pan"]').val();
        customer_id = $('input[name="customer_id"]').val();
        lead_id = $('input[name="lead_id"]').val();

        if ($.trim(proposer_pan) != '') {

            if(!(/^[A-Z]{5}\d{4}[A-Z]{1}$/.test(proposer_pan))){

                $('.age-diff-modal .msg').removeClass('text-success').addClass('text-danger').text('Invalid PAN');
                return false;
            }

            $.ajax({

                url: formObj.action,
                data: {
                    'proposer_pan': proposer_pan,
                    'customer_id': customer_id,
                    'lead_id': lead_id
                },
                dataType: 'JSON',
                method: 'post',
                async: false,
                beforeSend: function(){

                    $('.submit-btn-pan').attr('disabled', true);
                    $('.age-diff-modal .msg').removeClass('text-danger').text('');
                },
                success: function(response) {

                    if (response.success) {

                        $('.age-diff-modal .msg').removeClass('text-danger').addClass('text-success').text(response.msg);
                        $('.pan_added').val('y');
                        setTimeout(function() {
                            $('.age-diff-modal').modal('hide');
                        }, 3000);
                    } else {

                        $('.age-diff-modal .msg').removeClass('text-success').addClass('text-danger').text(response.msg);
                    }
                }
            });

            $('.submit-btn-pan').attr('disabled', false);
        } else {

            $('.age-diff-modal .msg').removeClass('text-success').addClass('text-danger').text('Field Required')
        }
    }*/

    function fillTraceId() {
        $("#unique_trace_id").text($("[name='trace_id']").val());
    }
    $("#mode_of_payment2").click(function() {
        var val = $(this).val();
        if (val == 2) {
            $("#chequedetails").show();
        } else {
            $("#chequedetails").hide();
        }
    });

    $("#mode_of_payment1").click(function() {
        $("#chequedetails").hide();
    });
    $("#mode_of_payment3").click(function() {
        $("#chequedetails").hide();
    });

    $.validator.addMethod('regex', function(value, element, param) {
        return this.optional(element) || /^[6789]\d{9}$/.test(value);
    });

    var vRules = {
        address_line1: {
            required: true,
            minlength: 10
        },
        city: {
            required: true
        },
        email_id: {
            required: true
        },
        salutation: {
            required: true
        },
        firstname: {
            required: true,
            firstnamelettersonly: true
        },
        middlename: {
            firstnamelettersonly: true
        },
        lastname: {
            required: true,
            lastnamevalidate: true
        },
        dob: {
            required: true
        },
        mob_no: {
            required: true,
            regex: true
        },
        state: {
            required: true
        },
        pin_code: {
            required: true,
            number: true,
            minlength: 6,
            maxlength: 6
        }
    };

    var vMessages = {
        address_line1: {
            required: "Address is required",
            minlength: "Address should be at least 10 character"
        },
        city: {
            required: "City is required"
        },
        firstname: {
            required: "This field is required"
        },
        lastname: {
            required: "This field is required"
        },
        dob: {
            required: "This field is required"
        },
        mob_no: {
            required: "This field is required",
            regex: "Please enter valid phone number"
        },
        state: {
            required: "State is required"
        },
        pin_code: {
            required: "Pincode is required",
            number: "Pincode should be numeric and 6 digit",
            minlength: "Pincode should be numeric and 6 digit"
        }
    };

    $(document).on('change', function(e) {
        if (e.target.classList.contains('quote_generation_fields')) {
            generateQuote(e.target.closest('form').id);
        }
    });

    function generateQuote(form_id) {
        let mapping = {
            'family_members_ac_count': "family_members_ac_count",
            'ghi_cover': "sum_insured1",
            'pa_cover': "sum_insured2",
            'ci_cover': "sum_insured3",
            'hospi_cash': "sum_insured6",
            'spouse_age': "spouse_age",
            'tenure': "tenure",
            'super_top_up_cover': "sum_insured5_1",
            'deductable': "deductable",
            'numbers_of_ci': 'numbers_of_ci'
        };

        let requestData = {
            plan_id: $("#" + form_id + " [name='plan_id']").val(),
            lead_id: $("#" + form_id + " [name='lead_id']").val(),
            trace_id: $("#" + form_id + " [name='trace_id']").val(),
            customer_id: $("#" + form_id + " [name='customer_id']").val(),
        };

        $spouseDob = $("#" + form_id + " [name='spouse_dob']");

        if (!$spouseDob.prop('disabled')) {
            requestData.spouse_age = $("#" + form_id + " [name='spouse_age']").val();
            requestData.spouse_dob = $("#" + form_id + " [name='spouse_dob']").val();
        }

        let $current_generate_quote_acc = $("#" + form_id).parent();

        if (!requestData.customer_id && $current_generate_quote_acc.hasClass('show')) {
            displayMsg('error', 'Please fill customer details first');
            return;
        }

        $('#' + form_id + ' .quote_generation_fields').each(function(i, obj) {
            let current_id = obj.id;
            if (Object.values(mapping).indexOf(current_id) > -1) {
                let key = Object.keys(mapping).find(key => mapping[key] === current_id);
                requestData[key] = obj.value;
            }
        });

        var quote_action_url = "<?php echo base_url(); ?>policyproposal/generateQuote";

        $.ajax({
            url: quote_action_url,
            data: requestData,
            type: 'post',
            dataType: 'json',
            cache: false,
            clearForm: false,
            success: function(response) {
                //if (response.success) {
                let data = response.data;
                populateQuoteData(data);
                //}
            }
        });
    }

    function populateQuoteData(data) {
        let applicant_header_html = "";
        let net_premium = 0;
        for (let applicant_key in data) {

            if (applicant_key !== "net_premium") {

                let policy_count = 0;

                if (data[applicant_key]['policies']) {
                    policy_count = Object.keys(data[applicant_key]['policies']).length;
                }

                if (!policy_count) {
                    continue;
                }

                applicant_header_html += `
							<div class="head-lbl-2 mt-1" id="${applicant_key}_premium">
								<p class="head-lbl-1">${applicant_key}</p>
							</div>`;
            } else if (applicant_key == "net_premium") {
                net_premium = data["net_premium"];
            }
        }
        $('#total_premium').html(net_premium);
        //app_coapp_premium = net_premium
        $('#premium_calculations_data .head-lbl-2').remove();

        $("#premium_calculations_data").append(applicant_header_html);

        for (let applicant_key in data) {

            let policy_data = data[applicant_key];

            let policies = policy_data.policies;

            for (let policy_name in policies) {
                if (policies.hasOwnProperty(policy_name)) {

                    $("#" + applicant_key + "_premium").append(
                        `<p>${policy_name}<span class="fl-right"><i class="fa fa-inr"></i> ${policies[policy_name]}</span></p>`
                    );
                }
            }
        }
    }

    var applicant_validation_object = {
        rules: vRules,
        messages: vMessages,
        submitHandler: function(form) {
            var act = "<?php echo base_url(); ?>policyproposal/submitForm";
            $("#cust_data").ajaxSubmit({
                url: act,
                type: 'post',
                dataType: 'json',
                cache: false,
                clearForm: false,
                beforeSubmit: function(arr, $form, options) {
                    debugger;
                    var mob = $("#cust_data" + coapplicant_tab_id + " #mobile_no2").val();
                    var gender = $("#cust_data" + coapplicant_tab_id + " select[name='gender1']").find('option:selected').val();
                    if (mob != '') {
                        var filter = /^[6789]\d{9}$/;
                        if (filter.test(mob)) {
                            $(".moberror_customer").html("").css('display', 'none');
                        } else {
                            $(".moberror_customer").html("Please enter valid phone number").removeAttr('style');
                            return false;
                        }
                    }

                    if (gender == '') {

                        $(".moberror_gender").html("Please select gender").removeAttr('style');
                        return false;
                    } else {

                        $(".moberror_gender").html("").css('display', 'none');
                    }
                    $(".btn-primary").hide();
                    //return false;
                },
                success: function(response) {
                    $(".btn-primary").show();
                    if (response.success) {

                        $("#self_age_" + coapplicant_tab_id).val(response.self_age);
                        displayMsg("success", response.msg);
                        enableNextAccordion("#cust_data");

                    } else {
                        displayMsg("error", response.msg);
                        return false;
                    }
                }
            });

        }
    }

    $("#cust_data").validate(applicant_validation_object);
    document.title = "Add/Edit Policy Proposal";


    /* for bank details form **/
    /*var finalRules = {
        //mode_of_payment:{required:true}
    };

    var finalMessages = {
        //mode_of_payment:{required:"Field is required"}
    };

    $("#finalform").validate({
        rules: finalRules,
        messages: finalMessages,
        submitHandler: function(form) {
            var act = "<?php echo base_url(); ?>policyproposal/submitfinalForm";
			var form = "#finalform";

			/*
		 if($("#mode_of_payment2").is(':checked')) {
            alert("Allot Thai Gayo Bhai");
          }
		***/

    /*if ($('input[name="mode_of_payment"]').is(':checked') && ($('input[name="mode_of_payment"]').val() == 'Cheque' || $('input[name="mode_of_payment"]').val() == 'NEFT')) {
                var cheque_date = $("#cheque_date").val();
                var cheque_number = $("#cheque_number").val();
                var account_number = $("#account_number").val();
                var ifsc_code = $("#ifsc_code").val();
                var bank_city = $("#bank_city").val();
                var bank_branch = $("#bank_branch").val();
                var bank_name = $("#bank_name").val();
                var error = 0;
                if (cheque_date == '') {
                    $(".cheque_date_error").html("*This field is required");
                    error = 1;
                } else {
                    $(".cheque_date_error").html("");
                }
                if (account_number == '') {
                    $(".account_number_error").html("*This field is required");
                    error = 1;
                } else {
                    $(".account_number_error").html("");
                }
                if (cheque_number == '') {
                    $(".cheque_number_error").html("*This field is required");
                    error = 1;
                } else {
                    $(".cheque_number_error").html("");
                }
                if (ifsc_code == '') {
                    $(".ifsc_code_error").html("*This field is required");
                    error = 1;
                } else {
                    $(".ifsc_code_error").html("");
                }
                if (bank_city == '') {
                    $(".bank_city_error").html("*This field is required");
                    error = 1;
                } else {
                    $(".bank_city_error").html("");
                }
                if (bank_branch == '') {
                    $(".bank_branch_error").html("*This field is required");
                    error = 1;
                } else {
                    $(".bank_branch_error").html("");
                }
                if (bank_name == '') {
                    $(".bank_name_error").html("*This field is required");
                    error = 1;
                } else {
                    $(".bank_name_error").html("");
                }
                if (error == 1) {
                    return false;
                }
            }

            $("#finalform").ajaxSubmit({
                url: act,
                type: 'post',
                dataType: 'json',
                cache: false,
                clearForm: false,
                beforeSubmit: function(arr, $form, options) {

                    // /$(".btn").hide();
                    //return false;
                },
                success: function(response) {

                    // /$(".btn-primary").show();
                    if (response.success) {
                        displayMsg("success", response.msg);
                        //$('#collapseTwo').addClass('in');
                        //$('#collapseOne').removeClass('in');

                    } else {
                        displayMsg("error", response.msg);
                        return false;
                    }
                }
            });
        }
    });*/

    $('#id_document_type').on('change', function() {

        if ($('option:selected', this).attr('value') != '') {

            $('.file-type').removeClass('d-none');
        } else {

            $('.file-type').addClass('d-none');
        }
    });

    var cache = {};
    var bankDetails = {};

    $('#ifsc_code').autocomplete({

        minLength: 5,
        delay: 100,
        source: function(request, response) {

            var term = request.term;
            if (term in cache) {

                response(cache[term]);
                return;
            }

            $.ajax({

                url: '<?php echo base_url(); ?>policyproposal/getBankDetails',
                method: 'POST',
                data: request,
                dataType: 'JSON',
                async: false,
                success: function(data) {

                    if (data.success) {

                        message = data.msg;
                        response($.map(message, function(item) {

                            obj = {
                                label: item.ifsc_code,
                                value: item.ifsc_code
                            }

                            bankDetails[item.ifsc_code] = item;
                            cache[term] = obj;
                            return obj;
                        }));
                    }
                }
            });
        },
        select: function(event, ui) {

            $('#bank_name').val(bankDetails[ui.item.value]['bank_name']);
            $('#bank_branch').val(bankDetails[ui.item.value]['branch']);
            $('#bank_city').val();
        }
    });

    var prevHtml = [];

    $('body').on('click', '.file-change', function(e) {

        e.preventDefault();
        attr = $(this).attr('data-file');
        prevHtml[attr] = $(this).closest('div').html();
        $(this).closest('div').html(`
			<input type="file" name="${attr}" id="${attr}" accept="image/jpg, image/jpeg, image/png, application/pdf" />
			<a href="javascript:void(0);" class="file-cancel" data-key="${attr}" alt="Cancel"><i class="ti-close"></i></a>
			<span class="error ${attr}_error"></span>
		`);
    });

    $('body').on('click', '.file-cancel', function(e) {

        e.preventDefault();
        attr = $(this).attr('data-key');
        $(this).closest('div').html(prevHtml[attr]);
    });

    $("#finalform").on('submit', function() {

        var error = 0;
        if ($('input[name="mode_of_payment"]').is(':checked')) {

            if (($('input[name="mode_of_payment"]:checked').val() == 2)) {

                var cheque_date = $("#cheque_date").val();
                var cheque_number = $("#cheque_number").val();
                var account_number = $("#account_number").val();
                var ifsc_code = $("#ifsc_code").val();
                var bank_city = $("#bank_city").val();
                var bank_branch = $("#bank_branch").val();
                var bank_name = $("#bank_name").val();
                //var error = 0;
                if (cheque_date == '') {
                    $(".cheque_date_error").html("*This field is required");
                    error = 1;
                } else {
                    $(".cheque_date_error").html("");
                }
                if (account_number == '') {
                    $(".account_number_error").html("*This field is required");
                    error = 1;
                } else {
                    $(".account_number_error").html("");
                }
                if (cheque_number == '') {
                    $(".cheque_number_error").html("*This field is required");
                    error = 1;
                } else {
                    $(".cheque_number_error").html("");
                }
                if (ifsc_code == '') {
                    $(".ifsc_code_error").html("*This field is required");
                    error = 1;
                } else {
                    $(".ifsc_code_error").html("");
                }
                if (bank_city == '') {
                    $(".bank_city_error").html("*This field is required");
                    error = 1;
                } else {
                    $(".bank_city_error").html("");
                }
                if (bank_branch == '') {
                    $(".bank_branch_error").html("*This field is required");
                    error = 1;
                } else {
                    $(".bank_branch_error").html("");
                }
                if (bank_name == '') {
                    $(".bank_name_error").html("*This field is required");
                    error = 1;
                } else {
                    $(".bank_name_error").html("");
                }

                if ($('#enrollment_form').length) {

                    if ($('#enrollment_form').val() == "") {

                        $('.enrollment_form_error').html("*This field is required");
                        error = 1;
                    } else {

                        $('.enrollment_form_error').html("");
                        error = 0;
                    }
                }

                if ($('#cheque_copy').length) {

                    if ($('#cheque_copy').val() == "") {

                        $('.cheque_copy_error').html("*This field is required");
                        error = 1;
                    } else {
                        $('.cheque_copy_error').html("");
                        error = 0;
                    }
                }
            }

            if (!error) {

                form = $(this)[0];
                formData = new FormData(form);

                $.ajax({

                    url: "<?php echo base_url(); ?>policyproposal/submitfinalForm",
                    type: 'post',
                    dataType: 'json',
                    data: formData,
                    processData: false,
                    contentType: false,
                    beforeSend: function() {

                        //$(".btn").hide();
                        //return false;
                        if ($('.pan_added').val() == '') {

                            $('.age-diff-modal .modal-title').text('Proposer PAN Required');
                            /*$('.age-diff-modal .modal-body')
                            .html('<form onsubmit="submitPan(this);return false;" action="<?php echo base_url(); ?>policyproposal/capturecustomerpan"><input type="text" name="proposer_pan">&nbsp;&nbsp;<span class="msg">&nbsp;&nbsp;</span><button type="submit" class="submit-btn-pan btn smt-btn">Save</button></form>');*/
                            $('.age-diff-modal .modal-body').html(panForm);
                            $('.age-diff-modal .modal-footer').addClass('d-none');
                            $('.age-diff-modal').modal('show');

                            return false;
                        }
                    },
                    success: function(response) {

                        //$(".btn-primary").show();
                        /*if (response.success) {
                            displayMsg("success", response.msg);
                            //$('#collapseTwo').addClass('in');
                            //$('#collapseOne').removeClass('in');

                        } else {
                            displayMsg("error", response.msg);
                            return false;
                        }*/

                        if (response.success) {
                            displayMsg("success", response.msg);
                            setTimeout(function() {

                                var url = new URL(window.location.href);
                                var text = url.searchParams.get("text");
                                window.location.href = "<?php echo base_url(); ?>policyproposal/proposalSummary/?text=" + text;
                            }, 3000);
                        } else {
                            displayMsg("error", response.msg);
                            return false;
                        }
                    }
                });
            }
        } else {

            displayMsg("error", "Payment mode is required");
        }

        return false;
    });
</script>

<script>
    // forNominee data

    /*
    $(".nominee_dob").datepicker({
            changeMonth: true,
            changeYear: true,
            minDate: new Date() });
    ***/


    $(".nominee_dob").datepicker({
        changeMonth: true,
        changeYear: true,
        maxDate: new Date()
    });

    $("#cheque_date").datepicker({
        changeMonth: true,
        changeYear: true,
        dateFormat: 'dd-mm-yy',
    });

    $(document).ready(function() {

        $("#cheque_date").datepicker('setDate', "<?php if (isset($leaddetails->proposal_details[0]->cheque_date) && !empty($leaddetails->proposal_details)) {
            echo date('d-m-Y', strtotime($leaddetails->proposal_details[0]->cheque_date));
        } ?>");

    });
</script>

<script>
    $("li").each(function() {
        var $thistab = $(this);
        var id = $(this).attr("data_id");

        if (!$(this).hasClass('disabled')) {

            $thistab.click(function() {
                let coapplicant_tab_id = $(this).attr("data_id") - 1;
                let lead_id = '<?php echo $leaddetails->customer_details[0]->lead_id; ?>';

                // Dont reload views if there is already existing html;
                if ($("#app" + id).html() == "") {

                    var load_url = "<?php echo base_url(); ?>policyproposal/addPolicyProposalView/" + lead_id +
                        "/?coapplicant_tab_id=" + coapplicant_tab_id;

                    <?php if (isset($is_only_previewable) && $is_only_previewable) : ?>
                    load_url += "&is_only_previewable=true";
                    <?php endif; ?>
                    $("#app" + id).load(load_url,
                        function(response, statusTxt, xhr) {

                            /*$('#leadform' + coapplicant_tab_id + ' #family_members_ac_count').trigger('change', {
                                page_load: true
                            });*/
                            cust_id = $('#cust_data' + coapplicant_tab_id + ' .customer_id_hidden' + coapplicant_tab_id).val();
                            $('body #accordion480' + coapplicant_tab_id + ' input[name="customer_id"]').val(cust_id);
                            loadGHDDeclaration(coapplicant_tab_id);

                            /*if (!$('body #accordion480' + coapplicant_tab_id + ' .nav-link').attr('data-member')) {

                                $('body #accordion480' + coapplicant_tab_id + ' .nav-link').attr('data-member', 0)
                                $('#accordion480' + coapplicant_tab_id + ' form')[0].reset();
                                if ($('#cust_data' + coapplicant_tab_id + ' .customer_id_hidden' + coapplicant_tab_id).val() != '') {

                                    data = {};
                                    data.lead_id = lead_id,
                                        data.customer_id = $('#cust_data' + coapplicant_tab_id + ' .customer_id_hidden' + coapplicant_tab_id).val();

                                    $.ajax({

                                        url: '<?php echo base_url(); ?>policyproposal/getMemberID',
										method: 'post',
										data: data,
										dataType: 'JSON',
										cache: false,
										async: false,
										success: function(response) {

											if (response.success) {

												if (response.data) {
													$(response.data).each(function(i, value) {

														if (value.relation_with_proposal == 1) {

															$('body #accordion480' + coapplicant_tab_id + ' #self-tab').attr('data-member', value.member_id).removeClass('disabled');
														} else if (value.relation_with_proposal == 2) {

															$('body #accordion480' + coapplicant_tab_id + ' #spouse-tab').attr('data-member', value.member_id).removeClass('disabled');
														} else if (value.relation_with_proposal == 5) {

															$('body #accordion480' + coapplicant_tab_id + ' #kid1-tab').attr('data-member', value.member_id).removeClass('disabled');
														} else if (value.relation_with_proposal == 6) {

															$('body #accordion480' + coapplicant_tab_id + ' #kid2-tab').attr('data-member', value.member_id).removeClass('disabled');
														}
													});
												}
											}
										}
									});
								}
							}

							$('body #accordion480' + coapplicant_tab_id + ' .nav-link:first').trigger('click');*/
                        });
                }

                // $("#app" + id).load("<?php echo base_url(); ?>policyproposal/addPolicyProposalView/" + lead_id +
                // 	"/?coapplicant_tab_id=" + coapplicant_tab_id);
            });
        }
    });
</script>

<script>
    /* for bank details form **/
    var vRules_lead_data = {
        //mode_of_payment:{required:true}
    };

    var vMessages_lead_data = {
        //mode_of_payment:{required:"Field is required"}
    };

    $(document).change(function(e) {
        let name = e.target.name;
        if (name == "pin_code") {

            let pincode = e.target.value;

            var state_city_url = "<?php echo base_url(); ?>policyproposal/getStateCity";

            let closest_form_id = e.target.closest("form").id;

            $.ajax({
                url: state_city_url,
                data: {
                    pincode: pincode
                },
                type: 'post',
                dataType: 'json',
                cache: false,
                clearForm: false,
                success: function(response) {
                    if (response.success) {
                        let data = response.data;
                        $("#" + closest_form_id + " [name='city']").val(data.CITY);
                        $("#" + closest_form_id + " [name='state']").val(data.STATE);
                    } else {
                        displayMsg("error", "Please enter correct pincode");
                        $("#" + closest_form_id + " [name='city']").val("");
                        $("#" + closest_form_id + " [name='state']").val("");
                    }
                }
            });
        }
    });

    $('body').on('click', 'input[name="mode_of_payment"]', function() {

        if ($('input[name="mode_of_payment"]:checked').val() == 2) {

            $('#chequedetails').show();
            $('.go-green-p').addClass('d-none');

        } else {

            $('#chequedetails').hide();
            $('.go-green-p').removeClass('d-none');
        }
    });

    $('.save-assignment-declaration').on('click', function() {

        data = {};
        data.value = $('input[name="assignment_declaration"]:checked').val();
        data.lead_id = "<?php echo $leaddetails->customer_details[0]->lead_id; ?>";

        $.ajax({

            url: "<?php echo base_url(); ?>policyproposal/saveAssignmentDeclaration",
            method: "POST",
            data: data,
            dataType: 'JSON',
            cache: false,
            async: false,
            success: function(response) {

                if (response.success) {

                    if (data.value == 'Agree') {

                        displayMsg("success", response.msg);

                        if (!$('.payment-tab').hasClass('active')) {

                            $('#' + current_main_tab_id).closest('.nav-item').nextAll('.nav-item:first').children('.main-tab.disabled').removeClass('disabled').trigger('click');
                        }

                        current_main_tab_id = $('.main-tab.active').attr('id');
                        $([document.documentElement, document.body]).animate({

                            scrollTop: $("#" + current_main_tab_id).offset().top
                        }, 800);
                        $('#app4-tab').trigger("click");
                    } else {

                        displayMsg("error", "Please agree to the terms to proceed");
                    }
                } else {

                    displayMsg("error", "Something went wrong");
                }
            }
        });

        return false;
    });
    $('#bank_city').keypress(function(e) {
        var regex = new RegExp(/^[a-zA-Z\s]+$/);
        var str = String.fromCharCode(!e.charCode ? e.which : e.charCode);
        if (regex.test(str)) {
            return true;
        } else {
            e.preventDefault();
            return false;
        }
    });
</script>