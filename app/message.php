<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class message extends Model
{

	public function fromUser(){
		return $this->hasOne(User::class,'id','from_user_id');
	}

	public function toUser(){
		return $this->hasOne(User::class,'id','to_user_id');
	}
}
