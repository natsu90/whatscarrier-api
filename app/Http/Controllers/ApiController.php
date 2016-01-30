<?php namespace App\Http\Controllers;

use App\Http\Models\Device;
use App\Http\Models\Product;
use App\Http\Models\Receipt;
use Tymon\JWTAuth\Facades\JWTAuth;
use Illuminate\Http\Request;
use GuzzleHttp\Client;
use ReceiptValidator\GooglePlay\Validator as PlayValidator;
use ReceiptValidator\iTunes\Validator as iTunesValidator;
use Davibennun\LaravelPushNotification\Facades\PushNotification;
use Carbon\Carbon;
use Stevebauman\Location\Facades\Location;
use GuzzleHttp\Exception\ClientException;

class ApiController extends BaseController
{
    public function postAuth(Request $request)
    {
        $this->validate($request, [
            'platform' => 'required',
            'udid' => 'required'
        ]);

    	$device = Device::where('udid', $request->input('udid'))->first();

    	if(is_null($device)) {

            $country_code = $request->input('countryCode');
            if(!$country_code)
                $country_code = Location::get($request->header('x-forwarded-for'), 'countryCode');
    		
    		$device = new Device;
    		$device->fill($request->all());
            $device->country_code = $country_code;
    		$device->save();
    	}

        $device = Device::find($device->id);
		$token = JWTAuth::fromUser($device);

    	return [
            'credits' => $device->credits,
            'auth_token' => $token,
            'sender_id' => env('GCM_SENDER_ID')
        ];
    }

    public function getCredit()
    {
        $device = JWTAuth::parseToken()->toUser();
        
        if($device)
    	   return ['credits' => $device->credits];

        throw new \Exception("No device found");
    }

    public function postCheck(Request $request)
    {
        $this->validate($request, [
            'number' => 'required'
        ]);

    	$device = JWTAuth::parseToken()->toUser();
    	$countryCode = strtoupper($device->country_code);

        if($device->credits <= 0)
            throw new \Exception("Insufficient balance", 403);
         
        $client = new Client();
        $res = $client->get('https://lookups.twilio.com/v1/PhoneNumbers/'.$request->input('number'), 
            [
                'auth' =>  [env('TWILIO_KEY'), env('TWILIO_SECRET')], 
                'query' => ['Type' => 'carrier', 'CountryCode' => $countryCode],
                'exceptions' => false
            ]);

        if($res->getStatusCode() == 200) {
            $device->credits--;
            $device->save();

            $response = json_decode($res->getBody(), true);

            if($response['carrier']['name'] == null)
                return ['error' => 404, 'credits' => $device->credits];

            return [
                'country_code' => $response['country_code'],
                'phone_number' => $response['phone_number'],
                'carrier_name' => $response['carrier']['name'],
                'number_type' => $response['carrier']['type'],
                'credits' => $device->credits
            ];
        } else if($res->getStatusCode() == 404) {

            return ['error' => 404, 'credits' => $device->credits];
        } else {

            throw new \Exception(ClientException::getMessage(), 500);
        }
    }

    public function postRegisterPush(Request $request)
    {
        $device = JWTAuth::parseToken()->toUser();
        $device->push_token = $request->input('push_token');
        $device->save();

        $device->send('Push message is enabled', array('title' => 'Push message is enabled'));

        return 'OK';
    }

    public function sendPushMessage(Request $request)
    {
        $device = JWTAuth::parseToken()->toUser();
        $device->send($request->input('message'), array('title' => $request->input('title')));

        return 'OK';
    }

    public function getRefreshToken(Request $request)
    {
        $code = $request->input('code');
        if($code) {
            $client = new Client();
            $res = $client->post('https://accounts.google.com/o/oauth2/token', array(), [
                'grant_type' => 'authorization_code',
                'code' => $code,
                'client_id' => env('GOOGLE_CLIENT_ID'),
                'client_secret' => env('GOOGLE_CLIENT_SECRET'),
                'redirect_uri' => url('oauth2callback')
            ]);
            echo $res->getBody();
        }
        else
            return redirect()->to('https://accounts.google.com/o/oauth2/auth?scope=https://www.googleapis.com/auth/androidpublisher&response_type=code&access_type=offline&redirect_uri='.url('oauth2callback').'&client_id='.env('GOOGLE_CLIENT_ID'));
    }

    public function getNewRefreshToken(Request $request)
    {
        $client = new Client();
        $res = $client->post('https://accounts.google.com/o/oauth2/token', null, [
            'grant_type' => 'refresh_token',
            'client_id' => env('GOOGLE_CLIENT_ID'),
            'client_secret' => env('GOOGLE_CLIENT_SECRET'),
            'refresh_token' => $request->input('refresh_token')
        ]);
        echo $res;
    }

    public function validatePurchase(Request $request)
    {
        $device = JWTAuth::parseToken()->toUser();
        $this->validate($request, [
            'id' => 'required',
            'price' => 'required',
            'transaction' => 'required',
        ]);

        if(is_null($request->input('transaction')))
            throw new \Exception("This is a weird bug, transaction parameter is null. Restart app and it will be fine.", 403);
        
        $receipt = new Receipt;
        $receipt->fill($request->input());
        $receipt->device_id = $device->id;
        $receipt->product_id = $request->input('id');
        $receipt->transaction_id = $request->input('transaction.id');

        if(!$receipt->save())
            throw new \Exception("Invalid Receipt", 403);
            
        return ['credits' => Device::find($device->id)->credits];
    }
}
