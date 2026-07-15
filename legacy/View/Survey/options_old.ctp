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
    <h1 style="text-align:left; font-size: 3em;"> Survey Data Items   </h1>
</section>
<section class="content">
    <div class="row">
        <div class="col-md-12">
            <div class="box ">
                <br>
                <div class="box-body">
                    <form class="form-horizontal" method="post" action=""  id="options_index_form" name="options_index_form"> 
                        <div class="row">
                            <div class="form-group">
                                <div class="col-sm-4">
                                    <label class="col-sm-5 control-label" for="survey_type">Choose Survey Type</label>
                                    <div class="col-md-7">
                                        <select id="survey_type_filter" name="survey_type_filter" class="form-control js-example-basic-single" onchange="filterOptions(this);" >
                                            <option value="">All</option>
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
                                        <select id="survey_category_filter" name="survey_category_filter" class="form-control js-example-basic-single" onchange="filterOptions(this);" >
                                            <option value="">All</option>
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
                                                                    
                            </div>
                        </div>
                    </form>
                    <div class="box-body">
                        <table id="options_table" class="table table-bordered table-hover" pagination="true">
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

    function getSurveyCategory(){
        var survey_type = $('#option_form #survey_type').val();
        var action      = ($('#option_form #option_id').val() != "")?'edit':'add';
        var row         = $('#options_table').datagrid('getSelected');
        
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
                $('#option_form #survey_category').html(surveyCategoryHtml);
                if ( row != null && action == 'edit') {
                    $('#option_form #survey_category option[value='+row.category_id+']').prop("selected",true);    
                    getOptionOrder();
                }
                
           }
       });
    }
    function filterOptions(obj) {
        var survey_type     = $('#options_index_form #survey_type_filter').val();
        var survey_category = $('#options_index_form #survey_category_filter').val();
    
        $('#options_table').datagrid('load', {
            survey_type: survey_type,
            survey_category:survey_category
        });
    }
   
    function getOptionOrder(obj){

        var survey_type     = $('#option_form #survey_type').val();
        var survey_category = $('#option_form #survey_category').val();

        var action          = ($('#option_form #option_id').val() != "")?'edit':'add';
        var isDisabled      = (action == 'add')? 'disabled' :'';
        var row             = $('#options_table').datagrid('getSelected');
        var orderHtml = '<option value=0 > Choose Option Order</option>';
        if (survey_type != "" && survey_category != "" && survey_category != 0 && survey_type != 0){
            $.ajax({
                url:'Survey/getOptionsOrder',
                type: 'POST',
                data: {
                   survey_type: survey_type,
                   survey_category:survey_category
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
                    $('#option_form #option_items_order').html(orderHtml);
                    if ( row != null && action == 'edit') {
                        $('#option_form #prev_option_order').val(row.o_order);
                        $('#option_form #option_items_order option[value='+row.o_order+']').prop("selected",true);
                        $('#option_form #survey_type').addClass('mouse-disable').parent('div .col-sm-7').addClass('no-drop');
                        $('#option_form #survey_category').addClass('mouse-disable').parent('div .col-sm-7').addClass('no-drop');
                    }else{
                        $('#option_form #option_items_order option[value='+(data.length+1)+']').prop("selected",true);
                    }
               }
             });
        }else{
            $('#option_form #option_items_order').html(orderHtml);
        }
         
    }

    function validateOptionForm(){
    
        
        var valid = true;
        
        var survey_typeObj    = $('#option_form #survey_type');
        var categoryObj       = $('#option_form #survey_category');
        var option_nameObj    = $('#option_form #option_items_name');
        var option_orderObj   = $('#option_form #option_items_order');
        var option_item_typeObj   = $('#option_form #option_item_type');
        var option_items_valObj   = $('#option_form #option_items_val');

        if (survey_typeObj.val() == "0"){
            survey_typeObj.parents('div .col-sm-12').addClass('has-error')
            survey_typeObj.parents('div .col-sm-7').append('<span class="help-block">Please choose a survey type.</span>');
            valid =false;
        }
        if (categoryObj.val() == "0"){
            categoryObj.parents('div .col-sm-12').addClass('has-error')
            categoryObj.parents('div .col-sm-7').append('<span class="help-block">Please choose a survey category.</span>');
            valid =false;
        }
        
        if (option_nameObj.val() == ""){
            option_nameObj.parents('div .col-sm-12').addClass('has-error')
            option_nameObj.parents('div .col-sm-7').append('<span class="help-block">Option name is required.</span>');
            valid =false;
        }
        if (option_orderObj.val() == "0"){
            option_orderObj.parents('div .col-sm-12').addClass('has-error')
            option_orderObj.parents('div .col-sm-7').append('<span class="help-block">Option order is required.</span>');
            valid =false;
        }
        if (option_item_typeObj.val() == "0"){
            option_item_typeObj.parents('div .col-sm-12').addClass('has-error')
            option_item_typeObj.parents('div .col-sm-7').append('<span class="help-block">Option Type is required.</span>');
            valid =false;
        }
        if (option_items_valObj.val() == "" && $("[name='option_items_values[]']").length == 0 && option_item_typeObj.val() != 'Input_box' && option_item_typeObj.val() !=  'Text_box'){
            option_items_valObj.parents('div .col-sm-12').addClass('has-error addnewBlk')
            option_items_valObj.parents('div .col-sm-6').append('<span class="help-block addnewBlkSpan">Option item value is required.</span>');
            valid =false;
        }
        
        return valid;
    }
    

    var action = '';
    
    jQuery(document).ready(function () {

        

        var survey_type     = $('#options_index_form #survey_type_filter').val();
        var survey_category = $('#options_index_form #survey_category_filter').val();

        $('#options_table').datagrid({
            url: livesite + "Survey/getOptionsList",
            pagination: true,
            singleSelect: true,
            rownumbers: true,
            queryParams: {
                survey_type: survey_type,
                survey_category:survey_category
            },
            toolbar: [{
                    text: 'New',
                    iconCls: 'icon-add',
                    handler: function () {
                        action = 'add';
                            showLargeModalForm(livesite + 'Survey/optionsForm')
                        
                    }
                },
                {
                    iconCls: 'icon-edit',
                    text: 'Edit',
                    handler: function () {
                        action = 'edit';
                        var row = $('#options_table').datagrid('getSelected');
                        if (row) {

                            showLargeModalForm(livesite + 'Survey/optionsForm/' + row.o_id,'getSurveyCategory()')  
                        
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
                        var rows = $('#options_table').datagrid('getSelected');
                        if (rows){
                            var option_id = rows.o_id;
                            if (confirm("Are you sure want to delete ")) {
                                $.ajax({
                                    url:  livesite+"Survey/deleteOptionItem",
                                    data : {
                                    ids : option_id
                                    },
                                    success : function(response) {
                                        var response =  $.parseJSON(response);
                                        if(response.msg){
                                            $.notify(response.msg,{
                                                type: 'success',
                                                allow_dismiss: true
                                            });
                                        }
                                        reloadTable('options_table')
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
                    {field: 'o_name', title: 'Option Name', width: "30%"},
                    {field: 'o_order', title: 'Option Order', width: "10%"},
                    {field: 'o_type', title: 'Option Item Type', width: "30%"},
                    {field: 'o_values', title: 'Option Item Values', width: "30%"},
                    
                ]]
        });

    });
</script>