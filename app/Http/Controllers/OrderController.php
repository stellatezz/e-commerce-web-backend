<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Str;
use App\Models\Order;
use App\Models\Session;
use App\Models\Product;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class OrderController extends Controller
{
    //
    public function getAllOrder() {
        $orders = DB::select('select uuid, products, uid, username, total_price, payment_status from orders order by create_time');
        return response()->json([
            'status' => '1',
            'message' => 'sucess',
            'orders' => $orders,
        ], 200);
    }

    public function getuserOrder() {
        request()->validate([
            'uid' => 'required',
            'username' => 'required',
        ]);

        try{
            $username = request('username');
            $uid = request('uid');
            $orders = DB::select('select uuid, products, payment_status, total_price from orders where uid = ? and username = ? order by create_time DESC LIMIT 5', array($uid, $username));

            return response()->json([
                'status' => '1',
                'message' => 'sucess',
                'orders' => $orders,
            ], 200);
        } catch(Exception $e){
            return response()->json([
                'message' => 'error occur'
            ], 500);
        }
        return Order::all();
    }

    public function paypalIPN()
    {
        header('HTTP/1.1 200 OK');
    }

    public function checkout()
    {
        request()->validate([
            'currency' => 'required',
            'products' => 'required',
            'username' => 'required',
            'uid' => 'required',
        ]);

        try{
            //header('HTTP/1.1 200 OK');
            error_log(request());
            error_log(implode(" ",request()->all()));
            $email = 'sb-bkea215676270@business.example.com';
            $payment_status = 'Processing';
            $currency = request('currency');
            $username = request('username');
            $uid = request('uid');

            $productsJson = request('products');
            $products = json_decode($productsJson);
            $productsLength = count($products);

            $totalprice = 0;

            $price = null;
            $productprice = array();

            $productsHash = '';
            $pricesHash = '';
            foreach ($products as &$product) {
                if ($product->quantity <= 0) {
                    return response()->json([
                        'status' => '-1',
                        'message' => 'invaild quantity',
                    ], 200);
                }

                $productsHash = $productsHash.$product->pid;
                $productsHash = $productsHash.$product->quantity;


                $pid = $product->pid;
                $newproductprice = DB::select('select pid, price from products where pid = ?', array($pid));
                if ($newproductprice) {
                    $totalprice += $newproductprice[0]->price;
                    $pricesHash = $pricesHash.$newproductprice[0]->price;

                    $productprice = array_merge($productprice, $newproductprice);    
                } else {
                    return response()->json([
                        'status' => '-2',
                        'message' => 'invaild product',
                    ], 200);
                }
            }
            $productpriceJson = json_encode($productprice);

            $salt = Str::random();
            $digest = hash_hmac('sha256', $currency.$email.$productsHash.$pricesHash.$totalprice, $salt);


            //$f_productsJson = filter_var($productsJson, FILTER_SANITIZE_STRING);
            $f_username = filter_var($username, FILTER_SANITIZE_STRING);
            $f_uid = filter_var($uid, FILTER_SANITIZE_STRING);

            $result = DB::table('orders')->insert([
                'digest' => $digest,
                'total_price' => $totalprice,
                'currency' => $currency,
                'email' => $email,
                'salt' => $salt,
                'products' => $productsJson,
                'prices' => $productpriceJson,
                'username' => $f_username,
                'uid' => $f_uid,
                'payment_status' => $payment_status,
                'create_time' => time(),
            ]);

            $uuid = DB::select('select uuid from orders where digest = ?', array($digest));
            $uuid = $uuid[0]->uuid;

            // error_log($uuid[0]->uuid);
            // error_log($digest);
            return response()->json([
                'status' => '1',
                'message' => 'checkout sucess',
                'custom_id' => $digest,
                'invoice_id' => $uuid,
            ], 200);
        } catch(Exception $e){
            return response()->json([
                'message' => 'error occur'
            ], 500);
        }
    }
}
