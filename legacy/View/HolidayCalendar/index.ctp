<style>
	#hcgrouptable_wrapper .DTTT.btn-group ,#hctable_wrapper .DTTT.btn-group{
		padding-left: 5px;
	}
	/* Edited by bindu 24-10-2025 */
		.heading {
		display: flex;
		flex-direction: row;
		align-items: end;
		justify-content: space-between;
	}

    .home {
        background-color: #ffffffff;
        border-radius: 50px;
        padding: 2px 15px;
        color: #1e516e !important;
        /* margin-right: 15px; */
        color: white;
        font-weight: 500;
        text-decoration: none;
        transition: all 0.3s ease;
        cursor: pointer;
        border: #1e516e 1px solid;
    }


	/* End */
</style>
<!-- /* edited by bindu 24-10-25 */ -->
<section class="content-header heading">
	<h1 class="text-primary-18">Holiday Calendar</h1>
	<?php if($plan !='basic'){?>
	  <div class="text-primary-16 home" style="display:flex; align-items:center; gap:10px; cursor:pointer;">
        <i class="fa" style="font-size:16px;">&#xf104;</i>
        Back
    </div>
	<?php } ?>

</section>
<hr style="margin-top: 8px;margin-bottom: -2px;margin-right: 15px;margin-left: 15px;">
<!-- end -->
<section class="content">
	<div class="row">
		<div class="col-md-4">
                    <ul id="hcgrouptable" title="Holiday Group" lines="true" style="width:100%; min-height:200px; height:auto">
                    </ul>
<!--			<div class="box box-solid">
				<div class="box-header with-border">
					<h3 class="box-title">Holiday Group</h3>
					<div class="box-tools"></div>
				</div>
				<div class="box-body">
					<table id="hcgrouptable" class="table table-hover table-striped" cellspacing="0" width="100%">
					
					</table>
				</div> /.box-body 
			</div>-->

		</div><!-- /.col -->
		<div class="col-md-8">
			<div class="box box-primary">
				<div class="box-header with-border">
					<h6 class="box-title">Holidays</h6>
					<div class="box-tools pull-right">
						<!--
						<div class="has-feedback">
						<input type="text" class="form-control input-sm" placeholder="Search Mail" />
						<span class="glyphicon glyphicon-search form-control-feedback"></span>
						</div>-->

                                                
                                                <label class="col-md-4 control-label" for="filterby_year"><div class="box-header with-border"><h6 class="box-title">Year</h6></div></label>
                                    <div class="col-md-8">
                                    <!-- <input type="hidden" name="" onload="findyear(this);"> -->
                                     <select2 id="filterby_yeares" name="filterby_year" class="form-control" data-minimum-results-for-search="Infinity" onchange="filterYear(this);" >
                                        
                                       
                                        <option value="0" >select</option>   
                                    <!--     <?php foreach ($array_date as $key=> $value) { ?>                              
                                        <option value="<?php echo $value['0']['year(HOLIDAYDATE)'] ; ?> "> <?php echo $value['0']['year(HOLIDAYDATE)'] ; ?> </option>
                                        <?php } ?> -->
                                    </select2>
                                    </div>
					</div><!-- /.box-tools -->
				</div><!-- /.box-header -->
				<div class="box-body">
					<table id="hctable"  class="table table-hover table-striped">
						
					
					</table><!-- /.table -->
					
				</div><!-- /.box-body -->

			</div><!-- /. box -->
		</div><!-- /.col -->
	</div><!-- /.row -->
</section><!-- /.content -->

<script>
    
    
    function filterYear(obj) {
      var date_year = $('#filterby_yeares').val();  
       var row = $('#hcgrouptable').datagrid('getSelected');	
			if (row) {
			  var group = row.HOLIDAY_GROUP_ID;
			}
			else{
			  alert("Please select a group");
			}
	 $('#hctable').datagrid('load', {
           group : group,
               d : date_year,
       }); 
    }
    //added by amal on 10/02/2020
     function findyear(branch)
    {
        $("#filterby_yeares").select2(
                {

                    //closeOnSelect:false,
                    placeholder: "All",
                    allowClear: true,
                    ajax: {
                        url: livesite + "HolidayCalendar/filterjson/" ,
                        dataType: 'json',
                        delay: 250,
                        data: function (params) {
                            return {
                                q: params.term, // search term
                                page: params.page
                            };
                        },

                        processResults: function (data, params) {
                            // parse the results into the format expected by Select2
                            // since we are using custom formatting functions we do not need to
                            // alter the remote JSON data, except to indicate that infinite
                            // scrolling can be used
                            params.page = params.page || 1;

                            return {
                                results: data.items,
                                pagination: {
                                    more: (params.page * 30) < data.total_count
                                }
                            };
                        }
                    },

                    escapeMarkup: function (markup) {
                        return markup;
                    }
                });
    
    }
    
    //end
	jQuery(document).ready(function() {
           findyear();
           $('#hcgrouptable').datagrid({
				url:livesite+"HolidayCalendar/listholidaygroupforgrid",
				pagination:true,
				singleSelect:true,
                                rownumbers: true,
				onLoadSuccess:function(){
					$('#hcgrouptable').datagrid("selectRow",0)
				},
				onSelect:function(index,row){
								var id = row.HOLIDAY_GROUP_ID;
                                                                 var date_year = $('#filterby_year').val(); 
								$('#hctable').datagrid('load',{
								group:id,
                                                                d : date_year,
								});
				},
				toolbar: [{
				text:'New',
				iconCls:'icon-add',
				handler: function(){
			
					showModalForm(livesite+'HolidayCalendar/groupform')
				}
				},{
				iconCls: 'icon-edit',
				text:'Edit',
				handler: function(){
				var row = $('#hcgrouptable').datagrid('getSelected');
				if (row){
				showModalForm(livesite+'HolidayCalendar/groupform?id=' +row.HOLIDAY_GROUP_ID)
						
			
				}else{
				alert("Please select a record to edit")
				}
				}
				},'-',{
				iconCls: 'icon-remove',
				text:'Remove',
				handler: function(){
			
				var row = $('#hcgrouptable').datagrid('getSelected');
			
				if (row){
			
				if (confirm("Are you sure want to delete ")) {
				var str_ids = row.HOLIDAY_GROUP_ID;
			        $.ajax({
			        url : livesite+"HolidayCalendar/deletegroup",
				data : {
				ids : str_ids
				},
				success : function(response) {
				var response =  $.parseJSON(response);
													if(response.success == true){
															$.notify(response.msg,{
																	type: 'success',
																	allow_dismiss: true
																															
																});
													}
                                                                                                        else
                                                                                                            {
                                                                                                                $.notify(response.msg,{
																	type: 'danger',
																	allow_dismiss: true
																															
																});
                                                                                                            }
				reloadTable('hcgrouptable')
				}
				});
			
				}
			
				}else{
                                    alert("Please select a row");
                                }
			
				}
				}],
				fitColumns:true,
				pageList:[2,5,10,50,100],
				columns:[[
				{field:'HOLIDAY_GROUP_NAME',title:'Name',width:"100%",sortable:true},
				]]
				});
				










				
	$('#hctable').datagrid({
					url:livesite+'HolidayCalendar/listholidays',
					pagination:true,
                                        rownumbers: true,

					/*
					onSelect:function(index,row){
					var id = row.LEAVEPOLICY_GROUP_ID;
					$('#leavepolicytable').DataTable().ajax.url('LeavePolicy/listpolicies?group=' + id).load();
					},*/

					toolbar: [{
							text:'New',
							iconCls:'icon-add',
							handler: function(){
								var row = $('#hcgrouptable').datagrid('getSelected');
								
				
							if (row) {
								
							var group = row.HOLIDAY_GROUP_ID;
									showSmallModalForm(livesite+'HolidayCalendar/form?HOLIDAY_GROUP_ID='+group);
							}
							else{
									alert("Please select a group")
							}
						
							}
						},

                                                    {
							iconCls: 'icon-edit',
							text:'Edit',
							handler: function(){
							var grouprow = $('#hcgrouptable').datagrid('getSelected');
							var rows = $('#hctable').datagrid('getSelections');
							var row = $('#hctable').datagrid('getSelected');
							console.log(rows);
							console.log(row);
							
							if (grouprow && row ) {
														// Show the salary submit form
														var group = grouprow.HOLIDAY_GROUP_ID
														showSmallModalForm(livesite+'HolidayCalendar/form?HOLIDAYID=' + row.HOLIDAYID+'HOLIDAY_GROUP_ID='+group)
														}
                                                                                                                else{
                    alert("Please select a record to edit")
                }
							
						
							}
					},'-',{
						iconCls: 'icon-remove',
						text:'Remove',
						handler: function(){
					
								var rows = $('#hctable').datagrid('getSelections');
								if ((rows)!=''){
                                                                   
													 var str_ids = "";
                    	 for(var i=0;i<rows.length;i++){
                    	 	var data = rows[i];
									 if(str_ids == ""){
										 str_ids += data.HOLIDAYID;
									 }else
									 {
										 str_ids += ","+data.HOLIDAYID;
									 }
                    	 }
											if (confirm("Are you sure want to delete ")) {
												
													$.ajax({
													url : livesite+"HolidayCalendar/delete",
													data : {
													ids : str_ids
													},
													success : function(response) {
													
													var response =  $.parseJSON(response);
													if(response.msg){
															$.notify(response.msg,{
																	type: 'success',
																	allow_dismiss: true
																															
																});
													}
															reloadTable('hctable')
														}
													});
										
											}
							
								}else{
                                    alert("Please select a row");
                                }
					
						}
	}],
	fitColumns:true,
	pageList:[2,5,10,50,100],
	columns:[[

			{field:'HOLIDAYNAME',title:'Holiday',width:"35%",sortable:true},
			{field:'HOLIDAYDATE',title:'Date',width:"28%",sortable:true},
//			{field:'HOLIDAYTYPE',title:'Type',width:"25%",sortable:true},
			{field:'DESCRIPTION',title:'Description',width:"35%",sortable:true}
	]]
	})

	}); 
	
/* edited by bindu 19-02-26 */
   $(".home").on("click", function () {

    $("#container").isLoading({
        text: "Loading",
        position: "overlay",
    });

    let url = "";
    var userGroup = <?php echo json_encode($this->Session->read('user_group')); ?>

    if (userGroup == "1") {
        url = livesite + "CompanySetup/index";
    } 
    else if (userGroup == "2") {
        url = livesite + "EmployeeMenu/addon";
    }

    $("#container").load(url, function () {
        isDashboardShown = false;
    });

});

	/* edited by bindu 19-02-26 */
</script>