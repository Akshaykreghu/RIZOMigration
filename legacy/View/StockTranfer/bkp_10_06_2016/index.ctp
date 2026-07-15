
<h1 class="page-header">Stock Transfer</h1>
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
                            <table id="stocktable">
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
        $('#stocktable').datagrid({
            url: 'StockTranfer/stocklist',
            rownumbers: true,
            title: "Stock Transfer",
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
                        var url = 'StockTranfer/form';
                        $('#modalDiv').load(url,function(){
                            $('#modalDiv').modal('show');
                        });
                    }
              }, {
                    iconCls: 'icon-edit',
                    text: "Edit",
                    handler: function () {
                        var row = $('#stocktable').datagrid('getSelected');
                        //console.log(row);
                        if (row) {
                            var stock_tranfer_pkey = row.stock_tranfer_pkey;
                             var url = 'StockTranfer/form/'+ stock_tranfer_pkey;
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
                        var row = $('#stocktable').datagrid('getSelected');
                        if (row) {
                                  var r=confirm("Do You Want  To Remove The Selected Item")
                                     if(r==true){
                                  var stock_tranfer_pkey = row.stock_tranfer_pkey;
                                                                $.ajax({ 
											 url:"StockTranfer/delete",
													data : {
													stock_tranfer_pkey : stock_tranfer_pkey
													},
													success : function(response) {
													//var text = response.responseText;
													// process server response here
                                                                                                          $.notify("Deleted",{type:'danger'});
															reloadTable('stocktable')
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
                    {field: 'adjustment_code', title: 'Adjustment Code', width: 100, sortable: true, order: 'asc', editor: 'textbox'}, 
                    {field: 'adjustment_date', title: 'Adjustment Date', width: 100, sortable: true, order: 'asc', editor: 'textbox'}, 
                    {field: 'from_store', title: 'From Store', width: 100, sortable: true, order: 'asc', editor: 'textbox'}, 
                    //{field: 'item_desc', title: 'Item Name', width: 100, sortable: true, order: 'asc', editor: 'textbox'}, 
                    {field: 'created_by', title: 'Created_by', width: 100, sortable: true, order: 'asc', editor: 'textbox'}, 
                    {field:'created_date',title:'Create Date' ,width: 100, sortable: true, order: 'asc',editer :'textbox'},
                    
                ]],
            });
    })  
    
    
</script>