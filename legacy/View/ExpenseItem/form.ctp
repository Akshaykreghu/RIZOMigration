<script>
    var options = {
        success: function (resp) {
            $('#modalForm').modal('hide');
            $('#acctptable').datagrid('reload');
            $.notify("Success", {
                type: 'success',
                allow_dismiss: false
            });
        }  // post-submit callback
    };
    $('#itemform').on('submit', function (event) {

        event.preventDefault();
        if (confirm("Do You Want To Save The Form")) {
            $('#itemform').ajaxSubmit(options);
        }
    });
    
   function checkIfitemcodeExists(callback)
   {        
        var item_code = $('#item_code').val();       

        $.ajax({
            url: 'ExpenseItem/chkcategory/',
            type: 'POST',
            data: {
                item_code: item_code
            }, 
            success: function (resp)
            {  
                var respval = '';
                var respval = JSON.parse(resp);
                if(respval.msg == '1'){
                 $('#errmsg1').html("Item code already exists");  
                $('#errmsg1').show();
                $('#item_code').val('');
              //  return null;
             }else {
                     $('#errmsg1').html('');                   
                }
            }
        });
    }
    function checkIfitemExists(callback)
   {          
        var item_desc = $('#item_desc').val();  

        $.ajax({
            url: 'ExpenseItem/chkcategory/',
            type: 'POST',
            data: {
                item_desc: item_desc
            }, 
            success: function (resp)
            {  
                var respval = '';
                var respval = JSON.parse(resp);
                 if(respval.msg == '2')
                   {
                //       alert(respval.msg);
                       $('#errmsg2').html("Item Name Already Exists");  
                       $('#errmsg2').show();
                       $('#item_desc').val('');
                   }                  
                    else{
                      $('#errmsg2').html('');                    
                }
            }
        });
    }
    function filterEmployees(branch)
    {
        
       var branch = $('#expense_item_pkey').val();
        // alert(branch);
        $("#expense_item_name").select2(
                {
                    //closeOnSelect:false,
                    placeholder: "Search Item By Type ... ",
                    allowClear: true,
                    ajax: {
                        url: livesite + "ExpenseItem/expensefilter/",
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

<div class="modal-dialog">

    <!-- Modal content-->
    <div class="modal-content">
        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal">&times;</button>
            <h4 class="modal-title">Expense Category </h4>
        </div>
        <div class="modal-body">
            <!-- Form starts -->
            <div id="empsetup-save-response" class="">
                <form class="form-horizontal"  method="post" action="<?php echo $this->webroot; ?>ExpenseItem/save" id="itemform" name="itemform">
                    <div class="modal-body">
                        <input type="hidden" value="<?php echo isset($arr_att['0']['expense_item']['expense_item_pkey']) ? $arr_att['0']['expense_item']['expense_item_pkey'] : ''; ?>" id="expense_item_pkey" name="expense_item_pkey">    

                        <div class="form-group form-group-sm">
                            <label class="col-sm-3 control-label pull-left" >Expense Name<lable style="color:red">*</lable></label>
                            <div class="col-sm-7">
                            <!-- <div class="col-md-4"> -->
                             <?php //debug($arr_type);
                                          //   debug($arr_att);  ?>
                                        <select id="expense_item_name" class="form-control js-example-basic-single" name="expense_item_name"  style="width: 302px">
                           <?php $status = isset($arr_att['0']['expense_item']['expense_item_name']) ? $arr_att['0']['expense_item']['expense_item_name']: ''; ?>
                               
                                        		<?php foreach ($arr_type as $value) {?>
                                             <?php 
                                if ($value['expense_type']['expense_type_pkey'] == $status) {
                                    $selected = 'selected="selected"';
                                } else {
                                    $selected = '';
                                }
                                ?>
                                                    <option <?php echo $selected; ?>
                                        			value="<?php echo $value['expense_type']['expense_type_pkey']; ?>"><?php echo $value['expense_type']['expense_type_name']; ?>
                                                
                                                    </option>
                                        		<?php } ?>
                                        	

                                        </select>
                                        <!-- </div> -->
                            </div>
                        </div>
                        <div class="form-group form-group-sm">
                            <label class="col-sm-3 control-label pull-left" for="item_desc">Category<label style="color:red">*</label></label>
                            <div class="col-sm-7">
                                <input class="form-control" type="text" value='<?php echo isset($arr_att["0"]["expense_item"]["category"]) ? $arr_att["0"]["expense_item"]["category"] : ""; ?>' name="category" id="category" required="required"  onchange="checkIfitemExists(this);" >
                            <div id="errmsg2" style="color:red"> </div>
                            </div>
                        </div>
           
   
                        <div class="form-group form-group-sm">
                            <label class="col-sm-3 control-label pull-left" for="description">Description</label>
                            <div class="col-sm-7">
                                <input class="form-control" type="text"  name="description" id="description" value='<?php echo isset($arr_att["0"]["expense_item"]["description"]) ? $arr_att["0"]["expense_item"]["description"] : ""; ?>'>
                            
                            </div>
                        </div>

                    </div>
                  
     
                          


                                <div class="modal-footer">
                                    <button type="submit" id="btn-submit" class="btn btn-primary">Save</button>
                                    <button type="button" class="btn btn-danger" data-dismiss="modal">Cancel</button>

                                </div>
                            </div>


                        </div>







                    </div>
                </form>
                <!-- Tax Head Detail Form -->

            </div>



            <!-- form ends-->
        </div>

    </div>

</div>
<script>
    $(document).ready(function () {
        // filterEmployees();
        $('#start_date').datepicker({
            dateFormat: 'yyyy-mm-dd'
        });
        $('#end_date').datepicker({
            dateFormat: 'yyyy-mm-dd'
        });
        $('#manu_date').datepicker({
            dateFormat: 'yyyy-mm-dd'
        });
    });
</script>