<style>
    .pull-left {
        float: right !important;
    }
</style>
<script>

    $.validate({
        form: '#form-stock-master'
    });
    var options = {
        success: function (resp) {
            $('#modalDiv').modal('hide');

            $('#stocktable').datagrid('reload');
            $.notify("Success", {type: 'success'});
        }  // post-submit callback
    };

    $('#form-stock-master').on('submit', function (event) {
        event.preventDefault();
        if (confirm(" Do You Want  To Save The Form")) {
            $('#form-stock-master').ajaxSubmit(options);


        }
    });



    function loadCriteriaItems(index) {
        var criteria = $('#select-criteria' + index).val();
        $('#hidden-criteria' + index).val(criteria);
        $('#div-items-criteria' + index).load(livesite + 'Reports/loadcriteriaitems/' + index + '/' + criteria);
    }
    function addOneReportCriteria() {
        var index = $('div[id^="div-criteria"]').length;
        console.log(index);
        $('#form-showreport .link-removecriterias').remove();
        var newIndex = index + 1;
        $('<div class="form-group" id="div-criteria' + newIndex + '"></div>').insertAfter($('#div-criteria' + index));
        $('#div-criteria' + newIndex).load('StockTranfer/addnewrow/'+newIndex);
    }

</script>

<div class="modal-dialog" style="width: 1200px ; ">

    <!-- Modal content-->
    <div class="modal-content">
        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal">&times;</button>
            <h4 class="modal-title"><?php echo $title; ?></h4><?php //debug($all_item);  ?>
        </div>
        <div class="modal-body">
            <!-- Form starts -->
            <div id="" class="">
                <form class="form-horizontal" id="form-stock-master" method="post" action="<?php echo $this->webroot; ?>StockTranfer/save" >
                    <div role="tabpanel" class="tab-pane" id="div-" name="">

                        <div class="form-group form-group-sm">
                            <div class="col-xs-4">
                                <label class="col-md-4 control-label" >Adjustment Code</label>
                                <div class="col-md-7">
                                    <input id="adjustment_code" name="adjustment_code" value="<?php echo isset($arr_list['0']['stock']['adjustment_code']) ? $arr_list['0']['stock']['adjustment_code'] : mt_rand(); ?>" type="text"  class="form-control input-md" >
                                </div>
                            </div>
                            <div class="col-xs-4">
                                <label  class="col-md-4 control-label" >Adjust Date<label style="color:red">*</label></label>
                                <div class="col-md-8">
                                    <input id="start_date" name="adjustment_date" value="<?php echo isset($arr_list['0']['stock']['adjustment_date']) ? $arr_list['0']['stock']['adjustment_date'] : ''; ?>" type="text"  class="form-control input-md" required="required" >
                                </div> 
                            </div>
                            <div class=col-xs-4">
                                <label  class="col-md-2 control-label" >From Store Code<label style="color:red;">*</label></label>
                                <div class="col-md-2">
                                    <select id="from_store" class="form-control input-md" name="from_store" required="required">
                                      <option value="" >---select---</option>
                                   <?php
                                      foreach ($all_store as $value) {
                                          $selected = ($arr_list['0']['stock']['from_store'] == $value['Store']['store_master_pkey']) ? 'selected="selected"' : '';
                                          echo '<option value="' . $value['Store']['store_master_pkey'] . '" ' . $selected . '>'.$value['Store']['store_code'] .'    </option>';
                                      }
                                      ?>
                                  </select>
<!--                                    <input id="from_store"  name="from_store" value="<?php // echo isset($arr_list['0']['stock']['from_store']) ? $arr_list['0']['stock']['from_store'] : ''; ?>" type="text"  class="form-control input-md" >-->
                                </div>
                            </div>
                        </div>

                        <div class="form-group form-group-sm">
                            <div class="col-xs-4">
                                <label class="col-md-4 control-label" >To Store Code<label style="color:red">*</label></label>
                                <div class="col-md-7">
                                    <select id="to_store" class="form-control input-md" name="to_store" required="required">
                                      <option value="" >---select---</option>
                                   <?php
                                      foreach ($all_store as $value) {
                                          $selected = ($arr_list['0']['stock']['to_store'] == $value['Store']['store_master_pkey']) ? 'selected="selected"' : '';
                                          echo '<option value="' . $value['Store']['store_master_pkey'] . '" ' . $selected . '>'.$value['Store']['store_code'] .'    </option>';
                                      }
                                      ?>
                                  </select>
<!--                                    <input id="to_store" name="to_store" value="<?php //echo isset($arr_list['0']['stock']['stock']) ? $arr_list['0']['stock']['to_store'] : ''; ?>" type="text"  class="form-control input-md" required="required">-->
                                </div>
                            </div>
                            <div class="col-xs-4">
                                <label  class="col-md-4 control-label" >Remark</label>
                                <div class="col-md-8">
                                    <input id="remarks" name="remarks" value="<?php echo isset($arr_list['0']['stock']['remarks']) ? $arr_list['0']['stock']['remarks'] : ''; ?>" type="text"  class="form-control input-md"  >
                                </div> 
                            </div>
                            </div>
                            
                            
                        </div>

                        
                        <div class="form-group form-group-sm">
                            <div class="col-md-20" > </div>
                        </div>

                        <div  style="border: 1px solid #DCDCDC" style="padding: 0px 20px 10px 19px;">
                            <div class="col-xs-4">
                            </div>
                            <div class="modal-title" style="font-family:verdana; color:black; size:12px">Stock Transfer Details</div>
                            <div class="form-group" id="div-criteria1" style="padding: 0px 20px 10px 19px;">
                                <div class="col-md-12" >
                                    <div class="pull-right ">
                                        <a class="btn btn-info btn-sm active"  onclick="addOneReportCriteria(1);">Add New</a>

                                    </div>

                                </div>
                                <div class="form-group form-group-sm">
                                    <div class="col-md-12" > </div>
                                </div>
                                <table class="table table-bordered">
                                    <thead>
                                        <tr>
                                            <th>Item Name<label style="color:red;">*</label></th> 
                                            <th>package</th>
                                            <th>year of make</th>
                                            <th>Thickness</th>
                                            <th>Dimension</th> 
                                            <th>Density</th>
                                            <th>Unit<label style="color:red;">*</label></th>
                                            <th>Stock</th>  
                                            <th>Transfer Stock</th>  




                                        </tr>
                                    </thead>

                                    <?php
                                    if (isset($arr_list) && !empty($arr_list)) {
                                        $i = 1;
                                      
                                        foreach ($arr_list as $value) {
                                            $data = count($value);
                                            //debug($value);
                                            ?>
                                            <tbody>
                                                <tr>
                                                    <td>
                                                        <select id="item_code"   name="item_code[]" style = "width:100%;" >
                                                            <option value="" >-select-</option>
                                                            <?php
                                                            
                                                            foreach ($all_item as $getitem) {
                                                                $selected = ($value['stockitem']['item_code'] == $getitem['Item']['item_master_pkey']) ? 'selected="selected"' : '';
                                                                echo '<option value="' . $getitem['Item']['item_master_pkey'] . '" ' . $selected . '>' . $getitem['Item']['item_desc'] . '   </option>';
                                                            }
                                                            ?>
                                                        </select>
                                                      
                                                    </td>

                                                    <td><input type = "text"  value = "<?php echo isset($value['stockitem']['package']) ? $value['stockitem']['package'] : ''; ?>"name = "package[]" id = "incoming_qty"  style="width:60px" ></td>
                                                    <td><input type = "text"  value = "<?php echo isset($value['stockitem']['year_of_make']) ? $value['stockitem']['year_of_make'] : ''; ?>"name = "year_of_make[]" id ="exp1_date" style = "width:100px"></td>
                                                    <td><input type = "text"  value = "<?php echo isset($value['stockitem']['thickness']) ? $value['stockitem']['thickness'] : ''; ?>" name = "thickness[]" id = "" style = "width: 100px" ></td>
                                                    <td><input type = "text"  value = "<?php echo isset($value['stockitem']['dimension']) ? $value['stockitem']['dimension'] : ''; ?>" name = "dimension[]" id = "unit" style = "width: 100px"></td>
                                                    <td><input type = "text"  value = "<?php echo isset($value['stockitem']['density']) ? $value['stockitem']['density'] : ''; ?>" name = "density[]" id = "unit" style = "width: 100px"></td>
                                                    <td><input type = "text"  value = "<?php echo isset($value['stockitem']['unit']) ? $value['stockitem']['unit'] : ''; ?>" name = "unit[]" id = "unit" style = "width: 100px"></td>
                                                    <td><input type = "text"  value = "<?php echo isset($value['stockitem']['current_stock']) ? $value['stockitem']['current_stock'] : ''; ?>" name = "current_stock[]" id = "current_stock" style = "width: 100px"></td>
                                                    <td><input type = "text"  value = "<?php echo isset($value['stockitem']['tranfer_stock']) ? $value['stockitem']['tranfer_stock'] : ''; ?>" name = "tranfer_stock[]" id = "tranfer_stock" style = "width: 100px"></td>

                                                     <input type = "hidden" value = "<?php echo isset($value['stockitem']['stock_item_pkey']) ? $value['stockitem']['stock_item_pkey'] : ''; ?>" id = "stock_item_pkey" name ="stock_item_pkey[]">
                                            </tr>
                                            </tbody> 
        <?php
        $i++;
    }
} else {
    ?>
                                        <tbody>
                                            <tr>
                                                <td>

                                                    <select id="item_code" name="item_code[]"  onchange="" style = "width:100%;">
                                                        <option value="" >-select-</option>
    <?php
//debug($all_item);
    foreach ($all_item as $value) {

        //$selected = ($arr_list['0']['stockitem']['store_code'] == $value['Store']['store_master_pkey']) ? 'selected="selected"' : '';
        echo '<option value="' . $value['Item']['item_master_pkey'] . '" ' . $selected . '>' . $value['Item']['item_desc'] . '   </option>';
    }
    ?>
                                                    </select>

                                                </td>
                                                <td><input type = "text"  name ="item_desc[]" id = "current_stock" style = "width:100%;"></td>
                                                <td><input type = "text"  name = "package[]" id = "incoming_qty"  style="width:60px" ></td>
                                                <td><input type = "text"  name = "year_of_make[]" value="<?php //echo isset($value['0']['sumunit']) ; ?>"id ="exp_date" style = "width:100px" >
                                                <td><input type = "text"  name = "thickness[]" id = "unit1" style = "width: 100px" ></td>
                                                <td><input type = "text"  name = "dimension[]" id = "unit" style = "width: 100px"></td>
                                                <td><input type = "text"  name = "density[]" id = "unit" style = "width: 100px"></td>
                                                <td><input type = "text"  name = "unit[]" id = "unit" style = "width: 100px"></td>
                                                <td><input type = "text"  name = "current_stock[]" id = "unit" style = "width: 100px"></td>
                                                 <td><input type = "text"  name = "tranfer_stock[]" id = "unit" style = "width: 100px"></td>
                                            </tr>

                                        </tbody>                      
<?php }
?>
                                </table>





                                <div class="table-responsive" style="padding:0px 3px -1px 19px; margin-top:-20px;" id="getmeterial"  style="display:">


                                </div>






                            </div>

                        </div>
                        <div class="modal-footer">
                            <input type="hidden" value="<?php echo isset($arr_list['0']['stock']['stock_tranfer_pkey']) ? $arr_list['0']['stock']['stock_tranfer_pkey'] : ''; ?>" id="stock_tranfer_pkey" name="stock_tranfer_pkey">  
                            <button type="button" class="btn btn-danger" data-dismiss="modal">Cancel</button>
                            <button type="submit" id="btn-submit" class="btn btn-primary">Save</button>
                        </div>
                                    </form>

                    </div>





                <!-- Tax Head Detail Form -->

          



            <!-- form ends-->
        </div>
    </div>
</div>

</div>
<script>
    $(document).ready(function () {
        /*jQuery('.sumall').on('input', function () {
            // do your stuff 
            alert('hallo');
        });
        $("#sumall").keyup(function () {
            alert('hallo');
        });
        //iterate through each textboxes and add keyup
        //handler to trigger sum event
        $(".sumall").each(function () {
            console.log('hallo');
            $(this).keyup(function () {
                calculateSum();
            });
        });*/
    });
  function pending(){
      
      
  }

//    function calculateSum(rowIndex) {
//        var sum = 0;
//        //iterate through each textboxes and add the values
//        $("input[name^=unit"+rowIndex+"]").each(function () {
//            //add only if the value is number
//            if (!isNaN(this.value) && this.value.length != 0) {
//                sum += parseFloat(this.value);
//            }
//        });
//
//        //.toFixed() method will roundoff the final sum to 2 decimal places
//        $("#unit"+rowIndex).val(sum.toFixed(2)); 
//        
//        
//        var unit = $('#pending').val();
//        var dedu = $('#dedu').val();
//        var totl = dedu - unit;
//        $('#dedu').val(totl);
        
//        if(unit>dedu)
//        {
//            alert("check value");
//            $('#pending').val("");
//        }
        
    
//
//    function getitemfordata1(obj,rowIndex) {
//        var Item_code = $(obj).val();//$('#item_code').val();
//        $('#getmeterial').show();
//        var request = $.ajax({
//            url: "PurchaseOrder/getitem/" + Item_code + "/" + rowIndex,
//            method: "POST",
//            dataType: "html"
//        });
//        request.done(function (msg) {
//            var data = msg;
//            var div_data = '';
//            div_data += "<div>" + data + "</div>";
//            $("#getmeterial").html(div_data);
//            getalltotal(Item_code);
//        });
//        request.fail(function (jqXHR, textStatus) {
//            alert("Request failed: " + textStatus);
//        });
//    }
//    function getalltotal(Item_code) {
//        var request = $.ajax({
//            url: "PurchaseOrder/itemtotel/" + Item_code,
//            method: "POST"
//        });
//        request.done(function (msg) {
//            $("#requiralll").val(msg);
//        });
//    }
//    $(document).ready(function () {
//        $('#unit').change(function () {
//            alert("The text has been changed.");
//        });
//    });
//
//
//



    $(document).ready(function () {
        $('#start_date').datepicker({
            dateFormat: 'yyyy-mm-dd'
        });
    });

    $(document).ready(function () {
        $('#exp_date').datepicker({
            dateFormat: 'yyyy-mm-dd'
        });
    });
     $(document).ready(function () {
        $('#exp1_date').datepicker({
            dateFormat: 'yyyy-mm-dd'
        });
    });
      

</script>