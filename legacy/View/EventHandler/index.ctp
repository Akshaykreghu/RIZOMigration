<section class="content-header">
    <h1>Event Handler</h1>
</section>
<!-- Main content -->
<section class="content">
    <div class="row">
        <div class="col-md-12">
            <!-- DIRECT CHAT DANGER -->
            <div class="box ">
                <div class="box-body" id="div-reportcriterias">
                    <h2>Task Reminder Setup</h2>
                    <div class="box "></div>
                    <form class="form-horizontal" method="post" id="familys">
                    <div class="form-group">
                         <label for="in_date" class="col-sm-2 control-label">Event Type</label>
                         <div class="col-sm-4">
                          <select required="required" class="form-control" value="" name="document_type" id="document_type">
                                 <option value="1">Employee Last Working Day</option>
                                 <option value="2">Probation</option>
                                 <option value="3">passport Expiring</option>
                                 <option value="4">Visa Expiring</option>
                                 <option value="5">Employee Confirmation</option>
                                 <option value="6">Birthday Reminder</option>
                                 <option value="7">Work anniversary</option>
                                 <option value="8">Designation Change</option>
                                 
                             </select>
                         </div>
                      </div>
                    <div class="form-group">
                         <label for="in_date" class="col-sm-2 control-label">Remind Type</label>
                         <div class="col-sm-4">
                          <select required="required" class="form-control" value="" name="document_type" id="document_type">
                                 <option value="Mail">By Mail</option>
                             </select>
                         </div>
                      </div>
                    <div class="form-group">
                         <label for="in_date" class="col-sm-2 control-label">Received by</label>
                         <div class="col-sm-4">
                          <select required="required" class="form-control" value="" name="document_type" id="document_type">
                                 <option value="employee">Employee</option>
                                 <option value="admin">Admin</option>
                             </select>
                         </div>
                      </div>
                    <div class="form-group">
                         <label for="in_date" class="col-sm-2 control-label">Received by</label>
                         <div class="col-sm-4">
                          <select required="required" class="form-control" value="" name="document_type" id="document_type">
                                 <option value="employee">Employee</option>
                                 <option value="admin">Admin</option>
                             </select>
                         </div>
                      </div>
                    <div class="row">
                        <div class="form-group">
                         <label for="in_date" class="col-sm-2 control-label">Mail template</label>
                         <div class="col-sm-4">
                          <select required="required" class="form-control" value="" name="document_type" id="document_type">
                                 <option value="1">Employee Last Working Day</option>
                                 <option value="2">Probation</option>
                                 <option value="3">passport Expiring</option>
                                 <option value="4">Visa Expiring</option>
                                 <option value="5">Employee Confirmation</option>
                                 <option value="6">Birthday Reminder</option>
                                 <option value="7">Work anniversary</option>
                                 <option value="8">Designation Change</option>
                                 
                             </select>
                         </div>
                      </div>
                        <div class="form-group">
                         <label for="in_date" class="col-sm-2 control-label">Send Notification Before </label>
                         <div class="col-sm-1">
                             <input type="Number" min="0" required="required" class="form-control" value="" name="document_number" id="document_number" >                         </div>
                         <div class="col-sm-2">Days</div>
                      </div>
                    </div>
                    </form>
                </div><!-- /.box-body -->
            </div>
        </div>
    </div>
</section>
<script>
jQuery(document).ready(function() {
    
});
</script>