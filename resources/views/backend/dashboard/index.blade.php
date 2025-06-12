@extends('adminlte::page')

@section('title', 'Dashboard')

@section('content_header')
<h1>Dashboard</h1>
@if (session('success'))
<div class="alert alert-success alert-dismissable mt-2">
    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
        <span aria-hidden="true">&times;</span>
    </button>
    <strong>{{ session('success') }}</strong>
</div>
@endif
@if (session('error'))
<div class="alert alert-danger alert-dismissible fade show" role="alert">
    {{ session('error') }}
    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
        <span aria-hidden="true">&times;</span>
    </button>
</div>
@endif
@stop

@section('content')
<div class="container-fluid px-0">
    <!-- User Statistics Cards - Only visible to admins -->
    @if(auth()->user()->hasRole('admin'))
    <div class="row mb-4">
        <div class="col-lg-4 col-md-6">
            <div class="small-box bg-info">
                <div class="inner">
                    <h3>{{ $adminCount }}</h3>
                    <p>Administrators</p>
                </div>
                <div class="icon">
                    <i class="fas fa-user-shield"></i>
                </div>
            </div>
        </div>
        <div class="col-lg-4 col-md-6">
            <div class="small-box bg-success">
                <div class="inner">
                    <h3>{{ $employeeCount }}</h3>
                    <p>Employees</p>
                </div>
                <div class="icon">
                    <i class="fas fa-user-tie"></i>
                </div>
            </div>
        </div>
        <div class="col-lg-4 col-md-6">
            <div class="small-box bg-warning">
                <div class="inner">
                    <h3>{{ $clientCount }}</h3>
                    <p>Clients</p>
                </div>
                <div class="icon">
                    <i class="fas fa-users"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Appointment Trends Chart -->
    <div class="row mb-4">
        <div class="col-sm-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Appointment Trends (Last 7 Days)</h5>
                </div>
                <div class="card-body">
                    <canvas id="appointmentTrendsChart" height="100"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Service Popularity and Recent Activity -->
    <div class="row">
        <!-- Service Popularity -->
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Most Popular Services</h5>
                </div>
                <div class="card-body">
                    <canvas id="servicePopularityChart" height="200"></canvas>
                </div>
            </div>
        </div>

        <!-- Recent Activity -->
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Recent Activity</h5>
                </div>
                <div class="card-body">
                    <div class="timeline">
                        @foreach($recentActivity['appointments'] as $appointment)
                        <div class="time-label">
                            <span class="bg-info">{{ $appointment->created_at->format('M d, Y') }}</span>
                        </div>
                        <div>
                            <i class="fas fa-calendar-check bg-success"></i>
                            <div class="timeline-item">
                                <span class="time"><i class="fas fa-clock"></i> {{ $appointment->created_at->format('H:i') }}</span>
                                <h3 class="timeline-header">New Appointment</h3>
                                <div class="timeline-body">
                                    <strong>{{ $appointment->name }}</strong> booked <strong>{{ $appointment->service->title }}</strong>
                                    <br>
                                    Status: <span class="badge" style="background-color: {{ $appointment->color }}">{{ $appointment->status }}</span>
                                </div>
                            </div>
                        </div>
                        @endforeach

                        @foreach($recentActivity['newUsers'] as $user)
                        <div class="time-label">
                            <span class="bg-warning">{{ $user->created_at->format('M d, Y') }}</span>
                        </div>
                        <div>
                            <i class="fas fa-user bg-primary"></i>
                            <div class="timeline-item">
                                <span class="time"><i class="fas fa-clock"></i> {{ $user->created_at->format('H:i') }}</span>
                                <h3 class="timeline-header">New User Registration</h3>
                                <div class="timeline-body">
                                    <strong>{{ $user->name }}</strong> joined as <strong>{{ $user->getRoleNames()->first() }}</strong>
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>
    @else
    <!-- Calendar - Only visible to employees and subscribers -->
    <div class="row">
        <div class="col-sm-12">
            <div id="calendar"></div>
        </div>
    </div>
    @endif
</div>

<!-- Appointment Modal -->
<form id="appointmentStatusForm" method="POST" action="{{ route('dashboard.update.status') }}"
    onsubmit="return confirm('Are you sure you want to update the booking status?')">

    @csrf
    <input type="hidden" name="appointment_id" id="modalAppointmentId">

    <div class="modal fade" id="appointmentModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Appointment Details</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>

                <div class="modal-body">
                    <p><strong>Client:</strong> <span id="modalAppointmentName">N/A</span></p>
                    <p><strong>Service:</strong> <span id="modalService">N/A</span></p>
                    <p><strong>Email:</strong> <span id="modalEmail">N/A</span></p>
                    <p><strong>Phone:</strong> <span id="modalPhone">N/A</span></p>
                    <p><strong>Staff:</strong> <span id="modalStaff">N/A</span></p>
                    <p><strong>Date & Time:</strong> <span id="modalStartTime">N/A</span></p>
                    {{-- <p><strong>End:</strong> <span id="modalEndTime">N/A</span></p> --}}
                    <p><strong>Amount:</strong> <span id="modalAmount">N/A</span></p>
                    <p><strong>Notes:</strong> <span id="modalNotes">N/A</span></p>
                    <p><strong>Current Status:</strong> <span id="modalStatusBadge">N/A</span></p>

                    <div class="form-group">
                        <label><strong>Change Status:</strong></label>
                        <select name="status" class="form-control" id="modalStatusSelect">
                            <option value="Pending payment">Pending payment</option>
                            <option value="Processing">Processing</option>
                            <option value="Confirmed">Confirmed</option>
                            <option value="Cancelled">Cancelled</option>
                            <option value="Completed">Completed</option>
                            <option value="On Hold">On Hold</option>
                            <option value="No Show">No Show</option>
                        </select>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="submit" class="btn btn-danger">Update Status</button>
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>
</form>

@stop

@section('css')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/fullcalendar@3.10.2/dist/fullcalendar.min.css" />
<style>
    #calendar {
        background-color: white;
        padding: 20px;
        border-radius: 5px;
        box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
    }

    .fc-toolbar h2 {
        font-size: 1.2em;
    }

    /* DAILY VIEW OPTIMIZATIONS */
    .fc-agendaDay-view .fc-time-grid-container {
        height: auto !important;
    }

    .fc-agendaDay-view .fc-event {
        margin: 1px 2px;
        border-radius: 3px;
    }

    .fc-agendaDay-view .fc-event.short-event {
        height: 30px;
        font-size: 0.85em;
        padding: 2px;
    }

    .fc-agendaDay-view .fc-event .fc-content {
        white-space: normal;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .fc-agendaDay-view .fc-time {
        width: 50px !important;
    }

    .fc-agendaDay-view .fc-time-grid {
        min-height: 600px !important;
    }

    .fc-agendaDay-view .fc-event.fc-short-event {
        height: 35px;
        font-size: 0.85em;
    }

    .fc-agendaDay-view .fc-time {
        width: 70px !important;
        padding: 0 10px;
    }

    .fc-agendaDay-view .fc-axis {
        width: 70px !important;
    }

    .fc-agendaDay-view .fc-content-skeleton {
        padding-bottom: 5px;
    }

    .fc-agendaDay-view .fc-slats tr {
        height: 40px;
    }

    .fc-event {
        opacity: 0.9;
        transition: opacity 0.2s;
    }

    .fc-event:hover {
        opacity: 1;
        z-index: 1000 !important;
    }

    /* Calm, less gradient FullCalendar buttons */
    .fc button,
    .fc .fc-button {
        background: #f4f6fa;
        color: #3a3a3a !important;
        border: 1px solid #d1d5db;
        border-radius: 0.3rem;
        box-shadow: none;
        padding: 0.5rem 1.2rem;
        font-weight: 500;
        margin: 0 2px;
        transition: background 0.2s, color 0.2s, border 0.2s;
    }

    .fc button:hover,
    .fc .fc-button:hover {
        background: #e2e6ea;
        color: #222 !important;
        border-color: #bfc5cc;
    }

    .fc .fc-state-active,
    .fc .fc-button-active {
        background: #e7f1fa !important;
        color: #1976d2 !important;
        border-color: #90caf9;
        box-shadow: none;
    }

    .fc .fc-today-button {
        background: #e0e0e0 !important;
        color: #1976d2 !important;
        font-weight: bold;
        border-color: #bdbdbd;
    }

    .fc .fc-button-disabled {
        background: #f8f9fa !important;
        color: #adb5bd !important;
        cursor: not-allowed;
        opacity: 0.7;
        border-color: #e9ecef;
    }
</style>
@stop

@section('js')
@parent
<script src="https://cdn.jsdelivr.net/npm/moment@2.29.1/moment.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/fullcalendar@3.10.2/dist/fullcalendar.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
    $(document).ready(function() {
        // Initialize toasts first
        // $('.toast').toast({
        //     delay: 5000
        // });

        // Initialize calendar
        $('#calendar').fullCalendar({
            header: {
                left: 'prev,next today',
                center: 'title',
                right: 'month,agendaDay'
            },
            defaultView: 'month',
            editable: false,
            slotDuration: '00:30:00',
            minTime: '06:00:00',
            maxTime: '22:00:00',
            events: @json($appointments ?? []),
            eventRender: function(event, element) {
                element.tooltip({
                    title: event.description || 'No description',
                    placement: 'top',
                    trigger: 'hover',
                    container: 'body'
                });
            },
            eventClick: function(calEvent, jsEvent, view) {
                // Populate modal with event data
                $('#modalAppointmentId').val(calEvent.id);
                $('#modalAppointmentName').text(calEvent.name || calEvent.title.split(' - ')[0] || 'N/A');
                $('#modalService').text(calEvent.service_title || calEvent.title.split(' - ')[1] || 'N/A');
                $('#modalEmail').text(calEvent.email || 'N/A');
                $('#modalPhone').text(calEvent.phone || 'N/A');
                $('#modalStaff').text(calEvent.staff || 'N/A');
                $('#modalAmount').text(calEvent.amount || 'N/A');
                $('#modalNotes').text(calEvent.description || calEvent.notes || 'N/A');
                $('#modalStartTime').text(moment(calEvent.start).format('MMMM D, YYYY h:mm A'));
                $('#modalEndTime').text(calEvent.end ? moment(calEvent.end).format('MMMM D, YYYY h:mm A') : 'N/A');

                // Get the status from the calendar event
                var status = calEvent.status || 'Pending payment';
                $('#modalStatusSelect').val(status);

                // Set status badge
                var statusColors = {
                    'Pending payment': '#f39c12',
                    'Processing': '#3498db',
                    'Confirmed': '#2ecc71',
                    'Cancelled': '#ff0000',
                    'Completed': '#008000',
                    'On Hold': '#95a5a6',
                    'No Show': '#e67e22',
                };

                var badgeColor = statusColors[status] || '#7f8c8d';
                $('#modalStatusBadge').html(
                    `<span class="badge px-2 py-1" style="background-color: ${badgeColor}; color: white;">${status}</span>`
                );

                $('#appointmentModal').modal('show');
            }
        });

        // Single form submission handler



    });
</script>

<script>
    $(document).ready(function() {
        $(".alert").delay(2000).slideUp(300);
    });
</script>

@if(auth()->user()->hasRole('admin'))
<script>
    document.addEventListener('DOMContentLoaded', function() {
        var ctx = document.getElementById('appointmentTrendsChart').getContext('2d');
        new Chart(ctx, {
            type: 'line',
            data: @json($chartData),
            options: {
                responsive: true,
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            stepSize: 1
                        }
                    }
                }
            }
        });

        var serviceCtx = document.getElementById('servicePopularityChart').getContext('2d');
        new Chart(serviceCtx, {
            type: 'doughnut',
            data: {
                labels: @json($serviceStats -> pluck('title')),
                datasets: [{
                    data: @json($serviceStats -> pluck('appointments_count')),
                    backgroundColor: [
                        'rgba(54, 162, 235, 0.8)',
                        'rgba(75, 192, 192, 0.8)',
                        'rgba(255, 206, 86, 0.8)',
                        'rgba(153, 102, 255, 0.8)',
                        'rgba(255, 159, 64, 0.8)'
                    ]
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        position: 'right'
                    }
                }
            }
        });
    });
</script>
@endif
@stop