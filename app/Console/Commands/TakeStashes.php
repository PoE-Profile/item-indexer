<?php

namespace App\Console\Commands;

use DB;
use Storage;
use App\Stash;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use App\ApiPages;
use App\Jobs\DbJob;
use App\Jobs\ModsAdd;
use Illuminate\Foundation\Bus\DispatchesJobs;

class TakeStashes extends Command
{

    use DispatchesJobs;
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'poe:addStashes {--continue} {--index}';
    // private $ids=[];
    // private $json;
    // private $api;
    // private $lastStashId = 0;
    // private $countChanges = 0;
    // private $players = [
    //   "Kas7erPoE", "ivanD87", "ponkata", "pinytenis", "nugiyen", "Etup", "xDANIHAKERAx", "WizzardBlizzard", "BarovecaBATE",
    //   "Darkee", "Morsexier"
    // ];
    private $lastPage;
    private $time_start;
    private $debug=true;
    private $continueIndex=true;
    private $next_change_id;
    private $lastPageStats;
    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '{--continue} {--etools=} poetools // Taking all Api data for stashes';

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
        $lastPageId="";
        //if only index option start geting only ids
        if($this->option('index')){
            $this->comment("start checking for new ids:");
            do {
                $next_page = ApiPages::orderBy('id', 'desc')->first();
                //$this->index($next_page->pageId);

                //if page ids difrent start new job

                if($next_page->pageId!=$lastPageId){
                  $this->logTime("start new DbJob:".$next_page);
                  //$this->comment("start new DbJob:".$next_page);
                  $lastPageId=$next_page->pageId;
                  $this->dispatch(new DbJob($next_page->pageId));
                }
                //$this->comment("no new ids..");
                sleep(1);
            } while (true);
         }

        $this->comment("get lastPage");
        //get lastPage id from DB
        $this->lastPage = ApiPages::orderBy('id', 'desc')->first();

        if(!$this->lastPage){
          //if no last page get next_change_id from begining of poe api
          $poeJson = file_get_contents('http://www.pathofexile.com/api/public-stash-tabs');
          $this->savePage("0-0-0-0-0",$poeJson);
        }

        if($this->option('continue')){
          $json = file_get_contents("http://107.191.126.15/poe.json");
          $temp_id=json_decode($json)->last_id;
          $this->comment("continue from server demon id:".$temp_id);
          $poeJson=$this->getPage($temp_id);
          $this->savePage($temp_id,$poeJson);
        }

        //get next id and redirect to slef
        do {
          $next_id=$this->lastPage->next_change_id;
          $this->comment("\nstart getPage(".$next_id.")");
          $poeJson=$this->getPage($next_id);
          if($this->debug){
            echo "finish api request:".$next_id;
            print_r($this->lastPageStats);
          }
          $this->logTime(" Time:",false);
          $this->savePage($next_id,$poeJson);
          $this->logTime("Time:");
          // sleep(1);
          usleep(1900000);
        } while (true);

    }

    private function getPage($id){
      $ch = curl_init();
      curl_setopt($ch, CURLOPT_URL, 'http://www.pathofexile.com/api/public-stash-tabs?id='.$id);
      curl_setopt($ch, CURLOPT_ENCODING, 'gzip');
      curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
      $output = curl_exec($ch);
      //$this->logTime("calc stats:",false);

      $tempArray = curl_getinfo($ch);
      $speed=$tempArray['speed_download'] * 8 / 1024 / 1024;
      $size=$tempArray['size_download']*0.000001;
      $this->lastPageStats= array(
        'curl_size' => $size.' mb',
        'curl_speed' => $speed.' mbps',
        'curl_time' => $tempArray['total_time'].'sec',
      );

      curl_close($ch);
      return $output;
    }

    private function savePage($id,$poeJson){
      $poeData = json_decode($poeJson);
      //if $poeData null stop to try request again
      if($poeData==null){
          echo "\njson_decode returns null";
          echo "\ntext from request:".$poeJson;
          return;
      }
      // save $this->json to a file in storage dir(name of the file next_change_id)
      Storage::disk('local')->put($id . '.txt', $poeJson);
      $this->logTime("savePage():",false);
      $newPage = new ApiPages;
      $newPage->pageId = $id;
      $newPage->next_change_id = $poeData->next_change_id;
      $newPage->stashes=count($poeData->stashes);
      $numItems=0;
      foreach ($poeData->stashes as $stash) {
        $numItems+=count($stash->items);
      }
      $newPage->items=$numItems;
      $newPage->stats=json_encode($this->lastPageStats);
      $newPage->save();

      $this->lastPage=$newPage;
    }

    private function logTime($msg,$reset=true)
    {
      if(!$this->debug){
        return;
      }
      $time_end = microtime(true);
      //dividing with 60 will give the execution time in minutes other wise seconds
      $execution_time = ($time_end - $this->time_start);///60;

      //execution time of the script
      $message = $msg.$execution_time;
      echo $message."\n";
      Log::info($message);

      //$this->comment('Time '.$execution_time);
      if($reset)
        $this->time_start = microtime(true);
    }

    private function index($id){
      $this->comment("start index ".$id." :".date("h:i:s"));
      //1892-3538-2234-3377-1013
      //15719964-16948011-15730519-18411668-17187790
       $this->result=file_get_contents ( 'http://www.pathofexile.com/api/public-stash-tabs?id='.$id, false, NULL, -1, 80);
      //$test=file_get_contents ( 'http://www.pathofexile.com/api/public-stash-tabs');

      $this->comment("finish geting string Time:".date("h:i:s"));
      //$temp = json_decode($this->result);
      // Storage::disk('local')->put( '1892-3642-2234-3385-1013.txt', $this->result);
      $pattern = '/"(.[0-9]*?)-(.*?)-(.*?)-(.*?)-(.*?)"/';
      preg_match ($pattern, $this->result, $matches);
      $next_id=str_replace("\"","",$matches[0]);

      $this->comment('next_id>'.$next_id);
      $this->comment("page_id>".$id);
      //if page id difrent set continueIndex true
      $this->continueIndex=($next_id!=$id);

      //if continueIndex true $next_id is new add to db
      if($this->continueIndex){
        $nextPage = new ApiPages;
        $nextPage->pageId = $next_id;
        $nextPage->save();
      }
      $this->comment($this->continueIndex." end Time:".date("h:i:s"));
    }



}
