<div class="profile_img">

    <!-- end of image cropping -->
    <div id="crop-avatar">
        <!-- Current avatar -->
        <form method="post" enctype="multipart/form-data" >
            <input type="file" id="avatarfile" name="avatarfile" />
        </form>

        <!-- Cropping modal -->

        <!-- /.modal -->

        <!-- Loading state -->
        <div class="loading" aria-label="Loading" role="img" tabindex="-1"></div>
    </div>
    <h3><?php echo $this->Session->read('user_name'); ?></h3>


</div>

<script>
    $(document).ready(function() {
        $("#avatarfile").fileinput({
<?php if (isset($user['avatar']) && $user['avatar'] != null) { ?>
                             initialPreview: [
                                            '<img width="140px" height="104px" src="<?php echo $this->webroot . $user['avatar']; ?>" class="file-preview-image" alt="<?php echo $user['first_name']; ?>" title="<?php echo $user['first_name'] ?>" ; >',
                                    ],
    //                        initialPreviewConfig: [
    //                            {
    //                                caption: 'desert.jpg', 
    //                                width: '120px', 
    //                                url: 'User/saveavatar', // server delete action 
    //                                key: 100, 
    //                                extra: {id: 100}
    //                            }
    //                        ],
<?php } ?>
                
                        uploadUrl:livesite+"User/saveavatar",
                        showCaption: true,
                        showClose:true,
                        allowedFileTypes:['jpg', 'gif', 'png'],
//			allowedPreviewTypes:['jpg', 'gif', 'png'],
                        maxFileCount:1,
                        showRemove:true,
                        dropZoneEnabled:true,
            }); 
    $('#avatarfile').on('fileuploaderror', function(event, data, previewId, index, jqXHR) {
    // do your validation and return an error like below
//            $('.img-circle').attr("src","second.jpg");
            $('.box-profile').load(livesite+'User/loadImage',function(){
                $('.box-profile').fadeIn('slow');
//                $('#avatarfile').fileinput('reset');
            });
    
    });
    
    $('#avatarfile').on('filepreupload', function(event, data, previewId, index) {
    var form = data.form, files = data.files, extra = data.extra,
        response = data.response, reader = data.reader;
        console.log('File pre upload triggered');
        $('#imgsloader').attr("src",livesite+"img/loaders.gif");
    });
    });
</script>    