<style>
    .form-horizontal .control-label{

        text-align: left;

    }
</style>

<section class="content-header">
    <h1 style="text-align:left; font-size: 3em;"> Vendor Master</h1>
</section>
<!-- Main content -->
<section class="content">
    <form class="form-horizontal" id="emp_expance" action="<?php echo $this->webroot; ?>Vendor/Master_save" method="POST">
                <div class="modal-body">
                    <div class="col-sm-12">
                        <div class="row">
                            <div class="col-sm-4 form-group">
                                <label>Vendor Name</label>
                                <input type="text" placeholder="Enter First Name Here.." class="form-control">
                            </div>
                            <div class="col-sm-4 form-group">
                                <label>Credit Period</label>
                                <input type="text" placeholder="Enter Nationality Here.." class="form-control">
                            </div>
                            <div class="col-sm-4 form-group">
                                <label>Email</label>
                                <input type="text" placeholder="Enter Last Name Here.." class="form-control">
                            </div>
                            
                        </div> 

                        <div class="row">
                        <div class="col-sm-4 form-group">
                                <label>Phone Number</label>
                                <input type="text" placeholder="Enter Last Name Here.." class="form-control">
                            </div>
                            <div class="col-sm-4 form-group">
                                <label>Contact Person</label>
                                <input type="text" placeholder="Enter First Name Here.." class="form-control">
                            </div>
                             <div class="col-sm-4 form-group">
                                <label>Desgination</label>
                                <input type="text" placeholder="Enter Last Name Here.." class="form-control">
                            </div>
                        </div> 
                      <div class="row">                 
                        
                            <div class="col-sm-3 form-group">
                                <label>Phone Number</label>
                                <input type="text" placeholder="Enter Last Name Here.." class="form-control">
                            </div>
                            <div class="col-sm-3 form-group">
                                <label>Email</label>
                                <input type="text" placeholder="Enter First Name Here.." class="form-control">
                            </div>
                            <div class="col-sm-6 form-group">
                            <label>Address</label>
                            <textarea placeholder="Enter Address Here.." rows="1" class="form-control"></textarea>
                        </div> 
</div> 
                                           
                          
                        <div class="row">   
                              <div class="col-sm-3 form-group">
                                <label>Add item</label>
                                <input type="text" placeholder="Enter First Name Here.." class="form-control">add
                              </div> 
                        
                 </div>
                    <div class="modal-footer">
                        <input type="hidden" required="required" class="form-control" value="<?php echo isset($item_details['0']['item']['item_pkey'])?$item_details['0']['item']['item_pkey']:''; ?>"name="emp_expenses_pkey">
                        <button type="button" class="btn btn-danger" data-dismiss="modal">Cancel</button>
                        <button type="submit" name="btn-submit" id="btn-submit" class="btn btn-success">Save</button>

                    </div>



                </div>
</div>


            </form>
                   
</section>

<script>
    //filtter using branch 
    function filterEmployees(branch)
    {
        
        var branch = $('#filterby_branch').val();
        //alert(branch);
        $("#emp_fkey").select2(
                {
                    //closeOnSelect:false,
                    placeholder: "All",
                    allowClear: true,
                    ajax: {
                        url: livesite + "Employee/jsons/" + branch,
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


    
    
    function filterAttendanceupload(obj) {
        var branch = $('#importemployeectcform #filterby_branch').val();
        var employee = $('#importemployeectcform #emp_fkey').val()

        $('#att_table').datagrid('load', {
            branch: branch,
            employee: employee,
        });
          filterEmployees();

    }


    function downloadEmployeeCTCForm() {
        var ctcuploadtype = $('#ctc_upload_type').val();
        if (ctcuploadtype == 1 || ctcuploadtype == 2) {
            window.open('<?php echo $this->webroot; ?>Employee/downloadempctcformat/' + ctcuploadtype, '_blank');
        } else {
            return false;
        }
    }
    function uploadEmployeeCTC() {
        var form = $('#importemployeectcform');
        var fileSelect = document.getElementById('empctccsv');
        var ctcuploadtype = $('#ctc_upload_type').val();

        // The rest of the code will go here...
        var files = fileSelect.files;
        // Create a new FormData object.
        var formData = new FormData();
        // Loop through each of the selected files.

        for (var i = 0; i < files.length; i++) {
            var file = files[i];
            // Add the file to the request.
            formData.append('empctc[]', file, file.name);
        }

        // Set up the request.
        var xhr = new XMLHttpRequest();

        // Open the connection.
        xhr.open('POST', livesite + 'Employee/uploadandsaveempctc/' + ctcuploadtype, true);

        // Set up a handler for when the request finishes.
        xhr.onload = function () {
            if (xhr.status === 200) {
                // File(s) uploaded.
                var response = JSON.parse(xhr.responseText);
                if (response.success) {
                    $.notify(response.msg, {
                        type: 'success',
                        allow_dismiss: false
                    });
                } else {
                    $.notify(response.msg, {
                        type: 'error',
                        allow_dismiss: false
                    });
                }
            } else {
                alert("Employee CTC import failed, please check informations given or try again.");
            }
        };
        $('#attdatacsv').val('');
        $("#filterby_branch").select2("val", "");
        $("#emp_fkey").select2("val", "");

        // Send the Data.
        xhr.send(formData);
    }


    jQuery(document).ready(function () {
        
        filterEmployees();
        $("#filterby_branch").select2();
        $("#filterby_month").select2();
        
        var employee = $('#attendanceuploadfilter #emp_fkey').val();

        $('#att_table').datagrid({
            url: livesite + "vendor/employeelist",
            pagination: true,
            singleSelect: true,
            rownumbers: true,
            queryParams: {
                employee: employee
            },
            toolbar: [{
                    text: 'New',
                    iconCls: 'icon-add',
                    handler: function () {
                        showModalForm(livesite + 'vendor/form')
                    }
                },{
				iconCls: 'icon-edit',
				text:'Edit',
				handler: function(){
						var row = $('#att_table').datagrid('getSelected');
						if (row){
								    showModalForm(livesite+'vendor/form/' + row.item_pkey);
						}else{
						alert("Please select a record to edit")
						}
				}
				},{
				iconCls: 'icon-remove',
				text:'Remove',
				handler: function(){
					
								var rows = $('#att_table').datagrid('getSelected');
								if (rows){
                                                                             var str_ids = "";
                                                                             str_ids = rows.item_pkey;
                                                                             
//                    	 for(var i=0;i<rows.length;i++){
//                    	 	var data = rows[i];
//									 if(str_ids == ""){
//										 str_ids += data.emp_pkey;
//									 }else
//									 {
//										 str_ids += ","+data.emp_pkey;
//									 }
//                    	 }      
											if (confirm("Do you want to delete the selected employee(s)?")) {
												
													$.ajax({
											 url:  livesite+"Uniform/deleteEmp",
													data : {
													ids : str_ids
													},
													success : function(response) {
													//var text = response.responseText;
													// process server response here
															reloadTable('att_table')
														}
													});
										
											}
							
								}else{
						alert("Please select a record to edit")
						}
					
				}
				}],
            fitColumns: true,
            pageList: [2, 5, 10, 50, 100],
            columns: [[
                    //{field: 'emp_expenses_pkey', title: '', width: "%"},
                    {field: 'item_name', title: 'Item Name', width: "30%"},
                    {field: 'item_code', title: 'Item Code', width: "30%"},
                    {field: 'item_desc', title: 'Item Description', width: "30%"},
                    //  {field: 'is_credited', title: 'Credited Rate', width: '10%'},
                ]]
        });
    });
</script>