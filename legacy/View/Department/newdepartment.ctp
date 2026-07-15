






<form  class="form-horizontal" name="deptform" id="deptform" method="post" ng-submit="save()">

<div class="modal-header">
    <h3 class="modal-title"><?php echo (isset($data['id']) && $data['id']  != 0) ?"Edit Department":"New Department"; ?></h3>
</div>
<div class="modal-body">
<div style="padding: 10px">

<div class="form-group">
                <label>Department Code</label>
                <input type="text" name="dept_code" id="dept_code" value="<?php echo (isset($data['dept_code']))? $data['dept_code']: ""; ?>" class="form-control"  ng-pattern="/^[a-zA-Z0-9_]$/" required >
              </div>
              
              
               <div class="form-group">
                <label>Address </label>
                <input type="text"  class="form-control" name="dept_name" id="dept_name"  required  value="<?php echo (isset($data['dept_name']))? $data['dept_name']: ""; ?>" />

                
              </div>
              



<input type="hidden" name="id" value="<?php echo (isset($data['id']))? $data['id']: 0; ?>" />
	 
 

 
 
</div>
</div>
<div class="modal-footer">                  
    <button class="btn btn-default" ng-click="cancel()">Cancel</button>
    <button class="btn btn-primary" >Save</button>
</div>

</form>