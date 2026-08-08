@extends(adminTheme().'layouts.app') @section('title')
<title>{{websiteTitle('Fund Received List')}}</title>
@endsection @push('css')
<style type="text/css"></style>
@endpush @section('contents')

<div class="flex-grow-1">


<!-- Start -->
<div class="card mb-30">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h3>Fund Received List</h3>
        <div class="dropdown">
            @can('ac_deposits.add')
            <a href="javascript:void(0)" class="btn-custom primary" data-toggle="modal" data-target="#AddDeposit" style="padding:5px 15px;">
                 <i class="bx bx-plus"></i> Fund Received
             </a>
             @endcan
             <a href="{{route('admin.deposits.index')}}" class="btn-custom yellow">
                 <i class="bx bx-rotate-left"></i>
             </a>
        </div>
    </div>
    <div class="card-body">
        @include(adminTheme().'alerts')
        <div class="accordionx-box">
            <div class="accordionx">
                <div class="accordionx-item">
                 <div class="accordionx-content">
                    <form action="{{route('admin.deposits.index')}}">
                        <div class="row">
                            <div class="col-md-4 mb-0">
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

                            <div class="col-md-5 mb-0">
                                <div class="input-group">
                                    <input type="text" name="search" value="{{request()->search?request()->search:''}}" placeholder="Search Transection ID " class="form-control {{$errors->has('search')?'error':''}}" />
                                    <button type="submit" class="btn btn-success btn-sm rounded-0">Search</button>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
                </div>
            </div>
        </div>
        {{-- <br> --}}
        <form action="{{route('admin.deposits.index')}}">
            <div class="row">
                <div class="col-md-4">
                    @can('ac_deposits.delete')
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
                                @can('ac_deposits.delete')
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
                                @else Al
                                @endcan
                            </th>
                            <th style="min-width: 200px;">Fund Received </th>
                            <th style="min-width: 300px;">Balance - Description</th>
                            <th style="min-width: 200px; width:200px" class="text-center">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($transections as $i=>$transection)
                        <tr>
                            <td>
                                <div class="checkbox">
                                     <input class="inp-cbx" id="cbx_{{$transection->id}}" type="checkbox" name="checkid[]" value="{{$transection->id}}" style="display: none;" />
                                     <label class="cbx" for="cbx_{{$transection->id}}">
                                        @can('ac_deposits.delete')
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
                                <span style="color: #43d39e;font-size: 20px;line-height: 20px;">
                                    <i class="bx bx-check-circle"></i>
                                </span>
                                @else
                                <span style="color: #FF9800;font-size: 20px;line-height: 20px;">
                                    <i class="bx bx-analyse"></i>
                                </span>
                                @endif
                            </td>
                            <td>
                                <b>Date:</b> {{$transection->created_at->format('d-m-Y')}}<br>
                                <b>TNX Id:</b> {{$transection->deposit_no}} @if($transection->imageFile) <a href="{{asset($transection->imageFile->file_url)}}" target="_blank"><i class="bx bx-file"></i></a> @endif <br>
                                <b>Accounts:</b> {{$transection->account?$transection->account->name:''}}
                                        @if($transection->account)
                                            <a target="_blank" href="{{ route('admin.accounts.show', $transection->account->id) }}">
                                                &nbsp;<i class="fas fa-external-link-alt"></i>
                                            </a>
                                        @endif
                                    <br>
                                <b>Received Method:</b> {{$transection->received_method}}<br>
                                <b>Received From:</b> {{$transection->received_from}}<br>
                                <!-- <b>Payment Method:</b> {{$transection->method?$transection->method->name:''}}<br> -->
                                @if($transection->bank_name)
                                <b>Bank Name:</b> {{$transection->bank_name}} <br>
                                @endif

                            </td>
                            <td>
                                @php
                                    $afterBalance = $transection->transaction->balance_after ?? 0;
                                    $previousBalance = $transection->status == 'success'
                                        ? $afterBalance - $transection->amount
                                        : ($transection->account ? $transection->account->current_balance : 0);
                                @endphp
                                <b>Previous:</b> <span style="color: #FF9800;">{{ numberFormat($previousBalance, 2) }}</span>
                                <br> <b>Add Balance:</b> <span style="color: #009688;font-weight: bold;"> {{numberFormat($transection->amount,2)}} </span>
                                <br> <b>After Balance:</b> <span>{{numberFormat($afterBalance,2)}} </span>
                                <br>
                                <b>Perticulars:</b> <span>{!!$transection->description!!}</span>
                            </td>
                            <td class="text-center">
                                @if(can('ac_deposits.edit') || can('ac_deposits.approve'))
                                    @can('ac_deposits.edit')
                                        <a href="javascript:void(0)" data-toggle="modal" data-target="#EditDeposit_{{$transection->id}}" class="btn-custom success">
                                            <i class="bx bx-edit"></i>
                                        </a>
                                    @endcan
                                    @can('ac_deposits.approve')
                                        @if($transection->status == 'pending')
                                            {{-- Approve Button --}}
                                            <a href="{{ route('admin.deposits.approve', $transection->id) }}"
                                            class="btn-custom yellow"
                                            onclick="return confirm('Mark as Approved?');">
                                                <i class="bx bx-check-circle"></i> Approve {{-- relative icon --}}
                                            </a>
                                        @endif
                                    @endcan
                                @else -- @endif
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                    <tfoot>
                        <tr>
                            <th colspan="2" class="text-right">Page Total:</th>
                            <td>{{ numberFormat($transections->sum('amount'), 2) }}</td>
                            <td></td>
                        </tr>
                        <tr>
                            <th colspan="2" class="text-right">Filtered Total:</th>
                            <td>{{ numberFormat($totalAmount, 2) }}</td>
                            <td></td>
                        </tr>
                    </tfoot>
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
	 <form action="{{route('admin.deposits.store')}}" method="post" enctype="multipart/form-data">
	   	  @csrf
    	   <div class="modal-header">
    		 <h4 class="modal-title">Add Fund</h4>
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
                    <div class="col-md-12 form-group">
        			    <label for="name">Received Method* </label>
                        <input type="text" name="received_method" class="form-control" placeholder="Enter received method" required="">
        				@if ($errors->has('received_method'))
        				<p style="color: red; margin: 0; font-size: 10px;">{{ $errors->first('received_method') }}</p>
        				@endif
                 	</div>
                    <div class="col-md-12 form-group">
                        <label for="name">Received From*</label>
                        <input type="text" class="form-control" placeholder="Enter Received From" name="received_from" required="" >
                        @if ($errors->has('received_from'))
        				<p style="color: red; margin: 0; font-size: 10px;">{{ $errors->first('received_from') }}</p>
        				@endif
                    </div>
                    <div class="col-md-12 form-group">
                        <label for="name">Attachtment</label>
                        <input type="file" class="form-control {{$errors->has('attachment')?'error':''}}" name="attachment" accept="image/*" style="padding: 3px;">
                        @if ($errors->has('attachment'))
                        <p style="color: red; margin: 0; font-size: 10px;">{{ $errors->first('attachment') }}</p>
                        @endif
                    </div>
                    <div class="col-md-12 form-group">
                        <label for="name">Particulars</label>
                        <input type="text" class="form-control" placeholder="Enter Perticulars" name="description">
                        @if ($errors->has('description'))
                        <p style="color: red; margin: 0; font-size: 10px;">{{ $errors->first('description') }}</p>
                        @endif
                    </div>
                    <div class="col-md-12 form-group">
        			    <label for="name">Amount of BDT* </label>
                        <input type="number" step="any" name="amount" class="form-control {{$errors->has('amount')?'error':''}}" placeholder="Amount of BDT" required="" >
        				@if ($errors->has('amount'))
        				<p style="color: red; margin: 0; font-size: 10px;">{{ $errors->first('amount') }}</p>
        				@endif
                 	</div>
                 	<div class="col-md-12 form-group">
        			    <label for="name">Receiver Account*</label>
                        <select class="form-control" name="account" required="">
                            <option value="">Select Receiver Account</option>
                            @foreach($accountMethods as $method)
                            <option value="{{$method->id}}">{{$method->name}}</option>
                            @endforeach
                        </select>
        				@if ($errors->has('account'))
        				<p style="color: red; margin: 0; font-size: 10px;">{{ $errors->first('account') }}</p>
        				@endif
                 	</div>
    	       </div>
    	   </div>
    	   <div class="modal-footer">
    		 <button type="button" class="btn grey btn-outline-secondary" data-dismiss="modal">Close </button>
    		 <button type="submit" class="btn btn-primary"><i class="bx bx-plus"></i> Add Fund</button>
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
	 <form action="{{route('admin.deposits.update',$dpm->id)}}" method="post" enctype="multipart/form-data">
	   	  @csrf
	   	  @method('PUT')
    	   <div class="modal-header">
    		 <h4 class="modal-title">Fund #{{$dpm->deposit_no}}</h4>
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
                    <div class="col-md-12 form-group">
        			    <label for="name">Received Method* </label>
                        <input type="text" name="received_method" value="{{$dpm->received_method}}" class="form-control" placeholder="Enter received method" required="">
        				@if ($errors->has('received_method'))
        				<p style="color: red; margin: 0; font-size: 10px;">{{ $errors->first('received_method') }}</p>
        				@endif
                 	</div>
                    <div class="col-md-12 form-group">
                        <label for="name">Received From</label>
                        <input type="text" value="{{$dpm->received_from}}" class="form-control" placeholder="Enter bank name" name="received_from" required="">
                        @if ($errors->has('received_from'))
        				<p style="color: red; margin: 0; font-size: 10px;">{{ $errors->first('received_from') }}</p>
        				@endif
                    </div>
                    <div class="col-md-12 form-group">
                        <label for="name">Attachtment</label>
                        <input type="file" class="form-control {{$errors->has('attachment')?'error':''}}" name="attachment" accept="image/*" style="padding: 3px;">
                        @if ($errors->has('attachment'))
                        <p style="color: red; margin: 0; font-size: 10px;">{{ $errors->first('attachment') }}</p>
                        @endif
                    </div>
                    <div class="col-md-12 form-group">
                        <label for="name">Particulars</label>
                        <input type="text" value="{{$dpm->description}}" class="form-control" placeholder="Enter bank name" name="description">
                        @if ($errors->has('description'))
                        <p style="color: red; margin: 0; font-size: 10px;">{{ $errors->first('description') }}</p>
                        @endif
                    </div>
                    <div class="col-md-12 form-group">
        			    <label for="name">Amount* </label>
                        <input type="number" step="any"  value="{{$dpm->amount}}" disabled="" class="form-control {{$errors->has('amount')?'error':''}}" placeholder="Amount"  >
        				@if ($errors->has('amount'))
        				<p style="color: red; margin: 0; font-size: 10px;">{{ $errors->first('amount') }}</p>
        				@endif
                 	</div>
                 	<div class="col-md-12 form-group">
        			    <label for="name">Account*</label>
                        <input value="{{$dpm->account?$dpm->account->name:''}}" class="form-control" disabled="" />
        				@if ($errors->has('account'))
        				<p style="color: red; margin: 0; font-size: 10px;">{{ $errors->first('account') }}</p>
        				@endif
                 	</div>
    	       </div>
    	   </div>
    	   <div class="modal-footer">
    		 <button type="button" class="btn grey btn-outline-secondary" data-dismiss="modal">Close </button>
    		 <button type="submit" class="btn btn-primary"><i class="bx bx-plus"></i> Update Fund</button>
    	   </div>
	   </form>
	 </div>
   </div>
 </div>

@endforeach




@endsection @push('js') @endpush
