<section class="content-header">
    <h1> Employee CTC Upload </h1>
</section>
<!-- Main content -->
<section class="content">
    <div class="row">
        <div class="col-md-12">
            <!-- DIRECT CHAT DANGER -->
            <div class="box ">
                <div class="box-header with-border">
                    <h3 class="box-title"> CTC Upload </h3>
                    
                </div><!-- /.box-header -->
                <div class="box-body">
                    <!-- Employee import form -->
                    <form class="form-horizontal" method="post" action="" id="importemployeectcform">
                        <div class="row">
                    <div class="form-group">
                        
                        <div class="form-group">
                            <div class="col-md-4">
                                <label class="col-md-5 control-label" for="first_name">Choose Type</label>
                                <div class="col-md-7">
                                    <select id="ctc_upload_type" name="ctc_upload_type" class="form-control" >
                                        <option value="">--Select--</option>
                                        <option value="1">Integration</option>
                                        <option value="2">Revision</option>
                                    </select>
                                </div>
                            </div>
                
                        <div class="col-md-4">
                            <label class="col-sm-5" for="filterby_branch">Choose Branch</label>
                            <div class="col-md-7">
                               <select id="filterby_branch" name="filterby_branch" class="form-control" onchange="filterAttendanceupload(this);" >
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
                            <select id="emp_fkey" class="form-control" name="emp_fkey" onchange="filterAttendanceupload(this);" >
                                <option value="">All</option>
                                <?php foreach ($arr_employees as $value) { ?>
                                    <option value="<?php echo $value['EmployeeDetails']['emp_pkey']; ?>"><?php echo $value['EmployeeDetails']['first_name']; ?><?php echo $value['EmployeeDetails']['last_name']; ?></option>
                                <?php } ?>
                            </select>
                        </div>    
                            </div>    
                    </div> 
                  
                    </div>
                            
                        </div>
                        
                        <div class="form-group">
                            <div class="col-md-4">
                                <label class="col-md-5 control-label" for="empctccsv">Choose file</label>
                                <div class="col-md-7">
                                    <input id="empctccsv" name="empctccsv" type="file" class="form-control input-md" >
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="col-md-7">
                                    <button type="button" id="btn-uploademployeectc" class="btn btn-primary" onclick="uploadEmployeeCTC();">Upload CTC</button>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <button type="button" id="btn-downloademployeectcform" class="btn btn-primary" onclick="downloadEmployeeCTCForm();">Download Employee CTC Form</button>
                            </div>
                        </div>
                    </form>
                     <div class="box-body">
                <br>
                <table id="att_table" class="table table-bordered table-hover">
                    <tbody>
                    </tbody>
                </table>
            </div><!-- /.box-body -->
                </div>
                 
            </div>
        </div>
    </div>
</section>

<script>    
    function filterAttendanceupload(obj) {
        var branch = $('#importemployeectcform #filterby_branch').val();   
        var employee = $('#importemployeectcform #emp_fkey').val()
  
        $('#att_table').datagrid('load', {
            branch: branch,
            employee: employee,
           
        }); 
        
    } 
 
    function downloadEmployeeCTCForm(){
        var ctcuploadtype = $('#ctc_upload_type').val();
        if(ctcuploadtype == 1 || ctcuploadtype == 2){
            window.open('<?php echo $this->webroot; ?>Employee/downloadempctcformat/'+ctcuploadtype,'_blank');
        }else{
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
        xhr.open('POST', livesite+'Employee/uploadandsaveempctc/'+ctcuploadtype, true);

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
        var employee = $('#attendanceuploadfilter #emp_fkey').val();

        $('#att_table').datagrid({
            url: livesite + "Employee/employeelist",
            pagination: true,
            singleSelect: true,
            queryParams:{
                    employee: employee
                },
                    toolbar: [{
                    text: 'New',
                    iconCls: 'icon-add',
                    handler: function () {
                        showModalForm(livesite + 'Employee/form')
                    }
                }
                //,'-', {
                 //   iconCls: 'icon-edit',
                   // text: 'Edit',
                 //   handler: function () {
                   //     var row = $('#att_table').datagrid('getSelected');
                     //   if (row) {
//
  //                          showModalForm(livesite + 'Employee/form?emp_ctc_upload_pkey=' + row.emp_ctc_upload_pkey + '&emp_fkey='+row.emp_fkey)
    //                    } else {
      //                      alert("Please select a record to edit")
        //                }
          //          }
            //    }
//                , '-', {
//                    iconCls: 'icon-remove',
//                    text: 'Remove',
//                    handler: function () {
//                        var rows = $('#att_table').datagrid('getSelections');
//                        if (rows) {
//                            var str_ids = "";
//                            for (var i = 0; i < rows.length; i++) {
//                                var data = rows[i];
//                                if (str_ids == "") {
//                                    str_ids += data.emp_ctc_upload_pkey;
//                                } else
//                                {
//                                    str_ids += "," + data.emp_ctc_upload_pkey;
//                                }
//                            }
//                            if (confirm("Are you sure want to delete ")) {
//
//                                $.ajax({
//                                    url: livesite + "Employee/deleteEmployees",
//                                    data: {
//                                        emp_ctc_upload_pkey: str_ids
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
//                                       
//                                    }
//                                }); 
//                                
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
                    //{field: 'emp_ctc_upload_pkey', title: '', width: "20%"},
                    {field: 'empname', title: 'Employee Name', width: "30%"},
                    {field: 'emp_anual_ctc', title: 'Anual CTC', width: "30%"},
                  //  {field:'emp_loan_balance',title:'Loan Balance ',width:'20%'},
                  //  {field:'emp_advance',title:'Advance',width:'20%'},
                 //   {field:'emp_tds_deducted',title:'Deducted',width:'20%'},
                    {field:'start_date_effective',title:'Start Date Effective',width:'20%'},
                    {field:'end_date_effective',title:'End Date Effective',width:'20%'},
                    
                  
                      ]]
        });
    });
</script>