<?php

declare(strict_types = 1);

namespace Loginbox\Identity;

use Loginbox\Identity\Account;

/*
use \API\Geoloc\geoIP;
use \API\Geoloc\region;
use \API\Model\sql\dbQuery;
use \API\Platform\engine;
use \DRVC\Comm\dbConnection;
*/

/**
 * Identity Account Session
 * Singleton class to manage account session.
 *
 * @version    0.1
 */
class AccountSession
{
    /**
     * The current session id.
     *
     * @type    string
     */
    private $sessionID = null;

    /**
     * The session salt.
     *
     * @type    string
     */
    private $salt = "";

    /**
     * The team to access the identity database.
     *
     * @type    string
     */
    private $teamName = "";

    /**
     * The identity database connection.
     *
     * @type    dbConnection
     */
    private $dbc;

    /**
     * An array of instances for each team identity (in case of multiple instances).
     *
     * @type    array
     */
    private static $instances = array();

    /**
     * Static team name for compatibility.
     *
     * @type    string
     */
    private static $staticTeamName = "";
    /**
     * Static mx id for compatibility.
     *
     * @type    string
     */
    private static $staticMxID = "";

    /**
     * Get an accountSession instance for the given attributes.
     *
     * @param    string $teamName
     *        The team name for the identity database.
     *
     * @param    string $sessionID
     *        The current session id.
     *
     * @return    accountSession
     *        The accountSession instance.
     */
    public static function getInstance($teamName, $sessionID = "")
    {
        // Check for an existing instance
        $instanceIdentifier = $teamName . "_" . $sessionID;
        if (!isset(self::$instances[$instanceIdentifier]))
            self::$instances[$instanceIdentifier] = new accountSession($teamName, $sessionID);

        // Return instance
        return self::$instances[$instanceIdentifier];
    }

    /**
     * Create a new accountSession instance.
     *
     * @param    string $teamName
     *        The team name.
     *
     * @param    string $sessionID
     *        The current session id.
     *
     * @return    void
     */
    protected function __construct($teamName, $sessionID)
    {
        // Initialize basics
        $this->teamName = $teamName;
        $this->sessionID = $sessionID;
        $this->dbc = new dbConnection($this->teamName);
    }

    /**
     * Creates a new account session.
     *
     * @param    string  $salt
     *        The logged in generated salt.
     *
     * @param    integer $accountID
     *        The account id to create the instance for.
     *
     * @param    boolean $rememberme
     *        Whether to remember the user in the database or not.
     *
     * @return    boolean
     *        True on success, false on failure.
     */
    public function create($salt, $accountID, $rememberme = false)
    {
        // Create new Account Session
        $q = new dbQuery("25218440938487", "identity.session");
        $attr = array();
        $attr['aid'] = $accountID;
        $attr['salt'] = $salt;
        $attr['ip'] = $_SERVER['REMOTE_ADDR'];
        $attr['date'] = time();
        $attr['userAgent'] = $_SERVER['HTTP_USER_AGENT'];
        $attr['rememberme'] = ($rememberme ? 1 : 0);
        $result = $this->dbc->execute($q, $attr);

        // Database error
        if (!$result)
            return false;

        // Get session id
        $accountSession = $this->dbc->fetch($result);

        return $this->sessionID = $accountSession['id'];
    }

    /**
     * Updates the account's data to the session.
     *
     * @param    integer $accountID
     *        The account id referred to the current session.
     *
     * @return    void
     */
    public function update($accountID)
    {
        // Get session data and check if cookies must be renewed.
        $sessionData = $this->info($accountID);

        // Get session data
        $lastAccess = $sessionData['lastAccess'];
        $rememberme = $sessionData['rememberme'];

        // Get current time
        $currentTime = time();

        // Check if session needs to be renewed
        if ($rememberme && $currentTime - $lastAccess > 7 * 24 * 60 * 60) {
            // Update session in database
            $q = new dbQuery("23782932225255", "identity.session");
            $attr = array();
            $attr['aid'] = $accountID;
            $attr['sid'] = $this->getSessionID();
            $attr['ip'] = $_SERVER['REMOTE_ADDR'];
            $attr['date'] = $currentTime;
            $attr['userAgent'] = $_SERVER['HTTP_USER_AGENT'];
            $attr['rememberme'] = 1;
            $this->dbc->execute($q, $attr);
        }
    }

    /**
     * Get all current session data from the database for the current account.
     *
     * @param    integer $accountID
     *        The account id referred to the current session.
     *
     * @param    integer $sessionID
     *        The session id.
     *        Leave empty for current session.
     *        It is empty by default.
     *
     * @return    array
     *        An array of all session data.
     */
    public function info($accountID, $sessionID = "")
    {
        // Get session id
        $sessionID = (empty($sessionID) ? $this->getSessionID() : $sessionID);
        if (empty($sessionID))
            return null;

        // Get session info
        $q = new dbQuery("35604398472519", "identity.session");
        $attr = array();
        $attr['aid'] = $accountID;
        $attr['sid'] = (empty($sessionID) ? $this->getSessionID() : $sessionID);
        $result = $this->dbc->execute($q, $attr);
        $sessionInfo = $this->dbc->fetch($result);

        // Get extra location from ip
        $countryISO2Code = geoIP::getCountryCode2ByIP($sessionInfo['ip']);
        $countryInfo = region::getCountryInfoByCode2($countryISO2Code);
        $sessionInfo['location'] = $countryInfo['countryName'] . ", " . $countryISO2Code;

        // Return info
        return $sessionInfo;
    }

    /**
     * Deletes a given account session.
     *
     * @param    integer $accountID
     *        The account id referred to the current session.
     *
     * @param    integer $sessionID
     *        The session id.
     *        Leave empty for current session.
     *        It is empty by default.
     *
     * @return    boolean
     *        True on success, false on failure.
     */
    public function remove($accountID, $sessionID = "")
    {
        // Remove account session
        $q = new dbQuery("30008014581206", "identity.session");
        $attr = array();
        $attr['sid'] = (empty($sessionID) ? $this->getSessionID() : $sessionID);
        $attr['aid'] = $accountID;
        if (empty($attr['sid']) || empty($attr['aid']))
            return false;

        return $this->dbc->execute($q, $attr);
    }

    /**
     * Gets the stored salt for the current account session.
     *
     * @param    integer $accountID
     *        The account id referred to the current session.
     *
     * @return    string
     *        The stored session salt.
     */
    public function getSalt($accountID)
    {
        // Get salt from variable
        if (!empty($this->salt))
            return $this->salt;

        // Get salt from account session
        $sessionInfo = $this->info($accountID);

        // Check if the session is valid and exists, otherwise, return empty salt
        if (empty($sessionInfo))
            $this->salt = "";
        else
            $this->salt = $sessionInfo['salt'];

        // Return salt
        return $this->salt;
    }

    /**
     * Gets the account session id.
     *
     * @return    integer
     *        The account session id.
     */
    public function getSessionID()
    {
        return $this->sessionID;
    }

    /**
     * Get all active sessions of the given account.
     *
     * @param    integer $accountID
     *        The account to get the active sessions for.
     *        If empty, get the current account.
     *        It is empty by default.
     *
     * @return    array
     *        An array of all active sessions' details.
     */
    public function getActiveSessions($accountID)
    {
        // Remove active session
        $q = new dbQuery("27779190319041", "identity.session");
        $attr = array();
        $attr['aid'] = $accountID;
        $result = $this->dbc->execute($q, $attr);

        $sessionList = array();
        while ($sinfo = $this->dbc->fetch($result)) {
            // Get extra location from ip
            $countryISO2Code = geoIP::getCountryCode2ByIP($sinfo['ip']);
            $countryInfo = region::getCountryInfoByCode2($countryISO2Code);
            $sinfo['location'] = $countryInfo['countryName'] . ", " . $countryISO2Code;

            // Append to session list
            $sessionList[] = $sinfo;
        }

        // Return session list
        return $sessionList;
    }
}

?>