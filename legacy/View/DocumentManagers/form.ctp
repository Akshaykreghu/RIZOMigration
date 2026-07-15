<style>
    @keyframes FadeIn {
        from {
            background-color: #7bebbd;
        }

        to {
            background-color: white;
        }
    }

    .block {
        overflow: hidden;
        width: 100% !important;
        height: 33.33% !important;
    }

    .block label {
        display: block;
        float: left;
        margin-bottom: 4px;
        width: 33.333% !important;
    }

    .copy_placeholder {
        cursor: copy;
    }
</style>


<script>
    editor = CKEDITOR.replace('template_editor', {
        //customConfig: 'config.js',
        extraPlugins: 'uploadimage,image2,templates',
        extraAllowedContent: 'a span',
        allowedContent: true,
        toolbarGroups: [{
                "name": "document",
                "groups": ["Preview", "mode", "Templates"]
            },
            {
                "name": "clipboard",
                "groups": ["PasteText", "PasteFromWord", "Undo", "Redo"]
            },
            {
                "name": "editing",
                "groups": ["Find", "Replace", "SelectAll", "Scayt"]
            },
            {
                "name": "undo",
                "groups": ["undo"]
            },
            {
                "name": "forms",
                "groups": ["TextField", "Textarea"]
            },
            {
                "name": "basicstyles",
                "groups": ["basicstyles"]
            },
            {
                "name": "links",
                "groups": ["Link", "Unlink"]
            },
            {
                "name": "paragraph",
                "groups": ["list", "blocks", "NumberedList", "BulletedList", "Outdent", "Indent", "Blockquote", "BidiLtr", "BidiRtl"]
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
            }
        ],
        removeButtons: 'Source,NewPage,Print,Cut,Copy,Paste,Form,Checkbox,Radio,Select,Button,HiddenField,Iframe,Flash,CreateDiv,Language,Anchor,ImageButton',
        removePlugins: 'about,jsplusTranslator,image',
        //filebrowserImageBrowseUrl: '/plugins/ckfinder/ckfinder.html?type=Images',
        //filebrowserImageUploadUrl: '/plugins/ckfinder/core/connector/php/connector.php?command=QuickUpload&type=Images',
    });
    CKEDITOR.config.allowedContent = true;

    /* var editor =  CKEDITOR.replace( 'template_editor', {
            customConfig: 'document_config.js',
    		extraPlugins: 'uploadimage,image2',
            extraAllowedContent: 'a span',
            allowedContent:true,
    		//toolbarLocation: 'bottom',
          // Remove some plugins that would conflict with the bottom
          // toolbar position. 
          //removePlugins: 'about,Underline,JustifyCenter,Source,Save,document',
    	  removeButtons: 'Save,Source,NewPage,Print,Templates,Cut,Copy,Paste,Form,Checkbox,Radio,Select,Button,HiddenField,Iframe,Flash,CreateDiv,Language,Anchor,ImageButton',
          removePlugins: 'about,jsplusTranslator',

          // Configure your file manager integration. This example uses CKFinder 3 for PHP.
          filebrowserBrowseUrl: '/plugins/ckeditor4/ckeditor4/plugins/ckfinder/ckfinder.html',
          filebrowserImageBrowseUrl: '/plugins/ckeditor4/ckeditor4/plugins/ckfinder/ckfinder.html?type=Images',
          filebrowserUploadUrl: '/plugins/ckeditor4/ckeditor4/plugins/ckfinder/core/connector/php/connector.php?command=QuickUpload&type=Files',
          filebrowserImageUploadUrl: '/plugins/ckeditor4/ckeditor4/plugins/ckfinder/core/connector/php/connector.php?command=QuickUpload&type=Images',
    	  // Upload dropped or pasted images to the CKFinder connector (note that the response type is set to JSON).
          uploadUrl: '/plugins/ckeditor4/ckeditor4/plugins/ckfinder/core/connector/php/connector.php?command=QuickUpload&type=Files&responseType=json',

          // Reduce the list of block elements listed in the Format drop-down to the most commonly used.
          format_tags: 'p;h1;h2;h3;pre',
          // Simplify the Image and Link dialog windows. The "Advanced" tab is not needed in most cases.
          removeDialogTabs: 'image:advanced;link:advanced',
    } );
       CKFinder.setupCKEditor( editor ); */
    var options = {
        success: function(resp) {
            $('#largeModalForm').modal('hide');
            $('#template_table').datagrid('reload');
            $.notify("Success", {
                type: 'success',
                allow_dismiss: false
            });
        }
    };
    $('#template_form').on('submit', function(event) {
        event.preventDefault();
        $('.help-block').remove();
        $('.has-error').removeClass('has-error');
        if (validateTemplateForm()) {
            $('#placeholders').val([...new Set(usedPlacehodersKey)].toString());
            var templateEditor = CKEDITOR.instances["template_editor"];
            var template = templateEditor.getData();
            $('#template_content').val(template);
            if (confirm("Do You Want To Save The Form")) {
                $('#template_form').ajaxSubmit(options);
            }
        }

    });



    var placeholders = [];
    placeholders[''] = '';
    //edited by ANUKRISHNAN on 14-01-25
    placeholders['emp'] = ' <div style="border: 1px solid #dfdfdf;padding:10px;"> <label class="copy_placeholder badge" onclick="addPlaceholder(\'{:empolyee_name}\', \'emp\')">Employee Name</label>&nbsp;&nbsp;&nbsp;&nbsp;\
                            <label class="copy_placeholder badge" onclick="addPlaceholder(\'{:empolyee_image}\', \'emp\')">Profile Image</label>&nbsp;&nbsp;&nbsp;&nbsp;\
                            <label class="copy_placeholder badge" onclick="addPlaceholder(\'{:employee_currentdate}\', \'emp\')">Current Date</label>&nbsp;&nbsp;&nbsp;&nbsp;\
                            <label class="copy_placeholder badge" onclick="addPlaceholder(\'{:employee_id}\', \'emp\') " >Employee Id</label>&nbsp;&nbsp;&nbsp;&nbsp;\
                            <label class="copy_placeholder badge" onclick="addPlaceholder(\'{:blood_group}\', \'emp\') " >Blood Group</label>&nbsp;&nbsp;&nbsp;&nbsp;\
                            <label class="copy_placeholder badge" onclick="addPlaceholder(\'{:issued_date}\', \'emp\') " >Issued Date</label>&nbsp;&nbsp;&nbsp;&nbsp;\
							<label class="copy_placeholder badge" onclick="addPlaceholder(\'{:employee_emp_type}\', \'emp\') " >Employee Type</label>&nbsp;&nbsp;&nbsp;&nbsp;\
                            <label class="copy_placeholder badge" onclick="addPlaceholder(\'{:employee_designation}\', \'emp\')"  >Designation</label>&nbsp;&nbsp;&nbsp;&nbsp;\
                            <label class="copy_placeholder badge" onclick="addPlaceholder(\'{:employee_department}\', \'emp\')">Department</label>&nbsp;&nbsp;&nbsp;&nbsp;\
                            <label class="copy_placeholder badge" onclick="addPlaceholder(\'{:employee_branch}\', \'emp\')">Branch</label>&nbsp;&nbsp;&nbsp;&nbsp;\
							<label class="copy_placeholder badge" onclick="addPlaceholder(\'{:employee_branch_address}\', \'emp\')">Branch Address</label>&nbsp;&nbsp;&nbsp;&nbsp;\
                            <label class="copy_placeholder badge" onclick="addPlaceholder(\'{:employee_joining_date}\', \'emp\')">Joining Date</label>&nbsp;&nbsp;&nbsp;&nbsp;\
							<label class="copy_placeholder badge" onclick="addPlaceholder(\'{:employee_notice_days}\', \'emp\')">Notice Period</label>&nbsp;&nbsp;&nbsp;&nbsp;\
							<label class="copy_placeholder badge" onclick="addPlaceholder(\'{:employee_address}\', \'emp\')">Address</label>&nbsp;&nbsp;&nbsp;&nbsp;\
							<label class="copy_placeholder badge" onclick="addPlaceholder(\'{:employee_city}\', \'emp\')">City</label>&nbsp;&nbsp;&nbsp;&nbsp;\
							<label class="copy_placeholder badge" onclick="addPlaceholder(\'{:employee_state}\', \'emp\')">State</label>&nbsp;&nbsp;&nbsp;&nbsp;\
							<label class="copy_placeholder badge" onclick="addPlaceholder(\'{:employee_pincode}\', \'emp\')">Pincode</label>&nbsp;&nbsp;&nbsp;&nbsp;\
							<label class="copy_placeholder badge" onclick="addPlaceholder(\'{:employee_mobile_no}\', \'emp\')">Mobile Number</label>&nbsp;&nbsp;&nbsp;&nbsp;\
							<label class="copy_placeholder badge" onclick="addPlaceholder(\'{:employee_email}\', \'emp\')">Email</label>&nbsp;&nbsp;&nbsp;&nbsp;\
							<label class="copy_placeholder badge" onclick="addPlaceholder(\'{:employee_gender}\', \'emp\')">Gender</label>&nbsp;&nbsp;&nbsp;&nbsp;\
							<label class="copy_placeholder badge" onclick="addPlaceholder(\'{:employee_maritual_status}\', \'emp\')">Marital Status</label>&nbsp;&nbsp;&nbsp;&nbsp;\
                                                        <label class="copy_placeholder badge" onclick="addPlaceholder(\'{:employee_guardian}\', \'emp\')">Guardian</label>&nbsp;&nbsp;&nbsp;&nbsp;\
							<label class="copy_placeholder badge" onclick="addPlaceholder(\'{:employee_education}\', \'emp\')">Education</label>&nbsp;&nbsp;&nbsp;&nbsp;\
							<label class="copy_placeholder badge" onclick="addPlaceholder(\'{:employee_date_of_birth}\', \'emp\')">DOB</label>&nbsp;&nbsp;&nbsp;&nbsp;\
							<label class="copy_placeholder badge" onclick="addPlaceholder(\'{:employee_bank_name}\', \'emp\')">Bank Name</label>&nbsp;&nbsp;&nbsp;&nbsp;\
							<label class="copy_placeholder badge" onclick="addPlaceholder(\'{:employee_branch_name}\', \'emp\')">Bank Branch </label>&nbsp;&nbsp;&nbsp;&nbsp;\
							<label class="copy_placeholder badge" onclick="addPlaceholder(\'{:employee_bank_address}\', \'emp\')">Bank Address</label>&nbsp;&nbsp;&nbsp;&nbsp;\
							<label class="copy_placeholder badge" onclick="addPlaceholder(\'{:employee_ifsc_code}\', \'emp\')">IFSC Code</label>&nbsp;&nbsp;&nbsp;&nbsp;\
							<label class="copy_placeholder badge" onclick="addPlaceholder(\'{:employee_account_no}\', \'emp\')">Account Number</label>&nbsp;&nbsp;&nbsp;&nbsp;\
							<label class="copy_placeholder badge" onclick="addPlaceholder(\'{:employee_ctc}\', \'emp\')">CTC</label>&nbsp;&nbsp;&nbsp;&nbsp;\
                                                        <label class="copy_placeholder badge" onclick="addPlaceholder(\'{:employee_yearly_ctc}\', \'emp\')">Yearly CTC</label>&nbsp;&nbsp;&nbsp;&nbsp;\
                                                        <label class="copy_placeholder badge" onclick="addPlaceholder(\'{:text_box}\', \'emp\')">Text Box</label>&nbsp;&nbsp;&nbsp;&nbsp;\
                                                        <label class="copy_placeholder badge" onclick="addPlaceholder(\'{:monthly_ctc}\', \'emp\')">Monthly CTC</label>&nbsp;&nbsp;&nbsp;&nbsp;\
                                                        <label class="copy_placeholder badge" onclick="addPlaceholder(\'{:employee_duration}\', \'emp\')">Probation Days</label>&nbsp;&nbsp;&nbsp;&nbsp;\
                                                        <label class="copy_placeholder badge" onclick="addPlaceholder(\'{:salary_break_up}\', \'emp\')">Salary Break Up</label></div>';
    // End
    // edited by bindu 24-10-2025

    placeholders['emp_join'] = '<div style="border: 1px solid #dfdfdf; padding:10px;"> \
    <label class="copy_placeholder badge" onclick="addPlaceholder(\'{:employee_join_name}\', \'emp_join\')">Employee Name</label>&nbsp;&nbsp;&nbsp;&nbsp; \
    <label class="copy_placeholder badge" onclick="addPlaceholder(\'{:employee_join_avatar}\', \'emp_join\')">Profile Image</label>&nbsp;&nbsp;&nbsp;&nbsp; \
    <label class="copy_placeholder badge" onclick="addPlaceholder(\'{:employee_join_mobile_no}\', \'emp_join\')">Mobile Number</label>&nbsp;&nbsp;&nbsp;&nbsp; \
        <label class="copy_placeholder badge" onclick="addPlaceholder(\'{:employee_join_nationality}\', \'emp_join\')">Nationality</label>&nbsp;&nbsp;&nbsp;&nbsp; \
    <label class="copy_placeholder badge" onclick="addPlaceholder(\'{:employee_join_date_of_birth}\', \'emp_join\')">Date of Birth</label>&nbsp;&nbsp;&nbsp;&nbsp; \
    <label class="copy_placeholder badge" onclick="addPlaceholder(\'{:employee_join_email}\', \'emp_join\')">Email</label>&nbsp;&nbsp;&nbsp;&nbsp; \
    <label class="copy_placeholder badge" onclick="addPlaceholder(\'{:employee_join_address}\', \'emp_join\')">Address</label>&nbsp;&nbsp;&nbsp;&nbsp; \
    <label class="copy_placeholder badge" onclick="addPlaceholder(\'{:employee_join_pincode}\', \'emp_join\')">Pincode</label>&nbsp;&nbsp;&nbsp;&nbsp; \
    <label class="copy_placeholder badge" onclick="addPlaceholder(\'{:employee_join_district}\', \'emp_join\')">District</label>&nbsp;&nbsp;&nbsp;&nbsp; \
    <label class="copy_placeholder badge" onclick="addPlaceholder(\'{:employee_join_guardian}\', \'emp_join\')">Guardian</label>&nbsp;&nbsp;&nbsp;&nbsp; \
    <label class="copy_placeholder badge" onclick="addPlaceholder(\'{:employee_join_relation_guardian}\', \'emp_join\')">Relation with guardian</label>&nbsp;&nbsp;&nbsp;&nbsp; \
    <label class="copy_placeholder badge" onclick="addPlaceholder(\'{:employee_join_gender}\', \'emp_join\')">Gender</label>&nbsp;&nbsp;&nbsp;&nbsp; \
    <label class="copy_placeholder badge" onclick="addPlaceholder(\'{:employee_join_state}\', \'emp_join\')">State</label>&nbsp;&nbsp;&nbsp;&nbsp; \
    <label class="copy_placeholder badge" onclick="addPlaceholder(\'{:employee_join_bank_name}\', \'emp_join\')">Bank Name</label>&nbsp;&nbsp;&nbsp;&nbsp; \
    <label class="copy_placeholder badge" onclick="addPlaceholder(\'{:employee_join_branch_name}\', \'emp_join\')">Bank Branch Name</label>&nbsp;&nbsp;&nbsp;&nbsp; \
    <label class="copy_placeholder badge" onclick="addPlaceholder(\'{:employee_join_ifsc_code}\', \'emp_join\')">IFSC Code</label>&nbsp;&nbsp;&nbsp;&nbsp; \
    <label class="copy_placeholder badge" onclick="addPlaceholder(\'{:employee_join_account_no}\', \'emp_join\')">Account Number</label>&nbsp;&nbsp;&nbsp;&nbsp; \
    <label class="copy_placeholder badge" onclick="addPlaceholder(\'{:employee_join_pf}\', \'emp_join\')">PF</label>&nbsp;&nbsp;&nbsp;&nbsp; \
    <label class="copy_placeholder badge" onclick="addPlaceholder(\'{:employee_join_previous_member}\', \'emp_join\')">Previous Member ID</label>&nbsp;&nbsp;&nbsp;&nbsp; \
    <label class="copy_placeholder badge" onclick="addPlaceholder(\'{:employee_join_esi_dispensary}\', \'emp_join\')">ESI Dispensary</label>&nbsp;&nbsp;&nbsp;&nbsp; \
    <label class="copy_placeholder badge" onclick="addPlaceholder(\'{:employee_join_esi}\', \'emp_join\')">ESI</label>&nbsp;&nbsp;&nbsp;&nbsp; \
    <label class="copy_placeholder badge" onclick="addPlaceholder(\'{:employee_join_pan_no}\', \'emp_join\')">PAN Number</label>&nbsp;&nbsp;&nbsp;&nbsp; \
    <label class="copy_placeholder badge" onclick="addPlaceholder(\'{:employee_join_uan}\', \'emp_join\')">UAN Number</label>&nbsp;&nbsp;&nbsp;&nbsp; \
            <label class="copy_placeholder badge" onclick="addPlaceholder(\'{:employee_join_maritual_status}\', \'emp_join\')">Maritual Status</label>&nbsp;&nbsp;&nbsp;&nbsp; \
                <label class="copy_placeholder badge" onclick="addPlaceholder(\'{:employee_join_wps_code}\', \'emp_join\')">WPS Number</label>&nbsp;&nbsp;&nbsp;&nbsp; \
                    <label class="copy_placeholder badge" onclick="addPlaceholder(\'{:employee_join_lwf_code}\', \'emp_join\')">LWF Number</label>&nbsp;&nbsp;&nbsp;&nbsp; \
                    <label class="copy_placeholder badge" onclick="addPlaceholder(\'{:employee_join_blood_group}\', \'emp_join\') " >Blood Group</label>&nbsp;&nbsp;&nbsp;&nbsp;\
                            <label class="copy_placeholder badge" onclick="addPlaceholder(\'{:employee_join_issued_date}\', \'emp_join\') " >Issued Date</label>&nbsp;&nbsp;&nbsp;&nbsp;\
                             <label class="copy_placeholder badge" onclick="addPlaceholder(\'{:text_box}\', \'emp_join\')">Text Box</label>&nbsp;&nbsp;&nbsp;&nbsp;\
    </div>'; // End

    // end
    placeholders['comp_contact'] = '<div style="border: 1px solid #dfdfdf;padding:10px;">\
                            <label class="copy_placeholder badge" onclick="addPlaceholder(\'{:comp_business_name}\', \'comp_contact\')" >Business Name</label>&nbsp;&nbsp;&nbsp;&nbsp;\
                            <label class="copy_placeholder badge" onclick="addPlaceholder(\'{:comp_business_nature}\', \'comp_contact\')" >Business Nature</label>&nbsp;&nbsp;&nbsp;&nbsp;\
                            <label class="copy_placeholder badge" onclick="addPlaceholder(\'{:comp_business_type}\', \'comp_contact\')" >Business Type</label>&nbsp;&nbsp;&nbsp;&nbsp;\
                            <label class="copy_placeholder badge" onclick="addPlaceholder(\'{:comp_address}\', \'comp_contact\')" >Address</label>&nbsp;&nbsp;&nbsp;&nbsp;\
                            <label class="copy_placeholder badge" onclick="addPlaceholder(\'{:comp_city}\', \'comp_contact\')">City</label>&nbsp;&nbsp;&nbsp;&nbsp;\
                            <label class="copy_placeholder badge" onclick="addPlaceholder(\'{:comp_pincode}\', \'comp_contact\')">Pincode</label>&nbsp;&nbsp;&nbsp;&nbsp;\
                            <label class="copy_placeholder badge" onclick="addPlaceholder(\'{:comp_state}\', \'comp_contact\')">State </label>&nbsp;&nbsp;&nbsp;&nbsp;\
                            <label class="copy_placeholder badge" onclick="addPlaceholder(\'{:comp_phone}\', \'comp_contact\')">Phone </label>&nbsp;&nbsp;&nbsp;&nbsp;\
                            <label class="copy_placeholder badge" onclick="addPlaceholder(\'{:comp_fax}\', \'comp_contact\')">Fax </label>&nbsp;&nbsp;&nbsp;&nbsp;\
                            <label class="copy_placeholder badge" onclick="addPlaceholder(\'{:text_box}\', \'comp_contact\')">Text Box</label>&nbsp;&nbsp;&nbsp;&nbsp;\
                            <label class="copy_placeholder badge" onclick="addPlaceholder(\'{:comp_logo}\', \'comp_contact\')">Logo</label></div>';

    placeholders['branch'] = '<div style="border: 1px solid #dfdfdf;padding:10px;"><label class="copy_placeholder badge" onclick="addPlaceholder(\'{:branch_name}\', \'branch\')" >Branch Name</label>&nbsp;&nbsp;&nbsp;&nbsp;\
                            <label class="copy_placeholder badge" onclick="addPlaceholder(\'{:branch_address}\', \'branch\')" >Branch Address</label>&nbsp;&nbsp;&nbsp;&nbsp;\
                            <label class="copy_placeholder badge" onclick="addPlaceholder(\'{:branch_city}\', \'branch\')" >Branch City </label>&nbsp;&nbsp;&nbsp;&nbsp;\
                            <label class="copy_placeholder badge" onclick="addPlaceholder(\'{:branch_state}\', \'branch\')" >Branch State</label>&nbsp;&nbsp;&nbsp;&nbsp;\
                            <label class="copy_placeholder badge" onclick="addPlaceholder(\'{:branch_pincode}\', \'branch\')">Branch Pincode</label>&nbsp;&nbsp;&nbsp;&nbsp;\
                            <label class="copy_placeholder badge" onclick="addPlaceholder(\'{:branch_email}\', \'branch\')">Branch Email</label>&nbsp;&nbsp;&nbsp;&nbsp;\
                            <label class="copy_placeholder badge" onclick="addPlaceholder(\'{:text_box}\', \'branch\')">Text Box</label></div>';

    //Edited by Akshay on 8-5-2024
    placeholders['separation'] = '<div style="border: 1px solid #dfdfdf;padding:10px;"><label class="copy_placeholder badge" onclick="addPlaceholder(\'{:submitted_date}\', \'separation\')">Resignation Submitted Date</label>&nbsp;&nbsp;&nbsp;&nbsp;\
                                                    <label class="copy_placeholder badge" onclick="addPlaceholder(\'{:last_applied_date}\', \'separation\')">Last Applied Working Date</label>&nbsp;&nbsp;&nbsp;&nbsp;\
                                                    <label class="copy_placeholder badge" onclick="addPlaceholder(\'{:last_working_date}\', \'separation\')">Last Working Date</label>&nbsp;&nbsp;&nbsp;&nbsp;\
                                                    <label class="copy_placeholder badge" onclick="addPlaceholder(\'{:last_approved_working_date}\', \'separation\')">Last Approved Working Date</label>&nbsp;&nbsp;&nbsp;&nbsp;\
                                                    <label class="copy_placeholder badge" onclick="addPlaceholder(\'{:current_date}\', \'separation\')">Current Date</label></div>';
    //End
    /* placeholders['supplier']='<div style="border: 1px solid #dfdfdf;padding:10px;"><label class="copy_placeholder badge" onclick="addPlaceholder(\'{:supplier_name}\', \'supplier\')" >Supplier Name</label>&nbsp;&nbsp;&nbsp;&nbsp;\
                            <label class="copy_placeholder badge" onclick="addPlaceholder(\'{:supplier_email}\', \'supplier\')" >Supplier Email </label>&nbsp;&nbsp;&nbsp;&nbsp;\
                            <label class="copy_placeholder badge" onclick="addPlaceholder(\'{:supplier_phone}\', \'supplier\')" >Supplier Phone</label>&nbsp;&nbsp;&nbsp;&nbsp;\
                            <label class="copy_placeholder badge" onclick="addPlaceholder(\'{:supplier_address}\', \'supplier\')">Supplier Address</label>&nbsp;&nbsp;&nbsp;&nbsp;\
                            <label class="copy_placeholder badge" onclick="addPlaceholder(\'{:supplier_city}\', \'supplier\')">Supplier City</label>&nbsp;&nbsp;&nbsp;&nbsp;\
                            <label class="copy_placeholder badge" onclick="addPlaceholder(\'{:supplier_state}\', \'supplier\')">Supplier State</label>&nbsp;&nbsp;&nbsp;&nbsp;\
                            <label class="copy_placeholder badge" onclick="addPlaceholder(\'{:supplier_pincode}\', \'supplier\')">Supplier Pincode</label>&nbsp;&nbsp;&nbsp;&nbsp;\
                            <label class="copy_placeholder badge" onclick="addPlaceholder(\'{:supplier_comp_name}\', \'supplier\')" >Supplier Company Name </label>&nbsp;&nbsp;&nbsp;&nbsp;\
                            <label class="copy_placeholder badge" onclick="addPlaceholder(\'{:text_box}\', \'esuppliermp\')">Text Box</label></div>'; */

    /* placeholders['customer']='<div style="border: 1px solid #dfdfdf;padding:10px;"><label class="copy_placeholder badge" onclick="addPlaceholder(\'{:customer_name}\', \'customer\')" >Customer Name</label>&nbsp;&nbsp;&nbsp;&nbsp;\
                            <label class="copy_placeholder badge" onclick="addPlaceholder(\'{:customer_email}\', \'customer\')" >Customer Email </label>&nbsp;&nbsp;&nbsp;&nbsp;\
                            <label class="copy_placeholder badge" onclick="addPlaceholder(\'{:customer_phone}\', \'customer\')" >Customer Phone</label>&nbsp;&nbsp;&nbsp;&nbsp;\
                            <label class="copy_placeholder badge" onclick="addPlaceholder(\'{:customer_address}\', \'customer\')">Customer Address</label>&nbsp;&nbsp;&nbsp;&nbsp;\
                            <label class="copy_placeholder badge" onclick="addPlaceholder(\'{:customer_city}\', \'customer\')">Customer City</label>&nbsp;&nbsp;&nbsp;&nbsp;\
                            <label class="copy_placeholder badge" onclick="addPlaceholder(\'{:customer_state}\', \'customer\')">Customer State</label>&nbsp;&nbsp;&nbsp;&nbsp;\
                            <label class="copy_placeholder badge" onclick="addPlaceholder(\'{:customer_pincode}\', \'customer\')">Customer Pincode</label>&nbsp;&nbsp;&nbsp;&nbsp;\
                            <label class="copy_placeholder badge" onclick="addPlaceholder(\'{:customer_comp_name}\', \'customer\')" >Customer Company Name </label>&nbsp;&nbsp;&nbsp;&nbsp;\
                            <label class="copy_placeholder badge" onclick="addPlaceholder(\'{:text_box}\', \'customer\')">Text Box</label></div>';

    placeholders['other']='<div style="border: 1px solid #dfdfdf;padding:10px;"><label class="copy_placeholder badge" onclick="addPlaceholder(\'{:others_name}\', \'other\')" >Name</label>&nbsp;&nbsp;&nbsp;&nbsp;\
                            <label class="copy_placeholder badge" onclick="addPlaceholder(\'{:others_email}\', \'other\')" >Email </label>&nbsp;&nbsp;&nbsp;&nbsp;\
                            <label class="copy_placeholder badge" onclick="addPlaceholder(\'{:others_phone}\', \'other\')" >Phone</label>&nbsp;&nbsp;&nbsp;&nbsp;\
                            <label class="copy_placeholder badge" onclick="addPlaceholder(\'{:others_address}\', \'other\')">Address</label>&nbsp;&nbsp;&nbsp;&nbsp;\
                            <label class="copy_placeholder badge" onclick="addPlaceholder(\'{:others_city}\', \'other\')">City</label>&nbsp;&nbsp;&nbsp;&nbsp;\
                            <label class="copy_placeholder badge" onclick="addPlaceholder(\'{:others_state}\', \'other\')">State</label>&nbsp;&nbsp;&nbsp;&nbsp;\
                            <label class="copy_placeholder badge" onclick="addPlaceholder(\'{:others_pincode}\', \'other\')">Pincode</label>&nbsp;&nbsp;&nbsp;&nbsp;\
                            <label class="copy_placeholder badge" onclick="addPlaceholder(\'{:others_comp_name}\', \'other\')" >Company Name </label>&nbsp;&nbsp;&nbsp;&nbsp;\
                            <label class="copy_placeholder badge" onclick="addPlaceholder(\'{:text_box}\', \'other\')">Text Box</label></div>'; */




    $('#template_form #placeholder_type').on('change', function(e) {
        var key = $(this).val();
        $('#template_form #available_placeholders').html(placeholders[key]);
    });
</script>
<div class="modal-dialog" style="width: 100%; ">

    <!-- Modal content-->
    <div class="modal-content">
        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal">&times;</button>
            <h4 class="modal-title" style="    margin: 5px 28px 0; "><b>DOCUMENT MANAGEMENT</b> </h4>
        </div>
        <div class="modal-body">
            <!-- Form starts -->
            <div id="empsetup-save-response" class="">
                <form class="form-horizontal" method="post" action="<?php echo $this->webroot; ?>DocumentManagers/saveTemplate" id="template_form" name="template_form">
                    <div class="modal-body">
                        <div class="col-sm-6">
                            <div class="form-group form-group-sm">
                                <label class="col-sm-5 control-label">Document Name<label style="color:red">*</label></label>
                                <div class="col-sm-7">
                                    <input class="form-control" placeholder="Enter Document Name" type="text" value='<?php echo isset($template_name) ? $template_name : ""; ?>' name="template_name" id="template_name">
                                </div>
                            </div>
                            <div class="form-group form-group-sm">
                                <label class="col-sm-5 control-label">Data Type<label style="color:red">*</label></label>
                                <div class="col-sm-7">
                                    <select id="placeholder_type" name="placeholder_type" class="form-control js-example-basic-single">
                                        <option value=""> Choose Data Types </option>
                                        <option value="emp"> Employee Info </option>
                                        <option value="emp_join"> Employee Join </option>
                                        <option value="comp_contact"> Company Contact Info </option>
                                        <option value="branch"> Branch Info</option>
                                        <!-- Edited by Akshay on 8-5-2024 -->
                                        <option value="separation"> Employee Separation</option>
                                        <!-- End -->
                                        <!-- <option value="supplier"> Supplier Info</option>
                                            <option value="customer"> Customer Info</option>
                                            <option value="other"> Other Contact Info</option>   -->
                                    </select>
                                </div>
                            </div>

                        </div>
                        <div class="col-sm-3">
                            <div class="col-sm-12">
                                <input type="checkbox" id="policy" name="policy" value="1" <?php echo (isset($policy) && $policy == 1) ? 'checked' : ''; ?>>&nbsp;
                                <label class="control-label">Policy Document</label>
                            </div>
                            <div class="col-sm-12">
                                <input type="checkbox" id="availability" name="availability" value="1" <?php echo (isset($availability) && $availability == 1) ? 'checked' : ''; ?>>&nbsp;
                                <label class="control-label">Employee Access</label>
                            </div>
                            <div class="col-sm-12">
                                <input type="checkbox" id="editable" name="editable" value="1" <?php echo (isset($editable) && $editable == 1) ? 'checked' : ''; ?>>&nbsp;
                                <label class="control-label">Editable Document</label>
                            </div>
                        </div>
                        <div class="col-sm-3">
                            <input onclick="addimage()" class="btn btn-primary" value="Add Image" readonly>
                        </div>

                        <div class="col-sm-12" style="margin-top:10px;">
                            <div class="col-sm-6">
                                <label class="control-label">Header Image</label>
                                <input type="file" id="header_image_file" name="header_image_file" />
                                <?php if (!empty($header_image)): ?>
                                    <?php $header_image_src = (strpos($header_image, '/') === 0 || strpos($header_image, 'http') === 0) ? $header_image : $this->webroot . $header_image; ?>
                                    <div id="header_image_preview" style="margin-top:5px;">
                                        <img src="<?php echo htmlspecialchars($header_image_src, ENT_QUOTES, 'UTF-8'); ?>" style="max-width:100%;max-height:80px;" alt="Header">
                                        <br><small class="text-muted"><?php echo htmlspecialchars($header_image, ENT_QUOTES, 'UTF-8'); ?></small>
                                    </div>
                                <?php endif; ?>
                                <input type="hidden" name="header_image" id="header_image" value="<?php echo isset($header_image) ? htmlspecialchars($header_image, ENT_QUOTES, 'UTF-8') : ''; ?>">
                            </div>
                            <div class="col-sm-6">
                                <label class="control-label">Footer Image</label>
                                <input type="file" id="footer_image_file" name="footer_image_file" />
                                <?php if (!empty($footer_image)): ?>
                                    <?php $footer_image_src = (strpos($footer_image, '/') === 0 || strpos($footer_image, 'http') === 0) ? $footer_image : $this->webroot . $footer_image; ?>
                                    <div id="footer_image_preview" style="margin-top:5px;">
                                        <img src="<?php echo htmlspecialchars($footer_image_src, ENT_QUOTES, 'UTF-8'); ?>" style="max-width:100%;max-height:80px;" alt="Footer">
                                        <br><small class="text-muted"><?php echo htmlspecialchars($footer_image, ENT_QUOTES, 'UTF-8'); ?></small>
                                    </div>
                                <?php endif; ?>
                                <input type="hidden" name="footer_image" id="footer_image" value="<?php echo isset($footer_image) ? htmlspecialchars($footer_image, ENT_QUOTES, 'UTF-8') : ''; ?>">
                            </div>
                        </div>

                        <div class="form-group form-group-sm" style="margin-top:10px;">
                            <div class="col-sm-12">
                                <div id="available_placeholders" class="col-sm-12"></div>
                                <!-- <div class="alert alert-default" role="alert" >
                                 <h5 class="alert-heading"><b>Placeholders</b></h5>
                                   <div class="block">There are some available placeholders that you can use:<br>
                                </div>
                                </div> -->
                                <label class="col-sm-12 control-label" for="item_desc">Document<label style="color:red">*</label></label>
                                <div class="col-sm-12">
                                    <textarea name="template_editor" id="template_editor" rows="10" cols="80">
                                    <?php echo isset($template_content) ? $template_content : ""; ?>
                                    </textarea>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-danger" data-dismiss="modal">Cancel</button>
                        <button type="submit" id="btn-submit" class="btn btn-primary">Save</button>
                    </div>
                    <input type="hidden" name="template_content" id="template_content">
                    <input type="hidden" value='<?php echo isset($template_pkey) ? $template_pkey : ""; ?>' name="template_pkey" id="template_pkey">
                    <input type="hidden" value='<?php echo isset($placeholders) ? $placeholders : ""; ?>' name="placeholders" id="placeholders">
                </form>
                <!-- Tax Head Detail Form -->

            </div>

            <!-- form ends-->
        </div>
    </div>
</div>
<script>
    function addimage() {
        showSmallModalForm(livesite + 'DocumentManagers/addimage/');
    }

    $(document).ready(function() {
        $("#header_image_file").fileinput({
            uploadUrl: livesite + "DocumentManagers/savefile/",
            showCaption: false,
            showClose: true,
            maxFileCount: 1,
            showRemove: true,
            dropZoneEnabled: false,
        });
        $('#header_image_file').on('fileuploaded', function(event, data) {
            var response = data.response;
            $('#header_image').val(response.data.documents);
            $('#header_image_preview').html('<img src="' + response.data.documents + '" style="max-width:100%;max-height:80px;" alt="Header"><br><small class="text-muted">' + response.data.documents + '</small>');
            $.notify("Header image uploaded.", { type: 'success', allow_dismiss: false, z_index: 2000 });
        });

        $("#footer_image_file").fileinput({
            uploadUrl: livesite + "DocumentManagers/savefile/",
            showCaption: false,
            showClose: true,
            maxFileCount: 1,
            showRemove: true,
            dropZoneEnabled: false,
        });
        $('#footer_image_file').on('fileuploaded', function(event, data) {
            var response = data.response;
            $('#footer_image').val(response.data.documents);
            $('#footer_image_preview').html('<img src="' + response.data.documents + '" style="max-width:100%;max-height:80px;" alt="Footer"><br><small class="text-muted">' + response.data.documents + '</small>');
            $.notify("Footer image uploaded.", { type: 'success', allow_dismiss: false, z_index: 2000 });
        });
    });
</script>