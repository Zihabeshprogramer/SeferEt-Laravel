@extends('layouts.b2b')

@section('title', 'Vehicle Management')

@section('content_header')
    <div class="row">
        <div class="col-md-8">
            <h1 class="m-0">
                <i class="fas fa-truck text-info mr-2"></i>
                Vehicle Management
            </h1>
        </div>
        <div class="col-md-4 text-right">
            <button class="btn btn-primary" data-toggle="modal" data-target="#addVehicleModal">
                <i class="fas fa-plus mr-1"></i>
                Add Vehicle
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
                    <h3>{{ $stats['total_vehicles'] }}</h3>
                    <p>Total Vehicles</p>
                </div>
                <div class="icon">
                    <i class="fas fa-truck"></i>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-6">
            <div class="small-box bg-success">
                <div class="inner">
                    <h3>{{ $stats['available'] }}</h3>
                    <p>Available</p>
                </div>
                <div class="icon">
                    <i class="fas fa-check-circle"></i>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-6">
            <div class="small-box bg-primary">
                <div class="inner">
                    <h3>{{ $stats['assigned'] }}</h3>
                    <p>On Assignment</p>
                </div>
                <div class="icon">
                    <i class="fas fa-route"></i>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-6">
            <div class="small-box bg-warning">
                <div class="inner">
                    <h3>{{ $stats['under_maintenance'] }}</h3>
                    <p>Under Maintenance</p>
                </div>
                <div class="icon">
                    <i class="fas fa-tools"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Vehicles Table -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-list mr-2"></i>
                        Vehicle Fleet
                    </h3>
                    <div class="card-tools">
                        <div class="input-group input-group-sm" style="width: 250px;">
                            <input type="text" id="searchVehicles" class="form-control" placeholder="Search vehicles...">
                            <div class="input-group-append">
                                <span class="input-group-text"><i class="fas fa-search"></i></span>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="card-body table-responsive p-0">
                    <table class="table table-hover text-nowrap">
                        <thead>
                            <tr>
                                <th>Vehicle Name</th>
                                <th>Type</th>
                                <th>Plate Number</th>
                                <th>Capacity</th>
                                <th>Brand/Model</th>
                                <th>Status</th>
                                <th>Drivers</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody id="vehiclesTableBody">
                            @forelse($vehicles as $vehicle)
                                <tr data-vehicle-id="{{ $vehicle->id }}">
                                    <td>
                                        <strong>{{ $vehicle->vehicle_name }}</strong>
                                        @if($vehicle->year)
                                            <small class="text-muted">({{ $vehicle->year }})</small>
                                        @endif
                                    </td>
                                    <td><span class="badge badge-secondary">{{ ucfirst($vehicle->vehicle_type) }}</span></td>
                                    <td><code>{{ $vehicle->plate_number }}</code></td>
                                    <td>
                                        <i class="fas fa-users mr-1"></i>{{ $vehicle->capacity }}
                                    </td>
                                    <td>
                                        @if($vehicle->brand || $vehicle->model)
                                            {{ $vehicle->brand }} {{ $vehicle->model }}
                                        @else
                                            <span class="text-muted">N/A</span>
                                        @endif
                                    </td>
                                    <td>
                                        <span class="badge {{ $vehicle->status_badge_class }}">
                                            {{ $vehicle->status_label }}
                                        </span>
                                    </td>
                                    <td>
                                        @php
                                            $primary = $vehicle->primaryDriver();
                                            $secondary = $vehicle->secondaryDriver();
                                        @endphp
                                        @if($primary || $secondary)
                                            <small>
                                                @if($primary)
                                                    <i class="fas fa-star text-warning" title="Primary"></i> {{ $primary->name }}<br>
                                                @endif
                                                @if($secondary)
                                                    <i class="fas fa-user text-info" title="Secondary"></i> {{ $secondary->name }}
                                                @endif
                                            </small>
                                        @else
                                            <span class="text-muted">No drivers</span>
                                        @endif
                                    </td>
                                    <td>
                                        <div class="btn-group">
                                            <button class="btn btn-sm btn-info" onclick="viewVehicle({{ $vehicle->id }})" title="View Details">
                                                <i class="fas fa-eye"></i>
                                            </button>
                                            <button class="btn btn-sm btn-primary" onclick="editVehicle({{ $vehicle->id }})" title="Edit">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            <button class="btn btn-sm btn-danger" onclick="deleteVehicle({{ $vehicle->id }})" title="Delete">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="text-center py-4">
                                        <i class="fas fa-truck fa-3x text-muted mb-3"></i>
                                        <p class="text-muted">No vehicles found. Click "Add Vehicle" to get started.</p>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                
                @if($vehicles->hasPages())
                    <div class="card-footer clearfix">
                        {{ $vehicles->links() }}
                    </div>
                @endif
            </div>
        </div>
    </div>
@stop

<!-- Add Vehicle Modal -->
<div class="modal fade" id="addVehicleModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header bg-primary">
                <h5 class="modal-title">
                    <i class="fas fa-plus mr-2"></i>Add New Vehicle
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <form id="addVehicleForm">
                @csrf
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Vehicle Name <span class="text-danger">*</span></label>
                                <input type="text" name="vehicle_name" class="form-control" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Vehicle Type <span class="text-danger">*</span></label>
                                <select name="vehicle_type" class="form-control" required>
                                    <option value="">Select Type</option>
                                    <option value="bus">Bus</option>
                                    <option value="van">Van</option>
                                    <option value="car">Car</option>
                                    <option value="minibus">Minibus</option>
                                    <option value="coach">Coach</option>
                                    <option value="suv">SUV</option>
                                    <option value="sedan">Sedan</option>
                                    <option value="other">Other</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Plate Number <span class="text-danger">*</span></label>
                                <input type="text" name="plate_number" class="form-control" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Capacity (Passengers) <span class="text-danger">*</span></label>
                                <input type="number" name="capacity" class="form-control" min="1" required>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Brand</label>
                                <input type="text" name="brand" class="form-control">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Model</label>
                                <input type="text" name="model" class="form-control">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Year</label>
                                <input type="number" name="year" class="form-control" min="1900" max="{{ date('Y') + 1 }}">
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <label>Notes</label>
                        <textarea name="notes" class="form-control" rows="3"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save mr-1"></i>Save Vehicle
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Vehicle Modal -->
<div class="modal fade" id="editVehicleModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header bg-primary">
                <h5 class="modal-title">
                    <i class="fas fa-edit mr-2"></i>Edit Vehicle
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <form id="editVehicleForm">
                @csrf
                @method('PUT')
                <input type="hidden" id="edit_vehicle_id" name="vehicle_id">
                <div class="modal-body">
                    <!-- Same fields as add form -->
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Vehicle Name <span class="text-danger">*</span></label>
                                <input type="text" id="edit_vehicle_name" name="vehicle_name" class="form-control" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Vehicle Type <span class="text-danger">*</span></label>
                                <select id="edit_vehicle_type" name="vehicle_type" class="form-control" required>
                                    <option value="bus">Bus</option>
                                    <option value="van">Van</option>
                                    <option value="car">Car</option>
                                    <option value="minibus">Minibus</option>
                                    <option value="coach">Coach</option>
                                    <option value="suv">SUV</option>
                                    <option value="sedan">Sedan</option>
                                    <option value="other">Other</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Plate Number <span class="text-danger">*</span></label>
                                <input type="text" id="edit_plate_number" name="plate_number" class="form-control" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Capacity <span class="text-danger">*</span></label>
                                <input type="number" id="edit_capacity" name="capacity" class="form-control" min="1" required>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Brand</label>
                                <input type="text" id="edit_brand" name="brand" class="form-control">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Model</label>
                                <input type="text" id="edit_model" name="model" class="form-control">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Year</label>
                                <input type="number" id="edit_year" name="year" class="form-control" min="1900" max="{{ date('Y') + 1 }}">
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <label>Notes</label>
                        <textarea id="edit_notes" name="notes" class="form-control" rows="3"></textarea>
                    </div>
                    <div class="form-group">
                        <div class="custom-control custom-switch">
                            <input type="checkbox" class="custom-control-input" id="edit_is_active" name="is_active" value="1">
                            <label class="custom-control-label" for="edit_is_active">Active</label>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save mr-1"></i>Update Vehicle
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@section('js')
<script>
$(document).ready(function() {
    // Add Vehicle
    $('#addVehicleForm').on('submit', function(e) {
        e.preventDefault();
        
        $.ajax({
            url: '{{ route("b2b.transport-provider.fleet.vehicles.store") }}',
            method: 'POST',
            data: $(this).serialize(),
            success: function(response) {
                if(response.success) {
                    toastr.success(response.message);
                    $('#addVehicleModal').modal('hide');
                    location.reload();
                }
            },
            error: function(xhr) {
                if(xhr.status === 422) {
                    let errors = xhr.responseJSON.errors;
                    Object.values(errors).forEach(error => {
                        toastr.error(error[0]);
                    });
                } else {
                    toastr.error('Failed to add vehicle');
                }
            }
        });
    });
    
    // Search vehicles
    $('#searchVehicles').on('keyup', function() {
        let value = $(this).val().toLowerCase();
        $('#vehiclesTableBody tr').filter(function() {
            $(this).toggle($(this).text().toLowerCase().indexOf(value) > -1);
        });
    });
});

function editVehicle(id) {
    // Fetch vehicle data and populate edit form
    $.ajax({
        url: `/b2b/transport-provider/fleet/vehicles/${id}`,
        method: 'GET',
        success: function(vehicle) {
            $('#edit_vehicle_id').val(vehicle.id);
            $('#edit_vehicle_name').val(vehicle.vehicle_name);
            $('#edit_vehicle_type').val(vehicle.vehicle_type);
            $('#edit_plate_number').val(vehicle.plate_number);
            $('#edit_capacity').val(vehicle.capacity);
            $('#edit_brand').val(vehicle.brand);
            $('#edit_model').val(vehicle.model);
            $('#edit_year').val(vehicle.year);
            $('#edit_notes').val(vehicle.notes);
            $('#edit_is_active').prop('checked', vehicle.is_active);
            $('#editVehicleModal').modal('show');
        }
    });
}

$('#editVehicleForm').on('submit', function(e) {
    e.preventDefault();
    let vehicleId = $('#edit_vehicle_id').val();
    
    $.ajax({
        url: `/b2b/transport-provider/fleet/vehicles/${vehicleId}`,
        method: 'PUT',
        data: $(this).serialize(),
        success: function(response) {
            if(response.success) {
                toastr.success(response.message);
                $('#editVehicleModal').modal('hide');
                location.reload();
            }
        },
        error: function(xhr) {
            toastr.error('Failed to update vehicle');
        }
    });
});

function deleteVehicle(id) {
    if(confirm('Are you sure you want to delete this vehicle?')) {
        $.ajax({
            url: `/b2b/transport-provider/fleet/vehicles/${id}`,
            method: 'DELETE',
            data: { _token: '{{ csrf_token() }}' },
            success: function(response) {
                if(response.success) {
                    toastr.success(response.message);
                    location.reload();
                }
            },
            error: function(xhr) {
                toastr.error(xhr.responseJSON.message || 'Failed to delete vehicle');
            }
        });
    }
}

function viewVehicle(id) {
    // View vehicle details - implement as needed
    toastr.info('View details functionality coming soon');
}
</script>
@stop
