<?php

declare(strict_types = 1);

namespace Loginbox\Identity;

use App\Identity\Account;
use App\Identity\AccountSession;

/*
use \ESS\Environment\session;
use \API\Model\sql\dbQuery;
use \API\Platform\engine;
use \DRVC\Comm\dbConnection;
*/

/**
 * Identity Team Manager Class
 *
 * @version	0.1
 */
class Team
{
    /**
     * Cache for getting team info.
     *
     * @type	array
     */
    private $teamInfo = array();

    /**
     * The team to access the identity database.
     *
     * @type	string
     */
    private $teamName = "";

    /**
     * The identity database connection.
     *
     * @type	dbConnection
     */
    private $dbc;

    /**
     * The current account instance.
     *
     * @type	account
     */
    private $account;

    /**
     * An array of instances for each team identity (in case of multiple instances).
     *
     * @type	array
     */
    private static $instances = array();

    /**
     * Get a team account instance.
     *
     * @param	string	$teamName
     * 		The team name for the identity database.
     *
     * @param	string	$authToken
     * 		The current authentication for the account instance.
     *
     * @return	team
     * 		The team instance.
     */
    public static function getInstance($teamName, $authToken = "")
    {
        // Check for an existing instance
        if (!isset(self::$instances[$teamName]))
            self::$instances[$teamName] = new team($teamName, $authToken);

        // Update instance token
        $instance = self::$instances[$teamName];
        $instance->setAccountAuthToken($authToken);

        // Return instance
        return $instance;
    }

    /**
     * Create a new team instance.
     *
     * @param	string	$teamName
     * 		The team name for the identity database.
     *
     * @param	string	$authToken
     * 		The current authentication for the account instance.
     *
     * @return	void
     */
    protected function __construct($teamName, $authToken = "")
    {
        $this->teamName = $teamName;
        $this->dbc = new dbConnection($this->teamName);
        $this->account = account::getInstance($this->teamName, $authToken);
    }

    /**
     * Set the authentication token for the account instance.
     *
     * @param	string	$authToken
     * 		The current authentication for the account instance.
     *
     * @return	void
     */
    public function setAccountAuthToken($authToken)
    {
        $this->account->setAuthToken($authToken);
    }

    /**
     * Get information about the given team id.
     *
     * @param	integer	$teamID
     * 		The team id.
     *
     * @return	array
     * 		An array of the team information.
     */
    public function info($teamID)
    {
        if (!empty($this->teamInfo[$teamID]))
            return $this->teamInfo[$teamID];

        // Get team information
        $q = new dbQuery("32631080802434", "identity.team");
        $attr = array();
        $attr['tid'] = $teamID;
        $result = $this->dbc->execute($q, $attr);
        return $this->teamInfo[$teamID] = $this->dbc->fetch($result);
    }

    /**
     * Update team information.
     *
     * @param	integer	$teamID
     * 		The team id to update information for.
     *
     * @param	string	$name
     * 		The team name.
     * 		It cannot be empty.
     *
     * @param	string	$description
     * 		A team description.
     *
     * @return	boolean
     * 		True on success, false on failure.
     */
    public function updateInfo($teamID, $name, $description = "")
    {
        // Check name
        if (empty($name))
            return FALSE;

        // Update Information
        $q = new dbQuery("2432633615587", "identity.team");
        $attr = array();
        $attr['tid'] = $teamID;
        $attr['name'] = $name;
        $attr['description'] = $description;
        $status = $this->dbc->execute($q, $attr);
        if ($status)
            unset($this->teamInfo[$teamID]);

        return $status;
    }

    /**
     * Remove a team from the identity database.
     *
     * @param	integer	$teamID
     * 		The team id to remove.
     *
     * @return	boolean
     * 		True on success, false on failure.
     */
    public function remove($teamID)
    {
        // Update Information
        $q = new dbQuery("15586672907194", "identity.team");
        $attr = array();
        $attr['tid'] = $teamID;
        return $this->dbc->execute($q, $attr);
    }

    /**
     * Create a new team and set the current account as owner.
     *
     * @param	string	$uname
     * 		The team unique name.
     *
     * @param	string	$name
     * 		The team normal name.
     *
     * @param	string	$description
     * 		The team description.
     *
     * @return	mixed
     * 		The team id on success, false on failure.
     */
    public function create($uname, $name, $description = "")
    {
        // Validate account
        if (!$this->account->validate())
            return FALSE;

        // Normalize uname
        $uname = trim($uname, "._ ");
        $uname = str_replace(" ", "_", $uname);
        $uname = str_replace(".", "_", $uname);

        // Check name
        if (empty($uname) || empty($name))
            return FALSE;

        // Update Information
        $q = new dbQuery("15778076543705", "identity.team");
        $attr = array();
        $attr['uname'] = $uname;
        $attr['name'] = $name;
        $attr['description'] = $description;
        $attr['aid'] = $this->account->getAccountID();
        $result = $this->dbc->execute($q, $attr);
        if (!$result)
            return FALSE;

        $teamInfo = $this->dbc->fetch($result);
        return $teamInfo['id'];
    }

    /**
     * Validates if the current account is member of the given team.
     *
     * @param	integer	$teamID
     * 		The team id to validate the account.
     *
     * @return	boolean
     * 		True if is member, false otherwise.
     */
    public function validate($teamID)
    {
        // Validate account
        if (!$this->account->validate())
            return FALSE;

        // Get current account id
        $accountID = $this->account->getAccountID();

        // Check if there is a valid team
        if (empty($teamID) || empty($accountID))
            return FALSE;

        // Check if account is in team
        $q = new dbQuery("25638597161503", "identity.team");
        $attr = array();
        $attr['tid'] = $teamID;
        $attr['aid'] = $accountID;
        $result = $this->dbc->execute($q, $attr);

        // Check account into team
        if ($this->dbc->get_num_rows($result) > 0)
            return TRUE;

        // Not valid
        return FALSE;
    }

    /**
     * Get the default team for the current account.
     *
     * @return	array
     * 		The default team information.
     */
    public function getDefaultTeam()
    {
        // Check if it's a valid account
        if (!$this->account->validate())
            return FALSE;

        // Get default team
        $q = new dbQuery("3486211728093", "identity.team");
        $attr = array();
        $attr['aid'] = $this->account->getAccountID();
        $result = $this->dbc->execute($q, $attr);
        return $this->dbc->fetch($result);
    }

    /**
     * Set the default team for the given account.
     *
     * @param	integer	$teamID
     * 		The team id to set as default.
     *
     * @return	boolean
     * 		True on success, false on failure.
     */
    public function setDefaultTeam($teamID)
    {
        // Check if it's a valid account
        if (!$this->account->validate())
            return FALSE;

        // Validate account member to given team
        if (!$this->validate($teamID))
            return FALSE;

        // Set default team
        $q = new dbQuery("23211170490594", "identity.team");
        $attr = array();
        $attr['aid'] = $this->account->getAccountID();
        $attr['tid'] = $teamID;
        return $this->dbc->execute($q, $attr);
    }

    /**
     * Add an account to a team.
     *
     * @param	integer	$teamID
     * 		The team id to accept the account.
     *
     * @param	integer	$accountID
     * 		The account id to add to the team.
     *
     * @return	boolean
     * 		True on success, false on failure.
     */
    public function addTeamAccount($teamID, $accountID)
    {
        // Add account to team
        $q = new dbQuery("27826273478896", "identity.team");
        $attr = array();
        $attr['aid'] = $accountID;
        $attr['tid'] = $teamID;
        return $this->dbc->execute($q, $attr);
    }

    /**
     * Remove an account from a team.
     *
     * @param	integer	$teamID
     * 		The team id to remove the account from.
     *
     * @param	integer	$accountID
     * 		The account id to remove from the team.
     *
     * @return	boolean
     * 		True on success, false on failure.
     */
    public function removeTeamAccount($teamID, $accountID)
    {
        // Remove account from team
        $q = new dbQuery("27592696951497", "identity.team");
        $attr = array();
        $attr['aid'] = $accountID;
        $attr['tid'] = $teamID;
        return $this->dbc->execute($q, $attr);
    }

    /**
     * Get all teams of the current account.
     *
     * @return	array
     * 		An array of all teams' information.
     */
    public function getAccountTeams()
    {
        // Check if it's a valid account
        if (!$this->account->validate())
            return NULL;

        // Get account teams from database
        $q = new dbQuery("32625895787891", "identity.team");
        $attr = array();
        $attr['aid'] = $this->account->getAccountID();
        $result = $this->dbc->execute($q, $attr);
        return $this->dbc->fetch($result, TRUE);
    }

    /**
     * Get all team member accounts.
     *
     * @param	integer	$teamID
     * 		The team id to get accounts for.
     *
     * @return	array
     * 		An array of all account information for each member.
     */
    public function getTeamAccounts($teamID)
    {
        // Get team accounts
        $q = new dbQuery("15969829439705", "identity.team");
        $attr = array();
        $attr['tid'] = $teamID;
        $result = $this->dbc->execute($q, $attr);
        return $this->dbc->fetch($result, TRUE);
    }

    /**
     * Get all teams from the identity database.
     *
     * @return	array
     * 		An array of all team information by team id.
     */
    public function getAllTeams()
    {
        // Get all identity teams
        $q = new dbQuery("31785884951161", "identity.team");
        $result = $this->dbc->execute($q);

        // Get teams
        $teams = array();
        while ($teamInfo = $this->dbc->fetch($result))
            $teams[$teamInfo['id']] = $teamInfo;

        return $teams;
    }
}

?>