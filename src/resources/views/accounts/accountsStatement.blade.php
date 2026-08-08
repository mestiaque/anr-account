@extends(adminTheme().'layouts.app') @section('title')
<title>Account Statement Report</title>
@endsection @push('css')
<style type="text/css">
    .loader-overlay {
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(0, 0, 0, 0.5);
        display: none;
        justify-content: center;
        align-items: center;
        z-index: 9999;
    }
    .loader-overlay.active {
        display: flex;
    }
    .loader-content {
        text-align: center;
        background: #fff;
        padding: 30px 50px;
        border-radius: 10px;
        box-shadow: 0 0 20px rgba(0,0,0,0.3);
    }
    .loader-content i {
        font-size: 40px;
        color: #4a90e2;
        animation: spin 1s linear infinite;
    }
    .loader-content p {
        margin-top: 15px;
        font-size: 16px;
        color: #333;
    }
    @keyframes spin {
        0% { transform: rotate(0deg); }
        100% { transform: rotate(360deg); }
    }
</style>
@endpush @section('contents')

<div class="flex-grow-1">
    <!-- Loader Overlay -->
    <div class="loader-overlay" id="pageLoader">
        <div class="loader-content">
            <i class="fa-solid fa-circle-notch"></i>
            <p>Loading, please wait...</p>
        </div>
    </div>
    <!-- Start -->
    <div class="card mb-30">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h3>Account View</h3>
            <div class="dropdown">
                <a href="javascript:void(0)" class="btn-custom danger" style="padding:5px 15px;" id="ExportAction" ><i class="fa-solid fa-file-excel"></i> Export</a>
                <a href="javascript:void(0)" class="btn-custom primary" style="padding:5px 15px;" id="PrintAction" >
                    <i class="fa fa-print"></i> Print
                </a>
                <a href="{{route('admin.accounts.statement')}}" class="btn-custom yellow">
                    <i class="bx bx-rotate-left"></i>
                </a>
            </div>
        </div>
        <div class="card-body">
            @include(adminTheme().'alerts')
            <div class="row">
                <div class="col-md-8">
                    <form action="{{route('admin.accounts.statement')}}">
                        <div class="row">
                            <div class="col-md-4 mb-0">
                                <label>Select Account</label>
                                <select class="form-control" name="account_id">
                                    <option value="">Select Method</option>
                                    @foreach($accounts as $account)
                                    <option value="{{ $account->id }}" {{ $loop->first && !request()->account_id ? 'selected' : '' }}
                                        {{ $account->id == request()->account_id || (isset($method) && $account->id == $method->id) ? 'selected' : '' }}>
                                        {{ $account->name }}
                                    </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-8 mb-0">
                                <label>Seach Here..</label>
                                <div class="input-group">
                                    <input type="date" name="startDate" value="{{$from->format('Y-m-d')}}" class="form-control {{$errors->has('startDate')?'error':''}}" />
                                    <input type="date" value="{{$to->format('Y-m-d')}}" name="endDate" class="form-control {{$errors->has('endDate')?'error':''}}" />
                                    <button type="submit" class="btn btn-success btn-sm rounded-0">Search</button>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="col-md-2 offset-md-2">
                    <div class=" rounded px-3 d-flex align-items-center justify-content-between shadow-sm" style="background: rgba(0, 255, 0, 0.25)">

                        <!-- Left Icon -->
                        <div  style="font-size: xxx-large; color: rgb(6, 163, 6)">
                            <i class="fa fa-wallet"></i>
                        </div>

                        <!-- Right Content -->
                        <div class="text-right">
                            <div style="font-size: 13px; color: #6c757d;">Current Balance</div>
                            <div class="text-dark font-weight-bold" style="font-size: 18px;">
                                {{ isset($openingBalance) ? priceFormat($openingBalance + $creditTotal - $debetTotal) : priceFormat(0) }}
                            </div>
                        </div>

                    </div>
                </div>
            </div>

            <br>

            @if($method)
            <div class="PrintAreaContact">
                <style>
                    .tableReport tr th{
                        padding: 5px 10px;
                        border: 1px solid #dee2e6;
                    }
                    .tableReport tr td{
                        padding: 5px 10px;
                        border: 1px solid #dee2e6;
                    }
                </style>
                <div class="text-center mb-4">
                    <div class="d-flex align-items-center justify-content-center">
                        <img src="{{asset(general()->logo())}}" alt="logo" style="max-height: 80px;" class="mr-3">
                        <div class="text-left">
                            <h2 style="font-size: 3rem" class="mb-1">{{general()->title}}</h2>
                            <p class="mb-0">
                                {!!general()->address_one!!} | <b>Phone:</b> {{general()->mobile}} | <b>Email:</b> {{general()->email}}
                            </p>
                        </div>
                    </div>
                    <span style="display: inline-block;padding: 1px 25px;border: 1px solid #e3cfcf;border-radius: 5px;background: #fbfbfb;">{{$method->name}} Statement</span>
                </div>
                <div class="table-responsive">
                    <table  class="table tableReport" >
                        <thead>
                            <tr>
                                <th style="width: 120px;min-width: 120px;">Date</th>
                                <th style="width: 130px;min-width: 130px;">Reference</th>
                                <th style="width: 130px;min-width: 130px;">Type</th>
                                <th style="min-width: 200px;">Particulars</th>
                                <th style="width: 130px;min-width: 130px;">Debit</th>
                                <th style="width: 130px;min-width: 130px;">Credit</th>
                                <th style="width: 150px;min-width: 150px;">Balance</th>
                                <th style="width: 130px;min-width: 130px;">Accounts</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td></td>
                                <td></td>
                                <td></td>
                                <td>Previous Balance</td>
                                <td></td>
                                <td></td>
                                <td>{{ priceFormat($openingBalance) }}</td>
                                <td></td>
                            </tr>
                            @forelse($transections as $tran)
                                <tr>
                                    <td>{{ $tran->transaction_date->format('d-m-Y') }}</td>
                                    <td>{{ $tran->reference }}</td>
                                    <td class="text-capitalize">{{ $tran->direction }}</td>
                                    <td>{{ $tran->particulars }}</td>
                                    <td>
                                        @if($tran->direction == 'debit')
                                            {{ priceFormat($tran->amount) }}
                                        @endif
                                    </td>
                                    <td>
                                        @if($tran->direction == 'credit')
                                            {{ priceFormat($tran->amount) }}
                                        @endif
                                    </td>
                                    <td>{{ priceFormat($tran->running_balance) }}</td>
                                    <td>{{ $method->name }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" style="text-align:center;">No Record</td>
                                </tr>
                            @endforelse
                        </tbody>
                        <tfoot>
                            <tr>
                                <td colspan="4"></td>
                                <td>
                                    {{ priceFormat($debetTotal) }}
                                </td>
                                <td>
                                    {{ priceFormat($creditTotal) }}
                                </td>
                                <td>{{ priceFormat($openingBalance + $creditTotal - $debetTotal) }}</td>
                                <td></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
            @endif

        </div>
    </div>
</div>


@endsection @push('js')
<script>
    $(document).ready(function () {
        // Show loader on form submit
        $('form').on('submit', function() {
            $('#pageLoader').addClass('active');
        });

        // Hide loader when page is fully loaded
        $(window).on('load', function() {
            $('#pageLoader').removeClass('active');
        });

        // Also hide on page ready in case of back button
        $(document).ajaxComplete(function() {
            $('#pageLoader').removeClass('active');
        });

        $('#example').DataTable( {
	        dom: 'Bfrtip',
	        buttons: [
	            'excel', 'pdf', 'print'
	        ]
	    } );

    });
</script>

@endpush
