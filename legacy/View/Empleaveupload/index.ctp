 
<section class="content-header">
    <h1 class="text-primary-18">Leave Upload</h1>
</section>
<section class="content">
    <div class="col-md-12">
        <!-- DIRECT CHAT DANGER -->
        <div class="box ">
            <div class="box-header with-border">
            </div><!-- /.box-header -->
            
            <form class="form-horizontal" method="post" action="" id="leaveuploadfilter">
                <div class="row">
                    <div class="form-group">
                        <div class="col-md-4">
                            <label for="month" class="col-sm-5 ">Choose Month</label>
                            <div class="col-md-7">
                                <select id="filterby_month" name="filterby_month" class="form-control"  onchange="filterleaveupload(this);" >                                                 
                                    <option value="">-- Choose a Month --</option>
                                     <?php
                                   
                                    for ($i = 0; $i < 10; $i++) {
                                        echo '<option value="' . date('Y-m', strtotime("-$i month", strtotime(date('M-Y')))) . '">' . date('M-Y', strtotime("-$i month", strtotime(date('M-Y')))) . '</option>';
                                    }
                                    ?>
                                </select>
                            </div> </div>
                        <div class="col-md-4">
                            <label class="col-sm-5 " for="filterby_branch">Choose Branch</label>
                            <div class="col-md-7">
                               <select id="filterby_branch" name="filterby_branch" class="form-control" onchange="filterleaveupload(this);" >
                                     <option value="">All</option>
                                    <?php foreach ($arr_branches as $key => $value) { ?>                              
                                        <option  value="<?php echo $value['Units']['branch_code']; ?>"><?php echo $value['Units']['branch_name']; ?></option>
                                    <?php } ?>
                                </select>
                            </div>
                        </div> 
                        <div class="col-md-4">
                            <label class="col-sm-5" for="employee">Choose Employee</label>                        
                        <!--<input type="radio" id="employeeview" name="employeeview" value="y">  -->                  
                        <div class="col-md-7">
                            <select id="emp_fkey" class="form-control" name="emp_fkey" onchange="filterleaveupload(this);" >
                                 <option value="">All</option>
                                <?php foreach ($arr_employees as $value) { ?>
                                    <option value="<?php echo $value['EmployeeDetails']['emp_pkey']; ?>"><?php echo $value['EmployeeDetails']['first_name']; ?><?php echo $value['EmployeeDetails']['last_name']; ?></option>
                                <?php } ?>
                            </select>
                        </div>    
                              
                    </div> 
                   
                </div>
                <div class="row">  
                    <div class="col-md-4">
                            <div class="col-md-7">
                                 <button type="button" id="btn-uploadattdata" class="btn btn-primary" onclick="downloadEmployeeCTCForm();" >Download Leave format</button>
                            </div>
                        </div>  
                     <div class="col-md-4">
                            <label class="col-md-5 control-label" for="file">Browse File</label>
                            <div class="col-md-7">
                                <input  type="file" id="lesa" class="form-control">
                            </div>
                        </div>   
                     <div class="col-md-4">
                         
                                <div class="col-md-7">
                                    <button type="button" onclick="uploadEmployeeCTC();" id="btn-uploadleavedata" class="btn btn-primary" >Upload Leave Data</button>
                                </div>
                            </div>
                                      
                </div>
            </form>
            <div class="box-body">
                <br>
                <table id="leave_table" class="table table-bordered table-hover">
                    <tbody>
                    </tbody>
                </table>
            </div><!-- /.box-body -->
        </div><!--/.direct-chat -->
    </div><!-- /.col -->
    </div>
</section>
<script>
    
       function downloadEmployeeCTCForm(){
        var ctcuploadtype = 3;
        if(ctcuploadtype == 3){
            window.open('<?php echo $this->webroot; ?>Empleaveupload/downloadempctcformat/'+ctcuploadtype,'_blank');
        }else{
            return false;
        }
    }
        function filterleaveupload(obj) {
        var branch = $('#leaveuploadfilter #filterby_branch').val();       
        var employee = $('#leaveuploadfilter #emp_fkey').val();
        var month = $('#leaveuploadfilter #filterby_month').val();       
        $('#leave_table').datagrid('load', {
            branch: branch,
            employee: employee ,
            month:month
        }); 

    }


     function uploadEmployeeCTC() {
        var form = $('#leaveuploadfilter');
        var fileSelect = document.getElementById('lesa');
        var ctcuploadtype = 3;

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
        xhr.open('POST', livesite+'EmployeeLeaveUpload/uploadandsaveempctc/'+ctcuploadtype, true);

        // Set up a handler for when the request finishes.
        xhr.onload = function() {
            if (xhr.status === 200) {
                // File(s) uploaded.
                var response = JSON.parse(xhr.responseText);
                if (response.success) {
                    $.notify(response.msg,{
                        type: 'success',
                        allow_dismiss: false
                    });
                } else {
                    $.notify(response.msg,{
                        type: 'error',
                        allow_dismiss: false
                    });
                }
            } else {
                alert("Employee CTC import failed, please check informations given or try again.");
            }
        };

        // Send the Data.
        xhr.send(formData);
    }
    jQuery(document).ready(function () {        
        var employee = $('#leaveuploadfilter #emp_fkey').val();
        $('#leave_table').datagrid({
            url: livesite + "Empleaveupload/listleave",
            pagination: true,
            singleSelect: true,
            queryParams:{
                    employee: employee
                },
            toolbar: [{
                    text: 'New',
                    iconCls: 'icon-add',
                    handler: function () {

                        showModalForm(livesite + 'Empleaveupload/form')
                    }
                }
//                , '-', {
//                    iconCls: 'icon-remove',
//                    text: 'Remove',
//                    handler: function () {
//                        var rows = $('#leave_table').datagrid('getSelections');
//                        if (rows) {
//                            var str_ids = "";
//                            for (var i = 0; i < rows.length; i++) {
//                                var data = rows[i];
//                                if (str_ids == "") {
//                                    str_ids += data.emp_leave_upload_pkey;
//                                } else
//                                {
//                                    str_ids += "," + data.emp_leave_upload_pkey;
//                                }
//                            }
//                            if (confirm("Are you sure want to delete ")) {
//
//                                $.ajax({
//                                    url: livesite + "EmployeeLeaveUpload/deleteleave",
//                                    data: {
//                                        emp_leave_upload_pkeys: str_ids
//                                    },
//                                    success: function (response) {
//
//                                        var response = $.parseJSON(response);
//                                        if (response.msg) {
//                                            $.notify(response.msg, {
//                                                type: 'success',
//                                                allow_dismiss: true
//
//                                            });
//                                        }
//
//
//                                        reloadTable('leave_table');
//                                    }
//                                });
//
//                            }
//
//                        }
//
//                    }
//             
//                }
            ],
            fitColumns: true,
            pageList: [2, 5, 10, 50, 100],
            columns: [[
                    {field: 'empname', title: 'Employee Name', width: "20%"},                   
                    {field: 'leave_start_date', title: 'Leave Start Date', width: "20%"},
                    {field: 'leave_start_session', title: 'Leave Start Session', width: "20%"},
                    {field: 'leave_end_date', title: 'Leave End Date', width: "20%"},
                    {field: 'leave_end_session', title: 'Leave End Session', width: "20%"},
                    {field: 'LEAVESTATUS', title: 'Leave Status', width: "20%"},
                    
                ]]
        });
    });
</script>