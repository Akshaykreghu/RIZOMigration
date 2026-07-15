<div class="row">
    <div class="col-md-12">
        <div class="datagrid-toolbar">
            <table cellspacing="0" cellpadding="0">
                <tbody>
                    <tr>
                        <?php if($tab == 0){ ?>
                        <td>
                            <a href="javascript:void(0)" onclick="verifyRegisterEntries();" class="l-btn l-btn-small l-btn-plain" group="" id="">
                                <span class="l-btn-left l-btn-icon-left">
                                    <span class="l-btn-text">Verify</span>
                                    <span class="l-btn-icon icon-ok">&nbsp;</span>
                                </span>
                            </a>
                        </td>
                        <?php $id = 'filterby_employee';}else{ $id = 'select_filterby_employee';} ?>
                        <td>
                            <select id="<?php echo $id; ?>" name="<?php echo $id; ?>" class="form-control" onchange="filterRegisterByEmployee(<?php echo $tab; ?>);" >
                                <option value="">--All--</option>
                                <?php
                                foreach ($arr_employees as $key => $value) {
                                    echo '<option value="' . $value['emp_pkey'] . '">' . $value['emp_name'] . '</option>';
                                }
                                ?>
                            </select>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
        <?php if($tab == 0){ ?>
        <input type="hidden" id="hidden-start-date" value="<?php echo $date_start; ?>" />
        <input type="hidden" id="hidden-end-date" value="<?php echo $date_end; ?>" />
        <table id="attendanceregistertable" class="table table-bordered table-hover">
            <thead>
                <tr>
                    <th field="action_verify" align="center" width="7%" formatter="showverifybutton">Verify</th>
                    <th data-options="field:'emp_name',width:'10%'">Employee name</th>
                    <?php $j = 1; foreach($arr_dates as $i) { ?>															
						<!-- By santhosh on 27 Dec 2015 -->
                        <!--th data-options="field:'<?php echo 'FIELD' . $j; ?>',width:'3%'" styler="styleDay"><?php echo $i; ?></th-->
                        <th data-options="field:'<?php echo 'FIELD' . $i; ?>',width:'3%'" styler="styleDay"><?php echo $i; ?></th>
                    <?php $j++; } ?>
                    <th data-options="field:'days_present',width:'10%'">Days present</th>
                    <th data-options="field:'days_leave',width:'10%'">Days on leave</th>
                    <th data-options="field:'days_holidays',width:'10%'">Holidays</th>
                </tr>
            </thead>
        </table>
        <?php }else{ ?>
        <table id="verifiedattendanceregistertable" class="table table-bordered table-hover">
            <thead>
                <tr>
                    <th data-options="field:'emp_name',width:'10%'">Employee name</th>
                    <?php $j = 1; foreach($arr_dates as $i) { ?>															
						<!-- By santhosh on 27 Dec 2015 -->
                        <!--th data-options="field:'<?php echo 'FIELD' . $j; ?>',width:'3%'" styler="styleDay"><?php echo $i; ?></th-->
                        <th data-options="field:'<?php echo 'FIELD' . $i; ?>',width:'3%'" styler="styleDay"><?php echo $i; ?></th>
                    <?php $j++; } ?>
                    <th data-options="field:'days_present',width:'10%'">Days present</th>
                    <th data-options="field:'days_leave',width:'10%'">Days on leave</th>
                    <th data-options="field:'days_holidays',width:'10%'">Holidays</th>
                </tr>
            </thead>
        </table>
        <?php } ?>
    </div>
</div>
<?php if($tab == 0){ ?>
<script>
    function showverifybutton(val,row){
        return '<a target="_blank" onclick="verifyRegisterEntries(' + row.registerid + ');"><button>Verify</button></a>';
    }
    var branch = $('#attendanceregisterfilter #filterby_branch').val();
    var employee = $('#filterby_employee').val();
    var month = $('#attendanceregisterfilter #filterby_month').val();
    $('#attendanceregistertable').datagrid({
        url: livesite + "Empattendance/listregisterentries",
        pagination: true,
        singleSelect: true,
        queryParams: {
            branch: branch,
            employee: employee,
            month: month
        },
        width: '100%',
        //toolbar: '#tb',
        fitColumns: true,
        pageList: [2, 5, 10, 50, 100]
    });
</script>
<?php }else{ ?>
<script>
    var branch = $('#attendanceregisterfilter #filterby_branch').val();
    var employee = $('#attendanceregisterfilter filterby_employee').val();
    var month = $('#attendanceregisterfilter #filterby_month').val();
    $('#verifiedattendanceregistertable').datagrid({
        url: livesite + "Empattendance/listverifiedregisterentries",
        pagination: true,
        singleSelect: true,
        queryParams: {
            branch: branch,
            employee: employee,
            month: month
        },
        width: '100%',
        //toolbar: '#tb',
        fitColumns: true,
        pageList: [2, 5, 10, 50, 100]
    });
</script>
<?php } ?>
<script>
function filterRegisterByEmployee(tab){
    if(tab == 1){
        var branch = $('#attendanceregisterfilter #filterby_branch').val();
        var employee = $('#select_filterby_employee').val();
        var month = $('#attendanceregisterfilter #filterby_month').val();
        $('#verifiedattendanceregistertable').datagrid('load', {
            branch: branch,
            employee: employee,
            month: month
        });
    }else {
        var branch = $('#attendanceregisterfilter #filterby_branch').val();
        var employee = $('#filterby_employee').val();
        var month = $('#attendanceregisterfilter #filterby_month').val();
        $('#attendanceregistertable').datagrid('load', {
            branch: branch,
            employee: employee,
            month: month
        });
    }
}
</script>
