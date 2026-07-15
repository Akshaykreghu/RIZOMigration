<style>
    .form-horizontal .control-label{
        text-align: left;
    }
</style>
<section class="content-header">
    <h1 style="text-align:left; font-size: 3em;">My Expense Request</h1>
</section>
<!-- Main content -->

<section class="content">
    <div class="row">
            <div class="col-md-12">
<div class="box">
      
    <div id="newreqeuest">
    </div>  
      </div>
</div>
        <div class="col-md-12">
            
            <!-- My Leave Requests -->
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
                    <table id="myleaverequeststable" class="table table-bordered table-hover">

                    </table>
                </div><!-- /.box-body -->
            </div><!--/.direct-chat -->
        </div><!-- /.col -->
    </div>
</section>
<script>
    jQuery(document).ready(function () {
        $('#myleaverequeststable').datagrid({ 
            url: livesite + "ProjectExpenses/viewrequest",
            pagination: true,
            singleSelect: true,
            rownumbers: true,
            onLoadSuccess: function (data) {

                //loadtabs();
            },
            toolbar: [{
                    text: 'New',
                    iconCls: 'icon-add',
                    handler: function () {
                       // showModalForm(livesite + 'ProjectExpenses/form')
                        $('#newreqeuest').html('<div class="col-md-12" style="text-align:center; "><li class="fa fa-spinner fa-spin" style="font-size:30px;text-align: center;"></li></div>');
                        $('#newreqeuest').load(livesite+'ProjectExpenses/loadnew');
                    }
//                }, {
//                    iconCls: 'icon-edit',
//                    text: 'View',
//                    handler: function () {
//                        var row = $('#myleaverequeststable').datagrid('getSelected');
//                        if (row != null) {
//                            var $expenseId = row.emp_expenses_pkey;
//                            showLargeModalForm(livesite + 'ProjectExpenses/viewexpense/' + $expenseId);
//                        } else {
//                            alert("Please select any data");
//                        }
//                    }
                    },'-', { 
                    iconCls: 'icon-edit',
                    text: 'View Details',
                    handler: function () {
                        var row = $('#myleaverequeststable').datagrid('getSelected');
                        if (row == null) {
                            alert('Please select any data');
                            return false;
                        } 
                        var $expenseId = row.emp_expenses_pkey;
                        showLargeModalForm(livesite + 'ProjectExpenses/view_expense/' + row.emp_expenses_pkey);
                    }
                }],
            fitColumns: true,
            pageList: [2, 5, 10],
            columns: [
                [
//                    {field: 'created_date', title: 'Applied Date & Time', width: "20%"},
//                    {field: 'expenses_amount', title: 'Expense Amount', width: "10%"},
////                    {field: 'affected_month', title: 'Affected Month', width: "10%"},
//                    {field: 'expense_date', title: 'Expense Date', width: "10%"},
//                    {field: 'emp_name', title: 'Approved By', width: "20%"},
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
            ]
        });
    });
</script>