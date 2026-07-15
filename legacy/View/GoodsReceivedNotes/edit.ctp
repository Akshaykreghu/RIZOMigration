<style>
    .pull-left {
        float: right !important;
    }
</style>

<script>
  
    </script>

<div class="modal-dialog" style="width: 1200px ; ">

    <!-- Modal content-->
    <div class="modal-content">
        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal">&times;</button>
            <h4 class="modal-title"></h4><?php   //  debug($arr_att);  ?>
        </div>
        <div class="modal-body">
            <!-- Form starts -->
            <div id="" class="">
                <form class="form-horizontal" id="Purchase_form" method="post" action="<?php echo $this->webroot; ?>GoodsReceivedNotes/save" >
                    <div role="tabpanel" class="tab-pane" id="div-" name="">

                       <div class="form-group form-group-sm">
                            <div class="col-xs-4">
                                <label class="col-md-4 control-label" >Po Number</label>
                                <div class="col-md-7">
                                    
                                    <input id="po_number" name="po_number" value="<?php echo isset($arr_att['0']['purchaseorder']['po_number']) ? $arr_att['0']['purchaseorder']['po_number'] : ''; ?>" type="text"  class="form-control input-md" readonly="readonly">
                                </div>
                            </div>
                            <div class="col-xs-3">
                                <label  class="col-md-4 control-label" >PO date<label style="color:red">*</label></label>
                                <div class="col-md-8">
                                    <input id="" name="po_date" value="<?php echo isset($arr_att['0']['purchaseorder']['po_date']) ? $arr_att['0']['purchaseorder']['po_date'] : ''; ?>" type="text"  class="form-control input-md"  readonly="readonly">
                                </div> 
                            </div>
                            <div class=col-xs-4">
                                <label  class="col-md-2 control-label" >Expected Date</label>
                                <div class="col-md-3">
                                    <input  name="expected_date" value="<?php echo isset($arr_att['0']['purchaseorder']['expected_date']) ? $arr_att['0']['purchaseorder']['expected_date'] : ''; ?>" type="text"  class="form-control input-md" readonly="readonly">
                                </div>
                            </div>
                        </div>

                        <div class="form-group form-group-sm">
                            <div class="col-xs-4">
                                <label class="col-md-4 control-label" >PO Type<label style="color:red;">*</label></label>
                                <div class="col-md-7">
                                    <input id="po_type" name="po_type" value="<?php echo isset($arr_att['0']['purchaseorder']['po_type']) ? $arr_att['0']['purchaseorder']['po_type'] : ''; ?>" type="text"  class="form-control input-md" readonly="readonly" >
                                </div>
                            </div>
                            <div class="col-xs-3">
                                <label  class="col-md-4 control-label" >Location</label>
                                <div class="col-md-8">
                                    <input id="location" name="location" value="<?php echo isset($arr_att['0']['purchaseorder']['location']) ? $arr_att['0']['purchaseorder']['location'] : ''; ?>" type="text"  class="form-control input-md"  readonly="readonly">
                                </div> 
                            </div>
                            <div class=col-xs-4">
                                <label  class="col-md-2 control-label" >Client Name</label>
                                <div class="col-md-3">
                                    <input id="client_name" name="client_name" value="<?php echo isset($arr_att['0']['purchaseorder']['client_name']) ? $arr_att['0']['purchaseorder']['client_name'] : ''; ?>" type="text"  class="form-control input-md"  readonly="readonly">
                                </div> 
                                
                            </div>
                        </div>
                        <div class="form-group form-group-sm">
                            <div class="col-xs-4">
                                <label class="col-md-4 control-label" >Supplier Code<label style="color:red;">*</label></label>
                                <div class="col-md-7">
                                    <input id="supplier_code" name="supplier_code" value="<?php echo isset($arr_att['0']['purchaseorder']['supplier_code']) ? $arr_att['0']['purchaseorder']['supplier_code'] : ''; ?>" type="text"  class="form-control input-md" readonly="readonly" >
                                </div>
                            </div>
                            <div class="col-xs-3">
                                <label  class="col-md-4 control-label" >remarks</label>
                                <div class="col-md-8">
                                    <input id="remarks" name="remarks" value="<?php echo isset($arr_att['0']['purchaseorder']['remarks']) ? $arr_att['0']['purchaseorder']['remarks'] : ''; ?>" type="text"  class="form-control input-md"  readonly="readonly">
                                </div> 
                            </div>
<!--                            <div class=col-xs-4">
                                <label  class="col-md-2 control-label" >Location</label>
                                <div class="col-md-8">
                                    <input id="" name="location" value="<?php //echo isset($arr_att['0']['purchaseorder']['location']) ? $arr_att['0']['purchaseorder']['location'] : ''; ?>" type="text"  class="form-control input-md"  readonly="readonly">
                                </div> 
                                
                            </div>-->
                        </div>
                        <div class="form-group form-group-sm">
                            <div class="col-md-20" > </div>
                        </div>

                        <div  style="border: 1px solid #DCDCDC" style="padding: 0px 20px 10px 19px;">
                            <div class="col-xs-4">
                            </div>
                            <div class="modal-title" style="font-family:verdana; color:black; size:12px">Purchase Order Details</div>
                            <div class="form-group" id="div-criteria1" style="padding: 0px 20px 10px 19px;">
                                <div class="col-md-12" >
<!--                                    <div class="pull-right ">
                                        <a class="btn btn-info btn-sm active"  onclick="addOneReportCriteria(1);">Add New</a>

                                    </div>-->

                                </div>
                                <div class="form-group form-group-sm">
                                    <div class="col-md-12" > </div>
                                </div>
                                <table class="table table-bordered">
                                    <thead>
                                        <tr>
                                            <th>Item Name</th> 
                                            <th>Required</th>
                                            <th>ordered Qty</th>
                                            <th>Current Stock</th>
                                            <th>Re Order Level</th> 
                                            <th>Package<label style="color:red;">*</label></th>
                                            




                                        </tr>
                                    </thead>

                                    <?php
                                    if (isset($arr_att) && !empty($arr_att)) {
                                        $i = 1;
                                      
                                        foreach ($arr_att as $value) {
                                            $data = count($value);
                                            //debug($value);
                                            ?>
                                            <tbody>
                                                <tr>
                                                    <td>
                                                        <select id="item_code"   name="item_code[]" style = "width:100%;" onchange="getitemfordata1(this,<?php echo $i; ?>);">
                                                            <option value="" >-select-</option>
                                                            <?php
                                                            foreach ($all_item as $getitem) {
                                                                $selected = ($value['gritemdetails']['item_code'] == $getitem['Item']['item_master_pkey']) ? 'selected="selected"' : '';
                                                                echo '<option value="' . $getitem['Item']['item_master_pkey'] . '" ' . $selected . '>' . $getitem['Item']['item_desc'] . '   </option>';
                                                            }
                                                            ?>
                                                        </select>
                                                    </td>
<!--                                                    <td><input type = "text"  value = "<?php //echo isset($value['gritemdetails']['unit']) ? $value['gritemdetails']['unit'] : ''; ?>"  style = "width:100%;" readonly="readonly"></td>-->
                                                    <td><input type = "text"  value = "<?php echo isset($value['gritemdetails']['ordering_qty']) ? $value['gritemdetails']['ordering_qty'] : ''; ?>"  name = "ordering_qty[]" id = "ordering_qty"  style="width:100%" readonly="readonly" ></td>

                                                    <td><input type = "text"  value = "<?php echo isset($value['gritemdetails']['required_qty']) ? $value['gritemdetails']['required_qty'] : ''; ?>" name ="required_qty[]" id = "required_qty" style = "width:100%;" ></td>
                                                    <td><input type = "text"  value = "<?php echo isset($value['gritemdetails']['current_stock']) ? $value['gritemdetails']['current_stock'] : ''; ?>" name = "current_stock[]" id = "current_stock" style = "width:100px" readonly="readonly"></td>
                                                    <td><input type = "text"  value = "<?php echo isset($value['gritemdetails']['re_order_level']) ? $value['gritemdetails']['re_order_level'] : ''; ?>" name = "re_order_level[]" id = "re_order_level" style = "width: 100px" readonly="readonly"></td>
                                                    <td><input type = "text"  value = "<?php echo isset($value['gritemdetails']['package']) ? $value['gritemdetails']['package'] : ''; ?>" name = "package[]" id = "package" style = "width: 100px" readonly="readonly"></td>
                                            <input type = "hidden" value = "<?php  echo isset($value['gritemdetails']['gr_item_pkey']) ? $value['gritemdetails']['gr_item_pkey'] : ''; ?>" id = "gr_item_pkey" name = "gr_item_pkey[]">
                                            </tr>
                                            </tbody> 
        <?php
        $i++;
    }
} else {
    ?>
                                                              
<?php }
?>
                                </table>





                                <div class="table-responsive" style="padding:0px 3px -1px 19px; margin-top:-20px;" id="getmeterial"  style="display:">


                                </div>






                            </div>

                        </div>
                        <div class="modal-footer">
<!--                            <input type="hidden" name="po_pkey" >-->
                            <input type="hidden" value="<?php  echo isset($arr_att['0']['goodsrecevednotes']['grn_pkey']) ? $arr_att['0']['goodsrecevednotes']['grn_pkey'] : ''; ?>" id="grn_pkey" name="grn_pkey">
                            <input type="hidden" value="<?php  echo isset($arr_att['0']['gritemdetails']['po_fkey']) ? $arr_att['0']['gritemdetails']['po_fkey'] : ''; ?>" id="po_fkey" name="po_fkey">
                            <input type = "hidden" value = "<?php  echo isset($value['gritemdetails']['grn_fkey']) ? $value['gritemdetails']['grn_fkey'] : ''; ?>" id = "grn_fkey" name = "grn_fkey">
                            <button type="submit" id="btn-submit" class="btn btn-primary">Save</button>
                            <button type="button" class="btn btn-danger" data-dismiss="modal">Cancel</button>
                            
                        </div>
                    </div>



                </form>


                <!-- Tax Head Detail Form -->

            </div>



            <!-- form ends-->
        </div>
    </div>
</div>

</div>
<script>
    
     $('#Purchase_form').on('submit', function (event) {
        event.preventDefault();
        
        if(confirm("Do You Want To Save The Form")){
        $('#Purchase_form').ajaxSubmit(options);
    }
    });
     var options = {
        success: function (resp) {
//             var row = $('#purchaselist').datagrid('getSelected');
//                        //console.log(row);
//                    
//                        
//                        
              $('#modalDiv').modal('hide');
//            $('#purchaselist').datagrid('reload');
//            
//         var pk  =  $("#grn_fkey").val();
//         //alert(pk);
//         
           // loadtable(pk,1);
            
            $.notify("Success", {
                type: 'success',
                allow_dismiss: false
            });
        }  // post-submit callback
    };
       jQuery(document).ready(function () { 
     
     
    });
//        
//        function test(){
//          
//            
// var data=   $("#form-user-master").serialize();
//   // alert(data)
//
//     $.ajax({
//								  type: "POST",
//                                                                  data: data,
//                                                                  url : '',
//                      			success : function(response) {
//                                            alert(response);
//					 	var pk = $.parseJSON(response).pk;  
//                                                  loadtable(pk,1);
//                                  
//                                   var url = 'PurchaseOrder/form/'+ mr_pkey +'/'+ pk ;
//                                                            $('#modalDiv').load(url, function () {
//                                                                $('#modalDiv').modal('show');
//                                                            });
//                                                    
//							}
//							});
//}
    

 function  loadtable(pk,rowindex){
             $.ajax({
								url : livesite+ 'PurchaseOrder/loadtable/'+pk+'/'+rowindex,
                      			success : function(response) {
					 	//alert(response);
                                                        var data=response;
                                                        var div_data='';
                                                        div_data +="<div>"+data+"</div>"
                                                      $("#table_appnd").html(div_data);
                                                    
							}
							});
            
        } 

        
        
        
    
        
        
     
</script>