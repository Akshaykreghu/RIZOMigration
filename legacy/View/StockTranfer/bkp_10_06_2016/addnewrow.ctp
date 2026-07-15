<script>


</script>
<div class="table-responsive"   style="padding: 19px 20px 23px 19px; margin-top: -45px; " >




    <table class="table table-bordered">

        <tbody>
            <tr>
                                                <td>

                                                    <select id="item_code" name="item_code[]"  onchange="" style = "width:100%;">
                                                        <option value="" >-select-</option>
    <?php
//debug($all_item);
    foreach ($all_item as $value) {

        //$selected = ($arr_list['0']['directpod']['store_code'] == $value['Store']['store_master_pkey']) ? 'selected="selected"' : '';
        echo '<option value="' . $value['Item']['item_master_pkey'] . '" ' . $selected . '>' . $value['Item']['item_desc'] . '   </option>';
    }
    ?>
                                                    </select>

                                                </td>
                                                <td><input type = "text"  name ="item_desc[]" id = "current_stock" style = "width:100%;"></td>
                                                <td><input type = "text"  name = "package[]" id = "incoming_qty"  style="width:60px" ></td>
                                                <td><input type = "text"  name = "year_of_make[]" value="<?php //echo isset($value['0']['sumunit']) ; ?>"id ="add_date" style = "width:100px" >
                                                <td><input type = "text"  name = "thickness[]" id = "unit1" style = "width: 100px" ></td>
                                                <td><input type = "text"  name = "dimension[]" id = "unit" style = "width: 100px"></td>
                                                <td><input type = "text"  name = "density[]" id = "unit" style = "width: 100px"></td>
                                                <td><input type = "text"  name = "unit[]" id = "unit" style = "width: 100px"></td>
                                                <td><input type = "text"  name = "current_stock[]" id = "unit" style = "width: 100px"></td>
                                                 <td><input type = "text"  name = "tranfer_stock[]" id = "unit" style = "width: 100px"></td>
                                            </tr>
        </tbody>              
    </table>
    <div class="table-responsive" style="padding:0px 3px -1px 19px; margin-top:-20px;" id="getmeteriall<?php echo $rowIndex; ?>"  style="display:">


    </div>
</div>

<script>

//    function getitemfordata1(obj,rowIndex) {
//        var Item_code = $(obj).val();//$('#item_code').val();
//        $('#getmeterial').show();
//        //alert(Item_code);
//        var request = $.ajax({
//            url: "PurchaseOrder/getitem/" + Item_code + "/" + rowIndex,
//            method: "POST",
//            dataType: "html"
//        });
//        request.done(function (msg) {
//            //alert(msg);
//            var data = msg;
//            var div_data1 = '';
//            div_data1 += "<div>" + data + "</div>";
//            $("#getmeteriall"+rowIndex).html(div_data1);
//            getall(Item_code);
//        });
//        request.fail(function (jqXHR, textStatus) {
//            alert("Request failed: " + textStatus);
//        });
//    }
//    function getall(Item_code) {
//        var request = $.ajax({
//            url: "PurchaseOrder/itemtotel/" + Item_code,
//            method: "POST" 
//        });
//        request.done(function (msg) {
//            $("#requirall").val(msg);
//        });
//    }

 $(document).ready(function () {
        $('#add_date').datepicker({
            dateFormat: 'yyyy-mm-dd'
        });
    });


</script>