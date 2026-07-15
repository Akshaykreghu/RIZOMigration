<div class="modal-dialog" style="width: 1200px ; ">
    <!-- Modal content-->
    <div class="modal-content">
        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal">&times;</button>
            <h4 class="modal-title"><?php //debug($site);  ?></h4><?php //debug($arr_att);   ?>
        </div>
        <div class="modal-body">
              <?php foreach ($site as $value) { ?>
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>Mr No</th> 
                        <th>Mr date</th>
                        <th>Store code</th>
                        <th>Client Name</th>
                        <th>Site Name</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                        echo '<tr>';
                        echo '<td>' . $value["material_master"]["mr_code"] . '</td>';
                        echo '<td>' . $value["material_master"]["mr_date"] . '</td>';
                        echo '<td>' . $value["store_details"] . '</td>';
                        echo '<td>' . $value["material_master"]["customer_name"] . '</td>';
                        echo '<td>' . $value["material_master"]["location"] . '</td>';
                        '<tr>';
                        '</tr>';
                    ?>
                   <tr>
                       <th>Sl No</th>
                       <th colspan="2"> Item Name</th>
                       <th colspan="2"> Required Qty</th>
                   </tr>
                       <?php
                       $i = 1;
                       $sit1 = $value['material_details'];
                       foreach ($sit1 as $value) {
                       ?>
                   <tr>
                       <td><?php echo $i; ?></td>
                       <td colspan="2"><?php echo $value['itemname']; ?></td>
                       <td colspan="2"><?php echo $value['required_qty']; ?></td>
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