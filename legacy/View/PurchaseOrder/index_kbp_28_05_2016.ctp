
<h1 class="page-header">Purchase Order </h1>
<div class="row">
  <section class="content">
    <div class="row">
        <div class="col-md-12">
            <!-- DIRECT CHAT DANGER -->
            <div class="box ">

                <div class="box-body">
                    <!-- Employee import form -->

                    <div class="box-body">
                        <div  class="col-md-4">
                            <table id="materialtable" >
                            </table>
                        </div>      
                        <div class="col-md-8">
                            <table id="purchasetable">
                            </table><!-- /.box-body -->

                        </div>
                    </div>

                </div>

            </div>
        </div>
    </div>
</section>  
    
</div>
<script>
   function dataload()
{
   var Client_name = $('#client_name').val();
    var store_code = $('#store_master_pkey').val();
     var Location = $('#location').val();  
      var item_code = $('#item_code').val();

     $('#materialtable').datagrid('load', {
         client_name: Client_name,
         location: Location,
         store_code:store_code,
            item_code:item_code
     });  
}
    $(document).ready(function () {
        $('#materialtable').datagrid({
            url: 'PurchaseOrder/meteriallist',
            rownumbers: true,
            title: "Material Request Pending",
            fitColumns: true,
            singleSelect: false,
            autoRowHeight: false,
            pagination: true,
            width: '90%',
            pageSize: 10,
            toolbar:[],
            columns:[[
                    {field: 'item_desc', title: 'Item Name', width: 120, sortable: true, order: 'asc', editor: 'textbox'}, 
                    {field: 'sumunit', title: 'Pending', width: 120, sortable: true, order: 'asc', editor: 'textbox'}, 
                    
                    
                ]],
            });
    })
    
   
    $(document).ready(function () {
        $('#purchasetable').datagrid({
            url: 'PurchaseOrder/purchaselist',
            rownumbers: true,
            title: "Purchase Order",
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
                        var url = 'PurchaseOrder/form';
                        $('#modalDiv').load(url,function(){
                            $('#modalDiv').modal('show');
                        });
                    }
              }, {
                    iconCls: 'icon-edit',
                    text: "Edit",
                    handler: function () {
                        var row = $('#purchasetable').datagrid('getSelected');
                        //console.log(row);
                        if (row) {
                            var po_pkey = row.po_pkey;
                             var url = 'PurchaseOrder/form/'+ po_pkey;
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
                        var row = $('#purchasetable').datagrid('getSelected');
                        if (row) {
                                  var r=confirm("Do You Want  To Remove The Selected Item")
                                    if(r==true){
                                  var po_pkey = row.po_pkey;
                                                                $.ajax({ 
											 url:"PurchaseOrder/purchasedelete",
													data : {
													po_pkey : po_pkey
													},
													success : function(response) {
													//var text = response.responseText;
													// process server response here
                                                                                                          $.notify("Deleted",{type:'danger'});
															reloadTable('purchasetable')
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
                    {field: 'po_number', title: 'Purchase Order Number', width: 100, sortable: true, order: 'asc', editor: 'textbox'}, 
                    {field: 'supplier_code', title: 'Supplier Code ', width: 100, sortable: true, order: 'asc', editor: 'textbox'}, 
                    {field: 'fcy_code', title: 'Material Request Code', width: 100, sortable: true, order: 'asc', editor: 'textbox'}, 
                    {field: 'locationpu', title: 'Location', width: 100, sortable: true, order: 'asc', editor: 'textbox'}, 
                    {field: 'client_namepu', title: 'Client Name', width: 100, sortable: true, order: 'asc', editor: 'textbox'}, 
                    {field:'bc_rate',title:'Bc Rate' ,width: 100, sortable: true, order: 'asc',editer :'textbox'},
                    
                ]],
            });
    })  
    
    
</script>