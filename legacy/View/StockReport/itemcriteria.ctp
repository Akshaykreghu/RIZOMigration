<!--<div class="col-md-4" >
<select id="select-store" name="select-store" class="form-control" onchange="selectItem();" >
                <option value="">--Choose Store--</option>
                <?php
               // foreach ($arr_branches as $key => $value) {
                    //echo '<option value="' . $value['Store']['store_master_pkey'] . '">' . $value['Store']['store_location'] . '</option>';
                //}
                ?>
</select>
</div>-->
<div class="col-md-12" >
          <select id="items-criteria" name="items-criteria" class="form-control js-example-basic-single" value="" >
              <option>Choose Item</option>  
               <?php 
                    foreach ($arr_itemlist as $key => $value) {
                    echo '<option value="' . $value['stock_details_view']['item_master_pkey'] . '">' . $value['stock_details_view']['item_desc'] . '</option>';
                    }
                    ?>
           </select>  
        </div>
<script>
//    function selectItem_name(){
//    var criteria = $('#items-criteria').val();
//    alert(criteria);
//   // $('#items-criteria1').load(livesite+'StockReport/itemlist/'+criteria);
//    }
     //load item names from selected store
    function  selectItem() {
        var from_store_name = $("#select-store").val();
      //alert(from_store_name);
          $("#items-criteria1").select2(
                {
                    //closeOnSelect:false,
                    placeholder: "Select Item",
                    allowClear: true,
                    ajax: {
                        url: livesite + "StockReport/itemlist/" + from_store_name,
                        dataType: 'json',
                        delay: 250,
                        data: function (params) {
                             
                            return {
                                q: params.term, // search term
                                page: params.page
                            };
                        },
                        processResults: function (data, params) {
                            // parse the results into the format expected by Select2
                            // since we are using custom formatting functions we do not need to
                            // alter the remote JSON data, except to indicate that infinite
                            // scrolling can be used
                            params.page = params.page || 1;
                            return {
                                results: data.items,
                                pagination: {
                                    more: (params.page * 30) < data.total_count
                                }
                            };
                        }
                    },
                    escapeMarkup: function (markup) {
                        return markup;
                    }
                });
    }
    </script>