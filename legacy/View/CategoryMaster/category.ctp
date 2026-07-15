<script>
      var options = {
        success: function (resp) {
            var Response =  $.parseJSON(resp);
            var type = '';
            if(Response.success == false){
                type = 'danger';
            }else{
                type = 'success';
              $('#modalForm').modal('hide');
            }
            $('#categorytable').datagrid('reload');
            $.notify(Response.msg, {
                type: type,
                allow_dismiss: false
            });
        }  // post-submit callback
    };
      $('#form-user-master').on('submit', function (event) {
        event.preventDefault();
        if(confirm("Do You Want To Save The Form")){
        $('#form-user-master').ajaxSubmit(options);
    }
    });
    </script>

<div class="modal-dialog">

    <!-- Modal content-->
    <div class="modal-content">
        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal">&times;</button>
            <h4 class="modal-header"><?php echo $title; ?></h4>
        </div>
      <div class="modal-body">
          <!-- Form starts -->
  <div id="" class="">
      <form class="form-horizontal" id="form-user-master" action="<?php echo $this->webroot; ?>CategoryMaster/save" method="POST" >
                    
          
           
            
          
                    <div class="modal-body">
                       <div class="form-group">
                            <div class="col-md-10">
                                <label style="text-align:left;" class="col-md-4 control-label" >Name <label style="color:red;">*</label><label style="color:red;"></label></label>
                                <div class="col-md-7">
                                    <input id="code" name="code" autocomplete="off" value="<?php echo isset($result['0']['category_master']['code']) ? $result['0']['category_master']['code'] : '' ;?>" type="text" class="form-control"   required="required">
                                </div>
                            </div>
                        </div>
                         <div class="form-group">
                            <div class="col-md-10">
                                <label style="text-align:left;" class="col-md-4 control-label" >Description</label>
                                <div class="col-md-7">
                                  <input id="description" name="description" autocomplete="off" value="<?php echo isset($result['0']['category_master']['description']) ? $result['0']['category_master']['description'] : '' ;?>" type="text" class="form-control"   >
                                </div> 
                            </div>
                        </div>
                       
                        
                        
                       
                    <div class="modal-footer">
                        <input type="hidden" id="category_pkey" name="category_pkey" value="<?php echo isset($result['0']['category_master']['category_pkey']) ? $result['0']['category_master']['category_pkey'] : '' ;?>" />
                         <button type="submit" id="btn-submit" class="btn btn-primary">Save</button>
						<button type="button" class="btn btn-danger" data-dismiss="modal">Cancel</button>
                       
                    </div>
        </div>
                            

        
    </form>
    <!-- Tax Head Detail Form -->
   
</div>
          
               
          
<!-- form ends-->
      </div>
           
    </div>

</div>
