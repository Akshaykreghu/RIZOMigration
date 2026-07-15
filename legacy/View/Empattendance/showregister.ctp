<style>
    .tabset-attendanceregister {
        min-height: 350px !important;
    }
    
    .att-register-entry-first-half {
        float: left;
        width: 50%;
        padding: 20% 0;
    }
    
    .att-register-entry-second-half {
        float: right;
        width: 50%;
        padding: 20% 0;
    }
    
    .att-register-entry-full-day {
        width: 100%;
        padding: 20% 0;
    }
    
    #tab1 .datagrid-cell, #tab2 .datagrid-cell {
        padding: 0 !important;
    }
    
    /*.background-att-register-entry {
      position: relative;
      height: 30px;
      width: 30px;
      overflow: hidden;
      background-color: red;
      color: white;
    }

    .background-att-register-entry:before { 
      content: '';
      position: absolute;
      left: 20%;
      width: 100%; 
      height: 200%; 
      background-color: red;
      color: white;
      top: 0;
      -webkit-transform: rotate(45deg);
      -moz-transform: rotate(45deg);
      transform: rotate(45deg);
    }
    .stripe-4 {
      color: white;
      background: repeating-linear-gradient(
        -45deg,
        red,
        red 80%,
        green 80%,
        green 100%
      );
    }*/
</style>

<section class="content-header">
    <h1 class="text-primary-18"> Attendance Register </h1>
</section>
<!-- Main content -->

<section class="content">
    
    <div class="row">
        <div class="col-md-12">
            <!-- Employee List -->
            <!-- DIRECT CHAT DANGER -->
            <div class="box ">
                <div class="box-header with-border">
                    <div class="row">
                        <div class="col-md-3">
                            <h3 class="box-title">Attendance Register for <label id="current_register_month"><?php echo date('M-Y'); ?></label></h3>
                        </div>
                        <div class="col-md-9">
                            <!-- Employee import form -->
                            <form class="form-horizontal" method="post" action="" id="attendanceregisterfilter">
                                <div class="form-group">
                                    <div class="col-md-4">
                                        <label class="col-md-5 control-label" for="filterby_branch">Choose Branch</label>
                                        <div class="col-md-7">
                                            <select id="filterby_branch" name="filterby_branch" class="form-control" onchange="filterRegister(this);" >
                                                <?php 
                                                foreach ($arr_branches as $key => $value) {
                                                    echo '<option value="' . $value['EmployeeProfessionalDetails']['branch_code'] . '">' . $value['Branches']['branch_name'] . '</option>';
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
                                    <div class="col-md-4">
                                        <label class="col-md-5 control-label" for="filterby_month">Choose Month</label>
                                        <div class="col-md-7">
                                            <select id="filterby_month" name="filterby_month" class="form-control" onchange="filterRegister(this);" >

                                                <?php
                                                                                        /*
                                                                                         * By santhosh on 27 Dec 2015
                                                                                         */
                                                                                        $start_month = strtotime(date('Y-m', strtotime("+1 month", strtotime(date('Y-m')))));
                                                for ($i = 0; $i < 10; $i++) {
                                                                                                $month = date('Y-m', strtotime("-$i month", $start_month));
                                                                                                if($month == date('Y-m')){
                                                                                                        echo '<option selected="selected" value="' . $month . '">' . date('M-Y', strtotime("-$i month", $start_month)) . '</option>';
                                                                                                }else{
                                                                                                        echo '<option value="' . $month . '">' . date('M-Y', strtotime("-$i month", $start_month)) . '</option>';
                                                                                                }
                                                }
                                                ?>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="col-md-7">
                                            <button type="button" id="btn-processregister" class="btn btn-primary" onclick="processRegisterEntries();">Process</button>
                                        </div>
                                    </div>
                                </div>
                                 <div class="col-md-12" align="right">
                <a href="#" class="btn btn-default" onclick="downloadReport('pdf');" ><i class="icon-file"></i>Download  As PDF</a>
                <a href="#" class="btn btn-default" onclick="downloadReport('excel');"><i class="icon-file"></i>Download As Excel</a>
            </div>
                            </form>
                        </div>
                    </div>
                </div><!-- /.box-header -->
                <div class="box-body">
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

                    <div class="tabset-attendanceregister">
                        <div id="tab1" data-pws-tab="tab1" data-pws-tab-name="To be Verify">
                        </div>
                        <div id="tab2" data-pws-tab="tab2" data-pws-tab-name="Verified" data-pws-tab-icon="fa-video-camera">
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
        </div><!-- /.col -->
    </div>
   
    
</section>

<script>
var str_leaveabbr = "<?php echo $str_leaveabbr; ?>";
var arr_leaveabbr = str_leaveabbr.split("#");
var arr_other_regentries = ['COFF','WFH','NA','OTHERS'];
    jQuery(document).ready(function () {        
        $('.tabset-attendanceregister #tab1').load(livesite + "Empattendance/showregistertab/0");
        $('.tabset-attendanceregister #tab2').load(livesite + "Empattendance/showregistertab/1");
        
        $('.tabset-attendanceregister').pwstabs({
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
    });
    
    function updateRegisterEntries(registerid) {
        if(registerid){
            //Check for any null values in register for selected employee
            var startdate = $('#hidden-start-date').val();
            var enddate = $('#hidden-end-date').val();
            $.ajax({
                url: livesite + "Attendance/checkifregistercanverify/"+registerid,
                type: 'post',
                dataType: 'json',
                data: {
                    startdate: startdate,
                    enddate: enddate
                },
                success: function (response) {
                    if(response.length){
                        var url = livesite + "Attendance/updateregisterentries/"+registerid;
                        $("#modalForm #modalForm-content").load(url, {
                            dates: JSON.stringify(response)
                        }, function() {
                            $("#modalForm").modal('show')
                        });
                    }else{
                        //alert("Please select atleast one record to verify");
                        $.notify("You can verify now.",{
                            type: 'success',
                            allow_dismiss: false
                        });
                        return false;
                    }
                }
            });
        }else{
            
        }
    }
    
    function verifyRegisterEntries(registerid) {
        if(registerid){
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
        }else{
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
                    } else
                    {
                        str_ids += "," + data.registerid;
                    }
                }
            }
            if (str_ids == '') {
                //alert("Please select atleast one record to verify");
                $.notify("Please select atleast one record to verify",{
                    type: 'warning',
                    allow_dismiss: false
                });
                return false;
            }
            doVerificationProcedure(str_ids);
        }
    }
    
    function doVerificationProcedure(str_ids){        
        var branch = $('#attendanceregisterfilter #filterby_branch').val();
        var employee = $('#filterby_employee').val();
        var month = $('#attendanceregisterfilter #filterby_month').val();
        
        //Modified on 21 Feb 2016 : passed start and end dates on verify register
        var startdate = $('#hidden-start-date').val();
        var enddate = $('#hidden-end-date').val();
        
        if (confirm("Are you sure to verify this register ?")) {
            $.ajax({
                url: livesite + "Attendance/verifyregisterentries",
                type: 'post',
                data: {
                    ids: str_ids,
                    startdate: startdate,
                    enddate: enddate
                },
                success: function (response) {
                    //process server response here
                    var success = $.parseJSON(response).success;
                    if (success) {
                        //alert('Attendance verified successfully');
                        $.notify("Attendance verified successfully",{
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
                        $.notify("Attendance verification failed!",{
                            type: 'danger',
                            allow_dismiss: false
                        });
                    }
                }
            });
        }
    }
    
    var monthNames = ["Jan", "Feb", "Mar", "Apr", "May", "Jun","Jul", "Aug", "Sep", "Oct", "Nov", "Dec"];
    function processRegisterEntries() {
 $('#loaders').show();
        var branch = $('#attendanceregisterfilter #filterby_branch').val();
        var month = $('#attendanceregisterfilter #filterby_month').val();
        $.ajax({
            url: livesite + "Empattendance/processregisterentries",
            type: 'post',
            data: {
                branch: branch,
                month: month
            },
            success: function (response) {
                //process server response here
                $('#loaders').hide();
                var success = $.parseJSON(response).success;
                if (success) {
                    //alert('Attendance processed successfully');
                    $.notify("Attendance processed successfully",{
                        type: 'success',
                        allow_dismiss: false
                    });
                    
                    //Modified On 17 April 2016
                    /*$('#attendanceregistertable').datagrid('load', {
                        branch: branch,
                        month: month
                    });*/
                    var dt = new Date(month);
                    $('#current_register_month').html(monthNames[dt.getMonth()]+'-'+dt.getFullYear());
                    $('.tabset-attendanceregister #tab1').load(livesite + "Empattendance/showregistertab/0",{month:month,branch:branch});
                    $('.tabset-attendanceregister #tab2').load(livesite + "Empattendance/showregistertab/1",{month:month,branch:branch});
                    //Ends
                    
                } else {
                    //alert('Attendance process failed!');
                    $.notify("Attendance process failed!",{
                        type: 'danger',
                        allow_dismiss: false
                    });
                }
            }
        });
    }
    
    function filterRegister(obj) {
        return false;
        var month = $('#attendanceregisterfilter #filterby_month').val();
        var branch = $('#attendanceregisterfilter #filterby_branch').val();
        
        var dt = new Date(month);
        $('#current_register_month').html(monthNames[dt.getMonth()]+'-'+dt.getFullYear());
        
        $('.tabset-attendanceregister #tab1').load(livesite + "Empattendance/showregistertab/0",{month:month,branch:branch});
        $('.tabset-attendanceregister #tab2').load(livesite + "Empattendance/showregistertab/1",{month:month,branch:branch});
        
        /*var activetabid = $('div.tabset-attendanceregister div.pws_tabs_scale_show').data('pws-tab');
        switch (activetabid) {
            case 'tab1':
                var branch = $('#attendanceregisterfilter #filterby_branch').val();
                var employee = $('#filterby_employee').val();
                var month = $('#attendanceregisterfilter #filterby_month').val();
                
                //Load attendance register header for selected month
                $.ajax({
                    url: livesite + "Empattendance/loadattendanceregisterheader",
                    type: 'post',
                    data: {
                        month : month
                    },
                    success: function (response) {
                        //process server response here
                        if (response) {
                            $('#attendanceregistertable').datagrid({
                                url: livesite + "Empattendance/listregisterentries",
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
                    url: livesite + "Empattendance/loadattendanceregisterheader",
                    type: 'post',
                    data: {
                        month : month
                    },
                    success: function (response) {
                        //process server response here
                        if (response) {
                            $('#verifiedattendanceregistertable').datagrid({
                                url: livesite + "Empattendance/listverifiedregisterentries",
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
    /*function styleDay(val, row, index) {
        var backColor = '';
        var textColor = '';
        switch (val) {
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
        }
        return 'background-color:' + backColor + ';color:' + textColor + ';';
    }*/
    function formatRegisterEntry(val,row){
        var backColor = '';
        var textColor = '';
        var regEntryHtml = '';
        
        if(val == null || val == ''){           
            return '<div class="att-register-entry-full-day" style="background-color:red;color:white;">&nbsp;</div>'; 
        }
        var arrRegEntry = val.split('/');   
        for(var i=0;i<arrRegEntry.length;i++){
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
            if(entry.toUpperCase() === 'P'){                
                backColor = 'green';
                textColor = 'white';
            }else if($.inArray(entry.toUpperCase(),arr_leaveabbr) >= 0){ 
                backColor = 'orange';
                textColor = 'white';
                row.days_leave = (arrRegEntry.length == 1) ? row.days_leave + 1 : row.days_leave + 1/2;
            }else if(entry.toUpperCase() === 'WO'){ 
                backColor = 'yellow';
                textColor = 'black';
            }else if(entry.toUpperCase() === 'HO'){ 
                backColor = 'blue';
                textColor = 'white';
            }else if(entry.toUpperCase() === 'LOP'){ 
                backColor = 'maroon';
                textColor = 'white';
            }else if($.inArray(entry.toUpperCase(),arr_other_regentries) >= 0){ 
                backColor = 'deepskyblue';
                textColor = 'white';
            }else {
                backColor = 'red';
                textColor = 'white';
            }
            var regEntryClass = (arrRegEntry.length == 1) ? 'att-register-entry-full-day' : ((i==0) ? 'att-register-entry-first-half' : 'att-register-entry-second-half');
            regEntryHtml += '<div class="' + regEntryClass + ' " style="background-color:' + backColor + ';color:' + textColor + ';">'+arrRegEntry[i]+'</div>';
        }        
        return regEntryHtml;
        //return '<div class="background-att-register-entry">('+val+')</div>';
        //return '<h2 class="stripe-4">('+val+')</h2>';
    }
    
    function formatEmployeeName(val,row){
        return '<div>&nbsp;&nbsp;' + val + '</div>';
    }
    function downloadReport(mode)
    {
         var branch = $('#attendanceregisterfilter #filterby_branch').val();
        var month = $('#attendanceregisterfilter #filterby_month').val();
        var employee = $('#filterby_employee').val();
        if(employee=='')
        {
            employee='null';
        }
      // action(livesite+'Attendance/verifiedpdf')
       $('#attendanceregisterfilter').attr('action',livesite+'Empattendance/verifiedpdf/'+branch+'/'+month+'/'+employee+'/'+mode);
        $('#attendanceregisterfilter').submit();
       
    }
</script>