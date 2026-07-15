<meta name="viewport" content="width=device-width, initial-scale=1">
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
    <link href="<?php echo $this->webroot; ?>css/skins/_all-skins.min.css" rel="stylesheet" type="text/css" />
    <!-- iCheck -->
    <link href="<?php echo $this->webroot; ?>plugins/iCheck/flat/blue.css" rel="stylesheet" type="text/css" />
    <!-- Morris chart -->
    <link href="<?php echo $this->webroot; ?>plugins/morris/morris.css" rel="stylesheet" type="text/css" />
    <!-- jvectormap -->
    <link href="<?php echo $this->webroot; ?>plugins/jvectormap/jquery-jvectormap-1.2.2.css" rel="stylesheet" type="text/css" />
    <!-- Date Picker -->
    <link href="<?php echo $this->webroot; ?>plugins/datepicker/datepicker3.css" rel="stylesheet" type="text/css" />
    <!-- Daterange picker -->
    
    <link href="<?php echo $this->webroot; ?>plugins/timepicker/bootstrap-timepicker.css" rel="stylesheet" type="text/css" />
    <link href="<?php echo $this->webroot; ?>plugins/daterangepicker/daterangepicker-bs3.css" rel="stylesheet" type="text/css" />
    
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
    <!-- JS file -->
    <!--script src="<?php echo $this->webroot; ?>plugins/easyautocomplete/jquery.easy-autocomplete.min.js"></script-->
    <script src="<?php echo $this->webroot; ?>plugins/easyautocomplete/jquery.easy-autocomplete-bugfix.min.js"></script> 

    <!-- CSS file -->
    <link rel="stylesheet" href="<?php echo $this->webroot; ?>plugins/easyautocomplete/easy-autocomplete.min.css"> 

    <!-- Additional CSS Themes file - not required-->
    <link rel="stylesheet" href="<?php echo $this->webroot; ?>plugins/easyautocomplete/easy-autocomplete.themes.min.css">
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
    
	  
  
    <!-- Bootstrap WYSIHTML5 -->
    <script src="<?php echo $this->webroot; ?>plugins/bootstrap-wysihtml5/bootstrap3-wysihtml5.all.min.js" type="text/javascript"></script>
    <!-- Slimscroll -->
    <script src="<?php echo $this->webroot; ?>plugins/slimScroll/jquery.slimscroll.min.js" type="text/javascript"></script>
    <!-- FastClick -->
    <script src="<?php echo $this->webroot; ?>plugins/fastclick/fastclick.min.js" type="text/javascript"></script>
    
    <script src="<?php echo $this->webroot; ?>plugins/datatables/jquery.dataTables.min.js" type="text/javascript"></script>
    <script src="<?php echo $this->webroot; ?>plugins/datatables/dataTables.bootstrap.min.js" type="text/javascript"></script>
    <script src="<?php echo $this->webroot; ?>plugins/ajaxform/jquery.form.min.js" type="text/javascript"></script>
    <script src="<?php echo $this->webroot; ?>plugins/parsley/parsley.min.js" type="text/javascript"></script>
    
    
      <link href="<?php echo $this->webroot; ?>plugins/uploader/css/fileinput.css" media="all" rel="stylesheet" type="text/css" />
      <script src="<?php echo $this->webroot; ?>plugins/uploader/js/fileinput.js" type="text/javascript"></script>
    
    <script src="<?php echo $this->webroot; ?>plugins/select2/select2.full.min.js" type="text/javascript"></script>
    <!-- InputMask -->
    <script src="<?php echo $this->webroot; ?>plugins/input-mask/jquery.inputmask.js" type="text/javascript"></script>
    <script src="<?php echo $this->webroot; ?>plugins/input-mask/jquery.inputmask.date.extensions.js" type="text/javascript"></script>
    <script src="<?php echo $this->webroot; ?>plugins/input-mask/jquery.inputmask.extensions.js" type="text/javascript"></script>
    <!-- date-range-picker -->
    <script src="<?php echo $this->webroot; ?>plugins/daterangepicker/daterangepicker.js" type="text/javascript"></script>
    <!-- bootstrap color picker -->
    <script src="<?php echo $this->webroot; ?>plugins/colorpicker/bootstrap-colorpicker.min.js" type="text/javascript"></script>
    <!-- bootstrap time picker -->
  <!--
    <script src="plugins/timepicker/bootstrap-timepicker.min.js" type="text/javascript"></script>
    -->
    <script src="<?php echo $this->webroot; ?>plugins/nestable/jquery.nestable.js"></script>
    <!-- AdminLTE App -->
      <script src="<?php echo $this->webroot; ?>plugins/dropzone/dropzone.js" type="text/javascript"></script>
   
   
   
   
   
   <link rel="stylesheet" type="text/css" href="<?php echo $this->webroot; ?>css/index.css">
   
   
    <link rel="stylesheet" type="text/css" href="<?php echo $this->webroot; ?>plugins/bootstrap-timepicker/css/bootstrap-timepicker.css"/>
    <link rel="stylesheet" type="text/css" href="<?php echo $this->webroot; ?>plugins/bootstrap-timepicker/css/bootstrap-timepicker.min.css"/>
    <link rel="stylesheet" type="text/css" href="<?php echo $this->webroot; ?>plugins/bootstrap-timepicker/css/timepicker.less"/>
              <script src="<?php echo $this->webroot; ?>plugins/bootstrap-timepicker/js/bootstrap-timepicker.js"></script>
              <script src="<?php echo $this->webroot; ?>plugins/bootstrap-timepicker/js/bootstrap-timepicker.min.js"></script>
                        
                        
   
   <script src="<?php echo $this->webroot; ?>plugins/Timer/Timer.js" type="text/javascript"></script>
    <link href="<?php echo $this->webroot; ?>plugins/Timer/Timer.css" rel="stylesheet" type="text/css" />
   
   
   <link href="<?php echo $this->webroot; ?>plugins/select2/select2.css" media="all" rel="stylesheet" type="text/css" />
      <link href="<?php echo $this->webroot; ?>plugins/select2/select2.min.css" media="all" rel="stylesheet" type="text/css" />
      
    <script src="<?php echo $this->webroot; ?>plugins/select2/select2.full.min.js" type="text/javascript"></script>
    <script src="<?php echo $this->webroot; ?>plugins/select2/select2.full.js" type="text/javascript"></script>
    <script src="<?php echo $this->webroot; ?>plugins/select2/select2.js" type="text/javascript"></script>
   
   
       
   
   
    <script src="<?php echo $this->webroot; ?>plugins/datepair/datepair.js" type="text/javascript"></script>
    <script src="<?php echo $this->webroot; ?>plugins/datepair/jquery.datepair.js" type="text/javascript"></script>
    <script src="<?php echo $this->webroot; ?>plugins/notify/bootstrap-notify.js"></script>
    <link rel="stylesheet" type="text/css" href="<?php echo $this->webroot; ?>plugins/grid/css/ui.jqgrid.css"/>
	<script src="<?php echo $this->webroot; ?>plugins/grid/js/jquery.jqGrid.min.js"></script>
    <script src="<?php echo $this->webroot; ?>plugins/chartjs/Chart.min.js" type="text/javascript"></script>
     <!--DHTMLX -->
     <link rel="stylesheet" type="text/css" href="<?php echo $this->webroot; ?>plugins/dhtmlx/grid/dhtmlxgrid.css"/>
	<script src="<?php echo $this->webroot; ?>plugins/dhtmlx/grid/dhtmlxgrid.js"></script>
    
    <link rel="stylesheet" type="text/css" href="<?php echo $this->webroot; ?>plugins/easyui/themes/bootstrap/easyui.css"/>
    <link rel="stylesheet" type="text/css" href="<?php echo $this->webroot; ?>plugins/easyui/themes/mobile.css">  
    <link rel="stylesheet" type="text/css" href="<?php echo $this->webroot; ?>plugins/easyui/themes/icon.css">
	<script src="<?php echo $this->webroot; ?>plugins/easyui/jquery.easyui.min.js"></script>
	<script type="text/javascript" src="<?php echo $this->webroot; ?>plugins/easyui/jquery.easyui.mobile.js"></script> 
	
	<script type="text/javascript" src="<?php echo $this->webroot; ?>plugins/easyui/datagrid-scrollview.js"></script>
	
 <link href="<?php echo $this->webroot; ?>plugins/fullcalendar/fullcalendar.css" rel="stylesheet" type="text/css" />
    <link href="<?php echo $this->webroot; ?>plugins/fullcalendar/fullcalendar.min.css" rel="stylesheet" type="text/css" />

    <link rel="<?php echo $this->webroot; ?>plugins/fullcalendar/fullcalendar.print.css" media="print">
<!--    <script src="https://code.jquery.com/ui/1.11.4/jquery-ui.min.js"></script>-->
         <script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.10.2/moment.min.js"></script>               
    <script src="<?php echo $this->webroot; ?>plugins/fullcalendar/fullcalendar.min.js" type="text/javascript"></script>
	
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
	
    
    <script src="https://cdn.datatables.net/buttons/1.5.1/js/buttons.print.min.js" type="text/javascript"></script>
    <script src="https://cdn.datatables.net/buttons/1.5.1/js/dataTables.buttons.min.js" type="text/javascript"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.1.3/jszip.min.js" type="text/javascript"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.32/pdfmake.min.js" type="text/javascript"></script>
    <script src="https://cdn.datatables.net/buttons/1.5.1/js/buttons.html5.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/1.5.1/js/buttons.print.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.32/vfs_fonts.js"></script>
    
    
	
	
	   <script src="<?php echo $this->webroot; ?>plugins/tab/assets/jquery.pwstabs-1.2.1.js"></script>
	<script src="<?php echo $this->webroot; ?>plugins/jquery.isloading.js" type="text/javascript"></script>
    <script src="<?php echo $this->webroot; ?>js/fs.js" type="text/javascript"></script>
    <script src="<?php echo $this->webroot; ?>js/app.min.js" type="text/javascript"></script>
<section class="content-header">
 </section>
<script>var livesite = '<?php echo $this->webroot;?>'</script>
    
<section>
    <div class="container" style="margin-bottom : 46px; margin-top : 46px;">
    <h1 style="text-align: center;padding-top: 20px;"> Manage Holidays </h1>
        <form  id="contactInfoForm" action="<?php echo $this->webroot; ?>DbConfig/emp_upload" method="post">
    
    <div class="col-md-12">
        <br>
        <div class="box ">
            <div class="box-header with-border">
                <h3 class="box-title">Select Holiday list </h3>
                <p style="margin-top: 17px;
    text-align: center; ">You can add additional holidays to this list from admin login . </p>
                <div class="box-tools pull-right">
                    <button class="btn btn-box-tool" data-widget="collapse"><i class="fa fa-minus"></i></button>
                </div> 
            </div><!-- /.box-header -->
            <div class="box-body">
                
                <div class="col-md-4">
                    <?php foreach($arr_holidays as $val) { ?>
                    <input name="checklists[]" type="checkbox" value="<?php echo $val['HOLIDAY_GROUP_ID']; ?>" class="checkbox" ><button type="button" class="btn bg-maroon btn-flat margin" onclick="move_departments(<?php echo $val['HOLIDAY_GROUP_ID']; ?>);"><?php echo $val['HOLIDAY_GROUP_NAME']; ?></button>
                    <?php } ?>
                </div>
                <div class="col-md-8" id="load_calender">
                    
                
                </div>

            </div><!-- /.box-body -->

        </div>
        
</div>
    
                 <button type="button" id="cinfocancel" class="btn btn-info pull-right "  onclick="window.location.href = livesite + 'Dashboard'" style="margin-right:5px">Skip Setup </button>
                   <button type="submit" id="cinfosave" class="btn btn-success pull-right "   style="margin-right:5px">Next</button>
                
                   
                </form>
    </div>

</section>

              
<div id="modalForm" class="modal fade">
    <div class="modal-dialog">
        <div class="modal-content" id="modalForm-content">
            <!-- Content will be loaded here from "remote.php" file -->
        </div>
    </div>
</div>
              
<div id="largeModalForm" class="modal fade">
    <div class="modal-dialog modal-lg">
        <div class="modal-content" id="largeModalForm-content">
            <!-- Content will be loaded here from "remote.php" file -->
        </div>
    </div>
</div>

<!-- Custom modal form with width : 85% -->
<div id="customModalForm" class="modal fade">
    <div class="modal-dialog modal-lg" style="width: 85%;">
        <div class="modal-content" id="customModalForm-content">
            <!-- Content will be loaded here from "remote.php" file -->
        </div>
    </div>
</div>
          
<div id="smallModalForm" class="modal fade" >
    <div class="modal-dialog modal-md">
        <div class="modal-content" id="smallModalForm-content">
            <!-- Content will be loaded here from "remote.php" file -->
        </div>
    </div>
</div>

<script type="text/javascript">
    function save_holidays(){
            $.ajax({
            url: livesite + 'DbConfig/save_shift'  ,
            method:'POST',
            data:{id:id},
            success: function (resp) {
                    var response = JSON.parse(resp);
                    if(response.success == 0){

                    }else{
                        $.notify("Policy Added",{
                                            type: 'success',
                                            allow_dismiss: false
                                        });
                        $(s).parent().find('button').addClass("bg-green").removeClass('bg-maroon');
                        $('#locallists').append('<div class="containe"><button type="button" class="btn bg-maroon btn-flat margin" onclick="move_departments('+id+');">'+name+'</button></div>');
                    }

                }
            });
    }
    
    function move_departments(s){
        var id = s;
        $('#load_calender').load(livesite+ 'DbConfig/showholidays/'+s);
    }
    
   $(document).ready(function() { 
	

    
    });

</script>