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

<section class="content-header" style="margin-left: 20px; margin-right: 20px; ">
    <button onclick="showAddModal(); " class="btn btn-primary pull-right"><li class="fa fa-plus"></li></button>
    <h1 style="font-size: 30px;">Projects Master</h1>
    <p>Add Projects Heads for your Task Management.</p>
    <hr style="margin-top: 8px;margin-bottom: -2px;">
</section>
<!-- Main content -->
<section class="content customized-contents">

    <div class="col-md-12">
        <br>
        <div class="box box-primary " style="margin-top: -21px;">
            <div class="box-body" style="    margin-top: -11px;">
                <br>
                <table id="uaccess"></table>
            </div>
        </div>
    </div>
</section>

<script>

    function showAddModal() {
        showModalForm(livesite + 'Activity/addproject');
    }
    //reload data grid with username start
    function reloadDatagrid(user_fkey)
    {
        $("#uaccess").datagrid('load', {
            user_fkey: user_fkey
        });
    }

    $(document).ready(function () {

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
            url: livesite + 'Activity/listProjectsData',
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
                            var bank_id = row.activity_projects_pkey;
                            showModalForm(livesite + 'Activity/addproject?id=' + bank_id)
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
                                    str_ids += data.activity_projects_pkey;
                                } else
                                {
                                    str_ids += "," + data.activity_projects_pkey;
                                }
                            }
                            if (confirm("Do you want to delete the selected Record(s)?")) {

                                $.ajax({
                                    url: livesite + "Activity/deleteProject",
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
                    {field: 'activity_projects_head', title: 'Project Name', width: 20, sortable: true, order: 'asc'},
                    {field: 'activity_projects_desc', title: 'Project Description', width: 40, sortable: true, order: 'asc'},
                    {field: 'created_by', title: 'Created By', width: 30, sortable: true, order: 'asc'},
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