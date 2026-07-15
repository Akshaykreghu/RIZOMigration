<style>
    .pull-left {
        float: right !important;
    }
</style>
<script>

    $.validate({
        form: '#form-direct-master'
    });
    var options = {
        success: function (resp) {
            $('#modalDiv').modal('hide');

            $('#detailspurchasetable').datagrid('reload');
            $.notify("Success", {type: 'success'});
        }  // post-submit callback
    };

    $('#form-direct-master').on('submit', function (event) {
        event.preventDefault();
        if (confirm(" Do You Want  To Save The Form")) {
            $('#form-direct-master').ajaxSubmit(options);


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
        $('#div-criteria' + newIndex).load('DirectPurchaseOrder/addnewrow/'+newIndex);
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
                <form class="form-horizontal" id="form-direct-master" method="post" action="<?php echo $this->webroot; ?>DirectPurchaseOrder/save" >
                    <div role="tabpanel" class="tab-pane" id="div-" name="">

                        <div class="form-group form-group-sm">
                            <div class="col-xs-4">
                                <label class="col-md-4 control-label" >Purchase Number</label>
                                <div class="col-md-7">
                                    <input id="direct_po_number" name="direct_po_number" value="<?php echo isset($arr_list['0']['directpo']['direct_po_number']) ? $arr_list['0']['directpo']['direct_po_number'] : ''; ?>" type="text"  class="form-control input-md" >
                                </div>
                            </div>
                            <div class="col-xs-3">
                                <label  class="col-md-4 control-label" >Purchase Date<label style="color:red">*</label></label>
                                <div class="col-md-8">
                                    <input id="start_date" name="direct_po_date" value="<?php echo isset($arr_list['0']['directpo']['direct_po_date']) ? $arr_list['0']['directpo']['direct_po_date'] : ''; ?>" type="text"  class="form-control input-md" required="required" >
                                </div> 
                            </div>
                            <div class=col-xs-4">
                                <label  class="col-md-2 control-label" >Location</label>
                                <div class="col-md-3">
                                    <input id="location"  name="location" value="<?php  echo isset($arr_list['0']['directpo']['location']) ? $arr_list['0']['directpo']['location'] : ''; ?>" type="text"  class="form-control input-md" >
                                </div>
                            </div>
                        </div>

                        <div class="form-group form-group-sm">
                            <div class="col-xs-4">
                                <label class="col-md-4 control-label" >Remark</label>
                                <div class="col-md-7">
                                    <input id="remarks" name="remarks" value="<?php echo isset($arr_list['0']['directpo']['remarks']) ? $arr_list['0']['directpo']['remarks'] : ''; ?>" type="text"  class="form-control input-md"  >
                                </div>
                            </div>
                           
                            
                        </div>

                        
                        <div class="form-group form-group-sm">
                            <div class="col-md-20" > </div>
                        </div>

                        <div  style="border: 1px solid #DCDCDC" style="padding: 0px 20px 10px 19px;">
                            <div class="col-xs-4">
                            </div>
                            <div class="modal-title" style="font-family:verdana; color:black; size:12px">Direct Purchase Order Details</div>
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
                                            <th>Item Code</th> 
                                            <th>Item Description</th>
                                            <th>uom</th>
                                            <th>Pending Qty:</th> 
                                            <th >Ordering Qty<label style="color:red;">*</label></th>
                                            <th>PO Rate<label style="color:red;">*</label></th>
                                            <th>PO value</th>  




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
                                                                $selected = ($value['directpod']['item_code'] == $getitem['Item']['item_master_pkey']) ? 'selected="selected"' : '';
                                                                echo '<option value="' . $getitem['Item']['item_master_pkey'] . '" ' . $selected . '>' . $getitem['Item']['item_desc'] . '   </option>';
                                                            }
                                                            ?>
                                                        </select>
                                                      
                                                    </td>

                                                    <td><input type = "text"  value = "<?php echo isset($value['directpod']['item_description']) ? $value['directpod']['item_description'] : ''; ?>"name ="item_description[]" id = "current_stock" style = "width:100%;"></td>
                                                    <td><input type = "text"  value = "<?php echo isset($value['directpod']['uom']) ? $value['directpod']['uom'] : ''; ?>"name = "uom[]" id = "incoming_qty"  style="width:60px" ></td>
                                                    <td><input type = "text"  value = "<?php echo isset($value['directpod']['requested_qty']) ? $value['directpod']['requested_qty'] : ''; ?>"name = "requested_qty[]" id = "item_description" style = "width:100px"></td>
                                                    <td><input type = "text"  value = "<?php echo isset($value['directpod']['ordered_qty']) ? $value['directpod']['ordered_qty'] : ''; ?>" name = "ordered_qty[]" id = "" style = "width: 100px" ></td>
                                                    <td><input type = "text"  value = "<?php echo isset($value['directpod']['po_rate']) ? $value['directpod']['po_rate'] : ''; ?>" name = "po_rate[]" id = "unit" style = "width: 100px"></td>
                                                    <td><input type = "text"  value = "<?php echo isset($value['directpod']['po_value']) ? $value['directpod']['po_value'] : ''; ?>" name = "po_value[]" id = "unit" style = "width: 100px"></td>
                                                     <input type = "hidden" value = "<?php echo isset($value['directpod']['direct_po_details_pkey']) ? $value['directpod']['direct_po_details_pkey'] : ''; ?>" id = "direct_po_details_pkey" name ="direct_po_details_pkey[]">
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

        //$selected = ($arr_list['0']['directpod']['store_code'] == $value['Store']['store_master_pkey']) ? 'selected="selected"' : '';
        echo '<option value="' . $value['Item']['item_master_pkey'] . '" ' . $selected . '>' . $value['Item']['item_desc'] . '   </option>';
    }
    ?>
                                                    </select>

                                                </td>
                                                <td><input type = "text"  name ="item_description[]" id = "current_stock" style = "width:100%;"></td>
                                                <td><input type = "text"  name = "uom[]" id = "incoming_qty"  style="width:60px" ></td>
                                                <td><input type = "text"  name = "requested_qty[]" value="<?php //echo isset($value['0']['sumunit']) ; ?>"id ="requiralll" style = "width:100px" >
                                                <td><input type = "text"  name = "ordered_qty[]" id = "unit1" style = "width: 100px" ></td>
                                                <td><input type = "text"  name = "po_rate[]" id = "unit" style = "width: 100px"></td>
                                                <td><input type = "text"  name = "po_value[]" id = "unit" style = "width: 100px"></td>
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
                            <input type="hidden" value="<?php echo isset($arr_list['0']['directpo']['direct_po_pkey']) ? $arr_list['0']['directpo']['direct_po_pkey'] : ''; ?>" id="direct_po_pkey" name="direct_po_pkey">  
                            <button type="button" class="btn btn-danger" data-dismiss="modal">Cancel</button>
                            <button type="submit" id="btn-submit" class="btn btn-primary">Save</button>
                        </div>
                    </div>



                </form>


                <!-- Tax Head Detail Form -->

            </div>



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

</script>