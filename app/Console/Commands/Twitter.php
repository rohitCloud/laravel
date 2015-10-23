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
        $loginPage = $this->login($email, $password)->getBody();

        return $loginPage;
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
        $token = $this->getAuthToken();

        if (!$token) {
            return false;
        }

        $data = ['authenticity_token'         => $token,
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
}
