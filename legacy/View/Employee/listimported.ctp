<style>
    .left-inner-addon {
        position: relative;
    }
    .left-inner-addon input {
        padding-left: 30px;    
    }
    .left-inner-addon i {
        position: absolute;
        padding: 10px 12px;
        pointer-events: none;
    }

    .right-inner-addon {
        position: relative;
    }

    td[field="avatar"] .datagrid-cell {
        width: 86px;
        height: 30px;
    }

    .right-inner-addon input {
        padding-right: 30px;    
    }
    .right-inner-addon i {
        position: absolute;
        right: 0px;
        padding: 10px 12px;
        pointer-events: none;
    }

    .form-horizontal .control-label{

        text-align: left;
        padding-left: 2px;
    }

    .resumebutton {
        font-weight: 600;
        padding-left: 40px;
    }

    .custom-file-upload {
        border: 1px solid #0d0c0c52;
        border-radius: 4px;
        display: inline-block;
        padding: 4px 12px;
        cursor: pointer;
        width: 100%;
        height: 30px;
        text-align: center;
    }
 /* <!-- edited by bindu 02-12-2025 --> */
     .heading {
        display: flex;
        flex-direction: row;
        align-items: end;
        justify-content: space-between;
        margin: 0 15px;
        padding: 15px 0 !important;
    }

    .home {
        background-color: #ffffffff;
        border-radius: 50px;
        padding: 2px 15px;
        color: #1e516e !important;
        margin-right: 15px;
        color: white;
        font-weight: 500;
        text-decoration: none;
        transition: all 0.3s ease;
        cursor: pointer;
        border: #1e516e 1px solid;
    }
     /* <!-- edited by bindu 02-12-2025 --> */
</style>
     <!-- /* edited by bindu 02-12-2025 */ -->
<section class="content-header heading">
      <!-- edited by athira on 03-07-2025 -->
    <h1 style="margin-left: 15px;" class="text-primary-18">Import Profile</h1>
    <!-- end --><div class="text-primary-16 home" style="display:flex; align-items:center; gap:10px; cursor:pointer;">
        <i class="fa" style="font-size:16px;">&#xf104;</i>
        Back
    </div>
  <!-- /* edited by bindu 02-12-2025 */ -->
</section>
<!-- Main content -->
<section class="content">
    <div class="col-md-12">

        <div class="box box-primary " style="margin-top: -21px;">
            <div class="box-body" style="    margin-top: -11px;">
                <br>
                <input type="hidden" id="rsndempid" value="0" name="resigned">
                <div class="col-md-7">
                    <span data-toggle="tooltip" data-placement="auto" title="Import User Data from MyProfile Application using Profile ID">
                        <button onclick="showImportProfileModal();" class="btn btn-primary"><li class="fa fa-upload"></li></button>
                    </span>
                </div>
                <div class="col-md-5 pull-right row">
                    <label class="col-md-3" for="filterby_branch">Branch</label>
                    <div class="col-md-1">:</div>
                    <div class="col-md-8">
                        <select id="filterby_branch" name="filterby_branch" class="form-control" onchange="filterEmployees(this);" >
                            <option value="">All</option>
                            <?php foreach ($arr_branches as $key => $value) { ?>                              
                                <option  value="<?php echo $value['branch_code']; ?>"><?php echo $value['branch_name']; ?></option>
                            <?php } ?>
                        </select>
                    </div>
                </div>
                <br><br>
                <table id="emptable" class="table table-bordered table-hover">

                </table>
            </div><!-- /.box-body -->
            <!--/.direct-chat -->
        </div><!-- /.col -->
    </div>
</div>

</section>
<div id="empSetupModalForm" class="modal fade">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <!-- Content will be loaded here from "remote.php" file -->
        </div>
    </div>
</div>
<script>
    
    function filterEmployees(obj) {
        var branch = $('#filterby_branch').val();
        var employee = $('#hid_filterby_employees').val();

        var designation = $('#filterby_Designation').val()

        $('#emptable').datagrid('load', {
            branch: branch,
            employee: employee,
            designation: designation,
            name: $('#rsndempid').val(),
        });

    }

    function showImportProfileModal() {
        showModalForm(livesite + 'Employee/importProfile');
    }

    function setwidth() {

        if ($('#checkrsgnd').length == 0) {
            // exists.
            $('.datagrid-toolbar').find('tr').append('<td>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<input type="checkbox" name"rsgnemp" id="checkrsgnd" onclick="showrsgnd();" value="0">Include Resigned </td>');

        }
    }
    function showrsgnd() {
        var branch = $('#filterby_branch').val();
        var employee = $('#filterby_employees').val();

        var designation = $('#filterby_Designation').val();
        if ($('#checkrsgnd').is(":checked"))
        {
            $('#rsndempid').val('1');

        }
        else {
            $('#rsndempid').val('0');

        }
        $('#emptable').datalist('load', {
            name: $('#rsndempid').val(),
            branch: branch,
            employee: employee,
            designation: designation,
        });
    }
    
    jQuery(document).ready(function () {
        $("#import_emp_branch").select2();
        $("#filterby_branch").select2();
        function filterAttendanceautocomplete(obj) {
            var branch = $('#filterby_branch').val();
            var employee = obj;

            var designation = $('#filterby_Designation').val()

            $('#emptable').datagrid('load', {
                branch: branch,
                employee: employee,
                designation: designation,
            });

        }

        var usersoptions = {
            url: function (phrase) {
                var branch = $('#filterby_branch').val();
                return livesite + "Employee/getautocompletions?username=" + phrase + "&branch=" + branch;
            },
            getValue: "emp_name",
            list: {
                onClickEvent: function () {
                    //var selectedItem = $('#filterby_employees').getSelectedItemData();
                    //var site_pkey = selectedItem.emp_pkey;
                    filterAttendanceautocomplete($('#hid_filterby_employees').val());
                },
                onKeyEnterEvent: function () {
                    filterAttendanceautocomplete($('#hid_filterby_employees').val());
                },
                onSelectItemEvent: function () {
                    var selectedItem = $('#filterby_employees').getSelectedItemData();
                    var site_pkey = selectedItem.emp_pkey;
                    $('#hid_filterby_employees').val(site_pkey);
                }
            }
        };

        $('#filterby_employees').easyAutocomplete(usersoptions);



        //  $('#filterby_employees').easyAutocomplete(getstages);
        $('#emptable').datagrid({
            url: livesite + "Employee/listemployeesProfileImporteds",
//            title: "Employee",
            fitColumns: true,
            singleSelect: true,
            autoRowHeight: false,
            pagination: true,
            PostsearchFilter: true,
            rownumbers: true,
            pageSize: 10,
            width: '100%',
            rowStyler: function (index, row) {
                var style = "";
                if (row.status == '2') {
                    style += 'background-color:#cac3c3;color:#fff;';
                }
                /* if (row.emp_company_id == null) {
                    style += 'background-color:#551414;color:#fff;';

                } */
                return style;
            },
            toolbar: [{
                    text: 'New',
                    iconCls: 'icon-add',
                    handler: function () {

                        showModalForm(livesite + 'Employee/importProfile');
                    }
                }, {
                    iconCls: 'icon-edit',
                    text: 'Complete Setup',
                    handler: function () {
                        var row = $('#emptable').datagrid('getSelected');
                        if (row) {
                            showLargeModalForm(livesite + 'Employee/setupprofile/' + row.emp_pkey);
                        } else {
                            alert("Please select a record to edit")
                        }
                    }
                }, 
				{
                    iconCls: 'icon-remove',
                    text: 'Remove',
                    handler: function () {

                        var rows = $('#emptable').datagrid('getSelected');
                        if (rows) {
                            var str_ids = "";
                            str_ids = rows.emp_pkey;    
                            if (confirm("Do you want to delete the selected employee(s)?")) {

                                $.ajax({
                                    url: livesite + "Employee/deleteEmp",
                                    data: {
                                        ids: str_ids
                                    },
                                    success: function (response) {
                                        //var text = response.responseText;
                                        // process server response here
                                        reloadTable('emptable')
                                    }
                                });

                            }

                        } else {
                            alert("Please select a record to edit")
                        }

                    }
                }
            ],
            fitColumns:true,
                    pageList: [2, 5, 10, 50, 100],
            columns: [
                [
                    {field: 'avatar', title: 'Image', width: "5%", sortable: true},
                    {field: 'emp_company_id', title: 'Employee ID', width: "15%", sortable: true},
                    {field: 'name', title: 'Full Name', width: "20%", sortable: true},
                    {field: 'desig_name', title: 'Designation', width: "20%", sortable: true},
                    {field: 'joining_date', title: 'Joined Date', width: "15%", sortable: true},
                    {field: 'branch_name', title: 'Branch Name', width: "15%", sortable: true},
                    {field: 'buttons', title: 'Doc', width: "8%", sortable: true}
                ]
            ],
            onSearch: function (s) {

                $('#emptable').datagrid('load', {
                    emp: $('#searchqupo').val(),
                    name: $('#rsndempid').val(),
                    branch: $('#filterby_branch').val()
                });
            },
            onLoadSuccess: function () {
                setwidth();
            }
        });


    });
     /* edited by bindu 20-02-26 */
   $(".home").on("click", function () {

    $("#container").isLoading({
        text: "Loading",
        position: "overlay",
    });

    let url = "";
    var userGroup=<?php echo json_encode($user_group); ?>

    if (userGroup == "1") {
        url = livesite + "EmployeeManage/index";
    } 
    else if (userGroup == "2") {
        url = livesite + "EmployeeMenu/addon";
    }

    $("#container").load(url, function () {
        isDashboardShown = false;
    });

});

	/* edited by bindu 20-02-26 */
</script>