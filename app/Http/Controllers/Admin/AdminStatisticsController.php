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
}