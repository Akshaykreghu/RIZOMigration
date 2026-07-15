<style>

    .form-horizontal .control-label{
        text-align: left;


    }

</style>
<!-- Structure re-edited by *** ARUL P DAS *** on 7/12/2020 -->
 <div class="modal-content">
<form class="form-horizontal" id="editpunchform" method="post" action="<?php echo $this->webroot; ?>DailyOvertimeVerify/savenew" >
   


        <!-- Text input-->
        <?php if ($diff_half_count == 2) { ?>
            <div class="modal-header" style="background: #00659f;color: white">
                <h4 class="modal-title">Alert</h4>  
            </div>
            <div class="modal-body">
                <h4 style="text-align: center;">Already leave on 2 half</h4>
            </div>


        <?php } elseif ($count > 0) { ?>

            <!-- <?php ?> -->

            <div class="modal-header" style="background: #00659f;color: white">
                <h4 class="modal-title">Alert</h4>  
            </div>
            <div class="modal-body">
                <h4 style="text-align: center;"><?php echo $leave ?></h4>
            </div>

        <?php } else { ?>

            <div class="modal-header" style="background: #00659f;color: white">
                <h4 class="modal-title">Attendance</h4>  
            </div>
            <div class="modal-body">
                <?php if ($sessions == 1 || 2) { ?>
                    <?php
                    foreach ($h_leave as $key => $value) {
                        $l = $value;
                        ?>

                        <div class="form-group col-md-12">
                            <label class=" control-label">
                                <?php echo $l; ?>
                            </label>
                        </div>

                        <?php
                    }
                }
                ?>

                <input type="hidden" name="empid" id="empid" value="<?php echo $empid; ?>"  />
                <input type="hidden" name="site_t_fkey" id="site_t_fkey" value="<?php echo $site_t_fkey; ?>"  />
                
                    <div class="form-group col-md-12">
                        <label class="col-md-4 control-label" for="LOGDATE">Date <span class="star">*</span></label>  
                        <div class="col-md-1">:</div>
                        <div class="col-md-7">
                            <input id="LOGDATE" name="LOGDATE" placeholder="YYYY-MM-DD"  value="" type="text" class="form-control input-md" required="">
                        </div>
                    </div>

                    <div class="form-group col-md-12">
                        <label class="col-md-4 control-label" for="LOGTIME">Time <span class="star">*</span></label>  
                        <div class="col-md-1">:</div>
                        <div class="col-md-7">
                            <input id="LOGTIME" name="LOGTIME" placeholder="HH:MM:SS" value="" type="text" class="form-control input-md" required="">
                        </div>
                    </div>

                    <!-- Text input-->
                    <div class="form-group col-md-12">
                        <label class="col-md-4 control-label" for="C1">Direction <span class="star">*</span></label>  
                        <div class="col-md-1">:</div>
                        <div class="col-md-7">
                            <select name="C1" class="form-control">
                                <option value="in">In</option>
                                <option value="out">Out</option>
                            </select>
                        </div>
                    </div>

                    <!-- Text input-->
                    <div class="form-group col-md-12">
                        <label class="col-md-4 control-label" for="C3">Remarks <span class="star">*</span></label>  
                        <div class="col-md-1">:</div>
                        <div class="col-md-7">
                            <input name="C3"  value="" type="text" placeholder="Remarks" class="form-control input-md" required="">
                            <span> You must enter a valid remarks. </span>
                        </div>
                    </div>
                </div>
            <?php } ?>
  
        <div style="text-align: right;padding:15px;">
            <button type="button" class="btn btn-danger" data-dismiss="modal" onclick="clearForm();">Close</button>
            <?php if ($count > 0 || $diff_half_count == 2) { ?>
                <button type="submit" class="hidden">Save</button>
            <?php } else { ?>
                <button type="submit" class="btn btn-primary">Save</button>
            <?php } ?>
        </div>
    <div id="punchesListContainer">
   
</div>
</form>
   </div> <!-- Closing of Modal Content -->
<script type="text/javascript">
function loadPunchesList(empid, month, branch) {
   // var empid = $('#empid').val();
   // var month = $('#LOGDATE').val();
   // var branch = $('#filterby_branch').val();
console.log("Loading punches with:", empid, month, branch);
    $('#dailyovertimediv').datagrid({
        url: livesite + "DailyOvertimeVerify/listpunches",
        method: 'POST',
        queryParams: {
            empid: empid,
            month: month,
            branch: branch
        },
        onLoadSuccess: function(data) {
            $.notify(data.message, {
                type: data.type,
                allow_dismiss: false
            });
        },
        pageList: [2, 5, 10, 20, 32, 50, 100],
        rowStyler: function(index, row) {
            var style = "";
            if (row.status == 'N') {
                style += 'background-color:rgba(214, 110, 13, 0.92);color:#FFFFFF';
            } else if (row.C1 === 'out') {
                style += 'background-color:#A9F5A9;';
            } else if (row.C1 === 'in') {
                style += 'background-color:#FAAC58;';
            }
            return style;
        },
        columns: [[
            {field: 'chek_encash_remove', title: '', width: "2%", checkbox: true},
            {field: 'first_name', title: 'Employee', width: "22%"},
            {field: 'att_in_time', title: 'Start', width: "16%"},
            {field: 'att_out_time', title: 'End', width: "16%"},
            {field: 'duration', title: 'Duration', width: "8%"},
            {
                field: 'status',
                title: 'Status',
                width: "8%",
                formatter: function(value, row) {
                    return `<strong style="color: ${row.status_color};">${row.leaves} ${value}</strong>`;
                }
            },
            {field: 'ot_duration', title: 'OT Min', width: "8%"},
            {
                field: 'set_duration',
                title: '<span style="color:green;">Set OT Min</span>',
                width: "10%",
                formatter: function(value, row) {
                    if (row.ot_duration > 0) {
                        return `<input type="number" name="setDuration${row.emp_detail_timeattandance_pkey}" 
                                id="setDuration${row.emp_detail_timeattandance_pkey}" 
                                style="color:black;width:100%;" value="${value}" min="0" 
                                oninput="this.value = this.value < 0 ? 0 : this.value" 
                                onchange="updateDuration('${window.btoa(JSON.stringify(row))}')">`;
                    }
                }
            },
            {
                field: 'action',
                title: 'Action',
                width: "8%",
                align: 'center',
                formatter: function(value, row) {
                    if (row.joining_date <= row.att_date) {
                        return `<a href="#" class="edit-button" onclick="showEditOnPopup('${window.btoa(JSON.stringify(row))}');">Edit</a>`;
                    }
                }
            },
            {
                field: 'remarks',
                title: '<span style="color:green;">Remarks</span>',
                width: "10%",
                formatter: function(value, row) {
                    if (row.ot_duration > 0) {
                        return `<input type="text" name="remark${row.emp_detail_timeattandance_pkey}" 
                                id="remark${row.emp_detail_timeattandance_pkey}" 
                                style="color:black;width:100%;" value="" 
                                onchange="setRemarks('${window.btoa(JSON.stringify(row))}')" autocomplete="off">`;
                    }
                    return '';
                }
            },
            {
                field: 'verify',
                title: '<span style="color:green;">Verify</span>',
                width: "7%",
                align: 'center',
                formatter: function(value, row) {
                    if ((row.att_in_time && row.att_out_time) || (row.status || '').trim().toUpperCase() === 'NA') {
                        return `<button class="btn btn-primary" onclick="verify('${window.btoa(JSON.stringify(row))}');">Verify</button>`;
                    }
                    return '';
                }
            }
        ]]
    });
}

    $(document).ready(function () {
        $('#LOGDATE').datepicker({
            format: 'yyyy-mm-dd',
            autoclose: true
        });
        $("#LOGDATE").inputmask("yyyy-mm-dd");

        var att_date = '<?php echo $att_date; ?>';
        var arrSelectedDate = att_date.split('-');
        if (arrSelectedDate.length == 3) {
            var selectedDate = new Date(arrSelectedDate[0], arrSelectedDate[1] - 1, arrSelectedDate[2]);
        } else {
            var selectedDate = new Date();
        }
        $("#LOGDATE").datepicker("setDate", selectedDate);
//edited by sinsiya on 03-10-2024
        $('#LOGTIME').timepicker({
            format: 'hh:mm:ss',
            showMeridian: false,
            showSeconds:true,
            autoclose: true
        });

        $('#editpunchform').parsley();
        var options = {
            success: function (responseText, statusText, xhr, $form) {
                var response = JSON.parse(responseText);
//                alert(response.success);
//                console.log(response);
// Add condition for checking leave exists and restrict adding punch in upcoming days -- Added By Nimisha 20/03/2019

                if (response.success == false) {
                    alert(response.msg);
                    clearForm();
                    closeSmallModalForm();
                    refreshgrid();
                    //		$.notify("Attandence With Same Data Already Exists..",{
//                    type: 'danger',
//                    allow_dismiss: false
//                });																	   
                }
                else {
                   // clearForm();
                    // alert(att_date + " = " + $("#filterby_branch").val());
                    $('#dailyovertimediv').datagrid('load', {
                        month: att_date,
                        branch: $("#filterby_branch").val(),
                    });
                    closeSmallModalForm();
                    //loadPunchesList();
                    loadPunchesList(
    $('#empid').val(),
    att_date,
    $('#filterby_branch').val()
);
                    refreshgrid();
 
                    $.notify("New Attandence Saved Successfully", {
                        type: 'success',
                        allow_dismiss: false
                    });
                }

                //$('#modalForm').modal('hide');

            }
        };

        // bind to the form's submit event 
        $('#editpunchform').submit(function () {
            $(this).ajaxSubmit(options);
            return false;
        });
    });
    function clearForm() {
        $('#empid').val("")
        $('#editpunchform').form('clear');
    }
</script>