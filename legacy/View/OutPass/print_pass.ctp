<style>
    #headingTable td{
        border:1px solid white;
    } 

    #firstTable td {
        width: 220px;
        word-break: break-all;
        border: 1px solid white;
        line-height: 1;
    }

    /* #firstTable tr td:first-child {
        padding-left: 20px;
    } */

    #reportTable {
        width: 50px;
        word-break: break-all;
    }

    .page-break {
        page-break-after: always;
    }
</style>
<?php
// debug($arr_out_pass); exit;
$type = isset($arr_out_pass['type']) ? $arr_out_pass['type'] : '';

?>
<page backtop="5mm" backbottom="10mm" backleft="10mm" backright="2mm" style="font-size: 12pt; ">

    <page_header style="margin-top: 0px;">
        <!-- <img style=" margin-left: 40px;margin-top: 40px; width: 70px; height: auto; " src="https://v1.mypayrollmaster.online/<?php echo $arr_comp_contact_info['CompanyContactInfo']['logo']; ?>" alt="Company Logo" />
        <h4 style="text-align: center;margin-top: 0px"><?php echo isset($arr_comp_contact_info['CompanyContactInfo']['business_name']) ? $arr_comp_contact_info['CompanyContactInfo']['business_name'] : ''; ?></h4>
        <h4 style="text-align: center;margin-top: 0px;"> <span><?php echo strtoupper($type); ?></span></h4>
        <h4 style="text-align: center;margin-top: 0px;padding-top:0px;"> <span>---------------------------------------------------</span></h4> -->
        <div style="text-align: center;">
            <table id="headingTable" style="width: 100%;">
                <tr>
                    <td style="width: 20%;">&nbsp;</td>
                    <td style="width: 60%; padding-top:20px;">
                        <h4 style="text-align: center;"><?php echo isset($arr_comp_contact_info['CompanyContactInfo']['business_name']) ? $arr_comp_contact_info['CompanyContactInfo']['business_name'] : ''; ?></h4>
                    </td>
                    <td style="width: 20%;text-align:left; padding-left:5px;"><img style="width: auto; height: 80px;" src="http://<?php echo $_SERVER['HTTP_HOST'] . $this->webroot . $arr_comp_contact_info['CompanyContactInfo']['logo']; ?>" alt="Company Logo" /></td>
                </tr>
                <tr>
                    <td style="width:20%">&nbsp;</td>
                    <td style="width: 60%; font-weight:bold;"><span style="border-bottom: 1px dashed #000;"><?php echo strtoupper($type); ?>&nbsp;OUT PASS</span></td>
                    <td style="width: 20%;">&nbsp;</td>
                </tr>
                <tr>
                    <td style="width:20%;">&nbsp;</td>
                    <td style="width: 60%; padding-top: -10px;">----------------------------------</td>
                    <td style="width: 20%; ">&nbsp;</td>
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
<div class="modal-content" style="margin-top: 110px;" id="content">


    <div class="modal-body page-break">
        <!-- Form starts -->
        <div class="container" style="width:100%;">
            <table id="firstTable" style="margin-left:100px;">
                <tr>
                    <td>Out Pass No</td>
                    <td colspan="2">:&nbsp;<?php echo isset($arr_out_pass["out_pass_number"]) ? $arr_out_pass["out_pass_number"] : ''; ?></td>
                </tr>
                <!-- <tr>
                    <td colspan="3">&nbsp;</td>
                </tr> -->
                <tr>
                    <td>Date</td>
                    <td colspan="2">:&nbsp;<?php echo isset($arr_out_pass['out_pass_date']) ? date('d-m-Y', strtotime($arr_out_pass['out_pass_date'])) : ''; ?></td>
                </tr>
                <!-- <tr>
                    <td colspan="3">&nbsp;</td>
                </tr> -->
                <tr>
                    <td>Out Pass Type</td>
                    <td colspan="2">:&nbsp;<?php echo ($type); ?></td>
                </tr>
                <!-- <tr>
                    <td colspan="3">&nbsp;</td>
                </tr> -->
                <tr>
                    <td>Staff Name</td>
                    <td style="width:300px;">:&nbsp;<?php echo isset($arr_out_pass["staff_name"]) ? trim($arr_out_pass['staff_name']) : ''; ?></td>
                    <td>&nbsp;</td>
                </tr>
                <!-- <tr>
                    <td colspan="3">&nbsp;</td>
                </tr> -->
                <tr>
                    <td>Out Time</td>
                    <td colspan="2">:&nbsp;<?php echo isset($arr_out_pass['out_time']) ? $arr_out_pass['out_time'] : ''; ?></td>
                </tr>
                <!-- <tr>
                    <td colspan="3">&nbsp;</td>
                </tr> -->
                <tr>
                    <td>In Time</td>
                    <td colspan="2">:&nbsp;<?php echo isset($arr_out_pass['in_time']) ? $arr_out_pass['in_time'] : ''; ?></td>
                </tr>
                <!-- <tr>
                    <td colspan="3">&nbsp;</td>
                </tr> -->
                <tr>
                    <td>Remarks</td>
                    <td style="width: 370px;">:&nbsp;<?php echo isset($arr_out_pass["remarks"]) ? trim($arr_out_pass["remarks"]) : ''; ?></td>
                    <td>&nbsp;</td>
                </tr>
                <!-- <tr>
                    <td colspan="3">&nbsp;</td>
                </tr> -->
                <tr>
                    <td>Sanctioned By</td>
                    <td style="width: 370px;">:&nbsp;<?php echo isset($arr_out_pass['sanctioned_by_name']) ? $arr_out_pass['sanctioned_by_name'] : ''; ?></td>
                    <td>&nbsp;</td>
                </tr>
                <!-- <tr>
                    <td colspan="3">&nbsp;</td>
                </tr> -->
                <tr>
                    <td>Issued By</td>
                    <td style="width: 370px;">:&nbsp;<?php echo isset($arr_out_pass['issued_by_name']) ? $arr_out_pass['issued_by_name'] : ''; ?></td>
                    <td>&nbsp;</td>
                </tr>
                <!-- <tr>
                    <td colspan="3">&nbsp;</td>
                </tr> -->
                <tr>
                    <td>&nbsp;</td>
                    <td style="text-align:right; padding-right:70px;">Security&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;:&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</td>
                    <td></td>

                </tr>
                <!-- <tr>
                    <td colspan="3">&nbsp;</td>
                </tr> -->
                <tr>
                    <td>Signature of the Authorised Person&nbsp;:</td>
                    <td style="text-align:right; padding-right:70px;">Date & Time&nbsp;:&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</td>
                    <td></td>

                </tr>

            </table>
        </div>
        <!-- form ends-->
    </div>
</div>