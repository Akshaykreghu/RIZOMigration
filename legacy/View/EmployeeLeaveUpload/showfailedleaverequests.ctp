<div class="modal-header">
    <h4 class="modal-title"><strong><?php echo $title; ?></strong></h4>
</div>
<div class="modal-body">
    <p>Total leave requests imported : <?php echo $importedCount; ?></p>
    
    <?php if(count($arrFailedLeaveRequests) > 0){ ?>
    <p>The following leave requests not approved.</p>
    <table class="table">
      <thead>
        <tr>
          <th>Employee Name</th>
          <th>Error Type</th>
          <th>Leave Start Date</th>
          <th>Leave End Date</th>
          <th>Reason</th>
        </tr>
      </thead>
      <tbody>
          <?php foreach($arrFailedLeaveRequests as $leaveRequest){ ?>
            <tr>
              <td><?php echo $leaveRequest['ed']['first_name'].' '.$leaveRequest['ed']['last_name']; ?></td>
              <td><?php echo $leaveRequest['ue']['Type']; ?></td>
              <td><?php echo $leaveRequest['ue']['start_date']; ?></td>
              <td><?php echo $leaveRequest['ue']['end_date']; ?></td>
              <td><?php echo $leaveRequest['ue']['textd']; ?></td>
            </tr>
          <?php } ?>
      </tbody>
    </table>
    <?php } ?>
</div>
<div class="modal-footer">
    <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
</div>