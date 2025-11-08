@extends('layouts.b2b')

@section('title', 'Driver Management')

@section('content_header')
    <div class="row">
        <div class="col-md-8">
            <h1 class="m-0">
                <i class="fas fa-user-tie text-info mr-2"></i>
                Driver Management
            </h1>
        </div>
        <div class="col-md-4 text-right">
            <button class="btn btn-primary" data-toggle="modal" data-target="#addDriverModal">
                <i class="fas fa-user-plus mr-1"></i>
                Add Driver
            </button>
        </div>
    </div>
@stop

@section('content')
    <!-- Stats Row -->
    <div class="row">
        <div class="col-lg-3 col-6">
            <div class="small-box bg-info">
                <div class="inner">
                    <h3>{{ $stats['total_drivers'] }}</h3>
                    <p>Total Drivers</p>
                </div>
                <div class="icon"><i class="fas fa-user-tie"></i></div>
            </div>
        </div>
        <div class="col-lg-3 col-6">
            <div class="small-box bg-success">
                <div class="inner">
                    <h3>{{ $stats['available'] }}</h3>
                    <p>Available</p>
                </div>
                <div class="icon"><i class="fas fa-check-circle"></i></div>
            </div>
        </div>
        <div class="col-lg-3 col-6">
            <div class="small-box bg-primary">
                <div class="inner">
                    <h3>{{ $stats['on_trip'] }}</h3>
                    <p>On Trip</p>
                </div>
                <div class="icon"><i class="fas fa-route"></i></div>
            </div>
        </div>
        <div class="col-lg-3 col-6">
            <div class="small-box bg-warning">
                <div class="inner">
                    <h3>{{ $stats['license_expiring_soon'] }}</h3>
                    <p>License Expiring Soon</p>
                </div>
                <div class="icon"><i class="fas fa-exclamation-triangle"></i></div>
            </div>
        </div>
    </div>

    <!-- Drivers Table -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title"><i class="fas fa-users mr-2"></i>Driver List</h3>
                    <div class="card-tools">
                        <input type="text" id="searchDrivers" class="form-control form-control-sm" placeholder="Search..." style="width: 200px;">
                    </div>
                </div>
                <div class="card-body table-responsive p-0">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Phone</th>
                                <th>License</th>
                                <th>Expiry</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody id="driversTableBody">
                            @forelse($drivers as $driver)
                                <tr>
                                    <td><strong>{{ $driver->name }}</strong></td>
                                    <td>{{ $driver->phone }}</td>
                                    <td><code>{{ $driver->license_number }}</code></td>
                                    <td>
                                        {{ $driver->license_expiry->format('M d, Y') }}
                                        @if($driver->isLicenseExpiringSoon())
                                            <span class="badge badge-warning">Soon</span>
                                        @endif
                                    </td>
                                    <td><span class="badge {{ $driver->status_badge_class }}">{{ $driver->status_label }}</span></td>
                                    <td>
                                        <button class="btn btn-sm btn-primary" onclick="editDriver({{ $driver->id }})"><i class="fas fa-edit"></i></button>
                                        <button class="btn btn-sm btn-danger" onclick="deleteDriver({{ $driver->id }})"><i class="fas fa-trash"></i></button>
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="6" class="text-center py-4">No drivers found.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                @if($drivers->hasPages())
                    <div class="card-footer">{{ $drivers->links() }}</div>
                @endif
            </div>
        </div>
    </div>
@stop

<!-- Add Driver Modal -->
<div class="modal fade" id="addDriverModal">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-primary"><h5 class="modal-title">Add Driver</h5><button type="button" class="close text-white" data-dismiss="modal">&times;</button></div>
            <form id="addDriverForm">
                @csrf
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6"><div class="form-group"><label>Name *</label><input type="text" name="name" class="form-control" required></div></div>
                        <div class="col-md-6"><div class="form-group"><label>Phone *</label><input type="text" name="phone" class="form-control" required></div></div>
                    </div>
                    <div class="row">
                        <div class="col-md-6"><div class="form-group"><label>License Number *</label><input type="text" name="license_number" class="form-control" required></div></div>
                        <div class="col-md-6"><div class="form-group"><label>License Expiry *</label><input type="date" name="license_expiry" class="form-control" required></div></div>
                    </div>
                </div>
                <div class="modal-footer"><button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button><button type="submit" class="btn btn-primary">Save</button></div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Driver Modal -->
<div class="modal fade" id="editDriverModal">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-primary"><h5 class="modal-title">Edit Driver</h5><button type="button" class="close text-white" data-dismiss="modal">&times;</button></div>
            <form id="editDriverForm">
                @csrf
                @method('PUT')
                <input type="hidden" id="edit_driver_id">
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6"><div class="form-group"><label>Name *</label><input type="text" id="edit_name" name="name" class="form-control" required></div></div>
                        <div class="col-md-6"><div class="form-group"><label>Phone *</label><input type="text" id="edit_phone" name="phone" class="form-control" required></div></div>
                    </div>
                    <div class="row">
                        <div class="col-md-6"><div class="form-group"><label>License Number *</label><input type="text" id="edit_license_number" name="license_number" class="form-control" required></div></div>
                        <div class="col-md-6"><div class="form-group"><label>License Expiry *</label><input type="date" id="edit_license_expiry" name="license_expiry" class="form-control" required></div></div>
                    </div>
                </div>
                <div class="modal-footer"><button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button><button type="submit" class="btn btn-primary">Update</button></div>
            </form>
        </div>
    </div>
</div>

@section('js')
<script>
$('#addDriverForm').on('submit', function(e) {
    e.preventDefault();
    $.ajax({
        url: '{{ route("b2b.transport-provider.fleet.drivers.store") }}',
        method: 'POST',
        data: $(this).serialize(),
        success: function(response) {
            toastr.success(response.message);
            location.reload();
        },
        error: function(xhr) {
            toastr.error('Failed to add driver');
        }
    });
});

function editDriver(id) {
    $.get(`/b2b/transport-provider/fleet/drivers/${id}`, function(driver) {
        $('#edit_driver_id').val(driver.id);
        $('#edit_name').val(driver.name);
        $('#edit_phone').val(driver.phone);
        $('#edit_license_number').val(driver.license_number);
        $('#edit_license_expiry').val(driver.license_expiry);
        $('#editDriverModal').modal('show');
    });
}

$('#editDriverForm').on('submit', function(e) {
    e.preventDefault();
    let id = $('#edit_driver_id').val();
    $.ajax({
        url: `/b2b/transport-provider/fleet/drivers/${id}`,
        method: 'PUT',
        data: $(this).serialize(),
        success: function(response) {
            toastr.success(response.message);
            location.reload();
        }
    });
});

function deleteDriver(id) {
    if(confirm('Delete this driver?')) {
        $.ajax({
            url: `/b2b/transport-provider/fleet/drivers/${id}`,
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
