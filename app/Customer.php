<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Customer extends Model
{
    //
    protected $fillable = ['fullName', 'email', 'authCode', 'due_date', 'billable'];

    public function transactions()
    {
        return $this->hasMany('App\Transaction');
    }
}
