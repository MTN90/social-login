//**************************************************************
//	LinkedIn
//**************************************************************

    function linkedinLogin(){
        // Guid is something we choose ourself, isnt really used atm.
        var guid = "wioqweroQ123Qqks";
        location.href = "https://www.linkedin.com/oauth/v2/authorization?response_type=code&client_id="+linkedin_api+"&redirect_uri="+linkedin_redirect_uri+"&state="+guid+"&scope=r_basicprofile%20r_emailaddress";
    }
    
    // Setup an event listener to make an API call once auth is complete
    function onLinkedInLoad() {
        IN.Event.on(IN, "auth", linkedinLogin);
    }