<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Item;
use DB;
use Storage;


class Uniques extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'index:names {--uniques} {--baseType}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'take all unique names now! :)';

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
        $types = ['Weapons', 'Armours', ''];
        //
        if ($this->option('uniques')){
            $uniques = Item::select('name')->groupBy('name')->where('frameType', '=', 3)->get();
            $uniqueNames = [];
            foreach ($uniques as $un) {
                $uniqueNames[] = $un->name;
            }
            $uniqueNames = json_encode($uniqueNames);
            // dd($names);
            Storage::disk('api')->put('uniqueNames.json', $uniqueNames);
        }

        if ($this->option('baseType')) {
            $items = Item::groupBy('typeLine')->where('frameType', '=', 2)->get();
            $typeData = [];
            foreach ($items as $item) {
                $iconString = explode('/', $item->icon);
                if ($iconString[6] === 'Maps') {
                  continue;
                }
                if ($iconString[5] === '2DItems') {
                  $count = 0;
                  $typesIcon = [];
                  foreach ($iconString as $iStr) {
                    if ($count > 5 && !strpos($iStr, '.png') ) {
                      $typesIcon[] = $iStr;
                    }
                    $count ++;
                  }
                }
                $typeData[] = [
                    'type' => implode('/', $typesIcon),
                    'typeLine' => $item->typeLine
                ];
            }
            $typeData = json_encode($typeData);
            Storage::disk('api')->put('typeData.json', $typeData);
        }

    }
}
