<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class battleCleanUp extends Command
{
	/**
	 * The name and signature of the console command.
	 *
	 * @var string
	 */
	protected $signature = 'battleCleanUp';

	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'Drop finished battles.';

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
		/*Every 30 minutes*/
		/*Finished battles*/
		$date = date('Y-m-d H:i:s', time()-1800);
		\DB::table('tbl_battles')->select('fight_status','updated_at')
			->where('fight_status','=','3')
			->orWhere('updated_at','<',$date)
			->delete();

		/*Expire premium users*/
		$date = date('Y-m-d').' 00:00:00';
		$users = \DB::table('users')->select('id','premium_activated','premium_expire_date')
			->where('premium_activated','=',1)
			->where('premium_expire_date','<',$date)
			->get();
		foreach($users as $user){
			\DB::table('users')->where('id','=',$user->id)->update(['premium_activated'=>0]);
		}
	}
}
