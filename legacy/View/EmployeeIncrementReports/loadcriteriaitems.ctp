


<div class="col-md-2">
    <!-- <a onclick="toggleItemsDisplay(<?php echo $index; ?>);"><i class="fa fa-minus"></i></a> -->
</div><div class="col-md-12"></div><br>
<div class="col-md-10" style="float: right; margin-top: -20px;">

    <ul title="<?php echo 'Select '.$criteria; ?>" lines="true" style="width:100%;min-height:200px;height:auto;max-height:500px;"><li></li></ul>
<input type="hidden" id="rsndempid" value="0" name="resigned">
</div>
<script>
function toggleItemsDisplay(index){
    $('#div-items-criteria'+index+' .panel-body').fadeToggle('slow', function() {
        if($(this).is(":visible")){
            $('#div-items-criteria'+index+' a .fa').removeClass('fa-plus');
            $('#div-items-criteria'+index+' a .fa').addClass('fa-minus');
        }else{
            $('#div-items-criteria'+index+' a .fa').removeClass('fa-minus')
            $('#div-items-criteria'+index+' a .fa').addClass('fa-plus');
        }
    });
}
function setwidth(){
    //alert("hi");
    $('.checkw').parent().parent().addClass('checkw').css("width","30px");
     var criteria = $('#hidden-criteria<?php echo $index; ?>').val();
            
            if ($('#checkrsgnd').length == 0) {
          // exists.
            $('.datagrid-toolbar').find('tr').append('<td>&nbsp;&nbsp;&nbsp;&nbsp;<input type="checkbox" name"rsgnemp" id="checkrsgnd" onclick="showrsgnd();" value="0">Include Resigned </td>');
         
            }
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
            name: $('#rsndempid').val(),
            
            });
    }
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
        url: livesite + 'EmployeeIncrementReports/listcriteriaitems/'+criteria,
        //checkbox: true,
		searchFilter:true,
        checkOnSelect: true,
        singleSelect: false,
        lines: true,
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
