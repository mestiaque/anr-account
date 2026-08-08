@extends(adminTheme().'layouts.app') @section('title')
<title>{{websiteTitle('I.O.U List')}}</title>
@endsection @push('css')

<style type="text/css">

    .expenseTableView tr th{
        padding:5px;
    }

    .expenseTableView tr td{
        padding:5px;
    }
    .select2-container{
        width: calc(100% - 70px) !important;
    }
    .select2-container--default .select2-selection--single {
        border-radius: 0px;
    }


    .stats-card-box{
        background-color: #fafafa;
        border: 1px solid #e0e0e0;
        padding: 10px 15px 10px 80px;
        margin-bottom: 0
    }
    .stats-card-box .icon-box {
        display: flex;
        align-items: center;
        justify-content: center;
        width: 45px;
        height: 45px;
        font-size: 20px;
    }

    .stats-card-box .sub-title {
        color: #000;
    }
    .stats-card-box h3 {
        font-size: 20px;
    }

    @media (max-width: 1400px) {
        .stats-card-box h3 {
            font-size: 14px;
        }
    }

</style>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://unpkg.com/jsbarcode@3.11.5/dist/JsBarcode.all.min.js"></script>
@endpush
@section('contents')

<div class="flex-grow-1">


<!-- Start -->
<div class="card mb-30">
    <div class="card-header d-flex justify-content-between align-items-center">
         <h3>Completed I.O.U List</h3>
         <div class="dropdown">
             <a href="{{route('admin.ious.completed')}}" class="btn-custom yellow">
                 <i class="bx bx-rotate-left"></i>
             </a>
         </div>
    </div>
    <div class="card-body">
        @include(adminTheme().'alerts')

        <div class="row align-items-center mb-4">
            <!-- Search Section -->
            <div class="col-md-8">
                <form action="{{route('admin.ious.completed')}}" method="GET">
                    <div class="col-md-12 d-flex">
                        <input type="date" name="startDate" value="{{ request()->startDate }}" class="form-control mr-1" style="width: 22%">
                        <input type="date" name="endDate" value="{{ request()->endDate }}" class="form-control mr-1" style="width: 22%">
                        <select name="account_id" class="form-control mr-2" style="width: 24%">
                            <option value="">All Accounts</option>
                            @foreach($filterAccounts as $account)
                                <option value="{{$account->id}}" {{request()->account_id == $account->id ? 'selected' : ''}}>{{$account->name}}</option>
                            @endforeach
                        </select>
                        <div class="input-group shadow-sm" style="width: 32%">
                            <input type="text"
                                name="search"
                                value="{{request()->search}}"
                                class="form-control border-secondary"
                                placeholder="Search by name..."
                                style="border-radius: 5px 0 0 5px;">
                            <div class="input-group-append">
                                <button type="submit" class="btn btn-success px-4" style="border-radius: 0 5px 5px 0;">
                                    <i class="fa fa-search mr-1"></i> Search
                                </button>
                            </div>
                            @if(request()->anyFilled(['search', 'account_id', 'startDate', 'endDate']))
                                <a href="{{route('admin.ious.completed')}}" class="btn btn-outline-danger ml-2 rounded">Clear</a>
                            @endif
                        </div>
                    </div>
                </form>
            </div>

            <!-- Stats Card Section -->
            <div class="col-md-3 offset-1" >
                <div class="card border-0 shadow-sm bg-light p-0">
                    <div class="card-body p-3 d-flex align-items-center justify-content-between">
                        <div class="d-flex align-items-center">
                            <div class="bg-success text-white rounded-circle d-flex align-items-center justify-content-center mr-3" style="width: 45px; height: 45px;">
                                <i class="fa-solid fa-file-invoice-dollar"></i>
                            </div>
                            <div>
                                <p class="text-muted mb-0 small font-weight-bold">TOTAL AMOUNT</p>
                                <h4 class="mb-0 font-weight-bold text-dark">
                                    {{ number_format($totalAmount, 2) }}
                                </h4>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>


        <div class="table-responsive">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th style="min-width: 100px;width: 100px;">SL</th>
                        <th style="min-width: 100px;">I.O.U Date</th>
                        <th style="min-width: 120px;">Adjust Date</th>
                        <th style="min-width: 100px;">Company</th>
                        <th style="min-width: 100px;">Receiver</th>
                        <th style="min-width: 120px;">Employee</th>
                        <th style="min-width: 120px;">Employee ID</th>
                        <th style="min-width: 150px;">Purpose/Referance</th>
                        <th style="min-width: 100px;">Amount</th>
                        <th style="min-width: 100px;">Account</th>
                        <th style="min-width: 120px;">Branch/Factory</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($completedIou as $i=>$Iou)
                    @php
                        $isOlderThan2Days = $Iou->created_at->lt(\Carbon\Carbon::now()->subDays(7));
                    @endphp
                    <tr>
                        <td>
                            <span style="margin:0 5px;">{{ $i+1 }}</span>
                            @if($Iou->status=='completed')
                            <span style="color: #43d39e;font-size: 20px;line-height: 20px;position:relative;">
                                <i class="bx bx-check-circle"></i>
                            </span>
                            @else
                            <span style="color: #FF9800;font-size: 20px;line-height: 20px;position:relative;">
                                <i class="bx bx-analyse"></i>
                            </span>
                            @endif
                        </td>

                        <td>{{$Iou->created_at->format('d.m.Y')}}</td>
                        <td>{{$Iou->updated_at->format('d.m.Y')}}</td>
                        <td>{{ $Iou->company_name ?? '--' }}</td>
                        <td>{{ $Iou->receiver_name ?? '--' }}</td>
                        <td>{{$Iou->employee?$Iou->employee->name:''}}</td>
                        <td>{{$Iou->employee_id}}</td>
                        <td>
                            <span>{!! nl2br(e($Iou->description)) !!}</span>
                            @if($Iou->imageFile)
                            <span style="border: 1px solid #dadada;display: inline-block;padding: 0px 10px;border-radius: 5px;">
                                <a href="{{asset($Iou->imageFile->file_url)}}" target="_blank"><i class="bx bx-file"></i></a>
                                <a href="{{route('admin.mediesDelete',$Iou->imageFile->id)}}" class="mediaDelete" style="padding-left: 5px;color: #dc3545;display: inline-block;border-left: 1px solid #d2d2d2;"><i class="bx bx-trash"></i></a>
                            </span>
                            @endif
                        </td>
                        <td>{{priceFormat($Iou->amount)}}</td>
                        <td>{{$Iou->account?$Iou->account->name:''}}</td>
                        <td>{{$Iou->branch?$Iou->branch->name:''}}</td>

                    </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr>
                        <th colspan="8" class="text-right">Page Total:</th>
                        <th>{{ priceFormat($completedIou->sum('amount')) }}</th>
                        <th colspan="2"></th>
                    </tr>
                    <tr>
                        <th colspan="8" class="text-right">Filtered Total:</th>
                        <th>{{ number_format($totalAmount, 2) }}</th>
                        <th colspan="2"></th>
                    </tr>
                </tfoot>
            </table>
        </div>

        <div class="mt-2">
            {{ $completedIou->links('pagination') }}
        </div>


    </div>
</div>
</div>

@endsection

