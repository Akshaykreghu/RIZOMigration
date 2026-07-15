<style>
    .list-group-item a {
        color: #233949;
        font-weight: bold;
    }
</style>




<ul class="list-group">
    <?php foreach ($arr_list_of_accesses_sites as $key => $value) { ?>
        <li class="list-group-item">
            <a href="loginWithCentral/<?= $value['central_control']['company_code']; ?>" ><?= $value['central_control']['company_name']; ?></a>
        </li>
    <?php } ?>
</ul>
<script src="<?php echo $this->webroot ?>/plugins/jQuery/jQuery-2.1.4.min.js"></script>

<script>

    setTimeout(() => {
        window.location.replace("<?php echo $this->webroot ?>Site/logout");
    }, 300000);

    $(".list-group-item a").on("click", function() {
        setTimeout(() => {
            $(".list-group-item a").attr("href", "#");
        }, 1000);
    });

</script>