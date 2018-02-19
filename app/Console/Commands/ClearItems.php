<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class ClearItems extends Command
{
    private $time_start;
    private $debug=true;
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'clear:items {--items-limit=0} {--mods-limit=0}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command clearing all the soft deleted items and mods';

    /**
     * Create a new command instance.
     *
     * @return void
     */
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
        $defaultItems = $this->option('items-limit') ? $this->option('items-limit') : 10000;
        $defaultMods = $this->option('mods-limit') ? $this->option('mods-limit') : 10000;
        $this->logTime('Clearing items', true);

        $items = \DB::table('items')->whereNotNull('deleted_at')->take($defaultItems)->get();
        $itemsChunks = array_chunk($items, 1000);
        $delDateTime=date("Y-m-d H:i:s");
        foreach($itemsChunks as $chunk) {
            $ids_to_delete = array_map(function($item){ return  $item->id; }, $chunk);
            \DB::table('item_mods')->whereIn('item_id', $ids_to_delete)->delete();
        }
        $this->logTime('Finish Deleting mods on items:'.count($items));

        \DB::table('items')->whereNotNull('deleted_at')->take($defaultItems)->delete();
        $this->logTime('Deleted items! ');
        // $this->logTime('Clearing mods', true);
        // \DB::table('item_mods')->whereNotNull('deleted_at')->take($defaultMods)->delete();
        // $this->logTime('Deleted mods: ' . $defaultMods);
        // \DB::raw("delete from `items` where `deleted_at` is not null limit :100");
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
        $message = 'Time '.$execution_time;
      echo $msg." ".$message."\n";
      \Log::info($message);

      //$this->comment('Time '.$execution_time);
      if($reset)
        $this->time_start = microtime(true);
    }
}
