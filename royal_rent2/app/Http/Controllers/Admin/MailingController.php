<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Supply\Functions;
use App\PageContent;
use App\Subscribers;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller as BaseController;
use Auth;
use Crypt;
use Validator;

class MailingController extends BaseController
{
	public function mailingAddPattern(Request $request){
		$data = $request->all();
		$result = PageContent::create([
			'title'		=> $data['title'],
			'caption'	=> $data['template'],
			'type'		=> 'mail_pattern',
			'content'	=> $data['pattern']
		]);
		if($result != false){
			return json_encode(['message' => 'success', 'id'=>$result->id]);
		}
	}

	public function mailingDropPattern(Request $request){
		$data = $request->all();
		$result = PageContent::where('id','=',$data['id'])->delete();
		if($result != false){
			return json_encode(['message'=>'success']);
		}
	}

	public function mailingAddTemplate(Request $request){
		$data = $request->all();

		if( (isset($data['id'])) && (!empty($data['id'])) ){
			$result = PageContent::find($data['id']);
			$result ->title		= $data['caption'];
			$result ->content	= serialize([
				'sender'	=> $data['sender'],
				'receiver'	=> $data['receiver'],
				'replyer'	=> $data['replyer'],
				'text'		=> $data['text']
			]);
			$result ->save();
		}else {
			$result = PageContent::create([
				'title'	=> $data['caption'],
				'caption' => Functions::str2url($data['caption']),
				'type'	=> 'mail_template',
				'content'	=> serialize([
					'sender'	=> $data['sender'],
					'receiver'	=> $data['receiver'],
					'replyer'	=> $data['replyer'],
					'text'		=> $data['text']
				])
			]);
		}
		if($result != false){
			return json_encode(['message'=>'success']);
		}
	}

	public function mailingDrop(Request $request){
		$data = $request->all();
		$result = PageContent::where('id','=',$data['id'])->delete();
		if($result != false){
			return json_encode(['message' => 'success']);
		}
	}

	public function dispatchAdd(Request $request){
		$data = $request->all();
		$isset = PageContent::where('content','=',$data['text'])->count();
		if($isset < 1){
			$slug = Functions::str2url($data['title']).uniqid();
			$result = PageContent::create([
				'title'		=> $data['title'],
				'caption'	=> $slug,
				'type'		=> 'dispatch',
				'content'	=> $data['text']
			]);
			if($result != false){
				return json_encode(['message' => 'success']);
			}
		}
	}

	public function dispatchGetTemplate(Request $request){
		$data = $request->all();
		if(isset($data['slug'])){
			$result = PageContent::where('type','=','dispatch')->where('caption','=',$data['slug'])->first();
			if($result != false){
				return json_encode([
					'message'	=> 'success',
					'title'		=> $result->title,
					'text'		=> $result->content
				]);
			}
		}
	}

	public function dispatchMake(Request $request){
		$data = $request->all();
		$mails = Subscribers::select('email')->get();
		$message = '
		<html>
			<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
			<head>
				<title>'.$data['title'].'</title>
			</head>
			<body>'.$data['text'].'</body>
		</html>';

		$headers  = "Content-type: text/html; charset=utf-8 \r\n";
		$headers .= "From: RoyalRent <info@royalrent.ru>\r\n";
		$headers .= "Reply-To: no-reply\r\n";
		foreach($mails as $mail) {
			mail($mail->email, $data['title'], $message, $headers);
		}
		return 'success';
	}
}