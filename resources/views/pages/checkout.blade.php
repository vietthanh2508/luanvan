<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">

  <!-- CSRF Token -->
  <meta name="csrf-token" content="{{ csrf_token() }}">

  <title> Thanh Toán - {{ config('app.name') }} </title>
  <link rel="icon" href="{{ asset('images/favicon.png') }}" type="image/png">

  <!-- Fonts -->
  <link href="https://fonts.googleapis.com/css?family=Nunito:200,600" rel="stylesheet">

  <!-- Embed CSS -->
  <link rel="stylesheet" href="{{ asset('common/css/normalize.min.css') }}">
  <link rel="stylesheet" href="{{ asset('common/css/bootstrap.min.css') }}">
  <link rel="stylesheet" href="{{ asset('common/css/bootstrap-theme.min.css') }}">
  <link rel="stylesheet" href="{{ asset('common/css/animate.css') }}">
  <link rel="stylesheet" href="{{ asset('common/css/fontawesome/css/all.css') }}">
  <link rel="stylesheet" href="{{ asset('common/css/sweetalert2.min.css') }}">

  <!-- Custom CSS -->
  <link rel="stylesheet" href="{{ asset('css/checkout.css') }}">
</head>

<body>
  <!-- Site Content -->
  <div class="container-fluid">
    <div class="row">
      <div class="col-lg-8 col-md-7 col-sm-6 col-xs-12">
        <div class="col-header">
          <h2><a href="{{ route('home_page') }}">{{ config('app.name') }}</a></h2>
        </div>
        <div class="row">
          <div class="col-lg-10 col-md-10">
            <div class="col-title">
              <h3>Thông Tin Mua Hàng</h3>
            </div>
            <div class="form-checkout">
              {{-- <form action="{{ route('vnpay_payment') }}" method="POST" accept-charset="utf-8"
                buy-method="{{ $buy_method }}">
                <div class="form-group">
                  <label for="email">Email</label>
                  <input name="email" type="email" class="form-control @error('email') is-invalid @enderror" id="email"
                    autocomplete="email" value="{{ old('email') ?: Auth::user()->email }}" required>
                  <div class="messages"></div>
                </div>

                <div class="form-group">
                  <label for="name">Họ Và Tên</label>
                  <input name="name" type="text" class="form-control" id="name" autocomplete="name"
                    value="{{Auth::user()->name }}" required>
                  <div class="messages"></div>
                </div>

                <div class="form-group">
                  <label for="phone">Số Điện Thoại</label>
                  <input name="phone" type="tel" class="form-control" id="phone" autocomplete="phone"
                    value="{{ Auth::user()->phone }}" required>
                  <div class="messages"></div>
                </div>

                <div class="form-group">
                  <label for="address">Địa Chỉ</label>
                  <input name="address" type="text" class="form-control" id="address" autocomplete="address"
                    value="{{ Auth::user()->address }}" required>
                  <div class="messages"></div>
                </div>

                <div class="form-group">
                  <label for="note">Ghi Chú</label>
                  <textarea name="note" type="text" class="form-control" id="note" rows="3"></textarea>
                </div>
              </form> --}}


              <form action="{{ route('vnpay_payment') }}" method="POST">
                @csrf
                <div class="form-group">
                  <label for="email">Email</label>
                  <input name="email" type="email" class="form-control @error('email') is-invalid @enderror" id="email"
                    autocomplete="email" value="{{ old('email') ?: Auth::user()->email }}" required>
                  <div class="messages"></div>
                </div>

                <div class="form-group">
                  <label for="name">Họ Và Tên</label>
                  <input name="name" type="text" class="form-control" id="name" autocomplete="name"
                    value="{{Auth::user()->name }}" required>
                  <div class="messages"></div>
                </div>

                <div class="form-group">
                  <label for="phone">Số Điện Thoại Nhận Hàng</label>
                  <input name="phone" type="tel" class="form-control" id="phone" autocomplete="phone"
                    value="{{ Auth::user()->phone }}" required>
                  <div class="messages"></div>
                </div>

                <div class="form-group">
                  <label for="address">Địa Chỉ Nhận Hàng</label>
                  <input name="address" type="text" class="form-control" id="address" autocomplete="address"
                    value="{{ Auth::user()->address }}" required>
                  <div class="messages"></div>
                </div>

                <div class="form-group">
                  <label for="note">Ghi Chú</label>
                  <textarea name="note" type="text" class="form-control" id="note" rows="3"></textarea>
                </div>
                <div class="payment-methods">
                  <ul class="list-content">
                    @foreach($payment_methods as $key => $payment_method)
                    @if($key == 0)
                    <li class="active">
                      <label>
                        <input type="radio" value="{{ $payment_method->id }}" name="payment_method" checked>
                        {{ $payment_method->name }}
                      </label>
                      {{-- <div class="box-content">
                        <p>{{ $payment_method->describe }}</p>
                      </div> --}}
                    </li>
                    @elseif($key == 1)
                    <li class="active">
                      <label>
                        <input type="radio" value="{{ $payment_method->id }}" name="payment_method">
                        {{ $payment_method->name }}
                      </label>
                      {{-- <div class="box-content">
                        <p>{{ $payment_method->describe }}</p>
                      </div> --}}
                    </li>
                    @endif
                    @endforeach
                  </ul>
                </div>
                @foreach($cart->items as $item)
                @if($buy_method=='buy_now')
                
                <div>
                  <input name="buy_method" type="hidden" value="buy_now">
                  <input type="hidden" value="{{$item['item']->id}}" name="product_id">
                  <input type="hidden" value="{{$item['price']}}" name="price">
                  <input type="hidden" value="{{$cart->totalPrice}}" name="totalPrice">
                  <input type="hidden" value="{{$item['qty']}}" name="qty">
                </div>
                
                @else
                <div>
                  <input name="buy_method" type="hidden" value="buy_cart">
                  <input type="hidden" value="{{$cart->totalPrice}}" name="totalPrice">
                  {{-- @foreach($cart->items as $item) --}}
                  <input type="hidden" value="{{$item['price']}}" name="price">
                  
                </div>
                @endif
                @endforeach
                <button type="submit" name="redirect" class="btn btn-info">Thanh toán</button>
                
                {{-- <button>
                  <a href="{{route('payment')}}"> Thanh toán Ship COD</a>
                </button> --}}
              </form>

            </div>
            <div>
            </div>
          </div>
          {{-- <div class="col-lg-6 col-md-6">
            <div class="col-title margin-bottom34">
              <h3>Phương Thức Thanh Toán</h3>
            </div>
            <div class="col-content"> --}}
              {{-- <div class="payment-methods">
                <ul class="list-content">
                  @foreach($payment_methods as $key => $payment_method)
                  @if($key == 0)
                  <li class="active">
                    <label>
                      <input type="radio" value="{{ $payment_method->id }}" name="payment_method" checked>
                      {{ $payment_method->name }}
                    </label>
                    <div class="box-content">
                      <p>{{ $payment_method->describe }}</p>
                    </div>
                  </li>
                  @elseif($key == 1)
                  <li class="active">
                    <label>
                      <input type="radio" value="{{ $payment_method->id }}" name="payment_method">
                      {{ $payment_method->name }}
                    </label>
                    <div class="box-content">
                      <p>{{ $payment_method->describe }}</p>
                    </div>
                  </li>
                  @endif
                  @endforeach
                </ul>
              </div> --}}
              {{-- </div>
          </div> --}}
        </div>
      </div>

      <div class="col-lg-4 col-md-5 col-sm-6 col-xs-12">
        <div class="col-order">
          <div class="col-header">
            <h2>Đơn Hàng <span data-qty="{{ $cart->totalQty }}">( {{ $cart->totalQty }} Sản Phẩm )</span></h2>
          </div>
          <div class="col-content">
            <div class="section-items">
              @foreach($cart->items as $item)
              <div class="item" data-product="{{ $item['item']->id }}" data-price="{{ $item['price'] }}">
                <div class="image-item">
                  <img src="{{ Helper::get_image_product_url($item['item']->product->image) }}">
                  <span>{{ $item['qty'] }}</span>

                </div>
                <div class="info">
                  <div class="name">{{ $item['item']->product->name }}</div>
                  <div class="color">{{ $item['item']->color }}</div>
                </div>
                <div class="price">{{ number_format($item['price'],0,',','.') }}₫</div>
              </div>
              @endforeach
            </div>
            <div class="section-price">
              <div class="temp-total-price">
                <div class="title">Tạm Tính</div>
                <div class="price">{{ number_format($cart->totalPrice,0,',','.') }}₫</div>
              </div>
              <div class="ship-price">
                <div class="title">Phí Vận Chuyển</div>
                <div class="price">0₫</div>
              </div>
              <div class="total-price">
                <div class="title">Tổng Cộng</div>
                <div class="price" name="total_price" data-price="{{ $cart->totalPrice }}">{{
                  number_format($cart->totalPrice,0,',','.') }}₫</div>
              </div>
            </div>
            {{-- <div class="btn-order">
              <div>
                <form action="{{ route('vnpay_payment') }}" method="POST">
                  @csrf
                  @if($buy_method=='buy_now')
                  <input name="buy_method" type="text" value="buy_now">
                  <input type="text" value="{{$item['item']->id}}" name="product_id">

                  <input type="text" value="{{$item['price']}}" name="price">
                  <input type="text" value="{{$cart->totalPrice}}" name="totalPrice">
                  <input name="name" type="hidden" class="form-control" id="name" autocomplete="name"
                    value="{{Auth::user()->name }}" required>
                  <input name="email" type="hidden" class="form-control @error('email') is-invalid @enderror" id="email"
                    autocomplete="email" value="{{ old('email') ?: Auth::user()->email }}" required>
                  <input type="text" value="{{$item['qty']}}" name="qty">
                  @else
                  <input name="buy_method" type="text" value="buy_cart">
                  <input type="text" value="{{$cart->totalPrice}}" name="totalPrice">

                  @foreach($cart->items as $item)
                  <input type="text" value="{{$item['price']}}" name="price">

                  <input name="name" type="hidden" class="form-control" id="name" autocomplete="name"
                    value="{{Auth::user()->name }}" required>
                  <input name="email" type="hidden" class="form-control @error('email') is-invalid @enderror" id="email"
                    autocomplete="email" value="{{ old('email') ?: Auth::user()->email }}" required>
                  <input type="text" value="{{$item['qty']}}" name="qty">

                  @endforeach
                  @endif
                  <div class="form-group">
                    <label for="phone">Số Điện Thoại Giao hàng</label>
                    <input name="phone" type="tel" class="form-control" id="phone" autocomplete="phone" required>
                    <div class="messages"></div>
                  </div>

                  <div class="form-group">
                    <label for="address">Địa Chỉ giao hàng</label>
                    <input name="address" type="text" class="form-control" id="address" autocomplete="address" required>
                    <div class="messages"></div>
                  </div>


                  <button type="submit" name="redirect" class="btn btn-danger">Thanh toán VN Pay</button>
                </form>
                @foreach($cart->items as $item)
                <div>{{dd($cart)}}</div>
                @endforeach
              </div>
              </br>
              <div>
              </div>
            </div> --}}
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- Embed Scripts -->
  <script src="{{ asset('common/js/jquery-3.3.1.js') }}"></script>
  <script src="{{ asset('common/js/bootstrap.min.js') }}"></script>
  <script src="{{ asset('common/js/sweetalert2.min.js') }}"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/underscore.js/1.8.3/underscore-min.js"></script>
  <script src="//cdnjs.cloudflare.com/ajax/libs/validate.js/0.13.1/validate.min.js"></script>

  <!-- Custom Scripts -->
  <script src="{{ asset('js/checkout.js') }}"></script>
</body>

</html>