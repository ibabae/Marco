<?php

use App\Models\Category;
use App\Models\Order;
use App\Models\OrderForm;
use App\Models\Product;
use App\Models\User;
use App\Models\Setting;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

    function user($value = ''){
        if(Auth::user()){
            $user = User::find(Auth::user()->id);
            return $user->$value;
        } else {
            return null;
        }
    }
    function Setting($value){
        $setting = Setting::where('code',$value)->first();
        if($setting){
            return $setting->value;
        }else{
            return null;
        }
    }
    function price($val, $type = 1){
        if(Setting('unit') == 1){
            $price_format = ' ﮬ.ت';
            return number_format($val / 1000).$price_format;
        } else if(Setting('unit') == 2) {
            $price_format = ' ﮬ.ر';
            return number_format($val*10).$price_format;
        } else if(Setting('unit') == 3) {
            $price_format = ' دلار';
            return number_format($val).$price_format;
        }
    }
    function xprice($value){
        return ((Setting('profit') / 100) * $value) + $value;
    }
    function Available($id){
        
    }
    function OrderStatus($status){
        if($status == 1){
            return '<div class="badge rounded-pill alert-success">دریافت شده</div>';
        } else if($status == 2){
            return '<div class="badge rounded-pill alert-info">ارسال شده</div>';
        } else if($status == 3){
            return '<div class="badge rounded-pill alert-primary">در صف ارسال</div>';
        } else {
            return '<div class="badge rounded-pill alert-warning">در انتظار پرداخت</div>';
        }
    }
    function TransStatus($status){
        if($status == 1){
            return '<div class="badge rounded-pill alert-success">پرداخت شده</div>';
        } else {
            return '<div class="badge rounded-pill alert-warning">ناموفق</div>';
        }
    }
    function State($id){
        $state = DB::table('states')->where('id',$id)->first();
        return $state->name_fa;
    }
    function PayType($id){
        switch ($id) {
            case '1':
                return 'کارت به کارت';
                break;
            case '2':
                return 'پرداخت با چک';
                break;
            case '3':
                return 'پرداخت آنلاین';
                break;
            
            default:
                return null;
                break;
        }
    }
    function Badge($id){
        $product = Product::where('id',$id)->first();
        // <span class="hot">داغ</span>
        // <span class="new">New</span>
        // <span class="best">Best Sell</span>
        // <span class="sale">Sale</span>
        // <span class="hot">-30%</span>
    }
    function reloadCaptcha(){
        return response()->json(['captcha'=> captcha_img()]);
    }
    function excerpt($value,$limit){
        $array = explode(' ', $value);
        if(count($array) > 1  ){
            $return = [];
            for ($i=0; $i < $limit; $i++) { 
                $return[] = $array[$i];
            }
            return implode(" ",$return).'...';
        } else {
            return $value;
        }
    }
    function SendSms($phone,$code){
        $smsdata = '{"mobile":"'.$phone.'","templateId":100000,"parameters":[{"name":"CODE","value":"'.$code.'"}]}';
        $curl = curl_init();
        $api_key = env('SMSIR_API');
        curl_setopt_array($curl, array(
            CURLOPT_URL => 'https://api.sms.ir/v1/send/verify',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => $smsdata,
            CURLOPT_HTTPHEADER => array(
                "Content-Type: application/json",
                "Accept: text/plain",
                "x-api-key: $api_key"
            ),
        ));

        $response = curl_exec($curl);

        curl_close($curl);
        // echo $response;
    }
    function phone($value){
        if(substr($value,0,1) == 0){
            $value = substr($value,0,11);
        }
        $part1 = substr($value, 0, 4);
        $part2 = substr($value, 4, 3);
        $part3 = substr($value, 7, 4);
        return $part1.'-'.$part2.'-'.$part3;
    }
    function Profit(){
        $orders = Order::where('status','!=',0)->get();
        $total = 0;
        foreach($orders as $order){
            $order_form = OrderForm::where('CartId',$order->id)->get();
            foreach($order_form as $item){
                $product = Product::where('id',$item->ProductId)->first();
                $total += ($item->Price - $product->Price) * $item->Count;
            }
        }
        return $total;
    }
    function Orders(){
        $orders = Order::where('status','!=',0)->get();
        $total = 0;
        foreach ($orders as $key => $order) {
            $total += $order->Price;
        }
        return $total;
    }
    function Products(){
        $products = Product::get();
        return $products->count();
    }
    function Categories(){
        $categories = Category::get();
        return $categories->count();
    }
    function GateWay($value,$type = 1){
        if($type == 1){
            if($value == 1){
                return '
                    <div class="icontext">
                        <img class="icon border" src="'. asset('/images/zarinpal.png').'" alt="Payment">
                        <span class="text text-muted">ZarinPal</span>
                    </div>
                ';
            } else {
                return '
                    <div class="icontext">
                        <img class="icon border" src="'. asset('/images/payir.svg').'" alt="Payment">
                        <span class="text text-muted">PayIr</span>
                    </div>
                ';
            }
        } else {
            if($value == 1){
                return 'زرین پال';
            } else {
                return 'پی آی آر';
            }
        }
    }
?>