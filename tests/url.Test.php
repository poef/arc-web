<?php

    /*
     * This file is part of the Ariadne Component Library.
     *
     * (c) Muze <info@muze.nl>
     *
     * For the full copyright and license information, please view the LICENSE
     * file that was distributed with this source code.

     */


    class TestUrl extends PHPUnit_Framework_TestCase
    {
        function testSafeUrl()
        {
            $starturl = 'http://www.ariadne-cms.org/?frop=1';
            $url = \arc\url::safeUrl($starturl);
            $this->assertInstanceOf('\arc\url\Url', $url);
            $this->assertEquals( $starturl, (string)$url);

            $starturl = 'http://www.ariadne-cms.org/?frop=1&frop=2';
            $url = \arc\url::safeUrl($starturl);
            $url->fragment = 'test123';
            $this->assertEquals( $starturl .'#test123', (string)$url);

            $starturl = 'http://www.ariadne-cms.org/view.html?some%20thing';
            $url = \arc\url::safeUrl($starturl);
            $this->assertInstanceOf('\arc\url\Url', $url);
            $this->assertEquals( $starturl, (string)$url);
            $this->assertEquals( 'some thing', $url->query[0] );

            $starturl = 'http://www.ariadne-cms.org/view.html?some%20thing';
            $url = \arc\url::safeUrl($starturl);
            $this->assertInstanceOf('\arc\url\Url', $url);
            $this->assertEquals('some thing', $url->query[0] );
        }

        function testparseUrlQueryMultipleElements()
        {
            $starturl = 'http://www.ariadne-cms.org/?test=test&test=frop';
            $url = \arc\url::url($starturl);
            $this->assertInstanceOf('\arc\url\Url', $url);
            $this->assertInstanceOf( '\arc\url\PHPQuery', $url->query );
            $this->assertEquals( 'frop', ''.$url->query['test'], "PHP url parser, the second instance has precedence");
            $this->assertNotEquals( $starturl, ''.$url );
        }

        function testparseUrlQueryUnnumberedElements()
        {
            $starturl = 'http://www.ariadne-cms.org/?test[]=test&test[]=frop';
            $url = \arc\url::url($starturl);
            $this->assertInstanceOf('\arc\url\Url', $url);
            $this->assertInstanceOf( '\arc\url\PHPQuery', $url->query );
            $this->assertEquals( ['test', 'frop'], $url->query['test'], "Auto indexed array from query");
            $this->assertEquals( (string)$url, (string) \arc\url::url($url) );
        }

        function testparseUrlQueryNumberedElements()
        {
            $starturl = 'http://www.ariadne-cms.org/?test[1]=test&test[0]=frop';
            $url = \arc\url::url($starturl);
            $this->assertInstanceOf('\arc\url\Url', $url);
            $this->assertInstanceOf( '\arc\url\PHPQuery', $url->query );
            $this->assertEquals( ['frop', 'test'], $url->query['test'], "manual index array from query");
            $this->assertEquals( (string)$url, (string) \arc\url::url($url) );
        }

        function testparseUrlQueryWithEncodedSpace()
        {
            $starturl = 'http://www.ariadne-cms.org/view.html?foo=some+thing';
            $url = \arc\url::url($starturl);
            $this->assertInstanceOf('\arc\url\Url', $url);
            $this->assertNotEquals( $starturl, (string)$url, '+ signed should be encoded with %20 conform rfc 3986' );
            $this->assertEquals( $starturl, str_replace('%20','+',(string)$url ));
            $this->assertEquals( 'some thing', $url->query['foo']);

        }

        function testParseAuthority()
        {
            $starturl = 'http://foo:bar@www.ariadne-cms.org:80/';
            $url = \arc\url::url($starturl);
            $this->assertInstanceOf('\arc\url\Url', $url);
            $this->assertEquals( $starturl, $url);
        }

        function testParseCommonURLS()
        {
            $commonUrls = [
                'ftp://ftp.is.co.za/rfc/rfc1808.txt',
                'http://www.ietf.org/rfc/rfc2396.txt',
                'ldap://[2001:db8::7]/c=GB?objectClass?one',
                'mailto:John.Doe@example.com',
                'news:comp.infosystems.www.servers.unix',
                'tel:+1-816-555-1212',
                'telnet://192.0.2.16:80/',
                'urn:oasis:names:specification:docbook:dtd:xml:4.1.2',
                '//google.com',
                '../../relative/',
                'file:///C:/',
                'http://www.ariadne-cms.org/~user/page'
            ];
            $parsedUrls = array_map( function($url) {
                return (string)\arc\url::safeUrl($url);
            }, $commonUrls);

            $this->assertEquals( $commonUrls, $parsedUrls);
        }

    }
