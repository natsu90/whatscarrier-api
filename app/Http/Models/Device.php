<?php namespace App\Http\Models;

use Illuminate\Database\Eloquent\Model;
use Davibennun\LaravelPushNotification\Facades\PushNotification;

class Device extends Model {

	protected $table = 'Device';

	protected $fillable = ['platform', 'udid', 'country_code'];

	public $timestamps = true;

	public function send($message, $data)
	{
		if($this->isAndroid()) {
			$push = PushNotification::app(['environment' => 'production',
            	'apiKey'      => env('GCM_API_KEY'),
            	'service'     => 'gcm']);

		} else if($this->isIos()) {
			$push = PushNotification::app(['environment' => 'development',
            	'certificate' => base_path('ck.pem'),
        		'passPhrase'  => 'push',
            	'service'     => 'apns']);
		}
		if(isset($push))
			$push->to($this->push_token)->send($message, $data);
	}

	public function isAndroid()
	{
		return strtolower($this->platform) == 'android';
	}

	public function isIos()
	{
		return strtolower($this->platform) == 'ios';
	}
}