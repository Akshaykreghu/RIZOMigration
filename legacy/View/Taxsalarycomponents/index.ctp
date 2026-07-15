<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */
?>
<style>
    /* <!-- edited by bindu 29-11-2025 --> */
    .heading {
        display: flex;
        flex-direction: row;
        align-items: end;
        justify-content: space-between;
        /* margin-left: 20px; */
    }

    .home {
        background-color: #ffffffff;
        border-radius: 50px;
        padding: 2px 15px;
        color: #1e516e !important;
        margin-right: 15px;
        color: white;
        font-weight: 500;
        text-decoration: none;
        transition: all 0.3s ease;
        cursor: pointer;
        border: #1e516e 1px solid;
    
    }

</style>
<!-- edited by bindu 29-11-2025 end -->
<script type="text/javascript">
$(document).ready(function() { 

 var options = {
        success: function (resp) {
            //alert(resp);
//             var row = $('#purchaselist').datagrid('getSelected');
//                        console.log(row);
                    
                       
                //var pkey =  $.parseJSON(resp).pk 
                //var skey =  $.parseJSON(resp).spk 
                //var skey =  $.parseJSON(resp).spk 
               
            $.notify("Success", {
                type: 'success',
                allow_dismiss: false
            });
        }  // post-submit callback
    };
    // bind to the form's submit event 
    $('#Tax').submit(function() { 
        $(this).ajaxSubmit(options); 
 
        
        return false; 
    });
    }); 
</script>
<!-- edited by bindu 29-11-2025 -->
<section class="content-header heading">
     <!-- edited by athira on 03-07-2025 -->
    <h1 class="text-primary-18"> Statutory Heads</h1>
    <div class="text-primary-16 home" style="display:flex; align-items:center; gap:10px; cursor:pointer;margin:0;">
                    <i class="fa" style="font-size:16px;">&#xf104;</i>
                    Back
                </div>

</section>
<hr style="margin:8px 15px -2px 15px;">
<!-- edited by bindu 29-11-2025 -->
<section class="content">
    
    <div class="row">
<div class="col-md-12">
<div class="">
<!--    <legend>Tax Components</legend>    -->  
 <form  id="Tax" action="<?php echo $this->webroot; ?>Taxsalarycomponents/Save" method="POST">
     <?php
           foreach($taxcomponents as $value) 
           {
         ?>
    <div class="col-md-3">
                <label><?php echo $value['Taxsalarycomponents']['tax_salary_components_name']; ?></label>
            </div>
         <div class="col-md-3">
                     <input name="pkey[]" type="hidden" value="<?php echo $value['Taxsalarycomponents']['tax_salary_components_pkey']; ?>">
              <select id="<?php echo $value['Taxsalarycomponents']['tax_salary_components_name']; ?>" name="components[]" class="form-control" >
                                        <option value="">--Select--</option>
                                       <?php foreach($heads as $head)
                                       {
                                       ?>
                                        <option <?php if(isset($value['Taxsalarycomponents']['salary_head_item_Fkey']) && $value['Taxsalarycomponents']['salary_head_item_Fkey'] == $head["SalaryHeadItems"]['salary_head_item_pkey']) { echo "selected='selected'" ;} ?> value="<?php echo $head["SalaryHeadItems"]['salary_head_item_pkey']; ?>" ><?php echo $head["SalaryHeadItems"]['item']; ?></option>
                                        <?php
                                       }
                                       ?>
              </select> </div>
     
     
         <div class="col-md-3">
                    <label for="exampleInputEmail1">Limit</label>
            </div>
         <div class="col-md-3">
                     <input type="hidden" id="emp_pkey">
                <input type="text" id="mTax" value="<?php echo isset($value['Taxsalarycomponents']['upper_limit'])?$value['Taxsalarycomponents']['upper_limit']: ''; ?>" class="form-control" name="Limit[]" placeholder="TAXs" />
            </div>
     <?php
           }
           ?>
     
     <div class="box-body col-md-12">
     <input type="submit" class="btn btn-primary pull-right">
     </div>
 </form>
</div>
</div>
    </div>
</section>
<script>
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