<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Input;
use App\Stash;
use App\Item;
use App\Job;
use App\Mod;

use App\Http\Requests;

class SearchController extends Controller
{
    public function index()
    {
        return view('vue_search');
    }

    public function main(Request $request)
    {
    //   var_dump(input::all());

      $searchItem = [];
      $searchItem = Item::where(function($query)
      {
        $str = $dex = $int = $vaal = '';
        if (input::has('ex_red_socket')) {
          $str = Input::get('ex_red_socket') . 'R';
        }
        if (input::has('ex_green_socket')) {
          $dex = Input::get('ex_green_socket') . 'G';
        }
        if (input::has('ex_blue_socket')) {
          $int = Input::get('ex_blue_socket') . 'B';
        }
        if (input::has('ex_white_socket')) {
          $vaal = Input::get('ex_white_socket') . 'W' ;
        }

        $scoketColors = $str . $dex . $int . $vaal;
        if (Input::has('ex_red_socket') || Input::has('ex_green_socket') || Input::has('ex_blue_socket') || Input::has('ex_white_socket')) {
          $query->where('socketColor', 'LIKE', '%'. $scoketColors . '%');
        }


        if (Input::has('name')) {
          $query->name(Input::get('name'));
        }
        if (Input::has('type')) {
          $query->type(Input::get('type'));
        }
        if (Input::has('typeLine')) {
          $query->typeLine(Input::get('typeLine'));
        }
        if (Input::has('identified')) {
          $query->whereRaw('identified =' . Input::get('identified'));
        }
        if (Input::has('corrupted')) {
          $query->whereRaw('corrupted =' . Input::get('corrupted'));
        }
        if (Input::has('rarity')) {
          $query->where('frameType', '=', Input::get('rarity'));
        }
        if (Input::has('league')) {
          $query->where('league', '=', Input::get('league'));
        }
        if (Input::has('buyout')) {
          $query->where('note', 'LIKE', '%b/o%');
        }
        if (Input::has('level_min') && Input::has('level_max')) {
          $query->whereBetween('ilvl', [intval(Input::get('level_min')), intval(Input::get('level_max'))]);
        } elseif (Input::has('level_min')){
          $query->where('ilvl', '>=', intval(Input::get('level_min')));
        } elseif (Input::has('level_max')){
          $query->where('ilvl', '<=', intval(Input::get('level_max')));
        }

        if (Input::has('socket_min') && Input::has('socket_max')) {
          $query->whereBetween('socketNum', [intval(Input::get('socket_min')), intval(Input::get('socket_max'))]);
        } elseif (Input::has('socket_min')){
          $query->where('socketNum', '>=', intval(Input::get('socket_min')));
        } elseif (Input::has('socket_max')){
          $query->where('socketNum', '<=', intval(Input::get('socket_max')));
        }

        if (Input::has('links_min') && Input::has('links_max')) {
          $query->whereBetween('maxLinks', [intval(Input::get('links_min')), intval(Input::get('links_max'))]);
        } elseif (Input::has('links_min')){
          $query->where('maxLinks', '>=', intval(Input::get('links_min')));
        } elseif (Input::has('links_max')){
          $query->where('maxLinks', '<=', intval(Input::get('links_max')));
        }

        if (input::has('red_socket')) {
          $str = str_repeat("R", (int)Input::get('red_socket'));
          $query->where('validColor', 'LIKE', '%'. $str . '%');
        }
        if (input::has('green_socket')) {
          $dex = str_repeat("G", (int)Input::get('green_socket'));
          $query->where('validColor', 'LIKE', '%'. $dex . '%');
        }
        if (input::has('blue_socket')) {
          $int = str_repeat("B", (int)Input::get('blue_socket'));
          $query->where('validColor', 'LIKE', '%'. $int . '%');
        }
        if (input::has('white_socket')) {
          $vaal = str_repeat("W", (int)Input::get('white_socket'));
          $query->where('validColor', 'LIKE', '%'.$vaal.'%');
        }

        if (input::has('mod')) {
          for ($i=0; $i < count(input::all()['mod']); $i++) {
            if (array_key_exists($i, input::all()['mod'])) {
              $inpMods = input::all()['mod'][$i];
              $query->whereHas('mods', function($query) use (&$inpMods){
                if ($inpMods['id']) {
                    $query->where('mod_id', '=', $inpMods['id']);
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
          }
        }

    })->take(40)->get();

      if (Input::has('character')) {
        if (count($searchItem) > 0) {
          $lastCharacter = [];
          foreach ($searchItem as $Charc) {
            if ($Charc->stash->lastCharacterName === Input::get('character')) {
              $lastCharacter[] = $Charc;
            }
          }
          $searchItem = $lastCharacter;
        }
        if (empty($searchItem)) {
          $lastCharacter = [];
          $stashes = Stash::where('lastCharacterName', 'LIKE', Input::get('character'))->get();
          // if ($searchItem->count() == 1) {
          //   foreach ($searchItem as $Charc) {
          //     $lastCharacter[] = $Charc;
          //   }
          // }
          if ($stashes->count() > 0) {
            foreach ($stashes as $stash) {
              foreach ($stash->items as $item) {
                $searchItem[] = $item;
              }
            }
          }

        }

      }


      $userAcc = (\Auth::user()) ? \Auth::user()->account : '';
      $request->flash();
      $oldFields = \Session::getOldInput();
      $bigData = \Storage::disk('api')->get("poeData.json");
      $uniqNames = json_encode(\Storage::disk('api')->get("names.json"));

      return view('index', compact('searchItem', 'userAcc', 'oldFields', 'bigData', 'uniqNames'));
    }

}
