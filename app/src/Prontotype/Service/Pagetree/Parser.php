<?php

namespace Prontotype\Service\Pagetree;

Class Parser {
	
	protected $page_tree = NULL;
	
	protected $route_map = array();
	
	protected $id_map = array();
	
	protected $pages_path;
	
	protected $app;
	
	public static $folder_format_regex = '/\/((\d)*[\._\-])/';
	
	public function __construct( $root_path, $pages_path, $app )
	{
		$this->root_path = $root_path;
		$this->pages_path = $pages_path;
		$this->app = $app;
	}

	public function getPage( $url_path )
	{
		$this->loadPages();
		
		$url_path = '/' . trim( $url_path, '/' );

		if ( $url_path == '/' )
		{
			$url_path = '/index'; // homepage
		}

		if ( isset($this->route_map[$url_path]) )
		{
			return $this->route_map[$url_path];
		}
		elseif ( isset($this->route_map[$url_path.'/index']) )
		{
			return $this->route_map[$url_path.'/index'];
		}

		return NULL;
	}

	public function getPath( $url_path )
	{
		$page = $this->getPage( $url_path );
		return $page ? $page->fs_path : NULL;
	}
	
	public function getPageById( $id )
	{
		$this->loadPages();
		return isset( $this->id_map[$id] ) ? $this->id_map[$id] : NULL;
	}
	
	public function getPathById( $id )
	{
		$page = $this->pageFromId( $id );
		return $page ? $page->fs_path : NULL;
	}
	
	public function getPageTree()
	{
		return $this->page_tree;
	}

 	protected function loadPages()
	{
		if ( $this->page_tree === NULL )
		{
			$pages = new \RecursiveDirectoryIterator( $this->pages_path );
			$this->page_tree = $this->parseDirectory( $pages );
		}
	}
	
	protected function parseDirectory( \RecursiveDirectoryIterator $iterator )
	{
		$page_tree = array();
		
		foreach ( $iterator as $file )
		{
			$item = NULL;
			if ( $file->isFile() && strpos( $file->getFilename(), '.' ) !== 0 )
			{
				$item = new Page( $file->getPathname(), $this->pages_path, $this->app );
				$this->route_map[$item->url] = $item;
				if ( $item->id )
				{
					$this->id_map[$item->id] = $item;
				}
			}
			elseif ( $iterator->hasChildren() )
			{
				$item = new Directory( $file->getPathname(), $this->pages_path, $this->app );
				
				$children = $this->parseDirectory( $iterator->getChildren() );
				
				$item->add_children( $children );
			}
			if ( $item ) $page_tree[] = $item;
		}
		return $page_tree;
	}
	
	public static function title_case( $title )
	{ 
		$smallwordsarray = array('of','a','the','and','an','or','nor','but','is','if','then','else','when','at','from','by','on','off','for','in','out','over','to','into','with');
		
		$words = explode(' ', $title); 
		foreach ($words as $key => $word) 
		{ 
			if ($key == 0 or !in_array($word, $smallwordsarray))
			{
				$words[$key] = ucwords(strtolower($word)); 
			}
		}
		
		$newtitle = implode(' ', $words); 
		return $newtitle; 
	}
	
}