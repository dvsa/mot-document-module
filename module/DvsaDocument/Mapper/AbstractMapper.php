<?php

/**
 * Abstract Mapper
 *
 * Enforces only string key => value pairs defined in the mapTemplate
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */

namespace DvsaDocument\Mapper;

/**
 * Abstract Mapper
 *
 * Enforces only string key => value pairs defined in the mapTemplate
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
abstract class AbstractMapper
{
    /**
     * Holds the date format (currently defined in GDS guidlines)
     */
    public const FORMAT_DATE = 'j F Y';

    /**
     * Holds the mapped data
     *
     * @var array
     */
    private $mappedData = array();

    /**
     * Holds the map template
     *
     * @var array
     */
    protected $mapTemplate = array();

    /**
     * Holds the input data
     *
     * @var array
     */
    protected $data = array();

    /**
     * Holds the data sources
     *
     * @var array
     */
    protected $dataSources = array();

    /**
     * Adds a data source
     *
     * @param string $name
     * @param array  $data
     */
    public function addDataSource($name, $data): void
    {
        $reset = false;

        // If we are overriding an existing data source, we need to reset the whole data array
        if (isset($this->dataSources[$name])) {
            $reset = true;
        }

        $this->dataSources[$name] = $data;

        if ($reset) {
            $this->resetData();
        } else {
            if ($this->data != null) {
                $this->data = array_merge($this->data, $data);
            } else {
                $this->data = $data;
            }
        }
    }

    /**
     * Get mapper data
     *
     * @return array
     */
    protected function getMappedData()
    {
        return $this->mappedData;
    }

    /**
     * Map items one to one based on a map config.
     *
     * @param array $mapConfig contains Strings or an array containing the source
     *                         data-key and a transformation callback to be applied.
     */
    protected function mapOneToOne($mapConfig): void
    {
        $data = $this->getData();

        foreach ($mapConfig as $mapKey => $dataKey) {
            if (is_array($dataKey)) {
                //  extended: transformation required before writing
                $key = $dataKey['key'];

                $this->setValue(
                    $mapKey,
                    (isset($data[$key]) ? $data[$key] : ''),
                    $dataKey['format']
                );
            } else {
                $this->setValue(
                    $mapKey,
                    (isset($data[$dataKey]) ? $data[$dataKey] : '')
                );
            }
        }
    }

    /**
     * Set a key's value
     *
     * @param string $key
     * @param string $value
     * @param string $formatter
     * @param array $params
     */
    protected function setValue($key, $value, $formatter = null, $params = array()): void
    {
        if (isset($this->mapTemplate[$key])) {
            $this->mappedData[$key] = $this->formatValue($value, $formatter, $params);
        }
    }

    /**
     * Format a value
     *
     * @param mixed  $value
     * @param string $formatter
     * @param array  $params
     *
     * @return mixed|string
     */
    protected function formatValue($value, $formatter = null, $params = array())
    {
        if (!is_null($formatter) && method_exists($this, 'format' . $formatter)) {
            // @phpstan-ignore-next-line
            return call_user_func([$this, 'format' . $formatter], $value, $params);
        }

        if (is_string($value) || is_array($value)) {
            return $value;
        }

        return '';
    }

    /**
     * Formats a country of origin: they contain strings of the form "XXX - YYY" or just "XXX".
     * For display purposes we want "YYY" if available, falling back to "XXX" otherwise.
     *
     * @param string $value
     * @param array $params
     *
     * @return string
     * @SuppressWarnings("unused")
     */
    protected function formatCountryRegistration($value, $params = [])
    {
        $parts = explode('-', $value);
        $value = count($parts) > 1 ? $parts[1] : $parts[0];

        return trim($value);
    }

    /**
     * Format date
     *
     * @param mixed  $value
     * @param array $params
     *
     * @return string
     */
    protected function formatDate($value, $params = array())
    {
        $date = null;
        $format = isset($params['format']) ? $params['format'] : self::FORMAT_DATE;

        if ($value instanceof \DateTime) {
            return $value->format($format);
        } elseif (is_array($value) && isset($value['date'])) {
            $date = $value['date'];
        } elseif (is_string($value)) {
            $date = $value;
        }

        if (is_null($date)) {
            return '';
        }

        return date($format, strtotime($date));
    }

    /**
     * Return the data array
     *
     * @return array
     */
    protected function getData()
    {
        return $this->data;
    }

    /**
     * Get a datasource by name
     *
     * @param string $name
     * @return mixed
     */
    protected function getDataSource($name)
    {
        return (isset($this->dataSources[$name]) ? $this->dataSources[$name] : null);
    }

    /**
     * Reset data array
     */
    private function resetData(): void
    {
        $this->data = array();

        foreach ($this->dataSources as $data) {
            $this->data = array_merge($this->data, $data);
        }
    }
}
