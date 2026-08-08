@extends(adminTheme().'layouts.app') @section('title')
<title>{{websiteTitle('Creditor Bill Payments')}}</title>
@endsection @push('css')
<style type="text/css"></style>
@endpush @section('contents')

<div class="flex-grow-1">

<!-- Start -->
<div class="card mb-30">
    <div class="card-header d-flex justify-content-between align-items-center">
         <h3>Creditor Bill Payments</h3>
         <div class="dropdown">
            @can('ac_creditor_bill_payments.add')
             <a href="javascript:void(0)" class="btn-custom primary" data-toggle="modal" data-target="#AddPayment" style="padding:5px 15px;">
                 <i class="bx bx-plus"></i> Payment
             </a>
             @endcan
             <a href="{{route('admin.creditorBillPayments.index')}}" class="btn-custom yellow">
                 <i class="bx bx-rotate-left"></i>
             </a>
         </div>
    </div>
    <div class="card-body">
        @include(adminTheme().'alerts')

        <form action="{{route('admin.creditorBillPayments.index')}}" method="GET">
            <div class="row">
                <div class="col-md-3 mb-1">
                    <input type="text" name="title" value="{{request('title')}}" class="form-control form-control-sm" placeholder="Bill Title / Transaction ID">
                </div>
                <div class="col-md-2 mb-1">
                    <input type="text" name="creditor_name" value="{{request('creditor_name')}}" class="form-control form-control-sm" placeholder="Creditor Name">
                </div>
                <div class="col-md-2 mb-1">
                    <input type="text" name="creditor_code" value="{{request('creditor_code')}}" class="form-control form-control-sm" placeholder="Creditor Code">
                </div>
                <div class="col-md-2 mb-1">
                    <select name="account_id" class="form-control form-control-sm">
                        <option value="">All Accounts</option>
                        @foreach($filterAccounts as $account)
                        <option value="{{$account->id}}" {{request('account_id') == $account->id ? 'selected' : ''}}>{{$account->name}}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2 mb-1">
                    <div class="input-group">
                        <input type="date" name="startDate" value="{{request('startDate')}}" class="form-control form-control-sm">
                        <input type="date" name="endDate" value="{{request('endDate')}}" class="form-control form-control-sm">
                    </div>
                </div>
                <div class="col-md-1 mb-1 d-flex gap-2">
                    <button type="submit" class="btn btn-sm btn-success w-100 mr-2">Search</button>
                    <a href="{{route('admin.creditorBillPayments.index')}}" class="btn btn-sm btn-custom yellow">Reset</a>
                </div>
            </div>
        </form>

        <br>

        <div class="table-responsive">
            <table class="table table-bordered align-middle">
                <thead>
                    <tr>
                        <th width="50">SL</th>
                        <th>Date</th>
                        <th>Creditor</th>
                        <th>Code</th>
                        <th>Title / Ref</th>
                        <th>Details</th>
                        <th class="text-end">Amount</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($ledgerEntries as $i => $row)
                    <tr>
                        <td>{{$ledgerEntries->firstItem() + $i}}</td>
                        <td>{{$row->date?$row->date->format('d.m.Y'):'-'}}</td>
                        <td>{{$row->creditor?$row->creditor->name:'-'}}</td>
                        <td>{{$row->creditor?$row->creditor->code:'-'}}</td>
                        <td>{{$row->title}}</td>
                        <td><small class="text-muted">{{$row->description?:'-'}}</small></td>
                        <td class="text-end @if($row->credit > 0) text-success @else text-danger @endif">
                            @if($row->credit > 0)+ @endif
                            @if($row->debit > 0)- @endif
                            {{number_format($row->credit > 0 ? $row->credit : $row->debit, 2)}}
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="text-center text-muted"><em>No payment records found</em></td>
                    </tr>
                    @endforelse
                </tbody>
                <tfoot>
                    <tr>
                        <th colspan="6" class="text-right">Net Balance:</th>
                        <th class="text-end">{{number_format($totalBills - $totalPayments, 2)}}</th>
                    </tr>
                </tfoot>
            </table>
        </div>

        {{$ledgerEntries->links('pagination::bootstrap-4')}}
    </div>
</div>
</div>

<!-- Add Modal -->
 <div class="modal fade text-left" id="AddPayment" tabindex="-1" role="dialog">
   <div class="modal-dialog" role="document">
	 <div class="modal-content">
	    <form action="{{route('admin.creditorBillPayments.store')}}" method="post">
	   	  @csrf
    	   <div class="modal-header">
    		 <h4 class="modal-title">Add Creditor Bill Payment</h4>
    		 <button type="button" class="close" data-dismiss="modal" aria-label="Close">
    		   <span aria-hidden="true">&times; </span>
    		 </button>
    	   </div>
    	   <div class="modal-body">
    	   		<div class="form-group">
    			    <label for="name">Date* </label>
                    <input type="date" class="form-control {{$errors->has('transaction_date')?'error':''}}" name="transaction_date" value="{{Carbon\Carbon::now()->format('Y-m-d')}}" required="">
             	</div>
    	   		<div class="form-group">
    			    <label for="name">Creditor* </label>
                    <select class="form-control" name="creditor_id" required="">
                        <option value="">Select Creditor</option>
                        @foreach($creditors as $creditor)
                        <option value="{{$creditor->id}}">{{$creditor->name}}</option>
                        @endforeach
                    </select>
             	</div>
    	   		<div class="form-group">
    			    <label for="name">Account* </label>
                    <select class="form-control" name="account" required="">
                        <option value="">Select Account</option>
                        @foreach($accountMethods as $method)
                        <option value="{{$method->id}}">{{$method->name}} - BDT {{priceFormat($method->current_balance)}}</option>
                        @endforeach
                    </select>
             	</div>
    	   		<div class="form-group">
    			    <label for="name">Payment Method </label>
                    <select class="form-control" name="payment">
                        <option value="">Select Payment Method</option>
                        @foreach($paymentMethods as $method)
                        <option value="{{$method->id}}">{{$method->name}}</option>
                        @endforeach
                    </select>
             	</div>
    	   		<div class="form-group">
    			    <label for="name">Amount* </label>
                    <input type="number" step="any" class="form-control {{$errors->has('amount')?'error':''}}" name="amount" placeholder="Amount" required="">
             	</div>
    			<div class="form-group">
    				<label for="name">Description</label>
					<textarea name="description" class="form-control {{$errors->has('description')?'error':''}}" placeholder="Enter Description"></textarea>
             	</div>
    	   </div>
    	   <div class="modal-footer">
    		 <button type="button" class="btn grey btn-outline-secondary" data-dismiss="modal">Close </button>
    		 <button type="submit" class="btn btn-primary"><i class="bx bx-plus"></i> Submit</button>
    	   </div>
	   </form>
	 </div>
   </div>
 </div>

@endsection @push('js') @endpush
