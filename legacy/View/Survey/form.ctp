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
                $('#survey_table').datagrid('reload');
                $.notify("Success", {
                    type: 'success',
                    allow_dismiss: false
                });
                action = '';    
            }else{
                 if (response.err.type_code == true){
                    var type_codeObj = $('#type_code');
                    type_codeObj.parents('div .col-sm-12').addClass('has-error')
                    type_codeObj.parents('div .col-sm-7').append('<span class="help-block">Survey type code is already exists.</span>');
                }
                if (response.err.type_name == true){
                    var type_nameObj = $('#type_name');
                    type_nameObj.parents('div .col-sm-12').addClass('has-error')
                    type_nameObj.parents('div .col-sm-7').append('<span class="help-block">Survey type name is already exists.</span>');
                }

            }
        }  
    };

    $('#survey_type_form').on('submit', function (event) {
        event.preventDefault();
        $('.help-block').remove();
        $('.has-error').removeClass('has-error');
        if(validateSurveyForm()){
            if (confirm("Do You Want To Save The Form")) {
                $('#survey_type_form').ajaxSubmit(options);
            }    
        }
        
    });
</script>
<div class="modal-dialog" style="width: 100%; ">

    <!-- Modal content-->
    <div class="modal-content">
        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal">&times;</button>
            <h4 class="modal-title" style="    margin: 5px 28px 0; "><b>SURVEY TYPE MANAGEMENT</b> </h4>
        </div>
        <div class="modal-body">
            <!-- Form starts -->
            <div id="empsetup-save-response" class="">
                <form class="form-horizontal"  method="post" action="<?php echo $this->webroot; ?>Survey/saveSurveyType" id="survey_type_form" name="survey_type_form">
                    <div class="modal-body"> 
                        <div class="form-group form-group-sm">
                            <div class="col-sm-12">
                                <label class="col-sm-4 control-label" >Survey Type Code<label style="color:red">*</label></label>
                                <div class="col-sm-7">
                                    <input class="form-control" placeholder="Please Enter Survey Type Code" type="text" value='<?php echo isset($type_code) ? $type_code : ""; ?>' name="type_code" id="type_code"  >
                                </div>
                            </div>
                        </div>
                        <div class="form-group form-group-sm">
                            <div class="col-sm-12">
                                <label class="col-sm-4 control-label" for="item_desc">Survey Type Name<label style="color:red">*</label></label>
                                <div class="col-sm-7">
                                    <input class="form-control" placeholder="Please Enter a Survey Type Name" type="text" value='<?php echo isset($type_name) ? $type_name : ""; ?>' name="type_name" id="type_name" >
                                </div>
                            </div>
                        </div>
                        <div class="form-group form-group-sm">
                            <div class="col-sm-12">
                                <label class="col-sm-4 control-label" for="equipment_type">Equipment Type<label style="color:red">*</label></label>
                                <div class="col-sm-7">
                                    <select id="equipment_type_fkey" name="equipment_type_fkey" class="form-control js-example-basic-single"  >
<!--                                            <option value="">All</option>-->
                                            <?php foreach ($equipments as $key => $value) { 
                                                
                                            ?>                              
                                                <option  <?php echo (($equipment_type_fkey == $value['equipment_type']['equipment_type_pkey']) && $type_pkey != "")?  "selected='selected'" : "";?> 
                                                value="<?php echo $value['equipment_type']['equipment_type_pkey']; ?>">
                                                    <?php echo $value[0]['equipment_name']; ?>
                                                </option>
                                            <?php 
                                                } 
                                            ?>
                                        </select>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-danger" data-dismiss="modal">Cancel</button>
                        <button type="submit" id="btn-submit" class="btn btn-primary">Save</button>
                    </div>
                    
                    <input  type="hidden" value='<?php echo isset($type_pkey) ? $type_pkey : ""; ?>' name="type_pkey" id="type_pkey" >
                </form>
                <!-- Tax Head Detail Form -->

            </div>

            <!-- form ends-->
        </div>
    </div>
</div>