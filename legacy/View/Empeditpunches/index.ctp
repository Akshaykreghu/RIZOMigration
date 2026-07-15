
<style>
    #editpunchform table tr td{
        padding: 5px;
        width: 100%;
    }
</style>
<!--script src="<?php echo $this->webroot; ?>plugins/easyui/jquery.easyui.min.js"></script-->
<section class="content-header">
    <h1 class="text-primary-18"> Edit Punches </h1>

</section>

<!-- Main content -->
<section class="content">
    <div class="row">
        <div class="col-md-12">
            <div class="box ">
                <!--
                <div class="box-header with-border">
                <h3 class="box-title">Branch</h3>
                <div class="box-tools pull-right">
                <button class="btn btn-box-tool" data-widget="collapse"><i class="fa fa-minus"></i></button>
                </div>
                </div>-->
                <!-- /.box-header -->
                <div class="box-body">
                    <table class="easyui-datagrid" id="editpunches" toolbar="#tb" class="table table-bordered table-hover">

                    </table>
                    <div id="tb" style="padding:5px;height:auto">

                        <div>
                            <span style="margin-right: 10px">  Employee: 


                                <input  id="employeeCombo"  style="width:150px;"
                                        ></span>
                            <span style="margin-right: 10px">   Month: 


                                <input  id="monthCombo"  style="width:100px;"
                                        >
                            </span>

                            <span style="margin-right: 10px"> Include Inactive <input type="checkbox" id="includeinactive"  name="includeinactive" onchange="refreshgrid()"/>
                            </span>
                            <a href="javascript:void(0)" class="easyui-linkbutton" iconCls="icon-add" onclick="insert()">New</a>
                            <a href="#" class="easyui-linkbutton" iconCls="icon-message">Send Memo</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
</section>
<script type="text/javascript">
    function refreshgrid()
    {
        var emp = $('#employeeCombo').combobox("getValue");
        var mnth = $('#monthCombo').combobox("getValue");
        var includeinactive = $('#includeinactive').is(":checked") ? "Y" : "N";
        
        $('#editpunches').datagrid('load', {
            emp: emp,
            month: mnth,
            includeinactive: includeinactive
        })
    }
</script>
<script>
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

    jQuery(document).ready(function () {

        $('#monthCombo').combobox({
            mode: 'remote',
            url: livesite + 'Empeditpunches/getmonths',
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
            url: livesite+'Empeditpunches/listempemployees',
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
        });
        $('#editpunches').datagrid({
            url: livesite + "Empeditpunches/listpunches",
            pagination: true,
            singleSelect: true,
            iconCls: 'icon-edit',
            fitColumns: true,
            pageList: [2, 5, 10, 50, 100],
            rowStyler: function (index, row) {
                var style = "";
                if (row.status == 'N') {
                    style += 'background-color:rgba(214, 110, 13, 0.92);color:#FFFFFF';
                } else {


                    if (row.C1 == 'out') {
                        style += 'background-color:#A9F5A9;';
                    }
                    else if (row.C1 == 'in') {
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
                        width: "20%"
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
                        field: 'status',
                        title: 'Status',
                        width: "5%",
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
                        width: "5%",
                        
                    },
                    {
                        field: 'action',
                        title: 'Action',
                        width: 70,
                        align: 'center',
                        formatter: function (value, row, index) {
                            //	console.log(row);
                            if (row.editing) {
                                var s = '<a href="#" onclick="saverow(this)">Save</a> ';
                                var c = '<a href="#" onclick="cancelrow(this)">Cancel</a>';
                                return s + c;
                            } else {
                                var e = '<a href="#" onclick="editrow(this)">Edit</a> ';
                                var d = '<a href="#" onclick="deleterow(this)">Delete</a>';
                                return e + d;
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
                    }
        });

    });
    function updateActions(index) {
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
        
        var r = confirm("Are you sure to delete this punch?");
        if (r == true) {
            var rows = $('#editpunches').datagrid('getRows')
            var row = rows[getRowIndex(target)];
            $.ajax({
                type: "POST",
                url: livesite + "Empeditpunches/remove",
                data: {
                    device_attandance_seq: row.device_attandance_seq
                },
                dataType: 'json',
                success: function (resp) {
                    //var resp = $.parseJSON(resp);
                    //$('#editpunches').datagrid('deleteRow', getRowIndex(target));
                    if (resp.success == true) {
                        //$.messager.show('Success', "Attandence removed successfully", 'info');
                        $.notify("Attandence removed successfully",{
                            type: 'success',
                            allow_dismiss: false
                        });
                        refreshgrid();
                    } else {
                        //$.messager.alert('Failed', "Error occured while removing record", 'info');
                        $.notify("Error occured while removing record",{
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
            url: livesite + "Empeditpunches/savepunch",
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
        var emp = $('#employeeCombo').combobox("getValue");
        if (emp) {
            $('#empid').val(emp);
            //$('#w').window('open');
            //Add form
            showModalForm(livesite+'Empeditpunches/form/'+emp);
        } else {
            alert("Please select an employee")
        }
    }

</script>
