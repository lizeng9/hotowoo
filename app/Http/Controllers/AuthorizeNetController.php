<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

use net\authorize\api\contract\v1 as AnetAPI;
use net\authorize\api\controller as AnetController;


class AuthorizeNetController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }


    public function charge(Request $request){
        $requires = ['order_id','order_title','total_fee','pay_token'];
        $data = array_select_by_keys($request->all(),$requires);
        foreach ($requires as $v){
            if(!(isset($data[$v]) && $data[$v])) return $this->error("1", "$v 必须");
        }
        $data['user_id'] = $request->get("user")['uid'];
        return $this->chargeCreditCard($data);
    }

    protected function chargeCreditCard($info)
    {

        // Set the transaction's refId
        $refId = date("YmdHis"). mt_rand(100,999);

        /* Create a merchantAuthenticationType object with authentication details
           retrieved from the constants file */
        $merchantAuthentication = new AnetAPI\MerchantAuthenticationType();
        $merchantAuthentication->setName(env('AUTHORIZENET_LOGIN_ID'));
        $merchantAuthentication->setTransactionKey(env('AUTHORIZENE_TRANSACTION_KEY'));



        // Create the payment data for a credit card
//        $creditCard = new AnetAPI\CreditCardType();
//        $creditCard->setCardNumber($info['card_name']);
//        $creditCard->setExpirationDate($info['card_expiration']);
//        $creditCard->setCardCode($info['card_code']);
//
//        // Add the payment data to a paymentType object
//        $paymentOne = new AnetAPI\PaymentType();
//        $paymentOne->setCreditCard($creditCard);


        $opaqueData = new AnetAPI\OpaqueDataType();
        $opaqueData->setDataDescriptor("COMMON.ACCEPT.INAPP.PAYMENT");
        $opaqueData->setDataValue($info['pay_token']);

        // Add the payment data to a paymentType object
        $paymentOne = new AnetAPI\PaymentType();
        $paymentOne->setOpaqueData($opaqueData);


        // Create order information
        $order = new AnetAPI\OrderType();
        $order->setInvoiceNumber($info['order_id']);
        $order->setDescription(get_abbrev_str($info['order_title'],40));

        // Set the customer's Bill To address
//        $customerAddress = new AnetAPI\CustomerAddressType();
//        $customerAddress->setFirstName($info['first_name']);
//        $customerAddress->setLastName($info['last_name']);
//        $customerAddress->setCompany($info['company']);
//        $customerAddress->setAddress($info['address']);
//        $customerAddress->setCity($info['city']);
//        $customerAddress->setState($info['state']);
//        $customerAddress->setZip($info['zip']);
//        $customerAddress->setCountry($info['country']);

        // Set the customer's identifying information
        $customerData = new AnetAPI\CustomerDataType();
        $customerData->setType("individual");
        $customerData->setId($info['user_id']);
//        $customerData->setEmail("EllenJohnson@example.com");

        // Add values for transaction settings
        $duplicateWindowSetting = new AnetAPI\SettingType();
        $duplicateWindowSetting->setSettingName("duplicateWindow");
        $duplicateWindowSetting->setSettingValue("60");

        // Add some merchant defined fields. These fields won't be stored with the transaction,
        // but will be echoed back in the response.
//        $merchantDefinedField1 = new AnetAPI\UserFieldType();
//        $merchantDefinedField1->setName("pay_type");
//        $merchantDefinedField1->setValue("authorize.net");
//
//        $merchantDefinedField2 = new AnetAPI\UserFieldType();
//        $merchantDefinedField2->setName("favoriteColor");
//        $merchantDefinedField2->setValue("blue");

        // Create a TransactionRequestType object and add the previous objects to it
        $transactionRequestType = new AnetAPI\TransactionRequestType();
        $transactionRequestType->setTransactionType("authCaptureTransaction");
        $transactionRequestType->setAmount($info['total_fee']/100);
        $transactionRequestType->setOrder($order);
        $transactionRequestType->setPayment($paymentOne);
//        $transactionRequestType->setBillTo($customerAddress);
        $transactionRequestType->setCustomer($customerData);
        $transactionRequestType->addToTransactionSettings($duplicateWindowSetting);
//        $transactionRequestType->addToUserFields($merchantDefinedField1);
//        $transactionRequestType->addToUserFields($merchantDefinedField2);

        // Assemble the complete transaction request
        $request = new AnetAPI\CreateTransactionRequest();
        $request->setMerchantAuthentication($merchantAuthentication);
        $request->setRefId($refId);
        $request->setTransactionRequest($transactionRequestType);

        // Create the controller and get the response
        $controller = new AnetController\CreateTransactionController($request);
        $response = $controller->executeWithApiResponse(\net\authorize\api\constants\ANetEnvironment::SANDBOX);
//        $response = $controller->executeWithApiResponse(\net\authorize\api\constants\ANetEnvironment::PRODUCTION);


        if ($response != null) {
            // Check to see if the API request was successfully received and acted upon
            if ($response->getMessages()->getResultCode() == "Ok") {
                // Since the API request was successful, look for a transaction response
                // and parse it to display the results of authorizing the card
                $tresponse = $response->getTransactionResponse();

                if ($tresponse != null && $tresponse->getMessages() != null) {
//                    echo " Successfully created transaction with Transaction ID: " . $tresponse->getTransId() . "\n";
//                    echo " Transaction Response Code: " . $tresponse->getResponseCode() . "\n";
//                    echo " Message Code: " . $tresponse->getMessages()[0]->getCode() . "\n";
//                    echo " Auth Code: " . $tresponse->getAuthCode() . "\n";
//                    echo " Description: " . $tresponse->getMessages()[0]->getDescription() . "\n";
                    $user_info=[
                        'user_id'=>$info['user_id'],
//                        'first_name'=>$info['first_name'],
//                        'last_name'=>$info['last_name'],
//                        'address'=>$info['address'],
//                        'company'=>$info['company'],
//                        'zip'=>$info['zip'],
//                        'city'=>$info['city'],
//                        'state'=>$info['state'],
//                        'country'=>$info['country']
                    ];

                    $pay_data['id'] = $tresponse->getTransId();
                    $pay_data['order_id'] = $info['order_id'];
                    $pay_data['user_id'] = $info['user_id'];
                    $pay_data['order_title'] = $info['order_title'];
                    $pay_data['total_fee'] = (int)$info['total_fee'];
                    $pay_data['fee_type'] =  'USD';
                    $pay_data['status'] = 0; //待支付,通过notify 来确认
                    $pay_data['pay_type'] = 'authorize_app';
                    $pay_data['notify_url'] = 'http://m20.tourscool.net/api/tour/v1/payment/notify';
                    $pay_data['user_info'] = json_encode($user_info);
                    $pay_data['created'] = date("Y-m-d H:i:s");


                    DB::table("service_payments")->insert($pay_data);

                    $data =[
                        'transaction_id' => $tresponse->getTransId(),
                        'response_code' => $tresponse->getResponseCode(),
                        'message_code' =>  $tresponse->getMessages()[0]->getCode(),
                        'auth_code' => $tresponse->getAuthCode(),
                        'description' => $tresponse->getMessages()[0]->getDescription()
                    ];
                    return $this->success($data);
                } else {
                    if ($tresponse->getErrors() != null) {
                        $error_code = $tresponse->getErrors()[0]->getErrorCode();
                        $error_msg =  $tresponse->getErrors()[0]->getErrorText();
                        return $this->error(1,"$error_code,$error_msg");
                    }
                }
                // Or, print errors if the API request wasn't successful
            } else {

                $tresponse = $response->getTransactionResponse();

                if ($tresponse != null && $tresponse->getErrors() != null) {
                    $error_code = $tresponse->getErrors()[0]->getErrorCode();
                    $error_msg =  $tresponse->getErrors()[0]->getErrorText();
                } else {
                    $error_code = $response->getMessages()->getMessage()[0]->getCode();
                    $error_msg = $response->getMessages()->getMessage()[0]->getText();
                }
                return $this->error(1,"$error_code,$error_msg");
            }
        } else {
            return $this->error(1,"No response returned");
        }

    }

    public function authorizeNetNotify(Request $request)
    {

        Log::debug('Authorize net raw notify -------------->\n' . $request->getContent());
        Log::debug('Authorize net request header notify -------------->\n' , $request->header());
        Log::debug('Authorize net request notify -------------->\n' , $request->all());

        $sign_key = env("AUTHORIZENE_SIGNATURE_KEY");
        $content = $request->getContent();
        $sign_str = "sha512=" . hash_hmac("sha512",$content,$sign_key);
        $signature = $request->header("x-anet-signature");

        if(strtolower($sign_str) != strtolower($signature)){
            Log::debug('Authorize net signature is wrong -------------->\n' . $request->header("x-anet-signature"));
            throw new \Exception('signature is wrong');
        }

        $payload = $request->get("payload");
        $id = $payload['id'];
        $responseCode = $payload['responseCode'];

        try{

            if($responseCode == 1 ){
                $new_status = 1; //成功
            }else{
                $new_status = 2;
            }

            Log::debug('Authorize net request notify -------------->\n' , $request->all());

            DB::transaction(function () use ($id,$new_status) {
                $row = DB::table('service_payments')->where('id',$id)->lockForUpdate()
                    ->select('id','order_id','notify_url','pay_type','status')->first();
                if(!$row){
                    throw new \Exception("tx id is wrong");
                }
                // 状态:0待支付,1支付成功,2,支付失败
                if($row->status !=0){
                    //重复通知
                    return '';
                }

                $info =[
                    'order_id' => $row->order_id,
                    'status' => $new_status,
                    'transaction_id' => $id,
                    'pay_type' => $row->pay_type,
                ];

                $this->notifyClient($row->notify_url,$info);

                $updates=[
                    'status' => $new_status,
                    'transaction_id' => $id,
                    'updated' => date("Y-m-d H:i:s"),
                ];
                DB::table('service_payments')->where('id',$id)->update($updates);

            });

        } catch (\Exception $e) {
            throw $e;
        }

        return $this->success();  //http code =200 is ok

    }

    private function notifyClient($url,$data){

        $client = new \GuzzleHttp\Client();
        $res = $client->request('POST', $url, [
            'json' => [
                'order_id'=>$data['order_id'],
                'trade_no'=>$data['transaction_id'],
                "payment_type"=>$data['pay_type'],
                "status"=>$data['status'],
            ]
        ]);
        $resp = json_decode($res->getBody(),true);
        $code = $resp['code'] ?? 1;
        if($code != 0){
            throw new \Exception('client process failed');
        }

    }

    public function test()
    {
        return 'OK';
        Cache::put("sessionid","1234567890",10);

        return $this->success(Cache::get("sessionid"));


    }


}
