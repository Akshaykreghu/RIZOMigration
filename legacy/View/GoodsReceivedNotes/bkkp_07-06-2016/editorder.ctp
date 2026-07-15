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

          
          <?php // debug($arr_material_master);?>
           <form class="form-horizontal" id="form_edit_order" method="post" action="<?php echo $this->webroot;?>MaterialRequest/editordersave" method="POST" >
             
                    <div class="modal-body">
                        <div class="form-group">
                            <div class="col-md-10">
                                <label style="text-align:left;" class="col-md-5 control-label" >Item Name<label style="color:red;">*</label></label>

                                <div class="col-md-7">
                                    <select id="item_code"  class="form-control" name="item_code"  required="reduired">
                                      <option value="" >-select-</option>
                                   <?php
                                      foreach ($all_item as $value) {
                                         
                                          $selected = ($arr_material_master['0']['mrdetails']['item_code'] == $value['Item']['item_master_pkey']) ? 'selected="selected"' : '';
                                          echo '<option value="' . $value['Item']['item_master_pkey'] . '" ' . $selected . '>'.$value['Item']['item_desc'] .'   </option>';
                                      }
                                      ?>
                                  </select>
                                </div>
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <div class="col-md-10">
                                <label style="text-align:left;" class="col-md-5 control-label" >Required Qty<label style="color:red;">*</label><label style="color:red;"></label></label>
                                <div class="col-md-7">
                                   <input type="" id="required_qty" class="form-control" name="required_qty" required="required" value="<?php echo  isset($arr_material_master['0']['mrdetails']['required_qty']) ?$arr_material_master['0']['mrdetails']['required_qty'] : '' ; ?>" />   
                                </div>
                            </div>
                        </div>
                        <div class="form-group">
                            <div class="col-md-10">
                                <label style="text-align:left;" class="col-md-5 control-label" >Incoming Date<label style="color:red;">*</label><label style="color:red;"></label></label>
                                <div class="col-md-7">
                                   <input type="" id="exp_date" class="form-control" name="incoming_date" value="<?php echo  isset($arr_material_master['0']['mrdetails']['incoming_date']) ?$arr_material_master['0']['mrdetails']['incoming_date'] : '' ; ?>" />   
                                </div>
                            </div>
                        </div>
                        
                        
                        
                       
                        <div class="modal-footer">
                            <input type="hidden" id="mr_fkey" class="form-control" value="<?php echo $arr_material_master[0]['mrdetails']['mr_fkey']; ?>" name="mr_fkey"/>
                           <input type="hidden" id="mr_details_pkey" class="form-control" value="<?php echo $arr_material_master[0]['mrdetails']['mr_details_pkey']; ?>" name="mr_details_pkey"/>                    
                            <button type="button" class="btn btn-danger" data-dismiss="modal">Cancel</button>
                            <button type="submit" id="btn-submit" class="btn btn-primary">Save</button>
                        </div>
        </div>
                            

        
    </form>
          
          
     
                            

        
     </div>
    <!-- Tax Head Detail Form -->
   
</div>
          
               
          
<!-- form ends-->
      </div>
           
 <script>
  
    
    $(document).ready(function () {
        $('#exp_date').datepicker({
            dateFormat: 'yyyy-mm-dd'
        });
    });
</script>