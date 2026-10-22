<?php

namespace BrizyEkklesia\Placeholder;

/************************************************************\
 *
 *   PHP Array Pagination Copyright 2007 - Derek Harvey
 *   www.lotsofcode.com
 *
 *   This file is part of PHP Array Pagination .
 *
 *   PHP Array Pagination is free software; you can redistribute it and/or modify
 *   it under the terms of the GNU General Public License as published by
 *   the Free Software Foundation; either version 2 of the License, or
 *   (at your option) any later version.
 *
 *   PHP Array Pagination is distributed in the hope that it will be useful,
 *   but WITHOUT ANY WARRANTY; without even the implied warranty of
 *   MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *   GNU General Public License for more details.
 *
 *   You should have received a copy of the GNU General Public License
 *   along with PHP Array Pagination ; if not, write to the Free Software
 *   Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
 *
 * \************************************************************/
class CustomPagination
{

    /**
     * Properties array
     * @var array
     * @access private
     */
    private $_properties = array();

    /**
     * Default configurations
     * @var array
     * @access public
     */
    public $_defaults = array(
        'page' => 1,
        'perPage' => 10
    );

    /**
     * Constructor
     *
     * @param array $array Array of results to be paginated
     * @param int $curPage The current page interger that should used
     * @param int $perPage The amount of items that should be show per page
     * @return void
     * @access public
     */
    public function __construct($array, $curPage = null, $perPage = null)
    {
        $this->array = $array;
        // cast so the arithmetic in getResults() never sees a non-numeric string or zero (TypeError/DivisionByZeroError on PHP 8)
        $this->curPage = max(1, (int) ($curPage == null ? $this->_defaults['page'] : $curPage));
        $this->perPage = max(1, (int) ($perPage == null ? $this->_defaults['perPage'] : $perPage));
    }

    /**
     * Global setter
     *
     * Utilises the properties array
     *
     * @param string $name The name of the property to set
     * @param string $value The value that the property is assigned
     * @return void
     * @access public
     */
    public function __set($name, $value)
    {
        $this->_properties[$name] = $value;
    }

    /**
     * Global getter
     *
     * Takes a param from the properties array if it exists
     *
     * @param string $name The name of the property to get
     * @return mixed Either the property from the internal
     * properties array or false if isn't set
     * @access public
     */
    public function __get($name)
    {
        if (array_key_exists($name, $this->_properties)) {
            return $this->_properties[$name];
        }
        return false;
    }

    /**
     * Set the show first and last configuration
     *
     * This will enable the "<< first" and "last >>" style
     * links
     *
     * @param boolean $showFirstAndLast True to show, false to hide.
     * @return void
     * @access public
     */
    public function setShowFirstAndLast($showFirstAndLast)
    {
        $this->_showFirstAndLast = $showFirstAndLast;
    }

    /**
     * Set the main seperator character
     *
     * By default this will implode an empty string
     *
     * @param string $mainSeperator The seperator between the page numbers
     * @return void
     * @access public
     */
    public function setMainSeperator($mainSeperator)
    {
        $this->mainSeperator = $mainSeperator;
    }

    /**
     * Get the result portion from the provided array
     *
     * @return array Reduced array with correct calculated offset
     * @access public
     */
    public function getResults()
    {
        // Assign the page variable
        if (empty($this->curPage) !== false) {
            $this->page = $this->curPage; // using the get method
        } else {
            $this->page = 1; // if we don't have a page number then assume we are on the first page
        }

        // Take the length of the array
        $this->length = count($this->array);

        // Get the number of pages
        $this->pages = ceil($this->length / $this->perPage);

        // Calculate the starting point
        $this->start = ceil(($this->page - 1) * $this->perPage);

        // return the portion of results
        return array_slice($this->array, $this->start, $this->perPage);
    }

    /**
     * Get the html links for the generated page offset
     *
     * @param array $params A list of parameters (probably get/post) to
     * pass around with each request
     * @return string  Return description (if any) ...
     * @access public
     */
    public function getLinks($params, $name, $anchorTag = "")
    {
        // Initiate the links array
        $plinks = array();
        $links = array();
        $slinks = array();

        if (isset($params[$name])) {
            unset($params[$name]);
        }

        // Concatenate the get variables to add to the page numbering string
        $queryUrl = '';
        if (!empty($params)) {
            $queryUrl = '&amp;' . http_build_query($params);
        }

        // If we have more then one pages
        if (($this->pages) > 1) {
            if ($this->_showFirstAndLast) {
                //$plinks[] = ' <a href="?page=1'.$queryUrl.'" class="first">First </a> ';
            }

            $prevHref = ($this->page > 1)
                ? "?" . $name . '=' . ($this->page - 1) . $queryUrl
                : '#';

            $nextHref = ($this->page < $this->pages)
                ? "?" . $name . '=' . ($this->page + 1) . $queryUrl
                : '#';

            $prevId = ($this->page > 1) ? 'previous' : 'off';
            $nextId = ($this->page < $this->pages) ? 'next' : 'off';

            $plinks[] = '<a href="' . $prevHref . '" id="' . $prevId . '">Previous</a>';

            // Assign all the page numbers & links to the array
            for ($j = 1; $j < ($this->pages + 1); $j++) {
                if ($this->page == $j) {
                    $links[] = ' <a id="current">' . $j . '</a> '; // If we are on the same page as the current item
                } else {
                    $links[] = " <a href=\"?$name=" . $j . $queryUrl . $anchorTag . '" class="number">' . $j . '</a> '; // add the link to the array
                }
            }

            $slinks[] = '<a href="' . $nextHref . '" id="' . $nextId . '">Next</a>';
            if ($this->_showFirstAndLast) {
                //$slinks[] = ' <a href="?page='.($this->pages).$queryUrl.'" class="last">Last</a> ';
            }

            // Push the array into a string using any some glue
            return implode(' ', $plinks) . implode($this->mainSeperator, $links) . implode(' ', $slinks);
        }

        return '';
    }
}

