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
    var options = {
        success: function(resp) {
            $('#modalForm').modal('hide');
            $('#template_table').datagrid('reload');
            $.notify("Success", {
                type: 'success',
                allow_dismiss: false
            });
            refresh();
        }
    };

    $('#template_form').on('submit', function(event) {
        event.preventDefault();
        if (confirm("Do You Want To Save The Form")) {
            $('#template_form').ajaxSubmit(options);
        }

    });

    $(document).ready(function() {

    });
</script>

<div class="modal-dialog" style="width: 100%; ">

    <!-- Modal content-->
    <div class="modal-content">
        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal">&times;</button>
            <h4 class="modal-title" style="    margin: 5px 28px 0; "><b>Create Template</b> </h4>
        </div>
        <div class="modal-body">
            <!-- Form starts -->
            <div id="empsetup-save-response" class="">
                <form class="form-horizontal" method="post" action="<?php echo $this->webroot; ?>DocumentManager/saveBirthdayTemplate" id="template_form" name="template_form">
                    <div class="modal-body">
                        <div class="col-sm-12">
                            <div class="form-group form-group-sm">
                                <label class="col-sm-4 control-label">Template Name<label style="color:red">*</label></label>
                                <div class="col-sm-7">
                                    <input class="form-control" placeholder="Enter Template Name" type="text" value='<?php echo isset($template_name) ? $template_name : ""; ?>' name="name" id="name">
                                </div>
                            </div>
                            <div class="form-group form-group-sm">
                                <label class="col-sm-4 control-label">Template Type<label style="color:red">*</label></label>
                                <div class="col-sm-7">
                                    <select id="placeholder_type" name="type" class="form-control js-example-basic-single">
                                        <option value=""> Choose Data Types </option>
                                        <option value="birthday"> Birthday </option>
                                        <option value="anniversary"> Anniversary </option>
                                        <option value="Other"> Other</option>
                                    </select>
                                </div>
                            </div>

                        </div>

                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-danger" data-dismiss="modal">Cancel</button>
                        <button type="submit" id="btn-submit" class="btn btn-primary">Save</button>
                    </div>
                    <input type="hidden" value='<?php echo isset($template_pkey) ? $template_pkey : ""; ?>' name="id" id="template_pkey">
                </form>
                <!-- Tax Head Detail Form -->

            </div>

            <!-- form ends-->
        </div>
    </div>
</div>