@extends(adminTheme().'layouts.app') @section('title')
<title>{{$method->name}} Account Statement Report</title>
@endsection @push('css')
<style type="text/css"></style>
@endpush @section('contents')

<div class="flex-grow-1">


<!-- Start -->
<div class="card mb-30">
    <div class="card-header d-flex justify-content-between align-items-center">
         <h3>Account View</h3>
         <div class="dropdown">
            <a href="javascript:void(0)" class="btn-custom danger" style="padding:5px 15px;" id="ExportAction" ><i class="fa-solid fa-file-excel"></i> Export</a>
            <a href="javascript:void(0)" class="btn-custom primary" style="padding:5px 15px;" id="PrintAction" >
                <i class="fa fa-print"></i> Print
            </a>
             <a href="{{route('admin.accounts.index')}}" class="btn-custom primary"  style="padding:5px 15px;">
                  Account List
             </a>
             <a href="{{route('admin.accounts.show',$method->id)}}" class="btn-custom yellow">
                 <i class="bx bx-rotate-left"></i>
             </a>
         </div>
    </div>
    <div class="card-body">
        @include(adminTheme().'alerts')
        <div class="row">
            <div class="col-md-6">
                <form action="{{route('admin.accounts.show',$method->id)}}">
                    <div class="row">
                        <div class="col-md-12 mb-0">
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
            <div class="col-md-6">
                <div class="single-stats-card-box">
                     <div class="icon">
                         <i class="bx bxs-badge-dollar"></i>
                     </div>
                     <span class="sub-title">{{$method->name}} </span>
                     <h3>BDT {{priceFormat($availableBalance)}} <span class="badge"></h3>
                     <!--<h3>USD {{priceFormat($method->usd_amount)}} <span class="badge"></h3>-->
                 </div>
            </div>
        </div>


        <br>
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
                <img src="{{asset(general()->logo())}}" alt="logo" style="max-height: 80px;">
                <h2>{{general()->title}}</h2>
                <p>
                    {!!general()->address_one!!}
                    <br>
                    <b>Phone:</b> {{general()->mobile}}
                    <b>Email:</b> {{general()->email}}
                    <br>
                    <b>Date:</b>
                    {{ date('d M, Y') }}
                </p>
                <span style="display: inline-block;padding: 1px 25px;border: 1px solid #e3cfcf;border-radius: 5px;background: #fbfbfb;">{{$method->name}} Statement</span>
            </div>
                <div class="table-responsive">
                    <table  class="table tableReport" >
                        <thead>
                            <tr>
                                <th style="width: 120px;min-width: 120px;">Date</th>
                                <th style="width: 130px;min-width: 130px;">Method</th>
                                <th style="min-width: 200px;">Concern Person</th>
                                <th style="width: 130px;min-width: 130px;">Type</th>
                                <th style="width: 130px;min-width: 130px;">Debit</th>
                                <th style="width: 130px;min-width: 130px;">Credit</th>
                                <th style="width: 150px;min-width: 150px;">Balance</th>
                            </tr>
                        </thead>
                        <tbody>

                            @forelse($transections as $tran)
                                <tr>
                                    <td>{{ $tran->transaction_date->format('d-m-Y') }}</td>
                                    <td>{{ $tran->payment_method_name ?? '' }}</td>
                                    <td>
                                        @if($tran->source_type == 'deposit')
                                            <b>TNX ID:</b> {{ $tran->transaction_no }} - <b>Account:</b> {{$tran->account?$tran->account->name:'N/A'}} {{ $tran->source?->description?'- '.$tran->source->description:'' }}
                                        @elseif($tran->source_type == 'expense')
                                           @if($tran->expense)
                                            <b>Company:</b> {{ $tran->expense->company_name}} - <b>Receiver:</b> {{ $tran->expense->receiver_name}} {{ $tran->expense->description?'- '.$tran->expense->description:'' }}
                                            @else
                                            <span>N/A</span>
                                            @endif
                                        @elseif($tran->source_type == 'iou')
                                            @if($tran->iou)
                                            <b>Company:</b> {{ $tran->iou->company_name}} - <b>Receiver:</b> {{ $tran->iou->receiver_name}} {{ $tran->iou->description?'- '.$tran->iou->description:'' }}
                                            @else
                                            <span>N/A</span>
                                            @endif
                                        @elseif($tran->source_type == 'withdrawal')
                                            <b>TNX ID:</b> {{ $tran->transaction_no }} - <b>Account:</b> {{$tran->account?$tran->account->name:'N/A'}} {{ $tran->source?->description?'- '.$tran->source->description:'' }}
                                        @elseif($tran->source_type == 'creditor_bill_payment')

                                            @if($tran->creditorBillPayment)
                                               <b>Payment:</b> {{$tran->creditorBillPayment->payment_no}}
                                               <b>Creditor:</b> {{$tran->creditorBillPayment->creditor->name ?? 'N/A'}}
                                            @else
                                            <span>N/A</span>
                                            @endif
                                        @else
                                            {{ $tran->transaction_no }}

                                        @endif

                                    </td>
                                    <td>

                                            @if($tran->source_type=='deposit')
                                                Deposit
                                            @elseif($tran->source_type=='creditor_bill_payment')
                                                Creditor Bill
                                            @elseif($tran->source_type=='transfer')
                                                Transfer Balance
                                            @elseif($tran->source_type=='expense')
                                                 Expense
                                            @elseif($tran->source_type=='withdrawal')
                                                Withdrawal
                                            @elseif($tran->source_type=='iou')
                                                I.O.U
                                            @else
                                                Unknown
                                            @endif

                                    </td>
                                    <td>
                                        @if($tran->direction === 'debit')
                                            {{ priceFormat($tran->amount) }}
                                        @endif
                                    </td>
                                    <td>
                                        @if($tran->direction === 'credit')
                                            {{ priceFormat($tran->amount) }}
                                        @endif
                                    </td>
                                    <td>{{ priceFormat($tran->running_balance) }}</td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="7" style="text-align:center;">No Record</td>
                                </tr>
                                @endforelse
                        </tbody>
                        <tfoot>
                            <tr>
                                <td colspan="5"></td>
                                <td>Available</td>
                                <td>{{ priceFormat($availableBalance ?? 0) }}</td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
        </div>
    </div>
</div>
</div>




@endsection @push('js')
<script>
    $(document).ready(function () {

        $('#example').DataTable( {
	        dom: 'Bfrtip',
	        buttons: [
	            'excel', 'pdf', 'print'
	        ]
	    } );

    });
</script>

@endpush
