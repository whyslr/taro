<?php
	/**
	* SmartTemplate Class
	* 
	* 'Compiles' HTML-Templates to PHP Code
	*
	*
	* Usage Example I:
	*
	* $page = new SmartTemplate( "template.html" );
	* $page->assign( 'TITLE',  'TemplateDemo - Userlist' );
	* $page->assign( 'user',   DB_read_all( 'select * from ris_user' ) );
	* $page->output();
	*	
	* Usage Example II:
	*
	* $data = array(
	*             'TITLE' => 'TemplateDemo - Userlist',  
	*             'user'  => DB_read_all( 'select * from ris_user' )
	*         );
	* $page = new SmartTemplate( "template.html" );
	* $page->output( $data );
	*
	*
	* @author Philipp v. Criegern philipp@criegern.com
	* @version 1.0.2 02.04.2003
	*/
	class core_tpl_smarttemplate
	{
		/**
		* Whether to store compiled php code or not (for debug purpose)
		*
		* @access public
		*/
		public $reuse_code = true;

		/**
		* Directory where all templates are stored
		* Can be overwritten by global configuration array $_CONFIG['template_dir']
		*
		* @access public
		*/
		public $template_dir =  '';

		/**
		* Where to store compiled templates
		* Can be overwritten by global configuration array $_CONFIG['smarttemplate_compiled']
		*
		* @access public
		*/
		public $temp_dir = 'tmp/';

		/**
		* Temporary folder for output cache storage
		* Can be overwritten by global configuration array $_CONFIG['smarttemplate_cache']
		*
		* @access public
		*/
		public $cache_dir = 'tmp/';

		/**
		* Default Output Cache Lifetime in Seconds
		* Can be overwritten by global configuration array $_CONFIG['cache_lifetime']
		*
		* @access public
		*/
		public $cache_lifetime = 600;

		/**
		* Temporary file for output cache storage
		*
		* @access private
		*/
		private $cache_filename;

		/**
		* The template filename
		*
		* @access private
		*/
		private $tpl_file;

		/**
		* The compiled template filename
		*
		* @access private
		*/
		private $cpl_file;

		/**
		* Template content array
		*
		* @access private
		*/
		private $data = array();

		/**
		* Parser Class
		*
		* @access private
		*/
		private $parser;

		/**
		* Debugger Class
		*
		* @access private
		*/
		private $debugger;

		/**
		* SmartTemplate Constructor
		*
		* @access public
		* @param string $template_filename Template Filename
		*/
		public function core_tpl_smarttemplate ( $template_filename = '' )
		{
			global $_CONFIG;

			if (!empty($_CONFIG['smarttemplate_compiled']))
			{
				$this->temp_dir  =  $_CONFIG['smarttemplate_compiled'];
			}
			
			if (!empty($_CONFIG['smarttemplate_cache']))
			{
				$this->cache_dir  =  $_CONFIG['smarttemplate_cache'];
			}
			
			if (is_numeric($_CONFIG['cache_lifetime']))
			{
				$this->cache_lifetime  =  $_CONFIG['cache_lifetime'];
			}
			
			if (!empty($_CONFIG['template_dir'])  &&  is_file($_CONFIG['template_dir'] . '/' . $template_filename))
			{
				$this->template_dir  =  $_CONFIG['template_dir'];
			}

			$this->tpl_file  =  $template_filename;
		}

		//	Methods used in older parser versions
		public function set_templatefile ($template_filename)
		{
			$this->tpl_file  =  $template_filename;
		}
		
		private function add_value ($name, $value )
		{
			$this->assign($name, $value);
		}
		
		private function add_array ($name, $value )
		{
			$this->append($name, $value);
		}

		/**
		* Assign Template Content
		*
		* Usage Example:
		* $page->assign( 'TITLE',     'My Document Title' );
		* $page->assign( 'userlist',  array(
		*                                 array( 'ID' => 123,  'NAME' => 'John Doe' ),
		*                                 array( 'ID' => 124,  'NAME' => 'Jack Doe' ),
		*                             );
		*
		* @access public
		* @param string $name Parameter Name
		* @param mixed $value Parameter Value
		* @desc Assign Template Content
		*/
		public function assign ( $name, $value = '' )
		{
			if (is_array($name))
			{
				foreach ($name as $k => $v)
				{
					$this->data[$k]  =  $v;
				}
			}
			else
			{
				$this->data[$name]  =  $value;
			}
		}


		/**
		* Assign Template Content
		*
		* Usage Example:
		* $page->append( 'userlist',  array( 'ID' => 123,  'NAME' => 'John Doe' ) );
		* $page->append( 'userlist',  array( 'ID' => 124,  'NAME' => 'Jack Doe' ) );
		*
		* @access public
		* @param string $name Parameter Name
		* @param mixed $value Parameter Value
		* @desc Assign Template Content
		*/
		public function append ( $name, $value )
		{
			if (is_array($value))
			{
				$this->data[$name][]  =  $value;
			}
			elseif (!is_array($this->data[$name]))
			{
				$this->data[$name]  .=  $value;
			}
		}
		
		/**
		* Parser Wrapper
		* Returns Template Output as a String
		*
		* @access public
		* @param array $_top Content Array
		* @return string  Parsed Template
		* @desc Output Buffer Parser Wrapper
		*/
		public function result ( $_top = '' )
		{
			ob_start();
			$this->output( $_top );
			$result  =  ob_get_contents(); 
			ob_end_clean();
			return $result;
		}


		/**
		* Execute parsed Template
		* Prints Parsing Results to Standard Output
		*
		* @access public
		* @param array $_top Content Array
		* @desc Execute parsed Template
		*/
		public function output ( $_top = '' )
		{
			//global $_top;
			
			//	Make sure that folder names have a trailing '/'
			if (strlen($this->template_dir)  &&  substr($this->template_dir, -1) != DS)
			{
				$this->template_dir  .=  DS;
			}
			if (strlen($this->temp_dir) &&  substr($this->temp_dir, -1) != DS)
			{
				$this->temp_dir .=  DS;
			}
			
			//	Prepare Template Content
			if (!is_array($_top))
			{
				if (strlen($_top))
				{
					$this->tpl_file  =  $_top;
				}
				$_top  =  $this->data;
			}
			$_obj  =  &$_top;
			
			$_stack_cnt  =  0;
			$_stack[$_stack_cnt++]  =  $_obj;
			
			//	Check if template is already compiled
	    	$this->cpl_file  =  $this->temp_dir . preg_replace('/[:\/.\\\\]/', '_', $this->tpl_file) . '.php';

			$compile_template  =  true;
			if ($this->reuse_code)
			{
				if (is_file($this->cpl_file))
				{
					if ($this->mtime($this->cpl_file) > $this->mtime($this->template_dir . $this->tpl_file))
					{
						$compile_template  =  false;
					}
				}
			}
			
			if ($compile_template)
			{
				$this->parser = new core_tpl_smarttemplateparser($this->template_dir . $this->tpl_file);
				if (!$this->parser->compile($this->cpl_file))
				{
					exit( "SmartTemplate Compiler Error: " . $this->parser->error );
				}
			}
			
			//	Execute Compiled Template
			include_once($this->cpl_file);

			//	Delete Global Content Array in order to allow multiple use of SmartTemplate class in one script
			unset ($GLOBALS["_top"]);
		}


		/**
		* Debug Template
		*
		* @access public
		* @param array $_top Content Array
		* @desc Debug Template
		*/
		public function debug ( $_top = '' )
		{
			//	Prepare Template Content
			if (!$_top)
			{
				$_top  =  $this->data;
			}

			$this->debugger = new core_tpl_smarttemplatedebugger($this->template_dir . $this->tpl_file);
			$this->debugger->start($_top);
		}


		/**
		* Start Ouput Content Buffering
		*
		* Usage Example:
		* $page = new SmartTemplate('template.html');
		* $page->use_cache();
		* ...
		*
		* @access public
		* @desc Output Cache
		*/
		public function use_cache ( $key = '' )
		{
			if (empty($_POST))
			{
				$this->cache_filename  =  $this->cache_dir . 'cache_' . md5($_SERVER['REQUEST_URI'] . serialize($key)) . '.ser';
				if (($_SERVER['HTTP_CACHE_CONTROL'] != 'no-cache')  &&  ($_SERVER['HTTP_PRAGMA'] != 'no-cache')  &&  @is_file($this->cache_filename))
				{
					if ((time() - filemtime($this->cache_filename)) < $this->cache_lifetime)
					{
						readfile($this->cache_filename);
						exit;
					}
				}
				ob_start( array( &$this, 'cache_callback' ) );
			}
		}


		/**
		* Output Buffer Callback Function
		*
		* @access private
		* @param string $output
		* @return string $output
		*/
		private function cache_callback ( $output )
		{
			if ($hd = @fopen($this->cache_filename, 'w'))
			{
				fputs($hd,  $output);
				fclose($hd);
			}
			return $output;
		}


		/**
		* Determine Last Filechange Date (if File exists)
		*
		* @access private
		* @param string $filename
		* @return mixed
		* @desc Determine Last Filechange Date 
		*/
		private function mtime ( $filename )
		{
			if (@is_file($filename))
			{
				return @filemtime($filename);
			}
		}

	}

?>