<?php

namespace App;

use App\Stash;
use App\Mod;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Item extends Model
{
  use SoftDeletes;

  protected $dates = ['deleted_at'];

  public function stash()
  {
      return $this->belongsTo('App\Stash','stash_id','id');
  }

  public function mods()
  {
      return $this->hasMany('App\Mod', 'item_id', 'id');
  }

  public function charname($charName)
  {
      // $scope->stash()->where('lastCharacterName', 'LIKE', '%' . $charName . '%');
      // dd($scope->stash->take(2)->get());
      $this->stash()->where('lastCharacterName', 'LIKE', '%' . $charName . '%');
  }

  public function requirements()
  {
      $requirements = json_decode($this->requirements);
      return $requirements;
  }

  public function implicitMods()
  {
      $requirements = json_decode($this->implicitMods);
      return $requirements;
  }

  public function explicitMods()
  {
      $requirements = json_decode($this->explicitMods);
      return $requirements;
  }

  public function scopeName($scope, $name)
  {
      $scope->where('name', 'LIKE', '%' . $name . '%');
  }

  // public function scopeOrName($scope, $name)
  // {
  //     $scope->where('name', 'LIKE', '%' . $name . '%');
  // }

  public function scopeType($scope, $type)
  {
      $scope->where('type', '=',  $type);
  }

  // public function scopeOrType($scope, $type)
  // {
  //     $scope->orWhere('icon', 'LIKE', '%' . $type . '%');
  // }

  public function scopeTypeLine($scope, $typeLine)
  {
      $scope->where('typeLine', 'LIKE', '%' .  $typeLine . '%');
  }

  protected static function boot() {
    parent::boot();

    static::deleting(function($item) {
        // dd('hello from deleting items: ');
        $item->mods()->delete();
    });
  }


}
