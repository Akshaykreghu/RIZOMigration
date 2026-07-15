<section class="content-header">
    <h1> Mobile Tracking Report </h1>
</section>
<!-- Main content -->
<section class="content">
    <div class="row">
        <div class="col-md-12">
            <!-- DIRECT CHAT DANGER -->
            <div class="box ">
                <div class="box-header with-border">
                    <!--<h3 class="box-title">Mobile location Report </h3>-->
                </div><!-- /.box-header -->
                <div class="box-body">
                    <!-- Employee import form -->
                    <form class="form-horizontal" method="post" action="" id="importvariableform">
                        <div class="form-group">
                            <div class="col-md-4">
                                <label class="col-sm-5" for="employee">Choose Employee</label>                        
                                <div class="col-md-7">
                                    <select id="emp_fkey" class="form-control select2-searching" name="emp_fkey" >
                                        <option value="">Select</option>
                                        <?php  //edited by arul employee company id added //foreach ($arr_employees as $value) { ?>
                                       <!-- <option value="<?php echo $value['EmployeeDetails']['emp_pkey']; ?>"><?php echo $value['EmployeeDetails']['first_name']; ?><?php echo " " . $value['EmployeeDetails']['last_name']; ?></option>-->
                                       <?php	foreach ($arr_employees as $key => $value) { ?>
		                       <option value="<?php echo $value['emp_details']['emp_pkey']; ?>">
			                <?php echo $value['emp_details']['first_name'].' '.$value['emp_details']['last_name'].' - '.$value['emp_proff']['emp_company_id']; ?>
		                       </option>     
                                           <?php } ?>
                                    </select>
                                </div>    
                            </div>
                            <div class="col-md-4 form-horizontal">
                                <label for="month" class="col-md-4 ">Choose Date</label>
                                <div class="col-md-4">
                                    <input type="text" id="from_date" class="form-control" name="from_dates" placeholder="Pick From Date" >
                                    
                                </div>
                                <div class="col-md-4">
                                    
                                    <input type="text" id="to_date" class="form-control" name="to_dates" placeholder="Pick To Date" >
                                </div>
                            </div>


                            <div class="col-md-4">
                                <label class="col-md-1 control-label" for="empvarcsv"></label>
                                <div class="col-md-12">
                                    <button type="button" id="btn-downloademployeectcform" class="btn btn-primary" onclick="mapld();">Generate Map</button>
                                    <!--<a href="#" class="btn btn-primary" onclick="downloads();" ><i class="icon-file"></i>Download As PDF</a>-->
                                    <a href="#" class="btn btn-primary" onclick="downloadexcel();" ><i class="icon-file"></i>Download Excel</a>
                                    
                                </div>
                            </div>

                        </div>



                    </form>
                    <div class="col-md-12" id="loadmap">

                    </div>
                </div>

            </div>
        </div>
    </div>
</section>
<script src="https://maps.googleapis.com/maps/api/js?key=AIzaSyBwj4pV2RX2vS6dra9FDGibWiEzYqsbIbc" type="text/javascript"></script>
<script>
    function downloads(){
        $('#importvariableform').attr('action',livesite+'MobileLocationUpdate/downloadpdf/'+'pdf');
        $('#importvariableform').submit();
    }
    
    function downloadexcel(){
        var emp = $('#emp_fkey').val();
        var from_dates = $("#from_date").val();
        var to_dates = $("#to_date").val();
        if(!emp){
            alert("Select Employee");
            return;
        }
        if(!from_dates){
            alert("Select Start Date");
            return;
        }
        if(!to_dates){
            alert("Select End Date");
            return;
        }
        window.open(livesite+'MobileLocationUpdate/downloadexcel/'+emp+'/'+from_dates+'/'+to_dates,'_blank');
    }
    
    jQuery(document).ready(function () {
        $("#from_date").datepicker({
            format: 'yyyy-mm-dd'
        });
        $("#to_date").datepicker({
            format: 'yyyy-mm-dd'
        });
        $('#emp_fkey').select2();
    });
    function mapld() {
        var emp = $('#emp_fkey').val();
        var from_dates = $("#from_date").val();
        var to_dates = $("#to_date").val();
        if(!emp){
            alert("Select Employee");
            return;
        }
        if(!from_dates){
            alert("Select Start Date");
            return;
        }
        if(!to_dates){
            alert("Select End Date");
            return;
        }
        $('#loadmap').load(livesite + "MobileLocationUpdate/loadmap/", {emp_key: emp,from_dates:from_dates,to_dates:to_dates},function(){
            
        })
    }
</script>





