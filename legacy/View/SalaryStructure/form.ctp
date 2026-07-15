<style>
    fieldset.fld-sal-structure {
        padding: 20px;
        padding-top: 35px !important;
        border: 1px solid #666;
        border-radius: 8px;
        box-shadow: 0 0 5px #666;
    }

    fieldset.fld-sal-structure legend {
        float: left;
        margin-top: -20px;
        color: #00659f;
    }

    .edit-dets {
        cursor: pointer;
        font-size: 10px;
        padding: 1px 1px 0px 4px;
    }

    fieldset.fld-sal-structure legend+* {
        clear: both;
    }

    .det-items {
        color: #523d3d !important;
        font-weight: 800;
    }
</style>
<script>
    $('#structure_name').on('change', function() {
        checkIfStructureExists();
    })

    $(".salamount").change(function() {
        setRemainingBalance();
    });


    $('.det-items').on('click', function() {
        var _selectedItem = $(this).html();
        var _index = $(this).parent().attr("data-src");
        $(this).parents(".det-parenens").find(".item-type").val(_selectedItem);
        $(this).parents(".det-parenens").find(".structure_det_operator").val(_selectedItem);
        $(this).parents(".det-parenens").find(".label-det").html(_selectedItem);
        $(this).parent().hide();
        clearForms(_index);
        if (_selectedItem == 'formula') {
            $('#structure_det_value_' + _index).attr("onclick", "dpopup(this," + _index + ")");
        } else if (_selectedItem == 'fixed') {
            var values = prompt("Please enter fixed value", "0");
            var _html = '<input type="hidden" id="hid_item_value_' + _index + '" name="hid_item_value_' + _index + '" value="' + values + '" /><input type="checkbox" onChange="onCheckChanged(this)" id="check-fixed-item-' + _index + '" checked="checked" />';
            $("#structure_det_value_" + _index).val(values);
            $(".label-det-val-" + _index).html(values);
            $(this).parents(".show-drop-down").prepend(_html);
        } else if (_selectedItem == 'limit') {
            $('#structure_det_value_' + _index).val(0).attr("onclick", "dpopup(this," + _index + ")");
            var values = prompt("Please enter Limit value", "0");
            $(".label-det-val-" + _index).html(values);
            var _htmls = '<input type="hidden" id="hid_item_value_' + _index + '" name="hid_item_value_' + _index + '" value="' + values + '" /><input type="hidden" id="hid_formula_item_value_' + _index + '" name="hid_formula_item_value_' + _index + '" value="0" /><div class="row radio_pickamount_' + _index + '"><div style="position:initial; float: right; " class="col-md-7 col-md-push-5"><input type="radio" id="radio_pickamount_wl_' + _index + '" name="radio_pickamount_' + _index + '" value="wl" onclick="pickAmount(' + _index + ', ' + "'wl'" + ',true);" /> Whichever is lesser<input type="radio" id="radio_pickamount_wg_' + _index + '" name="radio_pickamount_' + _index + '" style="margin-left: 10px ; " value="wg" onclick="pickAmount(' + _index + ', ' + "'wg'" + ',true);"  /> Whichever is greater</div></div>';
            $(this).parents(".det-parenens").append(_htmls);
        }
        setRemainingBalance();
    });

    function clearForms(_cons) {
        $("#hid_item_value_" + _cons).remove();
        $("#check-fixed-item-" + _cons).remove();
        $('#structure_det_value_' + _cons).removeAttr("onclick");
        $("#hid_item_value_" + _cons).remove();
        $("#radio_pickamount_wl_" + _cons).remove();
        $("#radio_pickamount_wg_" + _cons).remove();
        $("#hid_item_value_" + _cons).remove();
        $("#hid_formula_item_value_" + _cons).remove();
        $("#check-fixed-item-" + _cons).remove();
        $(".radio_pickamount_" + _cons).remove().hide();
        $("#equation_disp_" + _cons).html("");
        $("#cal_equation_" + _cons).val(null);
        $("structure_det_value_" + _cons).val(0).trigger('change');
        $(".label-det-val-" + _cons).html(0);
        // setRemainingBalance();
    }

    function checkIfStructureExists(callback) {
        var structure_name = $('#structure_name').val();
        var structure_id = $('#structure_id').val();
        $.ajax({
            url: 'SalaryStructure/checkstructureexists/' + structure_id,
            type: 'POST',
            data: {
                structure_name: structure_name
            },
            success: function(resp) {
                if (resp > 0) {
                    $.notify("Salary Structure already exists!", {
                        type: 'warning',
                        allow_dismiss: false
                    });
                    $('#structure_name').val('');
                } else {
                    if (typeof callback === 'function') {
                        callback.call();
                    }
                }
            }
        });
    }

    var dclass = "";
    var prev = "";
    var count = "";
    var symb = ["+", "-", "*", "/", ".", "(", ")"];
    jQuery(document).ready(function() {
        displayMonthysalary();
        //$( "#formula" ).dialog({  modal: true,autoOpen: false });
        //$( "#formula" ).dialog("close");
        $('.cal_button').click(function() {
            var place = "";
            var place_cal = "";
            if (!isNaN($(this).attr("value"))) { // pure number
                if (isNaN(prev)) {
                    place = $('#calc').val() + " " + $(this).attr("value");
                    place_cal = $('#cal_eq').val() + " " + $(this).attr("value");
                } else {
                    place = $('#calc').val() + $(this).attr("value");
                    place_cal = $('#cal_eq').val() + $(this).attr("value");
                }
            } else {
                if ($('#calc').val() != "")
                    place = $('#calc').val() + " " + $(this).attr("value");
                else
                    place = $(this).attr("value");
                if ($('#cal_eq').val() != "")
                    place_cal = $('#cal_eq').val() + " " + $(this).attr("value");
                else
                    place_cal = $(this).attr("value");

            }
            prev = $(this).attr("value");
            $("#calc").val(place);
            $("#cal_eq").val(place_cal);

        });
        $('.cal_sel_button').change(function() {
            var place = "";
            if ($('#calc').val() != "")
                place = $('#calc').val() + " " + this.options[this.selectedIndex].text
            else
                place = this.options[this.selectedIndex].text;
            if ($('#cal_eq').val() != "")
                place_cal = $('#cal_eq').val() + " " + this.value;
            else
                place_cal = this.value;
            prev = this.value;
            $("#calc").val(place);
            $("#cal_eq").val(place_cal);

        });

        $(document).on('change', '#structure_define', function() {
            var structure_define = $(this).val();
            changeCTCField(structure_define);
        });
        var structure_define = $('#structure_define').val();
        changeCTCField(structure_define);

        $("input[name^='hid_formula_item_value_']").each(function() {
            var index = $(this).attr('id').split('hid_formula_item_value_')[1];
            var value = eqEvaluvate($.trim($('#cal_equation_' + index).val()));
            $('#hid_formula_item_value_' + index).val(value);
        });

        //Set remaining balance on updating manually field
        $("input:text.salamount.manually").blur(function() {
            setRemainingBalance();
        });

        $("input:text.salamount.manually").change(function() {
            setRemainingBalance();
        });

        //To show checkbox for fixed items
        //On 07 July 2018
        $("input[id^='check-fixed-item-']").change(function() {
            var index = $(this).attr('id').split('check-fixed-item-')[1];
            if (this.checked) {
                $('#structure_det_value_' + index).val($('#hid_item_value_' + index).val());
            } else {
                $('#structure_det_value_' + index).val(0);
            }
            setRemainingBalance();
        });

    });

    //To show checkbox for fixed items
    //On 07 July 2018
    function onCheckChanged(_selectedMe) {
        var index = $(_selectedMe).attr('id').split('check-fixed-item-')[1];
        if (_selectedMe.checked) {
            $('#structure_det_value_' + index).val($('#hid_item_value_' + index).val());
        } else {
            $('#structure_det_value_' + index).val(0);
        }
        setRemainingBalance();
    }

    function changeCTCField(structure_define) {
        var label = '';
        if (structure_define == 1) {
            label = 'Min Salary/Wage';
        } else if (structure_define == 2) {
            label = 'Bymonthly Gross Salary';
        }
        $('#label-ctc').html(label);
        $('#structure_eg_amt').attr("placeholder", label);
    }

    function dpopup(e, cnt) {
        dclass = e.className.split(' ')[0];
        count = cnt;

        var rembalance = Number($('#hid_rembalance').val());
        if (rembalance > 0 && $('input[name="head_fkey_' + count + '"]').val() === '1') {
            console.log('rembalance is non zero');
            //$(".cal_sel_button option[value='rembalance']").remove();
            $('.cal_sel_button').showHideDropdownOptions('rembalance', true);
        } else {
            console.log('rembalance is zero');
            //$(".cal_sel_button option").eq(2).before($("<option></option>").val("rembalance").text("Remaining Balance"));
            $('.cal_sel_button').showHideDropdownOptions('rembalance', false);
        }

        $("input:text.salamount").each(function() {
            var $this = $(this);
            var salclass = ($this).attr("class").split(" ")[0];
            if ($this.val() == "" || $this.val() == 0)
                $('.cal_sel_button').showHideDropdownOptions(salclass, false);
            else
                $('.cal_sel_button').showHideDropdownOptions(salclass, true);
        });
        $('.cal_sel_button').prop('selectedIndex', 0);
        $("#calc").val($(e).next().next().val());
        $("#cal_eq").val($(e).next().next().next().val());

        //On 23 Feb 2016
        //var rembalance = Number($('#month_salary').html());
        //$('.cal_sel_button>option:eq(2)').prop('value', rembalance);
        //$('#hid_rembalance').val(rembalance);

        //$( "#formula" ).dialog("open");
        $('#formula').modal('show');

    }

    function clearAll() {
        $("#calc").val("");
        $("#cal_eq").val("");
        prev = "";
        $('.cal_sel_button').prop('selectedIndex', 0);

    }
    /*function dclose(cnt)
    {
        prev = "";
        $("#equation_" + cnt).val("");
        $("#cal_equation_" + cnt).val("");
        $('#structure_det_value_' + cnt).val('');
        
        if ( $( '#hid_formula_item_value_'+cnt ).length ) {
            $('#hid_formula_item_value_' + cnt).val('');
            //pickAmount(cnt,'wl');
            $('#radio_pickamount_wl_'+cnt).trigger('click');
        }
        
        $('#equation_disp_' + cnt).html('');
        setRemainingBalance();
        $('#formula').modal('hide');
    }*/
    function ok() {
        var donotupdate = false;

        $('label[for="' + dclass + '"]').html($("#calc").val());
        $("#equation_" + count).val($("#calc").val());
        $("#cal_equation_" + count).val($.trim($("#cal_eq").val()));
        $("#cal_equation_" + count).next().val('formula');

        //var value = ($('#cal_equation_' + count).val() !== 'rembalance') ? eqEvaluvate($.trim($("#cal_eq").val())) : (($('input.' + dclass + '[type="text"]').val() !== '' && $('input.' + dclass + '[type="text"]').val() !== '0') ? $('input.' + dclass + '[type="text"]').val() : eqEvaluvate($.trim($("#cal_eq").val())));
        var value = 0;

        if ($('#cal_equation_' + count).val() !== 'rembalance') {
            value = eqEvaluvate($.trim($("#cal_eq").val()));
        } else {
            //if($('input.' + dclass + '[type="text"]').val() !== '' && $('input.' + dclass + '[type="text"]').val() !== '0'){
            $(".rembalance").parents(".det-parenens").find(".structure_det_operator").val("");
            $(".rembalance").removeClass(".rembalance").css("color", "black");
            // $(".label-det-val-"+count).parent().css("color","orange").addClass("rembalance");
            if (Number($('#hid_rembalance').val()) <= 0) {

                value = $('input.' + dclass + '[type="text"]').val();
                donotupdate = true;
            } else {

                value = eqEvaluvate($.trim($("#cal_eq").val()))
            }
        }
        //added by megha on 8/01/2020 Error equation warning
        if (value == 'error') {
            clearAll();
            ok();
            $.notify("Please check the formula.", {
                type: 'warning',
                allow_dismiss: false
            });
        } else {
            //On 26 July 2016
            //$('input.' + dclass + '[type="text"]').val(value);
            if ($('#hid_formula_item_value_' + count).length) {
                $('#hid_formula_item_value_' + count).val(value);
                //By default choose lesser value
                $('#radio_pickamount_wl_' + count).trigger('click');
            } else {
                $('input.' + dclass + '[type="text"]').val(value);
            }

            prev = "";
            count = "";
            $("#calc").val("");
            $("#cal_eq").val("");
            $('.cal_sel_button').prop('selectedIndex', 0);
            //$( "#formula" ).dialog("close");
            $('#formula').modal('hide');

            if (donotupdate) {
                return false;
            } else {
                var k = 1;
                //displayMonthysalary();

                //On 24 Feb 2016
                /*$("input:radio.formula").each(function () {
                 var $this = $(this);
                 if ($(this).prop('checked') && $(this).attr("value") == 2) {
                 var mclass = $(this).attr("class").split(" ")[0];
                 var value = eqEvaluvate($.trim($('#cal_equation_' + k).val()));
                 $('input.' + mclass + '[type="text"]').val(value);
                 }
                 k++;
                 });*/
                $("input[name^='structure_det_operator_']").each(function() {
                    //On 13 July 2016
                    //if ($(this).val() == 'formula' || $('input[name="item_type_'+k+'"').val() == 'limit') {
                    if (($(this).val() == 'formula' || $('input[name="item_type_' + k + '"').val() == 'limit_wl') && $('#cal_equation_' + k).val() != 'rembalance') {
                        //if ($(this).val() == 'formula' && $('#cal_equation_' + k).val() != 'rembalance') {
                        //if ($(this).val() == 'formula') {
                        //if($('#cal_equation_' + k).val() == 'rembalance' && Number($('#hid_rembalance').val()) < 0){
                        //    continue;
                        //}
                        var mclass = $(this).attr("class").split(" ")[0];
                        var value = eqEvaluvate($.trim($('#cal_equation_' + k).val()));

                        //On 26 July 2016
                        //$('input.' + mclass + '[type="text"]').val(value);
                        if ($('#hid_formula_item_value_' + k).length) {
                            $('#hid_formula_item_value_' + k).val(value);
                            //pickAmount(k,'wl');
                            //$('#radio_pickamount_wl_'+k).trigger('click');
                            //On 01 Aug 2016
                            var mode = $("input[name='radio_pickamount_" + k + "']:checked").val();
                            //$('#radio_pickamount_'+mode+'_'+k).trigger('click');
                            pickAmount(k, mode);
                        } else {
                            $('input.' + mclass + '[type="text"]').val(value);
                        }
                    }
                    k++;
                });

                setRemainingBalance();

                //Moved by dev from here to Line --> 271, on 04 Oct 2017

                allCalculate();
                setRemainingBalance();

                //To set remaining balance, Added on 04 Oct 2017
                var currentrembalance = Number($('#hid_rembalance').val());
                if (currentrembalance != 0) {
                    $("input[name^='cal_equation_']").each(function() {
                        if ($(this).val() == 'rembalance') {
                            // $(this).parents(".det-parenens").find("label").css("color","orange");
                            var index = $(this).attr('id').split('cal_equation_')[1];
                            var newrembalance = currentrembalance; // + Number($('#structure_det_value_'+index).val());

                            if (newrembalance < 0) {
                                $('#structure_det_value_' + index).val(0);
                                $('#month_salary').html(newrembalance);
                                $('#hid_rembalance').val(newrembalance);
                                console.log("if newrembalance : " + newrembalance);
                            } else {
                                $('#structure_det_value_' + index).val(newrembalance);
                                //Added by dev on 04 Oct 2017
                                $('#month_salary').html(0);
                                $('#hid_rembalance').val(0);
                                console.log("else newrembalance : " + 0);
                                //Ends
                            }
                            setRemainingBalance();
                        }
                    }); //Iam here
                }
            }
        }
    }

    function allCalculate() {
        //displayMonthysalary();
        var k = 1;

        //No Need to process here : On 24 Feb 2016
        /*$("input:radio.formula").each(function () {
         var $this = $(this);
         if ($(this).prop('checked') && $(this).attr("value") == 2) {
         var mclass = $(this).attr("class").split(" ")[0];
         var value = eqEvaluvate($.trim($('#cal_equation_' + k).val()));
         $('input.' + mclass + '[type="text"]').val(value);
         }
         k++;
         });*/
        $("input[name^='structure_det_operator_']").each(function() {
            //On 13 July 2016

            if (($(this).val() == 'formula' || $('input[name="item_type_' + k + '"').val() == 'limit_wl') && $('#cal_equation_' + k).val() != 'rembalance') {

                //if ($(this).val() == 'formula' && $('#cal_equation_' + k).val() != 'rembalance') {
                //if ($(this).val() == 'formula') {
                //if($('#cal_equation_' + k).val() == 'rembalance' && Number($('#hid_rembalance').val()) < 0){
                //    continue;
                //}
                var mclass = $(this).attr("class").split(" ")[0];
                var value = eqEvaluvate($.trim($('#cal_equation_' + k).val()));
                console.log("each Function: " + $(this).val() + $(this).attr("class") + ' eq: ' + value);
                $('#structure_det_value_' + k).val(value);
                //On 26 July 2016
                //$('input.' + mclass + '[type="text"]').val(value);
                if ($('#hid_formula_item_value_' + k).length) {
                    $('#hid_formula_item_value_' + k).val(value);
                    //pickAmount(k,'wl');
                    //$('#radio_pickamount_wl_'+k).trigger('click');
                    //On 01 Aug 2016
                    var mode = $("input[name='radio_pickamount_" + k + "']:checked").val();
                    //$('#radio_pickamount_'+mode+'_'+k).trigger('click');
                    pickAmount(k, mode);
                } else {
                    $('input.' + mclass + '[type="text"]').val(value);
                }
            }
            k++;
        });

        //setRemainingBalance();
    }

    function setRemainingBalance() {
        console.log("entered");
        var isValidAmt = true;
        var rembalance = Math.round($('#structure_eg_amt').val());
        $(".salamount").each(function() {
            var $this = $(this);

            //Set remaining balance on updating manually field
            //console.log('aval:'+$(this).val()+'--'+isNaN($(this).val()));
            //if (!isNaN($(this).val()) && $(this).siblings('input[name^="item_head_operator_"]').val().toLowerCase() == 'addition' && $(this).siblings('input[name^="item_head_occurance_"]').val().toLowerCase() == 'fixed' && $(this).siblings('input[name^="structure_det_operator_"]').val().toLowerCase() !== 'na' && $(this).siblings('input[name^="structure_det_operator_"]').val().toLowerCase() !== 'leave') {
            if (
                !isNaN($(this).val())
                //&& 
                //$(this).siblings('input[name^="item_head_operator_"]').val().toLowerCase() == 'addition' 
                &&
                (($(this).siblings('input[name^="item_type_"]').val().toLowerCase() == 'limit_wl' || $(this).siblings('input[name^="item_type_"]').val().toLowerCase() == 'limit_wg' || $(this).siblings('input[name^="item_type_"]').val().toLowerCase() == 'limit') || $(this).siblings('input[name^="item_type_"]').val().toLowerCase() == 'fixed' || $(this).siblings('input[name^="item_type_"]').val().toLowerCase() == 'formula' || $(this).siblings('input[name^="item_type_"]').val().toLowerCase() == 'manually') &&
                $(this).siblings('input[name^="item_head_occurance_"]').val().toLowerCase() != 'variable' &&
                $(this).siblings('input[name^="item_head_occurance_"]').val().toLowerCase() != 'reimbursements' &&
                //On 13 July 2016
                //($(this).siblings('input[name^="item_part_"]').val().toLowerCase() == 'indirect' || ($(this).siblings('input[name^="item_part_"]').val().toLowerCase() == 'direct' && $(this).siblings('input[name^="item_head_operator_"]').val().toLowerCase() == 'addition'))
                ($(this).siblings('input[name^="item_part_"]').val().toLowerCase() == 'direct' && $(this).siblings('input[name^="item_head_operator_"]').val().toLowerCase() == 'addition') &&
                $(this).siblings('input[name^="structure_det_operator_"]').val().toLowerCase() !== 'na' &&
                $(this).siblings('input[name^="structure_det_operator_"]').val().toLowerCase() !== 'leave'
            ) {
                rembalance = Math.round(rembalance - Number($(this).val()));
            }
        });
        if (isValidAmt) {
            console.log("rembalance --> " + rembalance);
            $('#month_salary').html(rembalance);
            $('#hid_rembalance').val(rembalance);
        } else {
            $.notify("Keep remaining amount should be zero", {
                type: 'warning',
                allow_dismiss: false
            });
        }
    }

    function getRemainingBalance() {
        var rembalance = $('#structure_eg_amt').val();
        $(".salamount").each(function() {
            var $this = $(this);
            //console.log('gval:'+$(this).val()+'--'+isNaN($(this).val()));
            //if (!isNaN($(this).val()) && $(this).siblings('input[name^="item_head_operator_"]').val().toLowerCase() == 'addition' && $(this).siblings('input[name^="item_head_occurance_"]').val().toLowerCase() == 'fixed' && $(this).siblings('input[name^="structure_det_operator_"]').val().toLowerCase() !== 'na' && $(this).siblings('input[name^="structure_det_operator_"]').val().toLowerCase() !== 'leave') {
            if (
                !isNaN($(this).val())
                //&& 
                //$(this).siblings('input[name^="item_head_operator_"]').val().toLowerCase() == 'addition' 
                &&
                ($(this).siblings('input[name^="item_type_"]').val().toLowerCase() == 'limit_wl' || $(this).siblings('input[name^="item_type_"]').val().toLowerCase() == 'fixed' || $(this).siblings('input[name^="item_type_"]').val().toLowerCase() == 'formula') &&
                $(this).siblings('input[name^="item_head_occurance_"]').val().toLowerCase() != 'variable' &&
                $(this).siblings('input[name^="item_head_occurance_"]').val().toLowerCase() != 'reimbursements' &&
                //On 13 July 2016
                //($(this).siblings('input[name^="item_part_"]').val().toLowerCase() == 'indirect' || ($(this).siblings('input[name^="item_part_"]').val().toLowerCase() == 'direct' && $(this).siblings('input[name^="item_head_operator_"]').val().toLowerCase() == 'addition'))
                ($(this).siblings('input[name^="item_part_"]').val().toLowerCase() == 'direct' && $(this).siblings('input[name^="item_head_operator_"]').val().toLowerCase() == 'addition') &&
                $(this).siblings('input[name^="structure_det_operator_"]').val().toLowerCase() !== 'na' &&
                $(this).siblings('input[name^="structure_det_operator_"]').val().toLowerCase() !== 'leave'
            ) {
                if (rembalance - $(this).val() >= 0) {
                    //console.log('gsubstract val:'+$(this).val()+'--'+isNaN($(this).val()));
                    rembalance = Math.round(rembalance - $(this).val());
                }
            }
        });

        //if(rembalance == 0){
        //Clear all heads having Remaining Balance in formulae
        $("input[name^='cal_equation_']").each(function() {
            if ($(this).val() == 'rembalance') {
                var index = $(this).attr('id').split('cal_equation_')[1];
                $(this).val('');
                //$('#structure_det_value_'+index).val('');
                $('#structure_det_value_' + index).val(0);
                $('#equation_' + index).val('');
                $('#equation_disp_' + index).html('');
            }
        });
        //}else{
        //    $('#month_salary').html(rembalance);
        //}
    }

    function eqEvaluvate(equation) {
        var arr_eq = equation.split(" ");
        var final_eq = "";
        for (var i = 0; i < arr_eq.length; i++) {

            if (isNaN(arr_eq[i]) && symb.indexOf(arr_eq[i]) == -1) {
                //final_eq = (final_eq == "") ? $('input.' + arr_eq[i] + '[type="text"],input.' + arr_eq[i] + '[type="hidden"]').val() : final_eq + $('input.' + arr_eq[i] + '[type="text"],input.' + arr_eq[i] + '[type="hidden"]').val();
                if (final_eq == "") {
                    if (typeof($('input.' + arr_eq[i] + '[type="text"],input.' + arr_eq[i] + '[type="hidden"]').val()) !== 'undefined') {
                        final_eq = $('input.' + arr_eq[i] + '[type="text"],input.' + arr_eq[i] + '[type="hidden"]').val();
                    } else {
                        final_eq = '0';
                    }
                } else {
                    final_eq = final_eq + $('input.' + arr_eq[i] + '[type="text"],input.' + arr_eq[i] + '[type="hidden"]').val();
                }
            } else {
                final_eq = (final_eq == "") ? arr_eq[i] : final_eq + arr_eq[i];
            }
        }
        if (final_eq != '') {


            try {
                return eval(final_eq).toFixed(2);
            } catch (Exception) {
                return 'error';
            }
        } else {
            return 0;
        }
    }

    function displayMonthysalary() {

        var e = "#structure_eg_amt";

        if ($(e).val() != "") {

            switch ($('#structure_define').val()) {
                case "1":
                    //$('#month_salary').html($(e).val());
                    $('#hid_month_sal').val($(e).val());
                    break;
                case "2":
                    //$('#month_salary').html($(e).val() / 2);
                    $('#hid_month_sal').val($(e).val() / 2);
                    $(e).val($(e).val() / 2);
                    break;
                case "3":
                    //$('#month_salary').html($(e).val());
                    $('#hid_month_sal').val($(e).val());
                    break;
            }
            //$('#month_salary').html($(e).val());
        }
        getRemainingBalance();
        allCalculate();
        setRemainingBalance();
        return false;

    }

    function showDetdropdownOptions(argument) {
        // body...
        if ($(argument).attr("data-toggler") == "show") {
            $(".det").hide();
            $(argument).parent(".show-drop-down").find(".det").show();
            $(argument).attr("data-toggler", "hide");
        } else {
            $(argument).parent(".show-drop-down").find(".det").hide();
            $(argument).attr("data-toggler", "show");
        }
    }
    /**
     Nov 11 new functions
     */
    var companyCode = "<?= $company_code ?>"; // Edited by Akshay on 16-1-2026
    var fixedDays = "<?= $fixed_days ?>";
    function proCodeChange(e) {
        // Edited by Akshay on 21-01-2026
        if (fixedDays) {
            toggleFixedDays();
        }

        switch (e.value) {
            case "1":
                $('#procode_desc').val('Calendar days');
                $("#procode_desc").prop('readonly', true);
                break;
            case "2":
                $('#procode_desc').val('Depend on shift policy');
                $("#procode_desc").prop('readonly', true);
                break;
            case "3":
                // Edited by Akshay on 16-1-2026
                if (companyCode === 'ABSG') {
                    $('#procode_desc').val('26 days per month');
                } else if (fixedDays) {
                    $('#procode_desc').val('Custom fixed days per month');
                } else {
                    $('#procode_desc').val('30 days per month');
                }
                // End
                $("#procode_desc").prop('readonly', true);
                break;
        }
    }

    function pickAmount(index, mode, allcalculate) {
        var item_value = (!isNaN(parseFloat($('#hid_item_value_' + index).val()))) ? parseFloat($('#hid_item_value_' + index).val()) : 0;
        var formula_item_value = (!isNaN(parseFloat($('#hid_formula_item_value_' + index).val()))) ? parseFloat($('#hid_formula_item_value_' + index).val()) : 0;
        if (mode === 'wl') {
            $('#structure_det_value_' + index).val(Math.min(item_value, formula_item_value));
            $('#structure_det_operator_' + index).val('limit_wl');
        } else if (mode === 'wg') {
            $('#structure_det_value_' + index).val(Math.max(item_value, formula_item_value));
            $('#structure_det_operator_' + index).val('limit_wg');
        } else {
            return false;
        }

        //On 26 July 2016
        //$( "#structure_det_value_" + index ).trigger( "change" );
        if (typeof allcalculate !== 'undefined' && allcalculate == true) {
            allCalculate();
            setRemainingBalance();
        }
        return true;
    }
</script>
<style>
    .salary-structure-calculator {
        margin: 16px;
    }

    .salary-structure-calculator .row div {
        padding: 0px;
    }
</style>
<?php $arr = array('(', ')', ' ', '&'); ?>
<form id="spForm" action="<?php echo $this->webroot; ?>SalaryStructure/savesalarystructuresetup" method="post" class="class-sal-structure">
    <input type="hidden" id="structure_id" name="structure_id" value="<?php echo isset($salaryLoadItems['structure_id']) ? $salaryLoadItems['structure_id'] : 0 ?>" />
    <?php if (isset($salaryLoadItems['structure_id'])) { ?>
        <div class="modal-header">
            <button type="button" class="btn btn-default" onclick="showSalaryStructureView();">Cancel</button>
        </div>
    <?php } ?>
    <div class="modal-body">
        <fieldset class="form-group fld-sal-structure">
            <legend>Structure details</legend>
            <div class="row">
                <div class="col-sm-6">
                    <div class="form-group">
                        <label> Structure Name <span class="star">*</span></label>
                        <input type="text" value="<?php echo isset($salaryLoadItems['structure_name']) ? $salaryLoadItems['structure_name'] : '' ?>" name="structure_name" placeholder="Structure Name" id="structure_name" class="form-control" required="" />
                    </div>
                </div>
                <div class="col-sm-6">
                    <div class="form-group">
                        <label>Pay Period<span class="star">*</span></label>
                        <select name="structure_define" class="form-control" required="" id="structure_define">
                            <option value="1" <?php echo (isset($salaryLoadItems['defined_structure_for']) && $salaryLoadItems['defined_structure_for'] == 1) ? "selected" : '' ?>>Monthly</option>
                            <option value="3" <?php echo (isset($salaryLoadItems['defined_structure_for']) && $salaryLoadItems['defined_structure_for'] == 3) ? "selected" : '' ?>>Daily </option>
                            <!-- option value="2" <?php echo (isset($salaryLoadItems['defined_structure_for']) && $salaryLoadItems['defined_structure_for'] == 2) ? "selected" : '' ?>>Bimonthly</option-->
                        </select>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-sm-6">
                    <div class="form-group">
                        <label>Salary Calculation Logic <span class="star">*</span></label>
                        <select name="procode" class="form-control" required="" id="procode" onchange="proCodeChange(this);">
                            <option value="1" <?php echo (isset($salaryLoadItems['prorate_code']) && $salaryLoadItems['prorate_code'] == 1) ? "selected" : '' ?>>Calendar</option>
                            <option value="2" <?php echo (isset($salaryLoadItems['prorate_code']) && $salaryLoadItems['prorate_code'] == 2) ? "selected" : '' ?>>Working days</option>
                            <option value="3" <?php echo (isset($salaryLoadItems['prorate_code']) && $salaryLoadItems['prorate_code'] == 3) ? "selected" : '' ?>>Fixed days</option>
                        </select>
                    </div>
                </div>
                <div class="col-sm-6">
                    <div class="form-group">
                        <label> Method Explanation<span class="star">*</span></label>
                        <input type="text" value="<?php echo isset($salaryLoadItems['prorate_desc']) ? $salaryLoadItems['prorate_desc'] : 'Calendar days' ?>" name="procode_desc" placeholder="Procode description" id="procode_desc" class="form-control" required="" readonly />
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-sm-6">
                    <div class="form-group">
                        <label for="structure_eg_amt" id="label-ctc"> Annual Gross Salary <span class="star">*</span></label>
                        <input type="number" value="<?php echo isset($salaryLoadItems['structure_eg_amt']) ? $salaryLoadItems['structure_eg_amt'] : 0 ?>" name="structure_eg_amt" placeholder="Annual Gross Salary" id="structure_eg_amt" class="form-control" required="" onChange="displayMonthysalary();" />
                        <input type="hidden" id="hid_month_sal" class="monthsal" />
                    </div>
                </div>

                <!-- Edited by Akshay on 21-01-2026 -->
                <?php
                if ($fixed_days) {
                ?>
                    <div class="col-sm-6" id="fixedDaysRow" style="display:none;">
                        <div class="form-group">
                            <label>Enter Fixed Days <span class="star">*</span></label>
                            <!-- Edited by Akshay on 29 -->
                            <input type="number" id="fixed_days" name="fixed_days" class="form-control" value="<?php echo isset($salaryLoadItems['fixed_days']) ? $salaryLoadItems['fixed_days'] : 0 ?>" min="1" max="31" placeholder="Enter number of days" onchange="updateFixedDaysDesc(this);" oninput="validateFixedDays(this);">
                        </div>
                    </div>
                <?php
                }
                ?>
            </div>
            <!-- End -->
            <!--            <div class="row">
                <div class="col-sm-6">
                    <div class="form-group">
                        <labell> Effective start date<span class="star">*</span></label>
                        <input type="text" value="<?php echo isset($salaryLoadItems['startdate_effective']) ? $salaryLoadItems['startdate_effective'] : '' ?>"  name="eff_satrt_date" placeholder="Effective start date" id="eff_satrt_date" class="form-control" required="" />
                    </div>
                </div>
                <div class="col-sm-6">
                    <div class="form-group">
                        <label> Effective end date<span class="star">*</span></label>
                        <input type="text" value="<?php echo isset($salaryLoadItems['enddate_effective']) ? $salaryLoadItems['enddate_effective'] : '' ?>"  name="eff_end_date" placeholder="Effective end date" id="eff_end_date" class="form-control" required="" />
                    </div>
                </div>
            </div>-->
            <div class="row">
                <div class="col-sm-12">
                    <div class="form-group">
                        <label> Remaining Amount</label>
                        <label id="month_salary"></label>
                        <input type="hidden" id="hid_rembalance" class="rembalance" />
                    </div>
                </div>
            </div>
        </fieldset>
        <!--div class="row border"-->
        <?php
        $i = 1;
        //debug($salaryHeadItems);
        foreach ($salaryHeadItems as $key => $value) {
        ?>
            <!--div class="col-sm-12 col-lg-12">
                <h3><label><?php echo $key; ?></label></h3-->
            <fieldset class="form-group fld-sal-structure">
                <legend><?php echo $key; ?></legend>
                <?php foreach ($value as $salkey => $salvalue) { ?>
                    <input type="hidden" id="hid_item_value_to_save_<?php echo $i; ?>" name="hid_item_value_to_save_<?php echo $i; ?>" value="<?php echo $salvalue['item_value']; ?>" />
                    <div class="row det-parenens">
                        <div class="col-sm-12 col-lg-5 col-md-5 show-drop-down">
                            <?php
                            // debug($salvalue);
                            $salvalue['ogType'] = $salvalue['item_type'];
                            $salvalue['item_type'] = isset($salvalue['det']['0']['structure_det_operator']) ? $salvalue['det']['0']['structure_det_operator'] : $salvalue['item_type'];
                            //To show checkbox for fixed items
                            //On 07 July 2018
                            if ($salvalue['item_type'] == 'rembalance') {
                                $salvalue['item_type'] = $salvalue['ogType'];
                                $showColors = true;
                            } else {
                                $showColors = false;
                            }
                            if ($salvalue['item_type'] == 'fixed') {
                                if (isset($salvalue['det']) && !empty($salvalue['det'])) {
                                    $check_box_val = $salvalue['det']['0']['structure_det_value'];
                                    if ($salvalue['det']['0']['structure_det_value'] != 0) {
                                        $checked_fixed = true;
                                    } else {
                                        $checked_fixed = false;
                                    }
                                } else {
                                    $checked_fixed = true;
                                    $check_box_val = $salvalue['item_value'];
                                }

                            ?>
                                <input type="hidden" id="hid_item_value_<?php echo $i; ?>" name="hid_item_value_<?php echo $i; ?>" value="<?php echo $salvalue['item_value']; ?>" />
                                <input type="checkbox" id="check-fixed-item-<?php echo $i; ?>" <?php echo ($checked_fixed) ? 'checked="checked"' : ''; ?> />
                            <?php } ?>
                            <?php
                            if ($salvalue['item_type'] == 'limit_wl' || 'limit_wg') {
                                $limitVal = isset($salvalue['det'][0]['structure_det_depends']) ? $salvalue['det'][0]['structure_det_depends'] : $salvalue['item_value'];
                            } else {
                                $limitVal =  $salvalue['item_value'];
                            }
                            ?>
                            <label class="<?php if ($showColors) {
                                                echo 'rembalance';
                                            } ?>" style="<?php if ($showColors) {
                                                                echo 'color:  orange; ';
                                                            } ?>"><?php echo trim($salvalue['item_name']); ?> - (<span class="label-det"><?php echo isset($salvalue['item_type']) ? $salvalue['item_type'] : ''; ?></span>
                                <?php //echo (isset($salvalue['occurance']) && $salvalue['occurance'] != '') ? '-' . $salvalue['occurance'] : ''; 
                                ?>) <span class="label-det-val-<?php echo $i; ?>"><?php echo ($limitVal != '') ? ' - ' . $limitVal : ''; ?></span></label><a class="edit-dets" data-toggler="show" onclick="showDetdropdownOptions(this);">
                                <li class="fa fa-pencil"></li>
                            </a>
                            <ul style="display: none; " class="det" data-src="<?php echo $i; ?>">
                                <li class="det-items">formula</li>
                                <?php if ($key != 'Heads for Monthly Earnings' && $key != 'Monthly Salary Components') { ?>
                                    <li class="det-items">fixed</li>
                                    <li class="det-items">manually</li>
                                <?php } ?>
                                <li class="det-items">limit</li>
                            </ul>
                            <input type="hidden" name="item_name_<?php echo $i; ?>" value="<?php echo $salvalue['item_pkey']; ?>" />
                        </div>
                        <!-- div class="col-sm-6 col-lg-6">
                            <div class="col-sm-12 col-lg-12">
                                <input type="radio" value="1" name="structure_det_operator_<?php echo $i; ?>" <?php
                                                                                                                if (isset($salvalue['det'][0]))
                                                                                                                    echo ($salvalue['det'][0]['structure_det_operator'] == 1) ? "checked" : "";
                                                                                                                else
                                                                                                                    echo "checked";
                                                                                                                ?> onClick="dclose(<?php echo $i; ?>)" class="<?php echo str_replace($arr, '_', $salvalue['item_pkey'] . "_" . trim($salvalue['item_name'])); ?>"/>
                                <input type="radio" value="2" name="structure_det_operator_<?php echo $i; ?>" <?php if (isset($salvalue['det'][0])) echo ($salvalue['det'][0]['structure_det_operator'] == 2) ? "checked" : ""; ?>  class="<?php echo str_replace($arr, '_', $salvalue['item_pkey'] . "_" . trim($salvalue['item_name'])); ?> formula"/>
                                
                            </div>
                        </div-->
                        <?php
                        $disabled = "";
                        if ($salvalue['item_type'] == 'fixed')
                            $disabled = "readonly";

                        //if($salvalue['item_type'] === 'na' || $salvalue['item_type'] === 'leave')
                        if ($salvalue['item_type'] != 'formula' && $salvalue['item_type'] != 'limit_wl')
                            $showFormula = false;
                        else
                            $showFormula = true;

                        if ($salvalue['item_type'] == 'rembalance') $showFormula = true;
                        if ($salvalue['item_type'] == 'limit_wg' || $salvalue['item_type'] == 'limit_wl' || $salvalue['item_type'] == 'limit') {
                            $showFormula = true;
                        }
                        if ($salvalue['item_type'] == 'limit_wl') {
                            //On 26 July 2016
                            //$default_value = isset($salvalue['item_value'])?$salvalue['item_value']:'';
                            $default_value = 0;
                        } else {
                            $default_value = 0;
                        }

                        if ($salvalue['item_type'] == 'fixed') {
                            $salvalue['item_value'] = $check_box_val;
                        } else {
                            $salvalue['item_value'];
                        }

                        ?>
                        <div class="col-sm-12 col-lg-7 col-md-7">
                            <div class="row">
                                <div class="col-sm-12 col-lg-4 col-md-4">
                                    <div class="form-group">
                                        <?php if ($disabled != '') { ?>
                                            <input step="any" style="<?php if ($salvalue['item_type'] == 'rembalance') {
                                                                            echo 'border: 1px solid orange; ';
                                                                        } ?>" type="text" name="structure_det_value_<?php echo $i; ?>" id="structure_det_value_<?php echo $i; ?>" value="<?php echo (isset($salvalue['item_value']) && $salvalue['item_value'] != '') ? $salvalue['item_value'] : 0; ?>" <?php // echo $disabled; 
                                                                                                                                                                                                                                                                                                            ?> class="<?php echo str_replace($arr, '_', $salvalue['item_pkey'] . "_" . trim($salvalue['item_name'])); ?> salamount form-control" />
                                        <?php } else { ?>
                                            <input step="any" style="<?php if ($salvalue['item_type'] == 'rembalance') {
                                                                            echo 'border: 1px solid orange; ';
                                                                        } ?>" type="text" name="structure_det_value_<?php echo $i; ?>" id="structure_det_value_<?php echo $i; ?>" value="<?php echo isset($salvalue['det'][0]) ? ((isset($salvalue['det'][0]['structure_det_value']) && $salvalue['det'][0]['structure_det_value'] != '') ? $salvalue['det'][0]['structure_det_value'] : 0) : $default_value; ?>" <?php //echo $disabled; 
                                                                                                                                                                                                                                                                                                                                                                                                                    ?> <?php echo ($showFormula) ? 'onClick="dpopup(this,' . $i . ');"' : ''; ?> class="<?php echo str_replace($arr, '_', $salvalue['item_pkey'] . "_" . trim($salvalue['item_name'])); ?> salamount <?php echo isset($salvalue['item_type']) ? $salvalue['item_type'] : ''; ?> form-control" />
                                        <?php } ?>
                                        <input type="hidden" name="equation_<?php echo $i; ?>" id="equation_<?php echo $i; ?>" value="<?php echo isset($salvalue['det'][0]) ? $salvalue['det'][0]['structure_formula'] : ""; ?>" />
                                        <input type="hidden" name="cal_equation_<?php echo $i; ?>" id="cal_equation_<?php echo $i; ?>" value="<?php echo isset($salvalue['det'][0]) ? $salvalue['det'][0]['structure_det_calequation'] : ""; ?>" />
                                        <!-- On 23 Feb 2016 -->
                                        <!--input type="text" name="structure_det_operator_<?php echo $i; ?>" id="structure_det_operator_<?php echo $i; ?>" value="<?php echo isset($salvalue['det'][0]) ? $salvalue['det'][0]['structure_det_operator'] : $salvalue['item_type']; ?>" /-->
                                        <input type="hidden" class="structure_det_operator <?php echo str_replace($arr, '_', $salvalue['item_pkey'] . "_" . trim($salvalue['item_name'])); ?>" name="structure_det_operator_<?php echo $i; ?>" id="structure_det_operator_<?php echo $i; ?>" value="<?php echo isset($salvalue['det'][0]) ? $salvalue['det'][0]['structure_det_operator'] : $salvalue['item_type']; ?>">

                                        <input type="hidden" name="item_head_operator_<?php echo $i; ?>" value="<?php echo $salvalue['item_head_operator']; ?>" />
                                        <input type="hidden" name="item_head_occurance_<?php echo $i; ?>" value="<?php echo $salvalue['item_head_occurance']; ?>" />
                                        <input type="hidden" name="item_type_<?php echo $i; ?>" class="item-type" value="<?php echo $salvalue['item_type']; ?>" />
                                        <input type="hidden" name="item_part_<?php echo $i; ?>" value="<?php echo $salvalue['item_part']; ?>" />
                                        <input type="hidden" name="head_fkey_<?php echo $i; ?>" value="<?php echo $salvalue['head_fkey']; ?>" />
                                    </div>
                                </div>
                                <div class="col-sm-12 col-lg-8 col-md-8">
                                    <div class="form-group">
                                        <label id="equation_disp_<?php echo $i; ?>" class="cal_disp" for="<?php echo str_replace($arr, '_', $salvalue['item_pkey'] . "_" . trim($salvalue['item_name'])); ?>"><?php echo isset($salvalue['det'][0]) ? $salvalue['det'][0]['structure_formula'] : ""; ?></label>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php if ($salvalue['item_type'] == 'limit' || $salvalue['item_type'] == 'limit_wg' || $salvalue['item_type'] == 'limit_wl') { ?>
                        <?php $limitVal = isset($salvalue['det'][0]['structure_det_depends']) ? $salvalue['det'][0]['structure_det_depends'] : $salvalue['item_value']; ?>
                        <input type="hidden" id="hid_item_value_<?php echo $i; ?>" name="hid_item_value_<?php echo $i; ?>" value="<?php echo $limitVal; ?>" />
                        <input type="hidden" id="hid_formula_item_value_<?php echo $i; ?>" name="hid_formula_item_value_<?php echo $i; ?>" value="<?php echo isset($salvalue['det'][0]) ? $salvalue['det'][0]['structure_det_value'] : ''; ?>" />
                        <div class="row radio_pickamount_<?php echo $i; ?>">
                            <!--div class="col-sm-4 col-sm-push-4 col-lg-4 col-lg-push-4 col-md-4 col-md-push-4">
                                <button type="button" class="btn btn-default" onclick="pickAmount(<?php echo $i; ?>, 'wl');">
                                    Whichever is lesser
                                </button>
                            </div>
                            <div class="col-sm-4 col-sm-push-2 col-lg-4 col-lg-push-2 col-md-4 col-md-push-2">
                                <button type="button" class="btn btn-default" onclick="pickAmount(<?php echo $i; ?>, 'wg');">
                                    Whichever is greater
                                </button>
                            </div-->
                            <div class="col-sm-4 col-sm-push-5 col-lg-4 col-lg-push-5 col-md-4 col-md-push-5">
                                <input type="radio" id="radio_pickamount_wl_<?php echo $i; ?>" name="radio_pickamount_<?php echo $i; ?>" value="wl" onclick="pickAmount(<?php echo $i; ?>, 'wl',true);" <?php echo isset($salvalue['det'][0]['structure_det_operator']) ? (($salvalue['det'][0]['structure_det_operator'] == 'limit_wl') ? 'checked="checked"' : '') : 'checked="checked"'; ?> /> Whichever is lesser
                                <input type="radio" id="radio_pickamount_wg_<?php echo $i; ?>" name="radio_pickamount_<?php echo $i; ?>" value="wg" onclick="pickAmount(<?php echo $i; ?>, 'wg',true);" <?php echo (isset($salvalue['det'][0]['structure_det_operator']) && $salvalue['det'][0]['structure_det_operator'] == 'limit_wg') ? 'checked="checked"' : ''; ?> /> Whichever is greater
                            </div>
                        </div>
                    <?php } ?>
                    <hr>
                <?php
                    $i++;
                }
                ?>
                <!--/div-->
            </fieldset>
        <?php } ?>
        <input type="hidden" name="item_count" value="<?php echo $i; ?>" />
        <!--/div-->
    </div>
    <div class="modal-footer">
        <button type="submit" class="btn btn-primary">
            Save
        </button>
    </div>
</form>

<!--div id="formula" title="Formula builder"-->
<div id="formula" class="modal fade" role="dialog">
    <div class="modal-dialog modal-sm">
        <!-- Modal content-->
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">&times;</button>
                <h4 class="modal-title">Formula builder</h4>
            </div>
            <div class="modal-body">
                <fieldset class="salary-structure-calculator">
                    <div class="row">
                        <div class="col-lg-12 col-md-12 col-sm-12">
                            <textarea rows="3" cols="10" style="width: 259px; height: 31px;" id="calc" readonly></textarea>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-lg-12 col-md-12 col-sm-12">
                            <select class="cal_sel_button form-control">
                                <option value="0">---Select---</option>
                                <option value="monthsal">Monthly Gross Salary</option>
                                <option value="rembalance">Remaining Balance</option>
                                <?php
                                foreach ($salaryHeadItems as $key => $value) {
                                    foreach ($value as $salkey => $salvalue) {
                                ?>
                                        <option value="<?php echo str_replace($arr, '_', $salvalue['item_pkey'] . "_" . trim($salvalue['item_name'])); ?>"><?php echo trim($salvalue['item_name']); ?></option>
                                <?php
                                    }
                                }
                                ?>
                            </select>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-lg-2 col-md-2 col-sm-2">
                            <input type="button" class="cal_button form-control" value="7" />
                        </div>
                        <div class="col-lg-2 col-md-2 col-sm-2">
                            <input type="button" class="cal_button form-control" value="8" />
                        </div>
                        <div class="col-lg-2 col-md-2 col-sm-2">
                            <input type="button" class="cal_button form-control" value="9" />
                        </div>
                        <div class="col-lg-3 col-md-3 col-sm-3">
                            <input type="button" class="cal_button form-control" value="+" />
                        </div>
                        <div class="col-lg-3 col-md-3 col-sm-3">
                            <input type="button" class="cal_button form-control" value="-" />
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-lg-2 col-md-2 col-sm-2">
                            <input type="button" class="cal_button form-control" value="4" />
                        </div>
                        <div class="col-lg-2 col-md-2 col-sm-2">
                            <input type="button" class="cal_button form-control" value="5" />
                        </div>
                        <div class="col-lg-2 col-md-2 col-sm-2">
                            <input type="button" class="cal_button form-control" value="6" />
                        </div>
                        <div class="col-lg-3 col-md-3 col-sm-3">
                            <input type="button" class="cal_button form-control" value="*" />
                        </div>
                        <div class="col-lg-3 col-md-3 col-sm-3">
                            <input type="button" class="cal_button form-control" value="/" />
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-lg-2 col-md-2 col-sm-2">
                            <input type="button" class="cal_button form-control" value="1" />
                        </div>
                        <div class="col-lg-2 col-md-2 col-sm-2">
                            <input type="button" class="cal_button form-control" value="2" />
                        </div>
                        <div class="col-lg-2 col-md-2 col-sm-2">
                            <input type="button" class="cal_button form-control" value="3" />
                        </div>
                        <div class="col-lg-3 col-md-3 col-sm-3">
                            <input type="button" class="cal_button form-control" value="(" />
                        </div>
                        <div class="col-lg-3 col-md-3 col-sm-3">
                            <input type="button" class="cal_button form-control" value=")" />
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-lg-4 col-md-4 col-sm-4">
                            <input type="button" class="cal_button form-control" value="0" />
                        </div>
                        <div class="col-lg-2 col-md-2 col-sm-2">
                            <input type="button" class="cal_button form-control" value="." />
                        </div>
                        <div class="col-lg-3 col-md-3 col-sm-3">
                            <input type="button" value="Clear All" onclick="clearAll();" class="form-control" />
                        </div>
                        <div class="col-lg-3 col-md-3 col-sm-3">
                            <input type="button" value="Ok" onclick="ok();" class="form-control" />
                        </div>
                    </div>
                    <input type="hidden" id="cal_eq" value="" />
                </fieldset>
            </div>
        </div>
    </div>
</div>
<script type="text/javascript">
    jQuery(document).ready(function() {
        // Edited by Akshay on 21-01-2026
        var companyCode = "<?= $company_code ?>";
        var fixedDays = "<?= $fixed_days ?>";
        if (fixedDays) {
            toggleFixedDays();
        }
        // End

        $('#eff_satrt_date').datepicker({
            format: 'yyyy-mm-dd',
        })
        $("#eff_satrt_date").inputmask("yyyy-mm-dd");

        $('#eff_end_date').datepicker({
            format: 'yyyy-mm-dd',
        })
        $("#eff_end_date").inputmask("yyyy-mm-dd")

        $('#spForm').parsley();
        var options = {
            clearForm: true, // clear all form fields after successful submit 
            resetForm: true,
            beforeSubmit: function() {
                if ($('#month_salary').html() != 0) {
                    //alert("Remaining amount for applied formula should be zero");
                    $.notify("Remaining amount for applied formula should be zero", {
                        type: 'warning',
                        allow_dismiss: false
                    });
                    return false;
                }
                checkIfStructureExists();
            },
            success: function(response) {
                var response = $.parseJSON(response);
                if (response.msg) {
                    $.notify(response.msg, {
                        type: 'success',
                        allow_dismiss: false
                    });
                    $('#month_salary').html('');
                }
                showSalaryStructureView();
                reloadTable('shiftpolicygrouptable');
                $(".cal_disp").html("");
            }
        };

        // bind to the form's submit event
        $('#spForm').submit(function() {
            // inside event callbacks 'this' is the DOM element so we first
            // wrap it in a jQuery object and then invoke ajaxSubmit
            $(this).ajaxSubmit(options);

            // !!! Important !!!
            // always return false to prevent standard browser submit and page navigation
            return false;
        });

        $.fn.showHideDropdownOptions = function(value, canShowOption) {

            $(this).find('option[value="' + value + '"]').map(function() {
                return $(this).parent('span').length === 0 ? this : null;
            }).wrap('<span>').hide();

            if (canShowOption)
                $(this).find('option[value="' + value + '"]').unwrap().show();
            else
                $(this).find('option[value="' + value + '"]').hide();

        }

    });

    function showSalaryStructureView() {
        var structure_id = $('#structure_id').val();
        $("#shiftContainer").load(livesite + 'SalaryStructure/view/' + structure_id);
    }

    // Edited by Akshay on 21-01-2026
    function toggleFixedDays() {
        var selectedVal = $('#procode').val();
        var desc = '';

        if (selectedVal == '1') {
            desc = 'Calendar days';
            $('#fixedDaysRow').hide();
            $('#fixed_days').prop('disabled', true);
            $('#fixed_days').val('');
        } else if (selectedVal == '2') {
            desc = 'Working days';
            $('#fixedDaysRow').hide();
            $('#fixed_days').prop('disabled', true);
            $('#fixed_days').val('');
        } else if (selectedVal == '3') {
            desc = 'Custom fixed days per month';
            $('#fixedDaysRow').show();
            $('#fixed_days').prop('disabled', false);
        }
    }


    function validateFixedDays(input) {
        let value = input.value.replace(/[^0-9]/g, '');

        if (value === '') {
            input.value = '';
            return;
        }

        value = parseInt(value, 10);

        if (value < 0) value = 0;
        if (value > 31) value = 31;

        input.value = value;
        updateFixedDaysDesc(input);
    }

    function updateFixedDaysDesc(input) {
        const days = input.value;
        $('#procode_desc').val(days ? days + ' days per month' : 'Fixed number of days');
    }
    // End		
</script>