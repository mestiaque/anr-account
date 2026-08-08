@extends(adminTheme().'layouts.app') @section('title')
<title>{{websiteTitle('Balance Transfers')}}</title>
@endsection @push('css')
<style type="text/css"></style>
@endpush @section('contents')

<div class="flex-grow-1">
    

<!-- Start -->
<div class="card mb-30">
    <div class="card-header d-flex justify-content-between align-items-center">
         <h3>Balance Transfers</h3>
         <div class="dropdown">
             <a href="javascript:void(0)" class="btn-custom primary" data-toggle="modal" data-target="#AddTypes" style="padding:5px 15px;">
                 <i class="bx bx-plus"></i> Transfer
             </a>
             <a href="{{route('admin.balanceTransfers.index')}}" class="btn-custom yellow">
                 <i class="bx bx-rotate-left"></i>
             </a>
         </div>
    </div>
    <div class="card-body">
        @include(adminTheme().'alerts')
        <div class="accordion-box">
            <div class="accordion">
                <div class="accordion-item">
                 <a class="accordion-title" href="javascript:void(0)">
                     <i class="bx bx-filter-alt"></i>
                    Search click Here..
                 </a>
                 <div class="accordion-content" style="border:1px solid #e1000a;border-top:0;">
                    <form action="{{route('admin.balanceTransfers.index')}}">
                        <div class="row">
                            <div class="col-md-6 mb-1">
                                <div class="input-group">
                                    <input type="date" name="startDate" value="{{request()->startDate?Carbon\Carbon::parse(request()->startDate)->format('Y-m-d') :''}}" class="form-control {{$errors->has('startDate')?'error':''}}" />
                                    <input type="date" name="endDate"  value="{{request()->endDate?Carbon\Carbon::parse(request()->endDate)->format('Y-m-d') :''}}" class="form-control {{$errors->has('endDate')?'error':''}}" />
                                </div>
                            </div>
                            <div class="col-md-6 mb-0">
                                <div class="input-group">
                                    <select class="form-control" name="account" >
                                        <option value="">Select Account</option>
                                        @foreach($accountMethods as $method)
                                        <option value="{{$method->id}}">{{$method->name}}</option>
                                        @endforeach
                                    </select>
                                    <button type="submit" class="btn btn-success btn-sm rounded-0">Search</button>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
                </div>
            </div>
        </div>
        <br>
        <form action="{{route('admin.balanceTransfers.index')}}">
            <div class="row">
                <div class="col-md-4">
                    <div class="input-group mb-1">
                        <select class="form-control form-control-sm rounded-0" name="action" required="">
                            <option value="">Select Action</option>
                            <option value="5">Delete</option>
                        </select>
                        <button class="btn btn-sm btn-primary rounded-0" onclick="return confirm('Are You Want To Action?')">Action</button>
                    </div>
                </div>
                <div class="col-md-8">
                </div>
            </div>
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th style="min-width: 100px;width: 100px;padding-right:0;">
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
                            </th>
                            <th style="min-width: 200px;">Form Account</th>
                            <th style="min-width: 200px;">To Account</th>
                            <th style="min-width: 150px;">Amount</th>
                            <th style="min-width: 120px;">Date</th>
                            <th style="min-width: 100px;width:100px;">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($transections as $i=>$transection)
                        <tr>
                            <td>
                                <div class="checkbox">
                                     <input class="inp-cbx" id="cbx_{{$transection->id}}" type="checkbox" name="checkid[]" value="{{$transection->id}}" style="display: none;" />
                                     <label class="cbx" for="cbx_{{$transection->id}}">
                                         <span>
                                             <svg width="12px" height="10px" viewbox="0 0 12 10">
                                                 <polyline points="1.5 6 4.5 9 10.5 1"></polyline>
                                             </svg>
                                         </span>
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
                                <br>
                            </td>
                            <td>
                                <span>{{$transection->fromAccount?$transection->fromAccount->name:''}}</span> @if($transection->imageFile) <a href="{{asset($transection->imageFile->file_url)}}" target="_blank"><i class="bx bx-file"></i></a> @endif
                            </td>
                            <td>
                                <span>{{$transection->toAccount?$transection->toAccount->name:''}}</span>
                            </td>
                            <td>BDT {{priceFormat($transection->amount)}}</td>
                            <td>{{$transection->created_at->format('d-m-Y')}}</td>
                            <td class="center">
                                <a href="javascript:void(0)" data-toggle="modal" data-target="#EditType_{{$transection->id}}" class="btn-custom success">
                                    <i class="bx bx-show"></i>
                                </a>
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
 <div class="modal fade text-left" id="AddTypes" tabindex="-1" role="dialog">
   <div class="modal-dialog" role="document">
	 <div class="modal-content">
	 <form action="{{route('admin.balanceTransfers.store')}}" method="post" enctype="multipart/form-data">
	   	  @csrf
    	   <div class="modal-header">
    		 <h4 class="modal-title">Balance Transfer</h4>
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
        			    <label for="name">Amount* </label>
                        <input type="number" step="any" class="form-control {{$errors->has('amount')?'error':''}}" name="amount" placeholder="Amount"  required="">
        				@if ($errors->has('amount'))
        				<p style="color: red; margin: 0; font-size: 10px;">{{ $errors->first('amount') }}</p>
        				@endif
                 	</div>
    	       </div>

	           <div class="form-group">
    			    <label for="name">From Account*</label>
                    <select class="form-control" name="form_account" required="">
                        <option value="">Select Account</option>
                        @foreach($accountMethods as $method)
                        <option value="{{$method->id}}">{{$method->name}} - BDT {{priceFormat($method->current_balance)}}</option>
                        @endforeach
                    </select>
    				@if ($errors->has('form_account'))
    				<p style="color: red; margin: 0; font-size: 10px;">{{ $errors->first('form_account') }}</p>
    				@endif
             	</div>
	           <div class="form-group">
    			    <label for="name">To Account*</label>
                    <select class="form-control" name="to_account" required="">
                        <option value="">Select Account</option>
                        @foreach($accountMethods as $method)
                        <option value="{{$method->id}}">{{$method->name}} - BDT {{priceFormat($method->current_balance)}}</option>
                        @endforeach
                    </select>
    				@if ($errors->has('to_account'))
    				<p style="color: red; margin: 0; font-size: 10px;">{{ $errors->first('to_account') }}</p>
    				@endif
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
    		 <button type="submit" class="btn btn-primary"><i class="bx bx-plus"></i> Submit Transfer</button>
    	   </div>
	   </form>
	 </div>
   </div>
 </div>

<!--Edit Modal -->
@foreach($transections as $i=>$dpm)
 <div class="modal fade text-left" id="EditType_{{$dpm->id}}" tabindex="-1" role="dialog">
   <div class="modal-dialog" role="document">
	 <div class="modal-content">
    	   <div class="modal-header">
    		 <h4 class="modal-title">Balance Transfer View</h4>
    		 <button type="button" class="close" data-dismiss="modal" aria-label="Close">
    		   <span aria-hidden="true">&times; </span>
    		 </button>
    	   </div>
    	   <div class="modal-body">
    	   		<div class="table-responsive">
    	           <table class="table table-borderless expenseTableView">
    	               <tr>
    	                   <th style="width:150px;min-width:150px;">Date</th>
    	                   <th style="width:25px;min-width:25px;">:</th>
    	                   <td>{{$dpm->created_at->format('Y-m-d')}}</td>
    	               </tr>
    	               <tr>
    	                   <th>Form Account</th>
    	                   <th>:</th>
    	                   <td>{{$dpm->fromAccount?$dpm->fromAccount->name:''}}</td>
    	               </tr>
    	               <tr>
    	                   <th>To Account</th>
    	                   <th>:</th>
    	                   <td>{{$dpm->toAccount?$dpm->toAccount->name:''}}</td>
    	               </tr>
    	               <tr>
    	                   <th>Amount</th>
    	                   <th>:</th>
    	                   <td>BDT {{priceFormat($dpm->amount)}}</td>
    	               </tr>
    	               <tr>
    	                   <th>Attachment</th>
    	                   <th>:</th>
    	                   <td>@if($dpm->imageFile) <a href="{{asset($dpm->imageFile->file_url)}}" target="_blank">Attach File <i class="bx bx-file"></i></a> @endif</td>
    	               </tr>
    	               <tr>
    	                   <th>Description</th>
    	                   <th>:</th>
    	                   <td>{!!$dpm->description!!}</td>
    	               </tr>
    	           </table>
    	       </div>
    	   </div>
	 </div>
   </div>
 </div>
@endforeach


@endsection @push('js') @endpush