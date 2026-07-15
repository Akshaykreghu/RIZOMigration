

<form  class="form-horizontal" name="brform" id="brform" method="post" ng-submit="save()">

<div class="modal-header">
    <h3 class="modal-title"><?php echo (isset($data['id']) && $data['id']  != 0) ?"Edit Branch":"New Branch"; ?></h3>
</div>
<div class="modal-body">
<div style="padding: 10px">
   <div class="form-group">
                <label>Branch Name <span class="star">*</span></label>
                <input type="text" name="name" id="name" value="<?php echo (isset($data['branch_name']))? $data['branch_name']: ""; ?>" class="form-control"  ng-pattern="/^[a-zA-Z0-9_]$/" required >
              </div>
              
              
               <div class="form-group">
                <label>Address <span class="star">*</span></label>
                
 <textarea class="form-control"   name="address" id="address" required><?php echo (isset($data['address']))? $data['address']: ""; ?></textarea> 
                
              </div>
              
              
              
               <div class="form-group">
                <label>State<span class="star">*</span></label>
                <input type="text"  name="state" id="state" value="<?php echo (isset($data['state']))? $data['state']: ""; ?>" class="form-control"  ng-pattern="/^[a-zA-Z0-9_-]{2,25}$/" required >
              </div>
              
              
              
               <div class="form-group">
                <label>City <span class="star">*</span></label>
                <input type="text"   name="city" id="city" value="<?php echo (isset($data['city']))? $data['city']: ""; ?>" class="form-control"  ng-pattern="/^[a-zA-Z0-9_-]{2,30}$/" required >
              </div>
              
              
               <div class="form-group">
                <label>Pincode <span class="star">*</span><em class="text-muted"></label>
                <input type="text" name="pincode" id="pincode" value="<?php echo (isset($data['pincode']))? $data['pincode']: ""; ?>" class="form-control"  ng-pattern="/^[0-9]{6,10}$/" required >
              </div>

	
	<input type="hidden" name="id" value="<?php echo (isset($data['id']))? $data['id']: 0; ?>" />
	 
 

 
 
</div>
</div>
<div class="modal-footer">                  
	<span class="star">*</span>= Required Field
    <button class="btn btn-default" ng-click="cancel()">Cancel</button>
    <button class="btn btn-primary" >Save</button>
</div>

</form>