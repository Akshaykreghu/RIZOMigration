

<form  class="form-horizontal" name="brform" id="brform" method="post" ng-submit="save()">

<div class="modal-header">
    <h3 class="modal-title">Update Policy Type</h3>
</div>
<div class="modal-body">
<div style="padding: 10px">
	
	
	
	  <div class="form-group">
        <label>Name</label>
              <input type="text" name="item" id="item" value="" class="form-control" readonly="readonly" >
              </div>
	 <div class="form-group">
                <label>Code</label>
                <input type="text" name="code" id="code" value="" class="form-control" readonly="readonly" >
     </div>
	 <div class="form-group">
         <label>Yearly Limit</label>
         <input type="text" name="alloted_leave_forthe_year" id="alloted_leave_forthe_year" value="" class="form-control" >
     </div>
      <div class="form-group">
         <label>Carry Forward Limit</label>
         <input type="text" name="CARRY_FORWARD_LIMIT" id="CARRY_FORWARD_LIMIT" value="" class="form-control"  >
      </div>
   <div class="form-group">
                <label>Applicable To<span class="star">*</span></label>
                    <select required name="APPLICABLE_TO" class="form-control" >
                    		<option  value="A">ALL</option>
                    		<option  value="M">Male</option>
                    		<option  value="F">Female</option>
                    </select>
                
              </div>
        <div class="form-group">
                <label>Allow Negative Balance<span class="star">*</span></label>
                <input type="text" name="ALLOW_NEGETIVE" id="ALLOW_NEGETIVE" value="" class="form-control" >
              </div>       
              
              
              
              
               <div class="form-group">
                <label>DESCRIPTION</label>
                            
 				<textarea class="form-control"   name="REMARKS" id="REMARKS" ></textarea> 
                
    
              </div>
              
              
              
	
 

 
 
</div>
</div>
<div class="modal-footer">                  
	<span class="star">*</span>= Required Field
    <button class="btn btn-default" ng-click="cancel()">Cancel</button>
    <button class="btn btn-primary" >Save</button>
</div>

</form>