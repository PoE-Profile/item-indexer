<?php

namespace App\Console\Commands;

use DB;
use App\Item;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class ProcessMods extends Command
{
    /**
     * The name and signature of the console command.
     * @var string
     */
    protected $signature = 'process:mods {--import}';

    /**
     * The console command description.
     * @var string
     */
    protected $description = 'Command description';

    private $time_start;
    private $debug=true;
    private $dbMods = array();

    public function __construct()
    {
        parent::__construct();
        $this->time_start = microtime(true);
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        // $allMods=DB::table('mods')->get();
        // $allMods=array_map(function($mod){ return  array($mod->name=>$mod->id); }, $allMods);
        // $allMods=array_collapse($allMods);
        // var_dump($allMods["-# Physical Damage taken from Projectile Attacks"]);
        // die;
        $allMods=DB::table('mods')->get();
        $allMods=array_map(function($mod){ return  array($mod->name=>$mod->id); }, $allMods);
        $this->dbMods=array_collapse($allMods);

        if($this->option('import')){
            $mods = DB::table('item_mods')
             ->select('id','name')
             ->groupBy('name')
             ->get();
            $newMods=array();
            foreach ($mods as $mod) {
              $name = preg_replace('/\d+/u', '#', $mod->name);
              if (!array_key_exists($name, $this->dbMods)) {
                $newMods[]= ['name' => $mod->name];
              }
            }
            // dd(array_collapse($newMods));
            DB::table('mods')->insert($newMods);
            die;
        }

        $this->comment("Start checking for items:");
        do {
            $items = Item::where('mods', '=', false)->orderBy('id', 'ASC')->take(7000)->get();
            if(count($items)>0){
                $this->logTime("",true);
                $this->processMods($items);
            }else{
                sleep(3);
            }
            sleep(1);
        } while (true);

    }

    private function processMods($items){
        $mods = [];

        $lastId = $items->first()->id;
        $nextId = $items->last()->id;

        $numItems=count($items);
        $this->info("start procesing items");
        $this->logTime("from $lastId to $nextId count $numItems");

        foreach ($items as $item) {
          if ($item->explicitMods != null) {
              $itemMods [] = json_decode($item->explicitMods);
              $itemMods [] = json_decode($item->implicitMods);
              $itemMods [] = json_decode($item->craftedMods);
              $itemMods = array_collapse($itemMods);

              $totalMods = $this->precessTotal($itemMods, $item->id);
              foreach ($totalMods as $totalMod) {
                  array_push($mods,$totalMod);
              }

              foreach ($itemMods as $mod) {
                  $average = $mod;
                  preg_match_all('!\d+!', $average, $matches);
                  if (count($matches[0]) > 1) {
                      $average = array_sum($matches[0]) / 2 ;
                  } else {
                      $average = reset($matches[0]);
                  }

                  $name = preg_replace('/\d+/u', '#', $mod);
                  $db_mod_id=0;
                  if (array_key_exists($name, $this->dbMods)) {
                      $db_mod_id=$this->dbMods[$name];
                  }
                  $mods[] = [
                      'name' =>$name,
                      'value' => $average,
                      'item_id' => $item->id,
                      'mod_id'=>$db_mod_id
                  ];

              }
          }

        }
        $this->logTime('finish procesing items');

        // Bulk mods to DB possiblle creating separate job for items and mods
        $modChunks = array_chunk($mods, 1000);
        foreach($modChunks as $chunk) {
          DB::table('item_mods')->insert($chunk);
        }
        unset($mods);
        $this->logTime('finish inserting mods items');

        //set items as index for mods
        DB::update('update items set mods=1 where id>=? and id<=?', [$lastId, $nextId]);
        $this->logTime('finish update items items');

    }

    private function precessTotal($itemMods, $id){
        $pseudoMods = new \App\Parse_mods\CalcucaltePseudoMods;
        $pseudoMods->addMods($itemMods);
        $result = $pseudoMods->getMods();
        $result = array_filter($result, function($val){
            return $val->total !== 0;
        });

        $mods = [];
        foreach ($result as $key => $value) {
            $db_mod_id = 0;
            if (array_key_exists($key, $this->dbMods)) {
                $db_mod_id=$this->dbMods[$key];
            }
            $mods[] = [
                'name' => $key,
                'value' => $value->total,
                'item_id' => $id,
                'mod_id' => $db_mod_id,
            ];
        }

        return $mods;
    }

    private function logTime($msg,$reset=false)
    {
      if(!$this->debug){
        return;
      }

      $time_end = microtime(true);
      //dividing with 60 will give the execution time in minutes other wise seconds
      $execution_time = ($time_end - $this->time_start);///60;

      //execution time of the script
      $message="";
      if(!$reset)
        $message = ' Time '.$execution_time;
      echo $msg." ".$message."\n";
      Log::info($message);

      //$this->comment('Time '.$execution_time);
      if($reset)
        $this->time_start = microtime(true);
    }

}
