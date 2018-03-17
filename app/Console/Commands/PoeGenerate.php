<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class PoeGenerate extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'poe:generate  {--mods}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create Json file for Search view, type/baseTypes and mods';

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
        $types = \DB::table('items')->select('type')->groupBy('type')->get();
        $allTypes = \App\Item::where('frameType', '=', 4)
                ->orWhere('frameType', '=', 0)
                ->where('typeLine', 'not like', "%Talisman%")
                ->where('typeLine', 'not like', "%Fragment%")
                ->where('typeLine', 'not like', "%Superior%")
                ->where('typeLine', 'not like', "%Shaped%")
                ->where('typeLine', 'not like', "%Sacrifice%")
                ->where('typeLine', 'not like', "%Mortal%")
                ->where('typeLine', 'not like', "%Key%")
                ->groupBy('typeLine')
                ->get();
        $jsonTypeAndTypeLine = [];
        $tempMaps = [];
        $tempGems = [];
        foreach ($types as $type) {
            $skypTypes = ['Currency', 'FishingRods', 'unknown'];
            if (in_array($type->type, $skypTypes)) { continue; }

            $diffQue = ['Gems', 'VaalGems', 'Essence', 'Divination', 'Support'];

            $baseTypes = $allTypes->where('type', $type->type)->toArray();

            $baseTypes = array_map(function($value){
                return $value['typeLine'];
            }, $baseTypes);

            $mapTypes = ['act4maps', 'AtlasMaps', 'Maps'];
            if (in_array($type->type, $mapTypes) ) {
                $tempMaps = $tempMaps + $baseTypes;
                $baseTypes = array_values($tempMaps);
                $jsonTypeAndTypeLine['Maps'] = [
                    'type' => 'Maps',
                    'baseTypes' => $baseTypes,
                ];
                continue;
            }

            $gemTypes = ['Gems', 'VaalGems', 'Support'];
            if (in_array($type->type, $gemTypes) ) {
                $tempGems = $tempGems + $baseTypes;
                $baseTypes = array_values($tempGems);
                $jsonTypeAndTypeLine['Gems'] = [
                    'type' => 'Gems',
                    'baseTypes' => $baseTypes,
                ];
                continue;
            }
            $baseTypes = array_values($baseTypes);

            $jsonTypeAndTypeLine[$type->type] = [
                'type' => $type->type,
                'baseTypes' =>$baseTypes,
            ];
        }
        $jsonTypeAndTypeLine = array_values($jsonTypeAndTypeLine);
        $this->info('Type and BaseType generated');

        $mods = \DB::table('mods')->get()->toArray();
        $mods=array_map(function($mod){ return  array('id' => $mod->id, 'name' => $mod->name); }, $mods);
        $this->info('Mods generated');

        $newData = [
            'TypeAndBaseType' => $jsonTypeAndTypeLine,
            'mods' => $mods
        ];
        $newData = json_encode($newData, JSON_PRETTY_PRINT);
        \Storage::disk('api')->put("poeData.json", $newData);

        $this->info('Json file is created');
    }
}
