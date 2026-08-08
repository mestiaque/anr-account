@extends(adminTheme().'layouts.app') @section('title')
<title>{{websiteTitle('Creditors')}}</title>
@endsection @push('css')
<style type="text/css"></style>
@endpush @section('contents')

<div class="flex-grow-1">

<!-- Start -->
<div class="card mb-30">
    <div class="card-header d-flex justify-content-between align-items-center">
         <h3>Creditors</h3>
         <div class="dropdown">
            @can('ac_creditors.add')
             <a href="javascript:void(0)" class="btn-custom primary" data-toggle="modal" data-target="#AddCreditor" style="padding:5px 15px;">
                 <i class="bx bx-plus"></i> Creditor
             </a>
             @endcan
             <a href="{{route('admin.creditors.index')}}" class="btn-custom yellow">
                 <i class="bx bx-rotate-left"></i>
             </a>
         </div>
    </div>
    <div class="card-body">
        @include(adminTheme().'alerts')
            <form action="{{route('admin.creditors.index')}}">
                <div class="row">
                    <div class="col-md-6 mb-0">
                        <div class="input-group">
                            <input type="text" name="search" value="{{request()->search?request()->search:''}}" placeholder="Search Name / Company / Mobile" class="form-control {{$errors->has('search')?'error':''}}" />
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
                            <th style="min-width: 200px;">Name</th>
                            <th style="min-width: 200px;">Company</th>
                            <th style="min-width: 150px;">Mobile</th>
                            <th style="min-width: 200px;">Email</th>
                            <th style="min-width: 100px;width:100px;">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($creditors as $creditor)
                        <tr>
                            <td>
                                {{$creditor->name}}
                                @if($creditor->status=='active')
                                <span style="color: #43d39e;font-size: 18px;">
                                    <i class="bx bx-check-circle"></i>
                                </span>
                                @else
                                <span style="color: #FF9800;font-size: 18px;">
                                    <i class="bx bx-analyse"></i>
                                </span>
                                @endif
                            </td>
                            <td>{{$creditor->company_name}}</td>
                            <td>{{$creditor->mobile}}</td>
                            <td>{{$creditor->email}}</td>
                            <td class="text-center">
                                @can('ac_creditors.edit')
                                <a href="javascript:void(0)" data-toggle="modal" data-target="#EditCreditor_{{$creditor->id}}" class="btn-custom success">
                                    <i class="bx bx-edit"></i>
                                </a>
                                @endcan
                                @can('ac_creditors.delete')
                                <form action="{{route('admin.creditors.destroy',$creditor->id)}}" method="post" style="display:inline-block;" onsubmit="return confirm('Are You Want To Delete?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn-custom danger" style="border:0;"><i class="bx bx-trash"></i></button>
                                </form>
                                @endcan
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" class="text-center text-muted"><em>No data found</em></td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
                {{$creditors->links('pagination::bootstrap-4')}}
            </div>
    </div>
</div>
</div>

<!-- Add Modal -->
 <div class="modal fade text-left" id="AddCreditor" tabindex="-1" role="dialog">
   <div class="modal-dialog" role="document">
	 <div class="modal-content">
	    <form action="{{route('admin.creditors.store')}}" method="post">
	   	  @csrf
    	   <div class="modal-header">
    		 <h4 class="modal-title">Add Creditor</h4>
    		 <button type="button" class="close" data-dismiss="modal" aria-label="Close">
    		   <span aria-hidden="true">&times; </span>
    		 </button>
    	   </div>
    	   <div class="modal-body">
    	   		<div class="form-group">
    			    <label for="name">Name* </label>
                    <input type="text" class="form-control {{$errors->has('name')?'error':''}}" name="name" placeholder="Enter Name" required="">
             	</div>
    			<div class="form-group">
    				<label for="name">Company Name</label>
                    <input type="text" class="form-control" name="company_name" placeholder="Enter Company Name">
             	</div>
    			<div class="form-group">
    				<label for="name">Mobile</label>
                    <input type="text" class="form-control" name="mobile" placeholder="Enter Mobile">
             	</div>
    			<div class="form-group">
    				<label for="name">Email</label>
                    <input type="email" class="form-control" name="email" placeholder="Enter Email">
             	</div>
    			<div class="form-group">
    				<label for="name">Address</label>
					<textarea name="address" class="form-control" placeholder="Enter Address"></textarea>
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
@foreach($creditors as $creditor)
 <div class="modal fade text-left" id="EditCreditor_{{$creditor->id}}" tabindex="-1" role="dialog">
   <div class="modal-dialog" role="document">
	 <div class="modal-content">
	 <form action="{{route('admin.creditors.update',$creditor->id)}}" method="post">
	   	  @csrf
	   	  @method('PUT')
    	   <div class="modal-header">
    		 <h4 class="modal-title">Edit Creditor</h4>
    		 <button type="button" class="close" data-dismiss="modal" aria-label="Close">
    		   <span aria-hidden="true">&times; </span>
    		 </button>
    	   </div>
    	   <div class="modal-body">
    	   		<div class="form-group">
    			    <label for="name">Name* </label>
                    <input type="text" class="form-control" value="{{$creditor->name}}" name="name" placeholder="Enter Name" required="">
             	</div>
    			<div class="form-group">
    				<label for="name">Company Name</label>
                    <input type="text" class="form-control" value="{{$creditor->company_name}}" name="company_name" placeholder="Enter Company Name">
             	</div>
    			<div class="form-group">
    				<label for="name">Mobile</label>
                    <input type="text" class="form-control" value="{{$creditor->mobile}}" name="mobile" placeholder="Enter Mobile">
             	</div>
    			<div class="form-group">
    				<label for="name">Email</label>
                    <input type="email" class="form-control" value="{{$creditor->email}}" name="email" placeholder="Enter Email">
             	</div>
    			<div class="form-group">
    				<label for="name">Address</label>
					<textarea name="address" class="form-control" placeholder="Enter Address">{{$creditor->address}}</textarea>
             	</div>
                <div class="form-group">
                    <label for="name">Status</label><br>
                    <div class="checkbox">
                        <input class="inp-cbx" id="status_{{$creditor->id}}" type="checkbox" name="status" style="display: none;" {{$creditor->status=='active'?'checked':''}} />
                        <label class="cbx" for="status_{{$creditor->id}}">
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
    	   <div class="modal-footer">
    		 <button type="button" class="btn grey btn-outline-secondary" data-dismiss="modal">Close </button>
    		 <button type="submit" class="btn btn-primary"><i class="bx bx-check"></i> Update Creditor</button>
    	   </div>
	   </form>
	 </div>
   </div>
 </div>
@endforeach

@endsection @push('js') @endpush
