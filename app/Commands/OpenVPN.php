<?php

namespace App\Commands;

use GuzzleHttp\Client;
use Illuminate\Console\Command;

/**
 * @author  Rohit Arora
 *
 * Class OpenVPN
 * @package App\Commands
 */
class OpenVPN extends Command
{
    protected $Client;
    protected $signature = 'openvpn';
    protected $Command;

    const IP_URL               = 'http://icanhazip.com/';
    const RETRY                = 5;
    const RETRY_SECONDS        = 10;
    const DEFAULT_VPN_LOCATION = 'VPN';
    const TRAFFIC_SOURCE       = self::SOURCE_USA;
    const SOURCE_USA           = 'USA.';

    /**
     * OpenVPN constructor.
     *
     * @param Client $Client
     */
    public function __construct(Client $Client)
    {
        parent::__construct();
        $this->Client  = $Client;
        $this->Command = $this;
    }

    /**
     * @author Rohit Arora
     *
     * @return $this
     */
    public function connect()
    {
        chdir(storage_path(self::DEFAULT_VPN_LOCATION));
        $vpn = $this->getRandomVPN();
        $this->Command->info('trying to connect VPN ' . $vpn);
        exec('sudo openvpn ' . $vpn . ' > /dev/null 2>&1 &');

        return $this;
    }

    /**
     * @author Rohit Arora
     */
    public function kill()
    {
        $this->Command->info('killing current vpn if available');
        exec('sudo killall openvpn');
        sleep(1);

        return $this;
    }

    /**
     * @author Rohit Arora
     *
     * @param Command $command
     *
     * @return bool
     */
    public function reconnect(Command $command)
    {
        $this->Command = $command;

        $this->kill()
             ->connect();

        try {
            $originalIP = $this->getCurrentIP();
            $this->Command->info('Original IP ' . $originalIP);

            if (!$this->checkIP($originalIP)) {
                return $this->reconnect($command);
            }
        } catch (\Exception $Exception) {
            return false;
        }


        return true;
    }

    /**
     * @author Rohit Arora
     *
     * @param     $originalIP
     * @param int $count
     *
     * @return bool|string
     */
    public function checkIP($originalIP, $count = 1)
    {
        sleep(self::RETRY_SECONDS);
        $newIP = $this->getCurrentIP();
        if ($originalIP == $newIP || $newIP == '') {
            if ($count == self::RETRY) {
                return false;
            }

            $count += 1;

            $this->Command->info('trying again ' . $count . ' time');
            return $this->checkIP($originalIP, $count);
        }
        $this->Command->info('Your new ip ' . $newIP);

        return $newIP;
    }

    /**
     * @author Rohit Arora
     *
     * @return array
     */
    public function getRandomVPN()
    {
        $files = \File::files(storage_path(self::DEFAULT_VPN_LOCATION));
        shuffle($files);
        foreach ($files as $file) {
            if (strpos($file, self::TRAFFIC_SOURCE) !== false) {
                return $file;
            }
        }

        return false;
    }

    /**
     * @author Rohit Arora
     *
     * @return string
     */
    public function getCurrentIP()
    {
        return trim($this->Client->get(self::IP_URL, ['connect_timeout' => 10, 'timeout' => 5])
                                 ->getBody());
    }
}
