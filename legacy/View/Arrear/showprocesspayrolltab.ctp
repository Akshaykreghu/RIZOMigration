<div class="row">
    <div class="col-md-12">
        <div class="datagrid-toolbar">
            <table cellspacing="0" cellpadding="0">
                <tbody>
                    <tr>
                        <?php if ($tab == 0) { ?>
                            <td>
                                <a href="javascript:void(0)" onclick="processPayroll();" class="l-btn l-btn-small l-btn-plain" group="" id="">
                                    <span class="l-btn-left l-btn-icon-left">
                                        <span class="l-btn-text">Process</span>
                                        <span class="l-btn-icon icon-ok">&nbsp;</span>
                                    </span>
                                </a>
                            </td>
                            <!--// Include tax - update the tax field = Y Added By Nimisha 16/03/2019-->
                            <!-- <td style="padding-left: 10px;">Include Tax : </td>
                            <td style="padding-left: 10px;">
                                <select class="form-control" onchange="ADDTAX();" id="addtax">

                                    <option value="0">No</option>
                                    <option value="1">Yes</option>
                                </select>
                            </td> -->
                        <?php
                            $id = 'filterby_employee';
                        } ?>
                        <?php if ($tab == 1) { ?>

                            <td>
                                <a href="javascript:void(0)" onclick="removePayrollEntry();" class="l-btn l-btn-small l-btn-plain" group="" id="">
                                    <span class="l-btn-left l-btn-icon-left">
                                        <span class="l-btn-text">Remove</span>
                                        <span class="l-btn-icon icon-remove">&nbsp;</span>
                                    </span>
                                </a>
                            </td>

                            <td>
                                <a href="javascript:void(0)" onclick="approvePayroll();" class="l-btn l-btn-small l-btn-plain" group="" id="">
                                    <span class="l-btn-left l-btn-icon-left">
                                        <span class="l-btn-text">Approve</span>
                                        <span class="l-btn-icon icon-ok">&nbsp;</span>
                                    </span>
                                </a>
                            </td>
                        <?php
                            $id = 'select_filterby_employee';
                        }
                        ?>
                        <?php if ($tab == 2) { ?>
                        <?php
                            $id = 'select_filterby_employee1'; // Edited by Akshay on 23-12-2024
                        }
                        ?>

                        <td style="padding-left: 10px;">Select Employee : </td>
                        <td>
                            <select style="margin-left: 15px;" id="<?php echo $id; ?>" name="<?php echo $id; ?>" class="form-control" onchange="filterPayrollByEmployee(<?php echo $tab; ?>);">
                                <option value="">--All--</option>
                                <?php
                                foreach ($arr_employees as $key => $value) {
                                    echo '<option value="' . $value['emp_pkey'] . '">' . $value['emp_name'] . '-' . $value['emp_id'] . '</option>';
                                }
                                ?>
                            </select>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
        <?php if ($tab == 0) { ?>
            <table id="payrolltable" class="table table-bordered table-hover">
                <thead>
                    <tr>
                        <th data-options="width:'5%'" field="chk_payroll" checkbox="true"></th>
                        <th data-options="field:'emp_name',width:'25%'" sortable="true">Employee Name</th>
                        <th data-options="field:'month_year',width:'10%'" sortable="true">Month</th>
                        <th data-options="field:'calander_days',width:'10%'" sortable="true">Calender Days</th>
                        <th data-options="field:'working_days',width:'10%'" sortable="true">Working Days</th>
                        <th data-options="field:'days_presant',width:'14%'" sortable="true">Days present</th>
                        <th data-options="field:'days_leave',width:'15%'" sortable="true">Days on leave</th>
                        <th data-options="field:'loss_of_pay',width:'15%'" sortable="true">Loss of pay</th>
                        <!--                    th data-options="field:'monthly_ctc',width:'10%'">Monthly CTC</th>
                    <th data-options="field:'monthly_amount',width:'10%'">Monthly Amount</th>
                    <th data-options="field:'gross_salary',width:'10%'">Gross Salary</th>
                    <th data-options="field:'total_deductions',width:'12%'">Total Deductions</th>-->
                        <!-- <th data-options="field:'net_salary',width:'10%'">Net Salary</th> -->


                    </tr>
                </thead>
            </table>
        <?php }
        if ($tab == 1) { ?>
            <table id="processedpayrolltable" class="table table-bordered table-hover">
                <thead>
                    <tr>
                        <th data-options="width:'5%'" field="chk_processedpayroll" checkbox="true"></th>
                        <th field="btn_view_salaryslip" align="center" formatter="viewSalarySlip">View Slip</th>
                        <th data-options="field:'emp_name',width:'25%'" sortable="true">Employee Name</th>
                        <th data-options="field:'month',width:'8%'" sortable="true">Month</th>
                        <th data-options="field:'working_days',width:'10%'" sortable="true">Working Days</th>
                        <!--<th data-options="field:'days_presant',width:'8%'" sortable="true">Days present</th>-->
                        <!--<th data-options="field:'days_leave',width:'8%'" sortable="true">Days on leave</th>-->
                        <th data-options="field:'loss_of_pay',width:'8%'" sortable="true">Loss of pay</th>
                        <!--<th data-options="field:'monthly_ctc',width:'10%'" sortable="true">Monthly Amount</th>-->
                        <th data-options="field:'monthly_amount',width:'10%'" sortable="true">Monthly CTC</th>
                        <th data-options="field:'gross_salary',width:'10%'" sortable="true">Gross Salary</th>
                        <!--<th data-options="field:'total_deduction',width:'12%'" sortable="true">Total Deductions</th>-->
                        <!-- <th data-options="field:'net_salary',width:'10%'" sortable="true">Net Salary</th> -->
                        <th data-options="field:'arrear_net_salary',width:'30%'">Arrear Net Salary</th>
                        <!--   <th data-options="field:'last_month_net_salary',width:'10%'" sortable="true">Previous Salary</th>
                    <th data-options="field:'diff',width:'10%'" sortable="true">Difference</th> -->
                    </tr>
                </thead>
            </table>
        <?php } ?>
        <?php if ($tab == 2) { ?>
            <table id="approvedpayrolltable" class="table table-bordered table-hover">
                <thead>
                    <tr>
                        <!-- <th data-options="width:'5%'" field="chk_processedpayroll" checkbox="true"></th> -->
                        <th field="btn_view_salaryslip" align="center" formatter="viewSalarySlip">View Slip</th>
                        <th data-options="field:'emp_name',width:'20%'" sortable="true">Employee Name</th>
                        <th data-options="field:'month_year',width:'12%'" sortable="true">Month</th>
                        <th data-options="field:'days_present',width:'12%'" sortable="true">Present Days</th>
                        <!--<th data-options="field:'days_presant',width:'8%'" sortable="true">Days present</th>-->
                        <!--<th data-options="field:'days_leave',width:'8%'" sortable="true">Days on leave</th>-->
                        <!-- <th data-options="field:'monthly_ctc',width:'8%'" sortable="true">Monthly </th> -->
                        <!--<th data-options="field:'monthly_ctc',width:'10%'" sortable="true">Monthly Amount</th>-->
                        <th data-options="field:'monthly_amount',width:'12%'" sortable="true">Monthly CTC</th>
                        <th data-options="field:'gross_salary',width:'15%'" sortable="true">Gross Salary</th>
                        <!--<th data-options="field:'total_deduction',width:'12%'" sortable="true">Total Deductions</th>-->
                        <!-- <th data-options="field:'net_salary',width:'12%'" sortable="true">Net Salary</th> -->
                        <th data-options="field:'arrear_net_salary',width:'30%'" sortable="true">Arrear Net Salary</th>
                        <!-- <th data-options="field:'diff',width:'10%'" sortable="true">Difference</th> -->
                    </tr>
                </thead>
            </table>
        <?php } ?>
    </div>
</div>
<?php if ($tab == 0) { ?>
    <script>
        var branch = $('#payrollfilter #filterby_branch').val();
        // Edited by Akshay on 23-12-2024
        var employee = $('#filterby_employee').val();

        // End
        var month = $('#payrollfilter #filterby_month').val();
        $('#payrolltable').datagrid({
            url: livesite + "Arrear/listpayroll",
            pagination: true,
            searchFilter: false,
            singleSelect: false,
            queryParams: {
                branch: $('#filterby_branch').val(),
                employee: $('#filterby_employee').val(), // Akshay's edit 23-12-2024
                month: $('#filterby_month').val()
            },
            fitColumns: true,
            pageList: [2, 5, 10, 50, 100],
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
        $('#filterby_employee').select2(); // Edited by Akshay on 23-12-2024
    </script>
<?php } ?>
<?php if ($tab == 1) { ?>
    <script>
        var branch = $('#payrollfilter #filterby_branch').val();
        var employee = $('#select_filterby_employee').val();
        var month = $('#payrollfilter #filterby_month').val();
        $('#processedpayrolltable').datagrid({
            url: livesite + "Arrear/listprocessedpayroll",
            pagination: true,
            searchFilter: false,
            singleSelect: false,
            queryParams: {
                branch: branch,
                employee: employee,
                month: month
            },
            width: '100%',
            fitColumns: true,
            pageList: [2, 5, 10, 50, 100],
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
            var payroll_arrear_master_pkey = row.payroll_arrear_master_pkey;
            return '<a href="#" class="easyui-linkbutton" plain="true" iconCls="icon-cancel" onclick="showSmallModalForm(\'' + livesite + 'Arrear/showarrearsalaryslip/' + payroll_arrear_master_pkey + '\');">View Slip</a>';
        }
    </script>
<?php } ?>
<?php if ($tab == 2) { ?>
    <script>
        var branch = $('#payrollfilter #filterby_branch').val();
        var employee = $('#select_filterby_employee1').val(); // Edited by Akshay on 23-12-2024
        var month = $('#payrollfilter #filterby_month').val();
        console.log('employee', $('#select_filterby_employee1'));

        $('#approvedpayrolltable').datagrid({

            url: livesite + "Arrear/listapprovedpayroll",
            pagination: true,
            searchFilter: false,
            singleSelect: false,
            queryParams: {
                branch: branch,
                employee: employee,
                month: month
            },
            width: '100%',
            fitColumns: true,
            pageList: [2, 5, 10, 50, 100],
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
                resizePWSTabContentDiv(3);
            }
        });
        $('#select_filterby_employee1').select2(); // Edited by Akshay on 23-12-2024
        $('#filterby_employee').select2();

        function viewSalarySlip(value, row) {
            var payroll_arrear_master_pkey = row.payroll_arrear_master_pkey;
            return '<a href="#" class="easyui-linkbutton" plain="true" iconCls="icon-cancel" onclick="showSmallModalForm(\'' + livesite + 'Arrear/showarrearsalaryslip/' + payroll_arrear_master_pkey + '\');">View Slip</a>';
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
        } else if (tab == 0) {
            var branch = $('#payrollfilter #filterby_branch').val();
            var employee = $('#filterby_employee').val();
            var month = $('#payrollfilter #filterby_month').val();
            $('#payrolltable').datagrid('load', {
                branch: branch,
                employee: employee,
                month: month
            });
        } else {
            var branch = $('#payrollfilter #filterby_branch').val();
            var employee = $('#select_filterby_employee1').val();
            var month = $('#payrollfilter #filterby_month').val();
            $('#approvedpayrolltable').datagrid('load', {
                branch: branch,
                employee: employee,
                month: month
            });
        }
    }
</script>