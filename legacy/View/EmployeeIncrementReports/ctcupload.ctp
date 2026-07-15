<style>
    .form-horizontal .control-label {
        text-align: left;
    }

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

<section class="content">
    <div class="col-md-12" style="padding: -10px;">

        <h1 style="font-size: 25px;">Employee Gross Salary Upload</h1>
        <hr style="margin-top: 8px;margin-bottom: -2px;">

    </div>
    <div class="col-md-12">
        <!-- Employee import form -->
        <br>
        <form class="form-horizontal" method="post" action="" id="importemployeectcform" name="importemployeectcform">

            <div class="form-group">
                <div class="col-md-5">
                    <label class="col-md-4 control-label" for="filterby_branch">Branch</label>
                    <div class="col-md-1">:</div>
                    <div class="col-md-7">
                        <select id="filterby_branch" name="filterby_branch" class="form-control js-example-basic-single" onchange="filterAttendanceupload(this);">



                            <?php foreach ($arr_branches as $key => $value) {
                                if (isset($payroUser[0]['emp_proff']['emp_branch']) && $payroUser[0]['emp_proff']['payro_priv'] == 1) {
                                    if ($payroUser[0]['emp_proff']['emp_branch'] !==  $value['Units']['branch_code']) {
                            ?>

                                        <option value="<?php echo $value['Units']['branch_code']; ?>"><?php echo $value['Units']['branch_name']; ?></option>
                                    <?php }
                                } else { ?>
                                <!-- edited by athira on 30-01-2025 -->
                                 <?php
                                if($is_ho==1 || $user_group!=2){    
                                    echo '<option value="">All</option>';
                                     }?>
                                     <!-- end -->
                                    <option value="<?php echo $value['Units']['branch_code']; ?>"><?php echo $value['Units']['branch_name']; ?></option>
                            <?php }
                            } ?>
                        </select>
                    </div>
                </div>
                <div class="col-md-5">
                    <label class="col-md-4 control-label" for="employee">Employee</label>
                    <div class="col-md-1">:</div>
                    <div class="col-md-7">
                        <select id="emp_fkey" class="form-control js-example-basic-single" name="emp_fkey" onchange="filterAttendanceupload(this);">
                            <option value="">All</option>
                        </select>

                    </div>
                </div>
                <div class="col-md-2" align="center">
                    <button type="button" id="btn-downloademployeectcform" class="btn btn-danger btn-sm " onclick="downloadEmployeeCTCForm();"><i class="fa fa-download" aria-hidden="true"></i></button>
                </div>
                <!--                    <div class="col-md-3" >
                                        <label for="out_time" class="col-md-4 control-label">Start Date Effective<span class="star">*</span></label>
                                        <div class="col-md-1">:</div>
                                        <div class="col-md-7">
                                            <input type="text" required="required" class="form-control "  onblur="startdateeffect()"value="<?php echo isset($data['start_date_effective']) ? $data['start_date_effective'] : ""; ?>"name="start_date_effective" name="startdateeff" id="effect" >
                                        </div> 
                
                                    </div> -->
            </div>
            <div class="form-group">
                <!-- <div class="col-md-5">
                    <label class="col-md-4 control-label" for="first_name">Type</label>
                    <div class="col-md-1">:</div>
                    <div class="col-md-7">
                        <select id="ctc_upload_type" name="ctc_upload_type" class="form-control js-example-basic-single">
                            <option value="1">Integration</option>
                            <option value="2">Revision</option>
                        </select>
                    </div>
                </div> -->
                <!-- Edited by Akshay on 15-10-2024 -->
                <div class="col-md-5">
                    <label class="col-md-4 control-label" for="first_name">Approved By</label>
                    <div class="col-md-1">:</div>
                    <div class="col-md-7">
                        <select id="approved_bys" class="form-control js-example-basic-single" name="approved_by" onchange="filterAttendanceupload(this);">
                            <option value="">--Select--</option>
                        </select>
                    </div>
                </div>
                <!-- End -->
                <div class="col-md-5">
                    <label class="col-md-4 control-label" for="empctccsv">Upload File</label>
                    <div class="col-md-1">:</div>
                    <div class="col-md-7">
                        <!--                                    <button type="button" id="btn-uploademployeectc" class="btn btn-primary" onclick="uploadEmployeeCTC();">Upload Gross Salary</button>-->
                        <!-- Edited by Akshay on 15-10-2024 -->
                        <label style="font-weight: normal;" for="empctccsv" id="empctccsv-label" class="custom-file-upload">
                            Click Here to Browse File
                        </label>
                        <input id="empctccsv" name='empctccsv' type="file" style="display:none;" disabled>
                        <!-- End -->
                    </div>
                </div>

                <div class="col-md-2" align="center">
                    <button type="button" onclick="uploadEmployeeCTC();" id="btn-uploademployeectc" class="btn btn-success btn-sm "><i class="fa fa-upload" aria-hidden="true"></i></button>
                </div>


            </div>
            <!-- AMAL -->
            <div class="form-group">
                <!-- <div class="col-md-4">
                                                   <label class="col-md-5 control-label" for="empctccsv">Choose file</label>--
                    <div class="col-md-7">
                        <input id="empctccsv" name="empctccsv" type="file">--
                    </div>
                </div> -->
                <!--                <div class="col-md-12" style="margin-bottom: 20px;">-->
                <div class="col-md-5" id="monthly">
                    <label class="col-md-4 control-label" for="arrear">Arrear Salary Process</label>
                    <div class="col-md-1">:</div>
                    <div class="col-md-7">
                        <label class="radio-inline">
                            <input type="radio" name="arrear" value="Y" id="y" onclick="filterAttendanceupload(this);"> YES
                        </label>
                        <label class="radio-inline">

                            <input type="radio" name="arrear" value="N" id="z" checked="checked" onclick="filterAttendanceupload(this);"> NO
                        </label>
                    </div>
                </div>
                <div class="col-md-5" id="date" style="display: none;">
                    <label for="out_time" class="col-md-4 control-label">Pay Out Month<span class="star">*</span></label>
                    <div class="col-md-1">:</div>
                    <div class="col-md-7">
                        <input type="text" required="required" class="form-control " onblur="startdatcheck()" value="<?php echo isset($data['start_date_effective']) ? $data['start_date_effective'] : ""; ?>" name="start_date_effective" name="time" id="time">
                    </div>
                    <!-- <button type="submit" id="btn-submit" class="btn btn-primary" style="float: right;">Save</button> -->
                </div>
                <!--</div>-->
            </div>


            <!-- AMAL END -->

        </form>
        <!-- <div class="box-body"> -->
            <br>
            <table id="att_table" class="table table-bordered table-hover">
                <tbody>
                </tbody>
            </table>
        <!-- </div> -->
        <!-- /.box-body -->
    </div>
</section>
<style type="text/css">
    .pws_tabs_list {
        min-height: 900px;
    }
</style>
<script>
    $('#empctccsv').change(function() {
        var i = $(this).prev('label').clone();
        var file = $('#empctccsv')[0].files[0].name;
        $(this).prev('label').text(file);
    });

    //Edited by Akshay on 15-10-2024
    $("#empctccsv-label").on("click", function(event) {
        // Check if the input is disabled
        if ($("#empctccsv").prop("disabled")) {
            // Prevent the default behavior of the label
            event.preventDefault();
            // Show alert only if the input is disabled
            alert("Please select an employee in Approved by.");
        }
    });
    //End

    //filtter using branch 
    function filterEmployees() {


        var branch = $('#filterby_branch').val();
        //Edited by Akshay on 15-10-2024
        var fileInput = $('#empctccsv');
        if ($('#approved_by').val() !== '') {
            fileInput.prop('disabled', false);
        } else {
            fileInput.prop('disabled', true);
        }
        //End

        $("#emp_fkey, #approved_by").select2({ //Edited by Akshay on 15-10-2024
            //closeOnSelect:false,
            placeholder: "--Select--",
            allowClear: true,
            ajax: {
                url: livesite + "Employee/jsons/" + branch,
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
                    //Edited by Akshay pm 15-10-2024
                    var selectElement = this.$element;
                    var selectId = selectElement.attr('id');
                    if (selectId === 'approved_by') {
                        data.items = data.items.filter(function(item) {
                            return item.id !== '0';
                        })
                    }
                    //End
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
    // AMAL
    function startdatcheck() {
        var startDate = new Date($('#time').val());
        var endDate = new Date($('#out_time').val());

        if (startDate > endDate) {
            alert("expected starting date should be less than ending date");
            $("#time").val('');
        }
    }
    //date validation  end
    function enddatecheck() {
        var startDate = new Date($('#time').val());
        var endDate = new Date($('#out_time').val());

        if (startDate > endDate) {
            alert("expected ending date should be greater than starting date");
            $("#out_time").val('');
        }
    }
    $('#time').datepicker({
        format: 'dd-mm-yyyy',
        autoclose: true,
        startView: "days", // Edited by Akshay on 4-12-2024
        minViewMode: "days" // Edited by Akshay on 4-12-2024
    })

    $('#out_time').datepicker({
        format: 'yyyy-mm-dd',
        autoclose: true,
        //            onSelect: function (selected) {
        //                var ecdt = new Date(selected);
        //                var selectedstartdate = $("#in_date").val();
        //                var esdt = new Date(selectedstartdate);
        //                if (esdt > ecdt) {
        //                    alert('Expected Out Date Should Be Greater Than Expected In Date');
        //                    $("#out_date").val('');
        //                }
        //
        //            }
    })
    $("#out_date").inputmask("yyyy-mm-dd");
    $('#ctc_upload_type').on('change', function() {

        // if (this.value === '2') {
        //     $('#monthly').css("display", "block");
        // } else {
        //     $('#monthly').css("display", "none");
        // }
    });
    $('#y').on('click', function() {

        if (this.value === 'Y') {
            $('#date').css("display", "block");
        }
    });
    $('#z').on('click', function() {

        if (this.value === 'N') {
            $('#date').css("display", "none");
        }
    });
    // AMAL END
    function startdateeffect() {
        var startDateeffect = new Date($('#effect').val());
        var endDateeffect = new Date($('#end_time').val());

        if (startDateeffect > endDateeffect) {
            alert("expected starting date should be less than ending date");
            $("#effect").val('');
        }
    }
    //date validation  end
    function enddatecheck() {
        var startDateeffect = new Date($('#effect').val());
        var endDateeffect = new Date($('#end_time').val());

        if (startDateeffect > endDateeffect) {
            alert("expected ending date should be greater than starting date");
            $("#end_time").val('');
        }
    }
    $('#effect').datepicker({
        format: 'yyyy-mm-dd',
        autoclose: true,
        startView: "months",
        minViewMode: "months"
    })

    $('#end_time').datepicker({
        format: 'yyyy-mm-dd',
        autoclose: true,
        //            onSelect: function (selected) {
        //                var ecdt = new Date(selected);
        //                var selectedstartdate = $("#in_date").val();
        //                var esdt = new Date(selectedstartdate);
        //                if (esdt > ecdt) {
        //                    alert('Expected Out Date Should Be Greater Than Expected In Date');
        //                    $("#out_date").val('');
        //                }
        //
        //            }
    })
    $("#end_date").inputmask("yyyy-mm-dd");

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
        // AMAL
        // var arrear = $('#importemployeectcform #y').val();
        var arrear = $('input[name="arrear"]:checked').val(); //Edited by Akshay on 19-10-2024

        var payout = $('#importemployeectcform #time').val();
        var startdate = $('#importemployeectcform #effect').val();
        //Edited by Akshay on 15-10-2024
        var approvedBy = $('#approved_by').val();
        ctcuploadtype = (ctcuploadtype === undefined) ? 2 : ctcuploadtype;
        //End
        // AMAL END
        if (branch === '') {
            branch = "0";
        }

        if (true) { //Edited by Akshay on 15-10-2024
            //edited by megha removed month option

            //window.open('<?php echo $this->webroot; ?>Employee/downloadempctcformat/' + ctcuploadtype + '/' + branch + '/' + employee + '/' + month, '_blank');
            // window.open('<?php echo $this->webroot; ?>Employee/downloadempctcformat/' + ctcuploadtype + '/' + branch + '/' + arrear + '/' + payout + '/' + startdate + '/' + employee, '_blank' + '/' + approvedBy);
            //Edited by Akshay on 19-10-2024
            window.open('<?php echo $this->webroot; ?>Employee/downloadempctcformat/' +
                (ctcuploadtype || 'undefined') + '/' +
                (branch || 'undefined') + '/' +
                (arrear || 'undefined') + '/' +
                (payout || 'undefined') + '/' +
                (startdate || 'undefined') + '/' +
                (employee || 'undefined') + '/' +
                (approvedBy || 'undefined'), '_blank');
            //End
        } else {
            return false;
        }







    }

    function uploadEmployeeCTC() {


        var form = $('#importemployeectcform');
        var fileSelect = document.getElementById('empctccsv');
        var ctcuploadtype = ($('#ctc_upload_type').val() === undefined) ? 2 : $('#ctc_upload_type').val(); //Edited by Akshay on 19-10-2024
        var files = fileSelect.files;
        var approvedBy = $('#approved_by').val(); //Edited by Akshay on 21-10-2024
        // alert(files);

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
        xhr.open('POST', livesite + 'Employee/uploadandsaveempctc/' + ctcuploadtype + '/' + approvedBy, true); //Edited by Akshay on 21-10-2024

        // Set up a handler for when the request finishes.
        xhr.onload = function() {
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


    jQuery(document).ready(function() {

        filterEmployees();
        $("#filterby_branch").select2();
        $("#filterby_month").select2();

        var employee = $('#attendanceuploadfilter #emp_fkey').val();

        $('#att_table').datagrid({
            url: livesite + "Employee/employeelist",
            pagination: true,
            autoRowHeight: false,
            singleSelect: true,
            PostsearchFilter: true,
            rownumbers: true,
            pageSize: 10,
            width: '100%',
            queryParams: {
                employee: employee
            },
            toolbar: [{
                    text: 'New',
                    iconCls: 'icon-add',
                    handler: function() {
                        showModalForm(livesite + 'Employee/form')
                    }
                },
                //commented by megha on 10/08/2019 delete button not needed
                //                 '-', {
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
            pageList: [2, 5, 10],
            columns: [
                [
                    //{field: 'emp_ctc_upload_pkey', title: '', width: "20%"},
                    {
                        field: 'empname',
                        title: 'Employee Name',
                        width: "18%"
                    },
                    {
                        field: 'empid',
                        title: 'Employee ID',
                        width: "12%"
                    },
                    {
                        field: 'emp_anual_ctc',
                        title: 'Annual Gross Salary',
                        width: "13.5%"
                    },
                    //  {field:'emp_loan_balance',title:'Loan Balance ',width:'20%'},
                    //  {field:'emp_advance',title:'Advance',width:'20%'},
                    //   {field:'emp_tds_deducted',title:'Deducted',width:'20%'},
                    // edited by anukrishnan_17-02-2025 open
                    // {
                    //     field: 'start_date_effective',
                    //     title: 'Start Date Effective',
                    //     width: '13%'
                    // },
                    // edited by anukrishnan_17-02-2025_close
                    { //edited by anukrishnan_01-02-2025 open
                        field: 'next_increment_date',
                        title: 'Next Increment Date',
                        width: '13%'
                    }, //edited by anukrishnan_01-02-2025 close
                    {
                        field: 'arrear_salary',
                        title: 'Arrear Salary Process',
                        width: '14%'
                    },
                    {
                        field: 'pay_out_month',
                        title: 'Pay Out Month',
                        width: '10%'
                    }, //Edited by Akshay on 10-10-2024
                    {
                        field: 'approved_by',
                        title: 'Approved by',
                        width: '14%'
                    }, //Edited by Akshay on 10-10-2024
                    {
                        field: 'status',
                        title: 'Status',
                        width: '12%'
                    }, //Edited by Akshay on 10-10-2024
                    //                    {field: 'end_date_effective', title: 'End Date Effective', width: '14%'},
                ]
            ],

            onSearch: function(s) {

                $('#att_table').datagrid('load', {
                    emp: $('#searchqupo').val()
                });
            }, onLoadSuccess: function() {
                console.log("Loaded data:", data);
                setwidth();
            }
        });
    });
</script>