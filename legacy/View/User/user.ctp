<style>
    table.dataTable{width:100%;margin:0 auto;clear:both;border-collapse:separate;border-spacing:0}table.dataTable thead th,table.dataTable tfoot th{font-weight:bold}table.dataTable thead th,table.dataTable thead td{padding:10px 18px;border-bottom:1px solid #111}table.dataTable thead th:active,table.dataTable thead td:active{outline:none}table.dataTable tfoot th,table.dataTable tfoot td{padding:10px 18px 6px 18px;border-top:1px solid #111}table.dataTable thead .sorting,table.dataTable thead .sorting_asc,table.dataTable thead .sorting_desc{cursor:pointer;*cursor:hand}table.dataTable thead .sorting,table.dataTable thead .sorting_asc,table.dataTable thead .sorting_desc,table.dataTable thead .sorting_asc_disabled,table.dataTable thead .sorting_desc_disabled{background-repeat:no-repeat;background-position:center right}table.dataTable thead .sorting{background-image:url("../images/sort_both.png")}table.dataTable thead .sorting_asc{background-image:url("../images/sort_asc.png")}table.dataTable thead .sorting_desc{background-image:url("../images/sort_desc.png")}table.dataTable thead .sorting_asc_disabled{background-image:url("../images/sort_asc_disabled.png")}table.dataTable thead .sorting_desc_disabled{background-image:url("../images/sort_desc_disabled.png")}table.dataTable tbody tr{background-color:#ffffff}table.dataTable tbody tr.selected{background-color:#B0BED9}table.dataTable tbody th,table.dataTable tbody td{padding:8px 10px}table.dataTable.row-border tbody th,table.dataTable.row-border tbody td,table.dataTable.display tbody th,table.dataTable.display tbody td{border-top:1px solid #ddd}table.dataTable.row-border tbody tr:first-child th,table.dataTable.row-border tbody tr:first-child td,table.dataTable.display tbody tr:first-child th,table.dataTable.display tbody tr:first-child td{border-top:none}table.dataTable.cell-border tbody th,table.dataTable.cell-border tbody td{border-top:1px solid #ddd;border-right:1px solid #ddd}table.dataTable.cell-border tbody tr th:first-child,table.dataTable.cell-border tbody tr td:first-child{border-left:1px solid #ddd}table.dataTable.cell-border tbody tr:first-child th,table.dataTable.cell-border tbody tr:first-child td{border-top:none}table.dataTable.stripe tbody tr.odd,table.dataTable.display tbody tr.odd{background-color:#f9f9f9}table.dataTable.stripe tbody tr.odd.selected,table.dataTable.display tbody tr.odd.selected{background-color:#acbad4}table.dataTable.hover tbody tr:hover,table.dataTable.display tbody tr:hover{background-color:#f6f6f6}table.dataTable.hover tbody tr:hover.selected,table.dataTable.display tbody tr:hover.selected{background-color:#aab7d1}table.dataTable.order-column tbody tr>.sorting_1,table.dataTable.order-column tbody tr>.sorting_2,table.dataTable.order-column tbody tr>.sorting_3,table.dataTable.display tbody tr>.sorting_1,table.dataTable.display tbody tr>.sorting_2,table.dataTable.display tbody tr>.sorting_3{background-color:#fafafa}table.dataTable.order-column tbody tr.selected>.sorting_1,table.dataTable.order-column tbody tr.selected>.sorting_2,table.dataTable.order-column tbody tr.selected>.sorting_3,table.dataTable.display tbody tr.selected>.sorting_1,table.dataTable.display tbody tr.selected>.sorting_2,table.dataTable.display tbody tr.selected>.sorting_3{background-color:#acbad5}table.dataTable.display tbody tr.odd>.sorting_1,table.dataTable.order-column.stripe tbody tr.odd>.sorting_1{background-color:#f1f1f1}table.dataTable.display tbody tr.odd>.sorting_2,table.dataTable.order-column.stripe tbody tr.odd>.sorting_2{background-color:#f3f3f3}table.dataTable.display tbody tr.odd>.sorting_3,table.dataTable.order-column.stripe tbody tr.odd>.sorting_3{background-color:whitesmoke}table.dataTable.display tbody tr.odd.selected>.sorting_1,table.dataTable.order-column.stripe tbody tr.odd.selected>.sorting_1{background-color:#a6b4cd}table.dataTable.display tbody tr.odd.selected>.sorting_2,table.dataTable.order-column.stripe tbody tr.odd.selected>.sorting_2{background-color:#a8b5cf}table.dataTable.display tbody tr.odd.selected>.sorting_3,table.dataTable.order-column.stripe tbody tr.odd.selected>.sorting_3{background-color:#a9b7d1}table.dataTable.display tbody tr.even>.sorting_1,table.dataTable.order-column.stripe tbody tr.even>.sorting_1{background-color:#fafafa}table.dataTable.display tbody tr.even>.sorting_2,table.dataTable.order-column.stripe tbody tr.even>.sorting_2{background-color:#fcfcfc}table.dataTable.display tbody tr.even>.sorting_3,table.dataTable.order-column.stripe tbody tr.even>.sorting_3{background-color:#fefefe}table.dataTable.display tbody tr.even.selected>.sorting_1,table.dataTable.order-column.stripe tbody tr.even.selected>.sorting_1{background-color:#acbad5}table.dataTable.display tbody tr.even.selected>.sorting_2,table.dataTable.order-column.stripe tbody tr.even.selected>.sorting_2{background-color:#aebcd6}table.dataTable.display tbody tr.even.selected>.sorting_3,table.dataTable.order-column.stripe tbody tr.even.selected>.sorting_3{background-color:#afbdd8}table.dataTable.display tbody tr:hover>.sorting_1,table.dataTable.order-column.hover tbody tr:hover>.sorting_1{background-color:#eaeaea}table.dataTable.display tbody tr:hover>.sorting_2,table.dataTable.order-column.hover tbody tr:hover>.sorting_2{background-color:#ececec}table.dataTable.display tbody tr:hover>.sorting_3,table.dataTable.order-column.hover tbody tr:hover>.sorting_3{background-color:#efefef}table.dataTable.display tbody tr:hover.selected>.sorting_1,table.dataTable.order-column.hover tbody tr:hover.selected>.sorting_1{background-color:#a2aec7}table.dataTable.display tbody tr:hover.selected>.sorting_2,table.dataTable.order-column.hover tbody tr:hover.selected>.sorting_2{background-color:#a3b0c9}table.dataTable.display tbody tr:hover.selected>.sorting_3,table.dataTable.order-column.hover tbody tr:hover.selected>.sorting_3{background-color:#a5b2cb}table.dataTable.no-footer{border-bottom:1px solid #111}table.dataTable.nowrap th,table.dataTable.nowrap td{white-space:nowrap}table.dataTable.compact thead th,table.dataTable.compact thead td{padding:4px 17px 4px 4px}table.dataTable.compact tfoot th,table.dataTable.compact tfoot td{padding:4px}table.dataTable.compact tbody th,table.dataTable.compact tbody td{padding:4px}table.dataTable th.dt-left,table.dataTable td.dt-left{text-align:left}table.dataTable th.dt-center,table.dataTable td.dt-center,table.dataTable td.dataTables_empty{text-align:center}table.dataTable th.dt-right,table.dataTable td.dt-right{text-align:right}table.dataTable th.dt-justify,table.dataTable td.dt-justify{text-align:justify}table.dataTable th.dt-nowrap,table.dataTable td.dt-nowrap{white-space:nowrap}table.dataTable thead th.dt-head-left,table.dataTable thead td.dt-head-left,table.dataTable tfoot th.dt-head-left,table.dataTable tfoot td.dt-head-left{text-align:left}table.dataTable thead th.dt-head-center,table.dataTable thead td.dt-head-center,table.dataTable tfoot th.dt-head-center,table.dataTable tfoot td.dt-head-center{text-align:center}table.dataTable thead th.dt-head-right,table.dataTable thead td.dt-head-right,table.dataTable tfoot th.dt-head-right,table.dataTable tfoot td.dt-head-right{text-align:right}table.dataTable thead th.dt-head-justify,table.dataTable thead td.dt-head-justify,table.dataTable tfoot th.dt-head-justify,table.dataTable tfoot td.dt-head-justify{text-align:justify}table.dataTable thead th.dt-head-nowrap,table.dataTable thead td.dt-head-nowrap,table.dataTable tfoot th.dt-head-nowrap,table.dataTable tfoot td.dt-head-nowrap{white-space:nowrap}table.dataTable tbody th.dt-body-left,table.dataTable tbody td.dt-body-left{text-align:left}table.dataTable tbody th.dt-body-center,table.dataTable tbody td.dt-body-center{text-align:center}table.dataTable tbody th.dt-body-right,table.dataTable tbody td.dt-body-right{text-align:right}table.dataTable tbody th.dt-body-justify,table.dataTable tbody td.dt-body-justify{text-align:justify}table.dataTable tbody th.dt-body-nowrap,table.dataTable tbody td.dt-body-nowrap{white-space:nowrap}table.dataTable,table.dataTable th,table.dataTable td{-webkit-box-sizing:content-box;-moz-box-sizing:content-box;box-sizing:content-box}.dataTables_wrapper{position:relative;clear:both;*zoom:1;zoom:1}.dataTables_wrapper .dataTables_length{float:left}.dataTables_wrapper .dataTables_filter{float:right;text-align:right}.dataTables_wrapper .dataTables_filter input{margin-left:0.5em}.dataTables_wrapper .dataTables_info{clear:both;float:left;padding-top:0.755em}.dataTables_wrapper .dataTables_paginate{float:right;text-align:right;padding-top:0.25em}.dataTables_wrapper .dataTables_paginate .paginate_button{box-sizing:border-box;display:inline-block;min-width:1.5em;padding:0.5em 1em;margin-left:2px;text-align:center;text-decoration:none !important;cursor:pointer;*cursor:hand;color:#333 !important;border:1px solid transparent;border-radius:2px}.dataTables_wrapper .dataTables_paginate .paginate_button.current,.dataTables_wrapper .dataTables_paginate .paginate_button.current:hover{color:#333 !important;border:1px solid #979797;background-color:white;background:-webkit-gradient(linear, left top, left bottom, color-stop(0%, #fff), color-stop(100%, #dcdcdc));background:-webkit-linear-gradient(top, #fff 0%, #dcdcdc 100%);background:-moz-linear-gradient(top, #fff 0%, #dcdcdc 100%);background:-ms-linear-gradient(top, #fff 0%, #dcdcdc 100%);background:-o-linear-gradient(top, #fff 0%, #dcdcdc 100%);background:linear-gradient(to bottom, #fff 0%, #dcdcdc 100%)}.dataTables_wrapper .dataTables_paginate .paginate_button.disabled,.dataTables_wrapper .dataTables_paginate .paginate_button.disabled:hover,.dataTables_wrapper .dataTables_paginate .paginate_button.disabled:active{cursor:default;color:#666 !important;border:1px solid transparent;background:transparent;box-shadow:none}.dataTables_wrapper .dataTables_paginate .paginate_button:hover{color:white !important;border:1px solid #111;background-color:#585858;background:-webkit-gradient(linear, left top, left bottom, color-stop(0%, #585858), color-stop(100%, #111));background:-webkit-linear-gradient(top, #585858 0%, #111 100%);background:-moz-linear-gradient(top, #585858 0%, #111 100%);background:-ms-linear-gradient(top, #585858 0%, #111 100%);background:-o-linear-gradient(top, #585858 0%, #111 100%);background:linear-gradient(to bottom, #585858 0%, #111 100%)}.dataTables_wrapper .dataTables_paginate .paginate_button:active{outline:none;background-color:#2b2b2b;background:-webkit-gradient(linear, left top, left bottom, color-stop(0%, #2b2b2b), color-stop(100%, #0c0c0c));background:-webkit-linear-gradient(top, #2b2b2b 0%, #0c0c0c 100%);background:-moz-linear-gradient(top, #2b2b2b 0%, #0c0c0c 100%);background:-ms-linear-gradient(top, #2b2b2b 0%, #0c0c0c 100%);background:-o-linear-gradient(top, #2b2b2b 0%, #0c0c0c 100%);background:linear-gradient(to bottom, #2b2b2b 0%, #0c0c0c 100%);box-shadow:inset 0 0 3px #111}.dataTables_wrapper .dataTables_paginate .ellipsis{padding:0 1em}.dataTables_wrapper .dataTables_processing{position:absolute;top:50%;left:50%;width:100%;height:40px;margin-left:-50%;margin-top:-25px;padding-top:20px;text-align:center;font-size:1.2em;background-color:white;background:-webkit-gradient(linear, left top, right top, color-stop(0%, rgba(255,255,255,0)), color-stop(25%, rgba(255,255,255,0.9)), color-stop(75%, rgba(255,255,255,0.9)), color-stop(100%, rgba(255,255,255,0)));background:-webkit-linear-gradient(left, rgba(255,255,255,0) 0%, rgba(255,255,255,0.9) 25%, rgba(255,255,255,0.9) 75%, rgba(255,255,255,0) 100%);background:-moz-linear-gradient(left, rgba(255,255,255,0) 0%, rgba(255,255,255,0.9) 25%, rgba(255,255,255,0.9) 75%, rgba(255,255,255,0) 100%);background:-ms-linear-gradient(left, rgba(255,255,255,0) 0%, rgba(255,255,255,0.9) 25%, rgba(255,255,255,0.9) 75%, rgba(255,255,255,0) 100%);background:-o-linear-gradient(left, rgba(255,255,255,0) 0%, rgba(255,255,255,0.9) 25%, rgba(255,255,255,0.9) 75%, rgba(255,255,255,0) 100%);background:linear-gradient(to right, rgba(255,255,255,0) 0%, rgba(255,255,255,0.9) 25%, rgba(255,255,255,0.9) 75%, rgba(255,255,255,0) 100%)}.dataTables_wrapper .dataTables_length,.dataTables_wrapper .dataTables_filter,.dataTables_wrapper .dataTables_info,.dataTables_wrapper .dataTables_processing,.dataTables_wrapper .dataTables_paginate{color:#333}.dataTables_wrapper .dataTables_scroll{clear:both}.dataTables_wrapper .dataTables_scroll div.dataTables_scrollBody{*margin-top:-1px;-webkit-overflow-scrolling:touch}.dataTables_wrapper .dataTables_scroll div.dataTables_scrollBody th,.dataTables_wrapper .dataTables_scroll div.dataTables_scrollBody td{vertical-align:middle}.dataTables_wrapper .dataTables_scroll div.dataTables_scrollBody th>div.dataTables_sizing,.dataTables_wrapper .dataTables_scroll div.dataTables_scrollBody td>div.dataTables_sizing{height:0;overflow:hidden;margin:0 !important;padding:0 !important}.dataTables_wrapper.no-footer .dataTables_scrollBody{border-bottom:1px solid #111}.dataTables_wrapper.no-footer div.dataTables_scrollHead table,.dataTables_wrapper.no-footer div.dataTables_scrollBody table{border-bottom:none}.dataTables_wrapper:after{visibility:hidden;display:block;content:"";clear:both;height:0}@media screen and (max-width: 767px){.dataTables_wrapper .dataTables_info,.dataTables_wrapper .dataTables_paginate{float:none;text-align:center}.dataTables_wrapper .dataTables_paginate{margin-top:0.5em}}@media screen and (max-width: 640px){.dataTables_wrapper .dataTables_length,.dataTables_wrapper .dataTables_filter{float:none;text-align:center}.dataTables_wrapper .dataTables_filter{margin-top:0.5em}}
</style>
<style>
    .file-preview-image {
        width: inherit; 
    }
       /* <!-- edited by bindhu 19-02-2026 --> */
    .heading {
        display: flex;
        flex-direction: row;
        align-items: end;
        justify-content: space-between;
     margin: 0 0px 20px 20px;
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
    
    }
    .content-header{
        padding: 0;
    }
</style>

<section class="content">
  <section class="heading">
            <h1 class="text-primary-18"> My Profile</h1>
            <div class="text-primary-16 home"
                style="display:flex; align-items:center; gap:10px; cursor:pointer;">
                <i class="fa" style="font-size:16px;">&#xf104;</i>
                Back
            </div>
        </section>
    <div class="row">

        <div class="col-md-3">

            <!-- Profile Image -->
            <div class="box box-primary">
                <div class="box-body box-profile">


                    <!--<a href="#" class="btn btn-primary btn-block"><b>Follow</b></a>-->
                </div>
                <!-- /.box-body -->
            </div>
            <!-- /.box -->

            <!-- About Me Box -->

            <!-- /.box -->
        </div>
        <!-- /.col -->
        <div class="col-md-9">
            <div class="nav-tabs-custom">
                <ul class="nav nav-tabs">
                    <li class="active"><a href="#basics" data-toggle="tab" aria-expanded="true">Basic Details</a></li>
                    <li class=""><a href="#div-empqualification" data-toggle="tab" area-expanded="false">Qualifications</a></li>
                    <li class=""><a href="#div-empsetupfamily" data-toggle="tab" area-expanded="false">Family</a></li>
                    <li class=""><a href="#div-emphistorical" data-toggle="tab" area-expanded="false">Work Experiences</a></li>
                    <li class=""><a href="#div-empsetuppassport" data-toggle="tab" area-expanded="false">Passport & Visa </a></li>
                    <li class="" ><a id="imageUdate" href="#imgupdate" data-toggle="tab" aria-expanded="false">Update Image</a></li>
                    <li class=""><a href="#passwordrst" data-toggle="tab" aria-expanded="false">Reset Password</a></li>
                </ul>
                <div class="tab-content">
                    <div class="tab-pane active" id="basics">
                        <form class="form-horizontal" method="post" action="<?php echo $this->webroot; ?>Employee/saveemployeesetupnew" id="empsetuppersonal">
                            <input id="emp_pkey" name="emp_pkey" type="hidden"  value="<?php echo isset($emp_fkey)?$emp_fkey:''; ?>" >
                        </form>
                        
                        <input type="hidden" id="emp_fkey" name="emp_fkey" value="<?php echo isset($emp_fkey)?$emp_fkey:''; ?>" >



                        <div id="settings">

                        </div>
                    </div>


                    <!-- /.tab-pane -->
                    <div class="tab-pane" id="imgupdate">
                        <form class="form-horizontal" id="change_image_con">

                        </form>
                    </div>

                    <!-- /.tab-pane -->
                    <div class="tab-pane" id="passwordrst">
                        <form class="form-horizontal" id="changepassword" action="<?php echo $this->webroot ?>User/savePassword" >
                            <div class="form-group has-warning">
                                <label for="inputName" class="col-sm-2 control-label">Enter Old Password &nbsp;&nbsp; <a class="togglepassword" title="Show Password " style="position : absolute; " ><li class="fa fa-eye"></li></a> </label>

                                <div class="col-sm-10">
                                    <input required="required" onchange="checkpasswordvalidation();" minlength="4" type="password" class="form-control"  name="password"  id="inputOldPassws" placeholder="Enter Old Password">
                                    <span class="help-block oldpass">Enter Your Current Password </span>
                                </div>
                            </div>
                            <div class="form-group has-warning">
                                <label for="inputEmail" class="col-sm-2 control-label">New Password &nbsp;&nbsp;<a class="togglepassword" title="Show Password " style="position : absolute; " ><li class="fa fa-eye"></li></a> </label>

                                <div class="col-sm-10">
                                    <input required="required" type="password" class="form-control" minlength="8" onkeydown="checkLength(this);" id="inputNewPassw" autocomplete="off" name="password1" placeholder="Enter New Password">
                                    <span class="help-block">Password must be at least 8 characters </span>
                                </div>
                            </div>
                            <div class="form-group has-warning">
                                <label for="inputName" class="col-sm-2 control-label">Confirm Password &nbsp;&nbsp; <a class="togglepassword" title="Show Password " style="position : absolute; " ><li class="fa fa-eye"></li></a> </label>

                                <div class="col-sm-10">
                                    <input required="required" minlength="8" onchange="confirm();" type="password" class="form-control" id="inputConfirm" name="password2"  placeholder="Confirm Password">
                                    <span class="help-block">Must be the same as above </span>
                                </div>
                            </div>
                            <button class="btn btn-primary">Save</button>
                        </form>
                    </div>


                    <div role="tabpanel" class="tab-pane" id="div-empqualification" style="height: 300px; ">
                        <div class="modal-header">

                            <div class="form-group">
                                <button type="button" class="btn btn-info btn-sm" data-widget="Add Items" data-toggle="tooltip" title="Add Items" data-original-title="Add Item" onclick="showdetails();">
                                    <i class="fa fa-plus"></i>
                                </button> &nbsp;
                                <button id="del" type="button" class="btn btn-danger btn-sm" data-widget="remove" data-toggle="tooltip" title="Remove" data-original-title="Remove">
                                    <i class="fa fa-times"></i>
                                </button>
                            </div>
                        </div>
                        <div>
                            <table class="table table-bordered display" id="example1">
                                <thead>
                                    <tr>

<!--    <th>LEAVEPOLICY_GROUP_NAME</th> -->


                                        <th>Course</th>
                                        <th>University/College Name</th>
                                        <th>Duration</th>

                                        <th>Percentage/Marks</th>

                                    </tr>
                                </thead>

                                <tbody>

                                </tbody>
                            </table>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-default" onclick="$('#largeModalForm').modal('hide');">Cancel</button>
                            <button type="button" id="btn-submit" onclick="showtabhistory();" class="btn btn-primary">Save & Next</button>
                        </div>

                    </div>
                    <div role="tabpanel" class="tab-pane" id="div-emphistorical" style="height: 350px ; ">
                        <div class="modal-header">
                            <!--           <button type="button" class="btn btn-default" onclick="showhistory();">Add History Details</button><button type="button" id="del1" class="btn btn-default">Delete</button>-->
                            <div class="form-group"><button type="button" class="btn btn-info btn-sm" data-widget="Add Items" data-toggle="tooltip" title="Add Items" data-original-title="Add Item" onclick="showhistory();"><i class="fa fa-plus"></i></button> &nbsp;<button id="del1" type="button" class="btn btn-danger btn-sm" data-widget="remove" data-toggle="tooltip" title="Remove" data-original-title="Remove">
                                    <i class="fa fa-times"></i></button></div>
                        </div>
                        <div>
                            <table id="example" class="table table-bordered display">
                                <thead>
                                    <tr>

<!--    <th>LEAVEPOLICY_GROUP_NAME</th> -->


                                        <th>Company</th>
                                        <th>Department</th>
                                        <th>Designation</th>
                                        <th>From Date</th>
                                        <th>To Date</th>
                                        <th>CTC</th>
                                    </tr>
                                </thead>

                                <tbody>

                                </tbody>
                            </table>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-default" onclick="$('#largeModalForm').modal('hide');">Cancel</button>
                            <button onclick="showtabfamy();" type="button" id="btn-submit" class="btn btn-primary">Save & Next</button>
                        </div>
                    </div>








                    <div role="tabpanel" class="tab-pane" id="div-empsetupfamily" style="height: 350px ; ">
                        <div class="modal-header">

                            <div class="form-group"><button type="button" class="btn btn-info btn-sm" data-widget="Add Items" data-toggle="tooltip" title="Add Items" data-original-title="Add Item" onclick="addfamily();"><i class="fa fa-plus"></i></button> 
                                &nbsp;<button id="del1_family" type="button" class="btn btn-danger btn-sm" data-widget="remove" data-toggle="tooltip" title="Remove" data-original-title="Remove">
                                    <i class="fa fa-times"></i></button>
                                <button id="mark_nominee" type="button" class="btn btn-primary">Mark as Nominee </button>
							<button id="mark_emergency" type="button" class="btn btn-primary">Mark as Emergency Contact </button>			 
                           </div>
                        </div>
                        <div>
                            <table class="table table-bordered display" id="example_family">
                                <thead>
                                    <tr>

<!--    <th>LEAVEPOLICY_GROUP_NAME</th> -->


                                        <th>Name</th>
                                        <th>Relation</th>
                                        <th>DOB</th>

<!--<th>Age</th>-->
                                        <th>Blood Group</th>
                                    <!--    <th>Gender</th> -->
                                        <th>Nationality</th>
                                        <th>Nominee</th>
                                        <th>Emergency</th>
                                        <th>Contact Number</th>	   
                                    </tr>
                                </thead>

                                <tbody>

                                </tbody>
                            </table>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-default" onclick="$('#largeModalForm').modal('hide');">Cancel</button>
                            <button type="button" id="btn-submit" onclick="showtabpass();" class="btn btn-primary">Save & Next</button>
                        </div>

                    </div>
                    <div role="tabpanel" class="tab-pane" id="div-empsetuppassport">
                        <div class="modal-header">

                            <div class="form-group"><button type="button" class="btn btn-info btn-sm" data-widget="Add Items" data-toggle="tooltip" title="Add Items" data-original-title="Add Item" onclick="addpass();"><i class="fa fa-plus"></i></button>
                                &nbsp;<button id="del_passport" type="button" class="btn btn-danger btn-sm" data-widget="remove" data-toggle="tooltip" title="Remove" data-original-title="Remove">
                                    <i class="fa fa-times"></i></button>
                            </div>
                        </div>
                        <div>
                            <table class="table table-bordered display" id="example_passport">
                                <thead>
                                    <tr>

<!--    <th>LEAVEPOLICY_GROUP_NAME</th> -->


                                        <th>Name</th>
                                        <th>Document Type</th>
                                        <th>Document Number</th>

<!--<th>Age</th>-->
                                        <th>Relation</th>
                                        <th>Valid From</th>
                                        <th>Valid To</th>
                                        <th>Gender</th>
                                        <th>Nationality</th>

                                    </tr>
                                </thead>

                                <tbody>

                                </tbody>
                            </table>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-default" onclick="$('#largeModalForm').modal('hide');">Cancel</button>
                            <button type="button" id="btn-submit" onclick="$('#largeModalForm').modal('hide');" class="btn btn-primary">Save & Close</button>
																				   
                        </div>

                    </div>

                    <!-- /.tab-pane -->
                </div>
                <!-- /.tab-content -->
            </div>
            <!-- /.nav-tabs-custom -->
        </div>
        <!-- /.col -->
    </div>
    <!-- /.row -->

    
    <!-- Tax Head Detail Form -->
            <div id="modalShowTaxHeadDetailForm" class="modal fade">
                <div class="modal-dialog modal-lg">
                    <div class="modal-content" id="modalForm-content">
                        <!-- Content will be loaded here from "remote.php" file -->
                    </div>
                </div>
            </div>
			  
		  
            <div id="modalDetailForm" class="modal fade">
                <div class="modal-dialog modal-md">
                    <div class="modal-content" id="modaldetails-content">

                    </div>
                </div>
            </div>
			  
		  

</section>
<script type="text/javascript">
    // To make Pace works on Ajax calls
    $(document).ajaxStart(function () {
//    Pace.restart();
//    alert('satterds');
    })
    $('.ajax').click(function () {
        $.ajax({
            url: '#', success: function (result) {
                $('.ajax-content').html('<hr>Ajax Request Completed !')
            }
        })
    })
</script>
<script>

var empPkey = $('#emp_fkey').val();
    function showdetails()
    {
        var empPkey = $('#emp_fkey').val();
        console.log(empPkey);
        if(empPkey == 0){
            alert("Add an employee first");
            return false;
        }
        var url = livesite + 'Employee/addqualification/' + empPkey;

        var container = $("#modalDetailForm #modaldetails-content")
        container.load(url, function () {
            $("#modalDetailForm").modal('show');
        });
    }
    
    
    function showhistory()
    {
        var empPkey = $('#emp_fkey').val();
        console.log(empPkey);
        if(empPkey == 0){
            alert("Add an employee first");
            return false;
        }
        var url = livesite + 'Employee/history/' + empPkey;

        var container = $("#modalDetailForm #modaldetails-content")
        container.load(url, function () {
            $("#modalDetailForm").modal('show');
        });
    }
    function showtabhistory()
    {
        $('#history_details').addClass('active');
        $('#div-emphistorical').addClass("active");
        $('#div-emphistorical').addClass("in");
        $('#history_details').addClass("active");
        $('#div-empqualification').removeClass("active");
        $('#div-empqualification').removeClass("in");
        $('#qualifications').removeClass("active");
    }
    function showtabfamy() {
        $('#family').addClass('active');
        $('#div-empsetupfamily').addClass("active");
        $('#div-empsetupfamily').addClass("in");
        $('#div-emphistorical').removeClass("active");
        $('#div-emphistorical').removeClass("in");
        $('#history_details').removeClass("active");
    }
    function showtabpass() {
        $('#passport').addClass('active');
        $('#div-empsetuppassport').addClass("active");
        $('#div-empsetuppassport').addClass("in");
        $('#div-empsetupfamily').removeClass("active");
        $('#div-empsetupfamily').removeClass("in");
        $('#family').removeClass("active");
	 
							 
													 
										   
									   
														 
													 
													  
    }
    function showtabTax()
    {
        $('#ss').addClass('active');
        $('#div-empsetuptaxation').addClass("active");
        $('#div-empsetuptaxation').addClass("in");
        $('#ss').addClass("active");
        $('#div-empsetuppassport').removeClass("active");
        $('#div-empsetuppassport').removeClass("in");
        $('#passport').removeClass("active");

    }
    function addfamily()
    {
        var empPkey = $('#emp_fkey').val();
        if(empPkey == 0){
            alert("Add an employee first");
            return false;
        }
        var url = livesite + 'Employee/addfamily/' + empPkey;

        var container = $("#modalDetailForm #modaldetails-content")
        container.load(url, function () {
            $("#modalDetailForm").modal('show');
            
        });
    }
    function addpass()
    {
        empPkey = $('#emp_fkey').val();
        var url = livesite + 'Employee/passport/' + empPkey;
        if(empPkey == 0){
            alert("Add an employee first");
            return false;
        }
        var container = $("#modalDetailForm #modaldetails-content")
        container.load(url, function () {
            $("#modalDetailForm").modal('show');
        });
    }
    
    function loadsettings() {
        $('#basicdetails').fadeOut();
        $('#settings').load(livesite + 'User/savebasics_form', function () {
            $('#settings').fadeIn();
        });
    }

    function loadbasicsettings() {
        $('#basicdetails').fadeOut();
        $('#settings').load(livesite + 'User/load_basic_details', function () {
            $('#settings').fadeIn();
        });
    }

    function cancel_edit() {
        $('#settings').fadeOut();
        $('#settings').load(livesite + 'User/load_basic_details', function () {
            $('#settings').fadeIn();
        });
        
    }

    function checkLength(s) {
        var lenghth = $(s).val().length;
        if (lenghth > 7) {
            $(s).parent().parent().removeClass('has-warning').addClass('has-success');
        } else {
            $(s).parent().parent().removeClass('has-success').addClass('has-warning');
        }
    }
    function confirm() {
        var newpass = $('#inputNewPassw').val();
        var confirmPass = $('#inputConfirm').val();
        if (newpass == confirmPass) {
            $('#inputConfirm').parent().parent().removeClass('has-warning').removeClass('has-error').addClass('has-success');
        } else {
            $('#inputConfirm').parent().parent().removeClass('has-success').addClass('has-error');
        }
    }

    function load_change_image() {
        $('#change_image_con').load(livesite + 'User/change_image', function () {

        });
    }

    function openchangeimagemodal() {
        $('#imageUdate').trigger("click");
    }

    function checkpasswordvalidation() {
        var passwes = $('#inputOldPassws').val();
        $.ajax({
            url: livesite + 'User/checkpassvalidation',
            data: {oldpass: passwes},
            success: function (result) {
                if (result == 1) {
                    $('#inputOldPassws').parent().parent().removeClass('has-warning').removeClass('has-error').addClass('has-success');
                    $('#inputOldPassws').siblings('span').html('Password Matched ');
                } else {
                    $('#inputOldPassws').parent().parent().removeClass('has-success').addClass('has-error');
                    $('#inputOldPassws').siblings('span').html('Password Match Failed');
                }
//                $('.ajax-content').html('<hr>Ajax Request Completed !')
            }
        });
    }

    function removes(id)
    {
        var div = document.getElementById(id);

        if (confirm("Are You Sure you want to delete this file?"))
        {
            //$.post('./php/ajax_remove_file.php', {id:id});
            $.ajax({
                type: 'POST',
                url: './php/ajax_remove_file.php',
                data: {id: id},
                success: function (e) {
                    $(div).hide();

                }
            });
        }
    }
    $(document).ready(function () {

        load_change_image();
        $('.togglepassword').click(function () {
            var element = $(this).parent().closest('div').find('input');
            //alert($(element).attr('type'));
            if ($(element).attr('type') == 'password') {
                $(element).attr('type', "text");
            } else {
                $(element).attr('type', "password");
            }
        });
        loadbasicsettings();


        var id = $('#products').val();
        $('.box-profile').load(livesite + 'User/loadImage');

var table_family = $('#example_family').DataTable({
            "processing": true,
            "serverSide": true,
            "paging": false,
            "deferRender": false,
            "pageNumber": false,
            "lengthChange": true,
            "searching": false,
            "ordering": false,
            "info": false,
            "autoWidth": false,
            "ajax": {
                "url": livesite + "Employee/lstfamilies/" + $('#emp_fkey').val(),
                "type": "POST"
            },
            "columns": [
                {"data": "name"},
                {"data": "relation"},
                {"data": "DOB"},
                {"data": "blood_group"},
        //        {"data": "gender"},
                {"data": "nationality"},
                {"data": "is_nominee"},
				{"data": "emergency_contact"},
                {"data": function (data, type, dataToSet) {
                        var number = '';
                        if (data.contact_number != null) {
                            number += data.contact_number + "<br>";
                        }
                        if (data.alternate_number != null) {
                            number += data.alternate_number;
                        }
                        return  number;
                    }}
            ]
        });

        $('#example_family tbody').on( 'click', 'tr', function () {
            if ( $(this).hasClass('selected') ) {
                $(this).removeClass('selected');
            }
            else {
                table_family.$('tr.selected').removeClass('selected');
                $(this).addClass('selected');
            }
        } );
        
        $('#mark_nominee').click(function(){
            if(table_family.row('.selected').data()){
                var emp_family_pkey = table_family.row('.selected').data().emp_family_pkey;
                var emp = $('#empsetuppersonal #emp_pkey').val();
                $.ajax({
                    url: livesite + 'Employee/mark_nominee/' + emp_family_pkey +'/'+emp,
                    success: function (resp) {

                        alert("Marked as nominee Successfully");
                        $("#example_family").DataTable().ajax.reload();
                    }
                });
                
            }else{
                alert("please select any ! ");
            }
        });
        
		 $('#mark_emergency').click(function () {
            if (table_family.row('.selected').data()) {
                var emp_family_pkey = table_family.row('.selected').data().emp_family_pkey;
                var emp = $('#empsetuppersonal #emp_pkey').val();
                $.ajax({
                    url: livesite + 'Employee/mark_emergency/' + emp_family_pkey + '/' + emp,
                    success: function (responseText, statusText, xhr, $form) {
                        var response = JSON.parse(responseText);
                        if (response.success == true) {
                            $("#example_family").DataTable().ajax.reload();
                            alert("Marked as Emergency Contact Successfully");
                        } else {
                            alert(response.msg);
                        }
                    }
                });
            } else {
                alert("please select any ! ");
            }
        });


        $('#del1_family').click(function () {
            if(table_family.row('.selected').data()){
                var emp_family_pkey = table_family.row('.selected').data().emp_family_pkey;
            
                $.ajax({
                    url: livesite + 'Employee/deletefdetails/' + emp_family_pkey,
                    success: function (resp) {

                        alert("Deleted Successfully");
                        $("#example_family").DataTable().ajax.reload();
                    }
                });
                
            }else{
                alert("please select any ! ");
            }
        });
        //end of datas
        //----------------------------------//
        
        
        
        
        
        //History
        //-----------------------------//
        var table = $('#example').DataTable({
            "processing": true,
            "serverSide": true,
            "paging": false,
            "deferRender": false,
            "pageNumber": false,
            "lengthChange": true,
            "searching": false,
            "ordering": false,
            "info": false,
            "autoWidth": false,
            "ajax": {
                "url": livesite + "Employee/listhistory/" + $('#emp_fkey').val(),
                "type": "POST"
            },
            "columns": [
                {"data": "company"},
                {"data": "department"},
                {"data": "designation"},
                {"data": "from_date"},
                {"data": "to_date"},
                {"data": "salary"}
            ]
        });

        $('#example tbody').on( 'click', 'tr', function () {
            if ( $(this).hasClass('selected') ) {
                $(this).removeClass('selected');
            }
            else {
                table.$('tr.selected').removeClass('selected');
                $(this).addClass('selected');
            }
        } );
        
        $('#del1').click(function () {
            if(table.row('.selected').data()){
                var history_pkey = table.row('.selected').data().history_pkey;
            
                $.ajax({
                    url: livesite + 'Employee/deletehist/' + history_pkey,
                    success: function (resp) {

                        alert("Deleted Successfully");
                        $("#example").DataTable().ajax.reload();
                    }
                });
                
            }else{
                alert("please select any ! ");
            }
        });


        //end of history
        //---------------------------//
        
        
        // Qualifications 
        //------------------------------//
        var emp = $('#emp_fkey').val();
        var table1 = $('#example1').DataTable({
            "processing": true,
            "serverSide": true,
            "paging": false,
            "deferRender": false,
            "pageNumber": false,
            "lengthChange": true,
            "searching": false,
            "ordering": false,
            "info": false,
            "autoWidth": false,
            "ajax": {
                "url": livesite + "Employee/listqualifications/" + $('#emp_fkey').val(),
                "type": "POST"
            },
            "columns": [
                {"data": "course"},
                {"data": "university"},
                {"data": "duration"},
                {"data": "mark"}
            ]
        });
        
        $('#example1 tbody').on( 'click', 'tr', function () {
            if ( $(this).hasClass('selected') ) {
                $(this).removeClass('selected');
            }
            else {
                table1.$('tr.selected').removeClass('selected');
                $(this).addClass('selected');
            }
        } );
    
    
        $('#del').click( function () {
//            table1.row('.selected').remove().draw( false );
            if(table1.row('.selected').data()){
                var qualification_pkey = table1.row('.selected').data().qualification_pkey;
            
                $.ajax({
                    url: livesite + 'Employee/deletequal/' + qualification_pkey,
                    success: function (resp) {

                        alert("Deleted Successfully");
                        $("#example1").DataTable().ajax.reload();
                    }
                });
                
            }else{
                alert("please select any ! ");
            }
            
        } );
        //End Qualifications
        //-----------------------//
 
 
        //Passport
        //------------------------//
        var table_passport = $('#example_passport').DataTable({
            "processing": true,
            "serverSide": true,
            "paging": false,
            "deferRender": false,
            "pageNumber": false,
            "lengthChange": true,
            "searching": false,
            "ordering": false,
            "info": false,
            "autoWidth": false,
            "ajax": {
                "url": livesite + "Employee/listpassports/" + $('#emp_fkey').val(),
                "type": "POST"
            },
            "columns": [
                {"data": "name"},
                {"data": "document_type"},
                {"data": "document_number"},
                {"data": "relation"},{"data": "valid_from"},{"data": "valid_till"},{"data": "classification"},{"data": "nationality"}
            ]
        });
        
        $('#example_passport tbody').on( 'click', 'tr', function () {
            if ( $(this).hasClass('selected') ) {
                $(this).removeClass('selected');
            }
            else {
                table_passport.$('tr.selected').removeClass('selected');
                $(this).addClass('selected');
            }
        } );
    
    
        $('#del_passport').click( function () {
//            table1.row('.selected').remove().draw( false );
            if(table_passport.row('.selected').data()){
                var qualification_pkey = table_passport.row('.selected').data().emp_passport_visa_pkey;
            
                $.ajax({
                    url: livesite + 'Employee/deletepassports/' + qualification_pkey,
                    success: function (resp) {

                        alert("Deleted Successfully");
                        $("#example_passport").DataTable().ajax.reload();
                    }
                });
                
            }else{
                alert("please select any ! ");
            }
            
        } );

		   

        var options = {
            success: function (resp) {
//                    alert(resp);
                resp = $.parseJSON(resp);
                var errorMsg = resp.msg;
                $.notify(resp.msg, {
                    type: 'success',
                    allow_dismiss: false
                });
                $('#changepassword').trigger("reset");
            }

        };


        // bind to the form's submit event 
        $('#changepassword').submit(function () {
            var newpass = $('#inputNewPassw').val();
            var confirmPass = $('#inputConfirm').val();

            if (newpass == confirmPass) {
                $('#inputConfirm').parent().parent().removeClass('has-warning').removeClass('has-error').addClass('has-success');
            } else {
                $('#inputConfirm').parent().parent().removeClass('has-success').addClass('has-error');
                return false;
            }
            // inside event callbacks 'this' is the DOM element so we first 
            // wrap it in a jQuery object and then invoke ajaxSubmit 
            $(this).ajaxSubmit(options);

            // !!! Important !!! 
            // always return false to prevent standard browser submit and page navigation 
            return false;
        });






    });
    
 /* edited by bindu 19-02-26 */
   $(".home").on("click", function () {

    $("#container").isLoading({
        text: "Loading",
        position: "overlay",
    });

    let url = "";
     var userGroup = <?php echo json_encode($this->Session->read('user_group')); ?>

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
</script>
