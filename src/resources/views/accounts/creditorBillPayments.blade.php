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
            <div class="table-responsive">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th style="min-width: 100px;">Payment No</th>
                            <th style="min-width: 200px;">Creditor</th>
                            <th style="min-width: 150px;">Account</th>
                            <th style="min-width: 130px;">Amount</th>
                            <th style="min-width: 120px;">Date</th>
                            <th style="min-width: 200px;">Description</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($payments as $payment)
                        <tr>
                            <td>{{$payment->payment_no}}</td>
                            <td>{{$payment->creditor?$payment->creditor->name:'-'}}</td>
                            <td>{{$payment->account?$payment->account->name:'-'}}</td>
                            <td>BDT {{priceFormat($payment->amount)}}</td>
                            <td>{{$payment->created_at->format('d-m-Y')}}</td>
                            <td>{!!$payment->description!!}</td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="text-center text-muted"><em>No data found</em></td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
                {{$payments->links('pagination::bootstrap-4')}}
            </div>
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
                    <input type="date" class="form-control {{$errors->has('created_at')?'error':''}}" name="created_at" value="{{Carbon\Carbon::now()->format('Y-m-d')}}" required="">
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
