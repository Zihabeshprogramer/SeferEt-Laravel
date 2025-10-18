@extends('layouts.admin')

@section('title', 'Create Admin User')
@section('page-title', 'Create Admin User')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item"><a href="{{ route('admin.users.moderation') }}">User Moderation</a></li>
    <li class="breadcrumb-item active">Create Admin User</li>
@endsection

@section('content')
    <div class="row">
        <div class="col-md-8 offset-md-2">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Admin User Details</h3>
                </div>
                <form action="{{ route('admin.users.store-admin') }}" method="POST">
                    @csrf
                    <div class="card-body">
                        <div class="form-group">
                            <label for="name">Name *</label>
                            <input type="text" class="form-control @error('name') is-invalid @enderror" 
                                   id="name" name="name" value="{{ old('name') }}" required>
                            @error('name')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                        
                        <div class="form-group">
                            <label for="email">Email *</label>
                            <input type="email" class="form-control @error('email') is-invalid @enderror" 
                                   id="email" name="email" value="{{ old('email') }}" required>
                            @error('email')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                        
                        <div class="form-group">
                            <label for="password">Password *</label>
                            <input type="password" class="form-control @error('password') is-invalid @enderror" 
                                   id="password" name="password" required>
                            @error('password')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                        
                        <div class="form-group">
                            <label for="password_confirmation">Confirm Password *</label>
                            <input type="password" class="form-control" 
                                   id="password_confirmation" name="password_confirmation" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="role">User Role *</label>
                            <select class="form-control @error('role') is-invalid @enderror" 
                                    id="role" name="role" required readonly>
                                <option value="admin" selected>Admin (Only admin users can be created)</option>
                            </select>
                            <small class="form-text text-muted">Only admin users can be created through this interface. Customers and partners register through the public registration.</small>
                            @error('role')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                        
                        <div class="form-group">
                            <label for="permission_roles">Administrative Permission Roles *</label>
                            <select class="form-control @error('permission_roles') is-invalid @enderror" 
                                    id="permission_roles" name="permission_roles[]" multiple size="5" required>
                                @foreach($assignableRoles as $permissionRole)
                                    <option value="{{ $permissionRole->name }}" 
                                            {{ in_array($permissionRole->name, old('permission_roles', [])) ? 'selected' : '' }}>
                                        {{ str_replace('_', ' ', ucwords($permissionRole->name, '_')) }}
                                    </option>
                                @endforeach
                            </select>
                            <small class="form-text text-muted">Select one or more administrative roles. This admin can have multiple permission roles.</small>
                            @error('permission_roles')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                    <div class="card-footer">
                        <button type="submit" class="btn btn-primary">Create Admin User</button>
                        <a href="{{ route('admin.users.moderation') }}" class="btn btn-secondary">Cancel</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@push('styles')
    <style>
        .form-group label {
            font-weight: 600;
        }
        select[multiple] {
            height: auto !important;
        }
    </style>
@endpush
