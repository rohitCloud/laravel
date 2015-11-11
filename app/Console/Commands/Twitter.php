<?php

namespace App\Console\Commands;

use App\Commands\OpenVPN;
use GuzzleHttp\Client;
use GuzzleHttp\Cookie\CookieJar;
use Illuminate\Console\Command;
use Carbon\Carbon;

/**
 * @author  Rohit Arora
 *
 * Class Pinterest
 * @package App\Console\Commands
 */
class Twitter extends Command
{
    const TIMEOUT                  = 10;
    const CONNECT_TIMEOUT          = 10;
    const EMAIL                    = 'Email';
    const PASSWORD                 = 'Password';
    const DEFAULT_RANDOM_TAG_LIMIT = 2;
    const USER_LIST                = 'user_list';
    const TWEET_LIST               = 'tweet_list';
    const RANDOM_FAVOURITE_LIMIT   = 3;
    const RANDOM_RE_TWEET_LIMIT    = 1;
    const RANDOM_FOLLOW_LIMIT      = 1;
    const TRIPOTO                  = 'tripoto';
    const TRENDING_BASED_ON        = 'travel';
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'twitter';

    protected $userAgents = ['Mozilla/5.0 (Windows NT 6.3; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/43.0.2357.134 Safari/537.36',
                             'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/44.0.2217.88 Safari/537.36',
                             'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_10_4) AppleWebKit/600.7.12 (KHTML, like Gecko) Version/8.0.7 Safari/600.7.12',
                             'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/43.0.2203.89 Safari/537.36'];

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Twitter hack';

    protected $Client;
    protected $jar;
    protected $headers;
    protected $keyword;
    protected $boardID;
    protected $token;
    protected $blockedUserList = ['triptroopme'];
    protected $isViral         = false;
    protected $trip            = null;
    protected $tweet           = null;

    /**
     * @var OpenVPN
     */
    private $vpn;

    /**
     * @param OpenVPN $vpn
     */
    public function __construct(OpenVPN $vpn)
    {
        parent::__construct();
        $this->Client = new Client(['base_uri' => 'https://twitter.com/']);
        $this->vpn    = $vpn;
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
                $email    = $account[self::EMAIL];
                $password = $account[self::PASSWORD];
                if (!$account[self::EMAIL] || !$account[self::PASSWORD]) {
                    $this->info("Time -> " . Carbon::now()
                                                   ->toDateTimeString() . ' Account is not complete yet -> ' . $account[self::EMAIL]);

                    continue;
                }

                if ($this->vpn->reconnect($this)) {
                    try {
                        $this->login($email, $password)
                             ->getBody();

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

                        $this->randomPersonalTweets();
                        $this->randomHashTweets();
                    } catch
                    (\Exception $Exception) {
                        $this->info("Time -> " . Carbon::now()
                                                       ->toDateTimeString() . " Email {$account[self::EMAIL]} Error Message -> " . $Exception->getMessage());
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
                                               ->toDateTimeString() . ' making a trip viral');
                $this->tweet($this->trip);
            }
            if ($this->tweet) {
                $this->info("Time -> " . Carbon::now()
                                               ->toDateTimeString() . ' making a tweet viral');
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
        if ($tags == 'tags') {
            $trendingTags = $this->search(self::TRENDING_BASED_ON, 'trending');
            $hashTags     = array_merge($hashTags, $trendingTags);
        }
        shuffle($hashTags);

        return $hashTags;
    }

    /**
     * @author Rohit Arora
     *
     * @return array
     */
    private function randomHashTweets()
    {
        $hashTags = $this->getRandomTags();
        list($userList, $tweetList) = $this->getTweetsAndUsers($hashTags);

        $this->randomFavourite($tweetList, rand(3, 6));
        $this->randomReTweet($tweetList, rand(1, 2));
        if (!rand(0, 2)) {
            $this->randomFollow($userList, rand(0, 2));
        }

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
        if (!rand(0, 2)) {
            if (!rand(0, 1)) {
                $data = $this->getPersonalTweet();
                $this->tweet($data);
            } else {
                // Get Random tweets from personal tags
                $hashTags = $this->getRandomTags('personal');
                list($userList, $tweetList) = $this->getTweetsAndUsers($hashTags, 1);
                unset($userList);
                if (!$tweetList) {
                    return false;
                }

                $this->randomFavourite($tweetList, 1);
                $this->randomReTweet($tweetList, 1);
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
                $trip['category'] = self::TRENDING_BASED_ON;
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
     * @param     $tweets
     * @param int $count
     *
     * @throws \Exception
     */
    private function randomFavourite($tweets, $count = self::RANDOM_FAVOURITE_LIMIT)
    {
        if (!$tweets) {
            throw new \Exception('No tweets found');
        }
        shuffle($tweets);
        for ($index = 0; $index < $count; $index++) {
            $this->favorite($tweets[$index]);
        }
    }


    /**
     * @param     $tweets
     * @param int $count
     *
     * @throws \Exception
     */
    private function randomReTweet($tweets, $count = self::RANDOM_RE_TWEET_LIMIT)
    {
        if (!$tweets) {
            throw new \Exception('No tweets found');
        }

        shuffle($tweets);
        for ($index = 0; $index < $count; $index++) {
            $this->reTweet($tweets[$index]);
        }
    }


    /**
     * @param     $users
     * @param int $count
     *
     * @throws \Exception
     */
    private function randomFollow($users, $count = self::RANDOM_FOLLOW_LIMIT)
    {
        if (!$users) {
            throw new \Exception('No users found');
        }
        shuffle($users);
        for ($index = 0; $index < $count; $index++) {
            $this->follow($users[$index]);
        }
    }

    /**
     * @author Rohit Arora
     *
     * @param     $hashTags
     * @param int $count
     *
     * @return array
     */
    private function getTweetsAndUsers($hashTags, $count = self::DEFAULT_RANDOM_TAG_LIMIT)
    {
        $tweets[self::USER_LIST] = $tweets[self::TWEET_LIST] = [];
        for ($index = 0; $index < $count; $index++) {
            $this->info("Time -> " . Carbon::now()
                                           ->toDateTimeString() . ' hash tag searched :' . $hashTags[$index]);
            list($userList, $tweetList) = $this->search($hashTags[$index]);

            $tweets[self::USER_LIST]  = $tweets[self::USER_LIST] + $userList;
            $tweets[self::TWEET_LIST] = $tweets[self::TWEET_LIST] + $tweetList;
        }

        $userList = $this->cleanUsers($tweets[self::USER_LIST]);
        shuffle($userList);
        shuffle($tweets[self::TWEET_LIST]);

        return [$userList, $tweets[self::TWEET_LIST]];
    }

    /**
     * @author Rohit Arora
     *
     * @return \Psr\Http\Message\ResponseInterface
     */
    private function getAuthToken()
    {
        $homePage = $this->Client->get('/', ['cookies'         => $this->jar,
                                             'headers'         => $this->headers,
                                             'connect_timeout' => self::CONNECT_TIMEOUT,
                                             'timeout'         => self::TIMEOUT])
                                 ->getBody();
        preg_match('/(<input type="hidden" name="authenticity_token" value=")(.*)(">)/', $homePage, $result);

        if (!isset($result[2])) {
            preg_match('/(<input type="hidden" value=")(.*)(" name="authenticity_token")/', $homePage, $result);
        }

        return isset($result[2]) ? $result[2] : false;
    }

    /**
     * @param $email
     * @param $password
     *
     * @return bool|\Psr\Http\Message\ResponseInterface
     * @throws \Exception
     */
    private function login($email, $password)
    {
        if (!$email || !$password) {
            throw new \Exception('Error in inputs');
        }

        $this->jar = new CookieJar();

        $userAgent = $this->userAgents[array_rand($this->userAgents)];
        $host      = "twitter.com";
        $origin    = 'https://twitter.com';

        $this->headers = ["Host"                      => $host,
                          "Connection"                => "keep-alive",
                          "Accept"                    => "text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,*/*;q=0.8",
                          "User-Agent"                => $userAgent,
                          "Upgrade-Insecure-Requests" => "1",
                          "Referer"                   => $origin,
                          "Accept-Encoding"           => "gzip, deflate",
                          "Accept-Language"           => "en-US,en;q=0.8,hi;q=0.6"];


        $this->info("Time -> " . Carbon::now()
                                       ->toDateTimeString() . ' Trying to login with you email ' . $email);

        $this->token = $this->getAuthToken();

        $this->checkToken();

        $data = ['authenticity_token'         => $this->token,
                 'session[username_or_email]' => $email,
                 'session[password]'          => $password,
                 'remember_me'                => 1,
                 'return_to_ssl'              => 'true',
                 'scribe_log'                 => '',
                 'redirect_after_login'       => '/'];

        $this->headers['Content-Type'] = 'application/x-www-form-urlencoded';

        return $this->Client->post('/sessions',
            ['body'            => http_build_query($data),
             'headers'         => $this->headers,
             'cookies'         => $this->jar,
             'connect_timeout' => self::CONNECT_TIMEOUT,
             'timeout'         => self::TIMEOUT]);
    }

    /**
     * @param $userId
     *
     * @return bool|\Psr\Http\Message\ResponseInterface
     */
    private function follow($userId)
    {
        $this->checkToken();

        $data = ['authenticity_token' => $this->token,
                 'user_id'            => $userId,
                 'challenges_passed'  => 'false',
                 'inject_tweet'       => 'false',
                 'handles_challenges' => 1];

        $this->headers['Content-Type'] = 'application/x-www-form-urlencoded';

        $this->info("Time -> " . Carbon::now()
                                       ->toDateTimeString() . ' Followed :' . $userId);

        return $this->Client->post('/i/user/follow',
            ['body'            => http_build_query($data),
             'headers'         => $this->headers,
             'cookies'         => $this->jar,
             'connect_timeout' => self::CONNECT_TIMEOUT,
             'timeout'         => self::TIMEOUT]);
    }

    /**
     * @param $userId
     *
     * @return bool|\Psr\Http\Message\ResponseInterface
     */
    protected function unFollow($userId)
    {
        $this->checkToken();

        $data = ['authenticity_token' => $this->token,
                 'user_id'            => $userId,
                 'challenges_passed'  => 'false',
                 'inject_tweet'       => 'false',
                 'handles_challenges' => 1];

        $this->headers['Content-Type'] = 'application/x-www-form-urlencoded';

        return $this->Client->post('/i/user/unfollow',
            ['body'            => http_build_query($data),
             'headers'         => $this->headers,
             'cookies'         => $this->jar,
             'connect_timeout' => self::CONNECT_TIMEOUT,
             'timeout'         => self::TIMEOUT]);
    }

    /**
     * @param     $id
     * @param int $tweetStatCount
     *
     * @return bool|\Psr\Http\Message\ResponseInterface
     */
    private function reTweet($id, $tweetStatCount = 0)
    {
        $this->checkToken();

        $data = ['authenticity_token' => $this->token,
                 'id'                 => $id,
                 'tweet_stat_count'   => $tweetStatCount];

        $this->headers['Content-Type'] = 'application/x-www-form-urlencoded';

        $this->info("Time -> " . Carbon::now()
                                       ->toDateTimeString() . 'ReTweeted -> ' . $id);

        try {
            return $this->Client->post('/i/tweet/retweet',
                ['body'            => http_build_query($data),
                 'headers'         => $this->headers,
                 'cookies'         => $this->jar,
                 'connect_timeout' => self::CONNECT_TIMEOUT,
                 'timeout'         => self::TIMEOUT]);
        } catch (\Exception $Exception) {
            return false;
        }
    }

    /**
     * @param     $id
     * @param int $tweetStatCount
     *
     * @return bool|\Psr\Http\Message\ResponseInterface
     */
    private function favorite($id, $tweetStatCount = 0)
    {
        $this->checkToken();

        $data = ['authenticity_token' => $this->token,
                 'id'                 => $id,
                 'tweet_stat_count'   => $tweetStatCount];

        $headers = $this->headers;

        $headers['Content-Type'] = 'application/x-www-form-urlencoded';

        $this->info("Time -> " . Carbon::now()
                                       ->toDateTimeString() . 'Favourite -> ' . $id);

        try {
            return $this->Client->post('/i/tweet/favorite',
                ['body'            => http_build_query($data),
                 'headers'         => $headers,
                 'cookies'         => $this->jar,
                 'connect_timeout' => self::CONNECT_TIMEOUT,
                 'timeout'         => self::TIMEOUT]);
        } catch (\Exception $Exception) {
            return false;
        }
    }

    /**
     * @param        $keyword
     * @param string $type
     * @param int    $pages
     *
     * @return array
     */
    private function search($keyword, $type = '', $pages = 1)
    {
        $data = ['f'        => 'tweet',
                 'vertical' => 'default',
                 'q'        => $keyword,
                 'src'      => 'typd',
                 'lang'     => 'en'];

        $searchPage = $this->Client->get('/search?' . http_build_query($data),
            ['headers'         => $this->headers,
             'cookies'         => $this->jar,
             'connect_timeout' => self::CONNECT_TIMEOUT,
             'timeout'         => self::TIMEOUT])
                                   ->getBody();

        preg_match('/data-min-position="(.*)"/', $searchPage, $result);

        if ($type == 'trending') {
            return $this->getTrendingTags($keyword, $searchPage);
        }

        $minPosition = isset($result[1]) ? $result[1] : false;
        $maxPosition = false;
        $data += ['f'                          => 'tweets',
                  'include_available_features' => 1,
                  'include_entities'           => 1,
                  'include_new_items_bar'      => 'true'];
        for ($index = 0; $index < $pages; $index++) {

            if ($index === 0) {
                $newData = $data + [
                        'composed_count'        => 0,
                        'include_new_items_bar' => 'true',
                        'interval'              => 30000,
                        'min_position'          => $minPosition,
                        'latent_count'          => 0,
                        'last_note_ts'          => $index + 23];
            } else {
                $newData = $data + ['reset_error_state' => 'false',
                                    'max_position'      => $maxPosition,
                                    'last_note_ts'      => $index + 23];
            }


            $returnData = $this->timeLineSearch($newData);
            $searchPage .= isset($returnData['inner']['items_html']) ? $returnData['inner']['items_html'] : '';

            $maxPosition = isset($returnData['inner']['min_position']) ? $returnData['inner']['min_position'] :
                (isset($returnData['inner']['max_position']) ? $returnData['inner']['max_position'] : false);
            if (!$maxPosition) {
                break;
            }
        }

        preg_match_all('/data-tweet-id="(.*)"/', $searchPage, $tweetList);
        $tweetList = isset($tweetList[1]) ? array_unique($tweetList[1]) : [];

        preg_match_all('/data-user-id="(.*)"/', $searchPage, $userList);
        $userList = isset($userList[1]) ? $filtered = array_filter(array_unique($userList[1]), 'is_numeric') : [];

        array_shift($userList);
        return [$userList, array_values($tweetList)];
    }

    /**
     * @param $data
     *
     * @return mixed
     */
    private function timeLineSearch($data)
    {
        $headers = $this->headers;

        $headers['Accept']           = 'application/json, text/javascript, */*; q=0.01';
        $headers['X-Requested-With'] = 'XMLHttpRequest';

        $searchTimeLine = $this->Client->get('/i/search/timeline?' . http_build_query($data),
            ['headers'         => $headers,
             'cookies'         => $this->jar,
             'connect_timeout' => self::CONNECT_TIMEOUT,
             'timeout'         => self::TIMEOUT]);

        $json = $searchTimeLine->getBody();

        return json_decode($json, true);
    }

    /**
     * @param $tweet
     *
     * @return bool|\Psr\Http\Message\ResponseInterface
     */
    private function tweet($tweet)
    {
        $this->checkToken();

        $status = $tweet['status'];

        $data = ['authenticity_token' => $this->token,
                 'is_permalink_page'  => 'false',
                 'place_id'           => '',
                 'status'             => $status,
                 'tagged_users'       => ''];

        $headers = $this->headers;

        $headers['Content-Type'] = 'application/x-www-form-urlencoded';

        $this->info("Time -> " . Carbon::now()
                                       ->toDateTimeString() . 'Tweeted -> ' . $status);

        return $this->Client->post('/i/tweet/create',
            ['body'            => http_build_query($data),
             'headers'         => $headers,
             'cookies'         => $this->jar,
             'connect_timeout' => self::CONNECT_TIMEOUT,
             'timeout'         => self::TIMEOUT]);
    }

    /**
     * @param $keyword
     * @param $searchPage
     *
     * @return array
     */
    private function getTrendingTags($keyword, $searchPage)
    {
        $trendJson = [];
        preg_match('/<input type="hidden" id="init-data" class="json-data" value="(.*)">/', $searchPage, $trendJson);
        $trending = isset($trendJson[1]) ? json_decode(htmlspecialchars_decode($trendJson[1]), true) : false;

        if (!isset($trending['trendsCacheKey'])) {
            return false;
        }

        $this->info("Time -> " . Carbon::now()
                                       ->toDateTimeString() . ' Searching trending tags');

        $data = ['k'            => $trending['trendsCacheKey'],
                 'lang'         => 'en',
                 'pc'           => 'true',
                 'query'        => $keyword,
                 'show_context' => 'true',
                 'src'          => 'module'];

        $trendingPage = $this->Client->get('/i/trends?' . http_build_query($data),
            ['headers'         => $this->headers,
             'cookies'         => $this->jar,
             'connect_timeout' => self::CONNECT_TIMEOUT,
             'timeout'         => self::TIMEOUT])
                                     ->getBody();
        $trendingList = json_decode($trendingPage, true);

        preg_match_all('/data-trend-name="(.*)" >/', $trendingList['module_html'], $trendJson);

        return isset($trendJson[1]) ? $trendJson[1] : [];
    }

    private function checkToken()
    {
        if (!$this->token) {
            throw new \Exception('Sorry token not available');
        }
    }

    /**
     * @param array $users
     *
     * @return array
     */
    private function cleanUsers($users)
    {
        $blockedUsers = json_decode(\File::get(storage_path('app') . DIRECTORY_SEPARATOR . 'blockedUsers.json'), true);
        $cleanedUser  = array_intersect($users, $blockedUsers);
        $result       = array_diff($users, $cleanedUser);

        $this->info("Time -> " . Carbon::now()
                                       ->toDateTimeString() . ' CleanUser -> ' . json_encode($cleanedUser));
        return $result;
    }
}
