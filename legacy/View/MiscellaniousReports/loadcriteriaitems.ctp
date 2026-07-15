


<div class="col-md-2">
    <a onclick="toggleItemsDisplay(<?php echo $index; ?>);"><i class="fa fa-minus"></i></a>
</div><div class="col-md-12"></div><br>
<div class="col-md-12" style="padding-left: 55px; margin-top: -19px;">
	<?php
    if($criteria=="Units"){
        $criteria="Branches";
    }
    ?> 
    <ul title="<?php echo 'Select '.$criteria; ?>" lines="true" style="width:300px;min-height:200px;height:auto;max-height:500px;"></ul>
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
            $('.datagrid-toolbar').find('tr').append('<td>&nbsp;&nbsp;&nbsp;&nbsp;<input type="checkbox" name"rsgnemp" id="checkrsgnd" onclick="showrsgnd();" value="0"> &nbsp;Include Resigned </td>');
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

    //edited by athira on 21-11-2025
    <?php if ($criteria == "Branches") : ?>
var isBranchCriteria = true;
<?php else: ?>
var isBranchCriteria = false;
<?php endif; ?>
//end

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
            // handler: function () {
            //     $('#div-items-criteria<?php echo $index; ?> ul').datalist('checkAll');
            //     $('#div-items-criteria<?php echo $index; ?> .datagrid-view input:checkbox').each(function(){
            //         this.checked = true;
            //     });
            // }
            //edited by athira 21-11-2025
           handler: function () {
    var $box = $('#div-items-criteria<?php echo $index; ?>');
    var $dl = $box.find('ul');

    if (!isBranchCriteria) {
        // Normal select all for other criteria
        $dl.datalist('checkAll');
        $box.find('.datagrid-view input:checkbox').prop("checked", true);
        return;
    }

    // ----- Branch limit logic -----
    $dl.datalist('clearChecked');

    var rows = $dl.datalist('getRows');

    for (var i = 0; i < rows.length && i < 5; i++) {
        $dl.datalist('checkRow', i);
    }

    var $checkboxes = $box.find('.datagrid-view input:checkbox');
    $checkboxes.prop('checked', false);
    $checkboxes.slice(0, 5).prop('checked', true);

    $checkboxes.not(':checked').prop('disabled', true);

    if ($box.find('.limit-msg').length === 0) {
        $box.find('.datagrid-toolbar').after(
            '<span class="limit-msg" style="color:red;font-size:12px;display:block;margin-top:5px;">Only 5 branches can be selected.</span>'
        );
    }
}

//end


        },{
            text: 'Deselect all',
            iconCls: 'icon-delete',
            // handler: function () {
            //     $('#div-items-criteria<?php echo $index; ?> ul').datalist('clearChecked');
            //     $('#div-items-criteria<?php echo $index; ?> .datagrid-view input:checkbox').each(function(){
            //         this.checked = false;
            //     });
            // }
            //edited by athira 21-11-2025
           handler: function () {
    var $box = $('#div-items-criteria<?php echo $index; ?>');
    var $dl = $box.find('ul');

    $dl.datalist('clearChecked');
    var $checkboxes = $box.find('.datagrid-view input:checkbox');

    $checkboxes.prop('checked', false);

    // For non-branch criteria → no disabling
    if (!isBranchCriteria) {
        return;
    }

    // Branch only → re-enable
    $checkboxes.prop('disabled', false);
    $box.find('.limit-msg').remove();
}
//end

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
        url: livesite + 'miscellaniousReports/listcriteriaitems/'+criteria,
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
        //edited by athira on 21-11-2025
        onLoadSuccess: function () {
    setwidth();
    var $box = $('#div-items-criteria<?php echo $index; ?>');

    if (!isBranchCriteria) return;  // <-- Non-branches skip limit

    var updateLimitState = function () {
        var $checkboxes = $box.find('.datagrid-view input:checkbox');
        var selectedCount = $checkboxes.filter(':checked').length;

        if (selectedCount >= 5) {
            $checkboxes.not(':checked').prop('disabled', true);
            if ($box.find('.limit-msg').length === 0) {
                $box.find('.datagrid-toolbar').after(
                    '<span class="limit-msg" style="color:red;font-size:12px;display:block;margin:5px;">Only 5 branches can be selected.</span>'
                );
            }
        } else {
            $checkboxes.prop('disabled', false);
            $box.find('.limit-msg').remove();
        }
    };

    $box.find('.datagrid-view input:checkbox')
        .off('change')
        .on('change', function () {
            updateLimitState();
        });

    updateLimitState();
}
//end


    });
});
</script>
