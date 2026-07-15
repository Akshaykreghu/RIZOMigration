

<h1 class="page-header">STORE MESTER</h1>
<div class="row">
    
    
</div>
<br>
<table id="storetable" >

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
        $('#storetable').datagrid({
            url: 'Store/Storelist',
            rownumbers: true,
            title: "Store MESTER",
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
                        var url = 'Store/form';
                        $('#modalDiv').load(url,function(){
                            $('#modalDiv').modal('show');
                        });
                    }
              }, {
                    iconCls: 'icon-edit',
                    text: "Edit",
                    handler: function () {
                        var row = $('#storetable').datagrid('getSelected');
                        //console.log(row);
                        if (row) {
                            var store_master_pkey = row.store_master_pkey;
                             var url = 'Store/form/'+ store_master_pkey;
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
                        var row = $('#storetable').datagrid('getSelected');
                        if (row) {
                                  var r=confirm("Do You Want  To Remove The Selected Item")
                                    if(r==true){
                                  var store_master_pkey = row.store_master_pkey;
                                                                $.ajax({ 
											 url:"Store/Storedelete",
													data : {
													store_master_pkey : store_master_pkey
													},
													success : function(response) {
													//var text = response.responseText;
													// process server response here
															reloadTable('storetable')
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
                    {field: 'store_code', title: 'Store Code', width: 100, sortable: true, order: 'asc', editor: 'textbox'}, 
                    {field: 'store_location', title: 'Store Location', width: 100, sortable: true, order: 'asc', editor: 'textbox'}, 
                    {field: 'address', title: 'Address', width: 100, sortable: true, order: 'asc', editor: 'textbox'}, 
                    {field: 'city', title: 'City', width: 100, sortable: true, order: 'asc', editor: 'textbox'}, 
                    {field:'state',title:'State' ,width: 100, sortable: true, order: 'asc',editer :'textbox'},
                    {field:'pincode',title:'Pincode' ,width: 100, sortable: true, order: 'asc',editer :'textbox'},
                    {field:'created_by',title:'Created By' ,width: 100, sortable: true, order: 'asc',editer :'textbox'},
                    {field:'creation_date',title:'Created Date' ,width: 100, sortable: true, order: 'asc',editer :'textbox'},
                    {field:'modified_by',title:'Modified By' ,width: 100, sortable: true, order: 'asc',editer :'textbox'},
                ]],
            });
    })
</script>