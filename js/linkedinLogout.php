<script type="text/javascript" src="//platform.linkedin.com/in.js">
    api_key: 78uetin90s9lw4
    authorize: true
    onLoad: onLinkedInLoad
</script>
<script>

    //<-><-><-><-><-><-><-><-><-><-><-><-><-><-><-><-><-><-><-><-><-><-><->
    //      LOGOUT LINKEDIN
    //<-><-><-><-><-><-><-><-><-><-><-><-><-><-><-><-><-><-><-><-><-><-><->
    
    // Setup an event listener to make an API call once auth is complete
    function onLinkedInLoad() {
        IN.Event.on(IN, "auth", getProfileData);
    }

    // // Handle the successful return from the API call
    function onSuccess(data) {
        console.log(data);
    }

    // // Handle an error response from the API call
    function onError(error) {
        console.log(error);
    }

    // // Use the API call wrapper to request the member's basic profile data
    function getProfileData() {
        IN.API.Raw("/people/~:(id,emailAddress,firstName,lastName,picture-urls::(original))?format=json").result(onSuccess).error(onError);
    }

    // Logging out from linkedin.
    function linkedInLogout() {
        IN.User.logout(function(){
            location.href = "logout.php";
        });
    }
</script>