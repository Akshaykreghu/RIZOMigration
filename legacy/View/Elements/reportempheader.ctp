
<page backtop="60mm" backbottom=30mm" backleft="10mm" backright="10mm" style="font-size: 12pt">
    <page_header>


        <div style="text-align:right; width:100%">

            <?php echo date("l,F j, Y"); ?> </div>
        <div style="text-align:center; width:100%">
            <?php if (isset($arr_comp_contact_info['CompanyContactInfo']['logo']) && !empty($arr_comp_contact_info['CompanyContactInfo']['logo'])) { ?> 
                <img src="http://<?php echo $_SERVER['HTTP_HOST'] . $this->webroot . $arr_comp_contact_info['CompanyContactInfo']['logo']; ?>" height="50" width="70" class="img-circle" alt="Company Logo" />
            <?php } ?>
        </div>      
        <div style="text-align: center;font-weight: bold;"><?php echo $arr_comp_contact_info['CompanyContactInfo']['business_name']; ?>

        </div>
        <div style="text-align: center"><?php echo $arr_comp_contact_info['CompanyContactInfo']['address']; ?>

        </div>
        <div style="text-align: center"><?php echo $arr_comp_contact_info['CompanyContactInfo']['city']; ?>
            ,pin-<?php echo $arr_comp_contact_info['CompanyContactInfo']['pincode']; ?>
            ,<?php echo $arr_comp_contact_info['CompanyContactInfo']['state']; ?>
        </div> <div style="text-align: center"> <?php echo "Phone : " . $arr_comp_contact_info['CompanyContactInfo']['phone']; ?>
            <?php echo ' Fax : ' . $arr_comp_contact_info['CompanyContactInfo']['fax']; ?>
            <?php echo ' Email : ' . $arr_comp_contact_info['CompanyContactInfo']['email']; ?>
        </div>
        <table class="page_header">
            <tr style="border:none;">
                <td style="width: 100%; text-align: left;font-size: 15px; border:none;">
                    <?php echo $emp[0]['name'] . ' -' . $emp['designation']['desig_name']; ?>
                </td>
            </tr>
            <tr style="border:none;"> 
                <td style="width: 100%; text-align: left; border:none;">
                    <?php echo $emp['EmployeeDetails']['address']; ?></td>
            </tr> 
            <tr style="border:none;"> 
                <td style="width: 100%; text-align: left; border:none;">
                    <?php echo $emp['EmployeeDetails']['city']; ?></td>
            </tr>
            <tr style="border:none;">
                <td style="width: 100%; text-align: left; border:none">
                    <?php echo $emp['EmployeeDetails']['state'] . ' -' . $emp['EmployeeDetails']['pincode']; ?>
                </td>
            </tr>
        </table>
        <hr>

    </page_header>
    <page_footer>
        <table class="page_footer">
            <tr style="border:none;">
                <td style="width: 100%; text-align: right; border:none;">
                    page [[page_cu]]/[[page_nb]]
                </td>
            </tr>
        </table>
    </page_footer>
    <bookmark title="Sommaire" level="0" ></bookmark>
</page>