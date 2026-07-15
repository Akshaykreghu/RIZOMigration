<style>
    .pull-left {
  float: right !important;
}
</style>
<script>
 
    $.validate({
        form: '#form-user-master'
    });
    var options = {
        success: function (resp) {
            $('#modalDiv').modal('hide');
            
        $('#grntable').datagrid('reload');
        $('#purchasetable').datagrid('reload');
              $.notify("Success",{type:'success'});
        }  // post-submit callback
    };

    $('#form-user-master').on('submit', function (event) {
        event.preventDefault();
        if (confirm(" Do You Want  To Save The Form")) {
            $('#form-user-master').ajaxSubmit(options);
            
          
        }
    });


    
function loadCriteriaItems(index){
    var criteria = $('#select-criteria'+index).val();
    $('#hidden-criteria'+index).val(criteria);
    $('#div-items-criteria'+index).load(livesite+'Reports/loadcriteriaitems/'+index+'/'+criteria);
}
function addOneReportCriteria(index){
    $('#form-showreport .link-removecriterias').remove();
    var newIndex = index+1;
    $('<div class="form-group" id="div-criteria'+newIndex+'"></div>').insertAfter($('#div-criteria'+index));
 $('#div-criteria'+newIndex).load('GoodsReceivedNotes/addnewrow');
}


</script>

<div class="modal-dialog" style="width: 1200px ; ">

    <!-- Modal content-->
    <div class="modal-content">
      <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal">&times;</button>
            <h4 class="modal-title">Goods Received Notes</h4><?php // debug($arr_grn); ?>
      </div>
      <div class="modal-body">
          <!-- Form starts -->
  <div id="" class="">
 <form class="form-horizontal" id="form-user-master" method="post" action="<?php echo $this->webroot; ?>GoodsReceivedNotes/save" >
            <div role="tabpanel" class="tab-pane" id="" name="">
      
                       <div class="form-group form-group-sm">
                            <div class="col-xs-4">
                            <label class="col-md-4 control-label" >PO Number</label>
                                <div class="col-md-7">
                                    <input id="po_number" name="po_number" value="<?php  echo  isset($arr_grn['0']['grnorder']['po_number']) ? $arr_grn['0']['grnorder']['po_number']: ''; ?>" type="text"  class="form-control input-md" >
                                </div>
                            </div>
                            <div class="col-xs-3">
                                <label  class="col-md-4 control-label" >Po type<label style="color:red">*</label></label>
                                <div class="col-md-8">
                                    <input id="po_type" name="po_type" value="<?php echo isset($arr_grn['0']['grnorder']['po_type']) ? $arr_grn['0']['grnorder']['po_type'] : ''; ?>" type="text"  class="form-control input-md" >
                                </div> 
                            </div>
                            <div class=col-xs-4">
                            <label  class="col-md-2 control-label" >Client Name</label>
                                <div class="col-md-3">
                                    <input id="client_name"  name="client_name" value="<?php echo isset($arr_grn['0']['grnorder']['client_name']) ? $arr_grn['0']['grnorder']['client_name'] : ''; ?>" type="text"  class="form-control input-md" >
                                </div>
                            </div>
                        </div>
                
                 <div class="form-group form-group-sm">
                            <div class="col-xs-4">
                                <label class="col-md-4 control-label" >PO Date<label style="color:red;">*</label></label>
                                <div class="col-md-7">
                                    <input id="start_date" name="po_date" value="<?php echo isset($arr_grn['0']['grnorder']['po_date']) ? $arr_grn['0']['grnorder']['po_date'] : ''; ?>" type="text"  class="form-control input-md" required="required" >
                                </div>
                            </div>
                            <div class="col-xs-3">
                                <label  class="col-md-4 control-label" >Expected Date</label>
                                <div class="col-md-8">
                                    <input id="exp_date" placeholder="select date" name="expected_date" value="<?php echo isset($arr_grn['0']['grnorder']['expected_date']) ? $arr_grn['0']['grnorder']['expected_date'] : ''; ?>" type="text"  class="form-control input-md" >
                                </div> 
                            </div>
                            <div class=col-xs-4">
                            <label  class="col-md-2 control-label" >Location</label>
                                <div class="col-md-3">
                                    <input id="location" name="location" value="<?php echo isset($arr_grn['0']['grnorder']['location']) ? $arr_grn['0']['grnorder']['location'] : ''; ?>" type="text"  class="form-control input-md"  >
                                </div>
                            </div>
                        </div>
                
                 <div class="form-group form-group-sm">
                            <div class="col-xs-4">
                                <label class="col-md-4 control-label" >Supplier Name<lable style="color:red;">*</lable></label>
                                <div class="col-md-7">
                                        <input id="supplier_name" name="supplier_name" value="<?php  echo isset($arr_grn['0']['grnorder']['supplier_name']) ? $arr_grn['0']['grnorder']['supplier_name']: ''; ?>" type="text"  class="form-control input-md"  required="reduired">
                                </div>  
                            </div>
                            <div class="col-xs-3">
                                <label  class="col-md-4 control-label" >GR Create</label>
                                <div class="col-md-8">
                                    <input id="gr_create" name="gr_create" value="<?php  echo  isset($arr_grn['0']['grnorder']['gr_create']) ? $arr_grn['0']['grnorder']['gr_create'] : ''; ?>" type="text"  class="form-control input-md" >
                                </div> 
                            </div>
                            <div class=col-xs-4">
                                <label  class="col-md-2 control-label" >GR date<label style="color:red;">*</label></label>
                                <div class="col-md-3">
                                    <input id="gr_date"  name="gr_date" value="<?php  echo  isset($arr_grn['0']['grnorder']['gr_date']) ? $arr_grn['0']['grnorder']['gr_date'] :'' ; ?>" type="text"  class="form-control input-md" >
                                </div>
                            </div>
                        </div>
                <div class="form-group form-group-sm">
                            <div class="col-xs-4">
                                <label class="col-md-4 control-label" >Remark</label>
                                <div class="col-md-8">
                                    <input id="remark" name="remark" value="<?php  echo  isset($arr_grn['0']['grnorder']['remark']) ? $arr_grn['0']['grnorder']['remark']: ''; ?>" type="text"  class="form-control input-md"  >
                                </div>
                            </div>

                            
                        </div>
                        <div class="form-group form-group-sm">
                            <div class="col-md-20" > </div>
                        </div>
                       
                <div  style="border: 1px solid #DCDCDC" style="padding: 0px 20px 10px 19px;">
                    <div class="col-xs-4">
                    </div>
                    <div class="modal-title" style="font-family:verdana; color:black; size:12px">Purchase Order Details</div>
                    <div class="form-group" id="div-criteria1" style="padding: 0px 20px 10px 19px;">
<!--                        <div class="col-md-12" >
                            <div class="pull-right ">
                                <a class="btn btn-info btn-sm active"  onclick="addOneReportCriteria(1);">Add New</a>
                                
                            </div>
                            
                        </div>-->
                         <div class="form-group form-group-sm">
                            <div class="col-md-12" > </div>
                        </div>
                        <table class="table table-bordered">
                                    <thead>
                                        <tr>
                                            <th>Item Code</th> 
                                            <th>Item Description</th>
                                            <th>uom</th>
                                            <th>Pending Qty:</th> 
                                            <th>Ordering Qty<label style="color:red;">*</label></th>
                                            <th>PO Rate<label style="color:red;">*</label></th>
                                            <th>PO value</th>  




                                        </tr>
                                    </thead>

                                    <?php
                                    if (isset($arr_grn) && !empty($arr_grn)) {
                                        $i = 1;
                                      
                                        foreach ($arr_grn as $value) {
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
                                                                $selected = ($value['grnitem']['item_code'] == $getitem['Item']['item_master_pkey']) ? 'selected="selected"' : '';
                                                                  echo '<option value="' . $getitem['Item']['item_master_pkey'] . '" ' . $selected . '>' . $getitem['Item']['item_desc'] . '   </option>';
                                                            }
                                                            ?>
                                                        </select>
                                                    </td>

                                                    <td><input type = "text"  value = "<?php echo isset($value['grnitem']['item_description']) ? $value['grnitem']['item_description'] : ''; ?>"name ="item_description[]" id = "current_stock" style = "width:100%;"></td>
                                                    <td><input type = "text"  value = "<?php echo isset($value['grnitem']['uom']) ? $value['grnitem']['uom'] : ''; ?>"name = "uom[]" id = "incoming_qty"  style="width:60px" ></td>
                                                    <td><input type = "text"  value = "<?php echo isset($value['grnitem']['ordering_qty']) ? $value['grnitem']['ordering_qty'] : ''; ?>" name ="ordering_qty[]" id = "orderqty<?php echo $i; ?>" style = "width: 100px" readonly="readonly"></td>
                                                    <td><input type = "text"  value = "<?php echo isset($value['grnitem']['order_qty']) ? $value['grnitem']['order_qty'] : ''; ?>"name ="order_qty[]" id ="orderedqty<?php echo $i; ?>" style = "width:100px" onchange="valuecheck(<?php echo $i; ?>);"></td>
                                                    <td><input type = "text"  value = "<?php echo isset($value['grnitem']['po_rate']) ? $value['grnitem']['po_rate'] : ''; ?>" name ="po_rate[]" id = "unit" style = "width: 100px"></td>
                                                    <td><input type = "text"  value = "<?php echo isset($value['grnitem']['po_value']) ? $value['grnitem']['po_value'] : ''; ?>" name ="po_value[]" id = "unit" style = "width: 100px"></td>
                                            <input type = "hidden" value = "<?php  echo isset($value['grnitem']['gr_item_pkey']) ? $value['grnitem']['gr_item_pkey'] : ''; ?>" id = "gr_item_pkey" name = "gr_item_pkey[]">
                                            </tr>
                                            </tbody>
                                            
       
                            <?php
        $i++;
    }
} else {
    ?>
                                        <tbody>
                                            <tr>
                                                <td>

                                                    <select id="item_code" name="item_code[]"  onchange="" style = "width:100%;">
                                                        <option value="" >-select-</option>
    <?php
//debug($all_item);
    foreach ($all_item as $value) {

        //$selected = ($arr_list['0']['directpod']['store_code'] == $value['Store']['store_master_pkey']) ? 'selected="selected"' : '';
        echo '<option value="' . $value['Item']['item_master_pkey'] . '" ' . $selected . '>' . $value['Item']['item_desc'] . '   </option>';
    }
    ?>
                                                    </select>

                                                </td>
                                                <td><input type = "text"  name ="item_description[]" id = "current_stock" style = "width:100%;"></td>
                                                <td><input type = "text"  name = "uom[]" id = "incoming_qty"  style="width:60px" ></td>
                                                <td><input type = "text"  name = "ordering_qty[]" value=""id ="requiralll" style ="width:100px"  >
                                                <td><input type = "text"  name = "order_qty[]" id = "unit1" style = "width: 100px" onchange="valuecheck();" ></td>
                                                <td><input type = "text"  name = "po_rate[]" id = "unit" style = "width: 100px"></td>
                                                <td><input type = "text"  name = "po_value[]" id = "unit" style = "width: 100px"></td>
                                            </tr>

                                        </tbody>                      
<?php }
?>
                        </table>
                    </div>

                </div>
                    <div class="modal-footer">
                        <input type="hidden" id="grn_pkey" name="grn_pkey" value="<?php  echo  isset($arr_grn['0']['grnorder']['grn_pkey']) ? $arr_grn['0']['grnorder']['grn_pkey']: ''; ?>"  class="form-control input-md"  >
                        <input type="hidden" id="po_pkey" name="po_pkey" value="<?php  echo  isset($arr_grn['0']['grnorder']['po_pkey']) ? $arr_grn['0']['grnorder']['po_pkey']: ''; ?>"  class="form-control input-md"  >
                        <button type="button" class="btn btn-danger" data-dismiss="modal">Cancel</button>
                        <button type="submit" id="btn-submit" class="btn btn-primary">Save</button>
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
            
            
            
      $(document).ready(function(){
        $('#start_date').datepicker({
            dateFormat:'yyyy-mm-dd'
        });
    });
    $(document).ready(function(){
        $('#gr_date').datepicker({
            dateFormat:'yyyy-mm-dd'
        });
    });
    
  
  
       function getitemfordata(){
            var Item_code = $('#item_code').val();
          //  alert(Item_code);
        var request = $.ajax({
            url: "PurchaseOrder/getitem/"+Item_code,
            method: "POST",
            dataType: "html"
        });

        request.done(function( msg ) {
         //   alert(msg);
            $('#package').val(msg);          
        });
        
        request.fail(function( jqXHR, textStatus ) {
            
            alert( "Request failed: " + textStatus );
        });
    }
 
 
 function valuecheck(i)
 {
   //alert(i);
   var chek=$("#orderqty"+i).val();
    var add=$("#orderedqty"+i).val();
   
    if(chek < add)
        {
            alert("check value");
            $("#orderedqty"+i).val('');
         }
 }
 

</script>