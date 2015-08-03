<?php

namespace App\Console\Commands;

use Abraham\TwitterOAuth\TwitterOAuth;
use Illuminate\Console\Command;

/**
 * @author  Rohit Arora
 *
 * Class Twitter
 * @package App\Console\Commands
 */
class Twitter extends Command
{
    const TIMEOUT             = 30;
    const CONNECT_TIMEOUT     = 20;
    const CONSUMER_KEY        = 'consumer_key';
    const CONSUMER_SECRET     = 'consumer_secret';
    const ACCESS_TOKEN        = 'access_token';
    const ACCESS_TOKEN_SECRET = 'access_token_secret';
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'twitter';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description.';

    /**
     *
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
        $accounts = json_decode(\File::get(storage_path('app') . DIRECTORY_SEPARATOR . 'accounts.json'), true)['accounts'];
        shuffle($accounts);
        foreach ($accounts as $account) {
            $connection = new TwitterOAuth($account[self::CONSUMER_KEY], $account[self::CONSUMER_SECRET],
                $account[self::ACCESS_TOKEN], $account[self::ACCESS_TOKEN_SECRET]);
            $connection->setTimeouts(self::CONNECT_TIMEOUT, self::TIMEOUT);
            $content = $connection->get("search/tweets", ['q' => '#travel']);
            dd($content);
        }
    }
}
