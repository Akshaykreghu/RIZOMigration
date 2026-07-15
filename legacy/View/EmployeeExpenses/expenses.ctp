<style>
    .form-horizontal .control-label {

        text-align: left;

    }

    /* edited by bindu 24-10-25 */
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
    }

    /* edited by bindu 24-10-25 */
</style>

<section class="content-header heading">

    <!-- /* edited by bindu 24-10-25 */ -->
    <h1 class="text-primary-18"> Employee Expenses</h1>
      <?php if ($plan !== 'basic') : ?>
    
    <div class="text-primary-16 home"
         style="display:flex; align-items:center; gap:10px; cursor:pointer;">
        <i class="fa" style="font-size:16px;">&#xf104;</i>
        Back
    </div>

<?php endif; ?>
    <!-- end -->

</section>
<!-- Main content -->
<section class="content">
    <div class="row">
        <div class="col-md-12">
            <div class="box ">
                <br>
                <div class="box-body">
                    <!-- Employee import form -->
                    <form class="form-horizontal" method="post" action="" id="importemployeectcform">
                        <div class="row">
                            <div class="form-group">
                                <div class="col-sm-4">
                                    <label class="col-sm-5 control-label" for="filterby_branch">Choose Branch</label>
                                    <div class="col-md-7">
                                        <select id="filterby_branch" name="filterby_branch" class="form-control js-example-basic-single" onchange="filterAttendanceupload(this);">
                                            <!-- //edited by anukrishnan_29-01-2025 open-->
                                            <?php //if ($is_ho == 1 || $user_group != 2) { 
                                            ?>
                                            <option value="">All</option>
                                            <?php //} 
                                            ?>
                                            <!-- //edited by anukrishnan_29-01-2025 close-->
                                            <?php foreach ($arr_branches as $key => $value) { ?>
                                                <option value="<?php echo $value['Units']['branch_code']; ?>"><?php echo $value['Units']['branch_name']; ?></option>
                                            <?php } ?>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <label class="col-sm-5 control-label" for="employee">
                                        Choose Employee
                                    </label>

                                    <div class="col-md-7">

                                        <?php
                                         $company_code = strtoupper($this->Session->read('company_code'));
                                        $allowed_companies = array('GLET');
                                       $user_group = $this->Session->read('user_group');

                                        if ($user_group == 2) {
                                        ?>

                                            <select id="emp_fkey"
                                                class="form-control js-example-basic-single"
                                                name="emp_fkey"
                                                onchange="filterAttendanceupload(this);">

                                                <option value="">All</option>

                                                <?php
                                                foreach ($arr_employees as $value) {

                                                    $emp = isset($value['EmployeeDetails'])
                                                        ? $value['EmployeeDetails']
                                                        : $value;
                                                ?>

                                                    <option value="<?php echo $emp['emp_pkey']; ?>">
                                                        <?php echo $emp['first_name'] . ' ' . $emp['last_name']; ?>
                                                    </option>

                                                <?php } ?>

                                            </select>

                                        <?php
                                        } else {
                                        ?>

                                            <select id="emp_fkey"
                                                class="form-control js-example-basic-single"
                                                name="emp_fkey"
                                                onchange="filterAttendanceupload(this);">
                                            </select>

                                        <?php } ?>

                                    </div>
                                </div>
                            </div>
                        </div>
                    </form>
                    <div class="box-body" id="maintabtest">
                        <!--<input type="button" onclick="checktab();" id="checktab" value="Check">-->
                        <div class="tabset-attendanceregister">
                            <!--                        <div id="tab0" data-pws-tab="tab0" data-pws-tab-name="To be Verify">
                                                        <h3>Leave Requests</h3>
                                                    </div>-->
                            <div id="tab1" data-pws-tab="tab1" data-pws-tab-name="To be Verify">
                                <table id="att_table" class="table table-bordered table-hover">
                                    <tbody>
                                    </tbody>
                                </table>
                            </div>
                            <div id="tab2" data-pws-tab="tab2" data-pws-tab-name="Verified" data-pws-tab-icon="fa-video-camera">
                                <div style="text-align: right" class="row form-inline">
                                    <label class="col-md-9 col-form-label"><b>Choose Expense Status :</b></label>
                                    <select class="col-md-3 form-control" style="width: 250px;" id="expense_status" name="expense_status" onchange="filterfunction(this);">
                                        <option value="'Authorized','Approved','Rejected','Removed'" selected>All</option>
                                        <option value="'Authorized'">Authorized</option>
                                        <option value="'Approved'">Approved</option>
                                        <option value="'Rejected'">Rejected</option>
                                        <option value="'Removed'">Removed</option>
                                    </select>
                                </div>
                                <br>
                                <table id="att_table_verified" class="table table-bordered table-hover">

                                </table>
                            </div>
                        </div>
                    </div><!-- /.box-body -->
                </div>

            </div>
        </div>
    </div>
</section>
<!-- <script>
    function filterEmployees() {

    var branch = $('#filterby_branch').val();

    var allowed_companies = ['GLET'];

    var ajaxUrl = "";

    if (allowed_companies.indexOf(company_code) !== -1) {
        // Special companies
        ajaxUrl = livesite + "EmployeeExpenses/jsons/" + branch;
    } else {
        // Normal companies
        ajaxUrl = livesite + "Employee/jsons/" + branch;
    }

    // Destroy old select2 if exists
    if ($("#emp_fkey").hasClass("select2-hidden-accessible")) {
        $("#emp_fkey").select2('destroy');
    }

    $("#emp_fkey").select2({
        placeholder: "All",
        allowClear: true,
        ajax: {
            url: ajaxUrl,
            dataType: 'json',
            delay: 250,
            data: function(params) {
                return {
                    q: params.term,
                    page: params.page || 1
                };
            },
            processResults: function(data, params) {
                params.page = params.page || 1;

                return {
                    results: data.items,
                    pagination: {
                        more: (params.page * 30) < data.total_count
                    }
                };
            }
        },
        minimumInputLength: 1
    });
}
</script> -->
<script>
    var user_group = <?php echo (int)$this->Session->read('user_group'); ?>;
</script>
<script>
    // filtter using branch 
    function filterEmployees(branch) {
        var branch = $('#filterby_branch').val();
        if (user_group == 2) {
        // Group 2 → Call EmployeeExpenses controller
        ajaxUrl = livesite + "EmployeeExpenses/jsons/" + branch;
    } else {
        // Others → Normal employee listing
        ajaxUrl = livesite + "Employee/jsons/" + branch;
    }
        $("#emp_fkey").select2({
            //closeOnSelect:false,
            placeholder: "All",
            allowClear: true,
            ajax: {
                url:ajaxUrl,
                dataType: 'json',
                delay: 250,
                data: function(params) {
                    return {
                        q: params.term, // search term
                        page: params.page
                    };
                },
                processResults: function(data, params) {
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

    function filterfunction(obj) { //This is the approved/rejected filtering
        $('#att_table_verified').datagrid('load', {
            emp: $('#expense_status').val(),
            branch: $('#importemployeectcform #filterby_branch').val(),
            employee: $('#importemployeectcform #emp_fkey').val()
        });
    }

    function filterAttendanceupload(obj) { //This is the branch/emp filtering
        var branch = $('#importemployeectcform #filterby_branch').val();
        var employee = $('#importemployeectcform #emp_fkey').val()

        var current_tab = $('.pws_tab_active').attr('data-tab-id');
        if (current_tab == "tab1") {

            $('#att_table').datagrid('load', {
                emp: $('#expense_status').val(),
                branch: branch,
                employee: employee,
            });
        } else {
            $('#att_table_verified').datagrid('load', {
                emp: $('#expense_status').val(),
                branch: branch,
                employee: employee,
            });
        }
        filterEmployees();

    }

    jQuery(document).ready(function() {
        $('.tabset-attendanceregister').pwstabs({
            effect: 'scale', // You can change effects of your tabs container: scale / slideleft / slideright / slidetop / slidedown / none
            defaultTab: 1, // The tab we want to be opened by default
            containerWidth: '100%', // Set custom container width if not set then 100% is used
            tabsPosition: 'horizontal', // Tabs position: horizontal / vertical
            horizontalPosition: 'top', // Tabs horizontal position: top / bottom
            verticalPosition: 'left', // Tabs vertical position: left / right
            responsive: false, // Make tabs container responsive: true / false - boolean
            theme: '',
            rtl: false // Right to left support: true/ false
        });


        $('.pws_tabs_controll>li').click(function() {
            $('#filterby_branch').val('').children("option:selected");
            $('#emp_fkey').val('').children("option:selected");
            $("#expense_status").val("'Approved','Rejected'").children("option:selected");
            filterAttendanceupload();
            filterEmployees();
        });

        filterEmployees();
        //        $("#filterby_branch").select2();
        //        $("#filterby_month").select2();

        var employee = $('#attendanceuploadfilter #emp_fkey').val();

        $('#att_table').datagrid({
            url: livesite + "EmployeeExpenses/employeelist",
            pagination: true,
            singleSelect: true,
            rownumbers: true,
            onLoadSuccess: function(data) {
                loadtabs();
            },
            queryParams: {
                employee: employee
            },
            toolbar: [{
                text: 'New',
                iconCls: 'icon-add',
                handler: function() {
                    showModalForm(livesite + 'EmployeeExpenses/form')
                }
            }, {
                //    iconCls: 'icon-remove',
                //    text: 'Remove',
                //    handler: function() {
                //        var rows = $('#att_table').datagrid('getSelections');
                //        if (rows.length > 0) {
                //            var str_ids = "";
                //            for (var i = 0; i < rows.length; i++) {
                //                var data = rows[i];
                //                if (str_ids == "") {
                //                    str_ids += data.emp_expenses_pkey;
                //                } else {
                //                   str_ids += "," + data.emp_expenses_pkey;
                //               }
                //            }
                //            if (confirm("Do you want to delete the selected Record(s)?")) {
                //                $.ajax({
                //                    url: livesite + "EmployeeExpenses/deleteEmployee",
                //                   data: {
                //                        ids: str_ids
                //                    },
                //                    success: function(response) {
                //var text = response.responseText;
                // process server response here
                //                        reloadTable('att_table')
                //                    }
                //                });
                //            }
                //        } else {
                //            alert("Please select any data");
                //        }
                //    }
                //}, {
                iconCls: 'icon-edit',
                text: 'Manage Expense',
                handler: function() {
                    var row = $('#att_table').datagrid('getSelected');
                    if (row == null) {
                        alert('Please select any data');
                        return false;
                    }
                    var $expenseId = row.emp_expenses_pkey;
                    showLargeModalForm(livesite + 'EmployeeExpenses/manageexpense/' + row.emp_expenses_pkey);
                }
            }],
            fitColumns: true,
            pageList: [2, 5, 10, 50, 100],
            columns: [
                [
                    //{field: 'emp_expenses_pkey', title: '', width: "%"},
                    {
                        field: 'empname',
                        title: 'Employee Name',
                        width: "20%"
                    },
                    {
                        field: 'empid',
                        title: 'Employee ID',
                        width: "20%"
                    },
                    //edited by amal heading change on 15/08/2019
                    //{field: 'expenses_amount', title: 'Expenses amount', width: "20%"},
                    {
                        field: 'expenses_amount',
                        title: 'Amount',
                        width: "20%"
                    },
                    {
                        field: 'affected_month',
                        title: 'Expense Date',
                        width: '20%'
                    },
                    {
                        field: 'remarks',
                        title: 'Remark',
                        width: '20%'
                    },
                    //  {field: 'is_credited', title: 'Credited Rate', width: '10%'},
                ]
            ]
        });
        $('#att_table_verified').datagrid({
            url: livesite + "EmployeeExpenses/employeeverifiedlist",
            pagination: true,
            singleSelect: true,
            rownumbers: true,
            queryParams: {
                employee: employee
            },
            toolbar: [{
                    iconCls: 'icon-edit',
                    text: 'Manage Expense',
                    handler: function() {
                        var row = $('#att_table_verified').datagrid('getSelected');
                        if (row == null) {
                            alert('Please select any data');
                            return false;
                        }
                        var $expenseId = row.emp_expenses_pkey;
                        showLargeModalForm(livesite + 'EmployeeExpenses/manageexpense/' + row.emp_expenses_pkey);
                    }
                },
                // {
                //     iconCls: 'icon-edit',
                //     text: 'Test mail',
                //     handler: function() {
                //         $.ajax({
                //                 url: livesite + "Site/mailSend",
                //                 data: {
                //                     ids: str_ids
                //                 },
                //                 success: function(response) {
                //                     //var text = response.responseText;
                //                     // process server response here\
                //                     alert('Mail Send');
                //                 }
                //             });
                //         }
                //     }
            ],
            fitColumns: true,
            pageList: [2, 5, 10, 50, 100],
            columns: [
                [
                    //{field: 'emp_expenses_pkey', title: '', width: "%"},
                    {
                        field: 'empname',
                        title: 'Employee Name',
                        width: "20%"
                    },
                    {
                        field: 'empid',
                        title: 'Employee ID',
                        width: "15%"
                    },
                    //edited by amal heading change on 15/08/2019
                    //{field: 'expenses_amount', title: 'Expenses amount', width: "20%"},
                    {
                        field: 'expenses_amount',
                        title: 'Amount',
                        width: "15%"
                    },
                    {
                        field: 'affected_month',
                        title: 'Expense Date',
                        width: '15%'
                    },
                    {
                        field: 'remarks',
                        title: 'Remark',
                        width: '18%'
                    },
                    {
                        field: 'expense_status',
                        title: 'Status',
                        width: '15%'
                    },
                    //  {field: 'is_credited', title: 'Credited Rate', width: '10%'},
                ]
            ],
            onSearch: function(s) {
                $('#att_table_verified').datagrid('load', {
                    emp: $('#expense_status').val(),
                    //                    name: $('#rsndempid').val(),
                    //                    branch: $('#filterby_branch').val()
                });
            }
        });

        function loadtabs() {
            loadtabs = function() {};
            $('[data-tab-id="tab1"]').trigger('click');
        }
    });





















    //    function downloadEmployeeCTCForm() {
    //        var ctcuploadtype = $('#ctc_upload_type').val();
    //        if (ctcuploadtype == 1 || ctcuploadtype == 2) {
    //            window.open('<?php echo $this->webroot; ?>Employee/downloadempctcformat/' + ctcuploadtype, '_blank');
    //        } else {
    //            return false;
    //        }
    //    }
    //    function uploadEmployeeCTC() {
    //        var form = $('#importemployeectcform');
    //        var fileSelect = document.getElementById('empctccsv');
    //        var ctcuploadtype = $('#ctc_upload_type').val();
    //
    //        // The rest of the code will go here...
    //        var files = fileSelect.files;
    //        // Create a new FormData object.
    //        var formData = new FormData();
    //        // Loop through each of the selected files.
    //
    //        for (var i = 0; i < files.length; i++) {
    //            var file = files[i];
    //            // Add the file to the request.
    //            formData.append('empctc[]', file, file.name);
    //        }
    //
    //        // Set up the request.
    //        var xhr = new XMLHttpRequest();
    //
    //        // Open the connection.
    //        xhr.open('POST', livesite + 'Employee/uploadandsaveempctc/' + ctcuploadtype, true);
    //
    //        // Set up a handler for when the request finishes.
    //        xhr.onload = function () {
    //            if (xhr.status === 200) {
    //                // File(s) uploaded.
    //                var response = JSON.parse(xhr.responseText);
    //                if (response.success) {
    //                    $.notify(response.msg, {
    //                        type: 'success',
    //                        allow_dismiss: false
    //                    });
    //                } else {
    //                    $.notify(response.msg, {
    //                        type: 'error',
    //                        allow_dismiss: false
    //                    });
    //                }
    //            } else {
    //                alert("Employee CTC import failed, please check informations given or try again.");
    //            }
    //        };
    //        $('#attdatacsv').val('');
    //        $("#filterby_branch").select2("val", "");
    //        $("#emp_fkey").select2("val", "");
    //
    //        // Send the Data.
    //        xhr.send(formData);
    //    }

   

    $(".home").on("click", function () {

    $("#container").isLoading({
        text: "Loading",
        position: "overlay",
    });

    let url = "";
    var userGroup = <?php echo json_encode($this->Session->read('user_group')); ?>;

    if (userGroup == "1") {
        url = livesite + "PaymentApprovals/index";
    } 
    else if (userGroup == "2") {
        url = livesite + "EmployeeMenu/addon";
    }

    $("#container").load(url, function () {
        isDashboardShown = false;
    });

});
</script>