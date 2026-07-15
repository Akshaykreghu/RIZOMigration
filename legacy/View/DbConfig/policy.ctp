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

.col-sm-12 .row {
    width: auto !important; 
}

.col-sm-12 .row .col-sm-3:nth-child(2n){
    width: 236px !important;
    margin-top: 12px !important;
}

.row .col-sm-6 .list-group-item {
    height: auto !important ;
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
    

<style>
    .lud-ohdo {
        clear: both;
        display: flex;
        margin-bottom: 8px;
        max-width: 500px;
        width: 100%;
    }
    .lud-ohday {
        background-color: #f5f5f5;
        display: inline-block;
        flex-grow: 1;
        flex-grow: 1;
        height: 45px;
        margin: 1px 6px 1px 0px;
        min-width: 38px;
        position: relative;
    }
    label {
        cursor: default;
    }
    .lud-ohdt {
        display: none;
    }
    .g, body, html, input, .std, h1 {
        /*font-size: small;*/
        font-family: arial,sans-serif;
    }
    /*    input[type="radio" i], input[type="checkbox" i] {
            background-color: initial;
            margin: 3px 0.5ex;
            padding: initial;
            border: initial;
        }*/
    input, textarea, keygen, select, button {
        text-rendering: auto;
        color: initial;
        letter-spacing: normal;
        word-spacing: normal;
        text-transform: none;
        text-indent: 0px;
        text-shadow: none;
        display: inline-block;
        text-align: start;
        margin: 0em 0em 0em 0em;
        font: 13.3333px Arial;
    }
    .fAwjXaCTMo5__content {
        border-radius: 2px;
        border-radius: 2px;
        position: relative;
        display: inline-block;
        z-index: 1060;
        background-color: #fff;
        opacity: 0;
        text-align: left;
        vertical-align: middle;
        white-space: normal;
        overflow: hidden;
        transform: translateZ(0);
        -webkit-box-shadow: 0px 5px 26px 0px rgba(0,0,0,0.22),0px 20px 28px 0px rgba(0,0,0,0.30);
        box-shadow: 0px 5px 26px 0px rgba(0,0,0,0.22),0px 20px 28px 0px rgba(0,0,0,0.30);
    }
    .lud-ohdt:checked~.lud-ohdf {
        color: #fff;
        background-color: #4285f4;
    }
    .lud-ohdf {
        color: black;
        font-size: 14px;
        font-weight: 500;
        line-height: 45px;
        pointer-events: none;
        position: absolute;
        text-align: center;
        text-transform: uppercase;
        -webkit-user-select: none;
        user-select: none;
        width: 100%;
        /*margin-top: -11px;*/
    }
    label {
        cursor: default;
    }


    .lud-ohdo2 {
        clear: both;
        display: flex;
        margin-bottom: 8px;
        max-width: 500px;
        width: 100%;
    }
    .lud-ohday2 {
        background-color: #f5f5f5;
        display: inline-block;
        flex-grow: 1;
        flex-grow: 1;
        height: 25px;
        margin: 1px 6px 1px 0px;
        min-width: 38px;
        position: relative;
    }
    label {
        cursor: default;
    }
    .lud-ohdt2 {
        display: none;
    }
    .g, body, html, input, .std, h1 {
        /*font-size: small;*/
        font-family: arial,sans-serif;
    }
    input[type="radio" i], input[type="checkbox" i] {
        background-color: initial;
        margin: 3px 0.5ex;
        padding: initial;
        border: initial;
    }
    input, textarea, keygen, select, button {
        text-rendering: auto;
        color: initial;
        letter-spacing: normal;
        word-spacing: normal;
        text-transform: none;
        text-indent: 0px;
        text-shadow: none;
        display: inline-block;
        text-align: start;
        margin: 0em 0em 0em 0em;
        font: 13.3333px Arial;
    }
    .fAwjXaCTMo5__content {
        border-radius: 2px;
        border-radius: 2px;
        position: relative;
        display: inline-block;
        z-index: 1060;
        background-color: #fff;
        opacity: 0;
        text-align: left;
        vertical-align: middle;
        white-space: normal;
        overflow: hidden;
        transform: translateZ(0);
        -webkit-box-shadow: 0px 5px 26px 0px rgba(0,0,0,0.22),0px 20px 28px 0px rgba(0,0,0,0.30);
        box-shadow: 0px 5px 26px 0px rgba(0,0,0,0.22),0px 20px 28px 0px rgba(0,0,0,0.30);
    }
    .lud-ohdt2:checked~.lud-ohdf2 {
        color: #fff;
        background-color: red;
    }
    .lud-ohdf2 {
        color: black;
        font-size: 14px;
        font-weight: 500;
        line-height: 25px;
        pointer-events: none;
        position: absolute;
        text-align: center;
        text-transform: uppercase;
        -webkit-user-select: none;
        user-select: none;
        width: 100%;
        /*margin-top: -9px;*/
    }
    .material-switch > input[type="checkbox"] {
        display: none;   
    }

    .material-switch > label {
        cursor: pointer;
        height: 0px;
        position: relative; 
        width: 40px;  
    }

    .material-switch > label::before {
        background: rgb(0, 0, 0);
        box-shadow: inset 0px 0px 10px rgba(0, 0, 0, 0.5);
        border-radius: 8px;
        content: '';
        height: 16px;
        margin-top: -8px;
        position:absolute;
        opacity: 0.3;
        transition: all 0.4s ease-in-out;
        width: 40px;
    }
    .material-switch > label::after {
        background: rgb(255, 255, 255);
        border-radius: 16px;
        box-shadow: 0px 0px 5px rgba(0, 0, 0, 0.3);
        content: '';
        height: 24px;
        left: -4px;
        margin-top: -8px;
        position: absolute;
        top: -4px;
        transition: all 0.3s ease-in-out;
        width: 24px;
    }
    .material-switch > input[type="checkbox"]:checked + label::before {
        background: inherit;
        opacity: 0.5;
    }
    .material-switch > input[type="checkbox"]:checked + label::after {
        background: inherit;
        left: 20px;
    }
    .form-control
    {
        height:24px;
    }
    .margin-top{
        margin-top: 12px;
    }
    .multitimes{
        transition: 1s;
    }
    .Exceptionshow{
        display:none;
    }
    
    .h1{
        font-size: 36px !important;
    }
    

</style>
<style>
	#shiftpolicygrouptable_wrapper .DTTT.btn-group ,#shiftpolicytable_wrapper .DTTT.btn-group{
		padding-left: 5px;
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
    <div class="container" style="margin-bottom : 46px; margin-top : 16px;">
        <h1 class="h1" style="text-align: center;"> Manage Timings & Weekly offs </h1>
    <form  id="contactInfoForm" action="<?php echo $this->webroot; ?>DbConfig/save_policies" method="post">
        <p style="margin-top: 17px;
    text-align: center; ">You can edit the timings for each shift later from admin login  . </p>

        <div class="col-md-12">
            <br>
            <div class="box ">
                <div class="box-header with-border">
                    <h3 class="box-title">Select Groups </h3>
                    <div class="box-tools pull-right">
                        <button class="btn btn-box-tool" data-widget="collapse"><i class="fa fa-minus"></i></button>
                    </div> 
                </div><!-- /.box-header -->
                <div class="box-body">

                    <div class="col-md-4">
                        <p>Choose pre-defined policies below or Create new  </p>
                        <?php foreach ($arr_holidays as $val) { ?>
                            <div class="containe"><input name="checklists[]" type="checkbox" value="<?php echo $val['day_time_seq']; ?>" onchange="save_shift_policies(this)" class="checkbox" ><button type="button" class="btn bg-maroon btn-flat margin" onclick="move_departments(<?php echo $val['day_time_seq']; ?>);"><?php echo $val['day_time_desc']; ?></button></div>
                        <?php } ?>
                            
                        <h4>Policy Lists </h4>    
                        <button type="button" class="btn bg-green btn-flat margin" onclick="showLargeModalForm(livesite+'DayTimeProcedure/form');">Add New Policy <li class="fa fa-plus"></li></button>
                        
                        <div id="locallists">
                            <?php foreach ($arr_holidays_local as $val) { ?>
                            <div class="containe"><button type="button" class="btn bg-maroon btn-flat margin" onclick="move_departments(<?php echo $val['DayTimeProcedures']['day_time_seq']; ?>);"><?php echo $val['DayTimeProcedures']['day_time_desc']; ?></button></div>
                        <?php } ?>
                        </div>
                    </div>
                    <div class="col-md-8" id="load_calender">

                        <div style="height: 400px; text-align: center;
    padding-top: 23%;
    border: 1px dotted; ">
                            Select a Policy to view details here 
                        </div>


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
    function save_shift_policies(s){
        
        var id = $(s).val();
        var name = $(s).parent().find('button').html();
        if($(s).prop("checked") == true){
            
            $.ajax({
            url: livesite + 'DbConfig/save_shift'  ,
            method:'POST',
            data:{id:id},
            success: function (resp) {
                var response = JSON.parse(resp);
                if(response.success == 0){
                  //edited by megha removed duplicate entry of shift policy on 14/09/2019
                             $.notify("Policy Already Exists",{
                                        type: 'danger',
                                        allow_dismiss: false
                                    });
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
            
        }else{
            
            $.ajax({
            url: livesite + 'DbConfig/remove_shift'  ,
            method:'POST',
            data:{id:id},
            success: function (resp) {
                var response = JSON.parse(resp);
                if(response.success == 0){

                }else{
                    $.notify("Policy Removed",{
                                        type: 'success',
                                        allow_dismiss: false
                                    });
                    $(s).parent().find('button').addClass("bg-maroon").removeClass('bg-green');
                }

            }
        });
            
        }
        
    }
    function move_departments(s){
        var id = s;
        $('#load_calender').load(livesite+ 'DbConfig/show_policiess/'+s);
    }
    
   $(document).ready(function() { 
	

        $('#spForm').parsley(); 
    var options = { 
success:       function(responseText, statusText, xhr, $form){
	closeModal();
        $.notify("Branch Saved Successfully ", {
            type: 'success',
            allow_dismiss: false
        });
}
    }; 
 
    // bind to the form's submit event 
    $('#spForm').submit(function() { 
        $('#submit').html('<li class="fa fa-spin fa-spinner"></li> Saving ');
        $(this).ajaxSubmit(options); 
 
        
        return false; 
    });
    
    });

</script>