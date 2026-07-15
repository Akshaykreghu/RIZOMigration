<style>
    .datepicker{z-index:1151 !important;}
    .form-horizontal .control-label {
        text-align: left;
        /*padding-left: 76px;*/
    }
</style>
<div class="modal-dialog">

    <!-- Modal content-->
    <div class="modal-content">
        <div class="modal-header" style="background: #00659f;color: white">
            <h4 class="modal-title">Year</h4>  
        </div>
        <div class="modal-body">
            <form class="form-horizontal" id="finForm" action="<?php echo $this->webroot; ?>FinancialYear/save" method="post" >
                <div class="modal-body">

                    <!-- Text input-->
                    <input id="Fin_year_seq" name="Fin_year_seq" type="hidden"  value="<?php echo $data["Fin_year_seq"]; ?>" >

                    <div class="form-group">
                        <label class="col-md-4 control-label" for="start_month">Year Start</label>  
                        <div class="col-md-1">:</div>
                        <div class="col-md-7">
                            <div class="input-group date">

                                <input id="start_month" name="start_month"  value="<?php echo $data["start_month"]; ?>" type="text" placeholder="Start Month" class="form-control input-md" required="">
                                <div class="input-group-addon"><span class="fa fa-calendar" aria-hidden="true"></span></div>
                            </div>

                        </div>
                    </div>

                    <!-- Text input-->
                    <div class="form-group">
                        <label class="col-md-4 control-label" for="end_month">Year End</label>  
                        <div class="col-md-1">:</div>
                        <div class="col-md-7">
                            <div class="input-group date">

                                <input id="end_month" name="end_month"  value="<?php echo $data["end_month"]; ?>" type="text" placeholder="Year Month" class="form-control input-md" required="">
                                <div class="input-group-addon"><span class="fa fa-calendar" aria-hidden="true"></span></div>
                            </div>


                        </div>
                    </div>

                    <!-- Text input-->
                    <div class="form-group">
                        <label class="col-md-4 control-label" for="Year_status" >Status</label>  
                        <div class="col-md-1">:</div>
                        <div class="col-md-7">
                            <select name="Year_status" id="Year_status"  class="form-control input-md">

                                <option value="OPEN">OPEN</option>
                                <option value="CLOSED">CLOSED</option>
                            </select>

                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-md-4 control-label" for="Year_status" >Branch</label>  
                        <div class="col-md-1">:</div>
                        <div class="col-md-7">
                            <select name="branch" id="branch"  class="form-control input-md">
                                <?php
                                foreach ($branches as $branch) {
                                    ?>
                                    <option <?php
                                    if (isset($data['branch_code']) && $data['branch_code'] == $branch['branch_code']) {
                                        echo 'selected="selected"';
                                    };
                                    ?> value="<?php echo $branch['branch_code']; ?>"><?php echo $branch['branch_name']; ?></option>
                                        <?php
                                    }
                                    ?>
                            </select>

                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-md-4 control-label" for="Year_status" >Type</label>  
                        <div class="col-md-1">:</div>
                        <div class="col-md-7">
                            <select name="type" id="type"  class="form-control input-md">

                                <option <?php
                                if (isset($data['vattr1']) && $data['vattr1'] == 0) {
                                    echo 'selected="selected"';
                                };
                                ?> value="0">Leave</option>
                                <option <?php
                                if (isset($data['vattr1']) && $data['vattr1'] == 1) {
                                    echo 'selected="selected"';
                                };
                                ?> value="1">Financial</option>
                            </select>

                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-md-4 control-label" for="is_current_finyear" >Is Current Financial Year</label>  
                        <div class="col-md-1">:</div>
                        <div class="col-md-7">
                            <?php if ((isset($data['is_current_finyear']) && $data['is_current_finyear'] == 'Y')) {
                                ?>
                                <input type="checkbox" checked="checked" name="is_current_finyear" value="Y" id="is_current_finyear"  />


                                <?php
                            } else {
                                ?>
                                <input type="checkbox" name="is_current_finyear" value="Y" id="is_current_finyear"  />


                                <?php
                            }
                            ?>
                        </div>
                    </div>

                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-danger" data-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary">Save</button>
                </div>
            </form>
        </div>
    </div>
</div>
<script type="text/javascript">
    $(document).ready(function () {
        $('#start_month').datepicker({
            format: "dd/mm/yyyy",
            autoclose: true
        });
        $('#end_month').datepicker({
            format: "dd/mm/yyyy",
            autoclose: true
        });

        var options = {
            success: function (responseText, statusText, xhr, $form) {
                closeModal('finyeartable');
                var response = $.parseJSON(responseText);
                if (response.msg) {
                    $.notify(response.msg, {
                        type: 'success',
                        allow_dismiss: true

                    });
                }
            }
        };

        // bind to the form's submit event 
        $('#finForm').submit(function () {
            // inside event callbacks 'this' is the DOM element so we first 
            // wrap it in a jQuery object and then invoke ajaxSubmit 
            $(this).ajaxSubmit(options);

            // !!! Important !!! 
            // always return false to prevent standard browser submit and page navigation 
            return false;
        });
    });
</script>