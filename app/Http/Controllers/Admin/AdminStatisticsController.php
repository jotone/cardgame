<?php
namespace App\Http\Controllers\Admin;

use App\League;
use App\SummaryLeague;
use App\Http\Controllers\Admin\AdminFunctions;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Http\Request;

class AdminStatisticsController extends BaseController
{
	public function index(){
		$statistic = SummaryLeague::orderBy('league','asc')->get();
		$list = [];

		foreach ($statistic as $item){
			$league = League::select('title')->where('slug','=',$item->league)->first();
			$list[] = [
				'league'=> $league->title,
				'knight'=> unserialize($item->knight),
				'forest'=> unserialize($item->forest),
				'highlander'=> unserialize($item->highlander),
				'cursed'=> unserialize($item->cursed),
				'undead'=> unserialize($item->undead),
				'monsters'=> unserialize($item->monsters)
			];
		}

		return view('admin.statistics', [
			'list'=> $list
		]);
	}

	public function resetSummary(){
		$result = SummaryLeague::where('id','!=',0)->update([
			'knight'=>'a:3:{s:3:"win";i:0;s:4:"fair";i:0;s:5:"leave";i:0;}',
			'forest'=>'a:3:{s:3:"win";i:0;s:4:"fair";i:0;s:5:"leave";i:0;}',
			'highlander'=>'a:3:{s:3:"win";i:0;s:4:"fair";i:0;s:5:"leave";i:0;}',
			'cursed'=>'a:3:{s:3:"win";i:0;s:4:"fair";i:0;s:5:"leave";i:0;}',
			'undead'=>'a:3:{s:3:"win";i:0;s:4:"fair";i:0;s:5:"leave";i:0;}',
			'monsters'=>'a:3:{s:3:"win";i:0;s:4:"fair";i:0;s:5:"leave";i:0;}',
		]);
		if($result == false){
			dd($result);
		}else{
			return redirect()->route('admin-statistics');
		}
	}
}