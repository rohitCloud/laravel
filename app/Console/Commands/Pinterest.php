<?php

namespace App\Console\Commands;

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
        $keyword   = urlencode($this->ask('Which tag you want to like?'));
        $email     = urlencode($this->ask('Please enter your email'));
        $password  = urlencode($this->secret('Please enter your password'));
        $noOfPages = $this->ask('No of pages you want to like (Default is 5)', 5);

        $client  = new Client(['base_uri' => 'https://in.pinterest.com/']);
        $jar     = new CookieJar();
        $headers = ["Host"            => "in.pinterest.com",
                    "Connection"      => "keep-alive",
                    "Accept"          => "text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,*/*;q=0.8",
                    "User-Agent"      => "Mozilla/5.0 (Windows NT 6.3; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/43.0.2357.134 Safari/537.36",
                    "Referer"         => "https://www.google.co.in/",
                    "Accept-Encoding" => "gzip, deflate",
                    "Accept-Language" => "en-US,en;q=0.8,hi;q=0.6"];

        $pinterestHomePage = $client->get('/', ['cookies' => $jar, 'headers' => $headers]);

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

        $client->post('/resource/UserRegisterTrackActionResource/update/',
            ['body'    => 'source_url=%2F&data=%7B%22options%22%3A%7B%22actions%22%3A%5B%22register_landing%22%2C%22register_multi_screen_username_pass_loaded%22%2C%22unauth_home%22%2C%22traffic.desktop.google.HomePage.unauth%22%5D%7D%2C%22context%22%3A%7B%7D%7D',
             'headers' => $headers,
             'cookies' => $jar]);

        $client->get('/resource/EmailExistsResource/get/?source_url=%2F&data=%7B%22options%22%3A%7B%22email%22%3A%22' . $email . '%22%7D%2C%22context%22%3A%7B%7D%7D&module_path=App%3EHomePage%3EUnauthHomePage%3ESignupForm%3EUserRegister(is_login_form%3Dnull%2C+wall_class%3DdarkWall%2C+container%3Dhome_page%2C+show_personalize_field%3Dfalse%2C+unified_auth%3Dnull%2C+next%3Dnull%2C+register%3Dtrue)&_=' . round(microtime(true) * 1000),
            ['headers' => $headers,
             'cookies' => $jar]);

        $loginPage = $client->post('/resource/UserSessionResource/create/',
            ['body'    => 'source_url=%2F&data=%7B%22options%22%3A%7B%22username_or_email%22%3A%22' . $email . '%22%2C%22password%22%3A%22' . $password . '%22%7D%2C%22context%22%3A%7B%7D%7D&module_path=App%3EHomePage%3EUnauthHomePage%3ESignupForm%3EUserRegister(is_login_form%3Dnull%2C+wall_class%3DdarkWall%2C+container%3Dhome_page%2C+show_personalize_field%3Dfalse%2C+unified_auth%3Dnull%2C+next%3Dnull%2C+register%3Dtrue)',
             'headers' => $headers,
             'cookies' => $jar]);

        $headers['X-CSRFToken'] = $this->getCSRF($loginPage->getHeader('Set-Cookie'));

        $pinsJson = $client->get('/resource/BaseSearchResource/get/?source_url=%2Fsearch%2Fpins%2F%3Frs%3Dac%26len%3D2%26q%3D' . $keyword . '%26term_meta%255B%255D%3D' . $keyword . '%257Cautocomplete%257C0&data=%7B%22options%22%3A%7B%22restrict%22%3Anull%2C%22scope%22%3A%22pins%22%2C%22constraint_string%22%3Anull%2C%22show_scope_selector%22%3Atrue%2C%22query%22%3A%22' . $keyword . '%22%7D%2C%22context%22%3A%7B%7D%2C%22module%22%3A%7B%22name%22%3A%22SearchPage%22%2C%22options%22%3A%7B%22restrict%22%3Anull%2C%22scope%22%3A%22pins%22%2C%22constraint_string%22%3Anull%2C%22show_scope_selector%22%3Atrue%2C%22query%22%3A%22' . $keyword . '%22%7D%7D%2C%22render_type%22%3A1%2C%22error_strategy%22%3A0%7D&module_path=App%3EHeader%3ESearchForm%3ETypeaheadField(support_guided_search%3Dtrue%2C+resource_name%3DAdvancedTypeaheadResource%2C+tags%3Dautocomplete%2C+class_name%3DbuttonOnRight%2C+prefetch_on_focus%3Dtrue%2C+support_advanced_typeahead%3Dnull%2C+hide_tokens_on_focus%3Dundefined%2C+search_on_focus%3Dtrue%2C+placeholder%3DSearch%2C+show_remove_all%3Dtrue%2C+enable_recent_queries%3Dtrue%2C+name%3Dq%2C+view_type%3Dguided%2C+value%3D%22%22%2C+input_log_element_type%3D227%2C+populate_on_result_highlight%3Dtrue%2C+search_delay%3D0%2C+is_multiobject_search%3Dtrue%2C+type%3Dtokenized%2C+enable_overlay%3Dtrue)&_=' . round(microtime(true) * 1000),
            ['headers' => $headers,
             'cookies' => $jar]);

        $pinsJson = $pinsJson->getBody();

        $parsedPins = json_decode($pinsJson, true);

        $pins     = $parsedPins['resource_data_cache'][0]['data']['results'];
        $bookmark = $parsedPins['resource_data_cache'][0]['resource']['options']['bookmarks'][0];

        for ($index = 0; $index < $noOfPages; $index++) {
            $pinsJson = $client->get('/resource/SearchResource/get/?source_url=%2Fsearch%2F%3Fq%3D' . $keyword . '&data=' . urlencode('{"options":{"layout":null,"places":false,"constraint_string":null,"show_scope_selector":null,"query":"' . $keyword . '","scope":"pins","bookmarks":["' . $bookmark . '"]},"context":{}}') . '&_=' . round(microtime(true) * 1000),
                ['headers' => $headers,
                 'cookies' => $jar])
                               ->getBody();

            $parsedPins = json_decode($pinsJson, true);
            $pins       = array_merge($pins, $parsedPins['resource_data_cache'][0]['data']);
            $bookmark   = $parsedPins['resource']['options']['bookmarks'][0];
        }

        foreach ($pins as $pin) {
            if (isset($pin['id'])) {
                $this->info("Id liked " . $pin['id']);
                $client->post('/resource/PinLikeResource2/create/',
                    ['body'    => 'source_url=%2Fsearch%2Fpins%2F%3Frs%3Dac%26len%3D2%26q%3D' . $keyword . '%26term_meta%255B%255D%3D' . $keyword . '%257Cautocomplete%257C0&data=%7B%22options%22%3A%7B%22pin_id%22%3A%22' . $pin['id'] . '%22%2C%22source_interest_id%22%3Anull%7D%2C%22context%22%3A%7B%7D%7D&module_path=App%3ESearchPage%3ESearchPageContent%3EGrid%3EGridItems%3EPin%3EPinLikeButton(liked%3Dfalse%2C+source_interest_id%3Dnull%2C+has_icon%3Dtrue%2C+text%3DLike%2C+class_name%3DlikeSmall%2C+pin_id%3D' . $pin['id'] . '%2C+show_text%3Dfalse%2C+ga_category%3Dlike)',
                     'headers' => $headers,
                     'cookies' => $jar])
                       ->getBody();
            }
        }
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
}
