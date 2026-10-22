<?php

namespace BrizyEkklesia;


class PrayerCloudApi
{
    const API_URL_STAGING = 'https://prayer.stg.ministrycloud.com/';
    const API_URL_PRODUCTION = 'https://prayer.ministrycloud.com/';

    /**
     * @var string
     */
    private $apiBaseUrl;

    /**
     * @var string
     */
    private $apiKey;

    /**
     * @var string
     */
    private $churchUuid;

    /**
     * @var string|null
     */
    private $lastError;

    /**
     * @var bool
     */
    private $sslVerify = true;

    /**
     * @param string $apiKey     Bearer token for authenticated requests
     * @param string $churchUuid UUID of the church
     * @param string $apiBaseUrl Base URL of the Prayer Cloud API (with trailing slash)
     */
    public function __construct($apiKey, $churchUuid, $apiBaseUrl = self::API_URL_STAGING)
    {
        $this->apiKey     = $apiKey;
        $this->churchUuid = $churchUuid;
        $this->apiBaseUrl = rtrim($apiBaseUrl, '/') . '/';
        $this->sslVerify  = $this->apiBaseUrl !== 'https://prayercloud.test/';
    }

    // ---------------------------------------------------------------
    //  Public getters
    // ---------------------------------------------------------------

    /**
     * @return string|null Last error message, if any
     */
    public function getLastError()
    {
        return $this->lastError;
    }

    /**
     * @return string
     */
    public function getChurchUuid()
    {
        return $this->churchUuid;
    }

    /**
     * @return bool
     */
    public function isConnected()
    {
        return !empty($this->apiKey) && !empty($this->churchUuid);
    }

    // ---------------------------------------------------------------
    //  Prayers
    // ---------------------------------------------------------------

    /**
     * Get a paginated list of prayers.
     *
     * @param int|null    $limit        Number of results per page (default 5)
     * @param int|null    $page         Page number (default 1)
     * @param string|null $statusFilter e.g. 'approved'
     * @param string|null $category     Category slug or 'all'
     *
     * @return object|null Decoded JSON response or null on failure
     */
    public function getPrayers($limit = null, $page = null, $statusFilter = null, $category = null)
    {
        $url   = $this->churchUrl('prayers');
        $query = [];

        $query['limit'] = is_numeric($limit) ? (int) $limit : 5;
        $query['page']  = is_numeric($page) ? (int) $page : 1;

        if ($statusFilter !== null && $statusFilter !== '') {
            $query['filter[status]'] = $statusFilter;
        }

        if ($category !== null && $category !== '' && $category !== 'all') {
            $query['filter[with_tag]'] = 'category.' . $category;
        }

        $response = $this->authenticatedGet($this->buildUrl($url, $query));

        if ($response === null) {
            return null;
        }

        return json_decode($response);
    }

    /**
     * Get a single prayer by UUID.
     *
     * @param string $uuid
     *
     * @return object|null
     */
    public function getPrayer($uuid)
    {
        $response = $this->authenticatedGet($this->prayerUrl($uuid));

        if ($response === null) {
            return null;
        }

        return json_decode($response);
    }

    /**
     * Create a new prayer request.
     *
     * @param string      $name
     * @param string      $email
     * @param string      $prayer
     * @param string      $syndicationType
     * @param string      $authorizationType
     * @param bool        $emailUpdates
     * @param string|null $phone
     *
     * @return object|null Decoded JSON response or null on failure
     */
    public function createPrayer($name, $email, $prayer, $syndicationType, $authorizationType, $emailUpdates, $phone = null)
    {
        if (!$this->isConnected()) {
            $this->lastError = 'Not connected to Prayer Cloud API.';
            return null;
        }

        $request = [
            'church_uuid'        => $this->churchUuid,
            'name'               => $name,
            'email'              => $email,
            'prayer'             => $prayer,
            'syndication_type'   => $syndicationType,
            'authorization_type' => $authorizationType,
            'email_updates'      => $emailUpdates,
        ];

        if (!empty($phone)) {
            $request['phone'] = $phone;
        }

        $response = $this->authenticatedPost($this->prayerUrl(), $request);

        if ($response === null) {
            return null;
        }

        return json_decode($response);
    }

    /**
     * Delete a prayer by UUID.
     *
     * @param string $uuid
     *
     * @return bool
     */
    public function deletePrayer($uuid)
    {
        return $this->authenticatedRequest($this->prayerUrl($uuid), 'DELETE') !== null;
    }

    /**
     * Approve a prayer by UUID.
     *
     * @param string $uuid
     *
     * @return bool
     */
    public function approvePrayer($uuid)
    {
        return $this->authenticatedRequest($this->approvalUrl($uuid), 'POST') !== null;
    }

    /**
     * Un-approve a prayer by UUID.
     *
     * @param string $uuid
     *
     * @return bool
     */
    public function unapprovePrayer($uuid)
    {
        return $this->authenticatedRequest($this->approvalUrl($uuid), 'DELETE') !== null;
    }

    /**
     * Acknowledge a prayer using the full acknowledge link from the API response.
     *
     * @param string $ackLink Full URL from $prayer->links->acknowledge_prayer_link
     *
     * @return object|null Decoded JSON response or null on failure
     */
    public function acknowledgePrayer($ackLink)
    {
        $response = $this->authenticatedGet($ackLink);

        if ($response === null) {
            return null;
        }

        return json_decode($response);
    }

    // ---------------------------------------------------------------
    //  Tags / Categories
    // ---------------------------------------------------------------

    /**
     * Get tags (categories) for the church.
     *
     * @param string $type Tag type filter (default 'category')
     *
     * @return object|null Decoded JSON or null on failure
     */
    public function getTags($type = 'category')
    {
        $url   = $this->churchUrl('tags');
        $query = [];

        if ($type) {
            $query['filter[type]'] = $type;
        }

        $response = $this->authenticatedGet($this->buildUrl($url, $query));

        if ($response === null) {
            return null;
        }

        return json_decode($response);
    }

    // ---------------------------------------------------------------
    //  HTTP primitives (cURL, no WP functions)
    // ---------------------------------------------------------------

    /**
     * Authenticated GET request.
     *
     * @param string $url
     *
     * @return string|null Raw response body or null on error
     */
    private function authenticatedGet($url)
    {
        return $this->authenticatedRequest($url, 'GET');
    }

    /**
     * Authenticated POST request with JSON body.
     *
     * @param string $url
     * @param array  $data
     *
     * @return string|null
     */
    private function authenticatedPost($url, array $data)
    {
        return $this->authenticatedRequest($url, 'POST', $data);
    }

    /**
     * Generic authenticated HTTP request.
     *
     * @param string     $url
     * @param string     $method  HTTP method (GET, POST, PATCH, DELETE, …)
     * @param array|null $data    JSON payload (only for POST / PATCH)
     *
     * @return string|null Raw response body or null on error
     */
    private function authenticatedRequest($url, $method = 'GET', ?array $data = null)
    {
        $this->lastError = null;

        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'Accept: application/json',
            'Authorization: Bearer ' . $this->apiKey,
        ]);

        if (!$this->sslVerify) {
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        }

        if ($data !== null && in_array($method, ['POST', 'PATCH', 'PUT'], true)) {
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        }

        $output   = curl_exec($ch);
        $httpCode = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlErr  = curl_error($ch);

        curl_close($ch);

        if ($curlErr) {
            $this->lastError = 'cURL error: ' . $curlErr;
            return null;
        }

        if ($httpCode < 200 || $httpCode > 204) {
            $this->lastError = $httpCode . ' error connecting.';
            $decoded = json_decode($output);

            if ($decoded) {
                if (isset($decoded->message)) {
                    $this->lastError .= ' ' . $decoded->message;
                }
                if (isset($decoded->errors)) {
                    foreach ($decoded->errors as $field => $messages) {
                        $this->lastError .= ' ' . implode(', ', (array) $messages);
                    }
                }
            }

            return null;
        }

        return $output;
    }

    // ---------------------------------------------------------------
    //  URL helpers
    // ---------------------------------------------------------------

    /**
     * Build a church-scoped API path.
     *
     * @param string $path e.g. 'prayers', 'tags'
     *
     * @return string
     */
    private function churchUrl($path = '')
    {
        return $this->apiBaseUrl . 'api/churches/' . $this->churchUuid . ($path ? '/' . $path : '');
    }

    /**
     * Build a prayer API path.
     *
     * @param string $uuid Optional prayer UUID
     *
     * @return string
     */
    private function prayerUrl($uuid = '')
    {
        return $this->apiBaseUrl . 'api/prayers/' . $uuid;
    }

    /**
     * Build an approval API path.
     *
     * @param string $uuid Prayer UUID
     *
     * @return string
     */
    private function approvalUrl($uuid)
    {
        return $this->apiBaseUrl . 'api/approval/' . $uuid;
    }

    /**
     * Append query parameters to a URL (pure PHP replacement for WP's add_query_arg).
     *
     * @param string $url
     * @param array  $params
     *
     * @return string
     */
    private function buildUrl($url, array $params = [])
    {
        if (empty($params)) {
            return $url;
        }

        $separator = (strpos($url, '?') === false) ? '?' : '&';

        return $url . $separator . http_build_query($params);
    }
}
