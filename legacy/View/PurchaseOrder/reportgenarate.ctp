<div class="modal-dialog" style="width: 1200px ; ">
    <!-- Modal content-->
    <div class="modal-content">
        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal">&times;</button>
            <h4 class="modal-title"><?php //debug($purchse);  ?></h4><?php //debug($arr_att);   ?>
        </div>
        <div class="modal-body">
              <?php foreach ($purchse as $value) { ?>
            <table class="table table-bordered">
                <thead>
                    <tr style="background-color:#d9d9d9;">
                        <th>Po Number</th> 
                        <th>Po Date</th>
                        <th>Location</th>
                        <th>Supplier Name</th>
                        <th>Expected Date</th>
                    </tr>
                 <?php 
                   echo  '<tr>';
                   echo  '<td>'.$value["purchase_details"]["po_number"].'</th>';
                   echo  '<td>'.$value["purchase_details"]["po_date"].'</th>';
                   echo  '<td>'.$value["purchase_details"]["location"].'</th>';
                   echo  '<td>'.$value["purchase_details"]["supplier_name"].'</th>';
                   echo  '<td>'.$value["purchase_details"]["expected_date"].'</th>';
                   echo '</tr>';
                ?>
                    <tr style="background-coolr:#f0f0ff;">
                        <th>Mr Number</th> 
                        <th>Mr date</th>
                        <th>Store code</th>
                        <th>Client Name</th>
                        <th>Site Name</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                        echo '<tr>';
                        echo '<td>' . $value["material"]["mr_code"] . '</td>';
                        echo '<td>' . $value["material"]["mr_date"] . '</td>';
                        echo '<td>' . $value["store_details"] . '</td>';
                        echo '<td>' . $value["material"]["customer_name"] . '</td>';
                        echo '<td>' . $value["material"]["location"] . '</td>';
                        '<tr>';
                        '</tr>';
                    ?>
                    <tr style="background-color:#eaf9ff;">
                       <th>Sl No</th>
                       <th colspan="2">Item Name</th>
                       <th >Required Qty</th>
                       <th colspan="2">Ordering Qty</th>
                   </tr>
                       <?php
                       $i = 1;
                       $sit1 = $value['material_details'];
                       foreach ($sit1 as $value) {
                       ?>
                   <tr>
                       <td><?php echo $i; ?></td>
                       <td colspan="2"><?php echo $value['itemname']; ?></td>
                       <td ><?php echo $value['required_qty']; ?></td>
                       <td colspan="2"><?php echo $value['ordering_qty']; ?></td>
                   </tr>    
                       <?php
                       $i++;
                       }
                       ?>
               </tbody>
            </table>
              <?php }
              ?>
        </div>
    </div>
</div>