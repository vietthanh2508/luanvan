@extends('admin.layouts.master')

@section('title', 'Tạo Hãng Mới')

@section('embed-css')
  <!-- daterange picker -->
  <link rel="stylesheet" href="{{ asset('AdminLTE/bower_components/bootstrap-daterangepicker/daterangepicker.css') }}">
@endsection

@section('custom-css')

@endsection

@section('breadcrumb')
<ol class="breadcrumb">
  <li><a href="{{ route('admin.dashboard') }}"><i class="fa fa-dashboard"></i> Home</a></li>
  <li><a href="{{ route('admin.producer.index') }}"><i class="fa fa-sliders" aria-hidden="true"></i> Quản Lý Nhà Sản Xuất</a></li>
  <li class="active">Tạo Hãng Mới</li>
</ol>
@endsection

@section('content')


@if ($errors->any())
  <div class="callout callout-danger">
    <h4>Warning!</h4>
    <ul style="margin-bottom: 0;">
      @foreach ($errors->all() as $error)
        <li>{{ $error }}</li>
      @endforeach
    </ul>
  </div>
@endif

<form action="{{ route('admin.producer.save') }}" method="POST" accept-charset="utf-8" enctype="multipart/form-data">
  @csrf
  <div class="box box-primary">
    <div class="box-body">
      <div class="row">
        <div class="col-md-9">
          <div class="form-group">
            <label for="title">Tên hãng <span class="text-red">*</span></label>
            <input type="text" name="name" class="form-control" id="title" placeholder="Tên hãng" value="{{ old('name') }}" autocomplete="off">
          </div>
          <div class="form-group">
            <button type="submit" class="btn btn-success btn-flat pull-right"><i class="fa fa-floppy-o" aria-hidden="true"></i> Lưu</button>
            <a href="{{ route('admin.producer.index') }}" class="btn btn-danger btn-flat pull-right" style="margin-right: 5px;"><i class="fa fa-ban" aria-hidden="true"></i> Hủy</a>
          </div>
        </div>
      </div>
    </div>
  </div>
</form>
@endsection

@section('embed-js')

<!-- date-range-picker -->
<script src="{{ asset('AdminLTE/bower_components/moment/min/moment.min.js') }}"></script>
<script src="{{ asset('AdminLTE/bower_components/bootstrap-daterangepicker/daterangepicker.js') }}"></script>
@endsection

