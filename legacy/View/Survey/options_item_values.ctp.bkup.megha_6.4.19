<style>
    .form-horizontal .control-label{

        text-align: left;

    }
    .no-drop{
        cursor: no-drop;
    }
    .mouse-disable{
        pointer-events: none;
    }
</style>

<section class="content-header">
    <h1 style="text-align:left; font-size: 3em;"> Options Item Values Master   </h1>
</section>
<section class="content">
    <div class="row">
        <div class="col-md-12">
            <div class="box ">
                <br>
                <div class="box-body">
                    <form class="form-horizontal" method="post" action=""  id="frm_options_item_values" name="frm_options_item_values"> 
                        <div class="row">
                            <div class="form-group">
                                <div class="col-sm-4">
                                    <label class="col-sm-5 control-label" for="survey_type">Choose Survey Type</label>
                                    <div class="col-md-7">
                                        <select id="survey_type_filter" name="survey_type_filter" class="form-control js-example-basic-single" onchange="fillSurveyCategoryFilter(this);" >
                                            <option value="0">All</option>
                                            <?php foreach ($survey_types as $key => $value) { 
                                            ?>                              
                                                <option  value="<?php echo $value['survey_type']['type_pkey']; ?>">
                                                    <?php echo $value['survey_type']['type_name']; ?>
                                                </option>
                                            <?php 
                                                } 
                                            ?>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-sm-4">
                                    <label class="col-sm-5 control-label" for="survey_type">Choose Survey Category</label>
                                    <div class="col-md-7">
                                        <select id="survey_category_filter" name="survey_category_filter" class="form-control js-example-basic-single" onchange="fillOptionItemFilter(this);" >
                                            <option value="0">All</option>
                                            <?php foreach ($survey_categories as $key => $value) { 
                                            ?>                              
                                                <option  value="<?php echo $value['survey_category']['category_pkey']; ?>">
                                                    <?php echo $value['survey_category']['category_code']; ?>
                                                </option>
                                            <?php 
                                                } 
                                            ?>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-sm-4">
                                    <label class="col-sm-5 control-label" for="option_item">Choose Option Item</label>
                                    <div class="col-md-7">
                                        <select id="option_item_filter" name="option_item_filter" class="form-control js-example-basic-single" onchange="filterOptions(this);">
                                            <option value="0">All</option>
                                            <?php foreach ($option_items as $key => $value) { 
                                            ?>                              
                                                <option  value="<?php echo $value['option_items']['o_id']; ?>">
                                                    <?php echo $value['option_items']['o_name']; ?>
                                                </option>
                                            <?php 
                                                } 
                                            ?>
                                        </select>
                                    </div>
                                </div>
                                                                    
                            </div>
                        </div>
                    </form>
                    <div class="box-body">
                        <table id="options_value_table" class="table table-bordered table-hover" pagination="true">
                            <tbody>
                            </tbody>
                        </table>
                    </div><!-- /.box-body -->
                </div>

            </div>
        </div>
    </div>
</section>

<script>

    var action = '';
    
    jQuery(document).ready(function () {

        var survey_type     = $('#frm_options_item_values #survey_type_filter').val();
        var survey_category = $('#frm_options_item_values #survey_category_filter').val();
        var option_item     = $('#frm_options_item_values #option_item_filter').val();
        
        
        
            $('#options_value_table').datagrid({
                url: livesite + "Survey/getOptionItemValues",
                pagination: true,
                singleSelect: true,
                rownumbers: true,
                queryParams: {
                    survey_type     : survey_type,
                    survey_category : survey_category,
                    option_item     : option_item
                },
                toolbar: [{
                        text: 'New',
                        iconCls: 'icon-add',
                        handler: function () {
                            action = 'add';
                                if (addValidation()){
                                    var category_id = $('#frm_options_item_values #survey_category_filter').val();
                                    showLargeModalForm(livesite + 'Survey/optionsItemValuesForm?category_id='+category_id);    
                                }
                            
                        }
                    },
                    {
                        iconCls: 'icon-edit',
                        text: 'Edit',
                        handler: function () {
                            action = 'edit';
                            var row = $('#options_value_table').datagrid('getSelected');
                            if (row) {
                                
                                showLargeModalForm(livesite + 'Survey/optionsItemValuesForm/' + row.oiv_id,'setUI()')  
                            
                            } else
                                $.notify('Please Select A record to return', {
                                    type: 'danger',
                                    allow_dismiss: false
                                });
                        }
                    },'-',
                    {
                        iconCls: 'icon-remove',
                        text:'Delete',
                        handler: function(){
                            var rows = $('#options_value_table').datagrid('getSelected');
                            if (rows){
                                var option_value_id = rows.oiv_id;
                                if (confirm("Are you sure want to delete ")) {
                                    $.ajax({
                                        url:  livesite+"Survey/deleteOptionItemValue",
                                        data : {
                                        ids : option_value_id
                                        },
                                        success : function(response) {
                                            var response =  $.parseJSON(response);
                                            if(response.msg){
                                                $.notify(response.msg,{
                                                    type: 'success',
                                                    allow_dismiss: true
                                                });
                                            }
                                            reloadTable('options_value_table')
                                        }
                                    });
                                }
                            }else{
                                alert("Please select atleast one record to delete")
                            }
                        }
                 }
                    
                ],
                fitColumns: true,
                pageList: [2, 5, 10, 50, 100],
                columns: [[
                        {field: 'option_item_type', title: 'Option Item Type', width: "40%"},
                        {field: 'option_items_values', title: 'Option Item Value', width: "40%"},
                        {field: 'option_values_order', title: 'Option Item Order', width: "20%"},
                        
                ]]
            });
        
        

    });
    function fillSurveyCategoryFilter(){
        var survey_type = $('#frm_options_item_values #survey_type_filter').val();
        $.ajax({
         url:'Survey/getSurveyCategoryBySurveyType',
           type: 'POST',
           data: {
               survey_type: survey_type
           },
           success: function (resp){
                var surveyCategoryHtml = '<option value="0">Choose Survey Category </option>';
                var data = $.parseJSON(resp);
                for (i in data){
                   surveyCategoryHtml += "<option value='"+data[i].category_pkey+"'>"+data[i].category_code+"</option>" ;
                }
                $('#frm_options_item_values #survey_category_filter').html(surveyCategoryHtml);
           }
       });
    }
    function fillOptionItemFilter(){
       var survey_category = $('#frm_options_item_values #survey_category_filter').val();
        $.ajax({
         url:'Survey/getOptionItemByCategory',
           type: 'POST',
           data: {
               survey_category: survey_category
           },
           success: function (resp){
                var optionItemHtml = '<option value="0">Choose Option Item</option>';
                var data = $.parseJSON(resp);
                for (i in data){
                   optionItemHtml += "<option value='"+data[i].o_id+"'>"+data[i].o_name+"</option>" ;
                }
                $('#frm_options_item_values #option_item_filter').html(optionItemHtml);
           }
       }); 
    }
    function filterOptions(obj) {
        var survey_type     = $('#frm_options_item_values #survey_type_filter').val();
        var survey_category = $('#frm_options_item_values #survey_category_filter').val();
        var option_item     = $('#frm_options_item_values #option_item_filter').val();
    
        $('#options_value_table').datagrid('load', {
            survey_type: survey_type,
            survey_category:survey_category,
            option_item:option_item
        });
    }
    function addValidation(){
        var valid = true;
        
        var survey_typeObj    = $('#frm_options_item_values #survey_type_filter');
        var categoryObj       = $('#frm_options_item_values #survey_category_filter');

        var msg ='';
        if (survey_typeObj.val() == "0" || survey_typeObj.val() == ""){
            msg = 'Please choose survey type';
            valid =false;
        }
        if (categoryObj.val() == "0" || categoryObj.val() == ""){
            msg += (msg!='') ? ' and survey category.' : 'Please choose survey type';
            valid =false;
        } 
        if(msg != ''){
            alert(msg);    
        }
        return valid;
    }

    function validateOptionItemValueForm(){
    
        
        var valid = true;
        
        var option_itemObj = $('#frm_option_item_values_form #option_item');
        var item_typeObj   = $('#frm_option_item_values_form #item_type');
        var item_valueObj  = $('#frm_option_item_values_form #item_value');
        var item_orderObj  = $('#frm_option_item_values_form #item_order');

        if (option_itemObj.val() == "0"){
            option_itemObj.parents('div .col-sm-12').addClass('has-error')
            option_itemObj.parents('div .col-sm-7').append('<span class="help-block">Please choose a option item.</span>');
            valid =false;
        }
        if (item_typeObj.val() == "0"){
            item_typeObj.parents('div .col-sm-12').addClass('has-error')
            item_typeObj.parents('div .col-sm-7').append('<span class="help-block">Please choose a option value item type.</span>');
            valid =false;
        }
        
        if (item_valueObj.val() == ""){
            item_valueObj.parents('div .col-sm-12').addClass('has-error')
            item_valueObj.parents('div .col-sm-7').append('<span class="help-block">Option item value is required.</span>');
            valid =false;
        }
        if (item_orderObj.val() == "0"){
            item_orderObj.parents('div .col-sm-12').addClass('has-error')
            item_orderObj.parents('div .col-sm-7').append('<span class="help-block">Please choose item order.</span>');
            valid =false;
        }
        
        return valid;
    }
    
    function setUI(){
        var row             = $('#options_value_table').datagrid('getSelected');
        if ( row != null){
            $('#frm_option_item_values_form #item_type option[value='+row.option_item_type+']').prop("selected",true);
            $('#frm_option_item_values_form #option_item option[value='+row.option_item_id+']').prop("selected",true);
            $('#frm_option_item_values_form #option_item').addClass('mouse-disable').parent('div .col-sm-7').addClass('no-drop');
            getOptionsItemValueOrder();
        }
    }
    function getOptionsItemValueOrder(obj){

        var option_item     = $('#frm_option_item_values_form #option_item').val();

        var action          = ($('#frm_option_item_values_form #option_value_id').val() != "")?'edit':'add';
        

        var isDisabled      = (action == 'add')? 'disabled' :'';

        var row             = $('#options_value_table').datagrid('getSelected');
        var orderHtml = '<option value=0 > Choose Option Item Order</option>';
    
        $.ajax({
                url:'Survey/getOptionsItemValueOrder',
                type: 'POST',
                data: {
                   option_id: option_item
                },
                success: function (resp)
                {
                    var data = $.parseJSON(resp);
                    
                    if(data.length > 0){
                        for (i in data){
                            orderHtml +="<option  "+isDisabled+" value="+data[i]+">"+data[i]+"</option>";
                        }
                        if (action == 'add')
                            orderHtml  +="<option   value="+(data[data.length-1]+1)+">"+(data[data.length-1]+1)+"</option>";
                    }else{
                        orderHtml = '<option value="1" selected="selected">1</option>';
                    }
                    $('#frm_option_item_values_form #item_order').html(orderHtml);
                    if ( row != null && action == 'edit') {
                        $('#frm_option_item_values_form #prev_option_item_order').val(row.option_values_order);
                        $('#frm_option_item_values_form #item_order option[value='+row.option_values_order+']').prop("selected",true);
                    }else{
                        $('#frm_option_item_values_form #item_order option[value='+(data.length+1)+']').prop("selected",true);
                    }
               }
        });
    }
</script>