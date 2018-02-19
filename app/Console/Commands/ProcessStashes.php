<?php

namespace App\Console\Commands;

use Storage;
use DB;
use App\Stash;
use App\Item;
use App\ApiPages;
use Illuminate\Support\Facades\Log;
use Illuminate\Console\Command;

class ProcessStashes extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'process:stashes  {--mods}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    protected $api;
    private $time_start;
    private $debug=true;
    private $insertItems = array();
    private $updateItems = array();

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
        $this->time_start = microtime(true);
        $this->debug=config('app.debug');
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $this->comment("Start checking for new ids:");
        do {
          $next_page = ApiPages::where('processed', '0')->orderBy('created_at', 'ASC')->first();
          //if page ids difrent start new job
          if($next_page){
            $this->logTime("",true);
            if($this->debug){
              $this->comment("start proces json for :".$next_page);
            }else{
              $this->comment("start proces json for id: ".$next_page->id);
            }
            $this->process_stash($next_page);
          }
          //$this->comment("no new ids..");
          // sleep(1);
          usleep(700000);
        } while (true);
    }

    private function process_stash($page)
    {
        if(!isset($this->insertItems)){
            $this->insertItems=array();
        }
        // if no local json file stop procesing
        $id=$page->pageId;
        if (!Storage::disk('local')->has($id . '.txt')||$page->items==0) {
            $page->processed=true;
            $page->save();
            return;
        }

        // proces json
        $json = Storage::disk('local')->get($id . '.txt');
        $this->api = json_decode($json);

        //make an empty arrays to store data for bulks
        $newStashes = array();
        $itemIdsToDel = array();
        $lastStashId=0;
        $lastStash = Stash::orderBy('id', 'desc')->first();
        //$lastStash = DB::table('stashes')->select('id')->orderBy('id', 'desc')->first();
        if($lastStash){
          $lastStashId=$lastStash->id;
        }

        //remove old stashes
        $ids_to_delete = array_map(function($stash){ return  $stash->id; }, $this->api->stashes);
        $dbStashes = \App\Stash::whereIn('poeStashId', $ids_to_delete)->get();
        $dbStashes = $dbStashes->map(function ($stash, $key) {
          return array($stash['poeStashId'] => $stash);
        })->collapse();

        $this->logTime('start processing');

        foreach ($this->api->stashes as $stash) {

            // $skip_leagues = array("Standard", "Hardcore", "SSF Standard", "SSF Hardcore");
            $skip_leagues = array();
            if (count($stash->items)>0){
                if(in_array($stash->items[0]->league, $skip_leagues)) {
                    //dump("skip: ".$stash->items[0]->league);
                    continue;
                }else{
                    // dd($stash->items[0]->league);
                }
            }else{
                continue;
            }

            $dbStashId=0;
            $dbStashNote = substr($stash->stash, 0, 1) == '~' ? $stash->stash : '';
            // $this->info($dbStashNote);
            $stash_exists=array_key_exists($stash->id, $dbStashes->toArray());
            //set last stash id for items to insert and old items array
            if($stash_exists){
              $oldStashItems=json_decode($dbStashes[$stash->id]->current_items,true);
              $dbStashId=$dbStashes[$stash->id]->id;
            }else{
              //new stash set old items to empty array
              $oldStashItems=array();
              $lastStashId++;
              $dbStashId=$lastStashId;
            }

            //proces items
            $curentStashItems=array();
            $curentItemsIdsInStash=array();//array for calculating what to remove
            $stashLeague='Standard';
            foreach ($stash->items as $item) {
                if(array_key_exists($item->id, $oldStashItems)){
                  //add id of item to array with all items stil in stash
                  //to calculate what to remove from stash
                  $curentItemsIdsInStash[]=$item->id;

                  //check old and new hash if difrent add to update array
                  $newItemHash=$this->getItemHash($item);
                  $oldHash=$oldStashItems[$item->id];
                  if($newItemHash!=$oldHash){
                      $this->addToInsertItems($item,$dbStashId,$dbStashNote);
                      $itemIdsToDel[]=$item->id;
                  }
                }else{
                  $this->addToInsertItems($item,$dbStashId,$dbStashNote);
                }
                $curentStashItems[$item->id]=$this->getItemHash($item);
                $stashLeague=$item->league;
            }

            //calculate removed items from stash
            $temp=array_diff(array_keys($oldStashItems),$curentItemsIdsInStash);
            $itemIdsToDel=array_merge($itemIdsToDel,$temp);
            // if($stash->id=="bba715601faec2f491823439efc63c9426805902a12742c232d506452ae2e5cc"){
            //     var_dump($curentItemsIdsInStash);
            //     var_dump(array_keys($oldStashItems));
            //     var_dump($temp);
            // }

            //update info or add new stash
            if ($stash_exists) {
              //insert to updateStash array
              $updateStashesInfo = [
                  'stash' => $stash->stash,
                  'lastCharacterName' => $stash->lastCharacterName,
                  'current_items' => json_encode($curentStashItems),
                  'updated_at' => date("Y-m-d H:i:s"),
                  'league' => $stashLeague
              ];
              //$dbStashes[$stash->id]->update($updateStashesInfo);
              DB::table('stashes')
                ->where('poeStashId', $stash->id)
                ->update($updateStashesInfo);
            } else {
              $newStashes[] = [
                  'accountName' => $stash->accountName,
                  'lastCharacterName' => $stash->lastCharacterName,
                  'stash' => $stash->stash,
                  'poeStashId' => $stash->id,
                  'current_items' => json_encode($curentStashItems),
                  'league' => $stashLeague,
                  'created_at' => date("Y-m-d H:i:s"),
                  'updated_at' => date("Y-m-d H:i:s")
              ];
            }
        }
        $this->logTime('finish procesing items');

        // var_dump($itemIdsToDel);
        \DB::table('items')->whereIn('itemId', $itemIdsToDel)
            ->update(['deleted_at'=>date("Y-m-d H:i:s")]);
        $this->logTime('finish deleting items:'.count($itemIdsToDel));

        try {
          Stash::insert($newStashes);
        }catch (\Illuminate\Database\QueryException $e) {
          $this->comment("SQL Error: " . $e->getMessage() . "\n");
          $page->processed=2;
          $page->save();
          die;
        }
        $this->logTime('finish Stash::insert '.count($newStashes));
        unset($newStashes);

        // Bulk items to DB

        //dump($this->insertItems);
        $itemChunks = array_chunk($this->insertItems, 10);
        foreach($itemChunks as $chunk) {
            try {
              DB::table('items')->insert($chunk);
            }catch (\Illuminate\Database\QueryException $e) {
              $this->comment("SQL Error: " . $e->getMessage() . "\n");
              $page->processed=2;
              $page->save();
              die;
            }
        }
        $this->logTime('finish Item::insert '.count($this->insertItems));
        unset($this->insertItems);

        //set page as processed remove file
        $page->processed=true;
        $page->save();
        Storage::disk('local')->delete($page->pageId . '.txt');
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
        Log::info($message);

        //$this->comment('Time '.$execution_time);
        if($reset)
        $this->time_start = microtime(true);
    }

    private function getItemHash($item){
        $eMods = $iMods = $cMods = $note= '';
        if (array_key_exists("implicitMods", $item)) {
            $iMods = json_encode($item->implicitMods);
        }
        if (array_key_exists("explicitMods", $item)) {
            $eMods = json_encode($item->explicitMods);
        }
        if (array_key_exists("craftedMods", $item)) {
            $cMods = json_encode($item->craftedMods);
        }
        if (array_key_exists("note", $item)) {
            $note = $item->note;
        }

        $modsStr=$eMods.'/'.$iMods.'/'.$cMods;
        $sockets="";
        if (array_key_exists("sockets", $item)) {
          $sockets=json_encode($item->sockets);
        }
        
        $str=$note.'/'.$modsStr.'/'.$sockets;
        return md5($str);
    }
    private function sockColors($arr)
    {
        $colors = [];

        foreach ($arr as $color) {
        $colors[] = $color->attr;
        }

        $colors = implode($colors);
        $str = $dex = $int = $vaal = '';

        if (substr_count($colors, 'S') > 0) {
        $str = substr_count($colors, 'S') . 'R';
        }
        if (substr_count($colors, 'D') > 0) {
        $dex = substr_count($colors, 'D') . 'G';
        }
        if (substr_count($colors, 'I') > 0) {
        $int = substr_count($colors, 'I') . 'B';
        }
        if (substr_count($colors, 'G') > 0) {
        $vaal = substr_count($colors, 'G') . 'W';
        }

        $colors = $str . $dex . $int . $vaal;

        return $colors;
    }

    private function validColors($arr)
    {
        $colors = [];

        foreach ($arr as $color) {
        $colors[] = $color->attr;
        }

        $colors = implode($colors);
        $str = $dex = $int = $vaal = '';

        if (substr_count($colors, 'S') > 0) {
        $str = str_repeat("R", substr_count($colors, 'S'));
        }
        if (substr_count($colors, 'D') > 0) {
        $dex = str_repeat("G", substr_count($colors, 'D'));
        }
        if (substr_count($colors, 'I') > 0) {
        $int = str_repeat("B", substr_count($colors, 'I'));
        }
        if (substr_count($colors, 'G') > 0) {
        $vaal = str_repeat("W", substr_count($colors, 'G'));
        }

        $colors = $str . $dex . $int . $vaal;

        return $colors;
    }

    private function maxLinks($arr)
    {
        $links = [];

        foreach ($arr as $color) {
          $links[] = $color->group;
        }

        $links = implode($links);
        $maxNum = 0;
        for ($i=0; $i <=6 ; $i++) {
          if (substr_count($links, (string)$i) > 1) {
            $check = substr_count($links, (string)$i);
            if ($maxNum < $check) {
              $maxNum = $check;
            }
          }
        }

        return $maxNum;
    }

    private function addToInsertItems($item,$stash_id,$dbStashNote)
    {
        $note = $impMod = $requirements = $explicitMods = $craftedMods = $enchantMods = $type = $sockets = $socketColor  = $validColor = $properties = '';
        $maxLinks = $socketNum = 0;
        $pieces = explode("/", $item->icon);
        $type="unknown";
        if(count($pieces)<=10){
            $type=$pieces[count($pieces)-2];
        }

        if (array_key_exists("note", $item)) {
            $note = $item->note !== '' ? $item->note : $dbStashNote;
        }

        if (array_key_exists("properties", $item)) {
            $properties = json_encode($item->properties);
        }

        if (array_key_exists("implicitMods", $item)) {
            $impMod = json_encode($item->implicitMods);
        }

        if (array_key_exists("explicitMods", $item)) {
            $explicitMods = json_encode($item->explicitMods);
        }

        if (array_key_exists("craftedMods", $item)) {
            $craftedMods = json_encode($item->craftedMods);
        }

        if (array_key_exists("enchantMods", $item)) {
            $enchantMods = json_encode($item->enchantMods);
        }

        if (array_key_exists("requirements", $item)) {
            $requirements = json_encode($item->requirements);
        }

        if (array_key_exists("sockets", $item)) {
            if (count($item->sockets) > 0) {
                $sockets = json_encode($item->sockets);
            }
        }

        if (array_key_exists("sockets", $item)) {
            if (count($item->sockets) > 0) {
              $socketNum = count($item->sockets);
            }
        }

        if (array_key_exists("sockets", $item)) {
            if (!empty($item->sockets)) {
              $socketColor = $this->sockColors($item->sockets);
            }
        }

        if (array_key_exists("sockets", $item)) {
            if (!empty($item->sockets)) {
              $validColor = $this->validColors($item->sockets);
            }
        }

        if (array_key_exists("sockets", $item)) {
            if (!empty($item->sockets)) {
              $maxLinks = $this->maxLinks($item->sockets);
            }
        }

        $corrupted=false;
        if (array_key_exists("corrupted", $item)) {
            $corrupted=$item->corrupted;
        }

        $name = str_replace("<<set:MS>><<set:M>><<set:S>>", "", $item->name);
        $typeLine = str_replace("<<set:MS>><<set:M>><<set:S>>", "", $item->typeLine);

        $this->insertItems[] = [
        'stash_id' => $stash_id,
        'note' => $note,
        'implicitMods' => $impMod,
        'explicitMods' => $explicitMods,
        'craftedMods' => $craftedMods,
        'enchantMods' => $enchantMods,
        'properties' => $properties,
        'type' => $type,
        'sockets' => $sockets,
        'name' => $name . ' '. $typeLine,
        'typeLine' => $typeLine,
        'league' => $item->league,
        'frameType' => $item->frameType,
        'identified' => $item->identified,
        'identified' => $item->identified,
        'corrupted' => $corrupted,
        'requirements' => $requirements,
        'icon' => $item->icon,
        // 'icon' => substr($item->icon, 0, strpos($item->icon, ".png")),
        'itemId' => $item->id,
        'inventoryId' => $item->inventoryId,
        'ilvl' => $item->ilvl,
        'socketNum' => $socketNum,
        'maxLinks' => $maxLinks,
        'socketColor' => $socketColor,
        'validColor' => $validColor,
        'w' => $item->w,
        'h' => $item->h,
        'x' => $item->x,
        'y' => $item->y,
        'created_at' => date("Y-m-d H:i:s"),
        'updated_at' => date("Y-m-d H:i:s")
        ];
    }

}
