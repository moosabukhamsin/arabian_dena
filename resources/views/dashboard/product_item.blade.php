@extends('dashboard.layout', ['page' => 'product_item'])
@section('content')
    <div class="main-content app-content mt-0">
        <div class="side-app">
            <div class="main-container container-fluid">
                <div class="page-header">
                    <h1 class="page-title">Product Item</h1>
                    <div class="page-options">
                        <a href="{{ url()->previous() ?: route('dashboard.product_items') }}" class="btn btn-primary">
                            <i class="fe fe-arrow-left"></i> Back
                        </a>
                    </div>
                </div>
                <div class="row row-sm">
                    <div class="col-lg-12">
                        <div class="card">
                            <div class="card-body">
                                <div class="d-flex flex-column" style="gap: 14px;">
                                    <div><strong>Product Item ID:</strong> {{ $ProductItem->product_item_code ?? $ProductItem->id }}</div>
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
                                        <strong>Certificates:</strong>
                                        @php
                                            $certs = $ProductItem->Certificates ?? collect();
                                        @endphp

                                        @if($certs->isEmpty())
                                            -
                                        @else
                                            <div class="mt-2">
                                                @foreach ($certs as $idx => $cert)
                                                    <div class="d-flex align-items-center justify-content-between border p-2 mb-2">
                                                        <div class="d-flex align-items-center gap-2">
                                                            @if($idx === 0)
                                                                <span class="badge bg-success">Current</span>
                                                            @else
                                                                <span class="badge bg-secondary">Version</span>
                                                            @endif
                                                            <span class="text-muted">{{ optional($cert->created_at)->format('Y-m-d H:i') }}</span>
                                                        </div>
                                                        <div class="d-flex gap-2">
                                                            <a href="{{ URL('storage/' . $cert->certificate) }}" target="_blank" class="btn btn-sm btn-outline-primary">View</a>
                                                            <a href="{{ route('dashboard.download_product_item_certificate_version', $cert) }}" class="btn btn-sm btn-primary">Download</a>
                                                        </div>
                                                    </div>
                                                @endforeach
                                            </div>
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
