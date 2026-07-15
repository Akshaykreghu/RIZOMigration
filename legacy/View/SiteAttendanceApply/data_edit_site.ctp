<button class="btn btn-success pull-right" style="margin-right: 37px; " onclick="add_site();">
    <li class="fa fa-plus"></li>
</button>

<div class="col-md-12 clearfix" style="padding-top: 35px; ">
    <div class="">
        <div class="col-md-4 ">
            <?php if (isset($arrResponse['first_att'])) {  ?>
                <?php
                foreach ($arrResponse['first_att'] as $val) {
                ?>
                    <!--  // edited by megha on 11/01/2020 in time next day 3 -->
                    <input type="hidden" id="nextday" value="<?php echo $val['wd']['isnextday']; ?>">
                    <!-- // edited by megha on 30/08/2019 in time next day 1-->
                    <?php if ($val['wd']['isnextday'] == '1') {
                        $in_date = $val['0']['att_date'];  ?>
                        <input type="hidden" id="checkin_date" value="<?php echo date('Y-m-d', strtotime($in_date . ' +1 day')); ?>">
                        <input type="hidden" id="checkin_time" value="<?php echo $val['wd']['on_dutty1']; ?>">
                    <?php } ?>
                    <!-- //end out time next day 1-->
                    <div class="col-md-12">
                        <div class="col-md-12 box box-primary" style="border: white;">

                            <div class="col-md-12">
                                <h5><b style="color : #210ec6;"><?php echo $val['0']['EmpName']; ?></b></h5><span></span>
                            </div>
                            <div class="col-md-9">

                                <div class="pfa ">
                                    <span class="pull-left">
                                        <li class="fa fa-calendar" style=" padding-right: 8px; "></li>
                                        <b style="color : #210ec6;">Date : </b><?php echo $val['0']['att_date']; ?>
                                    </span><br />
                                    <span class="pull-left" style="text-align:left; ">
                                        <li class="fa fa-clock-o" style="    padding-right: 8px; "></li>
                                        <b style="color : #210ec6;">Check In : </b><input type="text" class="datePicker" value="<?php echo isset($val['wd']['on_dutty1']) ? $val['wd']['on_dutty1'] : '00:00:00'; ?>" style="width: 45px;">
                                    </span><br>
                                </div>
                            </div>
                            <div class="col-md-3 pull-left">
                                <button style="    height: 40px;margin-top: 10px;margin-bottom: 10px; " class="btn btn-success" type="button" onclick="in_punch(this,<?php echo $val['sa']['emp_fkey']; ?>);">IN</button>
                            </div>

                        </div>
                    </div>
                <?php
                }
                ?>
            <?php
            } else {
            ?>
                <div>No data found</div>
            <?php
            }
            ?>
        </div>
    </div>
    <div class="">
        <div class="col-md-4 ">
            <?php if (isset($arrResponse['second_att'])) { ?>
                <?php foreach ($arrResponse['second_att'] as $val) { ?>
                    <!-- // edited by megha on 30/08/2019 out time next day 1-->
                    <?php if ($val['wd']['isnextday'] == '1') {
                        $out_date = $val['sa']['att_date'];  ?>
                        <input type="hidden" id="checkout_date" value="<?php echo date('Y-m-d', strtotime($out_date . ' +1 day')); ?>">
                        <input type="hidden" id="checkout_time" value="<?php echo $val['wd']['off_dutty1']; ?>">
                    <?php } ?>
                    <!-- //end out time next day 1-->
                    <?php if ($val['sa']['out_time'] == '') { ?>
                        <div class="col-md-12">
                            <div class="col-md-12 activation box box-primary" style="border: white;">
                                <div class="col-md-12">
                                    <b style="color : #210ec6;"><?php echo $val['0']['EmpName']; ?></b><span></span>
                                </div>
                                <div class="col-md-9 ">

                                    <div class="pfa " style="text-align:left; ">
                                        <span class="pull-left">
                                            <li class="fa fa-calendar" style="padding-right: 8px; "></li> <b style="color : #210ec6;">Date : </b><?php echo $val['sa']['att_date']; ?>
                                        </span><br />
                                        <span class="pull-left"> <?php if ($val['sa']['out_time'] != '') { ?><li style=" padding-right: 8px; " class="fa fa-clock-o"></li> <b style="color : #210ec6;">Duration : </b> <?php echo isset($val['wd']['working_time1']) ? $val['wd']['working_time1'] : 0; ?> min <?php } ?></span>
                                        <span class="pull-left">
                                            <li class="fa fa-clock-o" style="padding-right: 8px; "></li> <b style="color : #210ec6;">Check In : </b><?php echo $val['sa']['in_time']; ?>
                                        </span>
                                        <input type="hidden" value="<?php echo $val['sa']['site_attendance_pkey']; ?>" class="site_pkey">
                                        <span class="pull-left">
                                            <li class="fa fa-clock-o" style="padding-right: 8px; "></li> <b style="color : #210ec6;">Check Out : </b>
                                            <?php
                                            if ($val['sa']['out_time'] == '') {
                                            ?><input type="text" class="datePicker" value="<?php echo isset($val['wd']['off_dutty1']) ? $val['wd']['off_dutty1'] : '00:00:00'; ?>" style="width: 45px;">
                                            <?php
                                            } else {
                                                echo isset($val['sa']['out_time']) ? $val['sa']['out_time'] : '00:00:00';
                                            } ?>
                                        </span>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <?php
                                    if ($val['sa']['out_time'] == '') {
                                    ?>
                                        <button style=" height: 40px;margin-top: 20px;margin-bottom: 20px;  " class="btn btn-warning" type="button" onclick="out_punch(this,<?php echo $val['sa']['emp_fkey']; ?>);">OUT</button>
                                    <?php
                                    }
                                    ?>
                                </div>
                            </div>
                        </div>
                <?php }
                }
                ?>
            <?php
            } else {
            ?>
                <!--                <div class="" >No data Found</div>-->
            <?php
            }
            ?>
        </div>
    </div>
    <div class="">
        <div class="col-md-4 ">
            <?php if (isset($arrResponse['second_att'])) { ?>
                <?php foreach ($arrResponse['second_att'] as $val) { ?>

                    <?php
                        // debug($val);
                    ?>
                    <?php if (($val['sa']['active'] == 1 || $val['sa']['active'] == 0) && $val['sa']['out_time'] != '') {
                        $datetime1 = new DateTime($val['sa']['in_time']);
                        $datetime2 = new DateTime($val['sa']['out_time']);
                        $interval = $datetime1->diff($datetime2);
                        $minutes = $interval->days * 24 * 60;
                        $minutes += $interval->h * 60;
                        $minutes += $interval->i;
                    ?>
                        <?php if ($val['wd']['isnextday'] == '1') {
                            $in_date = $val['sa']['att_date'];  ?>
                            <input type="hidden" id="checkin_edit_date" value="<?php echo date('Y-m-d', strtotime($in_date . ' +1 day')); ?>">
                            <input type="hidden" id="checkin_edit_time" value="<?php echo $val['wd']['on_dutty1']; ?>">
                        <?php } ?>
                        <?php if ($val['wd']['isnextday'] == '1') {
                            $out_date = $val['sa']['att_date'];  ?>
                            <input type="hidden" id="checkout_edit_date" value="<?php echo date('Y-m-d', strtotime($out_date . ' +1 day')); ?>">
                            <input type="hidden" id="checkout_edit_time" value="<?php echo $val['wd']['off_dutty1']; ?>">
                        <?php } ?>
                        <div class="">
                            <div class="col-md-12 activation box box-primary" style="border: white;">
                                <div class="col-md-12">
                                    <b style="color : #210ec6;"><?php echo $val['0']['EmpName']; ?></b><span></span>
                                </div>
                                <div class="col-md-9 ">

                                    <div class="pfa " style="text-align:left; ">
                                        <span class="pull-left">
                                            <li class="fa fa-calendar" style="padding-right: 8px; "></li> <b style="color : #210ec6;">Date : </b><?php echo $val['sa']['att_date']; ?>
                                        </span><br />
                                        <span class="pull-left"> <?php if ($val['sa']['out_time'] != '') { ?><li style=" padding-right: 8px; " class="fa fa-clock-o"></li>
                                                <!--                            <b style="color //echo isset($val['wd']['working_time1'])?$val['wd']['working_time1']:0; ?> min <?php //} 
                                                                                                                                                                                ?></span><br/>-->
                                                <b style="color : #210ec6;">Duration : </b> <?php echo isset($val['sa']['out_time']) ? $minutes : 0; ?> min <?php } ?></span><br />
                                        <span class="pull-left">
                                            <li class="fa fa-clock-o" style="padding-right: 8px; "></li> <b style="color : #210ec6;">Check In : </b>
                                            <?php
                                            if (!$val['sa']['in_time']) {
                                            ?>
                                                <input type="text" class="datePicker inField" value="<?php echo isset($val['wd']['on_dutty1']) ? $val['wd']['on_dutty1'] : '00:00:00'; ?>" style="width: 45px;">
                                                <?php
                                            } else {
                                                if ($val['sa']['active'] == 1) {
                                                ?>
                                                    <input type="text" class="datePicker inField" value="<?php echo isset($val['sa']['in_time']) ? date('H:i:s', strtotime($val['sa']['in_time'])) : '00:00:00'; ?>" style="width: 45px;">
                                            <?php
                                                } else {
                                                    echo isset($val['sa']['in_time']) ? $val['sa']['in_time'] : '00:00:00';
                                                }
                                            }
                                            ?>
                                        </span>
                                        <input type="hidden" value="<?php echo $val['sa']['site_attendance_pkey']; ?>" class="site_pkey"><br />
                                        <span class="pull-left">
                                            <li class="fa fa-clock-o" style="padding-right: 8px;"></li> <b style="color : #210ec6;">Check Out : </b>
                                            <?php
                                            if ($val['sa']['out_time'] == '') {
                                            ?>
                                                <input type="text" class="datePicker outField" value="<?php echo isset($val['wd']['off_dutty1']) ? $val['wd']['off_dutty1'] : '00:00:00'; ?>" style="width: 45px;">
                                                <?php
                                            } else {
                                                if ($val['sa']['active'] == 1) {
                                                ?>
                                                    <input type="text" class="datePicker outField" value="<?php echo isset($val['sa']['out_time']) ? date('H:i:s', strtotime($val['sa']['out_time'])) : '00:00:00'; ?>" style="width: 45px;">
                                            <?php
                                                } else {
                                                    echo isset($val['sa']['out_time']) ? $val['sa']['out_time'] : '00:00:00';
                                                }
                                            }
                                            ?>
                                        </span>
                                    </div>
                                </div>
                                <!-- Activate / Deactivate button for Punch transactions start -->
                                <div class="col-md-3">
                                    <?php if ($val['sa']['active'] == 1 && $val['sa']['out_time'] != '') { ?><button style=" height: 40px;margin-top: 10px;    margin-left: -25px; " class="btn btn-success" type="button" onclick="updateInOut(this,<?php echo $val['sa']['emp_fkey']; ?>);">Update</button><?php } ?>
                                    <?php if ($val['sa']['active'] == 1 && $val['sa']['out_time'] != '') { ?><button style=" height: 40px;margin-top: 10px;    margin-left: -25px; " class="btn btn-warning" type="button" onclick="deactivate(this,<?php echo $val['sa']['emp_fkey']; ?>);">Deactivate</button><?php } ?>
                                </div>
                                <!-- Activate / Deactivate button for Punch transactions end -->
                            </div>
                        </div>
                <?php }
                } ?>
            <?php } else { ?>
                <!--                <div class="" >No data Found</div>-->
            <?php } ?>
        </div>
    </div>
</div>

<script>
    function out_punch(s, emp_fkey) {
        var site = $('#filterby_branch').val();
        var site_attendance_pkey = $(s).parent().siblings(".col-md-9").find(".pfa").find('.site_pkey').val();
        var site_fkey = $('#filterby_branch').val();
        var day_time_seq_fkey = $('#filterby_shift').val();
        var designation_id = $('#designation_code').val();
        var out_time = $(s).parent().siblings(".col-md-9").find(".pfa").find('.datePicker').val();
        var att_date = $('#filterby_date').val();
        // edited by megha on 30/08/2019 out time next day 2
        var att_date1 = $('#checkout_date').val();
        var checkout_time = $('#checkout_time').val();
        $.ajax({
            url: livesite + 'SiteAttendanceApply/mark_attendance/' + emp_fkey,
            method: 'POST',
            // edited by megha on 30/08/2019 out time next day 3
            data: {
                site: site,
                site_attendance_pkey: site_attendance_pkey,
                site_fkey: site_fkey,
                day_time_seq_fkey: day_time_seq_fkey,
                designation_id: designation_id,
                out_time: out_time,
                att_date: att_date,
                out_date: att_date1,
                checkout_time: checkout_time
            },
            success: function(resp) {
                var response = JSON.parse(resp);
                alert(response.message);
                if (response.success == 0) {

                } else {
                    $(s).html('IN').addClass('btn-success').removeClass('btn-warning');
                    load_edit_data();
                    closure_shift();
                }

            }
        });
    }


    function in_punch(s, emp_fkey) {
        var site = $('#filterby_branch').val();
        var site_attendance_pkey = '';
        var site_fkey = $('#filterby_branch').val();
        var day_time_seq_fkey = $('#filterby_shift').val();
        var designation_id = $('#designation_code').val();
        var out_time = $(s).parent().siblings(".col-md-9").find(".pfa").find('.datePicker').val();
        //alert(out_time);
        var att_date = $('#filterby_date').val();
        // edited by megha on 30/08/2019 in time next day 2
        var att_date1 = $('#checkin_date').val();
        // edited by megha on 11/01/2020 in time next day 3
        if ($('#nextday').val() == '0') {
            var checkin_time = out_time;
        } else {
            var checkin_time = $('#checkin_time').val();
        }
        //console.log($('#checkin_time').val());
        $.ajax({
            url: livesite + 'SiteAttendanceApply/mark_attendance/' + emp_fkey,
            method: 'POST',
            data: {
                site: site,
                site_attendance_pkey: site_attendance_pkey,
                site_fkey: site_fkey,
                day_time_seq_fkey: day_time_seq_fkey,
                designation_id: designation_id,
                out_time: out_time,
                att_date: att_date,
                in_date: att_date1,
                checkin_time: checkin_time
            },
            success: function(resp) {
                var response = JSON.parse(resp);
                alert(response.message);
                if (response.success == 0) {

                } else {
                    $(s).html('IN').addClass('btn-success').removeClass('btn-warning');
                    load_edit_data();
                }

            }
        });
    }

    function out_punch_form(s, out_time) {
        var site = $('#filterby_branch').val();
        var site_attendance_pkey = '';
        //        alert($(s).val());
        var site_fkey = $('#filterby_branch').val();
        var day_time_seq_fkey = $('#filterby_shift').val();
        var designation_id = $('#designation_code').val();
        //        var out_time = $(s).parent().siblings(".col-md-8").find(".pfa").find('.datePicker').val();
        //        alert(out_time);
        var att_date = $('#filterby_date').val();
        // edited by megha on 30/08/2019 out time next day 4
        var att_date1 = $('#checkout_date').val();
        var checkout_time = $('#checkout_time').val();
        $.ajax({
            url: livesite + 'SiteAttendanceApply/mark_attendance/' + emp_fkey,
            method: 'POST',
            // edited by megha on 30/08/2019 out time next day 5
            data: {
                site: site,
                site_attendance_pkey: site_attendance_pkey,
                site_fkey: site_fkey,
                day_time_seq_fkey: day_time_seq_fkey,
                designation_id: designation_id,
                out_time: out_time,
                att_date: att_date,
                out_date: att_date1,
                checkout_time: checkout_time
            },
            success: function(resp) {
                var response = JSON.parse(resp);
                alert(response.message);
                if (response.success == 0) {

                } else {
                    $(s).html('IN').addClass('btn-success').removeClass('btn-warning');
                    load_edit_data();
                }

            }
        });
    }

    function deactivate(s, emp_fkey) {
        //alert(emp_fkey);
        var site = $('#filterby_branch').val();
        var site_attendance_pkey = $(s).parent().siblings(".col-md-9").find(".pfa").find('.site_pkey').val();
        var site_fkey = $('#filterby_branch').val();
        var day_time_seq_fkey = $('#filterby_shift').val();
        var designation_id = $('#designation_code').val();
        var out_time = $(s).parent().siblings(".col-md-9").find(".pfa").find('.datePicker').val();
        var att_date = $('#filterby_date').val();
        $.ajax({
            url: livesite + 'SiteAttendanceApply/mark_activestatus/' + emp_fkey,
            method: 'POST',
            data: {
                site: site,
                site_attendance_pkey: site_attendance_pkey,
                site_fkey: site_fkey,
                day_time_seq_fkey: day_time_seq_fkey,
                designation_id: designation_id,
                out_time: out_time,
                att_date: att_date
            },
            success: function(resp) {
                var response = JSON.parse(resp);
                //alert(response.message);
                if (response.success == 0) {

                } else {
                    $.notify(" Punching Disabled Successfully.", {
                        type: 'warning',
                        allow_dismiss: false
                    });
                    // $(s).html('IN').addClass('btn-success').removeClass('btn-warning');

                    $(s).closest(".col-md-12 .activation").css("background-color", "yellow");
                    load_edit_data();
                    closure_shift();
                }

            }
        });
    }

    function activate(s, emp_fkey) {
        //alert(emp_fkey);
        var site = $('#filterby_branch').val();
        var site_attendance_pkey = $(s).parent().siblings(".col-md-9").find(".pfa").find('.site_pkey').val();
        var site_fkey = $('#filterby_branch').val();
        var day_time_seq_fkey = $('#filterby_shift').val();
        var designation_id = $('#designation_code').val();
        var out_time = $(s).parent().siblings(".col-md-9").find(".pfa").find('.datePicker').val();
        var att_date = $('#filterby_date').val();
        $.ajax({
            url: livesite + 'SiteAttendanceApply/mark_deactivestatus/' + emp_fkey,
            method: 'POST',
            data: {
                site: site,
                site_attendance_pkey: site_attendance_pkey,
                site_fkey: site_fkey,
                day_time_seq_fkey: day_time_seq_fkey,
                designation_id: designation_id,
                out_time: out_time,
                att_date: att_date
            },
            success: function(resp) {
                var response = JSON.parse(resp);
                //alert(response.message);
                if (response.success == 0) {

                } else {
                    $.notify(" Punching Enabled Successfully.", {
                        type: 'success',
                        allow_dismiss: false
                    });
                    //$(s).html('IN').addClass('btn-success').removeClass('btn-warning');
                    $(this).parents("div.activation").css("background", "grey");
                    load_edit_data();

                }

            }
        });
    }

    $(document).ready(function() {
        $('.datePicker').each(function(i) {
            $(this).timepicker({
                format: 'hh:mm:ss',
                showMeridian: false,
                autoclose: true
            });
        });

    });


    /// New In And Out punch update starts here by arul 13-03-23

    function updateInOut(s, emp_fkey) {
        // out_all_punch(s, emp_fkey);
        // in_all_punch(s, emp_fkey);

        edit_in_out_fun(s, emp_fkey);

    }

    function edit_in_out_fun(s, emp_fkey) {
        var site = $('#filterby_branch').val();
        var site_attendance_pkey = $(s).parent().siblings(".col-md-9").find(".pfa").find('.site_pkey').val();
        var site_fkey = $('#filterby_branch').val();
        var day_time_seq_fkey = $('#filterby_shift').val();
        var designation_id = $('#designation_code').val();
        var out_time = $(s).parent().siblings(".col-md-9").find(".pfa").find('.outField').val();
        var att_date = $('#filterby_date').val();
        var out_att_date1 = $('#checkout_edit_date').val();
        var checkout_time = $('#checkout_edit_time').val();

        var in_time = $(s).parent().siblings(".col-md-9").find(".pfa").find('.inField').val();
        var in_att_date1 = $('#checkin_edit_date').val();
        if ($('#nextday').val() == '0') {
            var checkin_time = in_time;
        } else {
            var checkin_time = $('#checkin_edit_time').val();
        }
        
        $.ajax({
            url: livesite + 'SiteAttendanceApply/update_attendance/' + emp_fkey,
            method: 'POST',

            data: {
                site: site,
                site_attendance_pkey: site_attendance_pkey,
                site_fkey: site_fkey,
                day_time_seq_fkey: day_time_seq_fkey,
                designation_id: designation_id,
                out_time: out_time,
                att_date: att_date,
                out_date: out_att_date1,
                checkout_time: checkout_time,
                
                in_time: in_time,
                in_date: in_att_date1,
                checkin_time: checkin_time
            },
            success: function(resp) {
                var response = JSON.parse(resp);
                alert(response.message);
                if (response.success == 0) {

                } else {
                    $(s).html('IN').addClass('btn-success').removeClass('btn-warning');
                    load_edit_data();
                    closure_shift();
                }

            }
        });
    }


    function out_all_punch(s, emp_fkey) {
        var site = $('#filterby_branch').val();
        var site_attendance_pkey = $(s).parent().siblings(".col-md-9").find(".pfa").find('.site_pkey').val();
        var site_fkey = $('#filterby_branch').val();
        var day_time_seq_fkey = $('#filterby_shift').val();
        var designation_id = $('#designation_code').val();
        var out_time = $(s).parent().siblings(".col-md-9").find(".pfa").find('.outField').val();
        var att_date = $('#filterby_date').val();
        var att_date1 = $('#checkout_edit_date').val();
        var checkout_time = $('#checkout_edit_time').val();
        $.ajax({
            url: livesite + 'SiteAttendanceApply/mark_attendance/' + emp_fkey,
            method: 'POST',

            data: {
                site: site,
                site_attendance_pkey: site_attendance_pkey,
                site_fkey: site_fkey,
                day_time_seq_fkey: day_time_seq_fkey,
                designation_id: designation_id,
                out_time: out_time,
                att_date: att_date,
                out_date: att_date1,
                checkout_time: checkout_time
            },
            success: function(resp) {
                var response = JSON.parse(resp);
                alert(response.message);
                if (response.success == 0) {

                } else {
                    $(s).html('IN').addClass('btn-success').removeClass('btn-warning');
                    load_edit_data();
                    closure_shift();
                }

            }
        });
    }


    function in_all_punch(s, emp_fkey) {
        var site = $('#filterby_branch').val();
        var site_attendance_pkey = '';
        var site_fkey = $('#filterby_branch').val();
        var day_time_seq_fkey = $('#filterby_shift').val();
        var designation_id = $('#designation_code').val();
        var out_time = $(s).parent().siblings(".col-md-9").find(".pfa").find('.inField').val();
        //alert(out_time);
        var att_date = $('#filterby_date').val();
        // edited by megha on 30/08/2019 in time next day 2
        var att_date1 = $('#checkin_edit_date').val();
        // edited by megha on 11/01/2020 in time next day 3
        if ($('#nextday').val() == '0') {
            var checkin_time = out_time;
        } else {
            var checkin_time = $('#checkin_edit_time').val();
        }
        //console.log($('#checkin_time').val());
        $.ajax({
            url: livesite + 'SiteAttendanceApply/mark_attendance/' + emp_fkey,
            method: 'POST',
            data: {
                site: site,
                site_attendance_pkey: site_attendance_pkey,
                site_fkey: site_fkey,
                day_time_seq_fkey: day_time_seq_fkey,
                designation_id: designation_id,
                out_time: out_time,
                att_date: att_date,
                in_date: att_date1,
                checkin_time: checkin_time
            },
            success: function(resp) {
                var response = JSON.parse(resp);
                alert(response.message);
                if (response.success == 0) {

                } else {
                    $(s).html('IN').addClass('btn-success').removeClass('btn-warning');
                    load_edit_data();
                }

            }
        });
    }
</script>