<script>
  

</script>
<div class="table-responsive"   style="padding: 18px 65px 23px 19px; margin-top: -45px; " >
 
  

             
    <table class="table table-bordered">
    
     <tbody>
                                      <tr>
                                        <td><input type = "text"  name = "sr_no[]" id ="sr_no" style="width:40px;" ></td>
                                        <td>
                                        
                                            <select id="item_data" name="item_code[]" onchange="getitemfordata();" style = "width:100%;">
                                      <option value="" >-select-</option>
                                   <?php
//debug($all_item);
                                      foreach ($all_item as $value) {
                                         
                                          //$selected = ($arr_att['0']['poitemorder']['store_code'] == $value['Store']['store_master_pkey']) ? 'selected="selected"' : '';
                                          echo '<option value="' . $value['Item']['item_master_pkey'] . '" ' . $selected . '>'.$value['Item']['item_desc'] .'   </option>';
                                      }
                                      ?>
                                  </select>
                                           
                                         </td>
                                        <td><input type = "text"  name ="item_description[]" id = "current_stock" style = "width:100%;"></td>
                                        <td><input type = "text"  name = "uom[]" id = "incoming_qty"  style="width:60px" ></td>
<!--                                        <td><input type = "text"  name = "mr_code[]" id = "mr_code" ></td>-->
<!--                                        <td><input type = "text"  name = "store_code[]" id = "re_order_level"style = "width:70px" ></td>-->
                                        <td><input type = "text"  name = "requested_qty[]" id ="requirall" style = "width:100px" readonly="readonly">
                                        <td><input type = "text"  name = "ordering_qty[]" id = "unit" style = "width: 100px"></td>
                                        <td><input type = "text"  name = "po_rate[]" id = "unit" style = "width: 100px"></td>
                                        <td><input type = "text"  name = "po_value[]" id = "unit" style = "width: 100px"></td>
                                        <td><input type = "text"  name = "fc_po_value[]" id = "unit" style = "width: 100px"></td>
                                        <td><input type = "hidden"  name = "" id ="" style = "width:40px" ></td>
                                    </tr>
                                </tbody>              
  </table>
     <div class="table-responsive" style="padding:0px 3px -1px 19px; margin-top:-20px;" id="getmeteriall"  style="display:">
                           
                               
                        </div>
</div>

<script>
 

function getitemfordata(){
            var Item_code = $('#item_data').val();
            $('#getmeterial').show();
           //alert(Item_code);
        var request = $.ajax({
            url: livesite+"PurchaseOrder/getitem/"+Item_code,
            method: "POST",
            dataType: "html"
        });
        request.done(function( msg ) {
          //alert(msg);
            var data=msg;
            var div_data1 = '';
            div_data1 += "<div>"+data+"</div>"; 
        $("#getmeteriall").html(div_data1);
         getall(Item_code);
     });
        request.fail(function( jqXHR, textStatus ) {
            alert( "Request failed: " + textStatus );
        });
    }
function getall(Item_code) {
      var request = $.ajax({
            url: livesite+"PurchaseOrder/itemtotel/"+Item_code,
            method: "POST"
        });
        request.done(function( msg ) {
$("#requirall").val(msg);
        });
 }  
  
  
</script>
                        
    
