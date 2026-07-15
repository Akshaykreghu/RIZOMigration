<style>
.left-inner-addon {
    position: relative;
}
.left-inner-addon input {
    padding-left: 30px;    
}
.left-inner-addon i {
    position: absolute;
    padding: 10px 12px;
    pointer-events: none;
}

.right-inner-addon {
    position: relative;
}
.right-inner-addon input {
    padding-right: 30px;    
}
.right-inner-addon i {
    position: absolute;
    right: 0px;
    padding: 10px 12px;
    pointer-events: none;
}
</style>
<section class="content-header">
    <h1> Employee Setup </h1>
</section>
<!-- Main content -->
<section class="content">
    <div class="row">
        <div class="col-md-12">
            <!-- DIRECT CHAT DANGER -->
            <div class="box ">
                <div class="box-header with-border">
                   <h3 class="box-title"> Employees Setup</h3>
                </div><!-- /.box-header -->
                <div class="box-body">
                    <!-- Employee import form -->
                    <form class="form-horizontal" method="post" action="<?php echo $this->webroot; ?>Employee/saveemployeesetup" id="importemployeeform">
                        <div class="form-group">
                            <!--<div class="col-md-4">
                                <label class="col-md-5 control-label" for="import_emp_branch">Choose Branch</label>
                                <div class="col-md-7">
                                    <select id="import_emp_branch" name="import_emp_branch" class="form-control" >
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
                            </div>-->
                       
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
               
                    <div class=" box-body">
                        <div class="row">
                            <div class="col-md-4">
                                <label class="col-sm-5" for="filterby_branch">Choose Branch</label>
                                <div class="col-md-7">
                                    <select id="filterby_branch" name="filterby_branch" class="form-control" onchange="filterEmployees(this);" >
                                        <?php foreach ($arr_branches as $key => $value) { ?>                              
                                        <option  value="<?php echo $value['branch_code']; ?>"><?php echo $value['branch_name']; ?></option>
                                        <?php } ?>
                                    </select>
                                </div>
                            </div> 

                            <div class="col-md-4">
                                <label class="col-sm-5" for="filterby_employees">Choose Employee</label>                 
                                <div class="right-inner-addon col-md-7">
                                   <!-- <select id="filterby_employees" class="form-control" name="filterby_employees" onchange="filterAttendanceupload(this);" >
                                        <option value="">All</option>
                                        <?php foreach ($arr_Emp as $value) { ?>
                                        <option value="<?php echo $value['emp_pkey']; ?>" selected="selected"><?php echo $value['emp_name']; ?></option>
                                        <?php } ?>
                                    </select> -->

                                   <input type="search" id="filterby_employees" class="easyui-searchbox" name="filterby_employees" placeholder="Search Your Employee Name" /> 
                                </div>  
      
                            </div>
                            
                             <!--  <div class="col-md-4">
                                <label class="col-sm-5" for="filterby_Designation">Choose Designation</label>                 
                                <div class="col-md-7">
                                    <select id="filterby_Designation" class="form-control" name="filterby_Designation" onchange="filterAttendanceupload(this);" >
                                        <option value="">All</option>
                                        <?php foreach ($arr_Des as $value) { ?>
                                        <option value="<?php echo $value['desig_code']; ?>"><?php echo $value['desig_name']; ?></option>
                                        <?php } ?>
                                    </select>
                                </div>    
                            </div> -->
                        </div>
                    </div>
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
   
       function filterEmployees(obj) {
        var branch = $('#filterby_branch').val();   
        var employee = $('#filterby_employees').val();
        
        var designation = $('#filterby_Designation').val()
 
        $('#emptable').datagrid('load', {
            branch: branch,
            employee: employee,
            designation:designation,
           
        }); 
           
    } 
    function getstages(branch){
        //alert(skey);
         var request = $.ajax({
            url: "EmployeeUnder/getstages/"+branch
,            method: "POST",
            dataType: "html"
        });
        
        
        request.done(function( msg ) {
            $('#filterby_employees').html(msg);
        });
        
        request.fail(function( jqXHR, textStatus ) {
            alert( "Request failed: " + textStatus );
        });
    }
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
           
                      function filterAttendanceautocomplete(obj) {
        var branch = $('#filterby_branch').val();   
        var employee = obj;
        
        var designation = $('#filterby_Designation').val()
 
        $('#emptable').datagrid('load', {
            branch: branch,
            employee: employee,
            designation:designation,
           
        }); 
           
    } 
                var usersoptions = {
                    
            url: function (phrase) {   var branch = $('#filterby_branch').val();
                return livesite+"EmployeeUnder/getautocompletions?username=" +phrase + "&branch="+branch;
            },
            getValue: "emp_name",
            list: {
                onClickEvent: function () {
                     var selectedItem = $('#filterby_employees').getSelectedItemData();
                    var site_pkey = selectedItem.emp_pkey;

               //   alert(site_pkey);
                  //  reloadDatagrid(site_pkey);
                    filterAttendanceautocomplete(site_pkey);

                }
            }
        };

        $('#filterby_employees').easyAutocomplete(usersoptions);


        
        //  $('#filterby_employees').easyAutocomplete(getstages);
$('#emptable').datagrid({
				url:livesite+"EmployeeUnder/listemployees",
				pagination:true,
				singleSelect:true,
				
				toolbar: [{
				text:'New',
				iconCls:'icon-add',
				handler: function(){
			
					showLargeModalForm(livesite+'EmployeeUnder/setup/0');
				}
				},{
				iconCls: 'icon-edit',
				text:'Edit',
				handler: function(){
						var row = $('#emptable').datagrid('getSelected');
						if (row){
								    showLargeModalForm(livesite+'EmployeeUnder/setup/' + row.emp_pkey);
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
											 url:  livesite+"EmployeeUnder/deleteEmployees",
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
			              {field:'desig_name',title:'Designation',width:"20%"},
			              {field:'joining_date',title:'Joined Date',width:"20%"},
			              {field:'mobile_no',title:'Contact No',width:"20%"}
				]
				]
				});
        
  
    }); 
</script>