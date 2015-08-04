<?php

namespace App\Console\Commands;

use Abraham\TwitterOAuth\TwitterOAuth;
use Illuminate\Console\Command;
use App\Commands\OpenVPN;

/**
 * @author  Rohit Arora
 *
 * Class Twitter
 * @package App\Console\Commands
 */
class Twitter extends Command
{
    const TIMEOUT                  = 30;
    const CONNECT_TIMEOUT          = 20;
    const CONSUMER_KEY             = 'API Key';
    const CONSUMER_SECRET          = 'API Secret';
    const ACCESS_TOKEN             = 'Access Token';
    const ACCESS_TOKEN_SECRET      = 'Access Token Secret';
    const DEFAULT_RANDOM_TAG_LIMIT = 3;
    const SEARCH_TWEET_COUNT       = 30;
    const RANDOM_FAVOURITE_LIMIT   = 10;
    const RANDOM_RE_TWEET_LIMIT    = 1;
    const ID                       = 'id';
    const NAME                     = 'name';
    const FAVOURITE                = 'favourite';
    const RE_TWEETED               = 're_tweeted';
    const USER_ID                  = 'user_id';
    const USER_NAME                = 'user_name';
    const FOLLOWING                = 'following';
    const RANDOM_FOLLOW_LIMIT      = 1;
    const ACCOUNT_NAME             = 'Name';
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

    /** @var  TwitterOAuth */
    protected $connection;
    protected $vpn;

    /**
     * @param OpenVPN $vpn
     */
    public function __construct(OpenVPN $vpn)
    {
        parent::__construct();
        $this->vpn = $vpn;
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $accounts = json_decode(\File::get(storage_path('app') . DIRECTORY_SEPARATOR . 'accounts.json'), true);
        shuffle($accounts);
        foreach ($accounts as $account) {
            if ($this->vpn->reconnect($this)) {
                try {
                    if (!$account[self::CONSUMER_KEY] || !$account[self::CONSUMER_SECRET] || !$account[self::ACCESS_TOKEN] || !$account[self::ACCESS_TOKEN_SECRET]) {
                        $this->info('Account is not complete yet -> ' . $account[self::ACCOUNT_NAME]);
                        continue;
                    }

                    $this->connection = new TwitterOAuth($account[self::CONSUMER_KEY], $account[self::CONSUMER_SECRET],
                        $account[self::ACCESS_TOKEN], $account[self::ACCESS_TOKEN_SECRET]);
                    $this->connection->setTimeouts(self::CONNECT_TIMEOUT, self::TIMEOUT);

                    /**
                     * Check status
                     * $this->connection->get('application/rate_limit_status');
                     */

                    $hashTags = $this->getRandomTags();
                    $tweets   = $this->randomTweets($hashTags);

                    $this->randomFavourite($tweets);
                    $this->randomReTweet($tweets);
                    $this->randomFollow($tweets);
                } catch
                (\Exception $Exception) {
                    $this->info("Email {$account[self::ACCOUNT_NAME]} Error Message -> " . $Exception->getMessage());
                }
            }
        }
    }

    /**
     * @author Rohit Arora
     *
     * @return mixed
     */
    private function getRandomTags()
    {
        $hashTags = json_decode(\File::get(storage_path('app') . DIRECTORY_SEPARATOR . 'hashTags.json'), true)['tags'];
        shuffle($hashTags);

        return $hashTags;
    }

    /**
     * @author Rohit Arora
     *
     * @param $hashTags
     *
     * @return array
     */
    private function randomTweets($hashTags)
    {
        $tweets = [];
        for ($index = 0; $index < self::DEFAULT_RANDOM_TAG_LIMIT; $index++) {
            $this->info('hash tag searched :' . $hashTags[$index]);
            $tweetStatues = $this->connection->get("search/tweets", ['q' => $hashTags[$index], 'result_type' => 'recent', 'count' => self::SEARCH_TWEET_COUNT])->statuses;
            foreach ($tweetStatues as $tweet) {
                $tweets[] = [self::ID         => $tweet->id,
                             self::NAME       => $tweet->text,
                             self::FAVOURITE  => $tweet->favorited,
                             self::RE_TWEETED => $tweet->retweeted,
                             self::USER_ID    => $tweet->user->id,
                             self::USER_NAME  => $tweet->user->name,
                             self::FOLLOWING  => $tweet->user->following];
            }
        }

        return $tweets;
    }

    /**
     * @author Rohit Arora
     *
     * @param $tweets
     */
    private function randomFavourite($tweets)
    {
        shuffle($tweets);
        for ($index = 0; $index < self::RANDOM_FAVOURITE_LIMIT; $index++) {
            if (!$tweets[$index][self::FAVOURITE]) {
                $this->info('Favourite -> ' . $tweets[$index][self::NAME]);
                $this->connection->post('favorites/create', [self::ID => $tweets[$index][self::ID]]);
            }
        }
    }

    /**
     * @author Rohit Arora
     *
     * @param $tweets
     */
    private function randomReTweet($tweets)
    {
        shuffle($tweets);
        for ($index = 0; $index < self::RANDOM_RE_TWEET_LIMIT; $index++) {
            if (!$tweets[$index][self::RE_TWEETED]) {
                $this->info('ReTweeted -> ' . $tweets[$index][self::NAME]);
                $this->connection->post('statuses/retweet/' . $tweets[$index][self::ID]);
            }
        }
    }

    /**
     * @author Rohit Arora
     *
     * @param $tweets
     */
    private function randomFollow($tweets)
    {
        if (!rand(0, 5)) {
            shuffle($tweets);
            for ($index = 0; $index < self::RANDOM_FOLLOW_LIMIT; $index++) {
                if (!$tweets[$index][self::FOLLOWING]) {
                    $this->info('Followed -> ' . $tweets[$index][self::USER_NAME]);
                    $this->connection->post('friendships/create', ['user_id' => $tweets[$index][self::USER_ID]]);
                }
            }
        }
    }
}
