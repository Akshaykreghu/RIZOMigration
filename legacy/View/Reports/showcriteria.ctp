
<script type="text/javascript">
    //This is to hide the previous plus button. by ***ARUL P DAS on 3/3/2020
    $('#div-criteria<?php echo $newindex - 1; ?> .col-md-1').hide();
</script>

<input type="hidden" class="hidden-criterias" id="hidden-criteria<?php echo $newindex; ?>" name="hidden-criteria<?php echo $newindex; ?>" value="" />
<div class="col-md-3" style="font-weight: bold; text-align: right">Criteria <?php echo $newindex; ?> :</div>
<div class="col-md-4">
    <select id="select-criteria<?php echo $newindex; ?>" name="select-criteria<?php echo $newindex; ?>" class="form-control" onchange="loadCriteriaItems(<?php echo $newindex; ?>);" >
        <option value="">--Choose criteria--</option>
        <?php
        foreach ($arr_remainingcriterias as $key => $value) {
            echo '<option value="' . $value['reportcriteria'] . '">' . $value['reportcriteria_desc'] . '</option>';
        }
        ?>
    </select>
</div>
<div class="col-md-4" id="div-items-criteria<?php echo $newindex; ?>">

</div>
<div class="col-md-1">
<!--    <a onclick="addOneReportCriteria(<?php echo $newindex; ?>);"><i class="fa fa-plus-circle"></i></a>
    <a class="link-removecriterias" onclick="removeThisCriteria(<?php echo $newindex; ?>);"><i class="fa fa-minus-circle"></i></a>-->

    <?php
    if ($newindex < 4) {
        ?>
        <a onclick="addOneReportCriteria(<?php echo $newindex; ?>);"><i class="fa fa-plus-circle"></i></a>
        <?php
    }
    ?>
        <a class="link-removecriterias" onclick="removeThisCriteria(<?php echo $newindex; ?>);"><i class="fa fa-minus-circle"></i></a>
</div>
<style type="text/css">
    div .datagrid-body{/*This is to solve the issue when adding three criterias(jQuery issue). By ***ARUL P DAS on 27/12/2019*/
        width: auto !important;
    }
</style>