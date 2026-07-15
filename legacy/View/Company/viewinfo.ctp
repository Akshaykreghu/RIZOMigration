<div class="col-md-4">
    <br>            
    <?php if ($contactinfo['logosize'] == '1') { ?>
        <img style="height: 200px; width: 200px;" src="<?php echo $this->webroot . $contactinfo['logo']; ?>" class="file-preview-image" alt="<?php echo $contactinfo['business_name']; ?>" title="<?php echo $contactinfo['business_name']; ?>">
    <?php } else { ?>
        <img style="height: 100px; width: 200px;" src="<?php echo $this->webroot . $contactinfo['logo']; ?>" class="file-preview-image" alt="<?php echo $contactinfo['business_name']; ?>" title="<?php echo $contactinfo['business_name']; ?>">
    <?php } ?>
    <div>
        <br>
        <div class="row">
            <div class="col-md-1">
                <i class="fa fa-map-marker" style="color: #3c8dbc;" aria-hidden="true"></i>
            </div>
            <div class="col-md-9" style="padding: inherit;">
                <b><?php echo isset($contactinfo['address']) ? $contactinfo['address'] : '' ?>
                    <div>City<span style="padding-left: 31px;">-</span> <?php echo isset($contactinfo['city']) ? $contactinfo['city'] : '' ?></div>
                    <div>State<span style="padding-left: 24px;">-</span>  <?php echo isset($contactinfo['state']) ? $contactinfo['state'] : '' ?></div>
                    <div>Zip Code<span style="padding-left: 4px;">-</span> <?php echo isset($contactinfo['pincode']) ? $contactinfo['pincode'] : '' ?></div>
                </b>
            </div>
        </div>
        <br>
        <p> <i class="fa fa-phone-square" style="color: #3c8dbc;" aria-hidden="true"></i>
            <b> <?php echo isset($contactinfo['phone']) ? $contactinfo['phone'] : '' ?></b>
        </p>
        <p> <i class="fa fa-envelope" style="color: #3c8dbc;" aria-hidden="true"></i>
            <b> <?php echo isset($contactinfo['email']) ? $contactinfo['email'] : '' ?></b>
        </p>
        <p><i class="fa fa-fax" style="color: #3c8dbc;" aria-hidden="true"></i>
            <b> <?php echo isset($contactinfo['fax']) ? $contactinfo['fax'] : '' ?></b>
        </p>
    </div>
</div>

<div class="box-body col-md-8">
    <div class="row">
        <div class="form-group">
            <div class="col-md-12" style="text-align: center;">
                <legend>
                    <h2><b><?php echo isset($contactinfo['business_name']) ? $contactinfo['business_name'] : '' ?>
                        <button type="button" class="btn btn-primary" id="cinfoedit" style="float:right;" onclick="editable();"><i class="fa fa-pencil-square-o" aria-hidden="true"></i></button>
                    </b></h2>
                </legend>
            </div>
        </div><br><br><br><br>
    </div>

    <!-- Existing Rows -->
    <div class="row" style="font-size: 15px; min-height: 48px;">
        <div class="form-group">
            <div class="col-md-6" style="background-color: white;">
                <label class="control-label">Type Of Business</label>
                <span style="padding-left: 22px;">:</span>
                <b style="float: right;"><?php echo isset($contactinfo['business_type']) ? $contactinfo['business_type'] : '' ?></b>
            </div>
            <div class="col-md-6" style="background-color: white;">
                <label class="control-label">Nature Of Business</label>
                <span style="padding-left: 12px;">:</span>
                <b style="float: right;"><?php echo isset($contactinfo['business_nature']) ? $contactinfo['business_nature'] : '' ?></b>
            </div>
        </div>
    </div>

    <div class="row" style="font-size: 15px; min-height: 48px;">         
        <div class="form-group">
            <div class="col-md-6" style="background-color: white;">
                <label class="control-label">CIN No</label>
                <span style="padding-left: 87px;">:</span>
                <b style="float: right;"><?php echo isset($complianceInfo['cinno']) ? $complianceInfo['cinno'] : '' ?></b>
            </div>
            <div class="col-md-6" style="background-color: white;">
                <label class="control-label">PAN No</label>
                <span style="padding-left: 91px;">:</span>
                <b style="float: right;"><?php echo isset($complianceInfo['panno']) ? $complianceInfo['panno'] : '' ?></b>
            </div>
        </div>
    </div>

    <div class="row" style="font-size: 15px; min-height: 48px;">
        <div class="form-group">
            <div class="col-md-6" style="background-color: white;">
                <label class="control-label">TAN No</label>
                <span style="padding-left: 86px;">:</span>
                <b style="float: right;"><?php echo isset($complianceInfo['tanno']) ? $complianceInfo['tanno'] : '' ?></b>
            </div>
            <div class="col-md-6" style="background-color: white;">
                <label class="control-label">Service Tax</label>
                <span style="padding-left: 64px;">:</span>
                <b style="float: right;"><?php echo isset($complianceInfo['servicetax']) ? $complianceInfo['servicetax'] : '' ?></b>
            </div>
        </div>
    </div>

    <div class="row" style="font-size: 15px; min-height: 48px;">
        <div class="form-group">
            <div class="col-md-6" style="background-color: white;">
                <label class="control-label">PF No</label>
                <span style="padding-left: 93px;">:</span>
                <b style="float: right;"><?php echo isset($complianceInfo['pfno']) ? $complianceInfo['pfno'] : '' ?></b>
            </div>
            <div class="col-md-6" style="background-color: white;">
                <label class="control-label">ESI No</label>
                <span style="padding-left: 97px;">:</span>
                <b style="float: right;"><?php echo isset($complianceInfo['empstateinsno']) ? $complianceInfo['empstateinsno'] : '' ?></b>
            </div>
        </div>
    </div>

    <div class="row" style="font-size: 15px; min-height: 48px;">
        <div class="form-group">
            <div class="col-md-6" style="background-color: white;">
                <label class="control-label">Prof Tax No(Co.)</label>
                <span style="padding-left: 25px;">:</span>
                <b style="float: right;"><?php echo isset($complianceInfo['ptnoco']) ? $complianceInfo['ptnoco'] : '' ?></b>
            </div>
            <div class="col-md-6" style="background-color: white;">
                <label class="control-label">Prof Tax No(Dir.)</label>
                <span style="padding-left: 30px;">:</span>
                <b style="float: right;"><?php echo isset($complianceInfo['ptnodir']) ? $complianceInfo['ptnodir'] : '' ?></b>
            </div>
        </div>
    </div>

    <div class="row" style="font-size: 15px; min-height: 48px;">
        <div class="form-group">
            <div class="col-md-6" style="background-color: white;">
                <label class="control-label">Prof Tax No(Emp.)</label>
                <span style="padding-left: 12px;">:</span> 
                <b style="float: right;"><?php echo isset($complianceInfo['ptnoemp']) ? $complianceInfo['ptnoemp'] : '' ?></b>
            </div>
        </div>
    </div>

    <!-- NEW ADDITION: Attendance and Salary Cycle Display -->
    <div class="row" style="font-size: 15px; min-height: 48px;">
        <div class="form-group">
            <div class="col-md-6" style="background-color: white;">
                <label class="control-label">Attendance Cycle</label>
                <span style="padding-left: 12px;">:</span>                    
                <b style="float: right; font-size: 14.5px; text-align: left; max-width: 63%; line-height: 1.2; word-wrap: break-word;">
                    <?php 
                    $att_date = isset($db_config['attendance_date']) ? (int)$db_config['attendance_date'] : 1; 
                    $format = isset($db_config['attendance_format']) ? $db_config['attendance_format'] : 'B';

                    if (!function_exists('get_ordinal_label')) {
                        function get_ordinal_label($num) {
                            if (!in_array(($num % 100), array(11, 12, 13))) {
                                switch ($num % 10) {
                                    case 1:  return $num . 'st';
                                    case 2:  return $num . 'nd';
                                    case 3:  return $num . 'rd';
                                }
                            }
                            return $num . 'th';
                        }
                    }

                    if ($format == 'B' && $att_date == 0) {
                        echo '1 - End date of current month';
                    } else {
                        $start_val = ($att_date == 0) ? 1 : $att_date;
                        if ($start_val == 1) {
                            echo '1 - End date of current month';
                        } else {
                            $end_val = $start_val - 1;
                            echo get_ordinal_label($start_val) . " - " . get_ordinal_label($end_val);
                        }
                    }
                    ?>
                </b>
            </div>
        </div>
    </div>

    <div class="row" style="font-size: 15px; min-height: 48px;">
        <div class="form-group">
            <div class="col-md-6" style="background-color: white;">
                <label class="control-label">Payroll computation day</label>
                <span style="padding-left: 30px;">:</span> 
                <b style="float: right;">
                    <?php 
                    $payroll_type = isset($db_config['payroll_type']) ? strtoupper($db_config['payroll_type']) : 'T'; 
                    if ($payroll_type == 'M') {
                        echo 'Payroll days';
                    } else {
                        echo 'Attendance days';
                    }
                    ?>
                </b>
            </div>
        </div>
    </div>

</div>

<script type="text/javascript">
    function editable() {
        $("#infoid").load(livesite + "Company/infoedit")
    }
</script>
