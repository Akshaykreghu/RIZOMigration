<div class="col-md-12">
 <form class="form-horizontal" id="taxForm" action="<?php echo $this->webroot; ?>Taxation/uploadFile" method="post" >
     <div class="modal-body">
        <input id="emp_pkey" name="emp_pkey" type="hidden"  value="<?php echo $emp; ?>" >
        <input id="tax_heads_fkey" name="tax_heads_fkey" type="hidden"  value="<?php echo $tax_head; ?>" >
        <input id="tax_heads_detail" name="tax_heads_detail" type="hidden"  value="<?php echo $tax_detail; ?>" >
         <fieldset>

             <!-- Form Name -->
<!--             <legend>Upload Document</legend>-->

             <!-- Text input-->

             <div class="form-group">
                 <div class="col-md-12">

                     <div class="input-group date">
                     <div tabindex="500" class="btn btn-primary btn-file"><i class="glyphicon glyphicon-folder-open"></i> &nbsp;Browse
                     <input type="file" class="form-control file"  id="taxfile" name="taxfile" >
                     </div>   
                     </div>

                 </div>
             </div>


         </fieldset>

     </div>
     <div class="modal-footer">
         <button type="button" class="btn btn-danger" onclick="closediv(<?php echo $tax_detail; ?>);">Close</button>
         <button type="submit" class="btn btn-primary">Upload</button>
     </div>
 </form>
</div>
<script type="text/javascript">
$(document).ready(function() { 
    var options = { 
 	 success:function(responseText, statusText, xhr, $form){
	
        var response =  $.parseJSON(responseText);
        //console.log(response);
        alert(response.message);
        if(response.status == 1){
            $.notify(response.message,{
                            type: 'success',
                            allow_dismiss: true

                    });
               closediv(response.taxHeadKeyValue);
              showTaxHeadDetailss(response.taxHeadKey);
        //reloadtablenoticeperiod();
        }
        else{
//            $.notify("Notice Period Saving error",{
//                            type: 'warning',
//                            allow_dismiss: true
//
//                    });
                  //  closediv();
       // reloadtablenoticeperiod();
        }
}
    }; 
    function showTaxHeadDetailss(obj) {
        var taxHeadPkey = obj;
        var empPkey = $('#empsetuptaxation #emp_pkey').val();
        var url = livesite + 'Employee/showtaxheaddetail/' + empPkey + '/' + taxHeadPkey;

        var container = $("#modalShowTaxHeadDetailForm #modalForm-content")
        container.load(url, function () {
            $("#modalShowTaxHeadDetailForm").modal('show');
        });
    }
    // bind to the form's submit event 
    $('#taxForm').submit(function() {
        var value = $("#taxfile").val();
          if(value){
           $(this).ajaxSubmit(options); 
          }else{
              alert("Please Choose a file for Upload.");  
        //  showTaxHeadDetailsshow(head,emp);
          }
        // inside event callbacks 'this' is the DOM element so we first 
        // wrap it in a jQuery object and then invoke ajaxSubmit 
       
//    $.notify(response.msg,{
//                            type: response.type,
//                            allow_dismiss: true
//
//                    });
               //     closediv();
        // !!! Important !!! 
        // always return false to prevent standard browser submit and page navigation 
   
        return false; 
    });
    }); 
  
</script>