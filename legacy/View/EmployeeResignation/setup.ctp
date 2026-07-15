<style>
    .loader-div {
        width: 100%;
        height: 100%;
        bottom: 0;
        right: 0;
        position: absolute;
        z-index: 99;
        text-align: center;
        background: #cccccc1f;
        opacity: 0.1;
    }

    .loader-div .fa-spinner {
        position: absolute;
        top: 50%;
        font-size: -webkit-xxx-large;
        z-index: 141;
        color: #236fac;
        opacity: 17;
    }

    /* Edited by Akshay on 13-12-2023 */
    /* Apply a background color to visually indicate it's readonly */
    .description-header[readonly] {
        background-color: #f5f5f5;
        border: 1px solid #ccc;
    }

    .description-header[readonly] {
        cursor: not-allowed;
    }
</style>
<div class="row" id="resignation_loading">
    <input type="hidden" value="<?php echo $emp_pkey; ?>" id="emp_pkey">
    <div class="col-md-12">
        <div class="box box-default collapsed-box box-solid">
            <div class="box-header with-border">
                <h3 class="box-title">Resignation Details</h3>

                <div class="box-tools pull-right">
                    <button onclick="toggleExpandable(this,'contain')" type="button" class="btn btn-box-tool" data-widget="collapse"><i class="fa fa-minus"></i>
                    </button>
                </div>
                <!-- /.box-tools -->
            </div>
            <!-- /.box-header -->
            <div class="box-body show-first" id="contain" style="display:block;">

                <div class="col-sm-3 col-xs-6">
                    <div class="description-block border-right">
                        <!--<span class="description-percentage text-yellow"><i class="fa fa-caret-left"></i> 0%</span>-->
                        <h5 class="description-header"><?php echo date('d-m-Y', strtotime($termination_details['0']['Termination']['submitted_date'])); ?></h5>
                        <span class="description-text" title="RESIGNATION SUBMITTED DATE">RESIGNATION SUBMITTED DATE</span>
                    </div>
                    <!-- /.description-block -->
                </div>

                <div class="col-sm-3 col-xs-6">
                    <div class="description-block border-right">
                        <h5 class="description-header"><?php echo $termination_details['0']['Termination']['notice_period']; ?></h5>
                        <span class="description-text" title="NOTICE PERIOD">NOTICE PERIOD</span>
                    </div>
                </div>
                <div class="col-sm-3 col-xs-6">
                    <div class="description-block border-right">
                        <h5 class="description-header"><?php echo date('d-m-Y', strtotime($termination_details['0']['Termination']['last_working_date'])); ?></h5>
                        <span class="description-text" title="LAST WORKING DATE AS PER NOTICE DAYS">LAST WORKING DATE AS PER NOTICE DAYS</span>
                    </div>
                </div>
                <div class="col-sm-3 col-xs-6">
                    <div class="description-block border-right">
                        <!--<span class="description-percentage text-yellow"><i class="fa fa-caret-left"></i> 0%</span>-->
                        <h5 class="description-header"><?php echo date('d-m-Y', strtotime($termination_details['0']['Termination']['last_approved_working_date'])); ?></h5>
                        <span class="description-text" title="APPROVED LAST WORKING DATE">APPROVED LAST WORKING DATE</span>
                        <input type="hidden" id='resignation_date' value="<?php echo $termination_details['0']['Termination']['last_approved_working_date']; ?>">
                    </div>
                    <!-- /.description-block -->
                </div>
                <input type="hidden" value="" id="submitted">
                <input type="hidden" value="" id="applied_date">
                <input type="hidden" value="" id="notice_period">
                <input type="hidden" value="" id="lat_workingday" title="aspernoticedays">
                <input type="hidden" value="" id="apprved">
                <input type="hidden" value="<?php echo $termination_details['0']['Termination']['emp_fkey']; ?>" id="employee">
            </div>
            <!-- /.box-body -->
        </div>
        <!-- /.box -->
    </div>
    <div class="col-md-12">
        <div class="box box-default collapsed-box box-solid">
            <div class="box-header with-border">
                <h3 class="box-title">Notice Period Adjustments</h3>

                <div class="box-tools pull-right">
                    <button onclick="toggleExpandable(this,'contain1')" type="button" class="btn btn-box-tool" data-widget="collapse"><i class="fa fa-plus"></i>
                    </button>
                </div>
                <!-- /.box-tools -->
            </div>
            <!-- /.box-header -->
            <div class="box-body show-on" id="contain1">
                <div class="box" style="display:none;">
                    <div class="box-header with-border">
                        <div class="description-block border-right">
                            <h3 class="box-title"><span class="description-text">Leave Requests that Need to be Take Action</span></h3>
                        </div>
                        <div class="box-tools pull-right">
                            <button class="btn btn-box-tool" data-widget="collapse"><i class="fa fa-plus"></i></button>
                        </div>
                    </div><!-- /.box-header -->
                    <div class="box-body" id="leaveencash">
                        <div id="leaverequesttable">
                            <table id="datagrid_employee_leaves" class="table table-bordered table-hover">

                            </table>
                        </div>
                        <div class="description-block border-right">
                            <h3 class="box-title"><span class="description-text">Leave Encashments that Need to be Take Action</span></h3>
                        </div>
                        <div id="leaveencashtable">
                            <table id="empleaverequeststable" class="table table-bordered table-hover">

                            </table>
                        </div>

                    </div><!-- /.box-body -->

                </div>
                <div style="display:none; ">
                    <h4>
                        <div class="description-block border-right"><span class="description-text">Total Leave Encashment Days </span>: <span id="total_leaveencash"></span></div>
                    </h4>
                    <h4>
                        <div class="description-block border-right"><span class="description-text">Adjusted with notice Period </span>: <span id="adjusted_notice"></span></div>
                    </h4>

                    <h2 id="Encash_container pull-right">
                        <div class="description-block"><span class="description-text">Final Encashment Leave Days : <span id="fn_leaveencash"></span></span> <button class="btn btn-primary pull-right" onclick="approveencash();">Approve</button></div>
                    </h2>
                    <hr>
                </div>
                <input type="hidden" name="termination_pkey" id="termination_pkey">
                <div class="row">
                    <!-- /.col -->
                    <div class="col-sm-3 col-xs-6">
                        <div class="description-block border-right">
                            <!--<span class="description-percentage text-green"><i class="fa fa-caret-up"></i> 20%</span>-->
                            <h5 class="description-header" id="total_working">0</h5>
                            <span class="description-text" title="TOTAL WORKING DAYS (NOTICE DAYS - WEEKOFF AND HOLIDAYS)">RESIGNATION PERIOD WORKING DAYS</span>
                        </div>
                        <!-- /.description-block -->
                    </div>
                    <!-- /.col -->
                    <div class="col-sm-3 col-xs-6">
                        <div class="description-block border-right">
                            <!--<span class="description-percentage text-green"><i class="fa fa-caret-up"></i> 17%</span>-->
                            <h5 class="description-header" id="present_days_after_resg">0</h5>
                            <span class="description-text" title="PRESENT DAYS AFTER RESIGNATION">RESIGNATION PERIOD PRESENT DAYS</span>
                        </div>
                        <!-- /.description-block -->
                    </div>
                    <!-- /.col -->
                    <div class="col-sm-3 col-xs-6">
                        <div class="description-block">
                            <!--<span class="description-percentage text-red"><i class="fa fa-caret-down"></i> 18%</span>-->
                            <h5 class="description-header" id="balance_working_days">0</h5>
                            <span class="description-text" title="BALANCE WORKING DAYS">BALANCE WORKING DAYS</span>
                        </div>
                        <!-- /.description-block -->
                    </div>
                    <!-- /.col -->
                    <div class="col-sm-3 col-xs-6">
                        <div class="description-block border-right">
                            <!--<span class="description-percentage text-yellow"><i class="fa fa-caret-left"></i> 0%</span>-->
                            <h5 class="description-header" id="encashable_leavebal">0</h5>
                            <span class="description-text" title="ENCASHABLE LEAVE BALANCE">ENCASHABLE LEAVE BALANCE</span>
                        </div>
                    </div>

                </div>

                <!-- Edited by Akshay on 13-12-2023 -->
                <?php 
                if(($str_company_code =='DEMO'||$str_company_code =='KWMT')){
                ?>
                <div class="row">
                    <div class="col-sm-3 col-xs-6">
                        <div class="description-block border-right">
                            <h5 class="description-header"></h5>
                            <span class="description-text" title="ENCASHABLE LEAVE ADJUSTED" id="encashable"></span>
                            <input type="hidden" id="encashable_type">
                        </div>
                    </div>
                    <div class="col-sm-3 col-xs-6">
                        <div class="description-block border-right">
                            <h5 class="description-header"></h5>
                            <input type="number" class="description-header" style="text-align: center;" id="leave_adjusted" value="" inputmode="numeric" min="0" onchange="handleChange()">
                        </div>
                    </div>
                    <!-- /.col -->
                    <div class="col-sm-3 col-xs-6">
                        <div class="description-block border-right">
                            <h5 class="description-header"></h5>
                            <span class="description-text" title="BALANCE AFTER ADJUSTMENT">BALANCE AFTER ADJUSTMENT</span>
                        </div>
                    </div>
                    <div class="col-sm-3 col-xs-6">
                        <div class="description-block border-right">
                            <h5 class="description-header"></h5>
                            <input type="text" class="description-header" style="text-align: center;" id="balance_after_adjustment" value="" readonly>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-sm-12">
                        <div class="description-block border-right" style="text-align:right; padding-right:2.5%;">
                            <button class="btn btn-primary" id="btnLeaveUpdate" onclick="approveLeaveAdjustment()">Save</button>
                        </div>
                    </div>
                </div>
                <?php 
                }
                ?>
                <div class="row">
                    <!--                    <div class="col-md-12">
                        <hr>

                        <div class="col-sm-6 col-xs-6">
                            
                            <div class="description-block border-right">
                                
                                <h5 class="description-header" id="pay_to_be_set">0</h5>
                                <input title="uncheck if you want to skip this" checked="checked" type="checkbox" class="form-element">
                                <span class="description-text" id="notice_settlement" title="PAYMENTS TO BE SETTLED">NOTICE PERIOD SETTLEMENT DAYS</span>
                            </div>
                        </div>
                        
                    </div>-->
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-12">
        <div class="box box-default collapsed-box box-solid">
            <div class="box-header with-border">
                <h3 class="box-title">Loans,Advances, Expenses and Assets Adjustments</h3>

                <div class="box-tools pull-right">
                    <button onclick="toggleExpandable(this,'contain6')" type="button" class="btn btn-box-tool" data-widget="collapse"><i class="fa fa-plus"></i>
                    </button>
                </div>
                <!-- /.box-tools -->
            </div>
            <!-- /.box-header -->
            <div class="box-body" id="contain6">
                <h3 class="text-center"><b>Balance Recovery</b></h3>

                <?php if (count($arr_ln) > 0) { ?>
                    <div class="callout callout-info">
                        <h4>Unclosed Payments Found!</h4>

                        <!--<p>This Employee Liable to be Paid some payments to this Company , Please Review all those below and take an action to close those amounts on Loans/Advance/Expense Forms , Continue if you want to deduct it from salary.</p>-->
                        <p>
                            <Name of the Employee>This Employee has the following account liabilities to be closed and these will be considered while processing the full & final.

                                If you need to change anything here, Please go back to each item through their respective menu and settle these accounts separately.
                        </p>
                    </div>
                <?php } ?>
                <table class="table">
                    <thead>
                        <th>Accounts</th>
                        <th>Status</th>
                        <th>Amount</th>
                        <th>Balance</th>

                    </thead>
                    <tbody>
                        <?php $loan_sum = 0;
                        $advance_su = 0;
                        $asset_sum = 0; ?>
                        <?php if (count($arr_ln) > 0) { ?>
                            <?php foreach ($arr_ln as $val) { ?>
                                <tr>
                                    <td><?php echo $val['emp_loan']['emp_loan_pkey'] . " (" . $val['emp_loan']['emi_amount'] . " - EMI ) -  Loan Account"; ?></td>
                                    <td><?php echo "Open"; ?></td>
                                    <td><?php echo $val['emp_loan']['loan_amount']; ?></td>
                                    <td><?php echo isset($val['summary']['0']['emp_loan_info']['opening_balance']) ? $val['summary']['0']['emp_loan_info']['opening_balance'] : 0; ?></td>

                                    <?php $loan_sum += isset($val['summary']['0']['emp_loan_info']['opening_balance']) ? $val['summary']['0']['emp_loan_info']['opening_balance'] : 0; ?>
                                </tr>
                            <?php } ?>
                        <?php } else { ?>
                            <!--<tr><td> No Loan accounts Pending</td></tr>-->
                        <?php } ?>

                        <?php if (count($assets) > 1) { ?>
                            <?php foreach ($assets as $val) { ?>
                                <tr>
                                    <?php $asset_sum += isset($val['asset_allocate']['damaged_amout']) ? $val['asset_allocate']['damaged_amout'] : 0; ?>
                                </tr>
                            <?php } ?>
                        <?php } else { ?>
                            <!--<tr><td> No Assets Damages</td></tr>-->
                        <?php } ?>
                        <tr>

                            <td style="font-weight: bold; ">Total Amount Balance</td>
                            <td></td>
                            <td></td>
                            <td style="font-weight: bold; "><?php echo $loan_sum + $advance_su + $asset_sum; ?></td>
                        </tr>

                    </tbody>

                </table>
                <hr>
                <!--<button onclick="onloadprocess();"></button>-->
                <div id="assets">

                </div><!-- /.box-body -->
            </div>
        </div>
    </div>
    <div class="col-md-12" id="approves"> </div>
    <div class="col-md-12" id="get_completes"> </div>
</div>
<script>
    //Edited by Akshay on 13-12-2023
    var saveButtonClicked = false;
    var str_company_code = <?php echo json_encode($str_company_code); ?>;
    if (str_company_code == 'KWMT' || str_company_code == 'DEMO') {
        //hided the condition by sinsiya edited by sinsiya for water metro start
        //  $('#leave_adjusted').attr('readonly', true);
    } else {
        $('#leave_adjusted').attr('readonly', true);
    }
    $('#leave_adjusted').on('input', function() {
        // // Allow only numeric input
        // $(this).val(function(index, value) {
        //     return value.replace(/[^0-9.]/g, '');
        // });

        var sum = parseFloat($('#encashable_leavebal').html()) || 0;
        var leaveAdjustedValue = parseFloat($(this).val()) || 0;
        if (leaveAdjustedValue > sum) {
            // Value exceeds the limit, set to the maximum allowed value (sum)
            $(this).val(sum);
        }

        adjustBalance();
    });
    //Edited by Akshay on 24-7-2024
    function handleChange() {
        $('#approves').hide();
        $('#get_completes').hide();
        // $("#btnLeaveUpdate").prop("disabled", false);
    }
    //End

    function onloadprocess() {
        if (saveButtonClicked == true) {
            $("#contain6").prepend('<div class="loader-div"><li class="fa fa-spinner fa-spin"></li></div>');
            setTimeout(function() {
                $(".loader-div").hide();
            }, 1000)
            var employee = $('#employee').val();
            var leaves = $('#encashable_leavebal').html();
            $('#approves').load(livesite + "EmployeeResignation/approves/" + employee + "/" + leaves);
            //Edited by Akshay on 24-7-2024
            $('#approves').show();
            //End
        } else {
            if (str_company_code == 'KWMT' || str_company_code == 'DEMO') {
                alert('Please save Notice Period Adjustments');
            } else {
                $("#contain6").prepend('<div class="loader-div"><li class="fa fa-spinner fa-spin"></li></div>');
                setTimeout(function() {
                    $(".loader-div").hide();
                }, 1000)
                var employee = $('#employee').val();
                var leaves = $('#encashable_leavebal').html();
                $('#approves').load(livesite + "EmployeeResignation/approves/" + employee + "/" + leaves);
                //Edited by Akshay on 24-7-2024
                $('#approves').show();
                //End
            }
        }

    }

    function process() {
        var employee = $('#employee').val();
        var dayss = $('#present_days_after_resg').html();
        var leaves = $('#encashable_leavebal').html();
        var grativity = $('#total_amt_s').val();
        $('#get_completes').load(livesite + "EmployeeResignation/get_complete/" + employee, {
            emp: grativity,
            dayss: dayss,
            leaves: leaves
        });
        //show code
        //Edited by Akshay on 24-7-2024
        $('#get_completes').show();
        //End
    }

    function toggleExpandable(s, cont) {
        var iconChange = $(s).children();
        if ($(s).children().is(".fa-plus")) {
            $(iconChange).removeClass('fa-plus').addClass('fa-minus');
            $('#' + cont).fadeIn();
            $(s).parent().parent().css("background-color", "#60b576");
        } else {
            $(iconChange).removeClass('fa-minus').addClass('fa-plus');
            $('#' + cont).fadeOut();
            $(s).parent().parent().css("background-color", "#c1c6d2");
        }
    }

    function approveencash() {
        var leaveencash = $('#fn_leaveencash').html();
        var emp_pkey = $('#employee').val();
        if (leaveencash < 0) {
            $('#Encash_container').append('<button class="btn btn-primary"><li class="fa fa-spin fa-spinner"></li></button>');
            $.ajax({
                url: livesite + "FullandFinalsettlement/approveencash",
                type: "POST",
                data: {
                    leaveencash: leaveencash,
                    emp_pkey: emp_pkey
                },
                success: function(resp) {
                    if (resp == true) {
                        $('#Encash_container').html('Approved Successfully');
                        alert("Employee Removed");

                    }
                }
            });
        }
    }


    function Removeemps() {
        var emp_pkey = $('#employee').val();
        var dayss = $('#present_days_after_resg').html();
        var leaves = $('#encashable_leavebal').html();
        var grativity = $('#total_amt_s').val();
        var total_working = $('#total_working').val(); //Edited by Akshay om 22-8-2024
        var r = confirm("Employee Seperation process will be remove Employee From Our Application,After submission Changes cannot be undone.Also hierarchy assigned employees under this person will be automatically removed. Do you want to continue ? ")
        if (r == true) {

            $.ajax({
                url: livesite + "EmployeeResignation/removeemps",
                type: "POST",
                data: {
                    emp_pkey: emp_pkey,
                    emp: grativity,
                    dayss: dayss,
                    leaves: leaves,
                    total_working: total_working
                },
                success: function(resp) {
                    //                    if (resp == true) {

                    $.notify(resp, {
                        type: 'danger',
                        allow_dismiss: false
                    });
                    //                    $('#loadFullandfinal').html("<h2>Employee Terminated Successfully </h2>");
                    $('#emptable').datagrid('load');
                    $('#contain6').hide();
                    $('.btn-danger').hide();
                    $('#approves').hide();
                    $('#processfullandfinal').attr("disabled", true);
                    //                    }
                }
            });
        } else {

        }
    }

    function loadresignation(s) {
        var employee = $('#employee').val();

        $.ajax({
            url: livesite + "FullandFinalsettlement/getperiod/" + employee,
            type: "POST",
            success: function(resp) {

                //$(s).html('<li class="fa fa-spinner fa-spin"></li>');
                var response = JSON.parse(resp);
                //alert(response.days); 
                if (response.success == 1) {
                    //$(s).fadeOut(90, function () {
                    //                                $('#notice_period').val(response.days);
                    //});
                } else {
                    $('#notice_period').val('0');
                }
            }
        });
        $.ajax({
            url: livesite + "FullandFinalsettlement/details_res/" + employee,
            type: "POST",
            success: function(resp) {
                var response = JSON.parse(resp);
                //alert(response.days); 
                if (response.success == 1) {
                    //$(s).fadeOut(90, function () {
                    var diff = new Date(new Date(response.act_last_working_day) - new Date(response.submitted));
                    // get days
                    var days = diff / 1000 / 60 / 60 / 24 + 1;
                    //alert(days); 
                    $('#notice_period').val(days).html(days);
                    $('#termination_pkey').val(response.LEAVEENTRYID);
                    $('#Reason').val(response.Reason);
                    $('#applied_date').val(response.applied_date);
                    $('#submitted').val(response.submitted);
                    $('#lat_workingday').val(response.last_reason);
                    $('#apprved').val(response.approved);
                    $('#Remarks').val(response.remarks);
                    $('#act_lat_workingday').val(response.act_last_working_day);
                    //});
                }
                showattendancesettleddata();
            }
        });


        $('#assets').load(livesite + "FullandFinalsettlement/Assets/" + employee);
    }

    function showattendancesettleddata() {
        var employee = $('#employee').val();
        var date1 = $('#submitted').val();
        var date2 = $('#apprved').val();
        var date4 = new Date();
        //       next(4);
        $.ajax({
            url: livesite + "EmployeeResignation/workingattendnacedays",
            type: "POST",
            data: {
                emp: employee,
                from: date1,
                todate: date2,
                newdate: date4
            },
            success: function(resps) {
                var response = JSON.parse(resps);
                console.log(response);
                $('#attendance_days').html(response.atte);
                $('#encashable_leaves').html(response.leave);
                var date = new Date($('#apprved').val());
                var date2 = new Date($('#submitted').val());
                //var date4 = new Date();

                //if(date1 = date1){
                var timeDiff = Math.abs(date2.getTime() - date.getTime());
                var diffDays = Math.ceil(timeDiff / (1000 * 3600 * 24));
                //var present_diff = Math.abs(date4.getTime() - date2.getTime());
                //var diffDayspresent = Math.ceil(present_diff / (1000 * 3600 * 24));
                //}else{
                //    var diffDays = 0;
                //}

                $('#notice_period_adjust').html(diffDays);
                $('#click_toview').css("display", "none");
                $('#atte').css("display", "block");
                if (response.Warningd != '') {
                    $.notify(response.Warningd, {
                        type: 'danger',
                        allow_dismiss: false
                    });
                }
                var resig_pkey = $('#termination_pkey').val();
                var sum = 0;
                var leaveName = '';
                $("input[name='encashdys']").each(function() {
                    sum += parseFloat(this.value);
                });

                $("input[name='encash_type']").each(function() {
                    leaveName = this.value.trim();
                });

                $('#encashable_leaves').html(sum);
                $('#encashable_leavebal').html(sum);

                $('#encashable_leavebal').html(response.leave);

                $('#encashable_leaves').html(response.leave);

                var str_company_code = <?php echo json_encode($str_company_code); ?>;
                if (str_company_code == 'KWMT' || str_company_code == 'DEMO') {
                    //Edited by Akshay on 13-12-2023 
                    //hided the condition by sinsiya for water metro start
                    //  if (sum > 0) {
                    //    $('#leave_adjusted').removeAttr('readonly');
                    //} else {
                    //  $('#leave_adjusted').attr('readonly', true);
                    //}
                    //ended by sinsiya for water metro end
                } else {
                    //Edited by Akshay on 13-12-2023
                    if (sum > 0) {
                        $('#leave_adjusted').removeAttr('readonly');
                    } else {
                        $('#leave_adjusted').attr('readonly', true);
                    }
                }
                var leaveList = response.leave_name;
                $('#encashable').html('ENCASHABLE LEAVE ADJUSTED AGAINST NOTICE PERIOD - ' + leaveList);
                $('#encashable_type').val(leaveName);

                //var total = (parseInt(diffDays) + parseInt(response.atte)) - parseInt(response.leave);
                var total = response.atte - diffDays;
                var diff = 0;
                var adjusted = 0;
                if (diffDays > sum) {
                    total = total + sum;
                    adjusted = sum;
                } else {
                    diff = sum - diffDays;
                    total = total + diffDays;
                    adjusted = diffDays;
                }
                $('#encash_leave_bal_set').html(sum).val(sum);
                //                $('#pay_to_be_set').html(total).val(total);
                $('#total_leaveencash').html(sum).val(sum);
                $('#adjusted_notice').html(adjusted).val(adjusted);
                var diffdays_weekof = 0;
                if ($('#notice_period').val() != 0) {
                    diffdays_weekof = $('#notice_period').val() - response.weekoff_couts;
                }

                var balance_workingdays = 0;
                if ($('#notice_period').val() != 0) {
                    if (response.attendance_days_after_resignation + response.weekoff_couts > 0)
                        balance_workingdays = ($('#notice_period').val() - response.weekoff_couts) - ($('#notice_period').val() - (response.attendance_days_after_resignation + response.weekoff_couts));
                }
                //                var balance_workingdays = diffdays_weekof - ($('#notice_period').val()-(response.attendance_days_after_resignation+response.weekoff_couts));
                // var tt = sum - balance_workingdays;
                // if (tt < 0) {
                //     $('#notice_settlement').html('NOTICE PERIOD SETTLEMENT DAYS');
                // } else {
                //     $('#notice_settlement').html('LEAVE ENCASHMENT DAYS');
                // }
                // $('#pay_to_be_set').html(tt).val(tt);
                if (balance_workingdays < 0) {
                    balance_workingdays = 0;
                }
                //                alert($('#notice_period').val()-(response.attendance_days_after_resignation+response.weekoff_couts));
                // $('#balance_working_days').html(balance_workingdays);

                //Edited by Akshay on 21-8-2024
                var res_per_wd = <?php echo json_encode($resignaion_period_working_days); ?>;
                $('#total_working').html(res_per_wd).val(res_per_wd);
                //End
                // $('#total_working').html(diffdays_weekof).val(diffdays_weekof);
                //Edited by Akshay on 21-8-2024
                var present_days_after_resg = <?php echo json_encode($resignaion_period_present_days); ?>;
                $('#present_days_after_resg').val(present_days_after_resg).html(present_days_after_resg);
                var bal_wd = res_per_wd - present_days_after_resg;
                bal_wd = (bal_wd>0)? bal_wd:0;
                $('#balance_working_days').html(bal_wd);
                var tt = sum - bal_wd;
                if (tt < 0) {
                    $('#notice_settlement').html('NOTICE PERIOD SETTLEMENT DAYS');
                } else {
                    $('#notice_settlement').html('LEAVE ENCASHMENT DAYS');
                }
                $('#pay_to_be_set').html(tt).val(tt);
                //End
                // $('#present_days_after_resg').val(presented_after_res).html(presented_after_res);

                $('#fn_leaveencash').html(diff).val(diff);
                //var present_days_after_resg = diffDayspresent;
                var weekoffs = response.attendance_days_after_resignation + response.weekoff_couts;
                var presented_after_res = 0;
                if ($('#notice_period').val() != 0) {
                    presented_after_res = $('#notice_period').val() - (response.attendance_days_after_resignation + response.weekoff_couts);
                }

                //savedetails(employee,resig_pkey,response.atte,response.leave,diffDays,total);
            }
        });
    }

    jQuery(document).ready(function($) {

        $(".btn-primary").not('#btnLeaveUpdate').click(function() {
            var _html = $(this).html();
            // alert("hi");
            $(this).html('<li class="fa fa-spinner fa-spin"></li>');
            setInterval(function() {
                $(this).html(_html);
            }, 3000);
        });

        $(".show-first").prepend('<div class="loader-div"><li class="fa fa-spinner fa-spin"></li></div>');
        setTimeout(function() {
            $(".loader-div").hide();
        }, 1000)
        var emp_pkey = $('#employee').val();
        $('#empleaverequeststable').datagrid({
            url: livesite + "LeaveEncashmentRequest/listallempsforenc",
            pagination: true,
            queryParams: {
                id: emp_pkey
            },
            singleSelect: true,
            pageSize: 10,
            rownumbers: true,
            fitColumns: true,
            pageList: [2, 5, 10, 50, 100],
            columns: [
                [{
                        field: 'item',
                        title: 'Leave Type',
                        width: "20%"
                    },
                    {
                        field: 'leave_encash_limit',
                        title: 'Encash Days',
                        width: "20%"
                    },
                    {
                        field: 'yearlybalance',
                        title: 'yearly Balance',
                        width: "20%"
                    },
                    {
                        field: 'eligible',
                        title: 'Applicable Days',
                        width: "30%"
                    },
                    {
                        field: 'applied',
                        title: '',
                        width: "0%",
                        formatter: function(value, row, index) {
                            if (row.editing == false) {
                                var s = '<a href="#" onclick="saverow(this)">Save</a> ';
                                var c = '<a href="#" onclick="cancelrow(this)">Cancel</a>';
                                return s + c;
                            } else {
                                var leaveName = '<input value="' + row.item + '" type="hidden" name="encash_type" >';
                                var e = '<input value="' + row.eligible + '" type="text" name="encashdys" style="color:black; display:none; "> ';
                                return e + leaveName;
                            }
                        }
                    }
                ]
            ]
        });
        $('#datagrid_employee_leaves').datagrid({
            url: livesite + "FullandFinalsettlement/leavebalance/",
            pagination: true,
            queryParams: {
                null: null
            },
            pageSize: 10,
            rownumbers: true,
            toolbar: [{
                text: 'Approve Selected',
                iconCls: 'icon-add',
                handler: function() {

                    var row = $('#datagrid_employee_leaves').datagrid('getSelections');
                    if (row) {
                        var ss = [];
                        for (var i = 0; i < row.length; i++) {
                            var rows = row[i];
                            ss.push(rows.LEAVEENTRYID);
                        }
                        $.ajax({
                            url: livesite + "FullandFinalsettlement/approve_selectd/",
                            data: {
                                ss: ss
                            },
                            type: "POST",
                            success: function(resp) {
                                $('#datagrid_employee_leaves').datagrid('load')
                                //$(s).html('<li class="fa fa-spinner fa-spin"></li>');
                                $('#empleaverequeststable').datagrid('load');
                            }
                        });
                    } else {
                        alert("Please select a record to edit")
                    }
                }
            }, {
                iconCls: 'icon-edit',
                text: 'Reject Selected',
                handler: function() {
                    var row = $('#datagrid_employee_leaves').datagrid('getSelections');
                    if (row) {
                        var ss = [];
                        for (var i = 0; i < row.length; i++) {
                            var rows = row[i];
                            ss.push(rows.LEAVEENTRYID);
                        }
                        //alert(ss);
                        $.ajax({
                            url: livesite + "FullandFinalsettlement/reject_selected/",
                            type: "POST",
                            data: {
                                ss: ss
                            },
                            success: function(resp) {
                                $('#datagrid_employee_leaves').datagrid('load');
                                $('#empleaverequeststable').datagrid('load');
                            }
                        });
                    } else {
                        alert("Please select a record to edit")
                    }
                }
            }],
            onLoadSuccess: function(data) {
                //                    $.messager.show({
                //                        title:'Info',
                //                        msg:'You have  '+data.total+' Leave Requests'
                //                    });

            },
            fitColumns: true,
            pageList: [2, 5, 10, 50, 100],
            columns: [
                [{
                        field: 'emp_name',
                        title: 'Employee name',
                        width: "20%"
                    },
                    {
                        field: 'leave_type',
                        title: 'Leave Type',
                        width: "10%"
                    },
                    {
                        field: 'leave_days',
                        title: 'LeaveDays',
                        width: "10%"
                    },
                    {
                        field: 'FROMDATE',
                        title: 'From date',
                        width: "10%"
                    },
                    {
                        field: 'TODATE',
                        title: 'To Date',
                        width: "10%"
                    },
                    {
                        field: 'LEAVESTATUS',
                        title: 'Leave Status',
                        width: "20%"
                    },

                ]
            ]
        });
        $('#empleaverequeststable').datagrid('load', {
            id: emp_pkey + '=' + emp_pkey
        });
        $('#datagrid_employee_leaves').datagrid('load', {
            emp_fkey: emp_pkey + '=' + emp_pkey
        });
        loadresignation();

    });

    //Edited by Akshay on 14-12-2023
    function approveLeaveAdjustment() {
        saveButtonClicked = true;
        var leaveadjusted = $('#leave_adjusted').val();
        var balanceAfter = $('#balance_after_adjustment').val();
        var emp_pkey = $('#employee').val();
        var currentLeaveName = $('#encashable_type').val();
        var resignationMonth = $('#resignation_date').val();
        var balance_working_days = $('#balance_working_days').html();
        var total_working = $('#total_working').html();
        var res_present_days = $('#present_days_after_resg').html();
        if (true) {
            // $('#Encash_container').append('<button class="btn btn-primary"><li class="fa fa-spin fa-spinner"></li></button>');
            $.ajax({
                url: livesite + "FullandFinalsettlement/leaveadjustment",
                type: "POST",
                data: {
                    leaveadjusted: leaveadjusted,
                    balanceAfter: balanceAfter,
                    emp_pkey: emp_pkey,
                    currentLeaveName: currentLeaveName,
                    resignationMonth: resignationMonth,
                    total_working_days: total_working,
                    balance_working_days: balance_working_days,
                    res_present_days: res_present_days
                },
                dataType: 'json',
                success: function(resp) {
                    console.log('Resp', resp.success);
                    if (resp.success == true) {
                        $('#btnLeaveUpdate').removeClass('fa-spin');
                        $.notify({
                            message: resp.message
                        }, {
                            type: 'success'
                        });
                        // $("#btnLeaveUpdate").prop("disabled", true);
                    } else {
                        $.notify({
                            message: resp.message
                        }, {
                            type: 'danger'
                        })
                    }
                }
            });
        }
    }

    //Edited by Akshay on 14-11-2023
    function adjustBalance() {
        var initialBalance = parseFloat($('#encashable_leavebal').text()) || 0;
        // Get the value from leave_adjusted input
        var leaveAdjustedValue = parseFloat($('#leave_adjusted').val()) || 0;

        // Perform your adjustment logic here
        var adjustedBalance = initialBalance - leaveAdjustedValue;

        // Update the balance_after_adjustment input
        $('#balance_after_adjustment').val(adjustedBalance.toFixed(1));
    }
</script>