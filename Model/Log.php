<?php

namespace LogBundle\Model;

use DateTime;
use JMS\Serializer\Annotation as JMS;

class Log {

    const LOG_SYS = 0;
    const LOG_TRACK = 1;

    /**
     * @var string $timestamp
     */
    private $timestamp;

    /**
     * @var string $type
     */
    private $type;

    /**
     * @var integer $level
     */
    private $level;

    /**
     * @var string $message
     */
    private $message;

    /**
     * @var string $method
     */
    private $method;

    /**
     * @var string $url
     */
    private $url;

    /**
     * @var DateTime $when
     */
    private $when;

    /**
     * @var string $ip
     */
    private $ip;

    /**
     * @var string $userAgent
     */
    private $userAgent;

    /**
     * @var string $idUser
     */
    private $idUser;


    ################################################# SERIALIZED FUNCTIONS

    public function listSerializer() {
        $export_vars = array(
            'when'          => $this->serializedWhen(),
            'type'          => $this->serializedType(),
            'level'         => $this->serializedLevel(),
            'message'       => $this->serializedMessage(),
            'method'        => $this->serializedMethod(),
            'url'           => $this->serializedUrl(),
            'ip'            => $this->serializedIp(),
            'user_agent'    => $this->serializedUserAgent(),
            'timestamp'     => $this->serializedTimestamp(),
            'id_user'       => $this->serializedIdUser(),
        );
        return $export_vars;
    }

    public function listCsvSerializer() {
        $export_vars = array(
            "when : ".$this->serializedWhen(),
            "type : ".$this->serializedType(),
            "level : ".$this->serializedLevel(),
            "message : ".$this->serializedMessage(),
            "method : ".$this->serializedMethod(),
            "url : ".$this->serializedUrl(),
            "ip : ".$this->serializedIp(),
            "user_agent : ".$this->serializedUserAgent(),
            "timestamp : ".$this->serializedTimestamp(),
            "id_user : ".$this->serializedIdUser(),
        );
        return $export_vars;
    }


    public function getOrderedListFields () {
        return array(
            "when",
            "day",
            "type",
            "level",
            "message",
            "method",
            "url",
            "ip",
            "user_agent",
            "timestamp",
            "id_user"
        );
    }

    public function getOrderedListValues () {
        return array(
            $this->serializedWhen(),
            $this->serializedDay(),
            $this->serializedType(),
            $this->serializedLevel(),
            $this->serializedMessage(),
            $this->serializedMethod(),
            $this->serializedUrl(),
            $this->serializedIp(),
            $this->serializedUserAgent(),
            $this->serializedTimestamp(),
            $this->serializedIdUser(),
        );
    }


    /**
     * log type
     * @JMS\VirtualProperty
     * @JMS\SerializedName("type")
     * @JMS\Type("string")
     * @JMS\Since("1.0.x")
     */
    public function serializedType()
    {
        return (is_null($this->type)?null:$this->type);
    }

    /**
     * log level
     * @JMS\VirtualProperty
     * @JMS\SerializedName("level")
     * @JMS\Type("integer")
     * @JMS\Since("1.0.x")
     */
    public function serializedLevel()
    {
        return (is_null($this->level)?null:$this->level);
    }

    /**
     * log message
     * @JMS\VirtualProperty
     * @JMS\SerializedName("message")
     * @JMS\Type("string")
     * @JMS\Since("1.0.x")
     */
    public function serializedMessage()
    {
        return (is_null($this->message)?null:$this->message);
    }

    /**
     * log method
     * @JMS\VirtualProperty
     * @JMS\SerializedName("method")
     * @JMS\Type("string")
     * @JMS\Since("1.0.x")
     */
    public function serializedMethod()
    {
        return (is_null($this->method)?null:$this->method);
    }

    /**
     * log url
     * @JMS\VirtualProperty
     * @JMS\SerializedName("level")
     * @JMS\Type("string")
     * @JMS\Since("1.0.x")
     */
    public function serializedUrl()
    {
        return (is_null($this->url)?null:$this->url);
    }

    /**
     * log when
     * @JMS\VirtualProperty
     * @JMS\SerializedName("when")
     * @JMS\Type("string")
     * @JMS\Since("1.0.x")
     */
    public function serializedWhen()
    {
        return (is_null($this->when)?null:strftime('%Y-%m-%d %H:%M',$this->when->getTimestamp()));
    }


    /**
     * log day
     * @JMS\VirtualProperty
     * @JMS\SerializedName("day")
     * @JMS\Type("string")
     * @JMS\Since("1.0.x")
     */
    public function serializedDay()
    {
        return (is_null($this->when)?null:strftime('%Y-%m-%d',$this->when->getTimestamp()));
    }

    /**
     * log ip
     * @JMS\VirtualProperty
     * @JMS\SerializedName("ip")
     * @JMS\Type("string")
     * @JMS\Since("1.0.x")
     */
    public function serializedIp()
    {
        return (is_null($this->ip)?null:$this->ip);
    }

    /**
     * log user agent
     * @JMS\VirtualProperty
     * @JMS\SerializedName("user_agent")
     * @JMS\Type("string")
     * @JMS\Since("1.0.x")
     */
    public function serializedUserAgent()
    {
        return (is_null($this->userAgent)?null:$this->userAgent);
    }

    /**
     * log timestamp
     * @JMS\VirtualProperty
     * @JMS\SerializedName("timestamp")
     * @JMS\Type("string")
     * @JMS\Since("1.0.x")
     */
    public function serializedTimestamp()
    {
        return (is_null($this->timestamp)?null:$this->timestamp);
    }

    /**
     * log idUser
     * @JMS\VirtualProperty
     * @JMS\SerializedName("idUser")
     * @JMS\Type("string")
     * @JMS\Since("1.0.x")
     */
    public function serializedIdUser()
    {
        return (is_null($this->idUser)?null:$this->idUser);
    }

    ################################################# GETTERS AND SETTERS FUNCTIONS

    public static function getLogSys()
    {
        return self::LOG_SYS;
    }

    public static function getLogTrack()
    {
        return self::LOG_TRACK;
    }

    public function getType()
    {
        return $this->type;
    }

    public function setType($type)
    {
        $this->type = $type;
    }

    public function getLevel()
    {
        return $this->level;
    }

    public function setLevel($level)
    {
        $this->level = $level;
    }

    public function getMessage()
    {
        return $this->message;
    }

    public function setMessage($message)
    {
        $this->message = $message;
    }

    public function getMethod()
    {
        return $this->method;
    }

    public function setMethod($method)
    {
        $this->method = $method;
    }

    public function getUrl()
    {
        return $this->url;
    }

    public function setUrl($url)
    {
        $this->url = $url;
    }

    public function getWhen()
    {
        return $this->when;
    }

    public function setWhen($when)
    {
        $this->when = $when;
    }

    public function getIp()
    {
        return $this->ip;
    }

    public function setIp($ip)
    {
        $this->ip = $ip;
    }

    public function getUserAgent()
    {
        return $this->userAgent;
    }

    public function setUserAgent($userAgent)
    {
        $this->userAgent = $userAgent;
    }

    /**
     * @return string
     */
    public function getTimestamp()
    {
        return $this->timestamp;
    }

    /**
     * @param string $timestamp
     */
    public function setTimestamp($timestamp)
    {
        $this->timestamp = $timestamp;
    }

    /**
     * @return string
     */
    public function getIdUser()
    {
        return $this->idUser;
    }

    /**
     * @param string $idUser
     */
    public function setIdUser($idUser)
    {
        $this->idUser = $idUser;
    }


}