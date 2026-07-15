<?php
/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
//edited by sinsiya
?>

<script>
    function getnewsum() {
        var input = $("input[name='head_vals[]']").map(function() {
            return $(this).val();
        }).get();

        <?php
        if ($str_company_code == 'KWMT' || $str_company_code == 'DEMO' || $str_company_code == 'GLET') { ?>
            //edited by sinsiya for water metro start
            var amtEmpaddition = parseInt($("input[name='amt_Empadd']").map(function() {
                return $(this).val();
            }).get());

            //edited by sinsiya for water metro end 
        <?php }
        ?>

        var total = 0;
        for (var i = 0; i < input.length; i++) {
            total += input[i] << 0;
        }

        <?php
        if ($str_company_code == 'KWMT' || $str_company_code == 'DEMO' || $str_company_code == 'GLET') { ?>
            //edited by siniya for water metro start
            var total = total + amtEmpaddition;
            //edited by sinsiya for water metro end
        <?php }
        ?>

        $('#totalsum').val(total).html(total);

        //Edited by Akshay on 14-12-2023
        var input2 = $("input[name='head_vals2[]']").map(function() {
            return Math.abs($(this).val());
        }).get();
        console.log('input2', input2);
        var total2 = 0;
        for (var i = 0; i < input2.length; i++) {
            total2 += input2[i] << 0;
        }
        total2 = parseInt(total2);
        idCard = 0;
        var idCard = parseInt($("input[name='id_card']").map(function() {
            return $(this).val();
        }).get());

        //Edited by Akshay on 20-7-2024
        function toZeroIfNaN(value) {
            return isNaN(value) ? 0 : value;
        }
        //End
        <?php
        if ($str_company_code == 'KWMT' || $str_company_code == 'DEMO'  || $str_company_code == 'GLET') { ?>
            //edited by sinsiya for water metro start
            var amtEmpded = parseInt($("input[name='amt_Empded']").map(function() {
                return $(this).val();
            }).get());
            //edited by sinsiya for water metro end
            //Edited by Akshay on 11-7-2024
            var otherDed = parseInt($("input[name='other_ded']").map(function() {
                return $(this).val();
            }).get());
            //Edited by Akshay on 10-10-2024
            var noticePay = parseInt($("input[name='notice_pay']").map(function() {
                return $(this).val();
            }).get());
            //End
            //Edited by Akshay on 21-8-2024
            var total3 = Math.abs(toZeroIfNaN(total2)) + Math.abs(toZeroIfNaN(idCard)) + Math.abs(toZeroIfNaN(amtEmpded)) + Math.abs(toZeroIfNaN(otherDed)) + Math.abs(toZeroIfNaN(noticePay));
            //End
            //End
        <?php } else { ?>
            var total3 = toZeroIfNaN(total2) + toZeroIfNaN(idCard);
        <?php } ?>


        $('#totalsum2').val(total3).html(total3);
    }
</script>
<div class="box box-default collapsed-box box-solid">
    <div class="box-header with-border">
        <h3 class="box-title">Full And Final Settlement Summary </h3>

        <div class="box-tools pull-right">
            <button onclick="toggleExpandable(this, 'contain2')" type="button" class="btn btn-box-tool" data-widget="collapse"><i class="fa fa-plus"></i>
            </button>
        </div>
        <!-- /.box-tools -->
    </div>
    <!-- /.box-header -->
    <div class="box-body" id="contain2">
        <h3>Settlement summary</h3>
        <!-- <input type="text" id="emp_pkey" name="emp_pkey" value="<?php //echo $emp_pkey; 
                                                                        ?>">-->
        <input type="hidden" id="hidden_resign_month" name="hidden_resign_month">
        <!-- <input type="hidden" id="hidden_leave_adjusted_name" name="hidden_leave_adjusted_name">
            <input type="hidden" id="hidden_resignation_month" name="hidden_resignation_month"> -->
        <?php //debug($arr_emp_settle); 
        $sum_add = 0;
        $sum_ded = 0;
        ?>
        <form id="heads" method="POST" action="<?php echo $this->webroot; ?>EmployeeResignation/save_heads">
            <input type="hidden" id="emp_pkey" name="emp_pkey" value="<?php echo $emp_pkey; ?>">
            <?php if ($str_company_code != 'KWMT' && $str_company_code != 'DEMO' && $str_company_code != 'DEMO' && $str_company_code != 'GLET') {  ?>
                <table class="table table-bordered" style="border:1px solid #3C8DBC">
                    <thead>
                        <tr>
                            <th colspan="2" style="text-align: center; ">ADDITIONS</th>
                            <th colspan="2" style="text-align: center; ">DEDUCTIONS</th>
                        </tr>
                        <th>Item</th>
                        <th>Amount</th>
                        <th>Item</th>
                        <th>Amount</th>
                    </thead>
                    <?php $sum_add = 0;  ?>
                    <?php $sum_ded = 0;  ?>
                    <tbody>
                        <?php if (count($arr_emp_settle['payd_additional']) > count($arr_emp_settle['payd_deductions'])) {
                            $great = 'payd_additional';
                        } else {
                            $great = 'payd_deductions';
                        }
                        // debug($arr_emp_settle['payd_additional']);
                        // debug($arr_emp_settle['payd_deductions']);
                        foreach ($arr_emp_settle[$great] as $key => $val) {
                            //if (isset($arr_emp_settle[$great][$key]['emp_settle_slip']['month_year']) && $arr_emp_settle[$great][$key]['emp_settle_slip']['month_year'] == $end_date) {
                        ?>
                            <tr>
                                <td><?php echo isset($arr_emp_settle['payd_additional'][$key]['emp_settle_slip']['salary_head_item_desc'])
                                        ? $arr_emp_settle['payd_additional'][$key]['emp_settle_slip']['salary_head_item_desc'] : ''; ?></td>
                                <td><?php echo isset($arr_emp_settle['payd_additional'][$key]['emp_settle_slip']['salary_amount']) ? $arr_emp_settle['payd_additional'][$key]['emp_settle_slip']['salary_amount'] : ''; ?></td>

                                <td><?php echo isset($arr_emp_settle['payd_deductions'][$key]['emp_settle_slip']['salary_head_item_desc']) ?
                                        $arr_emp_settle['payd_deductions'][$key]['emp_settle_slip']['salary_head_item_desc'] : ''; ?></td>
                                <td><?php echo isset($arr_emp_settle['payd_deductions'][$key]['emp_settle_slip']['salary_amount']) ?
                                        $arr_emp_settle['payd_deductions'][$key]['emp_settle_slip']['salary_amount'] : ''; ?></td>

                                <?php $adds = isset($arr_emp_settle['payd_additional'][$key]['emp_settle_slip']['salary_amount']) ?
                                    $arr_emp_settle['payd_additional'][$key]['emp_settle_slip']['salary_amount'] : 0; ?>

                                <?php $sum_add = $sum_add + $adds; ?>
                                <?php if (isset($arr_emp_settle['payd_deductions'][$key])) { ?>
                                    <?php $deds = isset($arr_emp_settle['payd_deductions'][$key]['emp_settle_slip']['salary_amount']) ?
                                        $arr_emp_settle['payd_deductions'][$key]['emp_settle_slip']['salary_amount'] : 0; ?>
                                    <?php $sum_ded = $sum_ded + $deds; ?>
                                <?php } ?>
                            </tr>
                        <?php
                        } ?>
                        <tr>
                            <td>Total</td>
                            <td><?php echo '<b>' .  $sum_add . '</b>'; ?></td>
                            <td>Total</td>
                            <td><?php echo '<b>' . $sum_ded . '</b>'; ?></td>
                        </tr>
                    </tbody>
                </table>
            <?php } ?>

            <!-- Edited by Akshay on 25-3-2024 -->
            <?php
            if ($str_company_code == 'KWMT' || $str_company_code == 'DEMO' || $str_company_code == 'DEMO' || $str_company_code == 'GLET')
                foreach ($joinedArray as $month => $date) { ?>
                <?php if ($str_company_code == 'KWMT' || $str_company_code == 'DEMO' || $str_company_code == 'DEMO' || $str_company_code == 'GLET') {  ?>
                    <label for="salary_<?php echo $month; ?>">Salary Released</label> &nbsp;&nbsp;<input type="checkbox" id="salary_<?php echo $month; ?>" name="salary_<?php echo $month; ?>" value="Y" onclick="paysalary('<?php echo strval($month); ?>',<?php echo $emp_pkey; ?>)">
                <?php } ?>
                <table class="table table-bordered" style="border:1px solid #3C8DBC">
                    <thead>
                        <tr>
                            <th colspan="2" style="text-align: center; ">ADDITIONS</th>
                            <th colspan="2" style="text-align: center; ">DEDUCTIONS</th>
                        </tr>
                        <th>Item</th>
                        <th>Amount</th>
                        <th>Item</th>
                        <th>Amount</th>
                    </thead>
                    <?php $sum_add = 0;  ?>
                    <?php $sum_ded = 0;
                    $count = isset($date['payd_deductions']) ? $date['payd_deductions'] : 0 ?>
                    <tbody>
                        <?php if (count($date['payd_additional']) > count($count)) {
                            $great = 'payd_additional';
                        } else {
                            $great = 'payd_deductions';
                        }
                        foreach ($date[$great] as $key => $val) {

                        ?>
                            <tr>
                                <td><?php echo isset($date['payd_additional'][$key]['emp_settle_slip']['salary_head_item_desc'])
                                        ? $date['payd_additional'][$key]['emp_settle_slip']['salary_head_item_desc'] : ''; ?></td>
                                <td><?php echo isset($date['payd_additional'][$key]['emp_settle_slip']['salary_amount']) ? $date['payd_additional'][$key]['emp_settle_slip']['salary_amount'] : ''; ?></td>

                                <td><?php echo isset($date['payd_deductions'][$key]['emp_settle_slip']['salary_head_item_desc']) ?
                                        $date['payd_deductions'][$key]['emp_settle_slip']['salary_head_item_desc'] : ''; ?></td>
                                <td><?php echo isset($date['payd_deductions'][$key]['emp_settle_slip']['salary_amount']) ?
                                        $date['payd_deductions'][$key]['emp_settle_slip']['salary_amount'] : ''; ?></td>

                                <?php $adds = isset($date['payd_additional'][$key]['emp_settle_slip']['salary_amount']) ?
                                    $date['payd_additional'][$key]['emp_settle_slip']['salary_amount'] : 0; ?>

                                <?php $sum_add = $sum_add + $adds; ?>
                                <?php if (isset($date['payd_deductions'][$key])) { ?>
                                    <?php $deds = isset($date['payd_deductions'][$key]['emp_settle_slip']['salary_amount']) ?
                                        $date['payd_deductions'][$key]['emp_settle_slip']['salary_amount'] : 0; ?>
                                    <?php $sum_ded = $sum_ded + $deds; ?>
                                <?php } ?>
                            </tr>
                        <?php
                        } ?>
                        <tr>
                            <td>Total</td>
                            <td><?php echo '<b>' .  $sum_add . '</b>'; ?></td>
                            <td>Total</td>
                            <td><?php echo '<b>' . $sum_ded . '</b>'; ?></td>
                        </tr>
                    </tbody>
                </table>
            <?php }
            ?>
            <table class="table table-bordered" style="border:1px solid #3C8DBC">
                <tbody>
                    <?php if (count($arr_emp_settle['Extra_additions']) > count($arr_emp_settle['Extra_deductions'])) {
                        $great = 'Extra_additions';
                    } else {
                        $great = 'Extra_deductions';
                    }
                    ?>
                    <tr>
                        <th colspan="3" style="text-align: center; ">
                            OTHERS
                        </th>
                    </tr>

                    <?php
                    $totalamont = 0;
                    $totalamont2 = 0; //Edited by Akshay on 14-12-2023
                    $sumarray = array("0" => "0");
                    $sumarray2 = array("0" => "0");
                    //Edited by Akshay on 14-12-2023
                    ?>
                    <!-- Edited by Akshay on 14-12-2023 -->
                    <tr>
                        <th colspan="3" style="text-align: center; ">
                            ADDITION
                        </th>
                    </tr>
                    <tr colspan="3">
                        <th>Item</th>
                        <th>Amount</th>
                        <th>Change Amount</th>
                    </tr>
                    <?php
                    //Edited by Akshay on 11-7-2024
                    $amt = 0;
                    //End
                    foreach ($arr_emp_settle[$great] as $key => $val) {
                        if (isset($arr_emp_settle['Extra_additions'][$key]['emp_settle_slip']['salary_head_item_desc'])) { //Edited by Akshay on 11-7-2024
                    ?>
                            <tr>
                                <td><?php echo isset($arr_emp_settle['Extra_additions'][$key]['emp_settle_slip']['salary_head_item_desc'])
                                        ? $arr_emp_settle['Extra_additions'][$key]['emp_settle_slip']['salary_head_item_desc'] : ''; ?></td>
                                <td><?php echo abs(isset($arr_emp_settle['Extra_additions'][$key]['emp_settle_slip']['salary_amount']) ? $amt = $arr_emp_settle['Extra_additions'][$key]['emp_settle_slip']['salary_amount'] : ''); ?></td>
                                <td>
                                    <div><input type="number" class="net_sal" name="head_vals[]" onchange="getnewsum();" value="<?php echo $arr_emp_settle['Extra_additions'][$key]['emp_settle_slip']['salary_amount']; ?>">
                                        <input type="hidden" name="head_fkey[]" value="<?php echo $arr_emp_settle['Extra_additions'][$key]['emp_settle_slip']['emp_settle_slip_pkey']; ?>">
                                    </div>
                                </td>
                            </tr>

                    <?php
                        }
                        $totalamont += $amt;
                        array_push($sumarray, $amt);
                    }
                    $newsum = array_sum($sumarray); ?>
                    <?php
                    if ($str_company_code == 'KWMT' || $str_company_code == 'DEMO' || $str_company_code == 'DEMO' || $str_company_code == 'GLET') { ?>
                        <!--edited by sinsiya for water metro start-->
                        <tr>
                            <td>Amount paid by the employee</td>
                            <td>0</td>
                            <td>
                                <div><input type="number" class="net_sal" name="amt_Empadd" onchange="getnewsum();" value="0">

                                </div>
                            </td>
                        </tr>
                        <!--edited by sinsiya for water metro end-->
                    <?php }
                    ?>

                    <tr>

                        <td>Total</td>
                        <td><?php echo '<b>' . abs($totalamont) . '</b>'; ?></td>
                        <td>
                            <div id="totalsum"><?php echo '<b>' . $newsum . '</b>'; ?></div>
                        </td>
                    </tr>

                    <!-- Edited by Akshay on 14-12-2023 -->
                    <tr>
                        <th colspan="3" style="text-align: center; ">
                            DEDUCTION
                        </th>
                    </tr>
                    <tr colspan="3">
                        <th>Item</th>
                        <th>Amount</th>
                        <th>Change Amount</th>
                    </tr>
                    <?php
                    foreach ($arr_emp_settle[$great] as $key => $val) { //debug($arr_emp_settle);
                    ?>
                        <tr>
                            <?php if (isset($arr_emp_settle['Extra_deductions'][$key]['emp_settle_slip']['salary_amount'])) { ?>
                                <td><?php echo isset($arr_emp_settle['Extra_deductions'][$key]['emp_settle_slip']['salary_head_item_desc']) ?
                                        $arr_emp_settle['Extra_deductions'][$key]['emp_settle_slip']['salary_head_item_desc'] : ''; ?></td>
                                <td><?php echo isset($arr_emp_settle['Extra_deductions'][$key]['emp_settle_slip']['salary_amount']) ?
                                        $amt1 = ($arr_emp_settle['Extra_deductions'][$key]['emp_settle_slip']['salary_amount']) : ''; ?></td>
                                <td>
                                    <div class="editsum"><input type="number" class="net_salary" name="head_vals2[]" onchange="getnewsum();" value="<?php echo ($arr_emp_settle['Extra_deductions'][$key]['emp_settle_slip']['salary_amount']); ?>">
                                        <input type="hidden" name="head_fkey2[]" value="<?php echo $arr_emp_settle['Extra_deductions'][$key]['emp_settle_slip']['emp_settle_slip_pkey']; ?>">
                                    </div>
                                </td>
                            <?php $totalamont2 += $amt1;
                                array_push($sumarray2, $amt1);
                            }  ?>
                            <?php $adds = isset($arr_emp_settle['Extra_additions'][$key]['emp_settle_slip']['salary_amount']) ?
                                $arr_emp_settle['Extra_additions'][$key]['emp_settle_slip']['salary_amount'] : 0; ?>

                            <?php $sum_add = (isset($sum_add) ? $sum_add : 0) + $adds; ?>
                            <?php if (isset($arr_emp_settle['Extra_deductions'][$key])) { ?>
                                <?php $deds = isset($arr_emp_settle['Extra_deductions'][$key]['emp_settle_slip']['salary_amount']) ?
                                    $arr_emp_settle['Extra_deductions'][$key]['emp_settle_slip']['salary_amount'] : 0; ?>
                                <?php $sum_ded = $sum_ded + $deds; ?>
                            <?php } ?>
                        </tr>
                    <?php } ?>
                    <!-- Edited by Akshay on 20-12-2023 -->
                    <?php
                    if ($str_company_code == 'DEMO' || $str_company_code == 'KWMT' || $str_company_code == 'DEMO' || $str_company_code == 'GLET') {
                    ?>
                        <tr>

                            <td>ID Card</td>
                            <td><?php echo abs(isset($id_card) ?
                                    $amt1 = $id_card : 0); ?></td>
                            <td>
                                <div class="editsum"><input type="number" class="net_salary" name="id_card" onchange="getnewsum();" value="0">
                                    <!-- <input type="hidden" name="id_card_edit[]"> -->
                                </div>
                            </td>
                            <?php
                            $totalamont2 += $amt1;
                            array_push($sumarray2, $amt1);
                            $key = isset($key) ? $key : 0;
                            ?>
                            <?php $adds = isset($arr_emp_settle['Extra_additions'][$key]['emp_settle_slip']['salary_amount']) ?
                                $arr_emp_settle['Extra_additions'][$key]['emp_settle_slip']['salary_amount'] : 0; ?>

                            <?php $sum_add = $sum_add + $adds; ?>
                            <?php if (isset($arr_emp_settle['Extra_deductions'][$key])) { ?>
                                <?php $deds = isset($arr_emp_settle['Extra_deductions'][$key]['emp_settle_slip']['salary_amount']) ?
                                    $arr_emp_settle['Extra_deductions'][$key]['emp_settle_slip']['salary_amount'] : 0; ?>
                                <?php $sum_ded = $sum_ded + $deds; ?>
                            <?php } ?>
                        </tr>
                    <?php } ?>
                    <?php $newsum2 = array_sum($sumarray2); ?>
                    <?php
                    if ($str_company_code == 'KWMT' || $str_company_code == 'DEMO' || $str_company_code == 'DEMO' || $str_company_code == 'GLET') { ?>
                        <!--edited by sinisya for water metro start-->
                        <tr>
                            <td>Amount paid by the employee</td>
                            <td>0</td>
                            <td>
                                <div class="editsum"><input type="number" class="net_salary" name="amt_Empded" onchange="getnewsum();" value="0">

                                </div>
                            </td>
                        </tr>
                        <!-- edited by sinsiya for water metro end-->
                        <!-- Edited by Akshay on 10-10-2024 -->
                        <tr>
                            <td>Notice Pay</td>
                            <td><?php echo $notice_pay;
                                // Edited by Akshay on 26-2-2025
                                $totalamont2 += $notice_pay;
                                $newsum2 += (abs($notice_pay) <= 50000) ? $notice_pay : -50000;
                                // End
                                ?></td>
                            <td>
                                <div class="editsum"><input type="number" class="net_salary" name="notice_pay" onchange="getnewsum();" value="<?php echo (abs($notice_pay) <= 50000) ? $notice_pay : -50000; ?>">

                                </div>
                            </td>
                        </tr>
                        <!-- End -->
                        <!-- Edited by Akshay on 11-7-2024 -->
                        <tr>
                            <td>Other</td>
                            <td>0</td>
                            <td>
                                <div class="editsum"><input type="number" class="net_salary" name="other_ded" onchange="getnewsum();" value="0">

                                </div>
                            </td>
                        </tr>
                        <!-- End -->
                    <?php } ?>
                    <tr>
                        <td>Total</td>
                        <td><?php echo '<b>' . abs($totalamont2) . '</b>'; ?></td>
                        <td>
                            <div id="totalsum2"><?php echo '<b>' . abs($newsum2) . '</b>'; ?></div>
                        </td>
                    </tr>
                </tbody>
            </table><br>
            <button class="btn btn-primary pull-right">Generate Full and Final Slip</button>
        </form>
    </div>
</div>
<script>
    var options = {
        success: function(resp) {
            process();
            $.notify($.parseJSON(resp).msg, {
                type: 'success',
                allow_dismiss: false,
                autoHideDelay: 50000,
            });
        } // post-submit callback
    };
    $('#heads').on('submit', function(event) {
        //Edited by Akshay on 15-12-2023
        var currentValue = $('#resignation_date').val();
        $('#hidden_resign_month').val(currentValue);
        $("#contain2").prepend('<div class="loader-div"><li class="fa fa-spinner fa-spin"></li></div>');
        setTimeout(function() {
            $(".loader-div").hide();
        }, 1000)
        event.preventDefault();
        if (confirm(" Do You Want  To Save The Form  ")) {
            $('#heads').ajaxSubmit(options)
        }
    });
    jQuery(document).ready(function($) {
        var net_sal_sum;

        $(".net_sal").each(function(index) {
            net_sal_sum++;
        });
        $('#total_amt_s').html(net_sal_sum);

    });

    //Edited by Akshay on 26-3-2024
    function paysalary(month, emp_pkey) {
        console.log('Month', month);
        var month_year = month;
        var emp_pkey = emp_pkey;
        if ($('#salary_' + month_year).prop('checked')) {
            var paid = 'Y';
        } else {
            var paid = 'N';
        }
        // AJAX POST request
        $.ajax({
            url: livesite + "EmployeeResignation/paysalary",
            method: 'POST',
            data: {
                month_year: month_year,
                emp_pkey: emp_pkey,
                status: paid
            }, // Send the month_year and month values as data
            success: function(response) {
                // Handle the response from the server
                console.log('Success:', response);
            },
            error: function(xhr, status, error) {
                // Handle errors
                console.error('Error:', error);
            }
        });
    }
</script>