@extends('adminlte::page')

@section('title', 'My Appointments')

@section('content_header')
<h1>All My Appointments</h1>
@stop

@section('content')
<div class="card">
    <div class="card-body">
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>Client</th>
                    <th>Service</th>
                    <th>Date</th>
                    <th>Time</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($appointments as $appointment)
                <tr>
                    <td>{{ $appointment->user->name ?? '-' }}</td>
                    <td>{{ $appointment->service->title ?? '-' }}</td>
                    <td>{{ $appointment->booking_date }}</td>
                    <td>{{ $appointment->booking_time }}</td>
                    <td>
                        @php
                        $statusColors = [
                        'Pending payment' => '#f39c12', // Orange
                        'Processing' => '#3498db', // Blue
                        'Confirmed' => '#2ecc71', // Green
                        'Cancelled' => '#e74c3c', // Red
                        'Completed' => '#27ae60', // Dark Green
                        'On Hold' => '#95a5a6', // Gray
                        'Rescheduled' => '#f1c40f', // Yellow
                        'No Show' => '#e67e22' // Dark Orange
                        ];
                        $statusColor = $statusColors[$appointment->status] ?? '#6c757d';
                        @endphp
                        <span class="badge" style="background-color: {{ $statusColor }}; color: white;">
                            {{ $appointment->status }}
                        </span>
                    </td>
                    <td>
                        <button type="button" class="btn btn-primary btn-sm" data-toggle="modal" data-target="#editModal{{ $appointment->id }}">
                            <i class="fas fa-edit"></i> Edit
                        </button>
                    </td>
                </tr>

                <!-- Edit Modal -->
                <div class="modal fade" id="editModal{{ $appointment->id }}" tabindex="-1" role="dialog" aria-labelledby="editModalLabel{{ $appointment->id }}" aria-hidden="true">
                    <div class="modal-dialog" role="document">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title" id="editModalLabel{{ $appointment->id }}">Edit Appointment</h5>
                                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                    <span aria-hidden="true">&times;</span>
                                </button>
                            </div>
                            <form action="{{ route('appointments.update.status') }}" method="POST">
                                @csrf
                                <input type="hidden" name="appointment_id" value="{{ $appointment->id }}">
                                <div class="modal-body">
                                    <div class="form-group">
                                        <label for="status{{ $appointment->id }}">Status</label>
                                        <select class="form-control" id="status{{ $appointment->id }}" name="status" required>
                                            <option value="Pending payment" {{ $appointment->status == 'Pending payment' ? 'selected' : '' }}>Pending Payment</option>
                                            <option value="Processing" {{ $appointment->status == 'Processing' ? 'selected' : '' }}>Processing</option>
                                            <option value="Confirmed" {{ $appointment->status == 'Confirmed' ? 'selected' : '' }}>Confirmed</option>
                                            <option value="Cancelled" {{ $appointment->status == 'Cancelled' ? 'selected' : '' }}>Cancelled</option>
                                            <option value="Completed" {{ $appointment->status == 'Completed' ? 'selected' : '' }}>Completed</option>
                                            <option value="On Hold" {{ $appointment->status == 'On Hold' ? 'selected' : '' }}>On Hold</option>
                                            <option value="No Show" {{ $appointment->status == 'No Show' ? 'selected' : '' }}>No Show</option>
                                        </select>
                                    </div>
                                    <div class="form-group">
                                        <label for="notes{{ $appointment->id }}">Notes</label>
                                        <textarea class="form-control" id="notes{{ $appointment->id }}" name="notes" rows="3">{{ $appointment->notes }}</textarea>
                                    </div>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                                    <button type="submit" class="btn btn-primary">Save changes</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
                @empty
                <tr>
                    <td colspan="6">No appointments found.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@stop

@section('css')
<style>
    .badge {
        padding: 8px 12px;
        font-size: 12px;
        font-weight: 500;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }
</style>
@stop