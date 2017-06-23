<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class checkOfflineUsers extends Command
{
	/**
	 * The name and signature of the console command.
	 *
	 * @var string
	 */
	protected $signature = 'checkOfflineUsers';

	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'Command description';

	/**
	 * Create a new command instance.
	 *
	 * @return void
	 */
	public function __construct()
	{
		parent::__construct();
	}

	/**
	 * Execute the console command.
	 *
	 * @return mixed
	 */
	public function handle()
	{
		/*Every 4 minutes*/
		/*Online counters*/
		$date = date('Y-m-d H:i:s', time()-240);
		$users = \DB::table('users')->select('id','user_online','user_busy')
			->where('user_online','=',1)
			->where('user_busy','=',1)
			->get();

		foreach($users as $user){
			$battle = \DB::table('tbl_battle_members')->select('user_id','updated_at')->where('user_id','=',$user->id)->get();
			if(!isset($battle[0])){
				\DB::table('users')->where('id','=',$user->id)->update(['user_busy'=>0]);
			}else if($battle[0]->updated_at < $date){
				\DB::table('users')->where('id','=',$user->id)->update(['user_busy'=>0]);
			}
		}

		$payments = \DB::table('tbl_payment')->select('id','pay_status','created_at')->where('pay_status','=',0)->get();
		foreach($payments as $payment){
			if(time() > (strtotime($payment->created_at)+86400)){
				\DB::table('tbl_payment')->where('id','=',$payment->id)->delete();
			}
		}

		$date = date('Y-m-d H:i:s', time()-240);
		\DB::table('users')->select('id','user_online','updated_at')
			->where('user_online','>',0)
			->where('updated_at','<',$date)
			->update(['user_online' => 0, 'user_busy'=>0]);
	}
}
