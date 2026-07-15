

<style>

    @keyframes FadeIn {
  from {
    background-color: #7bebbd;
  }
  
  to {
    background-color: white;
  }
}
.block span{
    display: block;
    float: left;
    margin-bottom: 4px;
    width: 33.333%;
  }
.iframeCls {
    background-image: url("https://mpm.office24.online/img/updateimg.gif");   
    background-repeat: no-repeat;
    background-position: 50% 50%;
}
.hide{
    display:none !important;
}
</style>

<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery.form/4.3.0/jquery.form.min.js"></script>


<script>

    /* var editor = CKEDITOR.replace( 'document_editor', {
        customConfig: 'document_config.js',
        extraAllowedContent: 'a span',
        allowedContent:true,
    } ); */
	var editor = CKEDITOR.replace('document_editor', {
	  //customConfig: 'config.js',
	  extraPlugins: 'uploadimage,image2',
	  extraAllowedContent: 'a span',
      allowedContent:true,
	  toolbarGroups: [{
          "name": "document",
          "groups": ["Preview","mode"]
        },
		{
          "name": "clipboard",
          "groups": ["PasteText","PasteFromWord","Undo","Redo"]
        },
		{
          "name": "editing",
          "groups": ["Find","Replace","SelectAll","Scayt"]
        },
		{
          "name": "undo",
          "groups": ["undo"]
        },
		{
          "name": "forms",
          "groups": ["TextField","Textarea"]
        },
		{
          "name": "basicstyles",
          "groups": ["basicstyles"]
        },
        {
          "name": "links",
          "groups": ["Link","Unlink"]
        },
		{
          "name": "justify",
          "groups": ["JustifyLeft","JustifyCenter","JustifyRight","JustifyBlock"]
        },
        {
          "name": "paragraph",
          "groups": ["list", "blocks","NumberedList","BulletedList","Outdent","Indent","Blockquote","BidiLtr","BidiRtl"]
        },
        {
          "name": "insert",
          "groups": ["insert"]
        },
        {
          "name": "styles",
          "groups": ["styles"]
        },
		{
          "name": "colors",
          "groups": ["colors"]
        },
		{
          "name": "tools",
          "groups": ["tools"]
        },
        {
          "name": "about",
          "groups": ["about"]
        }
      ],
	  removeButtons: 'Save,Source,NewPage,Print,Cut,Copy,Paste,Form,Checkbox,Radio,Select,Button,HiddenField,Iframe,Flash,CreateDiv,Language,Anchor,ImageButton',
      removePlugins: 'about,jsplusTranslator,image',
    });
	CKEDITOR.config.allowedContent = true;
  /*   editor.on('blur', function(evt){
        CK_TEMPLATE = editor.getData();
    });
    editor.on('focus', function(evt){
        editor.setData(CK_TEMPLATE);
    }); */
    // var options = {
    //     success: function (resp) {
    //         $('#largeModalForm').modal('hide');
    //         $('#documents_table').datagrid('reload');
    //         $.notify("Success", {
    //             type: 'success',
    //             allow_dismiss: false
    //         });
    //     }  
    // };
    var options = {
    url: "<?php echo $this->webroot; ?>DocumentManagers/saveDocument",
    type: "POST",
    dataType: "json",
  success: function (resp) {
            $('#largeModalForm').modal('hide');
            $('#documents_table').datagrid('reload');
            $.notify("Success", {
                type: 'success',
                allow_dismiss: false
            });
        },
    error: function (xhr, status, error) {
        alert("Error: " + error);
    }
};

    $('#document_form').on('submit', function (event) {
        event.preventDefault();
        $('.help-block').remove();
        $('.has-error').removeClass('has-error');
        var templateEditor = CKEDITOR.instances["document_editor"];
        if (!templateEditor) {
            alert("Editor not ready. Please wait and try again.");
            return;
        }
        var template = templateEditor.getData();
        if (!template || template.trim() === '') {
            alert("Document content is empty. Please select a template and fill in the details before saving.");
            return;
        }
        $('#document_content').val(template);
        if (confirm("Do You Want To Save The Form")) {
            $('#document_form').ajaxSubmit(options);
        }
    });
	$('#document_form #template').on('change', function (e) {
                 var key =  $(this).val();                           
          $.ajax({
                url:  livesite+"DocumentManagers/getData/"+key,
                type:'POST',
                data : {
                    key : key
                },
                success : function(response) {
                    var res = JSON.parse(response);
					 
                    if(res && res.status == true){
                       editor.setReadOnly(false);
					   $('#img').show();
                    }else{
						editor.setReadOnly(true);
						$('#img').hide();
					}
                }
            });
    });
</script>
<div class="modal-dialog" style="width: 100%; ">

    <!-- Modal content-->
    <div class="modal-content">
        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal">&times;</button>
            <h4 class="modal-title" style="    margin: 5px 28px 0; "><b>Document Generator</b> </h4>
        </div>
        <div class="modal-body">
            <!-- Form starts -->
            <div id="empsetup-save-response" class="">
                <form class="form-horizontal"  method="post" id="document_form" name="document_form">
                    <div class="modal-body"> 
					<div class="col-sm-9">	
                        <div class="form-group form-group-sm">
                            <div class="col-sm-12">
                                <label class="col-sm-4 control-label" >Document<label style="color:red">*</label></label>
                                <div class="col-sm-8">
                                    <select  id="template" name="template" class="form-control js-example-basic-single" onchange="loadPlaceholders(this)">
                                            <option   value="">Choose Document</option>
                                            <?php foreach ($templates as $key => $value) {
                                            ?>                              
                                                <option value="<?php echo $value['doc_template']['template_pkey']; ?>">
                                                    <?php echo  $value['doc_template']['template_name']; ?>
                                                </option>
                                            <?php 
                                                } 
                                            ?>
                                        </select>                                    
                                </div>
                            </div>
                        </div>
                        <div class="form-group form-group-sm hide placeholdeDropDown" id="emp" >
                            <div class="col-sm-12">
                                <label class="col-sm-4 control-label" >Employee<label style="color:red">*</label></label>
                                <div class="col-sm-8">
                                   <select  id="employee" name="employee" class="form-control js-example-basic-single" onchange="documentPreview(this)">
                                            <option   value="">Choose Employee</option>
                                    </select>      
                                </div>
                            </div>
                        </div>
                        <div class="form-group form-group-sm hide placeholdeDropDown" id="emp_join" >
                            <div class="col-sm-12">
                                <label class="col-sm-4 control-label" >Employee Join<label style="color:red">*</label></label>
                                <div class="col-sm-8">
                                   <select  id="employee_join" name="employee_join" class="form-control js-example-basic-single" onchange="documentPreview(this)">
                                            <option   value="">Choose Employee Join</option>
                                    </select>      
                                </div>
                            </div>
                        </div>
                        <div class="form-group form-group-sm hide placeholdeDropDown" id="comp_contact">
                            <div class="col-sm-12">
                                <label class="col-sm-4 control-label" >Company<label style="color:red">*</label></label>
                                <div class="col-sm-8">
                                   <select  id="company" name="company" class="form-control js-example-basic-single" onchange="documentPreview(this)">
                                            <option   value="">Choose Company</option>
                                    </select>      
                                </div>
                            </div>
                        </div>
                        <div class="form-group form-group-sm hide placeholdeDropDown" id="branch">
                            <div class="col-sm-12">
                                <label class="col-sm-4 control-label" >Branches<label style="color:red">*</label></label>
                                <div class="col-sm-8">
                                   <select  id="branches" name="branches" class="form-control js-example-basic-single" onchange="documentPreview(this)">
                                            <option   value="">Choose Branch</option>
                                    </select>      
                                </div>
                            </div>
                        </div>
						</div>
						<div class="col-sm-3" id="img" style="display:none;">	
						<input onclick="addimage()" class="btn btn-primary" value="Add Image" readonly >
						</div>
                       <!-- <div class="form-group form-group-sm hide placeholdeDropDown" id="supplier">
                            <div class="col-sm-12">
                                <label class="col-sm-4 control-label" >Suppliers<label style="color:red">*</label></label>
                                <div class="col-sm-8">
                                   <select  id="suppliers" name="suppliers" class="form-control js-example-basic-single" onchange="documentPreview(this)">
                                            <option   value="">Choose Supplier</option>
                                    </select>      
                                </div>
                            </div>
                        </div>
                        <div class="form-group form-group-sm hide placeholdeDropDown" id="customer">
                            <div class="col-sm-12">
                                <label class="col-sm-4 control-label" >Customers<label style="color:red">*</label></label>
                                <div class="col-sm-8">
                                   <select  id="customers" name="customers" class="form-control js-example-basic-single" onchange="documentPreview(this)">
                                            <option   value="">Choose Customer</option>
                                    </select>      
                                </div>
                            </div>
                        </div>
                        <div class="form-group form-group-sm hide placeholdeDropDown" id="other">
                            <div class="col-sm-12">
                                <label class="col-sm-4 control-label" >Others<label style="color:red">*</label></label>
                                <div class="col-sm-8">
                                   <select  id="others" name="others" class="form-control js-example-basic-single" onchange="documentPreview(this)">
                                            <option   value="">Choose Other contact</option>
                                    </select>      
                                </div>
                            </div>
                        </div> */
                        
                        <!-- <div class="form-group form-group-sm">
                            <div class="col-sm-12">
                                <label class="col-sm-4 control-label" >Document Heading<label style="color:red">*</label></label>
                                <div class="col-sm-8">
                                    <input class="form-control" placeholder="Please Enter Document Heading" type="text" name="document_name" id="document_name" >
                                </div>
                            </div>
                        </div> -->
                        <div class="form-group form-group-sm">
                            <div class="col-sm-12">
							 <div class="col-sm-12">
                                <!-- <label class="col-sm-2 control-label" >Document Preview<label style="color:red">*</label></label>
                                <div class="col-sm-10"> -->
                                     <!-- <iframe id="document" name="document" width="100%" height="600">
                                    </iframe> -->
                                    <textarea name="document_editor" id="document_editor" rows="10" cols="80" >
                                    <?php echo isset($document_editor) ? $document_editor : ""; ?>
                                    </textarea>
                                <!-- </div> -->
								</div>
                            </div>
                        </div>
                        
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-danger" data-dismiss="modal">Cancel</button>
                        <button type="submit" id="btn-submit" class="btn btn-primary">Save</button>
                    </div>
					 <input  type="hidden"  name="document_content" id="document_content" >
                </form>
            </div>
        </div>
    </div>
</div>
<script>
 function addimage(){
     showSmallModalForm(livesite + 'DocumentManagers/addimage/');
 }
  
</script>  