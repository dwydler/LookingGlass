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
namespace Telephone\LookingGlass;

/**
 * Implement rate limiting of network commands
 */
class RateLimit
{
    /**
     * Check rate limit against SQLite database
     *
     * @param  integer $limit
     *   Number of commands per hour
     * @param  string  $clientip
     *   The real client ip
     * @return boolean
     *   True on success
     */
    public function rateLimit($limit, $clientip)
    {
        // check if rate limit is disabled
        if ($limit === 0) {
            return false;
        }

        /**
         * check for DB file
         * if nonexistent, no rate limit is applied
         */
        if (!file_exists('LookingGlass/ratelimit.db')) {
            exit('SQLITE - Rate limit is activated. The file \'Looking Glass/rate limit.db\' could not be found.');
        }

        // connect to DB
        try {
            $dbh = new \PDO('sqlite:LookingGlass/ratelimit.db');
        } catch (PDOException $e) {
            // check error code of execution
            $this->ErrorMessage($q);
        }

        // check for IP
        try {
            $q = $dbh->prepare('SELECT * FROM RateLimit WHERE ip = ?');
            $q->execute(array($clientip));
            $row = $q->fetch(\PDO::FETCH_ASSOC);
        } catch (\PDOException $e) {
            // check error code of execution
            $this->ErrorMessage($q);
        }

        // save time by declaring time()
        $time = time();

        // if IP does not exist
        if (!isset($row['ip'])) {
            // create new record
            try {
                $q = $dbh->prepare('INSERT INTO RateLimit (ip, hits, accessed) VALUES (?, ?, ?)');
                $q->execute(array($clientip, 1, $time));
            } catch (\PDOException $e) {
                // check error code of execution
                $this->ErrorMessage($q);
            }
        }

        // typecast SQLite results
        $accessed = (int) $row['accessed'] + 3600;
        $hits = (int) $row['hits'];

        // apply rate limit
        if ($accessed > $time) {
            if ($hits >= $limit) {
                $reset = (int) (($accessed - $time) / 60);
                if ($reset <= 1) {
                    exit('SQLITE - Rate limit exceeded. Try again in 1 minute.');
                }
                exit('SQLITE - Rate limit exceeded. Try again in '.$reset.' minutes.');
            }
            // update hits
            try {
                $q = $dbh->prepare('UPDATE RateLimit SET hits = ? WHERE ip = ?');
                $q->execute(array(($hits + 1), $clientip));
			} catch (\PDOException $e) {
                // check error code of execution
                $this->ErrorMessage($q);
            }
        } else {
            // reset hits + accessed time
            try {
                $q = $dbh->prepare('UPDATE RateLimit SET hits = ?, accessed = ? WHERE ip = ?');
                $q->execute(array(1, time(), $clientip));
			} catch (\PDOException $e) {
                // check error code of execution
                $this->ErrorMessage($q);
            }
        }
		
		// close database connection
        $dbh = null;
		
        return true;
    }
	
	/**
     * Check errror code of sql execution
     *
     * @param  array $error
     *   Array of errorCode und ErrorInfo
     * @return boolean
     *   True on success
     */
	private function ErrorMessage ($error) {

		$errors = $error->errorInfo();
        exit("SQLITE - Error Code: ".$errors[1]."; Message: ".$errors[2]);
	}
}