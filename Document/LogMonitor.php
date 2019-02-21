<?php

namespace FAC\LogBundle\Document;

use Schema\Document;
use Symfony\Component\Validator\Constraints as Assert;
use Doctrine\ODM\MongoDB\Mapping\Annotations as MongoDB;
use Doctrine\ODM\MongoDB\Mapping\Annotations\ReferenceMany as ReferenceMany;
use Doctrine\ODM\MongoDB\Mapping\Annotations\ReferenceOne as ReferenceOne;
use Doctrine\ODM\MongoDB\Mapping\Annotations\EmbedMany as EmbedMany;
use Doctrine\ODM\MongoDB\Mapping\Annotations\EmbedOne as EmbedOne;
use Symfony\Component\Validator\Constraints\Date;

/**
 * @MongoDB\Document(repositoryClass="FAC\LogBundle\Repository\LogMonitorRepository")
 */
class LogMonitor extends Document {

    const LOG_LEVEL_SYS   = 0;
    const LOG_LEVEL_TRACK = 1;

    const LOG_CHANNEL_UNKNOWN = 0;
    const LOG_CHANNEL_QUERY   = 1;
    const LOG_CHANNEL_REQUEST = 2;
    const LOG_CHANNEL_WARNING = 3;
    const LOG_CHANNEL_ERROR   = 4;
    const LOG_CHANNEL_SUCCESS = 5;

    const LOG_MAX_COUNT = 10;

    /**
     * @MongoDB\Id
     */
    protected $id;

    /**
     * @MongoDB\Field(type="int",name="count")
     */
    private $count;

    /**
     * @MongoDB\Field(type="int",name="channel")
     */
    private $channel;

    /**
     * @MongoDB\Field(type="int",name="level")
     */
    private $level;

    /**
     * @MongoDB\Field(type="string",name="message")
     */
    private $message;

    /**
     * @MongoDB\Field(type="string",name="exception_message")
     */
    private $exceptionMessage;

    /**
     * @MongoDB\Field(type="string",name="backtrace")
     */
    private $backtrace;

    /**
     * @MongoDB\Field(type="string",name="referral")
     */
    private $referral;

    /**
     * @MongoDB\Field(type="string",name="url")
     */
    private $url;

    /**
     * @MongoDB\Field(type="date",name="first_happened")
     */
    private $firstHappened;

    /**
     * @MongoDB\Field(type="date",name="last_happened")
     */
    private $lastHappened;

    /**
     * @MongoDB\Field(type="string",name="file")
     */
    private $file;

    /**
     * @MongoDB\Field(type="string",name="method")
     */
    private $method;

    /**
     * @MongoDB\Field(type="string",name="line")
     */
    private $line;

    /**
     * @MongoDB\Field(type="string",name="hash")
     */
    private $hash = null;

    ################################################# SERIALIZER FUNCTIONS

    /**
     * Returns the array of fields to serialize in entity administration view.
     * @return array
     */
    public function adminSerializer()
    {
        $view_vars = $this->viewSerializer();

        $admin_vars = array();

        return array_merge($view_vars, $admin_vars);
    }

    /**
     * Returns the array of fields to serialize in entity view.
     * @return array
     */
    public function viewSerializer()
    {
        $list_vars = $this->listSerializer();

        $view_vars = array(
        );

        return array_merge($list_vars, $view_vars);
    }

    /**
     * Returns the array of fields to serialize in a list of this entity.
     * @return array
     */
    public function listSerializer()
    {
        $list_vars = array(
        );
        return $list_vars;
    }

    /**
     * Returns the hash code unique identifier of the entity.
     * @return string
     */
    public function hashCode()
    {
        $str = "";
        $str .= (is_null($this->channel)?"":strtolower($this->channel));
        $str .= (is_null($this->method)?"":strtolower($this->method));
        $str .= (is_null($this->level)?"":strtolower($this->level));
        $str .= (is_null($this->url)?"":strtolower($this->url));
        return md5($str);
    }

    ################################################# SERIALIZED FUNCTIONS




    ################################################# GETTERS AND SETTERS FUNCTIONS

    /**
     * Get id
     *
     * @return MongoDB\Id $id
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set count
     *
     * @param int $count
     * @return $this
     */
    public function setCount($count)
    {
        $this->count = $count;
        return $this;
    }

    /**
     * Get count
     *
     * @return int $count
     */
    public function getCount()
    {
        return $this->count;
    }

    /**
     * Set channel
     *
     * @param int $channel
     * @return $this
     */
    public function setChannel($channel)
    {
        $this->channel = $channel;
        return $this;
    }

    /**
     * Get channel
     *
     * @return int $channel
     */
    public function getChannel()
    {
        return $this->channel;
    }

    /**
     * Set level
     *
     * @param int $level
     * @return $this
     */
    public function setLevel($level)
    {
        $this->level = $level;
        $this->hash = $this->hashCode();
        return $this;
    }

    /**
     * Get level
     *
     * @return int $level
     */
    public function getLevel()
    {
        return $this->level;
    }

    /**
     * Set message
     *
     * @param string $message
     * @return $this
     */
    public function setMessage($message)
    {
        $this->message = $message;
        return $this;
    }

    /**
     * Get message
     *
     * @return string $message
     */
    public function getMessage()
    {
        return $this->message;
    }

    /**
     * Set backtrace
     *
     * @param string $backtrace
     * @return $this
     */
    public function setBacktrace($backtrace)
    {
        $this->backtrace = $backtrace;
        return $this;
    }

    /**
     * Get backtrace
     *
     * @return string $backtrace
     */
    public function getBacktrace()
    {
        return $this->backtrace;
    }

    /**
     * Set referral
     *
     * @param string $referral
     * @return $this
     */
    public function setReferral($referral)
    {
        $this->referral = $referral;
        return $this;
    }

    /**
     * Get referral
     *
     * @return string $referral
     */
    public function getReferral()
    {
        return $this->referral;
    }

    /**
     * Set url
     *
     * @param string $url
     * @return $this
     */
    public function setUrl($url)
    {
        $this->url = $url;
        $this->hash = $this->hashCode();
        return $this;
    }

    /**
     * Get url
     *
     * @return string $url
     */
    public function getUrl()
    {
        return $this->url;
    }

    /**
     * Set firstHappened
     *
     * @param string $firstHappened
     * @return $this
     */
    public function setFirstHappened($firstHappened)
    {
        $this->firstHappened = $firstHappened;
        return $this;
    }

    /**
     * Get firstHappened
     *
     * @return date $firstHappened
     */
    public function getFirstHappened()
    {
        return $this->firstHappened;
    }

    /**
     * Set lastHappened
     *
     * @param string $lastHappened
     * @return $this
     */
    public function setLastHappened($lastHappened)
    {
        $this->lastHappened = $lastHappened;
        return $this;
    }

    /**
     * Get lastHappened
     *
     * @return date $lastHappened
     */
    public function getLastHappened()
    {
        return $this->lastHappened;
    }

    /**
     * Set file
     *
     * @param string $file
     * @return $this
     */
    public function setFile($file)
    {
        $this->file = $file;
        return $this;
    }

    /**
     * Get file
     *
     * @return string $file
     */
    public function getFile()
    {
        return $this->file;
    }

    /**
     * Set method
     *
     * @param string $method
     * @return $this
     */
    public function setMethod($method)
    {
        $this->method = $method;
        return $this;
    }

    /**
     * Get method
     *
     * @return string $method
     */
    public function getMethod()
    {
        return $this->method;
    }

    /**
     * Set line
     *
     * @param string $line
     * @return $this
     */
    public function setLine($line)
    {
        $this->line = $line;
        return $this;
    }

    /**
     * Get line
     *
     * @return string $line
     */
    public function getLine()
    {
        return $this->line;
    }

    /**
     * Set hash
     *
     * @param string $hash
     * @return $this
     */
    public function setHash($hash)
    {
        $this->hash = $hash;
        return $this;
    }

    /**
     * Get hash
     *
     * @return string $hash
     */
    public function getHash()
    {
        return $this->hash;
    }

    /**
     * @return mixed
     */
    public function getExceptionMessage()
    {
        return $this->exceptionMessage;
    }

    /**
     * @param mixed $exceptionMessage
     */
    public function setExceptionMessage($exceptionMessage)
    {
        $this->exceptionMessage = $exceptionMessage;
    }


}
