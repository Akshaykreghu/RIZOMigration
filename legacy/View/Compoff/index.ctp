<div class="modal-body myDivToPrint" style="overflow-y: auto;">
    <legend>Compensatory Off Report</legend>
    <div class="row" id="toPrint">
        <div class="col-md-12">
            <div class="box ">
                   <?php $i=0; foreach ($arr_leavepolicydetails_for_template as $value) {
                       if(!empty($value['summary']))
                       {
                   $i += 1; 
                  ?>
                <div class="box-body">
                    
                    <br>
                    <fieldset>
			

			    
                                <?php $arr_data  = $value['summary']; ?>
                                <?php if(count($arr_data)>0){ ?>
                        <div class="row">
                            
                            <div class="col-md-12">
                                <legend>Compensatory Off Report</legend>
                                <h4>Employee Details</h4>
                                    <table class="table table-bordered">
                                        <thead>
                                            <tr>
                                                <th style="border:1px solid black;">Employee Name</th>
                                                <th style="border:1px solid black;">Employee ID</th>
                                                <th style="border:1px solid black;">Designation</th>
                                                <th style="border:1px solid black;">Date Of Join</th>
                                                <th style="border:1px solid black;">Departments</th>
                                                <th style="border:1px solid black;">Branch</th>
                                                  
                                                  
                                                  
                                                  
                                            </tr>
                                        </thead>
                                        <tbody>
                                        <tr> 
                                            <td style="border:1px solid black;"><?php echo $arr_data['0']['employee_info']['EmpName'] ; ?></td>
                                            <td style="border:1px solid black;"><?php echo $arr_data['0']['employee_info']['employee_id'] ; ?></td>
                                            <td style="border:1px solid black;"><?php echo $arr_data['0']['employee_info']['designation'] ; ?></td>
                                            <td style="border:1px solid black;"><?php echo $arr_data['0']['employee_info']['joining_date'] ; ?></td>
                                            <td style="border:1px solid black;"><?php echo $arr_data['0']['employee_info']['department'] ; ?></td>
                                            <td style="border:1px solid black;"><?php echo $arr_data['0']['employee_info']['branch'] ; ?></td>
                                        </tr>
                                        </tbody>
                                    </table>
                            </div>
                            
                            <h4 style="margin-left:14px;">Compensatory Off Details</h4>
                            <div class="col-md-6">
                        <table class="table table-bordered">
                                        <thead>
                                            <tr>
                                                <th colspan="2" style="border:1px solid black;">Used</th>
                                                                
                                                                
                                            </tr>
                                            <tr><th style="border:1px solid black;">Year Month</th><th style="border:1px solid black;">Compensatory Off</th></tr>
                                        </thead>
                                        <tbody>
                                            
                                            <?php
                                            $used = 0;
                                            foreach ($arr_data as $val) {
                                                $used += $val['0']['compoff'];
                                                ?>
                                                <tr>
                                                    <td style="border:1px solid black;"><?php echo $val['attendance_register']['month_year']; ?></td><td style="border:1px solid black;"><?php echo $val['0']['compoff']; ?></td>
                                                </tr>
                                                <?php
                                            }
                                            ?>
                                                <tr>
                                                    <td style="border:1px solid black;">Total</td>
                                                    <td style="border:1px solid black;"><?php echo $used; ?></td>
                                                </tr>
                                                 </tbody>
                                    </table>
                            </div>
                            <div class="col-md-6">
                                 <table class="table table-bordered">
                                        <thead>
                                            <tr>
                                                <th colspan="2" style="border:1px solid black;">Earned</th>
                                            </tr>
                                            <tr><th style="border:1px solid black;">Year Month</th><th style="border:1px solid black;">Compensatory Off</th></tr>
                                            </thead>
                                        <tbody>
                                            <?php
                                            $earn = 0;
                                            $arr_datas = $value['eligibility']; 
                                            foreach ($arr_datas as $val) {
                                                $earn += $val['0']['eligibility'];
                                                ?>
                                                <tr>
                                                    <td style="border:1px solid black;"><?php echo $val['0']['yearmonth']; ?></td><td style="border:1px solid black;"><?php echo $val['0']['eligibility']; ?></td>
                                                </tr>
                                                <?php
                                            }
                                            ?>
                                                <tr>
                                                    <td style="border:1px solid black;">Total</td>
                                                    <td style="border:1px solid black;"><?php echo $earn; ?></td>
                                                </tr>
                                        </tbody>
                                    </table>
                        </div>
                            <div class="col-md-12">
                                <table class="table table-bordered">
                                    <tbody>
                                        <tr>
                                            <td style="border:1px solid black;">Total</td>
                                            <td style="border:1px solid black;"><?php echo $earn - $used; ?></td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                                <?php } ?>
                            
                        </div>
                    </fieldset>
                    <br>
                   
                </div>
                   <?php  } } ?> <!-- /.box-body -->
            </div>
        </div>
        <section class="content">
            <div class="row">

                <!-- /.col -->
                <div class="col-md-6">
                    <div class="box box-primary">
                        <div class="box-body no-padding">
                             THE CALENDAR 
                            <div id="calendar"></div>
                        </div>
                         /.box-body 
                    </div>
                     /. box 
                </div>
                <!-- /.col -->
            </div>
            <!-- /.row -->
        </section>
    </div>    
    <div class="row">
        <div class="form-group">
            <div class="col-md-12" align="right">
                <!--<a href="#" class="btn btn-default" onclick="printelement();" ><i class="icon-file"></i>Download As PDF</a>-->
                <!--a href="#" class="btn btn-default" onclick="downloadReport('LeaveBalance','excel');"><i class="icon-file"></i>Download As Excel</a-->
            </div>
        </div>
    </div>
</div>
<script>
function printelement()
{
    var mywindow = window.open('', 'PRINT', 'height=400,width=1200');


        mywindow.document.write('<html><head><title>' + document.title  + '</title>');

        mywindow.document.write('</head><body >');
      mywindow.document.write('<h1>' + document.title  + '</h1>');
        mywindow.document.write(document.getElementById("toPrint").innerHTML);
        mywindow.document.write('</body></html>');

        mywindow.document.close(); // necessary for IE >= 10
        mywindow.focus(); // necessary for IE >= 10*/

        mywindow.print();
        mywindow.close();

        return true;
}
</script>
<script>
function events()
    {
        $.ajax({
                    url:'Grade/eventsCalenderdate',
                    type: 'post',
                    success: function(resp){
//                        $.notify($.parseJSON(resp).msg,{
//                            type: 'success',
//                            allow_dismiss: false
//                        });
                    }
                });
    }
  $(function () {

    /* initialize the external events
     -----------------------------------------------------------------*/
    function ini_events(ele) {
      ele.each(function () {

        // create an Event Object (http://arshaw.com/fullcalendar/docs/event_data/Event_Object/)
        // it doesn't need to have a start or end
        var eventObject = {
          title: $.trim($(this).text()) // use the element's text as the event title
        };

        // store the Event Object in the DOM element so we can get to it later
        $(this).data('eventObject', eventObject);

        // make the event draggable using jQuery UI
        $(this).draggable({
          zIndex: 1070,
          revert: true, // will cause the event to go back to its
          revertDuration: 0  //  original position after the drag
        });

      });
    }

    ini_events($('#external-events div.external-event'));

    /* initialize the calendar
     -----------------------------------------------------------------*/
    //Date for the calendar events (dummy data)
    var date = new Date();
    var d = date.getDate(),
        m = date.getMonth(),
        y = date.getFullYear();
   
    $('#calendar').fullCalendar({
      header: {
        left: 'prev,next today',
        center: 'title',
        right: 'month,agendaWeek,agendaDay'
      },
      buttonText: {
        today: 'today',
        month: 'month',
        week: 'week',
        day: 'day'
      },
      //Random default events
      events: livesite+ 'grade/Getholidayss',
      editable: true,
      droppable: true, // this allows things to be dropped onto the calendar !!!
      drop: function (date, allDay) { // this function is called when something is dropped

        // retrieve the dropped element's stored Event Object
        var originalEventObject = $(this).data('eventObject');

        // we need to copy it, so that multiple events don't have a reference to the same object
        var copiedEventObject = $.extend({}, originalEventObject);

        // assign it the date that was reported
        copiedEventObject.start = date;
        copiedEventObject.allDay = allDay;
        copiedEventObject.backgroundColor = $(this).css("background-color");
        copiedEventObject.borderColor = $(this).css("border-color");

        // render the event on the calendar
        // the last `true` argument determines if the event "sticks" (http://arshaw.com/fullcalendar/docs/event_rendering/renderEvent/)
        $('#calendar').fullCalendar('renderEvent', copiedEventObject, true);
        //$('#calendar').fullCalendar('removeEvents', 2); //Remove events with the id: 2
        // is the "remove after drop" checkbox checked?
        if ($('#drop-remove').is(':checked')) {
          // if so, remove the element from the "Draggable Events" list
          $(this).remove();
        }
        var d = new Date(date);
        var day =  d.getFullYear() + '-' + (d.getMonth() + 1) + '-' + d.getDate() ;
        $.ajax({
                    url:'Grade/insert',
                    type: 'post',
                    data: { 
                        date:day,
                        Text:$(this).text(),
                        Background:copiedEventObject.backgroundColor,
                        Border:copiedEventObject.borderColor
                    },
                    success: function(resp){
//                        $.notify($.parseJSON(resp).msg,{
//                            type: 'success',
//                            allow_dismiss: false
//                        });
                    }
                });
      }
    });
   
    /* ADDING EVENTS */
    var currColor = "#3c8dbc"; //Red by default
    //Color chooser button
    var colorChooser = $("#color-chooser-btn");
    $("#color-chooser > li > a").click(function (e) {
      e.preventDefault();
      //Save color
      currColor = $(this).css("color");
      //Add color effect to button
      $('#add-new-event').css({"background-color": currColor, "border-color": currColor});
    });
    $("#add-new-event").click(function (e) {
      e.preventDefault();
      //Get value and make sure it is not null
      var val = $("#new-event").val();
      if (val.length == 0) {
        return;
      }

      //Create events
      var event = $("<div />");
      event.css({"background-color": currColor, "border-color": currColor, "color": "#fff"}).addClass("external-event");
      event.html(val);
      $('#external-events').prepend(event);

      //Add draggable funtionality
      ini_events(event);

      //Remove event from text input
      $("#new-event").val("");
    });
  });
</script>