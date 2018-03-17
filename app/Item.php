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

  protected $casts = [
          'implicitMods' => 'array',
          'explicitMods' => 'array',
          'craftedMods' => 'array',
          'enchantMods' => 'array',
          'properties' => 'array',
          'requirements' => 'array'
      ];
    public function getSocketsAttribute($value)
    {
      return json_decode($value);
    }

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
      $this->stash()->where('lastCharacterName', 'LIKE', '%' . $charName . '%');
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


    public function scopeTypeLine($scope, $typeLine)
    {
      $scope->where('typeLine', 'LIKE', '%' .  $typeLine . '%');
    }

    public function scopeLevel($scope,$min,$max){
        if ($min && $max) {
          $scope->whereBetween('ilvl', [intval($min), intval($max)]);
        } elseif ($min){
          $scope->where('ilvl', '>=', intval($min));
        } elseif ($max){
          $scope->where('ilvl', '<=', intval($max));
        }
    }

    public function scopeLinks($scope,$min,$max){
        if ($min && $max) {
          $scope->whereBetween('maxLinks', [intval($min), intval($max)]);
        } elseif ($min){
          $scope->where('maxLinks', '>=', intval($min));
        } elseif ($max){
          $scope->where('maxLinks', '<=', intval($max));
        }
    }

    public function scopeSockets($scope,$min,$max){
        if ($min && $max) {
          $scope->whereBetween('socketNum', [intval($min), intval($max)]);
        } elseif ($min){
          $scope->where('socketNum', '>=', intval($min));
        } elseif ($max){
          $scope->where('socketNum', '<=', intval($max));
        }
    }

    public function scopeMod($scope,$inpMods){
      $scope->whereHas('mods', function($query) use (&$inpMods){
          if ($inpMods['mod_id']) {
            $query->where('mod_id', '=', $inpMods['mod_id']);
          }
          if ($inpMods['min'] && $inpMods['max']) {
            $query->whereBetween('value', [intval($inpMods['min']), intval($inpMods['max'])]);
          } elseif ($inpMods['min']){
            $query->where('value', '>=', intval($inpMods['min']));
          } elseif ($inpMods['max']){
            $query->where('value', '<=', intval($inpMods['max']));
          }
      });
    }
    protected static function boot() {
        parent::boot();

        static::deleting(function($item) {
            // dd('hello from deleting items: ');
            $item->mods()->delete();
        });
    }


}
