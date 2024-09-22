<?php
/*
 * (c) Kerogs kerogs.labs@gmail.com
 *
 * This source file is subject to the Mozilla Public License Version 2.0 license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Kerogs\KerogsPhp;

class Algorithm
{
    public function __construct() {}


    /**
     * Returns the percentage of similarity between two strings. This function returns
     * the percentage of similarity as an integer between 0 and 100, where 0 means
     * the strings are completely different and 100 means they are identical.
     *
     * @param string $str1
     * @param string $str2
     * @return int
     */
    public function similarityPercentage($str1, $str2)
    {
        $str1 = basename($str1);

        similar_text($str1, $str2, $percent);

        return $percent;
    }

    /**
     * Search engine that returns a relevance-based sorted array of values from $values that match the given query.
     *
     * @param array $values The array of values to search from.
     * @param string $query The query to search for.
     *
     * @return array The sorted array of values that match the query, with the highest relevance first.
     */
    public function searchEngine(array $values, $query)
    {
        $result = [];

        $lowercaseQuery = strtolower($query);

        foreach ($values as $value) {
            $lowercaseValue = strtolower($value);

            $similarity = $this->similarityPercentage($lowercaseValue, $lowercaseQuery);

            $result[$value] = round($similarity, 2);
        }

        arsort($result);

        return $result;
    }
}
