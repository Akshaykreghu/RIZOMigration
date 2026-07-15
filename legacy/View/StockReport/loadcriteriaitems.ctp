<?php if($criteria == 'EmployeeProfessionalDetails'){ ?>
    <div class="col-md-6">
        <label class="col-md-4 control-label" for="reportfrom">From:</label>
        <div class="col-md-8">
            <input class="form-control input-md" id="reportfrom" name="reportfrom" value="" type="text" >
        </div>
    </div>
    <div class="col-md-6">
        <label class="col-md-4 control-label" for="reportto">To:</label>
        <div class="col-md-8">
            <input class="form-control input-md" id="reportto" name="reportto" value="" type="text" >
        </div>
    </div>
<script>
jQuery(document).ready(function() {
    $('#reportfrom').datepicker({
        format: 'yyyy-mm-dd'
    })
    $("#reportfrom").inputmask("yyyy-mm-dd");
    $('#reportto').datepicker({
        format: 'yyyy-mm-dd'
    })
    $("#reportto").inputmask("yyyy-mm-dd");
});
</script>
<?php }else{ ?>
<div class="col-md-2">
    <a onclick="toggleItemsDisplay(<?php echo $index; ?>);"><i class="fa fa-minus"></i></a>
</div>
<div class="col-md-10">
    <ul title="<?php echo 'Select '.$criteria; ?>" lines="true" style="width:100%;min-height:200px;height:auto;max-height:500px;"></ul>
</div>
<form method="post" action="" id="form-storelist">
<input id="storelist" name ="storelist" type="hidden" >
</form>
<script>
function toggleItemsDisplay(index){
    $('#div-items-criteria'+index+' .panel-body').fadeToggle('slow', function() {
        $('#div-items-criteria'+index+' a .fa').toggleClass('fa-plus');
        $('#div-items-criteria'+index+' a .fa').toggleClass('fa-minus');
//        if($(this).is(":visible")){
//            $('#div-items-criteria'+index+' a .fa').removeClass('fa-plus');
//            $('#div-items-criteria'+index+' a .fa').addClass('fa-minus');
//        }else{
//            $('#div-items-criteria'+index+' a .fa').removeClass('fa-minus')
//            $('#div-items-criteria'+index+' a .fa').addClass('fa-plus');
//        }
    });
}

function setwidth(){
    //alert("hi");
    $('.checkw').parent().parent().addClass('checkw').css("width","30px");
}
function get_items(data){
   
    var keys = [];
    for (var i = 0; i < data.length; i++) {
    keys[i] = data[i].key;
    $("#storelist").val(keys);
   
  }
  var store = $("#storelist").val();
  console.log(store);
  $.ajax({
                    url: "StockReport/itemcriteria/"+store,
                    success: function (resp) {
                  $("#items-criteria1").html(resp);
                    }
        
                });
//  $("#items-criteria1").select2(
//                {
//                    //closeOnSelect:false,
//                    placeholder: "Select Item",
//                    allowClear: true,
//                    ajax: {
//                        url: livesite + "StockReport/itemlist/"+store ,
//                        dataType: 'json',
//                        delay: 250,
//                        data: function (params) {
//                              console.log(params) ;
//                            return {
//                                q: params.term, // search term
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
    }
 function  selectItem(s) {
        var from_store_name = $("#storelist").val();
       // console.log(s.key);
        
          $("#items-criteria").select2(
                {
                    //closeOnSelect:false,
                    placeholder: "Select Item",
                    allowClear: true,
                    ajax: {
                        url: livesite + "StockReport/get_all_items/" + s.value,
                        dataType: 'json',
                        delay: 250,
                        data: function (params) {
                            console.log(params) ;
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
$(document).ready(function(){
    var criteria = $('#hidden-criteria<?php echo $index; ?>').val();
    var count = 0;
    var type = $('#hidden-report-type').val();
    $('#div-items-criteria<?php echo $index; ?> ul').datalist({
        toolbar: [{
            text: 'Select all',
            iconCls: 'icon-ok',
            handler: function () {
                $('#div-items-criteria<?php echo $index; ?> ul').datalist('checkAll');
                $('#div-items-criteria<?php echo $index; ?> input:checkbox').each(function(){
                    this.checked = true;
                    //document.getElementById("storelist").value = this;
                    //var store = this;
                    //console.log(this);
                    //selectItem(this);
                });
            },
                    
        },{
            text: 'Deselect all',
            iconCls: 'icon-delete',
            handler: function () {
                $('#div-items-criteria<?php echo $index; ?> ul').datalist('clearChecked');
                $('#div-items-criteria<?php echo $index; ?> input:checkbox').each(function(){
                    this.checked = false;
                });
            }
        }],
        //frozenColumns:[[
        columns:[[
		{
                    field:criteria+'[]',
                    formatter: function(value,row,index){
                        return '<input type="checkbox" class="checkw" name="'+criteria+'[]" value="'+row.key+'" />';
                    }
                },
		{
                    field:'Name',
                    formatter: function(value,row,index){
                        return row.text;
                    }
                }
	]],
        url: livesite + 'StockReport/listcriteriaitems/'+criteria,
        //checkbox: true,
        checkOnSelect: true,
	searchFilter:true,
        singleSelect: false,
        lines: true,
        valueField: "key",
        onCheck: function (i, rows) {
           //console.log(rows); 
           //get_items(rows);
          // document.getElementById("storelist").value = rows;
           //selectItem(rows);
        },
        onSelect: function (i , rows) {
            count = count + 1;
            
            if(type ==="StockAllDetails"){
            if(count <= 1){
            document.getElementById("storelist").value = $('#div-items-criteria<?php echo $index; ?> ul').datalist('getSelections');
            get_items($('#div-items-criteria<?php echo $index; ?> ul').datalist('getSelections'));
            }else{
                count = count - 2;
                alert("Only one store can be selected.");
                 $('#div-items-criteria<?php echo $index; ?> ul').datalist('clearChecked');
//                 $('#div-items-criteria<?php echo $index; ?> ul ').datalist('unselectRow', i);
//                 $('#div-items-criteria<?php echo $index; ?> ul ').datalist('clearChecked', i);
                 $('#div-items-criteria<?php echo $index; ?> input:checkbox').each(function(){
                    this.checked = false;
                });
                
            }
            }
        },
        onUncheck: function (i, rows) {
          count = count - 1;
        },
        onLoadSuccess: function () {
            setwidth();
        }
    });
});
</script>
<?php } ?>