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
     * @var string
     */
    private $priority = 'high';

    /**
     * @var int
     */
    private $badge;

    /**
     * @var string
     */
    private $channelId;

    /**
     * @var bool
     */
    private $wasSuccessful;

    /**
     * @var string
     */
    private $responseMessage;

    /**
     * @var array
     */
    private $responseDetails;

    /**
     * Set to
     *
     * @param string $token
     *
     * @return NotificationContentModel
     */
    public function setTo(string $token)
    {
        $this->to = $token;
        if (!preg_match('/^'.preg_quote(self::EXPO_TOKEN_PREFIX, '/').'.*'.preg_quote(self::EXPO_TOKEN_SUFFIX, '/').'$/', $token)) {
            $this->to = self::EXPO_TOKEN_PREFIX . $token . self::EXPO_TOKEN_SUFFIX;
        }

        return $this;
    }

    /**
     * Get to
     *
     * @return string
     */
    public function getTo()
    {
        return $this->to;
    }

    /**
     * Set data
     *
     * @param array $data
     *
     * @return NotificationContentModel
     */
    public function setData(array $data)
    {
        $this->data = $data;

        return $this;
    }

    /**
     * Get data
     *
     * @return array
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * Set title
     *
     * @param string $title
     *
     * @return NotificationContentModel
     */
    public function setTitle(string $title)
    {
        $this->title = $title;

        return $this;
    }

    /**
     * Get title
     *
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * Set body
     *
     * @param string $body
     *
     * @return NotificationContentModel
     */
    public function setBody(string $body)
    {
        $this->body = $body;

        return $this;
    }

    /**
     * Get body
     *
     * @return string
     */
    public function getBody()
    {
        return $this->body;
    }

    /**
     * Set sound
     *
     * @param string $sound
     *
     * @return NotificationContentModel
     */
    public function setSound(string $sound)
    {
        $this->sound = $sound;

        return $this;
    }

    /**
     * Get sound
     *
     * @return string
     */
    public function getSound()
    {
        return $this->sound;
    }

    /**
     * Set ttl
     *
     * @param int $ttl
     *
     * @return NotificationContentModel
     */
    public function setTtl(int $ttl)
    {
        $this->ttl = $ttl;

        return $this;
    }

    /**
     * Get ttl
     *
     * @return int
     */
    public function getTtl()
    {
        return $this->ttl;
    }

    /**
     * Set expiration
     *
     * @param int $expiration
     *
     * @return NotificationContentModel
     */
    public function setExpiration(int $expiration)
    {
        $this->expiration = $expiration;

        return $this;
    }

    /**
     * Get expiration
     *
     * @return int
     */
    public function getExpiration()
    {
        return $this->expiration;
    }

    /**
     * Set priority
     *
     * @param string $priority
     *
     * @return NotificationContentModel
     */
    public function setPriority(string $priority)
    {
        $this->priority = $priority;

        return $this;
    }

    /**
     * Get priority
     *
     * @return string
     */
    public function getPriority()
    {
        return $this->priority;
    }

    /**
     * Set badge
     *
     * @param int $badge
     *
     * @return NotificationContentModel
     */
    public function setBadge(int $badge)
    {
        $this->badge = $badge;

        return $this;
    }

    /**
     * Get badge
     *
     * @return int
     */
    public function getBadge()
    {
        return $this->badge;
    }

    /**
     * Set channelId
     *
     * @param string $channelId
     *
     * @return NotificationContentModel
     */
    public function setChannelId(string $channelId)
    {
        $this->channelId = $channelId;

        return $this;
    }

    /**
     * Get channelId
     *
     * @return string
     */
    public function getChannelId()
    {
        return $this->channelId;
    }

    /**
     * Set wasSuccessful
     *
     * @param bool $wasSuccessful
     *
     * @return NotificationContentModel
     */
    public function setWasSuccessful(bool $wasSuccessful)
    {
        $this->wasSuccessful = $wasSuccessful;

        return $this;
    }

    /**
     * Get wasSuccessful
     *
     * @return bool
     */
    public function getWasSuccessful()
    {
        return $this->wasSuccessful;
    }

    /**
     * Set responseMessage
     *
     * @param string $responseMessage
     *
     * @return NotificationContentModel
     */
    public function setResponseMessage(string $responseMessage)
    {
        $this->responseMessage = $responseMessage;

        return $this;
    }

    /**
     * Get responseMessage
     *
     * @return string
     */
    public function getResponseMessage()
    {
        return $this->responseMessage;
    }

    /**
     * Set responseDetails
     *
     * @param array $responseDetails
     *
     * @return NotificationContentModel
     */
    public function setResponseDetails(array $responseDetails)
    {
        $this->responseDetails = $responseDetails;

        return $this;
    }

    /**
     * Get responseDetails
     *
     * @return array
     */
    public function getResponseDetails()
    {
        return $this->responseDetails;
    }

    /**
     * Get requestData
     *
     * @return array
     */
    public function getRequestData()
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

        if ($this->channelId) {
            $result['channelId'] = $this->channelId;
        }

        return $result;
    }
}
