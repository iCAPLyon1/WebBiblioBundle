<?php

namespace ICAP\Bundle\WebBiblioBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="ICAP\Bundle\WebBiblioBundle\Repository\TagRepository")
 * @ORM\Table(name="icap__webbiblio_tag")
 */
class Tag
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\Column(type="string", length=100, unique=true)
     */
    protected $name;

    /**
     * @ORM\ManyToMany(targetEntity="ICAP\Bundle\WebBiblioBundle\Entity\WebLink", mappedBy="tags")
     */
    protected $webLinks;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->webLinks = new \Doctrine\Common\Collections\ArrayCollection();
    }

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
     * Set name
     *
     * @param string $name
     * @return Tag
     */
    public function setName($name)
    {
        $this->name = $name;
    
        return $this;
    }

    /**
     * Get name
     *
     * @return string 
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Add webLinks
     *
     * @param \ICAP\Bundle\WebBiblioBundle\Entity\WebLink $webLinks
     * @return Tag
     */
    public function addWebLink(\ICAP\Bundle\WebBiblioBundle\Entity\WebLink $webLinks)
    {
        $this->webLinks[] = $webLinks;
    
        return $this;
    }

    /**
     * Remove webLinks
     *
     * @param \ICAP\Bundle\WebBiblioBundle\Entity\WebLink $webLinks
     */
    public function removeWebLink(\ICAP\Bundle\WebBiblioBundle\Entity\WebLink $webLinks)
    {
        $this->webLinks->removeElement($webLinks);
    }

    /**
     * Get webLinks
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getWebLinks()
    {
        return $this->webLinks;
    }
}