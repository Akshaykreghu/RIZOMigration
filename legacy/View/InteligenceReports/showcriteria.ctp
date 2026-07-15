<style>
    /* select {
    border: none;
} */
</style>

<input type="hidden" class="hidden-criterias" id="hidden-criteria<?php echo $newindex; ?>" name="hidden-criteria<?php echo $newindex; ?>" value="" />
<div class="col-md-3">Criteria <?php echo $newindex; ?></div>
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
<!-- <div class="col-md-1">
    <a onclick="addOneReportCriteria(<?php echo $newindex; ?>);"><i class="fa fa-plus-circle"></i></a>
    <a class="link-removecriterias" onclick="removeThisCriteria(<?php echo $newindex; ?>);"><i class="fa fa-minus-circle"></i></a>
</div> -->