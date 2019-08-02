<?php namespace Tatter\Files\Export;

use CodeIgniter\Config\BaseConfig;
use CodeIgniter\Config\Services;

class Exports
{
	/**
	 * The configuration instance.
	 *
	 * @var \Tatter\Files\Config\Files
	 */
	protected $config;
	
	/**
	 * Array of supported extensions and their handlers
	 *
	 * @var array
	 */
	protected $handlers;
	
	/**
	 * Array error messages assigned on failure
	 *
	 * @var array
	 */
	protected $errors;
	
	
	// initiate library
	public function __construct(BaseConfig $config)
	{		
		// Save the configuration
		$this->config = $config;
		
		// Check for cached version of discovered handlers
		$this->handlers = cache('exportHandlers');
	}
	
	// Return any error messages
	public function getErrors()
	{
		return $this->errors;
	}
/*
	// Reads a file and checks for a supported handler to create the thumbnail
	public function create(string $input, string $output)
	{
		$this->ensureHandlers();

		// Check file extensions for a valid handler
		$extension = pathinfo($input, PATHINFO_EXTENSION);
		if (empty($this->handlers[$extension])):
			$this->errors[] = lang('Thumbnails.noHandler', [$extension]);
			return false;
		endif;
		
		// Try each supported handler until one succeeds
		foreach ($this->handlers[$extension] as $class):
			$instance = new $class();
			$result = $instance->create($input, $output, $this->config->imageType, $this->config->width, $this->config->height);
			if ($result):
				break;
			endif;
		endforeach;
		
		// Check for failure
		if (! $result):
			$this->errors[] = lang('Thumbnails.handlerFail', [$input]);
			return false;
		endif;
		
		// Verify the output
		if (exif_imagetype($output) != $this->config->imageType):
			$this->errors[] = lang('Thumbnails.createFaile', [$input]);
			return false;
		endif;
		
		return true;
	}
*/
	
	// Check for all supported extensions and their handlers
	protected function ensureHandlers()
	{
		if (! is_null($this->handlers))
			return true;
		if ($cached = cache('exportHandlers'))
			return true;
		
		$locator = Services::locator(true);

		// get all namespaces from the autoloader
		$namespaces = Services::autoloader()->getNamespace();
		
		// scan each namespace for thumbnail handlers
		$flag = false;
		foreach ($namespaces as $namespace => $paths):

			// get any files in /Thumbnails/ for this namespace
			$files = $locator->listNamespaceFiles($namespace, '/Exports/');
			foreach ($files as $file):
			
				// skip non-PHP files
				if (substr($file, -4) !== '.php'):
					continue;
				endif;
				
				// get namespaced class name
				$name = basename($file, '.php');
				$class = $namespace . '\Exports\\' . $name;
				
				include_once $file;

				// validate the class
				if (! class_exists($class, false))
					continue;
				$instance = new $class();
				
				// validate the property
				if (! isset($instance->extensions))
					continue;
				
				// register each supported extension
				foreach ($instance->extensions as $extension):
					if (empty($this->handlers[$extension])):
						$this->handlers[$extension] = [$class];
					else:
						$this->handlers[$extension][] = $class;
					endif;
				endforeach;
			endforeach;
		endforeach;
		
		// Cache the results
		cache()->save('exportHandlers', $this->handlers, 300);
		
		return true;
	}
}
