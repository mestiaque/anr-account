@extends(adminTheme().'layouts.app') @section('title')
<title>{{websiteTitle('Withdrawal List')}}</title>
@endsection @push('css')
<style type="text/css"></style>
@endpush @section('contents')

<div class="flex-grow-1">


<!-- Start -->
<div class="card mb-30">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h3>Withdrawal List</h3>
        <div class="dropdown">
            @can('ac_withdrawals.add')
            <a href="javascript:void(0)" class="btn-custom primary" data-toggle="modal" data-target="#AddDeposit" style="padding:5px 15px;">
                 <i class="bx bx-plus"></i> Withdrawal
             </a>
             @endcan
             <a href="{{route('admin.withdrawals.index')}}" class="btn-custom yellow">
                 <i class="bx bx-rotate-left"></i>
             </a>
        </div>
    </div>
    <div class="card-body">
        @include(adminTheme().'alerts')

        <form action="{{route('admin.withdrawals.index')}}">
            <div class="row">
                <div class="col-md-6 mb-0">
                    <div class="input-group">
                        <input type="date" name="startDate" value="{{request()->startDate?Carbon\Carbon::parse(request()->startDate)->format('Y-m-d') :''}}" class="form-control {{$errors->has('startDate')?'error':''}}" />
                        <input type="date" value="{{request()->endDate?Carbon\Carbon::parse(request()->endDate)->format('Y-m-d') :''}}" name="endDate" class="form-control {{$errors->has('endDate')?'error':''}}" />
                    </div>
                </div>
                <div class="col-md-3 mb-0">
                    <div class="form-group">
                        <select class="form-control" name="account" >
                            <option value="">Select Account</option>
                            @foreach($accountMethods as $method)
                            <option value="{{$method->id}}" {{request()->account==$method->id?'selected':''}}>{{$method->name}}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="col-md-3 mb-0">
                    <div class="form-group">
                        <select class="form-control" name="method" >
                            <option value="">Select Method</option>
                            @foreach($paymentMethods as $method)
                            <option value="{{$method->id}}" {{request()->method==$method->id?'selected':''}}>{{$method->name}}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="col-md-12 mb-0">
                    <div class="input-group">
                        <input type="text" name="search" value="{{request()->search?request()->search:''}}" placeholder="Search Transection ID " class="form-control {{$errors->has('search')?'error':''}}" />
                        <button type="submit" class="btn btn-success btn-sm rounded-0">Search</button>
                    </div>
                </div>
            </div>
        </form>
        <hr>

        <form action="{{route('admin.withdrawals.index')}}">
            <div class="row">
                <div class="col-md-4">
                    @can('ac_withdrawals.add')
                    <div class="input-group mb-1">
                        <select class="form-control form-control-sm rounded-0" name="action" required="">
                            <option value="">Select Action</option>
                            <option value="5">Delete</option>
                        </select>
                        <button class="btn btn-sm btn-primary rounded-0" onclick="return confirm('Are You Want To Action?')">Action</button>
                    </div>
                    @endcan
                </div>
                <div class="col-md-8">
                </div>
            </div>
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th style="min-width: 100px;width: 100px;padding-right:0;">
                                @can('ac_withdrawals.delete')
                                <div class="checkbox mr-3">
                                 <input class="inp-cbx" id="checkall" type="checkbox" style="display: none;" />
                                 <label class="cbx" for="checkall">
                                     <span>
                                         <svg width="12px" height="10px" viewbox="0 0 12 10">
                                             <polyline points="1.5 6 4.5 9 10.5 1"></polyline>
                                         </svg>
                                     </span>
                                     All <span class="checkCounter"></span>
                                    </label>
                                </div>
                                @else All
                                @endcan
                            </th>
                            <th style="min-width: 200px;">Withdrawal</th>
                            <th style="min-width: 300px;">Balance - Description</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($transections as $i=>$transection)
                        <tr>
                            <td>
                                <div class="checkbox">
                                     <input class="inp-cbx" id="cbx_{{$transection->id}}" type="checkbox" name="checkid[]" value="{{$transection->id}}" style="display: none;" />
                                     <label class="cbx" for="cbx_{{$transection->id}}">
                                        @can('ac_withdrawals.delete')
                                         <span>
                                             <svg width="12px" height="10px" viewbox="0 0 12 10">
                                                 <polyline points="1.5 6 4.5 9 10.5 1"></polyline>
                                             </svg>
                                         </span>
                                        @endcan
                                     </label>
                                 </div>
                                <span style="margin:0 5px;">{{$transections->currentpage()==1?$i+1:$i+($transections->perpage()*($transections->currentpage() - 1))+1}}</span>
                                @if($transection->status=='success')
                                <span style="color: #43d39e;font-size: 20px;line-height: 20px;position:absolute;">
                                    <i class="bx bx-check-circle"></i>
                                </span>
                                @else
                                <span style="color: #FF9800;font-size: 20px;line-height: 20px;position:absolute;">
                                    <i class="bx bx-analyse"></i>
                                </span>
                                @endif
                                @can('ac_withdrawals.edit')
                                <br><br>
                                <a href="javascript:void(0)" data-toggle="modal" data-target="#EditDeposit_{{$transection->id}}" class="btn-custom success">
                                    <i class="bx bx-edit"></i>
                                </a>
                                @endcan
                            </td>
                            <td>
                                <b>TNX Id:</b> {{$transection->withdrawal_no}} @if($transection->imageFile) <a href="{{asset($transection->imageFile->file_url)}}" target="_blank"><i class="bx bx-file"></i></a> @endif <br>
                                <b>Accounts:</b> {{$transection->account?$transection->account->name:''}}
                                    @if($transection->account)
                                            <a target="_blank" href="{{ route('admin.accounts.show', $transection->account->id) }}">
                                                &nbsp;<i class="fas fa-external-link-alt"></i>
                                            </a>
                                        @endif
                                    <br>
                                <b>Payment Method:</b> {{$transection->paymentMethod?$transection->paymentMethod->name:''}}<br>
                                @if($transection->bank_name)
                                <b>Bank Name:</b> {{$transection->bank_name}} <br>
                                @endif
                                <b>Date:</b> {{$transection->created_at->format('d-m-Y')}}<br>
                            </td>
                            <td>
                                @php $afterBalance = $transection->transaction->balance_after ?? 0; @endphp
                                <b>Previus:</b> <span style="color: #009688;"> {{numberFormat($afterBalance + $transection->amount,2)}} </span>
                                <br> <b>Minus Balance:</b> <span style="color: #FF9800;font-weight: bold;"> {{numberFormat($transection->amount,2)}} </span>
                                <br> <b>After Balance:</b> <span>{{numberFormat($afterBalance,2)}} </span>
                                <br>
                                <span>{!!$transection->description!!}</span>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
                {{$transections->links('pagination')}}
            </div>
        </form>


    </div>
</div>
</div>

<!-- Add Modal -->
 <div class="modal fade text-left" id="AddDeposit" tabindex="-1" role="dialog">
   <div class="modal-dialog" role="document">
	 <div class="modal-content">
	 <form action="{{route('admin.withdrawals.store')}}" method="post" enctype="multipart/form-data">
	   	  @csrf
    	   <div class="modal-header">
    		 <h4 class="modal-title">Add Withdrawal</h4>
    		 <button type="button" class="close" data-dismiss="modal" aria-label="Close">
    		   <span aria-hidden="true">&times; </span>
    		 </button>
    	   </div>
    	   <div class="modal-body">
    	       <div class="row">
    	           <div class="col-md-6 form-group">
        			    <label for="name">Date* </label>
                        <input type="date" class="form-control {{$errors->has('created_at')?'error':''}}" name="created_at" value="{{Carbon\Carbon::now()->format('Y-m-d')}}"  required="">
        				@if ($errors->has('created_at'))
        				<p style="color: red; margin: 0; font-size: 10px;">{{ $errors->first('created_at') }}</p>
        				@endif
                 	</div>
                 	<div class="col-md-6 form-group">
        			    <label for="name">Account*</label>
                        <select class="form-control" name="account" required="">
                            <option value="">Select Account</option>
                            @foreach($accountMethods as $method)
                            <option value="{{$method->id}}">{{$method->name}}</option>
                            @endforeach
                        </select>
        				@if ($errors->has('account'))
        				<p style="color: red; margin: 0; font-size: 10px;">{{ $errors->first('account') }}</p>
        				@endif
                 	</div>

    	       </div>
    	       <div class="row">
    	           <div class="col-md-6 form-group">
        			    <label for="name">Payment method* </label>
                        <select class="form-control" name="payment" required="">
                            <option value="">Select Method</option>
                            @foreach($paymentMethods as $method)
                            <option value="{{$method->id}}">{{$method->name}}</option>
                            @endforeach
                        </select>
        				@if ($errors->has('payment'))
        				<p style="color: red; margin: 0; font-size: 10px;">{{ $errors->first('payment') }}</p>
        				@endif
                 	</div>
    	           <div class="col-md-6 form-group">
        			    <label for="name">Amount* </label>
                        <input type="number" step="any" class="form-control {{$errors->has('amount')?'error':''}}" name="amount" placeholder="Amount"  required="">
        				@if ($errors->has('amount'))
        				<p style="color: red; margin: 0; font-size: 10px;">{{ $errors->first('amount') }}</p>
        				@endif
                 	</div>
                    <div class="col-md-12 form-group">
                        <label for="name">Bank Name </label>
                        <input type="text" class="form-control {{$errors->has('bank_name')?'error':''}}" placeholder="Enter bank name" name="bank_name">
                        @if ($errors->has('bank_name'))
        				<p style="color: red; margin: 0; font-size: 10px;">{{ $errors->first('bank_name') }}</p>
        				@endif
                    </div>
             	</div>
    	       <div class="form-group">
    				<label for="name">Attachtment</label>
					<input type="file" class="form-control {{$errors->has('attachment')?'error':''}}" name="attachment" accept="image/*" style="padding: 3px;">
					@if ($errors->has('attachment'))
					<p style="color: red; margin: 0; font-size: 10px;">{{ $errors->first('attachment') }}</p>
					@endif
             	</div>
    			<div class="form-group">
    				<label for="name">Description</label>
					<textarea name="description" rows="5" class="form-control {{$errors->has('description')?'error':''}}" placeholder="Enter Description"></textarea>
					@if ($errors->has('description'))
					<p style="color: red; margin: 0; font-size: 10px;">{{ $errors->first('description') }}</p>
					@endif
             	</div>
    	   </div>
    	   <div class="modal-footer">
    		 <button type="button" class="btn grey btn-outline-secondary" data-dismiss="modal">Close </button>
    		 <button type="submit" class="btn btn-primary"><i class="bx bx-plus"></i> Add Withdrawal</button>
    	   </div>
	   </form>
	 </div>
   </div>
 </div>

 <!--Edit Modal -->
@foreach($transections as $i=>$dpm)

 <div class="modal fade text-left" id="EditDeposit_{{$dpm->id}}" tabindex="-1" role="dialog">
   <div class="modal-dialog" role="document">
	 <div class="modal-content">
	 <form action="{{route('admin.withdrawals.update',$dpm->id)}}" method="post" enctype="multipart/form-data">
	   	  @method('PUT')
	   	  @csrf
    	   <div class="modal-header">
    		 <h4 class="modal-title">Withdrawal #{{$dpm->withdrawal_no}}</h4>
    		 <button type="button" class="close" data-dismiss="modal" aria-label="Close">
    		   <span aria-hidden="true">&times; </span>
    		 </button>
    	   </div>
    	   <div class="modal-body">
    	       <div class="row">
    	           <div class="col-md-6 form-group">
        			    <label for="name">Date* </label>
                        <input type="date" class="form-control {{$errors->has('created_at')?'error':''}}" name="created_at" value="{{$dpm->created_at->format('Y-m-d')}}"  required="">
        				@if ($errors->has('created_at'))
        				<p style="color: red; margin: 0; font-size: 10px;">{{ $errors->first('created_at') }}</p>
        				@endif
                 	</div>
                 	<div class="col-md-6 form-group">
        			    <label for="name">Account*</label>
                        <input value="{{$dpm->account?$dpm->account->name:''}}" class="form-control" disabled="" />
        				@if ($errors->has('account'))
        				<p style="color: red; margin: 0; font-size: 10px;">{{ $errors->first('account') }}</p>
        				@endif
                 	</div>

    	       </div>
    	       <div class="row">
    	           <div class="col-md-6 form-group">
        			    <label for="name">Payment method* </label>
                        <select class="form-control" name="payment" required="">
                            <option value="">Select Method</option>
                            @foreach($paymentMethods as $method)
                            <option value="{{$method->id}}" {{$dpm->payment_method_id==$method->id?'selected':''}}>{{$method->name}}</option>
                            @endforeach
                        </select>
        				@if ($errors->has('payment'))
        				<p style="color: red; margin: 0; font-size: 10px;">{{ $errors->first('payment') }}</p>
        				@endif
                 	</div>
    	           <div class="col-md-6 form-group">
        			    <label for="name">Amount* </label>
                        <input type="number" step="any"  value="{{$dpm->amount}}" disabled="" class="form-control {{$errors->has('amount')?'error':''}}" placeholder="Amount"  >
        				@if ($errors->has('amount'))
        				<p style="color: red; margin: 0; font-size: 10px;">{{ $errors->first('amount') }}</p>
        				@endif
                 	</div>
                    <div class="col-md-12 form-group">
                        <label for="name">Bank Name </label>
                        <input type="text" value="{{$dpm->bank_name}}" class="form-control {{$errors->has('bank_name')?'error':''}}" placeholder="Enter bank name" name="bank_name">
                        @if ($errors->has('bank_name'))
        				<p style="color: red; margin: 0; font-size: 10px;">{{ $errors->first('bank_name') }}</p>
        				@endif
                    </div>
             	</div>
    	       <div class="form-group">
    				<label for="name">Attachtment</label>
					<input type="file" class="form-control {{$errors->has('attachment')?'error':''}}" name="attachment" accept="image/*" style="padding: 3px;">
					@if ($errors->has('attachment'))
					<p style="color: red; margin: 0; font-size: 10px;">{{ $errors->first('attachment') }}</p>
					@endif
             	</div>
    			<div class="form-group">
    				<label for="name">Description</label>
					<textarea name="description" rows="5" class="form-control {{$errors->has('description')?'error':''}}" placeholder="Enter Description">{!!$dpm->description!!}</textarea>
					@if ($errors->has('description'))
					<p style="color: red; margin: 0; font-size: 10px;">{{ $errors->first('description') }}</p>
					@endif
             	</div>
    	   </div>
    	   <div class="modal-footer">
    		 <button type="button" class="btn grey btn-outline-secondary" data-dismiss="modal">Close </button>
    		 <button type="submit" class="btn btn-primary"><i class="bx bx-plus"></i> Update Withdrawal</button>
    	   </div>
	   </form>
	 </div>
   </div>
 </div>

@endforeach




@endsection @push('js') @endpush
