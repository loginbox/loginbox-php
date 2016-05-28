<?php

declare(strict_types = 1);

namespace Loginbox\Identity;

use Exception;
use InvalidArgumentException;
use Loginbox\Loginbox;

/**
 * Account Identity
 * Manages an account identity
 *
 * @version    0.1
 */
class Account
{
    /**
     * @type Loginbox
     */
    protected $loginbox;

    /**
     * @type string
     */
    protected $token;

    /**
     * Account constructor.
     *
     * @param Loginbox $loginbox
     * @param string   $token
     */
    public function __construct(Loginbox $loginbox, $token = "")
    {
        $this->loginbox = $loginbox;
        $this->token = $token;
    }

    /**
     * @param $username
     * @param $password
     *
     * @return bool
     * @throws InvalidArgumentException
     */
    public function authenticate($username, $password)
    {
        // Check that the password is not empty
        if (empty($username) || empty($password)) {
            throw new InvalidArgumentException("Authentication requires both username and password.");
        }

        // Prepare data to send
        $postData = array();
        $postData['username'] = $username;
        $postData['password'] = $password;

        // Send authentication request
        $response = $this->loginbox->post("/authenticate", $postData);
        if ($response->http_response_code === 200) {
            // Parse response
            return $response->http_response_body['status'];
        }

        return false;
    }

    /**
     * Authenticates the account and creates an active account session.
     *
     * @param    string  $username
     *        The account username.
     *
     * @param    string  $password
     *        The account password.
     *
     * @param    boolean $rememberme
     *        Whether to remember the user or not.
     *        Duration: 1 month.
     *
     * @return    boolean
     *        True on success, false on authentication failure.
     */
    public function login($username, $password, $rememberme = false)
    {
        // Check if there is a user already logged in
        if ($this->validate())
            return false;

        // Authenticate user
        if (!$this->authenticate($username, $password))
            return false;

        // Get account info from username
        $accountInfo = $this->getAccountByUsername($username, $includeEmail = true, $fullList = false);

        // Get Account Info
        $this->accountID = $accountInfo['id'];
        $this->personID = null;
        if (!$accountInfo['locked'])
            $this->personID = $accountInfo['person_id'];

        // Create salt
        $salt = hash("SHA256", "ast_salt_" . time() . "_" . mt_rand());

        // Create Account Session
        $this->sessionID = $this->getAccountSessionInstance()->create($salt, $this->accountID, $this->personID, $rememberme);

        // Create auth token
        $payload = array();
        $payload['acc'] = $this->accountID;
        $payload['prs'] = $this->personID;
        $payload['ssid'] = $this->sessionID;
        $this->authToken = authToken::generate($payload, $salt);

        // Update session
        $this->updateSession();
        $this->loggedIn = true;

        return true;
    }

    /**
     * Validates if the user is logged in.
     *
     * @param    boolean $logoutOnFail
     *        Set whether you want to logout (delete the active session) the user if the current tokens are invalid.
     *        It is FALSE by default.
     *
     * @return    boolean
     *        True on success, false on failure.
     */
    public function validate($logoutOnFail = false)
    {
        // Check loggedIn
        if ($this->loggedIn)
            return $this->loggedIn;

        // Get authentication token
        $this->authToken = $this->getAuthToken();

        // Verify token
        $accountID = $this->getAccountID();
        $salt = $this->getAccountSessionInstance()->getSalt($accountID);
        if (!authToken::verify($this->authToken, $salt)) {
            // Check whether to logout or not
            if ($logoutOnFail)
                $this->logout();

            // Return false
            return false;
        }

        // Update account session
        $this->updateSession();

        // Return valid status
        $this->loggedIn = true;

        return true;
    }

    /**
     * Update the current account session and renew cookies if necessary.
     *
     * @return    void
     */
    public function updateSession()
    {
        // Update Account Session
        $this->getAccountSessionInstance()->update($this->getAccountID());
    }

    /**
     * Logout the account from the system.
     * Delete active session.
     * Delete cookies.
     *
     * @return    void
     */
    public function logout()
    {
        // Remove Active Session
        $sessionID = $this->getSessionID();
        if (!empty($sessionID))
            $this->getAccountSessionInstance()->remove($this->getAccountID(), $sessionID);

        // Set class variables to null
        $this->loggedIn = false;
        $this->accountID = null;
        $this->personID = null;
        $this->sessionID = null;
        $this->authToken = null;
    }

    /**
     * Switch from one account to another.
     *
     * @param    integer $accountID
     *        The new account id to switch to.
     *
     * @param    string  $password
     *        The new account's password.
     *
     * @return    boolean
     *        Returns true on success and false if the current account is locked and cannot switch or the
     *        authentication fails.
     */
    public function switchAccount($accountID, $password)
    {
        // Check if it's a valid account
        if (!$this->validate())
            return false;

        // Check if current account is locked
        if ($this->isLocked())
            return false;

        // If is valid, and the account is not locked, switch account
        $accountInfo = $this->info($accountID);
        $username = $accountInfo['username'];
        if ($this->authenticate($username, $password, $accountID)) {
            $this->logout();

            return $this->login($username, $password);
        }

        return false;
    }

    /**
     * Update current account password.
     *
     * @param    string $currentPassword
     *        The current account password.
     *
     * @param    string $newPassword
     *        The new account password.
     *
     * @return    boolean
     *        True on success, false on failure.
     */
    public function updatePassword($currentPassword, $newPassword)
    {
        // Check if it's a valid account
        if (!$this->validate())
            return false;

        // Check if current account is locked
        if ($this->isLocked())
            return false;

        // If is valid, and the account is not locked, switch account
        $username = $this->getUsername(true);
        if ($this->authenticate($username, $currentPassword)) {
            // Update password in database
            $q = new dbQuery("35676347294164", "identity.account");
            $attr = array();
            $attr['aid'] = $this->getAccountID();
            $attr['password'] = password_hash($newPassword, PASSWORD_BCRYPT);

            return $this->dbc->execute($q, $attr);
        }

        return false;
    }

    /**
     * Update the account's password using the reset id from the recovery process.
     *
     * @param    string $resetID
     *        The reset id hash token.
     *
     * @param    string $newPassword
     *        The new account password.
     *
     * @return    boolean
     *        True on success, false on failure.
     */
    public function updatePasswordByReset($resetID, $newPassword)
    {
        // STATIC COMPATIBILITY CHECK
        if (!(isset($this) && get_class($this) == __CLASS__))
            return account::getInstance(self::$staticTeamName)->updatePasswordByReset($resetID, $newPassword);

        // Get account by reset password id
        $q = new dbQuery("2378186783046", "identity.account");
        $attr = array();
        $attr['reset'] = $resetID;
        $result = $this->dbc->execute($q, $attr);
        $accountInfo = $this->dbc->fetch($result);

        // If account is valid, update password
        if ($result && is_array($accountInfo)) {
            // Update password in database
            $q = new dbQuery("35676347294164", "identity.account");
            $attr = array();
            $attr['aid'] = $accountInfo['id'];
            $attr['password'] = password_hash($newPassword, PASSWORD_BCRYPT);

            return $this->dbc->execute($q, $attr);
        }

        return false;
    }

    /**
     * Generate a reset id token for the current account.
     *
     * @param    integer $aid
     *        The account id to generate the token for.
     *
     * @return    mixed
     *        The generated token on success, false on failure.
     */
    public function generateResetId($aid)
    {
        // STATIC COMPATIBILITY CHECK
        if (!(isset($this) && get_class($this) == __CLASS__))
            return account::getInstance(self::$staticTeamName)->generateResetId($aid);

        // Generate resetID.
        $resetID = md5($aid . time());

        // Put reset in the database.
        $q = new dbQuery("18841240149912", "identity.account");
        $attr = array();
        $attr['new_reset'] = $resetID;
        $attr['aid'] = $aid;
        $result = $this->dbc->execute($q, $attr);

        if ($result) {
            // Update password in database
            return $resetID;
        }

        return $result;
    }

    /**
     * Remove account form the database.
     *
     * @param    integer $accountID
     *        The account id to be removed.
     *
     * @return    boolean
     *        True on success, false on failure.
     */
    public function removeAccount($accountID)
    {
        // Get account connected persons
        $q = new dbQuery("34515928054033", "identity.account");
        $attr = array();
        $attr['aid'] = $accountID;
        $result = $this->dbc->execute($q, $attr);
        $persons = $this->dbc->fetch($result, true);

        // Remove account from database
        $q = new dbQuery("31116448436297", "identity.account");
        $attr = array();
        $attr['aid'] = $accountID;
        $result = $this->dbc->execute($q, $attr);
        if ($result) {
            // Remove connected person
            // if not connected to other accounts
            $personInstance = person::getInstance($this->teamName);
            foreach ($persons as $personInfo)
                $personInstance->remove($personInfo['id']);
        }

        // Return result (true or false)
        return $result;
    }

    /**
     * Gets whether the system remembers the logged in account.
     *
     * @return    boolean
     *        True if the system remembers the account, false otherwise.
     */
    public function rememberme()
    {
        // Get session data
        $sessionData = $this->getAccountSessionInstance()->info($this->getAccountID(), $this->getSessionID());

        // Return remember me value
        return $sessionData['rememberme'];
    }

    /**
     * Gets the current mx id.
     *
     * @return    string
     *        The current mx id.
     */
    public function getAuthToken()
    {
        return $this->authToken;
    }

    /**
     * Gets the current logged in account id.
     *
     * @return    integer
     *        The account id.
     */
    public function getAccountID()
    {
        // Check direct access
        if (!empty($this->accountID))
            return $this->accountID;

        // Get token and payload
        $token = $this->getAuthToken();
        $payload = authToken::getPayload($token);

        // Set payload
        $this->accountID = $payload['acc'];
        $this->sessionID = $payload['ssid'];
        $this->personID = $payload['prs'];

        // Get account id
        return $this->accountID;
    }

    /**
     * Gets the account session id.
     *
     * @return    string
     *        The account session id.
     */
    public function getSessionID()
    {
        // Check direct access
        if (!empty($this->sessionID))
            return $this->sessionID;

        // Get token and payload
        $token = $this->getAuthToken();
        $payload = authToken::getPayload($token);

        // Get session id
        return $this->sessionID = $payload['ssid'];
    }

    /**
     * Gets the person id of the logged in account.
     *
     * @return    integer
     *        The person id.
     */
    public function getPersonID()
    {
        // Check direct access
        if (!empty($this->personID))
            return $this->personID;

        // Get token and payload
        $token = $this->getAuthToken();
        $payload = authToken::getPayload($token);

        // Get person id
        return $this->personID = $payload['prs'];
    }

    /**
     * Checks whether this account is locked.
     *
     * @return    boolean
     *        True if locked, false otherwise.
     */
    public function isLocked()
    {
        return ($this->getAccountValue("locked") == true);
    }

    /**
     * Checks whether the account is admin.
     *
     * @return    boolean
     *        True if admin, false otherwise (shared).
     */
    public function isAdmin()
    {
        return ($this->getAccountValue("administrator") == true);
    }

    /**
     * Gets the account title for the logged in account.
     *
     * @return    string
     *        The account display title.
     */
    public function getAccountTitle()
    {
        return $this->getAccountValue("title");
    }

    /**
     * Get the account's username.
     *
     * @param    boolean $emailFallback
     *        Set TRUE to return the connected person's email if the account's username is empty.
     *        It is FALSE by default.
     *
     * @return    mixed
     *        Return the account username.
     *        If account doesn't have username, return the email of the person connected to this account.
     *        If there is no connected account, return NULL.
     */
    public function getUsername($emailFallback = false)
    {
        // Get account username
        $username = $this->getAccountValue("username");

        // If username is empty and email fallback is active,
        // get person's mail
        if (empty($username) && $emailFallback)
            $username = person::getInstance()->getMail();

        // Return username
        return $username;
    }

    /**
     * Gets an account value from the session. If the session is not set yet, updates from the database.
     *
     * @param    string $name
     *        The value name.
     *
     * @return    string
     *        The account value.
     */
    private function getAccountValue($name)
    {
        // Check session existance
        if (!isset($this->accountData[$name]))
            $this->accountData = $this->info();

        return $this->accountData[$name];
    }

    /**
     * Gets the account info.
     *
     * @param    integer $accountID
     *        The account id to get the information for.
     *        Leave empty for the current account.
     *        It is empty by default.
     *
     * @return    array
     *        Returns an array of the account information.
     */
    public function info($accountID = "")
    {
        // Get account info
        $q = new dbQuery("34485211600886", "identity.account");
        $attr = array();
        $attr['id'] = (empty($accountID) ? $this->getAccountID() : $accountID);
        $result = $this->dbc->execute($q, $attr);

        return $this->dbc->fetch($result);
    }

    /**
     * Update account information.
     *
     * @param    string $title
     *        The account title.
     *
     * @return    boolean
     *        True on success, false on failure.
     */
    public function updateInfo($title)
    {
        // Update account info
        $q = new dbQuery("24016942594207", "identity.account");
        $attr = array();
        $attr['aid'] = $this->getAccountID();
        $attr['title'] = $title;
        $result = $this->dbc->execute($q, $attr);

        return $this->dbc->fetch($result);
    }

    /**
     * Update the account's username.
     *
     * @param    string  $username
     *        The new account username.
     *
     * @param    integer $accountID
     *        The account id to update the username.
     *        Leave empty for current account.
     *        It is empty by default.
     *
     * @return    boolean
     *        True on success, false on failure.
     */
    public function updateUsername($username, $accountID = "")
    {
        // Update account username
        $q = new dbQuery("22262988258939", "identity.account");
        $attr = array();
        $attr['aid'] = (empty($accountID) ? $this->getAccountID() : $accountID);
        $attr['username'] = $username;

        return $this->dbc->execute($q, $attr);
    }

    /**
     * Create a new account into the identity system.
     * (Registration)
     * This process will create a person and a connected account.
     *
     * @param    string $email
     *        The person's email.
     *
     * @param    string $firstname
     *        The person's firstname.
     *
     * @param    string $lastname
     *        The person's lastname.
     *
     * @param    string $password
     *        The account password.
     *
     * @return    boolean
     *        The account id created on success, false on failure.
     */
    public function create($email, $firstname = "", $lastname = "", $password = "")
    {
        // Check email
        $email = trim($email);
        if (empty($email))
            return false;

        // Check if there is an account with the same username and change it
        $username = trim(explode("@", $email, 2)[0]);
        $account = $this->getAccountByUsername($username, $includeEmail = false, $fullList = false);
        if (!empty($account))
            $username .= mt_rand() . time();

        // Create new account
        $dbq = new dbQuery("26765983177913", "identity.account");
        $attr = array();
        $attr["firstname"] = trim($firstname);
        $attr["lastname"] = trim($lastname);
        $attr["password"] = (empty($password) ? "NULL" : password_hash($password, PASSWORD_BCRYPT));
        $attr['title'] = trim($firstname . " " . $lastname);
        $attr["email"] = trim($email);
        $attr['username'] = $username;
        $result = $this->dbc->execute($dbq, $attr);
        if (!$result)
            return false;

        // Get account id
        $accountInfo = $this->dbc->fetch($result);

        return $accountInfo['id'];
    }

    /**
     * Get all team accounts.
     *
     * @param    integer $startIndex
     *        The start index for the results.
     *
     * @param    integer $count
     *        The items count.
     *
     * @return    array
     *        An array of all team accounts.
     */
    public function getAllAccounts($startIndex = 0, $count = 50)
    {
        // Normalize limits
        $startIndex = (empty($startIndex) ? 0 : $startIndex);
        $count = (empty($count) ? 50 : $count);

        // Get all accounts
        $dbq = new dbQuery("14794507272004", "identity.account");
        $attr = array();
        $attr['limit'] = $startIndex . ", " . $count;
        $result = $this->dbc->execute($dbq, $attr);

        return $this->dbc->fetch($result, true);
    }

    /**
     * Get the number of accounts in the database.
     *
     * @return    integer
     *        The number of accouns in the identity database.
     */
    public function getAccountsCount()
    {
        // Get accounts count
        $dbq = new dbQuery("25607866701287", "identity.account");
        $result = $this->dbc->execute($dbq);
        $countInfo = $this->dbc->fetch($result);

        return $countInfo['acc_count'];
    }

    /**
     * Get an account (or a list of them) by username.
     *
     * @param    string  $username
     *        The username to search.
     *
     * @param    boolean $includeEmail
     *        If set to true, search for person emails also.
     *        It is FALSE by default.
     *
     * @param    boolean $fullList
     *        If true, return a full list (if available) instead of only the first result.
     *        It is FALSE by default.
     *
     * @return    array
     *        Array of accounts or account information in array.
     */
    public function getAccountByUsername($username, $includeEmail = false, $fullList = false)
    {
        // Get account by username
        $dbq = new dbQuery("18866259871831", "identity.account");
        $attr = array();
        $attr["username"] = trim($username);
        $attr["wmail"] = ($includeEmail ? 1 : 0);
        $result = $this->dbc->execute($dbq, $attr);

        return $this->dbc->fetch($result, $fullList);
    }

    /**
     * Get an accountSession instance for the current account.
     *
     * @return    accountSession
     *        The accountSession instance.
     */
    protected function getAccountSessionInstance()
    {
        return accountSession::getInstance($this->teamName, $this->getSessionID());
    }
}

?>