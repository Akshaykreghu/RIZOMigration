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
                $('#survey_category_table').datagrid('reload');
                $.notify("Success", {
                    type: 'success',
                    allow_dismiss: false
                });
                action = '';    
            }else{
                var data = response.data;
                var category_codeObj    = $('#form_survey_category #category_code');
                var category_nameObj    = $('#form_survey_category #category_name');
                for(i in data){
                    if (data[i] == "CODE"){
                        category_codeObj.parents('div .col-sm-12').addClass('has-error')
                        category_codeObj.parents('div .col-sm-7').append('<span class="help-block">Survey Head code already exists.</span>');
                    }
                    if (data[i] == "NAME"){
                        category_nameObj.parents('div .col-sm-12').addClass('has-error')
                        category_nameObj.parents('div .col-sm-7').append('<span class="help-block">Survey Head name already exists.</span>');
                    }
                }
            }
            
        }  
    };
    $('#form_survey_category').on('submit', function (event) {
        event.preventDefault();
        $('.help-block').remove();
        $('.has-error').removeClass('has-error');
        if(validateSurveyCategoryForm()){
            if (confirm("Do You Want To Save The Form")) {
                $('#form_survey_category').ajaxSubmit(options);
            }    
        }
        
    });
</script>
<div class="modal-dialog" style="width: 100%; ">

    <!-- Modal content-->
    <div class="modal-content">
        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal">&times;</button>
            <h4 class="modal-title" style="    margin: 5px 28px 0; "><b>SURVEY HEADS MANAGEMENT</b> </h4>
        </div>
        <div class="modal-body">
            <!-- Form starts -->
            <div id="survey-category-save-response" class="">
                <form class="form-horizontal"  method="post" action="<?php echo $this->webroot; ?>Survey/saveSurveyCategory" id="form_survey_category" name="form_survey_category">
                    <div class="modal-body"> 
                        <div class="form-group form-group-sm">
                            <div class="col-sm-12">
                                <label class="col-sm-4 control-label" >Survey Type <label style="color:red">*</label></label>
                                <div class="col-sm-7">
                                     <select  id="survey_type" name="survey_type" class="form-control js-example-basic-single" onchange="getCategoryOrder(this)">
                                            <option   value="0">Choose Survey</option>
                                            <?php foreach ($survey_types as $key => $value) {
                                            ?>                              
                                                <option <?php echo (($survey_type == $value['survey_type']['type_pkey']) && $category_pkey != "")?  "selected='selected'" : "";?> value="<?php echo $value['survey_type']['type_pkey']; ?>">
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
                                <label class="col-sm-4 control-label" >Survey Head Code<label style="color:red">*</label></label>
                                <div class="col-sm-7">
                                    <input class="form-control" placeholder="Please Enter Survey Head Code" type="text" value='<?php echo isset($category_code) ? $category_code : ""; ?>' name="category_code" id="category_code"  >
                                </div>
                            </div>
                        </div>
                        <div class="form-group form-group-sm">
                            <div class="col-sm-12">
                                <label class="col-sm-4 control-label" for="item_desc">Survey Head Name<label style="color:red">*</label></label>
                                <div class="col-sm-7">
                                    <input class="form-control" placeholder="Please Enter Survey Head Name" type="text" value='<?php echo isset($category_name) ? $category_name : ""; ?>' name="category_name" id="category_name"  >
                                </div>
                            </div>
                        </div>
                        <div class="form-group form-group-sm">
                            <div class="col-sm-12">
                                <label class="col-sm-4 control-label" for="item_desc">Survey Head Order<label style="color:red">*</label></label>
                                <div class="col-sm-7">
                                     <select id="category_order" name="category_order" class="form-control js-example-basic-single" >

                                            <option value="0"> Choose Survey Head Order </option>
                                                
                                        </select>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-danger" data-dismiss="modal">Cancel</button>
                        <button type="submit" id="btn-submit" class="btn btn-primary">Save</button>
                    </div>
                    
                    <input  type="hidden" value='<?php echo isset($category_pkey) ? $category_pkey : ""; ?>' name="category_pkey" id="category_pkey" >
                    <input  type="hidden"  name="prev_category_order" id="prev_category_order" >
                </form>
            </div>
        </div>
    </div>
</div>