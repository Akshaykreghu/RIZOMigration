<style>
    .form-horizontal .control-label{

        text-align: left;

    }

    /* Edited by bindu 24-10-2025 */
   	.heading {
		display: flex;
		flex-direction: row;
		align-items: end;
		justify-content: space-between;
	}

    .home {
        background-color: #ffffffff;
        border-radius: 50px;
        padding: 2px 15px;
        color: #1e516e !important;
        /* margin-right: 15px; */
        color: white;
        font-weight: 500;
        text-decoration: none;
        transition: all 0.3s ease;
        cursor: pointer;
        border: #1e516e 1px solid;
    }

    /* End */
</style>
<script src="<?php echo $this->webroot;?>plugins/ckeditor4/ckeditor4/ckeditor.js" type="text/javascript"></script>
  <!--<script src="https://cdn.ckeditor.com/4.16.0/standard-all/ckeditor.js"></script> 
<script src="<?php echo $this->webroot;?>plugins/ckeditor4/ckfinder/ckfinder.js" type="text/javascript"></script>
<script src="<?php echo $this->webroot;?>plugins/ckeditor1/ckeditor.js" type="text/javascript"></script>-->

<!-- // edited by bindu 30-08-25 -->
<section class="content-header heading">
    
     
    <h1 class="text-primary-18"> Document Master </h1>
    <div class="text-primary-16 home" style="display:flex; align-items:center; gap:10px; cursor:pointer;">
        <i class="fa" style="font-size:16px;">&#xf104;</i>
        Back
    </div>


</section>
    <!-- end -->
<!-- Main content -->
<section class="content">
    <div class="row">
        <div class="col-md-12">
            <div class="box ">
                <br>
                <div class="box-body">
                    <div class="box-body">
                        <table id="template_table" class="table table-bordered table-hover">
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
    var usedPlacehodersKey = [];
    function validateTemplateForm(){
    
        
        var valid = true;
        
        var templateEditor = CKEDITOR.instances["template_editor"];
        var template_nameObj    = $('#template_form #template_name');
        var template_editorObj = $('#template_form #template_editor');

        if (template_nameObj.val() == ""){
            template_nameObj.parents('div .col-sm-12').addClass('has-error')
            template_nameObj.parents('div .col-sm-7').append('<span class="help-block">Please enter the document name.</span>');
            valid =false;
        }
        
        if (templateEditor.getData() == ""){
            template_editorObj.parents('div .col-sm-12').addClass('has-error')
            template_editorObj.parents('div .col-sm-7').append('<span class="help-block">Please choose a data type.</span>');
            valid =false;
        }
        
        return valid;
    }

    jQuery(document).ready(function () {

        $('#template_table').datagrid({
            url: livesite + "DocumentManagers/getTemplates",
            pagination: true,
            singleSelect: true,
            rownumbers: true,
			PostsearchFilter:true,
            toolbar: [{
                    text: 'New',
                    iconCls: 'icon-add',
                    handler: function () {
                            showLargeModalForm(livesite + 'DocumentManagers/form')
                        
                    }
                },
                {
                    iconCls: 'icon-edit',
                    text: 'Edit',
                    handler: function () {
                        var row = $('#template_table').datagrid('getSelected');
                        if (row) {

                            showLargeModalForm(livesite + 'DocumentManagers/form/' + row.template_pkey)  
                        } else
                            $.notify('Please Select a record to return', {
                                type: 'danger',
                                allow_dismiss: false
                            });
                    }
                },'-',
                {
            iconCls: 'icon-remove',
            text:'Delete',
            handler: function(){
                var rows = $('#template_table').datagrid('getSelected');
                if (rows){
                    var template_id = rows.template_pkey;
                    if (confirm("Are you sure want to delete ")) {
                        $.ajax({
                            url:  livesite+"DocumentManagers/deleteTemplate",
                            data : {
                            ids : template_id
                            },
                            success : function(response) {
                                var response =  $.parseJSON(response);
                                if(response.msg){
                                    $.notify(response.msg,{
                                        type: 'success',
                                        allow_dismiss: true
                                    });
                                }
                                reloadTable('template_table')
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

                    {field: 'template_name', title: 'Document Name', width: "39%"},
					{field: 'created_by', title: 'Created By', width: "15%"},
					{field: 'creation_date', title: 'Created Date', width: "15%"},
					{field: 'modified_by', title: 'Modified By', width: "15%"},
					{field: 'modification_date', title: 'Modified Date', width: "15%"}
                    //{field: 'type_name', title: 'Survey Type Name', width: "50%"},
                ]],
				 onSearch:function(s){
                    $('#template_table').datagrid('load',{
                        emp: $('#searchqupo').val()
                    });
              }
        });
    });
    function addPlaceholder($key,$type){
        usedPlacehodersKey.push($type);
        CKEDITOR.instances['template_editor'].insertText($key);
		//e.editor.dataProcessor.writer.indentationChars = "";
//e.editor.dataProcessor.writer.lineBreakChars = "";
    }
/* edited by bindu 19-02-26 */
   $(".home").on("click", function () {

    $("#container").isLoading({
        text: "Loading",
        position: "overlay",
    });

    let url = "";
    var userGroup = <?php echo json_encode($this->Session->read('user_group')); ?>

    if (userGroup == "1") {
        url = livesite + "CompanySetup/index";
    } 
    else if (userGroup == "2") {
        url = livesite + "EmployeeMenu/addon";
    }

    $("#container").load(url, function () {
        isDashboardShown = false;
    });

});

	/* edited by bindu 19-02-26 */

</script>