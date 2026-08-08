@extends(adminTheme().'layouts.app') @section('title')
<title>{{websiteTitle('Account List')}}</title>
@endsection @push('css')
<style type="text/css"></style>
@endpush @section('contents')

<div class="flex-grow-1">


<!-- Start -->
<div class="card mb-30">
    <div class="card-header d-flex justify-content-between align-items-center">
         <h3>Account List</h3>
         <div class="dropdown">
            @can('ac_accounts.add')
             <a href="javascript:void(0)" class="btn-custom primary" data-toggle="modal" data-target="#AddTypes" style="padding:5px 15px;">
                 <i class="bx bx-plus"></i> Account
             </a>
             @endcan
             <a href="{{route('admin.accounts.index')}}" class="btn-custom yellow">
                 <i class="bx bx-rotate-left"></i>
             </a>
         </div>
    </div>
    <div class="card-body">
        @include(adminTheme().'alerts')
            <form action="{{route('admin.accounts.index')}}">
                <div class="row">
                    <div class="col-md-6 mb-0">
                        <div class="input-group">
                            <input type="text" name="search" value="{{request()->search?request()->search:''}}" placeholder="Search Account Name" class="form-control {{$errors->has('search')?'error':''}}" />
                            <button type="submit" class="btn btn-success btn-sm rounded-0">Search</button>
                        </div>
                    </div>
                </div>
            </form>
        <br>
            <div class="table-responsive">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            {{-- <th style="min-width: 100px; width: 100px;padding-right:0; position: relative;">
                                 @if(can('ac_accounts.edit'))
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
                                 @else All @endif
                            </th> --}}
                            <th style="min-width: 250px;">Account</th>
                            <th style="min-width: 300px;">Description</th>
                            <th style="min-width: 200px;width:200px;">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($accounts as $i=>$method)
                        <tr>
                            {{-- <td style=" position: relative;">
                                @if(can('ac_accounts.edit'))
                                <div class="checkbox">
                                    <input class="inp-cbx" id="cbx_{{$method->id}}" type="checkbox" name="checkid[]" value="{{$method->id}}" style="display: none;" />
                                    <label class="cbx" for="cbx_{{$method->id}}">
                                        <span>
                                            <svg width="12px" height="10px" viewbox="0 0 12 10">
                                                <polyline points="1.5 6 4.5 9 10.5 1"></polyline>
                                            </svg>
                                        </span>
                                    </label>
                                </div>

                                @endif
                                    <span style="margin:0 5px;">{{$accounts->currentpage()==1?$i+1:$i+($accounts->perpage()*($accounts->currentpage() - 1))+1}}</span>
                                @if($method->status)
                                    <span style="color: #43d39e;font-size: 20px;line-height: 20px;position:absolute;">
                                        <i class="bx bx-check-circle"></i>
                                    </span>
                                @else
                                    <span style="color: #FF9800;font-size: 20px;line-height: 20px;position:absolute;">
                                        <i class="bx bx-analyse"></i>
                                    </span>
                                @endif
                            </td> --}}
                            <td>
                                <b>Title:</b><span> {{$method->name}}</span><br>
                                <b>Owner:</b><span> {{$method->user?$method->user->name:'No Owner'}}</span><br>
                                <b>Opening Date:</b> {{$method->created_at->format('d-m-Y')}}
                                @if($method->status=='active')
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
                                <b>Balance:</b> BDT {{priceFormat($method->current_balance)}} <br>
                                <span>{!!$method->description!!}</span>
                            </td>
                            <td class="text-center">

                                {{-- If user has ANY action permission --}}
                                @if( can('ac_accounts.add') || can('ac_accounts.edit') || can('ac_accounts.view') )

                                    {{-- Edit --}}
                                    @can('ac_accounts.edit')
                                    <a href="javascript:void(0)"
                                    data-toggle="modal"
                                    data-target="#EditType_{{$method->id}}"
                                    class="btn-custom success">
                                        <i class="bx bx-edit"></i>
                                    </a>
                                    @endcan

                                    {{-- View --}}
                                    @can('ac_accounts.view')
                                    <a href="{{route('admin.accounts.show', $method->id)}}"
                                    class="btn-custom yellow">
                                        <i class="bx bx-show"></i>
                                    </a>
                                    @endcan

                                @else -- @endif



                                {{-- Delete --}}
                                {{-- @can('ac_accounts.delete')
                                <a href="{{route('admin.accountsAction', ['delete', $method->id])}}"
                                class="btn-custom danger"
                                onclick="return confirm('Are You Want To Delete?')">
                                    <i class="bx bx-trash"></i>
                                </a>
                                @endcan --}}

                            </td>

                        </tr>
                        @endforeach
                    </tbody>
                    <tfoot>
                        <tr>
                            <th class="text-right">Page Balance Total:</th>
                            <td>BDT {{ priceFormat($accounts->sum('current_balance')) }}</td>
                            <td></td>
                        </tr>
                    </tfoot>
                </table>
                {{$accounts->links('pagination::bootstrap-4')}}
            </div>
        </form>
    </div>
</div>
</div>


<!-- Add Modal -->
 <div class="modal fade text-left" id="AddTypes" tabindex="-1" role="dialog">
   <div class="modal-dialog" role="document">
	 <div class="modal-content">
	    <form action="{{route('admin.accounts.store')}}" method="post">
	   	  @csrf
    	   <div class="modal-header">
    		 <h4 class="modal-title">Add Account</h4>
    		 <button type="button" class="close" data-dismiss="modal" aria-label="Close">
    		   <span aria-hidden="true">&times; </span>
    		 </button>
    	   </div>
    	   <div class="modal-body">
    	   		<div class="form-group">
    			    <label for="name">Account Name* </label>
                    <input type="text" class="form-control {{$errors->has('name')?'error':''}}" name="name" placeholder="Enter Name" required="">
    				@if ($errors->has('name'))
    				<p style="color: red; margin: 0; font-size: 10px;">{{ $errors->first('name') }}</p>
    				@endif
             	</div>
    			<div class="form-group">
    				<label for="name">Description</label>
					<textarea name="description" class="form-control {{$errors->has('description')?'error':''}}" placeholder="Enter Description"></textarea>
					@if ($errors->has('description'))
					<p style="color: red; margin: 0; font-size: 10px;">{{ $errors->first('description') }}</p>
					@endif
             	</div>
             	<div class="form-group">
    			    <label for="name">Account Owner* </label>
                    <select class="form-control" name="account_owner" required="">
                        <option value="">Select Owner</option>
                        @foreach($adminUsers as $user)
                        <option value="{{$user->id}}">{{$user->name}}</option>
                        @endforeach
                    </select>
    				@if ($errors->has('name'))
    				<p style="color: red; margin: 0; font-size: 10px;">{{ $errors->first('name') }}</p>
    				@endif
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

<!--Edit Modal -->
@foreach($accounts as $i=>$dpm)
 <div class="modal fade text-left" id="EditType_{{$dpm->id}}" tabindex="-1" role="dialog">
   <div class="modal-dialog" role="document">
	 <div class="modal-content">
	 <form action="{{route('admin.accounts.update',$dpm->id)}}" method="post">
	   	  @csrf
	   	  @method('PUT')
    	   <div class="modal-header">
    		 <h4 class="modal-title">Edit Account</h4>
    		 <button type="button" class="close" data-dismiss="modal" aria-label="Close">
    		   <span aria-hidden="true">&times; </span>
    		 </button>
    	   </div>
    	   <div class="modal-body">
    	   		<div class="form-group">
    			    <label for="name">Total Balance* </label>
                    <input type="number" disabled="" class="form-control" value="{{$dpm->current_balance}}"  placeholder="Enter Amount">
             	</div>
    	   		<div class="form-group">
    			    <label for="name">Title* </label>
                    <input type="text" class="form-control {{$errors->has('name')?'error':''}}" value="{{$dpm->name?:old('name')}}" name="name" placeholder="Enter Name" required="">
    				@if ($errors->has('name'))
    				<p style="color: red; margin: 0; font-size: 10px;">{{ $errors->first('name') }}</p>
    				@endif
             	</div>
                <div class="form-group">
    			    <label for="name">Account Owner* </label>
                    <select class="form-control" name="account_owner" required="">
                        <option value="">Select Owner</option>
                        @foreach($adminUsers as $user)
                            <option value="{{$user->id}}" {{ $dpm?->user?->id == $user->id ? 'selected':'' }}>{{$user->name}}</option>
                        @endforeach
                    </select>
    				@if ($errors->has('name'))
    				<p style="color: red; margin: 0; font-size: 10px;">{{ $errors->first('name') }}</p>
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
    		 <button type="submit" class="btn btn-primary"><i class="bx bx-check"></i> Update Account</button>
    	   </div>
	   </form>
	 </div>
   </div>
 </div>
@endforeach



@endsection @push('js') @endpush
