<script type="text/javascript">
$(document).ready(function(){
    /*
     * Tax Head save
     */
    $('#taxheaddetailsform').parsley();
    var options = {
        success : function(responseText, statusText, xhr, $form) {
            var response = JSON.parse(responseText);
            if (response.success) {
                alert(response.message);
                var taxHeadKey = response.taxHeadKey;
                var taxHeadKeyValue = response.taxHeadKeyValue;
                if(taxHeadKey && taxHeadKeyValue && $('#empsetuptaxation #tax_heads_'+taxHeadKey)){
                    $('#empsetuptaxation #tax_heads_'+taxHeadKey).val(taxHeadKeyValue);
                }
                $('#modalShowTaxHeadDetailForm').modal('hide');
            } else {
                alert('Something wrong happened!');
            }
        }
    };

    // bind to the form's submit event
    $('#taxheaddetailsform').submit(function() {
        if($('#taxheaddetailsform #emp_pkey').val() == 0){
            alert('Please fill personal informations first!');
        }else{
            $(this).ajaxSubmit(options);
        }
        return false;
    });
    //Ends  
});
</script>
<div class="modal-body">
<!-- Form Name -->
<legend><?php echo $tax_head_name; ?></legend>
<form class="form-horizontal" method="post" action="Taxation/saveemployeetaxheaddetails" id="taxheaddetailsform">
    <div class="modal-body">
        <input id="emp_pkey" name="emp_pkey" type="hidden"  value="<?php echo $emp_pkey; ?>" >
        <input id="tax_heads_fkey" name="tax_heads_fkey" type="hidden"  value="<?php echo $tax_heads_fkey; ?>" >
        
        <?php $textFieldFound = false; ?>
        <?php if(!empty($arr_taxheaddetails)){
            $index = 0;
            foreach($arr_taxheaddetails as $key => $value){
                $index++;
                $fieldType = $value['fieldtype'];
                $fieldid = 'tax_head_detail_'.$value['tax_heads_details_pkey'];
                $fieldname = $value['tax_heads_details'];
                $fieldDesc = ($value['tax_heads_details1'] != '')?'('.$value['tax_heads_details1'].')':'';
                $fieldvalue = isset($arr_emptaxtransactions[$value['tax_heads_details_pkey']])?$arr_emptaxtransactions[$value['tax_heads_details_pkey']]:'';
                    
                /*if($index % 2 != 0){
                    //start new row
                    echo '<div class="form-group"><div class="col-md-6">';
                            if($fieldType == 1){
                                //Textfield
                                $textFieldFound = true;
                                echo '<label class="col-md-4 control-label" for="'.$fieldid.'">'.$fieldname.$fieldDesc.'</label>';
                                echo '<div class="col-md-8">';
                                echo '<input id="'.$fieldid.'" name="'.$fieldid.'" value="'.$fieldvalue.'" type="text" placeholder="'.$fieldname.'" class="form-control input-md" >';
                                echo '</div>';
                            }else{
                                //Label field
                                echo '<label class="col-md-12 control-label">'.$fieldname.$fieldDesc.'</label>';
                            }
                    echo '</div>';
                    if($index == count($arr_taxheaddetails)){
                        //End last row
                        echo '</div>'; 
                    }
                }else{
                    //End current row
                    echo '<div class="col-md-6">';
                    if($fieldType == 1){
                        //Textfield
                        $textFieldFound = true;
                        echo '<label class="col-md-4 control-label" for="'.$fieldid.'">'.$fieldname.$fieldDesc.'</label>';
                        echo '<div class="col-md-8">';
                        echo '<input id="'.$fieldid.'" name="'.$fieldid.'" value="'.$fieldvalue.'" type="text" placeholder="'.$fieldname.'" class="form-control input-md" >';
                        echo '</div>';
                    }else{
                        //Label field
                        echo '<label class="col-md-12 control-label">'.$fieldname.$fieldDesc.'</label>';
                    }
                    echo '</div>';
                    echo '</div>';
                }*/
                
                echo '<div class="form-group">';
                    echo '<div class="col-md-12">';
                    if($fieldType == 1){
                        //Textfield
                        $textFieldFound = true;
                        echo '<label style="text-align:left;" class="col-md-6 control-label" for="'.$fieldid.'">'.$fieldname.$fieldDesc.'</label>';
                        echo '<div class="col-md-6">';
                        echo '<input id="'.$fieldid.'" name="'.$fieldid.'" value="'.$fieldvalue.'" type="text" placeholder="'.$fieldname.'" class="form-control input-md" >';
                        echo '</div>';
                    }else{
                        //Label field
                        echo '<label style="text-align:left;" class="col-md-12 control-label">'.$fieldname.$fieldDesc.'</label>';
                    }
                    echo '</div>';
                echo '</div>';                
            }
        }else{
            echo 'No details found';
        } ?>
        
    </div>
    <?php if($textFieldFound){ ?>                
    <div class="modal-footer">
        <button type="button" class="btn btn-default" onclick="$('#modalShowTaxHeadDetailForm').modal('hide');">Cancel</button>
        <button type="submit" id="btn-submit" class="btn btn-primary">Save</button>
    </div>
    <?php }else{ ?>
    <div class="modal-footer">   
        <button type="button" class="btn btn-default" onclick="$('#modalShowTaxHeadDetailForm').modal('hide');">Cancel</button>
    </div>
    <?php } ?>
</form>
</div>