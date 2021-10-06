<?php

namespace App\Document;

use Doctrine\ODM\MongoDB\Mapping\Annotations as MongoDB;

/**
 * @MongoDB\Document(collection="players", repositoryClass="App\MongoRepository\PlayerRepository")
 * @MongoDB\HasLifecycleCallbacks
 */
class Player
{
    /**
     * @MongoDB\Id
     */
    private $id;
    
    /**
     * @MongoDB\Field(type="string")
     */
    private string $username;
    
//    /**
//     * @MongoDB\Field(type="string")
//     */
//    private string $userHistory;

    /**
     * @MongoDB\Field(type="date")
     */
    private $createdAt;

    /**
     * @MongoDB\Field(type="date")
     */
    private $updatedAt;

    /**
     * @MongoDB\PrePersist
     */
    public function onPrePersist()
    {
        $this->createdAt = time();
    }

    /**
     * @MongoDB\PreUpdate
     */
    public function onPreUpdate()
    {
//        $date = new DateTime();
        $this->updatedAt = time();
    }

    
    public function getId()
    {
        return $this->id;
    }

    
    public function setId($id): void
    {
        $this->id = $id;
    }

    /**
     * @return string
     */
    public function getUsername(): string
    {
        return $this->username;
    }

    /**
     * @param string $username
     */
    public function setUsername(string $username): void
    {
        $this->username = $username;
    }

//    /**
//     * @return string
//     */
//    public function getUserHistory(): string
//    {
//        return $this->userHistory;
//    }

    /**
//     * @param string $userHistory
//     */
//    public function setUserHistory(string $userHistory): void
//    {
//        $this->userHistory = $userHistory;
//    }

    /**
     * @return mixed
     */
    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    /**
     * @param mixed $createdAt
     */
    public function setCreatedAt($createdAt): void
    {
        $this->createdAt = $createdAt;
    }

    /**
     * @return mixed
     */
    public function getUpdatedAt()
    {
        return $this->updatedAt;
    }

    /**
     * @param mixed $updatedAt
     */
    public function setUpdatedAt($updatedAt): void
    {
        $this->updatedAt = $updatedAt;
    }

}