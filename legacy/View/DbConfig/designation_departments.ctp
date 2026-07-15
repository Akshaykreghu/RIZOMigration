<meta name="viewport" content="width=device-width, initial-scale=1">
<style>
        .btn-primary,.btn-success{
            background-color: #126aa3 !important;
        }
        
        h1{
            color: #126aa3  !important;
        }
        .select2-container--default .select2-results__option--highlighted[aria-selected]{
            background-color: #126aa3 !important;
        }
</style>
<style>
* {
    box-sizing: border-box;
}

input[type=text], select, textarea {
    width: 100%;
    padding: 12px;
    border: 1px solid #ccc;
    border-radius: 4px;
    resize: vertical;
}
input[type=number], select, textarea {
    width: 100%;
    padding: 12px;
    border: 1px solid #ccc;
    border-radius: 4px;
    resize: vertical;
}
input[type=email], select, textarea {
    width: 100%;
    padding: 12px;
    border: 1px solid #ccc;
    border-radius: 4px;
    resize: vertical;
}
label {
    padding: 12px 12px 12px 0;
    display: inline-block;
}

input[type=submit] {
    background-color: #0070c0;
    color: white;
    padding: 12px 20px;
    border: none;
    border-radius: 4px;
    cursor: pointer;
    float: right;
    margin-top: 10px;
}

input[type=submit]:hover {
    background-color: #45a049;
}

.container {
    border-radius: 12px;
    background-color: #f2f2f2;
    padding: 50px;
}

.col-25 {
    float: left;
    width: 25%;
    margin-top: 6px;
}

.col-75 {
    float: left;
    width: 75%;
    margin-top: 6px;
}

/* Clear floats after the columns */
.row:after {
    content: "";
    display: table;
    clear: both;
}

.tooltip-f{
    background: none;
}

/* Responsive layout - when the screen is less than 600px wide, make the two columns stack on top of each other instead of next to each other */
@media screen and (max-width: 600px) {
    .col-25, .col-75, input[type=submit] {
        width: 100%;
        margin-top: 12px;
    }
}
</style>
<link href="<?php echo $this->webroot; ?>css/bootstrap.min.css" rel="stylesheet" type="text/css" />
    <link href="https://maxcdn.bootstrapcdn.com/font-awesome/4.3.0/css/font-awesome.min.css" rel="stylesheet" type="text/css" />
    <!--<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">-->
<!--    <link href="<?php echo $this->webroot; ?>css/font-awesome.css" rel="stylesheet" type="text/css" />
    <link href="<?php echo $this->webroot; ?>css/font-awesome.min.css" rel="stylesheet" type="text/css" />-->
    <!-- Ionicons 2.0.0 -->
    <link href="https://code.ionicframework.com/ionicons/2.0.1/css/ionicons.min.css" rel="stylesheet" type="text/css" />
    <!-- Theme style -->
    <link href="<?php echo $this->webroot; ?>css/AdminLTE.min.css" rel="stylesheet" type="text/css" />
    <!-- AdminLTE Skins. Choose a skin from the css/skins
         folder instead of downloading all of them to reduce the load. -->
    
    
 <link type="text/css" rel="stylesheet" href="<?php echo $this->webroot; ?>plugins/tab/assets/jquery.pwstabs-1.2.1.css">
 <style>
    	.pws_tabs_controll{
    		margin-bottom: 0;
    		padding-left: 0;
    	}
    .pws_tab_single
    {
    	width: 97%;
    }
    </style>
    <!--
    <link href="plugins/datatables/jquery.dataTables.css" rel="stylesheet" type="text/css" />
      
    
    <link href="plugins/datatables/extensions/TableTools/css/dataTables.tableTools.min.css" rel="stylesheet" type="text/css" />
    -->
     <link href="<?php echo $this->webroot; ?>plugins/datatables/dataTables.bootstrap.css" rel="stylesheet" type="text/css" />
   
    <!-- bootstrap wysihtml5 - text editor -->
    <link href="<?php echo $this->webroot; ?>plugins/bootstrap-wysihtml5/bootstrap3-wysihtml5.min.css" rel="stylesheet" type="text/css" />
  	<link href="<?php echo $this->webroot; ?>css/style.css" rel="stylesheet" type="text/css" />
    <!-- HTML5 Shim and Respond.js IE8 support of HTML5 elements and media queries -->
    <!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
    <!--[if lt IE 9]>
        <script src="https://oss.maxcdn.com/html5shiv/3.7.2/html5shiv.min.js"></script>
        <script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
    <![endif]-->

    <!-- jQuery 2.1.4 -->
    <script src="<?php echo $this->webroot; ?>plugins/jQuery/jQuery-2.1.4.min.js"></script>
    <!-- jQuery UI 1.11.4 -->
    <script src="<?php echo $this->webroot; ?>plugins/jquery-ui.min.js" type="text/javascript"></script>
    
    <script src="<?php echo $this->webroot; ?>plugins/form-validator/jquery.form-validator.js" type="text/javascript"></script>    
    
    <!-- Easy Autocomplete Plugin -->
    
    <!-- Resolve conflict in jQuery UI tooltip with Bootstrap tooltip -->
    <script type="text/javascript">
      $.widget.bridge('uibutton', $.ui.button);
    </script>
    <!-- Bootstrap 3.3.2 JS -->
    <script src="<?php echo $this->webroot; ?>js/bootstrap.min.js" type="text/javascript"></script>
    
    <script src="<?php echo $this->webroot; ?>plugins/timepicker/bootstrap-timepicker.js" type="text/javascript"></script>
   
    <!-- Morris.js charts -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/raphael/2.1.0/raphael-min.js"></script>
    <script src="<?php echo $this->webroot; ?>plugins/morris/morris.min.js" type="text/javascript"></script>
    <!-- Sparkline -->
    <script src="<?php echo $this->webroot; ?>plugins/sparkline/jquery.sparkline.min.js" type="text/javascript"></script>
    <!-- jvectormap -->
    <script src="<?php echo $this->webroot; ?>plugins/jvectormap/jquery-jvectormap-1.2.2.min.js" type="text/javascript"></script>
    <script src="<?php echo $this->webroot; ?>plugins/jvectormap/jquery-jvectormap-world-mill-en.js" type="text/javascript"></script>
    <!-- jQuery Knob Chart -->
    <script src="<?php echo $this->webroot; ?>plugins/knob/jquery.knob.js" type="text/javascript"></script>
    <!-- daterangepicker -->
    <script src="<?php echo $this->webroot; ?>plugins/moment.min.js" type="text/javascript"></script>
    
	<script type="text/javascript" src="<?php echo $this->webroot; ?>plugins/timepicker/jquery.timepicker.js"></script>
	<link rel="stylesheet" type="text/css" href="<?php echo $this->webroot; ?>plugins/timepicker/jquery.timepicker.css" />
	
	<!--link rel="stylesheet" type="text/css" href="<?php echo $this->webroot; ?>plugins/datepicker/datepicker3.css" />
    <script src="<?php echo $this->webroot; ?>plugins/daterangepicker/daterangepicker.js" type="text/javascript"></script>
    <script src="<?php echo $this->webroot; ?>plugins/timepicker/jquery.timepicker.js" type="text/javascript"></script-->
    <script src="<?php echo $this->webroot; ?>plugins/datepicker/bootstrap-datepicker.js" type="text/javascript"></script>
    
	  
  
    <!-- bootstrap time picker -->
  <!--
    <script src="plugins/timepicker/bootstrap-timepicker.min.js" type="text/javascript"></script>
    -->
    <script src="<?php echo $this->webroot; ?>plugins/nestable/jquery.nestable.js"></script>
    <!-- AdminLTE App -->
      <script src="<?php echo $this->webroot; ?>plugins/dropzone/dropzone.js" type="text/javascript"></script>
   
   
   
   
   
   <!--<link rel="stylesheet" type="text/css" href="<?php echo $this->webroot; ?>css/index.css">-->
   
   
   
   <link href="<?php echo $this->webroot; ?>plugins/select2/select2.css" media="all" rel="stylesheet" type="text/css" />
      <link href="<?php echo $this->webroot; ?>plugins/select2/select2.min.css" media="all" rel="stylesheet" type="text/css" />
      
    <script src="<?php echo $this->webroot; ?>plugins/select2/select2.full.min.js" type="text/javascript"></script>
    <script src="<?php echo $this->webroot; ?>plugins/select2/select2.full.js" type="text/javascript"></script>
    <script src="<?php echo $this->webroot; ?>plugins/select2/select2.js" type="text/javascript"></script>
   
   
       
   
   
    <script src="<?php echo $this->webroot; ?>plugins/datepair/datepair.js" type="text/javascript"></script>
    <script src="<?php echo $this->webroot; ?>plugins/datepair/jquery.datepair.js" type="text/javascript"></script>
    <script src="<?php echo $this->webroot; ?>plugins/notify/bootstrap-notify.js"></script>
     <!--DHTMLX -->
     <link rel="stylesheet" type="text/css" href="<?php echo $this->webroot; ?>plugins/dhtmlx/grid/dhtmlxgrid.css"/>
	<script src="<?php echo $this->webroot; ?>plugins/dhtmlx/grid/dhtmlxgrid.js"></script>
    
	
    <style>
        .checkbox{
            display: inline-block;
            *display: inline;
            vertical-align: middle;
            margin: 0;
            padding: 0;
            width: 18px;
            height: 18px;
            background: url(blue.png) no-repeat;
            border: none;
            cursor: pointer;
        }
        .col-md-4 .margin{
            width: 84%;
        }
        .btn-flat{
            background: #2c388f !important; 
        }
        
    </style>
	
    
    
    
	
	
	   <script src="<?php echo $this->webroot; ?>plugins/tab/assets/jquery.pwstabs-1.2.1.js"></script>
	<script src="<?php echo $this->webroot; ?>plugins/jquery.isloading.js" type="text/javascript"></script>
    <script src="<?php echo $this->webroot; ?>js/fs.js" type="text/javascript"></script>
    <script src="<?php echo $this->webroot; ?>js/app.min.js" type="text/javascript"></script>
<section class="content-header">
 </section>
<script>var livesite = '<?php echo $this->webroot;?>'</script>
    
<section>
    <div class="container" style="margin-bottom : 46px; margin-top : 16px;">
    <h1 style="text-align: center;margin-top: 1px;"> Manage Departments & Designations </h1>
    <p style="text-align:center; ">Select the Designations and Departments that matches for your organization </p>
        <form  id="contactInfoForm" action="<?php echo $this->webroot; ?>DbConfig/save_holidays" method="post">
    
    <div class="col-md-12">
        <br>
        <div class="box ">
            <div class="box-header with-border">
                <!--<h3 class="box-title">Select Groups </h3>-->
                <div class="box-tools pull-right">
                    <button class="btn btn-box-tool" data-widget="collapse"><i class="fa fa-minus"></i></button>
                </div> 
            </div><!-- /.box-header -->
            <div class="box-body">
                <div class="col-md-6 col-sm-12">
                    <b>Designations</b>
                    <p>Please select any item from the below box to add it in Designation list.  You can also create your own Designation from admin login </p>
                </div>
                <div class="col-md-6 col-sm-12">
                    <b>Departments</b>
                    <p>Please select any item from the below box to add it in Departments list . You can also create your own Department from admin login </p>
                </div>
                <div class="col-md-6 col-sm-12">
                    <select id="desig" name="designationss" class="form-control js-example-basic-single" style="width: 100%; " >
                        <option>Select</option>
                        <?php foreach($arr_holidays_designations as $val){ ?>
                            <option value="<?php echo $val['desig_code']; ?>" ><?php echo $val['desig_name'] ; ?></option>
                        <?php } ?>
                    </select>
                </div>
                <div class="col-md-6 col-sm-12">
                    <select id="dept" name="deaprtments" class="form-control js-example-basic-single" style="width: 100%; " >
                        <option>Select</option>
                        <?php foreach($arr_holidays as $val){ ?>
                            <option value="<?php echo $val['dept_code']; ?>"><?php echo $val['dept_name']; ?></option>
                        <?php } ?>
                    </select>
                </div>
                
                <div class="col-md-6" id="designation_lists" style="padding-top: 20px; ">
                    <p><b>Selected Designations List </b></p>
                    <?php foreach ($fetch_designations as $val) { ?>
                        <button type="button" onclick="remove_desig('<?php echo $val['designation']['desig_code']; ?>',this);" class="btn bg-maroon btn-flat margin" ><?php echo $val['designation']['desig_name']; ?>  x</button>
                    <?php } ?>
                    
                </div>
                <div class="col-md-6" id="load_calender"  style="padding-top: 20px; ">
                    <p><b>Selected Departments List </b></p>
                    
                    <?php foreach ($fetch_departments as $val) { ?>
                        <button type="button" onclick="remove_depart('<?php echo $val['department']['dept_code']; ?>',this);" class="btn bg-maroon btn-flat margin" ><?php echo $val['department']['dept_name']; ?>  x</button>
                    <?php } ?>
                </div>

            </div><!-- /.box-body -->

        </div>
        
</div>
    
                 <button type="button" id="cinfocancel" class="btn btn-info pull-right "  onclick="window.location.href = livesite + 'Dashboard'" style="margin-right:5px">Skip Setup </button>
                   <button type="submit" id="cinfosave" class="btn btn-success pull-right "   style="margin-right:5px">Next</button>
                
                   
                </form>
    </div>

</section>
          
<div id="smallModalForm" class="modal fade" >
    <div class="modal-dialog modal-md">
        <div class="modal-content" id="smallModalForm-content">
            <!-- Content will be loaded here from "remote.php" file -->
        </div>
    </div>
</div>

<script type="text/javascript">
    function move_departments(s){
        var id = s;
        $('#load_calender').load(livesite+ 'DbConfig/showholidays/'+s);
    }
    
    function remove_desig(id,s){
        $.ajax({
                    url: livesite + 'DbConfig/remove_desig'  ,
                    method:'POST',
                    data:{id:id},
                    success: function (resp) {
                        var response = JSON.parse(resp);
                        //alert(response.msg);
                        $.notify(response.msg,{
                                        type: 'success',
                                        allow_dismiss: false
                                    });
                        if(response.success == 0){
                            
                        }else{
                            $(s).hide();
                        }
                        
                    }
                });
    }
    
    function remove_depart(id,s){
        $.ajax({
                    url: livesite + 'DbConfig/remove_dept'  ,
                    method:'POST',
                    data:{id:id},
                    success: function (resp) {
                        var response = JSON.parse(resp);
//                        alert(response.msg);
                        $.notify(response.msg,{
                                        type: 'success',
                                        allow_dismiss: false
                                    });
                        if(response.success == 0){
                            
                        }else{
                            $(s).hide();
                        }
                        
                    }
                });
    }
    
    $("#desig").change(function(){
      var id = $(this).val();
      if(id == 'Select'){
          return false;
      }
      var designame = $(this).children("option:selected").html();
      $.ajax({
                    url: livesite + 'DbConfig/save_Desig/'  ,
                    method:'POST',
                    data:{id:id,named:designame},
                    success: function (resp) {
                        var response = JSON.parse(resp);
//                        alert(response.msg);
                        $.notify(response.msg,{
                                        type: 'success',
                                        allow_dismiss: false
                                    });
                        if(response.success == 0){
                            
                        }else{
                            var append = '<button type="button" onclick="remove_desig(ids,this);" class="btn bg-maroon btn-flat margin" >'+designame+'   x</button>';
                            $('#designation_lists').append(append.replace('ids', "'"+id+"'"));
                        }
                        
                    }
                });
    });    
    
    $("#dept").change(function(){
      var id = $(this).val();
      if(id == 'Select'){
          return false;
      }
      var designame = $(this).children("option:selected").html();
      $.ajax({
                    url: livesite + 'DbConfig/savedepartment/'  ,
                    method:'POST',
                    data:{id:id,named:designame},
                    success: function (resp) {
                        var response = JSON.parse(resp);
                        //alert(response.msg);
                        $.notify(response.msg,{
                                        type: 'success',
                                        allow_dismiss: false
                                    });
                        if(response.success == 0){
                            
                        }else{
                           //edited by megha on 13/09/2019 not deleting added department at the instance
                           // $('#load_calender').append("<button type='button' class='btn bg-maroon btn-flat margin' onclick='remove_depart("'"+id+"'",this);' >"+designame+" x </button>");
                        var append = '<button type="button" onclick="remove_depart(ids,this);" class="btn bg-maroon btn-flat margin" >'+designame+'   x</button>';
                            $('#load_calender').append(append.replace('ids', "'"+id+"'"));  
                        }
                        
                    }
                });
    });    
    
    
   $(document).ready(function() { 
	$('#desig').select2();
        $('#dept').select2();

    
    });

</script>