<?php

namespace App\Jobs;

use App\User;
use Exception;

abstract class BaseTask
{
    public $params;
    public $requiredParams = null;

    /**
     * BaseTask constructor.
     *
     * @param null $params
     *
     * @throws Exception
     */
    public function __construct($params = null)
    {
        $this->params = $params;
        $this->setup();
        $this->verifyRequiredParams();
    }

    public function setup()
    {
    }

    /**
     * @throws Exception
     *
     * @return bool
     */
    public function verifyRequiredParams()
    {
        if ($this->requiredParams != null) {
            if ($this->params != null) {
                if (is_array($this->requiredParams) && is_array($this->params)) {
                    $diff = collect($this->requiredParams)->diff(array_keys($this->params));
                    if ($diff->count() == 0) {
                        return true;
                    } else {
                        throw new Exception('One or more parameters missing from task. Diff: '.json_encode($diff));
                    }
                } else {
                    return true;
                }
            }

            throw new Exception('Error verifying that required parameters in task exist. Task requires: '.json_encode($this->requiredParams));
        }

        return true;
    }

    /**
     * Add required params to the task, verify them afterwards.
     * Possible cases:
     * 1a. $this->requiredParams is null
     * 2a. $this->requiredParams is true
     * 3a. $this->requiredParams is an array
     * 1b. $params is true
     * 2b. $params is a single parameter
     * 3b. $params is an array of parameters.
     *
     * @param $params
     *
     * @throws Exception
     *
     * @return bool
     */
    public function addRequiredParams($params)
    {
        if ($params !== true) {
            if (!is_array($params)) {
                $params = [$params];
            }
            if (!is_array($this->requiredParams)) {
                if ($this->requiredParams == true || $this->requiredParams == null) {
                    $this->requiredParams = [];
                } else {
                    $this->requiredParams = [$this->requiredParams];
                }
            }
            $this->requiredParams = array_merge($this->requiredParams, $params);
        }

        if ($this->requiredParams == null) {
            $this->requiredParams = $params;
        }

        return $this->verifyRequiredParams();
    }

    /**
     * @param User|null $user
     */
    public function run(User $user = null)
    {
        $this->execute($user);
        $this->tearDown();
    }

    public function expectsUser(User $user)
    {
        if ($user && $user instanceof User) {
            return $user;
        } else {
            throw new Exception('User not passed to task.');
        }
    }

    /**
     * @param User|null $user
     *
     * @return
     */
    abstract public function execute(User $user = null);

    public function tearDown()
    {
    }

    public function getName()
    {
        $className = get_class($this);

        return substr($className, strrpos($className, '\\') + 1);
    }
}
