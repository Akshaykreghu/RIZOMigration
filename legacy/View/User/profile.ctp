<style>
.box {
    border: 1px solid #d2d6de!important;
}

  .heading {
    display: flex;
    flex-direction: row;
    align-items: end;
    justify-content: space-between;
    margin: 0 0 0 15px;

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

  /* edited by bindu 22-08-25 */
</style>
  <!-- /* edited by bindu 22-08-25 */ -->
   <section class="content-header heading">
  <h1 class="text-primary-18">User Profile</h1>
  <?php if ($plan !='basic'){ ?>
    <div class="text-primary-16 home" style="display:flex; align-items:center; gap:10px; cursor:pointer;">
        <i class="fa" style="font-size:16px;">&#xf104;</i>
        Back
    </div>
    <?php } ?>
    </section>
  <!-- end -->
<section class="content">
	  <div class="row">
            <div class="col-md-12">
              <!-- Custom Tabs (Pulled to the right) -->
              <div class="nav-tabs-custom">
                <ul class="nav nav-tabs">
                  <li class="active"><a href="#tab_1-1" data-toggle="tab">Profile</a></li>
                  <li><a href="#tab_2-2" data-toggle="tab">Settings</a></li>
                  <li><a href="#tab_3-3" data-toggle="tab">Plans</a></li>
                 
                </ul>
                <div class="tab-content">
                  <div class="tab-pane active" id="tab_1-1">
                    
                    <div class="row">
                        <?php
                        if($user_group == '2')
                        {
                        ?>
                    	<div class="col-sm-3">
                    		<div class="box" style="border-top: 1px solid #d2d6de !important;">
 
     <div class="box-body">
     	
     	
     	
     	
                                        <div class="profile_img">

                                            <!-- end of image cropping -->
                                            <div id="crop-avatar">
                                                <!-- Current avatar -->
                                                <form method="post" enctype="multipart/form-data" >
                                                     <input type="file" id="avatarfile" name="avatarfile" />
                                             </form>

                                                <!-- Cropping modal -->
                                                
                                                <!-- /.modal -->

                                                <!-- Loading state -->
                                                <div class="loading" aria-label="Loading" role="img" tabindex="-1"></div>
                                            </div>
                                            <h3><?php echo $this->Session->read('user_name'); ?></h3>
                                            

                                        </div>
     	
     	</div>

</div>
                    	</div> 
                        <?php
                        }
                        ?>
                    		<div class="col-sm-8">
     
     	
     	
     	

          <!-- Widget: user widget style 1 -->
          <div class="box box-widget widget-user-2" style="background:#2e7695;">
            <!-- Add the bg color to the header using any of the bg-* classes -->
            <div class="widget-user-header ">
             
              <!-- /.widget-user-image -->
              <h2 class="widget-user-username" style="padding-left:37px;color:#fff;"><?php echo $user['first_name']." ".$user['last_name']; ?></h2>
              <!--<h5 class="widget-user-desc" style="padding-left:37px;"><?php echo $user['user_id']; ?></h5>-->
            </div>
            <div class="box-footer no-padding">
              <ul class="nav nav-stacked">
			  <?php
                if($contactinfo['logosize']=='1') { ?>
                     <img style="  height: 200px; width: 200px;margin-top: 20px;" src="<?php echo   $this->webroot.$contactinfo['logo']; ?>" class="file-preview-image center-block" alt="<?php echo $contactinfo['business_name']; ?>" title="<?php echo $contactinfo['business_name']; ?>">
                  <?php  }
                  else { ?>
                  <img style="  height: 100px; width: 200px;margin-top: 20px;" src="<?php echo   $this->webroot.$contactinfo['logo']; ?>" class="file-preview-image center-block" alt="<?php echo $contactinfo['business_name']; ?>" title="<?php echo $contactinfo['business_name']; ?>">
                <?php } ?>
                <li><a href="#">Company Name : <b><?php echo $user['first_name']." ".$user['last_name']; ?> </b></a></li>
				<li><a href="#">Type of Business : <b><?php echo isset($contactinfo['business_type']) ? $contactinfo['business_type'] : '' ?> </b></a></li>
				<li><a href="#">Nature Of Business : <b><?php echo isset($contactinfo['business_nature']) ? $contactinfo['business_nature'] : '' ?> </b></a></li>
                <li><a href="#">Email :  <b><?php echo isset($contactinfo['email']) ? $contactinfo['email'] : '' ?></b></a></li>
                <li><a href="#">Mobile Number :  <b><?php echo isset($contactinfo['phone']) ? $contactinfo['phone'] : '' ?></b></a></li>
                <li><a href="#">Address :  <b><?php echo isset($contactinfo['address']) ? $contactinfo['address'] : '' ?>, <?php echo isset($contactinfo['city']) ? $contactinfo['city'] : '' ?>, <?php echo isset($contactinfo['state']) ? $contactinfo['state'] : '' ?>  <?php echo isset($contactinfo['pincode']) ? " - ".$contactinfo['pincode'] : '' ?></b></a></li>
              </ul>
            </div>
          </div>
          <!-- /.widget-user -->
        
                   	
                   		
                   		
                   		
                   

     	
     	
                    	</div>
                    </div>
                    
                    
                  </div><!-- /.tab-pane -->
                  <div class="tab-pane" id="tab_2-2">
				  <?php
//                        if($user_group == '2')
//                        {
                        ?>
                   <div class="row">
                   	<div class="col-sm-12">
                   	<fieldset>
                   		<legend> Password Change</legend>
<!--              <div id="errorcontainer">
              	
              </div>-->
                   		<form class="form-horizontal" id="changepassword" method="post" action="<?php echo $this->webroot ?>User/savePassword">
                    			  <div class="form-group">
                      <label for="password" class="col-sm-2 control-label">Current Password  </label>
                      <div class="col-sm-4">
                        <input type="password" class="form-control" id="password" autocomplete="off" name="password" required=""><a class="togglepassword" title="Show Password " style="position : absolute;top: 5px;right: 25px; " ><li class="fa fa-eye"></li></a>
                      </div>
                    </div>
                    <div class="form-group">
                      <label for="password1" class="col-sm-2 control-label">New Password  </label>
                      <div class="col-sm-4">
                          <input type="password"  data-validation="strength" data-validation-strength="2"  class="form-control" id="password1" autocomplete="off" name="password1" required="" min="6"><a class="togglepassword" title="Show Password " style="position : absolute;top: 5px;right: 25px; " ><li class="fa fa-eye"></li></a>
                   
                      </div>
                    </div>
                     <div class="form-group">
                      <label for="password2" class="col-sm-2 control-label">Confirm Password </label>
                      <div class="col-sm-4">
                          <input type="password" class="form-control" id="password2" autocomplete="off" name="password2" required="" min="6" data-validation="confirmation"  data-validation-confirm="password1">
                          <a class="togglepassword" title="Show Password " style="position : absolute;top: 5px;right: 25px; " ><li class="fa fa-eye"></li></a>
                      </div>
                    </div>
                     <div class="form-group">
                     	 <div class="col-sm-6">
                        <button type="submit" class="btn btn-info pull-right">Update</button>
                        </div>
                          </div>
                    	</form>
                   	</fieldset>
                   		
                   		
                   		
                   		
                   	</div>
                   	
                   </div>
<?php
                      //   }
                        ?>
<div class="row">
                   	<div class="col-sm-12">
                   	<fieldset>
                   		<legend>Profile Name Change</legend>
             <!-- <div id="errorcontainer">
              	
              </div> -->
                   		<form class="form-horizontal" id="changeName" method="post" action="<?php echo $this->webroot ?>User/saveNames">
                    			  
                    
                     
                                    <div class="form-group">
                      <label for="first_name" class="col-sm-2 control-label">First Name</label>
                      <div class="col-sm-4">
                          <input type="text" class="form-control" value="<?php echo isset($user['first_name'])?$user['first_name']: '' ; ?>" id="password2" autocomplete="off" name="first_name" >
                   
                      </div>
                    </div>
					<?php
                        if($user_group == '2')
                        {
                        ?>
                                    <div class="form-group">
                      <label for="middle_name" class="col-sm-2 control-label">Middle Name</label>
                      <div class="col-sm-4">
                          <input type="text" class="form-control" value="<?php echo isset($user['middle_name'])?$user['middle_name']: '' ; ?>" id="password2" autocomplete="off" name="middle_name" >
                   
                      </div>
                    </div>
					<?php
					}
					?>
                                    <div class="form-group">
                      <label for="last_name" class="col-sm-2 control-label">Last Name</label>
                      <div class="col-sm-4">
                          <input type="text" class="form-control" value="<?php echo isset($user['last_name'])?$user['last_name']: '' ; ?>" id="password2" autocomplete="off" name="last_name" >
                   
                      </div>
                    </div>
                     <div class="form-group">
                     	 <div class="col-sm-6">
                        <button type="submit" class="btn btn-info pull-right">Update</button>
                        </div>
                          </div>
                    	</form>
                   	</fieldset>
                   		
                   		
                   		
                   		
                   	</div>
                   	
                   </div>
<!--div class="row">
                   	<div class="col-sm-12">
                   	<fieldset>
                   		<legend> Inbox Setting</legend>
                   		<form class="form-horizontal" method="post" action="<?php echo $this->webroot ?>User/saveInboxSetting">
                    			  <div class="form-group">
                      <label for="password" class="col-sm-2 control-label">Mail Protocol</label>
                      <div class="col-sm-4">
                        <input type="text" class="form-control" id="smtp_protocol" autocomplete="off" name="smtp_protocol">
                      </div>
                    </div>
                    <div class="form-group">
                      <label for="password1" class="col-sm-2 control-label">SMTP Hostname</label>
                      <div class="col-sm-4">
                          <input type="text" class="form-control" id="smtp_hostname" autocomplete="off" name="smtp_hostname">
                   
                      </div>
                    </div>
                     <div class="form-group">
                      <label for="password2" class="col-sm-2 control-label">SMTP Username</label>
                      <div class="col-sm-4">
                          <input type="text" class="form-control" id="smtp_username" autocomplete="off" name="smtp_username">
                   
                      </div>
                    </div>
                      <div class="form-group">
                      <label for="password2" class="col-sm-2 control-label">SMTP Password</label>
                      <div class="col-sm-4">
                          <input type="password" class="form-control" id="smtp_password" autocomplete="off" name="smtp_password">
                   
                      </div>
                    </div>
                    
                      <div class="form-group">
                      <label for="password2" class="col-sm-2 control-label">SMTP Port</label>
                      <div class="col-sm-4">
                          <input type="text" class="form-control" id="smtp_port" autocomplete="off" name="smtp_port">
                   
                      </div>
                    </div>
                      <div class="form-group">
                      <label for="password2" class="col-sm-2 control-label">SMTP Timeout</label>
                      <div class="col-sm-4">
                          <input type="text" class="form-control" id="smtp_timeout" autocomplete="off" name="smtp_timeout">
                   
                      </div>
                    </div>
                     <div class="form-group">
                     	 <div class="col-sm-6">
                        <button type="submit" class="btn btn-info pull-right">Save</button>
                        </div>
                          </div>
                    	</form>
                   	</fieldset>
                   		
                   		
                   		
                   		
                   	</div>
                   	
                   </div-->



                  </div><!-- /.tab-pane -->
                  <div class="tab-pane" id="tab_3-3">
                
                  </div><!-- /.tab-pane -->
                </div><!-- /.tab-content -->
              </div><!-- nav-tabs-custom -->
            </div><!-- /.col -->
          </div>

</section>


<script>
	// $('#changepassword').parsley();
	jQuery(document).ready(function() {
        $('.togglepassword').click(function () {
            var element = $(this).parent().closest('div').find('input');
            //alert($(element).attr('type'));
            if ($(element).attr('type') == 'password') {
                $(element).attr('type', "text");
            } else {
                $(element).attr('type', "password");
            }
        });    
            
            
            
		$("#avatarfile").fileinput({
		<?php if(isset($user['avatar']) && $user['avatar'] != null){ ?>
			 initialPreview: [
					'<img width="140px" height="104px" src="<?php echo $this->webroot. $user['avatar']; ?>" class="file-preview-image" alt="<?php echo $user['first_name']; ?>" title="<?php echo $user['first_name'] ?>" ; >',
				],
		<?php } ?>
		    uploadUrl:livesite+"User/saveavatar",
			showCaption: false,
			showClose:false,
			allowedFileTypes:['jpg', 'gif', 'png'],
			allowedPreviewTypes:['jpg', 'gif', 'png'],
			maxFileCount:1,
			dropZoneEnabled:false,
            
		});
  });
  $.validate({
  modules : 'security',
  onModulesLoaded : function() {
    var optionalConfig = {
      fontSize: '12pt',
      padding: '4px',
      bad : 'Very bad',
      weak : 'Weak',
      good : 'Good',
      strong : 'Strong'
    };

    $('input[name="pass"]').displayPasswordStrength(optionalConfig);
  }
});
    var options = { 
      //  target:        '#output2',   // target element(s) to be updated with server response 
      //  beforeSubmit:  showRequest,  // pre-submit callback 
        success:       function(resp){
      // 	console.log($.parseJSON(resp).msg)
       	resp = $.parseJSON(resp);
          var errorMsg = 		'<div class="alert alert-danger alert-dismissible" id="passworderror"  role="alert">'
  '<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>'+
 '<span id="passworderrormsg">'+resp.msg+'</span>'+
'</div>';
       	  $("#errorcontainer").html(resp.msg);
$.notify(resp.msg,{
                                                   type: 'success',
                                                   allow_dismiss: false
                                               });		  
       
       /*
           $.notify($.parseJSON(resp).msg,{
                                                   type: 'success',
                                                   allow_dismiss: false
                                                                                                                                                                  });*/
       
       } , // post-submit callback 
 
        // other available options: 
        //url:       url         // override for form's 'action' attribute 
        //type:      type        // 'get' or 'post', override for form's 'method' attribute 
        //dataType:  null        // 'xml', 'script', or 'json' (expected server response type) 
       // clearForm: true,        // clear all form fields after successful submit 
       // resetForm: true        // reset the form after successful submit 
 
        // $.ajax options can be used here too, for example: 
        //timeout:   3000 
    }; 
 
    // bind to the form's submit event 
    $('#changepassword').submit(function() { 
        // inside event callbacks 'this' is the DOM element so we first 
        // wrap it in a jQuery object and then invoke ajaxSubmit 
        $(this).ajaxSubmit(options); 
 
        // !!! Important !!! 
        // always return false to prevent standard browser submit and page navigation 
        return false; 
    });
        $('#changeName').submit(function() { 
        // inside event callbacks 'this' is the DOM element so we first 
        // wrap it in a jQuery object and then invoke ajaxSubmit 
        $(this).ajaxSubmit(options); 
 
        // !!! Important !!! 
        // always return false to prevent standard browser submit and page navigation 
        return false; 
    });
      /* edited by bindu 22-08-25 */
  /* edited by bindu 19-02-26 */
    $(".home").on("click", function () {

        $("#container").isLoading({
            text: "Loading",
            position: "overlay",
        });

        let url = "";
        var userGroup = <?php echo json_encode($userGroup); ?>

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
  /* edited by bindu 22-08-25 */
</script>