<style>
      /* <!-- edited by bindhu 19-02-2026 --> */
    .heading {
        display: flex;
        flex-direction: row;
        align-items: end;
        justify-content: space-between;
        /* margin-left: 20px; */
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
    .datagrid .panel-body
    {
        width: 100% !important;
    }
    .box-header>.box-tools {
        display: none;}
        .box-header.with-border{
            border: none;
        }
</style>
<section class="content-header heading">
    <h1 class="text-primary-18">Employee Leave Request</h1>    
    <div class="text-primary-16 home"
         style="display:flex; align-items:center; gap:10px; cursor:pointer;">
        <i class="fa" style="font-size:16px;">&#xf104;</i>
        Back
    </div>
</section>
<!-- edited by bindhu 19-02-2026 end -->
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
                    
                    <div class="tabset-attendanceregister">
<!--                        <div id="tab0" data-pws-tab="tab0" data-pws-tab-name="To be Verify">
                            <h3>Leave Requests</h3>
                        </div>-->
                        <div id="tab1" data-pws-tab="tab1" data-pws-tab-name="To be Verify">
                            <table id="empleaverequeststable" class="table table-bordered table-hover">
                     
                            </table>
                        </div>
                        <div id="tab2" data-pws-tab="tab2" data-pws-tab-name="Verified" data-pws-tab-icon="fa-video-camera">
                        <table id="empleaverequeststableverified" class="table table-bordered table-hover">
                     
                            </table>
                        </div>
                    </div>
                    
                    
                </div><!-- /.box-body -->
            </div><!--/.direct-chat -->
        </div><!-- /.col -->
    </div>
</section>
<script>
jQuery(document).ready(function() {
     
        $('.tabset-attendanceregister').pwstabs({
                    effect: 'scale', // You can change effects of your tabs container: scale / slideleft / slideright / slidetop / slidedown / none
                    defaultTab: 1, // The tab we want to be opened by default
                    containerWidth: '100%', // Set custom container width if not set then 100% is used
                    tabsPosition: 'horizontal', // Tabs position: horizontal / vertical
                    horizontalPosition: 'top', // Tabs horizontal position: top / bottom
                    verticalPosition: 'left', // Tabs vertical position: left / right
                    responsive: false, // Make tabs container responsive: true / false - boolean
                    theme: '',
                    rtl: false                    // Right to left support: true/ false
                });
        
        
    
    $('#empleaverequeststable').datagrid({
                url:livesite+"LeaveRequest/listempleaves",
                pagination:true,
                singleSelect:true,
                rownumbers:true,
                onLoadSuccess:function(data){
                    $.messager.show({
                        title:'Info',
                        msg:'You have  '+data.total+' Leave Requests'
                    });
                    loadtabs();
                },
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
                          {field:'emp_name',title:'Employee Name',width:"20%"},
                          {field:'Action',title:'Action',width:"10%"},                          
                          {field:'leave_type',title:'Leave Type',width:"10%"},
                          {field:'FROMDATE',title:'From Date',width:"10%"},
                          {field:'TODATE',title:'To Date',width:"10%"},
                          {field:'leave_days',title:'Leave Total',width:"10%"},
                          {field:'applied_date',title:'Applied Date',width:"10%"},                         
                          {field:'LEAVESTATUS',title:'Leave Status',width:"20%"}
                ]
                ]
                });
                
                $('#empleaverequeststableverified').datagrid({
                url:livesite+"LeaveRequest/listempleavesverified",
                pagination:true,
                rownumbers:true,
                singleSelect:true,
                toolbar: [{
                iconCls: 'icon-edit',
                text:'Manage Leave',
                handler: function(){
                    var row = $('#empleaverequeststableverified').datagrid('getSelected');
                    var leaveentryId = row.LEAVEENTRYID;
                    showLargeModalForm(livesite+'LeaveRequest/manageempleave/' + row.LEAVEENTRYID);
                }
                }],
                fitColumns:true,
                pageList:[2,5,10,50,100],
                columns:[
                [
                          {field:'emp_name',title:'Employee Name',width:"20%"},
                          {field:'Action',title:'Action',width:"10%"},                          
                          {field:'leave_type',title:'Leave Type',width:"10%"},
                          {field:'FROMDATE',title:'From Date',width:"10%"},
                          {field:'TODATE',title:'To Date',width:"10%"},
                          {field:'leave_days',title:'Leave Total',width:"10%"},
                          {field:'applied_date',title:'Applied Date',width:"10%"},                         
                          {field:'LEAVESTATUS',title:'Leave Status',width:"20%"}
                ]
                ]
                });
                function loadtabs(){
                    $('[data-tab-id="tab1"]').trigger('click');
            }
});
// edited by bindhu 19-02-2026
 $(".home").on("click", function() {
        $("#container").isLoading({
            text: "Loading",
            position: "overlay",
        });

        $("#container").load(livesite + "EmployeeMenu/index", function() {
            isDashboardShown = false;
        });


    });
    //  edited by bindhu 19-02-2026 end
</script>