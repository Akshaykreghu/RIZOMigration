<div class="col-md-2">
    <a onclick="toggleItemsDisplay(<?php echo $index; ?>);"><i class="fa fa-minus"></i></a>
</div>
<div class="col-md-12"></div><br>
<div class="col-md-10" style="float: inherit; margin-top: -21px;">
    <?php if ($criteria == 'Units') {
        $criteria = "Branches";
    } ?>
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
            $('.datagrid-toolbar').find('tr').append('<td>&nbsp;&nbsp;<input type="checkbox" name"rsgnemp" id="checkrsgnd" onclick="showrsgnd();" value="0">&nbsp;Include Resigned </td>'); // Edited by Akshay on 10-12-2024
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
        var type = $('#filterby_reporttype').val(); // Edited by Akshay on 3-3-2025 - Start
        //edited by athira on 12-03-2025
        var selectionLimit;
        if (criteria==='EmployeeDetails'){
            selectionLimit = 10; // Limit for Units
        }
        //end
        // Define toolbar based on type condition
        var toolbarOptions = [];
        if (type !== 'nonpunched' && type !== 'nonattendance') {
            toolbarOptions = [{
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
            }];
        } // Edited by Akshay on 3-3-2025 - End

        $('#div-items-criteria<?php echo $index; ?> ul').datalist({
            rowStyler: function(index, row) {
                var style = "";
                if (row.status == '2') {
                    style += 'background-color:#cac3c3;color:#fff;';
                }
                return style;
            },
            toolbar: toolbarOptions,
             // Edited by Akshay on 3-3-2025 - Start
            columns: [
                [{
                        field: criteria + '[]',
                        formatter: function(value, row, index) {
                            // Edited by Akshay on 3-3-2025
                            var type = $('#filterby_reporttype').val();
                            var inputType = (type === 'nonpunched' || type === 'nonattendance') ?
                                '<?php echo ($criteria == "Branches") ? "radio" : "checkbox"; ?>' : 'checkbox';

                            return '<input type="' + inputType + '" class="checkw" name="' + criteria + '[]" value="' + row.key + '" />';
                            // End
                        }
                    },
                    {
                        field: 'Name',
                        formatter: function(value, row, index) {
                            return row.text;
                        }
                    }
                ]
            ], // Edited by Akshay on 3-3-2025 - End
            url: livesite + 'arrearsReports/listcriteriaitems/' + criteria,
            checkOnSelect: true,
            searchFilter: true,
            singleSelect: false,
            lines: true,
            valueField: "key",
            //edited by athira on 12-03-2025
            onCheck: function(i, rows) {
                updateCheckboxState();
            },
            onUncheck: function(i, rows) {
                updateCheckboxState();
            },
            onLoadSuccess: function() {
                setwidth();
            }
            //end
        });
        //edited by athira on 12-03-2025
        function updateCheckboxState() {
        let checkboxes = $('#div-items-criteria<?php echo $index; ?> input:checkbox').not('#checkrsgnd');
        let checkedCount = checkboxes.filter(':checked').length;

        if (checkedCount >= selectionLimit) {
            checkboxes.not(':checked').prop('disabled', true);
        } else {
            checkboxes.prop('disabled', false);
        }
        $('#checkrsgnd').prop('disabled', false);
    }
    $('#div-items-criteria<?php echo $index; ?>').on('change', 'input:checkbox', function() {
        updateCheckboxState();
    });
    //end
    });
</script>