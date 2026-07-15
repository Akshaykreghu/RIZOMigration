<style type="text/css">
    /**
     * Nestable
     */
    .tree-folder-open {
        background: none;
    }

    .fa-user:before {
        content: "\f007";
    }

    .tree-folder {
        background: none;
    }

    .tree-folder:before {
        content: "\f007";
    }

    .tree-file:before {
        content: "\f007";
    }

    .tree-file {
        background: none;
    }

    .gridbox .gridbox_dhx_skyblue .isModern {
        height: 370px;
    }

    div.gridbox {
        -webkit-box-sizing: content-box;
        -moz-box-sizing: content-box;
        box-sizing: content-box;
    }
      /* * edited by bindu 24-10-25 */
     
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
    }/* edited by bindu 24-10-25 end*/
</style>
<!--script src="<?php //echo $this->webroot; 
                ?>plugins/nestable/jquery.nestable.js"></script--->

      
 <!-- /* edited by bindu 24-10-25 */ -->
<section class="content-header heading">

    <h1 class="text-primary-18">Scheduled Break Off</h1>
<div class="text-primary-16 home" style="display:flex; align-items:center; gap:10px; cursor:pointer;">
        <i class="fa" style="font-size:16px;">&#xf104;</i>
        Back
    </div>

</section>
<hr style="margin-top: 8px;margin-bottom: -2px;margin-right: 15px;margin-left: 15px;">
    <!-- end -->
<!-- Main content -->
<section class="content">
    <div class="row">
        <div class="col-md-12">

            <div class="tabset0">
                <div data-pws-tab="tab1" data-pws-tab-name="Scheduled Break Off" data-pws-tab-icon="fa-cog">

                    <div class="box ">
                        <div class="box-header with-border">
                            <h3 class="box-title">Scheduled Break Off Allocation</h3>
                            <div class="box-tools pull-right">
                                <button class="btn btn-box-tool" data-widget="collapse">
                                    <i class="fa fa-minus"></i>
                                </button>
                            </div>
                        </div><!-- /.box-header -->
                        <div class="box-body">
                            <div class="row">
                                <div class="col-md-3">
                                    <select id="filterby_month" name="filterby_month" class="form-control" onchange="filterScheduledBreakOff();">
                                        <option value="">Select </option>
                                        <?php
                                        /*
                                        * By santhosh on 27 Dec 2015
                                        */
                                        $start_month = strtotime(date('Y-m')); //, strtotime("+1 month", strtotime(date('Y-m')))));
                                        for ($i = -1; $i < 11; $i++) {
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
                                <div class="col-md-1">
                                    <label>In</label>
                                    <input type="checkbox" id="filter_by_present" />
                                </div>
                                <div class="col-md-3">
                                    <input type="text" class="form-control pull-right" value="" placeholder="Break-off Message" name="message" id="message" required="">
                                </div>
                                <div class="col-md-3">
                                    <select class="form-control" id="out_direction">
                                        <option value="N">In & Out</option>
                                        <option value="I">In Only</option>
                                        <option value="O">Out Only</option>
                                    </select>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-sm-2">
                                    <div class="row">
                                        <h3 class="col-md-4" style="padding: 0px; padding-left: 20px; ">Date</h3>

                                    </div>
                                    <div id="scheduledbreakoffdates" style="width:100%; height:370px; background-color:white;"></div>
                                </div>
                                <div class="col-sm-5">
                                    <div class="row">
                                        <h3 class="col-md-2" style="padding: 0px; padding-left: 20px; ">All</h3>
                                        <div class="col-md-3 col-lg-3" style="padding: 0px; margin-top: 14px; text-align: right; ">
                                            <label style=" color: #343434; font-weight: 600; ">In < </label>
                                            <input id="in_dutty_time" type="text" name="in_dutty_time" step="2" value="00:00:00" style="width:65px;line-height: 25px;">
                                        </div>
                                        <div id="time">
                                            <div class="col-md-2" style=" margin-top: 23px;padding-right:0px!important;text-align:center; ">
                                                <label style=" color: #343434; font-weight: 600; ">Out > </label>
                                            </div>
                                            <div class="col-md-2 col-lg-1" style=" margin-top: 15px;padding-left:0px!important; display: flex; justify-content: space-between; ">
                                                <input id="dutty_time" type="text" name="dutty_time" step="2" value="00:00:00" style="width:65px;line-height: 25px;">
                                                <button style="margin-left:4px ; margin-right: 4px; " class="btn btn-primary btn-sm" onclick="filterwithDutyTime();">Apply</button>
                                                <button class="btn btn-warning btn-sm" onclick="clearTime();">Clear</button>
                                            </div>
                                        </div>
                                    </div>

                                    <div id="allempsforsbo" style="width:100%; height:370px!important; background-color:white;"></div>
                                </div>
                                <div class="col-sm-5">
                                    <h3>Employees allocated Break off in selected date </h3>
                                    <div id="empsinsbo" style="width:100%; height:370px!important; background-color:white;"></div>
                                </div>
                            </div>

                        </div>
                    </div>

                </div>

                <div data-pws-tab="tab2" data-pws-tab-name="Comb Off " data-pws-tab-icon="fa-cog">
                    
                    <div class="box ">
                        <div class="box-header with-border">
                            <h3 class="box-title">Comb Off</h3>
                            <div class="box-tools pull-right">
                                <button class="btn btn-box-tool" data-widget="collapse">
                                    <i class="fa fa-minus"></i>
                                </button>
                            </div>
                        </div>
                        <div class="box-body">
                            <div class="row">
                                <div class="col-md-3">
                                    <select id="filterby_month_2" name="filterby_month_2" class="form-control" onchange="filterScheduledBreakOffSecond();" >
                                        <option value="" >Select </option>
                                    <?php
                                    /*
                                        * By santhosh on 27 Dec 2015
                                        */
                                    $start_month = strtotime(date('Y-m')); //, strtotime("+1 month", strtotime(date('Y-m')))));
                                    for ($i = -1; $i < 11; $i++) {
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
                                <div class="col-md-3">
                                    <input type="text" class="form-control pull-right" placeholder="Comb off Message" name="messageComboff" id="messageComboff" >
                                </div>
                                <div class="col-md-2" >
                                            <input type="checkbox" id="first_half_2" />
                                            <label>First Half</label>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-sm-2">
                                    <div class="row">
                                        <h3 class="col-md-4" style="padding: 0px; padding-left: 20px; ">Date</h3>
                                        <div class="col-md-6 col-lg-6" style="padding: 0px; margin-top: 20px; width: 60%; text-align: right; ">
                                            <label>All Days</label>
                                            <input onchange="filterScheduledBreakOffSecond();" type="checkbox" id="alldays" />

                                        </div>
                                    </div>
                                    <div id="specialbreakoffdates" style="width:100%; height:370px; background-color:white;"></div>
                                </div>
                                <div class="col-sm-5">
                                    <div class="row">
                                        <h3 class="col-md-4">All employees</h3>
                                        <div class="col-md-4" style="margin-top: 20px; text-align: right; ">
                                            <input type="checkbox" id="filter_by_present_2" />
                                            <label>Present Only</label>
                                        </div>
                                        <div class="col-md-2 col-lg-1" style=" margin-top: 15px;padding-left:0px!important; display: flex; justify-content: space-between; ">
                                                <button style="margin-left:4px ; margin-right: 4px; " class="btn btn-primary btn-sm" onclick="filterwithDutyTime2();">Apply</button>
                                        </div>
                                    </div>
                                    
                                    <div id="allempsforsbospecial" style="width:100%; height:370px; background-color:white;"></div>
                                </div>
                                <div class="col-sm-5">
                                    <h3>Employees taken comb off in selected date </h3>
                                    <div id="empsinsbospecial" style="width:100%; height:370px; background-color:white;"></div>
                                </div>
                            </div>

                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>
</section>

<script>
    function toggleCheckbox(element) {
        //element.checked = !element.checked;
        var inputValue = $(this).attr("value");
        $("#time").toggle();
    }
    var monthChoosen = $('#filterby_month').val();

    jQuery(document).ready(function($) {

        $('.tabset0').pwstabs({
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

    var scheduledbreakoffdates;
    var allempsforsbo;
    var empsinsbo;

    //---------Scheduled Break Offs---------
    function doOnBreakOffDateSelect(id) {
        let isChecked = $('#filter_by_present').is(':checked');
        console.log(isChecked); // true (if checked at the time)
        let filterby_month = $('#filterby_month').val();
        let dutty_time = $('#dutty_time').val();
        let in_dutty_time = $("#in_dutty_time").val();
        allempsforsbo.clearAndLoad("<?php echo $this->webroot; ?>ScheduledBreakOff/listemployeesforsbodate?date=" + id + "&outtime=" + dutty_time + "&in_time=" + in_dutty_time + "&filter=" + isChecked + "&month=" + filterby_month, "json");
        empsinsbo.clearAndLoad("<?php echo $this->webroot; ?>ScheduledBreakOff/listemployeesinsbodate/attendance?date=" + id + "&outtime=" + dutty_time + "&in_time=" + in_dutty_time + "&filter=" + isChecked + "&month=" + filterby_month, "json");
    }
    scheduledbreakoffdates = new dhtmlXGridObject('scheduledbreakoffdates');
    scheduledbreakoffdates.setHeader("Date");
    scheduledbreakoffdates.setColAlign("left");
    scheduledbreakoffdates.setColumnIds("day_time_desc");
    scheduledbreakoffdates.setInitWidthsP("100");
    scheduledbreakoffdates.attachHeader("#text_search");
    //scheduledbreakoffdates.enableAutoHeight(true,400); 
    scheduledbreakoffdates.attachEvent("onRowSelect", doOnBreakOffDateSelect);
    scheduledbreakoffdates.enableAutoWidth(true);
    scheduledbreakoffdates.setColTypes("ro");
    scheduledbreakoffdates.init();
    scheduledbreakoffdates.load("<?php echo $this->webroot; ?>ScheduledBreakOff/listbreakoffdatesforsbo/" + monthChoosen, "json");

    allempsforsbo = new dhtmlXGridObject('allempsforsbo');
    allempsforsbo.selMultiRows = true;
    allempsforsbo.setHeader("Name,Branch,Designation,Department");
    allempsforsbo.attachHeader("#text_filter,#text_filter,#text_filter,#text_filter");
    allempsforsbo.setInitWidths("150,110,110,110");
    allempsforsbo.setColAlign("left,left,left,left");
    //	allempsforsp.attachHeader("#text_search,#combo_filter");
    allempsforsbo.setColSorting("str,str,str,str");
    allempsforsbo.attachEvent("onDrag", function(sId, tId, sObj, tObj, sInd, tInd) {

        let filterby_month = $('#filterby_month').val();
        if (scheduledbreakoffdates.getSelectedRowId()) {
            $('#loaders').show();
            $.ajax({
                url: livesite + "ScheduledBreakOff/removeEmpFromSBO",
                data: {
                    id: sId,
                    month: filterby_month,
                    sbodate: scheduledbreakoffdates.getSelectedRowId()
                },
                success: function(response) {
                    var response = $.parseJSON(response);
                    // process server response here
                    if (response) {
                        $.notify(response.msg, {
                            type: 'success',
                            allow_dismiss: false
                        });
                        $('#loaders').hide();
                    } else {
                        $.notify(response.msg, {
                            type: 'danger',
                            allow_dismiss: false
                        });
                        $('#loaders').hide();
                    }
                }
            });
            $('#loaders').hide();
        }

        return true;

    });
    allempsforsbo.setMultiLine(false);
    allempsforsbo.enableDragAndDrop(true);
    allempsforsbo.init();

    empsinsbo = new dhtmlXGridObject('empsinsbo');
    empsinsbo.selMultiRows = true;
    //	empsinsp.setImagePath("../../../codebase/imgs/");
    empsinsbo.setHeader("Name,Branch,Designation,Department");
    empsinsbo.attachHeader("#text_filter,#text_filter,#text_filter,#text_filter");
    empsinsbo.attachEvent("onDrag", function(sId, tId, sObj, tObj, sInd, tInd) {

        console.log(sId);
        let filterby_month = $('#filterby_month').val();
        let dutty_time = $('#dutty_time').val();
        let in_dutty_time = $("#in_dutty_time").val();
        let first_half = $('#out_direction').val();
        var message = $('#message').val();
        if (scheduledbreakoffdates.getSelectedRowId()) {
            $('#loaders').show();
            $.ajax({
                url: livesite + "ScheduledBreakOff/addEmpToSBO",
                data: {
                    id: sId,
                    month: filterby_month,
                    message: message,
                    dutty_time: dutty_time,
                    in_dutty_time: in_dutty_time,
                    type: 'Attendance',
                    first_half: first_half,
                    sbodate: scheduledbreakoffdates.getSelectedRowId()
                },
                success: function(response) {
                    //var text = response.responseText;
                    // process server response here
                    if (response) {
                        $.notify("Empolyee Added To Selected Date", {
                            type: 'success',
                            allow_dismiss: false

                        });
                        $('#loaders').hide();
                    } else {
                        $.notify(response, {
                            type: 'danger',
                            allow_dismiss: false

                        });
                        $('#loaders').hide();
                    }
                }
            });
            $('#loaders').hide();
        }
        return true;

    });
    empsinsbo.setInitWidths("150,110,110,110");
    empsinsbo.setColAlign("left,left,left,left");
    //	empsinsp.setColTypes("ed,ed");
    empsinsbo.setColSorting("str,str,str,str");
    empsinsbo.setMultiLine(false);
    empsinsbo.enableDragAndDrop(true);
    empsinsbo.init();
    //---------Ends---------

    function filterScheduledBreakOff() {
        var monthChoosen = $('#filterby_month').val();
        scheduledbreakoffdates.clearAndLoad("<?php echo $this->webroot; ?>ScheduledBreakOff/listbreakoffdatesforsbo/" + monthChoosen, "json");
    }


    var specialbreakoffdates;
    var allempsforsbospecial;
    var empsinsbospecial;


    //---------Scheduled Break Offs---------
    function doOnBreakOffDateSelectspecial(id) {
        let isChecked = $('#filter_by_present_2').is(':checked');
        console.log(isChecked); // true (if checked at the time)
        var filterby_month = $('#filterby_month_2').val();

        allempsforsbospecial.clearAndLoad("<?php echo $this->webroot; ?>ScheduledBreakOff/listemployeesforsbodateone?date=" + id + "&filter=" + isChecked + "&month=" + filterby_month, "json");
        empsinsbospecial.clearAndLoad("<?php echo $this->webroot; ?>ScheduledBreakOff/listemployeesinsbodate/special?date=" + id + "&filter=" + isChecked + "&month=" + filterby_month, "json");
    }
    specialbreakoffdates = new dhtmlXGridObject('specialbreakoffdates');
    specialbreakoffdates.setHeader("Date");
    specialbreakoffdates.setColAlign("left");
    specialbreakoffdates.setColumnIds("day_time_desc");
    specialbreakoffdates.setInitWidthsP("100");
    specialbreakoffdates.attachHeader("#text_search");
    //myGrid.enableAutoHeight(true,400);
    specialbreakoffdates.attachEvent("onRowSelect", doOnBreakOffDateSelectspecial);
    specialbreakoffdates.enableAutoWidth(true);
    specialbreakoffdates.setColTypes("ro");
    specialbreakoffdates.init();
    specialbreakoffdates.load("<?php echo $this->webroot; ?>ScheduledBreakOff/listbreakoffdatesforsbospecial/" + monthChoosen, "json");

    allempsforsbospecial = new dhtmlXGridObject('allempsforsbospecial');
    allempsforsbospecial.selMultiRows = true;
    allempsforsbospecial.setHeader("Name,Branch,Designation,Department");
    allempsforsbospecial.attachHeader("#text_filter,#text_filter,#text_filter,#text_filter");
    allempsforsbospecial.setInitWidths("150,110,110,110");
    allempsforsbospecial.setColAlign("left,left,left,left");
    //	allempsforsbospecial.attachHeader("#text_search,#combo_filter");
    allempsforsbospecial.setColSorting("str,str,str,str");
    allempsforsbospecial.attachEvent("onDrag", function(sId, tId, sObj, tObj, sInd, tInd) {
        let selectedDate = specialbreakoffdates.getSelectedRowId();
    console.log("Employee Dragged ID:", sId); // Log the dragged employee ID
    console.log("Selected Date ID:", selectedDate); // Log the selected date ID from specialbreakoffdates

    if (selectedDate) {
        // Proceed with removing the employee if a valid date is selected
        let filterby_month = $('#filterby_month_2').val();  // Get the selected month
        $('#loaders').show();
        $.ajax({
            url: livesite + "ScheduledBreakOff/removeEmpFromSBO",  // Ensure this URL is correct
            type: "POST",  // Use POST to send data securely
            data: {
                id: sId,  // Employee ID
                month: filterby_month,  // Get the selected month from the filter
                sbodate: selectedDate  // Send the selected break-off date
            },
            success: function(response) {
                // Log the server response 
console.log(response); 
var message = response.trim().replace(/"/g, ''); // Remove double quotes
 if (message.indexOf("Employee removed to selected date") !== -1) {
    $.notify(message, { type: 'success', allow_dismiss: false });

                    }  else {
                        $.notify(message, {
                            type: 'danger',
                            allow_dismiss: false,
                            offset: { x: 70, y: 60 }, // Move up
            placement: { from: "bottom", align: "right" }

                        });
                          $('#loaders').hide();
                        $id = specialbreakoffdates.getSelectedRowId();
                        doOnBreakOffDateSelectspecial($id)
                    }
         


            }
           
        });
          $('#loaders').hide();
    } 

    return true;  // Allow the drag event to continue
});
    allempsforsbospecial.setMultiLine(false);
    allempsforsbospecial.enableDragAndDrop(true);
    allempsforsbospecial.init();

    empsinsbospecial = new dhtmlXGridObject('empsinsbospecial');
    empsinsbospecial.selMultiRows = true;
    //	empsinsp.setImagePath("../../../codebase/imgs/");
    empsinsbospecial.setHeader("Name,Branch,Designation,Department");
    empsinsbospecial.attachHeader("#text_filter,#text_filter,#text_filter,#text_filter");
    empsinsbospecial.attachEvent("onDrag", function(sId, tId, sObj, tObj, sInd, tInd) {
        // your code here
        console.log(sId)
        if (specialbreakoffdates.getSelectedRowId()) {

            let filterby_month = $('#filterby_month_2').val();
            let first_half = $('#first_half_2').is(':checked');
            var message = $('#messageComboff').val();
            $.ajax({
                url: livesite + "ScheduledBreakOff/addEmpToSBO",
                data: {
                    id: sId,
                    month: filterby_month,
                    message: message,
                    type: 'W',
                    first_half: first_half,
                    sbodate: specialbreakoffdates.getSelectedRowId()
                },
                success: function(response) {
                    //var text = response.responseText;
                    // process server response here
                  //  console.log(response);
                  var message = response.trim().replace(/"/g, ''); // Remove double quotes
//var message = response.trim().replace(/"/g, '').replace(/\//g, ' / ');
    if (message.indexOf("Employee added to selected date") !== -1) {
    $.notify(message, { type: 'success', allow_dismiss: false });
                    } else {
                        $.notify(message, {
                            type: 'danger',
                            allow_dismiss: false,
                            offset: { x: 70, y: 60 }, // Move up
            placement: { from: "bottom", align: "right" }

                        });
                        $id = specialbreakoffdates.getSelectedRowId();
                        doOnBreakOffDateSelectspecial($id)
                    }
                }
            });

        }
        return true;
    });
    empsinsbospecial.setInitWidths("150,110,110,110");
    empsinsbospecial.setColAlign("left,left,left,left");
    //	empsinsp.setColTypes("ed,ed");
    empsinsbospecial.setColSorting("str,str,str,str");
    empsinsbospecial.setMultiLine(false);
    empsinsbospecial.enableDragAndDrop(true);
    empsinsbospecial.init();
    //---------Ends---------

    function filterwithDutyTime() {
        console.log("selected id", scheduledbreakoffdates.getSelectedRowId());
        if (scheduledbreakoffdates.getSelectedRowId()) {
            doOnBreakOffDateSelect(scheduledbreakoffdates.getSelectedRowId());
        } else {
            alert("Please select a date from the Dates Listed table first");
        }

    }

    function filterwithDutyTime2() {
        console.log("selected id", specialbreakoffdates.getSelectedRowId());
        if (specialbreakoffdates.getSelectedRowId()) {
            doOnBreakOffDateSelectspecial(specialbreakoffdates.getSelectedRowId());
        } else {
            alert("Please select a date from the Dates Listed table first");
        }

    }

    function clearTime() {
        $('#dutty_time').val('0:00:00');
        $("#in_dutty_time").val('0:00:00');
    }

    function filterScheduledBreakOffSecond() {

        var monthChoosen = $('#filterby_month_2').val();
        let alldays = $('#alldays').is(':checked');
        console.log("alldays", alldays);
        if(alldays) {
            specialbreakoffdates.clearAndLoad("<?php echo $this->webroot; ?>ScheduledBreakOff/listbreakoffdatesforsbospecialall/" + monthChoosen, "json");
        } else {
            specialbreakoffdates.clearAndLoad("<?php echo $this->webroot; ?>ScheduledBreakOff/listbreakoffdatesforsbospecial/" + monthChoosen, "json");
        }
    }

    $('#dutty_time').timepicker({
        format: 'hh:mm:ss',
        showMeridian: false,
        showSeconds: true,
        autoclose: true,
    });

    $("#in_dutty_time").timepicker({
        format: 'hh:mm:ss',
        showMeridian: false,
        showSeconds: true,
        autoclose: true,
    });
         /* edited by bindu 24-10-25 */
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
	/* edited by bindu 24-10-25 end*/
</script>