<style>
  /* <!-- edited by bindhu 19-02-2026 --> */
  .heading {
    display: flex;
    flex-direction: row;
    align-items: end;
    justify-content: space-between;
    margin-left: 20px;
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
<section class="content-header heading">
  <section>
    <h1 class="text-primary-18"> Holiday Calender </h1>
    <p>See your holidays provided by your company for you are listed here.</p>
  </section>
  <div class="text-primary-16 home"
    style="display:flex; align-items:center; gap:10px; cursor:pointer;">
    <i class="fa" style="font-size:16px;">&#xf104;</i>
    Back
  </div>
</section>
<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */
?>

    

    <!-- Main content -->
    <section class="content">
      <div class="row">
        
        <!-- /.col -->
        <div class="col-md-12">
          <div class="box box-primary">
            <div class="box-body no-padding">
              <!-- THE CALENDAR -->
              <div id="calendar"></div>
            </div>
            <!-- /.box-body -->
          </div>
          <!-- /. box -->
        </div>
        <!-- /.col -->
      </div>
      <!-- /.row -->
    </section>
    <!-- /.content -->
 
<script>
function events()
    {
        $.ajax({
                    url:'Grade/eventsCalender',
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
      events: livesite+ 'grade/Getholidays',
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
   // edited by bindhu 19-02-2026
  $(".home").on("click", function() {
    $("#container").isLoading({
      text: "Loading",
      position: "overlay",
    });

    $("#container").load(livesite + "EmployeeMenu/index", function() {
      isDashboardShown = false;
    });


  });
  //  edited by bindhu 19-02-2026 end
</script>