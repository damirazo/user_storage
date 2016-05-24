<?php

// Maximum user objects
define('MAX_ID', 100);
// Directory to save user files
define('USER_STORAGE_DIR', 'users');


/**
 * Class User
 * User entity object
 */
class User
{

    /** @var int User identifier */
    protected $id;
    /** @var string User name */
    protected $name;
    /** @var float User balance */
    protected $balance;


    /**
     * User constructor.
     */
    public function __construct()
    {
        $this->id = null;
        $this->name = '';
        $this->balance = 0;
    }

    /**
     * New user creation
     *
     * @param string $name User name
     * @param float $balance User balance
     */
    public function create($name, $balance)
    {
        $this->validateName($name);
        $this->validateBalance($balance);

        $this->id = $this->generateId();
        $this->name = $name;
        $this->balance = (float)$balance;

        $this->dumpData();
    }

    /**
     * Load user params to object
     *
     * @param int $id User identifier
     */
    public function load($id)
    {
        $this->loadData($id);
    }

    /**
     * Dump user params to storage
     */
    public function save()
    {
        if ($this->initialized()) {
            $this->dumpData();
        }
    }

    /**
     * Increased user balance
     *
     * @param float $amount Increased amount
     */
    public function increaseBalance($amount)
    {
        if ($this->initialized()) {
            $this->balance += $amount;
        }
    }

    /**
     * Decreased user balance
     *
     * @param float $amount Decreased amount
     */
    public function decreaseBalance($amount)
    {
        if ($this->initialized()) {
            $this->balance -= $amount;
            // Balance validation
            $this->validateBalance($this->balance);
        }
    }

    /**
     * Returns user identifier
     *
     * @return int User identifier
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Returns user name
     *
     * @return string User name
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Returns user balance
     *
     * @return float User balance
     */
    public function getBalance()
    {
        return $this->balance;
    }

    public function setName($name)
    {
        $this->validateName($name);
        $this->name = $name;
    }

    /**
     * Check if object is initialized
     *
     * @return bool true if object initialized
     */
    protected function initialized()
    {
        if (is_null($this->id)) {
            throw new RuntimeException('User object can not initialized!');
        }

        return true;
    }

    /**
     * User id generation
     *
     * @return int Generated user id
     *
     * @throws RuntimeException if generated id alreasy existed in storage
     */
    protected function generateId()
    {
        $id = rand(1, MAX_ID);

        if (file_exists($this->fileName($id))) {
            throw new RuntimeException('User with id = ' . $id . ' already existed!');
        }

        return $id;
    }

    /**
     * Contruct path to storage file based on user id
     *
     * @param int $id User identifier
     * @return string Path to storage file
     */
    protected function fileName($id)
    {
        return USER_STORAGE_DIR . '/' . $id . '.txt';
    }

    /**
     * User name validation
     *
     * @param string $name User name
     *
     * @throws InvalidArgumentException if user name is empty
     * @throws LengthException if user name length more that 100 characters
     */
    protected function validateName($name)
    {
        if (!$name) {
            throw new InvalidArgumentException('Argument `name` can not be empty!');
        }

        if (strlen($name) > 100) {
            throw new LengthException('Argument `name` can not be more 100 characters!');
        }
    }

    /**
     * User balance validation
     *
     * @param float $balance User balance amount
     *
     * @throws InvalidArgumentException if balance less that 0
     */
    protected function validateBalance($balance)
    {
        if ($balance < 0) {
            throw new InvalidArgumentException('Argument `balance` can not be less that 0');
        }
    }

    /**
     * User data to string serialization
     *
     * @return string User name and balance, joined from "\n" symbol
     */
    protected function serialize()
    {
        return implode(PHP_EOL, array($this->name, strval($this->balance)));
    }

    /**
     * Saved user data to storage
     */
    protected function dumpData()
    {
        $actualPath = $this->fileName($this->id);
        file_put_contents($actualPath, $this->serialize(), LOCK_EX);
    }

    /**
     * Load user data from storage
     *
     * @param int $id User identifier
     */
    protected function loadData($id)
    {
        $actualPath = $this->fileName($id);
        if (!file_exists($actualPath)) {
            throw new RuntimeException('User with id = ' . $id . ' does not existed!');
        }

        $data = file_get_contents($actualPath);
        $params = explode(PHP_EOL, $data);

        $this->id = $id;
        $this->name = $params[0];
        $this->balance = floatval($params[1]);
    }

}
