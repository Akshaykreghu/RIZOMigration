<style>
    .form-horizontal .control-label {
        text-align: left;
        /*padding-left: 76px;*/
    }
    .datagrid-row-selected {
    background-color: #3399FF !important; /* blue highlight */
    color: white !important;               /* text color */
}

</style>
<div class="modal-dialog">
    <div class="modal-content">
        <div class="modal-header" style="background: #1e516e;color: white;display:flex;">
            <h4 class="modal-title">Edit Punches</h4>
             
    <button type="button" 
            class="close" 
            id="closeModalX" 
            style="background:none;border:none;font-size:24px;color:white;margin-left:auto;">
        &times;
    </button>  
        </div>

        <div class="modal-body" style="padding:15px 8px;">
            <form class="form-horizontal">
                <div class="modal-body">
                    <div class="form-group">
                        <div class="col-md-12">
                            <div>
                                <?php if ($site_detailss) { ?>
                                    <div class="box box-body">
                                        <b><span><?php echo $site_detailss['0']['site']['site_id']; ?></span> - <span><?php echo $site_detailss['0']['site']['site_name']; ?></span> - <span><?php echo $site_detailss['0']['designation']['desig_name']; ?></span></b>
                                    </div>
                                <?php } ?>
                                <input type="hidden" id="site_trans_fkey" value="<?php echo isset($site_detailss['0']['site_transactions']['site_transactions_pkey']) ? $site_detailss['0']['site_transactions']['site_transactions_pkey'] : '0'; ?>" >
                            </div>

                            <?php if ($att_date != "" && $emp_id != "") { ?>
                                <div id="tb" style="padding:5px 5px 8px 5px; display:flex; justify-content:space-between; align-items:center; gap:10px; flex-wrap:wrap;">
    <!-- Left: New button -->
    <?php  if ($user_group != 2 && $shownewbutton == 'yes') { ?>
        <button 
            id="btn-new" 
            type="button" 
            onclick="insert()"
            style="
                background-color: #fff !important;
                border: 1px solid #e0e0e0 !important;
                color: #555 !important;
                border-radius: 8px !important;
                font-weight: 600 !important;
                font-size: 13px;
                padding: 5px 10px;
                display: flex;
                align-items: center;
                gap: 6px;
                transition: all 0.2s ease;
                cursor: pointer;
            "
        >
            <i class="fa fa-plus" style="color:#1e516e;"></i> New
        </button>
    <?php } ?>

    <!-- Middle: Include Inactive -->
    <span style="display:flex; align-items:center; gap:5px;">
        Include Inactive
        <input type="checkbox" id="includeinactive" name="includeinactive" onchange="refreshgrid('includeinactive');">
    </span>

    <!-- Right: Pass In/Out To -->
    <div style="margin-left:auto;">
         <?php if ($user_group !=2){ ?>
            <label for="prevNextDates">Pass In/Out To</label>
        <select id="prevNextDates">
            <option value="">-- Select --</option>
        </select>
       <?php } ?>
    </div>
</div>


                                <input type="hidden" id="hid_att_date" value="<?php echo $att_date; ?>" />
                                <input type="hidden" id="hid_att_in_time" value="<?php echo $att_in_time; ?>" />
                                <input type="hidden" id="hid_att_out_time" value="<?php echo $att_out_time; ?>" />
                                <input type="hidden" id="hid_empid" value="<?php echo $emp_id; ?>" />
                                <table class="easyui-datagrid" id="editpunchesbydate" class="table table-bordered table-hover" style="overflow:auto;"></table>
                            <?php } else { ?>
                                <center>No missing punches found!</center>
                            <?php } ?>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<?php if ($att_date != "" && $emp_id != "") { ?>
    <script type="text/javascript">
        function refreshgrid(source)
        {
            var empid = $('#hid_empid').val();
            var att_date = $('#hid_att_date').val();
            var att_in_time = $('#hid_att_in_time').val();
            var att_out_time = $('#hid_att_out_time').val();
            var includeinactive = $('#includeinactive').is(":checked") ? "Y" : "N";
            $('#editpunchesbydate').datagrid('load', {
                empid: empid,
                att_date: att_date,
                includeinactive: includeinactive
            })

            if (source != "includeinactive") {
                refresheditpunchgrid();
            }
        }
    </script>
    <script>

        $(document).ready(function () {
            // refresheditpunchgrid();
            
           
//             $('#modalForm').on('shown.bs.modal', function () {
                
//     $('#shift').select2({
//         width: '100%', // Ensures it uses full width
//         dropdownParent: $('#modalForm') // Prevents dropdown cutoff
//     });
// });

            
      

            // $('#btn-new').linkbutton({
            //     iconCls: 'icon-add'
            // });

            var empid = $('#hid_empid').val();
            var att_date = $('#hid_att_date').val();
            var att_in_time = $('#hid_att_in_time').val();
            var att_out_time = $('#hid_att_out_time').val();
            var user_group = <?php echo (int)$user_group; ?>;
            
         



            $('#editpunchesbydate').datagrid({
                queryParams: {
                    empid: empid,
                    att_date: att_date,
                    att_in_time: att_in_time,
                    att_out_time: att_out_time
                },
                width: '550px',
                //height: '600px',
                url: livesite + "EmployeeRegister/listpunchesbydate",
                pagination: true,
                rownumbers: true,
                singleSelect: true,
                iconCls: 'icon-edit',
                pageSize: 20,
                fitColumns: false,
                pageList: [2, 5, 10, 20, 50, 100],
                rowStyler: function (index, row) {
                    var style = "";
                    if (row.status == 'N') {
                        style += 'background-color:rgba(214, 110, 13, 0.92);color:#FFFFFF';
                    } else {
                        if (row.C1 == 'out' || row.C1 == 'OUT') {
                            style += 'background-color:#A9F5A9;';
                        } else if (row.C1 == 'in' || row.C1 == 'IN') {
                            style += 'background-color:#FAAC58;';
                        }
                    }
                    return style;
                },
                onBeforeEdit: function (index, row) {

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
                            width: "25%"
                        },
                        {
                            field: 'C1',
                            title: 'Direction',
                            width: "15%",
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
                            field: 'SHIFTDATE',
                            title: 'Shift Date',
                            width:"15%"
                        },
                        {
                            field: 'status',
                            title: 'Status',
                            width: "10%",
                            formatter: function (val, row) {
                                if (val == 'Y') {
                                    return  '<a href="#" class="check(this)" onclick = "get_clicked();">Active</a> ';
                                } else
                                {
                                    return 'Inactive';
                                }

                            },
                            editor: {
                                type: 'checkbox',
                                id: 'check',
                                options: {on: 'Y', off: 'N'}

                            }

                        }
                        ,
                        {
                            field: 'C3',
                            title: 'Location',
                            width: "13%",
                        },
                        {
                            field: 'action',
                            title: 'Action',
                            width: "27%",
                            align: 'left',
                            formatter: function (value, row, index) {
                                if (row.editing) {
                                    var s = '<a href="#" onclick="saverow(this)">Save</a> ';
                                    var c = '<a href="#" onclick="cancelrow(this)">Cancel</a>';
                                    return s + c;
                                } 
                                else {
                                    // ❌ If user_group = 2 → disable Edit
                                    if (user_group == 2) {
                                        return '<span style="color:gray;cursor:not-allowed;">Edit</span>';
                                    }

                                    // ✅ Normal users
                                    var e = '<a href="#" onclick="editrow(this)">Edit</a> ';
                                    return e;
                                }
                            }
                        }
                    ]
                ]
            });

            //$('#editpunchesbydate').datagrid('load');
        });

showPrevNextDates();
function showPrevNextDates() {
    
    // Get base attendance date from hidden field (not from grid log time)
    var attDateStr = $('#hid_att_date').val();
    if (!attDateStr) return;

    var baseDate = new Date(attDateStr);

    // Calculate previous and next dates
    var prevDate = new Date(baseDate);
    prevDate.setDate(baseDate.getDate() - 1);

    var nextDate = new Date(baseDate);
    nextDate.setDate(baseDate.getDate() + 1);

    // Format date as yyyy-mm-dd
    const fmt = d => d.toISOString().split('T')[0];

    // Populate dropdown
    $('#prevNextDates').empty().append(`
        <option value="">-- Select --</option>
        <option value="${fmt(prevDate)}">${fmt(prevDate)}</option>
        <option value="${fmt(nextDate)}">${fmt(nextDate)}</option>
    `);
}


$('#prevNextDates').on('change', function () {
    const selectedDate = $(this).val();
    var empid = $('#hid_empid').val();
    if (!selectedDate) return;

    const selectedRow = $('#editpunchesbydate').datagrid('getSelected');
    if (!selectedRow) {
        alert("Please select a punch first.");
        return;
    }

    if (!confirm("Are you sure you want to change the shift date to " + selectedDate + "?")) return;

    updateShiftDate(selectedRow.device_attandance_seq, selectedDate, empid, selectedRow.SHIFTDATE);

    // ✅ Reset dropdown after selection
    $('#prevNextDates').val(""); 
});

$(document).on("click", "#closeModalX", function () {
    $("#modalForm").modal("hide");
});


function updateShiftDate(device_attandance_seq, newDate,empid,old_shiftdate) {
    
    $.ajax({
        type: "POST",
        url: livesite + "EmployeeRegister/updateShiftDate",
        data: {
            device_attandance_seq: device_attandance_seq,
            shiftdate: newDate,
            empid : empid,
            old_shiftdate:old_shiftdate
        },
        dataType: "json",
        success: function (resp) {
            if (resp.success) {
                $.notify("Shift date updated successfully", { type: 'success' });
                refreshgrid();
            } else {
                $.notify("Failed to update shift date", { type: 'danger' });
            }
        },
        error: function () {
            $.notify("Error communicating with server", { type: 'danger' });
        }
    });
}


        function updateActions(index) {
            $('#editpunchesbydate').datagrid('updateRow', {
                index: index,
                row: {}
            });
        }

        function getRowIndex(target) {
            var tr = $(target).closest('tr.datagrid-row');
            return parseInt(tr.attr('datagrid-row-index'));
        }
        function editrow(target) {
            $('#editpunchesbydate').datagrid('beginEdit', getRowIndex(target));
        }
        function deleterow(target) {
            var r = confirm("Are you sure to delete this punch?");
            if (r == true) {
                var rows = $('#editpunchesbydate').datagrid('getRows')
                var row = rows[getRowIndex(target)];
                $.ajax({
                    type: "POST",
                    url: livesite + "EmployeeRegister/remove",
                    data: {
                        device_attandance_seq: row.device_attandance_seq
                    },
                    dataType: 'json',
                    success: function (resp) {
                        if (resp.success == true) {
                            $.notify("Attandence removed successfully", {
                                type: 'success',
                                allow_dismiss: false
                            });
                            refreshgrid();
                        } else {
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
        function get_clicked() {

            $('input[type=checkbox]')
                    .setCheckbox('unchecked') // set initial state

                    .on('click', function (e) {
                        var $checkbox = $(this);
                        switch ($checkbox.data('state')) {

                            case 1: // indeterminate
                                $checkbox.setCheckbox('checked');
                                console.log('Previous state: unchecked | New state: checked');
                                break;
                            case 2: // checked
                                $checkbox.setCheckbox('unchecked');
                                console.log('Previous state: checked | New state: unchecked');
                                break;
                        }

                    });
        }
        ;

        // $.get_clicked.setCheckbox = function(state) {
        //   return $(this).each(function() {
        //     var $checkbox = $(this);
        //     if (state=='unchecked') {
        //       $checkbox
        //         .data('state',0)
        //         .prop('indeterminate',false)
        //         .prop('checked',false);
        //     }
        //        else if (state=='checked') {
        //       $checkbox
        //         .data('state',2)
        //         .prop('indeterminate',false)
        //         .prop('checked',true);
        //     }
        //   });
        //   alert(checkbox);
        // };

        function saverow(target, row, value) {
            var rows = $('#editpunchesbydate').datagrid('getRows')
            var checkrow = $('#check').attr('checked') && $(this).prev().attr('checked')
            // alert(checkrow);

            var row = rows[getRowIndex(target)];
            var empid = $('#hid_empid').val();
            // alert(empid);
            $('#editpunchesbydate').datagrid('endEdit', getRowIndex(target));
            // var crow = rows[getRowIndex(getChanged)];


            $.ajax({
                type: "POST",
                url: livesite + "EmployeeRegister/savepunch/" + empid,
                data: {
                    C1: row.C1,
                    DEVICEID: row.DEVICEID,
                    // DEVICELOGID: row.DEVICELOGID,
                    branch_code: row.branch_code,
                    device_attandance_seq: row.device_attandance_seq,
                    status: row.status,
                    emp_id: empid,
                    LOGDATE: row.LOGDATE,
                    // rowsbefore: rowsbefore

                },
                dataType: 'json',
                success: function (resp) {
                    refreshgrid();
                },
            });

        }
        function cancelrow(target) {
            $('#editpunchesbydate').datagrid('cancelEdit', getRowIndex(target));
        }
        function insert() {

            var emp = $('#hid_empid').val();
            var att_date = $('#hid_att_date').val();
            var site_trans_fkey = $('#site_trans_fkey').val();
            console.log(emp);
            console.log(att_date);
            console.log(site_trans_fkey);
            if (emp) {
                $('#empid').val(emp);
                //Add form
                showSmallModalForm(livesite + 'EmployeeRegister/form/' + emp + '/' + att_date + '/' + site_trans_fkey);
            } else {
                alert("Please select an employee")
            }
        }

//         $('#save-shift-btn').click(function () {
//     var shift_id = $('#shift').val();
//     var emp_id = $('#hid_empid').val();
//     var att_date = $('#hid_att_date').val();

//     if (!shift_id) {
//         alert('Please select a shift before saving.');
//         return;
//     }

//     if (!confirm("Do you want to change the shift up to the last date?")) {
//         return; // If user says no, stop here
//     }

//    let update_till_last = "no";
// if (confirm("Do you want to reiterate attendance till last date?")) {
//     update_till_last = "yes";
// }

// $.ajax({
//     type: "POST",
//     url: livesite + "EmployeeRegister/saveshift",
//     data: {
//         emp_id: emp_id,
//         att_date: att_date,
//         shift_id: shift_id,
//         update_till_last: update_till_last
//     },
//     dataType: "json",
//     success: function (resp) {
//         if (resp.success) {
//             $.notify("Shift updated successfully", { type: 'success' });
//             refreshgrid();
//         } else {
//             $.notify("Failed to update shift", { type: 'danger' });
//         }
//     },
//     error: function () {
//         $.notify("Error communicating with server", { type: 'danger' });
//     }
// });

// });


    </script>
<?php } ?>