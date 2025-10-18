@extends('layouts.admin')

@section('title', 'Analytics')

@section('page-title', 'Analytics & Reports')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item active">Analytics</li>
@endsection

@section('content')
    <div class="row">
        <!-- Revenue Chart -->
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-chart-line mr-2"></i>
                        Revenue Analytics
                    </h3>
                </div>
                <div class="card-body">
                    <canvas id="revenueChart" style="height: 300px;"></canvas>
                </div>
            </div>
        </div>
        
        <!-- Bookings Breakdown -->
        <div class="col-md-4">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-chart-pie mr-2"></i>
                        Bookings Breakdown
                    </h3>
                </div>
                <div class="card-body">
                    <canvas id="bookingsChart" style="height: 300px;"></canvas>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Monthly Stats -->
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Monthly Statistics</h3>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>Month</th>
                                    <th>Bookings</th>
                                    <th>Revenue</th>
                                    <th>Growth</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>January 2024</td>
                                    <td>25</td>
                                    <td>$62,500</td>
                                    <td><span class="badge badge-success">+15%</span></td>
                                </tr>
                                <tr>
                                    <td>December 2023</td>
                                    <td>32</td>
                                    <td>$80,000</td>
                                    <td><span class="badge badge-success">+20%</span></td>
                                </tr>
                                <tr>
                                    <td>November 2023</td>
                                    <td>28</td>
                                    <td>$70,000</td>
                                    <td><span class="badge badge-warning">+5%</span></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Popular Packages -->
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Popular Packages</h3>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>Package</th>
                                    <th>Bookings</th>
                                    <th>Revenue</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>Premium Umrah Package</td>
                                    <td>45</td>
                                    <td>$112,500</td>
                                </tr>
                                <tr>
                                    <td>Economy Umrah Package</td>
                                    <td>38</td>
                                    <td>$76,000</td>
                                </tr>
                                <tr>
                                    <td>Luxury Umrah Package</td>
                                    <td>22</td>
                                    <td>$110,000</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
