<style>
	input {
		background: transparent;
	}

	input.no-autofill-bkg:-webkit-autofill {
		-webkit-background-clip: text;
	}
</style>
<!-- start: Content -->
<div class="col-md-10">
	<div class="content-section mt-3">
		<div class="card">
			<div class="cre-head">
				<div class="row">
					<div class="col-md-10 col-10">
						<p>Company Name - <i class="ti-user"></i></p>
					</div>
					<div class="col-md-2 col-2">
					</div>
				</div>
			</div>
			<div class="card-body">
				<form class="form-horizontal" id="form-validate" method="post" enctype="multipart/form-data">
					<input type="hidden" id="company_id" name="company_id" value="<?php if(!empty($getDetails[0]['company_id'])){echo $getDetails[0]['company_id'];}?>" />
					
					<div class="row">
						<div class="col-md-3 mb-3">
							<label for="validationCustomUsername" class="col-form-label">Company Name<span class="lbl-star">*</span></label>
							<div class="input-group">
								<input id="company_name" name="company_name" type="text" class="form-control no-autofill-bkg" placeholder="Enter Company Name" aria-describedby="inputGroupPrepend" value="<?php if(!empty($getDetails[0]['company_name'])){echo $getDetails[0]['company_name'];}?>" />
								<div class="input-group-prepend">
									<span class="input-group-text" id="inputGroupPrepend"><span class="material-icons">add_task</span></span>
								</div>
							</div>
						</div>
                        <?php
                        //echo $getDetails[0]['isactive'];die;
                        ?>
                        <div class="col-md-3 mb-3">
                            <label for="is_active" class="col-form-label">Status</label>
                            <div class="input-group">
                                <select class="form-control" name="is_active" id="is_active" style="height: calc(2.7rem + 0px);">
                                    <option value="" disabled>Select</option>
                                    <option value="1" <?php if($getDetails[0]['isactive'] == 1){echo "selected";  ?>  <?php }?>>Active</option>
                                    <option value="0" <?php if($getDetails[0]['isactive'] == 0){ echo "selected";  ?>  <?php }?>>In-Active</option>
                                </select>
                                <div class="input-group-prepend">
                                    <span class="input-group-text" id="inputGroupPrepend"><span class="material-icons">toggle_off</span></span>
                                </div>
                            </div>
                        </div>
					</div>

					<div class="row mt-3">
						<div class="col-md-1 col-6 text-left"><button type="submit" class="btn smt-btn btn-primary">Save</button></div>
						<div class="col-md-2 col-6 text-right"><a href="<?php echo base_url();?>companymst"><button type="button" class="btn cnl-btn">Cancel</button></a></div>
					</div>
				</form>	
			</div>
		</div>
	</div>
</div>
<!-- end: Content -->
			
<script type="text/javascript">
jQuery.validator.addMethod("lettersonlys", function(value, element) {
    return this.optional(element) || /^[a-zA-Z ]*$/.test(value);
}, "Letters only please");


$( document ).ready(function() {
});

var vRules = {
	company_name:{required:true, lettersonlys:true}	
};
var vMessages = {
	company_name:{required:"Please enter company."}
};

$("#form-validate").validate({
	rules: vRules,
	messages: vMessages,
	submitHandler: function(form) 
	{
		var act = "<?php echo base_url();?>companymst/submitForm";
		$("#form-validate").ajaxSubmit({
			url: act, 
			type: 'post',
			dataType: 'json',
			cache: false,
			clearForm: false,
			beforeSubmit : function(arr, $form, options){
				//$(".btn-primary").hide();
				//return false;
			},
			success: function (response) 
			{
				//$(".btn-primary").show();
				if(response.success)
				{
					displayMsg("success",response.msg);
					setTimeout(function(){
						window.location = "<?php echo base_url();?>companymst";
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

document.title = "Add/Edit Company";

</script>