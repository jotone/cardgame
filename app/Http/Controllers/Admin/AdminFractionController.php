<?php
namespace App\Http\Controllers\Admin;

use \App\Fraction;
use App\Http\Controllers\Admin\AdminFunctions;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Http\Request;

class AdminFractionController extends BaseController
{
	public function addFraction(Request $request){
		$data = $request->all();

		if($data['title'] == ''){
			return json_encode(['message' => 'Не указано название рассы.']);
		}

		if($data['slug'] == ''){
			return json_encode(['message' => 'Не указано обозначение рассы.']);
		}

		//Если была выбрана картинка
		$img_file = AdminFunctions::createImg($data['img_url'], 'fractions_images');

		//Если был указа Бэкграунд фракции
		$bg_file = AdminFunctions::createImg($data['bg_img'], 'fractions_images');

		//Если была указана рубашка фракции
		$card_file = AdminFunctions::createImg($data['card_img'], 'fractions_images');

		$result = Fraction::create([
			'title'			=> $data['title'],
			'slug'			=> $data['slug'],
			'type'			=> $data['type'],
			'img_url'		=> $img_file,
			'bg_img'		=> $bg_file,
			'card_img'		=> $card_file,
			'cards'			=> 'a:0:{}',
			'description'	=> $data['description'],
			'short_description'=> $data['short_description'],
			'descr_shop'	=> $data['descr_shop'],
			'descr_magic'	=> $data['descr_magic']
		]);

		if($result != false){
			return json_encode(['message' => 'success']);
		}else{
			return $result;
		}
	}

	public function editFraction(Request $request){
		$data = $request->all();

		if($data['title'] == ''){
			return json_encode(['message' => 'Не указано название рассы.']);
		}

		if($data['slug'] == ''){
			return json_encode(['message' => 'Не указано обозначение рассы.']);
		}

		$fraction_to_edit = Fraction::find($data['id']);

		$img_file = ($data['img_url'] != 'undefined')
			? AdminFunctions::createImg($data['img_url'], 'fractions_images')
			: $fraction_to_edit -> img_url;
		$bg_file = ($data['bg_img'] != 'undefined')
			? AdminFunctions::createImg($data['bg_img'], 'fractions_images')
			: $fraction_to_edit -> bg_img;
		$card_file = ($data['card_img'] != 'undefined')
			? AdminFunctions::createImg($data['card_img'], 'fractions_images')
			: $fraction_to_edit -> card_img;

		$fraction_to_edit -> title = $data['title'];
		$fraction_to_edit -> slug = $data['slug'];
		$fraction_to_edit -> type = $data['type'];
		$fraction_to_edit -> img_url = $img_file;
		$fraction_to_edit -> bg_img = $bg_file;
		$fraction_to_edit -> card_img = $card_file;
		$fraction_to_edit -> description = $data['description'];
		$fraction_to_edit -> short_description = $data['short_description'];
		$fraction_to_edit -> descr_shop = $data['descr_shop'];
		$fraction_to_edit -> descr_magic = $data['descr_magic'];
		$fraction_to_edit -> save();

		if($fraction_to_edit != false){
			return json_encode(['message' => 'success']);
		}else{
			return $fraction_to_edit;
		}
	}

	public function dropFraction(Request $request){
		$dropFraction = Fraction::find($request->input('fraction_id'));
		$result = $dropFraction -> delete();
		if($result !== false){
			return redirect(route('admin-main'));
		}
	}

	public function saveBaseDeck(Request $request){
		$data = $request->all();
		$deck = json_decode($data['deckArray']);

		$result_deck = [];
		for($i=0; $i<count($deck); $i++){
			if($i %2 == 1){
				$result_deck[] = [
					'id' => $deck[$i-1],
					'q'  => $deck[$i]
				];
			}
		}

		$result = Fraction::where('slug', '=', $data['deckType'])->update(['cards' => serialize($result_deck)]);
		if($result != false){
			return 'success';
		}
	}
}