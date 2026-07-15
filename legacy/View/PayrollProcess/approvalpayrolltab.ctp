<style>
    #processedpayrolltable td[field="provisional_remarks"] {
        height: auto !important;
        white-space: normal !important;
    }
</style>

<div class="row">
    <!-- <div><label class="col-md-4 control-label"> Total Employees : <?php echo $total; ?></label><label class="col-md-4 control-label" > Net Salary : <?php echo number_format($sum, 2); ?></label> </div> -->

    <div class="col-md-12">
        <div class="datagrid-toolbar">
            <table cellspacing="0" cellpadding="0">
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
                            $id = 'select_filterby_employee';
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
                        <!-- <th data-options="width:'5%'" field="chk_payroll" checkbox="true"></th> -->
                        <th data-options="field:'emp_name',width:'22%'" sortable="true">Employee Name</th>
                        <th data-options="field:'calander_days',width:'10%'" sortable="true">Calender Days</th>
                        <th data-options="field:'working_days',width:'8%'" sortable="true">Working Day</th>
                        <th data-options="field:'days_presant',width:'7%'" sortable="true">Present</th>
                        <th data-options="field:'days_leave',width:'7%'" sortable="true">Leave</th>
                        <th data-options="field:'loss_of_pay',width:'7%'" sortable="true">LOP</th>
                        <th data-options="field:'net_salary',width:'10%'">Net Salary</th>
                        <th data-options="field:'provisional_remarks',width:'22%'" sortable="true">Provisional Remarks</th>
                        <th data-options="field:'finance_remarks',width:'50%'" sortable="true">Finance Remarks</th>
                        <th data-options="field:'final_remarks',width:'320%'" sortable="true">Finalization Remarks</th>
                    </tr>
                </thead>
            </table>
        <?php } else { ?>
            <table id="processedpayrolltable" class="table table-bordered table-hover">
                <thead>
                    <tr>
                        <th data-options="width:'5%'" field="chk_processedpayroll" checkbox="true">
                            <input type="checkbox" checked style="display: none;">
                        </th>
                        <th field="btn_view_salaryslip" align="center" formatter="viewSalarySlip">View Slip</th>
                        <th data-options="field:'emp_name',width:'22%'" sortable="true" data-title="Employee Name">Employee Name</th>
                        
                        <th data-options="field:'working_days',width:'8%'" sortable="true">Working Day</th>
                        <th data-options="field:'loss_of_pay',width:'5%'" sortable="true">LOP</th>
                        <th data-options="field:'monthly_amount',width:'8%'" sortable="true">Monthly CTC</th>
                        <th data-options="field:'gross_salary',width:'8%'" sortable="true">Gross Salary</th>
                        <th data-options="field:'net_salary',width:'8%'" sortable="true">Net Salary</th>
                        <!-- <th data-options="field:'last_month_net_salary',width:'10%'" sortable="true">Prev Salary</th>
                        <th data-options="field:'diff',width:'10%'" sortable="true">Difference</th> -->
                        <th data-options="field:'provisional_remarks',width:'22%'" sortable="true">Provisional Remarks</th>
                        <th data-options="field:'finance_remarks',width:'50%'" sortable="true">Finance Remarks</th>
                        <th data-options="field:'final_remarks',width:'320%'" sortable="true">Finalization Remarks</th>
                        <th data-options="field:'remarks',width:'40%'" sortable="true">Salary Approval Remarks</th>
                    </tr>
                </thead>
            </table>
        <?php } ?>
    </div>
</div>

<?php if ($tab == 0) { ?>
    <script>
        //Edited by Akshay on 22-9-2023
        // Add a "tooltip" class to the cells where you want this behavior
        $('#payrolltable td[data-toggle="tooltip"]').addClass('tooltip');

        // Add the tooltip content to the cells
        $('#payrolltable td[data-toggle="tooltip"]').each(function() {
            var cellContent = $(this).text();
            $(this).append('<div class="tooltiptext">' + cellContent + '</div>');
        });



        var branch = $('#payrollfilter #filterby_branch').val();
        var employee = $('#filterby_employee').val();
        var month = $('#payrollfilter #filterby_month').val();
        $('#payrolltable').datagrid({
            url: livesite + "PayrollProcess/listsalaryapprovalpayroll",
            pagination: true,
            singleSelect: false,
            view: scrollview,
            rownumbers: true,
            autoRowHeight: true,
            pageSize: 150,
            width: '100%',
            height: '300px',
            queryParams: {
                branch: branch,
                employee: employee,
                month: month
            },
            fitColumns: true,
            pageList: [20, 50, 150, 250, 500],
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
            // console.log('Row',row);
            if (row.length > 0) {
                // Show the modal
                showSmallModalForm(livesite + "PayrollProcess/addremarks4/0");
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
            console.log('Row', row.length);
            if (row.length > 0) {
                // var checkedRows = $('#payrolltable').datagrid('getChecked');
                var checkedRows = $('#payrolltable').datagrid('getRows');
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
                    showSmallModalForm(livesite + "PayrollProcess/addrejectremarks/" + pmPkey + '/finalization');

                } else {
                    $.notify("You cannot remove an approved payroll entry!", {
                        type: 'danger',
                        allow_dismiss: false
                    });
                    return;
                }
            } else {
                // alert('Please select a row.');
                alert('There is no payroll to reject.');
                return; // Prevent further execution of the function
            }
        }

        // Add tooltips to cells with the "tooltip" class
        $('#payrolltable td[data-toggle="tooltip"]').tooltip({
            position: 'top',
            content: function() {
                return $(this).text();
            }
        });
    </script>
<?php } else { ?>
    <script>
        //Edited by Akshay on 22-9-2023
        // Add a "tooltip" class to the cells where you want this behavior
        $('#processedpayrolltable td[data-toggle="tooltip"]').addClass('tooltip');

        // Add the tooltip content to the cells
        $('#processedpayrolltable td[data-toggle="tooltip"]').each(function() {
            var cellContent = $(this).text();
            $(this).append('<div class="tooltiptext">' + cellContent + '</div>');
        });


        var branch = $('#payrollfilter #filterby_branch').val();
        var employee = $('#select_filterby_employee').val();
        var month = $('#payrollfilter #filterby_month').val();
        $('#processedpayrolltable').datagrid({
            url: livesite + "PayrollProcess/listsalaryapprovedpayroll",
            pagination: true,
            singleSelect: false,
            view: scrollview,
            rownumbers: true,
            autoRowHeight: true,
            queryParams: {
                branch: branch,
                employee: employee,
                month: month
            },
            pageSize: 150,
            width: '100%',
            height: '300px',
            fitColumns: true,
            pageList: [20, 50, 150, 250, 500],
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

        // Add tooltips to cells with the "tooltip" class
        $('#processedpayrolltable td[data-toggle="tooltip"]').tooltip({
            position: 'top',
            content: function() {
                return $(this).text();
            }
        });
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