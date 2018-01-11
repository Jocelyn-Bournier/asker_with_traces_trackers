<?php

namespace CRT\ToolBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Corporate
 *
 * @ORM\Table()
 * @ORM\Entity(repositoryClass="CRT\ToolBundle\Entity\CorporateRepository")
 */
class Corporate
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
     * @var string
     *
     * @ORM\Column(name="label", type="string", length=50)
     */
    private $label;

    /**
     * @var string
     *
     * @ORM\Column(name="ldapName", type="string", length=50)
     */
    private $ldapName;


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
     * Set label
     *
     * @param string $label
     * @return Corporate
     */
    public function setLabel($label)
    {
        $this->label = $label;

        return $this;
    }

    /**
     * Get label
     *
     * @return string 
     */
    public function getLabel()
    {
        return $this->label;
    }

    /**
     * Set ldapName
     *
     * @param string $ldapName
     * @return Corporate
     */
    public function setLdapName($ldapName)
    {
        $this->ldapName = $ldapName;

        return $this;
    }

    /**
     * Get ldapName
     *
     * @return string 
     */
    public function getLdapName()
    {
        return $this->ldapName;
    }
}
