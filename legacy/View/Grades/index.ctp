<script>

    //The below function is used to display grades in the index page. By ***ARUL P DAS on 27/11/2019
    jQuery(document).ready(function () {
        $('#tbl_grade').datagrid({
            url: livesite + "Grades/listGrades",
            pagination: true,
            singleSelect: true,
            rownumbers: true,
            toolbar: [{
                    text: 'New',
                    iconCls: 'icon-add',
                    handler: function () {
                        showModalForm(livesite + 'Grades/form/')
                    }
                }, {
                    text: 'Edit',
                    iconCls: 'icon-edit',
                    handler: function () {
                        var row = $('#tbl_grade').datagrid('getSelected');
                        if (row) {
                            showModalForm(livesite + 'Grades/form/' + row.grade_pkey)
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
                        var row = $('#tbl_grade').datagrid('getSelected');
                        if (row) {
                            if (confirm("Are you sure want to delete ")) {
                                $.ajax({
                                    url: livesite + "Grades/deleteGrade/" + row.grade_pkey,
                                    success: function (response) {
                                        var response = $.parseJSON(response);
                                        if (response.success) {
                                            $.notify(response.msg, {
                                                type: 'success',
                                                allow_dismiss: true
                                            });
                                        }
                                        reloadTable('tbl_grade');
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
                    // {field: 'grade_pkey', title: 'PKEY', width: "25%"},
                    {field: 'grade_code', title: 'Grade Code', width: "49%"},
                    {field: 'grade_name', title: 'Grade', width: "50%"},
                    // {field: 'status', title: 'Status', width: "25%"},
                ]]
        });
    });

</script>
<!-- <div class="toolbar"><a href="#" onclick="createGrade(0)" >New</a></div> -->
<section class="content-header">
    <h1 style="text-align:left; font-size: 40px; margin-left: 30px;"> Grade </h1>
</section>
<hr style="border-color: white;">
<div class="box" style="margin-right: 30px; margin-left: 30px; width: auto;">
    <div class="box-header with-border">
        <h1 class="box-title" style="font-size: 25px;">&nbsp;</h1>
        <div class="box-tools pull-right">
            <button class="btn btn-box-tool" data-widget="collapse">&nbsp;</button>
        </div>
    </div><!-- /.box-header -->
    <!--	<div class="com-md-12" id="infoid" style="height:-webkit-fill-available !important;">
                
            </div>-->
    <table id="tbl_grade" class="display" cellspacing="0" width="100%">
        <thead>
            <tr>
                <th></th>
                <th>Grade Code</th>
                <th>Grade Name</th>
            </tr>
        </thead>
    </table>
</div>
<div id="gradeModel"></div>
<style type="text/css">
    .main-footer{
        margin-top:-23px;
    }
    #container{
        margin-bottom: 23px;
    }
</style>