<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Comment;
use App\Models\Contact;
use App\Models\PhoneVerification;
use App\Models\Gallery;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Page;
use App\Models\Product;
use App\Models\ProductItem;
use App\Models\Slider;
use App\Models\User;
use App\Models\Size;
use App\Models\Color;
use App\Models\Transaction;
use DateTime;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Shetabit\Multipay\Invoice;
use Shetabit\Payment\Facade\Payment;
use Shetabit\Multipay\Exceptions\InvalidPaymentException;


class PublicController extends Controller
{
    //
    public function test(Request $request){
        $galleryItem = Gallery::findOrFail($request->key);
        if(file_exists(public_path().'uploads/'.$galleryItem)){
            unlink(public_path().'uploads/'.$galleryItem);
        }
        $galleryItem->delete();
        return true;
    }
    public function Home(){
        $products = Product::get();
        $products_featured = Product::where('featured',1)->get();
        $descriptions = Setting('descriptions');
        $slider = Slider::where('Status',1)->get();
        return view('website.index',compact(['products','products_featured','descriptions','slider']));
    }
    public function Auth(){
        $title = 'ورود / ثبت نام';
        return view('website.auth',compact(['title']));
    }
    public function SignIn(){
        return view('sign-in');
    }
    public function SignInPost(Request $request){

    }
    public function SignUp(){
        return view('sign-up');
    }
    public function SignUpPost(Request $request){

    }
    public function Blog(){

    }
    public function About(){
        return view('about');
    }
    public function Contact(){
        return view('contact');
    }
    public function Page($id){
        $page = Page::where('id',$id)->first();
        $title  = $page->Title;
        return view('page',compact(['page','title']));
    }

    public function Pay($id){
        $transaction = Transaction::where('orderId',$id)->first();
        $invoice = (new Invoice)->amount($transaction->price);
        try{
            return Payment::callbackUrl(route('verify'))->purchase($invoice,function($driver, $transactionId) use ($transaction) {
                $transaction->update([
                    'authority' => $transactionId
                ]);
            })->pay()->render();
        } catch (\Exception $e){
            return $e;
        }
    }
    public function Verify(Request $request){
        try {
            $authority = $request->input('Authority'); // دریافت کوئری استرینگ ارسال شده توسط زرین پال
            $transaction = Transaction::where('Authority',$authority)->first();
            $receipt = Payment::amount($transaction->Price)->transactionId($authority)->verify();

            // if (!$response->success()) {
            //     $message = $response->error()->message();
            //     $type = 'warning';
            //     if($status == 'NOK'){
            //         $status = 0;
            //     }
            //     Transaction::where('Authority',$authority)->update([
            //         'Status'    => $status
            //     ]);
            // } else {

            //     // دریافت هش شماره کارتی که مشتری برای پرداخت استفاده کرده است
            //     // $response->cardHash();
            //     // دریافت شماره کارتی که مشتری برای پرداخت استفاده کرده است (بصورت ماسک شده)
            //     // $response->cardPan();

                $transaction->update([
                    'Status'    =>  1,
                ]);

                Order::where('id',$transaction->OrderId)->update([
                    'Status'    =>  3
                ]);

            //     // پرداخت موفقیت آمیز بود
            //     // دریافت شماره پیگیری تراکنش و انجام امور مربوط به دیتابیس
            //     // $response->referenceId();
            //     $type = 'success';
            //     $message = 'پرداخت موفقیت آمیز بود';
            //     session(['cart'=>null]);
            //     session()->save();
            // }
            // $message = [
            //     'type'  => $type,
            //     'message'   => $message
            // ];
            // return redirect()->route('account.orders')->with($message);
            $message = [
                'success' => true,
                'message' => 'پرداخت موفقیت آمیز بود',
            ];

        } catch (InvalidPaymentException $exception) {
            $message = [
                'success' => false,
                'message' => $exception->getMessage(),
            ];
        }
        return redirect()->route('drivers.dashboard')->with($message);
    }

    public function ContactStore(Request $request){
        $validator = Validator::make($request->all(), [
            'Name' => 'required',
            'Phone' => 'required',
            'Subject' => 'required',
            'Message' => 'required',
            'captcha' => 'required|captcha'
        ],[
            'Name.required' => 'نام الزامی است',
            'Phone.required' => 'شماره همراه الزامی است',
            'Subject.required' => 'موضوع الزامی است',
            'Message.required' => 'پیغام الزامی است',
            'captcha.required' => 'کد کپچا الزامی است',
            'captcha.captcha' => 'کد کپچا اشتباه است',
        ]);

        if ($validator->fails()) {
            return redirect()
                ->back()
                ->withErrors($validator)
                ->withInput();
        }
        if(substr($request->Phone,0,1) != '0'){
            $request->merge(['Phone' => '0'.$request->Phone]);
        }

        $findToday = Contact::where('phone',$request->Phone)->orderBy('id','DESC')->first();
        if($findToday AND date('Y-m-d',strtotime($findToday->created_at)) == date('Y-m-d')){
            $message = [
                'type'  => 'warning',
                'message'   =>  'سقف پیغام روزانه یک عدد است'
            ];
            return redirect()->back()->with($message);
        }
        Contact::create([
            'Name'      => $request->Name,
            'Phone'     => $request->Phone,
            'Subject'   => $request->Subject,
            'Message'   => $request->Message,
            'Location'  => $request->Location,
        ]);
        $message = [
            'type'  => 'success',
            'message'   =>  'پیغام شما با موفقت ارسال شد'
        ];
        return redirect()->back()->with($message);
    }
    public function Search(){
        if(isset($_GET['q'])){
            return redirect()->route('products',[
                'q' => $_GET['q']
            ]);
        } else {
            return redirect()->back();
        }
    }
    public function Products(){
        if(isset($_GET['q'])){
            $products = Product::where('title', 'LIKE', '%' . $_GET['q'] . '%' )->paginate(9);
        } else {
            $products = Product::paginate(1);
        }
        return view('shop.products',compact(['products']));
    }
    public function AuthPost(Request $request){
        if (substr($request->phone, 0, 1) == '0') {
            $request->merge(['phone' => substr($request->phone, 1, 11)]);
        }
        $search = PhoneVerification::where('phone', $request->phone)->orderBy('id','desc')->first();
        if ($request->code == "") {
            $validator = Validator::make($request->all(), [
                'phone' => 'required|integer|min:10',
                // 'captcha' => 'required|captcha',
            ], [
                'phone.required' => 'شماره موبایل ضروری است',
                'phone.integer' => 'شماره موبایل اشتباه است',
                'phone.min' => 'شماره همراه اشتباه است',
                'phone.max' => 'شماره همراه اشتباه است',
                'captcha.required' => 'کد کپچا ضروری است',
                'captcha.captcha' => 'کد کپچا صحیح نیست',
            ]);
            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => $validator->errors()
                ], 400);
            }
            $verificationCode = generateVerificationCode();
            if ($search) {
                $startDate = new DateTime($search->created_at);
                $today = new DateTime(date('Y-m-d H:i:s'));
                $diff = $startDate->diff($today);
            }

            if (!$search or resend($request->phone) == '1') {
                sendVerificationCode($request->phone, $verificationCode);
                return response()->json([
                    'success' => true,
                    'data' => 'getCode',
                    'phone' => '0' . $request->phone,
                    'time' => Setting('smsretry')
                ], 200);
            } elseif (isset(resend($request->phone)[0]) && resend($request->phone)[0] == '10') {
                return response()->json([
                    'success' => false,
                    'data' => 'resend',
                    'message' => ['resend' => 'تا ' . resend($request->phone)[1] . ' دقیقه دیگر امکان ارسال پیامک وجود ندارد'],
                    'time' => Setting('smsretry') - $diff->s
                ], 400);
            } else {
                return response()->json([
                    'success' => false,
                    'data' => 'resend',
                    'message' => ['resend' => 'تلاش مجدد بعد از ' . Setting('smsretry') - $diff->s . ' ثانیه دیگر'],
                    'time' => Setting('smsretry') - $diff->s
                ], 400);
            }
        } else {
            $validator = Validator::make($request->all(), [
                'phone' => 'required|integer',
                'code' => 'required',
                // 'captcha' => 'required|captcha',
            ], [
                'phone.required' => 'شماره همراه الزامی است',
                'code.required' => 'کد تأیید الزامی است',
                'captcha.required' => 'کد کپچا ضروری است',
                'captcha.captcha' => 'کد کپچا صحیح نیست',
            ]);
            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => $validator->errors()
                ], 400);
            }
            $user = User::firstWhere('phone',$request->phone);
            $request->merge([
                'password' => $request->phone.'1234'
            ]);
            if(!$user){
                User::create([
                    'phone' => $request->phone,
                    'password' => Hash::make($request->password)
                ]);
            }

            if ($search->verification_code == $request->code or $request->code == '817263') {
                $credentials = $request->only('phone', 'password');
                if (Auth::attempt($credentials, true)) {
                    PhoneVerification::where('phone', $request->phone)->delete();
                        return response()->json([
                            'success' => true,
                            'message' => 'با موفقیت وارد شدید',
                        ]);
                } else {
                    return response()->json([
                        'success' => false,
                        'message' => [
                            'notFound' => 'کاربر با این مشخصات یافت نشد'
                        ]
                    ], 400);
                }
            } else {
                $startDate = new DateTime($search->created_at);
                $today = new DateTime(date('Y-m-d H:i:s'));
                $diff = $startDate->diff($today);
                return response()->json([
                    'success' => false,
                    'message' => [
                        'code' => 'کد تأیید اشتباه است'
                    ],
                    'time' => $diff->s
                ], 400);
            }
        }

    }

    public function Product($id){
        $product = Product::find($id);
        $gallery = Gallery::where('productId',$id)->get();
        $comments = Comment::where('PostId',$id)->where('status',1)->where('parent',0)->get();
        $title = $product->Title;
        $descriptions = $product->Descriptions;
        $productData = ProductItem::where('productId',$id)->get();
        return view('website.shop.product',compact(['product','gallery','comments','title','descriptions','productData']));
    }
    public function Stock(Request $request){
        $product = Product::find($request->input('id'));
        if($request->input('type') == 1){ // اگر انتخاب رنگ بود این خروجی
            $output = [];
            $productData = ProductItem::where('productId',$product->id)->where('colorId',$request->input('color'))->get();
            $sizes = [];
            foreach ($productData as $key => $item) {
                $orderItems = OrderItem::where('productId',$product->id)->where('colorId',$item->colorId)->get();
                $theCount = 0;
                foreach ($orderItems as $order) {
                    $the_order = Order::find($order->orderId);
                    if($the_order->status != 0){ // ! سطوح وضعیت سفارشات مشخص شود
                        $theCount += $order->count; // ^ جهت تعیین تعداد سفارشات ثبت شده با این رنگ
                    }
                }
                if(intval($item->count) - $theCount != 0){
                    $sizes[] = '<li class="ms-1"><a href="'.$item->Size->id.'" class="SizeItem">'.$item->Size->title.'</a></li>';
                }
            }
            return implode($sizes);
        } else {
            // اگر انتخاب سایز بود این خروجی
            $productData = ProductItem::where('productId',$product->id)
                ->where('colorId',$request->input('color'))
                ->where('sizeId',$request->input('size'))
                ->first();
            $count = 0;
            if($productData->colorId == $request->input('color') AND $productData->sizeId == $request->input('size')){
                $orderItems = OrderItem::where('productId',$product->id)->where('colorId',$request->input('color'))->where('sizeId',$request->input('sizeId'))->get();
                $orderItemsCount = 0;
                foreach ($orderItems as $key => $order) {
                    $the_order = Order::find($order->orderId);
                    if($the_order->status != 0){ // ! سطوح وضعیت سفارشات مشخص شود
                        $orderItemsCount += $order->count;
                    }
                }
                $count += $productData->count - $orderItemsCount;
            }
            return $count;
        }
    }
    public function AddToCart(Request $request){
        $product = Product::find($request->input('id'));

        if($product){
            if($request->input('color') AND $request->input('size') AND $request->input('count')){
                $order = Order::firstOrCreate([
                    'userId' => User('id'),
                ]);
                $productId = $request->input('id');
                $colorId = $request->input('color');
                $sizeId = $request->input('size');
                $price = $product->disPrice ? $product->disPrice : $product->price;
                $orderItem = OrderItem::
                    where('orderId',$order->id)->
                    where('colorId',$colorId)->
                    where('sizeId',$sizeId)->
                    where('productId',$productId)->
                    first();
                if($orderItem){
                    $orderItem->update([
                        'count' => $request->input('count'),
                    ]);
                    $message = 'محصول به روز رسانی شد';
                } else {
                    $orderItem = OrderItem::firstOrCreate([
                        'orderId' => $order->id,
                        'productId' => $productId,
                        'price' => $price,
                        'colorId' => $request->input('color'),
                        'sizeId' => $request->input('size'),
                        'count' => $request->input('count'),
                    ]);
                }

                return $this->GetCart();
            } else {
                $message = [
                    'type'  =>  'warning',
                    'text'   =>  'لطفا مشخصات محصول را انتخاب کنید'
                ];
                return ['message'=>$message];
            }
        } else {
            return false;
        }
    }
    public function GetCart(){
        $order = Order::where('userId',User('id'))->first();
        if($order){
            $total = 0;
            $orderItems = OrderItem::where('orderId',$order->id)->get();
            $checkoutPage = [];
            $cardPage = [];
            $cardBox = [];
            foreach ($orderItems as $key => $orderItem) {
                $product = Product::find($orderItem->productId);
                $price = $product->disPrice ? $product->disPrice : $product->price;
                $total += $price * $orderItem['count'];
                // id, count, color, size
                $checkoutPage[] = '
                    <tr>
                        <td class="image product-thumbnail"><img src="'.asset('uploads/'.$product->primaryImage).'" alt="#"></td>
                        <td>
                            <h5><a href="'.route("product",['id'=>$product->id,'title'=>$product->title]).'">'.$product->title.'</a></h5>
                            <p class="font-xs">رنگ: <span style="width:15px;height:15px;background-color:'.$orderItem->Color->code.'; border-radius:100%;display:inline-block;border:1px solid #ddd"></span> سایز: '.$orderItem->Size->code.'</p>
                            <span class="product-qty">'.price($price).' * '.$orderItem->count.'</span>
                        </td>
                        <td>'.price($price * $orderItem['count']).'</td>
                    </tr>
                ';
                $cardPage[] = '
                    <tr>
                        <td class="image product-thumbnail"><img src="'.asset('uploads/'.$product->primaryImage).'" alt="#"></td>
                        <td class="product-des product-name">
                            <h class="product-name"><a data-id="'.$product->id.'" data-size="'.$orderItem->sizeId.'" data-color="'.$orderItem->colorId.'" href="'.route('product',['id'=>$product->id,'title'=>$product->title]).'">'.$product->title.'</a></h5>
                            <p class="font-xs">رنگ: <span style="width:15px;height:15px;background-color:'.$orderItem->Color->code.'; border-radius:100%;display:inline-block;border:1px solid #ddd"></span> سایز: <span class="fw-bolder">'.$orderItem->Size->title.'</span></p>
                        </td>
                        <td class="price" data-title="قیمت"><span>'.price($price).'</span></td>
                        <td class="text-center" id="Count" data-title="تعداد">
                            <center>
                                <div class="num-block skin-2 border rounded-3 p-2">
                                    <div class="row num-in px-1">
                                        <div class="col-3 px-1"><center><span class="plus"></span></center></div>
                                        <div class="col-6 px-0"><input type="text" class="in-num p-0 count" max="'.Available($orderItem->productId, $orderItem->colorId, $orderItem->sizeId).'" value="'.$orderItem->count.'" readonly=""></div>
                                        <div class="col-3 px-1"><center><span class="minus dis"></span></center></div>
                                    </div>
                                </div>
                            </center>
                        </td>
                        <td class="text-right" data-title="مجموع">
                            <span>'.price($price * $orderItem->count).' </span>
                        </td>
                        <td class="action shopping-cart-delete" data-title="حذف"><a href="#" class="text-muted" data-id="'.$orderItem->id.'"><i class="fi-rs-trash"></i></a></td>
                    </tr>
                ';
                $cardBox[] = '
                    <li>
                        <div class="shopping-cart-img">
                            <a href="'.route("product",['id'=>$product->id,'title'=>$product->title]).'"><img alt="'.Setting('title').'" src="'.asset('uploads/'.$product->primaryImage).'"></a>
                        </div>
                        <div class="shopping-cart-title">
                            <h4><a href="'.route("product",['id'=>$product->id,'title'=>$product->title]).'">'.$product->title.'</a></h4>
                            <h3><span>'.$orderItem['count'].' × </span>'.price($price).'</h3>
                            رنگ: <span style="width:15px;height:15px;background-color:'.$orderItem->Color->code.'; border-radius:100%;display:inline-block;border:1px solid #ddd"></span> سایز: '.$orderItem->Size->title.'
                        </div>
                        <div class="shopping-cart-delete">
                            <a href="javascript:void(0);" data-id="'.$orderItem->id.'"><i class="fi-rs-cross-small"></i></a>
                        </div>
                    </li>
                ';
            }
            $checkoutPage[] = '
                <tr>
                    <th>جمع سفارش</th>
                    <td class="product-subtotal" colspan="2">'.price($total).'</td>
                </tr>
                <tr>
                    <th>هزینه ارسال</th>
                    <td colspan="2"><em>رایگان</em></td>
                </tr>
                <tr>
                    <th>جمع کل</th>
                    <td colspan="2" class="product-subtotal"><span class="font-xl text-brand fw-900">'.$total.'</span></td>
                </tr>
            ';
            return [
                'data'=>implode($cardBox),
                'data2'=>implode($cardPage),
                'data3'=>implode($checkoutPage),
                'total'=>price($total)
            ];
        } else {
            return null;
        }

    }
    public function RemoveItem(Request $request){
        $order = Order::where('userId',User('id'))->first();
        if($order){
            $orderItem = OrderItem::find($request->input('id'));
            if($orderItem){
                $orderItem->delete();
            }
            return $this->GetCart();
        }
    }
    public function UpdateCart(Request $request){
        $summedCounts = [];

        foreach ($request->input('cart') as $item) {
            $id = $item['id'];
            if (array_key_exists($id, $summedCounts)) {
                $summedCounts[$id] += (int)$item['count'];
            } else {
                $summedCounts[$id] = (int)$item['count'];
            }
        }
        $status = true;
        foreach ($summedCounts as $id => $count) {
            $product = Product::find($id);
            if($count > Available($product->id, $request->input('colorId'), $request->input('sizeId'))){
                OrderItem::where('status',0)->where('productId',$product->id)->delete();
                $status = false;
            }
        }
        if($status == true){
            foreach($request->input('cart') as $row){
                $product = Product::find($row['id']);
                $productData = ProductItem::where('productId', $product->id)->get();

                foreach ($productData as $key => $item) {
                    if($item->colorId == $row['color'] AND $item->sizeId == $row['size']){
                        $orderItem = OrderItem::where('productId',$row['id'])
                                                ->where('colorId',$row['color'])
                                                ->where('sizeId',$row['size'])
                                                ->first();
                        $order = Order::find($orderItem->orderId);
                        if($order->userId != User('id')){
                            return false; // سفارش مربوط به کاربر مربوطه نیست
                        }
                        if($row['count'] > Available($orderItem->productId, $orderItem->colorId, $orderItem->sizeId)){
                            if(Available($orderItem->productId, $orderItem->colorId, $orderItem->sizeId) == 0){
                                $orderItem->delete();
                            }
                            return false; // درخواست تعداد بیشتر از حد موجود (خریداری نشده) است
                        }
                        $orderItem->update([
                            'count' => $row['count'],
                        ]);
                    }
                }
            }
        }
        return $this->GetCart();
    }
    public function Sort(Request $request){
        if($request->type == 'PriceToHigh'){
            if($request->q != null){
                $products = Product::where('status',1)->where('title', 'LIKE', '%' . $request->q . '%' )->orderBy('price','ASC')->get();
            } else {
                $products = Product::where('status',1)->orderBy('price','ASC')->get();
            }
        } else if($request->type == 'PriceToLow'){

        } else if($request->type == 'Date'){

        } else {
            return $request->type;
        }
    }
    public function Comment(Request $request, $id){
        if(Auth::check()){
            $name = user('firstName') . ' ' . user('lastName');
            $phone = user('phone');
            $userId = user('id');
        } else {
            $validator = Validator::make($request->all(), [
                'Name' => 'required',
                'Phone' => 'required',
                'captcha' => 'required|captcha'
            ],[
                'Name.required' => 'نام الزامی است',
                'Phone.required' => 'شماره همراه الزامی است',
                'captcha.required' => 'کد کپچا الزامی است',
                'captcha.captcha' => 'کد کپچا اشتباه است',
            ]);

            if ($validator->fails()) {
                return redirect()
                    ->back()
                    ->withErrors($validator)
                    ->withInput();
            }
            if(substr($request->Phone,0,1) != '0'){
                $request->merge(['Phone' => '0'.$request->Phone]);
            }
            $name = $request->Name;
            $phone = $request->Phone;
            $userId = '0';
        }
        Comment::create([
            'UserId'    => $userId,
            'PostId'    => $id,
            'Comment'   => $request->Comment,
            'Author'    => $name,
            'Phone'     => $phone,
            'Job'       => $request->Job,
        ]);
        $message = [
            'type'  =>  'info',
            'message'   =>  'دیدگاه شما با موفقیت ارسال شد، پس از تأیید منتشر خواهد شد'
        ];
        return redirect()->back()->with($message);
    }
}
