<?php

namespace App\Document;

use Doctrine\ODM\MongoDB\Mapping\Annotations as MongoDB;

/**
 * @MongoDB\Document(collection="player", repositoryClass="App\MongoRepository\PlayerRepository")
 * @MongoDB\HasLifecycleCallbacks
 */
class Player
{
    /**
     * @MongoDB\Id
     */
    private int $id;
    
    /**
     * @MongoDB\String
     */
    private string $username;
    
    /**
     * @MongoDB\String 
     */
    private $json;

}