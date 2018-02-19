<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

class CheckStatus extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'poe:check-status';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

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
        // Test database connection
        try {
            \DB::connection()->getPdo();
        } catch (\Exception $e) {
            $this->info("Could not connect to the database.  Please check your configuration.");
            $this->sendMail('problem connecting to DB.');
            die;
        }

        $now =  \Carbon\Carbon::now()->toDateTimeString();
        $late_pages=$now;
        $temp = \DB::table('api_pages')->where('processed', '=', 0)->get();
        if(count($temp)>50){
            //dd(count($temp));
            $this->sendMail('To many unprocesed api_pages check for problem.');
        }

    }


    private function sendMail($msg){
        if(strlen(config('app.notification_mail'))==0){
            $this->info('No mail Notification .No notification_mail in config');
            return;
        }
        $this->info('Send mail Notification.');
        $date = new \DateTime();
        $title = 'Problem with Poe-Profile.info'.$date->format('Y-m-d H:i:s');
        //$content = dump($this->mailMsgs);
        $strContent = $strErors = '';

        $strContent=$msg;
        //$strContent=$strContent.'<br><a href="'.route('verify_catalogs').'">verify_catalogs</a>';
        Mail::send('emails.artisan_notificaion',
        ['title' => $title, 'content' => $strContent, 'errors'=>$strErors], function ($message)
        {
            $mail = config('app.notification_mail');
            $message->from('support@poe-profile.info', 'PoeProfile.info Artisan');
            $message->to($mail);
            $message->subject('Problem with Poe-Profile.info');
        });
    }
}
