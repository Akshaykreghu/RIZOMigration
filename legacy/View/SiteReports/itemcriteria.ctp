
<?php
$link = '';
$value = '';
$header_list = '';
if($criteria == 'Site'){ // This is to get site by employee
    $link = 'itemcriterialistSite';
    $value = $employee.'/'.$site_report_date;
    $header_list = 'Site';
} else { // This is to get client by branch
    $link = 'itemcriterialist';
    $value = $branch;
    $header_list = 'Client';
}
?>

<div class="col-md-12" style="float: inherit;" id="criteriasite">
    <ul title="Select <?php echo $header_list ?>" lines="true" style="width:100%;min-height:200px;height:auto;max-height:500px;"><li></li></ul>
</div>
 <input type="hidden" id="rsndempid1" value="0" name="resigned1">
<script>
    function setwidth1() {
        $('.checkw1').parent().parent().css("width", "30px");
        if ($('#checkrsgnd1').length == 0) {
        $('#criteriasite .datagrid-toolbar').find('tr').append('<td>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<input type="checkbox" name"rsgnemp1" id="checkrsgnd1" onclick="showrsgnd1();" value="0">&nbsp;Include Resigned </td>');
    }
    }
     function showrsgnd1() {

        if ($('#checkrsgnd1').is(":checked")) {
            $('#rsndempid1').val('1');

        } else {
            $('#rsndempid1').val('0');

        }
    }
    function siteCriteria() {
        var criteria ="<?php echo ($header_list == 'Client') ? 'Contacts' : 'Site'; ?>";
        $('#criteriasite ul').datalist({
            toolbar: [{
                    text: 'Select all',
                    iconCls: 'icon-ok',
                    handler: function () {
                        $('#criteriasite ul').datalist('checkAll');
                        $('#criteriasite .datagrid-view input:checkbox').each(function () {
                            this.checked = true;
                        });
                    }
                }, {
                    text: 'Deselect all',
                    iconCls: 'icon-delete',
                    handler: function () {
                        $('#criteriasite ul').datalist('clearChecked');
                        $('#criteriasite .datagrid-view input:checkbox').each(function () {
                            this.checked = false;
                        });
                    }
                }],
            //frozenColumns:[[
            columns: [[
                    {
                        field: criteria + '[]',
                        formatter: function (value, row, index) {
                            return '<input type="checkbox" class="checkw1" name="' + criteria + '[]" value="' + row.key + '" />';
                        }
                    },
                    {
                        field: 'Name',
                        formatter: function (value, row, index) {
                            return row.text;
                        }
                    }
                ]],
            url: livesite + 'SiteReports/<?php echo $link;?>/<?php echo $value;?>',
            //checkbox: true,
            checkOnSelect: true,
            searchFilter: false,
            singleSelect: false,
            lines: true,
            valueField: "key",
            onCheck: function (i, rows) {

            },
            onUncheck: function (i, rows) {

            },
            onLoadSuccess: function () {
              setwidth1();
            }
        });
        //The search field added by ****ARUL P DAS on 18/9/2020
        $('#div-items-criteria2 div.datagrid-toolbar').after('<input type="text" id="search2" class="form-control" autocomplete="off" placeholder="Search">');
    }
    siteCriteria();
    $(document).ready(function () {
        //The below keyup function is used to search elements. By ****ARUL P DAS on 16/1/2020
        $('#search2').keyup(function () {
            var val = $.trim($(this).val()).replace(/ +/g, ' ').toLowerCase();

            $('#div-items-criteria2 .datagrid-btable tr').show().filter(function () {
                var text = $(this).text().replace(/\s+/g, ' ').toLowerCase();
                return !~text.indexOf(val);
            }).hide();
        });
    });
</script>
<style type="text/css">
    div .datagrid-body{/*This is to solve the issue when adding three criterias(jQuery issue). By ***ARUL P DAS on 27/12/2019*/
        width: auto !important;
    }
</style>