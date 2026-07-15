<div class="modal-header">
    <h4 class="modal-title"><strong><?php echo $title; ?></strong></h4>
</div>
<div class="modal-body">
    <p>Total employees imported : <?php echo $importedCount; ?></p>
    <?php if(count($arrDuplicateEmpList) > 0){ ?>
    <p>The following employees already exists, so not imported!</p>
    <table class="table">
      <thead>
        <tr>
          <th>First name</th>
          <th>Last name</th>
          <th>Date of birth</th>
        </tr>
      </thead>
      <tbody>
          <?php foreach($arrDuplicateEmpList as $employee){ ?>
            <tr>
              <td><?php echo $employee['EmployeeDetails']['first_name']; ?></td>
              <td><?php echo $employee['EmployeeDetails']['last_name']; ?></td>
              <td><?php echo $employee['EmployeeDetails']['date_of_birth']; ?></td>
            </tr>
          <?php } ?>
      </tbody>
    </table>
    <?php } ?>
    <?php if(count($arrerrors) > 0){ ?>
    <p>Please Check The Designation Code/Department Code ,The following employees are not imported!</p>
    <table class="table">
      <thead>
        <tr>
          <th>First name</th>
        </tr>
      </thead>
      <tbody>
          <?php foreach($arrerrors as $employee){ ?>
            <tr>
              <td><?php echo $employee; ?></td>
            </tr>
          <?php } ?>
      </tbody>
    </table>
    <?php } ?>
</div>
<div class="modal-footer">
    <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
</div>