<?php //echo "<pre>";print_r($roles);exit;?>
<!-- Center Section -->
<div class="col-md-10">
	<div class="content-section mt-3">
		<div class="card">
			<div class="cre-head">
				<div class="row">
					<div class="col-md-12 col-12">
						<p>Sales Admin Dashboard - <i class="ti-user"></i></p>
					</div>
					<!-- <div class="col-md-2 col-2">
					</div> -->
				</div>
			</div>
			<div class="card-body">
			<!--<button class="btn" id="btn_filter_showhide" name="btn_filter_showhide">Filters</button>-->
			<div id="filter_showhide">
				<div class="row">
					<div class="col-md-3 mb-3">
						<label for="validationCustomUsername" class="col-form-label">Channel Partner</label>
						<div class="dataTables_filter input-group">
							<select id="sSearch_0" class="searchInput form-control" name="sSearch_0"> 
								<option value="">All</option>
								<?php 
								if(!empty($creditors)){
									for($i=0; $i < sizeof($creditors); $i++){
								?>
									<option value="<?php echo $creditors[$i]['creditor_id']; ?>" ><?php echo $creditors[$i]['creaditor_name']; ?></option>
								<?php 
									}
								}
								?> 
							</select>
							<div class="input-group-prepend">
								<span class="input-group-text" id="inputGroupPrepend"><span class="material-icons">account_circle</span></span>
							</div>
						</div>
					</div>
					
					<div class="col-md-3 mb-3">
						<label for="validationCustomUsername" class="col-form-label">SM Name</label>
						<div class="dataTables_filter input-group">
							<select id="sSearch_1" class="searchInput form-control" name="sSearch_1"> 
								<option value="">All</option>
								<?php 
								if(!empty($sm)){
									for($i=0; $i < sizeof($sm); $i++){
								?>
									<option value="<?php echo $sm[$i]['employee_id']; ?>" ><?php echo $sm[$i]['employee_full_name']; ?></option>
								<?php 
									}
								}
								?> 
							</select>
							<div class="input-group-prepend">
								<span class="input-group-text" id="inputGroupPrepend"><span class="material-icons">account_circle</span></span>
							</div>
						</div>
					</div>
					
					<div class="col-md-3 mb-3">
						<label for="validationCustomUsername" class="col-form-label">Location</label>
						<div class="dataTables_filter input-group">
							<select id="sSearch_2" class="searchInput form-control" name="sSearch_2"> 
								<option value="">All</option>
								<?php 
								if(!empty($locations)){
									for($i=0; $i < sizeof($locations); $i++){
								?>
									<option value="<?php echo $locations[$i]['location_id']; ?>" ><?php echo $locations[$i]['location_name']; ?></option>
								<?php 
									}
								}
								?> 
							</select>
							<div class="input-group-prepend">
								<span class="input-group-text" id="inputGroupPrepend"><span class="material-icons">add_location</span></span>
							</div>
						</div>
					</div>
					
					<div class="col-md-3 mb-3">
						<label for="validationCustomUsername" class="col-form-label">Status</label>
						<div class="dataTables_filter input-group">
							<select id="sSearch_3" name="sSearch_3" class="searchInput form-control" name="sSearch_3"> 
								<option value="">All</option>
								<option value="Approved" selected>Issued</option>
								<option value="Pending">Pending </option>
								<option value="Rejected">Cancelled</option>
								
							</select>
							<div class="input-group-prepend">
								<span class="input-group-text" id="inputGroupPrepend"><span class="material-icons">toggle_off</span></span>
							</div>
						</div>
					</div>
					
					<div class="col-md-3 mb-3">
						<label for="validationCustomUsername" class="col-form-label">From Date</label>
						<div class="dataTables_filter input-group">
							<input id="from_date" name="sSearch_4" type="text" class="searchInput form-control datepicker" placeholder="Select ..." aria-describedby="inputGroupPrepend">
							<div class="input-group-prepend">
								<span class="input-group-text" id="inputGroupPrepend"><span class="material-icons">date_range</span></span>
							</div>
						</div>
					</div>
					
					<div class="col-md-3 mb-3">
						<label for="validationCustomUsername" class="col-form-label">To Date</label>
						<div class="dataTables_filter input-group">
							<input id="end_date" name="sSearch_5" type="text" class="searchInput form-control datepicker" placeholder="Select ..." aria-describedby="inputGroupPrepend">
							<div class="input-group-prepend">
								<span class="input-group-text" id="inputGroupPrepend"><span class="material-icons">date_range</span></span>
							</div>
						</div>
					</div>

					<div class="col-md-2 mb-3">
						<label for="validationCustomUsername" class="col-form-label">Sort By Asc/Desc</label>
						<div class="dataTables_filter input-group">
							<select id="sSearch_6" name="sSearch_6" class="searchInput form-control"> 
								<option value="desc" selected>Descending</option>
								<option value="asc">Ascending </option>								
							</select>
							<div class="input-group-prepend">
								<span class="input-group-text" id="inputGroupPrepend"><span class="material-icons">toggle_off</span></span>
							</div>
						</div>
					</div>
					
					<div class="col-md-2 col-6 text-left">
						<label style="visibility: hidden;" class="mt-1">For Space</label>
						<a href="<?php echo base_url();?>saleadmindashboard"><button class="btn cnl-btn">Clear Search</button></a>
					</div>
					
					<div class="col-md-1 col-6 text-right">
					<label style="visibility: hidden;" class="mt-1 col-md-12">For</label>
						<a href="#" onclick="exportResults();"><button class="btn exp-button" style="margin-top: 2%;">Export <i class="ti-export"></i></button></a>
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
					<div class="col-md-8 col-6">
						<p>Details</p>
					</div>
						<div class="col-md-4 col-6">
							
						</div>
			</div>
			</div>
			<div class="card-body">
				<div class="col-md-12 table-responsive scroll-table" style="height:300px;">
					<table class="dynamicTable table table-bordered non-bootstrap display pt-3 pb-3">
						<thead class="tbl-cre">
							<tr>
								<th data-bSortable="false">Current Ranking</th>
								<th data-bSortable="false">Channel Partner Name</th>
								<th data-bSortable="false">SM Name</th>
								<th data-bSortable="false">This Week(Net)</th>
								<th data-bSortable="false">This Week(Gross)</th>
								<th data-bSortable="false">MTD(NET)</th>
								<th data-bSortable="false">MTD(Gross)</th>
								<th data-bSortable="false">YTD(NET)</th>
								<th data-bSortable="false">YTD(Gross)</th>
								<th data-bSortable="false">Date Range Total(NET)</th>
								<th data-bSortable="false">Date Range Total(Gross)</th>
								<th data-bSortable="false">Date From</th>
								<th data-bSortable="false">Date To</th>
								<th data-bSortable="false">Details</th>
							</tr>
						</thead>
						<tbody>
							
						</tbody>
						<tfoot>
							<tr>
								<td colspan="3">Total</td>
								<td class="weeklyNetTot"></td>
								<td class="weeklyGrossTot"></td>
								<td class="mothlyNetTot"></td>
								<td class="mothlyGrossTot"></td>
								<td class="yearlyNetTot"></td>
								<td class="yearlyGrossTot"></td>
								<td class="dateRangeNetTot"></td>
								<td class="dateRangeGrossTot" colspan="4"></td>
							</tr>
						</tfoot>
					</table>
				</div>
			</div>
		</div>
	</div>
</div>
<!-- Center Section End-->		
<script type="text/javascript">
$(document).ready(function(){

	$("#btn_filter_showhide").click(function (){
		$("#filter_showhide").toggle();
	});

});

$(".datepicker1").datepicker({ 
	dateFormat: 'dd-mm-yy',
	changeMonth: true,
	changeYear: true,
	yearRange: "-100:" + new Date('Y'),
	maxDate: new Date()
	
});

$("#from_date").datepicker({ 
	dateFormat: 'dd-mm-yy',
	changeMonth: true,
	changeYear: true,
	//yearRange: "-100:" + new Date('Y'),
	//maxDate: new Date(),
	//minDate: new Date(),
	onSelect: function(selected) {
		$(this).change();
		$("#end_date").datepicker("option","minDate", selected)
	}
	
});

$("#end_date").datepicker({ 
	dateFormat: 'dd-mm-yy',
	changeMonth: true,
	changeYear: true,
	//yearRange: "-100:" + new Date('Y'),
	//maxDate: new Date(),
	onSelect: function(selected) {
		$(this).change();
		$("#from_date").datepicker("option","maxDate", selected)
	}
	
});


function exportResults(){
	var creditor_id = $("#sSearch_0").val();
	var sm_id = $("#sSearch_1").val();
	var location_id = $("#sSearch_2").val();
	var status = $("#sSearch_3").val();
	var date_from = $("#from_date").val();
	var date_to = $("#end_date").val();
	
	//alert("creditor_id: "+creditor_id+" sm_id: "+sm_id+" location_id: "+location_id+" status: "+" date_from: "+date_from+" date_to: "+date_to);
	//return false;
	
	$.ajax({
		url: "<?php echo base_url().$this->router->fetch_module();?>/saleadmindashboard/exportexcel",
		async: false,
		type: "POST",
		dataType: 'json',
		data: {creditor_id:creditor_id, sm_id:sm_id, location_id:location_id, status:status, date_from:date_from, date_to:date_to },
		success: function (response){
			if(response.success)
			{
				displayMsg("success",response.msg);
				window.open(response.Data,'_blank' );
			}
			else
			{	
				displayMsg("error",response.msg);
				return false;
			}
		}
	});
	
	
}

document.title = "Dashboard";
</script>