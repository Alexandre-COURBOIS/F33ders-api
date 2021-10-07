<?php

namespace App\Document;

use Doctrine\ODM\MongoDB\Mapping\Annotations as MongoDB;

/**
 * @MongoDB\Document(collection="champions", repositoryClass="App\MongoRepository\ChampionRepository")
 * @MongoDB\HasLifecycleCallbacks
 */
class Champion
{
    /**
     * @MongoDB\Id
     */
    private $id;

    /**
     * @MongoDB\Field(type="string")
     */
    private $championId;

    /**
     * @MongoDB\Field(type="int")
     */
    private $key;

    /**
     * @MongoDB\Field(type="string")
     */
    private $name;

    /**
     * @MongoDB\Field(type="string")
     */
    private $resume;

    /**
     * @MongoDB\Field(type="string")
     */
    private $description;

    /**
     * @MongoDB\Field(type="int")
     */
    private $attack;

    /**
     * @MongoDB\Field(type="int")
     */
    private $defense;

    /**
     * @MongoDB\Field(type="int")
     */
    private $magic;

    /**
     * @MongoDB\Field(type="int")
     */
    private $difficulty;

    /**
     * @MongoDB\Field(type="collection")
     */
    private $types;

    /**
     * @MongoDB\Field(type="collection")
     */
    private $image;

    /**
     * @MongoDB\Field(type="string")
     */
    private $imageUrl;

    /**
     * @MongoDB\Field(type="date")
     */
    private $createdAt;

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
        $this->updatedAt = time();
    }

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param mixed $id
     */
    public function setId($id): void
    {
        $this->id = $id;
    }

    /**
     * @return mixed
     */
    public function getChampionId()
    {
        return $this->championId;
    }

    /**
     * @param mixed $championId
     */
    public function setChampionId($championId): void
    {
        $this->championId = $championId;
    }

    /**
     * @return mixed
     */
    public function getKey()
    {
        return $this->key;
    }

    /**
     * @param mixed $key
     */
    public function setKey($key): void
    {
        $this->key = $key;
    }

    /**
     * @return mixed
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param mixed $name
     */
    public function setName($name): void
    {
        $this->name = $name;
    }

    /**
     * @return mixed
     */
    public function getResume()
    {
        return $this->resume;
    }

    /**
     * @param mixed $resume
     */
    public function setResume($resume): void
    {
        $this->resume = $resume;
    }

    /**
     * @return mixed
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * @param mixed $description
     */
    public function setDescription($description): void
    {
        $this->description = $description;
    }

    /**
     * @return mixed
     */
    public function getAttack()
    {
        return $this->attack;
    }

    /**
     * @param mixed $attack
     */
    public function setAttack($attack): void
    {
        $this->attack = $attack;
    }

    /**
     * @return mixed
     */
    public function getDefense()
    {
        return $this->defense;
    }

    /**
     * @param mixed $defense
     */
    public function setDefense($defense): void
    {
        $this->defense = $defense;
    }

    /**
     * @return mixed
     */
    public function getMagic()
    {
        return $this->magic;
    }

    /**
     * @param mixed $magic
     */
    public function setMagic($magic): void
    {
        $this->magic = $magic;
    }

    /**
     * @return mixed
     */
    public function getDifficulty()
    {
        return $this->difficulty;
    }

    /**
     * @param mixed $difficulty
     */
    public function setDifficulty($difficulty): void
    {
        $this->difficulty = $difficulty;
    }

    /**
     * @return mixed
     */
    public function getTypes()
    {
        return $this->types;
    }

    /**
     * @param mixed $types
     */
    public function setTypes($types): void
    {
        $this->types = $types;
    }

    /**
     * @return mixed
     */
    public function getImage()
    {
        return $this->image;
    }

    /**
     * @param mixed $image
     */
    public function setImage($image): void
    {
        $this->image = $image;
    }

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

    /**
     * @MongoDB\Field(type="date")
     */
    private $updatedAt;

    /**
     * @return mixed
     */
    public function getImageUrl()
    {
        return $this->imageUrl;
    }

    /**
     * @param mixed $imageUrl
     */
    public function setImageUrl($imageUrl): void
    {
        $this->imageUrl = "https://ddragon.leagueoflegends.com/cdn/11.19.1/img/champion/".$imageUrl;
    }

}