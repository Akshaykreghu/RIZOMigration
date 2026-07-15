<style>
    .datagrid .panel-body
    {
        width: 100% !important;
    }
</style>
<section class="content-header">
    <h1>Manage Expenses  <?php // echo $emp_leave_count;       ?></h1>
</section>
<!-- Main content -->
<section class="content">
    <div class="row">
        <div class="col-md-12">
            <!-- Leave Requests -->
            <!-- DIRECT CHAT DANGER -->
            <div class="box ">
<!--                <div class="box-header with-border">
                    <div class="box-tools pull-right">
                        <button class="btn btn-box-tool" data-widget="collapse">
                            <i class="fa fa-minus"></i>
                        </button>
                        <button class="btn btn-box-tool" >
                            <i class="fa fa-times"></i>
                        </button>
                    </div>
                </div> /.box-header -->
                <div class="box-body">

                    <div class="tabset-attendanceregister">
                        <!--                        <div id="tab0" data-pws-tab="tab0" data-pws-tab-name="To be Verify">
                                                    <h3>Leave Requests</h3>
                                                </div>-->
                        <div id="tab1" data-pws-tab="tab1" data-pws-tab-name="To be Verify">
                            <div style="text-align: right" class="row form-inline">
                                    <label class="col-md-9 col-form-label"><b>Choose Project :</b></label>
                                    <select class="col-md-3 form-control" style="width: 250px;" id="project" name="project" class="form-control js-example-basic-single" onchange="filterfunction_pro();">
                                            <option value="">All</option>
                                            <?php foreach ($vendor_list as $list) {
                                             foreach ($list as $l) {
                                            ?>
                                            <option value="<?php echo $l["site_pkey"] ?>"><?php echo $l["site_name"].'-'.$l["site_id"] ?></option>
                                            <?php
                                        }
                                    }?>
                                        </select>
                                </div>
                            <br>
                            <table id="empleaverequeststable" class="table table-bordered table-hover">

                            </table>
                        </div>
                        <div id="tab2" data-pws-tab="tab2" data-pws-tab-name="Verified" data-pws-tab-icon="fa-video-camera">
                            <div style="text-align: right" class="row form-inline">
                                    <label class="col-md-5 col-form-label"><b>Choose Project :</b></label>
                                    <select class="col-md-2 form-control" style="width: 250px;" id="project1" name="project1" class="form-control js-example-basic-single" onchange="filterfunction();">
                                            <option value="">All</option>
                                            <?php foreach ($vendor_list as $list) {
                                             foreach ($list as $l) {
                                            ?>
                                            <option value="<?php echo $l["site_pkey"] ?>"><?php echo $l["site_name"].'-'.$l["site_id"] ?></option>
                                            <?php
                                        }
                                    }?>
                                        </select>
                                    <label class="col-md-2 col-form-label"><b>Choose Expense Status :</b></label>
                                    <select class="col-md-2 form-control" style="width: 250px;" id="expense_status" name="expense_status" onchange="filterfunction(this);">
                                        <option value="'Approved','Rejected'">All</option>
                                        <option value="'Approved'">Approved</option>
                                        <option value="'Rejected'">Rejected</option>
                                    </select>
                                </div>
                            <br>
                            <table id="empleaverequeststableverified" class="table table-bordered table-hover">

                            </table>
                        </div>
                    </div>


                </div><!-- /.box-body -->
            </div><!--/.direct-chat -->
        </div><!-- /.col -->
    </div>
</section>
<script>
    function filterfunction(obj) {
        $('#empleaverequeststableverified').datagrid('load', {
            emp: $('#expense_status').val(),
            project: $('#projec1').val()
        });
//        $('#empleaverequeststable').datagrid('load', {
//            project: $('#project').val()
//        });
    }
    function filterfunction(obj) {//This is the approved/rejected filtering
        $('#empleaverequeststableverified').datagrid('load', {
            emp: $('#expense_status').val(),
            project: $('#project1').val()
        });
    }
    function filterfunction_pro() {//This is the approved/rejected filtering
        $('#empleaverequeststable').datagrid('load', {
            project: $('#project').val()
        });
    }
    jQuery(document).ready(function () {

//        $('#tab1').on('change',function () {
////            $('#expense_status').val("'Approved','Rejected'");
//            $('#expense_status option[0]').attr('selected','selected');
//            reloadTable('empleaverequeststableverified');
////            $('#empleaverequeststableverified').datagrid('load', {
//////                emp: $('#expense_status').val(),
////            });
//        });
        $('.tabset-attendanceregister').pwstabs({
            effect: 'scale', // You can change effects of your tabs container: scale / slideleft / slideright / slidetop / slidedown / none
            defaultTab: 1, // The tab we want to be opened by default
            containerWidth: '100%', // Set custom container width if not set then 100% is used
            tabsPosition: 'horizontal', // Tabs position: horizontal / vertical
            horizontalPosition: 'top', // Tabs horizontal position: top / bottom
            verticalPosition: 'left', // Tabs vertical position: left / right
            responsive: false, // Make tabs container responsive: true / false - boolean
            theme: '',
            rtl: false                    // Right to left support: true/ false
        });

        $('.pws_tabs_controll>li').click(function () {
            $("#expense_status").val("'Approved','Rejected'").children("option:selected");
            filterfunction();
        });

        $('#empleaverequeststable').datagrid({
            url: livesite + "ProjectExpenses/listempexpense",
            pagination: true,
            singleSelect: true,
            rownumbers: true,
//            PostsearchFilter: true,
            onLoadSuccess: function (data) {
                loadtabs();
            },
            toolbar: [
                {
                    iconCls: 'icon-edit',
                    text: 'Manage Expense',
                    handler: function () {
                        var row = $('#empleaverequeststable').datagrid('getSelected');
                        if (row == null) {
                            alert('Please select any data');
                            return false;
                        }
                        var $expenseId = row.emp_expenses_pkey;
                        showLargeModalForm(livesite + 'ProjectExpenses/manageexpense/' + row.emp_expenses_pkey);
                    }
                 },'-', { 
                    iconCls: 'icon-edit',
                    text: 'View Details',
                    handler: function () {
                        var row = $('#empleaverequeststable').datagrid('getSelected');
                        if (row == null) {
                            alert('Please select any data');
                            return false;
                        } 
                        var $expenseId = row.emp_expenses_pkey;
                        showLargeModalForm(livesite + 'ProjectExpenses/view_expense/' + row.emp_expenses_pkey);
                    }
                }
            ],
            fitColumns: true,
            pageList: [2, 5, 10],
            columns: [
                [
//                    {field: 'emp_name', title: 'Employee Name', width: "20%"},
//                    {field: 'expense_date', title: 'Expense Date', width: "10%"},
//                    {field: 'expenses_amount', title: 'Amount', width: "10%"},
//                    {field: 'expense_type', title: 'Expense Type', width: "20%"},
//                    {field: 'remarks', title: 'Remarks', width: "30%"},
//                    {field: 'expense_status', title: 'Status', width: "10%"}
                    {field: 'expense_id', title: 'Request ID', width: "15%"},
                    {field: 'project', title: 'Project Name', width: "25%"},
		    {field: 'beneficiary', title: 'Beneficiary Name', width: '19%'},
		    {field: 'expense_date', title: 'Expense Date', width: '10%'},
                    //{field: 'purpose', title: 'Purpose', width: "15%"},
                    {field: 'remarks', title: 'Remarks', width: '20%'},
                    {field: 'expense_status', title: 'Status', width: "10%"}
           ]],
            onSearch: function (s) {
                $('#empleaverequeststable').datagrid('load', {
                    project: $('#project').val(),
                });
            }
        });

        $('#empleaverequeststableverified').datagrid({
            url: livesite + "ProjectExpenses/listempexpenseverified",
            pagination: true,
            rownumbers: true,
            singleSelect: true,
//            PostsearchFilter: true,
            toolbar: [
//                {
//                    iconCls: 'icon-edit',
//                    text: 'Manage Expense',
//                    handler: function () {
//                        var row = $('#empleaverequeststableverified').datagrid('getSelected');
//                        if (row == null) {
//                            alert('Select any data');
//                            return false;
//                        }
//                        var $expenseId = row.emp_expenses_pkey;
//                        showLargeModalForm(livesite + 'ProjectExpenses/manageexpense/' + row.emp_expenses_pkey);
//                    }
//                }
{ 
                    iconCls: 'icon-edit',
                    text: 'View Details',
                    handler: function () {
                        var row = $('#empleaverequeststableverified').datagrid('getSelected');
                        if (row == null) {
                            alert('Please select any data');
                            return false;
                        } 
                        var $expenseId = row.emp_expenses_pkey;
                        showLargeModalForm(livesite + 'ProjectExpenses/view_expense/' + row.emp_expenses_pkey);
                    }
                }
            ],
            fitColumns: true,
            pageList: [2, 5, 10],
            columns: [
                [
//                    {field: 'emp_name', title: 'Employee Name', width: "20%"},
//                    {field: 'expense_date', title: 'Expense Date', width: "10%"},
//                    {field: 'expenses_amount', title: 'Amount', width: "10%"},
//                    {field: 'expense_type', title: 'Expense Type', width: "20%"},
//                    {field: 'remarks', title: 'Remarks', width: "30%"},
//                    {field: 'expense_status', title: 'Status', width: "10%"}
                    {field: 'expense_id', title: 'Request ID', width: "15%"},
                    {field: 'project', title: 'Project Name', width: "25%"},
		    {field: 'beneficiary', title: 'Beneficiary Name', width: '19%'},
		    {field: 'expense_date', title: 'Expense Date', width: '10%'},
                    //{field: 'purpose', title: 'Purpose', width: "15%"},
                    {field: 'remarks', title: 'Remarks', width: '20%'},
                    {field: 'expense_status', title: 'Status', width: "10%"}
                ]
            ],
            onSearch: function (s) {
                $('#empleaverequeststableverified').datagrid('load', {
                    emp: $('#expense_status').val(),
                   // project: $('#project1').val(),
//                    name: $('#rsndempid').val(),
//                    branch: $('#filterby_branch').val()
                });
            }
        });
        function loadtabs() {
            $('[data-tab-id="tab1"]').trigger('click');
        }
    });
</script>