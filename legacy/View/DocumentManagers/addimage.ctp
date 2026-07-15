<section>
    <div class="row">
        <div class="col-md-12">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                    <h4>Add Image</h4>
                </div>
                <div class="modal-body">
                    <div class="form-group form-group-sm" style="margin-bottom: 0px;">
                        <div class="">
                            <!--<label class="col-sm-3" for="empctccsv">Select Image</label>-->
                            <div class="col-md-12">
                                <?php foreach ($images as $key => $val) { ?>
                                    <div class="col-md-3" style="display:flex;padding-left:0px;">
                                        <input type="checkbox" name="imgname" id="<?php echo $val['doc_images']['image_pkey']; ?>" style="margin: 18px 5px;" value="<?php echo $val['doc_images']['image_url']; ?>">
                                        <img src="<?php echo $val['doc_images']['image_url']; ?>" alt="Image" style="width:40px;margin-top: 4px;" height="40">

                                        <!--<div class="" style="padding: 2px;margin-top: 10px;" ><i class="glyphicon glyphicon-trash" onclick="deleteImage('<?php echo $val['doc_images']['image_pkey']; ?>')" title="Delete Image"></i></div>-->
                                    </div>
                                <?php } ?>
                            </div>

                        </div>
                    </div>
                    <div class="form-group form-group-sm" style="clear:both;margin-bottom: 0px;">
                        <!--<label class="col-sm-3" for="empctccsv">Upload Image</label>-->
                        <div class="col-md-5" style="display:grid;margin-top:20px;margin-bottom: 20px;">
                            <input type="file" id="avatarfile" name="avatarfile" />
                            <input type="hidden" name="files" id="files">
                        </div>
                    </div>
                </div>
                <div class="modal-footer" style="padding-right: 15px;padding-left: 15px;clear: both;">
                    <div class="col-md-12">
                        <div style="display:flex;float:right;">
                            <input id="image" name="image" style="border: none;height:0px;padding:0px;">
                            <input id="pkey" name="pkey" style="border: none;color: #fff;width:0px;height:0px;padding:0px;">
                            <input onclick="myFunction()" class="btn btn-primary" style="margin: 3px;width: 150px;" value="Copy Image Link" readonly>
                            <input onclick="deleteImage()" class="btn btn-danger " style="margin: 2px;width: 83px;float:right" value="Delete" readonly>
                            <!--<button type="button" class="btn btn-danger pull-right" data-dismiss="modal">Close</button>-->
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<script>
    function myFunction() {
        var copyText = document.getElementById("image");
        if (copyText.value != '') {
            copyText.select();
            copyText.setSelectionRange(0, 99999); /* For mobile devices */
            document.execCommand("copy");
            $.notify("Selected image link has been copied.", {
                type: 'success',
                allow_dismiss: false,
                z_index: 2000
            });
        } else {
            $.notify("Please select the image first.", {
                type: 'danger',
                allow_dismiss: false,
                z_index: 2000
            });
        }
        //alert("Copied the text: " + copyText.value);
    }


    $(document).ready(function() {

        $("#avatarfile").fileinput({
            uploadUrl: livesite + "DocumentManagers/savefile/",
            showCaption: false,
            showClose: true,
            //                        allowedFileTypes:['jpg', 'gif', 'png'],
            //			allowedPreviewTypes:['jpg', 'gif', 'png'],
            maxFileCount: 1,
            showRemove: true,
            dropZoneEnabled: false,
        });
        $('#avatarfile').on('fileuploaderror', function(event, data, previewId, index, jqXHR) {
            // do your validation and return an error like below
            //            $('.img-circle').attr("src","second.jpg");
            response = data.response;
            //alert(response.error);
            $.notify(response.error, {
                type: 'danger',
                allow_dismiss: false,
                z_index: 2000
            });
            // $('.box-profile').load(livesite + 'User/loadImage', function () {
            //   $('.box-profile').fadeIn('slow');
            //                $('#avatarfile').fileinput('reset');
            // });

        });

        $('#avatarfile').on('fileuploaded', function(event, data, previewId, index) {
            var form = data.form,
                files = data.files,
                extra = data.extra,
                response = data.response,
                reader = data.reader;
            //console.log(response.data.documents);
            $('#files').val(response.data.documents);
            $('#image').val(response.data.documents);
            //$('#image').show();
            $('#btn-submitfami').html('Save').attr('disabled', false);

            if (response.error == "") {
                $.notify("Document Uploaded.", {
                    type: 'success',
                    allow_dismiss: false,
                    z_index: 2000
                });
            } else {
                //alert(response.error);
                $.notify(response.error, {
                    type: 'danger',
                    allow_dismiss: false,
                    z_index: 2000
                });
            }
        });

        $('#avatarfile').on('filepreupload', function(event, data, previewId, index) {
            var form = data.form,
                files = data.files,
                extra = data.extra,
                response = data.response,
                reader = data.reader;
            //console.log('File pre upload triggered');
            $('#imgsloader').attr("src", livesite + "img/loaders.gif");
            $('#btn-submitfami').html('<li class="fa fa-spinner fa-spin"></li> Document Uploading...').attr('disabled', 'disabled');
        });
    });
    $("input:checkbox").on('click', function() {
        var $box = $(this);
        var value = $("input[name='imgname']:checked").val();
        if ($box.is(":checked")) {
            var id = $(this).attr('id');
            // the name of the box is retrieved using the .attr() method
            // as it is assumed and expected to be immutable
            var group = "input:checkbox[name='" + $box.attr("name") + "']";
            // the checked state of the group/box on the other hand will change
            // and the current value is retrieved using .prop() method
            $(group).prop("checked", false);
            $box.prop("checked", true);
            $('#image').val(value);
            $('#pkey').val(id);
        } else {
            $box.prop("checked", false);
            $('#image').val('');
            $('#pkey').val('');
        }
    });

    function deleteImage() {
        var copyText = document.getElementById("pkey");
        if (copyText.value != '') {
            if (confirm("Are you sure want to delete?")) {
                $.ajax({
                    url: livesite + 'DocumentManagers/deleteImage/' + copyText.value,
                    type: 'POST',
                    data: {
                        pkey: copyText.value
                    },
                    success: function(resp) {
                        if (resp == 1) {
                            $.notify("Image Deleted.", {
                                type: 'success',
                                allow_dismiss: false,
                                z_index: 2000
                            });
                            showSmallModalForm(livesite + 'DocumentManagers/addimage/');
                        } else {
                            if (typeof callback === 'function') {
                                callback.call();
                            }
                        }
                    }
                });
            }
        } else {
            $.notify("Please select the image first.", {
                type: 'danger',
                allow_dismiss: false,
                z_index: 2000
            });
        }
    }
</script>
<style>
    .file-caption {
        height: 34px;
        padding: 6px 12px;
    }
</style>