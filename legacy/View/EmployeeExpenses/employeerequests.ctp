
<style>
    .heading {
        display: flex;
        flex-direction: row;
        align-items: end;
        justify-content: space-between;
        /* margin: 0 15px; */
        /* padding: 15px 0 !important; */
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
</style>
<section class="content-header heading">
    <h1 class="text-primary-18">My Expense Request</h1>
      <div class="text-primary-16 home" style="display:flex; align-items:center; gap:10px; cursor:pointer;">
        <i class="fa" style="font-size:16px;">&#xf104;</i>
        Back
    </div>
</section>
<!-- Main content -->
<section class="content">
    <div class="row">
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
            </div>
            <!--/.direct-chat -->
        </div><!-- /.col -->
    </div>
</section>
<script>
    jQuery(document).ready(function() {
        $('#myleaverequeststable').datagrid({
            url: livesite + "EmployeeExpenses/viewrequest",
            pagination: true,
            singleSelect: true,
            rownumbers: true,
            onLoadSuccess: function(data) {

                loadtabs();
            },
            toolbar: [{
                text: 'New',
                iconCls: 'icon-add',
                handler: function() {
                    showModalForm(livesite + 'EmployeeExpenses/form')
                }
            }, {
                iconCls: 'icon-edit',
                text: 'View',
                handler: function() {
                    var row = $('#myleaverequeststable').datagrid('getSelected');
                    if (row != null) {
                        var $expenseId = row.emp_expenses_pkey;
                        showLargeModalForm(livesite + 'EmployeeExpenses/viewexpense/' + $expenseId);
                    } else {
                        alert("Please select any data");
                    }
                }
            }, {
                iconCls: 'icon-remove',
                text: 'Remove',
                handler: function() {
                    var rows = $('#myleaverequeststable').datagrid('getSelections');
                    if (rows.length > 0) {
                        var str_ids = "";
                        var appliedCount = 0;
                        for (var i = 0; i < rows.length; i++) {
                            var data = rows[i];
                            if (data.expense_status != "Applied") {
                                appliedCount = 1;
                                continue;
                            }
                            if (str_ids == "") {
                                str_ids += data.emp_expenses_pkey;
                            } else {
                                str_ids += "," + data.emp_expenses_pkey;
                            }
                        }
                        if(!str_ids && appliedCount) {
                            alert(" Applied status expenses are only removable.");
                            return false;
                        }
                        if (confirm("Do you want to delete the selected Record(s)?")) {
                            $.ajax({
                                url: livesite + "EmployeeExpenses/deleteEmployee",
                                data: {
                                    ids: str_ids
                                },
                                success: function(response) {
                                    //var text = response.responseText;
                                    // process server response here
                                    reloadTable('myleaverequeststable')
                                }
                            });
                        }
                    } else {
                        alert("Please select any data");
                    }
                }
            }],
            fitColumns: true,
            pageList: [2, 5, 10, 50, 100],
            columns: [
                [{
                        field: 'created_date',
                        title: 'Applied Date & Time',
                        width: "14%"
                    },
                    {
                        field: 'expenses_amount',
                        title: 'Expense Amount',
                        width: "10%"
                    },
                    {
                        field: 'expense_type',
                        title: 'Expense Type',
                        width: "10%"
                    },
                    {
                        field: 'expense_date',
                        title: 'Expense Date',
                        width: "10%"
                    },
                    {
                        field: 'emp_name',
                        title: 'Authorized By',
                        width: "15%"
                    },
                    {
                        field: 'emp_name2',
                        title: 'Approved By',
                        width: "15%"
                    },
                    {
                        field: 'remarks',
                        title: 'Remarks',
                        width: "15%"
                    },
                    {
                        field: 'expense_status',
                        title: 'Status',
                        width: "10%"
                    }
                ]
            ]
        });
    });

       /* edited by bindu 20-02-26 */
   $(".home").on("click", function () {

    $("#container").isLoading({
        text: "Loading",
        position: "overlay",
    });

    let url = "";
    var userGroup=<?php echo json_encode($user_group); ?>

    if (userGroup == "1") {
        url = livesite + "EmployeeManage/index";
    } 
    else if (userGroup == "2") {
        url = livesite + "EmployeeMenu/index";
    }

    $("#container").load(url, function () {
        isDashboardShown = false;
    });

});

	/* edited by bindu 20-02-26 */
</script>