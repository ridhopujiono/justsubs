<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;
use Ridho\JustSubs\Concerns\HasSubscriptions;

class Company extends Model
{
    use HasSubscriptions, HasUlids;

    protected $guarded = [];
}
