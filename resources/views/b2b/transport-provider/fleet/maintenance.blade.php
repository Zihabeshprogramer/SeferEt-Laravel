@extends('layouts.b2b')

@section('title', 'Maintenance Management')

@section('content_header')
    <div class="row">
        <div class="col-md-8">
            <h1 class="m-0">
                <i class="fas fa-tools text-info mr-2"></i>
                Maintenance Management
            </h1>
        </div>
        <div class="col-md-4 text-right">
            <button class="btn btn-warning" data-toggle="modal" data-target="#addMaintenanceModal">
                <i class="fas fa-wrench mr-1"></i>
                Schedule Maintenance
            </button>
        </div>
    </div>
@stop

@section('content')
    <!-- Stats -->
    <div class="row">
        <div class="col-lg-3 col-6">
            <div class="small-box bg-info"><div class="inner"><h3>{{ $stats['total_records'] }}</h3><p>Total Records</p></div><div class="icon"><i class="fas fa-clipboard-list"></i></div></div>
        </div>
        <div class="col-lg-3 col-6">
            <div class="small-box bg-primary"><div class="inner"><h3>{{ $stats['scheduled'] }}</h3><p>Scheduled</p></div><div class="icon"><i class="fas fa-calendar"></i></div></div>
        </div>
        <div class="col-lg-3 col-6">
            <div class="small-box bg-warning"><div class="inner"><h3>{{ $stats['in_progress'] }}</h3><p>In Progress</p></div><div class="icon"><i class="fas fa-cog fa-spin"></i></div></div>
        </div>
        <div class="col-lg-3 col-6">
            <div class="small-box bg-danger"><div class="inner"><h3>{{ $stats['overdue'] }}</h3><p>Overdue</p></div><div class="icon"><i class="fas fa-exclamation-triangle"></i></div></div>
        </div>
    </div>

    <!-- Maintenance Table -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header"><h3 class="card-title"><i class="fas fa-tools mr-2"></i>Maintenance Records</h3></div>
                <div class="card-body table-responsive p-0">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Vehicle</th>
                                <th>Type</th>
                                <th>Date</th>
                                <th>Cost</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($maintenanceRecords as $record)
                                <tr>
                                    <td><strong>{{ $record->vehicle->vehicle_name }}</strong></td>
                                    <td><span class="badge {{ $record->type_badge_class }}">{{ $record->type_label }}</span></td>
                                    <td>{{ $record->maintenance_date->format('M d, Y') }}</td>
                                    <td>@if($record->cost) ${{ number_format($record->cost, 2) }} @else N/A @endif</td>
                                    <td><span class="badge {{ $record->status_badge_class }}">{{ $record->status_label }}</span></td>
                                    <td>
                                        <button class="btn btn-sm btn-danger" onclick="deleteMaintenance({{ $record->id }})"><i class="fas fa-trash"></i></button>
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="6" class="text-center py-4">No maintenance records.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                @if($maintenanceRecords->hasPages())
                    <div class="card-footer">{{ $maintenanceRecords->links() }}</div>
                @endif
            </div>
        </div>
    </div>
@stop

<!-- Add Maintenance Modal -->
<div class="modal fade" id="addMaintenanceModal">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-warning"><h5 class="modal-title">Schedule Maintenance</h5><button type="button" class="close" data-dismiss="modal">&times;</button></div>
            <form id="addMaintenanceForm">
                @csrf
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6"><div class="form-group"><label>Vehicle *</label><select name="vehicle_id" class="form-control" required>@foreach($vehicles as $v)<option value="{{ $v->id }}">{{ $v->vehicle_name }}</option>@endforeach</select></div></div>
                        <div class="col-md-6"><div class="form-group"><label>Type *</label><select name="maintenance_type" class="form-control" required><option value="routine">Routine</option><option value="repair">Repair</option><option value="inspection">Inspection</option><option value="emergency">Emergency</option></select></div></div>
                    </div>
                    <div class="row">
                        <div class="col-md-6"><div class="form-group"><label>Date *</label><input type="date" name="maintenance_date" class="form-control" required></div></div>
                        <div class="col-md-6"><div class="form-group"><label>Cost</label><input type="number" name="cost" class="form-control" step="0.01"></div></div>
                    </div>
                    <div class="form-group"><label>Description *</label><textarea name="description" class="form-control" rows="3" required></textarea></div>
                </div>
                <div class="modal-footer"><button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button><button type="submit" class="btn btn-warning">Save</button></div>
            </form>
        </div>
    </div>
</div>

@section('js')
<script>
$('#addMaintenanceForm').on('submit', function(e) {
    e.preventDefault();
    $.ajax({
        url: '{{ route("b2b.transport-provider.fleet.maintenance.store") }}',
        method: 'POST',
        data: $(this).serialize(),
        success: function(response) {
            toastr.success(response.message);
            location.reload();
        },
        error: function() {
            toastr.error('Failed to schedule maintenance');
        }
    });
});

function deleteMaintenance(id) {
    if(confirm('Delete this maintenance record?')) {
        $.ajax({
            url: `/b2b/transport-provider/fleet/maintenance/${id}`,
            method: 'DELETE',
            data: { _token: '{{ csrf_token() }}' },
            success: function(response) {
                toastr.success(response.message);
                location.reload();
            }
        });
    }
}
</script>
@stop
