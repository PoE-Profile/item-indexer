<?php

namespace App;
use App\Item;

use Illuminate\Database\Eloquent\Model;

class Stash extends Model
{
    protected $fillable = ['username', 'email', 'password'];
    
  public function items()
  {
      return $this->hasMany('App\Item','stash_id');
  }

  public function addItems($items){
    foreach ($items as $i) {
      $new_item = new Item;
      $new_item->stashId = $this->id;
      if (array_key_exists("note", $i)) {
        $new_item->note = $i->note;
      }
      $new_item->ilvl = $i->ilvl;
      $new_item->identified = $i->identified;
      $new_item->corrupted = $i->corrupted;
      $new_item->name = str_replace("<<set:MS>><<set:M>><<set:S>>", "", $i->name);
      $new_item->typeLine = str_replace("<<set:MS>><<set:M>><<set:S>>", "", $i->typeLine);
      $new_item->identified = $i->identified;
      $new_item->corrupted = $i->corrupted;
      $new_item->league = $i->league;
      $new_item->frameType = $i->frameType;
      $new_item->w = $i->w;
      $new_item->h = $i->h;
      $new_item->x = $i->x;
      $new_item->y = $i->y;
      $new_item->icon = $i->icon;
      $new_item->itemId = $i->id;
      $new_item->inventoryId = $i->inventoryId;

      $new_item->save();

    }

  }
}
