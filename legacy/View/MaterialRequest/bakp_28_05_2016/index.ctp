<style>
    .searchb{
        background-color: #cccccc;
    }
</style>
<div style="padding-left:450px;">
<form class="form-horizontal" style=" padding-top: 15px; padding-left:inherit;" id="search_form">

    <div class="row " style="height:auto;"> 
        
        <div class="row">
            <div class="col-md-3">
                <h1 class="page-header">Search</h1>
            </div>
  <div class="col-md-4"><input type="search" name="bill" id="bill" required="required"></div>
  <div class="col-md-3"> <input type="button" id="btn-refresh" value="Refresh" onclick="refresh();" style="padding-left:1px;" class="btn btn-primary">   </div>
  
</div>
        
        </div> 
</form>

    </div>

<h1 class="page-header">Create Material Request</h1>


<form class="form-horizontal" style="padding-left:1px; padding-right:8px ; border:1px;" id="form-user-master" method="post" action="<?php echo $this->webroot; ?>GoodsReceivedNotes/save" >
                    
            
           <div class="row bg-success" style="height:auto;"> 
        <h4 style="padding-left:20px;">Add Basic Details</h4>
                    <div class="modal-body">
                       <div class="form-group">
                            <div class="col-md-4">
                                <label style="text-align:left;" class="col-md-5 control-label" >Material Request Code<label style="color:red;"></label></label>
                                <div class="col-md-6">
                                    <input id="mr_code" name="mr_code" value="<?php echo isset($arr_att['0']['material_request']['mr_code']) ? $arr_att['0']['material_request']['mr_code'] : mt_rand(); ?>" type="text"  class="form-control input-md" >

                                </div>
                            </div>
                        
                            
                           <div class="form-group">
                            <div class="col-md-4">
                                <label style="text-align:left;" class="col-md-4 control-label" >Po Type*<label style="color:red;">*</label><label style="color:red;"></label></label>
                                <div class="col-md-8">
                                    <input id="po_type" name="po_type" value="<?php  echo  isset($arr_att['0']['material_request']['po_type']) ? $arr_att['0']['material_request']['po_type'] : ''; ?>" type="text"  class="form-control input-md" required="re" >
                                </div>
                            </div>
                        
                            <div class="col-md-3">
                                <label style="text-align:left;" class="col-md-5 control-label" >MR Date<label style="color:red;">*</label></label>
                                <div class="col-md-7">
                                    <input id="start_date" placeholder="select date" name="mr_date" value="<?php echo  isset($arr_att['0']['material_request']['mr_date']) ? $arr_att['0']['material_request']['mr_date'] :'' ; ?>" type="text"  class="form-control input-md" >
                                </div> 
                            </div>
                        </div>
                       
                   
                       </div>
                        <div class="form-group">
                            <div class="col-md-4">
                                <label style="text-align:left;" class="col-md-5 control-label" >Client Name<label style="color:red;">*</label><label style="color:red;"></label></label>
                                <div class="col-md-6">
                                    <input id="client_name" name="client_name" value="<?php echo isset($arr_att['0']['material_request']['client_name']) ? $arr_att['0']['material_request']['client_name'] : ''; ?>" type="text"  class="form-control input-md" >

                                </div>
                            </div>
                        
                            
                           <div class="form-group">
                            <div class="col-md-4">
                                <label style="text-align:left;" class="col-md-4 control-label" >Client Po No<label style="color:red;">*</label><label style="color:red;"></label></label>
                                <div class="col-md-8">
                                    <input id="client_po_no" name="client_po_no" value="<?php  echo  isset($arr_att['0']['material_request']['client_po_no']) ?$arr_att['0']['material_request']['client_po_no']: '' ; ?>" type="text"  class="form-control input-md" >
                                </div>
                            </div>
                        
                            <div class="col-md-3">
                                <label style="text-align:left;" class="col-md-5 control-label" >Location<label style="color:red;">*</label></label>
                                <div class="col-md-7">
                                    <input id="location" name="location" value="<?php echo  isset($arr_att['0']['material_request']['location']) ?$arr_att['0']['material_request']['location'] : '' ; ?>" type="text"  class="form-control input-md" required="required" >
                                </div> 
                            </div>
                        </div>
                       
                   
                       </div>
                        <div class="form-group">
                            <div class="col-md-4">
                                <label style="text-align:left;" class="col-md-5 control-label" >Store Code<label style="color:red;">*</label><label style="color:red;"></label></label>
                                <div class="col-md-6">
                                   <select id="store_code" class="form-control" name="store_code">
                                      <option value="" >---select---</option>
                                   <?php
                               
                                      foreach ($all_store as $value) {
                                         
                                          $selected = ($arr_att['0']['material_request']['store_code'] == $value['Store']['store_master_pkey']) ? 'selected="selected"' : '';
                                          echo '<option value="' . $value['Store']['store_master_pkey'] . '" ' . $selected . '>'.$value['Store']['store_code'] .'   </option>';
                                      }
                                      ?>
                                  </select>                      
                                </div>
                            </div>
                        
                            
                           <div class="form-group">
                            <div class="col-md-4">
                                <label style="text-align:left;" class="col-md-4 control-label" >Att<label style="color:red;">*</label><label style="color:red;"></label></label>
                                <div class="col-md-8">
                                    <input id="att" name="att" value="<?php echo  isset($arr_att['0']['material_request']['att']) ?$arr_att['0']['material_request']['att'] : '' ; ?>" type="text"  class="form-control input-md" >
                                </div>
                            </div>
                        
                            <div class="col-md-3">
                                <label style="text-align:left;" class="col-md-5 control-label" >Remark<label style="color:red;">*</label></label>
                                <div class="col-md-7">
                                    <input id="remarks" name="remarks" value="<?php echo  isset($arr_att['0']['material_request']['remarks']) ?$arr_att['0']['material_request']['remarks'] : '' ; ?>" type="text"  class="form-control input-md" >
                                </div> 
                            </div>
                        </div>
                       
                   
                       </div>
                        
                           <h4 style="padding-left:20px;">Material Request Details</h4>
                        <div class="form-group">
                           
                        
                            <div class="col-md-3">
                                <label style="text-align:left;" class="col-md-5 control-label" >Item Name<label style="color:red;">*</label></label>
                                <div class="col-md-7">
                                   <select id="item_code"  class="form-control" name="item_code"  required="reduired">
                                      <option value="" >-select-</option>
                                   <?php
                                      foreach ($all_item as $value) {
                                         
                                          //$selected = ($arr_att['0']['poitemorder']['store_code'] == $value['Store']['store_master_pkey']) ? 'selected="selected"' : '';
                                          echo '<option value="' . $value['Item']['item_master_pkey'] . '" ' . $selected . '>'.$value['Item']['item_desc'] .'   </option>';
                                      }
                                      ?>
                                  </select>
                                </div> 
                            </div>
                        
                            <div class="col-md-3">
                                <label style="text-align:left;" class="col-md-6 control-label" >Required Qty<label style="color:red;">*</label><label style="color:red;"></label></label>
                                <div class="col-md-6">
                                    <input type="search" id="item_code" class="form-control" name="required_qty" required="required" />   
                                </div>
                            </div>   
                            <div class="form-group">
                          <div class="col-md-3">
                                <label style="text-align:left;" class="col-md-4 control-label" >Incoming Date<label style="color:red;">*</label><label style="color:red;"></label></label>
                                <div class="col-md-8">
                                   <input type="search" id="incoming_date" class="form-control" name="required_qty" required="required" />   
                                  
                                </div>
                            </div>
                            <div class="col-md-3">
                                <label style="text-align:left;" class="col-md-4 control-label" >Package</label>
                                <div class="col-md-8">
                                     <input type="search" id="incoming_date" class="form-control" name="package" required="required" /> 
                               </div> 
                            </div>
                                 
                       
                        
                   
                       </div>
                           <div class="modal-footer">
                       
                        <button type="submit" id="btn-submit" class="btn btn-primary">Add</button>
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
 <input type="button" id="btn-dispatch" value="Dispatch Order" class="btn btn-primary">
    <input type="button" id="btn-remove" value="Remove order" class="btn btn-primary">
  </div> 
<script>
    $(document).ready(function(){
     var currentDate = new Date();    
   $('#start_date').datepicker("setDate", currentDate);
      
      $('#form-user-master').on('submit', function (event) {
        event.preventDefault();
      
            $('#form-user-master').ajaxSubmit({
                
                success: function(resp){
                var pid=    $.parseJSON(resp).pk;
                                 $("#sales_order_master_pkey").val(pid);
                                 $("#dispatch").show();
                                 $("#item_name").focus();
                                 loadtable(pid,1);
                                 $("#item_qty").val('');
                                  $("#item_code").val('');
                                  $("#item_name").val('');
                                    $.notify($.parseJSON(resp).msg,{
                                        type: 'danger',
                                        allow_dismiss: false
                                    });
                                }
                
            });
       
    });
    
  var itemcode = {
                    
            url: function (phrase) {  
                return "Salesorderdetail/getautocompletions?itemcode=" +phrase;
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
                return "Salesorderdetail/getautocompletions?itemname=" +phrase;
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
                return "Salesorderdetail/getautocompletions?username=" +phrase;
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
                return "Salesorderdetail/getautocompletionsbillno?billno=" +phrase;
            },
            getValue: "bill_no",
                          list: {
                onSelectItemEvent: function () {
                     var selectedItem = $('#bill').getSelectedItemData();
                    var id = selectedItem.sales_order_master_pkey; //alert(id)
                    loadtable(id,1);
                    $('#sales_order_master_pkey').val(id);
                    $('#store_master_fkey').val(selectedItem.store_master_fkey);
  $('#contact_id').val(selectedItem.contact_id); 
  $('#contact_name').val(selectedItem.name); 
   $('#bill_no').val(selectedItem.bill_no); 
   $('#bill_date').val(selectedItem.bill_date); 
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
								url : 'Salesorderdetail/loadtable/'+pid+'/'+rowindex,
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
                                          
                       
                            var pid=$("#sales_order_master_pkey").val();
                            $.ajax({
                                url:'Salesorderdetail/deleteorder/' +id,
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
              var pid=$("#sales_order_master_pkey").val();
              
           var url='Salesorderdetail/editorder/'+id;
                            $('#modalDiv').load(url,function(){
                                $('#modalDiv').modal('show');
                            });
                            
                            loadtable(pid,1);
        }
        
        
        function refresh(){
            $("#bill").val('');  
            $("#contact_name").val('');
              $("#sales_order_master_pkey").val('');
            $("#form-user-master").find('input:text, input:password, input:file, select, textarea,hidden,search').val('');
           $("#form-user-master").find('input:radio, input:checkbox').removeAttr('checked').removeAttr('selected');
           $("#table_appnd").html('');
            $('#dispatch').hide();
        }
        
        $("#btn-remove").click( function(){
           var id= $("#sales_order_master_pkey").val(); 
            if (id) {
                              var r=confirm("Do You Want  To Remove The Selected Item")
                                    if(r==true){
                                          
                       
                            var pid=$("#sales_order_master_pkey").val();
                            $.ajax({
                                url:'Salesorderdetail/deleteordermaster/' +id,
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
              var pid=$("#sales_order_master_pkey").val();
                            $.ajax({
                                url:'Salesorderdetail/dispatchorder/' +pid,
                                success: function(resp){
                             refresh();
                                    $.notify($.parseJSON(resp).msg,{
                                        type: 'danger',
                                        allow_dismiss: false
                                    });
                                }
                            });
            
        });
        $("#item_qty").change(function (){
          var qty= $('#item_qty').val(); 
          var offer_id= $('#offer_value').val(); 
          var contact_id= $('#contact_id').val(); 
           $.ajax({
                                url:'Salesorderdetail/offercheck/' +offer_id+'/'+qty+'/'+contact_id,
                                success: function(resp){
               var sucess=   $.parseJSON(resp).msg; 
               if(sucess ==false){
                           $("#item_qty").focus();
                          $('#item_qty').val(''); 
                                    $.notify($.parseJSON(resp).message,{
                                        type: 'danger',
                                        allow_dismiss: false
                                    });
                                }
                                }
                            });
        //  alert(offer_id);
        });
</script>

  