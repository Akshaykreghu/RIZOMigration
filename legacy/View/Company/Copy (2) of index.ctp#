<div class="bg-light lter b-b wrapper-md">
  <h1 class="m-n font-thin h3">Company Setup</h1>
</div>
<div class="wrapper-md">
	
	
	
  <tabset class="tab-container" ng-init="">
    <tab heading="Contact Info" >
     
<form name="forma" id="forma" class="form-horizontal form-validation" method="post"   ng-controller="ContactInfoController" ajaxform="{url:'Company/savecompanysetup'}">
	
          <div class="panel panel-default">
            <div class="panel-heading">
             
            </div>
            <div class="panel-body">      
            	
          <div class="text-danger wrapper text-center" ng-show="formerror">
              {{formerror}}
          </div>
           <div class="text-success wrapper text-center" ng-show="message">
              {{message}}
          </div>
            	<div class="row inline-form">
            	   <div class="form-group col-md-6">
                <label class="col-sm-4 control-label">Business Name</label>
                <div class="col-md-8">
                  <input type="text" class="form-control" name="business_name" placeholder="Business Name" ng-model="ci.business_name" required >    
                </div>
              </div>
              
                 <div class="form-group col-md-6">
                <label class="col-sm-4 control-label">Nature Of Business</label>
                <div class="col-md-8">
                  <input type="text" class="form-control" name="business_nature" placeholder="Nature Of Business" ng-model="ci.business_nature" required >    
                </div>
              </div>
              </div>
              
              <div class="row inline-form">
                 <div class="form-group col-md-6">
                <label class="col-sm-4 control-label">Type Of Business</label>
                <div class="col-md-8">
                  <input type="text" class="form-control" name="business_type" placeholder="Type Of Business" ng-model="ci.business_type" required >    
                </div>
              </div>
              
                 <div class="form-group col-md-6">
                <label class="col-sm-4 control-label">Address</label>
                <div class="col-md-8">
                  <input type="text" class="form-control" name="address" placeholder="Address" ng-model="ci.address" required >    
                </div>
              </div>  
              </div>
              
              
              <div class="row inline-form">
                 <div class="form-group col-md-6">
                <label class="col-sm-4 control-label">Pincode</label>
                <div class="col-md-8">
                  <input type="text" class="form-control" name="pincode" placeholder="Pincode" ng-model="ci.pincode" required >    
                </div>
              </div>   
              
              
                 <div class="form-group col-md-6">
                <label class="col-sm-4 control-label">State</label>
                <div class="col-md-8">
                  <input type="text" class="form-control" name="state" placeholder="State" ng-model="ci.state" required >    
                </div>
              </div>   
              
              </div>
              
              
              <div class="row inline-form">
              
                              <div class="form-group col-md-6">
                <label class="col-sm-4 control-label">Phone</label>
                <div class="col-md-8">
                  <input type="text" class="form-control" name="phone" placeholder="(XXX) XXXX XXX" ng-model="ci.phone" ng-pattern="/\([0-9]{3}\) ([0-9]{3}) ([0-9]{3})$/"  >
                </div>
              </div>   
                 <div class="form-group col-md-6">
                <label class="col-sm-4 control-label">Fax</label>
                <div class="col-md-8">
                  <input type="text" class="form-control" name="fax" placeholder="Fax" ng-model="ci.fax" required >    
                </div>
              </div>  
              </div>
              
              <div class="row inline-form">  
              <div class="form-group col-md-6">
                <label class="col-sm-4 control-label">Email</label>
                <div class="col-md-8">
                  <input type="email" class="form-control" name="email" placeholder="email" ng-model="ci.email" required >    
                </div>
              </div>
              <div class="form-group col-md-6">
                <label class="col-sm-4 control-label">Website</label>
                <div class="col-md-8">
                  <input type="url" class="form-control" name="website" placeholder="http://" ng-model="ci.website"  >
                </div>
              </div>
              
              </div>
            </div>
            <footer class="panel-footer text-right bg-light lter">
              <button type="submit" class="btn btn-success" >Submit</button>
            </footer>
          </div>
        </form>
    </tab>
    <tab heading="Branch" >
      
  <div class="panel panel-default">
    <div class="panel-heading">
      Branches
    </div>
    <div class="table-responsive">
      <table ui-jq="dataTable" ui-options="{
          ajax:'Branch/listunits',
          processing: true,
		  serverSide: true,
         
        }" class="table table-striped m-b-none">
        <thead>
          <tr>
          	
            <th  style=""></th>
            <th  style="width:20%">Name</th>
            <th  style="width:25%">Address</th>
            <th  style="width:25%">City</th>
            <th  style="width:15%">State</th>
            <th  style="width:15%">Pincode</th>
            
            <th  style="width:15%">Status</th>
            
          </tr>
        </thead>
        <tbody>
        </tbody>
      </table>
    </div>
  </div>
    </tab>
    <tab heading="Department" >
      <div class="panel panel-default">
    <div class="panel-heading">
      Departments
    </div>
    <div class="table-responsive">
      <table ui-jq="dataTable" ui-options="{
          ajax:'Department/listdepartments',
          processing: true,
		  serverSide: true,
         
        }" class="table table-striped m-b-none">
        <thead>
          <tr>
          	
            <th  style=""></th>
            <th  style="width:20%">Department Code</th>
            <th  style="width:25%">Department Name</th>
            
          </tr>
        </thead>
        <tbody>
        </tbody>
      </table>
    </div>
  </div>
    </tab>
      <tab heading="Policy Info" >
     
    </tab>
      <tab heading="Compliance Info" >
      
    </tab>
      <tab heading="Banks" >
      <div class="panel panel-default">
    <div class="panel-heading">
      Branches
    </div>
    <div class="table-responsive">
      <table ui-jq="dataTable" ui-options="{
          ajax:'Bank/listbanks',
          processing: true,
		  serverSide: true,
         
        }" class="table table-striped m-b-none">
        <thead>
          <tr>
          	
            <th  style=""></th>
            <th  style="width:20%">Bank Name</th>
            <th  style="width:25%">Branch</th>
            <th  style="width:25%">IFSC Code</th>
            <th  style="width:15%">Acc. No.</th>
            
          </tr>
        </thead>
        <tbody>
        </tbody>
      </table>
    </div>
  </div>
    </tab>
  </tabset>
</div>