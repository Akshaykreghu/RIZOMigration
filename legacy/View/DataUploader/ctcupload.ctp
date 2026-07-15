<style>
    .form-horizontal .control-label{

        text-align: left;

    }
</style>



<section class="content-header">
    <h1> Employee Gross Salary Upload </h1>
</section>
<!-- Main content -->
<section class="content">
    <div class="row">
        <div class="col-md-12">
            <!-- DIRECT CHAT DANGER -->

            <div class="box ">
                <!--                <div class="box-header with-border">
                                    <h3 class="box-title"> Gross Salary Upload </h3>
                
                                </div> /.box-header -->
                <div class="box-body">
                    <!-- Employee import form -->
                    <br>
                    <form class="form-horizontal" method="post" action="" id="importemployeectcform" name="importemployeectcform">

                        <div class="form-group">


                            <div class="col-md-3">
                                <label class="col-md-5 control-label" for="first_name">Choose Type</label>
                                <div class="col-md-7">
                                    <select id="ctc_upload_type" name="ctc_upload_type" class="form-control js-example-basic-single" >
                                        <!--                                        <option value="">--Select--</option>-->
                                        <option value="1">Integration</option>
                                        <option value="2">Revision</option>
                                    </select>
                                </div>
                            </div>

                            <div class="col-md-4">
                                <label class="col-sm-5 control-label" for="filterby_branch">Choose Branch</label>
                                <div class="col-md-7">
                                    <select id="filterby_branch" name="filterby_branch" class="form-control js-example-basic-single" onchange="filterAttendanceupload(this);" >
                                        <option value="">All</option>
                                        <?php foreach ($arr_branches as $key => $value) { ?>                              
                                            <option  value="<?php echo $value['Units']['branch_code']; ?>"><?php echo $value['Units']['branch_name']; ?></option>
                                        <?php } ?>
                                    </select>
                                </div>
                            </div> 
                            <div class="col-md-5">
                                <label class="col-sm-5 control-label" for="employee">Choose Employee</label>                        
                                <div class="col-md-7">
                                    <select id="emp_fkey" class="form-control js-example-basic-single" name="emp_fkey" onchange="filterAttendanceupload(this);"  >

                                    </select>

                                </div>    
                            </div>    
                        </div> 
                        <br>
                        <div class="form-group">        
                            <div class="col-md-4">
                                <!--                                <label class="col-md-5 control-label" for="empctccsv">Choose file</label>-->
                                <div class="col-md-7">
<!--                                    <input id="empctccsv" name="empctccsv" type="file">-->
                                </div>
                            </div>
                            <div class="col-md-4">
                                <label class="col-md-5 control-label" for="empctccsv">Choose file</label>
                                <div class="col-md-7">
                                    <!--                                    <button type="button" id="btn-uploademployeectc" class="btn btn-primary" onclick="uploadEmployeeCTC();">Upload Gross Salary</button>-->
                                    <input id="empctccsv" name="empctccsv" type="file">

                                </div>
                            </div>
                            <div class="col-md-4">
                                <button type="button" id="btn-uploademployeectc" class="btn btn-success col-md-5" onclick="uploadEmployeeCTC();">Upload Gross Salary</button>
                                <div class="col-md-7">
                                    <button type="button" id="btn-downloademployeectcform" class="btn btn-danger col-md-12 " onclick="downloadEmployeeCTCForm();">Download Format</button>
                                </div>
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
//focus on text field
        if (ctcuploadtype == '') {
            alert('Please select Choose Type ');
            $('#ctc_upload_type').focus();
        }
        var ctcuploadtype = $('#ctc_upload_type').val();
        var branch = $('#importemployeectcform #filterby_branch').val();
        var employee = $('#importemployeectcform #emp_fkey').val();
        var month = $('#importemployeectcform #emp_fkey').val();

        //alert(branch);
        //alert(employee);

        if (ctcuploadtype == 1 || ctcuploadtype == 2) {

            window.open('<?php echo $this->webroot; ?>DataUploader/downloadempctcformat/' + ctcuploadtype + '/' + branch + '/' + employee + '/' + month, '_blank');
        } else {
            return false;
        }






    }
    function uploadEmployeeCTC() {

        
        var form = $('#importemployeectcform');
        var fileSelect = document.getElementById('empctccsv');
        var ctcuploadtype = $('#ctc_upload_type').val();
        var files = fileSelect.files;

        if (files.length == 0) {
            $.notify("please choose any file to upload!", {
                type: 'danger',
                allow_dismiss: false
            });
            return false;
        }

        // The rest of the code will go here...
        var files = fileSelect.files;
        // Create a new FormData object.
        var formData = new FormData();
        // Loop through each of the selected files.
//alert(formData);
        for (var i = 0; i < files.length; i++) {
            var file = files[i];
            // Add the file to the request.
            //alert(file);
            formData.append('empctc[]', file, file.name);
//          /  alert(formData);
        }
        // alert()

        // Set up the request.
        var xhr = new XMLHttpRequest();

        // Open the connection.
        xhr.open('POST', livesite + 'DataUploader/uploadandsaveempctc/' + ctcuploadtype, true);

        // Set up a handler for when the request finishes.
        xhr.onload = function () {
            if (xhr.status === 200) {
                // File(s) uploaded.
				$('#empctccsv').val("");
                var response = JSON.parse(xhr.responseText);
                if (response.success) {
                    $('#att_table').datagrid('reload');
                    $.notify(response.msg, {
                        type: 'success',
                        allow_dismiss: false
                    });
                } else {
                    $('#att_table').datagrid('reload');
                    $.notify(response.msg, {
                        type: 'error',
                        allow_dismiss: false
                    });
                }
            } else {
                alert("Employee Gross Salary import failed, please check informations given or try again.");
            }
        };
        //$('#ctc_upload_type').val('');
        $('#empctccsv').val('');
        $("#filterby_branch").select2("val", "");
        $("#emp_fkey").select2("val", "");

        //$("#importemployeectcform").resetForm();


        //document.getElementById("importemployeectcform").reset();

        // $('#importemployeectcform').reset();

//$('form[name=myform]').get(0).reset();
        //  $('importemployeectcform').get(0).reset();
        // $('importemployeectcform').clearForm()
        // Send the Data.
        xhr.send(formData);
    }


    jQuery(document).ready(function () {

        filterEmployees();
        $("#filterby_branch").select2();
        $("#filterby_month").select2();

        var employee = $('#attendanceuploadfilter #emp_fkey').val();

        $('#att_table').datagrid({
            url: livesite + "Employee/employeelist",
            pagination: true,
            singleSelect: true,
            PostsearchFilter:true,
            rownumbers: true,
            queryParams: {
                employee: employee
            },
            toolbar: [{
                    text: 'New',
                    iconCls: 'icon-add',
                    handler: function () {
                        showModalForm(livesite + 'Employee/form')
                    }
                },
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
                    {field: 'empname', title: 'Employee Name', width: "25%"},
                    {field: 'empid', title: 'Employee ID', width: "25%"},
                    {field: 'emp_anual_ctc', title: 'Anual Gross Salary', width: "25%"},
                    //  {field:'emp_loan_balance',title:'Loan Balance ',width:'20%'},
                    //  {field:'emp_advance',title:'Advance',width:'20%'},
                    //   {field:'emp_tds_deducted',title:'Deducted',width:'20%'},
                    {field: 'start_date_effective', title: 'Start Date Effective', width: '24%'},
//                    {field: 'end_date_effective', title: 'End Date Effective', width: '14%'},
                ]],
                                onSearch:function(s){
                                    
                                    $('#att_table').datagrid('load',{
                                            emp: $('#searchqupo').val()
                                    });
                                }
        });
    });
</script>