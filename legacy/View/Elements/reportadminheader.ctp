
<page backtop="60mm" backbottom="30mm" backleft="10mm" backright="10mm" style="font-size: 12pt">
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
<!--                    <div style=" text-align: center;">
                        <p>FORM XIII –See Rules 29(2)</p>
                    </div>-->
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
        <h3 style="text-align: center;padding-bottom: 20px;padding-top: 10px;"><?php echo $title; ?></h3>
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