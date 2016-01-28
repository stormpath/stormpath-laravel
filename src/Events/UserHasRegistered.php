<?php

namespace Stormpath\Laravel\Events;

use Stormpath\Resource\Account;

class UserHasRegistered
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
     * @param array $data The form data from the registration request
     */
    public function __construct(Account $account)
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
