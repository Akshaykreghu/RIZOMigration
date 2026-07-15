

<h1 class="page-header">MATERIAL REQUEST</h1>
<div class="row">
    
    
</div>
<br>
<table id="materialtable" >

</table>
<div id="empSetupModalForm" class="modal fade">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <!-- Content will be loaded here from "remote.php" file -->
        </div>
    </div>
</div>
<script>
   
    $(document).ready(function () {
        $('#materialtable').datagrid({
            url: 'MaterialRequest/materiallist',
            rownumbers: true,
            title: "Material Request",
            fitColumns: true,
            singleSelect: true,
            autoRowHeight: false,
            pagination: true,
            width: '100%',
            pageSize: 10,
            toolbar: [{
                    iconCls: 'icon-add',
                     
                    text: "New",
                    handler: function () {
                        var url = 'MaterialRequest/form';
                        $('#modalDiv').load(url,function(){
                            $('#modalDiv').modal('show');
                        });
                    }
              }, {
                    iconCls: 'icon-edit',
                    text: "Edit",
                    handler: function () {
                        var row = $('#materialtable').datagrid('getSelected');
                        //console.log(row);
                        if (row) {
                            var mr_pkey = row.mr_pkey;
                             var url = 'MaterialRequest/form/'+ mr_pkey;
                            $('#modalDiv').load(url, function () {
                                $('#modalDiv').modal('show');
                            });
                        }
                        else
                        {
                            alert("Please Choose A Row");
                        }
                    }
                },{
                     iconCls: 'icon-cancel',
                    text: "Remove",
                    handler: function () {
                        var row = $('#materialtable').datagrid('getSelected');
                        if (row) {
                                  var r=confirm("Do You Want  To Remove The Selected Item")
                                    if(r==true){
                                  var mr_pkey = row.mr_pkey;
                                                                $.ajax({ 
											 url:"MaterialRequest/materialdelete",
													data : {
													mr_pkey : mr_pkey
													},
													success : function(response) {
													//var text = response.responseText;
													// process server response here
                                                                                                          $.notify("Deleted",{type:'danger'});
															reloadTable('materialtable')
														}
															
													});
                                                             }
                        else
                        {
                            alert("canceled operation");
                        }
                        }
                          
                    }
                }],
            columns:[[
                    {field: 'client_name', title: 'Store Code', width: 100, sortable: true, order: 'asc', editor: 'textbox'}, 
                    {field: 'mr_code', title: 'Material Request Code', width: 100, sortable: true, order: 'asc', editor: 'textbox'}, 
                    {field: 'location', title: 'Store Location', width: 100, sortable: true, order: 'asc', editor: 'textbox'}, 
                    {field: 'mr_date', title: 'Address', width: 100, sortable: true, order: 'asc', editor: 'textbox'}, 
                    {field: 'store_code', title: 'City', width: 100, sortable: true, order: 'asc', editor: 'textbox'}, 
                    {field:'created_by',title:'Created By' ,width: 100, sortable: true, order: 'asc',editer :'textbox'},
                    
                ]],
            });
    })
</script>