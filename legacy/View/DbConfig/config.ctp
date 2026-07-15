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
    /*background-color: #f2f2f2;*/
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
    
    <script src="<?php echo $this->webroot; ?>plugins/input-mask/jquery.inputmask.js" type="text/javascript"></script>
    <script src="<?php echo $this->webroot; ?>plugins/input-mask/jquery.inputmask.date.extensions.js" type="text/javascript"></script>
    <script src="<?php echo $this->webroot; ?>plugins/input-mask/jquery.inputmask.extensions.js" type="text/javascript"></script>
    
    <script src="<?php echo $this->webroot; ?>plugins/notify/bootstrap-notify.js"></script>
   
   <link rel="stylesheet" type="text/css" href="<?php echo $this->webroot; ?>css/index.css">
   
    
    <link rel="stylesheet" type="text/css" href="<?php echo $this->webroot; ?>plugins/easyui/themes/bootstrap/easyui.css"/>
    <link rel="stylesheet" type="text/css" href="<?php echo $this->webroot; ?>plugins/easyui/themes/mobile.css">  
    <link rel="stylesheet" type="text/css" href="<?php echo $this->webroot; ?>plugins/easyui/themes/icon.css">
	<script src="<?php echo $this->webroot; ?>plugins/easyui/jquery.easyui.min.js"></script>
	<script type="text/javascript" src="<?php echo $this->webroot; ?>plugins/easyui/jquery.easyui.mobile.js"></script> 
	
	<script type="text/javascript" src="<?php echo $this->webroot; ?>plugins/easyui/datagrid-scrollview.js"></script>
	
    <style>
        .btn-primary,.btn-success{
            background-color: #126aa3 !important;
        }
        
        h1{
            color: #126aa3  !important;
        }
    </style>
        
    <style>
        .datagrid-header, .datagrid-td-rownumber {
            background-color: #fbfbfb;
             background: -webkit-linear-gradient(top,#fbfbfb 0,#fbfbfb 100%); 
            background: -moz-linear-gradient(top,#fbfbfb 0,#fbfbfb 100%);
            background: -o-linear-gradient(top,#fbfbfb 0,#fbfbfb 100%);
             background: linear-gradient(to bottom,#fbfbfb 0,#fbfbfb 100%); 
            background-repeat: repeat-x;
            filter: progid:DXImageTransform.Microsoft.gradient(startColorstr=#ffffff,endColorstr=#F2F2F2,GradientType=0);
        }
    </style>
    
	
	
	   <script src="<?php echo $this->webroot; ?>plugins/tab/assets/jquery.pwstabs-1.2.1.js"></script>
	<script src="<?php echo $this->webroot; ?>plugins/jquery.isloading.js" type="text/javascript"></script>
    <script src="<?php echo $this->webroot; ?>js/fs.js" type="text/javascript"></script>
    <script src="<?php echo $this->webroot; ?>js/app.min.js" type="text/javascript"></script>
<section class="content-header">
 </section>
<script>var livesite = '<?php echo $this->webroot;?>'</script>
    
<section >
    <div class="container" style="/*margin-bottom : 46px; margin-top : 46px;*/">
        <h1 style="text-align: center; margin-top: 0px;  "> Add Branches that you want to manage  </h1>
        <p  style="text-align: center; ">You should have atleast one branch . </p>
        <form  id="contactInfoForm" action="<?php echo $this->webroot; ?>DbConfig/designation_departments" method="post">

            <div class="col-md-12" style="padding-left: 68px; padding-right: 68px; ">
                <br>
                <div class="box ">
                    <div class="box-header with-border">
                        <h3 class="box-title">Lists of Branches</h3>
                        <div class="box-tools pull-right">
                            <button class="btn btn-box-tool" data-widget="collapse"><i class="fa fa-minus"></i></button>
                        </div>
                    </div><!-- /.box-header -->
                    <div class="box-body">
                        <table id="brtable" class="table table-bordered table-hover">
                            <thead>
                                <tr>
                                    <th></th>
                                    <th>Name</th>
                                    <th>Address</th>
                                    <th>City</th>
                                    <th>State</th>
                                    <th>Pincode</th>
                                </tr>
                            </thead>
                            <tbody>
                            </tbody>
                        </table>

                    </div><!-- /.box-body -->

                </div>

            </div>
            <div class="col-md-12">
                <hr>
                <button type="button" id="cinfocancel" class="btn btn-info pull-right "  onclick="window.location.href = livesite + 'Dashboard'" style="margin-right:5px">Skip Setup </button>
                <button type="submit" id="cinfosave" class="btn btn-success pull-right "   style="margin-right:5px">Next</button>
            </div>

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
    
    
   $(document).ready(function() { 
	

    $('#brtable').datagrid({
        url:livesite+"Branch/listunits",
        pagination:true,
        singleSelect:true,
        width:'96%',
        rownumbers:true,
        toolbar: [{
            text:'New',
            iconCls:'icon-add',
            handler: function(){
                showModalForm(livesite+'Branch/form')
            }
        },{
            iconCls: 'icon-edit',
            text:'Edit',
            handler: function(){
                var row = $('#brtable').datagrid('getSelected');
                if (row){
                    showModalForm(livesite+'Branch/form?id='+row.id)
                }else{
                    alert("Please select a record to edit")
                }
            }
        },'-',{
            iconCls: 'icon-remove',
            text:'Remove',
            handler: function(){
                var rows = $('#brtable').datagrid('getSelections');
                if (rows){
                    var str_ids = "";
                    for(var i=0;i<rows.length;i++){
                        var data = rows[i];
                        if(str_ids == ""){
                                str_ids += data.id;
                        }else
                        {
                                str_ids += ","+data.id;
                        }
                    }
                    if (confirm("Are you sure want to delete ")) {
                        $.ajax({
                            url:  livesite+"Branch/deleteBranches",
                            data : {
                                ids : str_ids
                            },
                            success : function(response) {
                                var response =  $.parseJSON(response);
                                if(response.msg){
                                    $.notify(response.msg,{
                                        type: 'success',
                                        allow_dismiss: true
                                    });
                                }
                                reloadTable('brtable')
                            }
                        });
                    }
                }
                else
                {
                    alert("Please select a record to edit")
                }
            }
        }],
        fitColumns:true,
        pageList:[2,5,10,50,100],
        columns:[[
            {field:'branch_name',title:'Name',width:"20%",sortable:true},
            {field:'address',title:'Address',width:"40%",sortable:true},
            {field:'city',title:'City',width:"20%",sortable:true},
            {field:'state',title:'State',width:"20%",sortable:true},
        ]]
    });
    
    });

</script>