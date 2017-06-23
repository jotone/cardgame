<?php
namespace App\Http\Controllers\Site;

use App\EtcData;
use App\Payment;
use App\User;

use Illuminate\Routing\Controller as BaseController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PaymentController extends BaseController
{
	public function success(Request $request){
		$data = $request->all();
		if(!empty($data['label'])){
			$label = base64_decode($data['label']);
			$operation = explode('_',$label);
			if(count($operation) == 2){
				$payment = Payment::find($operation[1]);
				if(($payment->user_id == $operation[0]) && ($payment->pay_status == 0)){
					$payment->pay_status = 1;
					$payment->save();

					$user = User::find($operation[0]);
					$user->user_gold = $user->user_gold + $payment->gold_amount;
					$user->save();
				}
			}
		}
		return 'success';
	}

	public function createPayStory(Request $request){
		$data = $request->all();
		$user = Auth::user();
		if($user){
			$usd_to_gold = EtcData::select('meta_value')
				->where('label_data','=','exchange_options')
				->where('meta_key','=','usd_to_gold')
				->first();
			$usd_to_rub = EtcData::select('meta_value')
				->where('label_data','=','exchange_options')
				->where('meta_key','=','rub_to_usd')
				->first();
			$gold = floor($data['money'] * $usd_to_gold->meta_value);
			$rub = round($data['money'] * $usd_to_rub->meta_value, 2);

			$koef = 0;
			switch($data['type']){
				case 'PC': $koef = $rub - $rub * (1/1.005); break;
				case 'AC': $koef = $rub - $rub * 0.98; break;
			}
			$rub = round(($rub + $koef), 2);
		}

		$result = Payment::create([
			'user_id'		=> $user['id'],
			'user_name'		=> $user['login'],
			'money_amount'	=> $rub,
			'gold_amount'	=> $gold,
			'type'			=> $data['type'],
			'pay_status'	=> 0,
			'last_gold_status' => $user['user_gold'],
			'last_exchange_status' => $usd_to_rub->meta_value
		]);
		if($result !== false){
			return json_encode([
				'message'		=> 'success',
				'transaction'	=> $result->id
			]);
		}else{
			return 'fail';
		}
	}
}