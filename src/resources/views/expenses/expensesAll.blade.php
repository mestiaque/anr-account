@extends(adminTheme().'layouts.app') @section('title')
<title>{{websiteTitle('Expenses List')}}</title>
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
    #creditorSelect + .select2-container {
        width: 100% !important;
        display: block !important;
    }


        .stats-card-box{
            background-color: #fafafa;
            border: 1px solid #e0e0e0;
            padding: 10px 15px 10px 80px;
            margin-bottom: 15px;
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
         <h3>Expenses List</h3>
         <div class="dropdown">
            @can('ac_expenses.add')
             <a href="javascript:void(0)" class="btn-custom primary" data-toggle="modal" data-target="#AddExpense" style="padding:5px 15px;">
                 <i class="bx bx-plus"></i> Expense
             </a>
             @endcan

             @can('ac_expenses.add')
             <a href="javascript:void(0)" class="btn-custom success" data-toggle="modal" data-target="#AddCreditorPayment" style="padding:5px 15px;">
                 <i class="bx bx-plus"></i> Creditor Payment
             </a>
             @endcan

             <a href="{{route('admin.expenses.index')}}" class="btn-custom yellow">
                 <i class="bx bx-rotate-left"></i>
             </a>
         </div>
    </div>
    <div class="card-body">
        @include(adminTheme().'alerts')

        <div class="row g-3 mb-4">
            <!-- Row 1: Search / Filter Form -->
            <div class="col-12">
                <div class="card border-0 p-0 mb-2">
                    <div class="card-body p-0">
                        <form action="{{ route('admin.expenses.index') }}" method="GET" class="row g-2 align-items-center">
                            <!-- Search by Serial -->
                            <div class="col-lg-2 col-md-6">
                                <div class="input-group">
                                    <span class="input-group-text bg-white"><i class="fa fa-hashtag text-muted"></i></span>
                                    <input type="text" class="form-control" name="search" value="{{request()->search}}" placeholder="Serial No">
                                </div>
                            </div>

                            <!-- Date Range -->
                            <div class="col-lg-3 col-md-6">
                                <div class="input-group">
                                    <span class="input-group-text bg-white"><i class="fa fa-calendar-alt text-muted"></i></span>
                                    <input type="date" name="startDate" value="{{request()->startDate ?? $from}}" class="form-control">
                                    <input type="date" name="endDate" value="{{request()->endDate ?? $to}}" class="form-control">
                                </div>
                            </div>

                            <!-- Expense Type -->
                            <div class="col-lg-2 col-md-6">
                                <select class="form-control select2" name="expense_type">
                                    <option value="">All Expense Types</option>
                                    @foreach($expenseTypes as $type)
                                        <option value="{{$type->id}}" {{request()->expense_type == $type->id ? 'selected' : ''}}>{{$type->name}}</option>
                                    @endforeach
                                </select>
                            </div>

                            <!-- Account -->
                            <div class="col-lg-2 col-md-6">
                                <select class="form-control select2" name="account_id">
                                    <option value="">All Accounts</option>
                                    @foreach($filterAccounts as $account)
                                        <option value="{{$account->id}}" {{request()->account_id == $account->id ? 'selected' : ''}}>{{$account->name}}</option>
                                    @endforeach
                                </select>
                            </div>

                            <!-- Submit Button -->
                            <div class="col-lg-3 col-md-6">
                                <div class="d-flex gap-1">
                                    <button type="submit" class="btn btn-primary w-100">
                                        <i class="fa fa-search me-1"></i> Search
                                    </button>
                                    @if(request()->anyFilled(['search', 'startDate', 'endDate', 'expense_type', 'account_id']))
                                        <a href="{{ route('admin.expenses.index') }}" class="btn btn-light border">Reset</a>
                                    @endif
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Row 2: Summary Cards -->
            <div class="col-12">
                <div class="row g-3">
                    <!-- Today -->
                    <div class="col-lg-3 col-md-6">
                        <div class="card h-100 border-0 shadow-sm">
                            <div class="card-body d-flex align-items-center">
                                <div class="rounded-circle bg-primary-soft text-primary d-flex align-items-center justify-content-center mr-3" style="width: 45px; height: 45px; background: #e7f1ff;">
                                    <i class="fa-solid fa-calendar-day"></i>
                                </div>
                                <div>
                                    <div class="text-muted small font-weight-bold">TODAY</div>
                                    <div class="h5 mb-0 font-weight-bold">{{ $report['today_expenses'] }}</div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Month -->
                    <div class="col-lg-3 col-md-6">
                        <div class="card h-100 border-0 shadow-sm">
                            <div class="card-body d-flex align-items-center">
                                <div class="rounded-circle bg-success-soft text-success d-flex align-items-center justify-content-center mr-3" style="width: 45px; height: 45px; background: #e8f5e9;">
                                    <i class="fa-solid fa-calendar"></i>
                                </div>
                                <div>
                                    <div class="text-muted small font-weight-bold">THIS MONTH</div>
                                    <div class="h5 mb-0 font-weight-bold">{{ $report['monthly_expenses'] }}</div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Date Filter Result -->
                    <div class="col-lg-3 col-md-6">
                        <div class="card h-100 border-0 shadow-sm">
                            <div class="card-body d-flex align-items-center">
                                <div class="rounded-circle bg-warning-soft text-warning d-flex align-items-center justify-content-center mr-3" style="width: 45px; height: 45px; background: #fffde7;">
                                    <i class="fa-solid fa-calendar-check"></i>
                                </div>
                                <div>
                                    <div class="text-muted small font-weight-bold">
                                        {{ \Carbon\Carbon::parse($from)->format('d.m.Y') }} - {{ \Carbon\Carbon::parse($to)->format('d.m.Y') }}
                                    </div>
                                    <div class="h5 mb-0 font-weight-bold">{{ $report['filtered_expenses'] ?? 0 }}</div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Filtered Total (New) -->
                    <div class="col-lg-3 col-md-6">
                        <div class="card h-100 border-0 shadow-sm bg-">
                            <div class="card-body d-flex align-items-center">
                                <div class="rounded-circle bg-light text-info d-flex align-items-center justify-content-center mr-3" style="width: 45px; height: 45px;">
                                    <i class="fa-solid fa-filter"></i>
                                </div>
                                <div>
                                    <div class="text-muted small font-weight-bold">SEARCH TOTAL</div>
                                    <div class="h5 mb-0 font-weight-bold text-dark">{{ $report['filtered_total'] ?? 0 }}</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>





        <form action="{{route('admin.expenses.index')}}">
            <div class="row">
                <div class="col-md-4 mb-3">
                    @can('ac_expenses.delete')
                    <div class="input-group mb-1">
                        <select class="form-control form-control-sm rounded-0" name="action" required="">
                            <option value="">Select Action</option>

                            <option value="5">Delete</option>
                        </select>
                        <button class="btn btn-sm btn-primary rounded-0" onclick="return confirm('Are You Want To Action?')">Action</button>
                    </div>
                    @endcan
                </div>
                <div class="col-md-4"></div>
                <div class="col-md-4">

                </div>
            </div>
            <div class="table-responsive">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th style="min-width: 100px;width: 100px;padding-right:0;">
                                @can('ac_expenses.delete')
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
                            <th style="min-width: 100px;">Serial No</th>
                            <th style="min-width: 100px;">Date</th>
                            <th style="min-width: 150px;">Company</th>
                            <th style="min-width: 150px;">Receiver</th>
                            <th style="min-width: 160px;">Type Of Expense</th>
                            <th style="min-width: 100px;">Amount</th>
                            <th style="min-width: 150px;">Description</th>
                            <th style="min-width: 100px;">Accounts</th>
                            <th style="min-width: 120px;">Branch/Factory</th>
                            <th style="min-width: 100px;width:100px;">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($expenses as $i=>$expense)
                        <tr>
                            <td style="position:relative;">
                                @can('ac_expenses.delete')
                                <div class="checkbox">
                                     <input class="inp-cbx" id="cbx_{{$expense->id}}" type="checkbox" name="checkid[]" value="{{$expense->id}}" style="display: none;" />
                                     <label class="cbx" for="cbx_{{$expense->id}}">
                                         <span>
                                             <svg width="12px" height="10px" viewbox="0 0 12 10">
                                                 <polyline points="1.5 6 4.5 9 10.5 1"></polyline>
                                             </svg>
                                         </span>
                                     </label>
                                 </div>
                                 @endcan
                                <span style="margin:0 5px;">{{$expenses->currentpage()==1?$i+1:$i+($expenses->perpage()*($expenses->currentpage() - 1))+1}}</span>

                                    @if(!is_null($expense->audit_at))
                                    <span style="color: #43d39e;font-size: 20px;line-height: 20px;position:absolute;" data-bs-toggle="tooltip" title="Audit At {{ $expense->audit_at?->format('d.m.Y h:i A') }}">
                                        <i class="bx bx-check-circle"></i>
                                    </span>
                                    @else
                                    <span style="color: #FF9800;font-size: 20px;line-height: 20px;position:absolute;" data-bs-toggle="tooltip" title="Pending for Audit">
                                        <i class="bx bx-analyse"></i>
                                    </span>
                                    @endif

                            </td>
                            <td>{{ str_pad($expense->id, 10, '0', STR_PAD_LEFT) }}</td>
                            <td>{{$expense->created_at->format('d.m.Y')}}</td>
                            <td>{{ $expense->company_name ?? '--' }}</td>
                            <td>{{ $expense->receiver_name ?? '--' }}</td>
                            <td>{{$expense->category?$expense?->category->name:''}}</td>
                            <td>{{priceFormat($expense->amount)}}</td>
                            <td>
                                <span>{!! nl2br(e($expense->description)) !!}</span>
                                @if($expense->imageFile)
                                <span style="border: 1px solid #dadada;display: inline-block;padding: 0px 10px;border-radius: 5px;">
                                    <a href="{{asset($expense->imageFile->file_url)}}" target="_blank"><i class="bx bx-file"></i></a>
                                    <a href="{{route('admin.mediesDelete',$expense->imageFile->id)}}" class="mediaDelete" style="padding-left: 5px;color: #dc3545;display: inline-block;border-left: 1px solid #d2d2d2;"><i class="bx bx-trash"></i></a>
                                </span>
                                @endif
                            </td>
                            <td>{{$expense->account?$expense->account->name:''}}</td>
                            <td>{{$expense->branch?$expense->branch->name:''}}</td>
                            <td class="text-center">
                                 @if(auth()->user()->hasPermission('ac_expenses.edit') || auth()->user()->hasPermission('ac_expenses.view'))
                                    @can('ac_expenses.edit')
                                        @if($expense->category_id !== 0)
                                        <a href="javascript:void(0)" data-toggle="modal" data-target="#EditExpense_{{$expense->id}}" class="btn-custom success">
                                            <i class="bx bx-edit"></i>
                                        </a>
                                        @endif
                                    @endcan
                                    @can('ac_expenses.view')
                                    <a href="javascript:void(0)" data-toggle="modal" data-target="#ViewExpense_{{$expense->id}}" class="btn-custom yellow">
                                        <i class="bx bx-show"></i>
                                    </a>
                                    @endcan
                                @else -- @endif
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="11" class="text-center text-muted"><em>No data found</em></td>
                        </tr>
                        @endforelse
                    </tbody>
                    <tfoot>
                        <tr>
                            <th colspan="6" class="text-right">Page Total:</th>
                            <th>{{ priceFormat($expenses->sum('amount')) }}</th>
                            <th colspan="4"></th>
                        </tr>
                        <tr>
                            <th colspan="6" class="text-right">Filtered Total:</th>
                            <th>{{ $report['filtered_total'] }}</th>
                            <th colspan="4"></th>
                        </tr>
                    </tfoot>
                </table>
                {{$expenses->links('pagination::bootstrap-4')}}
            </div>
        </form>


    </div>
</div>
</div>
{{-- @dd($lastAudit->audit_at) --}}
@isset(json_decode(Auth::user()->permission->permission, true)['expenses']['add'])
<!-- Add Modal -->
 <div class="modal fade text-left" id="AddExpense" tabindex="-1" role="dialog">
   <div class="modal-dialog" role="document">
	 <div class="modal-content">
	 <form action="{{route('admin.expenses.store')}}" method="post" enctype="multipart/form-data">
	   	  @csrf
    	   <div class="modal-header">
    		 <h4 class="modal-title">Add Expense</h4>
    		 <button type="button" class="close" data-dismiss="modal" aria-label="Close">
    		   <span aria-hidden="true">&times; </span>
    		 </button>
    	   </div>
    	   <div class="modal-body">
    	       <div class="row">
    	           <div class="col-md-6 form-group">
        			    <label for="name">Date* </label>
                        <input type="date" class="form-control {{$errors->has('created_at')?'error':''}}" name="created_at" value="{{Carbon\Carbon::now()->format('Y-m-d')}}" min="{{ $lastAudit && $lastAudit->audit_at
    ? \Carbon\Carbon::parse($lastAudit->audit_at)->addDay()->format('Y-m-d')
    : now()->format('Y-m-d') }}"  required="">
        				@if ($errors->has('created_at'))
        				<p style="color: red; margin: 0; font-size: 10px;">{{ $errors->first('created_at') }}</p>
        				@endif
                 	</div>
                 	<div class="col-md-6 form-group">
        			    <label for="name">Expense Type*</label>
                        <select class="form-control" name="expense_type">
                            <option value="">Select Type</option>
                            @foreach($expenseTypes as $type)
                            <option value="{{$type->id}}">{{$type->name}}</option>
                            @endforeach
                        </select>
        				@if ($errors->has('expense_type'))
        				<p style="color: red; margin: 0; font-size: 10px;">{{ $errors->first('expense_type') }}</p>
        				@endif
                 	</div>

    	       </div>
    	       <div class="row">
    	           <div class="col-md-6 form-group">
        			    <label for="name">Payment Method *</label>
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
                 	<div class="col-md-6 form-group">
        			    <label for="name">Account Method *</label>
                        <select class="form-control" name="account" required="">
                            <option value="">Select Account</option>
                            @foreach($accountMethods as $method)
                            <option value="{{$method->id}}">{{$method->name}} - BDT {{priceFormat($method->current_balance)}}</option>
                            @endforeach
                        </select>
        				@if ($errors->has('payment'))
        				<p style="color: red; margin: 0; font-size: 10px;">{{ $errors->first('payment') }}</p>
        				@endif
                 	</div>
                    <div class="col-md-6 form-group">
                        <label>Branch/Factory *</label>
                        <select class="form-control" name="branch_id" required="">
                            <option value="">Select Branch/Factory</option>
                            @foreach($branches as $branch)
                            <option value="{{$branch->id}}">{{$branch->name}}</option>
                            @endforeach
                        </select>
                        @if ($errors->has('branch_id'))
                        <p style="color: red; margin: 0; font-size: 10px;">{{ $errors->first('branch_id') }}</p>
                        @endif
                    </div>
                 	<div class="col-md-6 form-group">
        			    <label for="name">Company Name *</label>
                        <input type="text" name="company_name" id="" class="form-control" placeholder="Company Name" required>
        				@if ($errors->has('company_name'))
        				    <p style="color: red; margin: 0; font-size: 10px;">{{ $errors->first('company_name') }}</p>
        				@endif
                 	</div>
                 	<div class="col-md-6 form-group">
        			    <label for="name">Receiver Name *</label>
                        <input type="text" name="receiver_name" id="" class="form-control" placeholder="Receiver Name" required>
        				@if ($errors->has('receiver_name'))
        				    <p style="color: red; margin: 0; font-size: 10px;">{{ $errors->first('receiver_name') }}</p>
        				@endif
                 	</div>
                    <div class="col-md-12 form-group">
        			    <label for="name">Receiver Mobile</label>
                        <input type="text" name="receiver_mobile"  id="" class="form-control" placeholder="Receiver Mobile">
        				@if ($errors->has('receiver_mobile'))
        				    <p style="color: red; margin: 0; font-size: 10px;">{{ $errors->first('receiver_mobile') }}</p>
        				@endif
                 	</div>
    	       </div>
    	       <div class="form-group">
    				<label for="name">Attachtment</label>
					<input type="file" class="form-control {{$errors->has('attachment')?'error':''}}" name="attachment" accept="image/*"  style="padding: 3px;">
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
    		 <button type="submit" class="btn btn-primary"><i class="bx bx-plus"></i> Add Expense</button>
    	   </div>
	   </form>
	 </div>
   </div>
 </div>

<!-- Add Creditor Payment Modal -->
 <div class="modal fade text-left" id="AddCreditorPayment" tabindex="-1" role="dialog">
   <div class="modal-dialog" role="document">
	 <div class="modal-content">
	 <form id="creditorPaymentForm" action="{{ route('admin.suppliersAction', ['bill-payment-store', 0]) }}" data-action-template="{{ route('admin.suppliersAction', ['bill-payment-store', '__ID__']) }}" method="post" enctype="multipart/form-data">
	   	  @csrf
    	   <div class="modal-header">
    		 <h4 class="modal-title">Creditor Payment</h4>
    		 <button type="button" class="close" data-dismiss="modal" aria-label="Close">
    		   <span aria-hidden="true">&times; </span>
    		 </button>
    	   </div>
    	   <div class="modal-body">
    	       <div class="row">
    	           <div class="col-md-6 form-group">
        			    <label for="name">Creditor* </label>
                        <select class="form-control select2" name="creditor_id" id="creditorSelect" required="">
                            <option value="">Select Creditor</option>
                            @foreach($creditors as $creditor)
                            <option value="{{$creditor->id}}">{{$creditor->name}}</option>
                            @endforeach
                        </select>
                 	</div>
                 	<div class="col-md-6 form-group">
        			    <label for="name">Date* </label>
                        <input type="date" class="form-control" name="created_at" value="{{Carbon\Carbon::now()->format('Y-m-d')}}" required="">
                 	</div>
    	       </div>
    	       <div class="row">
    	           <div class="col-md-6 form-group">
        			    <label for="name">Pay Amount* </label>
                        <input type="number" step="any" class="form-control" name="pay_amount" placeholder="0.00" required="">
                 	</div>
                 	<div class="col-md-6 form-group">
        			    <label for="name">Account Method *</label>
                        <select class="form-control" name="account_id" required="">
                            <option value="">Select Account</option>
                            @foreach($accountMethods as $method)
                            <option value="{{$method->id}}">{{$method->name}} - BDT {{priceFormat($method->current_balance)}}</option>
                            @endforeach
                        </select>
                 	</div>
                 	<div class="col-md-6 form-group">
        			    <label for="name">Payment Method *</label>
                        <select class="form-control" name="payment_method_id" required="">
                            <option value="">Select Method</option>
                            @foreach($paymentMethods as $method)
                            <option value="{{$method->id}}">{{$method->name}}</option>
                            @endforeach
                        </select>
                 	</div>
                    <div class="col-md-6 form-group">
        			    <label for="name">Attachtment</label>
						<input type="file" class="form-control" name="attachment" accept="image/*,application/pdf" style="padding: 3px;">
                 	</div>
    	       </div>
    			<div class="form-group">
    				<label for="name">Note</label>
					<textarea name="note" rows="3" class="form-control" placeholder="Write note here..."></textarea>
             	</div>
    	   </div>
    	   <div class="modal-footer">
    		 <button type="button" class="btn grey btn-outline-secondary" data-dismiss="modal">Close </button>
    		 <button type="submit" class="btn btn-primary" id="creditorPaymentSubmitBtn" disabled><i class="bx bx-plus"></i> Add Payment</button>
    	   </div>
	   </form>
	 </div>
   </div>
 </div>

<!--Edit Modal -->
@foreach($expenses as $i=>$dpm)
 <div class="modal fade text-left" id="EditExpense_{{$dpm->id}}" tabindex="-1" role="dialog">
   <div class="modal-dialog" role="document">
	 <div class="modal-content">
	 <form action="{{route('admin.expenses.update',$dpm->id)}}" method="post" enctype="multipart/form-data" >
	   	  @csrf
	   	  @method('PUT')
    	   <div class="modal-header">
    		 <h4 class="modal-title">Edit Expense</h4>
    		 <button type="button" class="close" data-dismiss="modal" aria-label="Close">
    		   <span aria-hidden="true">&times; </span>
    		 </button>
    	   </div>
    	   <div class="modal-body">
    	       <div class="row">
    	           <div class="col-md-6 form-group">
        			    <label for="name">Date* </label>
                        <input type="date" class="form-control {{$errors->has('created_at')?'error':''}}" name="created_at" value="{{$dpm->created_at->format('Y-m-d')}}" 
                        min="{{ $lastAudit && $lastAudit->audit_at
    ? \Carbon\Carbon::parse($lastAudit->audit_at)->addDay()->format('Y-m-d')
    : '' }}"  required="">
        				@if ($errors->has('created_at'))
        				<p style="color: red; margin: 0; font-size: 10px;">{{ $errors->first('created_at') }}</p>
        				@endif
                 	</div>
                 	<div class="col-md-6 form-group">
        			    <label for="name">Expense Type*</label>
                        <select class="form-control" name="expense_type">
                            <option value="">Select Type</option>
                            @foreach($expenseTypes as $type)
                            <option value="{{$type->id}}" {{$dpm->category_id==$type->id?'selected':''}} >{{$type->name}}</option>
                            @endforeach
                        </select>
        				@if ($errors->has('expense_type'))
        				<p style="color: red; margin: 0; font-size: 10px;">{{ $errors->first('expense_type') }}</p>
        				@endif
                 	</div>

    	       </div>
    	       <div class="row">
    	           <div class="col-md-6 form-group">
        			    <label for="name">Payment Method *</label>
                        <select class="form-control" name="payment" required="">
                            <option value="">Select Method</option>
                            @foreach($paymentMethods as $method)
                            <option value="{{$method->id}}" {{$dpm->method_id==$method->id?'selected':''}}>{{$method->name}}</option>
                            @endforeach
                        </select>
        				@if ($errors->has('payment'))
        				<p style="color: red; margin: 0; font-size: 10px;">{{ $errors->first('payment') }}</p>
        				@endif
                 	</div>
    	            <div class="col-md-6 form-group">
        			    <label for="name">Amount* </label>
                        <input type="number" name="amount" class="form-control {{$errors->has('amount')?'error':''}}" step="any" value="{{$dpm->amount}}" placeholder="Amount" >
        				@if ($errors->has('amount'))
        				<p style="color: red; margin: 0; font-size: 10px;">{{ $errors->first('amount') }}</p>
        				@endif
                 	</div>
                 	<div class="col-md-6 form-group">
        			    <label for="name">Account Method *</label>
                        <select class="form-control" disabled="">
                            <option value="{{$dpm->account_id}}">{{$dpm->account?$dpm->account->name:''}}</option>
                        </select>
        				@if ($errors->has('payment'))
        				<p style="color: red; margin: 0; font-size: 10px;">{{ $errors->first('payment') }}</p>
        				@endif
                 	</div>
                    <div class="col-md-6 form-group">
                        <label>Branch/Factory *</label>
                        <select class="form-control" name="branch_id" required="">
                            <option value="">Select Branch/Factory</option>
                            @foreach($branches as $branch)
                            <option value="{{$branch->id}}" {{$dpm->branch_id==$branch->id?'selected':''}} >{{$branch->name}}</option>
                            @endforeach
                        </select>
                        @if ($errors->has('branch_id'))
                        <p style="color: red; margin: 0; font-size: 10px;">{{ $errors->first('branch_id') }}</p>
                        @endif
                    </div>
                    <div class="col-md-6 form-group">
        			    <label for="name">Company Name *</label>
                        <input type="text" name="company_name"  value="{{$dpm->company_name}}" id="" class="form-control" placeholder="Company Name" required>
        				@if ($errors->has('company_name'))
        				    <p style="color: red; margin: 0; font-size: 10px;">{{ $errors->first('company_name') }}</p>
        				@endif
                 	</div>
                 	<div class="col-md-6 form-group">
        			    <label for="name">Receiver Name *</label>
                        <input type="text" name="receiver_name"  value="{{$dpm->receiver_name}}" id="" class="form-control" placeholder="Receiver Name" required>
        				@if ($errors->has('receiver_name'))
        				    <p style="color: red; margin: 0; font-size: 10px;">{{ $errors->first('receiver_name') }}</p>
        				@endif
                 	</div>
                 	<div class="col-md-12 form-group">
        			    <label for="name">Receiver Mobile</label>
                        <input type="text" name="receiver_mobile"  value="{{$dpm->receiver_mobile}}" id="" class="form-control" placeholder="Receiver Mobile">
        				@if ($errors->has('receiver_mobile'))
        				    <p style="color: red; margin: 0; font-size: 10px;">{{ $errors->first('receiver_mobile') }}</p>
        				@endif
                 	</div>
    	       </div>
    	       <div class="form-group">
    				<label for="name">Attachtment</label>
					<input type="file" class="form-control {{$errors->has('attachment')?'error':''}}" name="attachment" style="padding: 3px;">
					@if ($errors->has('attachment'))
					<p style="color: red; margin: 0; font-size: 10px;">{{ $errors->first('attachment') }}</p>
					@endif
             	</div>

    			 <div class="form-group">
    				<label for="name">Description</label>
					<textarea name="description" class="form-control {{$errors->has('description')?'error':''}}" placeholder="Enter Description">{!!$dpm->description!!}</textarea>
					@if ($errors->has('description'))
					<p style="color: red; margin: 0; font-size: 10px;">{{ $errors->first('description') }}</p>
					@endif
             	</div>
             	<div class="row">
                 	<div class="col-md-6 form-group">
                 	    <label for="name">Status</label><br>
                 	    <div class="checkbox">
                             <input class="inp-cbx" id="status_{{$dpm->id}}" type="checkbox" name="status" style="display: none;" {{$dpm->status=='active'?'checked':''}} />
                             <label class="cbx" for="status_{{$dpm->id}}">
                                 <span>
                                     <svg width="12px" height="10px" viewbox="0 0 12 10">
                                         <polyline points="1.5 6 4.5 9 10.5 1"></polyline>
                                     </svg>
                                 </span>
                                 Active
                             </label>
                         </div>
                 	</div>
             	</div>
    	   </div>
    	   <div class="modal-footer">
    		 <button type="button" class="btn grey btn-outline-secondary" data-dismiss="modal">Close </button>
    		 <button type="submit" class="btn btn-primary"><i class="bx bx-check"></i> Update Expense</button>
    	   </div>
	   </form>
	 </div>
   </div>
 </div>
@endforeach
@endisset
<!--View Modal -->
@foreach($expenses as $i=>$exp)
 <div class="modal fade text-left" id="ViewExpense_{{$exp->id}}" tabindex="-1" role="dialog">
   <div class="modal-dialog modal-lg" role="document">
	 <div class="modal-content">
        <div>
            <button type="button" class="close" data-dismiss="modal" aria-label="Close" style="width: 50px;float: right;padding: 10px;">
                <span aria-hidden="true">&times;</span>
            </button>
            <span class="btn btn-danger printBtn" style="width: 100px;">Print</span>
        </div>
         <div class="slip-container">
            <style>
                /* slip css start */
                .slip-container {
                            background: #fff;
                            border-radius: 4px;
                            padding: 20px;
                            position: relative;
                    }

                    .header {
                        text-align: center;
                    }

                    .company-name {
                        font-size: 30px;
                        font-weight: bold;
                        color: #000;
                        margin: 0;
                        letter-spacing: 1px;
                        font-family: serif;
                    }

                    .subtitle {
                        font-size: 13px;
                        font-weight: bold;
                        color: #000 !important;
                        margin: 0px 0;
                    }

                    .contact-info {
                            font-size: 12px;
                            color: #000 !important;
                            margin: 0px 0;
                    }

                    .transaction-badge {
                        background: #000;
                        color: white;
                        padding: 5px 15px;
                        border-radius: 5px;
                        display: inline-block;
                        margin: 10px 0;
                        font-size: 12px;
                        font-weight: bold;
                    }

                    .date-field {
                        position: absolute;
                        top: 135px;
                        right: 40px;
                        font-size: 14px;
                    }

                    .date-label {
                        font-weight: bold;
                    }

                    .form-section {
                        margin: 20px 0;
                    }

                    .form-label {
                        font-weight: bold;
                        color: #2d5016;
                        margin-bottom: 0 !important;
                    }

                    .handwritten {
                        font-size: 18px;
                        color: #1a1a1a;
                        font-style: italic;
                    }

                    .slip-table {
                        width: 100%;
                        margin: 20px 0;
                        border-collapse: collapse;
                    }

                    .slip-table td {
                        padding: 10px;
                        border-bottom: 1px solid #5d8a3a;
                    }

                    .amount-column {
                        text-align: right;
                        font-weight: bold;
                        width: 150px;
                    }

                    .total-row {
                        border-top: 2px solid #2d5016;
                        font-weight: bold;
                        font-size: 16px;
                    }

                    .amount-words {
                        margin: 15px 0;
                        font-style: italic;
                    }

                    .signature-section {
                        display: flex;
                        justify-content: space-between;
                        margin-top: 0;
                        padding-top: 20px;
                    }

                    .signature-box {
                        text-align: center;
                        flex: 1;
                    }

                    .signature-line {
                        border-top: 1px solid #000;
                        margin: 40px 20px 5px 20px;
                        position: relative;
                    }

                    .signature-text {
                        font-family: 'Brush Script MT', cursive;
                        font-size: 24px;
                        margin-top: -35px;
                        color: #1a3d0a;
                    }

                    .input-underline {
                        border: none;
                        border-bottom: 1px solid #000;
                        background: transparent;
                        width: 100%;
                        font-size: 14px;
                    }

                    .input-underline:focus {
                        outline: none;
                        border-bottom-color: #2d5016;
                    }
                    .amountWriteText {
                        display: flex;
                        align-items: end;
                    }

                    .siral {
                        left: 40px;
                    }
                    .TableSlip tr th{
                        padding:5px 10px;
                    }
                    .TableSlip tr td{
                        padding:5px 10px;

                    }
                    .showBarcode rect{
                    }
                    @media print {
                        .transaction-badge {
                            -webkit-print-color-adjust: exact; /* Chrome/Safari */
                            print-color-adjust: exact; /* Firefox */
                            background-color: #000 !important; /* same as screen */
                            color: #fff !important;
                            margin-top: 1.5rem !important;
                        }
                    }

                /* slip css end */
            </style>

            <div class="date-field siral">
                <span class="date-label">SL:</span>
                <input type="text" class="input-underline" style="width: 100px;" value="{{ str_pad($exp->id, 10, '0', STR_PAD_LEFT) }}">
            </div>
            <div class="header">
                <span style="position: absolute;left: 10px;top: 6.5rem;" class="barCodeShow barCodeShow_{{$exp->id}}">
                    <svg class="showBarcode"
                        data-no="{{ str_pad($exp->id, 10, '0', STR_PAD_LEFT) }}">
                    </svg>
                </span>



                <h1 class="company-name">{{general()->title}}</h1>
                <p class="subtitle">(100% Export Oriented Garments Manufacturing Factory)</p>
                <p class="contact-info">{!!general()->address_one!!}</p>
                <p class="contact-info">Mobile: {{general()->mobile}}, {{general()->email}}</p>

                <div class="transaction-badge">TRANSACTION SLIP</div>
                <p class="" style="margin:0px !important"><i><b> {{$exp->category->name}}</b></i></p>
            </div>

            <div class="date-field">
                <span class="date-label">Date:</span>
                <input type="text" class="input-underline" style="width: 100px;" value="{{$exp->created_at->format('d.m.Y')}}">
            </div>

            <div class="form-section" style="margin-top: 1rem !important">
                <div class="row mb-3">
                    <div class="col-12">
                        <div class="amountWriteText">
                            <input type="text" class="input-underline handwritten" value="Paid to ( Name of company ) : {{$exp->company_name}}">
                        </div>
                        <div class="amountWriteText">
                            <input type="text" class="input-underline handwritten" value="( Name of receiver ) : {{$exp->receiver_name}} {{$exp->receiver_mobile?'- '.$exp->receiver_mobile:''}}">
                        </div>

                    </div>
                </div>
            </div>
            <table class="table table-bordered TableSlip">
                <tbody>
                    <tr>
                        <td><b>Cash Paid To </b></td>
                        <td style="min-width: 150px;width: 150px;"><b>Taka</b></td>
                    </tr>
                    <tr>
                        <td>{!! nl2br(e($exp->description)) !!}</td>
                        <td>{{numberFormat($exp->amount,2)}}</td>
                    </tr>
                    <tr>
                        <td style="height:30px;"></td>
                        <td></td>
                    </tr>
                    <tr>
                        <td style="height:30px;"></td>
                        <td></td>
                    </tr>
                    <tr>
                        <td style="text-align: right;"><b>Total </b></td>
                        <td>{{numberFormat($exp->amount,2)}}</td>
                    </tr>
                </tbody>
            </table>
            <div class="form-section">
                <div class="row mb-3">
                    <div class="col-12">
                        <div class="amountWriteText">
                            <label class="form-label" style="min-width: 120px;width: 120px;">Taka in word:</label>
                            <span class="input-underline handwritten TotalAmoutnInWord" data-amount="{{$exp->amount}}" ></span>
                        </div>

                    </div>
                </div>
            </div>
            <div class="signature-section">
                <div class="signature-box">
                    <div class="signature-line">
                        <div class="signature-text" style="height: 1px;"></div>
                    </div>
                    <small>Receiver</small>
                </div>
                <div class="signature-box">
                    <div class="signature-line">
                        <div class="signature-text" style="height: 1px;"></div>
                    </div>
                    <small>Accountant</small>
                </div>
                <div class="signature-box">
                    <div class="signature-line">
                        <div class="signature-text" style="height: 1px;" ></div>
                    </div>
                    <small>Approved by</small>
                </div>
            </div>
        </div>
	 </div>
   </div>
 </div>
@endforeach



@endsection
@push('js')

<script>
    $(document).ready(function(){

    $(function () {
        document.querySelectorAll('[data-bs-toggle="tooltip"]').forEach(function(el) {
            new bootstrap.Tooltip(el, {
                container: 'body',      // tooltip ke body te render korbe
                placement: 'right',     // primary placement
                fallbackPlacements: ['left', 'top', 'bottom'], // jodi right e space na thake
                boundary: 'viewport'    // viewport er bhitore tooltip thakbe
            });
        });
    });




        $(document).on('click', '.printBtn', function () {

            let slip = $(this).closest('.modal-content').find('.slip-container').html();

            // Create hidden iframe
            let iframe = document.createElement('iframe');
            iframe.style.position = "fixed";
            iframe.style.right = "0";
            iframe.style.bottom = "0";
            iframe.style.width = "0";
            iframe.style.height = "0";
            iframe.style.border = "0";
            document.body.appendChild(iframe);

            let doc = iframe.contentWindow.document;

            doc.open();
            doc.write(`
                <html>
                <head>
                    <title>Print Slip</title>
                    <style>
                        body { font-family: Arial, sans-serif; padding: 20px; }
                        table { width: 100%; border-collapse: collapse; }
                        table, th, td { border: 1px solid #000; padding: 6px; }
                    </style>
                </head>
                <body>
                    ${slip}
                </body>
                </html>
            `);
            doc.close();

            // Wait for iframe content to load then print
            iframe.onload = function () {
                iframe.contentWindow.focus();
                iframe.contentWindow.print();

                // Remove iframe after printing
                setTimeout(() => {
                    document.body.removeChild(iframe);
                }, 300);
            };
        });



        $(".select2").each(function () {
            var placeHolder = $(this).data('placeholder');

            $(this).select2({
                placeholder: placeHolder,
                allowClear: true
            });
        });

        $('.showBarcode').each(function () {
            let code = $(this).data('no');
            console.log("BARCODE =>", code);

            JsBarcode(this, code, {
                format: "CODE128",
                displayValue: false,
                fontSize: 16,
                height: 30,
                margin: 0
            });
        });


        $('.TotalAmoutnInWord').each(function () {
            var amount = Number($(this).data('amount'));
            var words = toWords(amount);
            $(this).html(words + ' Taka Only');
        });

        $('#creditorSelect').on('change', function () {
            var creditorId = $(this).val();
            var submitBtn = $('#creditorPaymentSubmitBtn');
            if (creditorId) {
                var actionUrl = $('#creditorPaymentForm').data('action-template').replace('__ID__', creditorId);
                $('#creditorPaymentForm').attr('action', actionUrl);
                submitBtn.prop('disabled', false);
            } else {
                submitBtn.prop('disabled', true);
            }
        });

    });
</script>
@endpush
