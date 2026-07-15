<form id="form-emp-professional-profile" name="form-emp-professional-profile" method="post" action="" enctype="multipart/form-data">
	<div class="row">
		<div class="control-group span5">
			<label class="control-label" for="joining_date">Joining Date<span style="color: red;">*</span> </label>
			<div class="controls">
				<input required type="date" id="joining_date" name="joining_date" value="<?php echo isset($arr_emp_professional_profile['joining_date']) ? $arr_emp_professional_profile['joining_date'] : ''; ?>" />
			</div>
		</div>
		<div class="control-group span5">
			<label class="control-label" for="emp_id">Employee ID </label>
			<div class="controls">
				<input readonly type="text" id="emp_id" name="emp_id" value="<?php echo isset($emp_id) ? $emp_id : ''; ?>" />
			</div>
		</div>
	</div>

	<div class="row">
		<div class="control-group span5">
			<label class="control-label" for="emp_type">Employee Type </label>
			<div class="controls">
				<select name="emp_type" id="emp_type">
			        <option value="Permanent" <?php echo isset($arr_emp_professional_profile['emp_type']) && $arr_emp_professional_profile['emp_type'] == 'Permanent' ? 'selected' : ''; ?>>Permanent</option>        
			        <option value="Contract" <?php echo isset($arr_emp_professional_profile['emp_type']) && $arr_emp_professional_profile['emp_type'] == 'Contract' ? 'selected' : ''; ?>>Contract</option>
			        <option value="Probation" <?php echo isset($arr_emp_professional_profile['emp_type']) && $arr_emp_professional_profile['emp_type'] == 'Probation' ? 'selected' : ''; ?>>Probation</option>
			        <option value="Part-Time" <?php echo isset($arr_emp_professional_profile['emp_type']) && $arr_emp_professional_profile['emp_type'] == 'Part-Time' ? 'selected' : ''; ?>>Part-Time</option>
			        <option value="Temporary" <?php echo isset($arr_emp_professional_profile['emp_type']) && $arr_emp_professional_profile['emp_type'] == 'Temporary' ? 'selected' : ''; ?>>Temporary</option>
			        <option value="Consultant" <?php echo isset($arr_emp_professional_profile['emp_type']) && $arr_emp_professional_profile['emp_type'] == 'Consultant' ? 'selected' : ''; ?>>Consultant</option>
			        <option value="other" <?php echo isset($arr_emp_professional_profile['emp_type']) && $arr_emp_professional_profile['emp_type'] == 'other' ? 'selected' : ''; ?>>Other Type</option>
				</select>
			</div>
		</div>
		<div class="control-group span5">
			<label class="control-label" for="designation">Designation </label>
			<div class="controls">
				<input type="text" id="designation" name="designation" value="<?php echo isset($arr_emp_professional_profile['designation']) ? $arr_emp_professional_profile['designation'] : ''; ?>" />
			</div>
		</div>
	</div>
	<div class="row">
		<div class="control-group span5">
			<label class="control-label" for="emp_dept">Department </label>
			<div class="controls">
				<select id="emp_dept" name="emp_dept">
					<option value="">--Select--</option>
					<?php if(isset($arr_departments)){
						foreach ($arr_departments as $key => $value) {
							$dept_name	=	$value["Departments"]["dept_name"];
							$dept_code	=	$value["Departments"]["dept_code"];
							echo '<option value="'.$dept_code.'" '.(($arr_emp_professional_profile['emp_dept'] == $dept_code)?"selected":"").'>'.$dept_name.'</option>';
						}
					} ?>
				</select>
			</div>
		</div>
		<div class="control-group span5">
			<label class="control-label" for="city">Grade </label>
			<div class="controls">
				<select id="grade_code" name="grade_code">
					<option value="">--Select--</option>
					<?php if(isset($arr_grades)){
						foreach ($arr_grades as $key => $value) {
							$grade_code	=	$value["Grades"]["grade_code"];
							$grade_name	=	$value["Grades"]["grade_name"];
							echo '<option value="'.$grade_code.'" '.(($arr_emp_professional_profile['emp_grade'] == $grade_code)?"selected":"").'>'.$grade_name.'</option>';
						}
					} ?>
				</select>
			</div>
		</div>
	</div>
	<div class="row">
		<div class="control-group span5">
			<label class="control-label" for="vert_code">Vertical </label>
			<div class="controls">
				<select id="vert_code" name="vert_code">
					<option value="">--Select--</option>
					<?php if(isset($arr_verticals)){
						foreach ($arr_verticals as $key => $value) {
							$vert_code	=	$value["Verticals"]["vert_code"];
							$vert_name	=	$value["Verticals"]["vertical_name"];
							echo '<option value="'.$vert_code.'" '.(($arr_emp_professional_profile['emp_vertical'] == $vert_code)?"selected":"").'>'.$vert_name.'</option>';
						}
					} ?>
				</select>
			</div>
		</div>
		<div class="control-group span5">
			<label class="control-label" for="state">Branch </label>
			<div class="controls">
				<select id="vert_code" name="vert_code">
					<option value="">--Select--</option>
					<?php if(isset($arr_branches)){
						foreach ($arr_branches as $key => $value) {
							$branch_name	=	$value["Units"]["branch_name"];
							echo '<option value="'.$branch_name.'" '.(($arr_emp_professional_profile['emp_branch'] == $branch_name)?"selected":"").'>'.$branch_name.'</option>';
						}
					} ?>
				</select>
			</div>
		</div>
	</div>
	<div class="control-group">
		<div class="controls">
			<input type="hidden" id="emp_proff_pkey" name="emp_proff_pkey" value="<?php echo $emp_proff_pkey; ?>" />
			<input onclick="saveEmployeeSetup('EmployeeProfessionalDetails');" type="button" id="btn-save-emp-professional-profile" name="btn-save-emp-professional-profile" value="Save" />
		</div>
	</div>
</form>