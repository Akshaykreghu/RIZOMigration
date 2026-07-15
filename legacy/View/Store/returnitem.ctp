<style>
    .searchb{
        background-color: #cccccc;
    }
.heading {
        display: flex;
        flex-direction: row;
        align-items: end;
        justify-content: space-between;
        /* margin-left: 20px; */
    }

    .home {
        background-color: #ffffffff;
        border-radius: 50px;
        padding: 2px 15px;
        color: #1e516e !important;
        margin-right: 15px;
        color: white;
        font-weight: 500;
        text-decoration: none;
        transition: all 0.3s ease;
        cursor: pointer;
        border: #1e516e 1px solid;
    }

</style>
<script>
    //form save time load table        
        $('#form-po-return').on('submit', function (event) { 
            event.preventDefault();
            $('#form-po-return').ajaxSubmit({
                success: function (resp) {    
                    var pid = $.parseJSON(resp).pk;
                    $("#po_pkey").val(pid);
                    $("#sumdata").val('');
                    $("#return_qty").val('');
                    $("#item_name").val([]);
                    //$("#item_name").select2({ allowClear: true }); 
                    $('#item_name').val('');
                    $('#gr_name').val('');
                    loadtable(pid, 1);
                   // $("#form-po-return").find('input:text, input:password, input:file,select2,  textarea,hidden,search').val('');
                    //$("#return_qty").val('');
                    //$("#return_date").val('');
                    //$("#return_number").val(<?php echo mt_rand(); ?>);
                    //$("#gr_name").select2({ allowClear: true }); 
                    $('#dispatch').show();
                    $("#item_name").select2({ allowClear: true }); 
                   // $('#att_table').datagrid('load');
                    $.notify($.parseJSON(resp).msg, {
                        type: 'success',
                        allow_dismiss: true
                        
                    });
                   
                }
                 
            });
           //$("#item_name").val('');
        });
         $(".home").on("click", function () {

    $("#container").isLoading({
        text: "Loading",
        position: "overlay",
    });

    let url = "";
    var userGroup = <?php echo json_encode($this->Session->read('user_group')); ?>;

    if (userGroup == "1") {
        url = livesite + "Stockmanagement/index";
    } 
    else if (userGroup == "2") {
        url = livesite + "EmployeeMenu/addon";
    }

    $("#container").load(url, function () {
        isDashboardShown = false;
    });

});
 </script>

<section class="content">
    <div class="row">
        <div class="col-md-12">
            <!-- DIRECT CHAT DANGER -->
            <div class="box ">

                <div class="box-body">


                    <!-- <h1 class="text-primary-18">PO Return Request</h1> -->
                      <div class="heading">
                    <h1 class="text-primary-18">PO Return Request</h1>
                    <div class="text-primary-16 home" style="display:flex; align-items:center; gap:10px; cursor:pointer;margin:0 0 10px 0;">
                        <i class="fa" style="font-size:16px;">&#xf104;</i>
                        Back
                    </div>
                    </div>


                    <form class="form-horizontal" style="padding-left:1px; padding-right:8px ; border:1px;" id="form-po-return" method="post" action="<?php echo $this->webroot; ?>Store/save_po_return" >
                        <div class="row bg-success" style="height:auto;"> 
                            <h4 style="padding-left:20px;">Add Basic Details</h4>
                            <div class="modal-body">
                                <div class="form-group">
                                    <div class="col-md-4">
                                        <label style="text-align:left;" class="col-md-6 control-label" >Transaction Code<label style="color:red;">*</label></label>
                                        <div class="col-md-6">
                                            <input id="return_number" name="return_number" value="<?php echo mt_rand(); ?>" type="text"  class="form-control input-md" required="required" readonly="readonly">
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <label style="text-align:left;" class="col-md-6 control-label" >Transaction Date<label style="color:red;">*</label></label>
                                        <div class="col-md-6">
                                            <input id="return_date" placeholder="select date" name="return_date" value="<?php echo date("Y-m-d"); ?> " type="text"  class="form-control input-md" required="required" onchange="refreshitem();">
                                        </div> 
                                    </div>
                                    <div class="col-md-4">
                                        <label style="text-align:left;" class="col-md-6 control-label" >Select Store <label style="color:red;">*</label></label>
                                        <div class="col-md-6">
                                            <select id="store_code" name="store_fkey" class="form-control" onchange="findgrnlist(this);" style="" required>
                                           <option value="">--Select--</option> 
                                            <?php
                                             foreach ($arr_stores as $key => $value) {
                                                echo '<option value="' . $value['store_master_pkey'] . '">' . $value['store_location'] . '</option>';
                                             }
                                            ?>
                                            </select>
                                        </div>
                                    </div>
                                   
                                </div>
                                
                            </div>
                             <div class="divider"></div>
                            <div class="modal-body">
                            <h4 style="padding-left:20px;">ADD ITEM</h4>
                            <div class="form-group">
                                
                                <div class="col-md-4">
                                    <label style="text-align:left;" class="col-md-6 control-label" >GR Number<label style="color:red;">*</label></label>
                                    <div class="col-md-6">
                                        <select id="gr_name" name="grn_fkey" class="form-control js-example-basic-single" style="" onchange="findgrnitems();" required style="width:100%">
                                          
                                        </select>
                                    </div> 
                                </div>
                                <div class="col-md-4">
                                    <label style="text-align:left;" class="col-md-6 control-label" >Item Name<label style="color:red;">*</label></label>
                                    <div class="col-md-6">
                                        <select id="item_name" name="item_fkey" class="form-control js-example-basic-single" style="" onkeyup="findgrnitems();" onchange=" itemcheck();" required style="width:100%">
                                           
                                        </select>
                                    </div> 
                                </div>
                                <div class="col-md-4">
                                    <label style="text-align:left;" class="col-md-6 control-label" >Available Qty<label style="color:red;">*</label></label>
                                    <div class="col-md-6">
                                        <input type="number" id="sumdata" class="form-control" name="available_qty" required="required"  readonly="readonly" style="">  
                                        <input type="hidden" name="po_number" id="po_number" value="<?php echo isset($avail_qty['0']['purchase_order']['po_number']) ? $avail_qty['0']['purchase_order']['po_number'] : ''; ?>">
                                <input type="hidden" name="mo_number" id="mo_number" value="<?php echo isset($avail_qty['0']['material_request']['mr_code']) ? $avail_qty['0']['material_request']['mr_code'] : ''; ?>">
                                <input type="hidden" name="supplier_code" id="supplier_code" value="<?php echo isset($avail_qty['0']['purchase_order']['supplier_code']) ? $avail_qty['0']['purchase_order']['supplier_code'] : ''; ?>">
                                    </div>
                                </div>
                                </div>
                            </div>
                             <div class="modal-body">
                                 <div class="form-group">
                                <div class="col-md-4">
                                    <label style="text-align:left;" class="col-md-6 control-label" >Return Qty<label style="color:red;">*</label></label>
                                    <div class="col-md-6">
                                        <input type="number" min="1" id="return_qty" class="form-control"   name="return_qty" onchange="valuecheck();" required="required" style="">   
                                    </div>
                                </div>
                                <div class="col-md-4">
                                        <label style="text-align:left;" class="col-md-6 control-label" >Remarks<label style="color:red;">*</label></label>
                                        <div class="col-md-6">
                                            <select name="remark" id="remarks" class="form-control input-md" style="">
                                                <option value="Damaged">Damage</option>
                                                <option value="Lowquality">Low Quality</option>
                                            </select>
                                        </div>
                               </div>
                               <div class="col-md-4">
                                <input type="hidden" name="po_pkey" id="po_pkey" value="<?php echo isset($arr_att['0']['po_return_request']['po_pkey']) ? $arr_att['0']['po_return_request']['po_pkey'] : ''; ?>">
                                <input type="hidden" name="po_fkey" id="po_fkey" value="<?php echo isset($arr_att['0']['return_gr_items']['po_fkey']) ? $arr_att['0']['return_gr_items']['po_fkey'] : ''; ?>">
                                
                                <button  type="submit" id="btn-submit"  class="btn btn-primary pull-right"  ><li class="fa fa-plus-circle"></li> Add Item to POR</button>
                               </div>
<!--                                <div class="modal-footer">
                                </div>-->
                            </div>
                            
                            </div>
                            <div id="errormsg" style="color: red;text-align: center;"></div>
                             <div class="col-xs-12">
            <div id="table_appnd" class="" style="text-align: center;">

            </div>
            <div id="dispatch">
                <input type="button" id="submit_mr" onclick="submitpor();" value="Submit Request" class="btn btn-primary pull-right">
                <input type="button" id="btn-remove" value="Remove POR" class="btn btn-danger">
            </div>
            <div class="spacer-20"></div>
                   </div >
                        </div>
                    </form>

                    <div class="col-xs-12">
                        <div>
                            <h1 class="text-primary-18">PO Return Product List </h1>
                            <table id="att_table" class="table table-bordered table-hover">
                                <tbody>
                                </tbody>
                            </table>
                        </div>
                    </div >


            </div>
        </div>
    </div>
</section> 
<script>
   
//value check 
    function  valuecheck()
    {
        
        var avil = parseInt($('#sumdata').val());
        var req = parseInt($('#return_qty').val());
//        if (avil < req || isNaN(avil))
//        {
//            alert("Please Check Available Qty ");
//            $("#return_qty").val('');
//        }
        var grn_pkey = $('#item_name').val();
        var date_allocated = $('#return_date').val();
        var store_fkey = $('#store_code').val();
    //alert(date_allocated);
      
        $.ajax({
            url: 'Store/finditem_qty/'+store_fkey+'/'+grn_pkey+'/'+date_allocated,
            success: function (response) {
             var data = $.parseJSON(response);
             //alert(response);
             if(parseInt(req) > data){
               //alert("Insufficient Stock");
               $("#errormsg").html("You don't have enough stock for this transaction. Please select another transaction date / lesser quantity and try again!!!");
               $('#return_qty').val('');
             }else{
               $("#errormsg").html("");
             }
            }
        });
    }
    //check item exists 
    function itemcheck(){
        var code = $("#return_number").val();
        var gr_pkey = $("#item_name").val();
         $.ajax({
                url: livesite+'Store/itemexists/' + code +'/'+gr_pkey,
                success: function (resp) {
                //console.log(resp); 
                 var countvalue = $.parseJSON(resp).cnt;
                //alert(response);
                //console.log(countvalue); 
                if(countvalue == 0){
                   loadqty();
                }else{
                  $.notify($.parseJSON(resp).msg, {
                            type: 'danger',
                            allow_dismiss: false
                        });
                    }  
                }
//                else{
//                $.notify($.parseJSON(response).msg, {
//                            type: 'danger',
//                            allow_dismiss: false
//                        });
//                    }
                   // }
                });
        
    }

    function submitpor(){
        var id = $("#po_pkey").val();
         if($('#table_appnd').find('table').length){
             
              $.ajax({
                    url: livesite+'Store/submit_return/' + id,
                    success: function (resp) {
                        
                        $.notify($.parseJSON(resp).msg, {
                            type: 'success',
                            allow_dismiss: false
                        });
                    }
                });
            refresh();
                      $("#return_date").val('<?php echo date("Y-m-d"); ?> ');
                      $("#return_number").val(<?php echo mt_rand(); ?>);
                      $("#item_name").select2({ allowClear: true }); 
                      $('#item_name').val('');
                      $('#att_table').datagrid('load');
        }else{
            alert("Please add items to Submit the Request")
        }
        
    }
    $("#return_date").on("change", function(e) {
        e.preventDefault();
        e.stopImmediatePropagation();
        //$('#item_name').val('');
        //$('#gr_name').val('');
        //$('#sumdata').val('');
        // $("#gr_name").select2({ allowClear: true }); 
        // $("#item_name").select2({ allowClear: true }); 
        var id = $("#po_pkey").val();
        var count=0;
        if (id) { 
            var r = confirm("Do You Want  To Remove The Selected Item")
            if (r == true) {
                count=count + 1;
                //alert(count);
                $.ajax({
                    url: livesite+'Store/deleteordermaster/' + id,
                    success: function (resp) {
                        $.notify($.parseJSON(resp).msg, {
                            type: 'danger',
                            allow_dismiss: false
                        });
                        $("#return_qty").val('');
                        $("#table_appnd").html('');
                        $('#dispatch').hide();
                        $("#gr_name").select2({ allowClear: true }); 
                        $('#store_code').val('');
                        $("#return_date").val('<?php echo date("Y-m-d"); ?> ');
                    }
                });
            } else
            {
                alert("canceled");
            }
        }
    });
    function refresh() {
//        var r = confirm("Do you want to refresh? ")
//            if (r == true) {
        
        
        
        $("#form-po-return").find('input:text, input:password, input:file,select2,  textarea,hidden,search').val('');
        $("#return_date").val('<?php echo date("Y-m-d"); ?> ');
        $("#return_number").val(<?php echo mt_rand(); ?>);
        $('#item_name').val('');
        $('#sumdata').val('');
        $("#return_qty").val('');
        $("#gr_name").val([]);
        $("#po_pkey").val('');
        $("#po_fkey").val('');
         $("#gr_name").select2({ allowClear: true }); 
        $("#item_name").select2({ allowClear: true }); 
        $('#store_code').val('');
        $('#gr_name').val('');
        //$("#form-po-return").find('input:radio, input:checkbox').removeAttr('checked').removeAttr('selected');
        $("#table_appnd").html('');
        $('#dispatch').hide();
        $('#att_table').datagrid('load');
        $('#btn-refresh').fadeOut();
        $('#table_appnd').datagrid('load');
//            }
    }
    function clear(){
        //alert("change clicked");
        $('#item_name').val('');
        $('#gr_name').val('');
        $('#sumdata').val('');
        $("#return_qty").val('');
        
    }
   $("#btn-remove").click(function () {
       var id = $("#po_pkey").val();
        //alert(id);
        if (id) {
            var r = confirm("Do You Want  To Remove The Selected Item")
            if (r == true) {
                $.ajax({
                    url: livesite+'Store/deleteordermaster/' + id,
                    success: function (resp) {
                        refresh();
                        $.notify($.parseJSON(resp).msg, {
                            type: 'success',
                            allow_dismiss: false
                        });
                    }
                });
            } else
            {
                alert("canceled");
            }
        }

    });
    $(document).ready(function () {
        var currentDate = new Date();
       // $("#return_date").val(currentDate);
        $('#return_date').datepicker({
            format: 'yyyy-mm-dd',
            autoclose: true,
            startDate: '-15d',
            endDate: '0d'
        });
     
       $('#att_table').datagrid({
            url: livesite + "Store/poreturn",
            pagination: true,
            singleSelect: true,
            title : "PO Return List",
            rownumbers: true,
            fitColumns: true,
            pageList: [2, 5, 10, 50, 100],
            pageSize:10,
//            toolbar: [{
//                    text: 'Return',
//                    iconCls: 'icon-remove',
//                    handler: function () {
//                        showLargeModalForm(livesite + 'Store/allocate_form')
//                    }
//                }],
            
            columns: [[
                    {field: 'return_number', title: 'PO Return Number', width: "15%"},
                    {field: 'return_date', title: 'Return Date', width: "15%"},
                    {field: 'store_fkey', title: 'Store', width: "15%"},
                    {field: 'gr_number', title: 'GR Number', width: "15%"},
                    {field: 'item_fkey', title: 'Item', width: "15%"},
                    {field: 'return_qty', title: 'Quantity', width: "10%"},
                    {field: 'remark', title: 'Remarks', width: '15%'},
                ]]
        });
        




    });
    $('#dispatch').hide();
    
function  loadtable(pid, rowindex) {
//alert('hai');
        $("#table_appnd").html('<li style="    font-size: -webkit-xxx-large;" class="fa fa-spinner fa-spin"></li><br>Loading Data....');
        $.ajax({
            url: livesite+ 'Store/loadtabledata/' + pid + '/' + rowindex,
            success: function (response) {
                //alert(response);
                var data = response;
                var div_data = '';
                div_data += "<div>" + data + "</div>"
                $("#table_appnd").html(div_data).promise().done(function(){
                    
                });

            }
        });

    }
    function removedata(index, id) {
        if (id) {
            var r = confirm("Do You Want  To Remove The Selected Item")
            if (r == true) {


                var pid = $("#po_pkey").val();
                $.ajax({
                    url: livesite+'Store/deletepoorder/' + id,
                    success: function (resp) {
                        loadtable(pid, 1);
                        $.notify($.parseJSON(resp).msg, {
                            type: 'danger',
                            allow_dismiss: false
                        });
                    }
                });
            } else
            {
                alert("canceled");
            }
        }
    }
   function editdata(index, id) {
      var pid = $("#po_pkey").val();
      var store = $('#store_code').val();
      var grn = $('#gr_name').val();
      //alert(store);
      var url = livesite+'Store/editpo_order/' + id +'/' + store + '/' + grn;
     showModalForm(url);
    
     //loadtable(pid, 1);
    }
    //load grn numbers from selected store
    function  findgrnlist() {
       var from_store_name = $("#store_code").val();
        var return_date = $("#return_date").val();
      //alert(from_store_name);
          $("#gr_name").select2(
                {
                    //closeOnSelect:false,
                    placeholder: "Select Item",
                    allowClear: true,
                    ajax: {
                        url: livesite + "Store/findgrnlistitems/" + from_store_name +"/"+return_date,
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
                            //$("#gr_name").val('');
                            $("#item_name").val('');
                           // $("#sumdata").val('');
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
     //find itemcode
        var item_fkey = {
            url: function (phrase) {
                return "Store/getautocompletionsitem_desc?item_desc=" + phrase + "&from_grn_no="+$('#gr_name').val();
            },
            getValue: "item_fkey",
            list: {
                onSelectItemEvent: function () {
                    var selectedItem = $('#item_name').getSelectedItemData();
                    $('#item_code').val(selectedItem.item_code);
                    $('#sumdata').val(selectedItem.qty);
                }
            }
        };
       // $('#item_name').easyAutocomplete(item_fkey);
     //load item names from selected grn number
    function  findgrnitems() {
        var from_grn_no = $("#gr_name").val();
        var from_store_name = $("#store_code").val();
      //alert(from_store_name);
          $("#item_name").select2(
                {
                    //closeOnSelect:true,
                    placeholder: "Select Item",
                    allowClear: true,
                    ajax: {
                        url: livesite + "Store/findgrnitemslist/" + from_grn_no+'/'+from_store_name,
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
                            //$("#gr_name").val('');
                            //$("#item_name").val('');
                            //$("#sumdata").val('');
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
    
    //load available qty
    function  loadqty() {
       var gr_no = $("#item_name").val();
       var from_store_name = $("#store_code").val();
       //load_po_number();
       //alert(gr_no);
      $.ajax({
            url: 'Store/getavailqty/'+gr_no+'/'+from_store_name,
            success: function (response) {
                var qty = $.parseJSON(response).pk;
                //alert(response);
                console.log(qty);
                if(qty >= 0){
                    $('#sumdata').val(qty);
                }
                else{
                $.notify($.parseJSON(response).msg, {
                            type: 'danger',
                            allow_dismiss: false
                        });
                    }
                //var data = response;     

            }
        });
   }
  //load po number, mr number, supplier code
    function  load_po_number() {
       var gr_no = $("#gr_name").val();
      $('#gr_name').load(livesite+'Store/load_po_num/' + gr_no);
//      $.ajax({
//            url: 'Store/load_po_num/' + gr_no,
//            success: function (response) {
//               // alert(response);
//                //console.log($.parseJSON(response));
//                var data = response;
//                alert(data['purchase_order']['po_number']);
//                $('#po_number').val(data['purchase_order']['po_number']);
//               // $('#sumdata').val(data);
//
//
//            }
//        });
   }
  
     //load gr list from selected item
//    function  findstoreitems() {
//        var from_item_name = $("#item_name").val();
//        //alert(from_item_name);
//          $("#gr_name").select2(
//                {
//                    //closeOnSelect:false,
//                    placeholder: "Select GRN",
//                    allowClear: true,
//                    ajax: {
//                        url: livesite + "Store/findgritems/" + from_item_name,
//                        dataType: 'json',
//                        delay: 250,
//                        data: function (params) {
//                            return {
//                                qu: params.term, // search term
//                                page: params.page
//                            };
//                        },
//                        processResults: function (data, params) {
//                            // parse the results into the format expected by Select2
//                            // since we are using custom formatting functions we do not need to
//                            // alter the remote JSON data, except to indicate that infinite
//                            // scrolling can be used
//                            params.page = params.page || 1;
//                            return {
//                                results: data.items,
//                                pagination: {
//                                    more: (params.page * 30) < data.total_count
//                                }
//                            };
//                        }
//                    },
//                    escapeMarkup: function (markup) {
//                        return markup;
//                    }
//                });
//    }
     //load gr list from selected item
//    function  findgritems() {
//        var from_item_name = $("#item_name").val();
//        //alert(from_item_name);
//          $("#gr_name").select2(
//                {
//                    //closeOnSelect:false,
//                    placeholder: "Select GRN",
//                    allowClear: true,
//                    ajax: {
//                        url: livesite + "Store/findgritems/" + from_item_name,
//                        dataType: 'json',
//                        delay: 250,
//                        data: function (params) {
//                            return {
//                                qu: params.term, // search term
//                                page: params.page
//                            };
//                        },
//                        processResults: function (data, params) {
//                            // parse the results into the format expected by Select2
//                            // since we are using custom formatting functions we do not need to
//                            // alter the remote JSON data, except to indicate that infinite
//                            // scrolling can be used
//                            params.page = params.page || 1;
//                            return {
//                                results: data.items,
//                                pagination: {
//                                    more: (params.page * 30) < data.total_count
//                                }
//                            };
//                        }
//                    },
//                    escapeMarkup: function (markup) {
//                        return markup;
//                    }
//                });
//    }
    
</script>

