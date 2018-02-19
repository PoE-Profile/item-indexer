<?php

namespace App;
use App\Item;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Mod extends Model
{
    use SoftDeletes;

    protected $dates = ['deleted_at'];
    protected $table = 'item_mods';

    //
    public function item()
    {
      return $this->belongsTo('App\Item');
    }
}
