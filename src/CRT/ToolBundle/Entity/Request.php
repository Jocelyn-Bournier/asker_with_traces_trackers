<?php

namespace CRT\ToolBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Request
 *
 * @ORM\Table(name="request",uniqueConstraints={@ORM\UniqueConstraint(name="own_uni_token_index", columns={"token"})})
 * @ORM\Entity(repositoryClass="CRT\ToolBundle\Entity\RequestRepository")
 */
class Request
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="createDate", type="datetime")
     */
    private $createDate;

    /**
     * @var string
     *
     * @ORM\Column(name="token", type="string", length=40)
     */
    private $token;

    /**
     * @var string
     *
     * @ORM\Column(name="firstName", type="string", length=50)
     */
    private $firstName;

    /**
     * @var string
     *
     * @ORM\Column(name="lastName", type="string", length=50)
     */
    private $lastName;

    /**
     * @var string
     *
     * @ORM\Column(name="email", type="string", length=100)
     */
    private $email;

    /**
     * @var string
     *
     * @ORM\Column(name="title", type="string", length=50, nullable=true)
     */
    private $title;

    /**
     * @var string
     *
     * @ORM\Column(name="deskPhone", type="string", length=20, nullable=true)
     */
    private $deskPhone;

    /**
     * @var string
     *
     * @ORM\Column(name="mobiPhone", type="string", length=20, nullable=true)
     */
    private $mobiPhone;

    /**
     * @ORM\ManyToOne(targetEntity="CRT\ToolBundle\Entity\Academy")
     * @ORM\JoinColumn(nullable=false)
     */
    private $academy;

    /**
     * @ORM\ManyToOne(targetEntity="CRT\ToolBundle\Entity\Corporate")
     * @ORM\JoinColumn(nullable=false)
     */
    private $corporate;

    /**
     * @ORM\ManyToOne(targetEntity="CRT\ToolBundle\Entity\RequestStatus")
     * @ORM\JoinColumn(nullable=false)
     */
    private $status;

    /**
     * Get id
     *
     * @return integer 
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set createDate
     *
     * @param \DateTime $createDate
     * @return Request
     */
    public function setCreateDate($createDate)
    {
        $this->createDate = $createDate;

        return $this;
    }

    /**
     * Get createDate
     *
     * @return \DateTime 
     */
    public function getCreateDate()
    {
        return $this->createDate;
    }

    /**
     * Set token
     *
     * @param string $token
     * @return Request
     */
    public function setToken($token)
    {
        $this->token = $token;

        return $this;
    }

    /**
     * Get token
     *
     * @return string 
     */
    public function getToken()
    {
        return $this->token;
    }

    /**
     * Set firstName
     *
     * @param string $firstName
     * @return Request
     */
    public function setFirstName($firstName)
    {
        $this->firstName = $firstName;

        return $this;
    }

    /**
     * Get firstName
     *
     * @return string 
     */
    public function getFirstName()
    {
        return $this->firstName;
    }

    /**
     * Set lastName
     *
     * @param string $lastName
     * @return Request
     */
    public function setLastName($lastName)
    {
        $this->lastName = $lastName;

        return $this;
    }

    /**
     * Get lastName
     *
     * @return string 
     */
    public function getLastName()
    {
        return $this->lastName;
    }

    /**
     * Set email
     *
     * @param string $email
     * @return Request
     */
    public function setEmail($email)
    {
        $this->email = $email;

        return $this;
    }

    /**
     * Get email
     *
     * @return string 
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * Set title
     *
     * @param string $title
     * @return Request
     */
    public function setTitle($title)
    {
        $this->title = $title;

        return $this;
    }

    /**
     * Get title
     *
     * @return string 
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * Set deskPhone
     *
     * @param string $deskPhone
     * @return Request
     */
    public function setDeskPhone($deskPhone)
    {
        $this->deskPhone = $deskPhone;

        return $this;
    }

    /**
     * Get deskPhone
     *
     * @return string 
     */
    public function getDeskPhone()
    {
        return $this->deskPhone;
    }

    /**
     * Set mobiPhone
     *
     * @param string $mobiPhone
     * @return Request
     */
    public function setMobiPhone($mobiPhone)
    {
        $this->mobiPhone = $mobiPhone;

        return $this;
    }

    /**
     * Get mobiPhone
     *
     * @return string 
     */
    public function getMobiPhone()
    {
        return $this->mobiPhone;
    }
    /**
    * Get corporate.
    *roro
    * @return corporate.
    */
    public function getCorporate()
    {
        return $this->corporate;
    }
    /**
    * Set corporate.
    *
    * @param corporate the value to set.
    */
    public function setCorporate($corporate)
    {
        $this->corporate = $corporate;
    }
    /**
    * Get academy.
    *roro
    * @return academy.
    */
    public function getAcademy()
    {
        return $this->academy;
    }
    /**
    * Set academy.
    *
    * @param academy the value to set.
    */
    public function setAcademy($academy)
    {
        $this->academy = $academy;
    }
    /**
    * Get status.
    *roro
    * @return status.
    */
    public function getStatus()
    {
        return $this->status;
    }
    /**
    * Set status.
    *
    * @param status the value to set.
    */
    public function setStatus($status)
    {
        $this->status = $status;
    }

    public function __toString()
    {
       return "Je suis la requete : " . $this->getToken(); 
    }
}
