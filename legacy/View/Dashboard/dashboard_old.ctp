<style>

.zoom_img{
display:block;
opacity:2;
font-size:0px;
-moz-transition:-moz-transform 0.5s ease-in; 
-webkit-transition:-webkit-transform 0.5s ease-in; 
-o-transition:-o-transform 0.5s ease-in;
}

#present:hover .zoom_img{
font-size:10px;
overflow:hidden;
-moz-transform:scale(1.3); 
-webkit-transform:scale(1.3);
-o-transform:scale(1.3);
}
#absent:hover .zoom_img{
font-size:10px;
overflow:hidden;
-moz-transform:scale(1.3); 
-webkit-transform:scale(1.3);
-o-transform:scale(1.3);
}
#presentall:hover .zoom_img{
font-size:10px;
overflow:hidden;
-moz-transform:scale(1.3); 
-webkit-transform:scale(1.3);
-o-transform:scale(1.3);
}
.dashboard{
    width:25%;
}
/*edited by sinsiya on 17-06-2024*/
.pws_tabs_container ul.pws_tabs_controll li a {
    width: 180px;
    height: 56px;
   text-align:center;
    
}

 
    #todayattandence .serial-number {
        padding-left: 20px !important; /* Adjust padding as needed */
    }
    
    #todayattandence th.serial-number {
        padding-left: 20px !important; /* Adjust padding as needed */
    }
    #todayattandence .direction-column {
        padding-left: 60px !important; /* Adjust padding as needed */
    }

    
    #todayattandence th.direction-column {
        padding-left: 60px !important; /* Adjust padding as needed */
    }

  .sl-no-forward {
        padding-left: 20px !important; /* Adjust the value as needed */
    }
    .direction-forward {
        padding-left: 60px !important; /* Adjust the value as needed */
    }

/*edited  by ASHIN on 28-06-24*/
    #todayattandence th.date-column {
    width: 50px; /* Adjust the width as needed */
    }    

    #thismonthattandence th.date-column{
        width: 50px;
    }
    #lastmonthattandence th.date-column{
        width: 50px;
    }
    #loadlists th.date-column{
        width: 50px;
    }
    #misspunch th.date-column{
        width: 50px;
    }
    #reportproblem th.date-column{
        width: 50px;
    }
    #EmployeesUpdaets th.date-column{
        width: 50px;
    }
    #EmployeesEvents th.date-column{
        width: 50px;
    }
</style>
<section class="content-header">
    <h1 style="text-align:center;font-size:32px;">
        <!--<i class="fa fa-dashboard"></i> Dashboard-->
<i class=""></i> Dashboard  <!--edited by sinsiya 07-06-2024-->
        <!--<small>Control panel</small>-->
    </h1>
    <ol class="breadcrumb">
<!--        <li><a href="#"><i class="fa fa-dashboard"></i> Home</a></li>-->
<!--edited by sinsiya to change the date formate in to 07 june 2024-->
       <li class="active">
    <?php 
    function ordinalSuffix($day) {
        return $day;
    }
 //edited by ASHIN on 28-06-24
    $date = date("d-m-Y");         
    list($day, $month, $year) = explode('-', $date);

    $months = array('January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December');

    echo '<sub style="font-size: 18px; vertical-align: -0.07em;font-weight: bold;">' . ordinalSuffix($day) . ' ' . $months[$month - 1] . ' ' . $year . '</sub>';
    ?>
</li>

    </ol>
</section>

<!-- Main content -->

<section class="content">
<!--    <marquee style="background-color: lightblue;"><h2 style="color:#a01515;font-size:25px;">Year End Process should be completed for all, whose Leave Year is 01-01-2019 to 31-12-2019.</h2></marquee>-->
<!--    <div class="row">
        <div class="col-md-12">
            <div class="box box-success">
    <div class="box-header with-border">
        <div class="pull-right">
                  <h3 style="color:#3c8dbc;font-weight: bold;"><?php echo date('jS F Y'); ?></h3>
        </div>
                  <div class="box-tools pull-right">
                    <span class="label label-danger"></span>
                    
                  </div>
                </div>
            </div>
    </div>
        </div>-->
    <!-- Info boxes -->
    <div class="row">
        <div class="dashboard col-sm-6 col-xs-12">
            <div class="info-box"><!--edited by sinsiya 07-06-2024 to change the design-->
                <span class="info-box-icon bg-aqua"><!--<i class="ion ion-ios-people-outline"></i>--> <span class="fa-stack fa-md">
                        <i class="fa fa-users fa-stack-1x"></i>
                    </span></span>
                <!--edited by sinsiya 07-06-2024 to change the design-->
                <div class="info-box-content" style="text-align:center;">
                    <span class="info-box-text" style="text-transform: none;margin-top:25px;">Total Employees</span>
                          <!-- edited by sinsiya on 13-03-2025-->
                    <span class="info-box-number"><?php echo $presentall + $results1; ?></span>
                    <span class="info-box-text zoom_img pull-right"><i class="fa fa-info-circle"></i></span>
                </div><!-- /.info-box-content -->
            </div><!-- /.info-box -->
        </div>
         <div  id="presentall" class="dashboard col-sm-6 col-xs-12 ">
            <div class="info-box">
                <span class="info-box-icon bg-navy">
                    <span class="fa-stack fa-md">
                        <i class="fa fa-users fa-stack-1x"></i>
                    </span>
                </span><!--edited by sinsiya 07-06-2024 to change the design-->
                <div class="info-box-content" style="text-align:center;"> 
                    <span class="info-box-text" style="text-transform: none;margin-top:25px;">Present Today</span>
                    <span class="info-box-number cnt-presentall"><?php echo $presentall; ?></span>
                 <span class="info-box-text zoom_img pull-right"><i class="fa fa-info-circle"></i></span>
                </div><!-- /.info-box-content -->
            </div><!-- /.info-box -->
        </div>
        <div  id="present" class="dashboard col-sm-6 col-xs-12 ">
            <div class="info-box">
                <span class="info-box-icon bg-green">
                    <span class="fa-stack fa-md">
                        <i class="fa fa-users fa-stack-1x"></i>
                    </span>
                </span><!--edited by sinsiya 07-06-2024 to change the design-->
                <div class="info-box-content" style="text-align:center;">
                    <span class="info-box-text" style="text-transform: none;margin-top:25px;">Active Now</span>
                    <span class="info-box-number cnt-present"><?php echo $present; ?></span>
                    <span class="info-box-text zoom_img pull-right"><i class="fa fa-info-circle"></i></span>
                </div><!-- /.info-box-content -->
            </div><!-- /.info-box -->
        </div>
        <div class="clearfix visible-sm-block"></div>

        <div id="absent" class="dashboard col-sm-6 col-xs-12">
            <div class="info-box">
                <span class="info-box-icon bg-red">	<span class="fa-stack fa-md">

                        <i class="fa fa-users fa-stack-1x"></i>
                    </span>
                </span>
             <!--edited by sinisya 07-06-2024 remove the style and change the design --> 
                <div class="info-box-content" style="text-align:center;">
                    <span class="info-box-text" style="text-transform: none;margin-top:25px;">Absent Now</span>
 <!-- edited by sinsiya on 13-03-2025-->
                    <span class="info-box-number cnt-absent"><?php echo $results1; ?></span>
                    <span class="info-box-text zoom_img pull-right"><i class="fa fa-info-circle"></i></span>
                </div><!-- /.info-box-content -->
            </div><!-- /.info-box -->
        </div><!-- /.col -->
<!--        <div class="dashboard col-sm-6 col-xs-12">
            <div class="info-box">
                <span class="info-box-icon bg-yellow"><i class="fa fa-clock-o "></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">Total Leaves</span>
                    <span class="info-box-number"><?php echo $arr_empleaverequests; ?></span>
                </div>
            </div>
        </div>-->
    </div><!-- /.row -->
    <div style="height:20px;" class="row"></div>
    <div class="row">
        <div class="col-md-12">

            <div class="tabset0"> <!--edited by sinsiya 07-06-2024-->
                <div data-pws-tab="tab1" id="tab1" data-pws-tab-name="Today Attendance" style="height:1200px;">
                    <div class="row">
                        <div class="col-md-12">

                            <div class="box box-info"> 
                                <div class="box-header with-border">
                                    <h3 class="box-title">Today Attendance</h3>
                                    <div class="box-tools pull-right">
                                        <button style="background: rgb(221, 75, 57); " type="button" onclick="Modal('Latein','Daily')" name="latein" id="latein" class="btn btn-linkedin">Late In</button>
                                        <button style="background: #00a65a; " type="button" onclick="Modal('Earlyin','Daily')" class="btn btn-linkedin">Early In</button>
                                        <button style="background: #00a65a; " type="button" onclick="Modal('Lateout','Daily')" class="btn btn-linkedin">Late Out</button>
                                        <button style="background: rgb(221, 75, 57); " type="button" onclick="Modal('Earlyout','Daily')" class="btn btn-linkedin">Early Out</button>
                                        <!--button class="btn btn-box-tool" data-widget="collapse"><i class="fa fa-minus"></i></button>
                                        <button class="btn btn-box-tool" data-widget="remove"><i class="fa fa-times"></i></button-->
                                    </div>
                                </div><!-- /.box-header -->
                                <div class="box-body">
                                    <div class="table-responsive">
                                        <table class="table no-margin" id="todayattandence">
                                   <!--edited by sinsiya on 13-06-2024-->
                                            <thead>
                                                <tr>
                                                    <th>Sl No</th>
                                                    <th>Employee Name </th>
                                                    <th>Employee ID</th>
                                                    <th>Branch</th>
                                                    <th class="date-column">Date</th>      <!--edited by ASHIN on 28-06-24-->
                                                    <th>Time</th>
                                                  <!--<th>Check-In/Out</th>-->
                                                    <th>Direction ( IN/OUT)</th>
                                                    <th>Location</th>
                                                </tr>
                                            </thead>
                                        </table>
                                    </div><!-- /.table-responsive -->
                                </div><!-- /.box-body -->

                            </div>
                           <!-- <hr>-->
  <!-- <div id="load_data">-->
        <!--<div> <li class="fa fa-spinner fa-spin"></li> Loading ..... Please Wait </div>-->
   <!-- </div>-->
                        </div>
                       
                
                
                    </div>
                </div>
                <div data-pws-tab="tab2" id="tab2" data-pws-tab-name="This Month Attendance" style="height:1000px; ">
                    
                    <div class="row">
                        <div class="col-md-12">

                            <div class="box box-info">
                                <div class="box-header with-border">
                                    <h3 class="box-title">This Month Attendance</h3>
                                    <div class="box-tools pull-right">
                                        <button style="background: rgb(221, 75, 57); " type="button" onclick="Modal('Latein','Month')"  class="btn btn-linkedin">Late In</button>
                                        <button style="background: #00a65a; " type="button" onclick="Modal('Earlyin','Month')" class="btn btn-linkedin">Early In</button>
                                        <button style="background: #00a65a; " type="button" onclick="Modal('Lateout','Month')" class="btn btn-linkedin">Late Out</button>
                                        <button style="background: rgb(221, 75, 57); " type="button" onclick="Modal('Earlyout','Month')" class="btn btn-linkedin">Early Out</button>
                                        <!--button class="btn btn-box-tool" data-widget="collapse"><i class="fa fa-minus"></i></button>
                                        <button class="btn btn-box-tool" data-widget="remove"><i class="fa fa-times"></i></button-->
                                    </div>
                                </div><!-- /.box-header -->
                                <div class="box-body">
                                    <div class="table-responsive">
                                        <table class="table no-margin" id="thismonthattandence">
                                            <thead>
                                                <tr>
<!--edited by sinsiya on 13-06-2024-->
                                                    <th>Sl No</th>
                                                    <th>Employee Name</th>
                                                    <th>Employee ID</th>
                                                    <th>Branch</th>
                                                    <th class="date-column">Date</th>      <!--edited by ASHIN on 28-06-24-->
                                                    <th>Time</th>
                                                    <th>Direction ( IN/OUT)</th>
                                                    <th>Location</th>
                                                </tr>
                                            </thead>
                                        </table>
                                    </div><!-- /.table-responsive -->
                                </div><!-- /.box-body -->

                            </div>
                        </div>
<!--                        <div class="col-md-4">
                            <div class="box box-danger">
                                <div class="box-header with-border">
                                    <h3 class="box-title">Statistics</h3>

                                </div>
                                <div class="box-body chart-responsive">
                                    <canvas id="punchChart" height="180"></canvas>
                                </div> /.box-body 
                            </div>
                        </div>-->
                    </div>
                    
                </div>
                <div data-pws-tab="tab3" data-pws-tab-name="Last Month Attendance" id="tab3" style="height:1000px; ">
                    
                    <div class="row">
                        <div class="col-md-12">

                            <div class="box box-info">
                                <div class="box-header with-border">
                                    <h3 class="box-title">Last Month Attendance</h3>
                                    <div class="box-tools pull-right">
                                        <button style="background: rgb(221, 75, 57); " type="button" onclick="Modal('Latein','LastMonth')" name="latein" id="latein" class="btn btn-linkedin">Late In</button>
                                        <button style="background: #00a65a; " type="button" onclick="Modal('Earlyin','LastMonth')"  class="btn btn-linkedin">Early In</button>
                                        <button style="background: #00a65a; " type="button"  onclick="Modal('Lateout','LastMonth')" class="btn btn-linkedin">Late Out</button>
                                        <button style="background: rgb(221, 75, 57); " type="button" onclick="Modal('Earlyout','LastMonth')"  class="btn btn-linkedin">Early Out</button>
                                        <!--button class="btn btn-box-tool" data-widget="collapse"><i class="fa fa-minus"></i></button>
                                        <button class="btn btn-box-tool" data-widget="remove"><i class="fa fa-times"></i></button-->
                                    </div>
                                </div><!-- /.box-header -->
                                <div class="box-body">
                                    <div class="table-responsive">
                                        <table class="table no-margin" id="lastmonthattandence">
                                            <thead>
                                                <tr>
                                      <!--edited by sinsiya on 13-06-2024-->
                                                    <th>Sl No</th>
                                                    <th>Employee Name</th>
                                                    <th>Employee ID</th>
                                                    <th>Branch</th>
                                                    <th class="date-column">Date</th>     <!--edited by ASHIN on 28-06-24-->
                                                    <th>Time</th>
                                                    <th>Direction ( IN/OUT)</th>
                                                    <th>Location</th>
                                                </tr>
                                            </thead>
                                        </table>
                                    </div><!-- /.table-responsive -->
                                </div><!-- /.box-body -->

                            </div>
                        </div>
<!--                        <div class="col-md-4">
                            <div class="box box-danger">
                                <div class="box-header with-border">
                                    <h3 class="box-title">Statistics</h3>

                                </div>
                                <div class="box-body chart-responsive">
                                    <canvas id="punchChart" height="180"></canvas>
                                </div> /.box-body 
                            </div>
                        </div>-->
                    </div>
                </div>

                <div data-pws-tab="tab4" id="tab4" data-pws-tab-name="Leave Requests" style="height:1000px; ">
                    
                    <div class="row">
                        <div class="col-md-12">
                            <div class="box box-info">
                                <div class="box-header with-border">
                                    <h3 class="box-title">Leave Requests</h3>
                                    <div class="box-tools pull-right">
                                        <div class="row">
<!--                                            <div class="col-md-6 col-sm-6 col-xs-12">
                                                <select id="leaverequests-month-filter" name="leaverequests-month-filter" class="form-control">
                                                    <?php
                                                    $start_month = strtotime(date('Y-m', strtotime("+1 month", strtotime(date('Y-m')))));
                                                    for ($i = 0; $i < 10; $i++) {
                                                        $month = date('Y-m', strtotime("-$i month", $start_month));
                                                        if ($month == date('Y-m')) {
                                                            echo '<option selected="selected" value="' . $month . '">' . date('M-Y', strtotime("-$i month", $start_month)) . '</option>';
                                                        } else {
                                                            echo '<option value="' . $month . '">' . date('M-Y', strtotime("-$i month", $start_month)) . '</option>';
                                                        }
                                                    }
                                                    ?>
                                                </select>
                                            </div>-->
<!--                                            <div class="col-md-6 col-sm-6 col-xs-12">
                                                <select id="leaverequests-emp-filter" name="leaverequests-emp-filter" class="form-control col-md-3 col-sm-6 col-xs-12">
                                                    <option value="">--All--</option>
                                                    <?php
//                                                    foreach ($arr_employees as $key => $value) {
                                                        //echo '<option value="' . $value['emp_pkey'] . '">' . $value['emp_name'] . '</option>';
//                                                        echo '<option value="' . $value['emp_name'] . '">' . $value['emp_name'] . '</option>';
//                                                    }
                                                    ?>
                                                </select>
                                            </div>-->
                                        </div>
                                        <!--button class="btn btn-box-tool" data-widget="collapse"><i class="fa fa-minus"></i></button>
                                        <button class="btn btn-box-tool" data-widget="remove"><i class="fa fa-times"></i></button-->
                                    </div>
                                </div><!-- /.box-header -->
                                <div class="box-body">
                                    <div class="table-responsive" id="loadlists">
                                        
                                    </div><!-- /.table-responsive -->
                                </div><!-- /.box-body -->

                            </div>
                        </div>
<!--                        <div class="col-md-4">
                            <div class="box box-danger">
                                <div class="box-header with-border">
                                    <h3 class="box-title">Analysis</h3>

                                </div>
                                <div class="box-body chart-responsive">
                                    <canvas id="punchChart" height="180"></canvas>
                                </div> /.box-body 
                            </div>
                        </div>-->
                    </div>
                    
                </div>

				<!-- Employee Miss punches --><!--edited by sinsiya on 13-06-2024-->
                <div data-pws-tab="tab5" id="tab5" data-pws-tab-name="Missed Attendance" style="height:1000px; ">
                    
                    <div class="row">
                        <div class="col-md-12">

                            <div class="box box-info">
                                <div class="box-header with-border">
                                    <h3 class="box-title">Missed Attendance</h3>
                                </div><!-- /.box-header -->
                                <div class="box-body" id="misspunch">
                                    
                                </div><!-- /.box-body -->

                            </div>
                        </div>
<!--                        <div class="col-md-4">
                            <div class="box box-danger">
                                <div class="box-header with-border">
                                    <h3 class="box-title">Analysis</h3>

                                </div>
                                <div class="box-body chart-responsive">
                                    <canvas id="punchChart" height="180"></canvas>
                                </div> /.box-body 
                            </div>
                        </div>-->
                    </div>
                    
                </div>
				<!-- Employee Miss punches -->
		   <div data-pws-tab="tab6" data-pws-tab-name="Support Requests" id="tab6" style="height:1000px; ">
                    
                    <div class="row">
                        <div class="col-md-12">

                            <div class="box box-info">
                                <div class="box-header with-border">
                                    <h3 class="box-title">Support Requests </h3>
                                     <!--<label class="col-md-2 control-label">Choose Date<span class="star">*</span></label>-->
                                   <!-- <input id="reportdate"  name="reportdate" class="col-md-4 control-label dateget " style="width: 160px; float: right;" value="" type="text"  class="form-control input-md" required="" placeholder="Select date">-->
                                </div><!-- /.box-header -->
                                <div class="box-body">
                                    <div class="table-responsive">
                                        <table class="table no-margin" id="reportproblem">
                                             
                                            <thead>
                                                <tr>
                                                    <th>Sl No</th>
                                                    <th>Employee Name</th>
                                                    <th>Employee ID</th>
                                                    <th>Branch </th>
                                                    <th class="date-column">Date & Time</th>    <!--edited by ASHIN on 28-06-24 -->
                                                    <th>Issue Type</th>
                                                   <!-- <th>User ID</th>-->
                                                    <th>Problem</th>
                                                    <th>Location</th>

                                                </tr>
                                            </thead>
                                        </table>
                                    </div><!-- /.table-responsive -->
                                </div><!-- /.box-body -->

                            </div>
                        </div>

<!--                        <div class="col-md-4">
                            <div class="box box-danger">
                                <div class="box-header with-border">
                                    <h3 class="box-title">Statistics</h3>

                                </div>
                                <div class="box-body chart-responsive">
                                    <canvas id="punchChart" height="180"></canvas>
                                </div> /.box-body 
                            </div>
                        </div>-->
                    </div>
                </div>	
<!--edited by sinsiya on 13-06-2024-->
 <div data-pws-tab="tab7" id="tab7" data-pws-tab-name="Customer Visits" style="height:1000px; " >
                    
                    <div class="row">
                        <div class="col-md-12">

                            <div class="box box-info">
                                <div class="box-header with-border">
                                    <h3 class="box-title">Customer Visits </h3>
                                  <!--  <div class="box-tools pull-right">-->
                                       <!-- <button style="background: rgb(221, 75, 57); " type="button" onclick="Modal('Latein','Month')"  class="btn btn-linkedin">Late In</button>-->
                                       <!-- <button style="background: #00a65a; " type="button" onclick="Modal('Earlyin','Month')" class="btn btn-linkedin">Early In</button>-->
                                       <!-- <button style="background: #00a65a; " type="button" onclick="Modal('Lateout','Month')" class="btn btn-linkedin">Late Out</button>-->
                                      <!--  <button style="background: rgb(221, 75, 57); " type="button" onclick="Modal('Earlyout','Month')" class="btn btn-linkedin">Early Out</button>-->
                                        <!--button class="btn btn-box-tool" data-widget="collapse"><i class="fa fa-minus"></i></button>-->
                                      <!--  <button class="btn btn-box-tool" data-widget="remove"><i class="fa fa-times"></i></button>-->
                                    <!--</div>-->
                                </div><!-- /.box-header -->
                                <div class="box-body">
                                    <div class="table-responsive">
                                        <table class="table no-margin" id="EmployeesUpdaets">
                                            <thead>
                                                <tr>
                                                    <th>Sl No</th>
                                                    <th>Employee Name</th>
                                                    <th>Employee ID</th>
                                                    <th>Branch</th>
                                                      <!--edited by ASHIN on 02-07-24-->                     
                                                    <th>Action</th>   
                                                    <th class="date-column">Date& Time</th>      <!--edited by ASHIN on 28-06-24 -->
                                                    <th>Location</th>

                                                </tr>
                                            </thead>
                                        </table>
                                    </div><!-- /.table-responsive -->
                                </div><!-- /.box-body -->

                            </div>
                        </div>
<!--                        <div class="col-md-4">
                            <div class="box box-danger">
                                <div class="box-header with-border">
                                    <h3 class="box-title">Statistics</h3>

                                </div>
                                <div class="box-body chart-responsive">
                                    <canvas id="punchChart" height="180"></canvas>
                                </div> /.box-body 
                            </div>
                        </div>-->
                    </div>
                    
                </div>
<!--ENDED-->
<!--edited by ASHIN on 28-06-2024-->
 <div data-pws-tab="tab8" id="tab8" data-pws-tab-name="Events" style="height:1000px; " >     
                    
                    <div class="row">
                        <div class="col-md-12">

                            <div class="box box-info">
                                <div class="box-header with-border">
                                       <h3 class="box-title">Next 30 Days Events - Birthdays And Anniversaries </h3>
                                   
                                </div><!-- /.box-header -->
                                <div class="box-body">
                                    <div class="table-responsive">
                                        <table class="table no-margin" id="EmployeesEvents">
                                            <thead>
                                                <tr>
                                                    <th>Sl No</th>
                                                    <th>Image</th>
                                                    <th>Employee Name</th>
                                                    <th>Employee ID</th>
                                                    <th>Branch</th>
                                                    <th>Type</th>
                                                    <th class="date-column">Event Date</th>       <!--edited by ASHIN on 28-06-24 -->
                                                    <th>Action</th>
                                                </tr>
                                            </thead>
                                        </table>
                                    </div><!-- /.table-responsive -->
                                </div><!-- /.box-body -->

                            </div>
                        </div>
<!--                        <div class="col-md-4">
                            <div class="box box-danger">
                                <div class="box-header with-border">
                                    <h3 class="box-title">Statistics</h3>

                                </div>
                                <div class="box-body chart-responsive">
                                    <canvas id="punchChart" height="180"></canvas>
                                </div> /.box-body 
                            </div>
                        </div>-->
                    </div>
                    
                </div>
<!--ENDED-->
                <!--div data-pws-tab="tab6" data-pws-tab-name="To Do" data-pws-tab-icon="fa-refresh fa-spin">


                </div>
                <div data-pws-tab="tab7" data-pws-tab-name="Reminders" data-pws-tab-icon="fa-refresh fa-spin">


                </div>
                <div data-pws-tab="tab8" data-pws-tab-name="Mail Box" data-pws-tab-icon="fa-refresh fa-spin">


                </div-->
                
            </div>

        </div>
    </div>
    
</section>

<script type="text/javascript" charset="utf-8">

    function Modal(name,job)
{
  //  alert(name);
    var url = 'dashboard/form/'+name+'/'+job;
   showModalForm(url)    
}
function openconfig(){
        $('#loaders').show();
        $('#container').load(livesite+'EmployeeConfig',function(){
            $('#loaders').hide();
        });
    }
function viewEmployeeMissPunches(){
	if(!arguments.length){
		return false;
	}
	var emp_id = arguments[0];
	if(emp_id){	
		$("#container").isLoading({
			text: "Loading",
			position: "overlay"
		});
		var url = "/EditPunches/index/" + emp_id;
		$("#container").load(url, function () {
			isDashboardShown = false;
		});
	}
}			

//    $.fn.dataTable.ext.search.push(
//        function( settings, data, dataIndex ) {
//            if(settings.sTableId == 'empleaverequests'){
//                var filteredByEmployee = false;
//                var filteredByMonth = false;
//
//                var emp_name = $('#leaverequests-emp-filter').val();
//                if(emp_name == '' || emp_name == data[0]){
//                    filteredByEmployee = true;
//                }
//
//                var month = $('#leaverequests-month-filter').val();
//                var fromdate = data[3];
//                var arr_fromdate = fromdate.split('-');
//                if(month == (arr_fromdate[0]+'-'+arr_fromdate[1])){
//                    filteredByMonth = true;
//                }
//
//                if(filteredByEmployee && filteredByMonth){
//                    return true;
//                }
//                return false;
//            }else{
//                return true;
//            }
//        }
//    );
    
    var punchChartCanvas = $("#punchChart").get(0).getContext("2d");
    var punchChart = new Chart(punchChartCanvas);
    
    var cntpresent = $('.cnt-present').html();
    var cntabsent = $('.cnt-absent').html();
    var cntpresentall = $('.cnt-presentall').html();
    
    var punchData = [
        {
            value: cntpresent,
            color: "#00a65a",
            highlight: "#00a65a",
            label: "Present"
        },
        {
            value: cntabsent,
            color: "#F75050",
            highlight: "#F75050",
            label: "Absent"
        },
         {
            value: cntpresentall,
            color: "#F75050",
            highlight: "#F75050",
            label: "Present All"
        }

    ];
    var pieOptions = {
//Boolean - Whether we should show a stroke on each segment
        segmentShowStroke: true,
//String - The colour of each segment stroke
        segmentStrokeColor: "#fff",
//Number - The width of each segment stroke
        segmentStrokeWidth: 1,
//Number - The percentage of the chart that we cut out of the middle
        percentageInnerCutout: 50, // This is 0 for Pie charts
//Number - Amount of animation steps
        animationSteps: 100,
//String - Animation easing effect
        animationEasing: "easeOutBounce",
//Boolean - Whether we animate the rotation of the Doughnut
        animateRotate: true,
//Boolean - Whether we animate scaling the Doughnut from the centre
        animateScale: false,
//Boolean - whether to make the chart responsive to window resizing
        responsive: true,
// Boolean - whether to maintain the starting aspect ratio or not when responsive, if set to false, will take up entire container
        maintainAspectRatio: false,
//String - A legend template
        legendTemplate: "<ul class=\"<%= name . toLowerCase() %>-legend\"><% for (var i = 0;i < segments . length;i++) { %><li><span style=\"background-color:<%= segments[i] . fillColor %>\"></span><% if (segments[i] . label) { %><%= segments[i] . label %><% } %></li><% } %></ul>",
//String - A tooltip template
        tooltipTemplate: "<%= value %> <%= label %> employees"
    };
//Create pie or douhnut chart
// You can switch between pie and douhnut using the method below.  
    punchChart.Doughnut(punchData, pieOptions);
</script>
<script type="text/javascript">
    $(document).ready(function () {
        
            $('#presentall').on('click',function(){
               var url = 'dashboard/presenttodayall';
               showModalForm(url) 
            })
            $('#present').on('click',function(){
              var url = 'dashboard/presenttoday';
   showModalForm(url)  
    })
     $('#absent').on('click',function(){
              var url = 'dashboard/absenttoday';
   showModalForm(url)  
   });     
        
        
        $('.tabset0').pwstabs({
            effect: 'slideleft', // You can change effects of your tabs container: scale / slideleft / slideright / slidetop / slidedown / none
            defaultTab: 1, // The tab we want to be opened by default
            containerWidth: '100%', // Set custom container width if not set then 100% is used
            containerHeight:'100%',
            tabsPosition: 'horizontal', // Tabs position: horizontal / vertical
            horizontalPosition: 'top', // Tabs horizontal position: top / bottom
            verticalPosition: 'left', // Tabs vertical position: left / right
            responsive: true, // Make tabs container responsive: true / false - boolean
            theme: '',
            rtl: false                    // Right to left support: true/ false
        });
        
$('#todayattandence').DataTable({
    "paging": true,
    "destroy": true,
    "lengthChange": false,
    "searching": true,
    "ordering": true,
    dom: 'Bfrtip',
    buttons: [
        {
            extend: 'print',
            messageTop: 'My Payroll Master Employees Attendance Report.',
            messageBottom: null,
            title: 'My Payroll Master - Attendance Export',
            customize: function (win) {
                var body = $(win.document.body);
                var table = body.find('table');
                table.addClass('print-table').css('font-size', '10pt');
                table.find('td').css('padding-left', '20px');
                // Recalculate and set the serial numbers for print view
                table.find('tr').each(function (index) {
                    var cell = $(this).find('td:eq(0)');
                    if (cell.length) {
                        cell.text(index); // 1-based index
                    }
                });
            }
        },
       {
                           extend: 'pdf',
                           messageTop: 'My Payroll Master Employees Attendance Report.',
                           messageBottom: null,
                           title: 'My Payroll Master - Attendance Export',
                           exportOptions: {
                               columns: ':visible:not(:eq(0))' //Exclude the first column (sl.no)
                           }
                       },
                       {
                           extend: 'excel',
                           messageTop: 'My Payroll Master Employees Attendance Report.',
                           messageBottom: null,
                           title: 'My Payroll Master - Attendance Export',
                           exportOptions: {
                               columns: ':visible:not(:eq(0))' //Exclude the first column (sl.no)
                           }
                       }
    ],
    "info": true,
    "autoWidth": false,
    "order": [[2, "desc"]],
   // "scrollY": 200,
    "language": {
        "loadingRecords": "", // Remove loading message
        "zeroRecords": "No data found" // Custom message for no data
    },
    "scrollCollapse": true,
    "ajax": {
        url: livesite + "Dashboard/listtodayattendance",
        dataSrc: function (json) {
            if (json.data.length === 0) {
                $('#todayattandence').html('<tr><td colspan="6">No data found</td></tr>'); // Show custom no data message
            }
            return json.data;
        },
        error: function (xhr, error, code) {
            console.error('AJAX error:', error);
            console.error('AJAX error code:', code);
        }
    },
     "columnDefs": [
        {
            "targets": 0, // Target the first column for the serial number
            "orderable": false,
            "searchable": false
        }
    ],
    "createdRow": function (row, data, dataIndex) {
        // Apply the 'sl-no-forward' class to the 'Sl No' column
        $(row).find('td:eq(0)').addClass('sl-no-forward');
        // Apply the 'direction-forward' class to the 'Direction (IN/OUT)' column
        $(row).find('td:eq(6)').addClass('direction-forward');
    },
    "drawCallback": function (settings) {
        var api = this.api();
        var start = api.page.info().start;
        api.column(0, { page: 'current' }).nodes().each(function (cell, i) {
            cell.innerHTML = start + i + 1;
        });
    }
});


$('.buttons-print').ready(function() {
    $('.buttons-print').html('<li class="fa fa-print"></li>').addClass('btn-primary btn');
});
$('.buttons-pdf').ready(function() {
    $('.buttons-pdf').html('<li class="fa fa-file-pdf-o"></li>').addClass('btn-danger btn');
});
$('.buttons-excel').ready(function() {
    $('.buttons-excel').html('<li class="fa fa-file-excel-o"></li>').addClass('btn-success btn');
});

         
        
        
        
        
         var interval = setInterval( function () {
//edited by sinsiya on 17-06-2024  to bring the no data found instead of loading..
 
         // edited by sinsiya on 18-06-2024  
    $('#thismonthattandence').DataTable({
    "paging": true,
    "destroy": true,
    "lengthChange": false,
    "searching": true,
    "ordering": true,
    dom: 'Bfrtip',
    buttons: [
        {
            extend: 'print',
            messageTop: 'My Payroll Master Employees Attendance Report.',
            messageBottom: null,
            title: 'My Payroll Master - Attendance Export',
            customize: function (win) {
                var body = $(win.document.body);
                var table = body.find('table');
                table.addClass('print-table').css('font-size', '10pt');
                table.find('td').css('padding-left', '20px');
                // Recalculate and set the serial numbers for print view
                table.find('tr').each(function (index) {
                    var cell = $(this).find('td:eq(0)');
                    if (cell.length) {
                        cell.text(index); // 1-based index
                    }
                });
            } 
        },
        {
                           extend: 'pdf',
                           messageTop: 'My Payroll Master Employees Attendance Report.',
                           messageBottom: null,
                           title: 'My Payroll Master - Attendance Export',
                           exportOptions: {
                               columns: ':visible:not(:eq(0))' //Exclude the first column (sl.no)
                           }
                       },
                       {
                           extend: 'excel',
                           messageTop: 'My Payroll Master Employees Attendance Report.',
                           messageBottom: null,
                           title: 'My Payroll Master - Attendance Export',
                           exportOptions: {
                               columns: ':visible:not(:eq(0))' //Exclude the first column (sl.no)
                           }
                       }
    ],
    "info": true,
    "autoWidth": false,
    "order": [[ 1, "desc" ]],
    "initComplete": function () {
        $('.pws_tabs_container').find('a[data-tab-id="tab2"]').find('.fa').removeClass("fa-spin");
    },
    "ajax": {
        url: livesite + "Dashboard/listthismonthattendance",
        dataSrc: function (json) {
            if (json.data.length === 0) {
              //  $('#thismonthattandence').html('<tr><td colspan="6">No data found</td></tr>'); // Show custom no data message
            }
            return json.data;
        },
        error: function (xhr, error, code) {
            console.error('AJAX error:', error);
            console.error('AJAX error code:', code);
        }
    },
    "columnDefs": [
        {
            "targets": 0, // Target the first column for the serial number
            "orderable": false,
            "searchable": false
        }
    ],
    "createdRow": function (row, data, dataIndex) {
        // Apply the 'sl-no-forward' class to the 'Sl No' column
        $(row).find('td:eq(0)').addClass('sl-no-forward');
        // Apply the 'direction-forward' class to the 'Direction (IN/OUT)' column
        $(row).find('td:eq(6)').addClass('direction-forward');
    },
    "drawCallback": function (settings) {
        var api = this.api();
        var start = api.page.info().start;
        api.column(0, { page: 'current' }).nodes().each(function (cell, i) {
            cell.innerHTML = start + i + 1;
        });
    }
});


        
        $('#load_data').load(livesite+ 'Dashboard/load_birthdays');
        $('#misspunch').load(livesite+ 'Dashboard/misspunch');
            //startTimer();
//edited by sinsiya on 18-06-2024 Set ordering:false 
           $('#lastmonthattandence').DataTable({
    "paging": true,
    "destroy": true,
    "lengthChange": false,
    "searching": true,
    "ordering": true,
    dom: 'Bfrtip',
    buttons: [
        {
            extend: 'print',
            messageTop: 'My Payroll Master Employees Attendance Report.',
            messageBottom: null,
            title: 'My Payroll Master - Attendance Export',
            customize: function (win) {
                var body = $(win.document.body);
                var table = body.find('table');
                table.addClass('print-table').css('font-size', '10pt');
                table.find('td').css('padding-left', '20px');
                // Recalculate and set the serial numbers for print view
                table.find('tr').each(function (index) {
                    var cell = $(this).find('td:eq(0)');
                    if (cell.length) {
                        cell.text(index); // 1-based index for print view
                    }
                });
            }
        },
       {
                           extend: 'pdf',
                           messageTop: 'My Payroll Master Employees Attendance Report.',
                           messageBottom: null,
                           title: 'My Payroll Master - Attendance Export',
                           exportOptions: {
                               columns: ':visible:not(:eq(0))' //Exclude the first column (sl.no)
                           }
                       },
                       {
                           extend: 'excel',
                           messageTop: 'My Payroll Master Employees Attendance Report.',
                           messageBottom: null,
                           title: 'My Payroll Master - Attendance Export',
                           exportOptions: {
                               columns: ':visible:not(:eq(0))' //Exclude the first column (sl.no)
                           }
                       }
    ],
    "info": true,
    "autoWidth": false,
    "initComplete": function () {
        $('.pws_tabs_container').find('a[data-tab-id="tab3"]').find('.fa').removeClass("fa-spin");
    },
    "order": [[1, "desc"]],
    "ajax": {
        url: livesite + "Dashboard/listlastmonthattendance",
        dataSrc: function (json) {
            if (json.data.length === 0) {
                $('#lastmonthattandence').html('<tr><td colspan="6">No data found</td></tr>'); // Show custom no data message
            }
            return json.data;
        },
        error: function (xhr, error, code) {
            console.error('AJAX error:', error);
            console.error('AJAX error code:', code);
        }
    },
    "columnDefs": [
        {
            "targets": 0, // Target the first column for the serial number
            "orderable": false,
            "searchable": false
        }
    ],
    "createdRow": function (row, data, dataIndex) {
        // Apply the 'sl-no-forward' class to the 'Sl No' column
        $(row).find('td:eq(0)').addClass('sl-no-forward');
        // Apply the 'direction-forward' class to the 'Direction (IN/OUT)' column
        $(row).find('td:eq(6)').addClass('direction-forward');
    },
    "drawCallback": function (settings) {
        var api = this.api();
        var start = api.page.info().start;
        api.column(0, { page: 'current' }).nodes().each(function (cell, i) {
            cell.innerHTML = start + i + 1; // Adjust the serial number calculation
        });
    }
});

        $('#loadlists').load(livesite + 'Dashboard/listleaverequests' );
        
        
        
        // Event listener to the two range filtering inputs to redraw on input
//        $('#leaverequests-emp-filter, #leaverequests-month-filter').change( function() {
//            empleaverequeststable.search( this.value ).draw();
//        });
//		
		
        var empmisspunchestable = $('#empmisspunches').DataTable({
            "paging": true,
             "destroy": true,
            "lengthChange": false,
            "searching": true,
            "ordering": true,
            "info": true,
            "order": [[ 1, "desc" ]],
            "autoWidth": false
        });
        
        
        $('.buttons-print').ready(function(){
            $('.buttons-print').html('<li class="fa fa-print"></li>').addClass('btn-primary').addClass('btn');;
        });
        $('.buttons-pdf').html('<li class="fa fa-file-pdf-o"></li>').addClass('btn-danger').addClass('btn');;
        $('.buttons-excel').html('<li class="fa fa-file-excel-o"></li>').addClass('btn-success').addClass('btn');
        
        
            var weekdata = {
                labels: ["January", "February", "March", "April", "May", "June", "July"],
                datasets: [
                    {
                        label: "My First dataset",
                        fillColor: "rgba(220,220,220,0.5)",
                        strokeColor: "rgba(220,220,220,0.8)",
                        highlightFill: "rgba(220,220,220,0.75)",
                        highlightStroke: "rgba(220,220,220,1)",
                        data: [65, 59, 80, 81, 56, 55, 40]
                    },
                    {
                        label: "My Second dataset",
                        fillColor: "rgba(151,187,205,0.5)",
                        strokeColor: "rgba(151,187,205,0.8)",
                        highlightFill: "rgba(151,187,205,0.75)",
                        highlightStroke: "rgba(151,187,205,1)",
                        data: [28, 48, 40, 19, 86, 27, 90]
                    }
                ]
            };


            /*var weekChartCanvas = $("#weekchart").get(0).getContext("2d");
            var weekoptions = {
                //Boolean - Whether the scale should start at zero, or an order of magnitude down from the lowest value
                scaleBeginAtZero: true,
                //Boolean - Whether grid lines are shown across the chart
                scaleShowGridLines: true,
                //String - Colour of the grid lines
                scaleGridLineColor: "rgba(0,0,0,.05)",
                //Number - Width of the grid lines
                scaleGridLineWidth: 1,
                //Boolean - Whether to show horizontal lines (except X axis)
                scaleShowHorizontalLines: true,
                //Boolean - Whether to show vertical lines (except Y axis)
                scaleShowVerticalLines: true,
                //Boolean - If there is a stroke on each bar
                barShowStroke: true,
                //Number - Pixel width of the bar stroke
                barStrokeWidth: 2,
                //Number - Spacing between each of the X value sets
                barValueSpacing: 5,
                //Number - Spacing between data sets within X values
                barDatasetSpacing: 1,
                //String - A legend template
                legendTemplate: "<ul class=\"<%= name . toLowerCase() %>-legend\"><% for (var i = 0;
            i < datasets . length;
            i++) { %><li><span style=\"background-color:<%= datasets[i] . fillColor %>\"></span><% if (datasets[i] . label) { %><%= datasets[i] . label %><% } %></li><% } %></ul>"

            };
            var weekChart = new Chart(weekChartCanvas).Bar(weekdata, weekoptions);*/



            var monthdata = {
                labels: ["January", "February", "March", "April", "May", "June", "July"],
                datasets: [
                    {
                        label: "My First dataset",
                        fillColor: "rgba(220,220,220,0.5)",
                        strokeColor: "rgba(220,220,220,0.8)",
                        highlightFill: "rgba(220,220,220,0.75)",
                        highlightStroke: "rgba(220,220,220,1)",
                        data: [65, 59, 80, 81, 56, 55, 40]
                    },
                    {
                        label: "My Second dataset",
                        fillColor: "rgba(151,187,205,0.5)",
                        strokeColor: "rgba(151,187,205,0.8)",
                        highlightFill: "rgba(151,187,205,0.75)",
                        highlightStroke: "rgba(151,187,205,1)",
                        data: [28, 48, 40, 19, 86, 27, 90]
                    }
                ]
            };

           
         $('#reportproblem').DataTable({
            "paging": true,
             "destroy": true,
            "lengthChange": false,
            "searching": true,
            "ordering": true,
            dom: 'Bfrtip',
            buttons: [
                {
                     extend: 'print',
                     messageTop: 'My Payroll Master Employees Support Requests Report.',
                     messageBottom: null,
                     title: 'My Payroll Master - Support Requests Export',
                      customize: function (win) {
                var body = $(win.document.body);
                var table = body.find('table');
                table.addClass('print-table').css('font-size', '10pt');
                table.find('td').css('padding-left', '20px');
                // Recalculate and set the serial numbers
                table.find('tr').each(function (index) {
                    var cell = $(this).find('td:eq(0)');
                    if (cell.length) {
                        cell.text(index);
                    }
                });
            }
                },
                {
                     extend: 'pdf',
                     messageTop: 'My Payroll Master Employees Support Requests Report.',
                     messageBottom: null,
                     title: 'My Payroll Master - Support Requests Export'
                },
                {
                     extend: 'excel',
                     messageTop: 'My Payroll Master Employees Support Requests Report.',
                     messageBottom: null,
                     title: 'My Payroll Master - Support Requests Export'
                }
            ],
            "info": true,
            "autoWidth": false,
            "order": [[ 1, "desc" ]],
            "initComplete": function () {
                $('.pws_tabs_container').find('a[data-tab-id="tab6"]').find('.fa').removeClass("fa-spin");
            },
            "ajax": livesite + "Dashboard/issuereport",
//             data: {
//                issuedate: issuedate
//            }

 //edited by sinsiya on 13-06-2024
            "columnDefs": [
            {
            "targets": 0, // Target the first column for the serial number
            "orderable": false,
            "searchable": false
           }
          ],
          "createdRow": function (row, data, dataIndex) {
        // Apply the 'sl-no-forward' class to the 'Sl No' column
        $(row).find('td:eq(0)').addClass('sl-no-forward');
          },
          "drawCallback": function (settings) {
               var api = this.api();
               var start = api.page.info().start;
               api.column(0, { page: 'current' }).nodes().each(function (cell, i) {
               cell.innerHTML = start + i + 1;
               });
          }
                });

// <!--EDITED BY SINSIYA ON 13-06-2024-->
$('#EmployeesUpdaets').DataTable({
    paging: true,
    lengthChange: false,
    searching: true,
    ordering: true,
    dom: 'Bfrtip',
    buttons: [
        {
            extend: 'print',
            messageTop: 'My Payroll Master Employees Customer Visits Report.',
            title: 'My Payroll Master - Customer Visits Export',
            customize: function (win) {
                var body = $(win.document.body);
                var table = body.find('table');
                table.addClass('print-table').css('font-size', '10pt');
                table.find('td').css('padding-left', '20px');
                table.find('tr').each(function (index) {
                    var cell = $(this).find('td:eq(0)');
                    if (cell.length) {
                        cell.text(index + 1);
                    }
                });
            }
        },
        {
            extend: 'pdf',
            messageTop: 'My Payroll Master Employees Customer Visits Report.',
            title: 'My Payroll Master - Customer Visits Export'
        },
        {
            extend: 'excel',
            messageTop: 'My Payroll Master Employees Customer Visits Report.',
            title: 'My Payroll Master - Customer Visits Export'
        }
    ],
    info: true,
    autoWidth: false,
    initComplete: function () {
        $('.pws_tabs_container').find('a[data-tab-id="tab7"]').find('.fa').removeClass("fa-spin");
    },
    ajax: livesite + "Dashboard/LocationUpdates",
     //edited by ASHIN on 02-07-24          
            "order": [
               [1, "desc"]
            ],
          
            "ajax": livesite + "Dashboard/LocationUpdates",
            "columnDefs": [
        {
            "targets": 0, // Target the first column for the serial number
            "orderable": false,
            "searchable": false
        }
    ],
//edited by sinsiya on 13-06-2024
           
          "drawCallback": function (settings) {
               var api = this.api();
               var start = api.page.info().start;
               api.column(0, { page: 'current' }).nodes().each(function (cell, i) {
               cell.innerHTML = start + i + 1;
               });
            },
    // "columnDefs": [
    //     { "searchable": false, "targets": 0 } // Disable filtering on the first column
    // ]
     });

$('.buttons-print').ready(function () {
    $('.buttons-print').html('<li class="fa fa-print"></li>').addClass('btn-primary btn');
});
$('.buttons-pdf').html('<li class="fa fa-file-pdf-o"></li>').addClass('btn-danger btn');
$('.buttons-excel').html('<li class="fa fa-file-excel-o"></li>').addClass('btn-success btn');



//edited by sinsiya on 13-06-2024
$('#EmployeesEvents').DataTable({
    "paging": true,
    "lengthChange": false,
    "searching": true,
    "ordering": true,
    dom: 'Bfrtip',
    buttons: [
        { 
            extend: 'print',
            messageTop: 'My Payroll Master Employees Event Report.',
            messageBottom: null,
            title: 'My Payroll Master - Event Export',
            exportOptions: {
                columns: ':visible:not(:eq(1))' // Exclude the second column (image column)
            },
            customize: function (win) {
                var body = $(win.document.body);
                var table = body.find('table');
                table.addClass('print-table').css('font-size', '10pt');
                table.find('td').css('padding-left', '20px');
                // Recalculate and set the serial numbers
                table.find('tr').each(function (index) {
                    var cell = $(this).find('td:eq(0)');
                    if (cell.length) {
                        cell.text(index);
                    }
                });
            } 
        },
        {
            extend: 'pdf',
            messageTop: 'My Payroll Master Employees Event Report.',
            messageBottom: null,
            title: 'My Payroll Master - Event Export',
            exportOptions: {
                columns: ':visible:not(:eq(1))' // Exclude the second column (image column)
            }
        },
        {
            extend: 'excel',
            messageTop: 'My Payroll Master Employees Event Report.',
            messageBottom: null,
            title: 'My Payroll Master - Event Export',
            exportOptions: {
                columns: ':visible:not(:eq(1))' // Exclude the second column (image column)
            }
        }
    ],
    "info": true,
    "autoWidth": false,
    "initComplete": function () {
        $('.pws_tabs_container').find('a[data-tab-id="tab8"]').find('.fa').removeClass("fa-spin");
    },
    "order": [
        [1, "desc"]
    ],
    "ajax": livesite + "Dashboard/EmployeeEvent",
    "columnDefs": [
        {
            "targets": 0, // Target the first column for the serial number
            "orderable": false,
            "searchable": false
        }
    ],
    "createdRow": function (row, data, dataIndex) {
        $(row).find('td:eq(0)').addClass('sl-no-forward'); // Apply class to first column

        var eventDateStr = data[6];
        var eventDateParts = eventDateStr.split('-');
        var eventDate = new Date(eventDateParts[2], eventDateParts[1] - 1, eventDateParts[0]); // year, month (0-based), day
        var currentDate = new Date();
        var tomorrowDate = new Date();
        tomorrowDate.setDate(currentDate.getDate() + 1);

        // Check if the event date is today or tomorrow
        if (eventDate.toDateString() === currentDate.toDateString() || eventDate.toDateString() === tomorrowDate.toDateString()) {
            // Find the "Wish" button in the row and enable it
            $(row).find('button.btn-primary').removeAttr('disabled');
        } else {
            // Disable the "Wish" button if the event date is not today or tomorrow
            $(row).find('button.btn-primary').attr('disabled', 'disabled');
        }
    },
    "drawCallback": function (settings) {
        var api = this.api();
        var start = api.page.info().start;
        api.column(0, { page: 'current' }).nodes().each(function (cell, i) {
            cell.innerHTML = start + i + 1;
        });

        // Attach click event listener to the "Wish" button
        $('button.btn-primary[onclick^="openWishModal"]').on('click', function () {
            var empPkey = $(this).attr('onclick').match(/openWishModal\('(\d+)'/)[1];
            var eventType = $(this).attr('onclick').match(/openWishModal\(.*?, '(.*?)'/)[1];
            openWishModal(empPkey, eventType);
        });
    }
});
       $('.buttons-print').ready(function() {
            $('.buttons-print').html('<li class="fa fa-print"></li>').addClass('btn-primary').addClass('btn');;
        });
        $('.buttons-pdf').html('<li class="fa fa-file-pdf-o"></li>').addClass('btn-danger').addClass('btn');;
        $('.buttons-excel').html('<li class="fa fa-file-excel-o"></li>').addClass('btn-success').addClass('btn');

            clearInterval(interval);
        }, 6000 );

        });
   
 function openWishModal(empPkey, event) {
        // Assuming 'event' is a string
        var eventText = event;

        var url = "Dashboard/wish_modal/" + empPkey + "/" + encodeURIComponent(eventText);
        showModalForm(livesite + url);
    }

        $(".dateget").change(function(){
          var issuedate = $('#reportdate').val();
         tablereport.ajax.url( livesite + "Dashboard/issuereport/"+issuedate ).load();
        });
     $('.dateget').datepicker({
            format: 'yyyy-mm-dd',
            autoclose: true
	});


</script>