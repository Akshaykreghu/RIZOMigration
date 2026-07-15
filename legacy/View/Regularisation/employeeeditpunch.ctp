<style>
    #editpunchform table tr td{
        padding: 5px;
        width: 100%;
    }
</style>
<section class="content-header">
    <h1 style="text-align:left; font-size: 3em;"> Edit Punches </h1>
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
                    
                    <div id="tb" style="padding:5px;height:auto">

                        <div>
                            
                            <input type="hidden" id="emp_fkey" value="<?php echo $emp_pkeys; ?>">
                            
                            <span style="margin-right: 10px">   Month: 
                                <!--input id="monthCombo"  style="width:100px;"-->
                                <select id="monthCombo" onchange="refresheditpunchgrid();" style="width: 100px;">
                                    <?php foreach ($arr_months as $month){ ?>
                                    <option value="<?php echo $month->id; ?>"><?php echo $month->text; ?></option>
                                    <?php } ?>
                                </select>
                            </span>
                            <button class="btn btn-primary" onclick="refresheditpunchgrid();">Process</button>

                            <!--span style="margin-right: 10px">
                                Include Inactive&nbsp;&nbsp;<input type="checkbox" id="includeinactive"  name="includeinactive" onchange="refreshgrid()">
                            </span-->
                            <!--a id="btn-new" href="javascript:void(0)" onclick="insert()">New</a-->
                            <!--a href="#" class="easyui-linkbutton" iconCls="icon-message">Send Memo</a-->
                            <button title="if you are finished missing in/out punches of the employee then you should use the
      Amendments button then only it will effect in attendance records" class="btn btn-primary pull-right" onclick="updatemem();">Update Amendments </button>
                        </div>
                        <br/>
                    </div>
                    <table class="easyui-datagrid" id="editpunches" class="table table-bordered table-hover">

                    </table>
                </div>
            </div>
        </div>
</section>
<script type="text/javascript">
    function refresheditpunchgrid()
    {
        //var emp = $('#employeeCombo').combobox("getValue");
        //var mnth = $('#monthCombo').combobox("getValue");
        var emp = $('#emp_fkey').val();
        var mnth = $('#monthCombo').val();
        //var includeinactive = $('#includeinactive').is(":checked") ? "Y" : "N";

        $('#editpunches').datagrid('load', {
            emp: emp,
            month: mnth,
            //includeinactive: includeinactive
        })
    }
</script>
<script>
    function updatemem()
    {
        var emp = $('#emp_fkey').val();
        var mnth = $('#monthCombo').val();
        
        if(emp){
		$.ajax({
            type: "POST",
            url: livesite + "Regularisation/Updateame",
            data: {
                emp:emp,month:mnth
            },
            success: function (resp) {
                 $.notify("Attandence Updates successfully", {
                        type: 'success',
                        allow_dismiss: false
                    });
                    refresheditpunchgrid();
            }
        });
    }
    else{
        alert("Please Select a Employee");
    }
    }
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

$(document).ready(function () {
    
    $('#monthCombo').val((new Date().getFullYear()) + '-' + (new Date().getMonth() + 1));

    $("#monthCombo").select2({
        placeholder: "Choose Month"
    });
    $("#employeeCombo").select2({
        placeholder: "Choose Employee"
    });
    
    /*$('#btn-new').linkbutton({
        iconCls: 'icon-add'
    });*/

    /*$('#monthCombo').combobox({
        mode: 'remote',
        url: livesite + 'Regularisation/getmonths',
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
			emp: '<?php echo isset($emp_id)?$emp_id:'0'; ?>',
            month: $('#monthCombo').val(),
		},
        height: '600px',
        url: livesite + "Regularisation/listpunches",
        pagination: true,
        singleSelect: true,
        iconCls: 'icon-edit',
        pageSize:32,
        fitColumns: true,
        pageList: [2, 5, 10, 20, 32, 50, 100],
        rowStyler: function (index, row) {
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
            [
                {
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
                /*{
                    field: 'min_bfr_on_dutty_cal_ot',
                    title: 'OT before on duty',
                    width: "15%",
                },
                {
                    field: 'min_aftr_off_dutty_cal_ot',
                    title: 'OT after off duty',
                    width: "15%",
                },
                {
                    field: 'ot_duration',
                    title: 'OT Duration',
                    width: "10%",
                },*/
                {
                    field: 'status',
                    title: 'Status',
                    width: "15%",
                    formatter: function (value, row, index) {
						//var e = "<div style=\"background-color: "+row.status_color+"; float: left; width: 100%; padding: 14% 0;\">"+value+"</div>";
						var e = "<strong style=\"color: "+row.status_color+";\">"+value+"</strong>";
						return e;
                    }
                },
                {
                    field: 'action',
                    title: 'Action',
                    width: "15%",
                    align: 'center',
                    formatter: function (value, row, index) {
						if(row.editable){
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
function showEditOnPopup(str_row){
	var row = JSON.parse(window.atob(str_row));
	extract(row, this);
	if (att_date && emp_id) {
        //Add form
        showModalForm(livesite + 'Regularisation/editpunch/' + att_date + '/' + emp_id + '/' + encodeURI(att_in_time) + '/' + encodeURI(att_out_time));
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
     url: livesite + "Regularisation/remove",
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
            url: livesite + "Regularisation/remove",
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
        url: livesite + "Regularisation/savepunch",
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
        showModalForm(livesite + 'Regularisation/form/' + emp);
    } else {
        alert("Please select an employee")
    }
}*/
</script>