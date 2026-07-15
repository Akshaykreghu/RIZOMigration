<?php
/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */
?>
  <style>
   .heading {
        display: flex;
        flex-direction: row;
        align-items: center;
        justify-content: space-between;
        /* margin-left: 15px; */
    }
    .home {
        background-color: #ffffffff;
        border-radius: 50px;
        padding: 2px 15px;
        color: #1e516e !important;
        margin-right: 15px;
        color: white;
        font-weight: 500;
        text-decoration: none;
        transition: all 0.3s ease;
        cursor: pointer;
        border: #1e516e 1px solid;
    }/* edited by bindu 24-10-25 */
</style>
<script>
    $(document).ready(function () {
        $('#load').html('<div><li class="fa fa-spinner fa-spin"></li></div>');
        $("#load").load(livesite + 'UserAccess/insec/<?php echo $arr_employee['0']['EmployeeDetails']['emp_pkey']; ?>');

//                var usersoptions = {
//            url: function (phrase) {
//                return "UserCredentials/getusers?username=" + phrase;
//            },
//            getValue: "full_name",
//            list: {
//                onClickEvent: function () {
//                     var selectedItem = $("#user").getSelectedItemData();
//                    var site_fkey = selectedItem.emp_pkey;
//                    $('#emp_pkey').val(selectedItem.emp_pkey);
//                  $('#load').load(livesite+'UserAccess/insec/'+site_fkey);
//                    //reloadDatagrid(site_fkey);
//
//                }
//            }
//        };
//
//        $('#user').easyAutocomplete(usersoptions);
        $('#tt').tree({
        });
        $('#emp_pkey').select2({
            placeholder: "Choose Employee"
        });
    })
</script>

<!--<script>
var x = document.getElementById("demo");
function getLocation() {
    if (navigator.geolocation) {
        navigator.geolocation.getCurrentPosition(showPosition);
    } else {
        x.innerHTML = "Geolocation is not supported by this browser.";
    }
}
function showPosition(position) {
    x.innerHTML = "Latitude: " + position.coords.latitude + 
    "<br>Longitude: " + position.coords.longitude; 
}
</script>


<p>Click the button to get your coordinates.</p>

<button onclick="getLocation()">Try It</button>

<p id="demo"></p>-->
<section class="content-header">
    <div class="heading">
        <!-- /* edited by bindu 24-10-25 */ -->
         <h1 class="text-primary-18">Menu Allocation</h1>
         <?php if ($user_group === 1 && $plan !== 'basic') : ?>
    
    <div class="text-primary-16 home"
         style="display:flex; align-items:center; gap:10px; cursor:pointer;">
        <i class="fa" style="font-size:16px;">&#xf104;</i>
        Back
    </div>

<?php endif; ?>

    </div>
    <p style="margin-top:5px;">Control user access to different menus for different level of employees.</p>
       <!-- end -->
    <hr style="margin-top: 8px;margin-bottom: -2px;">
</section>
<section class="content">
    <div class="col-md-12">
        <br>
        <div class="box box-primary " style="margin-top: -21px;">
            <div class="box-body" style="    margin-top: -11px;">
                <br>
                <div id="div-reportcriterias">
                    <div class="col-md-12">
                        <!--style="width: 100%; "-->
                        <select id="emp_pkey" style="height: 100px;" onchange="loaddata(this)">
                            <?php
                            foreach ($arr_employee as $val) {
                                ?>
                                <!--edited by arul on 18/11/2019 Employee Id changed to emp company id-->
                                <option value="<?php echo $val['EmployeeDetails']['emp_pkey']; ?>" > <?php echo $val['EmployeeDetails']['first_name']; ?>    <?php echo $val['EmployeeDetails']['last_name']; ?> -  <?php echo $val['EmployeeDetails']['emp_company_id']; ?></option>
                                <?php
                            }
                            ?>    
                        </select>
                    </div>
                </div>
                <div id="load" style="margin:10px 0"></div>
            </div><!-- /.box-body -->
            <!--/.direct-chat -->
        </div><!-- /.col -->
    </div>
</section>
<!--<section class="content">
    <div class="row">
        <div class="col-md-12">
            <div class="box box-header">
                <h2 style="text-align:left; font-size: 3em;">Advanced Access</h2> <p>Control user access to different menus for different level of employees.</p>
                <div class="box-body" id="div-reportcriterias">
                    <div class="col-md-12">
                    <input type="hidden" id="emp_pkey">
               <input type="text" id="user" class="form-control" name="user" placeholder="Search Your Employee Name" />
                        <select id="emp_pkey" style="width: 100%; " onchange="loaddata(this)">
<?php
foreach ($arr_employee as $val) {
    ?>
                                                    edited by arul on 18/11/2019 Employee Id changed to emp company id
                                                    <option value="<?php echo $val['EmployeeDetails']['emp_pkey']; ?>" > <?php echo $val['EmployeeDetails']['first_name']; ?>    <?php echo $val['EmployeeDetails']['last_name']; ?> -  <?php echo $val['EmployeeDetails']['emp_company_id']; ?></option>
    <?php
}
?>    
                        </select>
                    </div>
                </div>
            </div>
            <div id="load" class="box box-body" style="margin:10px 0">
            </div> 
        </div>
    </div>
</section>-->
<script type="text/javascript">

    function loaddata(s) {
        $('#load').html('<div><li class="fa fa-spinner fa-spin"></li></div>');
        $('#load').load(livesite + 'UserAccess/insec/' + $(s).val());
    }

    function getChecked() {
        var nodes = $('#tt').tree('getChecked');
        var s = '';
        for (var i = 0; i < nodes.length; i++) {
            if (s != '')
                s += ',';
            s += nodes[i].id;

        }
        var arr_men = [s];
        alert(arr_men);
        var u = $('#emp_pkey').val();
        //  alert(u);
        $.ajax({
            url: 'UserAccess/save/' + s + '/' + u,
            success: function (resp) {
                $('#uaccess').datagrid('reload');
                $.notify($.parseJSON(resp).msg, {
                    type: 'success',
                    allow_dismiss: false
                });
            }
        });
    }

          /* edited by bindu 24-10-25 */
    $(".home").on("click", function() {
        $("#container").isLoading({
            text: "Loading",
            position: "overlay",
        });

        $("#container").load(livesite + "EmployeeManage/index", function() {
            isDashboardShown = false;
        });


    });/* edited by bindu 24-10-25 end*/ 

</script>