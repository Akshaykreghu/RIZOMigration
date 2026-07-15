<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */
?>
<script>
function filterEmployees(branch)
{
    var branch = $('#filterby_branch').val();
    //alert(branch);
    $(".js-example-basic-single").select2(
        {
            //closeOnSelect:false,
            placeholder: "All",
             allowClear: true,
            ajax: {
                url: livesite+"EmpOtRegister/jsons/"+branch,
                dataType: 'json',
                delay: 250,
                data: function (params) {
                    return {
                        q: params.term, // search term
                        page: params.page
                    };
                },
                processResults: function (data, params) {
                    // parse the results into the format expected by Select2
                    // since we are using custom formatting functions we do not need to
                    // alter the remote JSON data, except to indicate that infinite
                    // scrolling can be used
                    params.page = params.page || 1;

                    return {
                        results: data.items,
                        pagination: {
                            more: (params.page * 30) < data.total_count
                        }
                    };
                }
            },
            escapeMarkup: function (markup) { return markup; }
        });
}
function processRegisterS()
{
    
}
    function filterRegister(obj)
{
    $('#loader').show();
    var month = $('#filterby_month').val();
   
    if($('#emp_fkey').val() != "0" || $('#emp_fkey').val() != "")
        {
    var site_fkey = $('#emp_fkey').val();
        }
        else
            {
                var site_fkey = 0;
            }
     if($('#filterby_branch').val() != "")
         {
    var branch = $('#filterby_branch').val();
         }
         else
             {
                 var branch = 0;
             }
     $('#load').load(livesite+'EmpOtRegister/Register/'+month+'/'+site_fkey+'/'+branch,function() { $('#loader').hide(); });
                            }
     $(document).ready(function(){
        
        $('#loader').hide();
        
        filterEmployees();
                var usersoptions = {
            url: function (phrase) {
                return "UserCredentials/getusers?username=" + phrase;
            },
            getValue: "full_name",
            list: {
                onClickEvent: function () {
                     var selectedItem = $("#user").getSelectedItemData();
                    var site_fkey = selectedItem.emp_pkey;
                    $('#emp_pkey').val(selectedItem.emp_pkey);
                    var month = $('#filterby_month').val();
                  $('#load').load(livesite+'Attendanceregister/registerbook/'+site_fkey+'/'+month);
                

                }
            }
        };


        $('#user').easyAutocomplete(usersoptions);
       $('#tt').tree({
   
});
    })
</script>

<section class="content">
    <div class="row">
<div class="col-md-12">
<div class="box box-header">
    <h2>Over Time Attendance Register</h2>
       <div class="box-body" id="div-reportcriterias">
                    <!-- <div class="col-md-8">
                     <input type="hidden" id="emp_pkey">
                <input type="text" id="user" class="form-control" name="user" placeholder="Search Your Employee Name" />
            </div> -->
            <div class="col-md-3">
                                <label class="col-md-5 control-label" for="filterby_month">Choose Month</label>
                                <div class="col-md-7">
                                    <select id="filterby_month" name="filterby_month" class="form-control">
										
                                        <?php
										/*
										 * By santhosh on 27 Dec 2015
										 */
										$start_month = strtotime(date('Y-m', strtotime("+1 month", strtotime(date('Y-m')))));
                                        for ($i = 0; $i < 10; $i++) {
											$month = date('Y-m', strtotime("-$i month", $start_month));
											if($month == date('Y-m')){
												echo '<option selected="selected" value="' . $month . '">' . date('M-Y', strtotime("-$i month", $start_month)) . '</option>';
											}else{
												echo '<option value="' . $month . '">' . date('M-Y', strtotime("-$i month", $start_month)) . '</option>';
											}
                                        }
                                        ?>
                                    </select>
                                </div>
                            </div>
<!--                         <div class="col-md-4">
                            <label class="col-sm-5" for="employee">Choose Employee</label>                        
                        <input type="radio" id="employeeview" name="employeeview" value="y">                    
                        <div class="col-md-7">
                            <select id="emp_fkey" class="form-control" name="emp_fkey" onchange="filterAttendanceupload(this);" >
                                <option value="">All</option>
                                <?php foreach ($arr_employees as $key => $val) { ?>
                                    <option value="<?php echo $val['EmployeeDetails']['emp_pkey']; ?>"><?php echo $val['EmployeeDetails']['first_name'].' '.$val['EmployeeDetails']['last_name']; ?></option>
                                <?php } ?>
                            </select>
                        </div>    
                              
                    </div>-->
                    
                         <div class="col-md-3">
                            <label class="col-sm-5" for="employee">Choose Employee</label>                        
                        <!--<input type="radio" id="employeeview" name="employeeview" value="y">  -->                  
                        <div class="col-md-7">
                            <select id="emp_fkey" class="js-example-basic-single" style="width: 100% ; ">
                                
                            </select>
                        </div>    
                              
                    </div> 
                  <div class="col-md-3">
                                <div class="col-md-7">
                                    <button type="button" id="btn-processregister" class="btn btn-primary" onclick="filterRegister();">List Overtime</button>
                                </div>
                            </div>
                </div>
  
    
  
</div>
 <div id="load" class="box box-body" style="margin:10px 0">
       
    </div> 
</div>
<!--       <div class="col-md-12">
                                                            <div class="col-md-3" style="margin-top: 10px;">
                                    <div class="col-md-5" style="color:white;background-color: green">P</div>
                                    <div class="col-md-7">Present</div>
                                </div>
                                                            <div class="col-md-3" style="margin-top: 10px;">
                                    <div class="col-md-5" style="color:white;background-color: orange">FDL</div>
                                    <div class="col-md-7">Full Day Leave</div>
                                </div>
                                                            <div class="col-md-3" style="margin-top: 10px;">
                                    <div class="col-md-5" style="color:white;background-color: orange">FHL</div>
                                    <div class="col-md-7">First Half Leave</div>
                                </div>
                                                            <div class="col-md-3" style="margin-top: 10px;">
                                    <div class="col-md-5" style="color:white;background-color: orange">SHL</div>
                                    <div class="col-md-7">Second Half Leave</div>
                                </div>
                                                            <div class="col-md-3" style="margin-top: 10px;">
                                    <div class="col-md-5" style="color:black;background-color: white">WO</div>
                                    <div class="col-md-7">Week Off</div>
                                </div>
                                                            <div class="col-md-3" style="margin-top: 10px;">
                                    <div class="col-md-5" style="color:white;background-color: red">P/A</div>
                                    <div class="col-md-7">Holiday</div>
                                </div>
                                                            <div class="col-md-3" style="margin-top: 10px;">
                                    <div class="col-md-5" style="color:white;background-color: red">A</div>
                                    <div class="col-md-7">Absent</div>
                                </div>
                                                            <div class="col-md-3" style="margin-top: 10px;">
                                    <div class="col-md-5" style="color:white;background-color: #34F593">AWT</div>
                                    <div class="col-md-7">Above Minimum Working Minutes</div>
                                </div>
                                                            <div class="col-md-3" style="margin-top: 10px;">
                                    <div class="col-md-5" style="color:white;background-color: #FF967E">BMW</div>
                                    <div class="col-md-7">Below Minimum Working Minutes</div>
                                </div>
                                                    </div> -->
    </div>
</section>
