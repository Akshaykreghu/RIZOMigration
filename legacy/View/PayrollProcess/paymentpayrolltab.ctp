<style>
    /* CSS for expanded cell */
    .expanded-cell {
        white-space: normal;
        word-wrap: break-word;
        height: auto;
        z-index: 1;
        position: relative;
    }
</style>

<div class="row">
    <div class="col-md-12">

        <div class="alert alert-success" role="alert">
            <h4 class="alert-heading">Sending Emails!</h4>
            <p>
                <li class="fa fa-spinner fa-spin"></li> Please wait...
            </p>
        </div>

        <div class="datagrid-toolbar">
            <table cellspacing="0" cellpadding="0"  >
                <tbody>
                    <tr>
                        <?php if ($tab == 0) { ?>
                            <td>
                                <a href="javascript:void(0)" onclick="showModal();" class="l-btn l-btn-small l-btn-plain" group="" id="">
                                    <span class="l-btn-left l-btn-icon-left">
                                        <span class="l-btn-text">Process</span>
                                        <span class="l-btn-icon icon-ok">&nbsp;</span>
                                    </span>
                                </a>
                            </td>

                            <!-- Edited by Akshay on 21-9-2023 -->
                            <td>
                                <a href="javascript:void(0)" onclick="showReversePreAuditModal();" class="l-btn l-btn-small l-btn-plain" group="" id="">
                                    <span class="l-btn-left l-btn-icon-left">
                                        <span class="l-btn-text">Reject</span>
                                        <span class="l-btn-icon icon-cancel">&nbsp;</span>
                                    </span>
                                </a>
                            </td>
                            <td style="padding-left: 10px;">Select Employee : </td>
                        <td>
                            <select style="margin-left: 15px;" id="<?php echo $id; ?>" name="<?php echo $id; ?>" class="form-control" onchange="filterPayrollByEmployee(<?php echo $tab; ?>);">
                                <option value="">--All--</option>
                                <?php
                                foreach ($arr_employees as $value) {
                                    echo '<option value="' . $value['emp_details']['emp_pkey'] . '">' . $value['emp_details']['first_name'] . ' ' . $value['emp_details']['last_name'] . ' - ' . $value['emp_proff']['emp_company_id'] . '</option>';
                                }
                                ?>
                            </select>
                        </td>
                        <td>&nbsp;&nbsp;</td>
                        <td style="color: red; background-color: yellow; padding-left: 10px; font-weight: bold;">Total Employees : <?php echo $total; ?></td>
                        <td style="color: red; background-color: yellow; padding-left: 10px;padding-right: 10px; font-weight: bold;">Net Salary : <?php echo number_format(round($sum), 2); ?></td>

                        <?php
                            $id = 'filterby_employee';
                        } else {
                        ?>

                            <?php
                            $id = 'select_filterby_employee'; ?>
                            <td>
                                <a href="javascript:void(0)" onclick="sendSlipEmailtoPersons();" class="l-btn l-btn-small l-btn-plain" group="" id="">
                                    <span class="l-btn-left l-btn-icon-left">
                                        <span class="l-btn-text">Email Slip</span>
                                        <span class="l-btn-icon icon-ok">&nbsp;</span>
                                    </span>
                                </a>
                            </td>
                        <td style="padding-left: 10px;">Select Employee : </td>
                        <td>
                            <select style="margin-left: 15px;" id="<?php echo $id; ?>" name="<?php echo $id; ?>" class="form-control" onchange="filterPayrollByEmployee(<?php echo $tab; ?>);">
                                <option value="">--All--</option>
                                <?php
                                foreach ($arr_employees as $value) {
                                    echo '<option value="' . $value['emp_details']['emp_pkey'] . '">' . $value['emp_details']['first_name'] . ' ' . $value['emp_details']['last_name'] . ' - ' . $value['emp_proff']['emp_company_id'] . '</option>';
                                }
                                ?>
                            </select>
                        </td>
                        <td>&nbsp;&nbsp;</td>
                        <td style="color: red; background-color: yellow; padding-left: 10px; font-weight: bold;">Total Employees : <?php echo $total1; ?></td>
                        <td style="color: red; background-color: yellow; padding-left: 10px;padding-right: 10px; font-weight: bold;">Net Salary : <?php echo number_format(round($sum1), 2); ?></td>

                        <?php
                        }
                        ?>

                        


                    </tr>
                </tbody>
            </table>
        </div>
        <?php if ($tab == 0) { ?>
            <table id="payrolltable" class="table table-bordered table-hover">
                <thead>
                    <tr>
                        <!-- <th data-options="width:'5%'" field="chk_payroll" checkbox="true" checked="checked"></th> -->
                        <th data-options="field:'emp_name',width:'25%'" sortable="true">Employee Name</th>
                        <th data-options="field:'calander_days',width:'10%'" sortable="true">Calender Days</th>
                        <th data-options="field:'working_days',width:'10%'" sortable="true">Working Days</th>
                        <th data-options="field:'days_presant',width:'14%'" sortable="true">Present</th>
                        <th data-options="field:'days_leave',width:'15%'" sortable="true">Leave</th>
                        <th data-options="field:'loss_of_pay',width:'8%'" sortable="true">LOP</th>
                        <th data-options="field:'net_salary',width:'10%'">Net Salary</th>
                        <th data-options="field:'provisional_remarks',width:'30%'" sortable="true" data-toggle="expand" data-placement="top">Provisional Remarks</th>
                        <th data-options="field:'finance_remarks',width:'50%'" sortable="true" data-toggle="expand" data-placement="top">Finance Remarks</th>
                        <th data-options="field:'final_remarks',width:'320%'" sortable="true" data-toggle="expand" data-placement="top">Finalization Remarks</th>
                        <th data-options="field:'salary_remarks',width:'30%'" sortable="true" data-toggle="expand" data-placement="top">Salary Approval Remarks</th>
                    </tr>
                </thead>
            </table>
        <?php } else { ?>
            <table id="processedpayrolltable" class="table table-bordered table-hover" style=" overflow-y: auto;">
                <thead>
                    <tr>
                        <th data-options="width:'5%'" field="chk_processedpayroll" checkbox="true"></th>
                        <th field="btn_view_salaryslip" align="center" formatter="viewSalarySlip">View Slip</th>
                        <th data-options="field:'emp_name',width:'25%'" sortable="true">Employee Name</th>
                        <th data-options="field:'working_days',width:'10%'" sortable="true">Working Days</th>
                        <th data-options="field:'loss_of_pay',width:'6%'" sortable="true">LOP</th>
                        <th data-options="field:'monthly_amount',width:'10%'" sortable="true">Monthly CTC</th>
                        <th data-options="field:'gross_salary',width:'10%'" sortable="true">Gross Salary</th>
                        <th data-options="field:'net_salary',width:'10%'" sortable="true">Net Salary</th>
                        <!-- <th data-options="field:'last_month_net_salary',width:'10%'" sortable="true">Prev Salary</th>
                        <th data-options="field:'diff',width:'10%'" sortable="true">Difference</th> -->
                        <th data-options="field:'provisional_remarks',width:'30%'" sortable="true" data-toggle="tooltip" data-placement="top">Provisional Remarks</th>
                        <th data-options="field:'finance_remarks',width:'50%'" sortable="true" data-toggle="tooltip" data-placement="top">Finance Remarks</th>
                        <th data-options="field:'final_remarks',width:'320%'" sortable="true" data-toggle="tooltip" data-placement="top">Finalization Remarks</th>
                        <th data-options="field:'salary_remarks',width:'30%'" sortable="true" data-toggle="tooltip" data-placement="top">Salary Approval Remarks</th>
                        <th data-options="field:'payment_remarks',width:'30%'" sortable="true" data-toggle="tooltip" data-placement="top">Payment Approval Remarks</th>
                    </tr>
                </thead>
            </table>
        <?php } ?>
    </div>
</div>


<script>
    $(".alert-success").hide();
</script>
<?php if ($tab == 0) { ?>
    <script>
        var branch = $('#payrollfilter #filterby_branch').val();
        var employee = $('#filterby_employee').val();
        var month = $('#payrollfilter #filterby_month').val();
        $('#payrolltable').datagrid({
            url: livesite + "PayrollProcess/listpaymentapprovalpayroll",
            pagination: true,
            singleSelect: false,
            view: scrollview,
            autoRowHeight: true,
            pageSize: 150,
            rownumbers: true,
            width: '100%',
            height: '300px',
            queryParams: {
                branch: branch,
                employee: employee,
                month: month
            },
            fitColumns: true,
            pageList: [20, 50, 100, 150, 250, 500],
            //Resize active tab height
            //07 July 2018
            //add color for net salary =0,-ve // added by nimisha 26-04-2019
            rowStyler: function(index, row) {
                if (row.net_salary < '0' || row.net_salary === '0') {
                    return 'background-color:red;color:white;';
                    //                return 'color:#B22222;';
                }
            },
            onLoadSuccess: function(data) {
                resizePWSTabContentDiv(1);
            }
        });
        $('#filterby_employee').select2(); //This is to search employee name or company id. By ***ARUL P DAS on 20/12/2019

        function showModal() {
            // var row = $('#payrolltable').datagrid('getSelected');
            var row = $('#payrolltable').datagrid('getRows');
            if (row.length > 0) {
                // Show the modal
                showSmallModalForm(livesite + "PayrollProcess/addremarks5/0");
            } else {
                // alert('Please select a row.');
                alert('There is no payroll to process.');
                return; // Prevent further execution of the function
            }
        }

        //Edited by Akshay on 21-9-2023
        function showReversePreAuditModal() {
            var canRemove = true;
            // var row = $('#payrolltable').datagrid('getSelected');
            var row = $('#payrolltable').datagrid('getRows');

            if (row.length > 0) {
                // var checkedRows = $('#payrolltable').datagrid('getChecked');
                var checkedRows = $('#payrolltable').datagrid('getRows');

                var payrollPkeys = checkedRows.map(function(row) {
                    return row.payroll_master_pkey;
                });

                var pmPkey = '';
                for (var register in checkedRows) {


                    pmPkey += checkedRows[register].payroll_master_pkey + ',';

                    if (checkedRows[register]['action'] == 'Approved') {
                        canRemove = false;
                        break;
                    }
                }
                if (canRemove) {
                    // Show the modal
                    showSmallModalForm(livesite + "PayrollProcess/addrejectremarks/" + pmPkey + '/salaryapproval');

                } else {
                    $.notify("You cannot remove an approved payroll entry!", {
                        type: 'danger',
                        allow_dismiss: false
                    });
                    return;
                }
            } else {
                // alert('Please select a row.');

                return; // Prevent further execution of the function
            }
        }

        function sendSlipEmailtoPersons() {
            var row = $('#processedpayrolltable').datagrid('getSelected');
            var arr_emp_pkeys = [];
            var arr_payroll_pkeys = [];
            var checkedRows = $('#processedpayrolltable').datagrid('getChecked');
            var month = $('#payrollfilter #filterby_month').val();
            for (var register in checkedRows) {
                arr_emp_pkeys.push(checkedRows[register]['emp_fkey']);
                arr_payroll_pkeys.push(checkedRows[register]['payroll_master_pkey']);
            }
            console.log("arr_payroll_pkeys", arr_payroll_pkeys);
            if (row) {
                // Show the modal
                $(".alert-success").show();
                $.ajax({
                    url: livesite + "SalaryReports/sendSliptoMail",
                    type: 'get',
                    data: {
                        emps: arr_emp_pkeys,
                        month: month
                    },
                    success: function(response) {
                        $(".alert-success").hide();
                        //process server response here
                        // var success = $.parseJSON(response).success;
                        // if (success) {
                        //     $.notify("Payroll listed successfully", {
                        //         type: 'success',
                        //         allow_dismiss: false
                        //     });
                        //     $('.tabset-processpayroll #tab1').load(livesite + "PayrollProcess/paymentpayrolltab/0", {
                        //         month: month,
                        //         branch: branch
                        //     });
                        //     $('.tabset-processpayroll #tab2').load(livesite + "PayrollProcess/paymentpayrolltab/1", {
                        //         month: month,
                        //         branch: branch
                        //     });
                        //     $('#payrolltable').datagrid('load');
                        // } else {
                        //     //alert('Attendance process failed!');
                        //     $.notify("Payroll listing failed!", {
                        //         type: 'danger',
                        //         allow_dismiss: false
                        //     });
                        // }
                    }
                });
            } else {
                alert('Please select a row.');
                return; // Prevent further execution of the function
            }
        }
    </script>
<?php } else { ?>
    <script>
        var branch = $('#payrollfilter #filterby_branch').val();
        var employee = $('#select_filterby_employee').val();
        var month = $('#payrollfilter #filterby_month').val();
        $('#processedpayrolltable').datagrid({
            url: livesite + "PayrollProcess/listpaymentapprovedpayroll",
            pagination: true,
            singleSelect: false,
            view: scrollview,
            autoRowHeight: true,
            pageSize: 150,
            rownumbers: true,
            queryParams: {
                branch: branch,
                employee: employee,
                month: month
            },
            width: '100%',
            height: '300px',
            fitColumns: true,
            pageList: [20, 50, 100, 150, 250, 500],
            rowStyler: function(index, row) {
                if (row.action == 'Hold') {
                    //return 'background-color:#b22222;color:white;';
                    return 'color:#B22222;';
                } else if (row.action == 'Approved') {
                    return 'color:#00D692;';
                }
            },

            //Resize active tab height
            //07 July 2018
            onLoadSuccess: function(data) {
                resizePWSTabContentDiv(2);
            }
        });
        $('#select_filterby_employee').select2();

        function viewSalarySlip(value, row) {
            var payroll_master_pkey = row.payroll_master_pkey;
            return '<a href="#" class="easyui-linkbutton" plain="true" iconCls="icon-cancel" onclick="showSmallModalForm(\'' + livesite + 'PayrollProcess/showsalaryslip/' + payroll_master_pkey + '\');">View Slip</a>';
        }
    </script>
<?php } ?>
<script>
    function filterPayrollByEmployee(tab) {
        if (tab == 1) {
            var branch = $('#payrollfilter #filterby_branch').val();
            var employee = $('#select_filterby_employee').val();
            var month = $('#payrollfilter #filterby_month').val();
            $('#processedpayrolltable').datagrid('load', {
                branch: branch,
                employee: employee,
                month: month
            });
        } else {
            var branch = $('#payrollfilter #filterby_branch').val();
            var employee = $('#filterby_employee').val();
            var month = $('#payrollfilter #filterby_month').val();
            $('#payrolltable').datagrid('load', {
                branch: branch,
                employee: employee,
                month: month
            });
        }
    }
</script>