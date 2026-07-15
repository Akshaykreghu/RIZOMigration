<script>
    var controllerName = "<?php echo isset($hierarchy) ? 'Empattendance' : 'Attendance'; ?>";
</script>
<style>
    .tabset-attendanceregister {
        min-height: 425px !important;
    }

    .att-register-entry-first-half {
        float: left;
        width: 50%;
        padding: 14% 0;
    }

    .att-register-entry-second-half {
        float: right;
        width: 50%;
        padding: 14% 0;
    }

    .att-register-entry-full-day {
        width: 100%;
        padding: 14% 0;
    }

    #tab1 .datagrid-cell,
    #tab2 .datagrid-cell {
        /*padding: 0 !important;*/
    }

    .datagrid-row{
        height: 40px;
    }
    .datagrid-header-row {
        height: 36px;
    }

    .form-horizontal .control-label {

        text-align: left;
        padding-left: 2px;
    }

    /*Edited by Ashin on 02-04-2024*/
    .timer-label-container {
        position: absolute;
        top: 100%;
        right: -43%;
        transform: translateX(-50%);
        background-color: rgba(255, 255, 255, 0.8);
        padding: 10px;
        font-family: 'Roboto Light', sans-serif;
    }

    /*Edited by Ashin on 08-02-24*/
    .cycle-tab-container {
        display: none;
        margin: 50px auto;
        width: 700px;
        padding: 20px;
        box-shadow: 0 0 10px 2px #ddd;
    }

    .cycle-tab-container a {
        color: #173649;
        font-size: 16px;
        font-family: roboto;
        text-align: center;
    }

    .tab-pane {
        text-align: top;
        height: 90px !important;
        margin: 30px auto;
        width: 650px;
        max-width: 150%;
    }

    .fade {
        opacity: 0;
        transition: opacity 50ms ease-in-out;
    }

    .fade.active {
        opacity: 1;
    }

    .cycle-tab-item {
        width: 160px;
    }

    .cycle-tab-item:after {
        display: block;
        content: '';
        border-bottom: solid 3px orange;
        transform: scaleX(0);
        transition: transform 0ms ease-out;
    }

    .cycle-tab-item.active:after {
        transform: scaleX(1);
        transform-origin: 0% 50%;
        transition: transform 1000ms ease-in;
    }

    .nav-link:focus,
    .nav-link:hover,
    .cycle-tab-item.active a {
        border-color: transparent !important;
        color: orange;
    }
     /* <!-- edited by bindu 21-11-2025 --> */
     .heading {
        display: flex;
        flex-direction: row;
        align-items: end;
        justify-content: space-between;
        /* margin-left: 20px; */
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
     /* <!-- edited by bindu 21-11-2025 --> */
</style>
<section class="content-header heading">
      <!-- edited by athira on 03-07-2025 -->
    <h1 class="col-md-4 text-primary-18">Attendance Register</h1>
    <!-- end -->
    <!-- edited by athira on 02-04-2025 -->
    <!-- <div class="col-md-8" align="right">
        <button onclick="updateamendmence();" title="If your datas not being proccessed, Please click this button to update all the entries again" id="updateamendance" class="btn btn-primary pull-right pull-up">Refresh</button>
    </div> -->
    <!-- end -->

 <!-- edited by bindu 21-11-2025 -->
  <?php if ($plan !='basic'){ ?>
               <div class="text-primary-16 home" style="display:flex; align-items:center; gap:10px; cursor:pointer;">
        <i class="fa" style="font-size:16px;">&#xf104;</i>
        Back
    </div>
    <?php } ?>
     <!-- edited by bindu 21-11-2025 -->
</section>
<hr style="margin: 5px 15px -2px 25px;">

<!--<section style="margin-bottom: -24px;" class="content-header">
    <div class="box box-header">
        <h1 style="float:left;"> Attendance Register for <label id="current_register_month"><?php echo date('M-Y'); ?></h1>
        <button onclick="updateamendmence();" title="If your datas not being proccessed, Please click this button to update all the entries gain" id="updateamendance" class="btn btn-primary pull-right pull-up" style="margin:20px;">Update Amendments</button>
    </div>
</section>-->
<!-- Main content -->

<section class="content" id="div-showregister">
    <div class="col-md-12">
        <br>
        <form class="form-horizontal" method="post" action="" id="attendanceregisterfilter">
            <div class="form-group">
                <div class="col-md-7">
                    <div class="col-md-5"><!--edited by ASHIN on 27-05-24 -->
                        <label class="col-md-3 control-label" for="filterby_branch">Branch</label>
                        <div class="col-md-1">:</div>
                        <div class="col-md-7">
                            <select id="filterby_branch" name="filterby_branch" class="form-control select2-searching" onchange="filterRegister(this);">
                                <option value="">Select</option>
                                <?php
                                foreach ($arr_branches as $key => $value) {
                                    echo '<option value="' . $value['branch_code'] . '">' . $value['branch_name'] . '</option>';
                                }
                                ?>
                            </select>
                        </div>
                    </div>
                    <!--div class="col-md-4">
                        <label class="col-md-5 control-label" for="filterby_employee">Choose Employee</label>
                        <div class="col-md-7">
                            <select id="filterby_employee" name="filterby_employee" class="form-control" onchange="filterRegister(this);" >
                                <option value="">--All--</option>
                    <?php
                    foreach ($arr_employees as $key => $value) {
                        echo '<option value="' . $value['emp_pkey'] . '">' . $value['emp_name'] . '</option>';
                    }
                    ?>
                            </select>
                        </div>
                    </div-->
                    <div class="col-md-5">
                        <label class="col-md-3 control-label" for="filterby_month">Month</label>
                        <div class="col-md-1">:</div>
                        <div class="col-md-7">
                            <select id="filterby_month" name="filterby_month" class="form-control" onchange="filterRegister(this);">
                                <?php
                                /*
                                 * By santhosh on 27 Dec 2015
                                 */
                                $start_month = strtotime(date('Y-m', strtotime("+1 month", strtotime(date('Y-m')))));
                                for ($i = 0; $i < 24; $i++) { // Edited by Akshay on 11-4-2025
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
                    <div class="col-md-2">
                        <div class="col-md-5">
                            <div style='display:flex;justify-content:space-between;align-items:center'>
                                <!-- edited by athira on 02-04-2025 -->
                                <button type="button" id="btn-viewregister" class="btn btn-success" style='margin:0 2px;padding:6px 22px;' onclick="viewRegisterEntries1();">View</button>
                                <!-- end -->
                                <button type="button" id="btn-processregister" class="btn btn-primary" onclick="processRegisterEntries1();">Process</button>
                            </div>
                        </div>

                    </div>
                    <!-- Edited by Ashin on 03-04-2024-->
                   
                </div>
                <!--                  <div class="timer-label-container" style="margin-right: -25px;">
                   <label id="timer-label">Attendance Loaded in 0 sec</label>
                    </div>-->
               <div class="col-md-1">
                            </div>
                <div class="col-md-4" align="right" style="text-align: start;">
                        <!-- <div class="timer-label-container " style="margin-right: -25px;"> -->
                        <label id="timer-label"> Attendance Loaded in 0 sec</label>
                    </div>
                    <!--a href="#" class="btn btn-primary" onclick="downloadReport('pdf');" ><i class="fa fa-download"></i> PDF</a-->
                    <!--a href="#" class="btn btn-primary" onclick="downloadReport('excel');"><i class="fa fa-download"></i> Excel</a-->
                </div>
            </div>
        </form>
        <div>
            <br>
            <!--Edited by Ashin on 08-02-24-->
            <div class="cycle-tab-container" id="processCycleTabContainer">
                <ul class="nav nav-tabs">
                    <li class="cycle-tab-item active">
                        <a class="nav-link" role="tab" data-toggle="tab" href="#home">Fetching Employees</a>
                    </li>
                    <li class="cycle-tab-item">
                        <a class="nav-link" role="tab" data-toggle="tab" href="#profile">Attendance Register</a>
                    </li>
                    <li class="cycle-tab-item">
                        <a class="nav-link" role="tab" data-toggle="tab" href="#messages">Attendance Mapping</a>
                    </li>
                    <li class="cycle-tab-item">
                        <a class="nav-link" role="tab" data-toggle="tab" href="#settings">Updating the Register</a>
                    </li>
                </ul>
                <div class="tab-content">
                    <div class="tab-pane fade active in" id="home" role="tabpanel" aria-labelledby="home-tab"> Searching for the employees who belong to the corresponding branch, have a joining date before the last day of the selected month, and are allocated with shift policy, holiday policy, leave policy, and salary structure.</div>
                    <div class="tab-pane fade" id="profile" role="tabpanel" aria-labelledby="profile-tab"> Removing previous entries from the attendance register for unverified employees belonging to the selected branch and month respectively. </div>
                    <div class="tab-pane fade" id="messages" role="tabpanel" aria-labelledby="messages-tab"> Checking the attendance status of each employee involves reviewing their attendance records in accordance with their specific shift policy settings..</div>
                    <div class="tab-pane fade" id="settings" role="tabpanel" aria-labelledby="settings-tab"> Updating the attendance status for the entire month in the attendance register involves accurately recording each employee's attendance daily.</div>
                </div>
            </div>
            <!--div class="box">
                <div class="box-body">
            <?php foreach ($arr_registerentries as $key => $entry) { ?>
                                                            <div class="col-md-3">
                                                                <div class="col-md-4" style="color:<?php echo $entry['textColor'] ?>;background-color: <?php echo $entry['color'] ?>"><?php echo $key; ?></div>
                                                                <div class="col-md-8"><?php echo $entry['label'] ?></div>
                                                            </div>
            <?php } ?>
                </div>
            </div-->

            <!-- Nav tabs -->
            <!--ul id="tabs-attendanceregister" class="nav nav-tabs" role="tablist">
              <li id="li-register-toverify" role="presentation" class="active"><a href="#div-register-toverify" aria-controls="div-register-toverify" role="tab" data-toggle="tab">To Verify</a></li>
              <li id="li-register-verified" role="presentation"><a href="#div-register-verified" aria-controls="div-register-verified" role="tab" data-toggle="tab">Verified</a></li>
            </ul-->
            <!-- Nav tabs -->

            <!--div class="tabset-attendanceregister" style="overflow-y:auto"-->
            <div class="box-body" style="margin-top: -11px;">
                <div class="tabset-attendanceregister">
                    <div id="tab1" data-pws-tab="tab1" data-pws-tab-name="Not Verified" data-pws-tab-icon="fa-spinner fa-spin">
                    </div>
                    <div id="tab2" data-pws-tab="tab2" data-pws-tab-name="Verified" data-pws-tab-icon="fa-spinner fa-spin">
                    </div>
                </div>
            </div>
            <!--form class="form-horizontal" method="post" action="" id="form-showreport">
             <div class="row">
        <div class="form-group">
        <div class="col-md-12" align="right">
        <a href="#" class="btn btn-default" onclick="downloadReport('pdf');" ><i class="icon-file"></i>Download  As PDF</a>
        <a href="#" class="btn btn-default" onclick="downloadReport('excel');"><i class="icon-file"></i>Download As Excel</a>
        </div>
        </div>
        </div>   
            </form-->

            <!--div class="tab-container tab-content">
                <div role="tabpanel"  id="div-register-toverify" class="row tab-pane active">
                </div>
                <div role="tabpanel" id="div-register-verified" class="row tab-pane">
                </div>
            </div-->
        </div><!-- /.box-body -->
        <div class="box-footer">
            <div class="row">
                <div class="col-md-12">
                    <?php foreach ($arr_registerentries as $key => $entry) { ?>
                        <div class="col-md-3" style="margin-top: 10px;">
                            <div class="col-md-5" style="color:<?php echo $entry['textColor'] ?>;background-color: <?php echo $entry['color'] ?>"><?php echo $key; ?></div>
                            <div class="col-md-7"><?php echo $entry['label'] ?></div>
                        </div>
                    <?php } ?>
                </div>
            </div>
        </div><!-- /.box-footer -->

    </div><!--/.direct-chat -->

    <!--<button onclick="loadmsg();" class="btn btn-primary">Load Tables</button>-->
</section>

<script>
    var str_leaveabbr = "<?php echo $str_leaveabbr; ?>";
    var arr_leaveabbr = str_leaveabbr.split("#");
    var arr_other_regentries = ['COFF', 'WFH',  'NA', 'OTHERS'];
    jQuery(document).ready(function() {
        //        $('.tabset-attendanceregister #tab1').load(livesite + controllerName + "/showregistertab/0");
        //        $('.tabset-attendanceregister #tab2').load(livesite + controllerName + "/showregistertab/1");
        processRegisterEntries();
        $('#filterby_branch').select2();
        $('#filterby_month').select2();
        $('.tabset-attendanceregister').pwstabs({
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
    });

    jQuery('#div-showregister .pws_tabs_controll a').on('click', function() {
        var tabIndex = $(this).data('tabId');
        reloadAttendanceRegister(tabIndex);
    });

    function updateRegisterEntries(registerid) {
        if (registerid) {
            //Check for any null values in register for selected employee
            var startdate = $('#hidden-start-date').val();
            var enddate = $('#hidden-end-date').val();
            $.ajax({
                url: livesite + controllerName + "/checkifregistercanverify/" + registerid,
                type: 'post',
                dataType: 'json',
                data: {
                    startdate: startdate,
                    enddate: enddate
                },
                success: function(response) {
                    if (Object.keys(response).length) {
                        var url = livesite + controllerName + "/updateregisterentries/" + registerid;
                        $("#modalForm #modalForm-content").load(url, {
                            dates: JSON.stringify(response)
                        }, function() {
                            $("#modalForm").modal('show')
                        });
                    } else {
                        //alert("Please select atleast one record to verify");
                        $.notify("You can verify now.", {
                            type: 'success',
                            allow_dismiss: false
                        });
                        return false;
                    }
                }
            });
        } else {

        }
    }
    //edited by megha on 18/11/2019 Attendance reversal
    function removeAttendanceEntry() {

        var canRemove = true;


        //alert(branch);

        var arr_emp_pkeys = [];
        var arr_att_pkeys = [];
        var checkedRows = $('#verifiedattendanceregistertable').datagrid('getChecked');
        console.log(checkedRows.length);
        if (checkedRows && checkedRows.length > 0) {
            for (var register in checkedRows) {

                //if(checkedRows[register]['action'] == 'Approved' || checkedRows[register]['action'] == 'Processed'){
                // canRemove = false;
                //  break;
                // }
                var data = $('#verifiedattendanceregistertable').datagrid('getData');
                arr_emp_pkeys.push(checkedRows[register]['emp_fkey']);
                arr_att_pkeys.push(checkedRows[register]['registerid']);

            }
        } else {
            canRemove = false;
        }
        var branch = $('#attendanceregisterfilter #filterby_branch').val();
        var month = $('#attendanceregisterfilter #filterby_month').val();
        var employee = $('#filterby_employee').val();
        if (canRemove) {
            $.ajax({
                url: livesite + "Attendance/removeAttendanceEntry",
                type: 'post',
                data: {
                    branch: branch,
                    month: month,
                    emp_pkey: arr_emp_pkeys.join(','),
                    payroll_pkey: arr_att_pkeys.join(',')
                },

                //edited by athira on 05-06-2025
                success: function(response) {
                    //process server response here
                    var success = $.parseJSON(response).success;
                    console.log(success);
                    if (success) {
                        $.notify("Entries removed successfully", {
                            type: 'success',
                            allow_dismiss: false
                        });
                        $('#verifiedattendanceregistertable').datagrid('load', {
                            branch: branch,
                            employee: employee,
                            month: month
                        });
                    } else{
                        //alert('Attendance process failed!');
                        $.notify("You cannot remove an processed/approved payroll entry!!", {
                            type: 'danger',
                            allow_dismiss: false
                        });
                        $('#verifiedattendanceregistertable').datagrid('load', {
                            branch: branch,
                            employee: employee,
                            month: month
                        });
                    }
                }
            });
        } else {
            if (checkedRows && checkedRows.length > 0) {
                $.notify("You cannot remove an processed/approved payroll entry!", {
                    type: 'danger',
                    allow_dismiss: false
                });
                $('#verifiedattendanceregistertable').datagrid('load', {
                    branch: branch,
                    employee: employee,
                    month: month
                });
            }
            if (checkedRows && checkedRows.length == 0) {
                //else{
                $.notify("Please Select a row.", {
                    type: 'danger',
                    allow_dismiss: false
                });
            }
        }
    }

    function verifyRegisterEntries(registerid) {
        if (registerid) {
            /*//Check for any null values in register for selected employee
             var startdate = $('#hidden-start-date').val();
             var enddate = $('#hidden-end-date').val();
             $.ajax({
             url: livesite + "Attendance/checkifregistercanverify/"+registerid,
             type: 'post',
             data: {
             startdate: startdate,
             enddate: enddate
             },
             success: function (response) {
             if(response.length){
             var url = livesite + "Attendance/updateregisterentries/"+registerid;
             $("#modalForm #modalForm-content").load(url, {
             dates: response
             }, function() {
             $("#modalForm").modal('show')
             });
             }else{
             doVerificationProcedure(registerid);
             }
             }
             });*/
            doVerificationProcedure(registerid);
        } else {
            //Verify all entries : On 21 Feb 2016
            //var rows = $('#attendanceregistertable').datagrid('getSelections');
            var data = $('#attendanceregistertable').datagrid('getData');
            var rows = data.rows;
            if (rows) {
                var str_ids = "";
                for (var i = 0; i < rows.length; i++) {
                    var data = rows[i];
                    if (str_ids == "") {
                        str_ids += data.registerid;
                    } else {
                        str_ids += "," + data.registerid;
                    }
                }
            }
            if (str_ids == '') {
                //alert("Please select atleast one record to verify");
                $.notify("Please select atleast one record to verify", {
                    type: 'warning',
                    allow_dismiss: false
                });
                return false;
            }
            doVerificationProcedure(str_ids);
        }
    }

    function doVerificationProcedure(str_ids) {
        var branch = $('#attendanceregisterfilter #filterby_branch').val();
        var employee = $('#filterby_employee').val();
        var month = $('#attendanceregisterfilter #filterby_month').val();

        //Modified on 21 Feb 2016 : passed start and end dates on verify register
        var startdate = $('#hidden-start-date').val();
        var enddate = $('#hidden-end-date').val();

        if (confirm("Are you sure to verify this register ?")) {
            $.ajax({
                url: livesite + controllerName + "/verifyregisterentries",
                type: 'post',
                data: {
                    ids: str_ids,
                    startdate: startdate,
                    enddate: enddate,
                    branch: branch //Edited by Akshay on 9-10-2024
                },
                success: function(response) {
                    //process server response here
                    var success = $.parseJSON(response).success;
                    if (success) {
                        //alert('Attendance verified successfully');
                        $.notify("Attendance verified successfully", {
                            type: 'success',
                            allow_dismiss: false
                        });
                        $('#attendanceregistertable').datagrid('load', {
                            branch: branch,
                            employee: employee,
                            month: month
                        });
                        $('#verifiedattendanceregistertable').datagrid('load', {
                            branch: branch,
                            employee: employee,
                            month: month
                        });
                    } else {
                        //alert('Attendance verification failed!');
                        $.notify("Attendance verification failed!", {
                            type: 'danger',
                            allow_dismiss: false
                        });
                    }
                }
            });
        }
    }

    var monthNames = ["Jan", "Feb", "Mar", "Apr", "May", "Jun", "Jul", "Aug", "Sep", "Oct", "Nov", "Dec"];

    function processRegisterEntries() {

        $('#attendanceregistertable').datagrid('loading');
        var branch = $('#attendanceregisterfilter #filterby_branch').val();
        var month = $('#attendanceregisterfilter #filterby_month').val();
        if (branch && month) {
            $('#loaders').show();
            $.ajax({
                url: livesite + controllerName + "/processregisterentries",
                type: 'post',
                data: {
                    branch: branch,
                    month: month
                },
                success: function(response) {
                    //process server response here
                    $('#loaders').hide();
                    $('#attendanceregistertable').datagrid('loaded');
                    var success = $.parseJSON(response).success;
                    if (success) {
                        //alert('Attendance processed successfully');
                        $.notify("Attendance processed successfully", {
                            type: 'success',
                            allow_dismiss: false
                        });

                        //Modified On 17 April 2016
                        /*$('#attendanceregistertable').datagrid('load', {
                         branch: branch,
                         month: month
                         });*/
                        var dt = new Date(month);
                        $('#current_register_month').html(monthNames[dt.getMonth()] + '-' + dt.getFullYear());
                        $('.tabset-attendanceregister #tab1').load(livesite + controllerName + "/showregistertab/0", {
                            month: month,
                            branch: branch
                        });
                        $('.tabset-attendanceregister #tab2').load(livesite + controllerName + "/showregistertab/1", {
                            month: month,
                            branch: branch
                        });
                        //Ends

                    } else {
                        //alert('Attendance process failed!');
                        $.notify("Attendance process failed!", {
                            type: 'danger',
                            allow_dismiss: false
                        });
                    }
                }
            });
        }
    }

    //Edited by Ashin on 08-02-24
    // Tab-Pane change function 
    function tabChange() {
        //Edited by Akshay on 1-8-2024
        var container = $('#processCycleTabContainer');
        var tabs = container.find('.nav-tabs > li');
        //End
        // var tabs = $('.nav-tabs > li');
        var active = tabs.filter('.active');
        var next = active.next('li').length ? active.next('li').find('a') : tabs.filter(':first-child').find('a');
        next.tab('show');
    }

    $('.tab-pane').hover(function() {
        clearInterval(tabCycle);
    }, function() {
        tabCycle = setInterval(tabChange, 1000);
    });

    // Tab Cycle function
    var tabCycle = setInterval(tabChange, 1000)

    // Tab click event handler
    $(function() {
        $('.nav-tabs a').click(function(e) {
            e.preventDefault();
            clearInterval(tabCycle);
            $(this).tab('show')
            tabCycle = setInterval(tabChange, 1000);
        });
    });

    // edited by athira on 02-04-2025
    function viewRegisterEntries1() {
        // updateamendmence();
        var startTime; //Edited by Ashin 02-04-2024
        $('#attendanceregistertable').datagrid('loading');
        var branch = $('#attendanceregisterfilter #filterby_branch').val();
        var month = $('#attendanceregisterfilter #filterby_month').val();
        if (branch && month) {
            $('.cycle-tab-container').show(); //Edited by Ashin on 08-02-24
            $.ajax({
                url: livesite + controllerName + "/viewregisterentries",
                type: 'post',
                //Edited by Ashin 02-04-2024
                beforeSend: function() {
                    startTime = window.performance.now();
                },
                data: {
                    branch: branch,
                    month: month
                },
                success: function(response) {
                    //Edited by Ashin 22-05-2024
                    var elapsedTime = window.performance.now() - startTime;
                    //  $('#timer-label').text('Attendance loaded in ' + (elapsedTime / 1000).toFixed(2) + ' sec');

                    var minutes = Math.floor(elapsedTime / 60000);
                    var seconds = Math.floor((elapsedTime % 60000) / 1000);
                    console.log(minutes);
                    console.log(seconds);
                    if (minutes > 0) {
                        // edited by ASHIN on 27-05-24
                        $('#timer-label').text('Attendance loaded in ' + minutes + ' min and ' + seconds + ' sec');
                    } else {
                        $('#timer-label').text('Attendance loaded in ' + seconds + ' sec');
                    }
                    //process server response here
                    $('.cycle-tab-container').hide(); //Edited by Ashin on 08-02-24
                    $('#attendanceregistertable').datagrid('loaded');
                    var success = $.parseJSON(response).success;
                    if (success) {
                        //alert('Attendance processed successfully');
                        $.notify("Attendance listed successfully.", {
                            type: 'success',
                            allow_dismiss: false
                        });

                        //Modified On 17 April 2016
                        /*$('#attendanceregistertable').datagrid('load', {
                         branch: branch,
                         month: month
                         });*/
                        var dt = new Date(month);
                        $('#current_register_month').html(monthNames[dt.getMonth()] + '-' + dt.getFullYear());
                        $('.tabset-attendanceregister #tab1').load(livesite + controllerName + "/showregistertab/0", {
                            month: month,
                            branch: branch
                        });
                        $('.tabset-attendanceregister #tab2').load(livesite + controllerName + "/showregistertab/1", {
                            month: month,
                            branch: branch
                        });
                        //Ends

                    } else {
                        //alert('Attendance process failed!');
                        $.notify("Attendance process failed!", {
                            type: 'danger',
                            allow_dismiss: false
                        });
                    }
                }
            });
        } else {
            //alert('Attendance process failed!');
            $.notify("Select branch.", {
                type: 'danger',
                allow_dismiss: false
            });
        }
    }
    // end

    function processRegisterEntries1() {
         var branch = $('#attendanceregisterfilter #filterby_branch').val();
        var month = $('#attendanceregisterfilter #filterby_month').val();

            $.ajax({
                url: livesite + controllerName + "/checkprocessingstatus",
                type: 'post',
                data: {
                    branch: branch,
                    month: month
                },
                success: function(response) {
                   
                    var success = $.parseJSON(response).success;
                    var message = $.parseJSON(response).message;
                    if (success == 1) {
                         $('#timer-label').text(message);
                        loadregister();


                    } else {
                        alert('Unable to process the attendance.');
                         $('#timer-label').text(message);
                    }
                }
            });
    }
    function loadregister(){
         var branch = $('#attendanceregisterfilter #filterby_branch').val();
        var month = $('#attendanceregisterfilter #filterby_month').val();
         if (confirm("Processing attendance for this branch may take a few minutes, and other actions will be locked until it finishes.\nAll rules will be re-applied.\nIf attendance was already processed and no data has changed, you can just update blank dates instead.\nDo you want to continue?")) {
        var startTime; //Edited by Ashin 02-04-2024
        $('#attendanceregistertable').datagrid('loading');
       
        if (branch && month) {
            $('.cycle-tab-container').show(); //Edited by Ashin on 08-02-24
            $.ajax({
                url: livesite + controllerName + "/processregisterentries",
                type: 'post',
                //Edited by Ashin 02-04-2024
                beforeSend: function() {
                    startTime = window.performance.now();
                },
                data: {
                    branch: branch,
                    month: month
                },
                success: function(response) {
                    //Edited by Ashin 22-05-2024
                    var elapsedTime = window.performance.now() - startTime;
                    //  $('#timer-label').text('Attendance loaded in ' + (elapsedTime / 1000).toFixed(2) + ' sec');

                    var minutes = Math.floor(elapsedTime / 60000);
                    var seconds = Math.floor((elapsedTime % 60000) / 1000);
                    console.log(minutes);
                    console.log(seconds);
                    if (minutes > 0) {
                        // edited by ASHIN on 27-05-24
                        $('#timer-label').text('Attendance loaded in ' + minutes + ' min and ' + seconds + ' sec');
                    } else {
                        $('#timer-label').text('Attendance loaded in ' + seconds + ' sec');
                    }
                    //process server response here
                    $('.cycle-tab-container').hide(); //Edited by Ashin on 08-02-24
                    $('#attendanceregistertable').datagrid('loaded');
                    var success = $.parseJSON(response).success;
                    if (success) {
                        //alert('Attendance processed successfully');
                        $.notify("Attendance processed successfully", {
                            type: 'success',
                            allow_dismiss: false
                        });

                        //Modified On 17 April 2016
                        /*$('#attendanceregistertable').datagrid('load', {
                         branch: branch,
                         month: month
                         });*/
                        var dt = new Date(month);
                        $('#current_register_month').html(monthNames[dt.getMonth()] + '-' + dt.getFullYear());
                        $('.tabset-attendanceregister #tab1').load(livesite + controllerName + "/showregistertab/0", {
                            month: month,
                            branch: branch
                        });
                        $('.tabset-attendanceregister #tab2').load(livesite + controllerName + "/showregistertab/1", {
                            month: month,
                            branch: branch
                        });
                        //Ends

                    } else {
                        //alert('Attendance process failed!');
                        $.notify("Attendance process failed!", {
                            type: 'danger',
                            allow_dismiss: false
                        });
                    }
                }
            });
        } else {
            //alert('Attendance process failed!');
            $.notify("Select branch.", {
                type: 'danger',
                allow_dismiss: false
            });
        }
    }
    }

    function filterRegister(obj) {
       // return false;
        var branch = $('#attendanceregisterfilter #filterby_branch').val();
        var month = $('#attendanceregisterfilter #filterby_month').val();
            $.ajax({
                url: livesite + controllerName + "/checkprocessinglaststatus",
                type: 'post',
                data: {
                    branch: branch,
                    month: month
                },
                success: function(response) {
                   
                    var success = $.parseJSON(response).success;
                    var message = $.parseJSON(response).message;
                   // if (success == 0) {
                         $('#timer-label').text(message);
                   // }
                }
            });

        // var dt = new Date(month);
        // $('#current_register_month').html(monthNames[dt.getMonth()] + '-' + dt.getFullYear());

        // $('.tabset-attendanceregister #tab1').load(livesite + controllerName + "/showregistertab/0", {
        //     month: month,
        //     branch: branch
        // });
        // $('.tabset-attendanceregister #tab2').load(livesite + controllerName + "/showregistertab/1", {
        //     month: month,
        //     branch: branch
        // });

        /*var activetabid = $('div.tabset-attendanceregister div.pws_tabs_scale_show').data('pws-tab');
         switch (activetabid) {
         case 'tab1':
         var branch = $('#attendanceregisterfilter #filterby_branch').val();
         var employee = $('#filterby_employee').val();
         var month = $('#attendanceregisterfilter #filterby_month').val();
         
         //Load attendance register header for selected month
         $.ajax({
         url: livesite + "Attendance/loadattendanceregisterheader",
         type: 'post',
         data: {
         month : month
         },
         success: function (response) {
         //process server response here
         if (response) {
         $('#attendanceregistertable').datagrid({
         url: livesite + "Attendance/listregisterentries",
         pagination: true,
         singleSelect: true,
         queryParams: {
         branch: branch,
         employee: employee,
         month: month
         },
         //toolbar: '#tb',
         fitColumns: true,
         pageList: [2, 5, 10, 50, 100],
         columns:[$.parseJSON(response)]
         });
         }
         }
         });
         break;
         case 'tab2':
         var branch = $('#attendanceregisterfilter #filterby_branch').val();
         var employee = $('#select_filterby_employee').val();
         var month = $('#attendanceregisterfilter #filterby_month').val();
         
         //Load attendance register header for selected month
         $.ajax({
         url: livesite + "Attendance/loadattendanceregisterheader",
         type: 'post',
         data: {
         month : month
         },
         success: function (response) {
         //process server response here
         if (response) {
         $('#verifiedattendanceregistertable').datagrid({
         url: livesite + "Attendance/listverifiedregisterentries",
         pagination: true,
         singleSelect: true,
         queryParams: {
         branch: branch,
         employee: employee,
         month: month
         },
         //toolbar: '#tb',
         fitColumns: true,
         pageList: [2, 5, 10, 50, 100],
         columns:[$.parseJSON(response)]
         });
         }
         }
         });
         break;
         default:
         return false;
         break;
         }*/
    }
   
    function formatRegisterEntry(val, row) {
        var backColor = '';
        var textColor = '';
        var regEntryHtml = '';

        if (val == null || val == '') {
            return '<div class="att-register-entry-full-day" style="background-color:red;color:white;">&nbsp;</div>';
        }
        var arrRegEntry = val.split('/');
        // console.log(arrRegEntry);
        for (var i = 0; i < arrRegEntry.length; i++) {
            /*switch (arrRegEntry[i]) {
             case 'P':
             case 'p':
             backColor = 'green';
             textColor = 'white';
             break;
             //case 'L':
             //case 'l':
             case 'FDL':
             case 'fdl':
             case 'FHL':
             case 'fhl':
             case 'SHL':
             case 'shl':
             backColor = 'orange';
             textColor = 'white';
             break;
             case 'WO':
             case 'wo':
             backColor = 'yellow';
             textColor = 'black';
             break;
             case 'HO':
             case 'ho':
             backColor = 'blue';
             textColor = 'white';
             break;
             case 'LOP':
             case 'lop':
             backColor = 'maroon';
             textColor = 'white';
             break;
             case 'COFF':
             case 'coff':
             case 'WFH':
             case 'wfh':
             case 'NA':
             case 'na':
             case 'OTHERS':
             case 'others':
             backColor = 'deepskyblue';
             textColor = 'white';
             break;
             default:
             backColor = 'red';
             textColor = 'white';
             break;
             }*/
            var entry = arrRegEntry[i];
            if (entry.toUpperCase() === 'P') {
                backColor = 'green';
                textColor = 'white';
            } else if (entry.toUpperCase() === 'TC') {
                backColor = '#ef00ff';
                textColor = 'white';
            } else if ($.inArray(entry.toUpperCase(), arr_leaveabbr) >= 0) {
                backColor = 'orange';
                textColor = 'white';
                //if(!row.verified){
                if (row.isdelete != 'N') {
                    row.days_leave = (arrRegEntry.length == 1) ? Number(row.days_leave) + 1 : Number(row.days_leave) + 0.5;
                    //if(row.registerid == 2290) console.log(row.days_leave);
                }
            } else if (entry.toUpperCase() === 'WO') {
                backColor = 'yellow';
                textColor = 'black';
            } else if (entry.toUpperCase() === 'HO') {
                backColor = 'blue';
                textColor = 'white';
            } else if (entry.toUpperCase() === 'LOP') {
                backColor = 'maroon';
                textColor = 'white';
            } else if (entry.toUpperCase() === 'LOP(I)') {
                backColor = 'maroon';
                textColor = 'white';
            } else if ($.inArray(entry.toUpperCase(), arr_other_regentries) >= 0) {
                backColor = 'deepskyblue';
                textColor = 'white';
            } else {
                backColor = 'red';
                textColor = 'white';
            }
            var regEntryClass = (arrRegEntry.length == 1) ? 'att-register-entry-full-day' : ((i == 0) ? 'att-register-entry-first-half' : 'att-register-entry-second-half');
            regEntryHtml += '<div class="' + regEntryClass + ' " style="background-color:' + backColor + ';color:' + textColor + ';">' + arrRegEntry[i] + '</div>';
        }
        return regEntryHtml;
        //return '<div class="background-att-register-entry">('+val+')</div>';
        //return '<h2 class="stripe-4">('+val+')</h2>';
    }

    function formatEmployeeName(val, row) {
        return '<div>&nbsp;&nbsp;' + val + '</div>';
    }

    function downloadReport(mode) {
        var branch = $('#attendanceregisterfilter #filterby_branch').val();
        var month = $('#attendanceregisterfilter #filterby_month').val();
        var employee = $('#filterby_employee').val();
        if (employee == '') {
            employee = 'null';
        }
        // action(livesite+'Attendance/verifiedpdf')
        $('#attendanceregisterfilter').attr('action', livesite + controllerName + '/verifiedpdf/' + branch + '/' + month + '/' + employee + '/' + mode);
        $('#attendanceregisterfilter').submit();

    }

    function updateamendmence() {
        var brn = $('#filterby_branch').val();
        var mnth = $('#filterby_month').val();
        $('#updateamendance').html('<li class="fa fa-spinner fa-spin"></li>Loading ...').attr("disabled", "disabled");
        $.ajax({
            type: "POST",
            url: livesite + "EditPunches/Updateamendmens",
            data: {
                brn: brn,
                month: mnth
            },
            success: function(resp) {
                $('#updateamendance').html('Refresh again').attr("disabled", false);
                $.notify("Attandence Updates successfully", {
                    type: 'success',
                    allow_dismiss: false
                });


            }
        });
    }

    function loadmsg() {
        $('#attendanceregistertable').datagrid('options').loadMsg = 'other message';
        $('#attendanceregistertable').datagrid('loading');
        $('#attendanceregistertable').datagrid('loaded');
    }

    function reloadAttendanceRegister(tabIndex) {
        var branch = $('#attendanceregisterfilter #filterby_branch').val();
        var month = $('#attendanceregisterfilter #filterby_month').val();
        if (tabIndex == "tab1") {
            var employee = $('#filterby_employee').val();
            $('#attendanceregistertable').datagrid('load', {
                branch: branch,
                employee: employee,
                month: month
            });
            //$('.tabset-attendanceregister #tab1').load(livesite + controllerName + "/showregistertab/0",{month:month,branch:branch});
            //$('.tabset-attendanceregister #tab1').datagrid('resize',{});
        } else if (tabIndex == "tab2") {
            var employee = $('#select_filterby_employee').val();
            $('#verifiedattendanceregistertable').datagrid('load', {
                branch: branch,
                employee: employee,
                month: month
            });
            ///$('.tabset-attendanceregister #tab2').load(livesite + controllerName + "/showregistertab/1",{month:month,branch:branch});
            //$('.tabset-attendanceregister #tab2').datagrid('resize',{});
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
        url = livesite + "AttendanceSetup/index";
    } 
    else if (userGroup == "2") {
        url = livesite + "EmployeeMenu/addon";
    }

    $("#container").load(url, function () {
        isDashboardShown = false;
    });

});
</script>