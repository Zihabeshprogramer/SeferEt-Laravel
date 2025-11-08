@extends('layouts.b2b')

@section('title', 'Fleet Calendar')

@section('content_header')
    <div class="row">
        <div class="col-md-8">
            <h1 class="m-0">
                <i class="fas fa-calendar-alt text-info mr-2"></i>
                Fleet Availability Calendar
            </h1>
        </div>
        <div class="col-md-4 text-right">
            <div class="btn-group">
                <button type="button" class="btn btn-outline-primary active" data-view="vehicles">
                    <i class="fas fa-truck"></i> Vehicles
                </button>
                <button type="button" class="btn btn-outline-primary" data-view="drivers">
                    <i class="fas fa-user-tie"></i> Drivers
                </button>
            </div>
        </div>
    </div>
@stop

@section('content')
    <!-- Legend -->
    <div class="row mb-3">
        <div class="col-12">
            <div class="card">
                <div class="card-body py-2">
                    <strong>Legend:</strong>
                    <span class="badge badge-info ml-2">Scheduled Assignment</span>
                    <span class="badge badge-primary ml-2">In Progress</span>
                    <span class="badge badge-success ml-2">Completed</span>
                    <span class="badge badge-warning ml-2">Maintenance</span>
                    <span class="badge badge-secondary ml-2">Cancelled</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Calendar -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <div id="fleet-calendar"></div>
                </div>
            </div>
        </div>
    </div>
@stop

@section('css')
<link href='https://cdn.jsdelivr.net/npm/fullcalendar@6.1.10/index.global.min.css' rel='stylesheet' />
<style>
.fc-event {
    cursor: pointer;
}
.fc-event:hover {
    opacity: 0.8;
}
</style>
@stop

@section('js')
<script src='https://cdn.jsdelivr.net/npm/fullcalendar@6.1.10/index.global.min.js'></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    var calendarEl = document.getElementById('fleet-calendar');
    var currentView = 'vehicles';
    
    var calendar = new FullCalendar.Calendar(calendarEl, {
        initialView: 'dayGridMonth',
        headerToolbar: {
            left: 'prev,next today',
            center: 'title',
            right: 'dayGridMonth,timeGridWeek,timeGridDay'
        },
        events: function(info, successCallback, failureCallback) {
            $.ajax({
                url: '{{ route("b2b.transport-provider.fleet.calendar.data") }}',
                method: 'GET',
                data: {
                    start: info.startStr,
                    end: info.endStr,
                    type: currentView
                },
                success: function(events) {
                    successCallback(events);
                },
                error: function() {
                    failureCallback();
                    toastr.error('Failed to load calendar data');
                }
            });
        },
        eventClick: function(info) {
            showEventDetails(info.event);
        },
        eventDidMount: function(info) {
            // Add tooltip
            $(info.el).tooltip({
                title: info.event.title,
                placement: 'top',
                trigger: 'hover',
                container: 'body'
            });
        }
    });
    
    calendar.render();
    
    // View switcher
    $('[data-view]').on('click', function() {
        $('[data-view]').removeClass('active');
        $(this).addClass('active');
        currentView = $(this).data('view');
        calendar.refetchEvents();
    });
    
    function showEventDetails(event) {
        var props = event.extendedProps;
        var content = '<dl class="row">';
        
        if (props.type === 'assignment') {
            content += '<dt class="col-sm-4">Vehicle:</dt><dd class="col-sm-8">' + props.vehicle + '</dd>';
            content += '<dt class="col-sm-4">Driver:</dt><dd class="col-sm-8">' + props.driver + '</dd>';
            content += '<dt class="col-sm-4">Status:</dt><dd class="col-sm-8"><span class="badge badge-' + getStatusColor(props.status) + '">' + props.status + '</span></dd>';
        } else if (props.type === 'maintenance') {
            content += '<dt class="col-sm-4">Vehicle:</dt><dd class="col-sm-8">' + props.vehicle + '</dd>';
            content += '<dt class="col-sm-4">Type:</dt><dd class="col-sm-8">' + props.maintenance_type + '</dd>';
            content += '<dt class="col-sm-4">Description:</dt><dd class="col-sm-8">' + props.description + '</dd>';
        }
        
        content += '</dl>';
        
        Swal.fire({
            title: event.title,
            html: content,
            icon: 'info',
            confirmButtonText: 'Close'
        });
    }
    
    function getStatusColor(status) {
        const colors = {
            'scheduled': 'info',
            'in_progress': 'primary',
            'completed': 'success',
            'cancelled': 'secondary'
        };
        return colors[status] || 'secondary';
    }
});
</script>
@stop
