<?php

/* 
* To change this license header, choose License Headers in Project Properties.
* To change this template file, choose Tools | Templates
* and open the template in the editor.
*/

?>
<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */
?>
<script>

function processRegisterS()
{
    
}
    function filterRegister(obj)
{
    $('#loaders').show();
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
     $('#load').load(livesite+'Attendanceregister/empregisterbook/'+month+'/'+site_fkey+'/'+branch,function() {
         $('#loaders').hide();
     });
                            }
     $(document).ready(function(){
        
        $('#loaders').hide();
        //filterEmployees();
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
                  $('#load').load(livesite+'Attendanceregister/empregisterbook/'+site_fkey+'/'+month);
                

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
        <h1  style="text-align: left;padding-left: 30px;font-size: 30px; margin-top: 1px;">Detailed Attendance Register</h1>
    <hr style="margin-top: -4px;margin-bottom: 15px;">
<div class="col-md-12">

    
       <div class="" id="div-reportcriterias">
                    <!-- <div class="col-md-8">
                     <input type="hidden" id="emp_pkey">
                <input type="text" id="user" class="form-control" name="user" placeholder="Search Your Employee Name" />
            </div> -->
                    <div class="col-md-12" style="    margin-top: -5px;">
                <label class="col-md-9 control-label" style="text-align: right;padding-top: 5px;margin-left: 75px;" for="filterby_month">Month :</label>
                                <div class="col-md-2">
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
                                    <button type="button" id="btn-processregister" class="btn btn-primary btn-sm " style="margin-left: 220px;margin-top: -54px;"onclick="filterRegister();"><i class="fa fa-eye"></i></button>
                                </div>
<!--                                <div class="col-md-1" style="text-align: right;">
                                     
                                </div>
                               -->
                            </div>

                         <div class="col-md-3">
                                                    
                        <!--<input type="radio" id="employeeview" name="employeeview" value="y">  -->                  
                        <div class="col-md-7">
                            <input type="hidden" value="<?php echo $emp; ?>" id="emp_fkey">
                        </div>    
                              
                    </div> 
                  <div class="col-md-3">
                                <div class="col-md-12">
                                    
                                </div>
                            </div>
                </div>
  
    
  

 <div  class="box box-primary" style="margin-top: 35px; height: 580px; ">
     <div id="load">
         
     </div>   
     <div class="col-md-12">
                                                            <div class="col-md-3" style="margin-top: 10px;">
                                    <div class="col-md-5" style="color:white;background-color: green">P</div>
                                    <div class="col-md-7">Present</div>
                                </div>
<!--                                                            <div class="col-md-3" style="margin-top: 10px;">
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
                                </div>-->
                                                            <div class="col-md-3" style="margin-top: 10px;">
                                    <div class="col-md-5" style="color:black;background-color: white">WO</div>
                                    <div class="col-md-7">Week Off</div>
                                </div>
<!--                                                            <div class="col-md-3" style="margin-top: 10px;">
                                    <div class="col-md-5" style="color:white;background-color: red">P/A</div>
                                    <div class="col-md-7">Holiday</div>
                                </div>-->
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
                                                    </div> 
    </div> 
</div>
       
    </div>
</section>
