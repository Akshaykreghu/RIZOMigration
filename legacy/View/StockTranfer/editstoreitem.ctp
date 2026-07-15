


<script>
    $('.datepicker').datepicker();
    var options = {
        success: function (resp) {
        var pid=    $.parseJSON(resp).pk;
                                 $('#modalDiv').modal('hide');
                                 loadtable(pid,1);
            $.notify("Success", {
                type: 'success',
                allow_dismiss: false
            });
        }  // post-submit callback
    };
    $('#form_edit_order').on('submit', function (event) { //alert('aaa');
        event.preventDefault(); 
        if (confirm("Do You Want To Save The Form")) {
            $('#form_edit_order').ajaxSubmit(options);
        }
    });
    $("#sales_price").change( function(){
    var data=    $("#sales_price option:selected").text(); 
    var datanew = data.split("-Rs-");
       $("#item_rate").val(datanew[1]);   
    });
    
</script>
<div class="modal-dialog">

    <!-- Modal content-->
    <div class="modal-content">
        
            <button type="button" class="close" data-dismiss="modal">&times;</button>
            <h4 class="modal-header">Edit</h4>
       
      <div class="modal-body">
          <!-- Form starts -->
  <div id="dfg" class="">
          <!-- Form starts -->

          
          <?php  //debug($arr_store_master);?>
           <form class="form-horizontal" id="form_edit_order" method="post" action="<?php echo $this->webroot;?>StockTranfer/editordersave" method="POST" >
             
                    <div class="modal-body">
<!--                        <div class="form-group">
                            <div class="col-md-10">
                                <label style="text-align:left;" class="col-md-5 control-label" >Item Name<label style="color:red;">*</label></label>

                                <div class="col-md-7">
                                    <select id="item_code"  class="form-control" name="item_code"  required="reduired">
                                      <option value="" >-select-</option>
                                   <?php
//                                      foreach ($all_item as $value) {
//                                         
//                                          $selected = ($arr_store_master['0']['stockitem']['item_code'] == $value['Item']['item_master_pkey']) ? 'selected="selected"' : '';
//                                          echo '<option value="' . $value['Item']['item_master_pkey'] . '" ' . $selected . '>'.$value['Item']['item_desc'] .'   </option>';
//                                      }
                                      ?>
                                  </select>
                                </div>
                            </div>
                        </div>-->
                        
                        <div class="form-group">
                            <div class="col-md-10">
                                <label style="text-align:left;" class="col-md-5 control-label" >Available Qty<label style="color:red;">*</label><label style="color:red;"></label></label>
                                <div class="col-md-7">
                                    <input type="number" id="available_qty" class="form-control" name="available_qty" required="required" value="<?php echo  isset($arr_store_master['0']['stockitem']['available_qty']) ?$arr_store_master['0']['stockitem']['available_qty'] : '' ; ?>"  readonly="readonly">   
                                </div>
                            </div>
                        </div>
                        <div class="form-group">
                            <div class="col-md-10">
                                <label style="text-align:left;" class="col-md-5 control-label" >Required Qty<label style="color:red;">*</label><label style="color:red;"></label></label>
                                <div class="col-md-7">
                                   <input type="number" id="requiredd_qty" class="form-control" name="required_qty" value="<?php echo  isset($arr_store_master['0']['stockitem']['required_qty']) ?$arr_store_master['0']['stockitem']['required_qty'] : '' ; ?>" onchange="valuecheck();" >   
                                </div>
                            </div>
                        </div>
<!--                        <div class="form-group">
                            <div class="col-md-10">
                                <label style="text-align:left;" class="col-md-5 control-label" >Transfer Stock<label style="color:red;">*</label><label style="color:red;"></label></label>
                                <div class="col-md-7">
                                    <input type="number" id="tranfer_stock" class="form-control" name="tranfer_stock" value="<?php // echo  isset($arr_store_master['0']['stockitem']['tranfer_stock']) ?$arr_store_master['0']['stockitem']['tranfer_stock'] : '' ; ?>"  />   
                                </div>
                            </div>
                        </div>-->
                        
                        
                        
                       
                        <div class="modal-footer">
                            <input type="hidden" id="stock_tranfer_fkey" class="form-control" value="<?php echo $arr_store_master[0]['stockitem']['stock_tranfer_fkey']; ?>" name="stock_tranfer_fkey"/>
                           <input type="hidden" id="stock_item_pkey" class="form-control" value="<?php echo $arr_store_master[0]['stockitem']['stock_item_pkey']; ?>" name="stock_item_pkey"/>                    
                            <button type="submit" id="btn-submit" class="btn btn-primary">Save</button>
                            <button type="button" class="btn btn-danger" data-dismiss="modal">Cancel</button>

                        </div>
        </div>
                            

        
    </form>
          
          
     
                            

        
     </div>
    <!-- Tax Head Detail Form -->
   
</div>
    </div>    
               
          
<!-- form ends-->
      </div>
           
 <script>
//check al value 

  function  valuecheck()
{
    var avil = $('#available_qty').val();
    var req = $('#requiredd_qty').val();
   
    if(avil < req)
    {
      alert("Please Check Enter Value");
      $("#requiredd_qty").val('');
      
    }
}

    
    $(document).ready(function () {
        $('#exp_date').datepicker({
            dateFormat: 'yyyy-mm-dd'
        });
    });
</script>