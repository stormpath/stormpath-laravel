<?php

namespace Stormpath\Laravel\Events;


class UserHasRequestedPasswordReset
{
    /**
     * The form data
     *
     * @var array
     */
    private $data = null;

    /**
     * Create a new event instance.
     *
     * @param array $data The form data from the registration request
     */
    public function __construct(array $data)
    {
        $this->data = $data;
    }

    /**
     * Get the form data provided with this event
     *
     * @return array The form data
     */
    public function getData()
    {
        return $this->data;
    }
}
