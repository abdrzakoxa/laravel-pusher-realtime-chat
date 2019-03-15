<?php

namespace App\Http\Controllers\API;

use App\Events\listenMessage;
use App\message;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;

class chatController extends Controller
{
    public function sendMessage(Request $request) {
		$request->validate([
			'message' => 'required',
			'to_user_id' => 'required|exists:users,id',
		]);

		$message = new message();
		$message->message = $request->message;
		$message->to_user_id = $request->to_user_id;
		$message->from_user_id = auth()->user()->id;

		try {
			$message->save();
			event(new listenMessage($message));
			return response()->json(['status'=> 'success']);
		}
		catch (\Exception $e) {
			return response()->json(['status'=> 'error']);
		}
	}

	public function getMessagesAuthToUser($id) { // req

		$user_id = auth()->user()->id;
		$data = message::where(['to_user_id'=>$id,'from_user_id'=> $user_id])->orWhere('from_user_id', $id)->where('to_user_id',$user_id)->get();

		return response()->json(['status'=>'success','data'=> $data]);
	}

	public function readMessage($id){
    	$msg = message::find($id);
    	if (empty($msg)) {
			return response()->json(['status'=>'error']);
		}

    	$msg->is_read = 1;
    	$msg->update();
		return response()->json(['status'=>'success']);
	}


	public function readMessages($id){
		$msg = message::where('from_user_id',$id);
		if (empty($msg)) {
			return response()->json(['status'=>'error']);
		}
		$msg->update(['is_read'=>1]);
		return response()->json(['status'=>'success']);
	}

	public function getMessagesAuth() {

    	$authUserId = auth()->user()->id;
    	// total and from user and to user
		$message_detail = message::with(['fromUser','toUser'])->select(DB::raw('count(*) as total'),'from_user_id','to_user_id')->where('to_user_id',$authUserId)->where('is_read',0)->groupBy('from_user_id','to_user_id')->orderBy('created_at')->get();

		foreach ($message_detail as $key => $message) {
			$ka =  message::select('message','is_read')->where(['to_user_id'=>$authUserId,'from_user_id'=>$message->from_user_id])->where('is_read', 0)->get()->last();
			$message_detail[$key]->last_message = $ka->message;
			$message_detail[$key]->is_read = $ka->is_read;
		}


		return response()->json(['status'=>'success','data'=> $message_detail]);
	}




}
