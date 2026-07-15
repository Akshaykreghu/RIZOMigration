
<style type="text/css">
    td.negative { color : red; }
</style>

<h1 class="page-header" style="text-align:center">Attendance Report</h1>
<div class="col-md-4">
    <label class="col-md-5 control-label" for="filterby_month">Choose Month</label>
    <div class="col-md-7">
        <select id="filterby_month" name="filterby_month" class="form-control"  >
            <?php
            for ($i = 0; $i < 10; $i++) {
                echo '<option value="' . date('Y-m', strtotime("-$i month", strtotime(date('M-Y')))) . '">' . date('M-Y', strtotime("-$i month", strtotime(date('M-Y')))) . '</option>';
            }
            ?>
        </select>
    </div>
</div>
<br/>
<div id="attendancetable"></div>
<script language="JavaScript" type="text/javascript">
        $(document).ready(function () {
        $('#filterby_month').on('change', function () {
            var m = $(this).val();
            attendance(m);
        });
        var month = $('#filterby_month').val();
        attendance(month);
    });
function attendance(month) {
        $("#attendancetable").load("Empreport/attendance", {attmonth: month});
    }
</script>


