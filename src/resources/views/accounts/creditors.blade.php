@extends(adminTheme().'layouts.app')
@section('title')
<title>{{websiteTitle('Creditor List')}}</title>
@endsection
@push('css')
<style type="text/css">
 @media (max-width: 1400px) {
        table tr td { font-size: 12px; }
        .table thead th { font-size: 14px; }
 }
</style>
@endpush
@section('contents')

<div class="flex-grow-1">
<!-- Start -->
<div class="card mb-30">
    <div class="card-header d-flex justify-content-between align-items-center">
         <h3>Creditor List</h3>
         <div class="dropdown">
            @can('ac_creditors.add')
             <a href="javascript:void(0)" class="btn-custom primary" data-toggle="modal" data-target="#AddCreditor">
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
               <div class="col-md-7 mb-1">
                   <div class="input-group">
                       <input type="date" name="startDate" value="{{request()->startDate?:''}}" class="form-control {{$errors->has('startDate')?'error':''}}" />
                       <input type="date" value="{{request()->endDate?:''}}" name="endDate" class="form-control {{$errors->has('endDate')?'error':''}}" />
                   </div>
               </div>
               <div class="col-md-5 mb-1">
                   <div class="input-group">
                       <input type="text" name="search" value="{{request()->search?:''}}" placeholder="Name, Email, Mobile, Company" class="form-control {{$errors->has('search')?'error':''}}" />
                       <button type="submit" class="btn btn-success btn-sm rounded-0">Search</button>
                   </div>
               </div>
           </div>
       </form>
        <br>
        <form action="{{route('admin.creditors.index')}}">
            <div class="row">
                <div class="col-md-4">
                    @if(can('ac_creditors.edit') || can('ac_creditors.delete'))
                    <div class="input-group mb-1">
                        <select class="form-control form-control-sm rounded-0" name="action" required="">
                            <option value="">Select Action</option>
                            @can('ac_creditors.edit')
                            <option value="1">Active</option>
                            <option value="2">Inactive</option>
                            @endcan
                            @can('ac_creditors.delete')
                            <option value="5">Delete</option>
                            @endcan
                        </select>
                        <button class="btn btn-sm btn-primary rounded-0" onclick="return confirm('Are You Want To Action?')">Action</button>
                    </div>
                    @endif
                </div>
                <div class="col-md-4"></div>
                <div class="col-md-4">
                    <ul class="statuslist">
                        <li><a href="{{route('admin.creditors.index')}}" class="{{request()->status?'':'active'}}" >All ({{$total->total}})</a></li>
                        <li><a href="{{route('admin.creditors.index',['status'=>'active'])}}" class="{{request()->status=='active'?'active':''}}" >Active ({{$total->active}})</a></li>
                        <li><a href="{{route('admin.creditors.index',['status'=>'inactive'])}}" class="{{request()->status=='inactive'?'active':''}}" >Inactive ({{$total->inactive}})</a></li>
                        @if($total->deleted > 0)
                            <li><a href="{{route('admin.creditors.index',['view'=>'deleted'])}}" class="text-danger" >Deleted ({{$total->deleted}})</a></li>
                        @endif
                    </ul>
                </div>
            </div>
            <div class="table-responsive">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th style="min-width: 100px; width: 100px;padding-right:0;">
                                 @if(can('ac_creditors.edit') || can('ac_creditors.delete'))
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
                            </th>
                            <th style="min-width: 200px; width: 200px;">Name</th>
                            <th style="min-width: 90px;">Code</th>
                            <th style="min-width: 150px;">Company</th>
                            <th style="min-width: 150px;">Mobile/Email</th>
                            <th style="min-width: 200px;">Address</th>
                            <th style="min-width: 100px;">Total Bill</th>
                            <th style="min-width: 100px;">Due Bill</th>
                            <th style="min-width: 100px;">Paid Bill</th>
                            <th style="min-width: 95px;">Join Date</th>
                            <th style="min-width: 80px; width: 180px;">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($creditors as $i=>$creditor)
                        @php
                            $totalBill = $creditor->bills()->sum('amount');
                            $totalPaid = $creditor->billPayments()->sum('amount');
                        @endphp
                        <tr>
                            <td style=" position: relative;">
                                @if(can('ac_creditors.edit') || can('ac_creditors.delete'))
                                <div class="checkbox">
                                     <input class="inp-cbx" id="cbx_{{$creditor->id}}" type="checkbox" name="checkid[]" value="{{$creditor->id}}" style="display: none;" />
                                     <label class="cbx" for="cbx_{{$creditor->id}}">
                                         <span>
                                             <svg width="12px" height="10px" viewbox="0 0 12 10">
                                                 <polyline points="1.5 6 4.5 9 10.5 1"></polyline>
                                             </svg>
                                         </span>
                                     </label>
                                 </div>
                                @endif
                                <span style="margin:0 5px;">{{$creditors->currentpage()==1?$i+1:$i+($creditors->perpage()*($creditors->currentpage() - 1))+1}}</span>
                                @if($creditor->status=='active')
                                <span style="color: #43d39e;font-size: 20px;line-height: 20px;position:absolute;">
                                    <i class="bx bx-check-circle"></i>
                                </span>
                                @else
                                <span style="color: #FF9800;font-size: 20px;line-height: 20px;position:absolute;">
                                    <i class="bx bx-analyse"></i>
                                </span>
                                @endif
                            </td>
                            <td>
                                <a href="{{route('admin.creditors.show',$creditor->id)}}" class="invoice-action-view mr-1">{{$creditor->name}}</a>
                            </td>
                            <td>{{$creditor->code}}</td>
                            <td>{{$creditor->company_name}}</td>
                            <td>{{$creditor->mobile?:$creditor->email}}</td>
                            <td>{{$creditor->address}}</td>
                            <td>{{priceFullFormat($totalBill)}}</td>
                            <td style="color:red;">{{priceFullFormat($totalBill - $totalPaid)}}</td>
                            <td>{{priceFullFormat($totalPaid)}}</td>
                            <td>{{$creditor->created_at->format('d M Y')}}</td>
                            <td style="padding: 8px 5px; text-align: center;">
                                @can('ac_creditors.edit')
                                <a href="javascript:void(0)" data-toggle="modal" data-target="#EditCreditor_{{$creditor->id}}" class="btn-custom success">
                                    <i class="bx bx-edit"></i>
                                </a>
                                @endcan
                                <a href="{{route('admin.creditors.show',$creditor->id)}}" class="btn-custom yellow">
                                    <i class="bx bx-credit-card"></i>
                                </a>
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
                            <td colspan="11" class="text-center text-muted"><em>No data found</em></td>
                        </tr>
                        @endforelse
                    </tbody>
                    @php
                        $footTotalBill = $creditors->sum(fn($c) => $c->bills()->sum('amount'));
                        $footTotalPaid = $creditors->sum(fn($c) => $c->billPayments()->sum('amount'));
                        $footTotalDue  = $footTotalBill - $footTotalPaid;
                    @endphp
                    <tfoot>
                        <tr>
                            <th colspan="6" class="text-right">Page Total:</th>
                            <th>{{ priceFullFormat($footTotalBill) }}</th>
                            <th style="color:red;">{{ priceFullFormat($footTotalDue) }}</th>
                            <th>{{ priceFullFormat($footTotalPaid) }}</th>
                            <th colspan="2"></th>
                        </tr>
                    </tfoot>
                </table>
            </div>
            {{ $creditors->links('pagination::bootstrap-4') }}
        </form>
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
            <div class="row">
                <div class="col-md-6 form-group">
                    <label for="name">Creditor Name* </label>
                    <input type="text" class="form-control {{$errors->has('name')?'error':''}}" name="name" placeholder="Enter Name" required="">
                </div>
                <div class="col-md-6 form-group">
                    <label for="code">Code</label>
                    <input type="text" class="form-control" name="code" placeholder="Enter Code">
                </div>
            </div>
            <div class="form-group">
                <label for="company_name">Company Name</label>
                <input type="text" class="form-control" name="company_name" placeholder="Enter Company Name">
            </div>
            <div class="form-group">
				<label for="name">Email/Mobile* </label>
                <input type="text" class="form-control {{$errors->has('email_mobile')?'error':''}}" name="email_mobile" placeholder="Enter Email/Mobile" required="">
            </div>
            <div class="form-group">
                <label for="address">Address Line</label>
                <input type="text" class="form-control" name="address" placeholder="Enter Address" />
            </div>
	   </div>
	   <div class="modal-footer">
		 <button type="button" class="btn grey btn-outline-secondary" data-dismiss="modal">Close </button>
		 <button type="submit" class="btn btn-primary"><i class="bx bx-plus"></i> Add Creditor</button>
	   </div>
	   </form>
	 </div>
   </div>
 </div>

<!-- Edit Modal -->
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
    			    <label for="code">Code</label>
                    <input type="text" class="form-control" value="{{$creditor->code}}" name="code" placeholder="Enter Code">
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

@endsection
@push('js')
@endpush
