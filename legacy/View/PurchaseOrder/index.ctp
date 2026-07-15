<style>
    .searchb{
        background-color: #cccccc;
    }
    .hiddenpurchaselist{
        display: none;
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
    function newmode()
    {
        $('#div-criteria1').load(livesite+'PurchaseOrder/index');
    }
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
            <!-- <h2 class="text-primary-18">Purchase Order</h2> -->
              <div class="heading">
                    <h1 class="text-primary-18">Purchase Order</h1>
                    <div class="text-primary-16 home" style="display:flex; align-items:center; gap:10px; cursor:pointer;margin:0 0 10px 0;">
                        <i class="fa" style="font-size:16px;">&#xf104;</i>
                        Back
                    </div>
                    </div>
            <!-- DIRECT CHAT DANGER -->
            <div class="box ">
                <div class="box-body">
                    <div class="col-md-12">
                        <h3 style="width:50%; float: left; ">Purchase Order List</h3>
                        <button class="btn btn-success pull-right" id="purchaselist" onclick="toggleshow(); ">List Purchase Orders</button>
                    </div>
                    
                    
                    <div id="showtables">
                        <div class="form-group col-md-12 ">
                        <div class="col-md-6 pull-right">
                            <label style="text-align:left;" class="col-md-4 control-label">Supplier Name</label>
                            <div class="col-md-8">
                                <select class="form-control js-example-basic-single" style="width : 100%;" id="supplier_codes" onchange="reloadbysupllier(this);">
                                    <option>All</option>
                                    <?php
                                    foreach ($a_suppliers as $val) {
                                        ?>
                                        <option value="<?php echo $val['Contacts']['contact_id'] ?>" ><?php echo $val['Contacts']['first_name'] . ' ' . $val['Contacts']['last_name'].' - '.$val['Contacts']['company_name'] ?> </option>
                                        <?php
                                    }
                                    ?>
                                </select>
                            </div>
                        </div>
                    </div>
                        <table id="purchase" style="width:100%; ">

                    </table>
                        
                    </div>
                    <hr>    
                    <form class="form-horizontal" id="" action="" method="" >
                        <div class="modal-body">
                            <div class="form-group">
                                <div class="col-md-3">
                                    <label style="text-align:left;" class="col-md-4 control-label">Search</label>
                                    <div class="col-md-8">
                                        <input id="bill" name="bill"   class="form-control">
                                    </div>
                                </div>
                                <button type="button" id="btn-submit" onclick="newmode();" class="btn btn-primary">New</button>
                                <button type="button" id="btn-refresh" value="Refresh" onclick="refresh();" accesskey=""class="btn btn-danger" data-dismiss="modal">Cancel</button>
                            </div>
                        </div>
                    </form>
                    <div class="spacer-20 divider"></div>
                    <h1 class="page-header">CREATE  PURCHASE ORDER</h1>
                    <form class="form-horizontal" id="form-user-master" method="post" action="<?php echo $this->webroot; ?>PurchaseOrder/save" >
                        <div class="row bg-success" style="height:auto;"> 
                            <h4 style="padding-left:20px;">PURCHASE ORDER</h4>
                            <div class="modal-body">
                                <div class="form-group">
                                    <div class="col-md-4">
                                        <label class="col-md-4 control-label" style="text-align:left;" >PO Number</label>
                                        <div class="col-md-6">
                                            <input id="po_number" name="po_number" value="<?php echo isset($arr_att['0']['purchaseorder']['po_number']) ? $arr_att['0']['purchaseorder']['po_number'] : mt_rand(); ?>" type="text"  class="form-control input-md" required="required" readonly="readonly">
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <label  class="col-md-4 control-label" style="text-align:left;" >PO Date<label style="color:red">*</label></label>
                                        <div class="col-md-6">
                                            <input id="current_date" name="po_date" value="<?php echo date("Y-m-d"); ?>" type="text"  class="form-control input-md" required="required" readonly="readonly">
                                        </div> 
                                    </div>
                                    <div class="col-md-4">
                                        <label  class="col-md-4 control-label" style="text-align:left;">Expected Date<label style="color:red">*</label></label>
                                        <div class="col-md-6">                               
                                            <input id="exp_date" name="expected_date" required="required" value="<?php echo isset($arr_att['0']['purchaseorder']['expected_date']) ? $arr_att['0']['purchaseorder']['expected_date'] : ''; ?>" type="text"  class="form-control input-md" >
                                        </div>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <!--                <div class="col-md-4">
                                                        <label class="col-md-4 control-label" style="text-align:left;">Store Code<label style="color:red;">*</label></label>
                                                        <div class="col-md-6">
                                                            <input id="store_code" name="store_code" value="" type="text"  class="form-control input-md"  required="required">
                                                        </div>
                                                    </div>-->
                                    <!--                <div class="col-md-4">
                                                        <label class="col-md-4 control-label" style="text-align:left;">PO Type<label style="color:red;">*</label></label>
                                                        <div class="col-md-6">
                                                            <input id="po_type" name="po_type" value="<?php // echo isset($arr_att['0']['purchaseorder']['po_type']) ? $arr_att['0']['purchaseorder']['po_type'] : '';   ?>" type="text"  class="form-control input-md"  required="required">
                                                        </div>
                                                    </div>-->
<!--                                    <div class="col-md-4">
                                        <label  class="col-md-4 control-label"  style="text-align:left;">Location</label>
                                        <div class="col-md-6">
                                            <input id="location" name="location" value="" type="text"  class="form-control input-md"  >
                                            <input id="location_code" name="location_code" value="" type="hidden"  class="form-control input-md"  >
                                        </div> 
                                    </div>-->
                                    <div class="col-md-4">
                                        <label  class="col-md-4 control-label" style="text-align:left;">Supplier Name<label style="color:red">*</label></label>
                                        <div class="col-md-6">                               
                                            <input id="supplier_name"  name="supplier_name" value="" type="text"  class="form-control input-md" >
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="col-md-4 control-label" style="text-align:left;" >Supplier Code<label style="color:red;">*</label></label>
                                        <div class="col-md-6">
                                            <input id="supplier_code"  name="supplier_code" value="" type="text"  class="form-control input-md" >
                                        </div>
                                    </div>

                                <div class="col-md-4">
                                    <label  class="col-md-4 control-label" style="text-align:left;" >Remark</label>
                                    <div class="col-md-6">
                                        <input id="remarks" name="remarks" value="<?php echo isset($arr_att['0']['purchaseorder']['remarks']) ? $arr_att['0']['purchaseorder']['remarks'] : ''; ?>" type="text"  class="form-control input-md"  >
                                    </div> 
                                </div>

                            
                                </div>
                            </div>
                            
                            <div class="modal-footer">
                                <input type="hidden" name="po_pkey" id="po_pkey"  >
                                <!--                <button type="submit" id="btn-submit" class="btn btn-primary">Add</button>-->
                                <!--                <input type="hidden" name="po_pkey" id="mpkey" value="<?php // echo isset($arr_att['0']['material_request']['po_pkey']) ? $arr_att['0']['material_request']['po_pkey'] : '';    ?>">-->
                                <input type="hidden" name="po_fkey">
                            </div>
                        </div>
                    </form>


                    <div id="table_appnd" class="table table-bordered" style="margin-top: 10px; ">

                    </div>

                    <div class="modal-footer">

                    </div>
                    <div class="col-md-3">
                        <label style="text-align:left;" class="col-md-4 control-label">Search Item Name</label>
                        <div class="col-md-8">
                            <input id="item" name="item_desc"   class="form-control">
                        </div>
                    </div>
                    <div class="modal-footer">

                    </div>
                    <table id="materialtable" >

                    </table>


                    <div class="col-xs-8">
                    </div >


                    <div id="dispatch" style="display: none;">

                        <input type="button" id="btn-remove" value="Remove order" class="btn btn-primary">
                    </div>
                </div>
            </div>
        </div>
    </div>
</section> 
<script>
    
    
    function toggleshow(){
//        alert("Hi");
        $('#showtables').toggleClass("hiddenpurchaselist");
        $('#purchase').datagrid('load');
    }
//suppilernarme
    function  checksuppilername()
    {
        var suppiler = $('#supplier_name').val();
        if (suppiler != '')
        {
            $("#supplier_code").val('');
        }
    }
//supplilercode
    function checksuppliercode() {
        var suppilercode = $('#supplier_code').val();
        if (suppilercode != '')
        {
            $('#supplier_name').val('');
        }
    }

    function reloadbysupllier(s) {
        $('#purchase').datagrid('load', {
            supplier_code: $(s).val()

        });
    }

    function refresh() {
        $("#bill").val('');
        $("#po_date").val('');
        $("#current_date").val('');
        $("#exp_date").val('');
        $("#po_type").val('');
        $("#location").val('');
        $("#supplier_name").val('');
        $("#supplier_code").val('');
        $("#store_code").val('');
        $("#remarks").val('');
        $("#form-user-master").find('input:text, input:password, input:file, select, textarea,hidden,search').val('');
        $("#form-user-master").find('input:radio, input:checkbox').removeAttr('checked').removeAttr('selected');
        $("#table_appnd").html('');
        $('#dispatch').hide();
    }


    function test(mr_pkey) {
        var data = $("#form-user-master").serialize();
        // alert(data)

        $.ajax({
            type: "POST",
            data: data,
            url: livesite+'PurchaseOrder/save/',
            success: function (response) {

                var pk = $.parseJSON(response).pk;
                $("#po_pkey").val(pk);

//                                                  loadtable(pk,1);

                var url = livesite+'PurchaseOrder/form/' + mr_pkey + '/' + pk;
                showLargeModalForm(url);
                //$('#modalDiv').load(url, function () {
                //    $('#modalDiv').modal('show');
                //});

            }
        });
    }

    function loaddata(selectedItem) {
        var id = selectedItem.po_pkey;
        //alert(id);
        loadtable(id, 1);
        //list in all field               
        $('#po_number').val(selectedItem.po_number);
        $('#po_pkey').val(selectedItem.po_pkey);
        $('#po_date').val(selectedItem.po_date);
        $('#expected_date').val(selectedItem.expected_date);
        $('#po_type').val(selectedItem.po_type);
        $('#location').val(selectedItem.location);
        $('#store_code').val(selectedItem.location);
        $('#supplier_name').val(selectedItem.supplier_name);
        $('#supplier_code').val(selectedItem.supplier_code);
        $('#remarks').val(selectedItem.remarks);
    }
    

    function  loadtable(pk, rowindex) {
        $.ajax({
            url: livesite+'PurchaseOrder/loadtable/' + pk + '/' + rowindex,
            success: function (response) {
                //alert(response);
                var data = response;
                var div_data = '';
                div_data += "<div>" + data + "</div>"
                $("#table_appnd").html(div_data);

            }
        });

    }

    function editdaata(index, id) {

        var po_pkey = $("#po_pkey").val();

        var url = livesite+'PurchaseOrder/edit/' + id + '/' + po_pkey;
        showLargeModalForm(url);
        //$('#modalDiv').load(url, function () {
        //    $('#modalDiv').modal('show');
        //});
//        alert(id);
//         alert(po_pkey);
        //alert(pk1);
        $("#table_appnd").html("");
        $('#materialtable').datagrid('reload');
        loadtable(po_pkey, 1);
       
        //editdaata(index, 1);
        //alert(loadtable);
    }


    //data gride  
    $(document).ready(function () {
        $('#purchase').datagrid({
            url: livesite+'PurchaseOrder/purchaseorders',
            rownumbers: true,
            title: "Purchase Order lists",
            fitColumns: true,
            singleSelect: true,
            autoRowHeight: false,
            pagination: true,
            width: '99%',
            pageSize: 10,
            toolbar: [{
                    iconCls: 'icon-add',
                    text: "SELECT TO PO",
                    handler: function () {
                        var row = $('#purchase').datagrid('getSelected');
                        //console.log(row);
                        if (row) {

                            var mr_pkey = row.mr_pkey;
                            loaddata(row);

                        } else
                        {
                            alert("Please Choose A Purchase Order");
                        }




                    }
                },
                {
                    iconCls: 'icon-add',
                    text: "Details",
                    handler: function () {
                        var row = $('#purchase').datagrid('getSelected');
                        //console.log(row);
                        if (row) {

                            var mr_pkey = row.po_pkey;
                            showLargeModalForm(livesite + 'PurchaseOrder/details/' + row.po_pkey)

                        } else
                        {
                            alert("Please Choose A Purchase Order");
                        }




                    }
                }],
            columns: [[
                    {field: 'po_number', title: 'PO Number', width: 100, sortable: true, order: 'asc', editor: 'textbox'},
                    {field: 'expected_date', title: 'Expected Date', width: 100, sortable: true, order: 'asc', editor: 'textbox'},
                    {field: 'supplier_name', title: 'Supplier Name', width: 100, sortable: true, order: 'asc', editor: 'textbox'},
//                    {field: 'location', title: 'Location', width: 100, sortable: true, order: 'asc', editor: 'textbox'},
                    {field: 'remarks', title: 'Remarks', width: 100, sortable: true, order: 'asc', editor: 'textbox'},
                    {field: 'po_date', title: 'PO Date', width: 100, sortable: true, order: 'asc', editor: 'textbox'},
                ]],
        });
        $('#showtables').toggleClass("hiddenpurchaselist");
        
        $('.js-example-basic-single').select2();
        
        $('#materialtable').datagrid({
            url: livesite+'PurchaseOrder/materialtable',
            rownumbers: true,
            title: "Material Request List",
            fitColumns: true,
            singleSelect: true,
            autoRowHeight: false,
            pagination: true,
            width: '99%',
            pageSize: 10,
            toolbar: [{
                    iconCls: 'icon-add',
                    text: "ADD TO PO",
                    handler: function () {
                        var row = $('#materialtable').datagrid('getSelected');
                        //console.log(row);
                        var suplier_name = $('#supplier_name').val();
                        var location = $('#location').val();
                        var expected_date = $('#exp_date').val();
                        if (suplier_name == '') {
                            alert("Select Supplier")
                            return false;
                        }
                        if (expected_date == '') {
                            alert("Select Expected Date")
                            return false;
                        }
//                        if (location == '') {
//                            alert("Select location")
//                            return false;
//                        }
                        if (row) {

                            var mr_pkey = row.mr_pkey;
                            test(mr_pkey);

                        } else
                        {
                            alert("Please Choose A Purchase Order");
                        }




                    }
                }],
            columns: [[
                    {field: 'mr_code', title: 'Material Request Code', width: 100, sortable: true, order: 'asc', editor: 'textbox'},
                    {field: 'mr_date', title: 'Required Date', width: 100, sortable: true, order: 'asc', editor: 'textbox'},
//                    {field: 'location', title: 'Location', width: 100, sortable: true, order: 'asc', editor: 'textbox'},
                    {field: 'store_code', title: 'Store Code', width: 100, sortable: true, order: 'asc', editor: 'textbox'},
                    {field: 'required', title: 'Total Required', width: 100, sortable: true, order: 'asc', editor: 'textbox'},
                    {field: 'ordered', title: 'Total Ordered', width: 100, sortable: true, order: 'asc', editor: 'textbox'},
                    {field: 'pending', title: 'Total Pending', width: 100, sortable: true, order: 'asc', editor: 'textbox'},
                    {field: 'remarks', title: 'Remark', width: 100, sortable: true, order: 'asc', editor: 'textbox'},
                ]],
        });



        function removedaata(index, id) {

            // alert(id);

            if (id) {
                var r = confirm("Do You Want  To Remove The Selected Item")
                if (r == true) {


                    var pk = $("#direct_mr_pkey").val();
                    $.ajax({
                        url: livesite+'PurchaseOrder/deleteorder/' + id,
                        success: function (resp) {
                            loadtable(pk, 1);
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

//daepiker
        var currentDate = new Date();
        //$('#current_date').datepicker("setDate", currentDate);
        $( function() {
        $('#exp_date').datepicker({ 
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
            //console.log($('#subsave').serialize());
            //load table    
            $('#form-user-master').ajaxSubmit({
                success: function (resp) {
                    var pk = $.parseJSON(resp).pk;
                    $("#direct_mr_pkey").val(pk);
                    //alert(pk);
                    $("#item_code").focus();

                    loadtable(pk, 1);
                    $("#ordered_qty").val('');
                    $("#item_code").val('');
                    $("#uom").val('');
                    $("#po_rate").val('');

                    $.notify($.parseJSON(resp).msg, {
                        type: 'success',
                        allow_dismiss: false
                    });
                }

            });

        });
// search all po 
        var ponumber = {
            url: function (phrase) {
                return livesite+"PurchaseOrder/getautocompletionspo_number?po_number=" + phrase;
            },
            getValue: "po_number",
            list: {
                onSelectItemEvent: function () {
                    var selectedItem = $('#bill').getSelectedItemData();
                    //passing id to cont                  
                    loaddata(selectedItem);
                }
            }
        };
        $('#bill').easyAutocomplete(ponumber);


// search all po 
        var itemfind = {
            url: function (phrase) {
                return livesite+"PurchaseOrder/searchm?item_desc=" + phrase;
            },
            //field name
            getValue: "item_desc",
            list: {
                onClickEvent: function () {
                    var selectedItem = $('#item').getSelectedItemData();
                    //passing id to cont                  
                    var id = selectedItem.item_master_pkey;

                    var item_desc = $('#item_desc').val();
                    $('#materialtable').datagrid('load', {
                        item_desc: id

                    });



                    var item_desc = selectedItem.item_desc;


                    //alert(id);
                    //list in all field               
                }
            }
        };
        $('#item').easyAutocomplete(itemfind);

        //location.
        var location = {
            url: function (phrase) {
                return livesite+"PurchaseOrder/getautocompletionslocation?location=" + phrase;
            },
            getValue: "location_name",
            list: {
                onSelectItemEvent: function () {
                    var selectedItem = $('#location').getSelectedItemData();
                    $('#location_code').val(selectedItem.location_code);
                }
            }
        };
        $('#location').easyAutocomplete(location);
//supplier name
        var supplier_name = {
            url: function (phrase) {
                return livesite+"PurchaseOrder/getautocompletionssupplier_name?supplier_name=" + phrase;
            },
            getValue: "name",
            list: {
                onSelectItemEvent: function () {
                    var selectedItem = $('#supplier_name').getSelectedItemData();
                    //$('#supplier_name').val(selectedItem.first_name);
                    console.log("ser");
                    console.log(selectedItem.contact_id);
                    $('#supplier_code').val(selectedItem.contact_id);
                }
            }
        };
        $('#supplier_name').easyAutocomplete(supplier_name);
//supplier_code
        var supplier_code = {
            url: function (phrase) {
                return livesite+"PurchaseOrder/getautocompletionssupplier_code?supplier_code=" + phrase;
            },
            getValue: "contact_id",
            list: {
                onSelectItemEvent: function () {
                    var selectedItem = $('#supplier_code').getSelectedItemData();

                    //$('#supplier_name').val(selectedItem.first_name);
                    $('#supplier_code').val(selectedItem.contact_id);
                    console.log(selectedItem.contact_id);
                    console.log("ser");
                }
            }
        };
        $('#supplier_code').easyAutocomplete(supplier_code);
// search from   store    
        var store_code = {
            url: function (phrase) {
                return livesite+"PurchaseOrder/getautocompletionsstore_code?store_code=" + phrase;
            },
            getValue: "store_code",
            list: {
                onSelectItemEvent: function () {
                    var selectedItem = $('#store_code').getSelectedItemData();
                    var store_master_pkey = selectedItem.store_master_pkey;
                    $('#store_master_pkey').val(store_master_pkey);
                }
            }
        };
        $('#store_code').easyAutocomplete(store_code);
    });

</script>

