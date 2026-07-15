
<h1 class="page-header">Goods Received Notes </h1>
<div class="row">
  <section class="content">
    <div class="row">
        <div class="col-md-12">
            <!-- DIRECT CHAT DANGER -->
            <div class="box ">
                
                <div class="box-body">
                    <!-- Employee import form -->
                    <form class="form-horizontal" method="post" action="" id="purchaseordertable">
                        <div class="row">
<!--                    <div class="form-group form-group-sm">
                            <div class="col-xs-4">
                            <label class="col-md-4 control-label" >Client Name</label>
                                <div class="col-md-7">
                                    <select id="client_name" class="form-control" onclick="dataload();" name="client_name">
                                      <option value="" >--All---</option>
                                  <?php //foreach ($all_meterial as  $value) {
                                      
 
                                      ?>                              
                                        <option  value="<?php //echo $value['MaterialRequest']['client_name']; ?>"><?php //echo $value['MaterialRequest']['client_name']; ?></option>
                                    <?php// } ?>
                                  </select>
                                </div>
                            </div>
                            <div class="col-xs-4">
                            <label class="col-md-4 control-label" >Store Code</label>
                                <div class="col-md-7">
                                    <select id="store_master_pkey" class="form-control" name="store_master_pkey" onclick="dataload();" >
                                      <option value="" >--All---</option>
                                  <?php //foreach ($all_store as  $value) {
   
                                      ?>                              
                                        <option  value="<?php //echo $value['Store']['store_master_pkey']; ?>"><?php //echo $value['Store']['store_code']; ?></option>
                                    <?php // } ?>
                                  </select>
                                </div>
                            </div>
                            <div class="col-xs-3">
                                <label  class="col-md-4 control-label" >Location</label>
                                <div class="col-md-7">
                                    <select id="location" class="form-control" onclick="dataload();" name="location" >
                                      <option value="" >--All---</option>
                                  <?php// foreach ($all_meterial as  $value) {
   
                                      ?>                              
                                        <option  value="<?php //echo $value['MaterialRequest']['location']; ?>"><?php //echo $value['MaterialRequest']['location']; ?></option>
                                    <?php //} ?>
                                  </select>
                                </div>
                            </div>
                            
                        </div>-->
                            
                        </div>
                         
                        
                    </form>
                     <div class="box-body">
                       
                             <div>
                <table id="purchasetable">

                </table><!-- /.box-body -->
                  
                              </div>
            </div>
                      <div>
                <table id="grntable">

                </table>
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
        $('#purchasetable').datagrid({
            url: 'GoodsReceivedNotes/purchaselist',
            rownumbers: true,
            title: "Purchase Order List",
            fitColumns: true,
            singleSelect: true,
            autoRowHeight: false,
            pagination: true,
            width: '100%',
            pageSize: 10,
            toolbar: [{
                    iconCls: 'icon-add',
                    text: "ADD TO GRN",
                    handler: function () {
                        var row = $('#purchasetable').datagrid('getSelected');
                        //console.log(row);
                          if (row) {
                            var po_pkey = row.po_pkey;
                             var url = 'GoodsReceivedNotes/form/'+ po_pkey;
                            $('#modalDiv').load(url, function () {
                                $('#modalDiv').modal('show');
                            });
                        }
                        else
                        {
                            alert("Please Choose A Purchase Order");
                        }
                    }
                }],
           columns:[[
                    {field: 'po_number', title: 'Purchase Order Number', width: 100, sortable: true, order: 'asc', editor: 'textbox'}, 
                    {field: 'client_name', title: 'Client Name', width: 100, sortable: true, order: 'asc', editor: 'textbox'}, 
                    {field: 'po_date', title: 'Purchase Order Date', width: 100, sortable: true, order: 'asc', editor: 'textbox'}, 
                    {field:'expected_date',title:'Expected Date' ,width: 100, sortable: true, order: 'asc',editer :'textbox'},
                    {field: 'location', title: 'Location', width: 100, sortable: true, order: 'asc', editor: 'textbox'}, 
                    {field: 'Remark', title: 'remark', width: 100, sortable: true, order: 'asc', editor: 'textbox'}, 
                   
                    
                ]],
            });
    })
    
   
    $(document).ready(function () {
        $('#grntable').datagrid({
            url: 'GoodsReceivedNotes/grnlist',
            rownumbers: true,
            title: "Goods Received Notes",
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
                        var url = 'GoodsReceivedNotes/form';
                        $('#modalDiv').load(url,function(){
                            $('#modalDiv').modal('show');
                        });
                    }
              }, {
                    iconCls: 'icon-edit',
                    text: "Edit",
                    handler: function () {
                        var row = $('#grntable').datagrid('getSelected');
                        //console.log(rowgrntable
                        if (row) {
                            var grn_pkey = row.grn_pkey;
                             var url = 'GoodsReceivedNotes/form/'+ grn_pkey+"/1";
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
                        var row = $('#grntable').datagrid('getSelected');
                        if (row) {
                                  var r=confirm("Do You Want  To Remove The Selected Item")
                                    if(r==true){
                                  var grn_pkey = row.grn_pkey;
                                                                $.ajax({ 
											 url:"GoodsReceivedNotes/grndelete",
													data : {
													grn_pkey : grn_pkey
													},
													success : function(response) {
													//var text = response.responseText;
													// process server response here
                                                                                                          $.notify("Deleted",{type:'danger'});
															reloadTable('grntable')
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
                    {field: 'supplier_name', title: 'Supplier Name', width: 100, sortable: true, order: 'asc', editor: 'textbox'}, 
                    {field: 'gr_create', title: 'Good Receive Create', width: 100, sortable: true, order: 'asc', editor: 'textbox'},
                    {field: 'gr_date', title: 'Good Receive Date', width: 100, sortable: true, order: 'asc', editor: 'textbox'},
                    {field: 'remark', title: 'Remark', width: 100, sortable: true, order: 'asc', editor: 'textbox'}, 
                    {field: 'created_by', title: 'Create By', width: 100, sortable: true, order: 'asc', editor: 'textbox'}, 
                    {field: 'created_date', title: 'Create Date', width: 100, sortable: true, order: 'asc', editor: 'textbox'}, 
                    
                ]],
            });
    })  
    
    
</script>