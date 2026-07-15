
<h1 class="page-header">Direct Purchase Order</h1>
<div class="row">
  <section class="content">
    <div class="row">
        <div class="col-md-12">
            <!-- DIRECT CHAT DANGER -->
            <div class="box ">

                <div class="box-body">
                    <!-- Employee import form -->
 
                    <div class="box-body">
                      
                        <div >
                            <table id="detailspurchasetable">
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
//   function dataload()
//{
//   var Client_name = $('#client_name').val();
//    var store_code = $('#store_master_pkey').val();
//     var Location = $('#location').val();  
//      var item_code = $('#item_code').val();
//
//     $('#materialtable').datagrid('load', {
//         client_name: Client_name,
//         location: Location,
//         store_code:store_code,
//            item_code:item_code
//     });  
//}
//   
    $(document).ready(function () {
        $('#detailspurchasetable').datagrid({
            url: 'DirectPurchaseOrder/directlist',
            rownumbers: true,
            title: "Direct Purchase Order",
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
                        var url = 'DirectPurchaseOrder/form';
                        $('#modalDiv').load(url,function(){
                            $('#modalDiv').modal('show');
                        });
                    }
              }, {
                    iconCls: 'icon-edit',
                    text: "Edit",
                    handler: function () {
                        var row = $('#detailspurchasetable').datagrid('getSelected');
                        //console.log(row);
                        if (row) {
                            var direct_po_pkey = row.direct_po_pkey;
                             var url = 'DirectPurchaseOrder/form/'+ direct_po_pkey;
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
                        var row = $('#detailspurchasetable').datagrid('getSelected');
                        if (row) {
                                  var r=confirm("Do You Want  To Remove The Selected Item")
                                     if(r==true){
                                  var direct_po_pkey = row.direct_po_pkey;
                                                                $.ajax({ 
											 url:"DirectPurchaseOrder/delete",
													data : {
													direct_po_pkey : direct_po_pkey
													},
													success : function(response) {
													//var text = response.responseText;
													// process server response here
                                                                                                          $.notify("Deleted",{type:'danger'});
															reloadTable('detailspurchasetable')
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
                    {field: 'direct_po_number', title: 'Direct Purchase Order', width: 100, sortable: true, order: 'asc', editor: 'textbox'}, 
                    {field: 'direct_po_date', title: 'Purchase Order Date ', width: 100, sortable: true, order: 'asc', editor: 'textbox'}, 
                    {field: 'location', title: 'Location', width: 100, sortable: true, order: 'asc', editor: 'textbox'}, 
                    //{field: 'item_desc', title: 'Item Name', width: 100, sortable: true, order: 'asc', editor: 'textbox'}, 
                    {field: 'created_by', title: 'Created_by', width: 100, sortable: true, order: 'asc', editor: 'textbox'}, 
                    {field:'created_date',title:'Create Date' ,width: 100, sortable: true, order: 'asc',editer :'textbox'},
                    
                ]],
            });
    })  
    
    
</script>