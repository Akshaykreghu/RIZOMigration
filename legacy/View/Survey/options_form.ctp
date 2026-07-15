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
     deletedOptionValues = [];
    var options = {
        success: function (resp) {
            var response =  $.parseJSON(resp);
            if (response.status == true){
                $('#largeModalForm').modal('hide');
                $('#options_table').datagrid('reload');
                $.notify("Success", {
                    type: 'success',
                    allow_dismiss: false
                });
                action = '';    
            }else{
                var option_nameObj    = $('#option_form #option_items_name');
                option_nameObj.parents('div .col-sm-12').addClass('has-error');
                option_nameObj.parents('div .col-sm-7').append('<span class="help-block">Option name already exists.</span>');
            }
            
        }  
    };
    $('#option_form').on('submit', function (event) {
        event.preventDefault();
        $('.help-block').remove();
        $('.has-error').removeClass('has-error');
        if(validateOptionForm()){
            if (confirm("Do You Want To Save The Form")) {
                $('#deletedOptionValues').val(deletedOptionValues.toString());
                $('#option_form').ajaxSubmit(options);
            }    
        }
        
    });
    function deleteItem(obj){
        obj.parents("div .col-sm-12").remove();
        deletedOptionValues.push(obj.attr('option_id'));

    }
    function addItem(){
        var optionItemObj =   jQuery('#option_items_val');
        $('.addnewBlk').removeClass('has-error').removeClass('addnewBlk');
        $('.addnewBlkSpan').remove();
        if (optionItemObj.val() == ""){
            optionItemObj.parents('div .col-sm-12').addClass('has-error addnewBlk')
            optionItemObj.parents('div .col-sm-6').append('<span class="help-block addnewBlkSpan">Option item value is required.</span>');
           
        }else{
            var newItem = '<div class="col-sm-12" style="padding-bottom: 5px;">'+
                                '<label class="col-sm-4 control-label" ></label>'+
                                '<div class="col-sm-6">'+
                                     '<input class="form-control" placeholder="Please Enter Option Item Value" type="text" value="'+optionItemObj.val()+'" name="option_items_values[]" id="option_items_values[]" >'+
                                '</div>'+
                                '<div class="col-sm-1">'+
                                    '<button type="button" class="btn btn-danger  btn-sm" onclick="deleteItem($(this)); "><i class="fa fa-trash"></i></button>'+
                                '</div>'+
                            '</div>';
            $("#optionItems").append(newItem);
            optionItemObj.val('');
        }
        
    }   
</script>
<div class="modal-dialog" style="width: 100%; ">

    <!-- Modal content-->
    <div class="modal-content">
        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal">&times;</button>
            <h4 class="modal-title" style="    margin: 5px 28px 0; "><b>OPTIONS MANAGEMENT</b> </h4>
        </div>
        <div class="modal-body">
            <!-- Form starts -->
            <div id="option-save-response" class="">
                <form class="form-horizontal"  method="post" action="<?php echo $this->webroot; ?>Survey/saveOption" id="option_form" name="option_form">
                    <div class="modal-body"> 
                        <div class="form-group form-group-sm">
                            <div class="col-sm-12">
                                <label class="col-sm-4 control-label" >Survey Type<label style="color:red">*</label></label>
                                <div class="col-sm-7">
                                    <select  id="survey_type" name="survey_type" class="form-control js-example-basic-single" onchange="getSurveyCategory(this)">
                                            <option   value="0">Choose Survey</option>
                                            <?php foreach ($survey_types as $key => $value) {
                                            ?>                              
                                                <option <?php echo (($survey_type_id == $value['survey_type']['type_pkey']) && $option_id != "")?  "selected='selected'" : "";?> value="<?php echo $value['survey_type']['type_pkey']; ?>">
                                                    <?php echo  $value['survey_type']['type_name']; ?>
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
                                <label class="col-sm-4 control-label" >Survey Category<label style="color:red">*</label></label>
                                <div class="col-sm-7">
                                    <select  id="survey_category" name="survey_category" class="form-control js-example-basic-single" onchange="getOptionOrder(this)">
                                            <option  value="0">Choose Survey Category</option>
                                        </select>
                                </div>
                            </div>
                        </div>
                        <div class="form-group form-group-sm">
                            <div class="col-sm-12">
                                <label class="col-sm-4 control-label" >Option Name<label style="color:red">*</label></label>
                                <div class="col-sm-7">
                                    <input class="form-control" placeholder="Please Enter Option Name" type="text" value='<?php echo isset($o_name) ? $o_name : ""; ?>' name="option_items_name" id="option_items_name"  >
                                </div>
                            </div>
                        </div>
                        <div class="form-group form-group-sm">
                            <div class="col-sm-12">
                                <label class="col-sm-4 control-label" for="item_desc">Option Order<label style="color:red">*</label></label>
                                <div class="col-sm-7">
                                     <select id="option_items_order" name="option_items_order" class="form-control js-example-basic-single" >
                                            <option value="0"> Choose Option Order </option>
                                        </select>
                                </div>
                            </div>
                        </div>
                        <div class="form-group form-group-sm">
                            <div class="col-sm-12">
                                <label class="col-sm-4 control-label" >Option Item Type<label style="color:red">*</label></label>
                                <div class="col-sm-7">
                                    <select id="option_item_type" name="option_item_type" class="form-control js-example-basic-single" >
                                            <option value="0"> Choose Option Item Type </option>
                                            <!--<option value="Check_box" <?php echo ( isset($o_type) && $o_type == 'Check_box')?  "selected='selected'" : "";?> > Check Box </option>-->
                                            <!--<option value="Input_box" <?php echo ( isset($o_type) && $o_type == 'Input_box')?  "selected='selected'" : "";?>> Input Box</option>-->
                                            <option value="Text"  <?php echo ( isset($o_type) && $o_type == 'Text')?  "selected='selected'" : "";?>> Text Box</option>
                                            <!--<option value="Radio_button"  <?php echo ( isset($o_type) && $o_type == 'Radio_button')?  "selected='selected'" : "";?>> Radio Button</option>-->
                                            <option value="List_box"  <?php echo ( isset($o_type) && $o_type == 'List_box')?  "selected='selected'" : "";?>> List Box</option>
                                             <option value="Image"  <?php echo ( isset($o_type) && $o_type == 'Image')?  "selected='selected'" : "";?>> Image</option>
                                             <option value="Date"  <?php echo ( isset($o_type) && $o_type == 'Date')?  "selected='selected'" : "";?>> Date</option>
                                             <option value="Signature"  <?php echo ( isset($o_type) && $o_type == 'Signature')?  "selected='selected'" : "";?>> Signature</option>
                                    </select>
                                </div>
                                
                            </div>
                        </div>
                        <div class="form-group form-group-sm">
                            <div class="col-sm-12">
                                <label class="col-sm-4 control-label" >Option Item Value<label style="color:red">*</label></label>
                                <div class="col-sm-6">
                                     <input class="form-control" placeholder="Please Enter Option Item Value" type="text"  name="option_items_val" id="option_items_val"  >
                                </div>
                                 <div class="col-sm-1">
                                    <button type="button" class="btn btn-success  btn-sm" onclick="addItem(); ">
                                        <i class="fa fa-plus"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                        <div id="optionItems" class="form-group form-group-sm">
                            <?php echo isset($optionItemValueHtml)?$optionItemValueHtml:'';?>
                        </div>
                    </div>

                    <div class="modal-footer">
                        <button type="button" class="btn btn-danger" data-dismiss="modal">Cancel</button>
                        <button type="submit" id="btn-submit" class="btn btn-primary">Save</button>
                    </div>
                    
                    <input  type="hidden" value='<?php echo isset($option_id) ? $option_id : ""; ?>' name="option_id" id="option_id" >
                    <input  type="hidden"  name="prev_option_order" id="prev_option_order" >
                    <input  type="hidden"  name="deletedOptionValues" id="deletedOptionValues" >
                </form>
                <!-- Tax Head Detail Form -->

            </div>

            <!-- form ends-->
        </div>
    </div>
</div>