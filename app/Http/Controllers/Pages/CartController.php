<?php

namespace App\Http\Controllers\Pages;

use App\Http\Controllers\Controller;
use App\Models\Advertise;
use App\Models\Cart;
use App\Models\Order;
use App\Models\OrderDetail;
use App\Models\PaymentMethod;
use App\Models\ProductDetail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Str;

class CartController extends Controller
{
    public function addCart(Request $request)
    {

        $product = ProductDetail::where('id', $request->id)
            ->with(['product' => function ($query) {
                $query->select('id', 'name', 'image', 'sku_code', 'RAM', 'ROM');
            }])->select('id', 'product_id', 'color', 'quantity', 'sale_price', 'promotion_price', 'promotion_start_date', 'promotion_end_date')->first();

        if (!$product) {
            $data['msg'] = 'Product Not Found!';
            return response()->json($data, 404);
        }

        $oldCart = Session::has('cart') ? Session::get('cart') : null;
        $cart = new Cart($oldCart);
        if (!$cart->add($product, $product->id, $request->qty)) {
            $data['msg'] = 'Số lượng sản phẩm trong giỏ vượt quá số lượng sản phẩm trong kho!';
            return response()->json($data, 412);
        }
        Session::put('cart', $cart);

        $data['msg'] = "Thêm giỏ hàng thành công";
        $data['url'] = route('home_page');
        $data['response'] = Session::get('cart');

        return response()->json($data, 200);
    }

    public function removeCart(Request $request)
    {

        $oldCart = Session::has('cart') ? Session::get('cart') : null;
        $cart = new Cart($oldCart);

        if (!$cart->remove($request->id)) {
            $data['msg'] = 'Sản Phẩm không tồn tại!';
            return response()->json($data, 404);
        } else {
            Session::put('cart', $cart);

            $data['msg'] = "Xóa sản phẩm thành công";
            $data['url'] = route('home_page');
            $data['response'] = Session::get('cart');

            return response()->json($data, 200);
        }
    }

    public function updateCart(Request $request)
    {
        $oldCart = Session::has('cart') ? Session::get('cart') : null;
        $cart = new Cart($oldCart);
        if (!$cart->updateItem($request->id, $request->qty)) {
            $data['msg'] = 'Số lượng sản phẩm trong giỏ vượt quá số lượng sản phẩm trong kho!';
            return response()->json($data, 412);
        }
        Session::put('cart', $cart);

        $response = array(
            'id' => $request->id,
            'qty' => $cart->items[$request->id]['qty'],
            'price' => $cart->items[$request->id]['price'],
            'salePrice' => $cart->items[$request->id]['item']->sale_price,
            'totalPrice' => $cart->totalPrice,
            'totalQty' => $cart->totalQty,
            'maxQty' => $cart->items[$request->id]['item']->quantity,
        );
        $data['response'] = $response;
        return response()->json($data, 200);
    }

    public function updateMiniCart(Request $request)
    {
        $oldCart = Session::has('cart') ? Session::get('cart') : null;
        $cart = new Cart($oldCart);
        if (!$cart->updateItem($request->id, $request->qty)) {
            $data['msg'] = 'Số lượng sản phẩm trong giỏ vượt quá số lượng sản phẩm trong kho!';
            return response()->json($data, 412);
        }
        Session::put('cart', $cart);

        $response = array(
            'id' => $request->id,
            'qty' => $cart->items[$request->id]['qty'],
            'price' => $cart->items[$request->id]['price'],
            'totalPrice' => $cart->totalPrice,
            'totalQty' => $cart->totalQty,
            'maxQty' => $cart->items[$request->id]['item']->quantity,
        );
        $data['response'] = $response;
        return response()->json($data, 200);
    }

    public function showCart()
    {

        $advertises = Advertise::where([
            ['start_date', '<=', date('Y-m-d')],
            ['end_date', '>=', date('Y-m-d')],
            ['at_home_page', '=', false],
        ])->latest()->limit(5)->get(['product_id', 'title', 'image']);

        $oldCart = Session::has('cart') ? Session::get('cart') : null;
        $cart = new Cart($oldCart);

        return view('pages.cart')->with(['cart' => $cart, 'advertises' => $advertises]);
    }

    public function showCheckout(Request $request)
    {
        if (Auth::check() && !Auth::user()->admin) {
            if ($request->has('type') && $request->type == 'buy_now') {
                $payment_methods = PaymentMethod::select('id', 'name', 'describe')->get();
                $product_detail = ProductDetail::select('id')->get();
                $product = ProductDetail::where('id', $request->id)
                    ->with(['product' => function ($query) {
                        $query->select('id', 'name', 'image', 'sku_code', 'RAM', 'ROM');
                    }])->select('id', 'product_id', 'color', 'quantity', 'sale_price', 'promotion_price', 'promotion_start_date', 'promotion_end_date')->first();
                $cart = new Cart(null);
                if (!$cart->add($product, $product->id, $request->qty)) {
                    return back()->with(['alert' => [
                        'type' => 'warning',
                        'title' => 'Thông Báo',
                        'content' => 'Số lượng sản phẩm trong giỏ vượt quá số lượng sản phẩm trong kho!',
                    ]]);
                }
                return view('pages.checkout')->with(['product' => $product, 'cart' => $cart, 'payment_methods' => $payment_methods, 'buy_method' => $request->type]);
            } elseif ($request->has('type') && $request->type == 'buy_cart') {

                $payment_methods = PaymentMethod::select('id', 'name', 'describe')->get();
                $oldCart = Session::has('cart') ? Session::get('cart') : null;
                $cart = new Cart($oldCart);
                $cart->update();
                Session::put('cart', $cart);
                return view('pages.checkout')->with(['cart' => $cart, 'payment_methods' => $payment_methods, 'buy_method' => $request->type]);
            }
        } elseif (Auth::check() && Auth::user()->admin) {
            return redirect()->route('home_page')->with(['alert' => [
                'type' => 'error',
                'title' => 'Thông Báo',
                'content' => 'Bạn không có quyền truy cập vào trang này!',
            ]]);
        } else {
            return redirect()->route('login')->with(['alert' => [
                'type' => 'info',
                'title' => 'Thông Báo',
                'content' => 'Bạn hãy đăng nhập để mua hàng!',
            ]]);
        }
    }

    public function vnpay_payment(Request $request)
    {
        if ($request->payment_method == 2) {
            if ($request->buy_method == 'buy_cart') {
                $cart = Session::get('cart');
                $order = new Order;
                $order->user_id = Auth::user()->id;
                $order->payment_method_id = 2;
                $order->order_code = 'PSO' . str_pad(rand(0, pow(10, 5) - 1), 5, '0', STR_PAD_LEFT);
                $order->name = $request->name;
                $order->email = $request->email;
                $order->phone = $request->phone;
                $order->address = $request->address;
                $order->status = 1;
                $order->save();

                foreach ($cart->items as $key => $item) {
                    $order_details = new OrderDetail;
                    $order_details->order_id = $order->id;
                    $order_details->product_detail_id = $item['item']->id;
                    $order_details->quantity = $item['qty'];
                    $order_details->price = $item['price'];
                    $order_details->save();

                    $product = ProductDetail::find($item['item']->id);
                    $product->quantity = $product->quantity - $item['qty'];
                    $product->save();
                }           
            } elseif ($request->buy_method == 'buy_now') {
                $order = new Order;
                $order->user_id = Auth::user()->id;
                $order->payment_method_id = 2;
                $order->order_code = 'PSO' . str_pad(rand(0, pow(10, 5) - 1), 5, '0', STR_PAD_LEFT);
                $order->name = $request->name;
                $order->email = $request->email;
                $order->phone = $request->phone;
                $order->address = $request->address;
                $order->status = 1;
                $order->save();

                $order_details = new OrderDetail;
                $order_details->order_id = $order->id;
                $order_details->product_detail_id = $request->product_id;
                $order_details->quantity = $request->qty;
                $order_details->price = $request->price;
                $order_details->save();

                $product = ProductDetail::find($request->product_id);
                $product->quantity = $product->quantity - $request->qty;
                $product->save();
            }

            //echo ($request->payment_method);
            $vnp_Url = "https://sandbox.vnpayment.vn/paymentv2/vpcpay.html";
            // $vnp_Returnurl = "http://127.0.0.1:8000/orders";
            $vnp_Returnurl = url('vnpay_return');

            $vnp_TmnCode = "IXLX9GVI"; //Mã website tại VNPAY
            $vnp_HashSecret = "TMBQZK5TX24V382CIO5EBOH39O32UDA7"; //Chuỗi bí mật

            $vnp_TxnRef = 'PSO' . str_pad(rand(0, pow(10, 5) - 1), 5, '0', STR_PAD_LEFT); //Mã đơn hàng. Trong thực tế Merchant cần insert đơn hàng vào DB và gửi mã này sang VNPAY
            $vnp_OrderInfo = 'thanh toan vnpay';
            $vnp_OrderType = 'bill payment';
            $vnp_Amount = $request->totalPrice * 100;
            $vnp_Locale = 'vn';
            $vnp_BankCode = 'NCB';
            $vnp_IpAddr = $_SERVER['REMOTE_ADDR'];
            //Add Params of 2.0.1 Version

            $inputData = array(
                "vnp_Version" => "2.1.0",
                "vnp_TmnCode" => $vnp_TmnCode,
                "vnp_Amount" => $vnp_Amount,
                "vnp_Command" => "pay",
                "vnp_CreateDate" => date('YmdHis'),
                "vnp_CurrCode" => "VND",
                "vnp_IpAddr" => $vnp_IpAddr,
                "vnp_Locale" => $vnp_Locale,
                "vnp_OrderInfo" => $vnp_OrderInfo,
                "vnp_OrderType" => $vnp_OrderType,
                "vnp_ReturnUrl" => $vnp_Returnurl,
                "vnp_TxnRef" => $vnp_TxnRef,
            );

            if (isset($vnp_BankCode) && $vnp_BankCode != "") {
                $inputData['vnp_BankCode'] = $vnp_BankCode;
            }
            if (isset($vnp_Bill_State) && $vnp_Bill_State != "") {
                $inputData['vnp_Bill_State'] = $vnp_Bill_State;
            }

            //var_dump($inputData);
            ksort($inputData);
            $query = "";
            $i = 0;
            $hashdata = "";
            foreach ($inputData as $key => $value) {
                if ($i == 1) {
                    $hashdata .= '&' . urlencode($key) . "=" . urlencode($value);
                } else {
                    $hashdata .= urlencode($key) . "=" . urlencode($value);
                    $i = 1;
                }
                $query .= urlencode($key) . "=" . urlencode($value) . '&';
            }

            $vnp_Url = $vnp_Url . "?" . $query;
            if (isset($vnp_HashSecret)) {
                $vnpSecureHash = hash_hmac('sha512', $hashdata, $vnp_HashSecret); //
                $vnp_Url .= 'vnp_SecureHash=' . $vnpSecureHash;
            }
            $returnData = array('code' => '00'
                , 'message' => 'success'
                , 'data' => $vnp_Url);
            if (isset($_POST['redirect'])) {
                header('Location: ' . $vnp_Url);
                die();
            } else {
                echo json_encode($returnData);
                // vui lòng tham khảo thêm tại code dem
            }
        } else {
            if ($request->buy_method == 'buy_cart') {
                $cart = Session::get('cart');
                $order = new Order;
                $order->user_id = Auth::user()->id;
                $order->payment_method_id = 2;
                $order->order_code = 'PSO' . str_pad(rand(0, pow(10, 5) - 1), 5, '0', STR_PAD_LEFT);
                $order->name = $request->name;
                $order->email = $request->email;
                $order->phone = $request->phone;
                $order->address = $request->address;
                $order->status = 1;
                $order->save();

                foreach ($cart->items as $key => $item) {
                    $order_details = new OrderDetail;
                    $order_details->order_id = $order->id;
                    $order_details->product_detail_id = $item['item']->id;
                    $order_details->quantity = $item['qty'];
                    $order_details->price = $item['price'];
                    $order_details->save();

                    $product = ProductDetail::find($item['item']->id);
                    $product->quantity = $product->quantity - $item['qty'];
                    $product->save();
                }
                Session::forget('cart');
                return redirect()->route('home_page')->with(['alert' => [
                    'type' => 'success',
                    'title' => 'Mua hàng thành công',
                    'content' => 'Cảm ơn bạn đã tin tưởng và sử dụng dịch vụ của chúng tôi. Sản phẩm của bạn sẽ được chuyển đến trong thời gian sớm nhất.',
                ]]);

            } elseif ($request->buy_method == 'buy_now') {
                $order = new Order;
                $order->user_id = Auth::user()->id;
                $order->payment_method_id = 2;
                $order->order_code = 'PSO' . str_pad(rand(0, pow(10, 5) - 1), 5, '0', STR_PAD_LEFT);
                $order->name = $request->name;
                $order->email = $request->email;
                $order->phone = $request->phone;
                $order->address = $request->address;
                $order->status = 1;
                $order->save();

                $order_details = new OrderDetail;
                $order_details->order_id = $order->id;
                $order_details->product_detail_id = $request->product_id;
                $order_details->quantity = $request->qty;
                $order_details->price = $request->price;
                $order_details->save();

                $product = ProductDetail::find($request->product_id);
                $product->quantity = $product->quantity - $request->qty;
                $product->save();
                return redirect()->route('home_page')->with(['alert' => [
                    'type' => 'success',
                    'title' => 'Mua hàng thành công',
                    'content' => 'Cảm ơn bạn đã tin tưởng và sử dụng dịch vụ của chúng tôi. Sản phẩm của bạn sẽ được chuyển đến trong thời gian sớm nhất.',
                ]]);
            }
        }
    }

    public function vnpay_return(Request $request)
    {
        // dd($request->toArray());
        $vnp_Url = "https://sandbox.vnpayment.vn/paymentv2/vpcpay.html";
        // $vnp_Returnurl = url('http://127.0.0.1:8000/orders');
        $vnp_TmnCode = "IXLX9GVI"; //Mã website tại VNPAY RNJWDMJ8
        $vnp_HashSecret = "TMBQZK5TX24V382CIO5EBOH39O32UDA7"; //Chuỗi bí mậtPJBWDVVKUUPZANMEWEKTEOIWAKUATCRX
        $vnp_apiUrl = "http://sandbox.vnpayment.vn/merchant_webapi/merchant.html";
        $apiUrl = "https://sandbox.vnpayment.vn/merchant_webapi/api/transaction";

        $vnp_SecureHash = $_GET['vnp_SecureHash'];
        $inputData = array();
        foreach ($_GET as $key => $value) {
            if (substr($key, 0, 4) == "vnp_") {
                $inputData[$key] = $value;
            }
        }

        unset($inputData['vnp_SecureHash']);
        ksort($inputData);
        $i = 0;
        $hashData = "";
        foreach ($inputData as $key => $value) {
            if ($i == 1) {
                $hashData = $hashData . '&' . urlencode($key) . "=" . urlencode($value);
            } else {
                $hashData = $hashData . urlencode($key) . "=" . urlencode($value);
                $i = 1;
            }
        }

        $secureHash = hash_hmac('sha512', $hashData, $vnp_HashSecret);
        if ($secureHash == $vnp_SecureHash) {
            Session::forget('cart');
            if ($_GET['vnp_ResponseCode'] == '00') {             
                return redirect()->route('home_page')->with(['alert' => [
                    'type' => 'success',
                    'title' => 'Mua hàng thành công',
                    'content' => 'Cảm ơn bạn đã tin tưởng và sử dụng dịch vụ của chúng tôi. Sản phẩm của bạn sẽ được chuyển đến trong thời gian sớm nhất.',
                ]]);
            } else {
                return redirect()->route('home_page')->with(['alert' => [
                    'type' => 'failed',
                    'title' => 'Mua hàng không thành công',
                    'content' => 'Thanh toán không thành công. Bạn hãy tiếp tục mua sắm !.',
                ]]);
            }
        } else {
            return redirect()->route('home_page')->with(['alert' => [
                'type' => 'error',
                'title' => 'Mua hàng không thành công',
                'content' => 'Lỗi thanh toán.',
            ]]);
        }
    }

    public function payment(Request $request)
    {
        $data = $request->all();
        $payment_method = PaymentMethod::select('id', 'name')->where('id', $request->payment_method)->first();
        if (Str::contains($payment_method->name, 'COD')) {
            if ($request->buy_method == 'buy_now') {
                $order = new Order;
                $order->user_id = Auth::user()->id;
                $order->payment_method_id = $request->payment_method;
                $order->order_code = 'PSO' . str_pad(rand(0, pow(10, 5) - 1), 5, '0', STR_PAD_LEFT);
                $order->name = $request->name;
                $order->email = $request->email;
                $order->phone = $request->phone;
                $order->address = $request->address;
                $order->status = 1;
                $order->save();

                $order_details = new OrderDetail;
                $order_details->order_id = $order->id;
                $order_details->product_detail_id = $request->product_id;
                $order_details->quantity = $request->totalQty;
                $order_details->price = $request->price;
                $order_details->save();

                $product = ProductDetail::find($request->product_id);
                $product->quantity = $product->quantity - $request->totalQty;
                $product->save();

                return redirect()->route('home_page')->with(['alert' => [
                    'type' => 'success',
                    'title' => 'Mua hàng thành công',
                    'content' => 'Cảm ơn bạn đã tin tưởng và sử dụng dịch vụ của chúng tôi. Sản phẩm của bạn sẽ được chuyển đến trong thời gian sớm nhất.',
                ]]);
            } elseif ($request->buy_method == 'buy_cart') {
                $cart = Session::get('cart');

                $order = new Order;
                $order->user_id = Auth::user()->id;
                $order->payment_method_id = $request->payment_method;
                $order->order_code = 'PSO' . str_pad(rand(0, pow(10, 5) - 1), 5, '0', STR_PAD_LEFT);
                $order->name = $request->name;
                $order->email = $request->email;
                $order->phone = $request->phone;
                $order->address = $request->address;
                $order->status = 1;
                $order->save();

                foreach ($cart->items as $key => $item) {
                    $order_details = new OrderDetail;
                    $order_details->order_id = $order->id;
                    $order_details->product_detail_id = $item['item']->id;
                    $order_details->quantity = $item['qty'];
                    $order_details->price = $item['price'];
                    $order_details->save();

                    $product = ProductDetail::find($item['item']->id);
                    $product->quantity = $product->quantity - $item['qty'];
                    $product->save();
                }
                Session::forget('cart');
                return redirect()->route('home_page')->with(['alert' => [
                    'type' => 'success',
                    'title' => 'Mua hàng thành công',
                    'content' => 'Cảm ơn bạn đã tin tưởng và sử dụng dịch vụ của chúng tôi. Sản phẩm của bạn sẽ được chuyển đến trong thời gian sớm nhất.',
                ]]);
            }
        } elseif (Str::contains($payment_method->name, 'VNPAY')) {
            if ($request->buy_method == 'buy_now') {
                // $vnp_Url = "https://sandbox.vnpayment.vn/paymentv2/vpcpay.html";
                // $vnp_Returnurl = url('vnpay_payment');
                // $vnp_TmnCode = "IXLX9GVI"; //Mã website tại VNPAY
                // $vnp_HashSecret = "TMBQZK5TX24V382CIO5EBOH39O32UDA7"; //Chuỗi bí mật

                // $vnp_TxnRef = 'PSO' . str_pad(rand(0, pow(10, 5) - 1), 5, '0', STR_PAD_LEFT); //Mã đơn hàng. Trong thực tế Merchant cần insert đơn hàng vào DB và gửi mã này sang VNPAY
                // $vnp_OrderInfo = 'thanh toan vnpay';
                // $vnp_OrderType = 'bill payment';
                // $vnp_Amount = $request->price * 100;
                // $vnp_Locale = 'vn';
                // $vnp_BankCode = 'NCB';
                // $vnp_IpAddr = $_SERVER['REMOTE_ADDR'];
                // Add Params of 2.0.1 Version

                //     $inputData = array(
                //         "vnp_Version" => "2.1.0",
                //         "vnp_TmnCode" => $vnp_TmnCode,
                //         "vnp_Amount" => $vnp_Amount,
                //         "vnp_Command" => "pay",
                //         "vnp_CreateDate" => date('YmdHis'),
                //         "vnp_CurrCode" => "VND",
                //         "vnp_IpAddr" => $vnp_IpAddr,
                //         "vnp_Locale" => $vnp_Locale,
                //         "vnp_OrderInfo" => $vnp_OrderInfo,
                //         "vnp_OrderType" => $vnp_OrderType,
                //         "vnp_ReturnUrl" => $vnp_Returnurl,
                //         "vnp_TxnRef" => $vnp_TxnRef,
                //     );

                //     if (isset($vnp_BankCode) && $vnp_BankCode != "") {
                //         $inputData['vnp_BankCode'] = $vnp_BankCode;
                //     }
                //     if (isset($vnp_Bill_State) && $vnp_Bill_State != "") {
                //         $inputData['vnp_Bill_State'] = $vnp_Bill_State;
                //     }

                //     var_dump($inputData);
                //     ksort($inputData);
                //     $query = "";
                //     $i = 0;
                //     $hashdata = "";
                //     foreach ($inputData as $key => $value) {
                //         if ($i == 1) {
                //             $hashdata .= '&' . urlencode($key) . "=" . urlencode($value);
                //         } else {
                //             $hashdata .= urlencode($key) . "=" . urlencode($value);
                //             $i = 1;
                //         }
                //         $query .= urlencode($key) . "=" . urlencode($value) . '&';
                //     }

                //     $vnp_Url = $vnp_Url . "?" . $query;
                //     if (isset($vnp_HashSecret)) {
                //         $vnpSecureHash = hash_hmac('sha512', $hashdata, $vnp_HashSecret); //
                //         $vnp_Url .= 'vnp_SecureHash=' . $vnpSecureHash;
                //     }
                //     $returnData = array('code' => '00'
                //         , 'message' => 'success'
                //         , 'data' => $vnp_Url);
                //     if (isset($_POST['redirect'])) {
                //         header('Location: ' . $vnp_Url);
                //         die();
                //     } else {
                //         echo json_encode($returnData);
                //     }
                // }
                $order = new Order;
                $order->user_id = Auth::user()->id;
                $order->payment_method_id = $request->payment_method;
                $order->order_code = 'PSO' . str_pad(rand(0, pow(10, 5) - 1), 5, '0', STR_PAD_LEFT);
                $order->name = $request->name;
                $order->email = $request->email;
                $order->phone = $request->phone;
                $order->address = $request->address;
                $order->status = 1;
                $order->save();

                $order_details = new OrderDetail;
                $order_details->order_id = $order->id;
                $order_details->product_detail_id = $request->product_id;
                $order_details->quantity = $request->totalQty;
                $order_details->price = $request->price;
                $order_details->save();

                $product = ProductDetail::find($request->product_id);
                $product->quantity = $product->quantity - $request->totalQty;
                $product->save();

                // return redirect()->route('orders_page')->with(['alert' => [
                //     'type' => 'success',
                //     'title' => 'Mua hàng thành công',
                //     'content' => 'Cảm ơn bạn đã tin tưởng và sử dụng dịch vụ của chúng tôi. Sản phẩm của bạn sẽ được chuyển đến trong thời gian sớm nhất.',
                // ]]);
                //       $vnp_Url = "https://sandbox.vnpayment.vn/paymentv2/vpcpay.html";
                //       $vnp_Returnurl = url('vnpay_return');
                //       $vnp_TmnCode ="IXLX9GVI" ; //Mã website tại VNPAY "RNJWDMJ8"
                //       $vnp_HashSecret = "TMBQZK5TX24V382CIO5EBOH39O32UDA7"; //Chuỗi bí mật PJBWDVVKUUPZANMEWEKTEOIWAKUATCRX

                //       $vnp_TxnRef = $order->order_code;    //Mã đơn hàng. Trong thực tế Merchant cần insert đơn hàng vào DB và gửi mã này sang VNPAY
                //       $vnp_OrderInfo = 'Thanh toán hóa đơn';
                //       $vnp_OrderType = 'billpayment';
                //       $vnp_Amount =  $order_details->price * $order_details->quantity * 100;
                //       $vnp_Locale = 'vn';
                //       $vnp_BankCode = 'NCB';
                //       $vnp_IpAddr = $_SERVER['REMOTE_ADDR'];

                //       $inputData = array(
                //         "vnp_Version" => "2.1.0",
                //         "vnp_TmnCode" => $vnp_TmnCode,
                //         "vnp_Amount" => $vnp_Amount,
                //         "vnp_Command" => "pay",
                //         "vnp_CreateDate" => date('YmdHis'),
                //         "vnp_CurrCode" => "VND",
                //         "vnp_IpAddr" => $vnp_IpAddr,
                //         "vnp_Locale" => $vnp_Locale,
                //         "vnp_OrderInfo" => $vnp_OrderInfo,
                //         "vnp_OrderType" => $vnp_OrderType,
                //         "vnp_ReturnUrl" => $vnp_Returnurl,
                //         "vnp_TxnRef" => $vnp_TxnRef,
                //       );

                //       if (isset($vnp_BankCode) && $vnp_BankCode != "") {
                //         $inputData['vnp_BankCode'] = $vnp_BankCode;
                //       }

                //       //var_dump($inputData);
                //       ksort($inputData);
                //       $query = "";
                //       $i = 0;
                //       $hashdata = "";
                //       foreach ($inputData as $key => $value) {
                //         if ($i == 1) {
                //           $hashdata .= '&' . urlencode($key) . "=" . urlencode($value);
                //         } else {
                //           $hashdata .= urlencode($key) . "=" . urlencode($value);
                //           $i = 1;
                //         }
                //         $query .= urlencode($key) . "=" . urlencode($value) . '&';
                //       }

                //       $vnp_Url = $vnp_Url . "?" . $query;
                //       if (isset($vnp_HashSecret)) {
                //         $vnpSecureHash =   hash_hmac('sha512', $hashdata, $vnp_HashSecret);
                //         $vnp_Url .= 'vnp_SecureHash=' . $vnpSecureHash;
                //       }
                //       $returnData = array(
                //         'code' => '00', 'message' => 'success', 'data' => $vnp_Url
                //       );
                //       header('Location: ' . $vnp_Url);
                //       die();
            } elseif ($request->buy_method == 'buy_cart') {
                $cart = Session::get('cart');

                $order = new Order;
                $order->user_id = Auth::user()->id;
                $order->payment_method_id = $request->payment_method;
                $order->order_code = 'PSO' . str_pad(rand(0, pow(10, 5) - 1), 5, '0', STR_PAD_LEFT);
                $order->name = $request->name;
                $order->email = $request->email;
                $order->phone = $request->phone;
                $order->address = $request->address;
                $order->status = 1;
                $order->save();

                foreach ($cart->items as $key => $item) {
                    $order_details = new OrderDetail;
                    $order_details->order_id = $order->id;
                    $order_details->product_detail_id = $item['item']->id;
                    $order_details->quantity = $item['qty'];
                    $order_details->price = $item['price'];
                    $order_details->save();

                    $product = ProductDetail::find($item['item']->id);
                    $product->quantity = $product->quantity - $item['qty'];
                    $product->save();
                }
                Session::forget('cart');

                //       $vnp_Url = "https://sandbox.vnpayment.vn/paymentv2/vpcpay.html";
                //       $vnp_Returnurl = url('vnpay_return');
                //       $vnp_TmnCode = "IXLX9GVI"; //Mã website tại VNPAY RNJWDMJ8
                //       $vnp_HashSecret = "TMBQZK5TX24V382CIO5EBOH39O32UDA7"; //Chuỗi bí mậtPJBWDVVKUUPZANMEWEKTEOIWAKUATCRX

                //       $vnp_TxnRef = $order->order_code;    //Mã đơn hàng. Trong thực tế Merchant cần insert đơn hàng vào DB và gửi mã này sang VNPAY
                //       $vnp_OrderInfo = 'Thanh toán hóa đơn';
                //       $vnp_OrderType = 'billpayment';
                //       $vnp_Amount =  $cart->totalPrice * 100;
                //       $vnp_Locale = 'vn';
                //       $vnp_BankCode = 'NCB';
                //       $vnp_IpAddr = $_SERVER['REMOTE_ADDR'];

                //       $inputData = array(
                //         "vnp_Version" => "2.1.0",
                //         "vnp_TmnCode" => $vnp_TmnCode,
                //         "vnp_Amount" => $vnp_Amount,
                //         "vnp_Command" => "pay",
                //         "vnp_CreateDate" => date('YmdHis'),
                //         "vnp_CurrCode" => "VND",
                //         "vnp_IpAddr" => $vnp_IpAddr,
                //         "vnp_Locale" => $vnp_Locale,
                //         "vnp_OrderInfo" => $vnp_OrderInfo,
                //         "vnp_OrderType" => $vnp_OrderType,
                //         "vnp_ReturnUrl" => $vnp_Returnurl,
                //         "vnp_TxnRef" => $vnp_TxnRef,
                //       );

                //       if (isset($vnp_BankCode) && $vnp_BankCode != "") {
                //         $inputData['vnp_BankCode'] = $vnp_BankCode;
                //       }

                //       //var_dump($inputData);
                //       ksort($inputData);
                //       $query = "";
                //       $i = 0;
                //       $hashdata = "";
                //       foreach ($inputData as $key => $value) {
                //         if ($i == 1) {
                //           $hashdata .= '&' . urlencode($key) . "=" . urlencode($value);
                //         } else {
                //           $hashdata .= urlencode($key) . "=" . urlencode($value);
                //           $i = 1;
                //         }
                //         $query .= urlencode($key) . "=" . urlencode($value) . '&';
                //       }

                //       $vnp_Url = $vnp_Url . "?" . $query;
                //       if (isset($vnp_HashSecret)) {
                //         $vnpSecureHash =   hash_hmac('sha512', $hashdata, $vnp_HashSecret); //
                //         $vnp_Url .= 'vnp_SecureHash=' . $vnpSecureHash;
                //       }
                //       $returnData = array(
                //         'code' => '00', 'message' => 'success', 'data' => $vnp_Url
                //       );
                //       header('Location: ' . $vnp_Url);
                //       die();
                //     }

            }
        }
    }
}
