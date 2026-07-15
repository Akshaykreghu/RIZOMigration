<section class="content">
<style type="text/css">
    td.negative { color : red; }
</style>

  <h1 class="page-header" style="text-align:center"><strong>ATTENDANCE REPORTsss</strong> </h1>
<div class="col-md-4">
    <label class="col-md-5 control-label" for="filterby_month">Choose Month</label>
    <div class="col-md-7">
        <select id="filterby_month" name="filterby_month" class="form-control"  >
            <option>--Choose Month--</option>
            <?php
            for ($i = 0; $i < 10; $i++) {
                echo '<option value="' . date('Y-m', strtotime("-$i month", strtotime(date('M-Y')))) . '">' . date('M-Y', strtotime("-$i month", strtotime(date('M-Y')))) . '</option>';
            }
            ?>
        </select>
    </div>
</div>
  <div>
      
  </div>
<br/><table id="att_table" class="table table-bordered table-hover">
                    <tbody>
                    </tbody>
                </table>
</section>
<script>
   function filterAttendanceupload(obj) {
        var branch = $('#importemployeectcform #filterby_branch').val();   
        var employee = $('#importemployeectcform #emp_fkey').val()
  
        $('#att_table').datagrid('load', {
            branch: branch,
            employee: employee,
           
        }); 
        
    } 
 
  
    
 jQuery(document).ready(function () {
        var employee = $('#attendanceuploadfilter #emp_fkey').val();

        $('#att_table').datagrid({
            url: livesite + "Empreport/getusers",
            pagination: true,
            singleSelect: false,
            queryParams:{
                    employee: employee
                },
                    
            fitColumns: true,
            pageList: [2, 5, 10, 50, 100],
            columns: [[
              
                    {field: 'C1', title: 'Time', width: "30%"},
                    {field: 'emp_anual_ctc', title: 'Anual CTC', width: "30%"},
                  
                  
                      ]]
        });
    });
</script>