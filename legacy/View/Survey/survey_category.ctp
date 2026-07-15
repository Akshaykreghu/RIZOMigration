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
    <h1 style="text-align:left; font-size: 3em;"> Survey Heads   </h1>
</section>
<section class="content">
    <div class="row">
        <div class="col-md-12">
            <div class="box ">
                <br>
                <div class="box-body">
                    <form class="form-horizontal" method="post" action=""  id="survey_category_form" name="survey_category_form"> 
                        <div class="row">
                            <div class="form-group">
                                <div class="col-sm-4">
                                    <label class="col-sm-5 control-label" for="survey_type">Choose Survey Type</label>
                                    <div class="col-md-7">
                                        <select id="survey_type_filter" name="survey_type_filter" class="form-control js-example-basic-single" onchange="filterSurveyCategories(this);" >
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
                                
                                                                    
                            </div>
                        </div>
                    </form>
                    <div class="box-body">
                        <table id="survey_category_table" class="table table-bordered table-hover">
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

    function filterSurveyCategories(obj) {
        var survey_type = $('#survey_category_form #survey_type_filter').val();

        $('#survey_category_table').datagrid('load', {
            survey_type: survey_type
        });
    }
    function getCategoryOrder(obj){
        var survey_type = $('#form_survey_category #survey_type').val();
        var action      = ($('#form_survey_category #category_pkey').val() != "")?'edit':'add';
        var isDisabled = (action == 'add')? 'disabled' :'';
        var row = $('#survey_category_table').datagrid('getSelected');
        
         $.ajax({
         url:'Survey/getCategoryOrder',
           type: 'POST',
           data: {
               survey_type: survey_type
           },
           success: function (resp)
           {
                var data = $.parseJSON(resp);
                console.log(data);
                var orderHtml = '';
                if(data.length > 0){
                    for (i in data){
                        orderHtml +="<option  "+isDisabled+" value="+data[i]+">"+data[i]+"</option>";
                    }
                    if (action == 'add')
                        orderHtml  +="<option   value="+(data[data.length-1]+1)+">"+(data[data.length-1]+1)+"</option>";
                }else{
                    orderHtml = '<option value="1" selected="selected">1</option>';
                }
                $('#form_survey_category #category_order').html(orderHtml);
                if ( row != null && action == 'edit') {
                    $('#form_survey_category #prev_category_order').val(row.category_order);
                    $('#form_survey_category #category_order option[value='+row.category_order+']').prop("selected",true);
                    $('#form_survey_category #survey_type').addClass('mouse-disable').parent('div .col-sm-7').addClass('no-drop');
                }
           }
       });
    }

    function validateSurveyCategoryForm(){
    
        
        var valid = true;
        
        var survey_typeObj      = $('#form_survey_category #survey_type');
        var category_codeObj    = $('#form_survey_category #category_code');
        var category_nameObj    = $('#form_survey_category #category_name');
        var category_orderObj   = $('#form_survey_category #category_order');

        if (survey_typeObj.val() == "0"){
            survey_typeObj.parents('div .col-sm-12').addClass('has-error')
            survey_typeObj.parents('div .col-sm-7').append('<span class="help-block">Please choose a survey type.</span>');
            valid =false;
        }
        if (category_codeObj.val() == ""){
            category_codeObj.parents('div .col-sm-12').addClass('has-error')
            category_codeObj.parents('div .col-sm-7').append('<span class="help-block">Survey Head code is required.</span>');
            valid =false;
        }
        if (category_nameObj.val() == ""){
            category_nameObj.parents('div .col-sm-12').addClass('has-error')
            category_nameObj.parents('div .col-sm-7').append('<span class="help-block">Survey Head name is required.</span>');
            valid =false;
        }
        if (category_orderObj.val() == 0){
            category_orderObj.parents('div .col-sm-12').addClass('has-error')
            category_orderObj.parents('div .col-sm-7').append('<span class="help-block">Please choose a Survey Head order.</span>');
            valid =false;
        }
        
        return valid;
    }
    

    var action = '';
    
    jQuery(document).ready(function () {

        

        var survey_type = $('#survey_category_form #survey_type_filter').val();

        $('#survey_category_table').datagrid({
            url: livesite + "Survey/getSurveyCategory",
            pagination: true,
            singleSelect: true,
            rownumbers: true,
            queryParams: {
                survey_type: survey_type
            },
            toolbar: [{
                    text: 'New',
                    iconCls: 'icon-add',
                    handler: function () {
                        action = 'add';
                            showLargeModalForm(livesite + 'Survey/surveyCategoryForm')
                        
                    }
                },
                {
                    iconCls: 'icon-edit',
                    text: 'Edit',
                    handler: function () {
                        action = 'edit';
                        var row = $('#survey_category_table').datagrid('getSelected');
                        if (row) {

                            showLargeModalForm(livesite + 'Survey/surveyCategoryForm/' + row.category_pkey,'getCategoryOrder()')  
                        
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
                        var rows = $('#survey_category_table').datagrid('getSelected');
                        if (rows){
                            var category_pkey = rows.category_pkey;
                            if (confirm("Are you sure want to delete ")) {
                                $.ajax({
                                    url:  livesite+"Survey/deleteSurveyCategory",
                                    data : {
                                    ids : category_pkey
                                    },
                                    success : function(response) {
                                        var response =  $.parseJSON(response);
                                        if(response.msg){
                                            $.notify(response.msg,{
                                                type: 'success',
                                                allow_dismiss: true
                                            });
                                        }
                                        reloadTable('survey_category_table')
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
                    {field: 'category_code', title: 'Survey Head Code', width: "40%"},
                    {field: 'category_name', title: 'Survey Head Name', width: "40%"},
                    {field: 'category_order', title: 'Survey Head Order', width: "20%"},
                    
                ]]
        });

    });
</script>