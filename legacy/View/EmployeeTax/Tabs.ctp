<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */
?>
<script>
     $(document).ready(function(){
        var site_fkey = $('#emp_pkey').val();
        $('#load').load(livesite+'EmployeeTax/setup/'+site_fkey);
                    //reloadDatagrid(site_fkey);
        
       $('#tt').tree({
   
});
    })
</script>

<section class="content">
    <div class="row">
<div class="col-md-12">
<!--<div class="box box-header">
    <h2>Tax Details</h2> <p>Control tax details of any employees.</p>
       <div class="box-body" id="div-reportcriterias">
                     <div class="col-md-12">
                     <input type="hidden" id="emp_pkey" value="<?php echo $emp_fkey; ?>">
                <input type="text" id="user" class="form-control" name="user" placeholder="Search Your Employee Name" />
            </div>
                </div>
  
    
    
</div>-->
 <div id="load" class="box box-body" style="margin:10px 0">
       
    </div> 
</div>
        
    </div>
</section>