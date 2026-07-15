<style>
    .searchb{
        background-color: #cccccc;
    }
</style>
<script>
    function newmode()
    {
        $('#div-criteria1').load('MaterialRequest/index');
    }
    
function generatereport(){ 
    
      var  storecode =$('#gstore_code').val();
      var  itemcode =$('#gitem_code').val();
      var  todate=$('#todate').val();
      var  fromdate=$('#fromdate').val();
      
      $.post( "MaterialRequest/generatereport", {fromdate: fromdate,todate:todate,item_code:itemcode,store_code:storecode})
                            .done(function (data) {
                            $("#reportResultContainer").html(data)  ;
                            });
 
}
//function adddiv()
//{
//    var id =$('#item_code').val();
//    alert(id);
//}
</script>



<form class="form-horizontal" id="" action="" method="" >
    <div class="modal-body">
        <div class="form-group">
            <div class="col-md-3">
                <label style="text-align:left;" class="col-md-4 control-label">Search</label>
                <div class="col-md-8">
                    <input id="bill" name="bill"   class="form-control">
                </div>
            </div>
            <button type="reset" id="btn-submit" onclick="newmode();" class="btn btn-primary">New</button>
            <button type="button" id="btn-refresh" value="Refresh" onclick="refresh();" accesskey=""class="btn btn-danger" data-dismiss="modal">Cancel</button>
        </div>
    </div>
</form>
<form style="" >
   
        <div class="form-group">
            <div class="col-md-3">
                <label style="text-align:left;" class="col-md-4 control-label">From Date</label>
                <div class="col-md-8">
                    <input id="fromdate" name="fromdate"   class="form-control">
                </div>
            </div>
            <div class="col-md-2">
                <label style="text-align:left;" class="col-md-4 control-label">To Date</label>
                <div class="col-md-8">
                    <input id="todate" name="todate"   class="form-control">
                </div>
            </div>
            <div class="col-md-3">
                <label style="text-align:left;" class="col-md-4 control-label">Store Name</label>
                <div class="col-md-8">
                    <input id="gstore_code" name="gstore_code"   class="form-control">
                </div>
            </div>
            <div class="col-md-3">
                <label style="text-align:left;" class="col-md-4 control-label">Item Name</label>
                <div class="col-md-8">
                   <select id="gitem_code"  class="form-control" name="item_code"  required="reduired"   >
                            <option value="" >-select-</option>
                            <?php
                            foreach ($all_item as $value) {

                                //$selected = ($arr_att['0']['poitemorder']['store_code'] == $value['Store']['store_master_pkey']) ? 'selected="selected"' : '';
                                echo '<option value="' . $value['Item']['item_master_pkey'] . ' '.valnew.'" ' . $selected . '>' . $value['Item']['item_desc'] . '   </option>';
                            }
                            ?>
                        </select>
                </div>
            </div>
            <button type="button"  onclick="generatereport()" class="btn btn-info">Generate</button>
        </div>
   
</form>
<h1 class="page-header">Create Material Request</h1>
<form class="form-horizontal" style="padding-left:1px; padding-right:8px ; border:1px;" id="form-user-master" method="post" action="<?php echo $this->webroot; ?>MaterialRequest/save" >


    <div class="row bg-success" style="height:auto;"> 
        <h4 style="padding-left:20px;">Add Basic Details</h4>
        <div class="modal-body">
            <div class="form-group">
                <div class="col-md-4">
                    <label style="text-align:left;" class="col-md-5 control-label" >Material Request Code<label style="color:red;">*</label></label>
                    <div class="col-md-6">
                        <input id="mr_code" name="mr_code" value="<?php echo isset($arr_att['0']['material_request']['mr_code']) ? $arr_att['0']['material_request']['mr_code'] : mt_rand(); ?>" type="text"  class="form-control input-md" required="required" >

                    </div>
                </div>
                <div class="col-md-4">
                    <label style="text-align:left;" class="col-md-4 control-label" >Required date</label>
                    <div class="col-md-6">
                        <input id="mr_date" placeholder="select date" name="mr_date" value="<?php echo isset($arr_att['0']['material_request']['mr_date']) ? $arr_att['0']['material_request']['mr_date'] : ''; ?>" type="text"  class="form-control input-md" >
                    </div> 
                </div>
                <div class="col-md-3">
                    <label style="text-align:left;" class="col-md-4 control-label" >Site Name<lable style="color:red;">*</lable></label>
                    <div class="col-md-8">
                        <input id="site_name" name="location" value="<?php echo isset($arr_att['0']['material_request']['location']) ? $arr_att['0']['material_request']['location'] : ''; ?>" type="text"  class="form-control input-md" required="required"  onkeyup="checksite();" >
                    </div>
                </div>
            </div>
            <div class="form-group">
                <div class="col-md-4">
                    <label style="text-align:left;" class="col-md-5 control-label" >Client Name<label style="color:red;">*</label></label>
                    <div class="col-md-6">
                        <input id="customer_name" name="customer_name" value="<?php echo isset($arr_att['0']['material_request']['customer_name']) ? $arr_att['0']['material_request']['customer_name'] : ''; ?>" type="text"  class="form-control input-md"  onkeyup="checkname();"  >
                    </div>
                </div>
                <div class="col-md-4">
                    <label style="text-align:left;" class="col-md-4 control-label" >Client Po No<label style="color:red;"></label></label>
                    <div class="col-md-6">
                        <input id="customer_po_number" name="customer_po_number" value="<?php echo isset($arr_att['0']['material_request']['customer_po_number']) ? $arr_att['0']['material_request']['customer_po_number'] : ''; ?>" type="text"  class="form-control input-md" onkeyup="checkpo();" >
                    </div>
                </div>
                <div class="col-md-3">
                    <label style="text-align:left;" class="col-md-4 control-label" >Store Code<label style="color:red;">*</label></label>
                    <div class="col-md-8">
                        <select id="store_code" class="form-control" name="store_code" required="required" >
                            <option value="" >---select---</option>
                            <?php
                            foreach ($all_store as $value) {

                                $selected = ($arr_att['0']['material_request']['store_code'] == $value['Store']['store_master_pkey']) ? 'selected="selected"' : '';
                                echo '<option value="' . $value['Store']['store_master_pkey'] . '" ' . $selected . '>' . $value['Store']['store_code'] . '   </option>';
                            }
                            ?>    
                        </select>                      
                    </div>
                </div>
            </div>
            <div class="form-group">

                <div class="col-md-4">
                    <label style="text-align:left;" class="col-md-5 control-label" >Remark</label>
                    <div class="col-md-6">
                        <input id="remarks" name="remarks" value="<?php echo isset($arr_att['0']['material_request']['remarks']) ? $arr_att['0']['material_request']['remarks'] : ''; ?>" type="text"  class="form-control input-md" >
                    </div> 
                </div>
            </div>
        </div>

            <h4 style="padding-left:20px;">Material Request Details</h4>
            <div class="form-group">


                <div class="col-md-4">
                    <label style="text-align:left;" class="col-md-5 control-label" >Item Name<label style="color:red;">*</label></label>
                    <div class="col-md-6">
                        <select id="item_code"  class="form-control itemid" name="item_code"  required="reduired"   >
                            <option value="" >-select-</option>
                            <?php
                            foreach ($all_item as $value) {

                                //$selected = ($arr_att['0']['poitemorder']['store_code'] == $value['Store']['store_master_pkey']) ? 'selected="selected"' : '';
                                echo '<option value="' . $value['Item']['item_master_pkey'] . ' '.valnew.'" ' . $selected . '>' . $value['Item']['item_desc'] . '   </option>';
                            }
                            ?>
                        </select>
                    </div> 
                </div>
                <div class="col-md-4">
                    <label style="text-align:left;" class="col-md-4 control-label" >Item Code<label style="color:red;">*</label><label style="color:red;"></label></label>
                    <div class="col-md-8">
                        <input type="text" id="required_qty" class="form-control" name="required_qty" required="required" onchange="itemfind();" />   
                    </div>
                </div>
                <div class="col-md-4">
                    <label style="text-align:left;" class="col-md-4 control-label" >Required Qty<label style="color:red;">*</label><label style="color:red;"></label></label>
                    <div class="col-md-8">
                        <input type="number" id="required_qty" class="form-control" name="required_qty" required="required" onchange="itemfind();" />   
                    </div>
                </div>
                <div class="form-group">
                </div>

                <div class="modal-footer">
                    <input type="hidden" name="mr_pkey" id="mr_pkey" value="<?php echo isset($arr_att['0']['material_request']['mr_pkey']) ? $arr_att['0']['material_request']['mr_pkey'] : ''; ?>">
                    <button type="submit" id="btn-submit" class="btn btn-primary"  >Add</button>
                </div>
            </div>




        </div>
    </div>                 


</form>

<div class="col-xs-8">
</div >

<div id="table_appnd" class="">

</div>
<div id="dispatch" style="display: none;">
<!-- <input type="button" id="reload" value="Dispatch Order" class="btn btn-primary">-->
    <input type="button" id="btn-remove" value="Remove order" class="btn btn-primary">
</div> 
<script>
    
function  itemfind()
{
    var number = $('#required_qty').val();
    if(number < 1)
    {
        alert("Please Check Enter Number");
        $("#required_qty").val('');
    }
}
//custmername check
 function checksite()
 {
     var site =$('#site_name').val();
     if(site != '')
     {
         $("#customer_po_number").val('');
         $("#customer_name").val('');
     }
 }
//namecheck
function checkname()
 {
     var custmer =$('#customer_name').val();
     if(custmer != '')
     {
         $("#customer_po_number").val('');
         $("#site_name").val('');
     }
 }
//pocheck
function checkpo()
 {
     var custmerpo =$('#customer_po_number').val();
     if(custmerpo != '')
     {
         $("#customer_name").val('');
         $("#site_name").val('');
     }
 }




    $(document).ready(function () {
        var currentDate = new Date();
        $('#mr_date').datepicker("setDate", currentDate);
        $('#fromdate').datepicker({
            dateFormat: 'yyyy-mm-dd'
        });
        $('#todate').datepicker({
            dateFormat:'yyyy-mm-dd'
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
                    $("#incoming_date").val('');

                    $.notify($.parseJSON(resp).msg, {
                        type: 'success',
                        allow_dismiss: false
                    });
                }

            });

        });
//site name
var site_name = {
            url: function (phrase) {
                return "MaterialRequest/getautocompletionssite_name?site_name=" + phrase;
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
                return "MaterialRequest/getautocompletionscustomer_name?customer_name=" + phrase;
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
                return "MaterialRequest/getautocompletionscustomer_po_number?customer_po_number=" + phrase;
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
//generate order
var gstore_code = {
            url: function (phrase) {
                return "MaterialRequest/getautocompletionsgstore_code?gstore_code=" + phrase;
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
//generate order

         
         
         

         
//        var itemcode = {
//            url: function (phrase) {
//                return "MaterialRequest/getautocompletionsitemcode?itemcode=" + phrase;
//            },
//            getValue: "item_code",
//            list: {
//                onSelectItemEvent: function () {
//                    var selectedItem = $('#item_code').getSelectedItemData();
//                    var site_pkey = selectedItem.site_pkey;
//                    var site_name = selectedItem.site_name;
//                    var site_id = selectedItem.site_id;
//
//                    $('#site_pkey').val(site_pkey);
//                    $('#customer_name').val(site_name);
//                    $('#customer_po_number').val(site_id);
//
//                    //alert(contact_id);
//                    //  reloadDatagrid(site_pkey);
//                    // filterAttendanceautocomplete(contact_id);
//
//                }
//            }
//        };
//        
//        
         
         
        var usersoptions = {
            url: function (phrase) {
                return "MaterialRequest/getautocompletions?username=" + phrase;
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
                return "MaterialRequest/getautocompletionsmr_code?mr_code=" + phrase;
            },
            getValue: "mr_code",
            list: {
                onSelectItemEvent: function () {
                    var selectedItem = $('#bill').getSelectedItemData();
                    var id = selectedItem.mr_pkey; //alert(id)

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
        $.ajax({
            url: 'MaterialRequest/loadtable/' + pid + '/' + rowindex,
            success: function (response) {
                //alert(response);
                var data = response;
                var div_data = '';
                div_data += "<div>" + data + "</div>"
                $("#table_appnd").html(div_data);

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
                    url: 'MaterialRequest/deleteorder/' + id,
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

        var url = 'MaterialRequest/editorder/' + id;
        $('#modalDiv').load(url, function () {
            $('#modalDiv').modal('show');
        });
        //alert (pid);
        
        loadtable(pid, 1);
        //alert(loadtable);
    }


    function refresh() {
        $("#bill").val('');
        $("#contact_name").val('');
        $("#mr_pkey").val('');
        $("#form-user-master").find('input:text, input:password, input:file, select, textarea,hidden,search').val('');
        $("#form-user-master").find('input:radio, input:checkbox').removeAttr('checked').removeAttr('selected');
        $("#table_appnd").html('');
        $('#dispatch').hide();
    }

    $("#btn-remove").click(function () {
        var id = $("#mr_pkey").val();
        if (id) {
            var r = confirm("Do You Want  To Remove The Selected Item")
            if (r == true) {


                var pid = $("#mr_details_pkey").val();
                $.ajax({
                    url: 'MaterialRequest/deleteordermaster/' + id,
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
                url: 'MaterialRequest/dispatchorder/' + pid,
                success: function (resp) {
                    refresh();
                    $.notify($.parseJSON(resp).msg, {
                        type: 'danger',
                        allow_dismiss: false
                    });
                }
            });

        });
        


</script>