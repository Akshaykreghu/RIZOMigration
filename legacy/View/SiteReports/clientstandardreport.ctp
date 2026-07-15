<?php if ($mode == '') { ?>
    <!-- <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.1/css/bootstrap.min.css"> -->
    <style type="text/css">
        .page{
            display: none;
        }
        #page1, #page2, #page3, #page4, #page5, #page6, #page7, #page8, #page9, #page10{
            display: block;
        }
        /*#list1{
        background-color: #007CB7;
        color: white;
        }*/
        #list_new{
            background-color: #007CB7;
            color: white;
        }
        #list_new2 , #list_new3 , #list_new4{
            background-color: white;
            color: black;
        }
        .pagination {
            display: inline-block;
        }

        .pagination a {
            color: black;
            float: left;
            padding: 8px 16px;
            text-decoration: none;
            transition: background-color .3s;
        }

        .pagination a.active {
            background-color: #4CAF50;
            color: white;
        }

    </style>
    <script type="text/javascript">
        $(document).ready(function () {
            $('#hidden_pointer').val(1);
            var totalNumberOfPages = <?php echo count($arr_clientreport_res); ?>;
    <?php
    $start = (count($arr_clientreport_res) / 10);
    ?>
            var start =<?php echo ceil($start); ?>;
            $('#current_id').val(1);
            if (start > 4) {
                for (var i = 2; i <= 4; i++) {
                    $('#current_id' + i).val(i);
                }
            } else {
                if (start <= 2) {
                    $('#current_id2').val(2);
                    $('#current_id3').css("display", "none");
                    $('#current_id4').css("display", "none");
                    $('#list_new3').css("display", "none");
                    $('#list_new4').css("display", "none");
                }
                if (start == 3) {
                    $('#current_id2').val(2);
                    $('#current_id3').val(3);
                    $('#list_new4').css("display", "none");
                    $('#current_id4').css("display", "none");
                }
                if (start == 4) {
                    $('#current_id2').val(2);
                    $('#current_id3').val(3);
                    $('#current_id4').val(4);
                }
                if (start < 2) {
                    $('#current_id2').css("display", "none");
                    $('#current_id3').css("display", "none");
                    $('#current_id4').css("display", "none");
                    $('#list_new2').css("display", "none");
                    $('#list_new3').css("display", "none");
                    $('#list_new4').css("display", "none");
                }
            }
            // alert(totalNumberOfPages+' '+start);
        });
        function showPages(id) {
            // alert(id);
            if (id != 'prev' && id != 'next') {
                // alert(id);
                var totalNumberOfPages = <?php echo count($arr_clientreport_res); ?>;
                var end = id * 10;
                var start = (id * 10) - 9;
                for (var i = 1; i <= totalNumberOfPages; i++) {
                    if ($('#page' + i)) {
                        $('#page' + i).css("display", "none");
                        // $('#list'+i).css("background-color","white");
                        // $('#list'+i).css("color","black");
                    }
                }
                for (var i = start; i <= end; i++) {
                    if ($('#page' + i)) {
                        $('#page' + i).css("display", "block");
                    }
                }
                $('#hidden_pointer').val(id);
                // $('#list'+id).css("background-color","#007CB7");
                // $('#list'+id).css("color","white");

                if ((totalNumberOfPages / 10) > 4) {
                    if ($('#current_id').val() == $('#hidden_pointer').val() && parseInt($('#current_id').val()) >= 2) {
                        $('#current_id').val(parseInt($('#current_id').val()) - 1);
                        $('#current_id2').val(parseInt($('#current_id2').val()) - 1);
                        $('#current_id3').val(parseInt($('#current_id3').val()) - 1);
                        $('#current_id4').val(parseInt($('#current_id4').val()) - 1);
                    } else if ($('#current_id4').val() == $('#hidden_pointer').val() && parseInt($('#current_id4').val()) <= (<?php echo count($arr_clientreport_res) / 10; ?>)) {
                        // alert($('#current_id4').val()+' '+$('#hidden_pointer').val());
                        $('#current_id').val(parseInt($('#current_id').val()) + 1);
                        $('#current_id2').val(parseInt($('#current_id2').val()) + 1);
                        $('#current_id3').val(parseInt($('#current_id3').val()) + 1);
                        $('#current_id4').val(parseInt($('#current_id4').val()) + 1);
                    }
                }


                if (id == $('#current_id').val()) {
                    $('#list_new').css("background-color", "#007CB7");
                    $('#list_new').css("color", "white");
                    $('#list_new2').css("background-color", "white");
                    $('#list_new2').css("color", "black");
                    $('#list_new3').css("background-color", "white");
                    $('#list_new3').css("color", "black");
                    $('#list_new4').css("background-color", "white");
                    $('#list_new4').css("color", "black");
                } else if (id == $('#current_id2').val()) {
                    $('#list_new').css("background-color", "white");
                    $('#list_new').css("color", "black");
                    $('#list_new2').css("background-color", "#007CB7");
                    $('#list_new2').css("color", "white");
                    $('#list_new3').css("background-color", "white");
                    $('#list_new3').css("color", "black");
                    $('#list_new4').css("background-color", "white");
                    $('#list_new4').css("color", "black");
                } else if (id == $('#current_id3').val()) {
                    $('#list_new').css("background-color", "white");
                    $('#list_new').css("color", "black");
                    $('#list_new2').css("background-color", "white");
                    $('#list_new2').css("color", "black");
                    $('#list_new3').css("background-color", "#007CB7");
                    $('#list_new3').css("color", "white");
                    $('#list_new4').css("background-color", "white");
                    $('#list_new4').css("color", "black");
                } else if (id == $('#current_id4').val()) {
                    $('#list_new').css("background-color", "white");
                    $('#list_new').css("color", "black");
                    $('#list_new2').css("background-color", "white");
                    $('#list_new2').css("color", "black");
                    $('#list_new3').css("background-color", "white");
                    $('#list_new3').css("color", "black");
                    $('#list_new4').css("background-color", "#007CB7");
                    $('#list_new4').css("color", "white");
                }


            } else {
                var totalNumberOfPages = <?php echo count($arr_clientreport_res); ?>;
                var start = parseInt($('#hidden_pointer').val());
                var temp = start;
                // alert(start);
                if (id == 'prev') {
                    if (start >= 2) {
                        id = start - 1;
                        var end = id * 10;
                        var start = (id * 10) - 9;
                        for (var i = 1; i <= totalNumberOfPages; i++) {
                            if ($('#page' + i)) {
                                $('#page' + i).css("display", "none");
                                $('#list' + i).css("background-color", "white");
                                $('#list' + i).css("color", "black");
                            }
                        }
                        for (var i = start; i <= end; i++) {
                            if ($('#page' + i)) {
                                $('#page' + i).css("display", "block");
                            }
                        }
                        temp = parseInt(temp - 1);
                        $('#hidden_pointer').val(temp);
                        $('#list' + temp).css("background-color", "#007CB7");
                        $('#list' + temp).css("color", "white");
                        if ($('#current_id').val() >= 2) {
                            $('#current_id').val(parseInt($('#current_id').val()) - 1);
                            $('#current_id2').val(parseInt($('#current_id2').val()) - 1);
                            $('#current_id3').val(parseInt($('#current_id3').val()) - 1);
                            $('#current_id4').val(parseInt($('#current_id4').val()) - 1);
                        }
                        if (id == $('#current_id').val()) {
                            $('#list_new').css("background-color", "#007CB7");
                            $('#list_new').css("color", "white");
                            $('#list_new2').css("background-color", "white");
                            $('#list_new2').css("color", "black");
                            $('#list_new3').css("background-color", "white");
                            $('#list_new3').css("color", "black");
                            $('#list_new4').css("background-color", "white");
                            $('#list_new4').css("color", "black");
                        } else if (id == $('#current_id2').val()) {
                            $('#list_new').css("background-color", "white");
                            $('#list_new').css("color", "black");
                            $('#list_new2').css("background-color", "#007CB7");
                            $('#list_new2').css("color", "white");
                            $('#list_new3').css("background-color", "white");
                            $('#list_new3').css("color", "black");
                            $('#list_new4').css("background-color", "white");
                            $('#list_new4').css("color", "black");
                        } else if (id == $('#current_id3').val()) {
                            $('#list_new').css("background-color", "white");
                            $('#list_new').css("color", "black");
                            $('#list_new2').css("background-color", "white");
                            $('#list_new2').css("color", "black");
                            $('#list_new3').css("background-color", "#007CB7");
                            $('#list_new3').css("color", "white");
                            $('#list_new4').css("background-color", "white");
                            $('#list_new4').css("color", "black");
                        } else if (id == $('#current_id4').val()) {
                            $('#list_new').css("background-color", "white");
                            $('#list_new').css("color", "black");
                            $('#list_new2').css("background-color", "white");
                            $('#list_new2').css("color", "black");
                            $('#list_new3').css("background-color", "white");
                            $('#list_new3').css("color", "black");
                            $('#list_new4').css("background-color", "#007CB7");
                            $('#list_new4').css("color", "white");
                        }
                        // alert($('#hidden_pointer').val());
                    }
                } else {
                    // count_val=<?php echo (float) (count($arr_clientreport_res) / 10); ?>;
                    // last=<?php echo (count($arr_clientreport_res) / 10); ?>;
                    // if(count_val><?php echo (int) (count($arr_clientreport_res) / 10); ?>){
                    //     last=<?php echo (count($arr_clientreport_res) / 10) + 1; ?>;
                    // }else{
                    //     last=<?php echo (count($arr_clientreport_res) / 10); ?>;
                    // }
                    last =<?php echo ceil(count($arr_clientreport_res) / 10); ?>;
                    // alert(last);
                    if (start <= last - 1) {
                        // alert(start);
                        id = start + 1;
                        var end = id * 10;
                        var start = (id * 10) - 9;
                        for (var i = 1; i <= totalNumberOfPages; i++) {
                            if ($('#page' + i)) {
                                $('#page' + i).css("display", "none");
                                $('#list' + i).css("background-color", "white");
                                $('#list' + i).css("color", "black");
                            }
                        }
                        for (var i = start; i <= end; i++) {
                            if ($('#page' + i)) {
                                $('#page' + i).css("display", "block");
                            }
                        }
                        temp = parseInt(temp) + 1;
                        $('#hidden_pointer').val(temp);
                        $('#list' + temp).css("background-color", "#007CB7");
                        $('#list' + temp).css("color", "white");
                        if (parseInt($('#current_id4').val()) <= last - 1) {
                            $('#current_id').val(parseInt($('#current_id').val()) + 1);
                            $('#current_id2').val(parseInt($('#current_id2').val()) + 1);
                            $('#current_id3').val(parseInt($('#current_id3').val()) + 1);
                            $('#current_id4').val(parseInt($('#current_id4').val()) + 1);
                        }
                        if (id == $('#current_id').val()) {
                            $('#list_new').css("background-color", "#007CB7");
                            $('#list_new').css("color", "white");
                            $('#list_new2').css("background-color", "white");
                            $('#list_new2').css("color", "black");
                            $('#list_new3').css("background-color", "white");
                            $('#list_new3').css("color", "black");
                            $('#list_new4').css("background-color", "white");
                            $('#list_new4').css("color", "black");
                        } else if (id == $('#current_id2').val()) {
                            $('#list_new').css("background-color", "white");
                            $('#list_new').css("color", "black");
                            $('#list_new2').css("background-color", "#007CB7");
                            $('#list_new2').css("color", "white");
                            $('#list_new3').css("background-color", "white");
                            $('#list_new3').css("color", "black");
                            $('#list_new4').css("background-color", "white");
                            $('#list_new4').css("color", "black");
                        } else if (id == $('#current_id3').val()) {
                            $('#list_new').css("background-color", "white");
                            $('#list_new').css("color", "black");
                            $('#list_new2').css("background-color", "white");
                            $('#list_new2').css("color", "black");
                            $('#list_new3').css("background-color", "#007CB7");
                            $('#list_new3').css("color", "white");
                            $('#list_new4').css("background-color", "white");
                            $('#list_new4').css("color", "black");
                        } else if (id == $('#current_id4').val()) {
                            $('#list_new').css("background-color", "white");
                            $('#list_new').css("color", "black");
                            $('#list_new2').css("background-color", "white");
                            $('#list_new2').css("color", "black");
                            $('#list_new3').css("background-color", "white");
                            $('#list_new3').css("color", "black");
                            $('#list_new4').css("background-color", "#007CB7");
                            $('#list_new4').css("color", "white");
                        }
                    }
                    // alert($('#hidden_pointer').val());
                }
            }
            // window.location = "#page"+start;
        }
    </script>
    <div class="modal-body">
        <?php //debug($arr_clientreport_res);?>
        <?php
        if (!empty($arr_clientreport_res)) {
            $month_num = date('m', strtotime($month));
            $year = date('Y', strtotime($month));
            // echo $month_num;
            $monthName = date('F', mktime(0, 0, 0, $month_num, 10)); // March
            if (isset($criteria)) {
                if ($criteria == 'Contacts') {
                    $msg = 'Belonging to a Client';
                } else {
                    $msg = 'Belonging to a ' . $criteria;
                }
            }
            ?>
            <h2 style="text-align:center;">Site Detailed Report (Standard Rate) : <?php echo $year . '-' . $monthName; ?></h2>
            <h3 style="text-align:center;">(<?php echo $msg; ?> Report run by <?php echo $user_name; ?> - <?php echo $date_time; ?>)</h3>
            <div class="">
                <div>
                        <?php
                        $grant_total_wages = 0;
                        $j = 1;
                        foreach ($arr_clientreport_res as $each) {
                            ?>
                        <div class="page" id="page<?php echo $j; ?>">
                            <?php
                            $j++;
                            $items = count($each);
                            $billing_value = array();
                            $total_wages = 0;
                            $client = '';
                            $total_number = 0;
                            $i = 1;


                            $total_value = 0;
                            $total_wages_per_person = 0;
                            $total_wages_total = 0;
                            $markup_total = 0;
                            $markup_total_percentage = 0;
                            foreach ($each as $value) {
                                $number = $value['site_transactions']['emp_count'];
                                $shift_hours = $value[0]['shift_hours'];
                                $shift_days = $value[0]['days'];
                                // $rate_per_head=round($value[0]['RATEPERHead']);
                                $sales_rate = $value['site_transactions']['srate'];
                                $rate_per_head = $sales_rate * $shift_hours * $shift_days;
                                if ($rate_per_head != 0) {
                                    $rate_per_hour = ($rate_per_head / $shift_days) / $shift_hours;
                                } else {
                                    $rate_per_hour = 0;
                                }
                                $res_value = $rate_per_hour * $shift_days * $shift_hours * $number;
                                 // Edited by Akshay on 21-8-2025
                                if (isset($value['site_transactions']['eratess']) && $value['site_transactions']['eratess'] !== null && $value['site_transactions']['eratess'] !== '') {
                                    $erate = $value['site_transactions']['eratess'];
                                } elseif (isset($value[0]['eratess']) && $value[0]['eratess'] !== null && $value[0]['eratess'] !== '') {
                                    $erate = $value[0]['eratess'];
                                } else {
                                    $erate = null; // or some default value
                                }
                                // End
                                $wages = ($erate * $shift_hours * $shift_days);
                                $total_wages = $wages * $number;
                                $markup = $res_value - $total_wages;
                                if ($total_wages != 0) {
                                    $markup_percentage = (($res_value - $total_wages) / $total_wages) * 100;
                                } else {
                                    $markup_percentage = 0;
                                }
                                $grant_total_wages = $grant_total_wages + $total_wages;


                                $total_value = round($total_value) + round($res_value);
                                $total_wages_per_person = round($total_wages_per_person) + round($wages);
                                $total_wages_total = round($total_wages_total) + round($total_wages);

                                $end_date = $value['site_transactions']['end_date_effective'];
                                $today = date('Y-m-d');
                                $closed_site = '';
                                if ($end_date < $today) {
                                    $closed_site = ' (Closed Site)';
                                }
                                if ($client != $value['contacts']['company_name']) {
                                    ?>
                                    <table class="table table-striped table-bordered table-sm">
                                        <tr>
                                            <th colspan="4" style="font-size: 20px;text-align: center;"><b>Details of <?php echo $value['contacts']['company_name']; ?></b></th>
                                        </tr>
                                        <tr>
                                            <td colspan="4"><b>Client Address</b> : <?php echo $value['contacts']['address']; ?></td>
                                        </tr>
                                        <tr>
                                            <td><b>Contact Person</b> : <?php echo $value['contacts']['first_name'] . ' ' . $value['contacts']['middle_name'] . ' ' . $value['contacts']['last_name']; ?></td>
                                            <td><b>Contact Person Designation</b> : <?php echo $value['contacts']['c_designation']; ?></td>
                                            <td><b>Mobile Number</b>  : <?php echo $value['contacts']['phone']; ?></td>
                                            <td><b>Mail ID</b> : <?php echo $value['contacts']['email']; ?></td>
                                        </tr>
                                    </table>
                                    <div style="overflow-y: auto;">
                                        <table class="table table-bordered datatable">
                                            <thead>
                                                <tr>
                                                    <th>Sl No</th>
                                                    <th>Site Code</th>
                                                    <th>Site Name</th>
                                                    <th>Site Address</th>
                                                    <th>Segment</th>
                                                    <th>Customer Name</th>
                                                    <th>Customer Contact Number</th>
                                                    <th>Contract Start Date</th>
                                                    <th>Contract End Date</th>
                                                    <th>Last Modified Date</th>
                                                    <th>Shift Policy</th>
                                                    <th>Designation</th>
                                                    <th>Number of Employees</th>
                                                    <th>Shift Hours</th>
                                                    <th>Days</th>
                                                    <th>Sales Rate</th>
                                                    <th>Sales Rate Per Head</th>
                                                    <th>Expense Rate</th>
                                                    <th>Value</th>
                                                    <th>Wages Per Person</th>
                                                    <th>Total Wages</th>
                                                    <th>Mark Up</th>
                                                    <th>Mark Up %</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                    <?php
                }//This is to test contact person repeats
                $client = $value['contacts']['company_name'];
                ?>
                                            <tr>
                                                <td><?php echo $i; ?></td>
                                                <td><?php echo $value['site']['site_id']; ?></td>
                                                <td><?php echo $value['site']['site_name'] . $closed_site; ?></td>
                                                <td><?php echo $value['site']['address']; ?></td>
                                                <td><?php echo $value['site']['special_remarks']; ?></td>
                                                <td><?php echo $value['site']['customer_name']; ?></td>
                                                <td><?php echo $value['site']['customer_contact']; ?></td>
                                                <td><?php echo $value['site_transactions']['start_date_effective']; ?></td>
                                                <td><?php echo $value['site_transactions']['end_date_effective']; ?></td>
                                                <td><?php echo $value[0]['modified_date']; ?></td>
                                                <td><?php echo $value['working_day_time_procedures']['day_time_desc']; ?></td>
                                                <td><?php echo $value['designation']['desig_name']; ?></td>
                                                <td><?php echo $value['site_transactions']['emp_count']; ?></td>
                                                <td><?php echo round($value[0]['shift_hours'], 2); ?></td>
                                                <td><?php echo $value[0]['days']; ?></td>
                                                <td><?php echo round($rate_per_hour, 2); ?></td>
                                                <td><?php echo round($rate_per_head, 2); ?></td>
                                                <td><?php echo round($erate, 2); ?></td>
                                                <td><?php echo round($res_value); ?></td>
                                                <td><?php echo round($wages); ?></td>
                                                <td><?php echo round($total_wages); ?></td>
                                                <td><?php echo round($markup); ?></td>
                                                <td><?php echo round($markup_percentage); ?></td>
                                            </tr>
                <?php
                $i++;
            }//This is the closing of foreach $value
            ?>
                                    </tbody>
                                    <tfoot>
                                        <tr>
                                            <th colspan="18" style="text-align: right;">Grand Total</th>
                                            <th><?php echo round($total_value); ?></th>
                                            <th><?php echo round($total_wages_per_person); ?></th>
                                            <th><?php echo round($total_wages_total); ?></th>
                                            <th>
                                                <?php
                                                $markup_total = round($total_value) - round($total_wages_total);
                                                echo round($markup_total);
                                                ?>
                                            </th>
                                            <th>
            <?php
            if($total_wages_total !=0){
            $markup_total_percentage = ((round($total_value) - round($total_wages_total)) / round($total_wages_total)) * 100;
            }else{
                $markup_total_percentage = 0;
            }
            echo round($markup_total_percentage);
            ?>
                                            </th>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                            <br><br>
                        </div>
            <?php
        }//This is the closing of foreach $each
        ?>
                    <!-- <table class="table" style="/*background-color: gray*/">
                        <tr>
                            <th style="width: 80%"><h2>Grand Total</h2></th>
                            <th><h2><?php echo round($grant_total_wages); ?></h2></th>
                        </tr>
                    </table> -->
                </div>
                <input type="hidden" name="hidden_pointer" id="hidden_pointer">
                <ul id="navigation_menu" class="pagination pull-right" style="padding: 0px;">
                    <?php
                    if (count($arr_clientreport_res) > 10) {
                        // $count_val=(float)(count($arr_clientreport_res)/10);
                        // $start=(count($arr_clientreport_res)/10);
                        // if($count_val>(int)(count($arr_clientreport_res)/10)){
                        //     $start=(count($arr_clientreport_res)/10)+1;
                        // }else{
                        //     $start=(count($arr_clientreport_res)/10);
                        // }
                        $start = ceil(count($arr_clientreport_res) / 10);
                        ?>
                        <!-- &laquo; &raquo;-->
                        <!-- <li><a href="#" id="list1" onclick="showPages(1)">First</a></li> -->
                        <li><a href="#" onclick="showPages('prev')">Previous</a></li>
                        <!-- <li><a href="#">...</a></li> -->
                        <!-- <?php
            for ($j = 1; $j <= $start; $j++) {
                ?>
                                <li><a href="#" id="list<?php echo $j; ?>" onclick="showPages('<?php echo $j; ?>')"><?php echo $j; ?></a></li>
                <?php
            }
            ?> -->
                        <li><a href="#" style="/*background-color: white;*/" id="list_new" onclick="showPages($('#current_id').val())"><input type="text" id="current_id" disabled style="display: inline; width: 14px;background-color: transparent;height: 10px;border: 0px;"></a></li>
                        <li><a href="#" style="/*background-color: white;*/" id="list_new2" onclick="showPages($('#current_id2').val())"><input type="text" id="current_id2" disabled style="display: inline; width: 14px;background-color: transparent;height: 10px;border: 0px;"></a></li>
                        <li><a href="#" style="/*background-color: white;*/" id="list_new3" onclick="showPages($('#current_id3').val())"><input type="text" id="current_id3" disabled style="display: inline; width: 14px;background-color: transparent;height: 10px;border: 0px;"></a></li>
                        <li><a href="#" style="/*background-color: white;*/" id="list_new4" onclick="showPages($('#current_id4').val())"><input type="text" id="current_id4" disabled style="display: inline; width: 14px;background-color: transparent;height: 10px;border: 0px;"></a></li>
                        <!-- <li><a href="#">...</a></li> -->
                        <li><a href="#" onclick="showPages('next')">Next</a></li>
                        <!-- <li><a href="#" id="list<?php echo $start; ?>" onclick="showPages(<?php echo (int) $start; ?>)">Last</a></li> -->
                <?php
            }
            ?>
                </ul>
            </div>
        <?php
    } else {
        echo isset($no_criteria) ? "<h2>" . $no_criteria . "</h2>" : '<h2>There is no data</h2>';
    }
    ?>
    </div>
<?php } else { ?>
    <?php //echo '<style>'.file_get_contents("css/pdfbootstrap.css").'</style>';  ?>
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
            padding: 5px;
            line-height: 1.42857143;
            vertical-align: top;
            border: 1px solid #B2B2B2;
        }
    </style>
    <?php
    $month_num = date('m', strtotime($month));
    $year = date('Y', strtotime($month));
    // echo $month_num;
    $monthName = date('F', mktime(0, 0, 0, $month_num, 10)); // March
    if (isset($criteria)) {
        if ($criteria == 'Contacts') {
            $msg = 'Belonging to a Client';
        } else {
            $msg = 'Belonging to a ' . $criteria;
        }
    }
    echo $this->element('reportadminheader', array(
        'title' => 'Site Detailed Report (Standard Rate) : ' . $year . '-' . $monthName . '<br />'));
    ?>
    <?php
    if (!empty($arr_clientreport_res_pdf)) {
        $month_num = date('m', strtotime($month));
        $year = date('Y', strtotime($month));
        // echo $month_num;
        $monthName = date('F', mktime(0, 0, 0, $month_num, 10)); // March
        ?>
        <br>
        <!--         <br><br>-->
        <!--        <h2 style="text-align:center;">Site Detailed Report (Standard Rate) : <?php echo $year . '-' . $monthName; ?></h2>-->
        <h3 style="text-align:center;">(<?php echo $msg; ?> Report run by <?php echo $user_name; ?> - <?php echo $date_time; ?>)</h3>
        <!--        <div class="row">-->
        <!--            <div>-->
        <?php
        $grant_total_wages = 0;
        foreach ($arr_clientreport_res_pdf as $each) {
            // debug($each);
            $items = count($each);
            $billing_value = array();
            $total_wages = 0;
            $client = '';
            $total_number = 0;
            $i = 1;


            $total_value = 0;
            $total_wages_per_person = 0;
            $total_wages_total = 0;
            $markup_total = 0;
            $markup_total_percentage = 0;
            foreach ($each as $value) {
                $number = $value['site_transactions']['emp_count'];
                $shift_hours = $value[0]['shift_hours'];
                $shift_days = $value[0]['days'];
                // $rate_per_head=round($value[0]['RATEPERHead']);
                $sales_rate = $value['site_transactions']['srate'];
                $rate_per_head = $sales_rate * $shift_hours * $shift_days;
                if ($rate_per_head != 0) {
                    $rate_per_hour = ($rate_per_head / $shift_days) / $shift_hours;
                } else {
                    $rate_per_hour = 0;
                }
                $res_value = $rate_per_hour * $shift_days * $shift_hours * $number;
                 // Edited by Akshay on 21-8-2025
                if (isset($value['site_transactions']['eratess']) && $value['site_transactions']['eratess'] !== null && $value['site_transactions']['eratess'] !== '') {
                    $erate = $value['site_transactions']['eratess'];
                } elseif (isset($value[0]['eratess']) && $value[0]['eratess'] !== null && $value[0]['eratess'] !== '') {
                    $erate = $value[0]['eratess'];
                } else {
                    $erate = null; // or some default value
                }
                // End
                $wages = ($erate * $shift_hours * $shift_days);
                $total_wages = $wages * $number;
                $markup = $res_value - $total_wages;
                if ($total_wages != 0) {
                    $markup_percentage = (($res_value - $total_wages) / $total_wages) * 100;
                } else {
                    $markup_percentage = 0;
                }
                $grant_total_wages = $grant_total_wages + $total_wages;


                $total_value = round($total_value) + round($res_value);
                $total_wages_per_person = round($total_wages_per_person) + round($wages);
                $total_wages_total = round($total_wages_total) + round($total_wages);

                $end_date = $value['site_transactions']['end_date_effective'];
                $today = date('Y-m-d');
                $closed_site = '';
                if ($end_date < $today) {
                    $closed_site = ' (Closed Site)';
                }
                if ($client != $value['contacts']['company_name']) {
                    ?>
                    <div style="display: block; page-break-before:always;page-break-after:always; clear: both;">
                        <!--                          <div  >-->
                        <table >
                    <!--                                    <thead>-->
                            <tr>
                                <th colspan="18" style="text-align: center;font-size: 20px; border:0px;"><b>Details of <?php echo $value['contacts']['company_name']; ?></b></th>
                            </tr>
                            <tr>
                                <td colspan="18" style="border:0px;"><b>Client Address</b> : <?php echo $value['contacts']['address']; ?></td>
                            </tr>
                            <tr>
                                <td colspan="3" style="border:0px;"><b>Contact Person</b> : <?php echo $value['contacts']['first_name'] . ' ' . $value['contacts']['middle_name'] . ' ' . $value['contacts']['last_name']; ?></td>
                                <td colspan="5" style="border:0px;"><b>Contact Person Designation</b> : <?php echo $value['contacts']['c_designation']; ?></td>
                                <td colspan="4" style="border:0px;"><b>Mobile Number</b>  : <?php echo $value['contacts']['phone']; ?></td>
                                <td colspan="6" style="border:0px;"><b>Mail ID</b> : <?php echo $value['contacts']['email']; ?></td>
                            </tr>
                            <tr>
                                <th colspan="18" style="border-left:0px;border-right:0px;">&nbsp;</th>
                            </tr>
                            <tr>
                                <th>Sl<br>No</th>
                                <th>Site<br>Details</th>
                                    <!-- <th>Site<br>Name</th>
                                        <th>Site<br>Address</th> -->
                                        <!-- <th>Segment</th> -->
                                        <!-- <th>Customer<br>Name</th> -->
                                <th>Customer<br>Details</th>
                                <th>Contract<br>Start<br>Date</th>
                                <th>Contract<br>End<br>Date</th>
                                <!-- <th>Last<br>Modified<br>Date</th> -->
                                <th>Shift<br>Policy</th>
                                <th>Designation</th>
                                <th>Number<br>of<br>Employees</th>
                                <th>Shift<br>Hours</th>
                                <th>Days</th>
                                <th>Sales<br>Rate</th>
                                <th>Sales<br>Rate<br>Per<br>Head</th>
                                <th>Expense<br>Rate</th>
                                <th>Value</th>
                                <th>Wages<br>Per<br>Person</th>
                                <th>Total<br>Wages</th>
                                <th>Mark<br>Up</th>
                                <th>Mark<br>Up %</th>
                            </tr>
                            <!--                                            </thead>
                                                                        <tbody>-->
                    <?php
                }//This is to test contact person repeats
                $client = $value['contacts']['company_name'];
                ?>
                        <tr>
                            <td style="width: 3px;"><?php echo $i; ?></td>
                            <!-- <td style="width: 20px;"><?php echo $value['site']['site_id']; ?></td> -->
                            <td style="width: 250px;"><?php echo $value['site']['site_id'] . '-- ' . $value['site']['site_name'] . '-- ' . $value['site']['address'].$closed_site; ?></td>
                            <!-- <td style="width: 90px;"><?php echo $value['site']['address']; ?></td> -->
                            <!-- <td style="width: 10px;"><?php echo $value['site']['special_remarks']; ?></td> -->
                            <td style="width: 150px;"><?php echo $value['site']['customer_name'] . '-- ' . $value['site']['customer_contact']; ?></td>
                            <!-- <td style="width: 10px;"><?php echo $value['site']['customer_contact']; ?></td> -->
                            <td style="width: 55px;"><?php echo $value['site_transactions']['start_date_effective']; ?></td>
                            <td style="width: 55px;"><?php echo $value['site_transactions']['end_date_effective']; ?></td>
                            <!-- <td style="width: 55px;"><?php echo $value[0]['modified_date']; ?></td> -->
                            <td style="width: 60px;"><?php echo $value['working_day_time_procedures']['day_time_desc']; ?></td>
                            <td style="width: 10px;"><?php echo $value['designation']['desig_name']; ?></td>
                            <td style="width: 10px;"><?php echo $value['site_transactions']['emp_count']; ?></td>
                            <td style="width: 10px;"><?php echo round($value[0]['shift_hours'], 2); ?></td>
                            <td style="width: 10px;"><?php echo $value[0]['days']; ?></td>
                            <td style="width: 30px;"><?php echo round($rate_per_hour, 2); ?></td>
                            <td style="width: 60px;"><?php echo round($rate_per_head, 2); ?></td>
                            <td style="width: 10px;"><?php echo round($erate, 2); ?></td>
                            <td style="width: 30px;"><?php echo round($res_value); ?></td>
                            <td style="width: 10px;"><?php echo round($wages); ?></td>
                            <td style="width: 30px;"><?php echo round($total_wages); ?></td>
                            <td style="width: 10px;"><?php echo round($markup); ?></td>
                            <td style="width: 10px;"><?php echo round($markup_percentage); ?></td>
                        </tr>
                <?php
                $i++;
            }
            ?>
                    <!--                        </tbody>-->
                    <!--                        <tfoot>-->
                    <tr>
                        <th colspan="13" style="text-align: right;">Grand Total</th>
                        <th><?php echo round($total_value); ?></th>
                        <th><?php echo round($total_wages_per_person); ?></th>
                        <th><?php echo round($total_wages_total); ?></th>
                        <th>
            <?php
            $markup_total = round($total_value) - round($total_wages_total);
            echo round($markup_total);
            ?> 
                        </th>
                        <th>
            <?php
             // Edited by Akshay on 25-8-2025
                            if ($total_wages_total != 0) {
                                $markup_total_percentage = ((round($total_value) - round($total_wages_total)) / round($total_wages_total)) * 100;
                            } else {
                                $markup_total_percentage = 0; // or NULL, depending on your requirement
                            }
                            // End
            echo round($markup_total_percentage);
            ?>
                        </th>
                    </tr>
                    <!--                        </tfoot>-->
                </table>
            </div>
            <!--                <br><br>-->
            <?php
        }
        ?>
                        <!-- <table class="table" style="/*background-color: gray*/">
                            <tr>
                                <th style="width: 80%"><h2>Grand Total</h2></th>
                                <th><h2><?php echo round($grant_total_wages); ?></h2></th>
                            </tr>
                        </table> -->
        <!--            </div>-->
        <!--        </div>-->
        <?php
    } else {
        echo isset($no_criteria) ? "<h2>" . $no_criteria . "</h2>" : '<div style="font-size: 25px;margin-top:30px;text-align:center; background-color:#F7D3D2;">
    There is no data available under the selected criteria.</div>';
    }
    ?>
<?php } ?>