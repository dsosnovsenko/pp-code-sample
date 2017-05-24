<?php
namespace Bfc\Core\Validator;

class RecaptchaValidator extends AbstractValidator
{
    /** @var string */
    protected $verifyUrl = 'https://www.google.com/recaptcha/api/siteverify';

    protected $acceptsEmptyValues = false;

    /**
     * @var array
     */
    protected $supportedOptions = [
        'secretKey' => ['', 'Secret key', 'string', true],
    ];

    /**
     * Checks if the given value is a valid ReCaptcha response.
     *
     * @param mixed $value
     *
     * @return bool
     */
    public function isValid($value = null)
    {
        $value = isset($_REQUEST['g-recaptcha-response'])
            ? $_REQUEST['g-recaptcha-response']
            : '';

        $secretKey = $this->options['secretKey'];
        $parameters = [
            'secret' => $secretKey,
            'response' => $value,
        ];

        $postResponse = $this->httpRequest($this->verifyUrl, $parameters, 'POST');
        $isValue = isset($postResponse['success']) && $postResponse['success'] === true
            ? true
            : false;

        return $isValue;
    }

    /**
     * Send http request.
     *
     * @param string $url
     * @param array $data
     * @param string $method The method ca be GET (default) or POST.
     *
     * @return array|bool
     */
    protected function httpRequest($url, $data = [], $method = 'GET')
    {
        $method = strtoupper($method);
        if (!in_array($method, ['GET', 'POST', 'PUT', 'DELETE'])) {
            $method = 'GET';
        }

        $queryData = http_build_query($data);
        $context = stream_context_create([
            'http' => [
                'header' => 'Content-Type: application/x-www-form-urlencoded' . PHP_EOL,
                'method' => $method,
                'content' => $queryData,
            ],
        ]);

        $response = file_get_contents($url, false, $context);
        if (is_string($response)) {
            $response = json_decode($response, true);
        }

        return $response;
    }
}