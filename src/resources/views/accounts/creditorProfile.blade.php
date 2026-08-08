@extends(adminTheme().'layouts.app')
@section('title')
<title>{{websiteTitle('Creditor Profile - ' . $creditor->name)}}</title>
@endsection

@push('css')
<style>
    .ProfileImage { width: 90px; height: 90px; border: 3px solid #fff; box-shadow: 0 0 15px rgba(0,0,0,0.1); }
    .stat-card { border: none; border-radius: 10px; transition: 0.3s; }
    .stat-card:hover { transform: translateY(-5px); }

    .nav-pills .nav-link { color: #555; font-weight: 600; border: 1px solid #eee; margin-right: 5px; }
    .nav-pills .nav-link.active { background-color: #0d6efd !important; color: #fff !important; }

    .table-ledger thead th { background: #ffffffad; text-transform: uppercase; font-size: 12px; letter-spacing: 0.5px; color: #555; }
    .table-ledger thead tr {border-left: 1px solid #55555570; }
    .credit-row { border-left: 1px solid #28a745; }
    .debit-row { border-left: 1px solid #dc3545; }
    .amount-text { font-family: 'Courier New', Courier, monospace; font-weight: bold; }

    .amount-card {
        border-left: 4px solid #198754;
        transition: all 0.25s ease-in-out;
        margin-bottom: 0.6rem;
        padding: 0.6rem 2rem;
    }
    .amount-card:hover {
        transform: translateY(-3px);
        box-shadow: 0 6px 16px rgba(0,0,0,0.12) !important;
    }
    .c1 { background: #00ff3c18 !important; }
    .c2 { background: #00d5ff1e !important; }
    .c3 { background: #ff001913 !important; }
</style>
@endpush
@section('contents')
<div class="flex-grow-1">

    <div class="breadcrumb-area">
        <h1>Profile</h1>
        <ol class="breadcrumb">
            <li class="item">
                <a href="{{route('admin.dashboard')}}"><i class="bx bx-home-alt"></i></a>
            </li>
            <li class="item"><a href="{{route('admin.creditors.index')}}">Creditor List</a></li>
            <li class="item">Profile</li>
        </ol>
    </div>

    @include(adminTheme().'alerts')

    <div class="row">
        {{-- Profile Info --}}
        <div class="col-lg-3">
            <div class="card shadow-sm border-0 mb-4">
                <div class="card-body text-center">
                    <div class="rounded-circle mb-3 ProfileImage mx-auto d-flex align-items-center justify-content-center bg-light">
                        <i class="bx bx-user" style="font-size: 40px; color: #aaa;"></i>
                    </div>
                    <h5 class="fw-bold mb-1">{{ $creditor->name }}</h5>
                    <p class="text-muted small mb-3">{{ $creditor->mobile }}</p>
                    <div class="text-start border-top pt-3 smallx">
                        <p class="mb-1"><strong>Email:</strong> {{ $creditor->email }}</p>
                        <p class="mb-1"><strong>Address:</strong> {{ $creditor->address }}</p>
                        <p class="mb-0"><strong>Creditor Since:</strong> {{ $creditor->created_at->format('d M, Y') }}</p>
                    </div>
                </div>
            </div>

            <div class="row g-3 mb-4">
                <div class="col-md-12">
                    <div class="card amount-card c1 shadow-sm border-0">
                        <div class="card-body">
                            <h6 class="text-muted mb-1 text-uppercase fw-semibold">Total Purchases</h6>
                            <h3 class="text-success fw-bold mb-0">{{ priceFullFormat($totalPurchases) }}</h3>
                        </div>
                    </div>
                </div>

                <div class="col-md-12">
                    <div class="card amount-card c2 shadow-sm border-0">
                        <div class="card-body">
                            <h6 class="text-muted mb-1 text-uppercase fw-semibold">Total Paid</h6>
                            <h3 class="text-info fw-bold mb-0">{{ priceFullFormat($totalPaid) }}</h3>
                        </div>
                    </div>
                </div>

                <div class="col-md-12">
                    <div class="card amount-card c3 shadow-sm border-0">
                        <div class="card-body">
                            <h6 class="text-muted mb-1 text-uppercase fw-semibold">Net Due Balance</h6>
                            <h3 class="text-danger fw-bold mb-0">{{ priceFullFormat($totalPurchases - $totalPaid) }}</h3>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Tabs --}}
        <div class="col-lg-9">
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-white pt-0">
                    <ul class="nav nav-pills card-header-pills ms-2" id="pills-tab" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link active" id="pills-history-tab" data-bs-toggle="pill" data-open-tab="#pills-history" data-bs-target="#pills-history" type="button" role="tab"> Statement / History </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="pills-bill-tab" data-bs-toggle="pill" data-open-tab="#pills-bill" data-bs-target="#pills-bill" type="button" role="tab"> Add Bill </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="pills-payment-tab" data-bs-toggle="pill" data-open-tab="#pills-payment" data-bs-target="#pills-payment" type="button" role="tab"> Make Payment </button>
                        </li>
                    </ul>
                </div>

                <div class="card-body">
                    <div class="tab-content" id="pills-tabContent">

                        {{-- Tab 1: Ledger --}}
                        <div class="tab-pane fade show active" id="pills-history" role="tabpanel">

                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <form method="GET" action="{{ route('admin.creditors.show', $creditor->id) }}" class="mb-0">
                                    <div class="row g-2">
                                        <div class="col-md-3">
                                            <input type="text" name="search" class="form-control form-control-sm" placeholder="Search by Title/Invoice/Transaction ID" value="{{ request('search') }}">
                                        </div>
                                        <div class="col-md-6 d-flex gap-2">
                                            <input type="date" name="startDate" class="form-control form-control-sm" value="{{ request('startDate') }}">
                                            <span class="text-muted" style="padding: 0.5rem"><i>TO</i></span>
                                            <input type="date" name="endDate" class="form-control form-control-sm" value="{{ request('endDate') }}">
                                        </div>
                                        <div class="col-md-3">
                                            <button type="submit" class="btn btn-sm btn-primary me-2"><i class="bx bx-filter"></i> Filter</button>
                                            <a href="{{ route('admin.creditors.show', $creditor->id) }}" class="btn btn-sm btn-secondary"><i class="bx bx-reset"></i> Reset</a>
                                        </div>
                                    </div>
                                </form>
                            </div>

                            <div class="table-responsive">
                                <table class="table align-middle table-ledger">
                                    <thead>
                                        <tr>
                                            <th>Date</th>
                                            <th>Title/Invoice/Transaction ID</th>
                                            <th>Description/Note</th>
                                            <th class="text-right">Credit (+)</th>
                                            <th class="text-right">Debit (-)</th>
                                            <th class="text-right">Balance</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($ledgerEntries as $item)
                                        <tr class="{{ $item->type == 'payment' ? 'debit-row' : 'credit-row' }}">
                                            <td>{{ $item->date ? $item->date->format('d-m-Y') : '' }}</td>
                                            <td>
                                                {{ $item->title }}
                                                @if($item->type == 'bill')
                                                    @can('ac_creditors.edit')
                                                    <a href="javascript:void(0)" data-toggle="modal" data-target="#EditBill_{{$item->id}}" class="btn btn-sm btn-link text-primary" title="Edit Bill"><i class="bx bx-edit"></i></a>
                                                    @endcan
                                                    @can('ac_creditors.delete')
                                                    <form action="{{route('admin.creditor-bills.destroy',$item->id)}}" method="post" style="display:inline-block;" onsubmit="return confirm('Delete this bill?')">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="btn btn-sm btn-link text-danger" style="border:0;" title="Delete Bill"><i class="bx bx-trash"></i></button>
                                                    </form>
                                                    @endcan
                                                @endif
                                            </td>
                                            <td>{{ $item->note ?? '-' }}</td>
                                            <td class="text-right text-success">{{ $item->credit > 0 ? priceFullFormat($item->credit) : '-' }}</td>
                                            <td class="text-right text-danger">{{ $item->debit > 0 ? priceFullFormat($item->debit) : '-' }}</td>
                                            <td class="text-right {{ $loop->first ? 'font-weight-bold' : '' }}">{{ priceFullFormat($item->balance) }}</td>
                                        </tr>
                                        @empty
                                        <tr><td colspan="6" class="text-center text-muted"><em>No entries found</em></td></tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                            <div class="mt-3">{{ $ledgerEntries->links('pagination::bootstrap-4') }}</div>
                        </div>

                        {{-- Tab 2: Add Bill --}}
                        <div class="tab-pane fade" id="pills-bill" role="tabpanel">
                            <form action="{{ route('admin.creditor-bills.store', $creditor->id) }}" method="POST" class="p-3 border rounded">
                                @csrf
                                <div class="row">
                                    <div class="col-md-4 mb-3">
                                        <label class="form-label fw-bold">Created Date</label>
                                        <input type="date" name="transaction_date" value="{{Carbon\Carbon::now()->format('Y-m-d')}}" class="form-control" required>
                                    </div>
                                    <div class="col-md-4 mb-3">
                                        <label class="form-label fw-bold">Bill Title/Invoice No</label>
                                        <input type="text" name="title" class="form-control" placeholder="Enter title" required>
                                    </div>
                                    <div class="col-md-4 mb-3">
                                        <label class="form-label fw-bold">Amount</label>
                                        <input type="number" step="any" name="amount" class="form-control" placeholder="0.00" required>
                                    </div>
                                    <div class="col-12 mb-3">
                                        <label class="form-label fw-bold">Description</label>
                                        <textarea name="description" class="form-control" rows="2"></textarea>
                                    </div>
                                </div>
                                <button type="submit" class="btn btn-success"><i class="bx bx-check"></i> Save Bill Entry</button>
                            </form>
                        </div>

                        {{-- Tab 3: Make Payment --}}
                        <div class="tab-pane fade" id="pills-payment" role="tabpanel">
                            <form action="{{ route('admin.creditorBillPayments.store') }}" method="POST">
                                @csrf
                                <input type="hidden" value="{{ $creditor->id }}" name="creditor_id">
                                <div class="row">
                                    <div class="col-md-4 mb-3">
                                        <label class="form-label fw-bold">Created Date</label>
                                        <input type="date" name="transaction_date" value="{{Carbon\Carbon::now()->format('Y-m-d')}}" class="form-control" required>
                                    </div>

                                    <div class="mb-2 col-md-4">
                                        <label>Pay Amount</label>
                                        <input type="number" placeholder="0.00" name="amount" step="any" class="form-control" required>
                                    </div>
                                    <div class="mb-2 col-md-4">
                                        <label>Select Account</label>
                                        <select name="account" class="form-control" required>
                                            <option value="">Select Account</option>
                                            @foreach($accountMethods as $acc)
                                                <option value="{{ $acc->id }}">{{$acc->name}} - BDT {{priceFormat($acc->current_balance)}}</option>
                                            @endforeach
                                        </select>
                                    </div>

                                    <div class="mb-2 col-md-6">
                                        <label>Payment Method</label>
                                        <select name="payment" class="form-control">
                                            <option value="">Select Method</option>
                                            @foreach($paymentMethods as $method)
                                                <option value="{{$method->id}}">{{$method->name}}</option>
                                            @endforeach
                                        </select>
                                    </div>

                                    <div class="mb-2 col-md-12">
                                        <label>Description</label>
                                        <textarea name="description" placeholder="Write note here..." class="form-control" rows="2"></textarea>
                                    </div>
                                </div>

                                <div class="d-flex gap-2 justify-content-start mt-2 w-100">
                                    <button type="submit" class="btn btn-primary"><i class="bx bx-plus"></i> Add Payment</button>
                                </div>
                            </form>
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Edit Bill Modals -->
@foreach($ledgerEntries as $item)
    @if($item->type == 'bill')
    <div class="modal fade text-left" id="EditBill_{{$item->id}}" tabindex="-1" role="dialog">
      <div class="modal-dialog" role="document">
        <div class="modal-content">
        <form action="{{route('admin.creditor-bills.update',$item->id)}}" method="post">
              @csrf
              @method('PUT')
              <div class="modal-header">
                <h4 class="modal-title">Edit Bill</h4>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                  <span aria-hidden="true">&times; </span>
                </button>
              </div>
              <div class="modal-body">
                <div class="form-group">
                    <label>Date*</label>
                    <input type="date" class="form-control" name="transaction_date" value="{{$item->date?->format('Y-m-d')}}" required>
                </div>
                <div class="form-group">
                    <label>Title*</label>
                    <input type="text" class="form-control" name="title" value="{{ preg_replace('/^\S+ - /', '', $item->title) }}" required>
                </div>
                <div class="form-group">
                    <label>Amount*</label>
                    <input type="number" step="any" class="form-control" name="amount" value="{{$item->credit}}" required>
                </div>
                <div class="form-group">
                    <label>Description</label>
                    <textarea name="description" class="form-control">{{$item->note}}</textarea>
                </div>
              </div>
              <div class="modal-footer">
                <button type="button" class="btn grey btn-outline-secondary" data-dismiss="modal">Close </button>
                <button type="submit" class="btn btn-primary"><i class="bx bx-check"></i> Update Bill</button>
              </div>
           </form>
        </div>
      </div>
    </div>
    @endif
@endforeach

@push('js')
<script>
$(document).ready(function() {
    var activeTab = localStorage.getItem('activeCreditorTab');
    if(activeTab) {
        $('#pills-tab button').removeClass('active');
        $('#pills-tabContent .tab-pane').removeClass('show active');
        $('#pills-tab button[data-bs-target="' + activeTab + '"]').addClass('active');
        $(activeTab).addClass('show active');
    }
    $('#pills-tab button[data-bs-toggle="pill"]').on('shown.bs.tab', function (e) {
        localStorage.setItem('activeCreditorTab', $(e.target).data('bs-target'));
    });
    $('[data-open-tab]').on('click', function() {
        var target = $(this).data('open-tab');
        $('#pills-tab button').removeClass('active');
        $('#pills-tabContent .tab-pane').removeClass('show active');
        $('#pills-tab button[data-bs-target="' + target + '"]').addClass('active');
        $(target).addClass('show active');
        localStorage.setItem('activeCreditorTab', target);
    });
});
</script>
@endpush
@endsection
