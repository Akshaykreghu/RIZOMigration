<script>
  

</script>
<div class="table-responsive" style="padding: 0px 43px 6px 22px; margin-top: -45px; " >
 
  

             
    <table class="table table-bordered">
    
     <tbody>
                                    <tr>
                                        <td><input type = "text"  name = "item_no[]" id = "item_no" style = "width: 60px" ></td>
                                        <td><input type = "text"  name = "required_qty[]" id = "required_qty" style = "width: 60px"></td>
                                        <td><input type = "text"  name = "current_stock[]" id = "current_stock" ></td>
                                        <td><input type = "text"  name = "incoming_qty[]" id = "incoming_qty" ></td>
                                        <td><input type = "text"  name = "incoming_date[]" id = "strt_date" placeholder = "select date"></td>
                                        <td><input type = "text" name = "re_order_level[]" id = "re_order_level" ></td>
                                        <td><input type = "text"  name = "item_description[]" id = "item_description"></td>
                                        <td><input type = "text"  name = "package[]" id = "package" ></td>
                                        <td><input type = "text"  name = "unit[]" id = "unit" style = "width: 60px"></td>
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