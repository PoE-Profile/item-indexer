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
        $bigData = json_decode(\Storage::disk('api')->get("poeData.json"));
        $uniqNames = json_encode(\Storage::disk('api')->get("names.json"));
        // dd($bigData);
        return view('search',compact('bigData'));
    }

    public function search(Request $request)
    {
        // dd(Input::All());
        $resultItems = [];
        $resultItems = Item::where(function($query) use($request)
        {
            if ($request->get('name')) {
                $query->name($request->get('name'));
            }
            if ($request->get('type')) {
              $query->type($request->get('type'));
            }
            if ($request->get('typeLine')) {
              $query->typeLine($request->get('typeLine'));
            }
            if ($request->get('rarity')) {
              $query->where('frameType', '=', $request->get('rarity'));
            }
            if ($request->get('league')) {
              $query->where('league', '=', $request->get('league'));
            }
            if ($request->get('corrupted')) {
              $query->whereRaw('corrupted =' . intval($request->get('corrupted')));
            }
            if ($request->get('buyout')) {
              $query->where('note', 'LIKE', '%b/o%');
            }
            $query->level($request->get('level_min'),$request->get('level_max'));
            $query->sockets($request->get('socket_min'),$request->get('socket_max'));
            $query->links($request->get('links_min'),$request->get('links_max'));

            if ($request->get('mods')) {
              foreach ($request->get('mods') as $mod) {
                $query->mod($mod);
              }
            }

        })->take(60)->get();
        // return $resultItems;
        $bigData = json_decode(\Storage::disk('api')->get("poeData.json"));
        return view('search',compact('bigData','resultItems'));
    }

}
