<?php
$link = '';
$value = '';
$header_list = '';
if ($criteria == 'banks') { // This is to get site by employee
    $link = 'listBankBranches';
    $value = $banks;
    $header_list = 'Branches';
} else { // This is to get client by branch
    $link = '';
    $value = '';
    $header_list = '';
}
?>

<div class="col-md-12" style="float: inherit;" id="criteriasite">
    <ul title="Select <?php echo $header_list ?>" lines="true" style="width:100%;min-height:200px;height:auto;max-height:500px;">
        <li></li>
    </ul>
</div>
<input type="hidden" id="rsndempid1" value="0" name="resigned1">
<script>
    function setwidth1() {
        $('.checkw1').parent().parent().css("width", "30px");
    }

    function showrsgnd1() {

        if ($('#checkrsgnd1').is(":checked")) {
            $('#rsndempid1').val('1');

        } else {
            $('#rsndempid1').val('0');

        }
    }

    function togglePrintContainer() {
        // Example function to show/hide print container
        var checkedItems = $('#criteriasite ul').datalist('getChecked');
        console.log('checkedItems',checkedItems);
        if (checkedItems.length > 0) {
            $('#print-container').show();
            $('#print-select').val('');
            $('#print-select').trigger('change');
        } else {
            $('#print-container').hide();
        }
    }

    function siteCriteria() {
        var criteria = "<?php echo ($header_list == 'Bank') ? 'bank_branch' : 'Site'; ?>";
        console.log('Criteria', criteria);
        try {
            $('#criteriasite ul').datalist({
                toolbar: [{
                    text: 'Select all',
                    iconCls: 'icon-ok',
                    handler: function() {
                        $('#criteriasite ul').datalist('checkAll');
                        $('#criteriasite .datagrid-view input:checkbox').each(function() {
                            this.checked = true;
                        });
                        togglePrintContainer();
                    }
                }, {
                    text: 'Deselect all',
                    iconCls: 'icon-delete',
                    handler: function() {
                        $('#criteriasite ul').datalist('clearChecked');
                        $('#criteriasite .datagrid-view input:checkbox').each(function() {
                            this.checked = false;
                        });
                        togglePrintContainer();
                    }
                }],
                columns: [
                    [{
                            field:  'key',
                            formatter: function(value, row, index) {
                                return '<input type="checkbox" class="checkw1" name="bank_branch[]" value="' + row.key + '" />';
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
                url: livesite + 'SalaryReports/<?php echo $link; ?>/<?php echo json_encode($value); ?>/<?php echo json_encode($month); ?>',
                checkOnSelect: true,
                searchFilter: false,
                singleSelect: false,
                lines: true,
                valueField: "key",
                onCheck: function(rows, i) {
                    // console.log('Rows', rows);
                    togglePrintContainer();
                },
                onUncheck: function(rows, i) {
                    togglePrintContainer();
                },
                onLoadSuccess: function(data) {
                    console.log('Data', data.rows);
                    setwidth1(); // Assuming this function exists
                }
            });
        } catch (error) {
            console.error('Error in datagrid initialization:', error);
        }

        // Append search input field
        if ($('#div-items-criteria2 input#search2').length === 0)
        $('#div-items-criteria2 div.datagrid-toolbar').after('<input type="text" id="search2" class="form-control" autocomplete="off" placeholder="Search">');

        // Fetch data and load into datagrid
        // $.ajax({
        //     url: livesite + 'SalaryReports/<?php echo $link; ?>/<?php echo json_encode($value); ?>',
        //     method: 'GET',
        //     success: function(response) {
        //         var data = response; // Assuming response.data contains the array of objects
        //         console.log('Data2', data);
        //         // Load data into the datagrid
        //         $('#criteriasite ul').datalist('loadData', data);
        //     },
        //     error: function(error) {
        //         console.error('Failed to load data:', error);
        //     }
        // });
    }
    siteCriteria();
    $(document).ready(function() {
        //The below keyup function is used to search elements. By ****ARUL P DAS on 16/1/2020
        $('#search2').keyup(function() {
            var val = $.trim($(this).val()).replace(/ +/g, ' ').toLowerCase();

            $('#div-items-criteria2 .datagrid-btable tr').show().filter(function() {
                var text = $(this).text().replace(/\s+/g, ' ').toLowerCase();
                return !~text.indexOf(val);
            }).hide();
        });
    });
</script>
<style type="text/css">
    div .datagrid-body {
        /*This is to solve the issue when adding three criterias(jQuery issue). By ***ARUL P DAS on 27/12/2019*/
        width: auto !important;
    }
</style>