@extends('layouts.admin')

@section('title', 'User Management')
@section('page-title', 'User Management')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item active">Users</li>
@endsection

@section('content')
    <!-- Stats Cards -->
    <div class="row">
        <div class="col-lg-2 col-6">
            <div class="small-box bg-primary">
                <div class="inner">
                    <h3>{{ $stats['total'] }}</h3>
                    <p>Total Users</p>
                </div>
                <div class="icon">
                    <i class="fas fa-users"></i>
                </div>
            </div>
        </div>
        
        <div class="col-lg-2 col-6">
            <div class="small-box bg-success">
                <div class="inner">
                    <h3>{{ $stats['customers'] }}</h3>
                    <p>Customers</p>
                </div>
                <div class="icon">
                    <i class="fas fa-user"></i>
                </div>
            </div>
        </div>
        
        <div class="col-lg-2 col-6">
            <div class="small-box bg-warning">
                <div class="inner">
                    <h3>{{ $stats['partners'] }}</h3>
                    <p>Partners</p>
                </div>
                <div class="icon">
                    <i class="fas fa-handshake"></i>
                </div>
            </div>
        </div>
        
        <div class="col-lg-2 col-6">
            <div class="small-box bg-info">
                <div class="inner">
                    <h3>{{ $stats['admins'] }}</h3>
                    <p>Admins</p>
                </div>
                <div class="icon">
                    <i class="fas fa-user-shield"></i>
                </div>
            </div>
        </div>
        
        <div class="col-lg-2 col-6">
            <div class="small-box bg-success">
                <div class="inner">
                    <h3>{{ $stats['active'] }}</h3>
                    <p>Active</p>
                </div>
                <div class="icon">
                    <i class="fas fa-check-circle"></i>
                </div>
            </div>
        </div>
        
        <div class="col-lg-2 col-6">
            <div class="small-box bg-danger">
                <div class="inner">
                    <h3>{{ $stats['inactive'] }}</h3>
                    <p>Inactive</p>
                </div>
                <div class="icon">
                    <i class="fas fa-times-circle"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Users Table -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-users mr-2"></i>
                        All Users
                    </h3>
                    <div class="card-tools">
                        <button type="button" class="btn btn-sm btn-success" data-toggle="modal" data-target="#createUserModal">
                            <i class="fas fa-user-plus mr-1"></i>
                            Add User
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    <!-- Filters -->
                    <div class="row mb-3">
                        <div class="col-md-3">
                            <select class="form-control select2" id="roleFilter">
                                <option value="">All Roles</option>
                                <option value="customer">Customers</option>
                                <option value="partner">Partners</option>
                                <option value="admin">Admins</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <select class="form-control select2" id="statusFilter">
                                <option value="">All Status</option>
                                <option value="active">Active</option>
                                <option value="inactive">Inactive</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <input type="text" class="form-control" placeholder="Search users..." id="searchUsers">
                        </div>
                        <div class="col-md-2">
                            <button class="btn btn-primary btn-block" id="filterBtn">
                                <i class="fas fa-filter mr-1"></i>
                                Filter
                            </button>
                        </div>
                    </div>

                    <!-- Users Table -->
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped" id="usersTable">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Name</th>
                                    <th>Email</th>
                                    <th>Role</th>
                                    <th>Status</th>
                                    <th>Country</th>
                                    <th>Joined</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @if(empty($users))
                                    <tr>
                                        <td colspan="8" class="text-center py-4">
                                            <i class="fas fa-users text-muted" style="font-size: 3rem;"></i>
                                            <p class="text-muted mt-2">No users found</p>
                                            <button class="btn btn-primary btn-sm" data-toggle="modal" data-target="#createUserModal">
                                                <i class="fas fa-user-plus mr-1"></i>
                                                Add First User
                                            </button>
                                        </td>
                                    </tr>
                                @else
                                    <!-- Users will be populated here -->
                                    <tr>
                                        <td>1</td>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <i class="fas fa-user-circle text-muted mr-2" style="font-size: 1.5rem;"></i>
                                                <div>
                                                    <strong>John Doe</strong><br>
                                                    <small class="text-muted">Sample User</small>
                                                </div>
                                            </div>
                                        </td>
                                        <td>john@example.com</td>
                                        <td>
                                            <span class="badge badge-success">Customer</span>
                                        </td>
                                        <td>
                                            <span class="badge badge-success">
                                                <i class="fas fa-check-circle mr-1"></i>
                                                Active
                                            </span>
                                        </td>
                                        <td>USA</td>
                                        <td>Jan 15, 2024</td>
                                        <td>
                                            <div class="btn-group" role="group">
                                                <button type="button" class="btn btn-sm btn-info" data-bs-toggle="tooltip" title="View Details">
                                                    <i class="fas fa-eye"></i>
                                                </button>
                                                <button type="button" class="btn btn-sm btn-warning" data-bs-toggle="tooltip" title="Edit User">
                                                    <i class="fas fa-edit"></i>
                                                </button>
                                                <button type="button" class="btn btn-sm btn-danger" data-bs-toggle="tooltip" title="Delete User">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                @endif
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    <div class="d-flex justify-content-between align-items-center mt-3">
                        <div>
                            <small class="text-muted">Showing 1 to 10 of 100 entries</small>
                        </div>
                        <nav>
                            <ul class="pagination pagination-sm mb-0">
                                <li class="page-item disabled">
                                    <span class="page-link">Previous</span>
                                </li>
                                <li class="page-item active">
                                    <span class="page-link">1</span>
                                </li>
                                <li class="page-item">
                                    <a class="page-link" href="#">2</a>
                                </li>
                                <li class="page-item">
                                    <a class="page-link" href="#">3</a>
                                </li>
                                <li class="page-item">
                                    <a class="page-link" href="#">Next</a>
                                </li>
                            </ul>
                        </nav>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
<script>
$(document).ready(function() {
    // Initialize DataTable (if using DataTables)
    $('#usersTable').DataTable({
        responsive: true,
        pageLength: 25,
        order: [[0, 'desc']],
        columnDefs: [
            { orderable: false, targets: [7] } // Disable sorting on Actions column
        ]
    });
    
    // Filter functionality
    $('#filterBtn').on('click', function() {
        // Implement filter logic here

    });
    
    // Search functionality
    $('#searchUsers').on('keyup', function() {
        // Implement search logic here

    });
});
</script>
@endsection
