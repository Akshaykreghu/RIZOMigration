<style>
    #editpunchform table tr td {
        padding: 5px;
        width: 100%;
    }

    .form-horizontal .control-label {

        text-align: left;
        padding-left: 2px;
    }

    .dropdownstatus {
        color: #000;
        border: 1px solid #dcdcdd;
        font-weight: bold;
    }

    .optionselects {
        width: max-content;
        height: fit-content;
        position: absolute;
        background: white;
        padding: 10px;
        left: 0px;
        top: 25;
        display: none;
        z-index: 100;
        border: 1px solid gray;
    }

    .datagrid-cell {
        height: auto;
        position: relative;
        overflow: inherit;
    }

    .dropdown1 span {
        line-height: 22px;
        width: 44px;
        text-align: center;
        border: 1px solid gray;
        margin-top: 4px;
        margin-left: 6px;
    }

    .dropdown1 .btn {
        padding: 0px 1px;
        text-align: center;
        background-color: #f4f4f4;
        color: #444;
        border-color: #ddd;
        margin-top: 4px;
    }

    .dropdown1 {
        margin-left: 11px !important;
        border: none !important;
        display: flex;
        color: #000;
        font-weight: bold;
        flex-direction: column;
    }

    .datagrid-row-selected strong {
        color: #fff !important;
    }

    .table-bordered td,
    .table-bordered th {
        text-align: center;
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
<section class="content-header">
    <h1 class="col-md-4 text-primary-18">Edit Attendance</h1>
    <div class="col-md-8">
        <button title="if you are finished missing in/out punches of the employee then you should use the
                            Amendments button then only it will effect in attendance records" class="btn btn-primary pull-right" type="button" onclick="updatemem();">Refresh </button>
    </div>

    <hr style="margin-top: 38px;margin-bottom: -2px;">
</section>

<!-- Main content -->
<section class="content">
    <div class="col-md-12">
        <br>
        <form class="form-horizontal" method="post" action="">
            <div id="tb" class="form-group" style="padding:5px;height:auto">

                <div class="col-md-6">
                    <div class="col-md-6">
                        <label for="month" class="col-md-3 control-label">Employee </label>
                        <select id="employeeCombo" onchange="refresheditpunchgrid();" style="width: 250px;">
                            <?php debug($arr_employees);
                            // exit();
                            ?>
                            <?php foreach ($arr_employees as $employee) { ?>
                                <option value="<?php echo $employee->id; ?>" <?php echo (isset($emp_id) && $emp_id == $employee->id) ? 'selected="selected"' : ''; ?>><?php echo $employee->text; ?></option>
                            <?php } ?>
                        </select>
                    </div>

                    <div class="col-md-6">

                        <label for="month" class="col-md-3 control-label">Month </label>
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

                    <div class="col-md-12">
                        <hr style="margin-top: 14px;
    margin-bottom: 0px;
    border: 0;
    border-top: 1px solid #aca2a2; ">
                    </div>


                    <!-- <div class="col-md-12"> -->
                    <div class="col-md-6" style="margin-top: 6px ">
                        <label for="month" class="col-md-12 control-label shiftonelabel">Shift 1 </label>
                        <select id="shift1combo" style="width: 250px;">
                            <option value="0">Select</option>
                            <option value="P/P">P/P</option>
                            <!-- <option value="A/A">A/A</option> -->
                            <option value="P/A">P/A</option>
                            <option value="A/P">A/P</option>
                            <option value="LOP">LOP</option>
                        </select>
                    </div>
                    <div class="col-md-6" style="margin-top: 6px; ">
                        <label for="month" class="col-md-12 control-label shofttwolabel">Shift 2 </label>
                        <select id="sift2combo" style="width: 250px;">
                            <option value="0">Select</option>
                            <option value="D/D">D/D</option>
                            <!-- <option value="A/A">A/A</option> -->
                        </select>
                    </div>

                    <div class="col-md-9" style="width: 250px; margin: 15px; padding: 0; ">
                        <label for="month" class="col-md-8 control-label"></label>
                        <button type="button" style="width: 100%; " class="btn btn-primary" onclick="updateselecteditem();">Bulk Update On Selected Record(s)</button>
                    </div>

                    <!-- </div> -->

                </div>

                <div class="col-md-6">

                    <div class="col-md-12" style="margin-top: 0px; ">
                        <table class="table table-bordered table-hover table-balances datatable">
                            <tr>
                                <th>Calendar Days</th>
                                <th>Present Days</th>
                                <th>Week OFF</th>
                                <th>Holidays</th>
                                <th>LOP</th>
                                <th>D/D</th>
                            </tr>
                            <tr>
                                <td id="days">0</td>
                                <td id="present">0</td>
                                <td id="weekoff">0</td>
                                <td id="holidays">0</td>
                                <td id="lop">0</td>
                                <td id="dd">0</td>
                            </tr>
                        </table>
                    </div>

                    <div class="col-md-12" style="margin-top: 0px; ">
                        <table class="table table-bordered table-hover datatable leavabalnces">

                        </table>
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
        <!-- <div class="col-md-12">
            <table id="lesshour" class="table table-bordered table-hover">
                <tr>
                    <th style="width:20px;">Late IN</th>
                    <th style="width:20px;">Early OUT</th>
                    <th style="width:20px;">Total Less Hour</th>
                    <th style="width:40px;">Action</th>
                </tr>
                <tr>
                    <td style="width:20px;"><input type="text" id="late_in_count" name="late_in_count" disabled></td>
                    <td style="width:20px;"><input type="text" id="early_out_count" name="early_out_count" disabled></td>
                    <td style="width:20px;"><input type="text" id="sum" name="sum" disabled></td>
                    <td style="width:40px;"><input type="number" id="less_hour_count" name="less_hour_count" min="0">
                        <button type="button" onclick="Submitlesshour();" class="btn btn-primary">Save</button>
                    </td>
                </tr>
            </table>
        </div>-->
    </div>
</section>
<script type="text/javascript">
    function refresheditpunchgrid() {
        //var emp = $('#employeeCombo').combobox("getValue");
        //var mnth = $('#monthCombo').combobox("getValue");
        var emp = $('#employeeCombo').val();
        var mnth = $('#monthCombo').val();
        //var includeinactive = $('#includeinactive').is(":checked") ? "Y" : "N";
        /* if (emp) {
            $.ajax({
                type: "POST",
                url: livesite + "EditAttendance/ealry_out_late_in",
                data: {
                    emp: emp,
                    month: mnth
                },
                success: function(resp) {
                    var resp = $.parseJSON(resp);
                    $('#late_in_count').val(resp.latein);
                    $('#early_out_count').val(resp.earlyout);
                    $('#less_hour_count').val(resp.lesshour);
                    var sum = parseInt(resp.latein, 10) + parseInt(resp.earlyout, 10);
                    $('#sum').val(sum);
                }
            });
        } */
        $('#editpunches').datagrid('load', {
            emp: emp,
            month: mnth,
            //includeinactive: includeinactive
        })
    }
</script>
<script>
    function updatemem() {
        var emp = $('#employeeCombo').val();
        var mnth = $('#monthCombo').val();

        if (emp) {
            $.ajax({
                type: "POST",
                url: livesite + "EditAttendance/Updateame",
                data: {
                    emp: emp,
                    month: mnth
                },
                success: function(resp) {
                    $.notify("Attandence Updates successfully", {
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
</script>
<script>
    $(document).mouseup(function(e) {
        var container = $(".optionselects");

        // if the target of the click isn't the container nor a descendant of the container
        if (!container.is(e.target) && container.has(e.target).length === 0) {
            container.hide();
        }
    });

    $(document).ready(function() {

        $('#tipeditpunches').fadeOut(10000);

        // $('#monthCombo').val((new Date().getFullYear()) + '-' + (new Date().getMonth() + 1));

        $("#monthCombo").select2({
            //placeholder: "Choose Month"
        });
        $("#employeeCombo").select2({
            placeholder: "Choose Employee"
        });

        $("#shift1combo").select2({
            placeholder: "Choose Option",
        });

        $("#sift2combo").select2({
            placeholder: "Choose Option",
        })

        $('#editpunches').datagrid({
            queryParams: {
                emp: '<?php echo isset($emp_id) ? $emp_id : '0'; ?>',
                month: $('#monthCombo').val(),
            },
            height: '600px',
            url: livesite + "EditAttendance/listpunches",
            pagination: true,
            singleSelect: false,
            iconCls: 'icon-edit',
            pageSize: 32,
            onLoadSuccess: function(data) {
                //            $.messager.show({
                //                title:'Info',
                //                msg:data.message
                //            });
                // console.log(data);
                $.notify(data.message, {
                    type: data.type,
                    allow_dismiss: false
                });

                getMonthDatas();

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
            //New List starts
            columns: [
                [{
                        field: 'emp_detail_timeattandance_pkey',
                        title: '',
                        rowspan: 2,
                        width: "10%",
                        checkbox: true,
                    },
                    {
                        field: 'att_date',
                        title: 'Date',
                        rowspan: 2,
                        width: "10%",
                    },
                    {
                        field: 'att_date_day',
                        title: 'Day',
                        rowspan: 2,
                        width: "10%",
                    },
                    {
                        title: '<span class="shiftonelabel">Shift 1 Name</span> ',
                        colspan: 2,
                    },
                    {
                        title: '<span class="shofttwolabel">Shift 2 Name</span> ',
                        colspan: 2,
                    },
                    {
                        title: '<span class="shiftonelabel">Shift 1 Name</span> ',
                        colspan: 2,
                    },
                    {
                        title: '<span class="shofttwolabel">Shift 2 Name</span> ',
                        colspan: 2,
                    }
                ],
                [{
                        field: 'status',
                        title: 'Status',
                        width: "8%",
                        formatter: function(value, row, index) {
                            // if(row.leave_date === row.att_date){

                            // if (row.editable == true) {


                            // var e = "<div style=\"background-color: "+row.status_color+"; float: left; width: 100%; padding: 14% 0;\">"+value+"</div>";
                            var e = "<strong style=\"color: " + row.status_color + ";\">" + (row.main_status != "" ? row.main_status : value) + " </strong>";
                            // }
                            if (row.editable == true) {
                                e += "<span style='position: absolute; width: 86%; height: 100%; right: 10px; top: 0; ' onclick = \"showEditOnPopupOption(this,'" + row.emp_detail_timeattandance_pkey + "');\"><i class=\"fa fa-angle-down pull-right\"></i></span>";
                                e += "<div class=\"optionselects\">";
                                e += "<div class=\"optionselectsinner\">";
                                e += "<div style='display:flex; flex-direction:row; justify-content:space-around;' >"
                                e += '<div class="dropdown1" style="display: flex; color: #000; border: 1px solid #dcdcdd; font-weight: bold; flex-direction: column; ""><span style="border: none;">First</span><button id="' + row.emp_detail_timeattandance_pkey + '" onclick="updateStatuses(this)" class="btn btn-default" type="first" value="P">P</button><button id="' + row.emp_detail_timeattandance_pkey + '" onclick="updateStatuses(this)" class="btn btn-default" type="first" value="LOP">LOP</button>';
                                for (var i = 0; i < row.arr_leaves.length; i++) {
                                    e += '<button id="' + row.emp_detail_timeattandance_pkey + '" onclick="updateStatuses(this)" class="btn btn-default" type="first" value="' + row.arr_leaves[i].Head + '">' + row.arr_leaves[i].Head + '</button>';
                                }
                                e += '</div>';
                                e += '<div class="dropdown1" style="display: flex; color: #000; border: 1px solid #dcdcdd; font-weight: bold; flex-direction: column; ""><span  style="border: none;">Second</span><button id="' + row.emp_detail_timeattandance_pkey + '" onclick="updateStatuses(this)" class="btn btn-default" type="second" value="P">P</button><button id="' + row.emp_detail_timeattandance_pkey + '" onclick="updateStatuses(this)" class="btn btn-default" type="second" value="LOP">LOP</button>';
                                for (var i = 0; i < row.arr_leaves.length; i++) {
                                    e += '<button id="' + row.emp_detail_timeattandance_pkey + '" onclick="updateStatuses(this)" class="btn btn-default"  type="second" value="' + row.arr_leaves[i].Head + '">' + row.arr_leaves[i].Head + '</button>';
                                }
                                e += '</div>';
                                e += '<div class="dropdown1" style="display: flex; color: #000; border: 1px solid #dcdcdd; font-weight: bold; flex-direction: column; ""><span  style="border: none;">Full</span><button id="' + row.emp_detail_timeattandance_pkey + '" onclick="updateStatuses(this)" class="btn btn-default" type="full"  value="P/P">P</button><button id="' + row.emp_detail_timeattandance_pkey + '" onclick="updateStatuses(this)" class="btn btn-default" type="full" value="HO">HO</button><button id="' + row.emp_detail_timeattandance_pkey + '" onclick="updateStatuses(this)" class="btn btn-default" type="full" value="WO">WO</button><button id="' + row.emp_detail_timeattandance_pkey + '" onclick="updateStatuses(this)" class="btn btn-default" type="full" value="LOP">LOP</button>';
                                for (var i = 0; i < row.arr_leaves.length; i++) {
                                    e += '<button id="' + row.emp_detail_timeattandance_pkey + '" onclick="updateStatuses(this)" class="btn btn-default"  type="full" value="' + row.arr_leaves[i].Head + '">' + row.arr_leaves[i].Head + '</button>';
                                }
                                e += '</div></div>';
                                e += "</div>";
                                e += "</div>";
                            } else {
                                e += "<span onclick=\"alert('Attendance Verified');\"><i class=\"fa fa-angle-down pull-right\"></i></span>";
                            }
                            return e;
                            // alert(e);
                        }
                    },
                    {
                        field: 'duration',
                        title: 'Duration',
                        width: "8%",
                    },
                    {
                        field: 'ad_present',
                        title: 'Status',
                        width: "10%",
                        formatter: function(value, row, index) {
                            // if(row.leave_date === row.att_date){

                            // if (row.editable == true) {
                            // var e = "<div style=\"background-color: "+row.status_color+"; float: left; width: 100%; padding: 14% 0;\">"+value+"</div>";
                            var status = (row.main_status != "" ? row.main_status : row.status);
                            var e = "<strong style=\"color: " + row.adtnl_status_color + ";\">" + value.trim() + " </strong>";
                            // }
                            if (row.editable == true) {
                                e += "<span onclick=\"showEditOnPopupOption(this,'" + row.emp_detail_timeattandance_pkey + "');\"><i class=\"fa fa-angle-down pull-right\"></i></span>";
                                e += "<div class=\"optionselects\">";
                                e += "<div class=\"optionselectsinner\">";
                                e += "<div style='display:flex; flex-direction:row; justify-content:space-around;' >"
                                e += '<div class="dropdown1" style="display: flex; color: #000; border: 1px solid #dcdcdd; font-weight: bold; flex-direction: column; ""><span style="border: none;">First</span><button id="' + row.emp_detail_timeattandance_pkey + '" onclick="updateStatuses2(this)" class="btn btn-default" type="first" statusmain="' + status + '" value="D">D</button><button id="' + row.emp_detail_timeattandance_pkey + '" onclick="updateStatuses2(this)" class="btn btn-default" statusmain="' + status + '" type="first" value="A">A</button>';
                                e += '</div>';
                                e += '<div class="dropdown1" style="display: flex; color: #000; border: 1px solid #dcdcdd; font-weight: bold; flex-direction: column; ""><span  style="border: none;">Second</span><button id="' + row.emp_detail_timeattandance_pkey + '" onclick="updateStatuses2(this)" class="btn btn-default" type="second" statusmain="' + status + '" value="D">D</button><button id="' + row.emp_detail_timeattandance_pkey + '" onclick="updateStatuses2(this)" class="btn btn-default" statusmain="' + status + '" type="second" value="A">A</button>'
                                e += '</div></div>';
                                e += '<div class="dropdown1" style="display: flex; color: #000; text-align: center;justify-content: center; margin-top: 15px; border: 1px solid #dcdcdd; font-weight: bold; width: 71%; justify-content: center; align-content: center; margin: auto; margin-top: 16px; flex-direction: column; ""><span  style="border: none;">Full</span><button id="' + row.emp_detail_timeattandance_pkey + '" statusmain="' + status + '" onclick="updateStatuses2(this)" class="btn btn-default" type="full"  value="D/D">D/D</button><button id="' + row.emp_detail_timeattandance_pkey + '" onclick="updateStatuses2(this)" class="btn btn-default" statusmain="' + status + '" type="full" value="A/A">A</button>';
                                e += '</div>';
                                e += "</div>";
                                e += "</div>";
                            } else {
                                e += "<span onclick=\"alert('Attendance Verified');\"><i class=\"fa fa-angle-down pull-right\"></i></span>";
                            }
                            return e;
                            // alert(e);
                        }
                    },
                    {
                        field: 'ad_duration',
                        title: 'Duration',
                        width: "8%",
                    },
                    // {
                    //     field: 'statusactions',
                    //     title: 'Update',
                    //     width: "8%",
                    //     formatter: function(value, row, index) {
                    //         // if(row.leave_date === row.att_date){

                    //         // var e = "<div style=\"background-color: "+row.status_color+"; float: left; width: 100%; padding: 14% 0;\">"+value+"</div>";
                    //         var e = '<select onchange="changeStatus(this)" class="dropdownstatus" id="' + row.emp_detail_timeattandance_pkey + '"><option value="0">Select</option>';
                    //         e += '<option ' + (row.main_status == 'P/P' ? 'selected' : '') + ' value="P/P">P/P</option>';
                    //         e += '<option ' + (row.main_status == 'A/A' ? 'selected' : '') + ' value="A/A">A/A</option>';
                    //         e += '<option ' + (row.main_status == 'P/A' ? 'selected' : '') + ' value="P/A">P/A</option>';

                    //         for (var i = 0; i < row.arr_leaves.length; i++) {
                    //             e += '<option ' + (row.main_status == row.arr_leaves[i].Head ? 'selected' : '') + ' value="' + row.arr_leaves[i].Head + '">' + row.arr_leaves[i].Head + ' - ' + row.arr_leaves[i].leaveBalance + '</option>';
                    //         }

                    //         e += '</select>';
                    //         return e;

                    //         // alert(e);
                    //     }
                    // },
                    {
                        field: 'att_in_time',
                        title: 'IN',
                        width: "11%"
                    },
                    {
                        field: 'att_out_time',
                        title: 'OUT',
                        width: "11%",
                    },
                    {
                        field: 'ad_in_time',
                        title: 'IN',
                        width: "11%",
                    },
                    {
                        field: 'ad_out_time',
                        title: 'OUT',
                        width: "11%",
                    },
                    // {
                    //     field: 'action',
                    //     title: 'Action',
                    //     width: "8%",
                    //     align: 'center',
                    //     formatter: function(value, row, index) {
                    //         // console.log(row);
                    //         if (row.isdelete == 'Y' && row.joining_date <= row.att_date && row.editable == true) { //Edited by Akshay for removing edit button in verified attendance

                    //             //var e = '<a href="#" onclick="showEditOnPopup(\'' + row.att_date + '\',\'' + row.emp_id + '\',\'' + row.att_in_time + '\',\'' + row.att_out_time + '\');">Edit</a> ';
                    //             var e = '<a href="#" onclick="showEditOnPopup(\'' + window.btoa(JSON.stringify(row)) + '\');">Edit</a> ';
                    //             return e;
                    //         }
                    //         return "";
                    //     }
                    // },
                    // {
                    //     field: 'statusadnlactions',
                    //     title: 'Update',
                    //     width: "8%",
                    //     formatter: function(value, row, index) {
                    //         var e = '<select onchange="changeStatusAd(this)" class="dropdownstatus" id="' + row.emp_detail_timeattandance_pkey + '"><option value="0">Select</option><option value="D/D">D/D</option><option value="A/A">A/A</option><option value="D/A">D/A</option>';
                    //         e += '</select>';
                    //         return e;
                    //         // alert(e);
                    //     }
                    // },
                    // {
                    //     field: 'shift_string',
                    //     title: 'Shift',
                    //     width: "24%",
                    // },
                    // {
                    //     field: 'ad_shift_string',
                    //     title: 'Adtnl Shift',
                    //     width: "24%",
                    // },
                    // {
                    //     field: 'ad_remarks',
                    //     title: 'Adtnl Remarks',
                    //     width: "10%",
                    // },
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
            showModalForm(livesite + 'EditAttendance/editpunch/' + att_date + '/' + emp_id + '/' + site_transactions_fkey + '/' + encodeURI(att_in_time) + '/' + encodeURI(att_out_time));
        } else {
            alert("Please select a record!")
        }
    }

    function changeStatus(_this) {
        //console.log(row);
        $.ajax({
            type: "POST",
            url: livesite + "EditAttendance/chnagestatus",
            data: {
                device_attandance_seq: $(_this).attr('id'),
                status: $(_this).val(),

            },
            dataType: 'json',
            success: function(resp) {
                //var resp = $.parseJSON(resp);
                //$('#editpunches').datagrid('deleteRow', getRowIndex(target));
                if (resp.success == true) {
                    $.messager.show('Success', "Attandence removed successfully", 'info');
                    // refresheditpunchgrid();
                } else {
                    $.messager.alert('Failed', "Error occured while removing record", 'info');
                }
            }
        });

    }

    function getMonthDatas() {
        //console.log(row);
        $.ajax({
            type: "POST",
            url: livesite + "EditAttendance/getmonthsList",
            data: {
                emp: $("#employeeCombo").val(),
                month: $('#monthCombo').val(),

            },
            dataType: 'json',
            success: function(resp) {
                // var resp = $.parseJSON(resp);
                console.log(resp);
                //$('#editpunches').datagrid('deleteRow', getRowIndex(target));
                if (resp.success == true) {

                    var firstRow = $('.table-balances').find('tr:first');
                    var second = $('.table-balances').find('tr:last');

                    firstRow.html('');
                    second.html('');

                    firstRow.append('<th>Calendar Days</th>');
                    firstRow.append('<th>Present Days</th>');
                    firstRow.append('<th>Week OFF</th>');
                    firstRow.append('<th>Holidays</th>');
                    firstRow.append('<th>D/D</th>');
                    // firstRow.append('<th>LOP</th>');

                    second.append('<th>' + resp.numberdays + '</th>');
                    second.append('<th>' + resp.presentDays + '</th>');
                    second.append('<th>' + resp.weeks + '</th>');
                    second.append('<th>' + resp.holiday + '</th>');
                    // second.append('<th>' + resp.lopdays + '</th>');
                    second.append('<th>' + resp.dd + '</th>');

                    // $("#days").html(resp.numberdays);
                    // $("#present").html(resp.presentDays);
                    // $("#weekoff").html(resp.weeks);
                    // $("#holidays").html(resp.holiday);
                    // $("#lop").html(resp.lopdays);

                    // $("#dd").html(resp.dd);
                    $("#ps").html(0);

                    $(".shiftonelabel").html(resp.shoftNames1);
                    $(".shofttwolabel").html(resp.shoftNames2);


                    $(".leavabalnces").html('');
                    let leaves = resp.leaves;
                    let leave = resp.leave;
                    if (leaves.length > 0) {
                        let appendtexts = '<tr>';
                        for (var i = 0; i < leaves.length; i++) {
                            // append this to a table
                            appendtexts += '<th id="leave_' + leaves[i].Head + '" value="' + leaves[i].leaveBalance + '">' + leaves[i].Head + '</th>';
                        }
                        appendtexts += '</tr>';
                        appendtexts += '<tr>';
                        for (var i = 0; i < leaves.length; i++) {
                            // append this to a table
                            appendtexts += '<td>' + leaves[i].leaveBalance + '</td>';
                        }
                        appendtexts += '</tr>';
                        $(".leavabalnces").append(appendtexts);


                        for (var i = 0; i < leave.length; i++) {
                            firstRow.append('<th>' + leave[i].head + '</th>');
                        }



                        for (var i = 0; i < leave.length; i++) {
                            second.append('<th>' + leave[i].balance + '</th>');
                        }

                    } else {
                        $(".leavabalnces").html('No Leaves');
                    }
                    //  refresheditpunchgrid();
                } else {
                    $.messager.alert('Failed', "Error occured while removing record", 'info');
                }
            }
        });

    }

    function changeStatusAd(_this) {
        //console.log(row);
        $.ajax({
            type: "POST",
            url: livesite + "EditAttendance/chnagestatusadditonal",
            data: {
                device_attandance_seq: $(_this).attr('id'),
                status: $(_this).val(),

            },
            dataType: 'json',
            success: function(resp) {
                //var resp = $.parseJSON(resp);
                //$('#editpunches').datagrid('deleteRow', getRowIndex(target));
                if (resp.success == true) {
                    $.messager.show('Success', "Attandence removed successfully", 'info');
                    // refresheditpunchgrid();
                } else {
                    $.messager.alert('Failed', "Error occured while removing record", 'info');
                }
            }
        });

    }

    function extract(data, where) {
        for (var key in data) {
            where[key] = data[key];
        }
    }

    function updateselecteditem() {
        // get selected rows of grid
        var rows = $('#editpunches').datagrid('getSelections');

        console.log(rows);

        var ids = [];

        if (rows.length == 0) {
            $.messager.alert('Error', "Please select atleast one record", 'info');
            return false;
        }

        for (let index = 0; index < rows.length; index++) {
            const element = rows[index];
            ids.push(element.att_date);
        }

        $.ajax({
            type: "POST",
            url: livesite + "EditAttendance/bulkipdatestatus",
            data: {
                device_attandance_seq: ids,
                status: $("#shift1combo").val(),
                adstatus: $("#sift2combo").val(),
                emp: $("#employeeCombo").val(),
            },
            dataType: 'json',
            success: function(resp) {
                //var resp = $.parseJSON(resp);
                //$('#editpunches').datagrid('deleteRow', getRowIndex(target));
                if (resp.success == true) {
                    $.messager.show('Success', "Attandence removed successfully", 'info');
                    //refresheditpunchgrid();
                } else {
                    $.messager.alert('Failed', "Error occured while removing record", 'info');
                }
            }
        });

    }

    function updateStatuses2(_this) {
        // alert($(_this).attr("value"));
        var newstatuses = $(_this).attr("value");
        var statusType = $(_this).attr("type");
        var currentStatuses = $(_this).parents(".optionselects").parent().find('strong').html();
        currentStatuses = currentStatuses.trim();
        var statusmain = $(_this).attr("statusmain");

        let newStatus = currentStatuses;
        if (statusType == 'first') {
            var secondhalf = currentStatuses.split('/')[1];
            newStatus = newstatuses + '/' + (secondhalf ? secondhalf : 'A');

        }

        if (statusType == 'second') {
            var fiirsthalf = currentStatuses.split('/')[0];

            console.log("fiirsthalf", fiirsthalf);

            newStatus = (fiirsthalf ? fiirsthalf : 'A') + '/' + newstatuses;

        }

        if (statusType == 'full') {
            newStatus = newstatuses;

        }

        $.ajax({
            type: "POST",
            url: livesite + "EditAttendance/chnagestatusadditonal",
            data: {
                device_attandance_seq: $(_this).attr('id'),
                status: newStatus,
                statusType: statusType,
                newstatuses: newstatuses,
                currentStatuses: currentStatuses,
                statusmain: statusmain
            },
            dataType: 'json',
            success: function(resp) {
                //var resp = $.parseJSON(resp);
                //$('#editpunches').datagrid('deleteRow', getRowIndex(target));
                if (resp.success == true) {
                    $.messager.show('Success', "Attandence removed successfully", 'info');
                    // refresheditpunchgrid();
                } else {
                    $.messager.alert('Failed', "Error occured while removing record", 'info');
                }
            }
        });

        console.log((newStatus));
        $(_this).parents(".optionselects").parent().find('strong').html(newStatus);
        $(_this).parents(".optionselects").css("display", "none");
    }

    function updateStatuses(_this) {
        // alert($(_this).attr("value"));
        var newstatuses = $(_this).attr("value");
        var statusType = $(_this).attr("type");
        var currentStatuses = $(_this).parents(".optionselects").parent().find('strong').html();

        if (!['P', 'P/P', 'P/A', 'A/P', 'A', 'N/A', 'WO', 'HO', '/WO'].includes(newstatuses)) {
            // your code here
            console.log("valid blacndes", $('#leave_' + newstatuses).attr('value'));
            if (statusType == 'full') {
                console.log("full", parseInt($('#leave_' + newstatuses).attr('value')));
                if (parseFloat($('#leave_' + newstatuses).attr('value')) < 1) {
                    alert("No Leave Balance Available");
                    return false;
                }

            } else {
                if (parseFloat($('#leave_' + newstatuses).attr('value')) < 0.5) {
                    alert("No Leave Balance Available");
                    return false;
                }

            }
        }

        let newStatus = currentStatuses;
        if (statusType == 'first') {
            var secondhalf = currentStatuses.split('/')[1];
            if (secondhalf) {
                secondhalf = secondhalf.trim();

            } else {
                secondhalf = 'A';
            }
            newStatus = newstatuses + '/' + (secondhalf ? secondhalf : currentStatuses);

        }

        if (statusType == 'second') {
            var fiirsthalf = currentStatuses.split('/')[0];
            fiirsthalf = fiirsthalf.trim();

            newStatus = (fiirsthalf ? fiirsthalf : currentStatuses) + '/' + newstatuses;

        }

        if (statusType == 'full') {
            newStatus = newstatuses;

        }

        $.ajax({
            type: "POST",
            url: livesite + "EditAttendance/chnagestatus",
            data: {
                device_attandance_seq: $(_this).attr('id'),
                status: newStatus,
                statusType: statusType,
                newstatuses: newstatuses,
                currentStatuses: currentStatuses
            },
            dataType: 'json',
            success: function(resp) {
                //var resp = $.parseJSON(resp);
                //$('#editpunches').datagrid('deleteRow', getRowIndex(target));
                if (resp.success == true) {
                    $.messager.show('Success', "Attandence removed successfully", 'info');
                    // refresheditpunchgrid();
                    getMonthDatas();
                } else {
                    $.messager.alert('Failed', "Error occured while removing record", 'info');
                }
            }
        });

        console.log((newStatus));
        $(_this).parents(".optionselects").parent().find('strong').html(newStatus);
        $(_this).parents(".optionselects").css("display", "none");
    }

    function showEditOnPopupOption(_s, s) {
        $(_s).parent().find('.optionselects').css("display", "block");

    }
    //Ends

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
     url: livesite + "EditAttendance/remove",
     data: {
     device_attandance_seq: row.device_attandance_seq
     
     },
     dataType: 'json',
     success: function (resp) {
     //var resp = $.parseJSON(resp);
     //$('#editpunches').datagrid('deleteRow', getRowIndex(target));
     if (resp.success == true) {
     $.messager.show('Success', "Attandence removed successfully", 'info');
    // refreshgrid();
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
     url: livesite + "EditAttendance/remove",
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
    // refreshgrid();
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
     url: livesite + "EditAttendance/savepunch",
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
    // refreshgrid();
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
     showModalForm(livesite + 'EditAttendance/form/' + emp);
     } else {
     alert("Please select an employee")
     }
     }*/
</script>