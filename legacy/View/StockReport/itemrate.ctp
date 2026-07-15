<style>
    
    .table , td, th,tr {
        border-style: solid;
        border-color: #d4d4de;
        

    }
    .modal-content {
        width: 125%   !important;

    }
</style>
<?php // debug($arr_salary_for_template); ?>
<?php if( $mode == '' ){ ?>
<div class="modal-body" style="overflow-y:initial; padding-left:3%; padding-right:3%; padding-bottom:3%;" >
     <h3 align="center" style="font-weight:bold; font-size: 30px;">Item Rate Report </h3>
        <h4 align="center" style="font-weight:bold;">(<?php echo isset($user_id) ? "Report run by " . ($user_id) . " - " . $date_time : ''; ?>)</h4>        
    <div class="row">
        <div class="col-md-12">
                <?php
                $i = 0;
                if (count($arr_itemsummary_for_template) == 0) {
                    echo "<h2>No Data Available With The Selected Criteria</h2>";
                } else {
                foreach ($arr_itemsummary_for_template as $category_name => $itemsummary) {
                        if(count($itemsummary) !== 0){
                   $i += 1; ?>
            <fieldset><legend> <?php echo isset($category_name) ? $category_name : '';?> </legend></fieldset>
                    <fieldset>
                        <table class="table ">
                            <thead>
                                <tr>
                                <th>SI No</th>
                                <th>Item Name</th>
                                <th>Item code</th>
                                <th>Item Category</th>
                                <th>Unit Cost</th>
                                <th>Sales Price</th>
                                <th>Created Person</th>
                                <th>Creation Date</th>
                                <th>Modified Person</th>
                                <th>Modification Date</th>
                                </tr>
                             </thead> 
                               <tbody>
                               <?php  $arr_data = $itemsummary['itemlists'];
                                 if (count($arr_data) > 0) {
                                  $tot = 0;
                                  $i = 1;
                            foreach ($arr_data as $val) {
                                $slno = $i;
                                $itemname = isset($val['item_name'])?$val['item_name']:'' ;
                                $itemcode = isset($val['item_code'])?$val['item_code']:'';
                                $itemcategory = isset($val['item_category'])?$val['item_category']:'';
                                $unitcost = isset($val['unit_price'])?$val['unit_price']:'';
                                $salesprice = isset($val['sales_price'])?$val['sales_price']:'';
                                $createdperson = isset($val['created_person'])?$val['created_person']:'';
                                $creationdate = isset($val['created_time'])?$val['created_time']:'';
                                $modifiedperson = isset($val['modified_person'])?$val['modified_person']:'';
                                $modificationdate = isset($val['modified_by'])?$val['modified_by']:'';   ?>                                                                                                      
                                <tr>
                                    <td><?php echo $slno; ?></td>
                                    <td><?php echo $itemname; ?></td>
                                    <td><?php echo $itemcode; ?></td>
                                    <td><?php echo $itemcategory; ?></td>
                                    <td><?php echo $unitcost; ?></td>
                                    <td><?php echo $salesprice; ?></td>
                                    <td><?php echo $createdperson; ?></td>
                                    <td><?php echo $creationdate; ?></td>
                                    <td><?php echo $modifiedperson; ?></td>
                                    <td><?php echo $modificationdate; ?></td>
                                </tr>        
                              <?php $i++; } } ?>
                            </tbody>
                        </table>
                    </fieldset>    
                <?php } }}  ?> 
        </div>
    </div>  
    </div>
<?php  }else{ ?>
<?php //echo '<style>'.file_get_contents("css/pdfbootstrap.css").'</style>'; ?>
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
    td, th {
        text-align: left;
        padding: 8px;
        line-height: 1.42857143;
        vertical-align: top;
        border: 1px solid #B2B2B2;
    }
</style>

<?php
echo $this->element('reportadminheader',array(
'title'=>'Item Rate Report')); ?>
    <h4 style="text-align: center;"><?php echo isset($user_id) ? "Report run by " . ($user_id) . " - " . $date_time : ''; ?></h4>
    <?php
    $i = 0;
    if (count($arr_itemsummary_for_template) == 0) {
        echo "<h2>No Data Available With The Selected Criteria</h2> ";
    } else {
        foreach ($arr_itemsummary_for_template as $category_name => $itemsummary) {
            if (count($itemsummary) !== 0) {
                $i += 1;
                ?>   
                <h3 style="text-align: left;padding-bottom: 0px;padding-top: 10px;"><?php echo isset($category_name) ? $category_name : '';?>  </h3>
                <hr>
                <br>
                <table class="table" align="center">
                            <thead>
                              <tr>
                                <th>SI No</th>
                                <th>Item Name</th>
                                <th>Item code</th>
                                <th>Item Category</th>
                                <th>Unit Cost</th>
                                <th>Sales Price</th>
                                <th>Created Person</th>
                                <th>Creation Date</th>
                                <th>Modified Person</th>
                                <th>Modification Date</th>
<!--                                  <th style="width: 13%;">Sl No</th>-->
                              </tr>
                            </thead>
                            <tbody>
                                 <?php $arr_data = $itemsummary['itemlists'];
                                 if (count($arr_data) > 0) {
                                  $tot = 0;
                                  $i = 1;
                                foreach ($arr_data as $val) {
                                $slno = $i;
                                $itemname = isset($val['item_name'])?$val['item_name']:'' ;
                                $itemcode = isset($val['item_code'])?$val['item_code']:'';
                                $itemcategory = isset($val['item_category'])?$val['item_category']:'';
                                $unitcost = isset($val['unit_price'])?$val['unit_price']:'';
                                $salesprice = isset($val['sales_price'])?$val['sales_price']:'';
                                $createdperson = isset($val['created_person'])?$val['created_person']:'';
                                $creationdate = isset($val['created_time'])?$val['created_time']:'';
                                $modifiedperson = isset($val['modified_person'])?$val['modified_person']:'';
                                $modificationdate = isset($val['modified_by'])?$val['modified_by']:'';    ?>
																  
                                <tr>
                                    <td><?php echo $slno; ?></td>
                                    <td><?php echo $itemname; ?></td>
                                    <td><?php echo $itemcode; ?></td>
                                    <td><?php echo $itemcategory; ?></td>
                                    <td><?php echo $unitcost; ?></td>
                                    <td><?php echo $salesprice; ?></td>
                                    <td><?php echo $createdperson; ?></td>
                                    <td><?php echo $creationdate; ?></td>
                                    <td><?php echo $modifiedperson; ?></td>
                                    <td><?php echo $modificationdate; ?></td>
                                </tr>
                                <?php $i++; } } ?>
                            </tbody>
                        </table>
                    <br>
                    <?php } } } ?> 
          
<?php } ?>