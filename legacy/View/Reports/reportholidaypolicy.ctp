<?php if ($mode == '') { ?>
    <div class="modal-body" style="overflow-y: auto;">
        <legend style="text-align:center; font-weight: bold">Holiday Policy Report</legend>
        <div class="row">
            <div class="col-md-12">
                <div class="box ">
                    <?php
                    $i = 0;
                    foreach ($arr_leavepolicydetails_for_template as $value) {
                        $i += 1;
                        ?>
                        <div class="box-body">
                            <fieldset>
                                <legend style="font-weight:bold;">Holiday Summary Of  <?php echo $value['HoliDayName']; ?>  </legend>
                                <div class="row">

                                </div>
                                <div class="row">
                                    <div class="col-md-12">

                                    </div>
                                </div>


                            </fieldset>
                            <br>
                            <fieldset>
                                <legend>Days List</legend>
                                <table class="table table-bordered">
                                    <thead>
                                        <tr>
                                            <th>Sl No</th>
                                            <th>Holiday</th>
                                            <th>Date</th>
                                            <th>Type</th>
                                            <th>Description</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php
                                        $arr_data = $value['summary'];
                                        ?>
                                         <?php
                                        if (count($arr_data)>0) {
                                            $count = 0;
                                            ?>
                                            <?php foreach ($arr_data as $val) { ?>
                                                <tr> 
                                                    <?php $count = $count + 1; ?>
                                                    <td><?php echo $count; ?></td>
                                                    <td><?php echo $val['holidays']['HOLIDAYNAME']; ?></td>
                                                    <td><?php echo $val['holidays']['HOLIDAYDATE']; ?></td>
                                                    <td><?php echo $val['holidays']['HOLIDAYTYPE']; ?></td>
                                                    <td><?php echo $val['holidays']['DESCRIPTION']; ?></td>
                                                </tr>
                                            <?php } ?>
                                        <?php } else { ?>
                                            <tr>
                                                <td colspan="5">No holidays found under this policy</td>
                                            </tr>  
                                        <?php } ?>
                                    </tbody>
                                </table>
                            </fieldset>
                        </div>
                    <?php } ?> <!-- /.box-body -->
                </div>
            </div>
        </div>    
        <!--    <div class="row">
                <div class="form-group">
                    <div class="col-md-12" align="right">
                        <a href="#" class="btn btn-default" onclick="downloadReport('holiday','pdf');" ><i class="icon-file"></i>Download As PDF</a>
                        <a href="#" class="btn btn-default" onclick="downloadReport('holiday','excel');"><i class="icon-file"></i>Download As Excel</a>
                    </div>
                </div>
            </div>-->
    </div>
<?php } else { ?>
    <?php //echo '<style>'.file_get_contents("css/pdfbootstrap.css").'</style>';    ?>
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
            width: 500px;
            margin: 0 auto; 
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
        }
    </style>
    <?php
echo $this->element('reportadminheader',array('title'=>'Holiday Policy Report'));
    ?>
<!--  <div >-->

 <?php
    $i = 0;
    foreach ($arr_leavepolicydetails_for_template as $value) {
        $i += 1;
        ?>
        <!--<div style="width:100%;">-->
<!--            <div style="width:100%;">-->
               
<!--            </div>-->
           
<!--                        <hr>-->
                                
                    <br>
                     <h4>Holiday Summary Of  <?php echo $value['HoliDayName']; ?> </h4>
                     <h4 style="text-align:center;">  Days List </h4>
            <table class="table" >
							  
					
                    <thead>
                        <tr>
                            <th colspan="5">
                                Days List 
                            </th>
                        </tr>
                    </thead>
            </table>
                <table class="table" >
							  
					
                    <thead>
<!--                        <tr>
                            <th colspan="5">
                                Days List 
                            </th>
                        </tr>-->
                        <tr>
                            <th style="width: 7%;">Sl No</th>
                            <th style="width: 30%;">Holiday</th>
                            <th style="width: 14%;">Date</th>
                            <th style="width: 14%;">Type</th>
                            <th style="width: 30%;">Description</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $arr_data = $value['summary']; ?>
                        <?php
                        if (count($arr_data) > 0) {
                            $count = 0;
                            ?>
                            <?php foreach ($arr_data as $val) { ?>
                                <tr> 
                                    <?php $count = $count + 1; ?>
                                    <td><?php echo $count; ?></td>
                                    <td><?php echo $val['holidays']['HOLIDAYNAME']; ?></td>
                                    <td><?php echo $val['holidays']['HOLIDAYDATE']; ?></td>
                                    <td><?php echo $val['holidays']['HOLIDAYTYPE']; ?></td>
                                    <td><?php echo $val['holidays']['DESCRIPTION']; ?></td>
                                </tr>
                            <?php } ?>
                        <?php } else { ?>
                            <tr>
                                <td colspan="5">No holidays found under this policy</td>
                            </tr>  
                        <?php } ?>
                    </tbody>
                </table>
<!--  <br>-->
           
<!--        </div>-->
    <?php } ?> <!-- /.box-body -->
<!-- </div>-->
<?php } ?>