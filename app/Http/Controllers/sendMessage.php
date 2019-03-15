<?php

namespace App\Http\Controllers;

use App\message;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class sendMessage extends Controller
{

	public static function sendMessage(Request $request){

//		$message = new message();
//		$message->message = $request->message;
//		$message->from_user_id = $request->from_user_id;
//		$message->to_user_id = $request->to_user_id;
//
////		$message->save();
//

		$data = message::with(['fromUser','toUser'])->where('to_user_id',Auth::user()->id)->orWhere('from_user_id',Auth::user()->id)->get();

		return response()->json(['status'=>'success','data'=> $data]);

	}


}
