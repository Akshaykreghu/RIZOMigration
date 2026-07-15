        <style>
    .searchb{
        background-color: #cccccc;
    }
</style>
<script>
function newmode()
{
           $('#div-criteria1').load(livesite+'DirectPurchaseOrder/index'); 
}
</script>
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
                       
                    </div>
        </div>
                            

        
    </form>




<h1 class="page-header">CREATE DIRECT PURCHASE ORDER</h1>


<form class="form-horizontal" style="padding-left:1px; padding-right:8px ; border:1px;" id="form-user-master" method="post" action="<?php echo $this->webroot; ?>DirectPurchaseOrder/save" >
    <div class="row bg-success" style="height:auto;"> 
        <h4 style="padding-left:20px;">DIRECT PURCHASE ORDER</h4>
        <div class="modal-body">
            <div class="form-group">
                <div class="col-md-3">
                    <label style="text-align:left;" class="col-md-5 control-label" >Purchase Number<label style="color:red;"></label></label>
                    <div class="col-md-7">
                        <input id="direct_po_number" name="direct_po_number" value="<?php echo isset($arr_list['0']['directpo']['direct_po_number']) ? $arr_list['0']['directpo']['direct_po_number'] : mt_rand(); ?>" type="text"  class="form-control input-md" >
                    </div>
                </div>
                <div class="col-md-3">
                    <label style="text-align:left;" class="col-md-4 control-label" >Purchase Date</label>
                    <div class="col-md-8">
                        <input id="current_date" name="direct_po_date" value="<?php echo isset($arr_list['0']['directpo']['direct_po_date']) ? $arr_list['0']['directpo']['direct_po_date'] : ''; ?>" type="text"  class="form-control input-md"  >
                    </div>
                </div>

                <div class="col-md-3">
                    <label style="text-align:left;" class="col-md-5 control-label" >Site<label style="color:red;">*</label></label>
                    <div class="col-md-7">
                        <select id="location" class="form-control" name="location" required="required" >
                                      <option value="" >---select---</option>
                                   <?php
                               
                                      foreach ($all_site as $value) {
                                         
                                          $selected = ($arr_att['0']['material_request']['location'] == $value['Site']['site_pkey']) ? 'selected="selected"' : '';
                                          echo '<option value="' . $value['Site']['site_pkey'] . '" ' . $selected . '>'.$value['Site']['site_id'] .'  '.$value['Site']['site_name']  .'   </option>';
                                      }
                                      ?>
                                  </select>
<!--                        <input id="location"  name="location" value="<?php // echo isset($arr_list['0']['directpo']['location']) ? $arr_list['0']['directpo']['location'] : ''; ?>" type="text"  class="form-control input-md"  >-->
                    </div> 
                </div>
                <div class="col-md-3">
                    <label style="text-align:left;" class="col-md-5 control-label" >Remark</label>
                    <div class="col-md-7">
                        <input id="remarks" name="remarks" value="<?php echo isset($arr_list['0']['directpo']['remarks']) ? $arr_list['0']['directpo']['remarks'] : ''; ?>" type="text"  class="form-control input-md"  >
                    </div>
                </div>
            </div>
            <h4 style="padding-left:20px;">Direct Purchase Order Details</h4>
            <div class="form-group">


                <div class="col-md-3">
                    <label style="text-align:left;" class="col-md-5 control-label" >Item Name<label style="color:red;">*</label></label>
                    <div class="col-md-6">
                        <select id="item_code"  class="form-control" name="item_code"  required="reduired" style=width:120%;>
                            <option value="" >-select-</option>
                            <?php
                            foreach ($all_item as $value) {

                                //$selected = ($arr_att['0']['poitemorder']['store_code'] == $value['Store']['store_master_pkey']) ? 'selected="selected"' : '';
                                echo '<option value="' . $value['Item']['item_master_pkey'] . '" ' . $selected . '>' . $value['Item']['item_desc'] . '   </option>';
                            }
                            ?>
                        </select>
                    </div> 
                </div>

                <div class="col-md-3">
                    <label style="text-align:left;" class="col-md-6 control-label" >Ordering Qty<label style="color:red;">*</label><label style="color:red;"></label></label>
                    <div class="col-md-6">
                        <input type="number" data-bv-integer-message="The value is not an integer"  id="ordered_qty" class="form-control" name="ordered_qty" required="required" />   
                    </div>
                </div>   

<!--                <div class="col-md-2">
                    <label style="text-align:left;" class="col-md-4 control-label" >Uom<label style="color:red;"></label></label>
                    <div class="col-md-8">
                        <input type="" id="uom" class="form-control"  name="uom"  />   

                    </div>
                </div>-->
                <div class="col-md-3">
                    <label style="text-align:left;" class="col-md-5 control-label" >PO Rate<label style="color:red;"></label></label>
                    <div class="col-md-7">
                        <input   type="text" id="po_rate" class="form-control"  name="po_rate"  />   

                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="submit" id="btn-submit" class="btn btn-primary">Add</button>
                <input type="hidden" name="direct_po_pkey" id="direct_po_pkey" value="<?php echo isset($arr_att['0']['material_request']['direct_po_pkey']) ? $arr_att['0']['material_request']['direct_po_pkey'] : ''; ?>">
                
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
</div>
</div>
</div>
</div>
</section>
<script>
    
    $(document).ready(function(){
     var currentDate = new Date();    
   $('#current_date').datepicker("setDate", currentDate);
   
      
      $('#form-user-master').on('submit', function (event) {
        event.preventDefault();
 //load table    
            $('#form-user-master').ajaxSubmit({
                success: function(resp){
                var pid=    $.parseJSON(resp).pk;
                                 $("#direct_po_pkey").val(pid);
                                 //alert(pid);
                                 $("#item_code").focus();
                                 loadtable(pid,1);
                                 $("#ordered_qty").val('');
                                  $("#item_code").val('');
                                  $("#uom").val('');
                                   $("#po_rate").val('');
                                 
                                    $.notify($.parseJSON(resp).msg,{
                                        type: 'success',
                                        allow_dismiss: false
                                    });
                                }
                
            });
       
    });
    
  var itemcode = {
                    
            url: function (phrase) {  
                return "DirectPurchaseOrder/getautocompletions?itemcode=" +phrase;
            },
            getValue: "item_code",
                          list: {
                onSelectItemEvent: function () {
                     var selectedItem = $('#item_code').getSelectedItemData();
                    var item_master_pkey = selectedItem.item_master_pkey;
                    var item_desc=selectedItem.item_desc;
                 var applicable_scheme = selectedItem.applicable_scheme;

  $('#item_master_fkey').val(item_master_pkey);
  $('#item_name').val(item_desc); 
   $('#offer_value').val(applicable_scheme); 
  
               //alert(contact_id);
                  //  reloadDatagrid(site_pkey);
                   // filterAttendanceautocomplete(contact_id);

                }
            }
        };
  var itemname = {
                    
            url: function (phrase) {  
                return "DirectPurchaseOrder/getautocompletions?itemname=" +phrase;
            },
            getValue: "item_desc",
                          list: {
                onSelectItemEvent: function () {
                     var selectedItem = $('#item_name').getSelectedItemData();
                    var item_master_pkey = selectedItem.item_master_pkey;
                     var applicable_scheme = selectedItem.applicable_scheme;
   var item_code=selectedItem.item_code;
  $('#item_master_fkey').val(item_master_pkey);
  $('#item_code').val(item_code); 
   $('#offer_value').val(applicable_scheme); 
  

                }
            }
        };
 var usersoptions = {
                    
            url: function (phrase) {  
                return "DirectPurchaseOrder/getautocompletions?username=" +phrase;
            },
            getValue: "name",
                          list: {
                onSelectItemEvent: function () {
                     var selectedItem = $('#contact_name').getSelectedItemData();
                    var contact_id = selectedItem.contact_id;
  $('#contact_id').val(contact_id);
  
  
      var div_data='';
                                                        div_data +="<div>"+selectedItem.pricename;+"</div>";
                                                          div_data +="<div>"+selectedItem.pricerate;+"</div>";
                                                           $("#sales_price_div").show();
                                                      $("#sales_price_div").html(div_data);
             //  alert(contact_id);
                  //  reloadDatagrid(site_pkey);
                   // filterAttendanceautocomplete(contact_id);

                }
            }
        };
        $('#item_name').easyAutocomplete(itemname); 
    $('#item_code').easyAutocomplete(itemcode); 
          $('#contact_name').easyAutocomplete(usersoptions); 
          var billno = {
                    
            url: function (phrase) {  
                return "DirectPurchaseOrder/getautocompletionsdirect_po_number?direct_po_number=" +phrase;
            },
            getValue: "direct_po_number",
                          list: {
                onSelectItemEvent: function () {
                     var selectedItem = $('#bill').getSelectedItemData();
                    var id = selectedItem.direct_po_pkey; 
                    //alert(id)
                    loadtable(id,1);
                    //value pass
                    $('#direct_po_pkey').val(id);
                    //list in all field
                    $('#direct_po_number').val(selectedItem.direct_po_number);
                    $('#po_type').val(selectedItem.po_type); 
                    $('#mr_date').val(selectedItem.mr_date); 
                     $('#client_name').val(selectedItem.client_name); 
                     $('#client_po_no').val(selectedItem.client_po_no); 
                      $('#location').val(selectedItem.location); 
                       $('#store_code').val(selectedItem.store_code); 
                       $('#att').val(selectedItem.att); 
                       $('#remarks').val(selectedItem.remarks); 
                       $('#current_date').val(selectedItem.current_date); 
                     $('#dispatch').show();
                     
  
  
               //alert(contact_id);
                  //  reloadDatagrid(site_pkey);
                   // filterAttendanceautocomplete(contact_id);

                }
            }
        };
         $('#bill').easyAutocomplete(billno); 
        
    });
 
        function  loadtable(pid,rowindex){
             $.ajax({
								url : 'DirectPurchaseOrder/loadtable/'+pid+'/'+rowindex,
                      			success : function(response) {
					 	//alert(response);
                                                        var data=response;
                                                        var div_data='';
                                                        div_data +="<div>"+data+"</div>"
                                                      $("#table_appnd").html(div_data);
                                                    
							}
							});
            
        } 
function removedaata(index,id){
            
           // alert(id);
            
              if (id) {
                              var r=confirm("Do You Want  To Remove The Selected Item")
                                    if(r==true){
                                          
                       
                            var pid=$("#direct_po_pkey").val();
                            $.ajax({
                                url:'DirectPurchaseOrder/deleteorder/' +id,
                                success: function(resp){
                                    loadtable(pid,1);
                                    $.notify($.parseJSON(resp).msg,{
                                        type: 'danger',
                                        allow_dismiss: false
                                    });
                                }
                            });
                        }
                        else
                        {
                            alert("canceled");
                        }
                        }
            
            
        }
        
         function editdaata(index,id){
              var pid=$("#direct_po_pkey").val();
              
           var url='DirectPurchaseOrder/editorder/'+id;
                            $('#modalDiv').load(url,function(){
                                $('#modalDiv').modal('show');
                            });
                            //alert (pid);
                            loadtable(pid,1);
                           //alert(loadtable);
        }
        
        
        function refresh(){
            $("#bill").val('');  
            $("#contact_name").val('');
              $("#direct_po_pkey").val('');
            $("#form-user-master").find('input:text, input:password, input:file, select, textarea,hidden,search').val('');
           $("#form-user-master").find('input:radio, input:checkbox').removeAttr('checked').removeAttr('selected');
           $("#table_appnd").html('');
            $('#dispatch').hide();
        }
        
        $("#btn-remove").click( function(){
           var id= $("#direct_po_pkey").val(); 
            if (id) {
                              var r=confirm("Do You Want  To Remove The Selected Item")
                                    if(r==true){
                                          
                       
                            var pid=$("#direct_po_details_pkey").val();
                            $.ajax({
                                url:'DirectPurchaseOrder/delete/' +id,
                                success: function(resp){
                             refresh();
                                    $.notify($.parseJSON(resp).msg,{
                                        type: 'danger',
                                        allow_dismiss: false
                                    });
                                }
                            });
                        }
                        else
                        {
                            alert("canceled");
                        }
                        }
            
        });
        
        $("#btn-dispatch").click(function () {
              var pid=$("#direct_po_pkey").val();
                            $.ajax({
                                url:'DirectPurchaseOrder/dispatchorder/' +pid,
                                success: function(resp){
                             refresh();
                                    $.notify($.parseJSON(resp).msg,{
                                        type: 'danger',
                                        allow_dismiss: false
                                    });
                                }
                            });
            
        });
      
</script>

  