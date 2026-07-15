<script>

    //The below function is used to display NoticePeriod in the index page. By ***ARUL P DAS on 27/11/2019
    jQuery(document).ready(function () {
        $('#tbl_notice').datagrid({
            url: livesite + "NoticePeriod/listNotice",
            pagination: true,
            singleSelect: true,
            rownumbers: true,
            // queryParams: {
            //     employee: employee
            // },
            toolbar: [{
                    text: 'New',
                    iconCls: 'icon-add',
                    handler: function () {
                        showModalForm(livesite + 'NoticePeriod/form/')
                    }
                }, {
                    text: 'Edit',
                    iconCls: 'icon-edit',
                    handler: function () {
                        var row = $('#tbl_notice').datagrid('getSelected');
                        if (row) {
                            showModalForm(livesite + 'NoticePeriod/form/' + row.notice_pkey)
                        } else {
                            $.notify('Please Select A record to Edit!', {
                                type: 'danger',
                                allow_dismiss: false
                            });
                        }
                    }
                }, {
                    iconCls: 'icon-remove',
                    text: 'Delete',
                    handler: function () {
                        var row = $('#tbl_notice').datagrid('getSelected');
                        if (row) {
                            if (confirm("Are you sure want to delete ")) {
                                // showModalForm(livesite + 'NoticePeriod/deleteGrade/'+ row.notice_pkey)
                                $.ajax({
                                    url: livesite + "NoticePeriod/deleteNotice/" + row.notice_pkey,
                                    // data: {
                                    //     notice_pkey: str_ids
                                    // },
                                    success: function (response) {
                                        var response = $.parseJSON(response);
                                        if (response.success) {
                                            $.notify(response.msg, {
                                                type: 'success',
                                                allow_dismiss: true
                                            });
                                        }
                                        reloadTable('tbl_notice');
                                    }
                                });
                            }
                        } else {
                            $.notify('Please Select A record to Delete!', {
                                type: 'danger',
                                allow_dismiss: false
                            });
                        }
                    }
                }],
            fitColumns: true,
            pageList: [2, 5, 10, 50, 100],
            columns: [[
                    // {field: 'notice_pkey', title: 'PKEY', width: "25%"},
                    //{field: 'attendance_type', title: 'Attendance Type', width: "20%"},
                    {field: 'notice_days', title: 'Days', width: "49%"},
                    {field: 'description', title: 'Description', width: "50%"},
                    // {field: 'status', title: 'Status', width: "25%"},
                ]]
        });
    });

</script>
<!-- <div class="toolbar"><a href="#" onclick="createGrade(0)" >New</a></div> -->
<section class="content-header">
    <h1 class="text-primary-18">Notice Period</h1>
    <hr style="margin-top: 8px;margin-bottom: -2px;">
</section>
<section class="content">
    <div class="col-md-12">
        <br>
        <div class="box box-primary " style="margin-top: -21px;">
            <div class="box-body" style="    margin-top: -11px;">
                <br>
                <table id="tbl_notice" class="display" cellspacing="0" width="100%">
                    <thead>
                        <tr>
                            <th></th>
                            <th>Days</th>
                            <th>Description</th>
                        </tr>
                    </thead>
                </table>
            </div><!-- /.box-body -->
            <!--/.direct-chat -->
        </div><!-- /.col -->
    </div>
</section>
<div id="gradeModel"></div>
<style type="text/css">
    .main-footer{
        margin-top:-23px;
    }
    #container{
        margin-bottom: 23px;
    }
</style>