<?php
namespace WebAuth;
/**
 * Class Identity
 * @package WebAuth
 */
class Identity
{
    /**
     * @var string
     */
    protected $displayName;
    /**
     * @var string
     */
    protected $email;
    /**
     * @var string
     */
    protected $firstName;
    /**
     * @var int
     */
    protected $id;
    /**
     * @var string
     */
    protected $lastName;

    /**
     * Retrieve $displayName
     *
     * @return string
     */
    public function getDisplayName()
    {
        return $this->displayName;
    }

    /**
     * Retrieve $email
     *
     * @return string
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * Retrieve $firstName
     *
     * @return string
     */
    public function getFirstName()
    {
        return $this->firstName;
    }

    /**
     * Retrieve $id
     *
     * @return string
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Retrieve $lastName
     *
     * @return string
     */
    public function getLastName()
    {
        return $this->lastName;
    }

    /**
     * Set $displayName
     *
     * @param string $displayName
     * @return Identity
     */
    public function setDisplayName($displayName)
    {
        $this->displayName = $displayName;

        return $this;
    }

    /**
     * Set $email
     *
     * @param string $email
     * @return Identity
     */
    public function setEmail($email)
    {
        $this->email = $email;

        return $this;
    }

    /**
     * Set $firstName
     *
     * @param string $firstName
     * @return Identity
     */
    public function setFirstName($firstName)
    {
        $this->firstName = $firstName;

        return $this;
    }

    /**
     * Set $id
     *
     * @param string $id
     * @return Identity
     */
    public function setId($id)
    {
        $this->id = $id;

        return $this;
    }

    /**
     * Set $lastName
     *
     * @param string $lastName
     * @return Identity
     */
    public function setLastName($lastName)
    {
        $this->lastName = $lastName;

        return $this;
    }
}