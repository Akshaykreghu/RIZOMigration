<form id="form-company-contact-info" name="form-company-contact-info" method="post" action="" enctype="multipart/form-data">
						<div class="row">
							<div class="control-group span5">
								<label class="control-label" for="business_name">Business Name</label>
								<div class="controls">
									<input required type="text" id="business_name" name="business_name" value="<?php echo isset($arr_comp_contact_info['business_name']) ? $arr_comp_contact_info['business_name'] : ''; ?>" />

								</div>
							</div>

							<div class="control-group span5">
								<label class="control-label" for="business_name">Nature Of Business </label>
								<div class="controls">
									<input required type="text" id="business_nature" name="business_nature" value="<?php echo isset($arr_comp_contact_info['business_nature']) ? $arr_comp_contact_info['business_nature'] : ''; ?>" />
								</div>
							</div>

						</div>

						<div class="row">
							<div class="control-group span5">
								<label class="control-label" for="business_name">Type Of Business </label>
								<div class="controls">
									<input required type="text" id="business_type" name="business_type" value="<?php echo isset($arr_comp_contact_info['business_type']) ? $arr_comp_contact_info['business_type'] : ''; ?>" />

								</div>
							</div>

							<div class="control-group span5">
								<label class="control-label" for="business_name">Address</label>
								<div class="controls">
									<input required type="text" id="address" name="address" value="<?php echo isset($arr_comp_contact_info['address']) ? $arr_comp_contact_info['address'] : ''; ?>" />

								</div>
							</div>

						</div>

						<div class="row">
							<div class="control-group span5">
								<label class="control-label" for="business_name">City</label>
								<div class="controls">
									<input required type="text" id="city" name="city" value="<?php echo isset($arr_comp_contact_info['city']) ? $arr_comp_contact_info['city'] : ''; ?>" />
								</div>
							</div>

							<div class="control-group span5">
								<label class="control-label" for="business_name">Pincode</label>
								<div class="controls">
									<input required type="text" id="pincode" name="pincode" value="<?php echo isset($arr_comp_contact_info['pincode']) ? $arr_comp_contact_info['pincode'] : ''; ?>" />
								</div>
							</div>
						</div>

						<div class="row">

							<div class="control-group span5">
								<label class="control-label" for="business_name">State</label>
								<div class="controls">
									<input required type="text" id="state" name="state" value="<?php echo isset($arr_comp_contact_info['state']) ? $arr_comp_contact_info['state'] : ''; ?>" />

								</div>
							</div>

							<div class="control-group span5">
								<label class="control-label" for="business_name">Phone</label>
								<div class="controls">
									<input required type="text" id="phone" name="phone" value="<?php echo isset($arr_comp_contact_info['phone']) ? $arr_comp_contact_info['phone'] : ''; ?>" />
								</div>
							</div>
						</div>

						<div class="row">
							<div class="control-group span5">
								<label class="control-label" for="business_name">Fax</label>
								<div class="controls">
									<input type="text" id="fax" name="fax" value="<?php echo isset($arr_comp_contact_info['fax']) ? $arr_comp_contact_info['fax'] : ''; ?>" />
								</div>
							</div>

							<div class="control-group span5">
								<label class="control-label" for="business_name">Email</label>
								<div class="controls">
									<input type="text" id="email" name="email" value="<?php echo isset($arr_comp_contact_info['email']) ? $arr_comp_contact_info['email'] : ''; ?>" />
								</div>
							</div>

						</div>

						<div class="row">
							<div class="control-group span5">
								<label class="control-label" for="business_name">Website</label>
								<div class="controls">
									<input type="text" id="website" name="website" value="<?php echo isset($arr_comp_contact_info['website']) ? $arr_comp_contact_info['website'] : ''; ?>" />
								</div>
							</div>

							<div class="control-group span5">
								<label class="control-label" for="business_name">Logo</label>
								<div class="controls">
									<?php if(count($arr_comp_contact_info)){ ?>
									<img width="100px" height="50px;" src="<?php echo $this -> webroot; ?>files/company-logos/<?php echo(isset($arr_comp_contact_info['logo']) && $arr_comp_contact_info['logo'] != '') ? $arr_comp_contact_info['logo'] : 'logo.png'; ?>" />
									<input type="file" id="logo" name="logo" />
									<?php }else{ ?>
									<input type="file" id="logo" name="logo" />
									<?php } ?>
								</div>
							</div>
						</div>

						<div class="control-group">
							<div class="controls">

								</label>

								<input onclick="saveCompanySetup('CompanyContactInfo')" type="button" id="btn-save-company-contact-info" name="btn-save-company-contact-info" value="Save" />

							</div>
						</div>

					</form>