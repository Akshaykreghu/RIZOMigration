<form id="form-emp-personal-profile" name="form-emp-personal-profile" method="post" action="" enctype="multipart/form-data">
	<div class="row">
		<div class="control-group span5">
			<label class="control-label" for="first_name">First Name<span style="color: red;">*</span> </label>
			<div class="controls">
				<input required type="text" id="first_name" name="first_name" value="<?php echo isset($arr_emp_personal_profile['first_name']) ? $arr_emp_personal_profile['first_name'] : ''; ?>" />
			</div>
		</div>
		<div class="control-group span5">
			<label class="control-label" for="middle_name">Middle Name </label>
			<div class="controls">
				<input required type="text" id="middle_name" name="middle_name" value="<?php echo isset($arr_emp_personal_profile['middle_name']) ? $arr_emp_personal_profile['middle_name'] : ''; ?>" />
			</div>
		</div>
	</div>

	<div class="row">
		<div class="control-group span5">
			<label class="control-label" for="last_name">Last Name<span style="color: red;">*</span> </label>
			<div class="controls">
				<input required type="text" id="last_name" name="last_name" value="<?php echo isset($arr_emp_personal_profile['last_name']) ? $arr_emp_personal_profile['last_name'] : ''; ?>" />
			</div>
		</div>
		<div class="control-group span5">
			<label class="control-label" for="classification">Gender<span style="color: red;">*</span> </label>
			<div class="controls">
				<select required name="classification" id="classification">
					<option value="">--Select--</option>
					<option value="male" <?php echo isset($arr_emp_personal_profile['classification']) && $arr_emp_personal_profile['classification'] == 'male' ? 'selected' : ''; ?>>Male</option>
					<option value="female" <?php echo isset($arr_emp_personal_profile['classification']) && $arr_emp_personal_profile['classification'] == 'female' ? 'selected' : ''; ?>>Female</option>
				</select>
			</div>
		</div>
	</div>
	<div class="row">
		<div class="control-group span5">
			<label class="control-label" for="address">Address </label>
			<div class="controls">
				<input required type="text" id="address" name="address" value="<?php echo isset($arr_emp_personal_profile['address']) ? $arr_emp_personal_profile['address'] : ''; ?>" />
			</div>
		</div>
		<div class="control-group span5">
			<label class="control-label" for="city">City </label>
			<div class="controls">
				<input required type="text" id="city" name="city" value="<?php echo isset($arr_emp_personal_profile['city']) ? $arr_emp_personal_profile['city'] : ''; ?>" />
			</div>
		</div>
	</div>
	<div class="row">
		<div class="control-group span5">
			<label class="control-label" for="pincode">Pincode </label>
			<div class="controls">
				<input required type="text" id="pincode" name="pincode" value="<?php echo isset($arr_emp_personal_profile['pincode']) ? $arr_emp_personal_profile['pincode'] : ''; ?>" />
			</div>
		</div>
		<div class="control-group span5">
			<label class="control-label" for="state">State</label>
			<div class="controls">
				<input required type="text" id="state" name="state" value="<?php echo isset($arr_emp_personal_profile['state']) ? $arr_emp_personal_profile['state'] : ''; ?>" />
			</div>
		</div>
	</div>
	<div class="row">
		<div class="control-group span5">
			<label class="control-label" for="mobile_no">Mobile No </label>
			<div class="controls">
				<input type="text" id="mobile_no" name="mobile_no" value="<?php echo isset($arr_emp_personal_profile['mobile_no']) ? $arr_emp_personal_profile['mobile_no'] : ''; ?>" />
			</div>
		</div>
		<div class="control-group span5">
			<label class="control-label" for="email">Email </label>
			<div class="controls">
				<input type="text" id="email" name="email" value="<?php echo isset($arr_emp_personal_profile['email']) ? $arr_emp_personal_profile['email'] : ''; ?>" />
			</div>
		</div>
	</div>
	<div class="row">
		<div class="control-group span5">
			<label class="control-label" for="maritual_status">Marital Status </label>
			<div class="controls">
				<select required name="maritual_status" id="maritual_status">
					<option value="">--Select--</option>
					<option value="single" <?php echo isset($arr_emp_personal_profile['maritual_status']) && $arr_emp_personal_profile['maritual_status'] == 'single' ? 'selected' : ''; ?>>Single</option>
					<option value="married" <?php echo isset($arr_emp_personal_profile['maritual_status']) && $arr_emp_personal_profile['maritual_status'] == 'married' ? 'selected' : ''; ?>>Married</option>
				</select>
			</div>
		</div>
		<div class="control-group span5">
			<label class="control-label" for="education">Education </label>
			<div class="controls">
				<select name="education" id="education">
					<option value="">--Select--</option>
					<option value="Graduate" <?php echo isset($arr_emp_personal_profile['education']) && $arr_emp_personal_profile['education'] == 'Graduate' ? 'selected' : ''; ?>>Graduate</option>
					<option value="Post-Graduate" <?php echo isset($arr_emp_personal_profile['education']) && $arr_emp_personal_profile['education'] == 'Post-Graduate' ? 'selected' : ''; ?>>Post-Graduate</option>
					<option value="Under-Graduate" <?php echo isset($arr_emp_personal_profile['education']) && $arr_emp_personal_profile['education'] == 'Under-Graduate' ? 'selected' : ''; ?>>Under-Graduate</option>
					<option value="No Education" <?php echo isset($arr_emp_personal_profile['education']) && $arr_emp_personal_profile['education'] == 'No Education' ? 'selected' : ''; ?>>No Education</option>
				</select>
			</div>
		</div>
	</div>
	<div class="row">
		<div class="control-group span5">
			<label class="control-label" for="date_of_birth">Birth Date (DD-MM-YYYY) </label>
			<div class="controls">
				<input id="date_of_birth" name="date_of_birth" type="date" value="<?php echo isset($arr_emp_personal_profile['date_of_birth']) ? $arr_emp_personal_profile['date_of_birth'] : ''; ?>" />
			</div>
		</div>
		<div class="control-group span5">
			<label class="control-label" for="bank_name">Bank Name </label>
			<div class="controls">
				<input type="text" id="bank_name" name="bank_name" value="<?php echo isset($arr_emp_personal_profile['bank_name']) ? $arr_emp_personal_profile['bank_name'] : ''; ?>" />
			</div>
		</div>
	</div>
	<div class="row">
		<div class="control-group span5">
			<label class="control-label" for="branch_name">Branch Name </label>
			<div class="controls">
				<input id="branch_name" name="branch_name" type="text" value="<?php echo isset($arr_emp_personal_profile['branch_name']) ? $arr_emp_personal_profile['branch_name'] : ''; ?>" />
			</div>
		</div>
		<div class="control-group span5">
			<label class="control-label" for="branch_address">Branch Address </label>
			<div class="controls">
				<input type="text" id="branch_address" name="branch_address" value="<?php echo isset($arr_emp_personal_profile['branch_address']) ? $arr_emp_personal_profile['branch_address'] : ''; ?>" />
			</div>
		</div>
	</div>
	<div class="row">
		<div class="control-group span5">
			<label class="control-label" for="ifsc_code">Bank IFSC Code </label>
			<div class="controls">
				<input id="ifsc_code" name="ifsc_code" type="text" value="<?php echo isset($arr_emp_personal_profile['ifsc_code']) ? $arr_emp_personal_profile['ifsc_code'] : ''; ?>" />
			</div>
		</div>
		<div class="control-group span5">
			<label class="control-label" for="account_no">Account No </label>
			<div class="controls">
				<input type="text" id="account_no" name="account_no" value="<?php echo isset($arr_emp_personal_profile['account_no']) ? $arr_emp_personal_profile['account_no'] : ''; ?>" />
			</div>
		</div>
	</div>
	<div class="row">
		<div class="control-group span5">
			<label class="control-label" for="pan_no">PAN No </label>
			<div class="controls">
				<input id="pan_no" name="pan_no" type="text" value="<?php echo isset($arr_emp_personal_profile['pan_no']) ? $arr_emp_personal_profile['pan_no'] : ''; ?>" />
			</div>
		</div>
	</div>
	<div class="control-group">
		<div class="controls">
			<input type="hidden" id="emp_pkey" name="emp_pkey" value="<?php echo $emp_pkey; ?>" />
			<input onclick="saveEmployeeSetup('EmployeeDetails');" type="button" id="btn-save-emp-personal-profile" name="btn-save-emp-personal-profile" value="Save" />
		</div>
	</div>
</form>