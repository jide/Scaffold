<?php

/**
 * Test class for Scaffold_Response_Cache.
 * Generated by PHPUnit on 2010-04-06 at 20:56:42.
 */
class Scaffold_Response_CacheTest extends PHPUnit_Framework_TestCase
{
	public function testModified()
	{
		$_SERVER['HTTP_IF_MODIFIED_SINCE'] = 'Wed, 11 Aug 2010 05:47:54 GMT';
		$cache = new Scaffold_Response_Cache;
		
		$modified = $cache->modified( strtotime('Wed, 11 Aug 2011 05:47:54 GMT'));
		$this->assertTrue($modified);
		
		$modified = $cache->modified( strtotime('Wed, 11 Aug 2009 05:47:54 GMT'));
		$this->assertFalse($modified);
	}
	
	public function testMatched()
	{
		$_SERVER['HTTP_IF_NONE_MATCH'] = 'foo';
		$cache = new Scaffold_Response_Cache;
		
		$matched = $cache->matched('bar');
		$this->assertFalse($matched);
		
		$matched = $cache->matched('foo');
		$this->assertTrue($matched);
	}
	
	public function testNoEtag()
	{
		$_SERVER['HTTP_IF_NONE_MATCH'] = null;
		$cache = new Scaffold_Response_Cache;
		
		$matched = $cache->matched('bar');
		$this->assertFalse($matched);
		
		$matched = $cache->matched(false);
		$this->assertTrue($matched);
		
		$matched = $cache->matched(null);
		$this->assertTrue($matched);
	}

	public function testGetModifiedSince()
	{
		$_SERVER['HTTP_IF_MODIFIED_SINCE'] = 'Wed, 11 Aug 2010 05:47:54 GMT';
		
		// Should return the same time
		$cache = new Scaffold_Response_Cache;
		$this->assertEquals($cache->get_modified_since(),strtotime('Wed, 11 Aug 2010 05:47:54 GMT'));
	}
	
	/**
     * @depends testGetModifiedSince
     */
    public function testIE6ModifiedSince()
    {
    	$_SERVER['HTTP_IF_MODIFIED_SINCE'] = 'Wed, 11 Aug 2010 05:47:54 GMT; length=100';

		// Cache should be valid as the headers match
		$cache = new Scaffold_Response_Cache;
		$this->assertEquals($cache->get_modified_since(),strtotime('Wed, 11 Aug 2010 05:47:54 GMT'));
    }
	
	/**
	 * @depends testGetModifiedSince
	 */
	public function testGetEtags()
	{
		$_SERVER['HTTP_IF_NONE_MATCH'] = '"foo"';
		
		// Should return an array with 1 value
		$cache = new Scaffold_Response_Cache;
		$this->assertEquals($cache->get_etag(),'"foo"');
	}

	/**
	 * @depends testGetEtags
	 */
    public function testValidNoCache()
    {
    	$_SERVER['HTTP_IF_MODIFIED_SINCE'] = null;
    	$_SERVER['HTTP_IF_NONE_MATCH'] = null;
    	
    	// Cache should be invalid, as the header haven't been sent
    	$cache = new Scaffold_Response_Cache;
    	$valid = $cache->valid(strtotime('Wed, 11 Aug 2010 05:47:54 GMT'),'foo');  
    	$this->assertFalse($valid);
    }
    
    /**
     * @depends testValidNoCache
     */
    public function test_OldModified_CurrentEtag()
    {
    	$_SERVER['HTTP_IF_MODIFIED_SINCE'] = 'Wed, 11 Aug 2000 05:47:54 GMT';
    	$_SERVER['HTTP_IF_NONE_MATCH'] = 'foo';
    	
    	// Cache should be invalid
    	$cache = new Scaffold_Response_Cache;
    	$valid = $cache->valid(strtotime('Wed, 11 Aug 2010 05:47:54 GMT'),'foo');
    	
    	// It has been modified
    	$this->assertTrue($cache->modified(strtotime('Wed, 11 Aug 2010 05:47:54 GMT')));
    	
    	// ETags match
    	$this->assertTrue($cache->matched('foo'));
    	
    	$this->assertFalse($valid);
    }
    
    /**
     * @depends test_OldModified_CurrentEtag
     */
    public function test_OldModified_OldEtag()
    {
    	$_SERVER['HTTP_IF_MODIFIED_SINCE'] = 'Wed, 11 Aug 2000 05:47:54 GMT';
    	$_SERVER['HTTP_IF_NONE_MATCH'] = 'foo';
    	
    	// Cache should be invalid
    	$cache = new Scaffold_Response_Cache;
    	$valid = $cache->valid(strtotime('Wed, 11 Aug 2010 05:47:54 GMT'),'bar');  
    	$this->assertFalse($valid);
    }
  
    /**
     * @depends test_OldModified_OldEtag
     */
    public function test_CurrentModified_OldEtag()
    {
    	$_SERVER['HTTP_IF_MODIFIED_SINCE'] = 'Wed, 11 Aug 2010 05:47:54 GMT';
    	$_SERVER['HTTP_IF_NONE_MATCH'] = 'foo';
    	
    	// Cache should be invalid
    	$cache = new Scaffold_Response_Cache;
    	$valid = $cache->valid(strtotime('Wed, 11 Aug 2010 05:47:54 GMT'),'bar');  
    	$this->assertFalse($valid);
    }
    
    /**
     * @depends test_CurrentModified_OldEtag
     */
    public function testValidCache()
    {
    	$_SERVER['HTTP_IF_MODIFIED_SINCE'] = 'Wed, 11 Aug 2010 05:47:54 GMT';
    	$_SERVER['HTTP_IF_NONE_MATCH'] = '"foo"';
		
		// Cache should be valid as the headers match
		$cache = new Scaffold_Response_Cache;
		$valid = $cache->valid(strtotime('Wed, 11 Aug 2010 05:47:54 GMT'),'"foo"');
    	$this->assertTrue($valid);
    }
}
