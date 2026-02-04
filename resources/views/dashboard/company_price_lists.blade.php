@extends('dashboard.layout', ['page' => 'companies'])
@section('content')
    <!--app-content open-->
    <div class="main-content app-content mt-0">
        <div class="side-app">
            <!-- CONTAINER -->
            <div class="main-container container-fluid">
                <!-- PAGE-HEADER -->
                <div class="page-header">
                    <h1 class="page-title">{{ $Company->name }} - Price Lists</h1>
                    <div class="page-options">
                        <a href="{{ route('dashboard.companies') }}" class="btn btn-secondary">Back to Companies</a>
                    </div>
                </div>
                <!-- PAGE-HEADER END -->

                <!-- ROW-1 -->
                <div class="row row-sm">
                    <div class="col-lg-12">
                        <div class="card">
                            <div class="card-header">
                                <h3 class="card-title">Current Price List</h3>
                                <div class="card-options">
                                    <button class="btn btn-success" onclick="saveAllPrices()">
                                        Save All Prices
                                    </button>
                                </div>
                            </div>
                            <div class="card-body">
                                <form id="priceListForm" action="{{ route('dashboard.bulk_update_company_price_lists', $Company->id) }}" method="POST">
                                    @csrf
                                    <div class="table-responsive">
                                        <table class="table table-bordered text-nowrap key-buttons border-bottom">
                                        <thead>
                                            <tr>
                                                <th class="border-bottom-0">Product</th>
                                                <th class="border-bottom-0">Pricing Type</th>
                                                <th class="border-bottom-0">Daily Price</th>
                                                <th class="border-bottom-0" id="weekly-header" style="display: {{ $Company->pricing_type === 'daily_weekly_monthly' ? 'table-cell' : 'none' }};">Weekly Price</th>
                                                <th class="border-bottom-0">Monthly Price</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach ($products as $product)
                                                @php
                                                    $existingPrice = $priceLists->where('product_id', $product->id)->first();
                                                @endphp
                                                <tr>
                                                    <td>
                                                        @if($product->image)
                                                            <img src="{{ asset('storage/'.$product->image) }}" alt="Product Image" width="30" class="me-2">
                                                        @else
                                                            <div class="bg-light d-inline-block me-2" style="width: 30px; height: 30px; border-radius: 4px;"></div>
                                                        @endif
                                                        {{ $product->name }}
                                                    </td>
                                                    <td>
                                                        <span class="badge {{ $Company->pricing_type === 'daily_monthly' ? 'bg-info' : 'bg-success' }}">
                                                            {{ $Company->pricing_type === 'daily_monthly' ? 'Daily & Monthly' : 'Daily, Weekly & Monthly' }}
                                                        </span>
                                                    </td>
                                                    <td>
                                                        <input type="number"
                                                               name="daily_price[{{ $product->id }}]"
                                                               class="form-control form-control-sm price-input"
                                                               step="0.01"
                                                               value="{{ $existingPrice ? $existingPrice->daily_price : 0 }}"
                                                               placeholder="0.00">
                                                    </td>
                                                    <td class="weekly-cell" style="display: {{ $Company->pricing_type === 'daily_weekly_monthly' ? 'table-cell' : 'none' }};">
                                                        @if($Company->pricing_type === 'daily_weekly_monthly')
                                                            <input type="number"
                                                                   name="weekly_price[{{ $product->id }}]"
                                                                   class="form-control form-control-sm price-input"
                                                                   step="0.01"
                                                                   value="{{ $existingPrice ? $existingPrice->weekly_price : 0 }}"
                                                                   placeholder="0.00">
                                                        @endif
                                                    </td>
                                                    <td>
                                                        <input type="number"
                                                               name="monthly_price[{{ $product->id }}]"
                                                               class="form-control form-control-sm price-input"
                                                               step="0.01"
                                                               value="{{ $existingPrice ? $existingPrice->monthly_price : 0 }}"
                                                               placeholder="0.00">
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- End Row -->

            </div>
            <!-- CONTAINER END -->
        </div>
    </div>
    <!--app-content close-->

    <script>
        function saveAllPrices() {
            document.getElementById('priceListForm').submit();
        }
    </script>
@endsection
