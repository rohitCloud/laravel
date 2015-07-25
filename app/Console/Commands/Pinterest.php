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
class Pinterest extends Command
{
    const PAGES                      = 20;
    const DEFAULT_PINS_LIMIT_PER_TAG = 30;
    const KEYWORD                    = 'travel';
    const TIMEOUT                    = 30;
    const CONNECT_TIMEOUT            = 10;
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'pinterest:like';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'A hack for like on pinterest with tags';

    protected $Client;
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
        $this->Client = new Client(['base_uri' => 'https://pinterest.com/']);
        $this->vpn    = $vpn;
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $tags = $this->getTags(self::KEYWORD);
        while ($tags) {
            $keyword  = $this->getRandomTag($tags);
            $accounts = \Config::get('pinterest.accounts');
            shuffle($accounts);
            foreach ($accounts as $account) {
                $email    = $account['email'];
                $password = $account['password'];
                if ($this->vpn->reconnect($this)) {
                    try {
                        $this->like($keyword, $email, $password, rand(5, self::PAGES));
                    } catch
                    (\Exception $Exception) {
                        $this->info("keyword -> $keyword email -> $email Error Message -> " . $Exception->getMessage());
                    }
                }
            }
        }
    }

    /**
     * @author Rohit Arora
     *
     * @param     $keyword
     * @param     $email
     * @param     $password
     * @param int $noOfPages
     *
     * @throws \Exception
     */
    private function like($keyword, $email, $password, $noOfPages = 1)
    {
        if (!$keyword || !$email || !$password) {
            throw new \Exception('Error in inputs');
        }

        $keyword  = urlencode($keyword);
        $email    = urlencode($email);
        $password = urlencode($password);

        $client = $this->Client;
        $jar    = new CookieJar();

        $this->info('Opening pinterest.com for csrf token and cookies');

        $headers = ["Host"            => "in.pinterest.com",
                    "Connection"      => "keep-alive",
                    "Accept"          => "text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,*/*;q=0.8",
                    "User-Agent"      => "Mozilla/5.0 (Windows NT 6.3; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/43.0.2357.134 Safari/537.36",
                    "Referer"         => "https://www.google.co.in/",
                    "Accept-Encoding" => "gzip, deflate",
                    "Accept-Language" => "en-US,en;q=0.8,hi;q=0.6"];

        $pinterestHomePage = $client->get('/', ['cookies' => $jar, 'headers' => $headers, 'connect_timeout' => self::CONNECT_TIMEOUT, 'timeout' => self::TIMEOUT]);

        $this->info('cookies done');

        $headers = ['Host'                 => 'in.pinterest.com',
                    "Connection"           => "keep-alive",
                    "CSP"                  => "active",
                    "Content-Type"         => "application/x-www-form-urlencoded; charset=UTF-8",
                    'Origin'               => 'https://in.pinterest.com',
                    'X-Pinterest-AppState' => 'active',
                    "Referer"              => "https://in.pinterest.com/",
                    'X-Requested-With'     => 'XMLHttpRequest',
                    'Accept'               => 'application/json, text/javascript, */*; q=0.01',
                    'Accept-Encoding'      => 'gzip, deflate',
                    'X-CSRFToken'          => $this->getCSRF($pinterestHomePage->getHeader('Set-Cookie')),
                    'Accept-Language'      => 'en-US,en;q=0.8,hi;q=0.6',
                    'User-Agent'           => 'Mozilla/5.0 (Windows NT 6.3; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/43.0.2357.134 Safari/537.36',
                    'X-APP-VERSION'        => $pinterestHomePage->getHeader('Pinterest-Version')[0],
        ];

        $this->info('Updating track actions pinterest.com/resource/UserRegisterTrackActionResource/update/');

        $client->post('/resource/UserRegisterTrackActionResource/update/',
            ['body'            => 'source_url=%2F&data=%7B%22options%22%3A%7B%22actions%22%3A%5B%22register_landing%22%2C%22register_multi_screen_username_pass_loaded%22%2C%22unauth_home%22%2C%22traffic.desktop.google.HomePage.unauth%22%5D%7D%2C%22context%22%3A%7B%7D%7D',
             'headers'         => $headers,
             'cookies'         => $jar,
             'connect_timeout' => self::CONNECT_TIMEOUT,
             'timeout'         => self::TIMEOUT]);

        $this->info('Checking if email already exists');

        $client->get('/resource/EmailExistsResource/get/?source_url=%2F&data=%7B%22options%22%3A%7B%22email%22%3A%22' . $email .
            '%22%7D%2C%22context%22%3A%7B%7D%7D&module_path=App%3EHomePage%3EUnauthHomePage%3ESignupForm%3EUserRegister(is_login_form%3Dnull%2C+wall_class%3DdarkWall%2C+container%3Dhome_page%2C+show_personalize_field%3Dfalse%2C+unified_auth%3Dnull%2C+next%3Dnull%2C+register%3Dtrue)&_=' . round(microtime(true) * 1000),
            ['headers' => $headers,
             'cookies' => $jar]);

        $this->info('Trying to login with you email ' . urldecode($email));

        $loginPage = $client->post('/resource/UserSessionResource/create/',
            ['body'            => 'source_url=%2F&data=%7B%22options%22%3A%7B%22username_or_email%22%3A%22' . $email . '%22%2C%22password%22%3A%22' . $password .
                '%22%7D%2C%22context%22%3A%7B%7D%7D&module_path=App%3EHomePage%3EUnauthHomePage%3ESignupForm%3EUserRegister(is_login_form%3Dnull%2C+wall_class%3DdarkWall%2C+container%3Dhome_page%2C+show_personalize_field%3Dfalse%2C+unified_auth%3Dnull%2C+next%3Dnull%2C+register%3Dtrue)',
             'headers'         => $headers,
             'cookies'         => $jar,
             'connect_timeout' => self::CONNECT_TIMEOUT,
             'timeout'         => self::TIMEOUT]);

        $this->info('Setting CSRFToken again after logged in');

        $headers['X-CSRFToken'] = $this->getCSRF($loginPage->getHeader('Set-Cookie'));

        $this->info('Searching your tags: ' . urldecode($keyword));

        $pinsJson = $client->get('/resource/BaseSearchResource/get/?source_url=%2Fsearch%2Fpins%2F%3Frs%3Dac%26len%3D2%26q%3D' . $keyword .
            '%26term_meta%255B%255D%3D' . $keyword . '%257Cautocomplete%257C0&data=%7B%22options%22%3A%7B%22restrict%22%3Anull%2C%22scope%22%3A%22pins%22%2C%22constraint_string%22%3Anull%2C%22show_scope_selector%22%3Atrue%2C%22query%22%3A%22' . $keyword . '%22%7D%2C%22context%22%3A%7B%7D%2C%22module%22%3A%7B%22name%22%3A%22SearchPage%22%2C%22options%22%3A%7B%22restrict%22%3Anull%2C%22scope%22%3A%22pins%22%2C%22constraint_string%22%3Anull%2C%22show_scope_selector%22%3Atrue%2C%22query%22%3A%22' . $keyword . '%22%7D%7D%2C%22render_type%22%3A1%2C%22error_strategy%22%3A0%7D&module_path=App%3EHeader%3ESearchForm%3ETypeaheadField(support_guided_search%3Dtrue%2C+resource_name%3DAdvancedTypeaheadResource%2C+tags%3Dautocomplete%2C+class_name%3DbuttonOnRight%2C+prefetch_on_focus%3Dtrue%2C+support_advanced_typeahead%3Dnull%2C+hide_tokens_on_focus%3Dundefined%2C+search_on_focus%3Dtrue%2C+placeholder%3DSearch%2C+show_remove_all%3Dtrue%2C+enable_recent_queries%3Dtrue%2C+name%3Dq%2C+view_type%3Dguided%2C+value%3D%22%22%2C+input_log_element_type%3D227%2C+populate_on_result_highlight%3Dtrue%2C+search_delay%3D0%2C+is_multiobject_search%3Dtrue%2C+type%3Dtokenized%2C+enable_overlay%3Dtrue)&_=' . round(microtime(true) * 1000),
            ['headers'         => $headers,
             'cookies'         => $jar,
             'connect_timeout' => self::CONNECT_TIMEOUT,
             'timeout'         => self::TIMEOUT]);

        $pinsJson = $pinsJson->getBody();

        $parsedPins = json_decode($pinsJson, true);

        $this->info('Fetching default search page data');

        $pins     = $parsedPins['resource_data_cache'][0]['data']['results'];
        $bookmark = $parsedPins['resource_data_cache'][0]['resource']['options']['bookmarks'][0];

        for ($index = 0; $index < $noOfPages; $index++) {
            $this->info('Collecting data based on pages you requested - pageNo:' . ($index + 1));

            $pinsJson = $client->get('/resource/SearchResource/get/?source_url=%2Fsearch%2F%3Fq%3D' . $keyword .
                '&data=' . urlencode('{"options":{"layout":null,"places":false,"constraint_string":null,"show_scope_selector":null,"query":"' . $keyword . '","scope":"pins","bookmarks":["' . $bookmark . '"]},"context":{}}') . '&_=' . round(microtime(true) * 1000),
                ['headers'         => $headers,
                 'cookies'         => $jar,
                 'connect_timeout' => self::CONNECT_TIMEOUT,
                 'timeout'         => self::TIMEOUT])
                               ->getBody();

            $parsedPins = json_decode($pinsJson, true);
            try {
                $pins     = array_merge($pins, $parsedPins['resource_data_cache'][0]['data']);
                $bookmark = $parsedPins['resource']['options']['bookmarks'][0];
            } catch (\Exception $Exception) {
                break;
            }
        }

        $pinsLiked = 0;

        $total = count($pins);

        $this->info('Total pins we got: ' . $total);

        shuffle($pins);

        $offset = rand(0, ($total - 1));

        if ($total > self::DEFAULT_PINS_LIMIT_PER_TAG) {
            $pins = array_slice($pins, $offset, self::DEFAULT_PINS_LIMIT_PER_TAG);
        } else {
            $pins = array_slice($pins, $offset, rand($offset, ($total - 1)));
        }

        foreach ($pins as $pin) {
            if (isset($pin['id'])) {
                $this->info("Id liked " . $pin['id']);
                $pinsLiked += 1;
                $client->post('/resource/PinLikeResource2/create/',
                    ['body'            => 'source_url=%2Fsearch%2Fpins%2F%3Frs%3Dac%26len%3D2%26q%3D' . $keyword . '%26term_meta%255B%255D%3D' . $keyword .
                        '%257Cautocomplete%257C0&data=%7B%22options%22%3A%7B%22pin_id%22%3A%22' . $pin['id'] . '%22%2C%22source_interest_id%22%3Anull%7D%2C%22context%22%3A%7B%7D%7D&module_path=App%3ESearchPage%3ESearchPageContent%3EGrid%3EGridItems%3EPin%3EPinLikeButton(liked%3Dfalse%2C+source_interest_id%3Dnull%2C+has_icon%3Dtrue%2C+text%3DLike%2C+class_name%3DlikeSmall%2C+pin_id%3D' . $pin['id'] . '%2C+show_text%3Dfalse%2C+ga_category%3Dlike)',
                     'headers'         => $headers,
                     'cookies'         => $jar,
                     'connect_timeout' => self::CONNECT_TIMEOUT,
                     'timeout'         => self::TIMEOUT])
                       ->getBody();
            }
        }

        $this->info('Total pins we liked: ' . $pinsLiked);
        $this->info('Take Care! :)');
    }

    /**
     * @author Rohit Arora
     *
     * @param $cookies
     *
     * @return string
     */
    private function getCSRF($cookies)
    {
        $csrf = '';
        foreach ($cookies as $cookie) {
            if (strpos($cookie, 'csrftoken') !== false) {
                $regex = '/csrftoken=(.*); Domain/';
                preg_match($regex, $cookie, $matches);
                $csrf = $matches[1];
                break;
            }
        }

        return $csrf;
    }

    /**
     * @author Rohit Arora
     *
     * @param $keyword
     *
     * @return array
     */
    private function getTags($keyword)
    {
        $this->info('getting tags based on ' . $keyword);
        $tags       = [];
        $searchPage = $this->Client->get('/search/pins/?q=' . $keyword)
                                   ->getBody();
        $regex      = '/<span class="guideText">(.*)<\/span>/';
        preg_match_all($regex, $searchPage, $matches);

        if ($matches[1]) {
            $tags = $matches[1];
        }

        return $tags;
    }

    /**
     * @author Rohit Arora
     *
     * @param $tags
     *
     * @return string
     */
    private function getRandomTag($tags)
    {
        if ((rand(2, 4) % 2) === 0) {
            return self::KEYWORD . ' ' . $tags[array_rand($tags)];
        }

        return self::KEYWORD . ' ' . $tags[array_rand($tags)] . ' ' . $tags[array_rand($tags)];
    }
}
