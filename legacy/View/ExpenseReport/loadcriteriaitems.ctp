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
            <input type="hidden" name="namefgerdd" value="">
        </div>
    </div>
<style>
    .checkw{
        width: 14px !important;
    }
</style>
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
<div class="col-md-10" style="float: inherit;">
<!--    //edited by arul on 4/12/2019  li list not showing fully-->
	 <?php if($criteria=="Units"){$msg="Branches";}else{$msg=$criteria;} ?><!--Here changing Units into Branches by ***ARUL P DAS on 27/2/2020-->
    <ul title="<?php echo 'Select '.$msg; ?>" lines="true" style="width:330px;min-height:200px;height:auto;max-height:500px;"><li></li></ul>
</div>
<!--<button onclick="setwidth();" type="button">Cloick</button>-->
<input type="hidden" id="rsndempid" value="0" name="resigned">
<input type="hidden" id="ngtvsal" value="0" name="ngtvsal">
<div id="div-b" style="padding:2px 5px;">
       
  
    </div>
<!-- <input type="hidden" id="indctid" value="0" name="indirect"> -->
<div id="div-b" style="padding:2px 5px;">
       
  
    </div>
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
        var criteria = $('#hidden-criteria<?php echo $index; ?>').val();
       // if(criteria == "EmployeeDetails"){
        if ($('#checkrsgnd').length == 0) {
      // exists.
        $('.datagrid-toolbar').find('tr').append('<td><input type="checkbox" name"rsgnemp"  style="vertical-align: sub;" id="checkrsgnd" onclick="showrsgnd();" value="0"> &nbsp;Include Resigned </td>');
    }
   /*  if ($('#checkindct').length == 0){
       
        $('.datagrid-toolbar').find('tr').append('<td><input type="checkbox" name"indirect"  style="vertical-align: sub;" id="checkindct" onclick="showindct();" value="0"> &nbsp;Include Indirect </td>');

    } */
    
    
      //  }
}

    function showng()
    {
        if($('#checkng').is(":checked"))
        {
           $('#ngtvsal').val('1');
        }
        else{
           $('#ngtvsal').val('0');
        }
         $('#div-items-criteria<?php echo $index; ?> ul').datalist('load', {
            name: $('#ngtvsal').val()
            });
    }
     function showrsgnd()
    { 
        if($('#checkrsgnd').is(":checked"))
        {
           $('#rsndempid').val('1');
        }
        else{
           $('#rsndempid').val('0');
        }
         $('#div-items-criteria<?php echo $index; ?> ul').datalist('load', {
            name: $('#rsndempid').val()
            });
    }
   $('#div-items-criteria').datagrid({
    rowStyler:function(index,row){
        if (row.listprice>50){
            return 'background-color:pink;color:blue;font-weight:bold;';
        }
    }
}); 
   /*  function showindct()
    {
        
        if($('#checkindct').is(":checked"))
        {
           $('#indctid').val('1');
           
        }
        else{
             $('#indctid').val('0');
            
        }
    
    } */
  
$(document).ready(function(){
    var criteria = $('#hidden-criteria<?php echo $index; ?>').val();
    $('#div-items-criteria<?php echo $index; ?> ul').datalist({
            rowStyler: function (index, row) {
                            var style = "";
                           if (row.status == '2') {
                                        style += 'background-color:#cac3c3;color:#fff;';
                                    }
                             return style;
                        },
            toolbar: [{
            text: 'Select all',
            iconCls: 'icon-ok',
            handler: function () {
                $('#div-items-criteria<?php echo $index; ?> ul').datalist('checkAll');
                $('#div-items-criteria<?php echo $index; ?> .datagrid-view input:checkbox').each(function(){
                    this.checked = true;
                });
            }
        },{
            text: 'Deselect all',
            iconCls: 'icon-delete',
            handler: function () {
                $('#div-items-criteria<?php echo $index; ?> ul').datalist('clearChecked');
                $('#div-items-criteria<?php echo $index; ?> .datagrid-view input:checkbox').each(function(){
                    this.checked = false;
                });
            }
        },{}],
        //frozenColumns:[[
        columns:[[
		{
                    field:criteria+'[]',
                    width:10,
                    formatter: function(value,row,index){
                        return '<input type="checkbox" class="checkw" name="'+criteria+'[]" value="'+row.key+'" />';
                    }
                },
		{
                    field:'Name',
                    width:80,
                    formatter: function(value,row,index){
                        return row.text;
                    }
                }
	]],
        url: livesite + 'ExpenseReport/listcriteriaitems/'+criteria,
        //checkbox: true,
        checkOnSelect: true,
	searchFilter:true,
        singleSelect: false,
        lines: true,
        fitColumns: true,
        valueField: "key",
        onCheck: function (i, rows) {
            
        },
        onUncheck: function (i, rows) {

        },
        onLoadSuccess: function () {
              setwidth();
        }
    });
    
});
</script>
<?php } ?>