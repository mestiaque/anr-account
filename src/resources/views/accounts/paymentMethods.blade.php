@extends(adminTheme().'layouts.app') @section('title')
<title>{{websiteTitle('Payment Methods')}}</title>
@endsection @push('css')
<style type="text/css"></style>
@endpush @section('contents')

<div class="flex-grow-1">


<!-- Start -->
<div class="card mb-30">
    <div class="card-header d-flex justify-content-between align-items-center">
         <h3>Payment Methods</h3>
         <div class="dropdown">
            @can('ac_payment_methods.add')
             <a href="javascript:void(0)" class="btn-custom primary" data-toggle="modal" data-target="#AddTypes" style="padding:5px 15px;">
                 <i class="bx bx-plus"></i> Method
             </a>
             @endcan
             <a href="{{route('admin.paymentMethods.index')}}" class="btn-custom yellow">
                 <i class="bx bx-rotate-left"></i>
             </a>
         </div>
    </div>
    <div class="card-body">
        @include(adminTheme().'alerts')
        <form action="{{route('admin.paymentMethods.index')}}">
            <div class="row">
                <div class="col-md-6 mb-0">
                    <div class="input-group">
                        <input type="text" name="search" value="{{request()->search?request()->search:''}}" placeholder="Search Method Name" class="form-control {{$errors->has('search')?'error':''}}" />
                        <button type="submit" class="btn btn-success btn-sm rounded-0">Search</button>
                    </div>
                </div>
            </div>
        </form>
        <br>
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th style="min-width: 100px;width: 100px;">
                                SL
                            </th>
                            <th style="min-width: 200px;">Name</th>
                            <th style="min-width: 300px;">Description</th>
                            <th style="min-width: 120px;">Date</th>
                            <th style="min-width: 100px;width:100px;">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($paymentMethods as $i=>$type)
                        <tr>
                            <td style="display:flex;">
                                <span style="margin:0 5px;">{{$paymentMethods->currentpage()==1?$i+1:$i+($paymentMethods->perpage()*($paymentMethods->currentpage() - 1))+1}}</span>
                                @if($type->status=='active')
                                <span style="color: #43d39e;font-size: 20px;line-height: 20px;">
                                    <i class="bx bx-check-circle"></i>
                                </span>
                                @else
                                <span style="color: #FF9800;font-size: 20px;line-height: 20px;">
                                    <i class="bx bx-analyse"></i>
                                </span>
                                @endif
                                @if($type->fetured)
                                <span style="color: #FF9800;font-size: 20px;line-height: 20px;">
                                <i class='bx bxs-bank'></i>
                                </span>
                                @endif
                            </td>
                            <td>
                                <span>{{$type->name}}</span>
                            </td>
                            <td>
                                <span>{!!$type->description!!}</span>
                            </td>
                            <td>{{$type->created_at->format('d-m-Y')}}</td>
                            <td class="text-center">
                                @if(auth()->user()->hasPermission('ac_payment_methods.edit') || auth()->user()->hasPermission('ac_payment_methods.delete'))
                                @can('ac_payment_methods.edit')
                                <a href="javascript:void(0)" data-toggle="modal" data-target="#EditType_{{$type->id}}" class="btn-custom success">
                                    <i class="bx bx-edit"></i>
                                </a>
                                @endcan
                                @can('ac_payment_methods.delete')
                                <form action="{{route('admin.paymentMethods.destroy',$type->id)}}" method="post" style="display:inline-block;" onsubmit="return confirm('Are You Want To Delete?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn-custom danger" style="border:0;"><i class="bx bx-trash"></i></button>
                                </form>
                                @endcan
                                @else --  @endif
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                    <tfoot>
                        <tr>
                            <th colspan="3" class="text-right">Total Methods:</th>
                            <th>{{ $paymentMethods->total() }}</th>
                            <th></th>
                        </tr>
                    </tfoot>
                </table>
                {{$paymentMethods->links('pagination')}}
            </div>
        </form>


    </div>
</div>
</div>


<!-- Add Modal -->
 <div class="modal fade text-left" id="AddTypes" tabindex="-1" role="dialog">
   <div class="modal-dialog" role="document">
	 <div class="modal-content">
	    <form action="{{route('admin.paymentMethods.store')}}" method="post">
	   	  @csrf
    	   <div class="modal-header">
    		 <h4 class="modal-title">Add Payment Method</h4>
    		 <button type="button" class="close" data-dismiss="modal" aria-label="Close">
    		   <span aria-hidden="true">&times; </span>
    		 </button>
    	   </div>
    	   <div class="modal-body">
    	   		<div class="form-group">
    			    <label for="name">Name* </label>
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
@foreach($paymentMethods as $i=>$dpm)
 <div class="modal fade text-left" id="EditType_{{$dpm->id}}" tabindex="-1" role="dialog">
   <div class="modal-dialog" role="document">
	 <div class="modal-content">
	 <form action="{{route('admin.paymentMethods.update',$dpm->id)}}" method="post">
	   	  @csrf
	   	  @method('PUT')
    	   <div class="modal-header">
    		 <h4 class="modal-title">Edit Payment Method</h4>
    		 <button type="button" class="close" data-dismiss="modal" aria-label="Close">
    		   <span aria-hidden="true">&times; </span>
    		 </button>
    	   </div>
    	   <div class="modal-body">
    	   		<div class="form-group">
    			    <label for="name">Title* </label>
                    <input type="text" class="form-control {{$errors->has('name')?'error':''}}" value="{{$dpm->name?:old('name')}}" name="name" placeholder="Enter Name" required="">
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
                        <label for="name">Publish Date</label>
                        <input type="date" class="form-control" value="{{$dpm->created_at?->format('Y-m-d')}}" disabled="">
                    </div>
             	</div>
    	   </div>
    	   <div class="modal-footer">
    		 <button type="button" class="btn grey btn-outline-secondary" data-dismiss="modal">Close </button>
    		 <button type="submit" class="btn btn-primary"><i class="bx bx-check"></i> Update Type</button>
    	   </div>
	   </form>
	 </div>
   </div>
 </div>
@endforeach



@endsection @push('js') @endpush
