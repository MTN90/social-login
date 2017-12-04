<!-- Favicon -->
<link rel="shortcut icon" href="favicon.ico">

<!-- Launch as Webapplication Android -->
<meta name="mobile-web-app-capable" content="yes">

<!-- Launch as Webapplication Apple -->
<meta name="apple-mobile-web-app-capable" content="yes">

<!-- Apple application icon -->
<link rel="apple-touch-icon" href="img/app.png">

<!-- Android application icon -->
<link rel="shortcut icon" sizes="150x150" href="img/app.png">

<!-- Browser theme color -->
<meta name="theme-color" content="#2196F3">

<!-- responsive viewport -->
<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no" />
<!-- <meta name="viewport" content="width=device-width, initial-scale=1"> -->

<!-- Cache Control -->
<meta http-equiv="Cache-Control" content="max-age=120" />

<!-- Materialize -->
<link rel="stylesheet" href="css/materialize.min.css">

<!-- Font Awesome Online And Fallback -->
<!-- <link rel="stylesheet" href="//maxcdn.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css"> -->
<script>
  // check that fonts loaded via CDN
  function fontLoaded() {
    var cdnFont = false;
    for (var i = 0; i < document.styleSheets.length; i++) {
      var font = document.styleSheets[i];
      if (font['href'] == "//maxcdn.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css"){
        cdnFont = true;
      }
    };
    return cdnFont;
  }
  // if fonts didn't load via CDN, load from local assets directory
  if (!fontLoaded()) {
    var font = document.createElement("link");
    font.rel = "stylesheet";
    font.href = "css/font-awesome.min.css";
    document.getElementsByTagName("head")[0].appendChild(font);
  }
</script>

<!-- General styles -->
<link rel="stylesheet" href="css/style.css">