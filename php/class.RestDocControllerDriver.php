<?php
class RestDocControllerDriver extends RestSiteDriver
	{
	static $drvInf;
	static function getDrvInf($methods=0)
		{
		$drvInf=new stdClass();
		$drvInf->name='Doc: Controller Driver';
		$drvInf->description='Show details of the selected controller.';
		$drvInf->usage='/doc/{user.i18n}/controller/(name).{document.type}';
		$drvInf->methods=new stdClass();
		$drvInf->methods->options=new stdClass();
		$drvInf->methods->options->outputMimes='text/varstream';
		$drvInf->methods->head=$drvInf->methods->get=new stdClass();
		$drvInf->methods->get->outputMimes='text/html';
		return $drvInf;
		}
	function get()
		{
		$this->prepare();
		$mainModule=new stdClass();
		$mainModule->template=$this->loadTemplate('/sites/doc/controller/'
			.$this->core->document->type.'/index.tpl','mainModules.0',true);
		$this->loadLocale('/sites/'.$this->request->uriNodes[0]
			.($this->request->uriNodes[0]!='doc'?',doc':'')
			.',default/controller/lang/$.lang', 'mainModules.0', true);
		$mainModule->values=new MergeArrayObject();
		$this->loadDatas('/mpfsi/php.dat',$files=new stdClass(),true);
		if(isset($files->files)&&$files->files->count())
			{
			foreach($files->files as $file)
				{
				if(strpos($file->name,'class.Rest'.$this->request->uriNodes[3])===0
					&&strpos($file->name,'Driver.php')===strlen($file->name)-10)
					{
					$name=substr($file->name,strlen('class.Rest'.$this->request->uriNodes[3]),
						strlen($file->name)-strlen('class.Rest'.$this->request->uriNodes[3])-10);
					if($name===ucfirst($name))
						{
						$entry=new stdClass();
						$entry->name=$this->request->uriNodes[3].$name;
						$entry->label=$theClass='Rest'.$this->request->uriNodes[3].$name.'Driver';
						if($drvInf=$theClass::getDrvInf())
							{
							$entry->description=(isset($drvInf->description)?
								$drvInf->description:'Not documented !');
							$entry->methods=RestMethods::getStringFromMethod(RestMethods::OPTIONS)
								.(isset($drvInf->methods)?
									(isset($drvInf->methods->get)?
										','.RestMethods::getStringFromMethod(RestMethods::GET):'')
									.(isset($drvInf->methods->post)?
										','.RestMethods::getStringFromMethod(RestMethods::POST):'')
									.(isset($drvInf->methods->put)?
										','.RestMethods::getStringFromMethod(RestMethods::PUT):'')
									.(isset($drvInf->methods->delete)?
										','.RestMethods::getStringFromMethod(RestMethods::DELETE):'')
									.(isset($drvInf->methods->patch)?
										','.RestMethods::getStringFromMethod(RestMethods::PATCH):'')
								:'');
							}
						else
							$entry->description='Extend only';
						$mainModule->values->append($entry);
						}
					}
				}
			}
		$source=$this->loadResource('/mpfs/php/class.Rest'.$this->request->uriNodes[3].'Controller.php',true);
		$mainModule->source=xcUtilsInput::filterAsCdata($source->getContents());
		$this->core->mainModules->append($mainModule);
		$this->core->layoutType='large';
		return $this->finish();
		}
	}
