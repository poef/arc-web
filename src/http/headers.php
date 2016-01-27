<?php

/*
 * This file is part of the Ariadne Component Library.
 *
 * (c) Muze <info@muze.nl>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace arc\http;

/**
 * Class headers
 * @package arc\http
 */
final class headers
{

    /**
     * Parse response headers string from a HTTP request into an array of headers. e.g.
     * [ 'Location' => 'http://www.example.com', ... ]
     * When multiple headers with the same name are present, all values will form an array, in the order in which
     * they are present in the source.
     * @param string $headers The headers string to parse.
     * @return array
     */
    public static function parse( $headers ) {
        if ( !is_array($headers) && !$headers instanceof \ArrayObject ) {
            $headers = array_filter(
                array_map( "trim", explode( "\n", (string) $headers ) )
            );
        }
        $result = [];
        foreach( $headers as $key => $header ) {
            $temp = array_map('trim', explode(':', $header, 2) );
            if ( isset( $temp[1] ) ) {
                if ( !isset($result[ $temp[0]]) ) {
                    // first entry for this header
                    $result[ $temp[0] ] = $temp[1];
                } else if ( is_string($result[ $temp[0] ]) ) {
                    // second header entry with same name
                    $result[ $temp[0] ] = [
                        $result[ $temp[0] ],
                        $temp[1]
                    ];
                } else { // third or later header entry with same name
                    $result[ $temp[0] ][] = $temp[1];
                }
            } else if (is_numeric($key)) {
                $result[] = $temp[0];
            } else { // e.g. HTTP1/1 200 OK
                $result[$key] = $temp[0];
            }
        }
        return $result;
    }

    /**
     * Return the last value sent for a specific header, uses the output of parse().
     * @param (mixed) $headers An array with multiple header strings or a single string.
     * @return array|mixed
     */
    private static function getLastHeader($headers) {
        if ( is_array($headers) ) {
            return end($headers);
        }
        return $headers;
    }

    public static function parseHeader($header)
    {
        $info = array_map('trim', explode(',', $header));
        $header = [];
        foreach ( $info as $entry ) {
            $temp = array_map( 'trim', explode( '=', $entry ));
            $header[ $temp[0] ] = (isset($temp[1]) ? $temp[1] : $temp[0] );
        }
        return $header;
    }

    static private function getCacheControlTime( $header, $private )
    {
        $dontcache = false;
        foreach ( $header as $key => $value ) {
            switch($key) {
                case 'max-age':
                case 's-maxage':
                    if ( isset($result) ) {
                        $result = min($result, (int) $value);
                    } else {
                        $result = (int) $value;
                    }
                break;
                case 'public':
                break;
                case 'private':
                    if ( !$private ) {
                        $dontcache = true;
                    }
                break;
                case 'no-cache':
                case 'no-store':
                    $dontcache = true;
                break;
                case 'must-revalidate':
                case 'proxy-revalidate':
                    $dontcache = true; // FIXME: should return more information than just the cache time instead
                break;
                default:
                break;
            }
        }
        if ( $dontcache ) {
            $result = 0;
        }
        return $result;
    }

    /**
     * Parse response headers to determine if and how long you may cache the response. Doesn't understand ETags.
     * @param mixed $headers Headers string or array as returned by parse()
     * @param bool $private Whether to store a private cache or public cache image.
     * @return int The number of seconds you may cache this result starting from now.
     */
    public static function parseCacheTime( $headers, $private=true )
    {
        $result = null;
        if ( is_string($headers) || !isset($headers['Content-Type'] )) {
            $headers = \arc\http\headers::parse( $headers );
        }
        if ( isset( $headers['Cache-Control'] ) ) {
            $header = self::parseHeader( self::getLastHeader( $headers['Cache-Control'] ) );
            $result = self::getCacheControlTime( $header, $private );
        }
        if ( !isset($result) && isset( $headers['Expires'] ) ) {
            $result = strtotime( self::getLastHeader($headers['Expires']) ) - time();
        }
        return (int) $result;
    }

}