<meta name="viewport" content="width=device-width, initial-scale=1.0">
<style>
@import url('https://fonts.googleapis.com/css?family=Open+Sans');
/*.content {
    min-height: 250px;
    padding: 15px;
    margin-right: auto;
    margin-left: auto;
    padding-left: 10px;
    padding-right: 0px;
    font-family: 'Open+Sans', serif;
    font-weight: lighter;
    font-style: inherit;
}*/
.fc-basic-view tbody .fc-row {
    min-height: 1em !important;
}
.h2, h2 {
    /* margin-top: -8px; */
    font-size: 15px;
}
.fc-toolbar {
    text-align: center;
    margin-bottom: 0em;
    margin-top: -2em;
}
body .fc {
    font-size: 8px;
}
.fc-day-number {
    font-size: 15px;
    font-weight: 300;  
}
.fc-event {
    
    line-height: 0.3;
    
}
.fc td, .fc th {
    
    /*background: #0c5e8e;*/
    color: #0c5e8e;
}
.fc td.fc-today {
    border-style: double;
    color: #121213;
}
.fc-widget-header:first-of-type, .fc-widget-content:first-of-type {
    border-left: 0;
    font-size: 12px;
    border-right: 0;
}
.fc button {
    background: #0c5e8e;
    color: white;
}
/*.content-wrapper, .right-side {
    min-height: 100%;
    background-color: white;
    z-index: 698;
    margin-left: 110px;
}
    
    .sidebar-mini.sidebar-collapse .content-wrapper, .sidebar-mini.sidebar-collapse .right-side, .sidebar-mini.sidebar-collapse .main-footer {
    margin-left: 27px!important;
    z-index: 840;*/
/*}*/

@media only screen and (min-width: 600px) {
  /* For tablets: */
  .col-sm-1 {width: 8.33%;}
  .col-sm-2 {width: 16.66%;}
  .col-sm-3 {width: 25%;}
  .col-sm-4 {width: 33.33%;}
  .col-sm-5 {width: 41.66%;}
  .col-sm-6 {width: 50%;}
  .col-sm-7 {width: 58.33%;}
  .col-sm-8 {width: 66.66%;}
  .col-sm-9 {width: 75%;}
  .col-sm-10 {width: 83.33%;}
  .col-sm-11 {width: 91.66%;}
  .col-sm-12 {width: 100%;}
}
@media only screen and (min-width: 768px) {
  /* For desktop: */
  .col-1 {width: 8.33%;}
  .col-2 {width: 16.66%;}
  .col-3 {width: 25%;}
  .col-4 {width: 33.33%;}
  .col-5 {width: 41.66%;}
  .col-6 {width: 50%;}
  .col-7 {width: 58.33%;}
  .col-8 {width: 66.66%;}
  .col-9 {width: 75%;}
  .col-10 {width: 83.33%;}
  .col-11 {width: 91.66%;}
  .col-12 {width: 100%;}
}

</style>
 <section class="content-header">
    <?php if(count($arr_employees_pics) > 0)
        {?>
        <div class="alert alert-warning alert-dismissible">
         <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
         <h4 style="font-size:1vw;"><i class="icon fa fa-birthday-cake"></i>    Happy Birthday </h4>
         <h5 style="font-size:1vw;" >Wishing you the best on your birthday and everything good in the year ahead!!!</h5>
        </div>
        <?php } ?>
<?php if(count($arr_employees_work) > 0)
        {?>
        <div class="alert alert-success alert-dismissible">
         <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
         <h4 style="font-size:1vw;"><i class="icon fa fa-trophy"></i>    Happy Work Anniversary </h4>
         <h5 style="font-size:1vw;" >Thank you for being such a valuable member of our team. Wishing you the best for continued success!!!</h5>
        </div>
        <?php } ?>
</section> 
<section class="content">
    <div class="row">
        <div class="col-3 col-sm-12 col-xs-12"style="padding: 0%;" >
             <div class="col-12 col-sm-12 col-xs-12">
                    <div class="box ">
                        
<!---------Employee Profile    and  WEB punch--------------> 

                        <div class="widget-user-header bg-aqua-active box" style="border: #0c5e8e !important; height: 60px;background-color: #0c5e8e  !important;margin-top: -23px;">  
                            <h3 class="widget-user-username" style="font-size:15px;padding-left: 11px;font-family: 'Poiret One', serif;"><span style="font-size: 10px;"> </span><br><span><?php echo  $this->Session->read('user_name'); ?> - <span style="font-size: 12px;"><?php echo $employeeid;?></span></span>  
                            <?php if($punch_type == 'S' ){ ?>
                            <button type="button" id="checkin"class="btn btn-success   " value="1" onclick="checkin(this);" style="margin-top: -5px;display: none;border-radius: 50%;height: 40px;width: 40px;position: absolute;right: 10px;margin-bottom: auto;padding-left: 12px;font-weight: bolder;">IN</button>
                            <button type="button"  id="checkout"class="btn btn-danger  " value="0" onclick="checkout(this);"style="margin-top: -5px;display: none;border-radius: 50%;height: 40px;width: 40px;position: absolute;right: 10px;margin-bottom: auto;padding-left: 4px;font-weight: bolder;">OUT</button>     
                         <?php } ?></h3>         
                        </div>
                        <div class="widget-user-image" style="padding-top: 21px;">
                            <img class="img-circle" style="width: 27%;display: block;margin-left: auto;margin-right: auto;margin-top: -66px;position: relative;height: 80px;" src="<?php echo $this->webroot.$user['avatar']; ?>" alt="User Avatar">
                        </div>
                         <div class="box-footer no-padding">
                              <ul class="nav nav-stacked">
                                    <li style="font-weight: bold;"><a href="#">Designation <span style="float: right;"><?php echo isset($empinfo['0']['employee_info']['designation']) ? $empinfo['0']['employee_info']['designation'] : ''; ?></span> </a></li>
                                    <li style="font-weight: bold;"><a href="#">Department   <span style="float: right;"><?php echo isset($empinfo['0']['employee_info']['department']) ? $empinfo['0']['employee_info']['department'] : ''; ?></span></a></li>
                                    <li style="font-weight: bold;"><a href="#">Branch  <span style="float: right;"><?php echo isset($empinfo['0']['employee_info']['branch']) ? $empinfo['0']['employee_info']['branch'] : ''; ?> </span></a></li>
                                    <li style="font-weight: bold;"><a href="#">Joining Date  <span style="float: right;"><?php echo isset($empinfo['0']['employee_info']['joining_date']) ? $empinfo['0']['employee_info']['joining_date'] : '';                        ?> </span></a></li>
                              </ul>
                        </div>
                    </div>
                </div>
            <!---------Last punch --------------> 
              
                <div class="col-12 col-sm-6 col-xs-6" >
                    <div class="small-box bg-aqua" style=" background-color: #0c5e8e !important;">
                        <div class="inner">
                             <h6 style="text-align: center;margin-top: -6px;"> Last Punch at</h6>
                             <?php $punch = isset($lastpunch['0']['device_attandance']['LOGDATE']) ? $lastpunch['0']['device_attandance']['LOGDATE'] :'00'; 
                                   $lastpunchdate =  date("d-m-Y", strtotime($punch));
                                   $lastpunchtime =  date("H:i:s", strtotime($punch));
                                   $direction = isset($dir)&& $dir == 'out' ? 'IN' :'OUT'; 
                                    ?>
                             <p style="text-align: center;font-weight: bolder;margin-top: -10px;"><?php echo isset($lastpunchtime) ? $lastpunchtime :'00:00:00';?> - <?php echo isset($lastpunchdate) ? $lastpunchdate :'00:00:0000';?> - <?php echo $direction;?></p>
                        </div>
                        <a href="#" class="small-box-footer"  onclick="gotoattendancereport();"style="margin-top: -21px;height: 18px;font-size: 10px;">
                        More Info <i class="fa fa-arrow-circle-right"></i>
                        </a>
                    </div>
                </div> 
            <!---------Session or Shift policy --------------> 
   
                <div class="col-12 col-sm-6 col-xs-6" >
                    <div class="small-box bg-aqua" style=" background-color: #0c5e8e !important;">
                        <div class="inner">
                             <h6 style="text-align: center;margin-top: -6px;">Your Shift Starts From</h6>
<?php  $off_dutty1 = isset($shiftpolicy['0']['working_day_time_procedures']['off_dutty1'])? $shiftpolicy['0']['working_day_time_procedures']['off_dutty1']:0;
       $off_dutty2 = isset($shiftpolicy['0']['working_day_time_procedures']['off_dutty2'])? $shiftpolicy['0']['working_day_time_procedures']['off_dutty2']:0;
      if($off_dutty2> 0){
                      $off = $off_dutty2;
                      }else{
                      $off = $off_dutty1;
                      } ?>
                             <p style="text-align: center;font-weight: bolder;margin-top: -10px;">
<?php echo isset($shiftpolicy['0']['working_day_time_procedures']['on_dutty1']) ? $shiftpolicy['0']['working_day_time_procedures']['on_dutty1'] :'00';?> -
 <?php echo isset($off) ? $off :'00';?></p>                                                    
                        </div>
                        <a href="#" class="small-box-footer" onclick="getshiftreport();" style="margin-top: -21px;height: 18px;font-size: 10px;">
                        More info <i class="fa fa-arrow-circle-right"></i>
                        </a>
                    </div>
                </div>   
        </div>
        <div class="col-9 col-sm-12 col-xs-12">
            <div class="row"style="padding-right: 0;">
                <div class="col-12">
                    <!----- Present Days  -------->
                    <div class="col-4 col-sm-4 col-xs-4">    
                    <div class="small-box bg-aqua" style=" background-color: #0c5e8e !important;">
                        <div class="inner">
                            <h4 style="font-weight: bolder;text-align: center;margin-top: -6px;"><?php echo $pr_count; ?></h4>
                            <p style="text-align: center;margin-top: -10px;">Present Days</p>
                        </div>
                        <a href="#" class="small-box-footer" onclick="gotoattendancereport();" style="margin-top: -21px;height: 18px;font-size: 10px;">
                        More info <i class="fa fa-arrow-circle-right"></i>
                        </a>
                    </div>
                    </div> 
                    <!----- Absent Days  -------->

                <div class="col-4 col-sm-4 col-xs-4">
                    <div class="small-box bg-aqua" style=" background-color: #0c5e8e !important;">
                        <div class="inner">
                            <h4 style="font-weight: bolder;text-align: center;margin-top: -6px;"><?php  echo $absentdays; ?></h4>
                            <p style="text-align: center;margin-top: -10px;">Absent Days</p>
                        </div>
                        <a href="#" class="small-box-footer" onclick="gotoattendancereport();" style="margin-top: -21px;height: 18px;font-size: 10px;">
                        More info <i class="fa fa-arrow-circle-right"></i>
                        </a>
                    </div>
                </div> 

<!----- Missed Punches  -------->

                <div class="col-4 col-sm-4 col-xs-4"style="">
                    <div class="small-box bg-aqua" style=" background-color: #0c5e8e !important;">
                        <div class="inner" style="padding-left: 5px;">
                            <h4 style="font-weight: bolder;text-align: center;margin-top: -6px;"><?php echo $misspunch;?></h4>
                            <p style="text-align: center;margin-top: -10px;">Missed Punches</p>
                        </div>
                        <!-- <a href="#" class="small-box-footer" onclick="gotoeditpunch();"style="margin-top: -21px;height: 18px;font-size: 10px;">
                        More info <i class="fa fa-arrow-circle-right"></i>
                        </a>-->
                    </div>
                </div> 
                </div>
            </div>
            <div class="row">
                <div class="col-12">
                    <!----- Salary Analysis ---  Bar Chart -------->
                     <div class="col-8 col-sm-12 col-xs-12" >
                    
                          <div class="box" style="border : white;height: 360px;">
                        <div class="box-header with-border" style="background: #0c5e8e ;color: white;">
                            <h3 class="box-title">Attendance Summary</h3>
                        </div>
                        <div class="box-body">
                            <?php if($showlinechart != 'No') {?>
                            <div class="chart">
                                <canvas id="lineChart" style="height:230px;"></canvas>
                            </div>
                            <div class="row" style="margin-top: 13px;margin-left: 25px;">
                                <div class="col-1 col-sm-1 col-xs-1" style="background: #0c5e8e;height: 15px;"></div><div class="col-2 col-sm-2 col-xs-2" style="padding-left:8px;">Working Days</div>
                                <div class="col-1 col-sm-1 col-xs-1" style="background: #00a65a;height: 15px;"></div><div class="col-2 col-sm-2 col-xs-2" style="padding-left:8px;">Present Days</div>
                                <div class="col-1 col-sm-1 col-xs-1" style="background: #edf111;height: 15px;"></div><div class="col-2 col-sm-2 col-xs-2 ">Leave Days</div>
                                 <div class="col-1 col-sm-1 col-xs-1" style="background: #e01818;height: 15px;"></div><div class="col-2 col-sm-2 col-xs-2">Lop Days</div>
                            </div>
                            <?php }else{?>
                            <div class="col-12">  <img src="<?php echo $this->webroot;?>img/attendance.png" style="height: 295px; " > </div>
                            <?php } ?>
                        </div>
                    </div>
                </div>
                    <!----- Events -------->

                <div class="col-4 col-sm-12 col-xs-12" style="">
                    <div class="box" style="border : white;">
                        <div class="box-header with-border" style="background: #0c5e8e ;color: white;">
                            <h3 class="box-title">Calender</h3>
                        </div>
                        <div class="box-body" style="padding-bottom: inherit;height: 318px;">
                            <div id="calendar"></div>   
                        </div>
                    </div>
                </div>
                </div>
            </div>
        </div>
        
    </div>
    <div class="row">
        <div class="col-12">
            <!---------Today's Working Time --------------> 
            <div class="col-3 col-sm-6 col-xs-6" >
                    <div class="small-box bg-aqua" style=" background-color: #0c5e8e !important;">
                        <div class="inner">
                             <h6 style="text-align: center;margin-top: -6px;">Today's Working Time</h6>
                             <p style="text-align: center;font-weight: bolder;margin-top: -10px;"><input type="hidden" value="" id="settimer">
                             <span id="clock" class="inactive" style="font-size: 13px;color: white;background: #0c5e8e;">
                              00:00
                             </span></p>                                         
                        </div>
                        <a href="#" class="small-box-footer" id="todayattendance"" style="margin-top: -21px;height: 18px;font-size: 10px;">
                        Today's Attendance Details <i class="fa fa-arrow-circle-right"></i>
                        </a>
                    </div>
                </div>
            <div class="col-3 col-sm-6 col-xs-6" >
                    <div class="small-box bg-aqua" style=" background-color: #0c5e8e !important;">
                        <div class="inner"style="padding-left: 0;">
                             <h6 style="text-align: center;margin-top: -6px;">This Month Attendance - <?php echo isset($percentage_total)?$percentage_total."%":0; ?></h6>
                             <p style="text-align: center;font-weight: bolder;margin-top: -10px;">
                             <div class="progress progress-sm active " style="padding-left: 0px;margin-right: 15px;margin-left: 15px;margin-top: 13px;">
                                <div class="progress-bar progress-bar-success progress-bar-striped" role="progressbar" aria-valuenow="<?php echo isset($percentage_total) ? $percentage_total : 0; ?>" aria-valuemin="0" aria-valuemax="100" style="width: <?php echo isset($percentage_total) ? $percentage_total . "%" : 0; ?>">
                                     <span class="sr-only">20% Complete</span>
                                </div>
                            </div </p>                                                    
                        </div>
                        <a href="#" class="small-box-footer" id="thismonth"style="margin-top: -26px;height: 18px;font-size: 10px;">
                        More info <i class="fa fa-arrow-circle-right"></i>
                        </a>
                    </div>
                </div>
             <div class="col-3 col-sm-6 col-xs-6" >
                    <div class="small-box bg-aqua" style=" background-color: #0c5e8e !important;">
                        <div class="inner">
                             <h6 style="text-align: center;margin-top: -6px;">Last Month Attendance</h6>
                             <p style="text-align: center;font-weight: bolder;margin-top: -10px;"><?php echo $lastmonthper_total;?>%</p>
                                                     
                        </div>
                        <a href="#" class="small-box-footer" id="lastmonth" style="margin-top: -21px;height: 18px;font-size: 10px;">
                       Last Month Attendance Details <i class="fa fa-arrow-circle-right"></i>
                        </a>
                    </div>
                </div>
             <div class="col-3 col-sm-6 col-xs-6" style="">
                    <div class="small-box bg-aqua" style=" background-color: #0c5e8e !important;">
                        <div class="inner" style="padding-left: 0;padding-right: 0;">
                             <h6 style="text-align: center;margin-top: -6px;">Total Leaves Taken in This Year</h6>
                             <p style="text-align: center;font-weight: bolder;margin-top: -10px;"><?php echo $yearly_leavecount;?></p>
                        </div>
                        <a href="#" class="small-box-footer" onclick="gotoleavereports();" style="margin-top: -21px;height: 18px;font-size: 10px;">
                        Leave Details <i class="fa fa-arrow-circle-right"></i>
                        </a>
                    </div>
                </div>

        </div>
    </div>
    <div class="row">
        <div class="col-12">
            <!---------Last Month Attendance Summary --------------> 
    
                <div class="col-3 col-sm-12 col-xs-12">
                    
                    
                    <div class="box" style="border : white;height: 445px;">
                        <div class="box-header with-border" style="background: #0c5e8e ;color: white;">
                            <h3 class="box-title" style="font-size:15px;">Attendance Summary  <?php echo $last_date; ?> </h3>                          
                        </div>
                        <div class="box-body chart-responsive">
                            <?php if($showdonutchart !='No'){?>
                             <div class="chart" id="sales-chart" style="height: 300px; position: relative;"></div>
                             <div style="    text-align: center;font-size: large; padding-top: 15px;"> Calander Days <?php echo $calanderdays;?></div>
                            <?php } else {?><div class="col-12">  <img src="<?php echo $this->webroot;?>img/donut.png" style=""> </div><?php }?>
                        </div>
                    </div>
                </div>
<!--               <div class="col-5 col-sm-12 col-xs-12">
                    <div class="box" style="border : white;height: 445px;">
                        <div class="box-header with-border" style="background: #0c5e8e ;color: white;">
                           <h3 class="box-title">Salary Analysis</h3>
                        </div>
                        <div class="box-body">
                            <?php if( $showbarchart !='No'){?>                           
                            <div class="chart" style="padding-top: 4%;">
                                 <canvas id="barChart" style="height:230px"></canvas>
                            </div>
                            <div class="row" style="padding-top: 3%;">
                                <div class="col-1 col-sm-1 col-xs-1" style="    background: #d2d6de;margin-left: 30px;height: 15px;"></div>
                                <div class="col-4 col-sm-2 col-xs-3"style="padding-right: 0px;">Net Salary</div>
                                <div class="col-1 col-sm-1 col-xs-1" style="    background: #0c5e8e;margin-left: 30px;height: 15px;"></div>
                                <div class="col-4 col-sm-1 col-xs-3">Gross Salary</div>
                                
                            </div>
                            <?php }
                            else{?><div class="col-6">  <img src="<?php echo $this->webroot;?>img/salary.png" style="width: fit-content;"> </div>
                            <?php } ?>
                                
                        </div>
                    </div>
                </div>-->
                <!----- Location Reports -------->

                <div class="col-9 col-sm-12 col-xs-12" style="">
                    <div class="box" style="border : white;height: 445px;">
                        <div class="box-header with-border" style="background: #0c5e8e ;color: white;">
                            <h3 class="box-title" style="font-size:15px;">Location Reports</h3>                           
                        </div>
                        <div class="box-body">            
                            <div class="nav-tabs-custom">
                                <ul class="nav nav-tabs">
                                    <li class="active"><a href="#tab_1" data-toggle="tab">Location Updates</a></li>
                                    <li><a href="#tab_2" data-toggle="tab">Tracking Updates</a></li>
<!--                                    <li><a href="#tab_3" data-toggle="tab">Client Visits</a></li>-->

                                </ul>
                                <div class="tab-content" style="height: 345px;">
                                    <div class="tab-pane active" id="tab_1">
                                        <div id="loadmap">
                                           <li class="fa fa-spinner fa-spin"style="margin-left: 191px;font-size: xx-large;margin-top: 142px;"></li> 
                                        </div>
                                    </div>
                                    <div class="tab-pane" id="tab_2">
                                        <div id="locationtracking">
                                           <li class="fa fa-spinner fa-spin"></li> 
                                        </div>
                                    </div>
<!--                                    <div class="tab-pane" id="tab_3">

                                    </div>-->
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
        </div>
    </div>
</section>
<script src="https://maps.googleapis.com/maps/api/js?key=AIzaSyBwj4pV2RX2vS6dra9FDGibWiEzYqsbIbc" type="text/javascript"></script> 
<script>
     
    function gotoattendancereport()
    {
        $('#loaders').show();
        $('#container').load(livesite+'Empreport/attendanceReports',function(){
        $('#loaders').hide();
        });
    }
    function gotoeditpunch()
    {
        $('#loaders').show();
        $('#container').load(livesite+'EditPunches/employeeeditpunch',function(){
        $('#loaders').hide();
        });
    }
    function gotoleavereports()
    {
        $('#loaders').show();
        $('#container').load(livesite+'Empreport/leavepolicyreport',function(){
        $('#loaders').hide();
        });
    }
    function gotoleaverequests()
    {
        $('#loaders').show();
        $('#container').load(livesite+'LeaveRequest',function(){
        $('#loaders').hide();
        });
    }
    function getshiftreport()
    {
//        $('#loaders').show();
        $('#container').load(livesite+'empreport/shiftpolicyreport',function(){
//        $('#loaders').hide();
        });
    }
    function checkin(s)
    {
        var x = $('#checkin').val();
        $(s).attr("disabled",true);
        var y =  document.getElementById("demo");
        if (navigator.geolocation) 
        {
            navigator.geolocation.getCurrentPosition(showPosition);
        } else { 
            y.innerHTML = "Geolocation is not supported by this browser.";
        }
        function showPosition(position) 
        {
            var lat = position.coords.latitude;
            var long = position.coords.longitude;
            $.ajax({
                url: livesite+'Dashboard/checkpunch/'+ x + '/' + lat +  '/'+ long,
                type:"POST",
                data: {
                         x: x
                     },
                success: function (responseText, statusText, xhr, $form) 
                {
                     var response = JSON.parse(responseText); 
                     if(response.success === true)
                     {
                        $("#checkin").hide();
                        $("#checkout").show();
                        location.reload();
                        $.notify("Punch Success",{
                            type: 'success',
                            allow_dismiss: false
                        });
                     }
                }
            });
        }
    }
    function checkout(s)
    {   
        var x = $('#checkout').val();
        $(s).attr("disabled",true);
        var y =  document.getElementById("demo");
        if (navigator.geolocation) 
        {
            navigator.geolocation.getCurrentPosition(showPosition);
        } else { 
            y.innerHTML = "Geolocation is not supported by this browser.";
        }
        function showPosition(position) 
        {
            var lat = position.coords.latitude;
            var  long = position.coords.longitude;
            $.ajax({
                url: livesite+'Dashboard/checkpunch/'+ x + '/' + lat +  '/'+ long,
                type:"POST",
                data: {
                    x: x
                },
                success: function (responseText, statusText, xhr, $form) 
                {
                    var response = JSON.parse(responseText); 
                    if(response.success === true)
                    {
                       $("#checkout").hide();
                       $("#checkin").show();
                       location.reload();
                       $.notify("Punch Success",{
                            type: 'success',
                             allow_dismiss: false
                        });
                    }
                }
            });
        }
    }
    
    function Modal(name,job)
    {
  //  alert(name);
        var url = 'empform/'+name+'/'+job;
        showSmallModalForm(url)    
    }
    
    var showbar = '<?php echo $showbarchart; ?>';
    var showline = '<?php echo $showlinechart?>';
    var showdonut = '<?php echo $showdonutchart ?>'
    
    $(document).ready(function () {
        <?php
        if (isset($settings_runner) && $settings_runner != '1') {
        ?>
                                var url = livesite + 'dashboard/general_setting';
                                showModalForm(url);
        <?php
        }
        ?>
         $('#loadmap').load(livesite+'Dashboard/loadmap',function(){
//         $('#loaders').hide();
        });
        $('#locationtracking').load(livesite+'Dashboard/locationtracking',function(){
//         $('#loaders').hide();
        });
        
        
        $('#lastmonth').on('click',function(){
            var url = livesite+'Dashboard/lastmonthattendanceinfo';
            showModalForm(url)    
        });
        $('#todayattendance').on('click',function(){
            var url = livesite+'Dashboard/todayattendance';
            showModalForm(url)    
        });
        $('#thismonth').on('click',function(){
            var url = livesite+'Dashboard/thismonthattendanceinfo';
            showModalForm(url)    
        });
        $.fn.dataTable.ext.search.push(
            function( settings, data, dataIndex ) {
                if(settings.sTableId == 'empleaverequests'){
                    var filteredByMonth = false;

                    var month = $('#leaverequests-month-filter').val();
                    var fromdate = data[3];
                    var arr_fromdate = fromdate.split('-');
                    if(month == (arr_fromdate[0]+'-'+arr_fromdate[1])){
                        filteredByMonth = true;
                    }

                    if(filteredByMonth){
                        return true;
                    }
                    return false;
                }else{
                    return true;
                }
            }
        );

//-----Charts-------------//
if(showline!== 'No'){
    var lineChartCanvas = $('#lineChart').get(0).getContext('2d');
    // This will get the first returned node in the jQuery collection.
    var lineChart       = new Chart(lineChartCanvas);

    var areaChartData = {
                                              labels  : ['<?php echo $chartmonth; ?>'],
                                              datasets: [
                                                {
                                                  label               : 'Electronics',
                                                  fillColor           : '#0c5e8e',
                                                  strokeColor         : '#0c5e8e',
                                                  pointColor          : '#0c5e8e',
                                                  pointStrokeColor    : '#c1c7d1',
                                                  pointHighlightFill  : '#fff',
                                                  pointHighlightStroke: '#0c5e8e',
                                                  data                : [<?php echo $wrkdays; ?>]
                                                },
                                                {
                                                  label               : 'Electronics',
                                                  fillColor           : '#00a65a',
                                                  strokeColor         : '#00a65a',
                                                  pointColor          : '#00a65a',
                                                  pointStrokeColor    : '#00a65a',
                                                  pointHighlightFill  : '#fff',
                                                  pointHighlightStroke: '#00a65a',
                                                  data                : [<?php echo $pdays; ?>]
                                                },
                                                {
                                                  label               : 'Digital Goods',
                                                  fillColor           : '#edf111',
                                                  strokeColor         : '#edf111',
                                                  pointColor          : '#edf111',
                                                  pointStrokeColor    : '#edf111',
                                                  pointHighlightFill  : '#fff',
                                                  pointHighlightStroke: '#edf111',
                                                  data                : [<?php echo $ldays; ?>]
                                                },
                                                {
                                                  label               : 'Digital Goods',
                                                  fillColor           : '#e01818',
                                                  strokeColor         : '#e01818',
                                                  pointColor          : '#e01818',
                                                  pointStrokeColor    : '#e01818',
                                                  pointHighlightFill  : '#fff',
                                                  pointHighlightStroke: '#e01818',
                                                  data                : [<?php echo $lopdays; ?>]
                                                },
                                                  
                                                
                                              ]
                                            };

    var lineChartOptions = {
      //Boolean - If we should show the scale at all
      showScale               : true,
      //Boolean - Whether grid lines are shown across the chart
      scaleShowGridLines      : true,
      //String - Colour of the grid lines
      scaleGridLineColor      : 'rgba(0,0,0,.05)',
      //Number - Width of the grid lines
      scaleGridLineWidth      : 1,
      //Boolean - Whether to show horizontal lines (except X axis)
      scaleShowHorizontalLines: true,
      //Boolean - Whether to show vertical lines (except Y axis)
      scaleShowVerticalLines  : true,
      //Boolean - Whether the line is curved between points
      bezierCurve             : true,
      //Number - Tension of the bezier curve between points
      bezierCurveTension      : 0.3,
      //Boolean - Whether to show a dot for each point
      pointDot                : true,
      //Number - Radius of each point dot in pixels
      pointDotRadius          : 4,
      //Number - Pixel width of point dot stroke
      pointDotStrokeWidth     : 1,
      //Number - amount extra to add to the radius to cater for hit detection outside the drawn point
      pointHitDetectionRadius : 20,
      //Boolean - Whether to show a stroke for datasets
      datasetStroke           : true,
      //Number - Pixel width of dataset stroke
      datasetStrokeWidth      : 2,
      //Boolean - Whether to fill the dataset with a color
      datasetFill             : false,
      //String - A legend template
      legendTemplate          : '<ul class="<%=name.toLowerCase()%>-legend"><% for (var i=0; i<datasets.length; i++){%><li><span style="background-color:<%=datasets[i].lineColor%>"></span><%if(datasets[i].label){%><%=datasets[i].label%><%}%></li><%}%></ul>',
      //Boolean - whether to maintain the starting aspect ratio or not when responsive, if set to false, will take up entire container
      maintainAspectRatio     : true,
      //Boolean - whether to make the chart responsive to window resizing
      responsive              : true
    };
    lineChart.Line(areaChartData, lineChartOptions);
    
    }
    //--------------- Line Chart ---------------//
   
   
        //-------------
        //- BAR CHART -
        //-------------
//        if(showbar !== 'No'){
//        var barChartCanvas                   = $('#barChart').get(0).getContext('2d');
//        var barChart                         = new Chart(barChartCanvas);
//        var barChartData                     = {
//                                              labels  : ['<?php echo $arr_month; ?>'],
//                                              datasets: [
//                                                {
//                                                  label               : 'Electronics',
//                                                  fillColor           : 'rgba(210, 214, 222, 1)',
//                                                  strokeColor         : 'rgba(210, 214, 222, 1)',
//                                                  pointColor          : 'rgba(210, 214, 222, 1)',
//                                                  pointStrokeColor    : '#c1c7d1',
//                                                  pointHighlightFill  : '#fff',
//                                                  pointHighlightStroke: 'rgba(220,220,220,1)',
//                                                  data                : [<?php echo $arr_grosssalary; ?>]
//                                                },
//                                                {
//                                                  label               : 'Digital Goods',
//                                                  fillColor           : 'rgba(60,141,188,0.9)',
//                                                  strokeColor         : 'rgba(60,141,188,0.8)',
//                                                  pointColor          : '#3b8bba',
//                                                  pointStrokeColor    : 'rgba(60,141,188,1)',
//                                                  pointHighlightFill  : '#fff',
//                                                  pointHighlightStroke: 'rgba(60,141,188,1)',
//                                                  data                : [<?php echo $arr_netsalary; ?>]
//                                                }
//                                                                 
//                                              ]
//                                            };
//        barChartData.datasets[1].fillColor   = '#0c5e8e';
//        barChartData.datasets[1].strokeColor = '#0c5e8e';
//        barChartData.datasets[1].pointColor  = '#0c5e8e';
//        var barChartOptions                  = {
//          //Boolean - Whether the scale should start at zero, or an order of magnitude down from the lowest value
//          scaleBeginAtZero        : true,
//          //Boolean - Whether grid lines are shown across the chart
//          scaleShowGridLines      : true,
//          //String - Colour of the grid lines
//          scaleGridLineColor      : 'rgba(0,0,0,.05)',
//          //Number - Width of the grid lines
//          scaleGridLineWidth      : 1,
//          //Boolean - Whether to show horizontal lines (except X axis)
//          scaleShowHorizontalLines: true,
//          //Boolean - Whether to show vertical lines (except Y axis)
//          scaleShowVerticalLines  : true,
//          //Boolean - If there is a stroke on each bar
//          barShowStroke           : true,
//          //Number - Pixel width of the bar stroke
//          barStrokeWidth          : 2,
//          //Number - Spacing between each of the X value sets
//          barValueSpacing         : 5,
//          //Number - Spacing between data sets within X values
//          barDatasetSpacing       : 1,
//          //String - A legend template
//          legendTemplate          : '<ul class="<%=name.toLowerCase()%>-legend"><% for (var i=0; i<datasets.length; i++){%><li><span style="background-color:<%=datasets[i].fillColor%>"></span><%if(datasets[i].label){%><%=datasets[i].label%><%}%></li><%}%></ul>',
//          //Boolean - whether to make the chart responsive
//          responsive              : true,
//          maintainAspectRatio     : true
//        };
//
//        barChartOptions.datasetFill = false;
//        barChart.Bar(barChartData, barChartOptions);
//        }

            $.ajax({
                url: livesite+'Dashboard/lastpunch',         
                success: function (responseText, statusText, xhr, $form) 
                {
                    var response = JSON.parse(responseText); 
                    if(response.success === true)
                    {
                        $("#checkout").hide();
                        $("#checkin").show();
                    }
                    else
                    {
                        $("#checkin").hide();
                        $("#checkout").show();
                    }
                }
            });
            var interval = setInterval( function () {
            $.ajax({
                        url:livesite+ 'Dashboard/Timers',
                        success: function(resp)
                        {
                            $('#settimer').val($.parseJSON(resp).set);
                            if($.parseJSON(resp).run == 1)
                            //alert($('#settimer').val());
                            {
                                startTimer();
                            }
                            else
                            {
                                FixedTimer();
                            }

                        }
                    });
            //startTimer();
            clearInterval(interval);
        }, 6000 );

        var alarmSound = new Audio('http://demo.tutorialzine.com/2015/04/material-design-stopwatch-alarm-and-timer/assets/06_Urban_Beat.mp3');
        //startTimer();

        var monthNames = [ "January", "February", "March", "April", "May", "June", "July", "August", "September", "October", "November", "December" ]; 
        var dayNames= [ "Sunday","Monday","Tuesday","Wednesday","Thursday","Friday","Saturday" ];

        var newDate = new Date();
        newDate.setDate(newDate.getDate());

        setInterval( function() {
                var hours = new Date().getHours();
                $(".hour").html(( hours < 10 ? "0" : "" ) + hours);
            var seconds = new Date().getSeconds();
                $(".second").html(( seconds < 10 ? "0" : "" ) + seconds);
            var minutes = new Date().getMinutes();
                $(".minute").html(( minutes < 10 ? "0" : "" ) + minutes);

            $(".month span,.month2 span").text(monthNames[newDate.getMonth()]);
            $(".date span,.date2 span").text(newDate.getDate());
            $(".day span,.day2 span").text(dayNames[newDate.getDay()]);
            $(".year span").html(newDate.getFullYear());
        }, 1000);	

        $(".outer").on({
            mousedown:function(){
                $(".dribbble").css("opacity","1");
            },
            mouseup:function(){
                $(".dribbble").css("opacity","0");
            }
        });
         $('#present').on('click',function(){
                //    alert("done");
                   var url = 'presenttoday';
                   showModalForm(url)    
        })

//-----------------CALANDER-------------------//  

            var date = new Date();
            var d    = date.getDate(),
            m    = date.getMonth(),
            y    = date.getFullYear();
        $('#calendar').fullCalendar({
          header    : {
            left  : 'prev',
            center: 'title',
            right : 'next'
          },
          buttonText: {
            today: 'today',
            month: 'month',
            week : 'week',
            day  : 'day'
          },
          eventRender: function(eventObj, $el) {
            
          $el.tooltip({
             
            title: eventObj.name,
            content: eventObj.name,
            trigger: 'hover',
            placement: 'top',
            container: 'body'
          });
        },  
//        loading: function (bool) {
//                 $("#calendar").html('');
//
//    },
        
//          height: 150,
//          contentHeight: 232,
//          aspectRatio: 100,
          //Random default events
          events    : livesite+ 'Dashboard/empcalendar',
//          events: [
//    {
//      start: '2014-11-10T10:00:00',
//      end: '2014-11-10T16:00:00',
//      rendering: 'background'
//    }
//  ]

          editable  : false,
          droppable : false, // this allows things to be dropped onto the calendar !!!
          drop      : function (date, allDay) { // this function is called when something is dropped

            // retrieve the dropped element's stored Event Object
            var originalEventObject = $(this).data('eventObject');

            // we need to copy it, so that multiple events don't have a reference to the same object
            var copiedEventObject = $.extend({}, originalEventObject);

            // assign it the date that was reported
            copiedEventObject.start           = date;
            copiedEventObject.allDay          = allDay;
            copiedEventObject.backgroundColor = $(this).css('background-color');
            copiedEventObject.borderColor     = $(this).css('border-color');

            // render the event on the calendar
            // the last `true` argument determines if the event "sticks" (http://arshaw.com/fullcalendar/docs/event_rendering/renderEvent/)
            $('#calendar').fullCalendar('renderEvent', copiedEventObject, true);

            // is the "remove after drop" checkbox checked?
            if ($('#drop-remove').is(':checked')) {
              // if so, remove the element from the "Draggable Events" list
              $(this).remove();
            }

          }
          
        });
        
//         $('.fc-prev-button').click(function(){  
//       
//       $("#calendar").html('<li class="fa fa-spinner fa-spin" style="margin-left: 2em;margin-top: 2em;font-size: 30px;"></li>');
      
      // $('#showattendancedetails').css({'height':'200px'});
//      retrn false;
//   });
       
       //DONUT CHART// 
       //#0c5e8e; blue
       //#edf111 yellow
       //#60e1ef skyblue
       //00a65a green
       //f56954 red
       if(showdonut !== 'No'){
            var donut = new Morris.Donut({
            element: 'sales-chart',
            resize: true,
            colors: ["#edf111", "#00a65a","#0c5e8e","#60e1ef","#e01818"],
            data: [
            {label: "Leave Days", value: <?php echo $leavedays; ?>},
            {label: "Present Days", value: <?php echo $presentdays; ?>},
            {label: "Holidays", value: <?php echo $holidays; ?>},
            {label: "Week Off", value: <?php echo $weekoff; ?>},
           
            {label: "Lop Days", value: <?php echo $lop; ?>} 
            ],
            hideHover: 'auto'
        });

}
});
  

  </script>