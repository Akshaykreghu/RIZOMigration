<section class="content-header">
    <h1>Employee Leave Request(s) (<?php echo $emp_leave_count; ?>)</h1>
</section>
<!-- Main content -->
<section class="content">
    <div class="row">
        <div class="col-md-12">
            <!-- Leave Requests -->
            <!-- DIRECT CHAT DANGER -->
            <div class="box ">
                <div class="box-header with-border">
                    <div class="box-tools pull-right">
                        <button class="btn btn-box-tool" data-widget="collapse">
                            <i class="fa fa-minus"></i>
                        </button>
                        <button class="btn btn-box-tool" >
                            <i class="fa fa-times"></i>
                        </button>
                    </div>
                </div><!-- /.box-header -->
                <div class="box-body">
                    <table id="empleaverequeststable" class="table table-bordered table-hover">
                     
                    </table>
                </div><!-- /.box-body -->
            </div><!--/.direct-chat -->
        </div><!-- /.col -->
    </div>
</section>
<script>
jQuery(document).ready(function() {
    $('#empleaverequeststable').datagrid({
                url:livesite+"LeaveRequest/listempleaves",
                pagination:true,
                singleSelect:true,
                toolbar: [{
                iconCls: 'icon-edit',
                text:'Manage Leave',
                handler: function(){
                    var row = $('#empleaverequeststable').datagrid('getSelected');
                    var leaveentryId = row.LEAVEENTRYID;
                    showLargeModalForm(livesite+'LeaveRequest/manageempleave/' + row.LEAVEENTRYID);
                }
                }],
                fitColumns:true,
                pageList:[2,5,10,50,100],
                columns:[
                [
                          {field:'emp_name',title:'Employee name',width:"20%"},
                          {field:'leave_type',title:'Leave type',width:"10%"},
                          {field:'applied_date',title:'Applied date',width:"20%"},
                          {field:'FROMDATE',title:'From date',width:"20%"},
                          {field:'TODATE',title:'To date',width:"20%"},
                          {field:'LEAVESTATUS',title:'Leave status',width:"10%"}
                ]
                ]
                });
});
</script>