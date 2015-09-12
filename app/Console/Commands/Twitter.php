<?php

namespace App\Console\Commands;

use Abraham\TwitterOAuth\TwitterOAuth;
use Carbon\Carbon;
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
    const DEFAULT_RANDOM_TAG_LIMIT = 4;
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
    const RANDOM_FOLLOW_LIMIT      = 2;
    const ACCOUNT_NAME             = 'TwitterHandle';
    const TRIPOTO                  = 'tripoto';
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
    protected $blockedUserList = ['triptroopme'];
    protected $isViral         = false;
    protected $trip            = null;
    protected $tweet           = null;
    protected $handleName;

    /**
     * @param OpenVPN $vpn
     */
    public function __construct(OpenVPN $vpn)
    {
        parent::__construct();
        $this->vpn = $vpn;
    }

    /**
     * @author Rohit Arora
     *
     * @param     $statusKey
     * @param     $limit
     * @param int $minutes
     */
    private static function updateStatuses($statusKey, $limit, $minutes = 15)
    {
        \Cache::put($statusKey, $limit, $minutes);
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $alreadyDone = false;
        while (true) {
            $accounts = json_decode(\File::get(storage_path('app') . DIRECTORY_SEPARATOR . 'accounts.json'), true);
            shuffle($accounts);
            if ($this->isViral && $alreadyDone) {
                $this->isViral = false;
                $this->trip    = null;
                $this->tweet   = null;
                $alreadyDone   = false;
            }
            foreach ($accounts as $account) {
                if ($this->vpn->reconnect($this)) {
                    try {
                        if (!$account[self::CONSUMER_KEY] || !$account[self::CONSUMER_SECRET] || !$account[self::ACCESS_TOKEN] || !$account[self::ACCESS_TOKEN_SECRET]) {
                            $this->info("Time -> " . Carbon::now()
                                                           ->toDateTimeString() . 'Account is not complete yet -> ' . $account[self::ACCOUNT_NAME]);
                            continue;
                        }

                        $this->info("Time -> " . Carbon::now()
                                                       ->toDateTimeString() . "logging in {$account[self::ACCOUNT_NAME]}");
                        $this->connection = new TwitterOAuth($account[self::CONSUMER_KEY], $account[self::CONSUMER_SECRET],
                            $account[self::ACCESS_TOKEN], $account[self::ACCESS_TOKEN_SECRET]);
                        $this->connection->setTimeouts(self::CONNECT_TIMEOUT, self::TIMEOUT);
                        $this->handleName = $account[self::ACCOUNT_NAME];

                        if (!$this->getLimit()) {
                            continue;
                        }

                        if ($this->isViral) {
                            $this->makeViral();
                            $alreadyDone = true;
                            continue;
                        }

                        $this->getPersonalTweet();

                        if ($this->isViral) {
                            $this->info("Time -> " . Carbon::now()
                                                           ->toDateTimeString() . 'Time is to make a trip or tweet viral Woohoo!');
                            break;
                        }

                        $this->randomHashTweets();
                        $this->randomPersonalTweets();
                    } catch
                    (\Exception $Exception) {
                        $this->info("Time -> " . Carbon::now()
                                                       ->toDateTimeString() . " Email {$account[self::ACCOUNT_NAME]} Error Message -> " . $Exception->getMessage());
                    }
                }
            }
        }
    }

    /**
     * @author Rohit Arora
     *
     */
    public function makeViral()
    {
        if ($this->isViral) {
            if ($this->trip) {
                $this->info("Time -> " . Carbon::now()
                                               ->toDateTimeString() . 'making a trip viral');
                $this->tweet($this->trip);
            }
            if ($this->tweet) {
                $this->info("Time -> " . Carbon::now()
                                               ->toDateTimeString() . 'making a tweet viral');
                $this->reTweet($this->tweet);
            }
        }
    }

    /**
     * @author Rohit Arora
     *
     * @param string $tags
     *
     * @return mixed
     */
    private function getRandomTags($tags = 'tags')
    {
        $hashTags = json_decode(\File::get(storage_path('app') . DIRECTORY_SEPARATOR . 'hashTags.json'), true)[$tags];
        shuffle($hashTags);

        return $hashTags;
    }

    /**
     * @author Rohit Arora
     *
     * @param     $hashTags
     * @param int $count
     *
     * @return array
     */
    private function randomTweets($hashTags, $count = self::DEFAULT_RANDOM_TAG_LIMIT)
    {
        $tweets = [];
        for ($index = 0; $index < $count; $index++) {
            $statuses = $this->getLimit();
            if ($statuses && $statuses->search > 0) {
                $this->info("Time -> " . Carbon::now()
                                               ->toDateTimeString() . ' hash tag searched :' . $hashTags[$index]);
                $tweetStatues = $this->connection->get("search/tweets", ['q' => $hashTags[$index], 'result_type' => 'recent', 'count' => self::SEARCH_TWEET_COUNT])->statuses;
                $remaining    = $statuses->search - 1;
                $this->info("Time -> " . Carbon::now()
                                               ->toDateTimeString() .
                    ' and limit remaining for search -> ' . $remaining);
                $statuses->search = $remaining;
                self::updateStatuses($this->getStatusKey(), $statuses);
                foreach ($tweetStatues as $tweet) {
                    if (!in_array($tweet->user->screen_name, $this->blockedUserList)) {
                        $tweets[] = [self::ID         => $tweet->id,
                                     self::NAME       => $tweet->text,
                                     self::FAVOURITE  => $tweet->favorited,
                                     self::RE_TWEETED => $tweet->retweeted,
                                     self::USER_ID    => $tweet->user->id,
                                     self::USER_NAME  => $tweet->user->name,
                                     self::FOLLOWING  => $tweet->user->following];
                    }
                }
            }
        }

        return $tweets;
    }

    /**
     * @author Rohit Arora
     *
     * @param     $tweets
     * @param int $count
     */
    private function randomFavourite($tweets, $count = self::RANDOM_FAVOURITE_LIMIT)
    {
        shuffle($tweets);
        for ($index = 0; $index < $count; $index++) {
            if (!$tweets[$index][self::FAVOURITE]) {
                $statuses = $this->getLimit();
                if ($statuses && $statuses->favourites > 0) {
                    $this->info("Time -> " . Carbon::now()
                                                   ->toDateTimeString() . 'Favourite -> ' . $tweets[$index][self::NAME]);
                    $this->connection->post('favorites/create', [self::ID => $tweets[$index][self::ID]]);
                    $remaining = $statuses->favourites - 1;
                    $this->info("Time -> " . Carbon::now()
                                                   ->toDateTimeString() .
                        'limit remaining for Favourite -> ' . $remaining);
                    $statuses->favourites = $remaining;
                    self::updateStatuses($this->getStatusKey(), $statuses);
                }
            }
        }
    }

    /**
     * @author Rohit Arora
     *
     * @param     $tweets
     * @param int $count
     */
    private function randomReTweet($tweets, $count = self::RANDOM_RE_TWEET_LIMIT)
    {
        shuffle($tweets);
        for ($index = 0; $index < $count; $index++) {
            if (!$tweets[$index][self::RE_TWEETED]) {
                $this->info("Time -> " . Carbon::now()
                                               ->toDateTimeString() . 'ReTweeted -> ' . $tweets[$index][self::NAME]);
                $this->reTweet($tweets[$index][self::ID]);
            }
        }
    }

    /**
     * @author Rohit Arora
     *
     * @param       $tweet
     */
    private function tweet($tweet)
    {
        $statuses = $this->getLimit();
        if ($statuses && $statuses->tweet > 0) {
            // $media  = isset($tweet['image_url']) ? $this->getMedia($tweet['image_url']) : [];
            $media  = [];
            $status = $tweet['status'];
            $this->info("Time -> " . Carbon::now()
                                           ->toDateTimeString() . 'Tweeted -> ' . $status);
            $data = ['status' => $status];

            if ($media) {
                $data = $data + ['media_ids' => implode(',', $media)];
            }

            $this->connection->post('statuses/update', $data);

            $remaining = $statuses->tweet - 1;
            $this->info("Time -> " . Carbon::now()
                                           ->toDateTimeString() .
                'limit remaining for tweet -> ' . $remaining);
            $statuses->tweet = $remaining;
            self::updateStatuses($this->getStatusKey(), $statuses);
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
                    $statuses = $this->getLimit();
                    if ($statuses && $statuses->follow > 0) {
                        $this->info("Time -> " . Carbon::now()
                                                       ->toDateTimeString() . 'Followed -> ' . $tweets[$index][self::USER_NAME]);
                        $this->connection->post('friendships/create', ['user_id' => $tweets[$index][self::USER_ID]]);
                        $remaining = $statuses->follow - 1;
                        $this->info("Time -> " . Carbon::now()
                                                       ->toDateTimeString() .
                            'limit remaining for follow -> ' . $remaining);
                        $statuses->follow = $remaining;
                        self::updateStatuses($this->getStatusKey(), $statuses);
                    }
                }
            }
        }
    }

    /**
     * @author Rohit Arora
     *
     * @return array
     */
    private function randomHashTweets()
    {
        $hashTags = $this->getRandomTags();
        $tweets   = $this->randomTweets($hashTags);
        if (!$tweets) {
            return false;
        }
        $this->randomFavourite($tweets, rand(3, 6));
        if (!rand(0, 1)) {
            $this->randomReTweet($tweets);
        }
        $this->randomFollow($tweets);

        return true;
    }

    /**
     * @author Rohit Arora
     *
     * @return bool
     * @throws \Exception
     */
    private function randomPersonalTweets()
    {
        if (!rand(0, 1)) {
            if (!rand(0, 1)) {
                $data = $this->getPersonalTweet();
                $this->tweet($data);
            } else {
                // Get Random tweets from personal tags
                $hashTags = $this->getRandomTags('personal');
                $tweets   = $this->randomTweets($hashTags, 1);
                if (!$tweets) {
                    return false;
                }
                $this->randomFavourite($tweets, 1);
                $this->randomReTweet($tweets, 1);
            }
        }

        return true;
    }

    /**
     * @author Rohit Arora
     *
     * @return string
     * @throws \Exception
     */
    private function getPersonalTweet()
    {
        $trip = json_decode(file_get_contents(env('TRIP_URL')), true);
        if ($trip) {
            if (!isset($trip['category'])) {
                $trip['category'] = 'travel';
            }
            if (isset($trip['isViral']) && $trip['isViral']) {
                $this->isViral = true;
            }
            if (isset($trip['tweet_id']) && $trip['tweet_id']) {
                $this->tweet   = $trip['tweet_id'];
                $this->isViral = true;
            }
            $link   = isset($trip['link']) && $trip['link'] ? $trip['link'] : '';
            $title  = isset($trip['title']) && $trip['title'] ? $trip['title'] : '';
            $hashes = [$trip['category'], self::TRIPOTO];
            shuffle($hashes);
            $data['status'] = $link . " \n" . $title . ' #' . implode(' #', $hashes);
            if (isset($trip['image_url'])) {
                $data['image_url'] = $trip['image_url'];
            }
            if ($link && $title && $this->isViral) {
                $this->trip = $data;
            }

            return $data;
        }

        throw new \Exception('Trip not found');
    }

    /**
     * @author Rohit Arora
     *
     * @param $id
     */
    private function reTweet($id)
    {
        $statuses = $this->getLimit();
        if ($statuses && $statuses->reTweet > 0) {
            $this->connection->post('statuses/retweet/' . $id);
            $remaining = $statuses->reTweet - 1;
            $this->info("Time -> " . Carbon::now()
                                           ->toDateTimeString() .
                'limit remaining for retweets -> ' . $remaining);
            $statuses->reTweet = $remaining;
            self::updateStatuses($this->getStatusKey(), $statuses);
        }
    }

    /**
     * @author Rohit Arora
     *
     * @return \stdClass
     */
    private function getLimit()
    {
        $statusKey = $this->getStatusKey();
        $statuses  = \Cache::get($statusKey);
        if ($statuses) {
            $diff = (new Carbon($statuses->time))->diff(Carbon::now());
            if ($diff->i > 0 && $diff->s > 0) {
                return $statuses;
            }
        }

        try {
            $statuses             = new \stdClass();
            $statuses->favourites = 12;
            $statuses->tweet      = 10;
            $statuses->follow     = 5;
            $statuses->reTweet    = 30;
            $statuses->search     = 150;
            $statuses->time       = Carbon::now()
                                          ->addMinute(15);
            self::updateStatuses($statusKey, $statuses, 15);

            return $statuses;
        } catch (\Exception $Exception) {
            $this->info("Time -> " . Carbon::now()
                                           ->toDateTimeString() . " Error -> " . $Exception->getMessage());
            return false;
        }
    }

    /**
     * @author Rohit Arora
     *
     * @param $imageURL
     *
     * @return array
     */
    private function getMedia($imageURL)
    {
        $media = [];
        if (isset($imageURL)) {
            $this->info("Time -> " . Carbon::now()
                                           ->toDateTimeString() . 'downloading ' . $imageURL);
            $fileMeta = explode('/', $imageURL);
            $fileName = '/tmp/' . end($fileMeta);
            $this->info("Time -> " . Carbon::now()
                                           ->toDateTimeString() . 'Filename ' . $fileName);
            file_put_contents($fileName, file_get_contents($imageURL));
            $this->info("Time -> " . Carbon::now()
                                           ->toDateTimeString() . 'downloaded to ' . $fileName);
            $this->info("Time -> " . Carbon::now()
                                           ->toDateTimeString() . 'trying to upload media ' . $fileName);
            $media[] = $this->connection->upload('media/upload', ['media' => $fileName])->media_id_string;
            unlink($fileName);
        }
        return $media;
    }

    /**
     * @author Rohit Arora
     *
     * @return string
     */
    private function getStatusKey()
    {
        return $this->handleName . '_statuses';
    }
}
