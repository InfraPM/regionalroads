class AppToken{
    constructor(){
	this.token;
	this.tokenExpiry;
	this.tokenObject;
    }
    check(){
	return new Promise((resolve, reject)=>{	    
	    var url ="/appToken.php";
	    var reload;
	    var that = this;
	    if (this.token==undefined || this.tokenExpiry == undefined || this.tokenObject == undefined){	    
		reload = true;
	    }
	    else{
		var currentDate = Date.now();
		var expiryDate = Date.parse(this.tokenExpiry);
		if (currentDate > expiryDate){//expired
		    reload = true;
		}
		else{
		    reload = false;
		}
	    }
	    if (reload){
		$.ajax({
		    url: url,
		    success: function (data, status, xhr) {
			var token = data['token'];
			var tokenExpiry = data['expires'];
			that.tokenObject = data;
			that.token = token;
			that.tokenExpiry = tokenExpiry;
			resolve(true);
		    },
		    error: function (xhr, status, error) {//issue 'public token'
			that.tokenObject = {"token": "public", "expires": "2121-01-13T07:21:12+00:00"};
			that.token = that.tokenObject['token'];
			that.tokenExpiry = that.tokenObject['expires'];
			reject(false);
		    }
		});
	    }
	    else{
		resolve(true);
	    }
	});
    }
}
