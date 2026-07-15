 <script>
      var options = {
        success: function (resp) {
              $('#modalForm').modal('hide');
            $('#item_specification').datagrid('reload');
            $.notify("Success", {
                type: 'success',
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
    <div class="modal-content">
        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal">&times;</button>
            <h4 class="modal-title">Item Specification</h4>
        </div>
        <div class="modal-body">
            <form role="form" id="form-user-master" action="<?php echo $this->webroot; ?>ItemSpecification/save" method="POST">
            <input type="hidden" id="specification_pkey" name="specification_pkey" value="<?php echo isset($result['0']['item_specification']['specification_pkey']) ? $result['0']['item_specification']['specification_pkey'] : '' ;?>" />
            <div class="row">
                <div class="col-md-6">
                    <label for="categorycode">Category Code:<span style="color: red"> * </span></label>
                    <input id="category_code" name="category_code" autocomplete="off" value="<?php echo isset($result['0']['item_specification']['category_code']) ? $result['0']['item_specification']['category_code'] : '' ;?>" type="text" class="form-control"  placeholder="Category Code" required="required">
                </div> 
            </div>
            <div class="row">
                <div class="col-md-6">
                    <label for="description">Description:</label>
                    <input id="description" name="description" autocomplete="off" value="<?php echo isset($result['0']['item_specification']['description']) ? $result['0']['item_specification']['description'] : '' ;?>" type="text" class="form-control"  placeholder="Description" required="required">
                </div>
            </div>
            <div class="row">
                <div class="col-md-6">
                    <label for="itemspecification">Item Specification:</label>
                    <input id="item_specification" name="item_specification" autocomplete="off" value="<?php echo isset($result['0']['item_specification']['item_specification']) ? $result['0']['item_specification']['item_specification'] : '' ;?>" type="text" class="form-control"  placeholder="Item Specification" required="required">
                </div>
            </div>
        
       <div class="modal-footer">
        <button type="submit" class="btn btn-default" >Save </button>
        <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
        </div>
              
              
            </form>
            
        </div>
        
        
        
    </div>
    
    
    
</div>

