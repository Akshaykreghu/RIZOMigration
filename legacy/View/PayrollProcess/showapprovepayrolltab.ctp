<div class="row">
    <div class="col-md-12">
        <div class="datagrid-toolbar"> 
            <table cellspacing="0" cellpadding="0">
                <tbody>
                    <tr>
                        <?php if($tab == 0){ ?>
                        <td>
                            <a href="javascript:void(0)" onclick="approvePayroll();" class="l-btn l-btn-small l-btn-plain" group="" id="">
                                <span class="l-btn-left l-btn-icon-left">
                                    <span class="l-btn-text">Approve</span>
                                    <span class="l-btn-icon icon-ok">&nbsp;</span>
                                </span>
                            </a>
                        </td>
                        <?php $id = 'filterby_employee';}else{ $id = 'select_filterby_employee';} ?>
                        <td>
                            <select id="<?php echo $id; ?>" name="<?php echo $id; ?>" class="form-control" onchange="filterPayrollByEmployee(<?php echo $tab; ?>);" >
                                <option value="">--All--</option>
                                <?php
                                       //edited by arul on 12/12/2019 Employee company id added
//                                foreach ($arr_employees as $key => $value) {
//                                    echo '<option value="' . $value['emp_pkey'] . '">' . $value['emp_name'] . '</option>';
//                                }
                                foreach ($arr_employees as $value) {
		                echo '<option value="' . $value['emp_details']['emp_pkey'] . '">' . $value['emp_details']['first_name'] . ' ' . $value['emp_details']['last_name'] . ' - ' .$value['emp_proff']['emp_company_id']. '</option>';
	                        }
                                    //end Employee company id added
                                ?>
                            </select>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
        <?php if($tab == 0){ ?>
        <table id="approvepayrolltable" class="table table-bordered table-hover">
            <thead>
                <tr>
                    <th field="chk_payroll" checkbox="true"></th>
                    <th field="btn_view_salaryslip" align="center" formatter="viewSalarySlip">View Slip</th>
                    <th data-options="field:'emp_name',width:'20%'" sortable="true">Employee name</th>
                    <th data-options="field:'days_presant',width:'8%'" sortable="true">Days present</th>
                    <th data-options="field:'days_leave',width:'8%'" sortable="true">Days on leave</th>
                    <th data-options="field:'loss_of_pay',width:'8%'" sortable="true">Loss of pay</th>
                    <th data-options="field:'monthly_ctc',width:'10%'" sortable="true">Monthly CTC</th>
                    <th data-options="field:'monthly_amount',width:'10%'" sortable="true">Monthly Amount</th>
                    <th data-options="field:'gross_salary',width:'10%'" sortable="true">Gross Salary</th>
                    <th data-options="field:'total_deduction',width:'13%'" sortable="true">Total Deductions</th>
                    <th data-options="field:'net_salary',width:'13%'" sortable="true">Net Salary</th>
                    <th data-options="field:'last_month_net_salary',width:'13%'" sortable="false">Last Month Salary</th>
                    <th data-options="field:'calander_days',width:'13%'" sortable="true">Calender Days</th>
                    <th data-options="field:'working_days',width:'13%'" sortable="true">Working Days</th>
                </tr>
            </thead>
        </table>
        <?php }else{ ?>
        <table id="approvedpayrolltable" class="table table-bordered table-hover">
            <thead>
                <tr>
                    <th field="chk_processedpayroll" checkbox="true"></th>
                    <th data-options="field:'emp_name',width:'20%'" sortable="true">Employee name</th>
                    <th data-options="field:'days_presant',width:'8%'" sortable="true">Days present</th>
                    <th data-options="field:'days_leave',width:'8%'" sortable="true">Days on leave</th>
                    <th data-options="field:'loss_of_pay',width:'8%'" sortable="true">Loss of pay</th>
                    <th data-options="field:'monthly_ctc',width:'10%'" sortable="true">Monthly CTC</th>
                    <th data-options="field:'monthly_amount',width:'10%'" sortable="true">Monthly Amount</th>
                    <th data-options="field:'gross_salary',width:'10%'" sortable="true">Gross Salary</th>
                    <th data-options="field:'total_deduction',width:'13%'" sortable="true">Total Deductions</th>
                    <th data-options="field:'net_salary',width:'13%'" sortable="true">Net Salary</th>
                    <th data-options="field:'calander_days',width:'13%'" sortable="true">Calender Days</th>
                    <th data-options="field:'working_days',width:'13%'" sortable="true">Working Days</th>
                </tr>
            </thead>
        </table>
        <?php } ?>
    </div>
</div>
<?php if($tab == 0){ ?>
<script>
    var branch = $('#approvepayrollfilter #filterby_branch').val();
    var employee = $('#filterby_employee').val();
    var month = $('#approvepayrollfilter #filterby_month').val();
    $('#approvepayrolltable').datagrid({
        url: livesite + "PayrollProcess/listapprovepayroll",
        pagination: true,
        singleSelect: false,
        queryParams: {
            branch: branch,
            employee: employee,
            month: month
        },
        fitColumns: true,
        pageList: [2, 5, 10, 50, 100]
    });
    $('#filterby_employee').select2();//This is to search employee name or company id. By ***ARUL P DAS on 20/12/2019
</script>
<?php }else{ ?>
<script>
    var branch = $('#approvepayrollfilter #filterby_branch').val();
    var employee = $('#select_filterby_employee').val();
    var month = $('#approvepayrollfilter #filterby_month').val();
    $('#approvedpayrolltable').datagrid({
        url: livesite + "PayrollProcess/listapprovedpayroll",
        pagination: true,
        singleSelect: false,
        queryParams: {
            branch: branch,
            employee: employee,
            month: month
        },
        width: '100%',
        fitColumns: true,
        pageList: [2, 5, 10, 50, 100]
    });
    $('#select_filterby_employee').select2();//This is to search employee name or company id. By ***ARUL P DAS on 20/12/2019
</script>
<?php } ?>
<script>
function filterPayrollByEmployee(tab){
    if(tab == 1){
        var branch = $('#approvepayrollfilter #filterby_branch').val();
        var employee = $('#select_filterby_employee').val();
        var month = $('#approvepayrollfilter #filterby_month').val();
        $('#approvedpayrolltable').datagrid('load', {
            branch: branch,
            employee: employee,
            month: month
        });
    }else {
        var branch = $('#approvepayrollfilter #filterby_branch').val();
        var employee = $('#filterby_employee').val();
        var month = $('#approvepayrollfilter #filterby_month').val();
        $('#approvepayrolltable').datagrid('load', {
            branch: branch,
            employee: employee,
            month: month
        });
    }
}
function viewSalarySlip(value,row){
    var payroll_master_pkey = row.payroll_master_pkey;
    return '<a href="#" class="easyui-linkbutton" plain="true" iconCls="icon-cancel" onclick="showSmallModalForm(\'' + livesite + 'PayrollProcess/showsalaryslip/' + payroll_master_pkey + '\');">View Slip</a>';
}
</script>
