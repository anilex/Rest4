<?php
class RestSiteDriver extends RestCompositeDriver
	{
	function prepare()
		{
		// Setting site db
		if(isset($this->core->database,$this->core->database->database))
			$this->core->db->selectDb($this->core->database->database);
		// Importing the config file
		$this->loadDatas('/mpfs/sites/'.$this->request->uriNodes[0]
			.'/system/data/config.dat?mode=append','',
			($this->loadDatas('/mpfs/sites/default/system/data/config.dat?mode=append',
				'',false)==false&&$required));
		//$this->loadSiteDatas('/system/data/config.dat?mode=append','',true); // This don't run currently but should later
		// Preparing composite structure
		parent::prepare();
		// Importing main language files
		$this->loadLocale('/public/lang/$/main.lang','',true);
		$this->loadSiteLocale('system','','',true);
		// Main modules
		$this->core->mainModules=new MergeArrayObject();
		// Site Modules : Init
		if(!isset($this->core->siteModules))
			$this->core->siteModules=new MergeArrayObject();
		foreach($this->core->siteModules as $name => $module)
			{
			$module->name=$name;
			if(!(isset($module->dir)&&$module->dir))
				$module->dir='system';
			if(!(isset($module->module)&&$module->module))
				$module->module='module';
			// Loading module config file
			$this->loadSiteDatas('/'.$module->dir.'/data/'.$module->module
				.'-config.dat?mode=append',$module,false);
			// Testing the driver name
			if((!isset($module->driver))||$module->driver==$this->request->uriNodes[2])
				{
				// Loading language file
				$this->loadSiteLocale($module->dir,$module->module,'siteModules.'.$name,false);
				// Loading resources
				if(isset($module->resources)&&$module->resources->count())
					{
					foreach($module->resources as $resource)
						{
						if(isset($resource->name)&&isset($resource->uri))
							{
							while(Template::parseVars($this->core,$resource->uri)!==-1)
								continue;
							$this->loadDatas($resource->uri,$module->{$resource->name}=new stdClass(),true);
							}
						else
							throw new RestException(RestCodes::HTTP_500,
								'Bad resource declaration in siteModule ('.$name.').');
						}
					}
				}
			}
		}
	function finish()
		{
		// Trying to set page title and description
		if(isset($this->core->i18n->mainModules[0],$this->core->i18n->mainModules[0]->title))
			$this->core->document->title=$this->core->i18n->mainModules[0]->title;
		if(isset($this->core->i18n->mainModules[0],$this->core->i18n->mainModules[0]->description))
			$this->core->document->description=$this->core->i18n->mainModules[0]->description;
		// Site Modules : Run
		foreach($this->core->siteModules as $module)
			{
			// Testing the driver name
			if((!isset($module->driver))||$module->driver==$this->request->uriNodes[2])
				{
				// Loading templates
				if(isset($module->templates)&&$module->templates->count())
					{
					foreach($module->templates as $template)
						{
						if(isset($template->name)&&$template->name)
							$template->template=$this->loadSiteTemplate('/'.$module->dir.'/'
								.$this->core->document->type.'/'.$template->name.'.tpl',
								'siteModules.'.$module->name,true);
						}
					}
				}
			}
		return new RestTemplatedResponse(
			RestCodes::HTTP_200,
			array('Content-Type'=>xcUtils::getMimeFromExt($this->core->document->type)),
			$this->loadSiteTemplate('/system/'.$this->core->document->type.'/index.tpl','',true),
			$this->core);
		}
	/* Locales management */
	function loadPublicLocale($name='',$context='',$required=false)
		{
		$this->loadLocale('/public/lang/$/'.$name.'.lang',$context,$required);
		}
	function loadDbLocale($table='',$context='',$required=false)
		{
		$this->loadLocale('/db/'.$this->core->database->database.'/default,'
			.$table.'/$.lang',$context,$required);
		}
	function loadSiteLocale($path,$name='',$context='',$required=false)
		{
		$this->loadLocale('/sites/default,'.$this->request->uriNodes[0].'/'.$path.'/lang/$.lang',
			$context, $required,($name?'-'.$name:''));
		}
	/* Site datas management */
	function loadSiteDatas($uri,$context=null,$required=false)
		{
		$this->loadDatas('/mpfs/sites/default,'.$this->request->uriNodes[0].$uri, $context, $required);
		}
	/* Templates management */
	function loadSiteTemplate($uri,$context='',$required=false)
		{
		if(!$content=$this->loadTemplate('/sites/'.$this->request->uriNodes[0].$uri,$context))
			{
			$content=$this->loadTemplate('/sites/default'.$uri,$context,$required);
			}
		return $content;
		}
	/* Errors management */
	function error($message,$debug='',$context='') // Find where it's used or remove
		{
		Varstream::set($this->core,'errors.+.message',$message);
		Varstream::set($this->core,'errors.*.context',$context);
		if($debug)
			Varstream::set($this->core,'errors.*.debugmessage',$debug);
		}
	function hasErrors($context='')
		{
		if(isset($this->core->errors)&&$this->core->errors->count())
			{
			if($context)
				{
				for($i=sizeof($this->core->errors)-1; $i>=0; $i--)
					{
					if($this->core->errors[$i]->context==$context)
						{ return true; }
					}
				return false;
				}
			return true;
			}
		return false;
		}
	function hasErrorCode($code)
		{
		if(isset($this->core->errors))
			{
			for($i=sizeof($this->core->errors)-1; $i>=0; $i--)
				{
				if($this->core->errors[$i]->code==$code)
					{ return true; }
				}
			}
		return false;
		}

	/* Notices management */
	function notice($message,$debug='',$context='') // Find where it's used or remove
		{
		Varstream::set($this->core,'notices.+.message',$message);
		if($debug)
			Varstream::set($this->core,'notices.*.debugmessage',$debug);
		}
	function hasNotices($context='')
		{
		if(isset($this->core->notices)&&sizeof($this->core->notices))
			{
			if($context)
				{
				for($i=sizeof($this->core->notices)-1; $i>=0; $i--)
					{
					if($this->core->notices[$i]->context==$context)
						{ return true; }
					}
				return false;
				}
			return true;
			}
		return false;
		}
	}
