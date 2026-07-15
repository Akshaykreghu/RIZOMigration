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

</style>

 <section class="content-header heading">
   
    <h1 class="text-primary-18">My Leave Request(s) (<?php echo $leave_count; ?>)</h1>
    <div class="text-primary-16 home"
         style="display:flex; align-items:center; gap:10px; cursor:pointer;">
        <i class="fa" style="font-size:16px;">&#xf104;</i>
        Back
    </div>
</section>
<!-- Main content -->
<section class="content">
    <div class="row">
        <div class="col-md-12">
            <!-- My Leave Requests -->
            <!-- DIRECT CHAT DANGER -->
            <div class="box box-primary">
                <!-- /.box-header -->
                <div class="box-body">
                    <table id="myleaverequeststable" class="table table-bordered table-hover">
                     
                    </table>
                </div><!-- /.box-body -->
            </div><!--/.direct-chat -->
        </div><!-- /.col -->
    </div>
</section>
<script>
jQuery(document).ready(function() {
    var companyCode = "<?php echo $company_code; ?>";
    $('#myleaverequeststable').datagrid({
                url:livesite+"LeaveRequest/listleaves",
                pagination:true,
                singleSelect:true,
                rownumbers:true,
                toolbar: [{
                text:'New Leave',
                iconCls:'icon-add',
                handler: function(){
                     //edited by athira on 21-09-2025
                    var companyCode = "<?php echo $company_code; ?>";
    // Call different URLs based on company
   const restrictedCompanies = [
    'KWMT','ABSG','MBCT','MRBS','STCL',
    'AGNG','ESNP','VGNN','AYRK','VGFS','VSFS'
];
if (!restrictedCompanies.includes(companyCode)) {
            var url =  livesite+'LeaveRequest/addeditleave_new/0' 
   }else{
            var url =  livesite+'LeaveRequest/addeditleave/0'    
   }
            showLargeModalForm(url);

                    // showLargeModalForm(livesite+'LeaveRequest/addeditleave/0');
                }
                },{
                iconCls: 'icon-edit',
                text:'Leave Details',
                handler: function(){
                        var row = $('#myleaverequeststable').datagrid('getSelected');
                        //if (row && row.LEAVESTATUS != 'Cancelled'){
                        if(row)
                            {
                                //edited by athira on 21-09-2025
                                var companyCode = "<?php echo $company_code; ?>";
                             const restrictedCompanies = [
    'KWMT','ABSG','MBCT','MRBS','STCL',
    'AGNG','ESNP','VGNN','AYRK','VGFS','VSFS'
];
if (!restrictedCompanies.includes(companyCode)) {
                                    var url =  livesite+'LeaveRequest/addeditleave_new/' + row.LEAVEENTRYID 
                                  }else{
                                    var url =  livesite+'LeaveRequest/addeditleave/' + row.LEAVEENTRYID
                                  }
                                    // showLargeModalForm(livesite+'LeaveRequest/addeditleave/' + row.LEAVEENTRYID);
                                    
                showLargeModalForm(url);
                        /*}else{
                            //alert("Please select a record to edit")
                
                                            $.notify("Please select a record to edit",{
                                type: 'error',
                                allow_dismiss: false
                                                                                        
                            });
                        }*/
                            }
                            else
                                {
                                    $.notify("Please Select a Row",{Type: 'warnig'});
                                }
                }
                },
                {
                iconCls: 'icon-edit',
                text:'Show Leave Days',
                handler: function(){
                        var row = $('#myleaverequeststable').datagrid('getSelected');
                        //if (row && row.LEAVESTATUS != 'Cancelled'){
                        if(row)
                            {
                                   showSmallModalForm(livesite + 'LeaveRequest/showleavedays/' + row.LEAVEENTRYID);
                        /*}else{
                            //alert("Please select a record to edit")
                            $.notify("Please select a record to edit",{
                                type: 'error',
                                allow_dismiss: false
                                                                                        
                            });
                        }*/
                            }
                            else
                                {
                                    $.notify("Please Select a Row",{Type: 'warnig'});
                                }
                }
                },
                {
                iconCls: 'icon-remove',
                text:'Remove',
                handler: function(){
							var leaveStatus= ["Authorized", "Approved", "Cancelled", "Rejected"];
                            var rows = $('#myleaverequeststable').datagrid('getSelected');
                            if (rows){
								var str_ids = "";
								
									var data = rows;
                                                                      //alert(data.LEAVESTATUS == 'Approved' || data.LEAVESTATUS == 'Authorized');
									//if(leaveStatus.indexOf(data.LEAVESTATUS)!=-1){
									if(data.LEAVESTATUS == 'Approved' || data.LEAVESTATUS == 'Authorized')
                                                                        {
                                                                                $.notify("You Cannot remove Approved/Authorized Leaves",{type:'danger'});
										return false;
									}
                                    //edited by athira on 24-09-2025
                                    if(data.LEAVESTATUS =='CancellationOfApproved'){
                                        $.notify("You Cannot remove the leave before Approval of Cancellation",{type:'danger'});
										return false;
                                    }
                                    //end
                                   
										str_ids += data.LEAVEENTRYID;
			//	alert(str_ids);
								
                                            if (confirm("Do you want to delete the selected leave request(s)?")) {
                                                
                                                    $.ajax({
                                             url:  livesite+"LeaveRequest/deleteLeaveRequests",
                                                    data : {
                                                    ids : str_ids
                                                    },
                                                    success : function(response) {
                                                    //var text = response.responseText;
                                                    // process server response here\
                                                    $.notify("Removed",{type:'success'});
                                                            reloadTable('myleaverequeststable')
                                                        }
                                                    });
                                        
                                            }
                            
                                }
                                else
                                {
                                $.notify("Please Select a Row",{type:'warning'});
                                }
                    
                }
                }
            ],
                fitColumns:true,
                pageList:[2,5,10,50,100],
				rowStyler: function(index,row){
                    if (row.LEAVESTATUS =='Can not Apply'){
                        return 'background-color:#FF6666;color:#fff;font-weight:bold;';
                    }
                },
                columns:[
                [
                          {field:'leave_type',title:'Leave Type',width:"20%"},
                          {field:'FROMDATE',title:'From Date',width:"10%"},
                          {field:'FROMHALF',title:'',width:"10%"},
                          {field:'TODATE',title:'To Date',width:"10%"},
                          {field:'TOHALF',title:'',width:"10%"},
                          {field:'leave_days',title:'No of Days',width:"10%"},
                          {field:'applied_date',title:'Applied Date',width:"10%"},
                          {field:'LEAVESTATUS',title:'Leave Status',width:"20%"}
                          
                ]
                ]
                });
});
 $(".home").on("click", function() {
        $("#container").isLoading({
            text: "Loading",
            position: "overlay",
        });

        $("#container").load(livesite + "EmployeeMenu/index", function() {
            isDashboardShown = false;
        });


    });
</script>