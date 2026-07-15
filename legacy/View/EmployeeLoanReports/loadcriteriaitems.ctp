<div class="col-md-2">
    <a onclick="toggleItemsDisplay(<?php echo $index; ?>);"><i class="fa fa-minus"></i></a>
</div>
<div class="col-md-12"></div><br>
<div class="col-md-10" style="float: right; margin-top: -21px;">
    <?php
    if ($criteria == "Units") {
        $msg = "Select Branches";
    } else {
        $msg = "Select " . $criteria;
    }
    ?>
    <ul title="<?php echo $msg; ?>" lines="true" style="width:100%;min-height:200px;height:auto;max-height:500px;">
        <li></li>
    </ul>
    <input type="hidden" id="rsndempid" value="0" name="resigned">
    <!-- Edited by Akshay on 6-8-2024 -->
    <input type="hidden" id="loan_comp" value="0" name="loan_comp">
    <!-- End -->
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

        //Edited by Akshay on 6-8-2024
        var type = $('#hidden-report-type').val();
        if ((type === 'Loan') && $('#loan_complete').length === 0) {
            $('.datagrid-toolbar').find('tr').append('<td>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<input type="checkbox" name"loan_complete" id="loan_complete" onclick="showLoanCompleted();" value="0">&nbsp;Include Completed </td>');
        }
        //End

        if ($('#checkrsgnd').length == 0) {
            <?php
            if ($type == 'LoanSummary') {
            ?>
                $('.datagrid-toolbar').find('tr').append('<td>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<input type="checkbox" name"rsgnemp" id="checkrsgnd" onclick="showrsgnd();" value="0">&nbsp;Include Closed </td>');
            <?php
            } else {
            ?>
                $('.datagrid-toolbar').find('tr').append('<td>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<input type="checkbox" name"rsgnemp" id="checkrsgnd" onclick="showrsgnd();" value="0">&nbsp;Include Resigned </td>');
            <?php
            }
            ?>
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

    //Edited by Akshay on 6-8-2024
    function showLoanCompleted() {

        if ($('#loan_complete').is(":checked")) {
            $('#loan_comp').val('1');

        } else {
            $('#loan_comp').val('0');

        }
        $('#div-items-criteria<?php echo $index; ?> ul').datalist('load', {
            name: $('#loan_comp').val(),

        });
    }
    //End

    $(document).ready(function() {
        var criteria = $('#hidden-criteria<?php echo $index; ?>').val();
        $('#div-items-criteria<?php echo $index; ?> ul').datalist({
            rowStyler: function(index, row) {
                var style = "";
                if (row.status == '2') {
                    style += 'background-color:#cac3c3;color:#fff;';
                }
                return style;
            },
            toolbar: [
                <?php
                if ($type == 'LoanDetails') {
                } else {
                ?> {
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
                    }
                <?php
                }
                ?>
            ],
            //frozenColumns:[[
            columns: [
                [{
                        field: criteria + '[]',
                        formatter: function(value, row, index) {
                            <?php
                            if ($type == 'LoanDetails') {
                            ?>
                                return '<input type="radio" class="checkw" name="' + criteria + '[]" value="' + row.key + '" />';
                            <?php
                            } else {
                            ?>

                                return '<input type="checkbox" class="checkw" name="' + criteria + '[]" value="' + row.key + '" />';

                            <?php }
                            ?>
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
            url: livesite + 'EmployeeLoanReports/listcriteriaitems/' + criteria,
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