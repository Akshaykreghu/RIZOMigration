<style>
    .form-horizontal .control-label{

        text-align: left;

    }
</style>

<section class="content-header">
    <h1 style="text-align:left; font-size: 3em;"> Survey Type Master   </h1>
</section>
<!-- Main content -->
<section class="content">
    <div class="row">
        <div class="col-md-12">
            <div class="box ">
                <br>
                <div class="box-body">
                    <div class="box-body">
                        <table id="survey_table" class="table table-bordered table-hover">
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
    function validateSurveyForm(){
    
        
        var valid = true;

        var type_codeObj = $('#type_code');
        var type_nameObj = $('#type_name');
        var equipmentObj = $('#equipment_type_fkey');
        

        if (type_codeObj.val() == ""){
            type_codeObj.parents('div .col-sm-12').addClass('has-error')
            type_codeObj.parents('div .col-sm-7').append('<span class="help-block">Please enter survey type code.</span>');
            valid =false;
        }
        if (type_nameObj.val() == ""){
            type_nameObj.parents('div .col-sm-12').addClass('has-error')
            type_nameObj.parents('div .col-sm-7').append('<span class="help-block">Please enter survey type name.</span>');
            valid =false;
        }
        if (equipmentObj.val() == ""){
            equipmentObj.parents('div .col-sm-12').addClass('has-error')
            equipmentObj.parents('div .col-sm-7').append('<span class="help-block">Please choose a equipment type.</span>');
            valid =false;
        }
        return valid;
    }
    jQuery(document).ready(function () {

        $('#survey_table').datagrid({
            url: livesite + "Survey/surveyTypesList",
            pagination: true,
            singleSelect: true,
            rownumbers: true,
            toolbar: [{
                    text: 'New',
                    iconCls: 'icon-add',
                    handler: function () {
                            showLargeModalForm(livesite + 'Survey/form')
                        
                    }
                },
                {
                    iconCls: 'icon-edit',
                    text: 'Edit',
                    handler: function () {
                        var row = $('#survey_table').datagrid('getSelected');
                        if (row) {

                            showLargeModalForm(livesite + 'Survey/form/' + row.type_pkey)  
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
                var rows = $('#survey_table').datagrid('getSelected');
                if (rows){
                    var type_pkey = rows.type_pkey;
                    if (confirm("Are you sure want to delete ")) {
                        $.ajax({
                            url:  livesite+"Survey/deleteSurveyType",
                            data : {
                            ids : type_pkey
                            },
                            success : function(response) {
                                var response =  $.parseJSON(response);
                                if(response.msg){
                                    $.notify(response.msg,{
                                        type: 'success',
                                        allow_dismiss: true
                                    });
                                }
                                reloadTable('survey_table')
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
                    {field: 'type_code', title: 'Survey Type Code', width: "30%"},
                    {field: 'type_name', title: 'Survey Type Name', width: "40%"},
                    {field: 'equipment_name', title: 'Equipment Type', width: "40%"},
                ]]
        });
    });
</script>