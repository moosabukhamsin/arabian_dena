@extends('dashboard.layout', ['page' => 'product_item'])
@section('content')
    <div class="main-content app-content mt-0">
        <div class="side-app">
            <div class="main-container container-fluid">
                <div class="page-header">
                    <h1 class="page-title">Product Item</h1>
                </div>
                <div class="row row-sm">
                    <div class="col-lg-12">
                        <div class="card">
                            <div class="card-body">
                                <div class="d-flex flex-column" style="gap: 14px;">
                                    <div><strong>Series Number:</strong> {{ $ProductItem->series_number }}</div>
                                    <div><strong>Product:</strong> {{ $ProductItem->product->name ?? '-' }}</div>
                                    <div><strong>Inspection Date:</strong> {{ $ProductItem->inspection_date ?? '-' }}</div>
                                    <div>
                                        <strong>Due Days:</strong>
                                        @if($ProductItem->inspection_date)
                                            @php
                                                $expiryDate = \Carbon\Carbon::parse($ProductItem->inspection_date)->addYear();
                                                $dueDays = (int) floor(now()->floatDiffInDays($expiryDate, false));
                                            @endphp
                                            {{ max($dueDays, 0) }}
                                        @else
                                            -
                                        @endif
                                    </div>
                                    <div><strong>Status:</strong> {{ $ProductItem->status ?? '-' }}</div>
                                    <div>
                                        <strong>Certificate:</strong>
                                        @if($ProductItem->certificate)
                                            <a href="{{ URL('storage/' . $ProductItem->certificate) }}" target="_blank" class="btn btn-sm btn-outline-primary me-2">View file</a>
                                            <a href="{{ route('dashboard.download_product_item_certificate', $ProductItem) }}" class="btn btn-sm btn-primary">Download</a>
                                        @else
                                            -
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
