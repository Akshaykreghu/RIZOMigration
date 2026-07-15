<style>
    /*.tabset-processpayroll {
        min-height: 350px !important;
    } */
</style>
<section class="content-header">
    <h1 class="text-primary-18">Payment Approval </h1>
    <hr style="margin-top: 8px;margin-bottom: -2px;">
</section>
<!-- Main content -->
<section class="content" id="div-showprocesspayroll">
    <div class="row">
        <div class="col-md-12">
            <!-- DIRECT CHAT DANGER -->
            <div class=" ">
                <div class="">
                    <!-- Employee import form -->
                    <form class="form-horizontal" method="post" action="" id="payrollfilter">
                        <div class="form-group">
                            <div class="col-md-3">
                                <label class="col-md-4 control-label" for="filterby_branch"> Branch : </label>
                                <div class="col-md-8">
                                    <select id="filterby_branch" name="filterby_branch" class="form-control select2-searching">
                                        <?php
                                        foreach ($arr_branches as $key => $value) {
                                            echo '<option value="' . $value['branch_code'] . '">' . $value['branch_name'] . '</option>';
                                        }
                                        ?>
                                    </select>
                                </div>
                            </div>
                            <!--div class="col-md-3">
                                <label class="col-md-6 control-label">Choose Employee</label>
                                <div class="col-md-6">
                                    <select class="form-control">
                                        <option value="">--All--</option>
                            <?php
                            foreach ($arr_employees as $key => $value) {
                                echo '<option value="' . $value['emp_pkey'] . '">' . $value['emp_name'] . '</option>';
                            }
                            ?>
                                    </select>
                                </div>
                            </div-->
                            <div class="col-md-3">
                                <label class="col-md-4 control-label" for="filterby_month"> Month : </label>
                                <div class="col-md-8">
                                    <select id="filterby_month" name="filterby_month" class="form-control">
                                        <?php
                                        $start_month = strtotime(date('Y-m', strtotime("+1 month", strtotime(date('Y-m')))));
                                        for ($i = 0; $i < 20; $i++) {
                                            $month = date('Y-m', strtotime("-$i month", $start_month));
                                            if ($month == date('Y-m')) {
                                                echo '<option selected="selected" value="' . $month . '">' . date('M-Y', strtotime("-$i month", $start_month)) . '</option>';
                                            } else {
                                                echo '<option value="' . $month . '">' . date('M-Y', strtotime("-$i month", $start_month)) . '</option>';
                                            }
                                        }
                                        ?>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="col-md-12">
                                    <button type="button" class="btn btn-primary" onclick="filterPayroll();">List</button>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-md-12">
            <!-- Employee List -->
            <!-- DIRECT CHAT DANGER -->
            <div class="box ">
                <!--                <div class="box-header with-border">
                    <div class="col-md-12 col-sm-12 col-xs-12 col-lg-6">
                        <h3 class="box-title"> Payroll for  <label id="current_register_month"><?php echo date('M-Y'); ?></label></h3>
                    </div>
                </div>-->
                <div class="box-body">
                    <div class="tabset-processpayroll">
                        <div id="tab1" data-pws-tab="tab1" data-pws-tab-name="Not Processed" data-pws-tab-icon="fa-money">

                        </div>
                        <div id="tab2" data-pws-tab="tab2" data-pws-tab-name="Processed" data-pws-tab-icon="fa-money">

                        </div>
                    </div>
                </div><!-- /.box-body -->

            </div><!--/.direct-chat -->
        </div><!-- /.col -->
    </div>
</section>
<script>
    jQuery(document).ready(function() {
        $('#filterby_branch').select2();
        $('#filterby_month').select2();
        $('.tabset-processpayroll').pwstabs({
            effect: 'scale', // You can change effects of your tabs container: scale / slideleft / slideright / slidetop / slidedown / none
            defaultTab: 1, // The tab we want to be opened by default
            containerWidth: '100%', // Set custom container width if not set then 100% is used
            tabsPosition: 'horizontal', // Tabs position: horizontal / vertical
            horizontalPosition: 'top', // Tabs horizontal position: top / bottom
            verticalPosition: 'left', // Tabs vertical position: left / right
            responsive: true, // Make tabs container responsive: true / false - boolean
            theme: '',
            rtl: false // Right to left support: true/ false
        });
        // Edited by Akshay on 26-9-2023
        var month = $('#payrollfilter #filterby_month').val();
        var branch = $('#payrollfilter #filterby_branch').val();
        $('.tabset-processpayroll #tab1').load(livesite + "PayrollProcess/paymentpayrolltab/0" + "/" + month + "/" + branch);
    });

    jQuery('#div-showprocesspayroll .pws_tabs_controll a').on('click', function() {
        var tabIndex = $(this).data('tabId');
        var month = $('#payrollfilter #filterby_month').val(); //Edited by Akshay on 21-9-2023
        var branch = $('#payrollfilter #filterby_branch').val(); //Edited by Akshay on 22-9-2023
        if (tabIndex == "tab1") {
            $('.tabset-processpayroll #tab1').load(livesite + "PayrollProcess/paymentpayrolltab/0/" + month + '/' + branch);
        } else {
            $('.tabset-processpayroll #tab2').load(livesite + "PayrollProcess/paymentpayrolltab/1/" + month + '/' + branch);
        }
    });

    var monthNames = ["Jan", "Feb", "Mar", "Apr", "May", "Jun", "Jul", "Aug", "Sep", "Oct", "Nov", "Dec"];

    function filterPayroll(obj) {

        var month = $('#payrollfilter #filterby_month').val();
        var branch = $('#payrollfilter #filterby_branch').val();

        var dt = new Date(month);
        $('#current_register_month').html(monthNames[dt.getMonth()] + '-' + dt.getFullYear());
        $.ajax({
            url: livesite + "PayrollProcess/FilterList",
            type: 'post',
            data: {
                branch: branch,
                month: month,
            },
            success: function(response) {
                //process server response here
                var success = $.parseJSON(response).success;
                if (success) {
                    $.notify("Payroll listed successfully", {
                        type: 'success',
                        allow_dismiss: false
                    });
                    $('.tabset-processpayroll #tab1').load(livesite + "PayrollProcess/paymentpayrolltab/0", {
                        month: month,
                        branch: branch
                    });
                    $('.tabset-processpayroll #tab2').load(livesite + "PayrollProcess/paymentpayrolltab/1", {
                        month: month,
                        branch: branch
                    });
                    $('#payrolltable').datagrid('load');
                } else {
                    //alert('Attendance process failed!');
                    $.notify("Payroll listing failed!", {
                        type: 'danger',
                        allow_dismiss: false
                    });
                }
            }
        });

    }

    function payrollapprovePayroll(remarks) {
        console.log('Remarks', remarks);
        var remarks = remarks;
        var branch = $('#payrollfilter #filterby_branch').val();
        var month = $('#payrollfilter #filterby_month').val();
        var tax = $('#addtax').val();
        var arr_emp_pkeys = [];
        var arr_payroll_pkeys = [];
        // var checkedRows = $('#payrolltable').datagrid('getChecked');
        var checkedRows = $('#payrolltable').datagrid('getRows');
        console.log('checkedRows', checkedRows);
        for (var register in checkedRows) {
            arr_emp_pkeys.push(checkedRows[register]['emp_fkey']);
            arr_payroll_pkeys.push(checkedRows[register]['payroll_master_pkey']);
        }
        $.ajax({
            url: livesite + "PayrollProcess/PaymentapprovePayroll",
            type: 'post',
            data: {
                branch: branch,
                month: month,
                tax: tax,
                emp_pkey: arr_emp_pkeys.join(','),
                payroll_pkey: arr_payroll_pkeys.join(','),
                remarks: remarks
            },
            success: function(response) {
                //process server response here
                var success = $.parseJSON(response).success;
                if (success) {
                    var remark = $('#remarks').val().trim(); // Get the value of the remarks field and remove leading/trailing whitespace

                    if (remark !== '') {
                        $.notify("Payment approval processed.", {
                            type: 'success',
                            allow_dismiss: false,
                        });
                        // Close the modal after a successful response using jQuery
                        $('.modal-dialog button[data-dismiss="modal"]').click(); // Simulate click on the close button

                        $('#payrolltable').datagrid('load', {
                            branch: branch,
                            month: month
                        });
                        $('#processedpayrolltable').datagrid('load', {
                            branch: branch,
                            month: month
                        });
                    }


                } else {
                    //alert('Attendance process failed!');
                    $.notify("Payroll process failed!", {
                        type: 'danger',
                        allow_dismiss: false
                    });
                }
            }
        });
    }

    function holdProcessPayroll() {
        var branch = $('#payrollfilter #filterby_branch').val();
        var month = $('#payrollfilter #filterby_month').val();

        var arr_emp_pkeys = [];
        var arr_payroll_pkeys = [];
        var checkedRows = $('#processedpayrolltable').datagrid('getChecked');
        for (var register in checkedRows) {
            arr_emp_pkeys.push(checkedRows[register]['emp_fkey']);
            arr_payroll_pkeys.push(checkedRows[register]['payroll_master_pkey']);
        }
        $.ajax({
            url: livesite + "PayrollProcess/holdProcessPayroll",
            type: 'post',
            data: {
                branch: branch,
                month: month,
                emp_pkey: arr_emp_pkeys.join(','),
                payroll_pkey: arr_payroll_pkeys.join(',')
            },
            success: function(response) {
                //process server response here
                var success = $.parseJSON(response).success;
                if (success) {
                    $.notify("Payroll hold successfully", {
                        type: 'success',
                        allow_dismiss: false
                    });
                    $('#processedpayrolltable').datagrid('load', {
                        branch: branch,
                        month: month
                    });
                } else {
                    //alert('Attendance process failed!');
                    $.notify("Payroll hold failed!", {
                        type: 'danger',
                        allow_dismiss: false
                    });
                }
            }
        });
    }

    function rejectPayrollEntry() {
        var canRemove = true;

        var branch = $('#payrollfilter #filterby_branch').val();
        var month = $('#payrollfilter #filterby_month').val();

        var arr_emp_pkeys = [];
        var arr_payroll_pkeys = [];
        // var checkedRows = $('#payrolltable').datagrid('getChecked');
        var checkedRows = $('#payrolltable').datagrid('getRows');
        for (var register in checkedRows) {
            if (checkedRows[register]['action'] == 'Approved') {
                canRemove = false;
                break;
            }
            arr_emp_pkeys.push(checkedRows[register]['emp_fkey']);
            arr_payroll_pkeys.push(checkedRows[register]['payroll_master_pkey']);
        }
        if (canRemove) {
            $.ajax({
                url: livesite + "PayrollProcess/removePayrollEntry",
                type: 'post',
                data: {
                    branch: branch,
                    month: month,
                    emp_pkey: arr_emp_pkeys.join(','),
                    payroll_pkey: arr_payroll_pkeys.join(','),
                    final_status: 'salaryapproval',
                    reverse_remarks: remarks
                },
                success: function(response) {
                    //process server response here
                    var success = $.parseJSON(response).success;
                    if (success) {
                        $.notify("Payment approval removed.", {
                            type: 'success',
                            allow_dismiss: false
                        });
                        $('#processedpayrolltable').datagrid('load', {
                            branch: branch,
                            month: month
                        });
                    } else {
                        //alert('Attendance process failed!');
                        $.notify("Payment approval removal failed!", {
                            type: 'danger',
                            allow_dismiss: false
                        });
                    }
                }
            });
        } else {
            $.notify("You cannot remove an approved entry!", {
                type: 'danger',
                allow_dismiss: false
            });
        }
    }

    //Resize active tab height
    //07 July 2018
    function resizePWSTabContentDiv(tabIndex) {
        if ($("div#tab" + tabIndex).hasClass("pws_tabs_scale_show")) {
            var divHeight = $("div#tab" + tabIndex).height();
            $("div.tabset-processpayroll").height(divHeight);
        }
    }
</script>