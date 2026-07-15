<style>
    /*.tabset-processpayroll {
        min-height: 350px !important;
    } */  
        /* edited by bindu 24-10-25 */
        .heading {
        display: flex;
        flex-direction: row;
        align-items: center;
        justify-content: space-between;
        /* margin-left: 20px; */
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
    /* <!-- edited by bindu 24-10-25 --> */
</style>
<section class="content-header heading">
      <!-- edited by athira on 03-07-2025 -->
        <!-- /* edited by bindu 24-10-25 */ -->
    <h1 class="text-primary-18">Process Payroll</h1>
   <?php if ($plan !== 'basic') : ?>
    
    <div class="text-primary-16 home"
         style="display:flex; align-items:center; gap:10px; cursor:pointer;">
        <i class="fa" style="font-size:16px;">&#xf104;</i>
        Back
    </div>

<?php endif; ?>

    <!-- end -->
   
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
                                     <select id="filterby_branch" name="filterby_branch" class="form-control select2-searching" onchange="filterRegister(this);">
                                        <?php
                                        // Edited by Akshay on 24-10-2025
                                        foreach ($arr_branches as $key => $value) {
                                            echo '<option value="' . $value['branch_code'] . '">' . $value['branch_name'] . '</option>';
                                        }
                                        // End
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
                                    <select id="filterby_month" name="filterby_month" class="form-control" >
                                        <?php
                                        $start_month = strtotime(date('Y-m', strtotime("+1 month", strtotime(date('Y-m')))));
                                        for ($i = 0; $i < 44; $i++) {
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
    jQuery(document).ready(function () {
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
            rtl: false                    // Right to left support: true/ false
        });
        $('.tabset-processpayroll #tab1').load(livesite + "payroll/showprocesspayrolltab/0");
        //$('.tabset-processpayroll #tab2').load(livesite + "payroll/showprocesspayrolltab/1");
    });
	
	jQuery('#div-showprocesspayroll .pws_tabs_controll a').on('click', function(){
		var tabIndex = $(this).data('tabId');
		if(tabIndex == "tab1"){
			$('.tabset-processpayroll #tab1').load(livesite + "payroll/showprocesspayrolltab/0");
		}else{
			$('.tabset-processpayroll #tab2').load(livesite + "payroll/showprocesspayrolltab/1");
		}
	});

    var monthNames = ["Jan", "Feb", "Mar", "Apr", "May", "Jun", "Jul", "Aug", "Sep", "Oct", "Nov", "Dec"];
    function filterPayroll(obj) {

        var month = $('#payrollfilter #filterby_month').val();
        var branch = $('#payrollfilter #filterby_branch').val();

        var dt = new Date(month);
        $('#current_register_month').html(monthNames[dt.getMonth()] + '-' + dt.getFullYear());
        $.ajax({
            url: livesite + "payroll/FilterList",
            type: 'post',
            data: {
                branch: branch,
                month: month,
            },
            success: function (response) {
                //process server response here
                var success = $.parseJSON(response).success;
                if (success) {
                    $.notify("Payroll listed successfully", {
                        type: 'success',
                        allow_dismiss: false
                    });
                    $('.tabset-processpayroll #tab1').load(livesite + "payroll/showprocesspayrolltab/0", {month: month, branch: branch});
                    $('.tabset-processpayroll #tab2').load(livesite + "payroll/showprocesspayrolltab/1", {month: month, branch: branch});
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

    function processPayroll() {
        var branch = $('#payrollfilter #filterby_branch').val();
        var month = $('#payrollfilter #filterby_month').val();
        var tax = $('#addtax').val();
        var arr_emp_pkeys = [];
        var arr_payroll_pkeys = [];
        var checkedRows = $('#payrolltable').datagrid('getChecked');
        for (var register in checkedRows) {
            arr_emp_pkeys.push(checkedRows[register]['emp_fkey']);
            arr_payroll_pkeys.push(checkedRows[register]['payroll_master_pkey']);
        }
        $.ajax({
            url: livesite + "payroll/processpayroll",
            type: 'post',
            data: {
                branch: branch,
                month: month,
                tax: tax,
                emp_pkey: arr_emp_pkeys.join(','),
                payroll_pkey: arr_payroll_pkeys.join(',')
            },
            success: function (response) {
                //process server response here
                var success = $.parseJSON(response).success;
                if (success) {
                    $.notify("Payroll processed successfully", {
                        type: 'success',
                        allow_dismiss: false
                    });
                    $('#payrolltable').datagrid('load', {
                        branch: branch,
                        month: month
                    });
                    $('#processedpayrolltable').datagrid('load', {
                        branch: branch,
                        month: month
                    });
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
            url: livesite + "payroll/holdProcessPayroll",
            type: 'post',
            data: {
                branch: branch,
                month: month,
                emp_pkey: arr_emp_pkeys.join(','),
                payroll_pkey: arr_payroll_pkeys.join(',')
            },
            success: function (response) {
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
    
    function removePayrollEntry() {
        var canRemove = true;
        
        var branch = $('#payrollfilter #filterby_branch').val();
        var month = $('#payrollfilter #filterby_month').val();

        var arr_emp_pkeys = [];
        var arr_payroll_pkeys = [];
        var checkedRows = $('#processedpayrolltable').datagrid('getChecked');
        for (var register in checkedRows) {
            if(checkedRows[register]['action'] == 'Approved'){
                canRemove = false;
                break;
            }
            arr_emp_pkeys.push(checkedRows[register]['emp_fkey']);
            arr_payroll_pkeys.push(checkedRows[register]['payroll_master_pkey']);
        }
        if(canRemove){
            $.ajax({
                url: livesite + "payroll/removePayrollEntry",
                type: 'post',
                data: {
                    branch: branch,
                    month: month,
                    emp_pkey: arr_emp_pkeys.join(','),
                    payroll_pkey: arr_payroll_pkeys.join(',')
                },
                success: function (response) {
                    //process server response here
                    var success = $.parseJSON(response).success;
                    if (success) {
                        $.notify("Entries removed successfully", {
                            type: 'success',
                            allow_dismiss: false
                        });
                        $('#processedpayrolltable').datagrid('load', {
                            branch: branch,
                            month: month
                        });
                    } else {
                        //alert('Attendance process failed!');
                        $.notify("Entries removal failed!", {
                            type: 'danger',
                            allow_dismiss: false
                        });
                    }
                }
            });
        }else{
            $.notify("You cannot remove an approved entry!", {
                type: 'danger',
                allow_dismiss: false
            });
        }
    }
    
    //Resize active tab height
    //07 July 2018
    function resizePWSTabContentDiv(tabIndex){
        if($("div#tab" + tabIndex).hasClass("pws_tabs_scale_show")){
            var divHeight = $("div#tab" + tabIndex).height();
            $("div.tabset-processpayroll").height(divHeight);
        }
    }
       

    $(".home").on("click", function () {

    $("#container").isLoading({
        text: "Loading",
        position: "overlay",
    });

    let url = "";
    var userGroup = <?php echo json_encode($this->Session->read('user_group')); ?>;

    if (userGroup == "1") {
        url = livesite + "SalaryProcessing/index";
    } 
    else if (userGroup == "2") {
        url = livesite + "EmployeeMenu/addon";
    }

    $("#container").load(url, function () {
        isDashboardShown = false;
    });

});
</script>