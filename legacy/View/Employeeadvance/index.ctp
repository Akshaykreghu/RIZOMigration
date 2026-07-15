<section class="content-header">
    <h1 class="text-primary-18"> Employee Setup </h1>
</section>
<!-- Main content -->
<section class="content">
    <div class="row">
        <div class="col-md-12">
            <!-- DIRECT CHAT DANGER -->
            <div class="box ">
                <div class="box-header with-border">
                    <h3 class="box-title"> Import employee(s) </h3>
                </div><!-- /.box-header -->
                <div class="box-body">
                    <!-- Employee import form -->
                    <form class="form-horizontal" method="post" action="<?php echo $this->webroot; ?>Employee/saveemployeesetup" id="importemployeeform">
                        <div class="form-group">
                            <div class="col-md-4">
                                <label class="col-md-5 control-label" for="first_name">Choose Branch</label>
                                <div class="col-md-7">
                                    <select id="import_emp_branch" name="import_emp_branch" class="form-control" >
                                        <option value="">--Select--</option>
                                        <?php
                                            foreach ($arr_branches as $key => $value) {
                                                echo '<option value="' . $value['branch_code'] . '">' . $value['branch_name'] . '</option>';
                                            }
                                        ?>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <label class="col-md-5 control-label" for="middile_name">Choose file</label>
                                <div class="col-md-7">
                                    <input id="empdatacsv" name="empdatacsv" type="file" class="form-control input-md" >
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="col-md-7">
                                    <button type="button" id="btn-uploademployeedata" class="btn btn-primary" onclick="uploadEmployeeData();">Upload Employee Data</button>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-md-12">
            <!-- Employee List -->
            <!-- DIRECT CHAT DANGER -->
            <div class="box ">
                <div class="box-header with-border">
                    <h3 class="box-title">List of employees(<?php echo $active_emp_count; ?>)</h3>
                    <!--div class="box-tools pull-right">
                        <button class="btn btn-box-tool" data-widget="collapse">
                            <i class="fa fa-minus"></i>
                        </button>
                        <button class="btn btn-box-tool" >
                            <i class="fa fa-times"></i>
                        </button>
                    </div-->
                </div><!-- /.box-header -->
                <div class="box-body">
                    <table id="emptable" class="table table-bordered table-hover">
                     
                    </table>

                </div><!-- /.box-body -->

            </div><!--/.direct-chat -->
        </div><!-- /.col -->
    </div>
</section>
<div id="empSetupModalForm" class="modal fade">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <!-- Content will be loaded here from "remote.php" file -->
        </div>
    </div>
</div>
<script>
    
    function uploadEmployeeData() {
            var form = $('#importemployeeform');
            //var fileSelect = $('#empdatacsv');
            var fileSelect = document.getElementById('empdatacsv');
            var empBranch = $('#import_emp_branch').val();
    
            // The rest of the code will go here...
            var files = fileSelect.files;
            // Create a new FormData object.
            var formData = new FormData();
            // Loop through each of the selected files.
           
            for (var i = 0; i < files.length; i++) {
                var file = files[i];    
                // Add the file to the request.
                formData.append('empdata[]', file, file.name);
            }
    
            // Set up the request.
            var xhr = new XMLHttpRequest();
    
            // Open the connection.
            xhr.open('POST', livesite+'Employee/uploadandsaveempdetails/'+empBranch, true);
    
            // Set up a handler for when the request finishes.
            xhr.onload = function() {
                if (xhr.status === 200) {
                    // File(s) uploaded.
                    var response = JSON.parse(xhr.responseText);
                    if (response.success) {
                        alert(response.msg);
                        reloadTable('emptable');
                    } else {
                        alert(response.msg);
                    }
                } else {
                    alert("Employee import failed, please try again.");
                }
            };
    
            // Send the Data.
            xhr.send(formData);
    }
    
    jQuery(document).ready(function() {       
        
$('#emptable').datagrid({
				url:livesite+"Employee/listemployees",
				pagination:true,
				singleSelect:true,
				
				toolbar: [{
				text:'New',
				iconCls:'icon-add',
				handler: function(){
			
					showLargeModalForm(livesite+'Employee/setup/0');
				}
				},{
				iconCls: 'icon-edit',
				text:'Edit',
				handler: function(){
						var row = $('#emptable').datagrid('getSelected');
						if (row){
								    showLargeModalForm(livesite+'Employee/setup/' + row.emp_pkey);
						}else{
						alert("Please select a record to edit")
						}
				}
				},{
				iconCls: 'icon-remove',
				text:'Remove',
				handler: function(){
					
								var rows = $('#emptable').datagrid('getSelections');
								if (rows){
													 var str_ids = "";
                    	 for(var i=0;i<rows.length;i++){
                    	 	var data = rows[i];
									 if(str_ids == ""){
										 str_ids += data.emp_pkey;
									 }else
									 {
										 str_ids += ","+data.emp_pkey;
									 }
                    	 }
											if (confirm("Do you want to delete the selected employee(s)?")) {
												
													$.ajax({
											 url:  livesite+"Employee/deleteEmployees",
													data : {
													ids : str_ids
													},
													success : function(response) {
													//var text = response.responseText;
													// process server response here
															reloadTable('emptable')
														}
													});
										
											}
							
								}
					
				}
				},'-',{
				iconCls: 'icon-export',
				text:'Download Employee Data Format',
				handler: function(){
					
					 window.open(livesite+'Employee/downloadempdataformat','_blank');
				}
				}],
				fitColumns:true,
				pageList:[2,5,10,50,100],
				columns:[
				[
						
			              {field:'emp_company_id',title:'Employee ID',width:"20%"},
			              {field:'name',title:'Full Name',width:"20%"},
			              {field:'designation',title:'Designation',width:"20%"},
			              {field:'joining_date',title:'Joined Date',width:"20%"},
			              {field:'mobile_no',title:'Contact No',width:"20%"}
				]
				]
				});
        
  
    }); 
</script>