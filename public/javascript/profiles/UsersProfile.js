var UsersProfile=new Class({
	classNames:[],
	initialize: function(app)
		{
		this.classNames.push('UsersProfile');
		this.app=app;
		this.locales=[];
		for(var i=this.classNames.length-1; i>=0; i--)
			{
			this.app.loadLocale(this.classNames[i],this.loaded.bind(this));
			}
		},
	loaded: function()
		{
		var allLoaded=true;
		for(var i=this.classNames.length-1; i>=0; i--)
			{
			if((!this.locales[this.classNames[i]])&&this.app.locales[this.classNames[i]])
				this.locales[this.classNames[i]]=this.app.locales[this.classNames[i]];
			else if(!this.locales[this.classNames[i]])
				allLoaded=false;
			}
		if(allLoaded)
			{
			this.locale=this.app.locales[this.classNames[0]];
			this.prepare();
			this.app.start();
			}
		},
	prepare : function (context) {
		// Registering commands
		this.app.registerCommand('openWindow',this.openWindowCommand.bind(this));
		},
	openWindowCommand : function (event,params) {
		var obj={}, i=1;
		while(params[i]&&params[i+1])
			{
			obj[params[i]]=params[i+1];
			i=i+2;
			}
		this.app.createWindow(params[0]+'Window',obj);
		}
});