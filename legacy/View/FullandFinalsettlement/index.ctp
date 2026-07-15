<section class="content-header">
    <h3>
        <li class="fa fa-building-o"></li>&nbsp;Full & Final Settlement

    </h3>
</section>
<!-- Main content -->
<section class="content">
    <div class="row">
        <div class="col-md-12">
            <div class="tabset0">
                <div data-pws-tab="tab1" data-pws-tab-name="Employee to Be Settled" data-pws-tab-icon="fa-info">
                    <div class="box ">
                        <div class="box-header with-border">
                            <h3 class="box-title">Employee</h3>
                            <div class="box-tools pull-right">
                                <button class="btn btn-box-tool" data-widget="collapse"><i class="fa fa-minus"></i></button>
                            </div>
                        </div><!-- /.box-header -->
                        <div class="box-body" style="height:200px;">
                            <input type="hidden" id="resignation_pkey">
                            <div class="col-md-6">
                                <li><input name="employee_style" type="radio" id="new_resigned" class="pull-left"></li>
                                <label class="col-md-5 control-label" for="import_emp_branch">Choose Employee</label>
                                <select disabled="disabled" class="js-example-basic-single new-settled">
                                    <?php foreach ($employee as $emp) { ?>
                                        <option value="<?php echo $emp['EmployeeDetails']['emp_pkey']; ?>"><?php echo $emp['EmployeeDetails']['first_name'] . ' ' . $emp['EmployeeDetails']['last_name']; ?></option>
                                    <?php } ?>
                                </select> 
                            </div>
                            <div class="col-md-6">
                                <li><input name="employee_style" type="radio" id="old_resigned" class="pull-left"></li>
                                <label class="col-md-5 control-label" for="import_emp_branch">Choose Employee from Resigned</label>
                                <select disabled="disabled" class="js-example-basic-single old-settled">
                                    <?php foreach ($resigned as $emp) { ?>
                                        <option value="<?php echo $emp['EmployeeDetails']['emp_pkey']; ?>"><?php echo $emp['EmployeeDetails']['first_name'] . ' ' . $emp['EmployeeDetails']['last_name']; ?></option>
                                    <?php } ?>
                                </select> 
                            </div>
                            <div class="col-md-12">
                                <div class="pull-right" style="margin-top:120px;"><input type="checkbox"> Draft Settlement</div>
                            </div>


                        </div><!-- /.box-body -->

                    </div>

                    <button class="btn btn-primary pull-right" onclick="next(2)">Next</button>


                </div>
                <div data-pws-tab="tab2" data-pws-tab-name="Resignation Details" data-pws-tab-icon="fa-sitemap">
                    <div class="box ">
                        <div class="box-header with-border">
                            <h3 class="box-title">Resignation Details</h3>
                            <div class="box-tools pull-right">
                                <button class="btn btn-box-tool" data-widget="collapse"><i class="fa fa-minus"></i></button>
                            </div>
                        </div><!-- /.box-header -->
                        <div class="box-body">
                            <form method="post" class="form-horizontal" action="<?php echo $this->webroot; ?>FullandFinalsettlement/Terminate" id="resignationform">
                                <div class="form-group">
                                    <div class="col-md-12">
                                        <label class="col-md-4 control-label">Reason For Leaving</label>
                                        <div clas="col-md-4">
                                            <select class="js-example-basic-single" name="Reason" required="required" id="Reason">
                                                <option value="Resigned">Resignation</option>
                                                <option value="Absconding">Absconding</option>
                                                <option value="Dissmissed">Dismissed</option>
                                                <option value="Retirement">Retirement</option>
                                                <option value="Retrenchment">Retrenchment</option>
                                                <option value="Permanent Disabilities">Permanent Disabilities</option>
                                                <option value="End of Contract">End of Contract</option>
                                                <option value="Death ">Death</option>
                                                <option value="Other">Other</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <div class="col-md-12">
                                        <label class="col-md-4 control-label">Resignation Submitted on</label>
                                        <div clas="col-md-4">
                                            <input onchange="asper_notice();" required="required" type="text" name="date_submitted" id="submitted" class="col-md-3 ClrElements control-text">
                                        </div>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <div class="col-md-12">
                                        <input type="hidden" name="emp_fkey" value="" id="emp_pkeys">
                                        <label class="col-md-4 control-label">Last Applied working date</label>
                                        <div clas="col-md-4">
                                            <input type="hidden" name="termination_pkey" id="termination_pkey">
                                            <input required="required" type="text" name="applied_date" id="applied_date" class="col-md-3 control-text ClrElements">
                                        </div>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <div class="col-md-12">
                                        <label class="col-md-4 control-label">Notice Period</label>
                                        <div clas="col-md-4">
                                            <input type="number" name="notice_period" id="notice_period" readonly="true" class="col-md-3 control-text ClrElements">
                                        </div>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <div class="col-md-12">
                                        <label class="col-md-4 control-label">Last working date (asper notice days)</label>
                                        <div clas="col-md-4">
                                            <input type="text" name="lat_workingday" readonly="true" id="lat_workingday" required="required" class="col-md-3 control-text ClrElements">
                                        </div>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <div class="col-md-12">
                                        <label class="col-md-4 control-label">Last Approved working date</label>
                                        <div clas="col-md-4">
                                            <input type="text" name="apprved" id="apprved" class="col-md-3 control-text ClrElements" required="required">
                                        </div>
                                    </div>
                                </div>
<!--                                <div class="form-group">
                                    <div class="col-md-12">
                                        <label class="col-md-4 control-label">Actual Last Working Day</label>
                                        <div clas="col-md-4">
                                            <input type="text" name="act_lat_workingday" id="act_lat_workingday" required="required" class="col-md-3 control-text ClrElements">
                                        </div>
                                    </div>
                                </div>-->
                                <div class="form-group">
                                    <div class="col-md-12">
                                        <label class="col-md-4 control-label">Remarks</label>
                                        <div clas="col-md-4">
                                            <textarea name="Remarks" id="Remarks" class="col-md-3 control-text ClrElements" required="required"></textarea>
                                        </div>
                                    </div>
                                </div>
                                <button class="btn btn-primary pull-right" type="submit">Save</button>
                            </form>

                        </div><!-- /.box-body -->

                    </div>
                    <!--<button class="btn btn-primary pull-right" onclick="next(3)">Next</button>-->
                </div>
                <div data-pws-tab="tab4" data-pws-tab-name="Working Days for Settlement" data-pws-tab-icon="fa-bars">
                    <div class="box " id="click_toview" onclick="viewdays();" style="text-align:center;">
                        <br>
                        <br>
                        <br>
                        <br>
                        <br>
                        <br>
                        <br>
                        <br>
                        <br>
                        <br>
                        <br>
                        <input type="button" onclick="showattendancesettleddata();" class="btn btn-primary" value="click to view logs">
                        <br>
                        <br>
                        <br>
                        <br>
                        <br>
                        <br>
                        <br>
                        <br>
                        <br>
                        
                        
                        
                    </div>
                    <div class="box " id="atte" style="display:none;">
                        <div class="box-header with-border">
                            <form id="daysbocs">    
                                <h3 class="box-title"><legend>Total Days To be Settled</legend></h3>
                                <div class="box-tools pull-right">
                                    <button class="btn btn-box-tool" data-widget="collapse"><i class="fa fa-minus"></i></button>
                                </div>
                                <div class="box box-body" >
                                    <div class="col-md-6">
                                        <h3><b><li class="fa fa-calendar"></li></b> Attendance Days</h3><h3 id="attendance_dats"></h3>
                                    </div>
                                    <div class="col-md-6">
									<div class="col-md-6">
                                        <h3><b><li class="fa fa-calendar"></li></b> Notice period Adjustments</h3><h3 id="notice_days_to_attend"></h3>
										</div>
										<div class="col-md-6">
										<h3><b><li class="fa fa-calendar"></li></b> Present Days After Resignation</h3><h3 id="present_days_after_designation"></h3>
										</div>
                                    </div>
                                    <div class="col-md-6">
                                        <h3><b><li class="fa fa-calendar"></li></b>Total working days (Notice Days - {weekoffs + holidays})</h3>  <h3 id="after_weekoff_calculation_function"></h3>
                                    </div>
                                    <div class="col-md-6">
                                        <h3><b><li class="fa fa-calendar"></li></b>Balance working days (Total Working Days - Attendance Days)</h3>  <h3 id="balance_working_days"></h3><input type="checkbox" class="form-controll" id="apply_encash" value="" >Apply for leave Encashment
                                    </div>
                                    <div class="col-md-6">
                                        <h3><b><li class="fa fa-calendar"></li></b>Encashable  Leave</h3><h3 id="leave_display"></h3>
                                    </div>
                                    <div class="col-md-6">
                                        <h3><b><li class="fa fa-calendar"></li></b>Encashable  Leave balance</h3>   <h3 id="encashable_leave_balanac"></h3>
                                    </div>
                                </div>
                                
                                

                                <div class="box box-body">
                                    <h3>Encahsable Leave Balance To be settled<span id="total_days"></span> Payment Days To be Settled<span id="total_payment_days"></span></h3><input type="radio" name="dys_radio" class="form-group">Approve day<input type="radio" name="dys_radio" class="form-group">Skip days
                                </div>
                            </form>
                        </div><!-- /.box-body -->

                    </div>
                    <button class="btn btn-primary pull-right" onclick="next(3)">Next</button>
                </div>
                <div data-pws-tab="tab3" data-pws-tab-name="Leave Encashment " data-pws-tab-icon="fa-info">

                    <div class="box ">
                        <div class="box-header with-border">
                            <h3 class="box-title">Leave Requests that Need to be Take Action</h3>
                            <div class="box-tools pull-right">
                                <button class="btn btn-box-tool" data-widget="collapse"><i class="fa fa-minus"></i></button>
                            </div>
                        </div><!-- /.box-header -->
                        <div class="box-body" id="leaveencash">
                            <div id="leaverequesttable">
                                <table id="datagrid_employee_leaves" class="table table-bordered table-hover">

                                </table>
                            </div>
                            <h3 class="box-title">Leave Encashments that Need to be Take Action</h3>
                            <div id="leaveencashtable">
                               <table id="empleaverequeststable" class="table table-bordered table-hover">

                                </table>
                                <br>
                                <h4> Total Leave Encashment Days : <span id="total_leaveencash"></span></h4>
                                <h4> Adjusted with notice Period : <span id="adjusted_notice"></span></h4>
                                <hr>
                                <h2 id="Encash_container"> Final Encashment Leave Days : <span id="fn_leaveencash"></span> <button class="btn btn-primary" onclick="approveencash();">Approve</button></h2>
                                
                            </div>

                        </div><!-- /.box-body -->

                    </div>
                    <button class="btn btn-primary pull-right" onclick="next(5)">Next</button>
                </div>


                
                <div data-pws-tab="tab5" data-pws-tab-name="Other Allocations" data-pws-tab-icon="fa-university">
                    <div class="box ">
                        <div class="box-header with-border">
                            <h3 class="box-title">Allocations</h3>
                            <div class="box-tools pull-right">
                                <button class="btn btn-box-tool" data-widget="collapse"><i class="fa fa-minus"></i></button>
                            </div>
                        </div><!-- /.box-header -->
                        <div class="box-body" id="assets">
                            
                        </div><!-- /.box-body -->

                    </div>
                    <button class="btn btn-primary pull-right" onclick="load_complete()">Process Full And Final</button>
                </div>	










                <div data-pws-tab="tab7" data-pws-tab-name="Complete" data-pws-tab-icon="fa-bars">

                    <div class="box ">
                        <div class="box-header with-border">
                            <h3 class="box-title">Full and Final Completion</h3>
                            <div class="box-tools pull-right">
                                <button class="btn btn-box-tool" data-widget="collapse"><i class="fa fa-minus"></i></button>
                            </div>
                        </div><!-- /.box-header -->
                        <div class="box-body" id="load_compl">

                        </div><!-- /.box-body -->

                    </div>
                </div>      




            </div>
        </div>
    </div>

    <input type="hidden" id="emp_pkey" name="emp_pkey">
</section>



<script type="text/javascript">
    function approveencash(){
        var leaveencash = $('#fn_leaveencash').html();
        var emp_pkey = $('#emp_pkey').val();
		$('#Encash_container').append('<button class="btn btn-primary"><li class="fa fa-spin fa-spinner"></li></button>');
        $.ajax({
            url: livesite + "FullandFinalsettlement/approveencash",
            type: "POST",
            data: {leaveencash: leaveencash, emp_pkey: emp_pkey},
            success: function (resp) {
                if (resp == true) {
					$('#Encash_container').html('Approved Successfully');
                    
                }
            }
        });
    }
    function generateslip(){
        var dayss = $('#total_payment_days').html();
        var leaves = $('#fn_leaveencash').html();
        var emp_pkey = $('#emp_pkey').val();
        $.ajax({
            url: livesite + "LeaveEncashmentRequest/encashemp",
            type: "POST",
            data: {emp: employee, applied_days: applied_value, salaryheaditemsfkey: salaryheaditemsfkey},
            success: function (resp) {
                if (resp == true) {
                    //$(s).html('<li class="fa fa-spinner fa-spin"></li>');
                    $(s).fadeOut(90, function () {
                        $(s).parent('div').append('Approved Successfully');
                        $('#empleaverequeststableverified').datagrid('load');
                    });
                }
            }
        });
    }
    function clearforms(){
        $('#assets').html(''); // clear assets tabs
        $('#load_compl').html(''); // clear slip tabs completed
        $('#total_leaveencash').html('');
        $('#adjusted_notice').html('');
        $('#fn_leaveencash').html('');
    }
    function viewdays(){
        showattendancesettleddata();
    }
    function load_complete(){
        var employee = $('#emp_pkey').val();
        var dayss = $('#total_payment_days').html();
        var leaves = $('#fn_leaveencash').html();
        $('#load_compl').load(livesite + "FullandFinalsettlement/get_complete/"+employee+"/"+dayss+"/"+leaves);
        //generateslip();
        next(7);
    }
    function save(s, applied, employee, salaryheaditemsfkey) {
        var input_te = $(s).siblings('input');
        var applied_value = $(input_te).val();
        $.ajax({
            url: livesite + "LeaveEncashmentRequest/encashemp",
            type: "POST",
            data: {emp: employee, applied_days: applied_value, salaryheaditemsfkey: salaryheaditemsfkey},
            success: function (resp) {
                if (resp == true) {
                    //$(s).html('<li class="fa fa-spinner fa-spin"></li>');
                    $(s).fadeOut(90, function () {
                        $(s).parent('div').append('Approved Successfully');
                        $('#empleaverequeststableverified').datagrid('load');
                    });
                }
            }
        });
    }
    function showattendancesettleddata(){
       var employee = $('#emp_pkey').val();
       var date1 = $('#submitted').val();
       var date2 = $('#lat_workingday').val();
       next(4);
       $.ajax({
            url: livesite + "FullandFinalsettlement/workingattendnacedays",
            type: "POST",
            data: {emp: employee,from:date1,todate:date2},
            success: function (resps) {
                var response = JSON.parse(resps);
              $('#attendance_dats').html(response.atte);
              $('#leave_display').html(response.leave);
              var date = new Date($('#apprved').val());
              var date2 = new Date($('#submitted').val());
			  var date4 = new Date();

              //if(date1 = date1){
                  var timeDiff = Math.abs(date2.getTime() - date.getTime());
                  var diffDays = Math.ceil(timeDiff / (1000 * 3600 * 24));
				  var present_diff = Math.abs(date4.getTime() - date2.getTime());
				  var diffDayspresent = Math.ceil(present_diff / (1000 * 3600 * 24));
              //}else{
              //    var diffDays = 0;
              //}
			  
              $('#notice_days_to_attend').html(diffDays);
              $('#click_toview').css("display","none");
              $('#atte').css("display","block");
              var resig_pkey = $('#resignation_pkey').val();
              var sum = 0;
                $("input[name='encashdys']").each(function(){
                    sum += parseFloat(this.value);
                });
                $('#leave_display').html(sum);
                $('#encashable_leave_balanac').html(sum);
                //var total = (parseInt(diffDays) + parseInt(response.atte)) - parseInt(response.leave);
                var total = response.atte - diffDays;
                var diff = 0;
                var adjusted = 0;
                if(diffDays > sum){
                    total = total + sum;
                    adjusted = sum;
                }else{
                    diff = sum - diffDays;
                    total = total + diffDays;
                    adjusted = diffDays;
                }
                $('#total_days').html(sum).val(sum);
                $('#total_payment_days').html(total).val(total);
                $('#total_leaveencash').html(sum).val(sum);
                $('#adjusted_notice').html(adjusted).val(adjusted);
                var diffdays_weekof = 0;
                if(diffDays != 0){
                    diffdays_weekof = $('#notice_period').val() - response.weekoff_couts;
                }
                var balance_workingdays = diffdays_weekof - response.atte;
                $('#balance_working_days').html(balance_workingdays);
                $('#after_weekoff_calculation_function').html(diffdays_weekof).val(diffdays_weekof);
                $('#fn_leaveencash').html(diff).val(diff);
				var present_days_after_designation = diffDayspresent;
				$('#present_days_after_designation').val(present_days_after_designation).html(present_days_after_designation);
              savedetails(employee,resig_pkey,response.atte,response.leave,diffDays,total);
            }
        }); 
    }
    function savedetails(employee,resig_pkey,atte,leave,diffDays,total){
        $.ajax({
            url: livesite + "FullandFinalsettlement/savedetails",
            type: "POST",
            data: {emp: employee,pkeys:resig_pkey,atte:atte,leave:leave,diffDays:diffDays,total:total},
            success: function (resps) {
                var response = JSON.parse(resps);
            }
        }); 
    }
    jQuery(document).ready(function ($) {
        $('#attendance_dats').css("padding-left","67px").css("font-size","-webkit-xxx-large").css("color","orange");
        $('#notice_days_to_attend').css("padding-left","67px").css("font-size","-webkit-xxx-large").css("color","orange");
        $('#leave_display').css("padding-left","67px").css("font-size","-webkit-xxx-large").css("color","red");
        $('#after_weekoff_calculation_function').css("padding-left","67px").css("font-size","-webkit-xxx-large").css("color","red");
        $('#balance_working_days').css("padding-left","67px").css("font-size","-webkit-xxx-large").css("color","red");
        $('#encashable_leave_balanac').css("padding-left","67px").css("font-size","-webkit-xxx-large").css("color","red");
        
        $('#total_days').css("padding-left","67px").css("font-size","-webkit-xxx-large");
        $('#total_payment_days').css("padding-left","67px").css("font-size","-webkit-xxx-large");
        $('#apply_encash').change(function() {
            var ecashable_days = $('#leave_display').html();
            var balance_working_days = $('#balance_working_days').html();
            var leave_balance = ecashable_days;
            var payroll_days = 0;
            var atte_days = $('#attendance_dats').html();
            
        if($(this).is(":checked")) {
            
            if(leave_balance < 0){
                leave_balance = 0;
                payroll_days = parseInt(atte_days) + parseInt(ecashable_days);
            }else{
                payroll_days =  parseInt(atte_days) + parseInt(balance_working_days);
            }
            leave_balance = ecashable_days - balance_working_days;
            $('#total_days').html(leave_balance);
            $('#adjusted_notice').html(balance_working_days);
            $('#fn_leaveencash').html(leave_balance);
            $('#total_payment_days').html(payroll_days);
            $('#encashable_leave_balanac').html(leave_balance);
        }else{
            //leave_balance = 0;
            balance_working_days = 0;
            payroll_days = parseInt(atte_days);
            $('#total_days').html(leave_balance);
            $('#adjusted_notice').html(balance_working_days);
            $('#fn_leaveencash').html(leave_balance);
            $('#total_payment_days').html(payroll_days);
            $('#encashable_leave_balanac').html(leave_balance);
        }       
        });
        $('#applied_date').datepicker({
            format: 'yyyy-mm-dd',
			autoclose: true
        });
        $('#submitted').datepicker({
            format: 'yyyy-mm-dd',
			autoclose: true
        });
        $('#apprved').datepicker({
            format: 'yyyy-mm-dd',
			autoclose: true
        });
        $('#act_lat_workingday').datepicker({
            format: 'yyyy-mm-dd',
			autoclose: true
        });
        $('#new_resigned').click(function () {
            if ($('#new_resigned').is(':checked') == true) {
                $('.new-settled').attr("disabled", false);
                $('.old-settled').attr("disabled", true);
            }

        });
        $('#old_resigned').click(function () {
            if ($('#old_resigned').is(':checked') == true) {
                $('.old-settled').attr("disabled", false);
                $('.new-settled').attr("disabled", true);
            }

        });
        $('.new-settled').change(function () {
            if ($('#new_resigned').is(':checked') == true) {
                var emp_pkey = $('.new-settled').val();
                $('#emp_pkey').val(emp_pkey);
                $('#emp_pkeys').val(emp_pkey);
                loadresignation(2);//1 for reignation
                $('#empleaverequeststable').datagrid('load', {id: emp_pkey + '=' + emp_pkey});
                $('#datagrid_employee_leaves').datagrid('load', {emp_fkey: emp_pkey + '=' + emp_pkey});
                next(2);
            }

        });
        $('.old-settled').change(function () {
            if ($('#old_resigned').is(':checked') == true) {
                var emp_pkey = $('.old-settled').val();
                $('#emp_pkey').val(emp_pkey);
                $('#emp_pkeys').val(emp_pkey);
                loadresignation(1);//1 for reigned
                $('#empleaverequeststable').datagrid('load', {id: emp_pkey + '=' + emp_pkey});
                $('#datagrid_employee_leaves').datagrid('load', {emp_fkey: emp_pkey + '=' + emp_pkey});
                next(2);
            }

        });
        
        function loadresignation(s) {
            var employee = $('#emp_pkey').val();
                            $('.ClrElements').val('');
//                            $('#total_days').html(total).val(total);
//                    $('#total_leaveencash').html(sum).val(sum);
//                    $('#adjusted_notice').html(adjusted).val(adjusted);
//                    $('#fn_leaveencash').html(diff).val(diff);
                            $('#daysbocs').trigger('reset');
                            $('#atte').css("display","none");
                            $('#click_toview').css("display","block");
            $.ajax({
                url: livesite + "FullandFinalsettlement/getperiod/"+employee,
                type: "POST",
                success: function (resp) {
                    
                        //$(s).html('<li class="fa fa-spinner fa-spin"></li>');
                        var response = JSON.parse(resp);
                    //alert(response.days); 
                    if(response.success == 1){
                        //$(s).fadeOut(90, function () {
                            $('#notice_period').val(response.days);
                        //});
                    }else{
                        $('#notice_period').val('0');
                    }
                }
            }); 
            if (s == 1) {
                   $.ajax({
                url: livesite + "FullandFinalsettlement/details_res/"+employee,
                type: "POST",
                success: function (resp) {
                    var response = JSON.parse(resp);
                    //alert(response.days); 
                    if(response.success == 1){
                        //$(s).fadeOut(90, function () {
                            $('#termination_pkey').val(response.LEAVEENTRYID);
                            $('#Reason').val(response.Reason);
                            $('#applied_date').val(response.applied_date);
                            $('#submitted').val(response.submitted);
                            $('#lat_workingday').val(response.last_reason);
                            $('#apprved').val(response.approved);
                            $('#Remarks').val(response.remarks);
                            $('#act_lat_workingday').val(response.act_last_working_day);
                        //});
                    }
                    
                }
            });     
            }
            
            $('#assets').load(livesite + "FullandFinalsettlement/Assets/"+employee);
        }
        
        $('.tabset0').pwstabs({
            effect: 'scale', // You can change effects of your tabs container: scale / slideleft / slideright / slidetop / slidedown / none
            defaultTab: 1, // The tab we want to be opened by default
            containerWidth: '100%', // Set custom container width if not set then 100% is used
            tabsPosition: 'horizontal', // Tabs position: horizontal / vertical
            horizontalPosition: 'top', // Tabs horizontal position: top / bottom
            verticalPosition: 'left', // Tabs vertical position: left / right
            responsive: true, // Make tabs container responsive: true / false - boolean
            theme: '',
            rtl: false        // Right to left support: true/ false
        });
        $('#empleaverequeststable').datagrid({
            url: livesite + "LeaveEncashmentRequest/listallempsforencash",
            pagination: true,
            queryParams: {
                null: null
            },
            singleSelect: true,
            pageSize: 10,
            rownumbers: true,
            onLoadSuccess: function (data) {
//                    $.messager.show({
//                        title:'Info',
//                        msg:'You have  '+data.total+' Leave Requests'
//                    });

            },
            fitColumns: true,
            pageList: [2, 5, 10, 50, 100],
            columns: [
                [
                    {field: 'item', title: 'Leave Type', width: "20%"},
                    {field: 'leave_encash_limit', title: 'Encash Days', width: "20%"},
                    {field: 'yearlybalance', title: 'yearly Balance', width: "20%"},
                    {field: 'eligible', title: 'Applicable Days', width: "30%"},
                    {field: 'applied', title: '', width: "0%", formatter: function (value, row, index) {
                            if (row.editing == false) {
                                var s = '<a href="#" onclick="saverow(this)">Save</a> ';
                                var c = '<a href="#" onclick="cancelrow(this)">Cancel</a>';
                                return s + c;
                            } else {
                                var e = '<input value="' + row.eligible + '" type="text" name="encashdys" style="color:black; display:none; "> ';
                                return e;
                            }
                        }}
                ]
            ]
        });
        $('#datagrid_employee_leaves').datagrid({
           url: livesite + "FullandFinalsettlement/leavebalance/",
            pagination: true,
            queryParams: {
                null: null
            },
            pageSize: 10,
            rownumbers: true,
            toolbar: [{
				text:'Approve Selected',
				iconCls:'icon-add',
                                handler: function(){
			
					var row = $('#datagrid_employee_leaves').datagrid('getSelections');
						if (row){
                                                    var ss = [];
                                                    for(var i=0; i<row.length; i++){
                                                            var rows = row[i];
                                                            ss.push(rows.LEAVEENTRYID);
                                                        }
								    $.ajax({
                                                                            url: livesite + "FullandFinalsettlement/approve_selectd/",
                                                                            data:{ss:ss},
                                                                            type: "POST",
                                                                            success: function (resp) {
                                                                                    $('#datagrid_employee_leaves').datagrid('load')
                                                                                    //$(s).html('<li class="fa fa-spinner fa-spin"></li>');
                                                                                    $('#empleaverequeststable').datagrid('load');
                                                                            }
                                                                        });
						}else{
						alert("Please select a record to edit")
						}
				}
				},{
				iconCls: 'icon-edit',
				text:'Reject Selected',
				handler: function(){
						var row = $('#datagrid_employee_leaves').datagrid('getSelections');
						if (row){
                                                    var ss = [];
                                                    for(var i=0; i<row.length; i++){
                                                            var rows = row[i];
                                                            ss.push(rows.LEAVEENTRYID);
                                                        }
                                                        //alert(ss);
								    $.ajax({
                                                                            url: livesite + "FullandFinalsettlement/reject_selected/",
                                                                            type: "POST",
                                                                            data:{ss:ss},
                                                                            success: function (resp) {
                                                                                $('#datagrid_employee_leaves').datagrid('load');
                                                                                $('#empleaverequeststable').datagrid('load');  
                                                                            }
                                                                        });
						}else{
						alert("Please select a record to edit")
						}
				}
				}],
            onLoadSuccess: function (data) {
//                    $.messager.show({
//                        title:'Info',
//                        msg:'You have  '+data.total+' Leave Requests'
//                    });

            },
            fitColumns: true,
            pageList: [2, 5, 10, 50, 100],
            columns: [
                [
                    {field: 'emp_name', title: 'Employee name', width: "20%"},
                    {field: 'leave_type', title: 'Leave Type', width: "10%"},
                    {field: 'leave_days', title: 'LeaveDays', width: "10%"},
                    {field: 'FROMDATE', title: 'From date', width: "10%"},
                    {field: 'TODATE', title: 'To Date', width: "10%"},
                    {field: 'LEAVESTATUS', title: 'Leave Status', width: "20%"},
                    
                ]
            ]
        });
        //$('#resignationform').parsley();
    var eeoptions = { 
    success:       function(responseText, statusText, xhr, $form){
	var response = JSON.parse(responseText);
        if(response.success == 1){
            next(4);
        $('#resignation_pkey').val(response.pkeys);
    }
    else{
        alert("error please try again after sometimes");
        }
    }
    };
        $('#resignationform').submit(function(e) {
            $(this).ajaxSubmit(eeoptions);
           e.preventDefault();
        });
        $(".js-example-basic-single").select2();
    });
    function asper_notice(){
            var applied_date = new Date($('#submitted').val());
            var period = parseInt($('#notice_period').val());
            if(period){
                period = period;
            }else{
                period = 0;
            }
            applied_date.setDate(applied_date.getDate() + period);
            var dd = applied_date.getDate();
            var mm = applied_date.getMonth()+1; //January is 0!

            var yyyy = applied_date.getFullYear();
            if(dd<10){
                dd='0'+dd;
            } 
            if(mm<10){
                mm='0'+mm;
            } 
            var today = yyyy+'-'+mm+'-'+dd;
            $('#lat_workingday').val(today);
            
    }
    
    function next(s) {
        var currentTab = $('.pws_tabs_container').find('a[data-tab-id="tab' + s + '"]').trigger('click');
        
    }
</script>