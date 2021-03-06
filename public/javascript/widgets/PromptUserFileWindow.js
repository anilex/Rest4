var PromptUserFileWindow=new Class({
	Extends: WebWindow,
	initialize: function(desktop,options) {
		// Setting default options
		this.options.synchronize=true;
		this.options.multiple=false;
		this.options.filter='';
		this.options.output={};
		this.classNames.push('PromptUserFileWindow');
		// Initializing window
		this.parent(desktop,options);
		// Registering commands
		this.app.registerCommand('win'+this.id+'-validate',this.validateDocument.bind(this));
	},
	// Rendering window
	render : function() {
		// Rendering window
		if(!this.options.content)
			this.options.content=this.locale.content;
		//Drawing window
		this.parent();
		// Putting window content
		var tpl='<div class="box">'
			+'<form action="#win'+this.id+'-validate">'
			+'	<fieldset>'
			+'		<label>'+this.locale.label+'</label>'
			+'			<p class="fieldrow">'
			+'				<input type="file"'+(this.options.filter?' accept="'+this.options.filter+'"':'')+(this.options.multiple?' multiple="multiple"':'')+' id="win'+this.id+'-prompt" />'
			+'			</p>'
			+'	</fieldset>'
			+'	<fieldset>'
			+'		<p class="fieldrow">'
			+'			<input type="submit" title="'+this.locale.validate_tx+'" name="validate" value="'+this.locale.validate+'" />'
			+'		</p>'
			+'	</fieldset>'
			+'</form></div>';
		this.view.innerHTML=tpl;
		},
	// Confirm commands
	validateDocument: function(event)
		{
		this.options.output.files=[];
		var files=$('win'+this.id+'-prompt').files;
		if(files.length)
			{
			this.curReader=0;
			for(var i=0, j=files.length; i<j; i++)
				{
				this.options.output.files[i]={};
				
				this.options.output.files[i].fileName=($('win'+this.id+'-prompt').files[i].name).substring(0,($('win'+this.id+'-prompt').files[i].name).lastIndexOf('.')).toLowerCase().replace(/([^a-z0-9]+)/gm,'_');
				this.options.output.files[i].fileExt=($('win'+this.id+'-prompt').files[i].name).substring(($('win'+this.id+'-prompt').files[i].name).lastIndexOf('.')+1).toLowerCase().replace(/([^a-z0-9]+)/gm,'');
				this.options.output.files[i].name=this.options.output.files[i].fileName+'.'+this.options.output.files[i].fileExt;
				this.options.output.files[i].size=$('win'+this.id+'-prompt').files[i].size;
				this.options.output.files[i].type=$('win'+this.id+'-prompt').files[i].type;
				this.options.output.files[i].file=$('win'+this.id+'-prompt').files[i];
				}
			var reader = new FileReader();
			reader.onload=this.handleFiles.bind(this);
			reader.readAsDataURL($('win'+this.id+'-prompt').files[this.curReader]);
			}
		else
			{
			this.close();
			this.fireEvent('validate', [event, this.options.output]);
			}
		},
	handleFiles: function(event)
		{
		this.options.output.files[this.curReader].content=event.target.result;
		if(this.curReader<$('win'+this.id+'-prompt').files.length-1)
			{
			this.curReader++;
			var reader = new FileReader();
			reader.onload=this.handleFile.bind(this);
			reader.readAsDataURL($('win'+this.id+'-prompt').files[this.curReader]);
			}
		else
			{
			this.close();
			this.fireEvent('validate', [event, this.options.output]);
			}
		},
	// Window destruction
	destruct : function() {
		this.parent();
		}
});