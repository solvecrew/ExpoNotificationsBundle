<?php

namespace Solvecrew\ExpoNotificationsBundle\Model;

class NotificationContentModel
{
    // Prefix and suffix for the Expo notification Token.
    const EXPO_TOKEN_PREFIX = 'ExponentPushToken[';
    const EXPO_TOKEN_SUFFIX = ']';

    /**
     * @var string
     */
    private $to;

    /**
     * @var array
     */
    private $data;

    /**
     * @var string
     */
    private $title;

    /**
     * @var string
     */
    private $body;

    /**
     * sound?: 'default' | null,
     *
     * @var string
     */
    private $sound = 'default';

    /**
     * @var int
     */
    private $ttl;

    /**
     * @var int
     */
    private $expiration;

    /**
     * priority?: 'default' | 'normal' | 'high',
     * @var string
     */
    private $priority = 'high';

    /**
     * @var int
     */
    private $badge;

    public function setTo($to)
    {
        $this->to = self::EXPO_TOKEN_PREFIX . $to . self::EXPO_TOKEN_SUFFIX;
    }

    public function getTo()
    {
        return $this->to;
    }

    public function setData($data)
    {
        $this->data = $data;
    }

    public function getData()
    {
        return $this->data;
    }

    public function setTitle($title)
    {
        $this->title = $title;
    }

    public function getTitle()
    {
        return $this->title;
    }

    public function setBody($body)
    {
        $this->body = $body;
    }

    public function getBody()
    {
        return $this->body;
    }

    public function setSound($sound)
    {
        $this->sound = $sound;
    }

    public function getSound()
    {
        return $this->sound;
    }

    public function setTtl($ttl)
    {
        $this->ttl = $ttl;
    }

    public function getTtl()
    {
        return $this->ttl;
    }

    public function setExpiration($expiration)
    {
        $this->expiration = $expiration;
    }

    public function getExpiration()
    {
        return $this->expiration;
    }

    public function setPriority($priority)
    {
        $this->priority = $priority;
    }

    public function getPriority()
    {
        return $this->priority;
    }

    public function setBadge($badge)
    {
        $this->badge = $badge;
    }

    public function getBadge()
    {
        return $this->badge;
    }

    public function getJson()
    {
        $result = [];

        if ($this->to) {
            $result['to'] = $this->to;
        }

        if ($this->data) {
            $result['data'] = $this->data;
        }

        if ($this->title) {
            $result['title'] = $this->title;
        }

        if ($this->body) {
            $result['body'] = $this->body;
        }

        if ($this->sound) {
            $result['sound'] = $this->sound;
        }

        if ($this->ttl) {
            $result['ttl'] = $this->ttl;
        }

        if ($this->expiration) {
            $result['expiration'] = $this->expiration;
        }

        if ($this->priority) {
            $result['priority'] = $this->priority;
        }

        if ($this->badge) {
            $result['badge'] = $this->badge;
        }

        return json_encode([$result]);
    }
}
