<?php

namespace Stormpath\Laravel\Events;

use Stormpath\Resource\Account;

class UserIsLoggingOut
{
    /**
     * The account property
     *
     * @var Account
     */
    private $account = null;

    /**
     * Create a new event instance.
     *
     * For reasons that I don't fully understand yet, it is not possible to get
     * the Account object in the `LoginController::getLogout` method, at least
     * in the test methods. So I have temporarily added a default value for the
     * `$account` parameter.
     *
     * @param array $data The form data from the log in request
     */
    public function __construct(Account $account = null)
    {
        $this->account = $account;
    }

    /**
     * Get the account associated with this event
     *
     * @return Account The account associated with this event
     */
    public function getAccount()
    {
        return $this->account;
    }
}
