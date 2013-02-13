<?php

namespace ICAP\Bundle\WebBiblioBundle\Service;

use ICAP\Bundle\WebBiblioBundle\Entity\WebLink;
use ICAP\Bundle\WebBiblioBundle\Entity\Tag;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Security\Acl\Domain\ObjectIdentity;
use Symfony\Component\Security\Acl\Domain\UserSecurityIdentity;
use Symfony\Component\Security\Acl\Permission\MaskBuilder;

class Manager
{
    protected $em;
    protected $logger;
    protected $aclProvider;
    protected $securityContext;

    public function __construct($em, $logger, $aclProvider, $securityContext)
    {
        $this->em = $em;
        $this->logger = $logger;
        $this->aclProvider = $aclProvider;
        $this->securityContext = $securityContext;
    }

    protected function getEntityManager()
    {
        return $this->em;
    }

    protected function getWebLinkRepository()
    {
        return $this->getEntityManager()->getRepository('ICAPWebBiblioBundle:WebLink');
    }

    protected function getTagRepository()
    {
        return $this->getEntityManager()->getRepository('ICAPWebBiblioBundle:Tag');
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
    protected function createTag($name)
    {
        // Create and persist a tag
        $tag = new Tag();
        $tag->setName($name);

        $this->getEntityManager()->persist($webLink);

        return $tag;
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
        if (!$webLink) {
            $webLink = $this->createWebLink($username, $url);
        }

        $tags = array();
        foreach ($tagNames as $tagName) {
            $tag = $this->getTagRepository()->findOneBy(array('name' => $tagName));
            if (!$tag) {
                $tag = $this->createTag($tagName);
            }
            $tags[] = $tag;
        }
        $this->add($webLink, $tags);

        $this->getEntityManager()->flush();
    }

    /**
     * Publish or unpublish a webLink by id
     *
     * @param int $id the webLink's id to update
     * @param boolean $published indicates whether the resource is published or not
     * @return void
     */
    public function publishWebLinkById($id, $published)
    {
        $webLink = $this->getWebLinkRepository()->findOne($id);
        $this->verifIsOwnerForWebLink($webLink);
        $this->publishWebLink($weblink, $published);
    }


    /**
     * Publish or unpublish a webLink
     *
     * @param WebLink $webLink a persisted webLink
     * @param boolean $published indicates whether the resource is published or not
     * @return void
     */
    public function publishWebLink($webLink, $published)
    {
        if ($webLink) {
            $this->verifIsOwnerForWebLink($webLink);
            $webLink->setPublished($published);
            $this->getEntityManager()->flush();
        }
    }

    /**
     * Delete a WebLink find by id
     * Do not Delete orphan Tag. See Later for a chron/deamon cleaner
     *
     * @param int $id the webLink's id to remove
     * @return void
     */
    public function removeWebLinkById($webLink)
    {
        $webLink = $this->getWebLinkRepository()->findOne($id);
        $this->verifIsOwnerForWebLink($webLink);
        $this->removeWebLink($webLink);
    }

    /**
     * Delete a WebLink
     * Do not Delete orphan Tag. See Later for a chron/deamon cleaner
     *
     * @param WebLink $webLink the persisted webLink to remove
     * @return void
     */
    public function removeWebLink($webLink)
    {
        if ($webLink) {
            $this->verifIsOwnerForWebLink($webLink);
            $this->getEntityManager()->remove($webLink);
            $this->getEntityManager()->flush();
        }
    }

    /**
     * Update a weblink object :
     *      - create if not present
     *      - update if present :
     *          - update published state
     *          - add tag in persisted object
     *
     * @param WebLink $notPersistedWebLink webLink Object which wasn't 
     * @return void
     */
    public function updateWebLink($notPersistedWebLink)
    {
        $this->logger->debug('begin updateWebLink()');
        $this->logger->debug('url: '.$notPersistedWebLink->getUrl());
        $this->logger->debug('updateWebLink: '.$notPersistedWebLink->getUsername());

        // Split notPersistedTags ans notPersistedWebLink
        $notPersistedTags = $notPersistedWebLink->getTags();
        $notPersistedWebLink->removeTags();

        $webLink = $this->getWebLinkRepository()->findOneBy(array(
                'url' => $notPersistedWebLink->getUrl(), 
                'username' => $notPersistedWebLink->getUsername())
        );

        if (!$webLink) {
            $this->logger->debug('webLink not found');
            $webLink = $notPersistedWebLink;
            $this->getEntityManager()->persist($webLink);
            $this->getEntityManager()->flush();

            $this->addAclToWebLink($webLink);
        } else {
            $this->logger->debug('webLink found');

            $this->verifIsOwnerForWebLink($webLink);
        }

        //Publish state managment
        $webLink->setPublished($notPersistedWebLink->getPublished());

        //Add new tags
        foreach ($notPersistedTags as $notPersistedTag) {
            $this->logger->debug('tagName to add: '.$notPersistedTag->getName());
            $tag = $this->getTagRepository()->findOneBy(array('name' => $notPersistedTag->getName()));
            if(!$tag) {
                $this->logger->debug('tag not found');
                $tag = $notPersistedTag;
                $this->getEntityManager()->persist($tag);
            }else {
                $this->logger->debug('tag found');
            }
            $webLink->addTag($tag);
        }

        $this->getEntityManager()->persist($webLink);
        $this->getEntityManager()->flush();

        $this->logger->debug('end and flush updateWebLink()');
    }

    public function addAclToWebLink($webLink) 
    {
        $this->logger->debug('add acl to weblink');
        // create acl for the webLink
        $objectIdentity = ObjectIdentity::fromDomainObject($webLink);
        $acl = $this->aclProvider->createAcl($objectIdentity);

        // Find logged user identity from security context
        $user = $this->securityContext->getToken()->getUser();
        $securityIdentity = UserSecurityIdentity::fromAccount($user);

        // add owner role
        $acl->insertObjectAce($securityIdentity, MaskBuilder::MASK_OWNER);
        $this->aclProvider->updateAcl($acl);
    }

    public function verifIsOwnerForWebLink($webLink) 
    {
        // check for edit access
        if (false === $this->securityContext->isGranted('EDIT', $webLink))
        {
            throw new AccessDeniedException();
        }else {
            die("acces granted");
        }
    }

    /**
     * Retrieve all WebLinks for a username (publish or not)
     * If username was null retrieve all webLinks. 
     *
     * @param string $username identifier for a user
     * @return WebLink's array
     */
    public function getListQueryBuilder($username) 
    {
        if ($username) {
            return $this->getWebLinkRepository()->getWebLinksQueryBuilderForUsername($username);
        }else {
            return null;
        }
    }

    /**
     * Retrieve index of a weblink in a list of weblinks of a user (publish or not)
     * If username was null retrieve all webLinks.
     *
     * @param string $username identifier for a user
     * @return WebLink's array
     */
    public function getWebLinkIndexInList($username, $webLinkId) 
    {
        if ($username) {
            return $this->getWebLinkRepository()->getWebLinkIndexInList($username, $webLinkId);
        }else {
            return null;
        }
    }

    /**
     * Retrieve all published WebLinks matching params
     *
     * @param array $params a mixed contains array usernames and/or tagNames
     * @return WebLink's array
     */
    public function searchWeblink($params)
    {
        return $this->getWebLinkRepository()->extract($params);
    }

     /**
     * Retrieve all tags matching params
     *
     * @param array $params a mixed array with tagName query
     * @return Tags' array
     */
    public function searchTag($params)
    {
        return $this->getTagRepository()->extract($params);
    }
}