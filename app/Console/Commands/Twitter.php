<?php

namespace App\Console\Commands;

use App\Commands\OpenVPN;
use GuzzleHttp\Client;
use GuzzleHttp\Cookie\CookieJar;
use Illuminate\Console\Command;

/**
 * @author  Rohit Arora
 *
 * Class Pinterest
 * @package App\Console\Commands
 */
class Twitter extends Command
{
    const TIMEOUT         = 10;
    const CONNECT_TIMEOUT = 10;
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
        $accounts = json_decode(\File::get(storage_path('app') . DIRECTORY_SEPARATOR . 'accounts.json'), true);
        shuffle($accounts);
        foreach ($accounts as $account) {
            $email    = $account['Email'];
            $password = $account['Password'];
            if (true) {
                try {
                    $this->start($email, $password);
                } catch
                (\Exception $Exception) {
                }
            }
        }
    }

    /**
     * @param $email
     * @param $password
     *
     * @return \Psr\Http\Message\StreamInterface
     * @throws \Exception
     */
    private function start($email, $password)
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


        $this->info('Trying to login with you email ' . urldecode($email));
        $this->login($email, $password)
             ->getBody();

        list($userList, $tweetList) = $this->search('#travel', 1);

        $this->favorite($tweetList[0]);
        $this->reTweet($tweetList[0]);
        $this->follow($userList[0]);
        $this->tweet('testing #travel');
    }

    /**
     * @author Rohit Arora
     *
     * @return \Psr\Http\Message\ResponseInterface
     */
    private function getAuthToken()
    {
        $homePage = $this->Client->get('/', ['cookies' => $this->jar, 'headers' => $this->headers, 'connect_timeout' => self::CONNECT_TIMEOUT, 'timeout' => self::TIMEOUT])
                                 ->getBody();
        preg_match('/(<input type="hidden" name="authenticity_token" value=")(.*)(">)/', $homePage, $result);

        return isset($result[2]) ? $result[2] : false;
    }

    /**
     * @author Rohit Arora
     *
     * @param $email
     * @param $password
     *
     * @return \Psr\Http\Message\ResponseInterface
     */
    private function login($email, $password)
    {
        $this->token = $this->getAuthToken();

        if (!$this->token) {
            return false;
        }

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
        if (!$this->token) {
            return false;
        }

        $data = ['authenticity_token' => $this->token,
                 'user_id'            => $userId,
                 'challenges_passed'  => 'false',
                 'inject_tweet'       => 'false',
                 'handles_challenges' => 1];

        $this->headers['Content-Type'] = 'application/x-www-form-urlencoded';

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
        if (!$this->token) {
            return false;
        }

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
        if (!$this->token) {
            return false;
        }

        $data = ['authenticity_token' => $this->token,
                 'id'                 => $id,
                 'tweet_stat_count'   => $tweetStatCount];

        $this->headers['Content-Type'] = 'application/x-www-form-urlencoded';

        return $this->Client->post('/i/tweet/retweet',
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
    private function favorite($id, $tweetStatCount = 0)
    {
        if (!$this->token) {
            return false;
        }

        $data = ['authenticity_token' => $this->token,
                 'id'                 => $id,
                 'tweet_stat_count'   => $tweetStatCount];

        $headers = $this->headers;

        $headers['Content-Type'] = 'application/x-www-form-urlencoded';

        return $this->Client->post('/i/tweet/favorite',
            ['body'            => http_build_query($data),
             'headers'         => $headers,
             'cookies'         => $this->jar,
             'connect_timeout' => self::CONNECT_TIMEOUT,
             'timeout'         => self::TIMEOUT]);
    }

    /**
     * @param     $keyword
     * @param int $pages
     *
     * @return array
     */
    private function search($keyword, $pages = 10)
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
     * @param $status
     *
     * @return bool|\Psr\Http\Message\ResponseInterface
     */
    private function tweet($status)
    {
        if (!$this->token) {
            return false;
        }

        $data = ['authenticity_token' => $this->token,
                 'is_permalink_page'  => 'false',
                 'place_id'           => '',
                 'status'             => $status,
                 'tagged_users'       => ''];

        $headers = $this->headers;

        $headers['Content-Type'] = 'application/x-www-form-urlencoded';

        return $this->Client->post('/i/tweet/create',
            ['body'            => http_build_query($data),
             'headers'         => $headers,
             'cookies'         => $this->jar,
             'connect_timeout' => self::CONNECT_TIMEOUT,
             'timeout'         => self::TIMEOUT]);
    }
}
