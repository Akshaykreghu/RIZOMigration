<?php
/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */
?>
<style>
    /* Edited by bindu 24-10-2025 */
   	.heading {
		display: flex;
		flex-direction: row;
		align-items: end;
		justify-content: space-between;
	}

    .home {
        background-color: #ffffffff;
        border-radius: 50px;
        padding: 2px 15px;
        color: #1e516e !important;
        /* margin-right: 15px; */
        color: white;
        font-weight: 500;
        text-decoration: none;
        transition: all 0.3s ease;
        cursor: pointer;
        border: #1e516e 1px solid;
    }

    /* End */
</style>
<section class="content-header heading">
      
        <!-- /* edited by bindu 22-08-25 */ -->
    <h1 class="text-primary-18">Year End Process</h1>
    <?php if($plan !='basic'){ ?>
  <div class="text-primary-16 home" style="display:flex; align-items:center; gap:10px; cursor:pointer;">
        <i class="fa" style="font-size:16px;">&#xf104;</i>
        Back
    </div>
    <?php } ?>
  
   
</section>
<hr style="margin-top: 8px;margin-bottom: -2px;margin-right: 15px;margin-left: 15px;">
  <!-- end -->
<section class="content">
    <div class="row">
<!--        <div class="col-md-12">
            <div class="box box-header">
                <h3>Leave Year End Process</h3>
            </div>
        </div>-->
        <div class="col-md-12">
            <div class="">
                <div class="col-md-6">
                     <div class="col-md-8">
<!--                    <label>Please Choose a branch to process</label>-->
<div class="col-md-3">
<b> Branch : </b> 
</div><div class="col-md-9"><select class="form-control pull-left" id="branch" name="branch" >
                        <?php
                        foreach ($branches as $branch) {
                            echo '<option value="' . $branch['branch_code'] . '" >' . $branch['branch_name'] . '</option>';
                        }
                        ?>
                    </select>
</div>
                </div>
                    <div class="col-md-4"><button class="btn btn-success" onclick="selectbranch(this);">Process</button></div>
                </div>
                <div class="col-md-4">
                    <span id="showfinyear"></span>
                </div>
                <div class="col-md-12" id="loadprocess">

                </div>
        </div>
        </div>
    </div>
</section>
<script>
function selectbranch(s){
    $(s).html("Processing ... ").prop("disabled",true);
    var abranch = $('#branch').val();
    $('#loadprocess').load(livesite+'YearEnd/loadprocess/',{id:abranch},function(){
        $(s).html("Process").prop("disabled",false);
        $.notify("Loaded Successfully", {
            type: 'success',
            allow_dismiss: false
        });
    });
    $('#showfinyear').html('<h3>Year end process </h3>');
}
  	 /* edited by bindu 19-02-26 */
   $(".home").on("click", function () {

    $("#container").isLoading({
        text: "Loading",
        position: "overlay",
    });

    let url = "";
    var userGroup = <?php echo json_encode($this->Session->read('user_group')); ?>

    if (userGroup == "1") {
        url = livesite + "CompanySetup/index";
    } 
    else if (userGroup == "2") {
        url = livesite + "EmployeeMenu/addon";
    }

    $("#container").load(url, function () {
        isDashboardShown = false;
    });

});

	/* edited by bindu 19-02-26 */
</script>