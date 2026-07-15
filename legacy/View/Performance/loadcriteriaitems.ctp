<div class="col-md-10" style="float: inherit; margin-top: -1px; margin-bottom:-25px;margin-right:65px;padding-left: 85px;width:150%;">
    <ul title="<?php echo 'Select ' . $criteria; ?>" lines="true" style="width:100%;min-height:200px;height:auto;max-height:500px;">
        <li></li>
    </ul>
    <input type="hidden" id="rsndempid" value="0" name="resigned">
</div>
<script>
    function toggleItemsDisplay(index) {
        $('#div-items-criteria' + index + ' .panel-body').fadeToggle('slow', function() {
            if ($(this).is(":visible")) {
                $('#div-items-criteria' + index + ' a .fa').removeClass('fa-plus');
                $('#div-items-criteria' + index + ' a .fa').addClass('fa-minus');
            } else {
                $('#div-items-criteria' + index + ' a .fa').removeClass('fa-minus')
                $('#div-items-criteria' + index + ' a .fa').addClass('fa-plus');
            }
        });
    }

    function setwidth() {
        //alert("hi");
        $('.checkw').parent().parent().addClass('checkw').css("width", "30px");
        var criteria = $('#hidden-criteria<?php echo $index; ?>').val();

        if ($('#checkrsgnd').length == 0) {
            // exists.
            $('.datagrid-toolbar').find('tr').append('<td>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<input type="checkbox" name"rsgnemp" id="checkrsgnd" onclick="showrsgnd();" value="0"> &nbsp;Include Resigned </td>');
        }

    }

    function showrsgnd() {

        if ($('#checkrsgnd').is(":checked")) {
            $('#rsndempid').val('1');

        } else {
            $('#rsndempid').val('0');

        }
        $('#div-items-criteria<?php echo $index; ?> ul').datalist('load', {
            name: $('#rsndempid').val(),

        });
    }
    $(document).ready(function() {
        var criteria = $('#hidden-criteria<?php echo $index; ?>').val();
        var report = $('#filterby_reporttype').val(); // Edited by Akshay on 22-5-2025
        $('#div-items-criteria<?php echo $index; ?> ul').datalist({
            rowStyler: function(index, row) {
                var style = "";
                if (row.status == '2') {
                    style += 'background-color:#cac3c3;color:#fff;';
                }
                return style;
            },
            toolbar: [{
                text: 'Select all',
                iconCls: 'icon-ok',
                handler: function() {
                    $('#div-items-criteria<?php echo $index; ?> ul').datalist('checkAll');
                    $('#div-items-criteria<?php echo $index; ?> .datagrid-view input:checkbox').each(function() {
                        this.checked = true;
                    });
                }
            }, {
                text: 'Deselect all',
                iconCls: 'icon-delete',
                handler: function() {
                    $('#div-items-criteria<?php echo $index; ?> ul').datalist('clearChecked');
                    $('#div-items-criteria<?php echo $index; ?> .datagrid-view input:checkbox').each(function() {
                        this.checked = false;
                    });
                }
            }],
            //frozenColumns:[[
            columns: [
                [{
                        field: criteria + '[]',
                        formatter: function(value, row, index) {
                            return '<input type="checkbox" class="checkw" name="' + criteria + '[]" value="' + row.key + '" />';
                        }
                    },
                    {
                        field: 'Name',
                        formatter: function(value, row, index) {
                            return row.text;
                        }
                    }
                ]
            ],
            url: livesite + 'Performance/listcriteriaitems/' + criteria + '/' + report, // Edited by Akshay on 22-5-2025
            //checkbox: true,
            searchFilter: true,
            checkOnSelect: true,
            singleSelect: false,
            lines: true,
            valueField: "key",
            onCheck: function(i, rows) {

            },
            onUncheck: function(i, rows) {

            },
            onLoadSuccess: function() {
                setwidth();
            }
        });
    });
</script>