<style>
    .form-horizontal .control-label {
        text-align: left;
        /*padding-left: 76px;*/
    }
</style>
<div class="modal-dialog">
    <div class="modal-content">
        <div class="modal-header" style="background: #00659f;color: white">
            <h4 class="modal-title">Details</h4>
        </div>

        <div class="modal-body">
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
                                <input type="hidden" id="site_trans_fkey" value="<?php echo isset($site_detailss['0']['site_transactions']['site_transactions_pkey']) ? $site_detailss['0']['site_transactions']['site_transactions_pkey'] : '0'; ?>">
                            </div>

                            <?php if ($att_date != "" && $emp_id != "") { ?>
                                <div id="tb" style="padding:5px;height:auto">
                                    <div>

                                        <span style="margin-right: 10px">
                                            Include Inactive&nbsp;&nbsp;<input type="checkbox" id="includeinactive" name="includeinactive" onchange="refreshgrid('includeinactive');">
                                        </span>

                                    </div>
                                    <br />
                                </div>

                                <input type="hidden" id="hid_att_date" value="<?php echo $att_date; ?>" />
                                <input type="hidden" id="hid_att_in_time" value="<?php echo $att_in_time; ?>" />
                                <input type="hidden" id="hid_att_out_time" value="<?php echo $att_out_time; ?>" />
                                <input type="hidden" id="hid_empid" value="<?php echo $emp_id; ?>" />
                                <table class="easyui-datagrid" id="editpunchesbydate" class="table table-bordered table-hover"></table>
                            <?php } else { ?>
                                <center>No missing punches found!</center>
                            <?php } ?>
                        </div>
                    </div>
                </div>
            </form>
        </div>
        <div class="modal-footer">
            <button class="btn btn-danger pull-right" onclick="$('#modalForm').modal('hide');">Close</button>
        </div>
    </div>
</div>

<?php if ($att_date != "" && $emp_id != "") { ?>
    <script type="text/javascript">
        function refreshgrid(source) {
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
        $(document).ready(function() {
            refresheditpunchgrid();


            $('#btn-new').linkbutton({
                iconCls: 'icon-add'
            });

            var empid = $('#hid_empid').val();
            var att_date = $('#hid_att_date').val();
            var att_in_time = $('#hid_att_in_time').val();
            var att_out_time = $('#hid_att_out_time').val();


            $('#editpunchesbydate').datagrid({
                queryParams: {
                    empid: empid,
                    att_date: att_date,
                    att_in_time: att_in_time,
                    att_out_time: att_out_time
                },
                width: '550px',
                //height: '600px',
                url: livesite + "Regularisation/listpunchesbydate/" + '<?= ($shownewbutton) ? 'yes' : 'yes' ?>',
                pagination: true,
                singleSelect: true,
                iconCls: 'icon-edit',
                pageSize: 20,
                fitColumns: true,
                pageList: [2, 5, 10, 20, 50, 100],
                rowStyler: function(index, row) {
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
                onBeforeEdit: function(index, row) {

                    row.editing = true;
                    updateActions(index);

                },
                onAfterEdit: function(index, row) {

                    row.editing = false;
                    updateActions(index);
                },
                onCancelEdit: function(index, row) {
                    row.editing = false;
                    updateActions(index);
                },
                columns: [
                    [{
                            field: 'LOGDATE',
                            title: 'Log Time',
                            width: "<?= ($shownewbutton == 'no') ? '25' : '22' ?>%",
                            formatter: function(val, row) {
                                if (row.LOGTIME) {
                                    return val + " " + row.LOGTIME;
                                } else {
                                    return val;
                                }
                            },
                        },
                        {
                            field: 'C1',
                            title: 'Direction',
                            width: "<?= ($shownewbutton == 'no') ? '15' : '10' ?>%",
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
                        <?php
                        if ($shownewbutton == 'yes') {
                        ?> {
                                field: 'status',
                                title: 'Status',
                                width: "<?= ($shownewbutton == 'no') ? '10' : '10' ?>%",
                                align: 'center',
                                formatter: function(value, row, index) {
                                    if (value == "1" || value == "Y") {
                                        return 'Active ';
                                    } else {
                                        return 'Inactive';
                                    }
                                },
                                editor: {
                                    type: 'checkbox',
                                    id: 'check',
                                    options: {
                                        on: '1',
                                        off: '0'
                                    }
                                }
                            },
                        <?php
                        }
                        ?> {
                            field: 'C3',
                            title: 'Remarks',
                            width: "<?= ($shownewbutton == 'no') ? '40' : '20' ?>%",
                        },
                        {
                            field: 'approved',
                            title: 'Reg. Status',
                            width: "<?= ($shownewbutton == 'no') ? '25' : '18' ?>%",
                            formatter: function(val, row) {
                                if (val == 'A') {
                                    return 'Approved';
                                } else if (val == 'R') {
                                    return 'Rejected';
                                } else {
                                    if (row.attendance_type == "existing") {
                                        return 'Approved';
                                    } else {
                                        return 'Pending';
                                    }
                                }

                            }
                        },

                        <?php
                        if ($shownewbutton == 'no') {
                        ?> //  {
                            //         field: 'action',
                            //         title: 'Action',
                            //         width: "<?= ($shownewbutton == 'no') ? '30' : '30' ?>%",
                            //         align: 'center',
                            //         formatter: function(value, row, index) {
                            //             if (row.approved == 'P') {
                            //                 var s = '<a href="#" onclick="approveAttendance(this)">Approve</a> ';
                            //                 var c = '<a href="#" onclick="rejectAttendance(this)">Reject</a>';
                            //                 return s + c;
                            //             }
                            //         }
                            //     },
                        <?php
                        } else {
                        ?> {
                                field: 'action',
                                title: 'Action',
                                width: "20%",
                                align: 'center',
                                formatter: function(value, row, index) {
                                    if (row.editing) {
                                        var s = '<a href="#" onclick="updateStatus(this)">Save</a> ';
                                        var c = '<a href="#" onclick="cancelrow(this)">Cancel</a>';
                                        return s + c;
                                    } else {
                                        if (row.approved == 'P') {
                                            var e = '<a href="#" onclick="editrow(this)">Edit</a> ';
                                            return e;
                                        } else {
                                            return '';
                                        }
                                    }
                                }
                            }
                        <?php
                        }
                        ?>
                    ]
                ]
            });

            //$('#editpunchesbydate').datagrid('load');
        });

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
                    url: livesite + "Regularisation/remove",
                    data: {
                        device_attandance_seq: row.device_attandance_seq
                    },
                    dataType: 'json',
                    success: function(resp) {
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

                .on('click', function(e) {
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
        };

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

        function approveAttendance(target, row, value) {
            var rows = $('#editpunchesbydate').datagrid('getRows')
            var row = rows[getRowIndex(target)];
            var empid = $('#hid_empid').val();
            var remarks = $('#approval_remarks').val();
            if (!remarks) {
                alert("Please enter remarks to Approve/Reject");
                return false;
            }
            // alert(empid);
            $('#editpunchesbydate').datagrid('endEdit', getRowIndex(target));
            // var crow = rows[getRowIndex(getChanged)];

            $.ajax({
                type: "POST",
                url: livesite + "Regularisation/bulkupdate",
                data: {
                    id: [row.id],
                    approved: 'A',
                    remarks: remarks,
                },
                dataType: 'json',
                success: function(resp) {
                    if (resp.success) {
                        refreshgrid();
                    } else {
                        alert(resp.msg);
                    }
                },
            });

        }

        function updateStatus(target, row, value) {
            var rows = $('#editpunchesbydate').datagrid('getRows')
            var row = rows[getRowIndex(target)];
            var empid = $('#hid_empid').val();
            $('#editpunchesbydate').datagrid('endEdit', getRowIndex(target));

            $.ajax({
                type: "POST",
                url: livesite + "Regularisation/updateStatus",
                data: {
                    id: row.id,
                    status: row.status,
                    C1: row.C1
                },
                dataType: 'json',
                success: function(resp) {
                    refreshgrid();
                },
            });
        }

        function rejectAttendance(target, row, value) {
            var rows = $('#editpunchesbydate').datagrid('getRows')
            var row = rows[getRowIndex(target)];
            var empid = $('#hid_empid').val();
            var remarks = $('#approval_remarks').val();
            if (!remarks) {
                alert("Please enter remarks to Approve/Reject");
                return false;
            }
            // alert(empid);
            $('#editpunchesbydate').datagrid('endEdit', getRowIndex(target));
            // var crow = rows[getRowIndex(getChanged)];

            $.ajax({
                type: "POST",
                url: livesite + "Regularisation/bulkupdate",
                data: {
                    id: [row.id],
                    approved: 'R',
                    remarks: remarks,
                },
                dataType: 'json',
                success: function(resp) {
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
                showSmallModalForm(livesite + 'Regularisation/form/' + emp + '/' + att_date + '/' + site_trans_fkey);
            } else {
                alert("Please select an employee")
            }
        }
    </script>
<?php } ?>