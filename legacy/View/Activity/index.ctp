<style>
    .customized-contents input.form-control, textarea.form-control {
        border-radius: 4px !important;
    }

    .customized-contents input.form-control, textarea.form-control, .select2 .select2-selection {
        -webkit-box-align: center;
        align-items: center;
        background-color: var(--ds-background-subtleNeutral-resting, #F4F5F7);
        border-color: var(--ds-border-neutral, #F4F5F7);
        border-radius: 3px;
        border-style: solid;
        border-width: 2px;
        box-shadow: none;
        cursor: default;
        display: flex;
        flex-wrap: wrap;
        -webkit-box-pack: justify;
        justify-content: space-between;
        min-height: 34px;
        position: relative;
        transition: background-color 200ms ease-in-out 0s, border-color 200ms ease-in-out 0s;
        box-sizing: border-box;
        padding: 0px;
        outline: 0px !important;
        color: #353535;
        font-weight: 600;
        padding-left: 10px;
    }

    .customized-contents #searchqupo {
        width: 100%;
        background: #fff;
        border: 1px solid #ccc;
        margin-top: 5px;
    }

</style>
<script src="<?php echo $this->webroot;?>plugins/ckeditor4/ckeditor4/ckeditor.js" type="text/javascript"></script>

<section class="content-header" style="margin-left: 20px; margin-right: 20px; ">
    <button onclick="showAddModal(); " class="btn btn-primary pull-right"><li class="fa fa-plus"></li></button>
    <?php if(!$this->Session->read('emp_fkey')) { ?>
    <button onclick="loadMenu(this, 'Activity/report'); " style="margin-right: 20px; " class="btn btn-default pull-right">View Report</button>
    <?php } ?>
    <h1 style="font-size: 30px;">Task & Time</h1>
    <p>Enter Project Logs and Manage your tasks timeline here.</p>
    <hr style="margin-top: 8px;margin-bottom: -2px;">
</section>
<!-- Main content -->
<section class="content customized-contents">



    <div class="col-md-3" style="">
        <div class="row">
            <label class="col-md-3" for="project_id" style="text-align: right; ">Task: </label>
            <div class="col-md-9">
                <select id="project_id" onchange="filterbyType(); " name="project_id" class="form-control js-example-basic-single" >
                    <option value="">--All--</option>
                    <?php foreach ($activity_ids as $key => $value) { ?>                              
                        <option value="<?php echo $value['activity_track']['activity_track_pkey']; ?>"><?php echo $value['activity_track']['activity_track_pkey']; ?> - <?php echo $value['activity_track']['summary']; ?></option>
                    <?php } ?>
                </select>
            </div>  
        </div>
    </div>

    <div class="col-md-3" style="">
        <div class="row">
            <label class="col-md-3" for="project_name" style="text-align: right; ">Project : </label>
            <div class="col-md-9">
                <select id="project_name" onchange="filterbyType(); " name="project_name" class="form-control js-example-basic-single" >
                    <option value="">--All--</option>
                    <?php foreach ($project_list as $key => $value) { ?>                              
                        <option value="<?php echo $value['activity_projects']['activity_projects_pkey']; ?>"><?php echo $value['activity_projects']['activity_projects_head']; ?></option>
                    <?php } ?>
                </select>
            </div>  
        </div>
    </div>

    <div class="col-md-3" style="">
        <div class="row">
            <label class="col-md-3" for="import_emp_branch" style="text-align: right; ">Status : </label>
            <div class="col-md-9">
                <select id="activity_type" onchange="filterbyType(); " name="import_emp_branch" class="form-control js-example-basic-single" >
                    <option value="">--All--</option>
                    <?php foreach ($activity_status as $key => $value) { ?>                              
                        <option value="<?php echo $value['activity_status']['activity_status_pkey']; ?>"><?php echo $value['activity_status']['activity_status']; ?></option>
                    <?php } ?>
                </select>
            </div>  
        </div>
    </div>

    <div class="col-md-3" style="">
        <div class="row">
            <label class="col-md-3" for="due_date" style="text-align: right; ">Due Date : </label>
            <div class="col-md-9">
                <input class="form-control" onchange="filterbyType(); " value="" placeholder="Pick a Date to Search" id="due_date" />
            </div>  
        </div>
    </div>

    <div class="col-md-12" style="margin-top: 20px; ">
        <br>
        <div class="box box-primary " style="margin-top: -21px;">
            <div class="box-body" style="    margin-top: -11px;">
                <br>
                <table id="uaccess"></table>
            </div>
        </div>
    </div>
</section>
<!--<section class="content">
    <div class="row">
        <div class="col-md-12">
             DIRECT CHAT DANGER 
            <div class="box box-body">
                 /.box-header 
                <div class="box-body" id="div-reportcriterias">
                    <div class="col-md-12">

                        <input type="text" id="user" class="form-control" name="user" placeholder="Search Your Employee Name" />
                    </div>
                </div> /.box-body 
            </div>
            <div class="box box-body">
                <table id="uaccess" >
                </table>
            </div>
        </div>
    </div>
</section>-->
<script>

    function showAddModal() {
        showActivityModalForm(livesite + 'Activity/add');
    }
    //reload data grid with username start
    function reloadDatagrid(user_fkey)
    {
        $("#uaccess").datagrid('load', {
            user_fkey: user_fkey
        });
    }

    $(document).ready(function () {

        $('#due_date').datepicker({
            format: 'yyyy-mm-dd',
            autoclose: true,
            clearBtn: true
        });

        $("#activity_type").select2();
        $("#project_name").select2();
        $("#project_id").select2();

        //   alert("doness");

        var usersoptions = {
            url: function (phrase) {
                return "UserCredentials/getusers?username=" + phrase;
            },
            getValue: "full_name",
            list: {
                onLoadEvent: function () {
                    if ($('#user').val() == "") {
                        $("#eac-container-user>ul").empty();
                    }
                },
                onClickEvent: function () {
                    var selectedItem = $("#user").getSelectedItemData();
                    var site_fkey = selectedItem.emp_pkey;


                    reloadDatagrid(site_fkey);

                }
            },
            requestDelay: 200,
            //theme: "plate-dark",
            placeholder: "Search for a employee"
        };

        $('#user').easyAutocomplete(usersoptions);
        $('#uaccess').datagrid();
        var user_fkey = $('#usr').val();
        reloadDatagrid(user_fkey);
        $('#usr').on('change', function () {
            var user_fkey = $(this).val();
            reloadDatagrid(user_fkey);
        });




        // end
        $('#uaccess').datagrid({
            url: livesite + 'Activity/listData',
            rownumbers: true,
//            title: "User Credentials",
            fitColumns: true,
            singleSelect: true,
            autoRowHeight: false,
            pagination: true,
            PostsearchFilter: true,
            pageSize: 10,
            toolbar: [{
                    iconCls: 'icon-edit',
                    text: 'Edit',
                    handler: function () {
                        var row = $('#uaccess').datagrid('getSelected');
                        if (row) {
                            var bank_id = row.activity_track_pkey;
                            showActivityModalForm(livesite + 'Activity/add?id=' + bank_id)
                        } else {
                            alert("Please select a record to edit")
                        }
                    }
                }, '-' , {
                    iconCls: 'icon-remove',
                    text: 'Remove',
                    handler: function () {

                        var rows = $('#uaccess').datagrid('getSelections');
                        if (rows.length > 0) {
                            var str_ids = "";
                            for (var i = 0; i < rows.length; i++) {
                                var data = rows[i];
                                if (str_ids == "") {
                                    str_ids += data.activity_track_pkey;
                                } else
                                {
                                    str_ids += "," + data.activity_track_pkey;
                                }
                            }
                            if (confirm("Do you want to delete the selected Record(s)?")) {

                                $.ajax({
                                    url: livesite + "Activity/deleteRow",
                                    data: {
                                        ids: str_ids
                                    },
                                    success: function (response) {
                                        //var text = response.responseText;
                                        // process server response here
                                        reloadTable('uaccess')
                                    }
                                });

                            }

                        } else {
                            alert("Please select any data");
                        }
                    }
                }, '-',
            ],
            columns: [[
                    {field: 'activity_track_pkey', title: 'ID', width: 20, sortable: true, order: 'asc'},
                    {field: 'summary', title: 'Task Name', width: 40, sortable: true, order: 'asc'},
                    {field: 'fullname', title: 'Employee', width: 30, sortable: true, order: 'asc'},
                    {field: 'activity_projects_head', title: 'Project', width: 30, sortable: true, order: 'asc'},
                    {field: 'activity_status', title: 'Status', width: 30, sortable: true, order: 'asc'},
                    {field: 'activity_date', title: 'Due Date', width: 20, sortable: true, order: 'asc'},
                    {field: 'totald', title: 'Total Duration', width: 20, sortable: true, order: 'asc'},
                    {field: 'estimated_time', title: 'Est. Time', width: 20, sortable: true, order: 'asc'},
                    {field:'description',title:'Description',width:40,sortable:true,order:'asc'},
                ]],
            onCheck: function (index, row) {
                var menu_id = row.menu_id;
                var user_pkey = $("#usr").val();

                var active = 'Y';
                if ($('#chk-useraccess-' + menu_id).length != 0) {
                    if ($('#chk-useraccess-' + menu_id).prop('checked') == true) {
                        active = 'Y';
                    } else {
                        active = 'N';
                    }
                } else {
                    return false;
                }

                var url = livesite + 'UserCredential/saveuseraccess';
                $.ajax({
                    url: url,
                    type: 'post',
                    data: {
                        menu_id: menu_id,
                        user_pkey: user_pkey,
                        active: active
                    },
                    success: function (resp) {
                        $.notify($.parseJSON(resp).msg, {
                            type: 'success',
                            allow_dismiss: false
                        });
                    }
                });
            },
            onUncheck: function (index, row) {
                var menu_id = row.menu_id;
                var user_pkey = $("#usr").val();

                var active = 'N';
                if ($('#chk-useraccess-' + menu_id).length != 0) {
                    if ($('#chk-useraccess-' + menu_id).prop('checked') == true) {
                        active = 'Y';
                    } else {
                        active = 'N';
                    }
                } else {
                    return false;
                }

                var url = 'UserCredential/saveuseraccess';
                $.ajax({
                    url: url,
                    type: 'post',
                    data: {
                        menu_id: menu_id,
                        user_pkey: user_pkey,
                        active: active
                    },
                    success: function (resp) {
                        $.notify($.parseJSON(resp).msg, {
                            type: 'success',
                            allow_dismiss: false
                        });
                    }
                });
            }
            ,
            onSearch: function (s) {

                $('#uaccess').datagrid('load', {
                    emp: $('#searchqupo').val()
                });
            }
        });
    })

    function reloadDatagrid(site_fkey) {
        //fetch current status for site

        $('#uaccess').datagrid('load', {
            user_fkey: site_fkey
        });
    }

    function filterbyType(obj) {
        var type = $('#activity_type').val();
        var project = $('#project_name').val();
        var due_date = $('#due_date').val();
        var project_id = $("#project_id").val();

        $('#uaccess').datagrid('load', {
            activitystatus: type,
            project: project,
            due_date: due_date,
            project_id: project_id
        });

    }
</script>