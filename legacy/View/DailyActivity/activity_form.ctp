
<div class="modal-dialog">

    <!-- Modal content-->
    <div class="modal-content">
        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal">&times;</button>
            <h4 class="modal-title" style="    margin: 5px 28px 0; "><b><?php echo $title; ?></b></h4>
        </div>
      <div class="modal-body">
          <!-- Form starts -->
  <div id="" class="">
      <form class="form-horizontal" id="form-user-expense" action="<?php echo $this->webroot; ?>DailyActivity/activity_save" method="POST" >
       
                    <div class="modal-body">
                        <div class="form-group">
                          <div class="col-md-12">
                          <label style="text-align:left;" for="out_date" class="col-sm-4 control-label">Select Project<label style="color:red;">*</label><label style="color:red;"></label></label>
                          <div class="col-md-7">
                          <select id="project" style="width: 100%; " name="project" class="form-control js-example-basic-single" required="required">
                          <?php foreach ($site as $value) { ?>
                          <option <?php if(isset($arr_data[0]['project_activity']['project_fkey']) && $value['site']['site_pkey'] == $arr_data['0']['project_activity']['project_fkey']){ echo 'selected="selected"'; } ?> value="<?php echo $value['site']['site_pkey']; ?>">
                              <?php echo $value['site']['site_name'].'-'.$value['site']['site_id'] ;?></option>
                          <?php } ?>
                          </select>
                          </div> 
                          </div>
                        </div>
                        <div class="form-group">
                            <div class="col-md-12">
                                <label style="text-align:left;" class="col-md-4 control-label" >Working Date<label style="color:red;">*</label><label style="color:red;"></label></label>
                                <div class="col-md-7">
                                    <input class="form-control" placeholder=" Work Detail Date" type="text" value='<?php echo isset($arr_data["0"]["project_activity"]["date"]) ? $arr_data["0"]["project_activity"]["date"] : ""; ?>' name="work_date" id="work_date" readonly >
                                </div>
                            </div>
                        </div>
                         <div class="form-group">
                            <div class="col-md-12">
                                <label style="text-align:left;" class="col-md-4 control-label" >Work Details<label style="color:red;">*</label><label style="color:red;"></label></label>
                                <div class="col-md-7">
                                  <input id="work_detail" name="work_detail" autocomplete="off" value="<?php echo isset($arr_data['0']['project_activity']['work_detail']) ? $arr_data['0']['project_activity']['work_detail'] : '' ;?>" type="text" class="form-control"  required="required">
                                </div> 
                            </div>
                        </div>
                      </div>
                    <div class="modal-footer">
                        <input type="hidden" id="activity_pkey" name="activity_pkey" value="<?php echo isset($arr_data[0]['project_activity']['activity_pkey']) ? $arr_data[0]['project_activity']['activity_pkey'] : '' ;?>" />
                        <button type="submit" id="btn-submit" class="btn btn-primary">Save</button>
			<button type="button" class="btn btn-danger" data-dismiss="modal">Cancel</button>
                    </div>
          </form>
        </div>
            
    <!-- Tax Head Detail Form -->
   
</div>
           
<!-- form ends-->
      </div>
           
    </div>

<script>
  
      var options = {
        success: function (resp) {
          var success = $.parseJSON(resp).success;
          var msg = $.parseJSON(resp).msg;
            // alert(resp);
            if (success == false) {
                        alert("Work detail already added.");
                       // $('#btn-submit').html('Save').prop('disabled', true);                      
                        return;
                    }

            else{
            
               $.notify("Success",{              
                type: 'success',
                allow_dismiss: false

            });

              $('#modalForm').modal('hide');
              $('#att_table').datagrid('reload');
            }
            // $('#expensetable').datagrid('reload');
            // $.notify("Success", {               
            //     type: 'success',
            //     allow_dismiss: false
            // });
        }  // post-submit callback
    };
      $('#form-user-expense').on('submit', function (event) {
        event.preventDefault();
        if(confirm("Do You Want To Save The Form")){
        $('#form-user-expense').ajaxSubmit(options);
    }
    });
      $('#expense_type_code').on('change',function(){
        checkIfExpensecodeExists();
    })
    
    function checkIfExpensecodeExists(callback){        
        var expense_type_code = $('#expense_type_code').val();
        //var id = $('#id').val();
        $.ajax({
            url: 'ExpenseType/checkexpensetypecodeexists/',
            type: 'POST',
            data: {
                expense_type_code: expense_type_code
            },
            success: function (resp)
            {
                if(resp > 0){      
                    alert("Expense Code Already Exists!!");
                    $('#expense_type_code').val('');
                }else{
                    if(typeof callback === 'function'){
                        callback.call();
                    }
                }
            }
        });
    }
    $('#expense_type_name').on('change',function(){
        checkIfExpensenameExists();
    })
    
    function checkIfExpensenameExists(callback){        
        var expense_type_name  = $('#expense_type_name').val();
        //var id = $('#id').val();
        $.ajax({
            url: 'ExpenseType/checkexpensetypenameexists/',
            type: 'POST',
            data: {
                expense_type_name : expense_type_name 
            },
            success: function (resp)
            {
                if(resp > 0){      
                   alert("Expense Name Already Exists!!");
                    $('#expense_type_name').val('');

                }else{
                     // $('#btn-submit').html('Save').prop('disabled', false);
                    if(typeof callback === 'function'){
                        callback.call();
                    }
                }
            }
        });
    }
    $('#work_date').datepicker({
        format: 'yyyy-mm-dd',
        autoclose: true
    });
    
    </script>

