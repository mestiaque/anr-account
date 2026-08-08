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
         <h3>I.O.U List</h3>
         <div class="dropdown">
             @can('ac_ious.add')
             <a href="javascript:void(0)" class="btn-custom primary" data-toggle="modal" data-target="#AddExpense" style="padding:5px 15px;">
                 <i class="bx bx-plus"></i> I.O.U
             </a>
             @endcan
             <a href="{{route('admin.ious.index')}}" class="btn-custom yellow">
                 <i class="bx bx-rotate-left"></i>
             </a>
         </div>
    </div>
    <div class="card-body">
        @include(adminTheme().'alerts')

<div class="row align-items-center mb-4">
    <!-- Search Section -->
    <div class="col-md-8">
        <form action="{{route('admin.ious.index')}}" method="GET">
            <div class="row g-2">
                <div class="col-md-5">
                    <input type="text"
                           name="search"
                           value="{{request()->search}}"
                           class="form-control border-secondary"
                           placeholder="Search by name...">
                </div>
                <div class="col-md-4">
                    <select class="form-control" name="account_id">
                        <option value="">All Accounts</option>
                        @foreach($filterAccounts as $account)
                            <option value="{{$account->id}}" {{request()->account_id == $account->id ? 'selected' : ''}}>{{$account->name}}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3 d-flex">
                    <button type="submit" class="btn btn-success px-4 w-100 mr-2">
                        <i class="fa fa-search mr-1"></i> Search
                    </button>
                    @if(request()->anyFilled(['search', 'account_id']))
                        <a href="{{route('admin.ious.index')}}" class="btn btn-outline-danger rounded">Clear</a>
                    @endif
                </div>
            </div>
        </form>
    </div>

    <!-- Stats Card Section -->
    <div class="col-md-4" >
        <div class="card border-0 shadow-sm bg-light p-0">
            <div class="card-body p-3 d-flex align-items-center justify-content-between">
                <div class="d-flex align-items-center">
                    <div class="bg-success text-white rounded-circle d-flex align-items-center justify-content-center mr-3" style="width: 45px; height: 45px;">
                        <i class="fa-solid fa-file-invoice-dollar"></i>
                    </div>
                    <div>
                        <p class="text-muted mb-0 small font-weight-bold">TOTAL AMOUNT</p>
                        <h4 class="mb-0 font-weight-bold text-dark">
                            {{ number_format($expenseIou->sum('amount'), 2) }}
                        </h4>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

        <form action="{{route('admin.ious.index')}}">
            <div class="row">
                <div class="col-md-4 mb-2">
                    @can('ac_ious.delete')
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
                                @can('ac_ious.delete')
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
                            <th style="min-width: 100px;">I.O.U Date</th>
                            <th style="min-width: 100px;">Company</th>
                            <th style="min-width: 100px;">Receiver</th>
                            <th style="min-width: 120px;">Employee</th>
                            <th style="min-width: 120px;">Employee ID</th>
                            <th style="min-width: 150px;">Purpose/Referance</th>
                            <th style="min-width: 100px;">Amount</th>
                            <th style="min-width: 100px;">Account</th>
                            <th style="min-width: 120px;">Branch/Factory</th>
                            <th style="min-width: 130px;width:130px;">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($expenseIou as $i=>$Iou)
                        @php
                            $isOlderThan2Days = $Iou->created_at->lt(\Carbon\Carbon::now()->subDays(7));
                        @endphp
                        <tr @if($isOlderThan2Days) style="background-color: #ffebeb;" @endif>
                            <td>
                                <div class="checkbox">
                                     <input class="inp-cbx" id="cbx_{{$Iou->id}}" type="checkbox" name="checkid[]" value="{{$Iou->id}}" style="display: none;" />
                                      @can('ac_ious.delete')
                                     <label class="cbx" for="cbx_{{$Iou->id}}">
                                         <span>
                                             <svg width="12px" height="10px" viewbox="0 0 12 10">
                                                 <polyline points="1.5 6 4.5 9 10.5 1"></polyline>
                                             </svg>
                                         </span>
                                     </label>
                                     @endcan
                                 </div>
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
                            <td class="text-center">
                                @if(auth()->user()->hasPermission('iou.edit') || auth()->user()->hasPermission('iou.delete'))

                                 @can('ac_ious.edit')
                                <a href="javascript:void(0)" data-toggle="modal" data-target="#EditExpense_{{$Iou->id}}" class="btn-custom success">
                                    <i class="bx bx-edit"></i>
                                </a>
                                @endcan
                                @can('ac_ious.delete')
                                <a href="javascript:void(0)" data-toggle="modal" data-target="#ViewExpense_{{$Iou->id}}" class="btn-custom yellow">
                                    <i class="bx bx-show"></i>
                                </a>
                                @endcan
                                @else -- @endif
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                    <tfoot>
                        <tr>
                            <th colspan="7" class="text-right">Total Amount:</th>
                            <th>{{ priceFormat($expenseIou->sum('amount')) }}</th>
                            <th colspan="3"></th>
                        </tr>
                    </tfoot>
                </table>

            </div>
        </form>


    </div>
</div>
</div>

<!-- Add Modal -->
 <div class="modal fade text-left" id="AddExpense" tabindex="-1" role="dialog">
   <div class="modal-dialog" role="document">
	 <div class="modal-content">
	 <form action="{{route('admin.ious.store')}}" method="post" enctype="multipart/form-data">
	   	  @csrf
    	   <div class="modal-header">
    		 <h4 class="modal-title">Add I.O.U</h4>
    		 <button type="button" class="close" data-dismiss="modal" aria-label="Close">
    		   <span aria-hidden="true">&times; </span>
    		 </button>
    	   </div>
    	   <div class="modal-body">
    	       <div class="row">
    	           <div class="col-md-12 form-group">
        			    <label for="name">Date* </label>
                        <input type="date" class="form-control {{$errors->has('created_at')?'error':''}}" name="created_at" value="{{Carbon\Carbon::now()->format('Y-m-d')}}"  required="">
        				@if ($errors->has('created_at'))
        				<p style="color: red; margin: 0; font-size: 10px;">{{ $errors->first('created_at') }}</p>
        				@endif
                 	</div>
                 	<div class="col-md-6 form-group">
        			    <label for="name">Employee ID</label>
                        <input type="text" name="employee_id" value="" class="form-control EmployeeIDInput" data-url="{{route('admin.ious.searchEmployee')}}" placeholder="Enter employee ID" />
        				@if ($errors->has('employee_id'))
        				<p style="color: red; margin: 0; font-size: 10px;">{{ $errors->first('employee_id') }}</p>
        				@endif
                 	</div>
                 	<div class="col-md-6 form-group">
        			    <label for="name">Employee</label>
                        <input type="text" readonly="" class="form-control EmployeeName" placeholder="Employee Name">
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
                            @foreach($accountMethods as $m=>$method)
                            <option value="{{$method->id}}" {{$m==0?'selected':''}}>{{$method->name}} - BDT {{priceFormat($method->amount)}}</option>
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
        			    <label for="name">Company Name </label>
                        <input type="text" name="company_name" id="" class="form-control" placeholder="Company Name">
        				@if ($errors->has('company_name'))
        				    <p style="color: red; margin: 0; font-size: 10px;">{{ $errors->first('company_name') }}</p>
        				@endif
                 	</div>
                 	<div class="col-md-6 form-group">
        			    <label for="name">Receiver Name</label>
                        <input type="text" name="receiver_name" id="" class="form-control" placeholder="Receiver Name">
        				@if ($errors->has('receiver_name'))
        				    <p style="color: red; margin: 0; font-size: 10px;">{{ $errors->first('receiver_name') }}</p>
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
    				<label for="name">Purpose/Referance</label>
					<textarea name="description" rows="5" class="form-control {{$errors->has('description')?'error':''}}" placeholder="Enter Description"></textarea>
					@if ($errors->has('description'))
					<p style="color: red; margin: 0; font-size: 10px;">{{ $errors->first('description') }}</p>
					@endif
             	</div>
    	   </div>
    	   <div class="modal-footer">
    		 <button type="button" class="btn grey btn-outline-secondary" data-dismiss="modal">Close </button>
    		 <button type="submit" class="btn btn-primary"><i class="bx bx-plus"></i> Add I.O.U</button>
    	   </div>
	   </form>
	 </div>
   </div>
 </div>

  <!--Edit Modal -->
@foreach($expenseIou as $i=>$dpm)
 <div class="modal fade text-left" id="EditExpense_{{$dpm->id}}" tabindex="-1" role="dialog">
   <div class="modal-dialog" role="document">
	 <div class="modal-content">
	 <form action="{{route('admin.ious.update',$dpm->id)}}" method="post" enctype="multipart/form-data" >
	   	  @csrf
	   	  @method('PUT')
    	   <div class="modal-header">
    		 <h4 class="modal-title">Edit I.O.U</h4>
    		 <button type="button" class="close" data-dismiss="modal" aria-label="Close">
    		   <span aria-hidden="true">&times; </span>
    		 </button>
    	   </div>
    	   <div class="modal-body">
    	       <div class="row">
    	           <div class="col-md-12 form-group">
        			    <label for="name">Date* </label>
                        <input type="date" class="form-control {{$errors->has('created_at')?'error':''}}" name="created_at" value="{{$dpm->created_at->format('Y-m-d')}}"  required="">
        				@if ($errors->has('created_at'))
        				<p style="color: red; margin: 0; font-size: 10px;">{{ $errors->first('created_at') }}</p>
        				@endif
                 	</div>
                    <div class="col-md-6 form-group">
        			    <label for="name">Employee ID</label>
                        <input type="text" name="employee_id" value="{{$dpm->employee_id}}" class="form-control EmployeeIDInput" data-url="{{route('admin.ious.searchEmployee')}}" placeholder="Enter employee ID" />
        				@if ($errors->has('employee_id'))
        				<p style="color: red; margin: 0; font-size: 10px;">{{ $errors->first('employee_id') }}</p>
        				@endif
                 	</div>
                 	<div class="col-md-6 form-group">
        			    <label for="name">Employee</label>
                        <input type="text" readonly="" value="{{$dpm->employee?$dpm->employee->name:''}}" class="form-control EmployeeName" placeholder="Employee Name">
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
        			    <label for="name">Company Name</label>
                        <input type="text" name="company_name"  value="{{$dpm->company_name}}" id="" class="form-control" placeholder="Company Name">
        				@if ($errors->has('company_name'))
        				    <p style="color: red; margin: 0; font-size: 10px;">{{ $errors->first('company_name') }}</p>
        				@endif
                 	</div>
                 	<div class="col-md-6 form-group">
        			    <label for="name">Receiver Name</label>
                        <input type="text" name="receiver_name"  value="{{$dpm->receiver_name}}" id="" class="form-control" placeholder="Receiver Name">
        				@if ($errors->has('receiver_name'))
        				    <p style="color: red; margin: 0; font-size: 10px;">{{ $errors->first('receiver_name') }}</p>
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
    				<label for="name">Purpose/Referance</label>
					<textarea name="description" class="form-control {{$errors->has('description')?'error':''}}" placeholder="Enter Description">{!!$dpm->description!!}</textarea>
					@if ($errors->has('description'))
					<p style="color: red; margin: 0; font-size: 10px;">{{ $errors->first('description') }}</p>
					@endif
             	</div>
             	<div class="row">
                 	<div class="col-md-6 form-group">
                 	    <label for="name">Status</label><br>
                 	    <div class="checkbox">
                             <input class="inp-cbx" id="status_{{$dpm->id}}" type="checkbox" name="status" style="display: none;" {{$dpm->status=='completed'?'checked':''}} />
                             <label class="cbx" for="status_{{$dpm->id}}">
                                 <span>
                                     <svg width="12px" height="10px" viewbox="0 0 12 10">
                                         <polyline points="1.5 6 4.5 9 10.5 1"></polyline>
                                     </svg>
                                 </span>
                                 Completed
                             </label>
                         </div>
                 	</div>
                    <div class="col-md-6 form-group">
                        <label for="name">Publish Date*</label>
                        <input type="date" class="form-control {{$errors->has('created_at')?'error':''}}" value="{{$dpm->created_at->format('Y-m-d')}}" name="created_at" required="">
                        @if ($errors->has('created_at'))
    					<p style="color: red; margin: 0; font-size: 10px;">{{ $errors->first('created_at') }}</p>
    					@endif
                    </div>
             	</div>
    	   </div>
    	   <div class="modal-footer">
    		 <button type="button" class="btn grey btn-outline-secondary" data-dismiss="modal">Close </button>
    		 <button type="submit" class="btn btn-primary"><i class="bx bx-check"></i> Update I.O.U</button>
    	   </div>
	   </form>
	 </div>
   </div>
 </div>

 <div class="modal fade text-left" id="ViewExpense_{{$dpm->id}}" tabindex="-1" role="dialog">
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

                /* slip css end */
            </style>

            <!-- <div class="date-field siral">
                <span class="date-label">SL:</span>
                <input type="text" class="input-underline" style="width: 100px;" value="{{ str_pad($dpm->id, 10, '0', STR_PAD_LEFT) }}">
            </div> -->
            <div class="header">
                <!-- <span style="position: absolute;left: 10px;" class="barCodeShow barCodeShow_{{$dpm->id}}">
                    <svg class="showBarcode"
                        data-no="{{ str_pad($dpm->id, 10, '0', STR_PAD_LEFT) }}">
                    </svg>
                </span> -->

                <h1 class="company-name">{{$Iou->branch?$Iou->branch->name:general()->title}}</h1>
                <p class="subtitle">(100% Export Oriented Garments Manufacturing Factory)</p>
                <p class="contact-info">{!!general()->address_one!!}</p>
                <p class="contact-info">Mobile: {{general()->mobile}}, {{general()->email}}</p>

                <div class="transaction-badge">I.O.U</div>
            </div>

            <div class="date-field">
                <span class="date-label">Date:</span>
                <input type="text" class="input-underline" style="width: 100px;" value="{{$dpm->created_at->format('d.m.Y')}}">
            </div>

            <div class="form-section">
                <div class="row mb-3">
                    <div class="col-12">
                        <div class="amountWriteText">
                            <label class="form-label" style="white-space: nowrap;">Paid To : &nbsp;</label>
                            <input type="text" class="input-underline handwritten" value="( Name of company ) {{$dpm->company_name}}">
                        </div>
                        <div class="amountWriteText">
                            <label class="form-label">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; </label>
                            <input type="text" class="input-underline handwritten" value="( Name of receiver ) {{$dpm->receiver_name}}">
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
                        <td>{!! nl2br(e($dpm->description)) !!}</td>
                        <td>{{numberFormat($dpm->amount,2)}}</td>
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
                        <td>{{numberFormat($dpm->amount,2)}}</td>
                    </tr>
                </tbody>
            </table>
            <div class="form-section">
                <div class="row mb-3">
                    <div class="col-12">
                        <div class="amountWriteText">
                            <label class="form-label" style="min-width: 120px;width: 120px;">Taka in word:</label>
                            <span class="input-underline handwritten TotalAmoutnInWord" data-amount="{{$dpm->amount}}" ></span>
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

        let timer = null;
        $(document).on('keyup', '.EmployeeIDInput', function () {
            let input = $(this);
            let data = input.val();
            let url = input.data('url');
            clearTimeout(timer);
            $('.EmployeeName').attr('placeholder','Searching..');
            timer = setTimeout(function () {
                $.ajax({
                    url: url,
                    type: 'GET',
                    data: { search: data },
                    success: function (response) {
                        console.log("Success:", response);
                        $('.EmployeeName').val(response.name);
                    },
                    error: function (xhr) {
                        console.log("Error:", xhr);
                    },
                    complete: function () {
                        console.log("Completed");
                        $('.EmployeeName').attr('placeholder','Employee Name');
                    }
                });
            }, 1000); // 1 sec delay
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
                height: 35
            });
        });


        $('.TotalAmoutnInWord').each(function () {
            var amount = Number($(this).data('amount'));
            var words = toWords(amount);
            $(this).html(words + ' Taka Only');
        });

    });
</script>
@endpush
