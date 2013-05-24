<?php

namespace Verdet\SphinxSearchBundle\Services\Search;

/**
 * Sphinx search service, wrapper for SphinxAPI.php
 */
class SphinxSearch
{
    /**
     * @var string
     */
    protected $host;

    /**
     * @var string
     */
    protected $port;

    /**
     * @var string
     */
    protected $socket;

    /**
     * @var array
     *
     */
    protected $indexes;

    /**
     * @var \SphinxClient
     */
    protected $sphinx;

    /**
     * Constructor.
     *
     * @param string $host    The server's host name/IP.
     * @param string $port    The port that the server is listening on.
     * @param string $socket  The UNIX socket that the server is listening on.
     * @param array  $indexes The list of indexes that can be used.
     */
    public function __construct($host = 'localhost', $port = '9312', $socket = null, array $indexes = array())
    {
//        require_once __DIR__ . '/SphinxAPI.php';

        $this->host = $host;
        $this->port = $port;
        $this->socket = $socket;
        $this->indexes = $indexes;

        $this->sphinx = new \SphinxClient();
        if ($this->socket !== null) {
            $this->sphinx->setServer($this->socket);
        } else {
            $this->sphinx->setServer($this->host, $this->port);
        }
    }

    /**
     * Escape the supplied string.
     *
     * @param string $string The string to be escaped.
     *
     * @return string The escaped string.
     */
    public function escapeString($string)
    {
        return $this->sphinx->escapeString($string);
    }

    /**
     * Set the desired match mode.
     *
     * @param int $mode The matching mode to be used.
     */
    public function setMatchMode($mode)
    {
        $this->sphinx->setMatchMode($mode);
    }

    /**
     * Set limits on the range and number of results returned.
     *
     * @param int $offset The number of results to seek past.
     * @param int $limit  The number of results to return.
     * @param int $max    The maximum number of matches to retrieve.
     * @param int $cutoff The cutoff to stop searching at.
     */
    public function setLimits($offset, $limit, $max = 0, $cutoff = 0)
    {
        $this->sphinx->setLimits($offset, $limit, $max, $cutoff);
    }

    /**
     * Set weights for individual fields.  $weights should look like:
     *
     * $weights = array(
     *   'Normal field name' => 1,
     *   'Important field name' => 10,
     * );
     *
     * @param array $weights Array of field weights.
     */
    public function setFieldWeights(array $weights)
    {
        $this->sphinx->setFieldWeights($weights);
    }

    /**
     * Set the desired search filter.
     *
     * @param string $attribute The attribute to filter.
     * @param array  $values    The values to filter.
     * @param bool   $exclude   Is this an exclusion filter?
     */
    public function setFilter($attribute, $values, $exclude = false)
    {
        $this->sphinx->setFilter($attribute, $values, $exclude);
    }

    /**
     * Reset all previously set filters.
     */
    public function resetFilters()
    {
        $this->sphinx->resetFilters();
    }

    /**
     * Search for the specified query string.
     *
     * @param string $query       The query string that we are searching for.
     * @param array  $indexes     The indexes to perform the search on.
     * @param array  $options     The options for the query.
     * @param bool   $escapeQuery Should the query string be escaped?
     *
     * @return array|bool The results of the search.
     * @throws \RuntimeException
     */
    public function search($query, array $indexes, array $options = array(), $escapeQuery = true)
    {
        if ($escapeQuery) {
            $query = $this->sphinx->escapeString($query);
        }

        /**
         * Build the list of indexes to be queried.
         */
        $indexNames = '';
        foreach ($indexes as &$label) {
            if (isset($this->indexes[$label])) {
                $indexNames .= $this->indexes[$label] . ' ';
            }
        }

        /**
         * If no valid indexes were specified, return an empty result set.
         *
         * FIXME: This should probably throw an exception.
         */
        if (empty($indexNames)) {
            return array();
        }

        /**
         * Set the offset and limit for the returned results.
         */
        if (isset($options['result_offset']) && isset($options['result_limit'])) {
            $this->sphinx->setLimits($options['result_offset'], $options['result_limit']);
        }

        /**
         * Weight the individual fields.
         */
        if (isset($options['field_weights'])) {
            $this->sphinx->setFieldWeights($options['field_weights']);
        }

        /**
         * Perform the query.
         */
        $results = $this->sphinx->query($query, $indexNames);
        if ($results['status'] !== SEARCHD_OK) {
            throw new \RuntimeException(sprintf(
                'Searching index "%s" for "%s" failed with error "%s".',
                $label,
                $query,
                $this->sphinx->getLastError()
            ));
        }

        return $results;
    }

    /**
     * Adds a query to a multi-query batch using current settings.
     *
     * @param string $query   The query string that we are searching for.
     * @param array  $indexes The indexes to perform the search on.
     */
    public function addQuery($query, array $indexes)
    {
        $indexNames = '';
        foreach ($indexes as &$label) {
            if (isset($this->indexes[$label])) {
                $indexNames .= $this->indexes[$label] . ' ';
            }
        }

        if (!empty($indexNames)) {
            $this->sphinx->addQuery($query, $indexNames);
        }
    }

    /**
     * Runs the currently batched queries, and returns the results.
     *
     * @return array The results of the queries.
     */
    public function runQueries()
    {
        return $this->sphinx->runQueries();
    }

    /**
     * Get snippet for text
     *
     * @param string $text    Text to process
     * @param string $index   Search index
     * @param string $terms   Words for highlight
     * @param array  $options Generate snippet option
     *
     * @return string|false
     */
    public function getSnippet($text, $index, $terms, $options = array())
    {
        $results = $this->getSnippets(array($text), $index, $terms, $options);

        if ($results) {
            return $results[0];
        }

        return false;
    }

    /**
     * Get snippets for array of text
     *
     * @param array  $text    Array of text to process
     * @param string $index   Search index
     * @param string $terms   Words for highlight
     * @param array  $options Generate snippet option
     *
     * @return array|false
     */
    public function getSnippets($text, $index, $terms, $options = array())
    {
        return $this->sphinx->BuildExcerpts($text, $index, $terms, $options);
    }

    /**
     * Reset filters, group by, overrides
     */
    public function resetAll()
    {
        $this->sphinx->ResetFilters();
        $this->sphinx->ResetGroupBy();
        $this->sphinx->ResetOverrides();
        $this->sphinx->SetSortMode(SPH_SORT_RELEVANCE);
    }

    /**
     * Set geo anchor for geosphere distance calculations (filters and sorting)
     *
     * @param string $attrLat  Attribute name for latitude
     * @param string $attrLong Attribute name for longitude
     * @param float  $lat      Point latitude, must be in radians
     * @param float  $long     Point longitude, must be in radians
     *
     * @link http://www.sanisoft.com/blog/2011/05/02/geo-distance-search-in-sphinx/
     */
    public function setGeoAnchor($attrLat, $attrLong, $lat, $long)
    {
        $this->sphinx->SetGeoAnchor($attrLat, $attrLong, $lat, $long);
    }

    /**
     * Set float range filter
     *
     * @param string $attribute Attribute name
     * @param float  $min       Min range value
     * @param float  $max       Max range value
     * @param bool   $exclude   Is this an exclusion filter?
     */
    public function setFilterFloatRange($attribute, $min, $max, $exclude = false)
    {
        $this->sphinx->SetFilterFloatRange($attribute, $min, $max, $exclude);
    }

    /**
     * Set range filter
     *
     * @param string $attribute Attribute name
     * @param float  $min       Min range value
     * @param float  $max       Max range value
     * @param bool   $exclude   Is this an exclusion filter?
     */
    public function setFilterRange($attribute, $min, $max, $exclude = false)
    {
        $this->sphinx->SetFilterRange($attribute, $min, $max, $exclude);
    }

    /**
     * Set sort mode
     *
     * @param int    $mode   Match sorting mode
     * @param string $sortBy Attribute to sort by
     */
    public function setSortMode($mode, $sortBy = '')
    {
        $this->sphinx->SetSortMode($mode, $sortBy);
    }
}
