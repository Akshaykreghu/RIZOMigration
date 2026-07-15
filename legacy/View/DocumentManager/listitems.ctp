<link href="https://cdn.datatables.net/1.13.7/css/jquery.dataTables.min.css" rel="stylesheet">

<section class="content-header">
    <h1 style="text-align:left; font-size: 3em;"> Birthday Templates </h1>
</section>

<section class="content">
    <div class="row">
        <div class="col-md-12">
            <div class="box ">
                <br>
                <div class="box-body">
                    <div class="box-body">
                        <button class="btn btn-primary" onclick="showModalForm(livesite + 'DocumentManager/templateform');">Add</button>
                        <hr>
                        <div>
                            <table id="example" class="display" style="width:100%">
                                <thead>
                                    <tr>
                                        <th>Name</th>
                                        <th>Type</th>
                                        <th>Image</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($arr_template as $key => $value) { ?>
                                        <tr>
                                            <td><?= $value['templates']['name'] ?></td>
                                            <td><?= $value['templates']['type'] ?></td>
                                            <td><?= $value['templates']['imageTop'] ?></td>
                                            <td><button class="btn btn-default btn-xs" onclick="loadItems('<?= $value['templates']['id'] ?>');">Edit</button> <button class="btn btn-danger btn-xs" onclick="loadItems('<?= $value['templates']['id'] ?>');">Delete</button>
                                                <?php if ($value['templates']['is_default'] == 0) { ?>
                                                    <button class="btn btn-primary btn-xs" onclick="setDefault('<?= $value['templates']['id'] ?>', '<?= $value['templates']['type'] ?>');">Set as Default</button>
                                                <?php } else { ?>
                                                    <button class="btn btn-success btn-xs">Default</button>
                                                <?php } ?>
                                            </td>
                                        </tr>
                                    <?php } ?>
                                </tbody>
                            </table>
                        </div>

                    </div>
                </div>
            </div>

        </div>
    </div>
</section>

<script>
    $(document).ready(function() {

        $('#example').DataTable({

        });
    });

    function setDefault(id, type) {

        $.ajax({
            type: "POST",
            url: livesite + "DocumentManager/savedefault_template",
            data: {
                'id': id,
                'type': type
            },
            success: function(msg) {
                var res = JSON.parse(msg);
                console.log(msg);
                alert(res.msg);
                refresh();
            },
            error: function(msg) {
                console.log(msg);
            }
        })


    }

    function refresh() {
        $('#loader').show();
        $("#container").load("<?= $this->request->base ?>/DocumentManager/listitems", function() {
            $('#loader').hide();
        });
    }

    function loadItems(id) {
        $('#loader').show();
        $("#container").load("<?= $this->request->base ?>/DocumentManager/template/" + id, function() {
            $('#loader').hide();
        });
    }
</script>