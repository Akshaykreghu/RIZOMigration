<style>
    .form-horizontal .control-label{

        text-align: left;

    }
    .modal{
        margin-left: 10%;
        width: 80%; 
            position: relative;
    background-color: #fff;
    }
    .skin-blue .content-header {
    background: transparent;
}
.content-header {
    position: relative;
    padding: 15px 15px 0 15px;
}
.content {
    min-height: 250px;
    padding: 15px;
    margin-right: auto;
    margin-left: auto;
    padding-left: 15px;
    padding-right: 15px;
}
.row {
    margin-right: -15px;
    margin-left: -15px;
}

.col-md-12 {
    width: 100%;
        float: left;
            position: relative;
    min-height: 1px;
    padding-right: 15px;
    padding-left: 15px;
}
.box {
    position: relative;
    border-radius: 3px;
    background: #ffffff;
    border-top: 3px solid #d2d6de;
    margin-bottom: 20px;
    width: 100%;
    box-shadow: 0 1px 1px rgba(0,0,0,0.1);
}
legend {
    display: block;
    width: 100%;
    padding: 0;
    margin-bottom: 20px;
    font-size: 21px;
    line-height: inherit;
    color: #333;
    border: 0;
    border-bottom: 1px solid #e5e5e5;
}
blockquote, pre, a, abbr, acronym, address, code, del, dfn, em, img, q, dl, dt, dd, ol, ul, li, article, aside, hgroup, nav {
    margin: 0;
    padding: 0;
    border: 0;
    font-weight: inherit;
    font-style: inherit;
    font-family: inherit;
    vertical-align: baseline;
    text-decoration: none;
    list-style: none;
}
.col-md-6 {
    width: 50%;
     float: left;
}


.table {
    width: 100%;
    max-width: 100%;
    margin-bottom: 20px;
}
table {
    background-color: transparent;
}
thead {
    display: table-header-group;
    vertical-align: middle;
    border-color: inherit;
}
tr {
    display: table-row;
    vertical-align: inherit;
    border-color: inherit;
}

table {
    border-spacing: 0;
    border-collapse: collapse;
}
thead {
    display: table-header-group;
    vertical-align: middle;
    border-color: inherit;
}
tbody {
    display: table-row-group;
    vertical-align: middle;
    border-color: inherit;
}.table>tbody>tr>td, .table>tbody>tr>th, .table>tfoot>tr>td, .table>tfoot>tr>th, .table>thead>tr>td, .table>thead>tr>th {
    padding: 8px;
    line-height: 1.42857143;
    vertical-align: top;
    border-top: 1px solid #ddd;
}
.table-striped>tbody>tr:nth-of-type(odd) {
    background-color: #f9f9f9;
}
.table>thead>tr>th, .table>tbody>tr>th, .table>tfoot>tr>th, .table>thead>tr>td, .table>tbody>tr>td, .table>tfoot>tr>td {
    border-top: 1px solid #f4f4f4;
}
td, th {
    display: table-cell;
    vertical-align: inherit;
}


legend {
    display: block;
    width: 100%;
    padding: 0;
    margin-bottom: 20px;
    font-size: 21px;
    line-height: inherit;
    color: #333;
    border: 0;
    border-bottom: 1px solid #e5e5e5;
}
</style>
<div class="modal">
    
<section class="content-header">
    <h1 style="text-align:center; font-size: 2em;"> Purchase Order <div class="pull-right">&nbsp;</div></h1>
</section>
<!-- Main content -->
<input type="hidden" value="<?php echo isset($po_pkey)?$po_pkey:'0'; ?>" id="loan_pkey">
<section class="content">
    <div class="row">
        <div class="col-md-12">
            
            <!-- DIRECT CHAT DANGER -->
            <div class="box ">
                <legend style="text-align: center; "><b>Purchase Order</b></legend>
                <!--<legend  style="background: #1464ab;color: white;padding: 8px 1px 9px 16px;">Company Details</legend>-->
                <div style="text-align:right; width:100%">

                    <?php echo date("l,F j, Y"); ?> </div>
                <div style="text-align:center; width:100%">
                    <?php if (isset($arr_comp_contact_info['CompanyContactInfo']['logo']) && !empty($arr_comp_contact_info['CompanyContactInfo']['logo'])) { ?> 
                    <img style="width:auto; " src="http://<?php echo $_SERVER['HTTP_HOST'] . $this->webroot . $arr_comp_contact_info['CompanyContactInfo']['logo']; ?>" height="50" width="70" class="img-circle" alt="Company Logo" />
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
                
                <div class="col-md-12">
                    <div class="col-md-6">
                        <h3 style="background: #1464ab;color: white;padding: 8px 1px 9px 16px;">Supplier</h3>
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <td><?php echo isset($arr_po_details['0']['Contacts']['company_name']) ? $arr_po_details['0']['Contacts']['company_name'] : ''; ?></td>
                                    
                                </tr>
                                <tr>
                                    <td><?php echo isset($arr_po_details['0']['Contacts']['first_name']) ? $arr_po_details['0']['Contacts']['first_name'] : ''; ?></td>
                                    
                                </tr>
                                <tr>
                                    <td><?php echo isset($arr_po_details['0']['Contacts']['address']) ? $arr_po_details['0']['Contacts']['address'] : ''; ?></td>
                                    
                                </tr>
                                <tr>
                                    <td><?php echo isset($arr_po_details['0']['Contacts']['city']) ? $arr_po_details['0']['Contacts']['city'] : ''; ?></td>
                                    
                                </tr>
                                <tr>
                                    <td><?php echo isset($arr_po_details['0']['Contacts']['c_mob_no']) ? $arr_po_details['0']['Contacts']['c_mob_no'] : ''; ?></td>
                                    
                                </tr>
                                <tr>
                                    <td><?php echo isset($arr_po_details['0']['Contacts']['c_email']) ? $arr_po_details['0']['Contacts']['c_email'] : ''; ?></td>
                                    
                                </tr>
                            </thead>
                            <tbody>
                            </tbody>
                        </table>
                    </div>

                    <div class="col-md-6">
                        <h3 style="background: #1464ab;color: white;padding: 8px 1px 9px 16px;">Ship To</h3>
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <td><?php echo isset($arr_po_details['0']['Location']['location_name']) ? $arr_po_details['0']['Location']['location_name'] : ''; ?></td>
                                    
                                </tr>
                                <tr>
                                    <td><?php echo isset($arr_po_details['0']['Location']['address']) ? $arr_po_details['0']['Location']['address'] : ''; ?></td>
                                    
                                </tr>
                                <tr>
                                    <td><?php echo isset($arr_po_details['0']['Location']['city']) ? $arr_po_details['0']['Location']['city'] : ''; ?></td>
                                    
                                </tr>
                                <tr>
                                    <td><?php echo isset($arr_po_details['0']['Location']['state']) ? $arr_po_details['0']['Location']['state'] : ''; ?></td>
                                    
                                </tr>
                                <tr>
                                    <td><?php echo isset($arr_po_details['0']['Location']['organization_id']) ? $arr_po_details['0']['Location']['organization_id'] : ''; ?></td>
                                    
                                </tr>
                                <tr>
                                    <td><?php echo isset($arr_po_details['0']['Location']['pincode']) ? $arr_po_details['0']['Location']['pincode'] : ''; ?></td>
                                    
                                </tr>
                            </thead>
                            <tbody>
                            </tbody>
                        </table>
                    </div>
                </div>
                
                <div class="col-md-12">
                    <legend>PO Details</legend>
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>PO Number</th>
                                <th>Po Date</th>
                                <th>Expected Date</th>
                                <th>Location</th>
                                <th>Supplier</th>
                                <th>Remarks</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td><?php echo $arr_po_details['0']['PurchaseOrder']['po_number']; ?></td>
                                <td><?php echo $arr_po_details['0']['PurchaseOrder']['po_date']; ?></td>
                                <td><?php echo $arr_po_details['0']['PurchaseOrder']['expected_date']; ?></td>
                                <td><?php echo $arr_po_details['0']['PurchaseOrder']['location']; ?></td>
                                <td><?php echo $arr_po_details['0']['PurchaseOrder']['supplier_name']; ?></td>
                                <td><?php echo $arr_po_details['0']['PurchaseOrder']['remarks']; ?></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <div class="spacer-20"></div>
                <div class="col-md-12">
                        <legend>Order Details</legend>
                    <div class="box-body">
                    <table class="table table-bordered">
                        <thead>
                            <tr style="background: #1464ab;color: white;padding: 8px 1px 9px 16px;">
                                <th>SL No </th>
                                <th>Item</th>
                                <th>Details</th>
                                <th>Qty</th>
                                <th>Value</th>
                                <th>Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $i = 1;
                            $total_amt = 0;
                            foreach($arr_find_meterial as $key => $vals){ 
                                $qty = 0;
                                $rate = 0;
                                $data = current($vals);
                                foreach ($vals as $itmes){
                                  $qty += $itmes['po']['ordering_qty']; 
                                  $rate += $itmes['po']['ordering_qty'] * $itmes['po']['po_rate'];
                                }
                                ?>
                                <tr>
                                    <td><?php echo $i; ?></td>
                                    <td><?php echo $data['im']['item_desc']; ?></td>
                                    <td>
                                        <table class="table table-striped">
                                            <thead>
                                            <th>Mr Code</th>
                                            <th>Req Qty</th>
                                            <th>Ordering Qty</th>
                                            <th>Rate</th>
                                            <th>Total</th>
                                            </thead>
                                            <?php foreach($vals as $key => $itms){ ?>
                                            <tr>
                                                <td><?php echo $itms['mr']['mr_code']; ?></td>
                                                <td><?php echo $itms['po']['required_qty']; ?></td>
                                                <td><?php echo $itms['po']['ordering_qty']; ?></td>
                                                <td><?php echo $itms['po']['po_rate']; ?></td>
                                                <td><?php echo $itms['po']['po_rate'] * $itms['po']['ordering_qty']; ?></td>
                                            </tr>
                                            <?php } ?>
                                        </table>
                                    </td>
                                    <td><?php echo $qty; ?></td>
                                    <td></td>
                                    <td><?php echo $rate; ?></td>
                                </tr>
                                <?php
                                $total_amt += $rate;
                                $i += 1;
                            } ?>
                                <tr>
                                    <th colspan="5" style="text-align: right; ">Subtotal</th>
                                    <th><?php echo $total_amt; ?></th>
                                </tr>    
                        </tbody>
                    </table>
                    
                </div>
                </div>
                
            </div>
        </div>
    </div>
</section>

</div>
<!--<div class="row">
    <div class="form-group">
        <form id="form-showreport" method="post" action="" ></form>
        <div class="col-md-12" align="right">
            <a href="<?php echo $this->webroot; ?>PurchaseOrder/details/<?php echo isset($po_pkey)?$po_pkey:'0'; ?>" target="_blank" class="btn btn-default" onclick="downloadReport();" ><i class="icon-file"></i>Download As PDF</a>
            <a href="#" class="btn btn-default" onclick="downloadexcelReport();"><i class="icon-file"></i>Download As Excel</a>
        </div>
    </div>
</div>-->
