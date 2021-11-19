<?php


namespace App\Document;

use Doctrine\ODM\MongoDB\Mapping\Annotations as MongoDB;

/**
 * @MongoDB\Document(collection="fakePlayers", repositoryClass="App\MongoRepository\FakePlayerRepository")
 * @MongoDB\HasLifecycleCallbacks
 */
class FakePlayer
{
    /**
     * @MongoDB\Id
     */
    private $id;

    /**
     * @MongoDB\Field(type="string")
     */
    private string $mainRole;

    /**
     * @MongoDB\Field(type="string")
     */
    private string $secondaryRole;

    /**
     * @MongoDB\Field(type="boolean")
     */
    private string $agressivity;

    /**
     * @MongoDB\Field(type="boolean")
     */
    private string $vulnerable;

    /**
     * @MongoDB\Field(type="boolean")
     */
    private string $teamfighter;


    /**
     * @MongoDB\Field(type="boolean")
     */
    private string $ccer;

    /**
     * @MongoDB\Field(type="boolean")
     */
    private string $moneyPlayer;

    /**
     * @MongoDB\Field(type="string")
     */
    private string $username;

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
     * @return string
     */
    public function getSecondaryRole(): string
    {
        return $this->secondaryRole;
    }

    /**
     * @param string $secondaryRole
     */
    public function setSecondaryRole(string $secondaryRole): void
    {
        $this->secondaryRole = $secondaryRole;
    }

    /**
     * @return string
     */
    public function getAgressivity(): string
    {
        return $this->agressivity;
    }

    /**
     * @param string $agressivity
     */
    public function setAgressivity(string $agressivity): void
    {
        $this->agressivity = $agressivity;
    }

    /**
     * @return string
     */
    public function getVulnerable(): string
    {
        return $this->vulnerable;
    }

    /**
     * @param string $vulnerable
     */
    public function setVulnerable(string $vulnerable): void
    {
        $this->vulnerable = $vulnerable;
    }

    /**
     * @return string
     */
    public function getTeamfighter(): string
    {
        return $this->teamfighter;
    }

    /**
     * @param string $teamfighter
     */
    public function setTeamfighter(string $teamfighter): void
    {
        $this->teamfighter = $teamfighter;
    }

    /**
     * @return string
     */
    public function getCcer(): string
    {
        return $this->ccer;
    }

    /**
     * @param string $ccer
     */
    public function setCcer(string $ccer): void
    {
        $this->ccer = $ccer;
    }

    /**
     * @return string
     */
    public function getMoneyPlayer(): string
    {
        return $this->moneyPlayer;
    }

    /**
     * @param string $moneyPlayer
     */
    public function setMoneyPlayer(string $moneyPlayer): void
    {
        $this->moneyPlayer = $moneyPlayer;
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


    /**
     * @return string
     */
    public function getMainRole(): string
    {
        return $this->mainRole;
    }

    /**
     * @param string $mainRole
     */
    public function setMainRole(string $mainRole): void
    {
        $this->mainRole = $mainRole;
    }
}