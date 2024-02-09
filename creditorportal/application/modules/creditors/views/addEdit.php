<?php //echo "<pre>";print_r($getDetails);exit;?>
<!-- start: Content -->
<style type="text/css">
    .file-error{
        position: unset;
    }
    
    .input-box{
        position: relative;
        top: 4px;
    }

    input {
		background: transparent;
	}

	input.no-autofill-bkg:-webkit-autofill {
		-webkit-background-clip: text;
	}
</style>
<div class="col-md-10">
    <div class="content-section mt-3">
        <div class="card">
            <div class="cre-head">
                <div class="row">
                    <div class="col-md-10 col-10">
                        <p>Add Partners - <i class="ti-user"></i></p>
                    </div>
                    <div class="col-md-2 col-2">
                    </div>
                </div>
            </div>
            <div class="card-body">
                <form class="form-horizontal" id="form-validate" method="post" enctype="multipart/form-data">
                    <input type="hidden" id="creditor_id" name="creditor_id" value="<?php if(!empty($getDetails[0]['creditor_id'])){echo $getDetails[0]['creditor_id'];}?>" />
                    <div class="row">
                        <div class="col-md-3 mb-3">
                            <label for="creaditor_name" class="col-form-label">Partner Name<span class="lbl-star">*</span></label>
                            <div class="input-group">
                                <input class="form-control no-autofill-bkg" placeholder="Enter Partner Name" id="creaditor_name" name="creaditor_name" type="text" value="<?php if(!empty($getDetails[0]['creaditor_name'])){echo $getDetails[0]['creaditor_name'];}?>" aria-describedby="inputGroupPrepend" />
                                <div class="input-group-prepend">
                                    <span class="input-group-text" id="inputGroupPrepend"><span class="material-icons">account_circle</span></span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3 mb-3">
                            <label for="creditor_code" class="col-form-label">Partner Code<span class="lbl-star">*</span></label>
                            <div class="input-group">
                                <input class="form-control no-autofill-bkg" placeholder="Enter Partner Code" id="creditor_code" name="creditor_code" type="text" value="<?php if(!empty($getDetails[0]['creditor_code'])){echo $getDetails[0]['creditor_code'];}?>" aria-describedby="inputGroupPrepend" />
                                <div class="input-group-prepend">
                                    <span class="input-group-text" id="inputGroupPrepend"><span class="material-icons">assignment_ind</span></span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3 mb-3">
                            <label for="ceditor_email" class="col-form-label">Partner Email<span class="lbl-star">*</span></label>
                            <div class="input-group">
                                <input class="form-control no-autofill-bkg" placeholder="Enter Partner Email" id="ceditor_email" name="ceditor_email" type="text" oninput="validateInput2(this)" value="<?php if(!empty($getDetails[0]['ceditor_email'])){echo $getDetails[0]['ceditor_email'];}?>" aria-describedby="inputGroupPrepend" />
                                <div class="input-group-prepend">
                                    <span class="input-group-text" id="inputGroupPrepend"><span class="material-icons">alternate_email</span></span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3 mb-3">
                            <label for="creditor_mobile" class="col-form-label">Partner Mobile<span class="lbl-star">*</span></label>
                            <div class="input-group">
                                         <input class="form-control no-autofill-bkg" placeholder="Enter Partner Mobile" id="creditor_mobile" name="creditor_mobile" maxlength="10" pattern="\d*"  type="tel" value="<?php if(!empty($getDetails[0]['creditor_mobile'])){echo $getDetails[0]['creditor_mobile'];}?>" maxlength="10" aria-describedby="inputGroupPrepend" />
                                <div class="input-group-prepend">
                                    <span class="input-group-text" id="inputGroupPrepend"><span class="material-icons">stay_current_portrait</span></span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3 mb-3">
                            <label for="creditor_phone" class="col-form-label">Partner Phone</label>
                            <div class="input-group">
                                <input class="form-control no-autofill-bkg" placeholder="Enter Partner Phone" id="creditor_phone" maxlength="10" pattern="\d*" name="creditor_phone" type="tel" value="<?php if(!empty($getDetails[0]['creditor_phone'])){echo $getDetails[0]['creditor_phone'];}?>" aria-describedby="inputGroupPrepend" maxlength="10" />
                                <div class="input-group-prepend">
                                    <span class="input-group-text" id="inputGroupPrepend"><span class="material-icons">stay_current_portrait</span></span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3 mb-3">
                            <label for="creditor_pancard" class="col-form-label">PAN<span class="lbl-star">*</span></label>
                            <div class="input-group">
                                <input class="form-control no-autofill-bkg" placeholder="Enter PAN" id="creditor_pancard" name="creditor_pancard" type="text" value="<?php if(!empty($getDetails[0]['creditor_pancard'])){echo $getDetails[0]['creditor_pancard'];}?>" aria-describedby="inputGroupPrepend" maxlength="10"/>
                                <div class="input-group-prepend">
                                    <span class="input-group-text" id="inputGroupPrepend"><span class="material-icons">contact_mail</span></span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3 mb-3">
                            <label for="creditor_gstn" class="col-form-label">GST Number<span class="lbl-star">*</span></label>
                            <div class="input-group">
                                <input class="form-control no-autofill-bkg" placeholder="Enter GST Number" id="creditor_gstn" name="creditor_gstn" type="text" value="<?php if(!empty($getDetails[0]['creditor_gstn'])){echo $getDetails[0]['creditor_gstn'];}?>" aria-describedby="inputGroupPrepend" maxlength="15"/>
                                <div class="input-group-prepend">
                                    <span class="input-group-text" id="inputGroupPrepend"><span class="material-icons">contact_mail</span></span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3 mb-3">
                            <label for="creditor_gstn" class="col-form-label">CD Balance</label>
                            <div class="input-group">
                                <input class="form-control no-autofill-bkg" placeholder="Enter CD Balance" id="cd_balance" name="cd_balance" type="text" value="<?php if(!empty($getDetails[0]['initial_cd'])){echo $getDetails[0]['initial_cd'];}?>" aria-describedby="inputGroupPrepend" />
                                <div class="input-group-prepend">
                                    <span class="input-group-text" id="inputGroupPrepend"><span class="material-icons">contact_mail</span></span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3 mb-3">
                            <label for="isactive" class="col-form-label">CD Balance Threshold</label>
                            <div class="input-group">
                                <select class="form-control" name="threshold_value" id="threshold_value">
                                    <option value="">Select Threshold</option>
                                    <option value="1" <?php if(!empty($getDetails[0]['threshold_value']) && $getDetails[0]['threshold_value'] == '1'){?> selected <?php }?>>Percentage</option>
                                    <option value="2" <?php if(!empty($getDetails[0]['threshold_value']) && $getDetails[0]['threshold_value'] == '2'){?> selected <?php }?>>Amount</option>
                                </select>
                                <div class="input-group-prepend">
                                    <span class="input-group-text" id="inputGroupPrepend"><span class="material-icons">toggle_off</span></span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3 mb-3">
                            <label for="creditor_gstn" class="col-form-label">Threshold</label>
                            <div class="input-group">
                                <input class="form-control no-autofill-bkg" placeholder="Enter Threshold" id="threshold" min="0.1" name="threshold" type="text" value="<?php if(!empty($getDetails[0]['cd_threshold'])){echo $getDetails[0]['cd_threshold'];}?>" aria-describedby="inputGroupPrepend" />
                                <div class="input-group-prepend">
                                    <span class="input-group-text" id="inputGroupPrepend"><span class="material-icons">contact_mail</span></span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3 mb-3">
                            <label for="isactive" class="col-form-label">Status<span class="lbl-star">*</span></label>
                            <div class="input-group">
                                <select class="form-control" name="isactive" id="isactive">
                                    <option value="">Select Status</option>
                                    <option value="1" <?php if(!empty($getDetails[0]['isactive']) && $getDetails[0]['isactive'] == 1){?> selected <?php }?>>Active</option>
                                    <option value="0" <?php if(!empty($getDetails[0]['isactive']) && $getDetails[0]['isactive'] == 0){?> selected <?php }?>>In-Active</option>
                                </select>
                                <div class="input-group-prepend">
                                    <span class="input-group-text" id="inputGroupPrepend"><span class="material-icons">toggle_off</span></span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3 mb-3">
                            <label for="address" class="col-form-label">Address<span class="lbl-star">*</span></label>
                            <div class="input-group">
                                <textarea class="form-control no-autofill-bkg"  placeholder="Enter Address"  name="address" id="address"><?php if(!empty($getDetails[0]['address'])){echo $getDetails[0]['address'];}?></textarea>
                                <div class="input-group-prepend">
                                    <span class="input-group-text" id="inputGroupPrepend"><span class="material-icons">home</span></span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3 mb-3">
                            <label for="creaditor_name" class="col-form-label">Short Code</label>
                            <div class="input-group">
                                <input class="form-control no-autofill-bkg" minlength="2" maxlength="3" onkeydown="return /[a-z]/i.test(event.key)" placeholder="Enter Short Code" id="short_code" name="short_code" type="text" value="<?php if(!empty($getDetails[0]['short_code'])){echo $getDetails[0]['short_code'];}?>" aria-describedby="inputGroupPrepend" />
                                <div class="input-group-prepend">
                                    <span class="input-group-text" id="inputGroupPrepend"><span class="material-icons">account_circle</span></span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="address" class="col-form-label">Term & condition Text<span class="lbl-star">*</span></label>
                            <div class="input-group">
                                <textarea class="form-control no-autofill-bkg" placeholder="Enter Term & condition Text" name="tc_text" id="tc_text"><?php if(!empty($getDetails[0]['term_condition'])){echo $getDetails[0]['term_condition'];}?></textarea>
                                <div class="input-group-prepend">
                                    <span class="input-group-text" id="inputGroupPrepend"><span class="material-icons">contact_mail</span></span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3 mb-3">
                            <label for="creaditor_name" class="col-form-label">Allow negative issuance</label>
                            <?php
                            //allow_negative_issuance
                            $checked='';
                            if(($getDetails[0]['allow_negative_issuance']) == 1){
                                $checked='checked';
                            }
                            ?>
                                <input id="negative_issuance" <?php echo $checked; ?> class="mt-1 input-box" name="negative_issuance" type="checkbox" value="1" aria-describedby="inputGroupPrepend" />

                        </div>
                        <div class="col-md-4 mb-3">
                            <label for="validationCustomUsername" class="col-form-label">Partner Logo<span class="lbl-star">*</span></label>
                            <?php

                            if (!empty($getDetails[0]['creditor_logo'])) {
                                ?>
                                <div class="input-group-prepend1">
                                    <a href="<?= $getDetails[0]['creditor_logo']; ?>" target="_blank" class="p-btn">Preview <i class="ti-eye"></i></a>&nbsp;&nbsp;
                                    <a href="javascript:void(0);" class="file-change f-btn" data-file="creditor_logo">Change <i class="ti-pencil"></i></a>
                                </div>
                                <?php
                            } else {

                                ?>
                                <input type="file" id="creditor_logo" name="creditor_logo" accept="image/jpg, image/jpeg, image/png, application/pdf" />

                                <?php
                            }
                            ?>
                        </div>
                    </div>
                    <div class="row mt-4">
                        <div class="col-md-1 col-6 text-left">
                            <button type="submit" class="btn smt-btn">Save</button>
                        </div>
                        <div class="col-md-2 col-6 text-right">
                            <a href="<?php echo base_url();?>creditors"><button type="button" class="btn cnl-btn">Cancel</button></a>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
<!-- end: Content -->
<script type="text/javascript">

     $("body").on("keyup", "#creditor_mobile,#creditor_phone", function(e) {
                var $th = $(this);
                if (
                    e.keyCode != 46 &&
                    e.keyCode != 8 &&
                    e.keyCode != 37 &&
                    e.keyCode != 38 &&
                    e.keyCode != 39 &&
                    e.keyCode != 40 &&
                    e.keyCode != 32
                ) {
                    $th.val(
                        $th.val().replace(/[^0-9]/g, function(str) {
                            return "";
                        })
                    );
                }
                return;
            });
    $.validator.addMethod("pan", function(value, element)
    {
        return this.optional(element) || /^[A-Z]{5}\d{4}[A-Z]{1}$/.test(value);
    }, "Invalid Pan Number");

    $.validator.addMethod("phone", function(value, element)
    {
        return this.optional(element) || /^[0-9\-\(\)\s]+$/.test(value);
    }, "Invalid Phone Number");

    jQuery.validator.addMethod("lettersonlys", function(value, element) {
        return this.optional(element) || /^[a-zA-Z ]*$/.test(value);
    }, "Letters only please");

    jQuery.validator.addMethod("mob", function(value, element) {
        return this.optional(element) || /^[6-9][0-9]{9}$/.test(value);
    }, "Enter valid 10 digit No. starting with 6 to 9.");
    jQuery.validator.addMethod("gstNumber", function(value, element) {
        return this.optional(element) || /^[0-9]{2}[A-Z]{5}[0-9]{4}[A-Z]{1}[1-9A-Z]{1}Z[0-9A-Z]{1}$/.test(value);
    }, "Enter valid GST Number.");
  

    $("body").on("keyup", "#creditor_pancard,#creditor_gstn", function (e) {
        var $th = $(this);
        $(this).val($(this).val().toUpperCase());

        $th.val(
            $th.val().replace(/[^A-Z0-9 ]/g, function(str) {
                return "";
            })
        );

    });

    $("body").on("keyup", "#threshold,#cd_balance", function () {
      this.value = this.value.replace(/[^0-9.]/g, '').replace(/(\..*?)\..*/g, '$1');
    });
    $('#threshold_value').trigger('change');
    $("body").on("change", "#threshold_value", function () {
        if($(this).val()=='2'){
            $('#threshold').removeAttr('max')
        }if($(this).val()=='1'){
            $('#threshold').attr('max',100)
        }
    });

    var vRules = {
        creaditor_name:{required:true, lettersonlys:true},
        creditor_code:{required:true},
        ceditor_email:{required:true, email:true},
        creditor_mobile:{required:true, mob:true},
        creditor_phone:{ phone:true, minlength:10, maxlength:10},
        creditor_pancard:{required:true, pan:true, minlength:10, maxlength:15},
        creditor_gstn:{required:true,gstNumber:true},
        address:{required:true},
        isactive:{required:true},
        tc_text:{required:true},
        creditor_logo:{required:true}
    };

    var vMessages = {
        creaditor_name:{required:"Please enter name."},
        creditor_code:{required:"Please enter code."},
        ceditor_email:{required:"Please enter email id.", email:"Please enter valid email id."},
        creditor_mobile:{required:"Please enter mobile number."},
        creditor_phone:{required:"Please enter phone number."},
        creditor_pancard:{required:"please enter pancard number."},
        creditor_gstn:{required:"Please enter GST number."},
        address:{required:"Please enter address."},
        isactive:{required:"Please select status."},
        short_code:{required:"Please Enter ShortCode."},
        tc_text:{required:"Please Enter Term and condition text."}
    };

    $("#form-validate").validate({
        rules: vRules,
        messages: vMessages,
        errorPlacement: function(label, element) {
            if (element.attr("type") == "file") {
                if(element.parents().find('a#creditor_logo').length){
                     label.insertAfter(element.parents().find('a#creditor_logo')).addClass('file-error');   
                }else{
                    label.insertAfter(element).addClass('file-error');
                }
               
               element.addClass('file-error');
              label.css('display','block')
            } else{
              label.insertAfter(element);
            }
            
          },
        submitHandler: function(form)
        {
            var act = "<?php echo base_url();?>creditors/submitForm";
            $("#form-validate").ajaxSubmit({
                url: act,
                type: 'post',
                dataType: 'json',
                cache: false,
                clearForm: false,
                beforeSubmit : function(arr, $form, options){
                    $(".btn-primary").hide();
                    //return false;
                },
                success: function (response)
                {
                    $(".btn-primary").show();
                    if(response.success)
                    {
                        displayMsg("success",response.msg);
                        setTimeout(function(){
                            window.location = "<?php echo base_url();?>creditors";
                        },2000);
                    }
                    else
                    {
                        displayMsg("error",response.msg);
                        return false;
                    }
                }
            });
        }
    });

    var prevHtml = [];

    $('body').on('click', '.file-change', function(e) {

        e.preventDefault();
        attr = $(this).attr('data-file');
        prevHtml[attr] = $(this).closest('div').html();
        $(this).closest('div').html(`
		<input type="file" name="${attr}" id="${attr}" accept="image/jpg, image/jpeg, image/png, application/pdf" />
		<a href="javascript:void(0);" name="${attr}" id="${attr}" class="file-cancel" data-key="${attr}" alt="Cancel"><i class="ti-close"></i></a>
	`);
    });

    $('body').on('click', '.file-cancel', function(e) {

        e.preventDefault();
        attr = $(this).attr('data-key');
        $(this).closest('div').html(prevHtml[attr]);
    });

    document.title = "Add/Edit Partner";
</script>

<script>
    function validateInput2(input) {
    var value = input.value;
    // Remove spaces
    value = value.replace(/ /g, '');
    // Split the value into parts by hyphen
    var parts = value.split('-');
    // Reconstruct the value, ensuring only one hyphen between parts
    value = parts[0];
    for (var i = 1; i < parts.length; i++) {
        // If the part before or after the hyphen is a valid email part, keep the hyphen
        if (parts[i-1].includes('@') || parts[i].includes('@')) {
            value += '-' + parts[i];
        } else {
            value += parts[i];
        }
    }
    // Update the input value
    input.value = value;
}
</script>