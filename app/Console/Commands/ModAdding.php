<?php

namespace App\Console\Commands;

use App\Item;
use DB;

use Illuminate\Console\Command;

class ModAdding extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'poe:addMods';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'addin mods from items';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $mods = [];
        $upItems = [];

        $lastId = 1;
        if (Item::count() > 0) {
          $lastItem = Item::where('mods', '=', 1)->orderBy('id', 'desc')->first();

          if ($lastItem != null) {
            $lastId = $lastItem->id;
          }
        }

        $nextId = $lastId + 7000;
        $items = Item::where('mods', '=', null)->where('id', '<', $nextId)->get();

        DB::update('update items set mods=1 where id>=? and id<?', [$lastId, $nextId]);

        foreach ($items as $item) {
            $upItems[] = $item->id;

            if ($item->explicitMods != null) {
                $itemMods = json_decode($item->explicitMods);

                foreach ($itemMods as $mod) {
                    $average = $mod;
                    preg_match_all('!\d+!', $average, $matches);
                    if (count($matches[0]) > 1) {
                        $average = array_sum($matches[0]) / 2 ;
                    } else {
                        $average = reset($matches[0]);
                    }

                    $mods[] = [
                        'name' => preg_replace('/\d+/u', '#', $mod),
                        'value' => $average,
                        'item_id' => $item->id
                    ];
                }
            }

        }

        // Bulk mods to DB possiblle creating separate job for items and mods
        dd($mods);
        $modChunks = array_chunk($mods, 500);
        foreach($modChunks as $chunk) {
            DB::table('mods')->insert($chunk);
        }
        unset($mods);
    }
}
