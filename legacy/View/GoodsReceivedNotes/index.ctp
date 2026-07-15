<style>
    .searchb{
        background-color: #cccccc;
    }
    .hiddenpurchaselist {
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
        $('#div-criteria1').load(livesite+'GoodsReceivedNotes/index');
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
<!-- <section class="content-header">
    <legend style="text-align:left;" class="text-primary-18"> Goods Received Notes   </legend>
</section> -->
 <div class="content-header heading">
                    <h1 class="text-primary-18"> Item Master</h1>
                    <div class="text-primary-16 home" style="display:flex; align-items:center; gap:10px; cursor:pointer;margin:0;">
                        <i class="fa" style="font-size:16px;">&#xf104;</i>
                        Back
                    </div>
                    </div>
<section class="content">
    <div class="row">
        <div class="col-md-12">
            <!-- DIRECT CHAT DANGER -->
            <div class="box ">

                <div class="box-body">
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
                                <button type="button" class="btn btn-success pull-right" id="purchaselists" onclick="toggleshow(); ">List Goods Received  Notes </button>
                            </div>
                        </div>
                        <div class="showtable" style="margin-top: 20px; margin-bottom: 20px; ">
                            <table id="materialtable" >

                    </table>
                        </div>
                    </form>
                    <form class="form-horizontal" style="padding-left:1px; padding-right:8px ; border:1px;" id="form-user-master" method="post" action="<?php echo $this->webroot; ?>GoodsReceivedNotes/save" >
                        <div class="row bg-success" style="height:auto;"> 
                            <h4 style="padding-left:20px;">Goods Received Notes</h4>
                            <div class="modal-body">
                                <div class="form-group">
                                    <div class="col-md-4">
                                        <label class="col-md-4 control-label" style="text-align:left;" >GR Number</label>
                                        <div class="col-md-6">
                                            <input id="gr_number" name="gr_number" value="<?php echo isset($arr_att['0']['purchaseorder']['gr_number']) ? $arr_att['0']['purchaseorder']['gr_number'] : mt_rand(); ?>" type="text"  class="form-control input-md" required="required" readonly="readonly">
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <label  class="col-md-5 control-label" style="text-align:left;">GR date<label style="color:red;">*</label></label>
                                        <div class="col-md-6">                               
                                            <input id="current_date" name="gr_date" value="<?php echo date("Y-m-d"); ?>" type="text"  class="form-control input-md" required="required">
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <label style="text-align:left;" class="col-md-5 control-label" >Store Code<label style="color:red;">*</label></label>
                                        <div class="col-md-6">
                                            <input id="store_codes"   name="store_codes" value="" type="text"  class="form-control input-md"  >
                                            <input type="hidden" name="store_code" id="store_code">
                                        </div>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <div class="col-md-4">
                                        <label class="col-md-4 control-label" style="text-align:left;">Remark</label>
                                        <div class="col-md-6">
                                            <input id="remark" name="remark" value="<?php // echo isset($arr_att['0']['purchaseorder']['remark']) ? $arr_att['0']['purchaseorder']['remark'] : '';   ?>" type="text"  class="form-control input-md"  required="required">
                                        </div>
                                    </div>
                                </div>
                                <div class="modal-footer">
                                    <input type="hidden" name="grn_fkey" id="ppkey"  class="">
                                    <input type="hidden" name="grn_fkey">
                                </div>
                            </div>

                        </div>

                    </form>


                    <div id="table_appnd" style="margin-top:40px;">

                    </div>

                    <div class="modal-footer">

                    </div>
                    <div class="form-group col-md-12">
                        <div class="col-md-6 pull-right">
                            <label style="text-align:left;" class="col-md-4 control-label">Supplier Name</label>
                            <div class="col-md-8">
                                <select class="form-control js-example-basic-single" id="supplier_code" onchange="reloadbysupllier(this);">
                                    <option>All</option>
                                    <?php
                                        foreach($a_suppliers as $val){
                                    ?>
                                    <option value="<?php echo $val['Contacts']['contact_id'] ?>" ><?php echo $val['Contacts']['first_name'].' '.$val['Contacts']['last_name'].' - '.$val['Contacts']['company_name'] ?> </option>
                                    <?php
                                        }
                                    ?>
                                </select>
                            </div>
                        </div>
                     </div>
                    <table id="purchaselist" >

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
        $('.showtable').toggleClass("hiddenpurchaselist");
        $('#materialtable').datagrid('load' );
    }
    
    function reloadbysupllier(s){
            $('#purchaselist').datagrid('load', {
                            supplier_code: $(s).val()

                        });
        }


    function test(po_pkey) {
        var data = $("#form-user-master").serialize();
        //alert(data)

        $.ajax({
            type: "POST",
            data: data,
            url: livesite+'GoodsReceivedNotes/save/' + po_pkey,
            success: function (response) {

                var pk = $.parseJSON(response).pk;
                $("#grn_fkey").val(pk);
                //alert (pk);
                //alert (grn_pkey);
                var store_code = $('#store_code').val();
                var current_date = $('#current_date').val();
//                                                  loadtable(pk,1);
                //alert(store_code);
                 if(current_date){
               
                if(store_code){
                var url = livesite+'GoodsReceivedNotes/form/' + po_pkey + '/' + pk +'/'+store_code;
                showLargeModalForm(url);
                //$('#modalDiv').load(url, function () {
                //    $('#modalDiv').modal('show');
                //});
            }else{
                alert("Select a Store First");
            }
                 }
                 else{
                alert("Select a GR date");
            }
            }
        });
    }

    function  loadtable(pk, rowindex) {
        $.ajax({
            url: livesite+'GoodsReceivedNotes/loadtable/' + pk + '/' + rowindex,
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

        var grn_id = $("#grn_pkey").val();
        //alert (grn_id);
        var url = livesite+'GoodsReceivedNotes/edit/' + id + '/' + grn_id;
        $('#modalDiv').load(url, function () {
            $('#modalDiv').modal('show');
        });
        //alert(response);
        // alert(grn_pkey);
        //alert(id);
        //loadtable(pk, 1);
        //alert(loadtable);
    }


//function findtable()
//    {
//        var store = $("#store_code").val();
//        var url=("GoodsReceivedNotes/purchaselist");
//         $.post(url, {store: store})
//    }

    $(document).ready(function () {
        
        $('.js-example-basic-single').select2();
        $('#materialtable').datagrid({
            url: livesite+'GoodsReceivedNotes/pgrnlist',
            pagination: true,
            singleSelect: true,
            rownumbers: true,
            title: "Goods Received Notes List",
            fitColumns: true,
//            autoRowHeight: false,
//            width: '99%',
//            pageSize: 10,
            toolbar: [{
                    iconCls: 'icon-add',
                    text: "View Details ",
                    handler: function () {
                        var row = $('#materialtable').datagrid('getSelected');
                        //console.log(row);
                        if (row) {

                            var mr_pkey = row.grn_pkey;
                            var url = livesite+'GoodsReceivedNotes/showgrndetails/' + mr_pkey;
                            showLargeModalForm(url);

                        } else
                        {
                            alert("Please Choose a row first ");
                        }




                    }
                }],
            columns: [[
                    {field: 'gr_number', title: 'GR Number', width: '25%', sortable: true, order: 'asc', editor: 'textbox'},
                    {field: 'gr_date', title: 'GR Date', width: '25%', sortable: true, order: 'asc', editor: 'textbox'},
//                    {field: 'location', title: 'Location', width: 100, sortable: true, order: 'asc', editor: 'textbox'},
                    {field: 'store_location', title: 'Store Name', width: '25%', sortable: true, order: 'asc', editor: 'textbox'},
                    {field: 'remarks', title: 'Remarks', width: '24%', sortable: true, order: 'asc', editor: 'textbox'},
                ]],
        });
        
        $('.showtable').toggleClass("hiddenpurchaselist");
        
        $('#purchaselist').datagrid({
            url: livesite+'GoodsReceivedNotes/purchaselist',
            rownumbers: true,
            title: "Purchase Order List",
            fitColumns: true,
            singleSelect: true,
            autoRowHeight: false,
            pagination: true,
            width: '99%',
            pageSize: 10,
            toolbar: [{
                    iconCls: 'icon-add',
                    text: "ADD TO GR",
                    handler: function () {
                        var row = $('#purchaselist').datagrid('getSelected');
                        //console.log(row);

                        if (row) {

                            var po_pkey = row.po_pkey;
                            test(po_pkey);
                            //alert (po_pkey);

                        } else
                        {
                            alert("Please Choose A Purchase Order");
                        }




                    }
                }],
            columns: [[
                    {field: 'po_number', title: 'Purchase Order Code', width: 100, sortable: true, order: 'asc', editor: 'textbox'},
                    {field: 'po_date', title: 'Purchase Date', width: 100, sortable: true, order: 'asc', editor: 'textbox'},
                    {field: 'expected_date', title: 'Expected Date', width: 100, sortable: true, order: 'asc', editor: 'textbox'},
//                    {field: 'location', title: 'Location', width: 100, sortable: true, order: 'asc', editor: 'textbox'},
                    {field: 'supplier_name', title: 'Client Name', width: 100, sortable: true, order: 'asc', editor: 'textbox'},
                    {field: 'remarks', title: 'Remark', width: 100, sortable: true, order: 'asc', editor: 'textbox'},
                ]],
        });


        function removedaata(index, id) {

            // alert(id);

            if (id) {
                var r = confirm("Do You Want  To Remove The Selected Item")
                if (r == true) {


                    var pk = $("#direct_grn_pkey").val();
                    $.ajax({
                        url: livesite+'GoodsReceivedNotes/deleteorder/' + id,
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

        var currentDate = new Date();
        $('#current_date').datepicker({
            format: 'yyyy-mm-dd',       
            autoclose: true,
            startDate:'-15d',
            endDate: '0d'});
        $('#fromdate').datepicker({
            dateFormat: 'yyyy-mm-dd'
        });
        $('#todate').datepicker({
            dataFormat: 'yyyy-mm-dd'
        });
    $('#current_date').on('change', function (event) {
            var current_Date = $('#current_date').val();
            //alert('hai');
            event.preventDefault();
            $('#purchaselist').datagrid('load', {
                        po_date: current_Date
                    });
    });







        $('#form-user-master').on('submit', function (event) {

            event.preventDefault();
            //console.log($('#subsave').serialize());
            //load table    
            $('#form-user-master').ajaxSubmit({
                success: function (resp) {
                    var pk = $.parseJSON(resp).pk;
                    $("#direct_grn_pkey").val(pk);
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
                return livesite+"GoodsReceivedNotes/getautocompletionsgr_number?gr_number=" + phrase;
            },
            getValue: "gr_number",
            list: {
                onSelectItemEvent: function () {
                    var selectedItem = $('#bill').getSelectedItemData();
                    //passing id to cont                  
                    var id = selectedItem.grn_pkey;
                    //alert(id);
                    loadtable(id, 1);
                    //list in all field               
                    $('#gr_number').val(selectedItem.gr_number);
                    $('#grn_pkey').val(selectedItem.grn_pkey);
                    $('#gr_create').val(selectedItem.gr_create);
                    $('#gr_date').val(selectedItem.gr_date);
                    $('#store_code').val(selectedItem.store_code);
                    $('#remark').val(selectedItem.remark);

                }
            }
        };
        $('#bill').easyAutocomplete(ponumber);
// search from   store    
        var store_code = {
            url: function (phrase) {
                return livesite+"GoodsReceivedNotes/getautocompletionsstore_code?store_code=" + phrase;
            },
            getValue: "store_code",
            list: {
                onSelectItemEvent: function () {
                    var selectedItem = $('#store_codes').getSelectedItemData();
                    var id = selectedItem.store_master_pkey;
                    $('#store_code').val(id);
                    $('#purchaselist').datagrid('load', {
                        store_code: id
                    });
                }
            }
        };
        $('#store_codes').easyAutocomplete(store_code);

// search all po 
        var itemfind = {
            url: function (phrase) {
                return livesite+"GoodsReceivedNotes/searchm?po_number=" + phrase;
            },
            //field name
            getValue: "po_number",
            list: {
                onClickEvent: function () {
                    var selectedItem = $('#item').getSelectedItemData();
                    //passing id to cont                  
                    var id = selectedItem.item_master_pkey;

                    var po_number = $('#po_number').val();
                    $('#purchaselist').datagrid('load', {
                        po_number: id

                    });


                    var po_number = selectedItem.po_number;


                    //alert(id);
                    //list in all field               
                }
            }
        };
        $('#item').easyAutocomplete(itemfind);

    });








</script>

