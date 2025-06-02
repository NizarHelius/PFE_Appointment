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
                </tr>
            </thead>
            <tbody>
                @forelse($appointments as $appointment)
                <tr>
                    <td>{{ $appointment->user->name ?? '-' }}</td>
                    <td>{{ $appointment->service->title ?? '-' }}</td>
                    <td>{{ $appointment->booking_date }}</td>
                    <td>{{ $appointment->booking_time }}</td>
                    <td>{{ $appointment->status }}</td>
                </tr>
                @empty
                <tr>
                    <td colspan="5">No appointments found.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@stop