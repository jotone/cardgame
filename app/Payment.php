<?php
namespace App;

use Illuminate\Database\Eloquent\Model;

class Payment extends Model{
    protected $table = 'tbl_payment';
    protected $fillable = [
        'user_id','user_name', 'money_amount', 'gold_amount', 'pay_status', 'type', 'last_gold_status','last_exchange_status'
    ];
}