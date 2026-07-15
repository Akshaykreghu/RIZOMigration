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
            
        $('#materialtable').datagrid('reload');
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
 $('#div-criteria'+newIndex).load('MaterialRequest/addnewrow');
}


</script>

<div class="modal-dialog" style="width: 1200px ; ">

    <!-- Modal content-->
    <div class="modal-content">
      <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal">&times;</button>
            <h4 class="modal-title">MATERIAL REQUEST</h4><?php  //debug($arr_att); ?>
      </div>
      <div class="modal-body">
          <!-- Form starts -->
  <div id="" class="">
 <form class="form-horizontal" id="form-user-master" method="post" action="<?php echo $this->webroot; ?>MaterialRequest/save" >
            <div role="tabpanel" class="tab-pane" id="div-" name="">
      
                       <div class="form-group form-group-sm">
                            <div class="col-xs-4">
                            <label class="col-md-4 control-label" >Material Request Code</label>
                                <div class="col-md-7">
                                    <input id="mr_code" name="mr_code" value="<?php echo  isset($arr_att['0']['material_request']['mr_code']) ? $arr_att['0']['material_request']['mr_code']: ''; ?>" type="text"  class="form-control input-md" >
                                </div>
                            </div>
                            <div class="col-xs-3">
                                <label  class="col-md-4 control-label" >Po Type<label style="color:red">*</label></label>
                                <div class="col-md-8">
                                    <input id="po_type" name="po_type" value="<?php  echo  isset($arr_att['0']['material_request']['po_type']) ? $arr_att['0']['material_request']['po_type'] : ''; ?>" type="text"  class="form-control input-md" required="re" >
                                </div> 
                            </div>
                            <div class=col-xs-4">
                            <label  class="col-md-2 control-label" >MR date</label>
                                <div class="col-md-3">
                                    <input id="start_date" placeholder="select date" name="mr_date" value="<?php echo  isset($arr_att['0']['material_request']['mr_date']) ? $arr_att['0']['material_request']['mr_date'] :'' ; ?>" type="text"  class="form-control input-md datepicker" data-date-format="yyyy-mm-dd"> 
                                </div>
                            </div>
                        </div>
                <div class="form-group form-group-sm">
                            <div class="col-xs-4">
                            <label class="col-md-4 control-label" >Client Name</label>
                                <div class="col-md-7">
                                    <input id="client_name" name="client_name" value="<?php echo isset($arr_att['0']['material_request']['client_name'])? $arr_att['0']['material_request']['client_name'] :''; ?>" type="text"  class="form-control input-md" >
                                </div>
                            </div>
                            <div class="col-xs-3">
                            <label  class="col-md-4 control-label" >Client Po No</label>
                                <div class="col-md-8">
                                    <input id="client_po_no" name="client_po_no" value="<?php  echo  isset($arr_att['0']['material_request']['client_po_no']) ?$arr_att['0']['material_request']['client_po_no']: '' ; ?>" type="text"  class="form-control input-md" >
                                </div> 
                            </div>
                            <div class=col-xs-4">
                                <label  class="col-md-2 control-label" >Location<lable style="color:red;">*</lable></label>
                                <div class="col-md-3">
                                    <input id="location" name="location" value="<?php echo  isset($arr_att['0']['material_request']['location']) ?$arr_att['0']['material_request']['location'] : '' ; ?>" type="text"  class="form-control input-md" required="required" >
                                </div>
                            </div>
                        </div>
                <div class="form-group form-group-sm">
                            <div class="col-xs-4">
                                <label class="col-md-4 control-label" >Store Code<lable style="color:red;">*</lable></label>
                                <div class="col-md-7">
                                    <select id="store_code" class="form-control" name="store_code">
                                      <option value="" >---select---</option>
                                   <?php
                               
                                      foreach ($all_store as $value) {
                                         
                                          $selected = ($arr_att['0']['material_request']['store_code'] == $value['Store']['store_master_pkey']) ? 'selected="selected"' : '';
                                          echo '<option value="' . $value['Store']['store_master_pkey'] . '" ' . $selected . '>'.$value['Store']['store_code'] .'   </option>';
                                      }
                                      ?>
                                  </select>
<!--                                    <input id="store_code" name="store_code" value="<?php //echo  isset($arr_att['0']['material_request']['store_code']) ?$arr_att['0']['material_request']['store_code'] : '' ; ?>" type="text"  class="form-control input-md" >-->
                                </div>
                            </div>
                            <div class="col-xs-3">
                            <label  class="col-md-4 control-label" >Att</label>
                                <div class="col-md-8">
                                    <input id="att" name="att" value="<?php echo  isset($arr_att['0']['material_request']['att']) ?$arr_att['0']['material_request']['att'] : '' ; ?>" type="text"  class="form-control input-md" >
                                </div> 
                            </div>
                            <div class=col-xs-4">
                            <label  class="col-md-2 control-label" >Remark</label>
                                <div class="col-md-3">
                                    <input id="remarks" name="remarks" value="<?php echo  isset($arr_att['0']['material_request']['remarks']) ?$arr_att['0']['material_request']['remarks'] : '' ; ?>" type="text"  class="form-control input-md" >
                                </div>
                            </div>
                        </div>
                        <div class="form-group form-group-sm">
                            <div class="col-md-20" > </div>
                        </div>
                       
                <div  style="border: 1px solid #DCDCDC" style="padding: 0px 20px 10px 19px;">
                    <div class="col-xs-4">
                    </div>
                    <div class="modal-title" style="font-family:verdana; color:black; size:12px">Material Request Details</div>
                    <div class="form-group" id="div-criteria1" style="padding: 0px 20px 10px 19px;">
                        <div class="col-md-12" >
                            <div class="pull-right ">
                                <a class="btn btn-info btn-sm active"  onclick="addOneReportCriteria(1);">Add New</a>
                                
                            </div>
                            
                        </div>
                         <div class="form-group form-group-sm">
                            <div class="col-md-12" > </div>
                        </div>
                        <table class="table table-bordered">
                            <thead>
                                <tr>
                                    <th>Item Name</th>
                                    <th>Required Qty<label style="color:red;">*</label></th>
                                    <th>Current Stock</th>
                                    <th>Incoming Qty:</th>
                                    <th>Incoming Date</th> 
                                    <th>Re Order Level</th>
                                    <th>Item Description</th>
                                    <th>Package</th>
                                 
                                </tr>
                            </thead>
                            
                                    <?php
                           // debug($arr_att);  
                            if (isset($arr_att) && !empty($arr_att)) {
                                foreach ($arr_att as $value) {
                                    $data = count($value);
                                    ?>
                                    <tbody>
                                        <tr>
                                            <td><select id="" name="item_code[]" style = "width:135px" required="required">
                                      <option value="" >-select-</option>
                                
                                   <?php
                                      foreach ($all_item as $getitem) {
                                         
                                          $selected = ($value['mr_details']['item_code'] == $getitem['Item']['item_master_pkey']) ? 'selected="selected"' : '';
                                          echo '<option value="' . $getitem['Item']['item_master_pkey'] . '" ' . $selected . '>'.$getitem['Item']['item_desc'] .'   </option>';
                                      }
                                      
                                      ?>
                                  </select></td>
                                            <td><input type = "text" value = "<?php echo isset($value['mr_details']['required_qty']) ? $value['mr_details']['required_qty'] : ''; ?>" name = "required_qty[]" id = "required_qty" required="reduired" style = "width:100%"></td>
                                            <td><input type = "text" value = "<?php echo isset($value['mr_details']['current_stock']) ? $value['mr_details']['current_stock'] : ''; ?>" name ="current_stock[]" id = "current_stock" style="width:112px" ></td>
                                            <td><input type = "text" value = "<?php echo isset($value['mr_details']['incoming_qty']) ? $value['mr_details']['incoming_qty'] : ''; ?>" name = "incoming_qty[]" id = "incoming_qty" style="width:115px" ></td>
                                            <td><input type = "text" value = "<?php echo isset($value['mr_details']['incoming_date']) ? $value['mr_details']['incoming_date'] : ''; ?>" name = "incoming_date[]" id ="end_date" class="datepicker" data-date-format="yyyy-mm-dd" placeholder = "select date"></td>
                                            <td><input type = "text" value = "<?php echo isset($value['mr_details']['re_order_level']) ? $value['mr_details']['re_order_level'] : ''; ?>"name = "re_order_level[]" id = "re_order_level" ></td>
                                            <td><input type = "text" value = "<?php echo isset($value['mr_details']['item_description']) ? $value['mr_details']['item_description'] : ''; ?>" name = "item_description[]" id = "item_description"></td>
                                            <td><input type = "text" value = "<?php echo isset($value['mr_details']['package']) ? $value['mr_details']['package'] : ''; ?>" name = "package[]" id = "package" ></td>
                                    <input type = "hidden" value = "<?php echo isset($value['mr_details']['mr_details_pkey']) ? $value['mr_details']['mr_details_pkey'] : ''; ?>" id = "mr_details_pkey" name = "mr_details_pkey[]">
                                    </tr>
                                    </tbody>
        <?php
    }
} else {
    ?> 
                                <tbody>
                                    <tr>
                                        <td><select id="" name="item_code[]" style = "width:135px" required="reduired">
                                      <option value="" >-select-</option>
                                   <?php
                                      foreach ($all_item as $value) {
                                         
                                          //$selected = ($arr_att['0']['poitemorder']['store_code'] == $value['Store']['store_master_pkey']) ? 'selected="selected"' : '';
                                          echo '<option value="' . $value['Item']['item_master_pkey'] . '" ' . $selected . '>'.$value['Item']['item_desc'] .'   </option>';
                                      }
                                      ?>
                                  </select></td>
                                        <td><input type = "text"  name = "required_qty[]" id = "required_qty" style = "width: 100%" required="reduired"></td>
                                        <td><input type = "text"  name ="current_stock[]" id = "current_stock"  style = "width: 100%"  ></td>
                                        <td><input type = "text"  name = "incoming_qty[]" id = "incoming_qty"  style = "width: 100%" ></td>
                                        <td><input type = "text"  name = "incoming_date[]" id = "end_date" placeholder = "select date" class="datepicker" data-date-format="yyyy-mm-dd"></td>
                                        <td><input type = "text" name = "re_order_level[]" id = "re_order_level" ></td>
                                        <td><input type = "text"  name = "item_description[]" id = "item_description"></td>
                                        <td><input type = "text"  name = "package[]" id = "package" ></td>
                                      
                                        
                                    </tr>
                                </tbody>                      
<?php }
?>
                        </table>
                    </div>

                </div>
                    <div class="modal-footer">
                        <input type="hidden" value="<?php echo  isset($arr_att['0']['material_request']['mr_pkey']) ?$arr_att['0']['material_request']['mr_pkey'] :'' ; ?>" id="mr_pkey" name="mr_pkey">  
                        <button type="Reset" class="btn btn-default" onclick="$('#largeModalForm').modal('hide');">Cancel</button>
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

<script>
      $(document).ready(function(){
      //alert('hi')
        $('#start_date').datepicker({
            dateFormat:'yyyy-mm-dd'
        });
    });
    
    $(document).ready(function(){
        $('#end_date').datepicker({
            dateFormat:'yyyy-mm-dd'
        });
    });
     $(document).ready(function(){
        $('#update_date').datepicker({
            dateFormat:'yyyy-mm-dd'
        });
        
    }); 

</script>