<script>
function showform()
{
 var url = livesite+'ResignationRequest/Request';
        
        var container = $("#modalDetailForm #modaldetails-content")
container.load(url, function() {
            $("#modalDetailForm").modal('show');
        });
}
</script>
<section class="content-header">
    <h1>Employee Resignation Request</h1>
</section>
<!-- Main content -->
<section class="content">
    <div class="row">
        <div class="col-md-12">
            <!-- My Leave Requests -->
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
                  
               <table id="myleaverequeststable" class="table table-bordered table-hover">
                     
                    </table>
                    <input type="hidden" value="<?php echo $cur_emp_key ; ?>" id="emp_pkey">
                </div><!-- /.box-body -->
            </div><!--/.direct-chat -->
        </div><!-- /.col -->
    </div>
</section>
<script>
jQuery(document).ready(function() {
    $('#myleaverequeststable').datagrid({
                url:livesite+"ResignationRequest/listleaves",
                pagination:true,
                singleSelect:true,
                toolbar: [{
                text:'Manage Request',
                iconCls:'icon-edit',
                handler: function(){
                    
                    var row = $('#myleaverequeststable').datagrid('getSelected');
                        //if (row && row.LEAVESTATUS != 'Cancelled'){
                        if(row)
                            {
                                    showLargeModalForm(livesite+'ResignationRequest/addeditleave/'+ row.Resignation_pkey);
                            }
                            else
                                {
                                    $.notify("Please Select a Row",{Type: 'warnig'});
                                }
                
                    
                }
                },{
                iconCls: 'icon-remove',
                text:'Remove',
                handler: function(){
							
                            var rows = $('#myleaverequeststable').datagrid('getSelected');
                            if (rows){	
                                var emp = $('#emp_pkey').val();
                                if(rows.authorised_to != emp)
                                    {
                                            if (confirm("Do you want to delete the selected resignation request(s)?")) {
                                                
                                                    $.ajax({
                                             url:  livesite+"ResignationRequest/deleteresignation/"+ rows.Resignation_pkey,
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
                                    alert("You Cannot delete requests");
                                    }
                                }
                                else
                                {
                                $.notify("Please Select a Row",{type:'warning'});
                                }
                    
                }
                }],
                fitColumns:true,
                pageList:[2,5,10,50,100],
                columns:[
                [
                          {field:'Name',title:'Employee Name',width:"20%"},
                          {field:'Reason',title:'Reason',width:"10%"},
                          {field:'Reason_Desc',title:'Reason Desc',width:"10%"},
                          {field:'Comments_to_manager',title:'Comments',width:"10%"},
                          {field:'applied_date',title:'Applied date',width:"10%"},
                          {field:'Last_workingday',title:'Last Working Day',width:"10%"},
                          {field:'contact_no',title:'Contact Number',width:"20%"}
                ]
                ]
                });
});
</script>
<div id="modalDetailForm" class="modal fade bs-example-modal-lg"  tabindex="-1" role="dialog" aria-labelledby="myLargeModalLabel">
    <div class="modal-dialog modal-lg">
        <div class="modal-content" id="modaldetails-content">

        </div>
    </div>
    </div>