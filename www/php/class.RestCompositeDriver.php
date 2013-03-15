<?php
class RestCompositeDriver extends RestDriver
	{
	function __construct(RestRequest $request)
		{
		parent::__construct($request);
		$this->core->datasLoaded=new xcObjectCollection();
		}
	function prepare()
		{
		// Getting user informations
		if($this->core->user->id&&isset($this->core->database,$this->core->database->database))
			xcDatas::loadObject($this->core->user,$this->loadResource('/db/'.$this->core->database->database.'/users/'.$this->core->user->id.'.dat?mode=fulljoin','',true)->content->entry);
		// Getting the document language and locale
		if(!isset($this->core->document))
			$this->core->document=new xcDataObject();
		$this->core->document->lang=$this->request->lang;
		$this->core->document->locale=$this->request->locale;
		$this->core->document->i18n=$this->request->i18n;
		$this->core->document->i18nFallback=($this->request->locale&&$this->request->i18n!=$this->core->server->defaultLang.'-'.$this->core->server->defaultLocale?$this->request->i18n.',':'')
			.($this->request->lang!=$this->core->server->defaultLang?$this->request->lang.',':'')
			.($this->core->server->defaultLocale?$this->core->server->defaultLang.'-'.$this->core->server->defaultLocale.',':'')
			.$this->core->server->defaultLang;
		if(!isset($this->core->i18n))
			$this->core->i18n=new xcDataObject();
		// Creating reference to uriNodes :
		$this->core->uriNodes=$this->request->uriNodes;
		// Getting the document type
		if(!$this->request->fileExt)
			throw new RestException(RestCodes::HTTP_301,'No file type given, redirecting to default file type.', '', array('Location'=>$this->core->server->location.$this->request->controller.$this->request->filePath.$this->request->fileName.'.'.$this->core->site->defaultType.($this->request->queryString?'?'.$this->request->queryString:'')));
		$this->core->document->type=$this->request->fileExt;
		if(!xcDatas::get($this->core,'types.'.$this->core->document->type))
			throw new RestException(RestCodes::HTTP_400,'Can\'t play with the given type yet: '.$this->core->document->type.'.');
		}
	// Resources load
	function loadLocale($path,$context='',$required=false,$fallbackPatch='') // Add a way to not search in the default locale.
		{
		$fallback=$this->core->document->i18nFallback;
		if($fallbackPatch===true||$fallbackPatch===false)
			throw new RestException(RestCodes::HTTP_500,'Multiple argument is deprecated ('.$path.').');
		else if($fallbackPatch)
			{
			$fallbacks=explode(',',$this->core->document->i18nFallback);
			for($i=sizeof($fallbacks)-1; $i>=0; $i--)
				$fallbacks[$i].=$fallbackPatch;
			$fallback=implode(',',$fallbacks);
			}
			
		if(!$context)
			{
			$context=$this->core->i18n;
			}
		else if(!xcDatas::get($this->core,'i18n.'.$context))
			$context=xcDatas::set($this->core,'i18n.'.$context,new xcDataObject());
		else
			$context=xcDatas::get($this->core,'i18n.'.$context);
		$path='/mmpfs'.$path;//.'?mode=first';
		if((!$found=$this->loadDatas(str_replace('$',$fallback,$path), $context, false))
			&&$required)
			throw new RestException(RestCodes::HTTP_500,'No language file available ('.$path.').');
		return $found;
		}
	function loadDatas($uri,$context=null,$required=false)
		{
		if($res=$this->loadResource($uri,$required))
			{
			if(!$context)
				$context=$this->core;
			if(!$context instanceof xcDataObject)
				throw new RestException(RestCodes::HTTP_500,'Context object is not an instance of xcDataObject.');
			if($res->content instanceof xcObjectCollection||$res->content instanceof xcDataObject)
				{
				xcDatas::loadObject($context,$res->content);
				}
			else
				{
				if($res->getHeader('Content-Type')=='application/internal'||$res->getHeader('Content-Type')=='text/lang')
					trigger_error($this->core->server->location.': CompositeDriver: '.$uri.': the response content is not a xcObjectCollection or a xcDataObject i had to convert him.');
				xcDatas::import($context,$res->content);
				}
			return true;
			}
		return false;
		}
	function loadTemplate($uri,$context='',$required=false)
		{
		if($res=$this->loadResource('/mpfs'.$uri,$required))
			return str_replace(utf8_encode('�'),$context,$res->content);
		return false;
		}
	function loadResource($uri,$required=false)
		{
		$res=new RestResource(new RestRequest(RestMethods::GET,$uri));
		$res=$res->getResponse();
		if($res->code==RestCodes::HTTP_200)
			{
			$this->core->datasLoaded->append($uri);
			return $res;
			}
		else if($required)
			throw new RestException(RestCodes::HTTP_500,'Can\'t read ressource content ('.$uri.').');
		else
			return false;
		return true;
		}
	}
?>