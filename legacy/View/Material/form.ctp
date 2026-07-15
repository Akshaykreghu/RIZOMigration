
<div class="modal-dialog">

    <!-- Modal content-->
    <div class="modal-content">
      <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal">&times;</button>
            <h4 class="modal-title">STORE MASTER</h4><?php // debug($arr_att); ?>
      </div>
      <div class="modal-body">
          <!-- Form starts -->
  <div id="" class="">
 <form class="form-horizontal" id="form-user-master" method="post" action="<?php echo $this->webroot; ?>Store/save" >
                    
          
            <div role="tabpanel" class="tab-pane" id="div-quantitydetails" name="quantitydetails">
            
          
                    <div class="modal-body">
                       <div class="form-group">
                            <div class="col-md-10">
                            <label style="text-align:left;" class="col-md-4 control-label" >store Code</label>
                                <div class="col-md-7">
                                    <input id="store_code" name="store_code" value="<?php echo  isset($arr_att['0']['store_master']['store_code']) ? $arr_att['0']['store_master']['store_code']: ''; ?>" type="text"  class="form-control input-md" >
                                </div>
                            </div>
                        </div>
                        <div class="form-group">
                            <div class="col-md-10">
                            <label style="text-align:left;" class="col-md-4 control-label" >Store Location</label>
                                <div class="col-md-7">
                                    <input id="store_location" name="store_location" value="<?php  echo  isset($arr_att['0']['store_master']['store_location']) ? $arr_att['0']['store_master']['store_location'] : ''; ?>" type="text"  class="form-control input-md" >
                                </div> 
                            </div>
                        </div>
                        <div class="form-group">
                            <div class="col-md-10">
                            <label style="text-align:left;" class="col-md-4 control-label" >Address</label>
                                <div class="col-md-7">
                                    <input id="address" name="address" value="<?php echo  isset($arr_att['0']['store_master']['address']) ? $arr_att['0']['store_master']['address'] :'' ; ?>" type="text"  class="form-control input-md" >
                                </div>
                            </div>
                        </div><div class="form-group">
                            <div class="col-md-10">
                            <label style="text-align:left;" class="col-md-4 control-label" >City</label>
                                <div class="col-md-7">
                                    <input id="city" name="city" value="<?php echo  isset($arr_att['0']['store_master']['city'])? $arr_att['0']['store_master']['city'] :''; ?>" type="text"  class="form-control input-md" >
                                </div>
                            </div>
                        </div>
                        <div class="form-group">
                            <div class="col-md-10">
                            <label style="text-align:left;" class="col-md-4 control-label" for="qty_on_order">State</label>
                                <div class="col-md-7">
                                    <input id="state" name="state" value="<?php echo  isset($arr_att['0']['store_master']['state']) ?$arr_att['0']['store_master']['state']: '' ; ?>" type="text"  class="form-control input-md" >
                                </div>
                            </div>
                        </div>
                        <div class="form-group">
                            <div class="col-md-10">
                            <label style="text-align:left;" class="col-md-4 control-label" for="re_order_level">Pin Code</label>
                                <div class="col-md-7">
                                    <input id="pincode" name="pincode" value="<?php echo  isset($arr_att['0']['store_master']['pincode']) ?$arr_att['0']['store_master']['pincode'] : '' ; ?>" type="text"  class="form-control input-md" >
                                </div>
                            </div>
                        </div>
                       
                    <div class="modal-footer">
                        <input type="hidden" value="<?php echo  isset($arr_att['0']['store_master']['store_master_pkey']) ?$arr_att['0']['store_master']['store_master_pkey'] :'' ; ?>" id="store_master_pkey" name="store_master_pkey">   
                        <button type="Reset" class="btn btn-default" onclick="$('#largeModalForm').modal('hide');">Cancel</button>
                        <button type="submit" id="btn-submit" class="btn btn-primary">Save</button>
                    </div>
        </div>
                            

        </div>
    </form>
    <!-- Tax Head Detail Form -->
   
</div>
          
               
          
<!-- form ends-->
      </div>
           
    </div>

</div>
