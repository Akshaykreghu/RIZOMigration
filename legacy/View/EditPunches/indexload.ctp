<style>
    #editpunchform table tr td {
        padding: 5px;
        width: 100%;
    }

    .form-horizontal .control-label {

        text-align: left;
        padding-left: 2px;
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
<!--<section class="content-header">
    <h1 style="text-align:left; font-size: 3em;" class="col-md-6"> Edit Attendance </h1>
    <div id="tipeditpunches" class="col-md-4 pull-right">
        <div class="callout callout-success">
            <h4>Tip!</h4>

            <p>Please Sync Attendances if you have any chnages. <li style="font-size: 30px; " class="fa fa-user pull-right"></li> </p>
            
        </div>

    </div>
</section>-->
<!-- edited by athira 14-10-2025 -->
<ul class="nav nav-tabs" role="tablist">
  <!-- <li class="nav-item">
    <a class="nav-link active" data-toggle="tab" href="#tab1" role="tab">Edit Attendance</a>
  </li> -->
  <!-- <li class="nav-item">
    <a class="nav-link" data-toggle="tab" href="#tab2" role="tab">Bulk Attendance</a>
  </li> -->
</ul>


<div class="tab-content" style="margin-top:15px;">
      <div class="tab-pane  active" id="tab1" role="tabpanel">
        
<section class="content-header">
    <!-- edited by athira on 03-07-2025 -->
    <!-- <h1 class="col-md-4 text-primary-18">Edit Attendance</h1> -->
    <!-- end -->
       <!-- edited by athira on 03-07-2025 -->
     <div style="display:flex;justify-content:space-between;align-items:center;">
    <h1 class=" text-primary-18" style="padding-left:0;">Edit Attendance</h1>
      <div class="text-primary-16 home" style="display:flex; align-items:center; gap:10px; cursor:pointer;">
        <i class="fa" style="font-size:16px;">&#xf104;</i>
        Back
    </div>
</div>
    <!-- end -->
     <div class="col-md-12" align="right">
        <button title="if you are finished missing in/out punches of the employee then you should use the
                            Amendments button then only it will effect in attendance records" class="btn btn-primary pull-right" type="button" onclick="updatemem();">Refresh </button>
     
        <!-- <button class="btn btn-primary pull-right" type="button" onclick="iteratemem();" style="margin-right: 10px;">Re-iterate </button>
        <button class="btn btn-primary pull-right" type="button" onclick="syncemem();" style="margin-right: 10px;">Re-Sync </button> -->
       
    </div> 

    <hr style="margin-top: 38px;margin-bottom: -2px;">
</section>

<!-- Main content -->
<section class="content">
    <div class="col-md-12">
        <br>
        <form class="form-horizontal" method="post" action="">
            <div id="tb" class="form-group" style="padding:5px;height:auto">

                <div class="col-md-4">
                    <label for="month" class="col-md-3 control-label">Employee </label>
                    <div class="col-md-1 control-label">:</div>
                    <div class="col-md-8">
                        <select id="employeeCombo" onchange="refresheditpunchgrid();" style="width: 200px;">
                            <?php foreach ($arr_employees as $employee) { ?>
                                <option value="<?php echo $employee->id; ?>" <?php echo (isset($emp_id) && $emp_id == $employee->id) ? 'selected="selected"' : ''; ?>><?php echo $employee->text; ?></option>
                            <?php } ?>
                        </select>
                    </div>
                </div>
                <div class="col-md-4">

                    <label for="month" class="col-md-3 control-label">Month </label>
                    <div class="col-md-1 control-label">:</div>
                    <div class="col-md-8">
                        <select id="monthCombo" onchange="refresheditpunchgrid();" style="width: 250px;">
                            <?php //foreach ($arr_months as $month) { 
                            ?>
                            <!--  <option value="<?php echo $month->id; ?>"><?php echo $month->text; ?></option> -->
                            <?php //} 
                            ?>
                            <?php
                            $start_month = strtotime(date('Y-m', strtotime("+1 month", strtotime(date('Y-m')))));
                            for ($i = 0; $i < 24; $i++) {
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

            </div>
        </form>

        <div>
            <div class="box-body">
                <table class="easyui-datagrid" id="editpunches" class="table table-bordered table-hover">

                </table>
            </div>
        </div>
    </div>
</section>
</div>


<div class="tab-pane fade" id="tab2" role="tabpanel">
    <section class="content-header heading">
      
        <!-- /* edited by bindu 22-08-25 */ -->
    <h1 class="text-primary-18">Bulk Attendance</h1>
  <!-- <div class="text-primary-16 home"> Back to Attendance Home</div> -->
    <!-- end -->
   
</section>
<hr style="margin-top: 8px;margin-bottom: -2px;margin-right: 15px;margin-left: 15px;">
<!-- Main content -->
<section class="content">
    <div class="row">
        <div class="col-md-12">

            <div class="tabset0">
                <div data-pws-tab="tab1" data-pws-tab-name="Scheduled Break Off" data-pws-tab-icon="fa-cog">

                    <div class="box ">
                        <div class="box-header with-border">
                            <h3 class="box-title">Bulk Attendance Allocation</h3>
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
                                <!-- <div class="col-md-3">
                                    <input type="text" class="form-control pull-right" value="" placeholder="Break-off Message" name="message" id="message" required="">
                                </div> -->
                                <div class="col-md-3">
                                    <select class="form-control" id="out_direction">
                                        <option value="N">In & Out</option>
                                        <option value="I">In Only</option>
                                        <option value="O">Out Only</option>
                                    </select>
                                </div>
                                <div class="col-md-2" >
                                            <input type="checkbox" id="first_half_1" name="duration"/>
                                            <label>First Half</label>
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

                <div data-pws-tab="tab2" data-pws-tab-name="Comb Off " data-pws-tab-icon="fa-cog" style="display:none;">
                    
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
</div>
</div>

<!-- Modal -->
<div class="modal fade" id="messageModal" tabindex="-1" style="">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content"  style="width:75%;left:20%;border-radius:5px;">
      
      <div class="modal-body">
        <div class="form-group" style="padding: 25px 25px 0 25px;">
          <label for="message" class="font-weight-bold">
            Please provide a valid reason for attendance changes <span class="text-danger">*</span>
          </label>
          <input type="text" 
                 class="form-control" 
                 placeholder="Enter your reason (max 100 characters)" 
                 name="message" 
                 id="message" 
                 required 
                 maxlength="100">
        </div>
      </div>
      
      <div class="modal-footer">
        <button type="button" class="btn btn-primary" id="sendAjaxBtn">Submit</button>
      </div>
      
    </div>
  </div>
</div>
<!-- end -->
<script type="text/javascript">
    function refresheditpunchgrid() {
        //var emp = $('#employeeCombo').combobox("getValue");
        //var mnth = $('#monthCombo').combobox("getValue");
        var emp = $('#employeeCombo').val();
        var mnth = $('#monthCombo').val();
        //var includeinactive = $('#includeinactive').is(":checked") ? "Y" : "N";

        $('#editpunches').datagrid('load', {
            emp: emp,
            month: mnth,
            //includeinactive: includeinactive
        })
    }


    function updatemem() {
        var emp = $('#employeeCombo').val();
        var mnth = $('#monthCombo').val();

        if (emp) {
            $.ajax({
                type: "POST",
                url: livesite + "EditPunches/Updateame",
                data: {
                    emp: emp,
                    month: mnth
                },
                success: function(resp) {
                    $.notify("Attendance updated successfully.", {
                        type: 'success',
                        allow_dismiss: false
                    });
                    refresheditpunchgrid();
                }
            });
        } else {
            alert("Please Select a Employee");
        }
    }

    // Edited by Ashin on 14-12-2024
    function iteratemem() {
        var emp = $('#employeeCombo').val();
        var mnth = $('#monthCombo').val();

        if (emp) {
            $.ajax({
                type: "POST",
                url: livesite + "EditPunches/Iterateame",
                data: {
                    emp: emp,
                    month: mnth
                },
                success: function(resp) {
                    $.notify("Attendance updated successfully.", {
                        type: 'success',
                        allow_dismiss: false
                    });
                    refresheditpunchgrid();
                }
            });
        } else {
            alert("Please Select a Employee");
        }
    }

    function syncemem() {
        var emp = $('#employeeCombo').val();
        var mnth = $('#monthCombo').val();

        if (emp) {
            $.ajax({
                type: "POST",
                url: livesite + "EditPunches/Syncame",
                data: {
                    emp: emp,
                    month: mnth
                },
                success: function(resp) {
                    $.notify("Attendance updated successfully.", {
                        type: 'success',
                        allow_dismiss: false
                    });
                    refresheditpunchgrid();
                }
            });
        } else {
            alert("Please Select a Employee");
        }
    }
    // End

    /*function submitForm() {
     $('#editpunchform').form('submit', {
     onSubmit: function () {
     return $(this).form('enableValidation').form('validate');
     },
     success: function (data) {
     clearForm();
     refreshgrid();
     //$('#w').window('close')
     //$.messager.show('Success', "New Attandence Saved Successfully", 'info');
     $.notify("New Attandence Saved Successfully",{
     type: 'success',
     allow_dismiss: false
     });
     }
     });
     }
     function clearForm() {
     $('#empid').val("")
     $('#editpunchform').form('clear');
     
     }*/
</script>
<script>
    $(document).ready(function() {

        $('#tipeditpunches').fadeOut(10000);

        // $('#monthCombo').val((new Date().getFullYear()) + '-' + (new Date().getMonth() + 1));

        $("#monthCombo").select2({
            //placeholder: "Choose Month"
        });
        $("#employeeCombo").select2({
            placeholder: "Choose Employee"
        });

        /*$('#btn-new').linkbutton({
         iconCls: 'icon-add'
         });*/

        /*$('#monthCombo').combobox({
         mode: 'remote',
         url: livesite + 'EditPunches/getmonths',
         panelHeight: 'auto',
         onSelect: function (record) {
         var emp = $('#employeeCombo').combobox("getValue");
         var includeinactive = $('#includeinactive').is(":checked") ? "Y" : "N";
         $('#editpunches').datagrid('load', {
         emp: emp,
         month: record.id,
         includeinactive: includeinactive
         })
         },
         valueField: 'id',
         textField: 'text'
         });
         $('#monthCombo').combobox('setValue', (new Date().getFullYear()) + '-' + (new Date().getMonth() + 1));
         
         $('#employeeCombo').combobox({
         url: 'ApiRequest/listemployees',
         panelHeight: 'auto',
         onSelect: function (record) {
         //console.log(record)
         var mnth = $('#monthCombo').combobox("getValue");
         var includeinactive = $('#includeinactive').is(":checked") ? "Y" : "N";
         $('#editpunches').datagrid('load', {
         emp: record.id,
         month: mnth,
         includeinactive: includeinactive
         })
         },
         valueField: 'id',
         textField: 'text'
         });*/

        $('#editpunches').datagrid({
            queryParams: {
                emp: '<?php echo isset($emp_id) ? $emp_id : '0'; ?>',
                month: $('#monthCombo').val(),
            },
            height: '600px',
            url: livesite + "EditPunches/listpunchesnew",
            pagination: true,
            singleSelect: true,
            iconCls: 'icon-edit',
            pageSize: 32,
            onLoadSuccess: function(data) {
                //            $.messager.show({
                //                title:'Info',
                //                msg:data.message
                //            });
                $.notify(data.message, {
                    type: data.type,
                    allow_dismiss: false
                });

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
            /*onBeforeEdit: function (index, row) {
             row.editing = true;
             updateActions(index);
             },
             onAfterEdit: function (index, row) {
             row.editing = false;
             updateActions(index);
             },
             onCancelEdit: function (index, row) {
             row.editing = false;
             updateActions(index);
             },
             columns: [
             [
             {
             field: 'LOGDATE',
             title: 'Log Time',
             width: "20%"
             },
             {
             field: 'C1',
             title: 'Direction',
             width: "10%",
             editor: {
             type: 'combobox',
             options: {
             valueField: 'C1',
             textField: 'label',
             data: [{
             label: "IN",
             C1: "in"
             }, {
             label: "OUT",
             C1: "out"
             }],
             required: true
             }
             }
             },
             {
             field: 'status',
             title: 'Status',
             width: "10%",
             formatter: function (val, row) {
             if (val == 'Y') {
             return 'Active';
             } else
             {
             return 'Inactive';
             }
             },
             editor: {
             type: 'checkbox',
             options: {on: 'Y', off: 'N'}
             }
             }
             ,
             {
             field: 'C3',
             title: 'Location',
             width: "50%",
             },
             {
             field: 'action',
             title: 'Action',
             width: "12%",
             align: 'center',
             formatter: function (value, row, index) {
             //	console.log(row);
             if (row.editing) {
             var s = '<a href="#" onclick="saverow(this)">Save</a> ';
             var c = '<a href="#" onclick="cancelrow(this)">Cancel</a>';
             return s + c;
             } else {
             var e = '<a href="#" onclick="editrow(this)">Edit</a> ';
             //var d = '<a href="#" onclick="deleterow(this)">Delete</a>';
             return e;
             }
             }
             }
             ]
             ],
             onBeforeEdit : function (index, row) {
             row.editing = true;
             updateActions(index);
             },
             onAfterEdit : function (index, row) {
             row.editing = false;
             updateActions(index);
             },
             onCancelEdit : function (index, row) {
             row.editing = false;
             updateActions(index);
             }*/
            //New List starts
            columns: [
                [{
                        field: 'att_date',
                        title: 'Log Date',
                        width: "15%"
                    },
                    {
                        field: 'att_in_time',
                        title: 'Start',
                        width: "20%"
                    },
                    {
                        field: 'att_out_time',
                        title: 'End',
                        width: "20%",
                    },
                    {
                        field: 'duration',
                        title: 'Duration',
                        width: "15%",
                    },
                    {
                        field: 'status',
                        title: 'Status',
                        width: "15%",
                        formatter: function(value, row, index) {
                            // if(row.leave_date === row.att_date){

                            // var e = "<div style=\"background-color: "+row.status_color+"; float: left; width: 100%; padding: 14% 0;\">"+value+"</div>";
                            var e = "<strong style=\"color: " + row.status_color + ";\">" + row.leaves + "" + '  ' + "" + value + " </strong>";
                            // alert(e);
                            // }
                            //               else{
                            // var e = "<strong style=\"color: "+row.status_color+";\">"+value+"</strong>";
                            //               }
                            return e;
                            // alert(e);
                        }
                    },
                    {
                        field: 'action',
                        title: 'Action',
                        width: "15%",
                        align: 'center',
                        formatter: function(value, row, index) {
                            if (row.isdelete == 'Y' && row.joining_date <= row.att_date) {

                                //var e = '<a href="#" onclick="showEditOnPopup(\'' + row.att_date + '\',\'' + row.emp_id + '\',\'' + row.att_in_time + '\',\'' + row.att_out_time + '\');">Edit</a> ';
                                var e = '<a href="#" onclick="showEditOnPopup(\'' + window.btoa(JSON.stringify(row)) + '\');">Edit</a> ';
                                return e;
                            }
                            return "";
                        }
                    }
                ]
            ],
            //Ends
        });

    });

    //New List actions
    function showEditOnPopup(str_row) {
        var site_transactions_fkey = '';
        var row = JSON.parse(window.atob(str_row));
        extract(row, this);
        if (att_date && emp_id) {
            //Add form
            //        IF(!site_transactions_fkey){
            //                site_transactions_fkey = 0;
            //            }
            showModalForm(livesite + 'EditPunches/editpunch/' + att_date + '/' + emp_id + '/' + site_transactions_fkey + '/' + encodeURI(att_in_time) + '/' + encodeURI(att_out_time));
        } else {
            alert("Please select a record!")
        }
    }

    function extract(data, where) {
        for (var key in data) {
            where[key] = data[key];
        }
    }
    //Ends

    function Backto() {
        $("#container").isLoading({
            text: "Loading",
            position: "overlay",
        });

        $("#container").load(livesite + "AttendanceSetup/index", function() {
            isDashboardShown = false;
        });
    }

    //edited by athira on 14-10-2025
    
     function toggleCheckbox(element) {
        //element.checked = !element.checked;
        var inputValue = $(this).attr("value");
        $("#time").toggle();
    }
    var monthChoosen = $('#filterby_month').val();

    // jQuery(document).ready(function($) {

        // $('.tabset0').pwstabs({
        //     effect: 'scale', // You can change effects of your tabs container: scale / slideleft / slideright / slidetop / slidedown / none
        //     defaultTab: 1, // The tab we want to be opened by default
        //     containerWidth: '100%', // Set custom container width if not set then 100% is used
        //     tabsPosition: 'horizontal', // Tabs position: horizontal / vertical
        //     horizontalPosition: 'top', // Tabs horizontal position: top / bottom
        //     verticalPosition: 'left', // Tabs vertical position: left / right
        //     responsive: true, // Make tabs container responsive: true / false - boolean
        //     theme: '',
        //     rtl: false // Right to left support: true/ false
        // });



    // });

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
        let duration =  $('#first_half_1').is(':checked');
        var type= 'Attendance';
        if (scheduledbreakoffdates.getSelectedRowId()) {
            $('#loaders').show();
            // $.ajax({
            //     url: livesite + "ScheduledBreakOff/addEmpToSBO",
            //     data: {
            //         id: sId,
            //         month: filterby_month,
            //         message: message,
            //         dutty_time: dutty_time,
            //         in_dutty_time: in_dutty_time,
            //         type: 'Attendance',
            //         first_half: first_half,
            //         sbodate: scheduledbreakoffdates.getSelectedRowId()
            //     },
            //     success: function(response) {
            //         //var text = response.responseText;
            //         // process server response here
            //         if (response) {
            //             $.notify("Empolyee Added To Selected Date", {
            //                 type: 'success',
            //                 allow_dismiss: false

            //             });
            //             $('#loaders').hide();
            //         } else {
            //             $.notify(response, {
            //                 type: 'danger',
            //                 allow_dismiss: false

            //             });
            //             $('#loaders').hide();
            //         }
            //     }
            // });

            // Show modal
// $('#messageModal').modal('show');
// Show modal (prevent close on outside click or ESC key)
$('#messageModal').modal({
    backdrop: 'static',
    keyboard: false
}).modal('show');


// On submit
$('#sendAjaxBtn').off('click').on('click', function () {
    var message = $('#message').val().trim();

    // 🔴 Validate before calling AJAX
   if (message === "") {
    $.notify({
        message: "Message is required."
    }, {
        type: 'danger',
        allow_dismiss: false,
        z_index: 9999 // force above modal
    });
    $('#message').focus();
    return false;
}

if (message.length > 100) {
    $.notify({
        message: "Maximum 100 characters allowed."
    }, {
        type: 'danger',
        allow_dismiss: false,
        z_index: 9999
    });
    $('#message').focus();
    return false;
}


    $('#loaders').show();
     let first_half = $('#first_half_1').is(':checked');
    $.ajax({
        url: livesite + "ScheduledBreakOff/addEmpToSBO",
        type: "POST",
        data: {
            id: sId,
            month: filterby_month,
            message: message,
            dutty_time: dutty_time,
            in_dutty_time: in_dutty_time,
            attendance: type,
            duration: duration,
            first_half: first_half,
            sbodate: scheduledbreakoffdates.getSelectedRowId()
        },
        success: function (response) {
            if (response) {
                $.notify("Employee Added To Selected Date", { type: 'success', allow_dismiss: false });
            } else {
                $.notify(response, { type: 'danger', allow_dismiss: false });
            }
            $('#loaders').hide();
            $('#messageModal').modal('hide');
            $('#message').val('');
        },
        error: function () {
            $.notify("Something went wrong. Try again.", { type: 'danger', allow_dismiss: false });
            $('#loaders').hide();
        }
    });
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
        let filterby_month = $('#filterby_month').val();
        if (specialbreakoffdates.getSelectedRowId()) {

            $('#loaders').show();
            $.ajax({
                url: livesite + "ScheduledBreakOff/removeEmpFromSBO",
                data: {
                    id: sId,
                    month: filterby_month,
                    sbodate: specialbreakoffdates.getSelectedRowId()
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
            // $.ajax({
            //     url: livesite + "ScheduledBreakOff/addEmpToSBO",
            //     data: {
            //         id: sId,
            //         month: filterby_month,
            //         message: message,
            //         type: 'W',
            //         first_half: first_half,
            //         sbodate: specialbreakoffdates.getSelectedRowId()
            //     },
            //     success: function(response) {
            //         //var text = response.responseText;
            //         // process server response here
            //         if (response) {
            //             $.notify("Empolyee Added To Selected Date", {
            //                 type: 'success',
            //                 allow_dismiss: false

            //             });
            //         } else {
            //             $.notify(response, {
            //                 type: 'danger',
            //                 allow_dismiss: false

            //             });
            //         }
            //     }
            // });

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

     	/* edited by bindu 22-08-25 */
    $(".home").on("click", function() {
        $("#container").isLoading({
            text: "Loading",
            position: "overlay",
        });

        $("#container").load(livesite + "AttendanceSetup/index", function() {
            isDashboardShown = false;
        });


    });
    //end

    /*function updateActions(index) {
     $('#editpunches').datagrid('updateRow', {
     index: index,
     row: {}
     });
     }
     
     function getRowIndex(target) {
     var tr = $(target).closest('tr.datagrid-row');
     return parseInt(tr.attr('datagrid-row-index'));
     }
     function editrow(target) {
     $('#editpunches').datagrid('beginEdit', getRowIndex(target));
     }
     function deleterow(target) {
     /*$.messager.confirm('Confirm', 'Are you sure?', function (r) {
     if (r) {
     
     var rows = $('#editpunches').datagrid('getRows')
     var row = rows[getRowIndex(target)];
     $.ajax({
     type: "POST",
     url: livesite + "EditPunches/remove",
     data: {
     device_attandance_seq: row.device_attandance_seq
     
     },
     dataType: 'json',
     success: function (resp) {
     //var resp = $.parseJSON(resp);
     //$('#editpunches').datagrid('deleteRow', getRowIndex(target));
     if (resp.success == true) {
     $.messager.show('Success', "Attandence removed successfully", 'info');
     refreshgrid();
     } else {
     $.messager.alert('Failed', "Error occured while removing record", 'info');
     }
     
     }
     });
     
     }
     });*/

    /*    var r = confirm("Are you sure to delete this punch?");
     if (r == true) {
     var rows = $('#editpunches').datagrid('getRows')
     var row = rows[getRowIndex(target)];
     $.ajax({
     type: "POST",
     url: livesite + "EditPunches/remove",
     data: {
     device_attandance_seq: row.device_attandance_seq
     },
     dataType: 'json',
     success: function (resp) {
     //var resp = $.parseJSON(resp);
     //$('#editpunches').datagrid('deleteRow', getRowIndex(target));
     if (resp.success == true) {
     //$.messager.show('Success', "Attandence removed successfully", 'info');
     $.notify("Attandence removed successfully", {
     type: 'success',
     allow_dismiss: false
     });
     refreshgrid();
     } else {
     //$.messager.alert('Failed', "Error occured while removing record", 'info');
     $.notify("Error occured while removing record", {
     type: 'danger',
     allow_dismiss: false
     });
     }
     
     }
     });
     } else {
     return false;
     }
     }
     function saverow(target, row, value) {
     var rows = $('#editpunches').datagrid('getRows')
     //console.log(rows[getRowIndex(target)]);
     var row = rows[getRowIndex(target)];
     $('#editpunches').datagrid('endEdit', getRowIndex(target));
     $.ajax({
     type: "POST",
     url: livesite + "EditPunches/savepunch",
     data: {
     C1: row.C1,
     DEVICEID: row.DEVICEID,
     DEVICELOGID: row.DEVICELOGID,
     branch_code: row.branch_code,
     device_attandance_seq: row.device_attandance_seq,
     status: row.status
     },
     dataType: 'json',
     success: function (resp) {
     refreshgrid();
     }
     });
     }
     function cancelrow(target) {
     $('#editpunches').datagrid('cancelEdit', getRowIndex(target));
     }
     function insert() {
     //var emp = $('#employeeCombo').combobox("getValue");
     var emp = $('#employeeCombo').val();
     if (emp) {
     $('#empid').val(emp);
     //$('#w').window('open');
     //Add form
     showModalForm(livesite + 'EditPunches/form/' + emp);
     } else {
     alert("Please select an employee")
     }
     }*/
    	/* edited by bindu 22-08-25 */
    $(".home").on("click", function() {
        $("#container").isLoading({
            text: "Loading",
            position: "overlay",
        });

        $("#container").load(livesite + "AttendanceSetup/index", function() {
            isDashboardShown = false;
        });


    });
    //end
    
</script>