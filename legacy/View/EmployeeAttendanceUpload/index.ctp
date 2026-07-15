 <style>
        .heading {
        display: flex;
        flex-direction: row;
        align-items: center;
        justify-content: space-between;
        /* margin-left: 20px; */
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
    }/* edited by bindu 24-10-25 */
</style>
 <!-- /* edited by bindu 24-10-25 */ -->
<section class="content-header heading">    
    <h1 class="text-primary-18">Attendance Upload</h1>
  <div class="text-primary-16 home" style="display:flex; align-items:center; gap:10px; cursor:pointer;">
        <i class="fa" style="font-size:16px;">&#xf104;</i>
        Back
    </div>

   
</section>
<hr style="margin-top: 8px;margin-bottom: -2px;margin-right: 15px;margin-left: 15px;">
    <!-- end -->
<section class="content">
    <div class="col-md-12">
        <!-- DIRECT CHAT DANGER -->
        <div class=" ">
            <!--            <div class="box-header with-border">
                            <h3 class="box-title">Attendance Upload</h3>
                        </div>-->
            <div class="">
                <?php
                ?>
                <form class="form-horizontal" method="post" action="" id="attendanceuploadfilter" enctype="multipart/form-data">

                    <div class="form-group">
                        <div class="col-md-4">
                            <label for="month" class="col-sm-3">Month</label>
                            <div class="col-md-1">:</div>
                            <div class="col-md-8">
                                <select id="filterby_month" name="filterby_month" class="form-control" onchange="filterAttendanceupload(this);">
                                    <option value="">All</option>
                                    <?php
                                    for ($i = -1; $i < 11; $i++) {
                                        echo '<option value="' . date('Y-m', strtotime("-$i month", strtotime(date('M-Y')))) . '">' . date('M-Y', strtotime("-$i month", strtotime(date('M-Y')))) . '</option>';
                                    }
                                    ?>
                                </select>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="col-md-3">

                                <label for="employee">Employee </label> <br>(Shift Allocated Employees Only)
                            </div>
                            <div class="col-md-1">:</div>
                            <!--<input type="radio" id="employeeview" name="employeeview" value="y">  -->
                            <div class="col-md-8">
                                <!-- //edited by megha branchwise employees display on 15_06_19-->
                                <select id="emp_fkey" class="form-control js-example-basic-single" name="emp_fkey" onchange="filterAttendanceupload(this);" value="0">
                                    <option value="">All</option>
                                    <?php //foreach ($arr_employees as $key => $val) {  
                                    ?>
                                    <!--                                        <option value="<?php //echo $val['EmployeeDetails']['emp_pkey'];  
                                                                                                ?>">-->
                                    <?php //echo $val['EmployeeDetails']['first_name'];  
                                    ?>
                                    <?php //echo ' '.$val['EmployeeDetails']['last_name']; 
                                    ?>
                                    <?php //echo ' - '.$val['EmployeeProfessionalDetails']['emp_company_id']; 
                                    ?>
                                    <!--                                </option>-->
                                    <?php //}  
                                    ?>
                                    <!--ends here megha-->
                                </select>
                            </div>
                        </div>
                        <div class="col-md-2" style="padding-left: 76px;margin-top: -3px;">
                            <span class=" " style="margin-top: -10px;margin-left: -18px;">
                                <span data-toggle="tooltip" data-placement="auto" title="Download the excel format with selected criterias. add the data then upload it.">
                                    <button type="button" id="btn-uploadattdata1" class="btn btn-danger btn-sm " onclick="downloadEmployeeCTCForm();"><i class="fa fa-download" aria-hidden="true"></i></button>
                                    <!--<span class="tooltiptext"></span>-->
                                </span>
                            </span>
                        </div>
                    </div>
                    <div class="form-group">
                        <div class="col-md-4">
                            <label class="col-sm-3" for="filterby_branch">Branch</label>
                            <div class="col-md-1">:</div>
                            <div class="col-md-8">
                                <select id="filterby_branch" name="filterby_branch" class="form-control" onchange="filterAttendanceupload(this);">
                                    <!-- edited by athira 29-01-2025 -->
                                    <?php
                                    if ($is_ho == 1 || $user_group != 2) {
                                        echo ' <option value="" style="text-align: center;">---- All ----</option>';
                                    }
                                    ?>
                                    <!-- end -->
                                    <?php foreach ($arr_branches as $key => $value) { ?>
                                        <option value="<?php echo $value['Units']['branch_code']; ?>"><?php echo $value['Units']['branch_name']; ?></option>
                                    <?php } ?>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="col-md-3">
                                <b>Upload File</b>
                            </div>
                            <div class="col-md-1">:</div>
                            <div class="col-md-8">
                                <label style="font-weight: normal;" for="file-upload" class="custom-file-upload">
                                    Click Here to Browse File
                                </label>
                                <input id="file-upload" name='attdatacsv' type="file" style="display:none;">
                            </div>


                        </div>
                        <div class="col-md-2" style="padding-left: 59px;">
                            <span class=" ">
                                <span data-toggle="tooltip" data-placement="auto" title="Upload the downloaded excel file">
                                    <!--                        <label class="col-md-5 control-label" for="file"></label>-->
                                    <button type="button" onclick="uploadEmployeeCTC();" id="btn-uploadattdata" class="btn btn-success btn-sm "><i class="fa fa-upload" aria-hidden="true"></i></button>

                                </span>
                            </span>
                        </div>
                        <!--                        <div class="col-md-6">
                                                    <label class="col-md-4" for="file">Upload File</label>
                                                    <div class="col-md-7">
                                                        <input  id="attdatacsv" name="attdatacsv" type="file"  class="form-control">
                                                    </div>
                                                     <div class="col-md-8">
                                                    <label style="margin-left: 10px;font-weight: normal;"for="file-upload" class="custom-file-upload">
                                                Click Here to Browse File
                                              </label>
                                              <input id="attdatacsv" name="attdatacsv" type="file" style="display:none;">
                                                </div>
                                                </div> 
                                                <div class="col-md-4">
                                                    <label class="col-md-5 control-label" for="file"></label>
                                                    <div class="col-md-7">
                                                        <button type="button" id="btn-uploadattdata" class="btn btn-primary" onclick="uploadEmployeeCTC();" >Upload Attendance</button>
                                                    </div>
                                                </div>
                                                <div class="col-md-4">
                                                        <label class="col-md-5 control-label" for="file"></label>
                                                        <div class="col-md-7">
                                                        <button type="button" id="btn-uploadattdata" class="btn btn-primary" onclick="downloadEmployeeCTCForm();" >Download attendance Form</button>
                                                    </div>
                                                </div>                           -->


                    </div>
                </form>
            </div>
        </div><!--/.direct-chat -->


        <div class="box box-primary ">
            <div class="box-body">
                <br>
                <table id="att_table" class="table table-bordered table-hover">
                    <tbody>
                    </tbody>
                </table>
            </div><!-- /.box-body -->
            <!--/.direct-chat -->
        </div><!-- /.col -->
    </div><!-- /.col -->
</section>
<script>
    $('#file-upload').change(function() {
        var i = $(this).prev('label').clone();
        var file = $('#file-upload')[0].files[0].name;
        $(this).prev('label').text(file);
    });
    //corected by sruthi 07/09/16 here starts
    function downloadEmployeeCTCForm() {
        var ctcuploadtype = 3;
        var filterby_branch = $('#attendanceuploadfilter #filterby_branch').val();
        var filterby_month_date = $('#attendanceuploadfilter #filterby_month').val();
        var emp_fkey = $('#attendanceuploadfilter #emp_fkey').val();
        if (filterby_month_date == '') {
            alert("Please Choose a Month");
            return false;
        }
        if (filterby_branch == '') {
            alert("Please Choose a Branch");
            return false;
        }

        if (ctcuploadtype == 3) {

            window.open('<?php echo $this->webroot; ?>EmployeeAttendanceUpload/downloadempattendanceformat/' + ctcuploadtype + '/' + filterby_branch + '/' + filterby_month_date + '/' + emp_fkey);

        } else {
            return false;
        }
    }
    //edited by megha branchwise employees display on 15_06_19
    function find_branchemployees() {
        var branch = $('#filterby_branch').val();
        $("#emp_fkey").select2({
            //closeOnSelect:false,
            placeholder: "All",
            allowClear: true,
            ajax: {
                url: livesite + "EmployeeAttendanceUpload/employeefilter/" + branch,
                dataType: 'json',
                delay: 250,
                data: function(params) {
                    return {
                        q: params.term, // search term
                        page: params.page
                    };
                },
                processResults: function(data, params) {
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
            escapeMarkup: function(markup) {
                return markup;
            }
        });
    }
    //here ends
    function filterAttendanceupload(obj) {
        var branch = $('#attendanceuploadfilter #filterby_branch').val();
        var employee = $('#attendanceuploadfilter #emp_fkey').val();
        var month = $('#attendanceuploadfilter #filterby_month').val();
        //edited by megha branchwise employees display on 15_06_19
        find_branchemployees();

        $('#att_table').datagrid('load', {
            branch: branch,
            employee: employee,
            month: month
        });

    }

    function uploadEmployeeCTC() {
        //added by megha on 15_06_19 file upload file exists checking
        if (document.getElementById("file-upload").files.length == 0) {
            alert("No files selected");
            return false;
        }
        //end here
        var form = $('#attendanceuploadfilter');
        var fileSelect = document.getElementById('file-upload');
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
        $('#btn-uploadattdata').html('<li class="fa fa-spin fa-spinner"></li>  Uploading Attendance please wait ...  ');
        // Open the connection.
        xhr.open('POST', livesite + 'EmployeeAttendanceUpload/uploadandsaveempctc/' + ctcuploadtype, true);

        // Set up a handler for when the request finishes.
        xhr.onload = function() {
            if (xhr.status === 200) {
                $('#file-upload').val("");
                // File(s) uploaded.
                var response = JSON.parse(xhr.responseText);
                if (response.success) {
                    $.notify(response.msg, {
                        type: 'success',
                        allow_dismiss: false
                    });
                    $('#btn-uploadattdata').html('<i class="fa fa-upload" aria-hidden="true"></i>');
                    $('#att_table').datagrid('load');
                } else {
                    $.notify(response.msg, {
                        type: 'error',
                        allow_dismiss: false
                    });
                    $('#btn-uploadattdata').html('<i class="fa fa-upload" aria-hidden="true"></i>');
                }
            } else {
                alert("Employee Attendance  import failed, please check informations given or try again.");
            }
        };

        // Send the Data.
        xhr.send(formData);
    }
    /*function uploadAttendanceData() {
     var form = $('#attendanceuploadfilter');
     //var fileSelect = $('#empdatacsv');
     var fileSelect = document.getElementById('attdatacsv');
     var empBranch = $('#filterby_branch').val();
     
     // The rest of the code will go here...
     var files = fileSelect.files;
     // Create a new FormData object.
     var formData = new FormData();
     // Loop through each of the selected files.
     
     for (var i = 0; i < files.length; i++) {
     var file = files[i];    
     // Add the file to the request.
     formData.append('empattdata[]', file, file.name);
     }
     
     // Set up the request.
     var xhr = new XMLHttpRequest();
     
     // Open the connection.
     xhr.open('POST', livesite+'EmployeeAttendanceUpload/uploadandsaveattdetails/'+empBranch, true);
     
     // Set up a handler for when the request finishes.
     xhr.onload = function() {
     if (xhr.status === 200) {
     // File(s) uploaded.
     var response = JSON.parse(xhr.responseText);
     if (response.success) {
     alert(response.msg);
     reloadTable('att_table');
     } else {
     alert(response.msg);
     }
     } else {
     alert("Attendance import failed, please try again.");
     }
     };
     
     // Send the Data.
     xhr.send(formData);
     }*/
    jQuery(document).ready(function() {
        //        $("#emp_fkey").select2({
        //        });
        //edited by athira on 28-01-2025
        find_branchemployees();
        //end
        var employee = $('#attendanceuploadfilter #emp_fkey').val();
        var fekyss = $('#emp_fkelistattendancey').val();
        $('#att_table').datagrid({
            url: livesite + "EmployeeAttendanceUpload/listattendance",
            pagination: true,
            singleSelect: true,
            rownumbers: true,
            queryParams: {
                employee: employee
            },
            toolbar: [{
                    text: 'New',
                    iconCls: 'icon-add',
                    handler: function() {
                        showModalForm(livesite + 'EmployeeAttendanceUpload/form/' + employee)
                    }
                }

                //                 , '-',{
                //                   iconCls: 'icon-edit',
                //                 text: 'Edit',
                //                  handler: function () {
                //                    var row = $('#att_table').datagrid('getSelected');
                //                  if (row) {
                //
                //                    showModalForm(livesite + 'EmployeeAttendanceUpload/form?emp_attendance_upload_pkey=' + row.emp_attendance_upload_pkey)
                //                } else {
                //                   alert("Please select a record to edit")
                //                }
                //                 }
                //                }
                , '-', {
                    iconCls: 'icon-remove',
                    text: 'Remove',
                    handler: function() {
                        var rows = $('#att_table').datagrid('getSelections');

                        if (rows != '') {
                            var str_ids = "";
                            for (var i = 0; i < rows.length; i++) {
                                var data = rows[i];
                                if (str_ids == "") {
                                    str_ids += data.emp_attendance_upload_pkey;
                                } else {
                                    str_ids += "," + data.emp_attendance_upload_pkey;
                                }
                            }
                            if (confirm("Are you sure want to delete ")) {

                                $.ajax({
                                    url: livesite + "EmployeeAttendanceUpload/deleteattendance",
                                    data: {
                                        emp_attendance_upload_pkeys: str_ids
                                    },
                                    success: function(response) {

                                        var response = $.parseJSON(response);
                                        if (response.msg) {
                                            $.notify(response.msg, {
                                                type: 'success',
                                                allow_dismiss: true

                                            });
                                        }


                                        reloadTable('att_table');
                                    }
                                });

                            }

                        } else {
                            alert("Please choose any record to delete.")
                        }
                    }
                }
            ],
            fitColumns: true,
            pageList: [2, 5, 10, 50, 100],
            columns: [
                [{
                        field: 'empname',
                        title: 'Employee Name',
                        width: "25%"
                    },
                    //{field: 'attendance_type', title: 'Attendance Type', width: "20%"},
                    {
                        field: 'in_date',
                        title: 'In Date',
                        width: "25%"
                    },
                    {
                        field: 'in_time',
                        title: 'In Time',
                        width: "25%"
                    },
                    {
                        field: 'direction',
                        title: 'Direction',
                        width: "25%"
                    },
                ]
            ]
        });
    });



   $(".home").on("click", function () {

    $("#container").isLoading({
        text: "Loading",
        position: "overlay",
    });

    let url = "";
    var userGroup = <?php echo json_encode($this->Session->read('user_group')); ?>;

    if (userGroup == "1") {
        url = livesite + "AttendanceSetup/index";
    } 
    else if (userGroup == "2") {
        url = livesite + "EmployeeMenu/addon";
    }

    $("#container").load(url, function () {
        isDashboardShown = false;
    });

});
</script>
<style>
    .custom-file-upload {
        border: 1px solid #0d0c0c52;
        border-radius: 4px;
        display: inline-block;
        padding: 4px 12px;
        cursor: pointer;
        width: 100%;
        height: 30px;
        text-align: center;
    }
</style>