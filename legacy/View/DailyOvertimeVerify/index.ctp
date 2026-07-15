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

 
 
.datagrid-row-selected .edit-button {
    color: #FFFFFF !important; /* Forces the color of the Edit button to remain white */
}

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
</style>



<section class="content-header heading" style="margin-top:0;">
    <h1 class="text-primary-18">Verifying Daily Overtime/Attendance </h1>
    <!-- edited by sinsiya on 02-11-2024-->
    <div class="heading">

        <div class="col-md-12" align="right" style="margin: 10px 0;">
            <button onclick="updateamendmence();" title="If your datas not being proccessed, Please click this button to update all the entries again" id="updateamendance" class="btn btn-primary pull-right pull-up">Refresh</button>
        </div>
        <div class="text-primary-16 home" style="display:flex; align-items:center; gap:10px; cursor:pointer;">
            <i class="fa" style="font-size:16px;">&#xf104;</i>
            Back
        </div>
    </div>
</section>

<!-- Main content -->
<section class="content">
    <div class="row">
        <div class="">
            <div class="box ">
                
                <div class="box-body">
                    <div class="col-md-12">
                        <label class="col-md-1 control-label" for="filterby_branch" style="text-align: left;">Branch</label>
                        <div class="col-md-3">
                            <select id="filterby_branch" name="filterby_branch" class="form-control select2-searching" onchange="filterDailyOvertime();" >
                                <?php
                                foreach ($arr_branches as $key => $value) {
                                    echo '<option value="' . $value['branch_code'] . '">' . $value['branch_name'] . '</option>';
                                }
                                ?>
                            </select>
                        </div>
                        <label class="col-md-1 control-label" for="filterby_branch" style="text-align: left;">Month</label>
                        <div class="col-md-3">
                            <select id="filterby_month" name="filterby_month" class="form-control" onchange="filterDailyOvertime();">
                                <option value="">Select </option>
                                <?php
                               // $start_month = strtotime(date('Y-m')); 
                               
                                $start_month =  strtotime("+1 month", strtotime(date('Y-m')));
                                for ($i = 0; $i < 10; $i++) {
                                    $month = date('Y-m', strtotime("-$i month", $start_month));
                                    if ($month == date('Y-m')) {
                                        echo '<option selected="selected" value="' . $month . '">' . date('M-Y', strtotime("-$i month", $start_month)) . '</option>';
                                    } else {
                                        echo '<option value="' . $month . '">' . date('M-Y', strtotime("-$i month", $start_month)) . '</option>';
                                    }
                                }
                                ?>
                            </select>
                            <input type="hidden" id="date1" name="date1" value="" >
                            <input type="hidden" id="date2" name="date2" value="" >
                        </div>
                    </div>
                    <div class="">
                        <div class="col-sm-2" style="display: block;    margin-top: 20px;" id="dateDiv1">
                           <!-- <h3>Date</h3> -->
                            <div id="dailyovertimedates" style="width:100%; height:530px; background-color:white;"></div>
                        </div>
                        <div class="col-sm-2" style="display: none;    margin-top: 20px;" id="dateDiv2">
                             <!-- <h3>Date</h3> -->
                            <div id="dailyovertimedates_verify" style="width:100%; height:530px; background-color:white;"></div>
                        </div>
                        <div class="col-sm-10" id="data-tab-select" style="padding-right:0px;">
                            <br>
                            <div class="tabset-attendanceregister" style="padding-left: 0px;padding-right:0px;">
                                <div id="tab1" data-pws-tab="tab1" data-pws-tab-name="Not Verified" data-pws-tab-icon="fa-spinner fa-spin" onclick="load_date_grid(1)">
                                    <div style="width:100%; height:450px; background-color:white;">
<div class="datagrid-toolbar">
            <table cellspacing="0" cellpadding="0">
                <tbody>
                    <tr>
                        <td>
                            <a href="javascript:void(0)" onclick="verifyRegisterEntries();" class="l-btn l-btn-small l-btn-plain" group="" id="">
                                <span class="l-btn-left l-btn-icon-left">
                                    <span class="l-btn-text">Verify</span>
                                    <span class="l-btn-icon icon-ok">&nbsp;</span>
                                </span>
                            </a>
                        </td>
                        <td style="padding-left: 10px;">Select Employee :  </td>
                        <td >
                            <select style="margin-left: 15px;" id="employee_select"  name="employee_select"  class="form-control" onchange="filterDailyOvertimeByEmployee(this);" >
                                <option value="">--All--</option>
                                <?php
//                                foreach ($arr_employees as $key => $value) {
//                                    echo '<option value="' . $value['emp_pkey'] . '">' . $value['emp_name'] . '</option>';
//                                }
                                 //edited by arul on 12/12/2019 Employee company id added
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
                                        <table  id="dailyovertimediv" class="table table-bordered table-hover easyui-datagrid">

                                        </table>
                                    </div>
                                </div>
                                <div id="tab2" data-pws-tab="tab2" data-pws-tab-name="Verified" data-pws-tab-icon="fa-spinner fa-spin" onclick="load_date_grid(2)">
                                    <div style="width:100%; height:450px; background-color:white;">
<div class="datagrid-toolbar">
            <table cellspacing="0" cellpadding="0">
                <tbody>
                    <tr>
                        <td>
                            <a href="javascript:void(0)" onclick="removeEntries();" class="l-btn l-btn-small l-btn-plain" group="" id="">
                                <span class="l-btn-left l-btn-icon-left">
                                    <span class="l-btn-text">Remove</span>
                                    <span class="l-btn-icon icon-cancel">&nbsp;</span>
                                </span>
                            </a>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
                                        <table  id="dailyovertimediv_verified" class="table table-bordered table-hover easyui-datagrid">

                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <!-- <div class="col-sm-5">
                            <h3>All employees</h3>
                            <div id="allempsforsbo" style="width:100%; height:400px; background-color:white;"></div>
                        </div>
                        <div class="col-sm-5">
                            <h3>Employees taken break off in selected date </h3>
                            <div id="empsinsbo" style="width:100%; height:400px; background-color:white;"></div>
                        </div> -->
                    </div>

                </div>
            </div>
        </div>
    </div>
</section>

<script>
    var monthChoosen = $('#filterby_month').val();
    var branch = $('#filterby_branch').val();

    var dailyovertimedates;

    var dailyovertimedates_verified;
    // var allempsforsbo;
    // var empsinsbo;
    jQuery(document).ready(function () {
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

    jQuery('#data-tab-select .pws_tabs_controll a').on('click', function () {
        var tabIndex = $(this).data('tabId');
        load_date_grid(tabIndex);
    });

    //---------Daily Attendance/Overtime Verifys---------
    function overtimeDateSelect(date) {
        $('#date1').val(date);
        $('#dailyovertimediv').datagrid('load', {
            branch: $("#filterby_branch").val(),
            month: date,
        });

        // $("#test_div").html();
        // $.ajax({
        //     url: livesite + "DailyOvertimeVerify/listpunches",
        //     data: {
        //         branch: $("#filterby_branch").val(),
        //         month: date,
        //     },
        //     success: function(response) {
        //         if (response) {
        //             $("#test_div").html(response);
        //             // $.notify("Empolyee Removed From Selected Date", {
        //             //     type: 'success',
        //             //     allow_dismiss: false
        //             // });
        //         } else {
        //             $.notify(response, {
        //                 type: 'danger',
        //                 allow_dismiss: false
        //             });
        //         }
        //     }
        // });
        // allempsforsbo.clearAndLoad("<?php echo $this->webroot; ?>DailyOvertimeVerify/listemployeesforsbodate?date=" + id, "json");
        // empsinsbo.clearAndLoad("<?php echo $this->webroot; ?>DailyOvertimeVerify/listemployeesinsbodate?date=" + id, "json");
    }

    function overtimeDateVerifiedSelect(date) {
        $('#date2').val(date);
        $('#dailyovertimediv_verified').datagrid('load', {
            branch: $("#filterby_branch").val(),
            month: date,
        });
    }

    dailyovertimedates = new dhtmlXGridObject('dailyovertimedates');
    dailyovertimedates.setHeader("Date");
    dailyovertimedates.setColAlign("left");
    dailyovertimedates.setColumnIds("day_time_desc");
    dailyovertimedates.setInitWidthsP("100");
    dailyovertimedates.attachHeader("#text_search");
    //myGrid.enableAutoHeight(true,400);
    dailyovertimedates.attachEvent("onRowSelect", overtimeDateSelect);
    dailyovertimedates.enableAutoWidth(true);
    dailyovertimedates.setColTypes("ro");
    dailyovertimedates.init();
    dailyovertimedates.load("<?php echo $this->webroot; ?>DailyOvertimeVerify/listbreakoffdatesforsbo/" + monthChoosen+"/"+branch, "json");

    dailyovertimedates_verified = new dhtmlXGridObject('dailyovertimedates_verify');
    dailyovertimedates_verified.setHeader("Date");
    dailyovertimedates_verified.setColAlign("left");
    dailyovertimedates_verified.setColumnIds("day_time_desc");
    dailyovertimedates_verified.setInitWidthsP("100");
    dailyovertimedates_verified.attachHeader("#text_search");
    //myGrid.enableAutoHeight(true,400);
    dailyovertimedates_verified.attachEvent("onRowSelect", overtimeDateVerifiedSelect);
    dailyovertimedates_verified.enableAutoWidth(true);
    dailyovertimedates_verified.setColTypes("ro");
    dailyovertimedates_verified.init();
    dailyovertimedates_verified.load("<?php echo $this->webroot; ?>DailyOvertimeVerify/listbreakoffdatesforsbo_verified/" + monthChoosen+"/"+branch, "json");

    function filterDailyOvertime() {
      //console.log('hi'); 
        var monthChoosen = $('#filterby_month').val();
        let branch = $("#filterby_branch").val();
        dailyovertimedates.clearAndLoad("<?php echo $this->webroot; ?>DailyOvertimeVerify/listbreakoffdatesforsbo/" + monthChoosen+"/"+branch, "json");
        dailyovertimedates_verified.clearAndLoad("<?php echo $this->webroot; ?>DailyOvertimeVerify/listbreakoffdatesforsbo_verified/" + monthChoosen+"/"+branch, "json");
      //  edited by sinsiya on 22-11-2024
        $('#dailyovertimediv').datagrid('loadData', { total: 0, rows: [] });

        // Clear the datagrid data for #dailyovertimediv_verified
         $('#dailyovertimediv_verified').datagrid('loadData', { total: 0, rows: [] });
        $('#dailyovertimediv').datagrid('load', {
            // branch: '',
            month: '',
            branch: branch, 
            // month: monthChoosen + '-01',
            //includeinactive: includeinactive
        });

        $('#dailyovertimediv_verified').datagrid('load', {
            // branch: '',
            month: '',
            branch: branch,
            // month: monthChoosen + '-01',
            //includeinactive: includeinactive
        });

    }
function filterDailyOvertimeByEmployee(selectElement) {
    var monthChoosen = $('#filterby_month').val();
    var branch = $("#filterby_branch").val();
      var empid = $(selectElement).val();
console.log(empid); 
console.log(monthChoosen);
console.log(branch); 
    // Reload break-off dates
    //dailyovertimedates.clearAndLoad(
   // "<?php echo $this->webroot; ?>DailyOvertimeVerify/listbreakoffdatesforsbo/" + monthChoosen + "/" + branch + "/" + empid,
  //  "json"
//);
//dailyovertimedates.clearAndLoad("<?php echo $this->webroot; ?>DailyOvertimeVerify/listbreakoffdatesforsbo/" + monthChoosen+"/"+branch, "json");
        //dailyovertimedates_verified.clearAndLoad("<?php echo $this->webroot; ?>DailyOvertimeVerify/listbreakoffdatesforsbo_verified/" + monthChoosen+"/"+branch, "json");
$('#dailyovertimediv').datagrid({
        queryParams: {
           branch: branch,
        month: monthChoosen,
        empid: empid
        },
        height: '430px',
        url: livesite + "DailyOvertimeVerify/listpunches",
        autoRowHeight: true,
        pagination: true,
        pageSize: 10,
        rownumbers: true,
        singleSelect: false,
        //iconCls: 'icon-edit',
	width: '100%',
        onLoadSuccess: function(data) {
            // if(data.type == 'danger') {
            //     $("#dailyovertimediv").notify(data.message, {
            //         type: data.type,
            //         position:"left"
            //     });
            // } else {
            // }
            $.notify(data.message, {
                type: data.type,
                allow_dismiss: false
            });
          
          
        },
        //fitColumns: true,
        pageList: [2, 5, 10, 20, 32, 50, 100],
        rowStyler: function(index, row) {
            var style = "";
            if (row.status == 'N') {
                style += 'background-color:rgba(214, 110, 13, 0.92);color:#FFFFFF';
            } else {
                if (row.C1 == 'out') {
                    style += 'background-color:#A9F5A9;';
                } else if (row.C1 == 'in') {
                    style += 'background-color:#FAAC58;';
                }
            }
            return style;
        },

        columns: [
            [
                // {
                //     field: 'att_date',
                //     title: 'Log Date',
                //     width: "15%"
                // },
                {field: 'chek_encash_remove', title: '', width: "2%",checkbox:true},
                {
                    field: 'first_name',
                    title: 'Employee',
                    width: "22%"
                },
                {
                    field: 'att_in_time',
                    title: 'Start',
                    width: "16%"
                },
                {
                    field: 'att_out_time',
                    title: 'End',
                    width: "16%",
                },
                {
                    field: 'duration',
                    title: 'Duration',
                    width: "8%",
                },
                {
                    field: 'status',
                    title: 'Status',
                    width: "8%",
                    formatter: function(value, row, index) {
                        var e = "<strong style=\"color: " + row.status_color + ";\">" + row.leaves + "" + '  ' + "" + value + " </strong>";
                        return e;
                    }
                },
               
                
                {
                    field: 'ot_duration',
                    title: 'OT Min',
                    width: "8%",
                },{
                    field: 'set_duration',
                    title: '<span style="color:green;">Set OT Min</span>',
                    width: "10%",
                    formatter: function(value, row, index) {
                        if(row.ot_duration > 0){
                            var e = '<input type="number" name="setDuration'+row.emp_detail_timeattandance_pkey+'" id="setDuration'+row.emp_detail_timeattandance_pkey+'" style="color:black;width:100%;" value="'+value+'" min="0" oninput="this.value = this.value < 0 ? 0 : this.value" onchange="updateDuration(\'' + window.btoa(JSON.stringify(row)) + '\')"> ';
                            return e;
                        }
                    }
                },
                 {
                    field: 'action',
                    title: 'Action',
                    width: "8%",
                    align: 'center',
                    formatter: function(value, row, index) {
// if (row.company_code != 'HRBL' ) {
                       // if (row.editable && row.joining_date <= row.att_date  ) { 
 if (row.joining_date <= row.att_date  ) {
                            var e = '<a href="#" class="edit-button" onclick="showEditOnPopup(\'' + window.btoa(JSON.stringify(row)) + '\');">Edit</a> ';
                            return e;
                        } 
//}else{
//var e = '<a href="#" onclick="showEditOnPopup(\'' + window.btoa(JSON.stringify(row)) + '\');">Edit</a> ';
                   //         return e;
//}
                        //return "";
                    }
                },
//EDITED BY SINSIYA ON 06-03-2025
                {
    field: 'remarks',
    title: '<span style="color:green;">Remarks</span>',
    width: "10%",
    formatter: function(value, row, index) {
        // Ensure remarks are always blank when first loading
        value = ''; // Force blank value for first load
        if (row.ot_duration > 0) {
            var e = '<input type="text" name="remark' + row.emp_detail_timeattandance_pkey + 
                    '" id="remark' + row.emp_detail_timeattandance_pkey + 
                    '" style="color:black;width:100%;" value="' + value + 
                    '" onchange="setRemarks(\'' + window.btoa(JSON.stringify(row)) + '\')" autocomplete="off">';
            return e;
        }
        return ''; // Return blank if OT duration is 0
    }
},{
                    field: 'verify',
                    title: '<span style="color:green;">Verify</span>',
                    width: "7%",
                    align: 'center',
                    //formatter: function(value, row, index) {
                      //  var e = '<button class="btn btn-primary" onclick="verify(\'' + window.btoa(JSON.stringify(row)) + '\');">Verify</button> ';
                        //return e;
                    //}
//edited by sinsiya on 03-10-2024
                   formatter: function(value, row, index) {
        // Check if both att_in_time and att_out_time have values 
        if (row.att_in_time && row.att_out_time) {
            var e = '<button class="btn btn-primary" onclick="verify(\'' + window.btoa(JSON.stringify(row)) + '\');">Verify</button> ';
            return e;
        }
        var status = (row.status || '').trim().toUpperCase();

        // Show the Verify button if the status is 'NA', regardless of att_in_time or att_out_time
        if (status === 'NA') {
            var e = '<button class="btn btn-primary" onclick="verify(\'' + window.btoa(JSON.stringify(row)) + '\');">Verify</button>';
            return e;
        }
        // Return empty string if att_in_time or att_out_time is missing
        return '';
    }
                }
                



            ]
        ],
        //Ends
    });

//dailyovertimedates_verified.clearAndLoad(
   // "<?php echo $this->webroot; ?>DailyOvertimeVerify/listbreakoffdatesforsbo_verified/" + monthChoosen + "/" + branch + "/" + empid,
  //  "json"
//);

    // Clear existing data in both grids
    $('#dailyovertimediv').datagrid('loadData', { total: 0, rows: [] });
    $('#dailyovertimediv_verified').datagrid('loadData', { total: 0, rows: [] });

    // 🔁 Load new data filtered by employee
    $('#dailyovertimediv').datagrid('load', {
        month: monthChoosen, // or pass monthChoosen + '-01' if needed
        branch: branch,
        empid: empid // 👈 Add employee filter
    });

    $('#dailyovertimediv_verified').datagrid('load', {
        month: monthChoosen,
        branch: branch,
        empid: empid // 👈 Add employee filter
    });
}

 $('#employee_select').select2();

var monthChoosen = $('#filterby_month').val();
var empid = $('#employee_select').val();
console.log(empid);
    $('#dailyovertimediv').datagrid({
        queryParams: {
            // branch: $("#filterby_branch").val(),
             //monthselected: $("#filterby_month").val()+'-01',
             empid: empid
        },
        height: '430px',
        url: livesite + "DailyOvertimeVerify/listpunches",
        autoRowHeight: true,
        pagination: true,
        pageSize: 10,
        rownumbers: true,
        singleSelect: false,
        //iconCls: 'icon-edit',
	width: '100%',
        onLoadSuccess: function(data) {
            // if(data.type == 'danger') {
            //     $("#dailyovertimediv").notify(data.message, {
            //         type: data.type,
            //         position:"left"
            //     });
            // } else {
            // }
            $.notify(data.message, {
                type: data.type,
                allow_dismiss: false
            });
          
          
        },
        //fitColumns: true,
        pageList: [2, 5, 10, 20, 32, 50, 100],
        rowStyler: function(index, row) {
            var style = "";
            if (row.status == 'N') {
                style += 'background-color:rgba(214, 110, 13, 0.92);color:#FFFFFF';
            } else {
                if (row.C1 == 'out') {
                    style += 'background-color:#A9F5A9;';
                } else if (row.C1 == 'in') {
                    style += 'background-color:#FAAC58;';
                }
            }
            return style;
        },

        columns: [
            [
                // {
                //     field: 'att_date',
                //     title: 'Log Date',
                //     width: "15%"
                // },
                {field: 'chek_encash_remove', title: '', width: "2%",checkbox:true},
                {
                    field: 'first_name',
                    title: 'Employee',
                    width: "22%"
                },
                {
                    field: 'att_in_time',
                    title: 'Start',
                    width: "16%"
                },
                {
                    field: 'att_out_time',
                    title: 'End',
                    width: "16%",
                },
                {
                    field: 'duration',
                    title: 'Duration',
                    width: "8%",
                },
                {
                    field: 'status',
                    title: 'Status',
                    width: "8%",
                    formatter: function(value, row, index) {
                        var e = "<strong style=\"color: " + row.status_color + ";\">" + row.leaves + "" + '  ' + "" + value + " </strong>";
                        return e;
                    }
                },
               
                
                {
                    field: 'ot_duration',
                    title: 'OT Min',
                    width: "8%",
                },{
                    field: 'set_duration',
                    title: '<span style="color:green;">Set OT Min</span>',
                    width: "10%",
                    formatter: function(value, row, index) {
                        if(row.ot_duration > 0){
                            var e = '<input type="number" name="setDuration'+row.emp_detail_timeattandance_pkey+'" id="setDuration'+row.emp_detail_timeattandance_pkey+'" style="color:black;width:100%;" value="'+value+'" min="0" oninput="this.value = this.value < 0 ? 0 : this.value" onchange="updateDuration(\'' + window.btoa(JSON.stringify(row)) + '\')"> ';
                            return e;
                        }
                    }
                },
                 {
                    field: 'action',
                    title: 'Action',
                    width: "8%",
                    align: 'center',
                    formatter: function(value, row, index) {
// if (row.company_code != 'HRBL' ) {
                       // if (row.editable && row.joining_date <= row.att_date  ) { 
 if (row.joining_date <= row.att_date  ) {
                            var e = '<a href="#" class="edit-button" onclick="showEditOnPopup(\'' + window.btoa(JSON.stringify(row)) + '\');">Edit</a> ';
                            return e;
                        } 
//}else{
//var e = '<a href="#" onclick="showEditOnPopup(\'' + window.btoa(JSON.stringify(row)) + '\');">Edit</a> ';
                   //         return e;
//}
                        //return "";
                    }
                },
//EDITED BY SINSIYA ON 06-03-2025
                {
    field: 'remarks',
    title: '<span style="color:green;">Remarks</span>',
    width: "10%",
    formatter: function(value, row, index) {
        // Ensure remarks are always blank when first loading
        value = ''; // Force blank value for first load
        if (row.ot_duration > 0) {
            var e = '<input type="text" name="remark' + row.emp_detail_timeattandance_pkey + 
                    '" id="remark' + row.emp_detail_timeattandance_pkey + 
                    '" style="color:black;width:100%;" value="' + value + 
                    '" onchange="setRemarks(\'' + window.btoa(JSON.stringify(row)) + '\')" autocomplete="off">';
            return e;
        }
        return ''; // Return blank if OT duration is 0
    }
},{
                    field: 'verify',
                    title: '<span style="color:green;">Verify</span>',
                    width: "7%",
                    align: 'center',
                    //formatter: function(value, row, index) {
                      //  var e = '<button class="btn btn-primary" onclick="verify(\'' + window.btoa(JSON.stringify(row)) + '\');">Verify</button> ';
                        //return e;
                    //}
//edited by sinsiya on 03-10-2024
                   formatter: function(value, row, index) {
        // Check if both att_in_time and att_out_time have values 
        if (row.att_in_time && row.att_out_time) {
            var e = '<button class="btn btn-primary" onclick="verify(\'' + window.btoa(JSON.stringify(row)) + '\');">Verify</button> ';
            return e;
        }
        var status = (row.status || '').trim().toUpperCase();

        // Show the Verify button if the status is 'NA', regardless of att_in_time or att_out_time
        if (status === 'NA') {
            var e = '<button class="btn btn-primary" onclick="verify(\'' + window.btoa(JSON.stringify(row)) + '\');">Verify</button>';
            return e;
        }
        // Return empty string if att_in_time or att_out_time is missing
        return '';
    }
                }
                



            ]
        ],
        //Ends
    });

    $('#dailyovertimediv_verified').datagrid({
        queryParams: {
            // branch: $("#filterby_branch").val(),
            // month: $("#filterby_month").val()+'-01',
        },
        height: '430px',
        url: livesite + "DailyOvertimeVerify/listpunchesverify",
        pagination: true,
        pageSize: 10,
        singleSelect: false,
        iconCls: 'icon-edit',
        onLoadSuccess: function(data) {
            // if(data.type == 'danger') {
            //     $("#dailyovertimediv_verified").notify(data.message, {
            //         type: data.type,
            //         position:"left"
            //     });
            // } else {
                
            // }


            //$.notify(data.message, {
            //    type: data.type,
            //    allow_dismiss: false
            //});
        },
        fitColumns: true,
        pageList: [2, 5, 10, 20, 32, 50, 100],
        rowStyler: function(index, row) {
            var style = "";
            if (row.status == 'N') {
                style += 'background-color:rgba(214, 110, 13, 0.92);color:#FFFFFF';
            } else {
                if (row.C1 == 'out') {
                    style += 'background-color:#A9F5A9;';
                } else if (row.C1 == 'in') {
                    style += 'background-color:#FAAC58;';
                }
            }
            return style;
        },

        columns: [
            [
                // {
                //     field: 'att_date',
                //     title: 'Log Date',
                //     width: "15%"
                // },
                {field: 'chek_encash_remove', title: '', width: "2%",checkbox:true},
                {
                    field: 'first_name',
                    title: 'Employee',
                    width: "20%"
                },
                {
                    field: 'att_in_time',
                    title: 'Start',
                    width: "15%"
                },
                {
                    field: 'att_out_time',
                    title: 'End',
                    width: "15%",
                },
                {
                    field: 'duration',
                    title: 'Duration',
                    width: "10%",
                },
                {
                    field: 'status',
                    title: 'Status',
                    width: "8%",
                    formatter: function(value, row, index) {
                        var e = "<strong style=\"color: " + row.status_color + ";\">" + row.leaves + "" + '  ' + "" + value + " </strong>";
                        return e;
                    }
                },
                // {
                //     field: 'action',
                //     title: 'Action',
                //     width: "8%",
                //     align: 'center',
                //     formatter: function(value, row, index) {
                //         if (row.editable) {
                //             var e = '<a href="#" onclick="showEditOnPopup(\'' + window.btoa(JSON.stringify(row)) + '\');">Edit</a> ';
                //             return e;
                //         }
                //         return "";
                //     }
                // },
                // {
                //     field: 'min_bfr_on_dutty_cal_ot',
                //     title: 'OT Before',
                //     width: "8%",
                // },
                // {
                //     field: 'min_aftr_off_dutty_cal_ot',
                //     title: 'OT After',
                //     width: "8%",
                // },
                // {
                //     field: 'work_time_day_off_cal_ot',
                //     title: 'Duration',
                //     width: "15%",
                // },
                {
                    field: 'ot_duration',
                    title: 'OT Min',
                    width: "8%",
                },{
                    field: 'set_duration',
                    title: 'Set OT Min',
                    width: "10%",
                },{
                    field: 'remarks',
                    title: 'Remarks',
                    width: "16%",
                }
            ]
        ],
        //Ends
    });

    function showEditOnPopup(str_row) {
        var site_transactions_fkey = '';
        var row = JSON.parse(window.atob(str_row));
        extract(row, this);
        if (att_date && emp_pkey) {
            showModalForm(livesite + 'DailyOvertimeVerify/editpunch/' + att_date + '/' + emp_pkey + '/' + site_transactions_fkey + '/' + encodeURI(att_in_time) + '/' + encodeURI(att_out_time));
        } else {
            alert("Please select a record!")
        }
    }
    function extract(data, where) {
        for (var key in data) {
            where[key] = data[key];
        }
    }

    function load_date_grid(tab) {
        if(tab == "tab1") {
            $("#dateDiv1").css("display","block");
            $("#dateDiv2").css("display","none");

        } else if(tab == "tab2") {
            $("#dateDiv1").css("display","none");
            $("#dateDiv2").css("display","block");
        }
        // if($('#date1').val()){
        //     $('#dailyovertimediv').datagrid('load', {
        //         branch: $("#filterby_branch").val(),
        //         month: $('#date1').val(),
        //     });
        // }
        // if($('#date2').val()) {
        //     $('#dailyovertimediv_verified').datagrid('load', {
        //         branch: $("#filterby_branch").val(),
        //         month: $('#date2').val(),
        //     });
        // }
    }

    function verify(str_row) {
        if(confirm('Are you sure to verify the record?')){
            var row = JSON.parse(window.atob(str_row));
            extract(row, this);
            if (att_date && emp_pkey) {
                $.ajax({
                    url: livesite + "DailyOvertimeVerify/verify",
                    data: {
                        att_date: att_date,
                        emp_pkey: emp_pkey
                    },
                    success: function(response) {
                        if (response) {
                            // $("#test_div").html(response);
                            $.notify("Verified Successfully", {
                                type: 'success',
                                allow_dismiss: false
                            });

                            $('#dailyovertimediv').datagrid('load', {
                                branch: $("#filterby_branch").val(),
                                month: $('#date1').val(),
                            });
                            $('#dailyovertimediv_verified').datagrid('load', {
                                branch: $("#filterby_branch").val(),
                                month: $('#date1').val(),
                            });
                            filterDailyOvertime();
                        } else {
                            $.notify('Someting wrong. please try again!', {
                                type: 'danger',
                                allow_dismiss: false
                            });
                        }
                    }
                });
            } else {
                alert("Please select a record!")
            }
        }
    }

  function updateDuration(str_row) {
    var row = JSON.parse(window.atob(str_row));
    extract(row, this);
    let setDuration = $("#setDuration" + row.emp_detail_timeattandance_pkey).val(); // This is the text field value

    if (att_date && emp_pkey && setDuration) {
        $.ajax({
            url: livesite + "DailyOvertimeVerify/updateSetDuration",
            data: {
                att_date: att_date,
                emp_pkey: emp_pkey,
                value: setDuration
            },
            success: function(response) {
                if (response) {
                    // Show success notification
                    var notification = $.notify("Updated Successfully", {
                        type: 'success',
                        allow_dismiss: false
                    });

                    // Hide the notification after 1 second
                    setTimeout(function() {
                        notification.close(); // Manually close the notification
                    }, 1000); // 1000 ms = 1 second
                } else {
                    // Show error notification
                    var errorNotification = $.notify('Something went wrong. Please try again!', {
                        type: 'danger',
                        allow_dismiss: false
                    });

                    // Hide the error notification after 1 second
                    setTimeout(function() {
                        errorNotification.close(); // Manually close the notification
                    }, 1000); // 1000 ms = 1 second
                }
            }
        });
    } else {
        // Show error notification for missing data
        var missingDataNotification = $.notify('Required data is missing.', {
            type: 'danger',
            allow_dismiss: false
        });

        // Hide the missing data notification after 1 second
        setTimeout(function() {
            missingDataNotification.close(); // Manually close the notification
        }, 1000); // 1000 ms = 1 second
    }
}


function setRemarks(str_row) {
    var row = JSON.parse(window.atob(str_row)); // Decode and parse row data
    var emp_pkey = row.emp_detail_timeattandance_pkey; // Extract employee key
    var att_date = row.att_date; // Extract attendance date

    // Get the value of the remark input field by targeting the correct input
    let remark = $("#remark" + emp_pkey).val(); // This is the text field value

    // Check if att_date, emp_pkey, and remark are valid
    if (att_date && emp_pkey && remark) {
        $.ajax({
            url: livesite + "DailyOvertimeVerify/setRemarks", // Backend URL for setting remarks
            type: "POST", // Specify HTTP method
            data: {
                att_date: att_date,
                emp_pkey: emp_pkey,
                value: remark
            },
            success: function(response) {
                if (response) {
                    // Show success notification
                    $.notify("Updated Successfully", {
                        type: 'success',
                        allow_dismiss: false
                    });

                    // Optionally, you can reload the data grid or refresh the UI here
                    // $('#dailyovertimediv').datagrid('reload'); // Uncomment if needed
                } else {
                    // Show error notification
                    $.notify('Something went wrong. Please try again!', {
                        type: 'danger',
                        allow_dismiss: false
                    });
                }
            },
            error: function() {
                // Handle AJAX error
                $.notify('Error in the request. Please try again!', {
                    type: 'danger',
                    allow_dismiss: false
                });
            }
        });
    } else {
        // Handle case where required data is missing
        $.notify('Remark cannot be empty.', {
            type: 'warning',
            allow_dismiss: false
        });
    }
}


   // function setRemarks(str_row){
     //   var row = JSON.parse(window.atob(str_row));
       // extract(row, this);
        //let remark = $("#remark"+emp_detail_timeattandance_pkey).val();// This is the text field value
        //if (att_date && emp_pkey && remark) {

          //  $.ajax({
            //    url: livesite + "DailyOvertimeVerify/setRemarks",
              //  data: {
                //    att_date: att_date,
                  //  emp_pkey: emp_pkey,
                    //value:remark
                //},
                //success: function(response) {
                   // if (response) {
                        // $("#test_div").html(response);
                       // $.notify("Updated Successfully", {
                           // type: 'success',
                           // allow_dismiss: false
                       // });

                        //$('#dailyovertimediv').datagrid('load', {
                        //    branch: $("#filterby_branch").val(),
                        //    month: $('#date1').val(),
                        //});
                        // $('#dailyovertimediv_verified').datagrid('load', {
                        //     branch: $("#filterby_branch").val(),
                        //     month: $('#date1').val(),
                        // });
                        //filterDailyOvertime();
                  //  } else {
                       // $.notify('Someting wrong. please try again!', {
                         //   type: 'danger',
                         //   allow_dismiss: false
                      //  });
                 //   }
               // }
            //});
       // }
   // }


function verifyRegisterEntries() {

            var rows = $('#dailyovertimediv').datagrid('getSelections');
           // var data = $('#dailyovertimediv').datagrid('getData');
            if (rows.length > 0) {
                var str_ids = "";
                var id = 0;
                for (var i = 0; i < rows.length; i++) {
                var data = rows[i];
                    if (str_ids == "") {
                        str_ids += data.emp_detail_timeattandance_pkey;
                    } else
                    {
                        str_ids += "," + data.emp_detail_timeattandance_pkey;
                    }
                 
              }

doVerificationProcedure(str_ids);
            }else{
alert("Please select atleast one record to verify.");
}
         
           
    }
function doVerificationProcedure(str_ids) {
        if (confirm("Are you sure to verifying the selected entries?")) {
            $.ajax({
                url: livesite +"DailyOvertimeVerify/verifyregisterentries",
                type: 'post',
                data: {
                    ids: str_ids
                },
                success: function (response) {
                    //process server response here
                    var success = $.parseJSON(response).success;
                    if (success) {
                       $.notify("Updated Successfully", {
                            type: 'success',
                            allow_dismiss: false
                        });

                        $('#dailyovertimediv').datagrid('load', {
                            branch: $("#filterby_branch").val(),
                            month: $('#date1').val(),
                        });
                        $('#dailyovertimediv_verified').datagrid('load', {
                            branch: $("#filterby_branch").val(),
                            month: $('#date1').val(),
                         });
                        filterDailyOvertime();
                    } else {
                        //alert('Attendance verification failed!');
                        $.notify("Verification failed!", {
                            type: 'danger',
                            allow_dismiss: false
                        });
                    }
                }
            });
        }
    }

function removeEntries() {

            var rows = $('#dailyovertimediv_verified').datagrid('getSelections');
           // var data = $('#dailyovertimediv_verified').datagrid('getData');
            if (rows.length > 0) {
                var str_ids = "";
                var id = 0;
                for (var i = 0; i < rows.length; i++) {
                var data = rows[i]; 
                    if (str_ids == "") {
                        str_ids += data.emp_detail_timeattandance_pkey;
                    } else
                    {
                        str_ids += "," + data.emp_detail_timeattandance_pkey;
                    }
                 
              }

doRemovalProcedure(str_ids);
            }else{
alert("Please select atleast one record to verify.");
}
}
//function doRemovalProcedure(str_ids) {
        //if (confirm("Are you sure to removing the selected items?")) {
            //$.ajax({
                //url: livesite +"DailyOvertimeVerify/removeentries",
                //type: 'post',
                //data: {
                  //  ids: str_ids
                //},
                //success: function (response) {
                    //process server response here
                    //var success = $.parseJSON(response).success;
                 // console.log(success);
                    //if (success) {
                     //  $.notify("Removed Successfully", { //edited by sinsiya 05-10-2024
                          //  type: 'success',
                        //    allow_dismiss: false
                      //  });

                        //$('#dailyovertimediv').datagrid('load', {
                           // branch: $("#filterby_branch").val(),
                         //   month: $('#date1').val(),
                       // });
                       // $('#dailyovertimediv_verified').datagrid('load', {
                       //     branch: $("#filterby_branch").val(),
                     //       month: $('#date1').val(),
                   //      });
                 //       filterDailyOvertime();
               //     } else {
                        //alert('Attendance verification removal failed!');
                        //$.notify("Can't remove, attendance already verified.", {
                          //  type: 'danger',
                          //  allow_dismiss: false
                        //});
                        //$('#dailyovertimediv').datagrid('load', {
                          //  branch: $("#filterby_branch").val(),
                        //    month: $('#date1').val(),
                      //  });
                        //$('#dailyovertimediv_verified').datagrid('load', {
                        //    branch: $("#filterby_branch").val(),
                         //   month: $('#date1').val(),
                        // });
            //            filterDailyOvertime();
          //          }
        //        }
      //      });
    //    }
  //  }
function doRemovalProcedure(str_ids) {
    if (confirm("Are you sure to remove the selected items?")) {
        $.ajax({
            url: livesite + "DailyOvertimeVerify/removeentries",
            type: 'post',
            data: {
                ids: str_ids
            },
            success: function(response) {
               try {
    var parsedResponse = $.parseJSON(response);
    var success = parsedResponse.success;
    console.log("Success value:", success);

    // Convert string values to numbers if necessary
    if (success === "true" || success === true || success === 1 || success === "1") {
        // Notify success
        $.notify("Removed Successfully", {
            type: 'success',
            allow_dismiss: false
        });

        // Reload both tables to reflect the changes
        $('#dailyovertimediv').datagrid('load', {
            branch: $("#filterby_branch").val(),
            month: $('#date1').val(),
        });
        $('#dailyovertimediv_verified').datagrid('load', {
            branch: $("#filterby_branch").val(),
            month: $('#date1').val(),
        });

        // Call your custom filtering function if needed
        filterDailyOvertime();
    } else {
        // Different failure messages depending on the condition
        if (success === 0 || success === "0") {
            $.notify("Can't remove, attendance already verified.", {
                type: 'danger',
                allow_dismiss: false
            });
        } else {
            $.notify("You cannot remove, an approved entry!", { 
                type: 'danger',
                allow_dismiss: false
            });
        }

        // Reload tables again to ensure consistency
        $('#dailyovertimediv').datagrid('load', {
            branch: $("#filterby_branch").val(),
            month: $('#date1').val(),
        });
        $('#dailyovertimediv_verified').datagrid('load', {
            branch: $("#filterby_branch").val(),
            month: $('#date1').val(),
        });

        filterDailyOvertime();
    } } catch (e) {
                    // Handle any parsing errors
                 //   $.notify("Error processing the request. Please try again.", {
                     //   type: 'danger',
                   //     allow_dismiss: false
                   // });
                }
            },
            error: function() {
                // Handle AJAX errors
                $.notify("Error communicating with the server. Please try again.", {
                    type: 'danger',
                    allow_dismiss: false
                });
            }
        });
    }
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
