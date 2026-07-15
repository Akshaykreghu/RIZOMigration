

<form  class="form-horizontal" name="bankform" id="bankform" method="post" ng-submit="save()">

<div class="modal-header">
    <h3 class="modal-title"><?php echo (isset($data['id']) && $data['id']  != 0) ?"Edit Bank":"New Bank"; ?></h3>
</div>
<div class="modal-body">
<div style="padding: 10px">
   <div class="form-group">
                <label>Bank Name </label>
                <input type="text" name="bank_name" id="bank_name" value="<?php echo (isset($data['bank_name']))? $data['bank_name']: ""; ?>" class="form-control"  required >
              </div>
              
              
   <div class="form-group">
                <label>Branch </label>
                <input type="text" name="bank_branch" id="bank_branch" value="<?php echo (isset($data['bank_branch']))? $data['bank_branch']: ""; ?>" class="form-control"  required >
              </div>
              
              
              
               <div class="form-group">
                <label>IFSC Code</label>
                <input type="text"  name="ifsc_code" id="ifsc_code" value="<?php echo (isset($data['ifsc_code']))? $data['ifsc_code']: ""; ?>" class="form-control"  required >
              </div>
                 
              
              
               <div class="form-group">
                <label>Acc. No </label>
                <input type="number"   name="acct_no" id="acct_no" value="<?php echo (isset($data['acct_no']))? $data['acct_no']: ""; ?>" class="form-control"  required >
              </div>
              
              
               
	
	<input type="hidden" name="id" value="<?php echo (isset($data['id']))? $data['id']: 0; ?>" />
	 
 

 
 
</div>
</div>
<div class="modal-footer">                  
    <button class="btn btn-default" ng-click="cancel()">Cancel</button>
    <button class="btn btn-primary" >Save</button>
</div>

</form>