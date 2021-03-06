<?php

namespace ICAP\Bundle\WebBiblioBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="ICAP\Bundle\WebBiblioBundle\Repository\WebLinkRepository")
 * @ORM\Table(name="icap__webbiblio_weblink", 
 *      indexes={
 *          @ORM\Index(name="username_idx", columns={"username"}),
 *          @ORM\Index(name="published_idx", columns={"published"})
 *      },
 *      uniqueConstraints={
 *          @ORM\UniqueConstraint(name="single_username_url", columns={"url", "username"})
 *      }
 * )
 */
class WebLink
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\Column(type="string", length=255)
     */
    protected $url;

    /**
     * @ORM\Column(type="string", length=126)
     */
    protected $username;

    /**
     * @ORM\Column(type="boolean")
     */
    protected $published;

    /**
     * @ORM\ManyToMany(targetEntity="ICAP\Bundle\WebBiblioBundle\Entity\Tag", inversedBy="webLinks", cascade={"persist"})
     * @ORM\JoinTable(name="icap__webbiblio_weblink_tag",
     *     joinColumns={@ORM\JoinColumn(name="weblink_id", referencedColumnName="id", onDelete="Cascade")},
     *     inverseJoinColumns={@ORM\JoinColumn(name="tag_id", referencedColumnName="id", onDelete="Cascade")}
     * )
     */
    protected $tags;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->tags = new \Doctrine\Common\Collections\ArrayCollection();
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
     * Set url
     *
     * @param string $url
     * @return WebLink
     */
    public function setUrl($url)
    {
        $this->url = $url;
    
        return $this;
    }

    /**
     * Get url
     *
     * @return string 
     */
    public function getUrl()
    {
        return $this->url;
    }

    /**
     * Set username
     *
     * @param string $username
     * @return WebLink
     */
    public function setUsername($username)
    {
        $this->username = $username;
    
        return $this;
    }

    /**
     * Get username
     *
     * @return string 
     */
    public function getUsername()
    {
        return $this->username;
    }

    /**
     * Set published
     *
     * @param boolean $published
     * @return WebLink
     */
    public function setPublished($published)
    {
        $this->published = $published;
    
        return $this;
    }

    /**
     * Get published
     *
     * @return boolean 
     */
    public function getPublished()
    {
        return $this->published;
    }

    /**
     * Add tags
     *
     * @param \ICAP\Bundle\WebBiblioBundle\Entity\Tag $tag
     * @return WebLink
     */
    public function addTag(\ICAP\Bundle\WebBiblioBundle\Entity\Tag $tag)
    {
        if(!$this->hasTag($tag)) {
            $this->tags[] = $tag;
        }
    
        return $this;
    }

    /**
     * Has tags
     *
     * @param \ICAP\Bundle\WebBiblioBundle\Entity\Tag $tag
     * @return boolean
     */
    public function hasTag(\ICAP\Bundle\WebBiblioBundle\Entity\Tag $tag)
    {
        foreach ($this->tags as $current) {
            if($current->getName() == $tag->getName()) {
                return true;
            }
        }
    
        return false;
    }

    /**
     * Remove tags
     *
     * @param \ICAP\Bundle\WebBiblioBundle\Entity\Tag $tags
     */
    public function removeTag(\ICAP\Bundle\WebBiblioBundle\Entity\Tag $tags)
    {
        $this->tags->removeElement($tags);
    }

    /**
     * Remove tags
     *
     */
    public function removeTags()
    {
        $this->tags = array();
    }

    /**
     * Get tags
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getTags()
    {
        return $this->tags;
    }

    /**
     * to string method
     *
     * @return String name
     */
    public function __toString()
    {
        return $this->url;
    }
}