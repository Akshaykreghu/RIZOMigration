
		<script>
			
			

$(document).ready(function() {

    
    //appMaster.placeHold();
   // alert("sjadasj")
   

    $('#regform').bootstrapValidator({
        // To use feedback icons, ensure that you use Bootstrap v3.1.0 or later
      
        fields: {
            username: {
                message: 'The username is not valid',
                validators: {
                    notEmpty: {
                        message: 'The username is required and cannot be empty'
                    },
                    stringLength: {
                        min: 4,
                        max: 30,
                        message: 'The username must be more than 6 and less than 30 characters long'
                    },
                    regexp: {
                        regexp: /^[a-zA-Z0-9]+$/,
                        message: 'The username can only consist of alphabetical and number'
                    },
                    remote: {
                        message: 'The username is not available',
                        url: 'Site/checkusername'
                    }
                    
                }
            },
            firstname:{
            	 message: 'The first name is not valid',
                 validators: {
                    notEmpty: {
                        message: 'The first name is required and cannot be empty'
                    }
                   }
            	
            },
           currency:{
            	 message: 'The currency is not valid',
                 validators: {
                    notEmpty: {
                        message: 'Please select currency'
                    }
                   }
            	
            },
          country:{
            	 message: 'The country is not valid',
                 validators: {
                    notEmpty: {
                        message: 'Please select country'
                    }
                   }
            	
            },
            lastname:{
            	 message: 'The last name is not valid',
                 validators: {
                    notEmpty: {
                        message: 'The last name is required and cannot be empty'
                    }
                   }
            	
            },
            companyname:{
            	 message: 'The company name is not valid',
                 validators: {
                    notEmpty: {
                        message: 'The company name is required and cannot be empty'
                    }
                   }
            	
            },
            mobile:{
            	 message: 'The mobile is not valid',
                 validators: {
                 	digits: {
                        message: 'The phone number can contain digits only'
                    },
                    notEmpty: {
                        message: 'The mobile is required and cannot be empty'
                    }
                   }
            	
            },
            email: {
                validators: {
                    notEmpty: {
                        message: 'The email address is required and cannot be empty'
                    },
                    emailAddress: {
                        message: 'The email address is not a valid'
                    }
                }
            },
            
            policy: {
                validators: {
                    notEmpty: {
                        message: 'Please accept term and condition'
                    }
                }
            }
        }
    }).on('success.form.bv', function(e) {
            // Prevent form submission
            e.preventDefault();

            // Get the form instance
            var $form = $(e.target);

            // Get the BootstrapValidator instance
            var bv = $form.data('bootstrapValidator');

            // Use Ajax to submit form data
            $.post("Site/register", $form.serialize(), function(result) {
                // ... Process the result ..
                $("regform").reset();
                if(result.saved == true)
                {
                	$("regform").reset();
                }
            }, 'json');
          //  $form.clear();
            //$form.reset();
        });


});

		</script>
		
 <div class="form-contrainer text-center">
 	
 	
 	
 	
 	
 		<div role="tabpanel" id="signtabpanel">

  <!-- Nav tabs -->
  <ul class="nav nav-tabs" role="tablist" id="signtab">
    <li role="presentation" class="active"><a href="#signintab" aria-controls="signintab" role="tab" data-toggle="tab">Sign In</a></li>
    <li role="presentation"><a href="#signuptab2" aria-controls="signuptab2" role="tab" data-toggle="tab">Register</a></li>
  </ul>

  <!-- Tab panes -->
  <div class="tab-content">
    <div role="tabpanel" class="tab-pane active" id="signintab">
    	<form role="form"  style="float: left" method="post" action="<?php echo $this->webroot ?>Site/login">
		  <h4>Sign In </h4>
                          
                            <div class="col-lg-12 col-md-12 col-sm-12" style="float: left">
                             <?php 
							         if(isset($messages) && count($messages) > 0){
							         ?>
                            	<div  class="text-danger wrapper text-center ">
							        <?php foreach ($messages as $key => $value) {
										?>
										<div><?php echo $value; ?></div>
										<?php
									} ?>
							      </div>
							      <?php } ?>
                                    <div class="form-group">
                                <input type="text" class="form-control" name="user_id"  id="user_id" required="required" placeholder="User Name">
                                     </div>
                                      <div class="form-group">
                                     <input type="password" class="form-control" name="password" id="password" required="required" placeholder="Password">
                                    </div>
                                    <div class="form-group">
                                       <label class="radio-inline">
										  <input type="radio" name="user_group" id="user_group1" value="1"> Admin
										</label>
										<label class="radio-inline">
										  <input type="radio" name="user_group" id="user_group2" value="2"> Employee
										</label>
                                        </div>
                                   
                                      <div class="form-group">
                                    <button type="submit" class="btn btn-primary btn-lg">Sign In</button>
                                          </div>
                                   </div>
                                </form>
                                
                                </div>
    <div role="tabpanel" class="tab-pane" id="signuptab2">
	
	<form role="form" id="regform"  style="float: left">
		 <h4>Register </h4>
                          
                            <div class="col-lg-12 col-md-12 col-sm-12" style="float: left">
                                    <div class="form-group">
                                        <input type="text" class="form-control" name="firstname" id="lastname" placeholder="First Name">
                                    </div>
                                      <div class="form-group">
                                        <input type="text" class="form-control"  name="lastname" id="lastname" placeholder="Last Name">
                                    </div>
                                    <div class="form-group">
                                        <input type="email" class="form-control" name="email" id="email" placeholder="Email">
                                        </div>
                                    <div class="form-group">
                                        <input type="text" class="form-control" name="mobile" id="mobile" placeholder="Mobile">
                                    </div>
                                      <div class="form-group">
                                        <input type="text" class="form-control" placeholder="Company Name" name="companyname" id="companyname">
                                    </div>
                                     <div class="form-group">
                                     	<select name="country" class="form-control" id="country">
                                     		
                                     		<option value="">--Select Country-- </option>
                                     		<?php foreach ($countries as $key => $value) {
                                     			 ?>
												 <option value="<?php echo $key; ?>"><?php echo $value; ?></option>
										<?php		 
											 } ?>
                                     	</select>
                                     	
                                       
                                    </div>
                                     <div class="form-group">
                                    
										<select name="currency" class="form-control" id="currency">
                                     		<option value="">--Select Currency-- </option>
                                     		<?php foreach ($currencies as $key => $value) {
                                     			 ?>
												 <option value="<?php echo $key; ?>"><?php echo $value; ?></option>
										<?php		 
											 } ?>
                                     	</select>
                                    </div>
                                      <div class="form-group">
                                        <input type="text" class="form-control" placeholder="Desired User ID" name="username" id="username">
                                    </div>
                                      <div class="form-group">
                                   <label class="checkbox-inline" style="margin-left: 5px">
                                     <input type="checkbox" id="policy" name="policy" ><i></i> Agree the <a href>terms and policy</a></label>
                                      <button type="submit" class="btn btn-primary btn-lg">Register</button>
                                          </div>
                                   </div>
                                </form>
	</div>
  </div>

</div>
                        </div>