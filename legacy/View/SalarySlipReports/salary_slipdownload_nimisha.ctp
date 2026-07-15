<style type="text/css">
    body {
        line-height: 2em;
    }
    .block-container {
        width: 95%;
        padding: 20px;
        /*border: #000000 solid thin;*/
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
        /*border: 1px solid #f4f4f4;*/
        width: 80%;
        max-width: 80%;
        margin-bottom: 20px;
        /*background-color: transparent;*/
        border-spacing: 0;
        border-collapse: collapse;
    }
    td, th {
        text-align: left;
        padding: 8px;
        font-weight: normal;
        /*font-size: 11px;*/
        font-size: 14px;
        /*font-family: serif;*/
        line-height: 1.42857143;
        word-wrap: break-word;
        vertical-align: top;
        color: black ;
        border: 1px solid;
    }
</style>
<?php
$i = 0;
foreach ($arr_salary_for_template as $value) {
?>
<page backtop="50mm" backbottom="50mm" backleft="10mm" backright="10mm" style="font-size: 12pt">
    <page_header>


<!--        <div style="text-align:right; width:100%">

            <?php echo date("l,F j, Y"); ?> </div>-->
        <div style="text-align:left; width:100%; ">
            <?php if (isset($arr_comp_contact_info['CompanyContactInfo']['logo']) && !empty($arr_comp_contact_info['CompanyContactInfo']['logo'])) { ?> 
            <div style="width: 20%; margin-left: 20px; font-size: 18px; ">
                <img style=" margin-left: 20px; margin-top: 40px; " src="http://<?php echo $_SERVER['HTTP_HOST'] . $this->webroot . $arr_comp_contact_info['CompanyContactInfo']['logo']; ?>" height="100" width="100" class="img-circle" alt="Company Logo" />
            </div>
                <!--<img style=" margin-left: 70px; " src="http://<?php echo $_SERVER['HTTP_HOST'] . $this->webroot . $arr_comp_contact_info['CompanyContactInfo']['logo']; ?>" height="50" width="70" class="img-circle" alt="Company Logo" />-->
            <?php } ?>
                <div style="width: 80%; margin-left: 100px; margin-top: 40px; position : absolute ; float: left; font-size: 14px; ">
                    <div style=" text-align: center;">
                        <p>FORM XIII –See Rules 29(2)</p>
                    </div>
                    <div style="text-align: center;font-weight: bold;font-size: 14px; ; padding-top: 4px; "><?php echo $arr_comp_contact_info['CompanyContactInfo']['business_name']; ?>

                    </div>
                    <div style="text-align: center ; padding-top: 4px; word-break: break-all;font-size: 12px; "><?php echo $arr_comp_contact_info['CompanyContactInfo']['address']; ?>

                    </div>
                    <div style="text-align: center ; padding-top: 4px;"><?php echo $arr_comp_contact_info['CompanyContactInfo']['city']; ?>
                        ,PIN - <?php echo $arr_comp_contact_info['CompanyContactInfo']['pincode']; ?>
                        ,<?php echo $arr_comp_contact_info['CompanyContactInfo']['state']; ?>
                    </div> <div style="text-align: center ; padding-top: 4px;"> <?php echo "Phone : " . $arr_comp_contact_info['CompanyContactInfo']['phone']; ?>
                        <?php // echo ' Fax : ' . $arr_comp_contact_info['CompanyContactInfo']['fax']; ?>
                        <?php echo ' Email : ' . $arr_comp_contact_info['CompanyContactInfo']['email']; ?>
                    </div>
                </div>
                
        </div>      
        
        
        <hr>
        <h3 style="text-align: center;padding-bottom: 20px;padding-top: 10px;"><?php echo 'Salary Slip - '.date("M Y",strtotime($value['summary']['0']['ectc']['month_year'])); ?></h3>
        <br>
    </page_header>
    <page_footer>

        <div style="width: 100%; text-align: right">
            page [[page_cu]]/[[page_nb]]
        </div>
        <div style="width: 100%; text-align: left">
            Downloaded By  <?php echo $user_name; ?> <?php echo date("l,F j, Y"); ?> 
        </div>
    </page_footer>
    <bookmark title="Sommaire" level="0" ></bookmark>
</page>
<?php
//echo $this->element('reportadminheader', array(
//'title' => 'Salary Slip - '.date("M Y",strtotime($value['summary']['0']['ectc']['month_year']))));
?>

<table class="table table-bordered" align="center" style="margin-top: 10px; ">
    <tbody>
        <tr style="width : 120% ; ">

                <!--<th>LEAVEPOLICY_GROUP_NAME</th>--> 


            <th style="width:101%" > <b><?php
        echo isset($value['summary']['0']['ed']['first_name']) ? $value['summary']['0']['ed']['first_name'] : '';
        echo ' ';
        echo isset($value['summary']['0']['ed']['last_name']) ? $value['summary']['0']['ed']['last_name'] : '';
        ?></b> - <?php echo isset($value['empdet']['0']['dd']['desig_name']) ? $value['empdet']['0']['dd']['desig_name'] : ''; ?>  -  <?php echo isset($value['summary']['0']['br']['branch_name']) ? $value['summary']['0']['br']['branch_name'] : ''; ?></th>
            
            

<!--                                              <th>Leave days</th>
            <th>Holidays</th>-->

        </tr>
    </tbody>
</table>
<table class="table table-bordered" align="center" style="margin-tosp: 10px; ">
    <tbody>
        <tr style="width : 120% ; ">

                <!--<th>LEAVEPOLICY_GROUP_NAME</th>--> 


            <th style="width:51%; border-bottom: 0px solid white ; border-right: 0px solid white ; " colspan="2" ></th>
            <th style="width:50%; border-bottom:  0px solid white ; border-left: 0px solid white ; " colspan="2" > </th>

                                              <!--<th>Leave days</th>-->
            <!--<th>Holidays</th>-->

        </tr>
        <tr>
            <th style="border-right-style: hidden; border-right: 0px solid white; border-bottom: 0px solid white ;  "><span style="text-align:  left ; ">Employee ID   </span></th>
            <th style="border-right-style: hidden; border-right: 0px solid white; border-bottom: 0px solid white ;  "><span style="text-align: right ; word-wrap: break-word; ">:  <?php echo isset($value['empdet']['0']['ep']['emp_company_id']) ? $value['empdet']['0']['ep']['emp_company_id'] : ''; ?>  </span><span style="float: right ;"></span></th>
            <th style="border-right-style: hidden; border-right: 0px solid white; border-bottom: 0px solid white ;  "><span style="text-align:  left ; ">Date of Joining  </span></th>
            <th style="border-bottom: 0px solid white ; "><span style="text-align: right ;  border-bottom: 0px solid white ; ">: <?php
        
        echo isset($value['summary']['0']['ep']['joining_date']) ? $value['summary']['0']['ep']['joining_date'] : '';
        ?>  </span><span style="float: right ;"></span></th>
        </tr>
        <tr>
            <th style="border-right-style: hidden; border-bottom-style: hidden; border-right: 0px solid white; border-bottom: 0px solid white ;  "><span style="text-align:  left ; ">Department   </span></th>
            <th style="border-right-style: hidden; border-bottom-style: hidden; border-right: 0px solid white; border-bottom: 0px solid white ;  "><span style="text-align: right ; ">: <?php echo isset($value['empdet']['0']['d']['dept_name']) ? $value['empdet']['0']['d']['dept_name'] : ''; ?>  </span><span style="float: right ;"></span></th>
            <th style="border-right-style: hidden; border-bottom-style: hidden; border-right: 0px solid white; border-bottom: 0px solid white ;  "><span style="text-align:  left ; ">Total no of days   </span></th>
            <th style="border-bottom-style: hidden;  border-bottom: 0px solid white ; "><span style="text-align: right ; ">: <?php echo isset($value['empdet']['0']['payroll_master']['working_days']) ? $value['empdet']['0']['payroll_master']['working_days'] : ''; ?>   </span><span style="float: right ;"></span></th>
        </tr>
        <tr>
            <th style="border-right-style: hidden; border-right: 0px solid white; border-bottom: 0px solid white ;   "><span style="text-align:  left ; ">No of days paid   </span></th>
            <th style="text-align: left ;  border-right: 0px solid white; border-bottom: 0px solid white ; "><span style="text-align: left ;  border-right: 0px solid white; border-bottom: 0px solid white ; ">: <?php echo isset($value['empdet']['0']['payroll_master']['days_presant']) ? $value['empdet']['0']['payroll_master']['days_presant'] : ''; ?>   </span><span style="float: right ;"></span></th>
            <th style="border-right-style: hidden; border-bottom-style: hidden; border-right: 0px solid white; border-bottom: 0px solid white ;  "><span style="text-align:  left ; ">Lop Days   </span></th>
            <th style="border-bottom-style: hidden;  border-bottom: 0px solid white ; "><span style="text-align: right ; ">: <?php echo isset($value['empdet']['0']['payroll_master']['loss_of_pay']) ? $value['empdet']['0']['payroll_master']['loss_of_pay'] : ''; ?>   </span><span style="float: right ;"></span></th>
        </tr>
        <tr>
            <th style="border-right-style: hidden; border-right: 0px solid white; border-bottom: 0px solid white ;   "><span style="text-align:  left ; ">PF account No   </span></th>
            <th style="border-right-style: hidden; border-right: 0px solid white; border-bottom: 0px solid white ;   "><span style="text-align: right ; ">: <?php
        
        echo isset($value['summary']['0']['ed']['company_pf']) ? $value['summary']['0']['ed']['company_pf'] : '';
        ?>.  </span><span style="float: right ;"></span></th>
            <th style="border-right-style: hidden; border-right: 0px solid white; border-bottom: 0px solid white ;   "><span style="text-align:  left ; ">ESI No   </span></th>
            <th style=" border-bottom: 0px solid white ; "><span style="text-align: right ;  border-bottom: 0px solid white ; ">: <?php echo isset($value['summary']['0']['ed']['esi']) ? $value['summary']['0']['ed']['esi'] : ''; ?>   </span><span style="float: right ;"></span></th>
        </tr>
        <tr>
            <th style="border-right-style: hidden; border-right: 0px solid white; border-bottom: 0px solid white ;   "><span style="text-align:  left ; ">Bank Name   </span></th>
            <th style="border-right-style: hidden; border-right: 0px solid white; border-bottom: 0px solid white ;   "><span style="text-align: right ; ">: <?php
        
        echo isset($value['summary']['0']['ed']['bank_name']) ? $value['summary']['0']['ed']['bank_name'] : '';
        ?>.  </span><span style="float: right ;"></span></th>
            <th style="border-right-style: hidden; border-right: 0px solid white; border-bottom: 0px solid white ;   "><span style="text-align:  left ; ">Account Number   </span></th>
            <th style="border-right-style: hidden; border-bottom-style: hidden; border-bottom: 0px solid white ;  "><span style="text-align: right ; ">: <?php
        
        echo isset($value['summary']['0']['ed']['account_no']) ? $value['summary']['0']['ed']['account_no'] : '';
        ?>  </span><span style="float: right ;"></span></th>
        </tr>
        <tr>
            <th style="border-right-style: hidden; border-right: 0px solid white; border-bottom: 0px solid white ;   "><span style="text-align:  left ; ">IFSC Code   </span></th>
            <th style="border-right-style: hidden; border-right: 0px solid white; border-bottom: 0px solid white ;   "><span style="text-align: right ; ">: <?php
        
        echo isset($value['summary']['0']['ed']['ifsc_code']) ? $value['summary']['0']['ed']['ifsc_code'] : '';
        ?>.  </span><span style="float: right ;"></span></th>
            <th style="border-right-style: hidden; border-right: 0px solid white; border-bottom: 0px solid white ;   "><span style="text-align:  left ; ">Branch   </span></th>
            <th><span style="text-align: right ;  border-bottom: 0px solid white ; ">: <?php echo isset($value['summary']['0']['ed']['branch_name']) ? $value['summary']['0']['ed']['branch_name'] : ''; ?>   </span><span style="float: right ;"></span></th>
        </tr>
        
    </tbody>

    
</table>
<table class="table table-bordered" align="center">
    <tbody>
        <tr style="background: #cccccc ;width : 120% ;" >

                                    <!--    <th>LEAVEPOLICY_GROUP_NAME</th> -->


                                    <th style="width:40%" >Earnings</th>
                                    <th style="width:10%" >Amount</th>
                                    <th style="width:41%" >Deductions</th>

                                                                      <!--<th>Leave days</th>-->
                                    <th style="width:10%" >Amount</th>

                                </tr>
                    <?php $arr_data = $value['summary'];
                    $arr_withoutComponents = $value['withoutcomponent'];
                    ?>
                    <?php
                    if (count($arr_data) >= 0) {
                        $countss = count($arr_data);
                        if(count($arr_data) < count($arr_withoutComponents)){
                            $countss = count($arr_withoutComponents);
                        }
                        $sum = 0;
                        $tot = 0;
                        $dd = 0;
                        $net = 0;
                        ?>
                        <?php for ($i = 0; $i < $countss; $i++) {
                            ?>
                            <tr> <?php
                                $sum += isset($arr_data[$i]['ectc']['salary_amount'])?$arr_data[$i]['ectc']['salary_amount']:0;
                                    if(isset($arr_withoutComponents[$i]['ectc']))
                                    $dd += isset($arr_withoutComponents[$i]['ectc']['salary_amount'])?$arr_withoutComponents[$i]['ectc']['salary_amount']:0;
                                ?>
                                <td><?php echo isset($arr_data[$i]['ectc']['salary_head_item_desc'])?$arr_data[$i]['ectc']['salary_head_item_desc']:''; ?></td>
                                <!--<td><?php echo isset($arr_data[$i]['ectc']['structure_det_value'])?round($arr_data[$i]['ectc']['structure_det_value'], 2):''; ?></td>-->
                                <td><?php echo isset($arr_data[$i]['ectc']['salary_amount'])?abs(round($arr_data[$i]['ectc']['salary_amount'], 2)):''; ?></td>
                                <td><?php echo isset($arr_withoutComponents[$i]['ectc']['salary_head_item_desc']) ? $arr_withoutComponents[$i]['ectc']['salary_head_item_desc'] : ''; ?></td>
                                <!--<td><?php echo round(isset($arr_withoutComponents[$i]['ectc']['structure_det_value']) ? $arr_withoutComponents[$i]['ectc']['structure_det_value'] : '', 2); ?></td>-->
                                <td><?php echo round(isset($arr_withoutComponents[$i]['ectc']['salary_amount']) ? abs($arr_withoutComponents[$i]['ectc']['salary_amount']) : '', 2); ?></td>

                            </tr>

                <?php } ?>
                        <tr style="background: #cccccc ;" >
                            <th style="text-align :center ; ">Total Earnings</th><th><?php echo abs($sum); ?></th><th>Total Deductions </th><th><?php echo abs($dd); ?></th>
                        </tr>
                        <tr style="background: #cccccc ;" >
                            <th style="text-align :center ; " colspan="3">Net Pay</th><th><?php echo $sum+$dd; ?></th>
                        </tr>
                        <?php $arr_withoutComponents = $value['withoutcomponent']; ?>

                <?php if (count($arr_withoutComponents) > 0) { ?>



                        <?php } ?>
                    <?php } else {
                        ?>
                        <tr>
                            <td colspan="4">No Components found under this data</td>
                        </tr>  
            <?php } ?>   

                </tbody>
</table>
<p style="padding-left: 20px; ">*This is a System generated pay slip and does not require signature.</p>
<?php } ?>