<form class="form-horizontal" style="padding-left:1px; padding-right:8px ; border:1px;" id="form-user-master" method="post" action="<?php echo $this->webroot; ?>MaterialRequest/save" >
<h1 class="page-header">Create Material Request</h1>
    <div class="row bg-success" style="height:auto;"> 
        <div class="modal-body">
            <h4 style="padding-left:20px;">Add Basic Details</h4>

        </div>

        <div class="modal-body">
            <div class="form-group">
                <div class="col-md-3">
                    <label style="text-align:left;" class="col-md-5 control-label" >MR Number<label style="color:red;">*</label></label>
                    <div class="col-md-6">
                        <input id="mr_code" name="mr_code" value="<?php echo isset($arr_att['0']['material_request']['mr_code']) ? $arr_att['0']['material_request']['mr_code'] : mt_rand(); ?>" type="text"  class="form-control input-md" required="required" readonly="readonly">
                    </div>
                </div>
                <div class="col-md-3">
                    <label style="text-align:left;" class="col-md-4 control-label" >Required date</label>
                    <div class="col-md-6">
                        <input id="mr_date" placeholder="select date" name="mr_date" value="<?php echo isset($arr_att['0']['material_request']['mr_date']) ? $arr_att['0']['material_request']['mr_date'] : ''; ?>" type="text"  class="form-control input-md" required="required">
                    </div> 
                </div>
                <div class="col-md-3">
                    <label style="text-align:left;" class="col-md-4 control-label" >Store Code<label style="color:red;">*</label></label>
                    <div class="col-md-8">
                        <select id="store_code" class="form-control" name="store_code" required="required" onchange="refresh_values();">
                            <option value="" >---select---</option>
                            <?php
                            foreach ($all_store as $value) {

                                $selected = ($arr_att['0']['material_request']['store_code'] == $value['Store']['store_master_pkey']) ? 'selected="selected"' : '';
                                echo '<option value="' . $value['Store']['store_master_pkey'] . '" ' . $selected . '>' . $value['Store']['store_code'] . ' - '. $value['Store']['store_location'] . '   </option>';
                            }
                            ?>    
                        </select>                      
                    </div>
                </div>
                <div class="col-md-3">
                    <label style="text-align:left;" class="col-md-5 control-label" >Remark</label>
                    <div class="col-md-6">
                        <input id="remarks" name="remarks" value="<?php echo isset($arr_att['0']['material_request']['remarks']) ? $arr_att['0']['material_request']['remarks'] : ''; ?>" type="text"  class="form-control input-md" >
                    </div> 
                </div>
            </div>
        </div>

    </div>

    


    <div class="spacer-20"></div>



    <div class="row bg-success" style="height:auto;">

        <h4 style="padding-left:20px;">Add Item</h4>
        <div class="divider"></div>
        <div class="form-group">
            <div class="col-md-3">
                <label style="text-align:left;" class="col-md-4 control-label" >Item Name<label style="color:red;">*</label></label>
                <div class="col-md-8">
                     <select id="item_code" class="form-control js-example-basic-single" name="item_code" onchange="getcode();" >

                                    </select>
<!--                    <input type="text" id="item_code" class="form-control" name="items" required="required" onchange="checkitemname();" >   -->
                </div> 
            </div>
            <input type="hidden" id="item_code1" class="form-control" name="item_code" required="required" onchange="checkitemname();" >
            <div class="col-md-3">
                <label style="text-align:left;" class="col-md-4 control-label" >Item Code<label style="color:red;">*</label></label>
                <div class="col-md-8">
                    <input type="text" id="itemcode" class="form-control" name="itemcode" onchange="checkitemcode();">   
                </div>
            </div>
            <div class="col-md-2">
                <label style="text-align:left;" class="col-md-4 control-label" >Available Qty:<label style="color:red;">*</label></label>
                <div class="col-md-8">
                    <input type="number" id="available_qty" class="form-control" name="available_qty" required="required" readonly="readonly">   
                </div>
            </div>
            <div class="col-md-2">
                <label style="text-align:left;" class="col-md-4 control-label" >Required Qty:<label style="color:red;">*</label></label>
                <div class="col-md-8">
                    <input type="number" id="required_qty" class="form-control" name="required_qty" required="required" min="1">   
                </div>
            </div>
            <div class="col-md-2 pull-right">
                <input type="hidden" name="mr_pkey" id="mr_pkey" value="<?php echo isset($arr_att['0']['material_request']['mr_pkey']) ? $arr_att['0']['material_request']['mr_pkey'] : ''; ?>">
                <button type="submit" id="btn-submit" class="btn btn-primary"  ><li class="fa fa-arrow-down"></li> Add Item to MR</button>
            </div>
            <div class="modal-footer">
                
            </div>
        </div>
        <div class="divider"></div>
        <div class="col-xs-12">
            <div id="table_appnd" class="" style="text-align: center; ">

            </div>
            <div id="dispatch">
                <input type="button" id="submit_mr" onclick="submitmr();" value="Submit Request" class="btn btn-primary pull-right">
                <input type="button" id="btn-remove" value="Remove MR" class="btn btn-danger">
            </div>
            <div class="spacer-20"></div>
        </div >

                 
    </div>
</form>
<script>
  
    function  itemfind()
    {
        var number = $('#required_qty').val();
        if (number < 1)
        {
            alert("Please Check Enter Number");
            $("#required_qty").val('');
        }
    }
    
    function submitmr(){
        //refresh();
       //console.log(tables);
         if($('#table_appnd').find('table').length){
            if(confirm("Are you sure to submit MR Request ? ")){
               
            refresh();
            $('#materialtable').datagrid('load'); 
            } 
        }else{
            alert("Please add items to Submit the Request")
        }
        
    }
    
//custmername check
    function checksite()
    {
        var site = $('#site_name').val();
        if (site != '')
        {
            $("#customer_po_number").val('');
            $("#customer_name").val('');
        }
    }
//namecheck
    function checkname()
    {
        var custmer = $('#customer_name').val();
        if (custmer != '')
        {
            $("#customer_po_number").val('');
            $("#site_name").val('');
        }
    }
//pocheck
    function checkpo()
    {
        var custmerpo = $('#customer_po_number').val();
        if (custmerpo != '')
        {
            $("#customer_name").val('');
            $("#site_name").val('');
        }
    }
//checkitemname
    function checkitemname()
    {
        var itemname = $('#item_code');
        if (itemname != '')
        {
            $('#itemcode').val('');
        }
    }
//checkitemcode
    function checkitemcode()
    {
        var itemcode = $('#itemcode');
        if (itemcode != '')
        {
            $('#item_code').val('');
        }
    }


    $(document).ready(function () {

        $('#btn-refresh').fadeIn();
        var currentDate = new Date();
        $( function() {
        $('#mr_date').datepicker({ 
            format: 'yyyy-mm-dd',       
            autoclose: true,
            startDate: '+1d',
        
        });
        });
        $('#fromdate').datepicker({
            dateFormat: 'yyyy-mm-dd'
        });
        $('#todate').datepicker({
            dateFormat: 'yyyy-mm-dd'
        });
        $('#form-user-master').on('submit', function (event) {
            event.preventDefault();

            $('#form-user-master').ajaxSubmit({
                success: function (resp) {
                    var pid = $.parseJSON(resp).pk;
                    $("#mr_pkey").val(pid);
                    //alert(pid);
                    $("#item_code").focus();
                    loadtable(pid, 1);
                    $("#required_qty").val('');
                    $("#item_code").val('');
                    $("#itemcode").val('');
                    $("#incoming_date").val('');
                    $("#available_qty").val('');
                     //added by megha on 14/08/2019 store disable for select multiple items
                    //$('#store_code').prop('disabled', true);
                    //$('#mr_date').prop('disabled', true);
                    //$('#remarks').prop('disabled', true);
                    $.notify('Success: Auto saving data <li class="fa fa-spinner fa-spin"></li>', {
                        type: 'success',
                        allow_dismiss: true
                    });
                }

            });

        });
//site name
        var site_name = {
            url: function (phrase) {
                return livesite+ "MaterialRequest/getautocompletionssite_name?site_name=" + phrase;
            },
            getValue: "site_name",
            list: {
                onSelectItemEvent: function () {
                    var selectedItem = $('#site_name').getSelectedItemData();
                    var customer_name = selectedItem.customer_name;
                    var customer_po_number = selectedItem.customer_po_number;

                    $('#customer_name').val(customer_name);
                    $('#customer_po_number').val(customer_po_number);
                }
            }
        };
        $('#site_name').easyAutocomplete(site_name);
//site Client Name
        var customer_name = {
            url: function (phrase) {
                return livesite+ "MaterialRequest/getautocompletionscustomer_name?customer_name=" + phrase;
            },
            getValue: "customer_name",
            list: {
                onSelectItemEvent: function () {
                    var selectedItem = $('#customer_name').getSelectedItemData();
                    var customer_po_number = selectedItem.customer_po_number;
                    var site_name = selectedItem.site_name;

                    $('#site_name').val(selectedItem.site_name);
                    $('#customer_po_number').val(customer_po_number);
                }
            }
        };
        $('#customer_name').easyAutocomplete(customer_name);
//site customer po m=number
        var customer_po_number = {
            url: function (phrase) {
                return livesite+ "MaterialRequest/getautocompletionscustomer_po_number?customer_po_number=" + phrase;
            },
            getValue: "customer_po_number",
            list: {
                onSelectItemEvent: function () {
                    var selectedItem = $('#customer_po_number').getSelectedItemData();
                    var site_name = selectedItem.site_name;
                    var customer_name = selectedItem.customer_name;

                    $('#customer_name').val(customer_name);
                    $('#site_name').val(site_name);
                }
            }
        };
        $('#customer_po_number').easyAutocomplete(customer_po_number);
//itemname
        var item_code = {
          //  url: function (phrase) {
//                return livesite+ "MaterialRequest/getautocompletionsitem_code?item_code=" + phrase;
//            },
//            getValue: "item_desc",
//            list: {
//                onSelectItemEvent: function () {
//                    var selectedItem = $('#item_code').getSelectedItemData();
//                    var itemcode = selectedItem.item_code;
//                    var item_pkey = selectedItem.item_master_pkey;
//
//                    $('#itemcode').val(itemcode);
//                    $('#item_code1').val(item_pkey);

//edited by megha 3_5_19
            url: function (phrase) {
                  return livesite+ "MaterialRequest/getautocompletionsitem_code?item_code=" + phrase+"&store_code="+$("#store_code").val();
            },
            getValue: "item_desc",
            list: {
                onSelectItemEvent: function () {
                    var selectedItem = $('#item_code').getSelectedItemData();
                    console.log(selectedItem);
                    var itemcode = selectedItem.item_code;
                    var item_pkey = selectedItem.item_master_pkey;
                    var qtyuptodate = selectedItem.qtyuptodate;
                    

                    $('#itemcode').val(itemcode);
                    $('#item_code1').val(item_pkey);
                    $('#available_qty').val(qtyuptodate);
                }
            }
            
        };
         function refresh_values(){
         $('#item_code').val('');
         $('#required_qty').val('');
         $('#itemcode').val('');
         $('#item_code1').val('');
         $('#available_qty').val('');
    
        }
        //edited by megha 3_5_19
        //$('#item_code').easyAutocomplete(item_code);
//itemcode
        var itemcode = {
            url: function (phrase) {
                return livesite+ "MaterialRequest/getautocompletionsitemcode?itemcode=" + phrase;
            },
            getValue: "item_code",
            list: {
                onSelectItemEvent: function () {
                    var selectedItem = $('#itemcode').getSelectedItemData();
                    var item_code = selectedItem.item_desc;

                    $('#item_code').val(item_code);
                }
            }
        };
        $('#itemcode').easyAutocomplete(itemcode);

//generate order
        var gstore_code = {
            url: function (phrase) {
                return livesite+ "MaterialRequest/getautocompletionsgstore_code?gstore_code=" + phrase;
            },
            getValue: "site_name",
            list: {
                onSelectItemEvent: function () {
                    var selectedItem = $('#site_name').getSelectedItemData();
                    var gstore_code = selectedItem.site_name;
                    //var site_name = selectedItem.site_name;
                    //var site_name = selectedItem.site_name;
                    // alert(store_code);  
                    //$('#customer_name').val(customer_name);
                    //$('#site_name').val(site_name);
                }
            }
        };
        $('#gstore_code').easyAutocomplete(gstore_code);


        var usersoptions = {
            url: function (phrase) {
                return livesite+ "MaterialRequest/getautocompletions?username=" + phrase;
            },
            getValue: "name",
            list: {
                onSelectItemEvent: function () {
                    var selectedItem = $('#contact_name').getSelectedItemData();
                    var contact_id = selectedItem.contact_id;
                    $('#contact_id').val(contact_id);


                    var div_data = '';
                    div_data += "<div>" + selectedItem.pricename;
                    +"</div>";
                    div_data += "<div>" + selectedItem.pricerate;
                    +"</div>";
                    $("#sales_price_div").show();
                    $("#sales_price_div").html(div_data);
                    //  alert(contact_id);
                    //  reloadDatagrid(site_pkey);
                    // filterAttendanceautocomplete(contact_id);

                }
            }
        };

//        $('#item_code').easyAutocomplete(itemcode);
        $('#contact_name').easyAutocomplete(usersoptions);


        var billno = {
            url: function (phrase) {
                return livesite+ "MaterialRequest/getautocompletionsmr_code?mr_code=" + phrase;
            },
            getValue: "mr_code",
            list: {
                onSelectItemEvent: function () {
                    var selectedItem = $('#bill').getSelectedItemData();
                    var id = selectedItem.mr_pkey; //alert(id)
                    $("#table_appnd").html('<li class="fa fa-spinner fa-spin"></li>');
                    loadtable(id, 1);
                    //value pass 
                    $('#mr_pkey').val(id);
                    //list in all field
                    $('#mr_code').val(selectedItem.mr_code);
                    $('#po_type').val(selectedItem.po_type);
                    $('#mr_date').val(selectedItem.mr_date);
                    $('#customer_name').val(selectedItem.customer_name);
                    $('#customer_po_number').val(selectedItem.customer_po_number);
                    $('#site_name').val(selectedItem.location);
                    $('#store_code').val(selectedItem.store_code);
                    $('#att').val(selectedItem.att);
                    $('#remarks').val(selectedItem.remarks);
                    $('#dispatch').show();
                }
            }
        };
        $('#bill').easyAutocomplete(billno);

    });






    function  loadtable(pid, rowindex) {
        $("#table_appnd").html('<li style="    font-size: -webkit-xxx-large;" class="fa fa-spinner fa-spin"></li><br>Loading Data....');
        $.ajax({
            url: livesite+ 'MaterialRequest/loadtable/' + pid + '/' + rowindex,
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

    function removedaata(index, id) {

        // alert(id);

        if (id) {
            var r = confirm("Do You Want  To Remove The Selected Item")
            if (r == true) {


                var pid = $("#mr_pkey").val();
                $.ajax({
                    url: livesite+'MaterialRequest/deleteorder/' + id,
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

    function editdaata(index, id) {
        var pid = $("#mr_pkey").val();

        var url = livesite+'MaterialRequest/editorder/' + id;
        showModalForm(url);
        //$('#modalDiv').load(url, function () {
        //    $('#modalDiv').modal('show');
        //});
        //alert (pid);

        loadtable(pid, 1);
        //alert(loadtable);
    }


    function refresh() {
//        var r = confirm("Do you want to refresh? ")
//            if (r == true) {
        $("#bill").val('');
        $("#contact_name").val('');
        $("#mr_pkey").val('');
        $("#form-user-master").find('input:text, input:password, input:file, select, textarea,hidden,search').val('');
        $("#form-user-master").find('input:radio, input:checkbox').removeAttr('checked').removeAttr('selected');
        $("#table_appnd").html('');
        $('#dispatch').hide();
        $('#btn-refresh').fadeOut();
        $('#newreqeuest').html('');
        $('#materialtable').datagrid('load');
//            }
    }

    $("#btn-remove").click(function () {
        var id = $("#mr_pkey").val();
        if (id) {
            var r = confirm("Do You Want  To Remove The Selected Item")
            if (r == true) {


                var pid = $("#mr_details_pkey").val();
                $.ajax({
                    url: livesite+'MaterialRequest/deleteordermaster/' + id,
                    success: function (resp) {
                        refresh();
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

    });

    $("#btn-dispatch").click(function () {
        var pid = $("#mr_pkey").val();
        $.ajax({
            url: livesite+'MaterialRequest/dispatchorder/' + pid,
            success: function (resp) {
                refresh();
                $.notify($.parseJSON(resp).msg, {
                    type: 'danger',
                    allow_dismiss: false
                });
            }
        });

    });
 $(document).ready(function () {
        filterItems();
    });
 function filterItems()
    {   
        $("#item_code").select2(
                {
                    //closeOnSelect:false,
                    placeholder: "Search Item By Name... ",
                    allowClear: true,
                    ajax: {
                        url: livesite + "MaterialRequest/itemfilter/",
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
                            console.log(data);
                            
//                    $('#itemcode').val(itemcode);
//                    $('#item_code1').val(item_pkey);
//                    $('#available_qty').val(qtyuptodate);
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
 function getcode() {
        var store = $("#store_code").val();
        var item = $("#item_code").val();
        $.ajax({
            url: livesite+'MaterialRequest/getitem_code/' + store +'/'+ item,
            success: function (resp) {
                 console.log($.parseJSON(resp).item_code);
                    $('#itemcode').val($.parseJSON(resp).item_code);
                     $('#available_qty').val($.parseJSON(resp).qtyuptodate);
                    $('#item_code1').val($.parseJSON(resp).item_master_pkey);
            }
        });
 }
</script>