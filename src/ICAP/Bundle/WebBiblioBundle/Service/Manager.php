<?php

namespace ICAP\Bundle\WebBiblioBundle\Service;

use ICAP\Bundle\WebBiblioBundle\Entity\WebLink;
use ICAP\Bundle\WebBiblioBundle\Entity\Tag;

class Manager
{
    protected $em;

    public function __construct($em)
    {
        $this->$em = $em;
    }

    protected function getEntityManager()
    {
        return $this->$em;
    }

    protected function getWebLinkRepository()
    {
        return $this->getEntityManager()->getRepository('WebLink');
    }

    protected function getTagRepository()
    {
        return $this->getEntityManager()->getRepository('Tag');
    }

    /**
     * Create a WebLink from properties
     * @param string $username a user identifier
     * @param string $url an url
     * @return WebLink
     */
    protected function createWebLink($username, $url)
    {
        // Create and persist a webLink
        $webLink = new WebLink();
        $webLink->setUsername($username);
        $webLink->setUrl($url);

        $this->getEntityManager()->persist($webLink);

        return $webLink;
    }

    /**
     * Create a Tag from properties
     * @param string $name the tag's name
     * @return Tag 
     */
    protected function createTag()
    {
        // Create and persist a tag
        $tag = new Tag();
        $tag->setName($name);

        $this->getEntityManager()->persist($webLink);
        return $tag;
    }

    /**
     * Add a tag to a webLink
     * @param Tag $tag Tag to add to the WebLink
     * @param WebLink $webLink the webLink to update
     * @return void
     */
    protected function addTagToWebLink($tag, $webLink) {
        $webLink->addTag($tag);
        $tag->addWebLink($webLink);
    }

    /**
     * Take a username, an url, a publish state and a tagNames
     * Search a WebLink by url and username or create it if not exist
     * For each tagName in tagNames
     * Search a Tag by tagName or create it if not exist
     * Add all Tags to the WebLink
     *
     * @param string $username identifier for a user
     * @param string $url webLink's url
     * @param boolean $visible indicates whether the resource is published or not
     * @param array $tagNames tag's names to add to the WebLink
     * @return void
     */
    public function addByParams($username, $url, $published, $tagNames) 
    {
        $webLink = $this->getWebLinkRepository()->findOneBy(array('url' => $url, 'username' => $username));
        if(!$webLink) {
            $webLink = $this->createWebLink($username, $url);
        }

        $tags = array();
        foreach ($tagNames as $tagName) {
            $tag = $this->getTagRepository()->findOneBy(array('name' => $tagName));
            if(!$tag) {
                $tag = $this->createTag($tagName);
            }
            $tags[] = $tag;
        }
        $this->add($webLink, $tags);
    }

    /**
     * Take a WebLink
     * For each tag in tags
     * Add the Tag to the WebLink
     *
     * @param WebLink $webLink the webLink to update
     * @param array $tags Tags to add to the WebLink
     * @return void
     */
    public function add($webLink, $tags) 
    {
        foreach ($tags as $tag) {
            $this->addTagToWebLink($tag, $webLink);
        }
        $this->getEntityManager()->flush();
    }

    /**
     * Delete a WebLink find by id
     * Do not Delete orphan Tag. See Later for a chron/deamon cleaner
     *
     * @param int $id the webLink's id to remove
     * @return void
     */
    public function removeById($webLink)
    {
        $webLink = $this->getWebLinkRepository()->findOne($id);
        $this->remove($webLink);
    }

    /**
     * Delete a WebLink
     * Do not Delete orphan Tag. See Later for a chron/deamon cleaner
     *
     * @param WebLink $webLink the webLink to remove
     * @return void
     */
    public function remove($webLink)
    {
        $this->getEntityManager()->remove($webLink);
        $this->getEntityManager()->flush();
    }

    /**
     * Publish or unpublish a webLink by id
     *
     * @param int $id the webLink's id to update
     * @param boolean $visible indicates whether the resource is published or not
     * @return void
     */
    public function publishById($id, $visible)
    {
        $webLink = $this->getWebLinkRepository()->findOne($id);
        $this->publish($webLink);
    }

    /**
     * Publish or unpublish a webLink
     *
     * @param WebLink $webLink the webLink to update
     * @param boolean $visible indicates whether the resource is published or not
     * @return void
     */
    public function publish($webLink, $visible)
    {
        $webLink->setPublished($visible);
        $this->getEntityManager()->flush();
    }

    /**
     * Retrieve all WebLinks for a username (publish or not)
     *
     * @param string $username identifier for a user
     * @return WebLink's array
     */
    public function getList($username) 
    {
        return $this->getWebLinkRepository()->findBy(array('username' => $username));
    }

    /**
     * Retrieve all published WebLinks matching params
     *
     * @param array $params a mixed contains array usernames and/or tagNames
     * @return WebLink's array
     */
    public function search($params)
    {
        return $this->getWebLinkRepository()->customSearch($params);
    }
}