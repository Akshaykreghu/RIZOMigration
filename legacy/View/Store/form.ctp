<script>

    $.validate({
        form: '#form-user-master'
    });
    var options = {
        success: function (resp) {
//            var Response =  $.parseJSON(resp);
//            var type = '';
//            alert(Response.success);
//            if(Response.success == false){
//                type = 'danger';
//            }else{
//                type = 'success';
//              $('#modalForm').modal('hide');
//            }
            $('#modalForm').modal('hide');
            $('#storetable').datagrid('reload');
            $.notify(Response.msg, {
                type: type,
                allow_dismiss: false,
                showDuration: 6000
            });
        }  // post-submit callback
    };


    $('#form-user-master').on('submit', function (event) {
        event.preventDefault();
        if (confirm(" Do You Want  To Save The Form")) {
            //checkIfStoreExists(function(){
                $('#form-user-master').ajaxSubmit(options);
            //});
        }
    });
    
    function checkIfStorecodeExists(callback){        
        var store_code = $('#store_code').val();

        $.ajax({
            url: 'store/chkcategory/',
            type: 'POST',
            data: {
                store_code: store_code
            },
            success: function (resp)
            {  
                var respval = '';
                var respval = JSON.parse(resp);
            //     alert(respval.store_id);
            //    alert(respval.msg);
                if(respval.msg == '1'){
                
                // var msg = "store exists";
               // alert(resp);
                $('#errmsg1').html("Store code already exists");  
                $('#errmsg1').show();
                $('#store_code').val('');
                //return null;
            }else {
                     $('#errmsg1').html('');
                }
               
            }
        });
    }
    function checkIfStoreExists(callback){       
        var store_location =  $('#store_location').val();      

        $.ajax({
            url: 'store/chkcategory/',
            type: 'POST',
            data: {
                store_location: store_location
            },
            success: function (resp)
            {  
                var respval = '';
                var respval = JSON.parse(resp);
            //     alert(respval.store_id);
            //    alert(respval.msg);
                if(respval.msg == '2')
                   {
                 //    alert(respval.msg);
                       $('#errmsg2').html("Store location already exists");  
                       $('#errmsg2').show(); 
                       $('#store_location').val('');
                   }                  
                    else {
                      $('#errmsg2').html('');  
                }
               
            }
        });
    }
</script>
<div class="modal-dialog">

    <!-- Modal content-->
    <div class="modal-content">
        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal">&times;</button>
            <h4 class="modal-title"><?php echo $action; ?></h4>
        </div>
        <div class="modal-body">
            <!-- Form starts -->
            <div id="" class="">
                <form class="form-horizontal" id="form-user-master" method="post" action="<?php echo $this->webroot; ?>Store/save" >


                    <div role="tabpanel" class="tab-pane" id="div-quantitydetails" name="quantitydetails">


                        <div class="modal-body">
                            <div class="form-group">
                                <div class="col-md-10">
                                    <label style="text-align:left;" class="col-md-4 control-label" >Store Code <label style="color:red">*</label></label>

                                    <div class="col-md-7">
                                        <input type="hidden" name="store_master_pkey" value="<?php echo  isset($arr_att['0']['store_master']['store_master_pkey']) ? $arr_att['0']['store_master']['store_master_pkey']: ''; ?>" id="store_master_pkey" >
                                        <input id="store_code" name="store_code" value="<?php echo  isset($arr_att['0']['store_master']['store_code']) ? $arr_att['0']['store_master']['store_code']: ''; ?>" type="text"  class="form-control input-md" onchange="checkIfStorecodeExists(this);" required="required">
                                     <div id="errmsg1" style="color:red"> </div>
                                    </div>
                                </div>
                            </div>
                            <div class="form-group">
                                <div class="col-md-10">
                                    <label style="text-align:left;" class="col-md-4 control-label" >Store Location<label style="color:red">*</label></label>
                                    <div class="col-md-7">
                                        <input id="store_location" onchange="checkIfStoreExists(this);" name="store_location" value="<?php  echo  isset($arr_att['0']['store_master']['store_location']) ? $arr_att['0']['store_master']['store_location'] : ''; ?>" type="text"  class="form-control input-md" required="required">
                                   <div id="errmsg2" style="color:red"> </div>
                                    </div> 
                                </div>
                            </div>
                            <div class="form-group">
                                <div class="col-md-10">
                                    <label style="text-align:left;" class="col-md-4 control-label" >Address<label style="color:red">*</label></label>
                                    <div class="col-md-7">
                                        <input id="address" name="address" value="<?php echo  isset($arr_att['0']['store_master']['address']) ? $arr_att['0']['store_master']['address'] :'' ; ?>" type="text"  class="form-control input-md" required="required">
                                    </div>
                                </div>
                            </div><div class="form-group">
                                <div class="col-md-10">
                                    <label style="text-align:left;" class="col-md-4 control-label" >City<label style="color:red">*</label></label>
                                    <div class="col-md-7">
                                        <input id="city" name="city" value="<?php echo  isset($arr_att['0']['store_master']['city'])? $arr_att['0']['store_master']['city'] :''; ?>" type="text"  class="form-control input-md" required="required">
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
                                        <input id="pincode" name="pincode" value="<?php echo  isset($arr_att['0']['store_master']['pincode']) ?$arr_att['0']['store_master']['pincode'] : '' ; ?>" type="number"  class="form-control input-md" >
                                    </div>
                                </div>
                            </div>

                            <div class="modal-footer">
                                <input type="hidden" value="<?php echo  isset($arr_att['0']['store_master']['store_master_pkey']) ?$arr_att['0']['store_master']['store_master_pkey'] :'' ; ?>" id="store_master_pkey" name="store_master_pkey">   
                                <button type="button" class="btn btn-danger" data-dismiss="modal">Cancel</button>
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
