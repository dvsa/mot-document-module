<?php

namespace DvsaReport\Service\Encoder;

class ParamsEncoder
{
    /**
     * @param array $params
     *
     * @return false|string
     */
    public function arrayToJson($params)
    {
        foreach ($params as $key => $value) {
            if (is_string($value) && !is_numeric($value)) {
                try {
                    $decodedValue = json_decode($value, flags: \JSON_THROW_ON_ERROR);

                    $params[$key] = $decodedValue;
                } catch (\JsonException $e) {
                    // This is a way to check if the $value is valid JSON or not.
                }
            }
        }

        return json_encode($params);
    }
}
