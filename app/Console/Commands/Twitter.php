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
        $alreadyDone = false;
        while (true) {
            $accounts = json_decode(\File::get(storage_path('app') . DIRECTORY_SEPARATOR . 'accounts.json'), true);
            shuffle($accounts);
            if ($this->isViral && $alreadyDone) {
                $this->isViral = false;
                $this->trip    = null;
                $this->tweet   = null;
            }
            foreach ($accounts as $account) {
                if ($this->vpn->reconnect($this)) {
                    try {
                        if (!$account[self::CONSUMER_KEY] || !$account[self::CONSUMER_SECRET] || !$account[self::ACCESS_TOKEN] || !$account[self::ACCESS_TOKEN_SECRET]) {
                            $this->info('Account is not complete yet -> ' . $account[self::ACCOUNT_NAME]);
                            continue;
                        }

                        $this->info("logging in {$account[self::ACCOUNT_NAME]}");
                        $this->connection = new TwitterOAuth($account[self::CONSUMER_KEY], $account[self::CONSUMER_SECRET],
                            $account[self::ACCESS_TOKEN], $account[self::ACCESS_TOKEN_SECRET]);
                        $this->connection->setTimeouts(self::CONNECT_TIMEOUT, self::TIMEOUT);

                        if ($this->isViral) {
                            $this->makeViral();
                            $alreadyDone = true;
                            continue;
                        }

                        $this->getPersonalTweet();

                        if ($this->isViral) {
                            $this->info('Time is to make a trip or tweet viral Woohoo!');
                            break;
                        }

                        $this->randomHashTweets();
                        $this->randomPersonalTweets();
                    } catch
                    (\Exception $Exception) {
                        $this->info("Email {$account[self::ACCOUNT_NAME]} Error Message -> " . $Exception->getMessage());
                    }
                }
            }
        }
    }

    public function makeViral()
    {
        if ($this->isViral) {
            if ($this->trip) {
                $this->info('making a trip viral');
                $media = $this->getMedia($this->trip);
                $this->tweet($this->trip['status'], $media);
            }
            if ($this->tweet) {
                $this->info('making a tweet viral');
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
            $this->info('hash tag searched :' . $hashTags[$index]);
            $tweetStatues = $this->connection->get("search/tweets", ['q' => $hashTags[$index], 'result_type' => 'recent', 'count' => self::SEARCH_TWEET_COUNT])->statuses;
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
                $this->info('Favourite -> ' . $tweets[$index][self::NAME]);
                $this->connection->post('favorites/create', [self::ID => $tweets[$index][self::ID]]);
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
                if (!rand(0, 1)) {
                    $this->info('ReTweeted -> ' . $tweets[$index][self::NAME]);
                    $this->reTweet($tweets[$index][self::ID]);
                } else {
                    $this->tweet($tweets[$index][self::NAME]);
                }
            }
        }
    }

    /**
     * @author Rohit Arora
     *
     * @param       $status
     * @param array $media
     */
    private function tweet($status, $media = [])
    {
        $this->info('Tweeted -> ' . $status);
        $data = ['status' => $status];

        if ($media) {
            $data = $data + ['media_ids' => implode(',', $media)];
        }

        $this->connection->post('statuses/update', $data);
    }

    /**
     * @author Rohit Arora
     *
     * @param $tweets
     */
    private function randomFollow($tweets)
    {
        if (!rand(0, 10)) {
            shuffle($tweets);
            for ($index = 0; $index < self::RANDOM_FOLLOW_LIMIT; $index++) {
                if (!$tweets[$index][self::FOLLOWING]) {
                    $this->info('Followed -> ' . $tweets[$index][self::USER_NAME]);
                    $this->connection->post('friendships/create', ['user_id' => $tweets[$index][self::USER_ID]]);
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
        // Get random tweets from has tags

        $hashTags = $this->getRandomTags();
        $tweets   = $this->randomTweets($hashTags);
        $this->randomFavourite($tweets, rand(6, 12));
        if (!rand(0, 2)) {
            $this->randomReTweet($tweets);
        }
        $this->randomFollow($tweets);
    }

    private function randomPersonalTweets()
    {
        if (!rand(0, 2)) {
            if (!rand(0, 1)) {
                $data  = $this->getPersonalTweet();
                $media = $this->getMedia($data);
                $this->tweet($data['status'], $media);
            } else {
                // Get Random tweets from personal tags
                $hashTags = $this->getRandomTags('personal');
                $tweets   = $this->randomTweets($hashTags, 1);
                $this->randomFavourite($tweets, 1);
                $this->randomReTweet($tweets, 1);
            }
        }
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
                $trip['category'] = '';
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
        $this->connection->post('statuses/retweet/' . $id);
    }

    /**
     * @author Rohit Arora
     *
     * @param $data
     *
     * @return array
     */
    private function getMedia($data)
    {
        $media = [];
        if (isset($data['image_url'])) {
            $this->info('downloading ' . $data['image_url']);
            $fileMeta = explode('/', $data['image_url']);
            $fileName = '/tmp/' . end($fileMeta);
            $this->info('Filename ' . $fileName);
            file_put_contents($fileName, file_get_contents($data['image_url']));
            $this->info('downloaded to ' . $fileName);
            $this->info('trying to upload media ' . $fileName);
            $media[] = $this->connection->upload('media/upload', ['media' => $fileName])->media_id_string;
            unlink($fileName);
        }
        return $media;
    }
}
