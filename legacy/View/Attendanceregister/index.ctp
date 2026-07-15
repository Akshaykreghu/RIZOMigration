<?php
/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
?>
<!-- edited by bindu 12-12-25 -->
<style>
    .heading {
        display: flex;
        flex-direction: row;
        align-items: end;
        justify-content: space-between;
        /* margin-left: 20px; */
    }

    .home {
        background-color: #ffffffff;
        border-radius: 50px;
        padding: 2px 15px;
        color: #1e516e !important;
        /* margin-right: 15px; */
        color: white;
        font-weight: 500;
        text-decoration: none;
        transition: all 0.3s ease;
        cursor: pointer;
        border: #1e516e 1px solid;
    }
</style>
<!-- edited by bindu 12-12-25 end -->

<script>
    function filterEmployees(branch)
    {
        var branch = $('#filterby_branch').val();
        //alert(branch);
        //added by megha on 8_6_19 resigned employee data
        if($('input[type="checkbox"]').is(":checked")){
               // alert("Checkbox is checked.");
               var resigned = $('#resigned').val();
         }
         else if($('input[type="checkbox"]').is(":not(:checked)")){
               // alert("Checkbox is unchecked.");
               var resigned = 0;
        }
        //end
          $(".js-example-basic-employee").select2(
                {
                    //closeOnSelect:false,
                    placeholder: "All",
                    allowClear: true,
                    ajax: {
                        url: livesite + "Attendanceregister/jsons/" + branch + "/" + resigned,
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
                    escapeMarkup: function (markup) {
                        return markup;
                    }
                });
    }
      function filtersitemonth(month){
         var month = $('#filterby_month').val();
        //alert(branch);
        $(".js-example-basic-site").select2(
                {
                    //closeOnSelect:false,
                    placeholder: "All",
                    allowClear: true,
                    ajax: {
                        url: livesite + "Attendanceregister/getbranches",
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
                    escapeMarkup: function (markup) {
                        return markup;
                    }
                });
    }
    function refresh(t)
    {
        var elem = $(t).attr('id');
//        alert(elem);
        if(elem === 'filterby_month'){
            $('#emp_fkey').val('');
            $('#filterby_branch').val('');
        }
        if(elem === 'filterby_branch'){
            $('#emp_fkey').val('');
        }
//        $('#emp_fkey').val('');
        filterEmployees();
        filtersitemonth();
    }
    
    function processRegisterS()
    {

    }
    function filterRegister(obj)
    {
        $('#loaders').show();
        var month = $('#filterby_month').val();
        //added by megha on 8_6_19 resigned employee data
        if($('input[type="checkbox"]').is(":checked")){
               // alert("Checkbox is checked.");
               var resigned = $('#resigned').val();
         }
         else if($('input[type="checkbox"]').is(":not(:checked)")){
               // alert("Checkbox is unchecked.");
               var resigned = 0;
        }
        //end
        if ($('#emp_fkey').val() != "0" || $('#emp_fkey').val() != "")
        {
            var site_fkey = $('#emp_fkey').val();
        } else
        {
            var site_fkey = 0;
        }
        if ($('#filterby_branch').val() != "")
        {
            var branch = $('#filterby_branch').val();
        } else
        {
            var branch = 0;
        }
        $('#load').load(livesite + 'Attendanceregister/registerbook/' + month + '/' + site_fkey + '/' + branch + '/' + resigned, function () {
            $('#loaders').hide();
        });
    }
    
    function showlessdata(obj)
    {
        $('#loaders').show();
        var month = $('#filterby_month').val();
         //added by megha on 8_6_19 resigned employee data
        if($('input[type="checkbox"]').is(":checked")){
               // alert("Checkbox is checked.");
               var resigned = $('#resigned').val();
         }
         else if($('input[type="checkbox"]').is(":not(:checked)")){
               // alert("Checkbox is unchecked.");
               var resigned = 0;
        }
        //end
        if ($('#emp_fkey').val() != "0" || $('#emp_fkey').val() != "")
        {
            var site_fkey = $('#emp_fkey').val();
        } else
        {
            var site_fkey = 0;
        }
        if ($('#filterby_branch').val() != "")
        {
            var branch = $('#filterby_branch').val();
        } else
        {
            var branch = 0;
        }
        $('#load').load(livesite + 'Attendanceregister/registerbookless/' + month + '/' + site_fkey + '/' + branch + '/' + resigned, function () {
            $('#loaders').hide();
        });
    }
    
    $(document).ready(function () {
        $('#filterby_month').select2();
        $('#filterby_branch').select2();
        refresh();
        $('#loaders').hide();
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
                    $('#load').load(livesite + 'Attendanceregister/registerbook/' + site_fkey + '/' + month);


                }
            }
        };


        $('#user').easyAutocomplete(usersoptions);
        $('#tt').tree({
        });
         //added by megha on 8_6_19 resigned employee data
         $('input[type="checkbox"]').click(function(){
             filterEmployees();
         });
         //end
    })
     $(".home").on("click", function () {

    $("#container").isLoading({
        text: "Loading",
        position: "overlay",
    });

    let url = "";
    var userGroup = <?php echo json_encode($this->Session->read('user_group')); ?>;

    if (userGroup == "1") {
        url = livesite + "AttendanceSetup/index";
    } 
    else if (userGroup == "2") {
        url = livesite + "EmployeeMenu/addon";
    }

    $("#container").load(url, function () {
        isDashboardShown = false;
    });

});
</script>
<!-- edited by bindu 12-12-25 -->
<section class="content-header heading">
      <!-- edited by athira on 03-07-2025 -->
    <h1 class="text-primary-18">Timesheet</h1>
    <!-- end -->
     <div class="text-primary-16 home" style="display:flex; align-items:center; gap:10px; cursor:pointer;">
                    <i class="fa" style="font-size:16px;">&#xf104;</i>
                    Back
                </div>
    
</section>
<hr style="margin-top: 8px;margin-bottom: -2px;">
<!-- edited by bindu 12-12-25 end -->
<section class="content">
    <div class="row">
        <div class="col-md-12">
            <div class="">
<!--                <h2 style="text-align:left; font-size: 3em;">Detailed Attendance Register</h2>-->
                <div class="box-body" id="div-reportcriterias">
                    <!-- <div class="col-md-8">
                     <input type="hidden" id="emp_pkey">
                <input type="text" id="user" class="form-control" name="user" placeholder="Search Your Employee Name" />
            </div> -->
                  <div class="col-md-3" style="margin-left: -22px;">
                        <label class="col-sm-4" for="filterby_month">Month : </label>                                        
                        <div class="col-md-8">
                            <!--//    Add new function for bank search box  ---- Added By Nimisha 19/03/2019    Start //-->

                            <select id="filterby_month" name="filterby_month" onchange="refresh(this);" style="width: 100%; "   >                                                 
                                 <?php  $start_month = strtotime(date('Y-m', strtotime("+1 month", strtotime(date('Y-m')))));
                                for ($i = 0; $i < 55; $i++) {
                                    $month = date('Y-m', strtotime("-$i month", $start_month));
                                    if ($month == date('Y-m')) {
                                        echo '<option selected="selected" value="' . $month . '">' . date('M-Y', strtotime("-$i month", $start_month)) . '</option>';
                                    } else {
                                        echo '<option value="' . $month . '">' . date('M-Y', strtotime("-$i month", $start_month)) . '</option>';
                                    }
                                }?>
                            </select>
                        </div>    
                    </div>
                    <div class="col-md-3">
                        <label class="col-sm-4" >Branch : </label>                        
                        <div class="col-md-8" >
                            <!--//    Add new function for bank search box  ---- Added By Nimisha 19/03/2019    Start //-->

<!--                            <select id="filterby_branch" name="filterby_branch" class="js-example-basic-site" style="width: 100%; "  onchange="refresh(this);  ">
                            </select>-->
                            <select id="filterby_branch" name="filterby_branch" class="form-control" onchange="filterEmployees(this);" >
                                <option value="">All</option>
                                <?php foreach ($arr_branches as $key => $value) { ?>                              
                                    <option  value="<?php echo $value['branch_code']; ?>"><?php echo $value['branch_name']; ?></option>
                                <?php } ?>
                            </select>
                        </div>    
                    </div> 
                    <div class="col-md-3">
                        <label class="col-sm-5" for="employee">Employee : </label>                                   
                        <div class="col-md-7">
                            <select id="emp_fkey" class="js-example-basic-employee" style="width: 100% ; ">
                            </select>
                        </div>    
                    </div>  
                     <!-- added by megha on 8_6_19 resigned employee data -->
                    <div class="col-md-2">
                        <div  style=" border: #aaaaaa 1px solid;border-radius: 5px;padding-left:15px;">
                            <input type="checkbox" value="1" name="resigned" id="resigned" style="width:16px;height:16px;margin:4px 0 0;">&nbsp;&nbsp;<label>Include Resigned  </label>
                        </div>
                    </div>
                     <div class="col-md-1">
                         <button onclick="showlessdata();" class="btn btn-success pull-right"><li class="fa fa-eye"></li></button>
                    </div>
                    <div class="col-md-2 pull-right" style="margin-top:15px;">
                    <!-- end -->
                        <div class="col-md-12 pull-right">
                            <button type="button" id="btn-processregister" class="btn btn-primary pull-right" onclick="filterRegister();">List Register</button>
                           
                        </div>
                    </div>
                </div>



            </div>
             <div>
                <div class="box-body" style="    margin-top: -11px;">
                    <div id="load" style="margin:10px 0">

                    </div>
                </div>
            </div> 
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
</section>
