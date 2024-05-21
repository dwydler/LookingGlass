<?php
/**
 * LookingGlass - User friendly PHP Looking Glass
 *
 * @package     LookingGlass
 * @author      Nick Adams <nick@iamtelephone.com>
 * @copyright   2015 Nick Adams.
 * @link        http://iamtelephone.com
 * @license     http://opensource.org/licenses/MIT MIT License
 */
namespace Telephone;

/**
 * Create a Looking Glass with output buffering
 */
class LookingGlass
{
    // Global variables
    private $noIpv4Address;
    private $noIpv6Address;

    public function __construct() {
        $this->noIpv4Address = "The Host have no valid IPv4 address.";
        $this->noIpv6Address = "The Host have no valid IPv6 address.";
    }

    /**
     * Execute a 'host' command against given host:
     * Host is a simple utility for performing DNS lookups
     *
     * @param  string $host
     *   IP/URL to perform command against
     * @return boolean
     *   True on success
     */
    public function host($host)
    {
        if ($host = $this->validate($host)) {
            return $this->procExecute('host', $host);
        }
        
        echo $this->noIpv4Address;
        return true;
    }

    public function host6($host) {
        if ($host = $this->validate($host, 6)) {
                return $this->procExecute('host', $host);
        }

        echo $this->noIpv6Address;
        return true;
    }

    /**
     * Execute a 'mtr' command against given host:
     * Mtr combines the functionality of the traceroute and ping programs in a
     * single network diagnostic tool.
     *
     * @param  string $host
     *   IP/URL to perform command against
     * @return boolean
     *   True on success
     */
    public function mtr($host)
    {
        if ($host = $this->validate($host)) {
            return $this->procExecute('mtr -4 --report --report-wide', $host);
        }
        
        echo $this->noIpv4Address;
        return true;
    }

    /**
     * Execute a 'mtr6' command against given host:
     * Mtr combines the functionality of the traceroute and ping programs in a
     * single network diagnostic tool.
     *
     * @param  string $host
     *   IP/URL to perform command against
     * @return boolean
     *   True on success
     */
    public function mtr6($host)
    {
        if ($host = $this->validate($host, 6)) {
            return $this->procExecute('mtr -6 --report --report-wide', $host);
        }
        
        echo $this->noIpv6Address;
        return true;
    }

    /**
     * Execute a 'ping' command against given host:
     * Ping uses the ICMP protocol's mandatory ECHO_REQUEST datagram to elicit
     * an ICMP ECHO_RESPONSE from a host or gateway.
     *
     * @param  string $host
     *   IP/URL to perform command against
     * @param  intger $count
     *   Number of ping requests
     * @return boolean
     *   True on success
     */
    public function ping($host, $count = 4)
    {
        if ($host = $this->validate($host)) {
            return $this->procExecute('ping -4 -c' . $count . ' -w15', $host);
        }
        
        echo $this->noIpv4Address;
        return true;
    }

    /**
     * Execute a 'ping6' command against given host:
     * Ping uses the ICMP protocol's mandatory ECHO_REQUEST datagram to elicit
     * an ICMP ECHO_RESPONSE from a host or gateway.
     *
     * @param  string $host
     *   IP/URL to perform command against
     * @param  intger $count
     *   Number of ping requests
     * @return boolean
     *   True on success
     */
    public function ping6($host, $count = 4)
    {
        if ($host = $this->validate($host, 6)) {
            return $this->procExecute('ping6 -c' . $count . ' -w15', $host);
        }
        
        echo $this->noIpv6Address;
        return true;
    }

    /**
     * Execute a 'traceroute' command against given host:
     * Traceroute tracks the route packets taken from an IP network on their
     * way to a given host.
     *
     * @param  string $host
     *   IP/URL to perform command against
     * @param  intger $fail
     *   Number of failed hops before exiting command
     * @return boolean
     *   True on success
     */
    public function traceroute($host, $fail = 2)
    {
        if ($host = $this->validate($host)) {
            return $this->procExecute('traceroute -4 -w2', $host, $fail);
        }
        
        echo $this->noIpv4Address;
        return true;
    }

    /**
     * Execute a 'traceroute6' command against given host:
     * Traceroute tracks the route packets taken from an IP network on their
     * way to a given host.
     *
     * @param  string $host
     *   IP/URL to perform command against
     * @param  intger $fail
     *   Number of failed hops before exiting command
     * @return boolean
     *   True on success
     */
    public function traceroute6($host, $fail = 2)
    {
        if ($host = $this->validate($host, 6)) {
            return $this->procExecute('traceroute -6 -w2', $host, $fail);
        }
        
        echo $this->noIpv6Address;
        return true;
    }

    // ==================================================================
    //
    // Internal functions
    //
    // ------------------------------------------------------------------

    /**
     * Execute command, and open pipe for input/output
     * This is a work around to terminate the command if X consecutive
     * timeouts occur (traceroute)
     *
     * @param  string  $cmd
     *   Command to perform
     * @param  string  $host
     *   IP/URL to issue command against
     * @param  integer $failCount
     *   Number of consecutive failed hops (traceroute)
     * @return boolean
     *   True on success
     */
    private function procExecute($cmd, $host, $failCount = 2)
    {
        // define output pipes
        $spec = array(
            0 => array("pipe", "r"),
            1 => array("pipe", "w"),
            2 => array("pipe", "w")
        );

        // sanitize + remove single quotes
        $host = str_replace('\'', '', filter_var($host, FILTER_SANITIZE_URL));
        // execute command
        $process = proc_open("{$cmd} '{$host}'", $spec, $pipes, null);

        // check pipe exists
        if (!is_resource($process)) {
            return false;
        }

        // check for mtr/traceroute
        if (strpos($cmd, 'mtr') !== false) {
            $type = 'mtr';
        } elseif (strpos($cmd, 'traceroute') !== false) {
            $type = 'traceroute';
        } else {
            $type = '';
        }

        $fail = 0;
        $match = 0;
        $traceCount = 0;
        $lastFail = 'start';
        // iterate stdout
        while (($str = fgets($pipes[1], 1024)) != null) {
            // check for output buffer
            if (ob_get_level() == 0) {
                ob_start();
            }

            // fix RDNS XSS (outputs non-breakble space correctly)
            $str = htmlspecialchars(trim($str));

            // correct output for mtr
            if ($type === 'mtr') {
                if ($match < 10 && preg_match('/^[0-9]\. /', $str, $string)) {
                    $str = preg_replace('/^[0-9]\. /', '&nbsp;&nbsp;' . $string[0], $str);
                    $match++;
                } else {
                    $str = preg_replace('/^[0-9]{2}\. /', '&nbsp;' . substr($str, 0, 4), $str);
                }
            }
            // correct output for traceroute
            elseif ($type === 'traceroute') {
                if ($match < 10 && preg_match('/^[0-9] /', $str, $string)) {
                    $str = preg_replace('/^[0-9] /', '&nbsp;' . $string[0], $str);
                    $match++;
                }
                // check for consecutive failed hops
                if (strpos($str, '* * *') !== false) {
                    $fail++;
                    if ($lastFail !== 'start'
                        && ($traceCount - 1) === $lastFail
                        &&  $fail >= $failCount
                    ) {
                        echo str_pad($str . '<br />-- Traceroute timed out --<br />', 1024, ' ', STR_PAD_RIGHT);
                        break;
                    }
                    $lastFail = $traceCount;
                }
                $traceCount++;
            }

            // pad string for live output
            echo str_pad($str . '<br />', 1024, ' ', STR_PAD_RIGHT);

            // flush output buffering
            @ob_flush();
            flush();
        }

        // iterate stderr
        while (($err = fgets($pipes[2], 1024)) != null) {
            // check for IPv6 hostname passed to IPv4 command, and vice versa
            if (strpos($err, 'Name or service not known') !== false || strpos($err, 'unknown host') !== false) {
                echo 'Unauthorized request';
                break;
            }
        }

        $status = proc_get_status($process);
        if ($status['running'] == true) {
            // close pipes that are still open
            foreach ($pipes as $pipe) {
                fclose($pipe);
            }
            // retrieve parent pid
            $ppid = $status['pid'];
            // use ps to get all the children of this process
            $pids = preg_split('/\s+/', `ps -o pid --no-heading --ppid $ppid`);
            // kill remaining processes
            foreach($pids as $pid) {
                if (is_numeric($pid)) {
                    posix_kill($pid, 9);
                }
            }
            proc_close($process);
        }
        return true;
    }

    /**
     * Validate IP and URL
     *
     * @param  string  $host
     *   IP/URL to validate
     * @param  integer $type
     *   Define 4 or 6 for IPv4 or IPv6 validation
     * @return boolean
     *   True on success
     */
    private function validate($host, $type = 4)
    {
        // validate ip
        if ($this->validIP($host, $type)) {
            return $host;
        }
        // validate that IPv4 doesn't pass as a valid URL for IPv6
        elseif ($type === 6 && $this->validIP($host, 4)) {
            return false;
        }
        elseif ($type === 4 && $this->validIP($host, 6)) {
            return false;
        }
        // validate url
        elseif ($host = $this->validUrl($host)) {
            return $host;
        }
        return false;
    }

    /**
     * Validate IP: IPv4 or IPv6
     *
     * @param  string  $host
     *   IP to validate
     * @param  integer $type
     *   Define 4 or 6 for IPv4 or IPv6 validation
     * @return boolean
     *   True on success
     */
    private function validIP($host, $type = 4) {
        // Query if $host name is an CNAME entry
        $dns = dns_get_record($host, DNS_CNAME);

        // Check if $Host perhaps an FQDN which is an CNAME entry
        if (isset($dns[0]["type"]) == "CNAME" ) {
            $host = $dns[0]["target"];
        }
        else {
            // Assign the given value
            $ip = $host;
        }

        // Validate IPv4
        if ($type === 4) {
            // Resolving DNS A record
            $dns = dns_get_record($host, DNS_A);

            // If DNS lookup successfully overwrite default value
            if(isset($dns[0]["ip"])) {
                $ip = $dns[0]["ip"];
            }

            // Check if the given ip address matches
            if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4 | FILTER_FLAG_NO_PRIV_RANGE)) {
                return true;
            }
        }

        // Validate IPv6
        if ($type === 6) {
            // Resolving DNS AAAA record
            $dns = dns_get_record($host, DNS_AAAA);

            // If DNS lookup successfully overwrite default value
            if(isset($dns[0]["ipv6"])) {
                $ip = $dns[0]["ipv6"];
            }

            // Check if the given ip address matches
            if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6)) {
                return true;
            }
        }
        
        return false;
    }

    /**
     * Validate hostname/URL
     *
     * @param  string $url
     *   Hostname or URL to validate
     * @return boolean
     *   True on success
     */
    private function validUrl($url)
    {
        // check for http/s
        if ( (stripos($url, 'http://') !== 0) && (stripos($url, 'https://') !== 0) ) {
            $url = 'http://' . $url;
        }

        // validate url
        if (filter_var($url, FILTER_VALIDATE_URL)) {
            // parse url for host
            if ($host = parse_url($url, PHP_URL_HOST)) {
                return $host;
            }
            return $url;
        }
        return false;
    }
}
