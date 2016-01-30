<?php namespace App\Http\Models;

use Illuminate\Database\Eloquent\Model;
use ReceiptValidator\GooglePlay\Validator as PlayValidator;
use ReceiptValidator\iTunes\Validator as iTunesValidator;
use App\Jobs\SendPurchaseEmail;
use Queue;

class Receipt extends Model {

	protected $table = 'Receipt';

	protected $fillable = ['price', 'transaction'];

	public $timestamps = true;

	public function device()
	{
		return $this->belongsTo('App\Http\Models\Device');
	}

	public function product()
	{
		return $this->belongsTo('App\Http\Models\Product');
	}

	public function setTransactionAttribute($value)
	{
		$this->attributes['transaction'] = json_encode($value);
	}

	public function getTransactionAttribute()
	{
		return json_decode($this->attributes['transaction']);
	}

	public static function boot()
	{
		static::creating(function($receipt) {

			return !$receipt->isExist() && $receipt->isValid();
		});

		static::created(function($receipt) {

            $receipt->device->credits += $receipt->product->credits;
            $receipt->device->save();
            //$this->dispatch(new SendPurchaseEmail($receipt));
		});
	}

	public function isExist()
	{
		return static::where('transaction_id', $this->transaction->id)->count() > 0;
	}

	public function isValid()
	{
		if($this->device->isAndroid()) {

			$validator = new PlayValidator([
               'client_id' => env('GOOGLE_CLIENT_ID'),
               'client_secret' => env('GOOGLE_CLIENT_SECRET'),
               'refresh_token' => env('GOOGLE_REFRESH_TOKEN')
            ]);

            $transaction_receipt = json_decode($this->transaction->receipt);

            try {

            	$response = $validator->setPackageName($transaction_receipt->packageName)
                	->setProductId($transaction_receipt->productId)
                	->setPurchaseToken($transaction_receipt->purchaseToken)
                	->validate();
            } catch(\Exception $e) {
            	return false;
            }
            return true;

		} else if($this->device->isIos()) {

			$validator = new iTunesValidator(iTunesValidator::ENDPOINT_PRODUCTION);
			try {

    			$response = $validator->setReceiptData($this->transaction->transactionReceipt)->validate();
			} catch (Exception $e) {
    			return false;
			}

			return $response->isValid();
		}

		return false;
	}
}