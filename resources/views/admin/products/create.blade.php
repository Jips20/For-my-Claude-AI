@extends('admin.layouts.app')
@push('page-css')
    
@endpush    
@push('page-header')
<div class="col-sm-12">
	<h3 class="page-title">Add Product</h3>
	<ul class="breadcrumb">
		<li class="breadcrumb-item"><a href="{{route('dashboard')}}">Dashboard</a></li>
		<li class="breadcrumb-item active">Add Product</li>
	</ul>
</div>
@endpush
<!-- Visit codeastro.com for more projects -->
@section('content')
<div class="row">
	<div class="col-sm-12">
		<div class="card">
			<div class="card-body custom-edit-service">
                <!-- Add Product -->
                <form method="post" enctype="multipart/form-data" id="update_service" action="{{route('products.store')}}">
                    @csrf
                    <div class="service-fields mb-3">
                        <div class="row">
                            
                            <div class="col-lg-12">
                                <div class="form-group">
                                    <label>Product <span class="text-danger">*</span></label>
                                    <select class="select2 form-select form-control" name="product"> 
                                        @foreach ($purchases as $purchase)
                                            <option value="{{$purchase->id}}">{{$purchase->product}}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="service-fields mb-3">
                        <div class="row">
                            <div class="col-lg-6">
                                <div class="form-group">
                                    <label>Selling Price<span class="text-danger">*</span></label>
                                    <input class="form-control" type="text" name="price" id="selling_price" value="{{old('price')}}" maxlength="5">
                                </div>
                            </div>
                            <div class="col-lg-6">
                                <div class="form-group">
                                    <label>Margin (%)<span class="text-danger">*</span></label>
                                    <input class="form-control" type="text" name="discount" id="margin" value="0" maxlength="2">
                                </div>
                            </div>
                            
                        </div>
                    </div>
                                    
                    
                    <div class="service-fields mb-3">
                        <div class="row">
                            <div class="col-lg-12">
                                <div class="form-group">
                                    <label>Descriptions <span class="text-danger">*</span></label>
                                    <textarea class="form-control service-desc" name="description" id="description" maxlength="300">{{old('description')}}</textarea>
                                </div>
                            </div>
                            
                        </div>
                    </div>
                    
                    
                    <div class="submit-section">
                        <button class="btn btn-success submit-btn" type="submit" name="form_submit" value="submit">Submit</button>
                    </div>
                </form>
                <!-- /Add Product -->
			</div>
		</div>
	</div>			
</div>
@endsection

@push('page-js')
	<script>
		// Selling Price: numbers, period, max 5
		document.getElementById('selling_price').addEventListener('input', function() {
			this.value = this.value.replace(/[^0-9.]/g, '').slice(0, 5);
		});

		// Margin: numbers, period, max 2
		document.getElementById('margin').addEventListener('input', function() {
			this.value = this.value.replace(/[^0-9.]/g, '').slice(0, 2);
		});

		// Description: letters, numbers, space, max 300
		document.getElementById('description').addEventListener('input', function() {
			this.value = this.value.replace(/[^a-zA-Z0-9\s]/g, '').slice(0, 300);
		});
	</script>
@endpush