

<form  class="form-horizontal" name="brform" id="brform" method="post" ng-submit="save()">

<div class="modal-header">
    <h3 class="modal-title"><?php echo (isset($data['HOLIDAYID']) && $data['HOLIDAYID']  != 0) ?"Edit Holiday":"New Holiday"; ?></h3>
</div>
<div class="modal-body">
<div style="padding: 10px">
	
	
	
	  <div class="form-group">
                <label>Holiday Group<span class="star">*</span></label>
                 <select required name="HOLIDAY_GROUP_ID" class="form-control" >
                <?php foreach ($holidaygroup as $key => $value) {
                    if($data['HOLIDAY_GROUP_ID'] == $key){
                    	?>
                    	<option selected="selected" value="<?php echo $key; ?>"><?php echo $value; ?></option>
                    	<?php
                    }else{
                    ?>
                    
                	<option value="<?php echo $key; ?>"><?php echo $value; ?></option>
                	
                    <?php
					}
                } ?>
               
                	                </select>
                
              </div>
	
	  <div class="form-group">
                <label>Holiday Type<span class="star">*</span></label>
                
                <select required name="HOLIDAYTYPE" class="form-control" >
                	<?php if($data['HOLIDAYTYPE'] == "MANDATORY"){
                		?>
                		<option value="OPTIONAL">Optional</option>
                		<option  selected="selected" value="MANDATORY">Mandatory</option>
                		<?php
                	}else{
                		?>
                		<option value="OPTIONAL">Optional</option>
                	<option value="MANDATORY">Mandatory</option>
                		<?php
                	} ?>
                	
                	                </select>
                
              </div>
	
   <div class="form-group">
                <label>Holiday<span class="star">*</span></label>
                <input type="text" name="HOLIDAYNAME" id="HOLIDAYNAME" value="<?php echo (isset($data['HOLIDAYNAME']))? $data['HOLIDAYNAME']: ""; ?>" class="form-control"  ng-pattern="/^[a-zA-Z0-9_]$/" required >
              </div>
              
              
               <div class="form-group">
                <label>Date <span class="star">*</span></label>
                <!--
                
                                <div class="col-sm-10" ng-controller="DatepickerDemoCtrl">
                            <div class="input-group w-md">
                              <input type="text"  class="form-control" datepicker-popup="{{format}}" name="HOLIDAYDATE" id="HOLIDAYDATE" is-open="opened" datepicker-options="dateOptions" ng-required="true" close-text="Close" />
                              <span class="input-group-btn">
                                <button type="button" class="btn btn-default" ng-click="open($event)"><i class="glyphicon glyphicon-calendar"></i></button>
                              </span>
                            </div>
                          </div>-->
                
              
                <input type="text" datepicker2 required   id="HOLIDAYDATE" name="HOLIDAYDATE"  value="<?php echo (isset($data['HOLIDAYDATE']))? $data['HOLIDAYDATE']: ""; ?>" class="form-control"  >
               
                             </div>
              
              
              
               <div class="form-group">
                <label>DESCRIPTION</label>
                            
 				<textarea class="form-control"   name="DESCRIPTION" id="DESCRIPTION" ><?php echo (isset($data['DESCRIPTION']))? $data['DESCRIPTION']: ""; ?></textarea> 
                
    
              </div>
              
              
              
	
	<input type="hidden" name="HOLIDAYID" value="<?php echo (isset($data['HOLIDAYID']))? $data['HOLIDAYID']: 0; ?>" />
	 
 

 
 
</div>
</div>
<div class="modal-footer">                  
	<span class="star">*</span>= Required Field
    <button class="btn btn-default" ng-click="cancel()">Cancel</button>
    <button class="btn btn-primary" >Save</button>
</div>

</form>