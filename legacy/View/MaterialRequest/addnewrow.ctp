<script>
  

</script>
<div class="table-responsive" style="padding: 0px 43px 6px 19px; margin-top: -45px; " >
 
  

             
    <table class="table table-bordered">
    
     <tbody>
                                    <tr>
                                        <td >
                                            
                                            <select id="" name="item_code[]" style ="width:135px" required="reduired">
                                      <option value="" >-select-</option>   
                                  <?php
                                      foreach ($all_item as $value) {
                                          //$selected = ($arr_att['0']['poitemorder']['store_code'] == $value['Store']['store_master_pkey']) ? 'selected="selected"' : '';
                                          echo '<option value="' . $value['Item']['item_master_pkey'] . '" ' . $selected . '>'.$value['Item']['item_desc'] .'   </option>';
                                      }
                                      ?>
                                  </select></td>
                                        <td><input type = "text"  name = "required_qty[]" id = "required_qty" style ="width: 63px;" required="reduired"></td>
                                        <td><input type = "text"  name = "current_stock[]" id = "current_stock" style = "width: 54px;"></td>
                                        <td><input type = "text"  name = "incoming_qty[]" id = "incoming_qty" style = "width: 66px;"></td>
                                        <td><input type = "text"  name = "incoming_date[]" id = "strt_date" placeholder = "select date" class="datepicker" data-date-format="yyyy-mm-dd"></td>
                                        <td><input type = "text" name = "re_order_level[]" id = "re_order_level" ></td>
                                        <td><input type = "text"  name = "item_description[]" id = "item_description"></td>
                                        <td><input type = "text"  name = "package[]" id = "package" ></td>
                                       
                                    </tr>
                                </tbody>              
  </table>
</div>

    
                        
    
<script>
      $(document).ready(function(){
        $('#strt_date').datepicker({
            dateFormat:'yyyy-mm-dd'
        });
        
    });
</script>