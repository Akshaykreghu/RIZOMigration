<style>
    @keyframes FadeIn {
  from {
    background-color: #7bebbd;
  }
  
  to {
    background-color: white;
  }
}
</style>
<script>
    var options = {
        success: function (resp) {
            var response =  $.parseJSON(resp);
            if (response.status == true){
                $('#largeModalForm').modal('hide');
                $('#options_value_table').datagrid('reload');
                $.notify("Success", {
                    type: 'success',
                    allow_dismiss: false
                });
                action = '';    
            }else{
                alert(response.msg);
            }
            
        }  
    };
    $('#frm_option_item_values_form').on('submit', function (event) {
        event.preventDefault();
        $('.help-block').remove();
        $('.has-error').removeClass('has-error');
        if(validateOptionItemValueForm()){
            if (confirm("Do You Want To Save The Form")) {
                $('#frm_option_item_values_form').ajaxSubmit(options);
            }    
        }
        
    });
</script>
<div class="modal-dialog" style="width: 100%; ">

    <!-- Modal content-->
    <div class="modal-content">
        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal">&times;</button>
            <h4 class="modal-title" style="    margin: 5px 28px 0; "><b>OPTIONS VALUES MANAGEMENT</b> </h4>
        </div>
        <div class="modal-body">
            <!-- Form starts -->
            <div id="option-save-response" class="">
                <form class="form-horizontal"  method="post" action="<?php echo $this->webroot; ?>Survey/saveOptionItemValue" id="frm_option_item_values_form" name="frm_option_item_values_form">
                    <div class="modal-body"> 
                        <div class="form-group form-group-sm">
                            <div class="col-sm-12">
                                <label class="col-sm-4 control-label" >Option Item<label style="color:red">*</label></label>
                                <div class="col-sm-7">
                                    <select  id="option_item" name="option_item" class="form-control js-example-basic-single" onchange="getOptionsItemValueOrder(this)">
                                            <option   value="0">Choose Option Item</option>
                                            <?php foreach ($option_items as $key => $value) {
                                            ?>     
                                            <option  value="<?php echo $value['option_items']['o_id']; ?>">
                                                    <?php echo  $value['option_items']['o_name']; ?>
                                                </option>                         

                                            <?php 
                                                } 
                                            ?>
                                        </select>
                                </div>
                            </div>
                        </div>
                        <div class="form-group form-group-sm">
                            <div class="col-sm-12">
                                <label class="col-sm-4 control-label" >Option Item Type<label style="color:red">*</label></label>
                                <div class="col-sm-7">
                                    <select id="item_type" name="item_type" class="form-control js-example-basic-single" >
                                            <option value="0"> Choose Option Item Type </option>
                                            <option value="Check_box"> Check Box </option>
                                            <option value="Input_box"> Input Box</option>
                                            <option value="Text_box"> Text Box</option>
                                            <option value="Radio_button"> Radio Button</option>
                                            <option value="List_box"> List Box</option>
                                    </select>
                                </div>
                                
                            </div>
                        </div>
                        <div class="form-group form-group-sm">
                            <div class="col-sm-12">
                                <label class="col-sm-4 control-label" for="item_value">Option Item Value <label style="color:red">*</label></label>
                                <div class="col-sm-7">
                                    <input class="form-control" placeholder="Please Enter Option Item Value " type="text" value='<?php echo isset($item_value) ? $item_value : ""; ?>' name="item_value" id="item_value"  >
                                </div>
                            </div>
                        </div>
                        <div class="form-group form-group-sm">
                            <div class="col-sm-12">
                                <label class="col-sm-4 control-label" for="item_order">Option Item Value Order<label style="color:red">*</label></label>
                                <div class="col-sm-7">
                                     <select id="item_order" name="item_order" class="form-control js-example-basic-single" >

                                            <option value="0"> Choose Option Item Value Order </option>
                                                
                                        </select>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-danger" data-dismiss="modal">Cancel</button>
                        <button type="submit" id="btn-submit" class="btn btn-primary">Save</button>
                    </div>
                    
                    <input  type="hidden" value='<?php echo isset($option_value_id) ? $option_value_id : ""; ?>' name="option_value_id" id="option_value_id" >
                    <input  type="hidden"  name="prev_option_item_order" id="prev_option_item_order" >
                </form>
                <!-- Tax Head Detail Form -->

            </div>

            <!-- form ends-->
        </div>
    </div>
</div>