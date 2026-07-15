<script src="<?php echo $this->webroot; ?>plugins/ckeditor4/ckeditor4/ckeditor.js" type="text/javascript"></script>

<!-- <link rel="stylesheet" href="//code.jquery.com/ui/1.13.2/themes/base/jquery-ui.css"> -->
<link rel="stylesheet" href="/resources/demos/style.css">
<!-- <link href="https://stackpath.bootstrapcdn.com/bootstrap/3.4.1/css/bootstrap.min.css" rel="stylesheet"> -->
<!-- include summernote css/js -->
<link rel="stylesheet" href="//code.jquery.com/ui/1.13.2/themes/base/jquery-ui.css">
<link rel="stylesheet" href="/resources/demos/style.css">
<link href="https://stackpath.bootstrapcdn.com/bootstrap/3.4.1/css/bootstrap.min.css" rel="stylesheet">
<!-- <link href="https://cdn.jsdelivr.net/npm/summernote@0.8.18/dist/summernote.min.css" rel="stylesheet"> -->

<style>
    .draggable {
        width: 450px;
        padding: 0em;
    }

    .ui-draggable,
    .note-editor {
        background: transparent;
    }

    .droppable {
        width: 100px;
        height: 100px;
        padding: 20px;
        background: #ccc;
    }
</style>

<input type="hidden" id="templateid" value="<?= $data[0]['templates']['id']; ?>">

<?php
$image = $data[0]['templates']['image'] ? $data[0]['templates']['image'] : 'https://static.vecteezy.com/system/resources/previews/018/795/648/non_2x/happy-birthday-background-with-frame-use-for-greeting-card-poster-template-party-invitation-layout-free-png.png'; ?>
<!-- Main content -->
<section class="content bgimage" style="height: 880px; top: 20px; border: 2px solid darkgray; position: relative; width: 899px; background: url('<?= $image; ?>'); background-size: contain; background-repeat: no-repeat; background-position: center; ">
    <div class="row">
        <div class="col-md-12" style="position: unset; ">
            <div class="">

                <br>
                <div class="">
                    <div class="">

                        <div>
                            <div class="form-group droppable" style="border: 1px solid #ccc;     text-align: center;display: flex; flex-direction: column; justify-content: center; width: <?= $data[0]['templates']['imagesize'] ? $data[0]['templates']['imagesize'] : 40; ?>px; height: <?= $data[0]['templates']['imageHeight']; ?>px; position: absolute; top: <?= $data[0]['templates']['imageTop'] ? $data[0]['templates']['imageTop'] : 10; ?>px; left: <?= $data[0]['templates']['imageLeft'] ? $data[0]['templates']['imageLeft'] : 40; ?>px; ">
                                <label for="exampleInputEmail1">Profile Image Area</label>
                                <p>Drag this box wherever you want to position profile image</p>
                            </div>
                        </div>

                        <div id="append-widget-content">
                            <div class="draggable" style="position: absolute; border: 1px solid lightgray; top: <?= $template[0]['templates_details']['top_axis'] ? $template[0]['templates_details']['top_axis'] : 140; ?>px; left: <?= $template[0]['templates_details']['left_axis'] ? $template[0]['templates_details']['left_axis'] : 200; ?>px; " class="ui-widget-content">
                                <p style="background: aliceblue; padding: 4px; ">Drag this box to a position where you like</p>
                                <form method="post"><textarea class="summernote" name="template_editor" id="summernote" name="editordata"><?= $template[0]['templates_details']['text_content']; ?></textarea></form>
                            </div>
                        </div>
                    </div>
                </div><!-- /.box-body -->
            </div>

        </div>
    </div>

</section>

<section class="content">
    <div class="form-group form-row" style="display: flex ; margin: 40px; justify-content: space-between; flex-direction: row; ">

        <div>
            <button class="btn btn-primary" onclick="addtexttoeditor('name');">Add Name</button>
            <button class="btn btn-primary" onclick="addtexttoeditor('joining_date');">Add Joining date</button>
        </div>

        <div style="width: 60%; display: flex; justify-content: end; ">
            <!-- <button class="btn btn-primary" onclick="addtext();">Add Text</button> -->

            <input style="margin: 6px; " type="file" id="avatarfile" name="filename" />

            <button class="btn btn-primary" style="margin-left: 10px; " onclick="saveData();">Save</button>

            <button class="btn btn-primary" style="margin-left: 10px; " onclick="loaditem();">Preview</button>
        </div>
    </div>


</section>

<!-- <script src="https://code.jquery.com/jquery-3.6.0.js"></script> -->
<script src="https://code.jquery.com/ui/1.13.2/jquery-ui.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/3.4.1/js/bootstrap.min.js"></script>
<!-- <script src="https://cdn.jsdelivr.net/npm/summernote@0.8.18/dist/summernote.min.js"></script> -->
<script>
    $(document).ready(function() {

        $("#avatarfile").fileinput({
            uploadUrl: livesite + "DocumentManager/saveImage/<?= $id; ?>",
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

            if (response.error == "") {
                $('.bgimage').css("background", 'url(' + response.data.documents + ') ');
                $('.bgimage').css("background-size", 'contain');
                $('.bgimage').css({
                    "background-repeat": 'no-repeat',
                    'height': '880px',
                    'position': 'relative',
                    'width': '899px',
                    'background-position': 'center'
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

        setTimeout(function() {

            $(".droppable").draggable({
                stop: function(event, ui) {
                    console.log("stop resizsweds", ui.size);
                    console.log("position", ui.position);
                    // saveData();
                }
            }).resizable({
                resize: function(event, ui) {
                    console.log("resize", event);
                },
                stop: function(event, ui) {
                    console.log("stop resizsweds", ui.size);
                    console.log("position", ui.position);
                    // saveData();
                }
            });
            $(".draggable").draggable({
                stop: function(event, ui) {
                    console.log("position", ui.position);
                    var data = CKEDITOR.instances['summernote'].getData(); // $('.summernote').summernote('code');
                    console.log(data);
                    // saveData();
                }
            }).resizable();
            // $('.summernote').summernote({
            //     height: 100, // set editor height
            //     minHeight: null, // set minimum height of editor
            //     maxHeight: null, // set maximum height of editor
            //     focus: true,
            //     toolbar: [
            //         // [groupName, [list of button]]
            //         ['style', ['bold', 'italic', 'underline', 'clear']],
            //         ['font', []],
            //         ['fontsize', ['fontsize']],
            //         ['color', ['color']],
            //         ['para', ['ul', 'ol', 'paragraph']],
            //         ['height', ['height']]
            //     ]
            // });
            CKEDITOR.disableAutoInline = true;
            var editor = CKEDITOR.inline('summernote', {
                preset: 'basic',

                removeButtons: 'Source,Table,Link,Unlink,Anchor,NewPage,Templates,PasteText,PasteFromWord,Find,Replace,SelectAll,Print,Cut,Copy,Paste,Form,Checkbox,Radio,Select,Button,HiddenField,Iframe,Flash,CreateDiv,Language,Anchor,ImageButton',
                removePlugins: 'about,jsplusTranslator,image,elementspath,save,font',
            });

            editor.addCommand("mySaveCommand", { // create named command
                exec: function(edt) {
                    alert(edt.getData());
                }
            });

            editor.ui.addButton("saveButton", { // add new button and bind our command
                label: "Click me",
                command: "mySaveCommand",
                toolbar: "insert",
                icon: "https://i.stack.imgur.com/IWRRh.jpg?s=328&g=1"
            });
            // editor = CKEDITOR.replace('template_editor', {
            //     height: 300
            // });
        }, 1000);


    });


    function addtexttoeditor(data) {
        if (data == "name") {
            CKEDITOR.instances['summernote'].insertHtml("%name%");
        } else {
            CKEDITOR.instances['summernote'].insertHtml("%date_of_joining%");
        }

    }

    function loaditem() {
        showLargeModalForm(livesite + 'DocumentManager/renderImageTempaltePrew/<?= $id; ?>/<?= $companycode; ?>')
    }

    function saveData() {
        var data = CKEDITOR.instances['summernote'].getData(); //$('.summernote').summernote('code');
        var templateid = $("#templateid").val();
        console.log(data);
        $.ajax({
            type: "POST",
            url: livesite + "DocumentManager/save_template",
            data: {
                'data': data,
                'left': $(".draggable").position().left,
                'top': $(".draggable").position().top,
                'imageLeft': $(".droppable").position().left,
                'imageTop': $(".droppable").position().top,
                'imageWidth': $(".droppable").outerWidth(),
                'imageHeight': $(".droppable").outerHeight(),
                'templid': templateid
            },
            success: function(msg) {
                var res = JSON.parse(msg);
                console.log(msg);
                alert(res.msg);
            },
            error: function(msg) {
                console.log(msg);
            }
        })
    }

    function addtext() {
        $("#append-widget-content").append('<div class="draggable" class="ui-widget-content"><p>Drag me around</p><form method="post"><textarea class="summernote" name="editordata"></textarea></form></div></div>');
        $(".draggable").draggable({
            stop: function(event, ui) {
                console.log("position", ui.position);
            }
        }).resizable();
        // $('.summernote').summernote({
        //     height: 100, // set editor height
        //     minHeight: null, // set minimum height of editor
        //     maxHeight: null, // set maximum height of editor
        //     focus: true,
        //     toolbar: [
        //         // [groupName, [list of button]]
        //         ['style', ['bold', 'italic', 'underline', 'clear']],
        //         ['font', []],
        //         ['fontsize', ['fontsize']],
        //         ['color', ['color']],
        //         ['para', ['ul', 'ol', 'paragraph']],
        //         ['height', ['height']]
        //     ]
        // });
    }
</script>

<script>

</script>