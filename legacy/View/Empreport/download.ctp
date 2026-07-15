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
        border: 2px solid #f4f4f4;
        width: 100%;
        max-width: 100%;
        margin-bottom: 20px;
        background-color: transparent;
        border-spacing: 0;
        border-collapse: collapse;
    }

    td,
    th {
        text-align: left;
        padding: 8px;
        line-height: 1.42857143;
        vertical-align: top;
        border: 1px solid #B2B2B2;
    }
</style>
<?php
//echo $this->element('reportadminheader',array(
//'title'=>'Cost To Company(CTC) Report'));
?>
<?php $i = 0; ?>
<page backtop="50mm" backbottom="50mm" backleft=20mm" backright="2mm">
    <page_header>


        <!--        <div style="text-align:right; width:100%">

        <?php echo date("l,F j, Y"); ?> </div>-->
        <div style="text-align:left; width:100%; ">
            <?php if (isset($arr_comp_contact_info['CompanyContactInfo']['logo']) && !empty($arr_comp_contact_info['CompanyContactInfo']['logo'])) { ?>
                <div style="width: 20%; margin-left: 20px; font-size: 18px; ">
                    <img style=" margin-left: 20px; margin-top: 30px; " src="http://<?php echo $_SERVER['HTTP_HOST'] . $this->webroot . $arr_comp_contact_info['CompanyContactInfo']['logo']; ?>" height="100" width="auto" class="img-circle" alt="Company Logo" />
                </div>
                <!--<img style=" margin-left: 70px; " src="http://<?php echo $_SERVER['HTTP_HOST'] . $this->webroot . $arr_comp_contact_info['CompanyContactInfo']['logo']; ?>" height="50" width="70" class="img-circle" alt="Company Logo" />-->
            <?php } ?>
            <div style="width: 80%; margin-left: 100px; margin-top: 40px; position : absolute ; float: left; font-size: 14px; ">
                <div style="text-align: center;font-weight: bold;font-size: 14px; ; padding-top: 4px; "><?php echo $arr_comp_contact_info['CompanyContactInfo']['business_name']; ?>

                </div>
                <div style="text-align: center ; margin-left: 20px;padding-top: 4px; word-break: break-all;font-size: 11px; "><?php echo $arr_comp_contact_info['CompanyContactInfo']['address']; ?>

                </div>
                <div style="text-align: center ; padding-top: 4px;"><?php echo $arr_comp_contact_info['CompanyContactInfo']['city']; ?>
                    ,PIN - <?php echo $arr_comp_contact_info['CompanyContactInfo']['pincode']; ?>
                    ,<?php echo $arr_comp_contact_info['CompanyContactInfo']['state']; ?>
                </div>
                <div style="text-align: center ; padding-top: 4px;"> <?php echo "Phone : " . $arr_comp_contact_info['CompanyContactInfo']['phone']; ?>
                    <?php // echo ' Fax : ' . $arr_comp_contact_info['CompanyContactInfo']['fax']; 
                    ?>
                    <?php echo ' Email : ' . $arr_comp_contact_info['CompanyContactInfo']['email']; ?>
                </div>
            </div>

        </div>


        <hr>
        <h3 style="text-align: center;padding-bottom: 20px;padding-top: 10px;"><?php echo 'Cost To Company'; ?></h3>
        <br>
    </page_header>
    <page_footer>

        <div style="width: 100%; text-align: right">
            page [[page_cu]]/[[page_nb]]
        </div>
        <div style="width: 100%; text-align: left">
            Downloaded on <?php //echo $user_name; ?> <?php echo date("l,F j, Y"); ?>
        </div>
    </page_footer>
    <bookmark title="Sommaire" level="0"></bookmark>
</page>
<?php
foreach ($arr_salary_for_template as $values) {
    if (count($values['summary']) !== 0) {
        $i += 1;
        // debug($values['summary'])
?>


        <h3> <?php echo isset($values['summary']['0']['ed' ]['first_name']) ? $values['summary']['0']['ed' ]['first_name'] : '';
                echo ' ';
                echo  isset($values['summary']['0']['ed' ]['last_name']) ? $values['summary']['0']['ed' ]['last_name'] : '';
                echo (isset($values['summary']['0']['0']['0']['status'])) && $values['summary']['0']['0']['0']['status'] == "2" ? '  (Resigned)' : ''; ?>
        </h3>
        <div class="col-md-8">EMP ID : <?php echo  isset($values['summary']['0']['ep' ]['emp_company_id']) ? $values['summary']['0']['ep' ]['emp_company_id'] : ''; ?> </div>
        <div class="col-md-8">Branch : <?php echo  isset($values['summary']['0']['br']['branch_name']) ? $values['summary']['0']['br']['branch_name'] : ''; ?> </div>
        <div class="col-md-8">Designation : <?php echo  isset($values['summary']['0']['desg']['desig_name']) ? $values['summary']['0']['desg']['desig_name'] : ''; ?> </div>
        <div class="col-md-8">Department : <?php echo  isset($values['summary']['0']['dpt']['dept_name']) ? $values['summary']['0']['dpt']['dept_name'] : ''; ?> </div>




        <br>



        <table class="table">
            <thead>
                <tr>

                    <!--    <th>LEAVEPOLICY_GROUP_NAME</th> -->


                    <th style="width:40%">Salary</th>
                    <th style="width:40%;text-align:center;">Amount</th>


                </tr>
            </thead>

            <tbody>
                <?php if (count($values['summary']['0']) >= 0) {
                    $sum = 0; ?>
                    <?php foreach ($values['summary'] as $val) { ;
                        if ($val['ectc']['item_part'] != 'Indirect') {
                            if ($val['ectc']['structure_det_value'] != '0') {
                                if ($val['ectc']['head_type'] == 'fixed' || $val['ectc']['head_type'] == 'manually' || $val['ectc']['head_type'] == 'limit') {
                                    $det = round($val['ectc']['structure_det_value'], 2);
                                } else {
                                    $det = round($val['ectc']['structure_det_value']);
                                }
                                $sum = $sum + $det;
                    ?>
                                <tr>
                                    <td><?php echo $val['ectc']['salary_head_item_desc']; ?></td>
                                    <td style="text-align:center;"><?php echo $det; ?></td>
                                    <!--                                            <td><?php echo $val['ectc']['head_operator']; ?></td>-->

                                    <!--                                            <td><?php echo $val['ectc']['item_part']; ?></td>-->
                                </tr>

                    <?php
                            }
                        }
                    } ?>
                    <tr>
                        <th>Gross Salary</th>
                        <th style="text-align:center;"><?php echo round($sum); ?></th>
                    </tr>
                    <?php foreach ($values['summary'] as $val) {
                        if ($val['ectc']['item_part'] == 'Indirect') {
                            if ($val['ectc']['structure_det_value'] != '0') {
                                if ($val['ectc']['head_type'] == 'fixed' || $val['ectc']['head_type'] == 'manually' || $val['ectc']['head_type'] == 'limit') {
                                    $det = round($val['ectc']['structure_det_value'], 2);
                                } else {
                                    $det = round($val['ectc']['structure_det_value']);
                                }
                                $sum = $sum + $det;
                    ?>
                                <tr>
                                    <td><?php echo $val['ectc']['salary_head_item_desc']; ?></td>
                                    <td style="text-align:center;"><?php echo $det; ?></td>
                                    </tr>

                    <?php
                            }
                        }
                    } ?>
                    <tr>
                        <th>Cost To Company</th>
                        <th style="text-align:center;"><?php echo round($sum); ?></th>
                    </tr>
                <?php } else { ?>
                    <tr>
                        <td colspan="4">No employees found under this data</td>
                    </tr>
                <?php } ?>


            </tbody>
        </table>
        <br>


<?php } 
} ?> <!-- /.box-body -->