<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Producer;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;

class ProducerController extends Controller
{
    public function index()
  {
    $producers = Producer::select('id', 'name', 'created_at')->latest()->get();
    return view('admin.producer.index')->with('producers', $producers);
  }
  public function new()
  {
    return view('admin.producer.new');
  }
  public function save(Request $request)
  {
    $producer = new Producer;
    $producer->name = $request->name;
    $producer->save();

    return redirect()->route('admin.producer.index')->with(['alert' => [
      'type' => 'success',
      'title' => 'Thành Công',
      'content' => 'Nhà sản xuất được tạo thành công.'
    ]]);
  }

  public function delete(Request $request)
  {
    $products = Product::select('id', 'producer_id', 'name', 'image', 'sku_code', 'OS', 'rate', 'created_at')->where('producer_id', $request->producer_id)->first();
    $producer = Producer::where('id', $request->producer_id)->first();
    if($products == null){
        if(!$producer) {

            $data['type'] = 'error';
            $data['title'] = 'Thất Bại';
            $data['content'] = 'Bạn không thể xóa nhà sản xuất không tồn tại!';
          } else {
            $producer->delete();
      
            $data['type'] = 'success';
            $data['title'] = 'Thành Công';
            $data['content'] = 'Xóa nhà sản xuất thành công!';
          }
    }
    else{
        $data['type'] = 'error';
        $data['title'] = 'Thất bại';
        $data['content'] = 'Xóa nhà sản xuất thất bại! Còn sản phẩm';
    }

    return response()->json($data, 200);
  }

  public function edit($id)
  {
    $producer = Producer::where('id', $id)->first();
    if(!$producer) abort(404);
    return view('admin.producer.edit')->with('producer', $producer);
  }

  public function update(Request $request, $id)
  {
    $producer = Producer::where('id', $id)->first();
    $producer->name = $request->name;
    $producer->save();

    return redirect()->route('admin.producer.index')->with(['alert' => [
      'type' => 'success',
      'title' => 'Thành Công',
      'content' => 'Nhà sản xuất đã được cập nhật thành công.'
    ]]);
  }
}
