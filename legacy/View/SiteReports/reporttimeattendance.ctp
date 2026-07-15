<style type="text/css">
    
    td, th {
        text-align: left;
        padding: 8px;
        line-height: 1.42857143;
        vertical-align: top;
        border: 1px solid #B2B2B2;
        font-size: 10px;
    }
    .scroll{
         width: 100%;
    height: 100%;
    margin: 0;
    padding: 0;
    overflow: auto;
    }
</style>
<style>
div.inflow {

}
div.positioner {position: absolute; right: 0;} /*may not be needed: see below*/
div.fixed {
overflow: auto;

}
/*tr td.fix{
    position:fixed;
}*/
</style>
<?php if( $mode == '' ){ ?>
<?php $total_columns = count($arr_dates)+count($arr_registerentry_heads)+2;$columnwidth = 100/$total_columns; ?>
<div class="modal-body mCustomScrollbar" id="attendance" data-mcs-theme="dark">
    <legend class="text-center"><strong>Time Attendance Report</strong></legend>
    <!--button class="close modalMaximise"> <i class='fa fa-plus-square'></i> </button-->
    <div class="row">
        <div class="col-sm-6 col-xs-6 col-md-6 text-left">
            <?php
                if(!empty($arr_company_info)){
                    echo '<p><strong>'.$arr_company_info[0]['company_name'].'</strong></p>';
                    echo '<p><strong>'.$arr_company_info[0]['Address'].'</strong></p>';
                }
            ?>
        </div>
        <div class="col-sm-6 col-xs-6 col-md-6 text-right">
            <?php echo '<p><strong>'.$report_month.'</strong></p>'; ?>
        </div>
    </div>
    
    <?php foreach ($arr_timeattendancereporttemplate as $branch_code=>$data){ ?>
        <div class="row">
            <div class="col-md-4 col-sm-4 col-xs-4 pull-right text-right">
                <?php echo '<p><strong>'.(isset($arr_branchinfo[$branch_code]['branch_code'])?$arr_branchinfo[$branch_code]['branch_code'].' - ':'').(isset($arr_branchinfo[$branch_code]['branch_name'])?$arr_branchinfo[$branch_code]['branch_name']:'').'</strong></p>'; ?>
            </div>
        </div>
        <div class="row">
            <div class="col-sm-12 col-xs-12 col-md-12">
                <div class="inflow">
                                        <div class="fixed">
                <table class="table table-bordered " id="table-timeattendancereport"  >
                        <thead>
                            <tr>
                                <th class="text-center" style="width: <?php echo $columnwidth.'%'; ?>;">Sl No.</th>
                                <th class="text-center" style="width: <?php echo $columnwidth.'%'; ?>;">Employee Name</th>
                                <th class="text-center" style="width: <?php echo $columnwidth.'%'; ?>;" colspan="<?php echo count($arr_dates); ?>">Days</th>
                                <th class="text-center" style="width: <?php echo $columnwidth.'%'; ?>;" colspan="<?php echo count($arr_registerentry_heads); ?>">Summary</th>
                            </tr>
                            <tr>
                                <th class="text-center"></th>
                                <th class="text-center"></th>
                                <?php foreach ($arr_dates as $key=>$val){ ?>
                                <th class="text-center"><?php echo $val; ?></th>
                                <?php } ?>
                                <?php foreach ($arr_registerentry_heads as $head=>$val) { ?>
                                <th class="text-center"><?php echo $head;  ?></th>
                                <?php } ?>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if(!empty($data)){ $i = 1; ?>
                                <?php foreach ($data as $emp_pkey => $regdata){ ?>
                                    <tr>
                                        <td><?php echo $i++; ?></td>
                                        <td>
                                            <?php 
                                                echo (isset($regdata['employeeinfo']['first_name'])?$regdata['employeeinfo']['first_name']:' ').(isset($regdata['employeeinfo']['last_name'])?$regdata['employeeinfo']['last_name']:' ')." ".(isset($regdata['emp']['emp_company_id'])?$regdata['emp']['emp_company_id']:' ');
                                            ?>
                                        </td>
                                        
                                        <?php if(!empty($regdata['registerentries'])){ foreach ($regdata['registerentries'] as $day=>$regentry){ ?>
                                        <td><?php echo $regentry; ?></td>
                                        <?php } }else{ ?>
                                        <?php foreach ($arr_dates as $date){ ?>
                                        <td></td>
                                        <?php } ?>
                                        <?php } ?>
                                        
                                        <?php if(!empty($regdata['registerentrysummary'])){ foreach ($regdata['registerentrysummary'] as $regentryhead=>$cnt_regentry){ ?>
                                        <td><?php echo $cnt_regentry; ?></td>
                                        <?php } }else{ ?>
                                        <?php foreach ($arr_registerentry_heads as $date){ ?>
                                        <td></td>
                                        <?php } ?>
                                        <?php } ?>
                                        
                                    </tr>
                                    
                                    <tr>
                                    <?php if(!empty($regdata['checkin'])){ ?>
                                        <td></td>
                                        <td>Check In Time</td>
                                        <?php foreach ($arr_dates as $index => $day){ ?>
                                        <?php if(isset($regdata['checkin'][$day])){ ?>
                                        <td><?php echo $regdata['checkin'][$day]; ?></td>
                                        <?php }else{ ?>
                                        <td></td>
                                        <?php } ?>
                                        <?php } ?>
                                        <td colspan="<?php echo count($arr_registerentry_heads); ?>"></td>
                                    <?php }else{ ?>
                                        <td></td>
                                        <td>Check In Time</td>
                                        <?php foreach ($arr_dates as $date){ ?>
                                        <td></td>
                                        <?php } ?>
                                        <td colspan="<?php echo count($arr_registerentry_heads); ?>"></td>
                                    <?php } ?>
                                    </tr>
                                    
                                    <tr>
                                    <?php if(!empty($regdata['checkout'])){ ?>
                                        <td></td>
                                        <td>Check Out Time</td>
                                        <?php foreach ($arr_dates as $index => $day){ ?>
                                        <?php if(isset($regdata['checkout'][$day])){ ?>
                                        <td><?php echo $regdata['checkout'][$day]; ?></td>
                                        <?php }else{ ?>
                                        <td></td>
                                        <?php } ?>
                                        <?php } ?>
                                        <td colspan="<?php echo count($arr_registerentry_heads); ?>"></td>
                                    <?php }else{ ?>
                                        <td></td>
                                        <td>Check Out Time</td>
                                        <?php foreach ($arr_dates as $date){ ?>
                                        <td></td>
                                        <?php } ?>
                                        <td colspan="<?php echo count($arr_registerentry_heads); ?>"></td>
                                    <?php } ?>
                                    </tr>
                                    
                                    <tr>
                                    <?php if(!empty($regdata['duration'])){ ?>
                                        <td></td>
                                        <td>Duration(In Min)</td>
                                        <?php foreach ($arr_dates as $index => $day){ ?>
                                        <?php if(isset($regdata['duration'][$day])){ ?>
                                        <td><?php echo $regdata['duration'][$day]; ?></td>
                                        <?php }else{ ?>
                                        <td></td>
                                        <?php } ?>
                                        <?php } ?>
                                        <td colspan="<?php echo count($arr_registerentry_heads); ?>"></td>
                                     <?php }else{ ?>
                                        <td></td>
                                        <td>Duration(In Min)</td>
                                        <?php foreach ($arr_dates as $date){ ?>
                                        <td></td>
                                        <?php } ?>
                                        <td colspan="<?php echo count($arr_registerentry_heads); ?>"></td>
                                    <?php } ?>
                                    </tr>
                                    
                                <?php } ?>
                            <?php }else{ ?>
                            <tr>
                                <td colspan="100%">No records found!</td>
                            </tr>
                            <?php } ?>
                        </tbody>
                </table>
                                            </div>
        </div>
            </div>
        </div>
    <?php } ?>
    
<!--    <div class="row">
        <div class="form-group">
            <div class="col-md-12" align="right">
                <a href="#" class="btn btn-default" onclick="downloadReport('TimeAttendance','pdf');" ><i class="icon-file"></i>Download As PDF</a>
                <a href="#" class="btn btn-default" onclick="downloadReport('TimeAttendance','excel');"><i class="icon-file"></i>Download As Excel</a>
            </div>
        </div>
    </div>-->
</div>
<script>
    $(document).ready(function(){
    (function($){
        $(window).on("load",function(){
            $("#attendance").mCustomScrollbar(
     {
         theme:"dark"    
     }
    );
        });
    })(jQuery);
    });
</script>
<?php }else{ ?>
<?php //echo '<style>'.file_get_contents("css/pdfbootstrap.css").'</style>'; ?>
<style type="text/css">
    body {
        line-height: 2em;
    }
    .block-container {
        width: 95%;
        padding: 20px;
        border: #000000 solid thin;
    }
    .sub-head {
        border-bottom: #000000 solid thin;
    }
    .row {
        height: 32px;
    }
    .col-md-4 {
        width: 33.33%;
        float: left;
    }
    table {
        border: 1px solid #f4f4f4;
        width: 100%;
        max-width: 100%;
        margin-bottom: 20px;
        background-color: transparent;
        border-spacing: 0;
        border-collapse: collapse;
    }
    td, th {
        text-align: left;
        padding: 8px;
        line-height: 1.42857143;
        vertical-align: top;
        border: 1px solid #B2B2B2;
        font-size: 4;
    }
</style>

<?php $total_columns = count($arr_dates)+count($arr_registerentry_heads);$columnwidth = 100/$total_columns*2; ?>
<?php
echo $this->element('reportadminheader',array(
'title'=>'Time Attendance Employees Report'));
?>
<div>
    <legend class="text-center"><strong>Time Attendance Report</strong></legend>
    
    <!--button class="close modalMaximise"> <i class='fa fa-plus-square'></i> </button-->
    <div>
        <div>
            <?php
                if(!empty($arr_company_info)){
                    echo '<p><strong>'.$arr_company_info[0]['company_name'].'</strong></p>';
                    echo '<p><strong>'.$arr_company_info[0]['Address'].'</strong></p>';
                }
            ?>
        </div>
        <div>
            <?php echo '<p><strong>'.$report_month.'</strong></p>'; ?>
        </div>
    </div>
    
    <?php foreach ($arr_timeattendancereporttemplate as $branch_code=>$data){ ?>
        <div class="row">
            <div>
                <?php echo '<p><strong>'.(isset($arr_branchinfo[$branch_code]['branch_code'])?$arr_branchinfo[$branch_code]['branch_code'].' - ':'').(isset($arr_branchinfo[$branch_code]['branch_name'])?$arr_branchinfo[$branch_code]['branch_name']:'').'</strong></p>'; ?>
            </div>
        </div>
        <div>
            <div>
                <table>
                        <thead>
                            <tr>
                                <th class="text-center">Sl No.</th>
                                <th class="text-center">Employee Name</th>
                                <th class="text-center" colspan="<?php echo count($arr_dates); ?>">Days</th>
                                <th class="text-center" colspan="<?php echo count($arr_registerentry_heads); ?>">Summary</th>
                            </tr>
                            <tr>
                                <th class="text-center"></th>
                                <th class="text-center"></th>
                                <?php foreach ($arr_dates as $key=>$val){ ?>
                                <th class="text-center"><?php echo $val; ?></th>
                                <?php } ?>
                                <?php foreach ($arr_registerentry_heads as $head=>$val) { ?>
                                <th class="text-center"><?php echo $head;  ?></th>
                                <?php } ?>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if(!empty($data)){ $i = 1; ?>
                                <?php foreach ($data as $emp_pkey => $regdata){ ?>
                                    <tr>
                                        <td><?php echo $i++; ?></td>
                                        <td>
                                            <?php 
                                                echo (isset($regdata['employeeinfo']['first_name'])?$regdata['employeeinfo']['first_name']:' ').(isset($regdata['employeeinfo']['last_name'])?$regdata['employeeinfo']['last_name']:' ')." ".(isset($regdata['emp']['emp_company_id'])?$regdata['emp']['emp_company_id']:' ');
                                            ?>
                                        </td>
                                        
                                        <?php if(!empty($regdata['registerentries'])){ foreach ($regdata['registerentries'] as $day=>$regentry){ ?>
                                        <td><?php echo $regentry; ?></td>
                                        <?php } }else{ ?>
                                        <?php foreach ($arr_dates as $date){ ?>
                                        <td></td>
                                        <?php } ?>
                                        <?php } ?>
                                        
                                        <?php if(!empty($regdata['registerentrysummary'])){ foreach ($regdata['registerentrysummary'] as $regentryhead=>$cnt_regentry){ ?>
                                        <td><?php echo $cnt_regentry; ?></td>
                                        <?php } }else{ ?>
                                        <?php foreach ($arr_registerentry_heads as $date){ ?>
                                        <td></td>
                                        <?php } ?>
                                        <?php } ?>
                                        
                                    </tr>
                                    
                                    <tr>
                                    <?php if(!empty($regdata['checkin'])){ ?>
                                        <td></td>
                                        <td>Check In Time</td>
                                        <?php foreach ($arr_dates as $index => $day){ ?>
                                        <?php if(isset($regdata['checkin'][$day])){ ?>
                                        <td><?php echo ($regdata['checkin'][$day] != '')?date("H:i",strtotime($regdata['checkin'][$day])):''; ?></td>
                                        <?php }else{ ?>
                                        <td></td>
                                        <?php } ?>
                                        <?php } ?>
                                        <td colspan="<?php echo count($arr_registerentry_heads); ?>"></td>
                                    <?php }else{ ?>
                                        <td></td>
                                        <td>Check In Time</td>
                                        <?php foreach ($arr_dates as $date){ ?>
                                        <td></td>
                                        <?php } ?>
                                        <td colspan="<?php echo count($arr_registerentry_heads); ?>"></td>
                                    <?php } ?>
                                    </tr>
                                    
                                    <tr>
                                    <?php if(!empty($regdata['checkout'])){ ?>
                                        <td></td>
                                        <td>Check Out Time</td>
                                        <?php foreach ($arr_dates as $index => $day){ ?>
                                        <?php if(isset($regdata['checkout'][$day])){ ?>
                                        <td><?php echo ($regdata['checkout'][$day] !== '')?date("H:i",strtotime($regdata['checkout'][$day])):''; ?></td>
                                        <?php }else{ ?>
                                        <td></td>
                                        <?php } ?>
                                        <?php } ?>
                                        <td colspan="<?php echo count($arr_registerentry_heads); ?>"></td>
                                    <?php }else{ ?>
                                        <td></td>
                                        <td>Check Out Time</td>
                                        <?php foreach ($arr_dates as $date){ ?>
                                        <td></td>
                                        <?php } ?>
                                        <td colspan="<?php echo count($arr_registerentry_heads); ?>"></td>
                                    <?php } ?>
                                    </tr>
                                    
                                    <tr>
                                    <?php if(!empty($regdata['duration'])){ ?>
                                        <td></td>
                                        <td>Duration(In Min)</td>
                                        <?php foreach ($arr_dates as $index => $day){ ?>
                                        <?php if(isset($regdata['duration'][$day])){ ?>
                                        <td><?php echo $regdata['duration'][$day]; ?></td>
                                        <?php }else{ ?>
                                        <td></td>
                                        <?php } ?>
                                        <?php } ?>
                                        <td colspan="<?php echo count($arr_registerentry_heads); ?>"></td>
                                    <?php }else{ ?>
                                        <td></td>
                                        <td>Duration(In Min)</td>
                                        <?php foreach ($arr_dates as $date){ ?>
                                        <td></td>
                                        <?php } ?>
                                        <td colspan="<?php echo count($arr_registerentry_heads); ?>"></td>
                                    <?php } ?>
                                    </tr>
                                    
                                <?php } ?>
                            <?php }else{ ?>
                            <tr>
                                <td colspan="100%">No records found!</td>
                            </tr>
                            <?php } ?>
                        </tbody>
                </table>
            </div>
        </div>
    <?php } ?>
    
    
</div>
<?php } ?>