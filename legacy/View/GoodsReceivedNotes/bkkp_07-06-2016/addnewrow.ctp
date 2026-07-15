<script>
  

</script>
<div class="table-responsive" style="padding: 0px 58px 23px 19px; margin-top: -45px; " >
 
  

             
    <table class="table table-bordered">
    
     <tbody>
                                      <tr>
                                        <td><input type = "text"  name = "sr_no[]" id ="sr_no" style = "width:40px" ></td>
                                        <td>
                                        
                                         <select id="new_cod" name="item_code[]" onchange="getitemfordata();" style = "width:120px">
                                      <option value="" >-select-</option>   
                                  <?php
                                      foreach ($all_item as $value) {
                                          //$selected = ($arr_att['0']['poitemorder']['store_code'] == $value['Store']['store_master_pkey']) ? 'selected="selected"' : '';
                                          echo '<option value="' . $value['Item']['item_master_pkey'] . '" ' . $selected . '>'.$value['Item']['item_desc'] .'   </option>';
                                      }
                                      ?>
                                  </select>
                                           
                                         </td>
                                        <td><input type = "text"  name ="item_description[]" id = "current_stock" style = "width:100px"></td>
                                        <td><input type = "text"  name = "uom[]" id = "incoming_qty"  style="width:40px" ></td>
                                        <td><input type = "text"  name = "mr_code[]" id = "mr_code" ></td>
                                        <td><input type = "text"  name = "store_code[]" id = "re_order_level"style = "width:70px" ></td>
                                        <td><input type = "text"  name = "requested_qty[]" id = "item_description" style = "width:70px"></td>
                                        <td><input type = "text"  name = "ordered_qty[]" id = "new_package" style = "width:70px" ></td>
                                        <td><input type = "text"  name = "ordering_qty[]" id = "unit" style = "width: 60px"></td>
                                        <td><input type = "text"  name = "po_rate[]" id = "unit" style = "width: 60px"></td>
                                        <td><input type = "text"  name = "po_value[]" id = "unit" style = "width: 60px"></td>
                                        <td><input type = "text"  name = "fc_po_value[]" id = "unit" style = "width: 60px"></td>
                                        <td><input type = "hidden"  name = "" id ="" style = "width:40px" ></td>
                                    </tr>
                                </tbody>              
  </table>
</div>

<script>
  function getitemfordata(){
            var Item_code = $('#new_cod').val();
          //  alert(Item_code);
        var request = $.ajax({
            url: "PurchaseOrder/getitem/"+Item_code,
            method: "POST",
            dataType: "html"
        });

        request.done(function( msg ) {
         //   alert(msg);
            $('#new_package').val(msg);          
        });
        
        request.fail(function( jqXHR, textStatus ) {
            
            alert( "Request failed: " + textStatus );
        });
    }
</script>
                        
    
