<style>
    #inputTable {
        border-collapse: collapse;
        width: 100%;
    }

    #inputTable,
    #inputTable th,
    #inputTable td {
        border-bottom: 1px dashed #000;
        width: 60px;
        word-break: break-all;
    }

    #inputTable th,
    #inputTable td {
        padding: 8px;
        text-align: left;
    }

    #inputTable input {
        width: 100%;
    }

    #firstTable td {
        /* width: 80px; */
        word-break: break-all;
        border: 1px solid white;
    }

    #reportTable {
        border-collapse: collapse;
        word-break: break-all;
        padding-top: 20px;
    }

    #reportTable td {
        border: 1px solid white;
    }

    .page-break {
        page-break-after: always;
    }
</style>
<?php
$type = '';
if (isset($arr_gate_pass['GatePass']['type']) && $arr_gate_pass['GatePass']['type'] == 'Material Gate Pass - Free') {
    $type = 'Material Gate Pass - Free';
} elseif (isset($arr_gate_pass['GatePass']['type']) && $arr_gate_pass['GatePass']['type'] == 'Material Gate Pass - Return') {
    $type = 'Material Gate Pass - Return';
} elseif (isset($arr_gate_pass['GatePass']['type']) && $arr_gate_pass['GatePass']['type'] == 'Material Gate Pass - Sale') {
    $type = 'Material Gate Pass - Sale';
}
?>
<page backtop="20mm" backbottom="10mm" backleft="10mm" backright="2mm" style="font-size: 12pt; ">

    <page_header>
        <div style="text-align: center;">
            <table style="width: 100%; ">
                <tr>
                    <td style="width: 20%;">&nbsp;</td>
                    <td style="width: 60%;">
                        <h4 style="text-align: center;"><?php echo isset($arr_comp_contact_info['CompanyContactInfo']['business_name']) ? $arr_comp_contact_info['CompanyContactInfo']['business_name'] : ''; ?></h4>
                    </td>
                    <td style="width: 20%;text-align:left; padding-left:0px; padding-top:-15px;"><img style="width: auto; height: 70;" src="https://v1.mypayrollmaster.online/<?php echo $arr_comp_contact_info['CompanyContactInfo']['logo']; ?>" alt="Company Logo" /></td>
                </tr>
                <tr>
                    <td style="width:20%">&nbsp;</td>
                    <td style="width: 60%; font-weight:bold; "><span style="border-bottom: 1px dashed #000;;"><?php echo strtoupper($type); ?></span></td>
                    <td style="width: 20%;">&nbsp;</td>
                </tr>
                <tr style="line-height: 0.2;">
                    <td style="width:20%; line-height: 0.2;">&nbsp;</td>
                    <td style="width: 60%; font-size: smaller; vertical-align: middle;line-height: 0.2;padding-top:-25px;">- - - - - - - - - - - - - - - - - - - - - - - - - </td>
                    <td style="width: 20%; line-height: 0.2;">&nbsp;</td>
                </tr>
                <tr>
                    <td style="width:20%;">&nbsp;</td>
                    <td style="width: 60%;">&nbsp;</td>
                    <td style="width: 20%; ">&nbsp;</td>
                </tr>
            </table>
        </div>
    </page_header>

    <bookmark title="Sommaire" level="0"></bookmark>
</page>
<!-- Modal content-->
<div class="modal-content" style="margin-top: 0px;" id="content">

    <!-- <div class="modal-header" style="background: #00659f; color: white; display: flex; justify-content: space-between;">
        <h4 class="modal-title" style="margin: 0;"> Preview</h4>


    </div> -->
    <div class="modal-body page-break">
        <!-- Form starts -->
        <div class="container" style="width:100%;">


            <table id="firstTable">
                <tr>
                    <td>Gate Pass No</td>
                    <!-- <td>:</td> -->
                    <td>&nbsp;:&nbsp;&nbsp;<?php echo isset($arr_gate_pass['GatePass']["gate_pass_no"]) ? $arr_gate_pass['GatePass']["gate_pass_no"] : ''; ?></td>
                    <!-- <td></td> -->
                    <td colspan="3" style="width:150px; text-align:right; ">&nbsp;Requested Person&nbsp;&nbsp;:</td>
                    <td colspan="2" style="width:200px; ">&nbsp;&nbsp;<?php echo isset($arr_gate_pass['req']['req_person_name']) ? $arr_gate_pass['req']['req_person_name'] : ''; ?></td>
                </tr>
                <tr>
                    <td>Issued To</td>
                    <!-- <td>:</td> -->
                    <td colspan="6" style="width: 320px; text-align:left;">&nbsp;:&nbsp;&nbsp;<?php echo isset($arr_gate_pass['GatePass']['issued_to']) ? $arr_gate_pass['GatePass']['issued_to'] : ''; ?></td>
                    <td colspan="2">&nbsp;</td>
                </tr>
                <tr>
                    <td>Purpose</td>
                    <td colspan="6" style="width:400px; text-align:left;">&nbsp;:&nbsp;&nbsp;<?php echo isset($arr_gate_pass['GatePass']["purpose"]) ? $arr_gate_pass['GatePass']["purpose"] : ''; ?></td>
                    <td colspan="2">&nbsp;</td>
                </tr>
                <tr>
                    <td>Remarks</td>
                    <td colspan="6" style="width:400px; text-align:left;">&nbsp;:&nbsp;&nbsp;<?php echo isset($arr_gate_pass['GatePass']["remarks"]) ? $arr_gate_pass['GatePass']["remarks"] : ''; ?></td>
                    <td colspan="2">&nbsp;</td>
                </tr>
            </table>


            <?php
            if (count($arr_item) > 0) {
            ?>
                <div id='tableDiv' class="form-group" style="display: block;text-align:center;">
                    <table id="inputTable">
                        <thead>
                            <tr>
                                <th style="border-left: none; border-top: 1px dashed #000;border-bottom: 1px dashed #000;">Sl No.</th>
                                <th style=" border-bottom:none;border-top: 1px dashed #000;border-bottom: 1px dashed #000;">Item</th>
                                <th style=" border-bottom:none;border-top: 1px dashed #000;border-bottom: 1px dashed #000;">Qty</th>
                                <th style="width: 100px; border-bottom:none;border-top: 1px dashed #000;border-bottom: 1px dashed #000;">UOM</th>
                                <th style=" border-bottom:none;border-top: 1px dashed #000;border-bottom: 1px dashed #000;">Rate</th>
                                <th style=" border-bottom:none;border-top: 1px dashed #000;border-bottom: 1px dashed #000;">Amount</th>
                                <th style=" border-bottom:none;border-top: 1px dashed #000;border-bottom: 1px dashed #000;">Expected Returnable Date</th>
                            </tr>
                            <!-- <tr>
                                <th style=" border-top:none;" colspan="2">Exp.Return</th>
                                <th style=" border-top:none;"></th>
                                <th style=" border-top:none;"></th>
                                <th style="width: 100px; border-top:none;"></th>
                                <th style=" border-top:none;"></th>
                                <th style=" border-top:none;"></th>
                            </tr> -->
                        </thead>
                        <tbody>
                            <!-- Add your rows with input boxes here -->
                            <?php
                            $total_amount = 0;
                            $total_rate = 0;
                            $count = count($arr_item);
                            foreach ($arr_item as $key => $item) {
                                $total_amount += isset($item['gi']['amount']) ? $item['gi']['amount'] : 0;
                                $total_rate += isset($item['gi']['rate']) ? $item['gi']['rate'] : 0;
                                $return = isset($item['gi']['return']) ? $item['gi']['return'] : '';
                            ?>
                                <input type="hidden" name="gate_pass_items_pkey[]" value="<?php echo isset($item['gi']['gate_pass_items_pkey']) ? $item['gi']['gate_pass_items_pkey'] : 0; ?>">
                                <?php
                                if (($key + 1) < $count) { ?>
                                    <tr>

                                        <td style=" border-top:none; border-bottom:none;"><?php echo $key + 1; ?></td>
                                        <td style=" border-top:none; border-bottom:none;"><?php echo $item['gi']['item_name']; ?></td>
                                        <td style=" border-top:none; border-bottom:none;"><?php echo sprintf("%.2f", $item['gi']['qty']); ?></td>

                                        <td style=" border-top:none; border-bottom:none;">
                                            <?php echo $item['gi']['units']; ?>
                                        </td>
                                        <td style=" border-top:none; border-bottom:none;"><?php echo sprintf("%.2f", $item['gi']['rate']); ?></td>
                                        <td style=" border-top:none; border-bottom:none;"><?php echo sprintf("%.2f", $item['gi']['amount']); ?></td>

                                        <!-- <td><input disabled name="return[]" type="text" value="<?php echo $item['gi']['return']; ?>" placeholder=""></td> -->
                                        <td style=" border-top:none; border-bottom:none;">
                                            <?php echo $return; ?>
                                        </td>
                                    </tr>
                                <?php } else { ?>
                                    <tr>

                                        <td style=" border-top:none; "><?php echo $key + 1; ?></td>
                                        <td style=" border-top:none; "><?php echo $item['gi']['item_name']; ?></td>
                                        <td style=" border-top:none; "><?php echo sprintf("%.2f", $item['gi']['qty']); ?></td>

                                        <td style=" border-top:none; ">
                                            <?php echo $item['gi']['units']; ?>
                                        </td>
                                        <td style=" border-top:none; "><?php echo sprintf("%.2f", $item['gi']['rate']); ?></td>
                                        <td style=" border-top:none; "><?php echo sprintf("%.2f", $item['gi']['amount']); ?></td>

                                        <!-- <td><input disabled name="return[]" type="text" value="<?php echo $item['gi']['return']; ?>" placeholder=""></td> -->
                                        <td style=" border-top:none; ">
                                            <?php echo $return; ?>
                                        </td>
                                    </tr>
                                <?php }
                                ?>
                                <!-- <tr>
                                    <td colspan="2" style=" border-top:none; border-bottom:none;"><?php echo $return; ?></td>
                                    <td style=" border-top:none; border-bottom:none;"></td>
                                    <td colspan="4" style=" border-top:none; border-bottom:none;"></td>
                                </tr> -->
                            <?php
                            }
                            ?>
                            <tr>
                                <td colspan="3" style="border-top: 1px dashed #000;"></td>
                                <td>TOTAL</td>
                                <td style="  border-top: 1px dashed #000;"><?php echo sprintf("%.2f", $total_rate); ?></td>
                                <td style="  border-top: 1px dashed #000;"><?php echo sprintf("%.2f", $total_amount); ?></td>
                                <td style="  border-top: 1px dashed #000;"></td>
                            </tr>
                            <!-- Add more rows as needed -->
                        </tbody>
                    </table>
                </div>
            <?php
            }
            ?>


            <table id="reportTable">
                <tr>

                    <td style="text-align:left;padding:0px;">Issued By</td>
                    <td>:</td>
                    <td style="text-align:left;padding: 0px; width:200px;">
                        <?php echo isset($arr_gate_pass['issued_by']['issued_by_name']) ? $arr_gate_pass['issued_by']['issued_by_name'] : ''; ?>
                    </td>
                    <td></td>
                    <td style="text-align:left; padding:0px; width:300px;" colspan="4">Sanctioned By&nbsp;:&nbsp; <span><?php echo isset($arr_gate_pass['sanctioned_by']['sanctioned_by_name']) ? $arr_gate_pass['sanctioned_by']['sanctioned_by_name'] : ''; ?></span> </td>
                </tr>
                <tr>
                    <td colspan="8">&nbsp;</td>
                </tr>
                <tr>
                    <td style="text-align:left; ">Date</td>
                    <td>:</td>
                    <td style="text-align:left;">
                        <?php echo isset($arr_gate_pass['GatePass']["issued_date"]) ? $arr_gate_pass['GatePass']["issued_date"] : ''; ?>
                    </td>
                    <td></td>
                    <td style="text-align:left; width:auto;padding:0px;padding-left:68px;">Name of Security&nbsp;&nbsp;:&nbsp;</td>
                    <td></td>
                    <td></td>
                    <td></td>
                </tr>

                <tr style=" line-height: 10px;">
                    <td style="text-align:left; width:150px;padding:0px;" colspan="3">Signature of the Authorized Person&nbsp;&nbsp;:</td>

                    <td></td>
                    <td style="text-align:left; padding-left:70px;">Date & Time&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;:&nbsp;</td>
                    <td></td>
                    <td></td>
                    <td></td>
                </tr>
                <tr>
                    <td colspan="8">&nbsp;</td>
                </tr>
                <tr>
                    <td colspan="8">* In case of returnable goods it is the responsibility of the person to ensure that the goods are returned within the expected date of return in good condition.</td>
                </tr>

            </table>


        </div>
        <!-- form ends-->
    </div>
</div>