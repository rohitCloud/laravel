<?php

namespace App\Console\Commands;

use App\Commands\OpenVPN;
use App\Exceptions\InvalidArguments;
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
    const TIMEOUT                    = 10;
    const CONNECT_TIMEOUT            = 10;
    const DEFAULT_RE_PIN_LIMIT       = 3;
    const TRIPOTO                    = 'tripoto';
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'pinterest:like';

    protected $userAgents = ['Mozilla/5.0 (Windows NT 6.3; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/43.0.2357.134 Safari/537.36',
                             'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/44.0.2217.88 Safari/537.36',
                             'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_10_4) AppleWebKit/600.7.12 (KHTML, like Gecko) Version/8.0.7 Safari/600.7.12',
                             'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/43.0.2203.89 Safari/537.36'];

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'A hack for like on pinterest with tags';

    protected $Client;
    protected $jar;
    protected $headers;
    protected $keyword;
    protected $boardID;
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
            $accounts = json_decode(\File::get(storage_path('app') . DIRECTORY_SEPARATOR . 'accounts.json'), true);
            shuffle($accounts);
            foreach ($accounts as $account) {
                $keyword  = $this->getRandomTag($tags);
                $email    = $account['Email'];
                $password = $account['Pinterest Password'];
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

        $this->keyword = urlencode($keyword);
        $email         = urlencode($email);
        $password      = urlencode($password);

        $this->jar = new CookieJar();

        $userAgent = $this->userAgents[array_rand($this->userAgents)];
        $host      = "www.pinterest.com";
        $origin    = 'https://www.pinterest.com';

        $this->headers = ["Host"            => $host,
                          "Connection"      => "keep-alive",
                          "Accept"          => "text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,*/*;q=0.8",
                          "User-Agent"      => $userAgent,
                          "Referer"         => "https://www.google.com/",
                          "Accept-Encoding" => "gzip, deflate",
                          "Accept-Language" => "en-US,en;q=0.8,hi;q=0.6"];

        $this->info('Opening pinterest.com for csrf token and cookies');

        $pinterestHomePage = $this->getHomePage();

        $this->headers = ['Host'                 => $host,
                          "Connection"           => "keep-alive",
                          "CSP"                  => "active",
                          "Content-Type"         => "application/x-www-form-urlencoded; charset=UTF-8",
                          'Origin'               => $origin,
                          'X-Pinterest-AppState' => 'active',
                          "Referer"              => "https://www.pinterest.com/",
                          'X-Requested-With'     => 'XMLHttpRequest',
                          'Accept'               => 'application/json, text/javascript, */*; q=0.01',
                          'Accept-Encoding'      => 'gzip, deflate',
                          'X-CSRFToken'          => $this->getCSRF($pinterestHomePage->getHeader('Set-Cookie')),
                          'Accept-Language'      => 'en-US,en;q=0.8,hi;q=0.6',
                          'User-Agent'           => $userAgent,
                          'X-APP-VERSION'        => $pinterestHomePage->getHeader('Pinterest-Version')[0],
        ];

        $this->info('Updating track actions pinterest.com/resource/UserRegisterTrackActionResource/update/');
        $this->updateTracking();
        $this->info('Checking if email already exists');
        $this->checkEmailExists($email);
        $this->info('Trying to login with you email ' . urldecode($email));
        $loginPage = $this->login($email, $password);
        $this->info('Setting CSRFToken again after logged in');
        $this->headers['X-CSRFToken'] = $this->getCSRF($loginPage->getHeader('Set-Cookie'));

        try {
            $trip = $this->pinRandomTrip();
            if ($trip) {
                $this->info("Pinned title -> {$trip['title']} link -> {$trip['link']} image -> {$trip['image_url']} category -> {$trip['category']}");
            }
        } catch (\Exception $Exception) {
            $this->info('cant pin error -> ' . $Exception->getMessage());
        }
        $boards = $this->getBoards();

        if (!$this->boardID) {
            $this->boardID = $this->getBoard($boards);
        }

        if (!$this->boardID) {
            $this->boardID = $this->getBoard($boards, self::KEYWORD);
        }

        $this->info('Searching your tags: ' . urldecode($this->keyword));
        $parsedPins = $this->getDefaultPins();
        $this->info('Fetching default search page data');

        $pins = $parsedPins['resource_data_cache'][0]['data']['results'];

        $bookmark = $parsedPins['resource_data_cache'][0]['resource']['options']['bookmarks'][0];

        for ($index = 0; $index < $noOfPages; $index++) {
            $this->info('Collecting data based on pages you requested - pageNo:' . ($index + 1));

            $parsedPins = $this->getPinsWithBookmark($bookmark);
            try {
                $pins     = array_merge($pins, $parsedPins['resource_data_cache'][0]['data']);
                $bookmark = $parsedPins['resource']['options']['bookmarks'][0];
            } catch (\Exception $Exception) {
                break;
            }
        }

        $pinsLiked = 0;
        shuffle($pins);
        $offset        = 0;
        $pins          = array_slice($pins, $offset, self::DEFAULT_PINS_LIMIT_PER_TAG);
        $pinsCount     = count($pins);
        $randomNumbers = $this->getRandomNumbers($offset, $pinsCount, self::DEFAULT_RE_PIN_LIMIT);

        for ($index = 0; $index < $pinsCount; $index++) {
            if (isset($pins[$index]['id']) && (isset($pins[$index]["liked_by_me"]) && !$pins[$index]["liked_by_me"])) {
                $this->info("Id liked " . $pins[$index]['id']);
                $pinsLiked += 1;
                $this->likePin($pins[$index]);
                try {
                    if (in_array($index, $randomNumbers) && $this->rePin($pins[$index])) {
                        $this->info("Id rePined " . $pins[$index]['id'] . " with board id " . $this->boardID);
                    }
                } catch (\Exception $Exception) {
                    $this->info('Cant RePin -> ' . $Exception->getMessage());
                }
                try {
                    $pinner = isset($pins[$index]['pinner']) ? $pins[$index]['pinner'] : [];
                    if (!$pinner['explicitly_followed_by_me'] && in_array($index, $randomNumbers) && $this->follow($pinner['username'], $pinner['id'])) {
                        $this->info("user followed -> " . $pinner['username']);
                    }
                } catch (\Exception $Exception) {
                    $this->info('Cant Follow -> ' . $Exception->getMessage());
                }
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

    /**
     * @author Rohit Arora
     *
     * @param $boards
     * @param $findBoard
     *
     * @return bool
     * @throws InvalidArguments
     */
    private function getBoard($boards, $findBoard = '')
    {
        if (isset($boards['resource_data_cache'][0]['data']['all_boards'])) {
            $boards = $boards['resource_data_cache'][0]['data']['all_boards'];
        } else {
            $boards = [];
        }

        $boardList = [];

        foreach ($boards as $board) {
            $boardList[] = $board['id'];
            if ($findBoard && $board['name'] == $findBoard) {
                return $board['id'];
            }
        }

        if ($findBoard) {
            $this->info('Creating board ' . $findBoard);

            return $this->createBoard($findBoard, 'travel');
        }

        if ($boardList) {
            return $boardList[array_rand($boardList)];
        }

        return false;
    }

    /**
     * @author Rohit Arora
     *
     * @return \Psr\Http\Message\ResponseInterface
     */
    private function getHomePage()
    {
        return $this->Client->get('/', ['cookies' => $this->jar, 'headers' => $this->headers, 'connect_timeout' => self::CONNECT_TIMEOUT, 'timeout' => self::TIMEOUT]);
    }

    private function updateTracking()
    {
        $this->Client->post('/resource/UserRegisterTrackActionResource/update/',
            ['body'            => 'source_url=%2F&data=%7B%22options%22%3A%7B%22actions%22%3A%5B%22register_landing%22%2C%22register_multi_screen_username_pass_loaded%22%2C%22unauth_home%22%2C%22traffic.desktop.google.HomePage.unauth%22%5D%7D%2C%22context%22%3A%7B%7D%7D',
             'headers'         => $this->headers,
             'cookies'         => $this->jar,
             'connect_timeout' => self::CONNECT_TIMEOUT,
             'timeout'         => self::TIMEOUT]);
    }

    /**
     * @author Rohit Arora
     *
     * @param $email
     */
    private function checkEmailExists($email)
    {
        $this->Client->get('/resource/EmailExistsResource/get/?source_url=%2F&data=%7B%22options%22%3A%7B%22email%22%3A%22' . $email .
            '%22%7D%2C%22context%22%3A%7B%7D%7D&module_path=App%3EHomePage%3EUnauthHomePage%3ESignupForm%3EUserRegister(is_login_form%3Dnull%2C+wall_class%3DdarkWall%2C+container%3Dhome_page%2C+show_personalize_field%3Dfalse%2C+unified_auth%3Dnull%2C+next%3Dnull%2C+register%3Dtrue)&_=' . round(microtime(true) * 1000),
            ['headers' => $this->headers,
             'cookies' => $this->jar]);
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
        return $this->Client->post('/resource/UserSessionResource/create/',
            ['body'            => 'source_url=%2F&data=%7B%22options%22%3A%7B%22username_or_email%22%3A%22' . $email . '%22%2C%22password%22%3A%22' . $password .
                '%22%7D%2C%22context%22%3A%7B%7D%7D&module_path=App%3EHomePage%3EUnauthHomePage%3ESignupForm%3EUserRegister(is_login_form%3Dnull%2C+wall_class%3DdarkWall%2C+container%3Dhome_page%2C+show_personalize_field%3Dfalse%2C+unified_auth%3Dnull%2C+next%3Dnull%2C+register%3Dtrue)',
             'headers'         => $this->headers,
             'cookies'         => $this->jar,
             'connect_timeout' => self::CONNECT_TIMEOUT,
             'timeout'         => self::TIMEOUT]);
    }

    /**
     * @author Rohit Arora
     *
     * @return mixed
     */
    private function getBoards()
    {
        $boardsJson = $this->Client->get('/resource/BoardPickerBoardsResource/get/?source_url=%2Fsearch%2Fpins%2F%3Fq%3Dtravel%2520cars&data=%7B%22options%22%3A%7B%22filter%22%3A%22all%22%2C%22field_set_key%22%3A%22board_picker%22%2C%22allow_stale%22%3Atrue%7D%2C%22context%22%3A%7B%7D%7D&module_path=App%3EHeader%3ESearchForm%3ETypeaheadField(support_guided_search%3Dtrue%2C+resource_name%3DAdvancedTypeaheadResource%2C+tags%3Dautocomplete%2C+class_name%3DbuttonOnRight%2C+prefetch_on_focus%3Dtrue%2C+support_advanced_typeahead%3Dnull%2C+hide_tokens_on_focus%3Dundefined%2C+search_on_focus%3Dtrue%2C+placeholder%3DSearch%2C+show_remove_all%3Dtrue%2C+enable_recent_queries%3Dtrue%2C+name%3Dq%2C+view_type%3Dguided%2C+value%3D%22%22%2C+input_log_element_type%3D227%2C+populate_on_result_highlight%3Dtrue%2C+search_delay%3D0%2C+is_multiobject_search%3Dtrue%2C+type%3Dtokenized%2C+enable_overlay%3Dtrue)&_=' . round(microtime(true) * 1000),
            ['headers'         => $this->headers,
             'cookies'         => $this->jar,
             'connect_timeout' => self::CONNECT_TIMEOUT,
             'timeout'         => self::TIMEOUT])
                                   ->getBody();

        $boards = json_decode($boardsJson, true);

        return $boards;
    }

    /**
     * @author Rohit Arora
     *
     * @return array
     */
    private function getDefaultPins()
    {
        $pinsJson = $this->Client->get('/resource/BaseSearchResource/get/?source_url=%2Fsearch%2Fpins%2F%3Frs%3Dac%26len%3D2%26q%3D' . $this->keyword .
            '%26term_meta%255B%255D%3D' . $this->keyword . '%257Cautocomplete%257C0&data=%7B%22options%22%3A%7B%22restrict%22%3Anull%2C%22scope%22%3A%22pins%22%2C%22constraint_string%22%3Anull%2C%22show_scope_selector%22%3Atrue%2C%22query%22%3A%22' . $this->keyword . '%22%7D%2C%22context%22%3A%7B%7D%2C%22module%22%3A%7B%22name%22%3A%22SearchPage%22%2C%22options%22%3A%7B%22restrict%22%3Anull%2C%22scope%22%3A%22pins%22%2C%22constraint_string%22%3Anull%2C%22show_scope_selector%22%3Atrue%2C%22query%22%3A%22' . $this->keyword . '%22%7D%7D%2C%22render_type%22%3A1%2C%22error_strategy%22%3A0%7D&module_path=App%3EHeader%3ESearchForm%3ETypeaheadField(support_guided_search%3Dtrue%2C+resource_name%3DAdvancedTypeaheadResource%2C+tags%3Dautocomplete%2C+class_name%3DbuttonOnRight%2C+prefetch_on_focus%3Dtrue%2C+support_advanced_typeahead%3Dnull%2C+hide_tokens_on_focus%3Dundefined%2C+search_on_focus%3Dtrue%2C+placeholder%3DSearch%2C+show_remove_all%3Dtrue%2C+enable_recent_queries%3Dtrue%2C+name%3Dq%2C+view_type%3Dguided%2C+value%3D%22%22%2C+input_log_element_type%3D227%2C+populate_on_result_highlight%3Dtrue%2C+search_delay%3D0%2C+is_multiobject_search%3Dtrue%2C+type%3Dtokenized%2C+enable_overlay%3Dtrue)&_=' . round(microtime(true) * 1000),
            ['headers'         => $this->headers,
             'cookies'         => $this->jar,
             'connect_timeout' => self::CONNECT_TIMEOUT,
             'timeout'         => self::TIMEOUT]);

        $pinsJson = $pinsJson->getBody();

        $parsedPins = json_decode($pinsJson, true);
        return $parsedPins;
    }

    /**
     * @author Rohit Arora
     *
     * @param $bookmark
     *
     * @return mixed
     */
    private function getPinsWithBookmark($bookmark)
    {
        $pinsJson = $this->Client->get('/resource/SearchResource/get/?source_url=%2Fsearch%2F%3Fq%3D' . $this->keyword .
            '&data=' . urlencode('{"options":{"layout":null,"places":false,"constraint_string":null,"show_scope_selector":null,"query":"' . $this->keyword . '","scope":"pins","bookmarks":["' . $bookmark . '"]},"context":{}}') . '&_=' . round(microtime(true) * 1000),
            ['headers'         => $this->headers,
             'cookies'         => $this->jar,
             'connect_timeout' => self::CONNECT_TIMEOUT,
             'timeout'         => self::TIMEOUT])
                                 ->getBody();

        $parsedPins = json_decode($pinsJson, true);
        return $parsedPins;
    }

    /**
     * @author Rohit Arora
     *
     * @param $pin
     */
    private function likePin($pin)
    {
        $this->Client->post('/resource/PinLikeResource2/create/',
            ['body'            => 'source_url=%2Fsearch%2Fpins%2F%3Frs%3Dac%26len%3D2%26q%3D' . $this->keyword . '%26term_meta%255B%255D%3D' . $this->keyword .
                '%257Cautocomplete%257C0&data=%7B%22options%22%3A%7B%22pin_id%22%3A%22' . $pin['id'] . '%22%2C%22source_interest_id%22%3Anull%7D%2C%22context%22%3A%7B%7D%7D&module_path=App%3ESearchPage%3ESearchPageContent%3EGrid%3EGridItems%3EPin%3EPinLikeButton(liked%3Dfalse%2C+source_interest_id%3Dnull%2C+has_icon%3Dtrue%2C+text%3DLike%2C+class_name%3DlikeSmall%2C+pin_id%3D' . $pin['id'] . '%2C+show_text%3Dfalse%2C+ga_category%3Dlike)',
             'headers'         => $this->headers,
             'cookies'         => $this->jar,
             'connect_timeout' => self::CONNECT_TIMEOUT,
             'timeout'         => self::TIMEOUT])
                     ->getBody();
    }

    /**
     * @author Rohit Arora
     *
     * @param $userName
     * @param $userId
     *
     * @return bool
     */
    private function follow($userName, $userId)
    {
        $this->Client->post('/resource/UserFollowResource/create/',
            ['body'            => 'source_url=%2F' . $userName . '%2F&data=%7B%22options%22%3A%7B%22user_id%22%3A%22' . $userId . '%22%7D%2C%22context%22%3A%7B%7D%7D&module_path=App%3EUserProfilePage%3EUserProfileHeader%3EUserFollowButton(followed%3Dfalse%2C+is_me%3Dfalse%2C+text%3DFollow%2C+memo%3D%5Bobject+Object%5D%2C+disabled%3Dfalse%2C+suggested_users_menu%3D%5Bobject+Object%5D%2C+follow_ga_category%3Duser_follow%2C+follow_text%3DFollow%2C+follow_class%3Dprimary%2C+user_id%3D' . $userId . '%2C+unfollow_text%3DUnfollow%2C+unfollow_ga_category%3Duser_unfollow%2C+color%3Dprimary)',
             'headers'         => $this->headers,
             'cookies'         => $this->jar,
             'connect_timeout' => self::CONNECT_TIMEOUT,
             'timeout'         => self::TIMEOUT])
                     ->getBody();

        return true;
    }

    /**
     * @author Rohit Arora
     *
     * @param $pin
     *
     * @return bool
     */
    private function rePin($pin)
    {
        if ($this->boardID) {
            $body = 'source_url=%2Fsearch%2Fpins%2F%3Frs%3Dac%26len%3D2%26q%3D' . $this->keyword . '%26term_meta%255B%255D%3D' . $this->keyword .
                '%257Cautocomplete%257C0&data=%7B%22options%22%3A%7B%22pin_id%22%3A%22' . $pin['id'] . '%22%2C%22description%22%3A%22' . urlencode($pin['description']) .
                '%22%2C%22link%22%3A%22' . urlencode($pin['link']) . '%22%2C%22is_video%22%3Afalse%2C%22board_id%22%3A%22' . $this->boardID .
                '%22%7D%2C%22context%22%3A%7B%7D%7D&module_path=App%3EModalManager%3EModal%3EPinCreate3%3EBoardPicker%3ESelectList(view_type%3DpinCreate3%2C+selected_section_index%3Dundefined%2C+selected_item_index%3Dundefined%2C+highlight_matched_text%3Dtrue%2C+suppress_hover_events%3Dundefined%2C+scroll_selected_item_into_view%3Dtrue%2C+select_first_item_after_update%3Dfalse%2C+item_module%3D%5Bobject+Object%5D)';
            $this->info($body);

            $this->Client->post('/resource/RepinResource/create/',
                ['body'            => $body,
                 'headers'         => $this->headers,
                 'cookies'         => $this->jar,
                 'connect_timeout' => self::CONNECT_TIMEOUT,
                 'timeout'         => self::TIMEOUT])
                         ->getBody();

            return true;
        }

        return false;
    }

    /**
     * @author Rohit Arora
     *
     * @param        $imageURL
     * @param        $link
     * @param string $description
     *
     * @return bool
     */
    private function pin($imageURL, $link, $description = '')
    {
        if ($this->boardID && $imageURL) {
            $body = 'source_url=%2Fpin%2Ffind%2F%3Furl%3D' . urlencode($link) . '&data=%7B%22options%22%3A%7B%22method%22%3A%22scraped%22%2C%22description%22%3A%22'
                . urlencode($description) . '%22%2C%22link%22%3A%22' . urlencode($link) . '%22%2C%22image_url%22%3A%22' . urlencode($imageURL) . '%22%2C%22board_id%22%3A%22'
                . $this->boardID . '%22%7D%2C%22context%22%3A%7B%7D%7D&module_path=App%3EModalManager%3EModal%3EPinCreate3%3EBoardPicker%3ESelectList(view_type%3DpinCreate3%2C+selected_section_index%3Dundefined%2C+selected_item_index%3Dundefined%2C+highlight_matched_text%3Dtrue%2C+suppress_hover_events%3Dundefined%2C+scroll_selected_item_into_view%3Dtrue%2C+select_first_item_after_update%3Dfalse%2C+item_module%3D%5Bobject+Object%5D)';
            $this->info($body);
            $this->Client->post('/resource/PinResource/create/',
                ['body'            => $body,
                 'headers'         => $this->headers,
                 'cookies'         => $this->jar,
                 'connect_timeout' => self::CONNECT_TIMEOUT,
                 'timeout'         => self::TIMEOUT])
                         ->getBody();

            return true;
        }

        return false;
    }

    /**
     * @author Rohit Arora
     *
     * @param        $board
     * @param string $category
     * @param string $description
     *
     * @return bool
     */
    private function createBoard($board, $category = 'travel', $description = '')
    {
        $boardJson = $this->Client->post('/resource/BoardResource/create/',
            ['body'            => 'source_url=%2Fsearch%2F%3Fq%3D' . $this->keyword . '&data=%7B%22options%22%3A%7B%22name%22%3A%22' . urlencode($board) .
                '%22%2C%22category%22%3A%22' . urlencode($category) . '%22%2C%22description%22%3A%22' . urlencode($description) .
                '%22%2C%22privacy%22%3A%22public%22%2C%22layout%22%3A%22default%22%7D%2C%22context%22%3A%7B%7D%7D&module_path=App%3ESearchPage%3ESearchPageContent%3EGrid%3EGridItems%3EPin%3EShowModalButton(module%3DPinCreate3)%23App%3EModalManager%3EModal(modal_style%3DwebNewContentNewRepin%2C+custom_entrance_animation%3Dtrue%2C+mask_type%3DwebNewContentNewRepin%2C+custom_exit_animation%3Dtrue%2C+container_class%3DwebNewContentNewRepin)',
             'headers'         => $this->headers,
             'cookies'         => $this->jar,
             'connect_timeout' => self::CONNECT_TIMEOUT,
             'timeout'         => self::TIMEOUT])
                                  ->getBody();

        $board = json_decode($boardJson, true);

        if (isset($board['resource_response']['data']['id'])) {
            return $board['resource_response']['data']['id'];
        }

        return false;
    }

    /**
     * @author Rohit Arora
     *
     * @param $min
     * @param $max
     * @param $quantity
     *
     * @return array
     */
    private function getRandomNumbers($min, $max, $quantity)
    {
        $numbers = range($min, $max);
        shuffle($numbers);
        return array_slice($numbers, 0, $quantity);
    }

    /**
     * @author Rohit Arora
     *
     * @param $URL
     *
     * @return mixed
     */
    private function getPinnableWithURL($URL)
    {
        $pinnableJson = $this->Client->get('/pin/find/?url=' . urlencode($URL),
            ['headers'         => $this->headers,
             'cookies'         => $this->jar,
             'connect_timeout' => self::CONNECT_TIMEOUT,
             'timeout'         => self::TIMEOUT])
                                     ->getBody();

        $pinnableItems = json_decode($pinnableJson, true);
        $pinnableItems = $pinnableItems['resource_data_cache'][0]['data']['items'];
        $items         = [];
        foreach ($pinnableItems as $item) {
            if (preg_match('/static.*\/l\/.*/', $item['url'])) {
                $item['url'] = str_replace('filter/l', 'transfer', $item['url']);
                $items[]     = $item['url'];
            }
        }

        return $items;
    }

    /**
     * @author Rohit Arora
     *
     * @return mixed
     */
    private function getRandomPinnable()
    {
        $trip = [];
        if (rand(0, 2)) {
            $tripJson = file_get_contents(env('TRIP_URL'));
            $trip     = json_decode($tripJson, true);
        }

        return $trip;
    }

    /**
     * @author Rohit Arora
     *
     * @return bool
     */
    private function pinRandomTrip()
    {
        $trip = $this->getRandomPinnable();
        if (!$trip || !$trip['link'] || !$trip['category'] || !$trip['title']) {
            return false;
        }

        $trip['category'] = explode(',', $trip['category'])[0];

        $keyword = $this->getHashTags($trip['category']);

        $boards        = $this->getBoards();
        $this->boardID = $this->getBoard($boards, $trip['category']);
        $pinnableItems = $this->getPinnableWithURL($trip['link']);

        if ((!isset($trip['image_url']) || !$trip['image_url']) && $pinnableItems) {
            $trip['image_url'] = $pinnableItems[array_rand($pinnableItems)];
        }

        if ($this->pin($trip['image_url'], $trip['link'], $trip['title'] . $keyword)) {
            return $trip;
        }

        return false;
    }

    /**
     * @author Rohit Arora
     *
     * @param string $additionalKeyword
     *
     * @return string
     */
    private function getHashTags($additionalKeyword = '')
    {
        $keywords = explode(' ', trim(urldecode($this->keyword) . ' ' . $additionalKeyword));
        if (rand(0, 2)) {
            $keywords[] = self::TRIPOTO;
        }
        shuffle($keywords);
        $keyword = ' #' . implode(' #', $keywords);
        return $keyword;
    }
}
