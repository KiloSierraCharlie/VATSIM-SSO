<?php
    function reportStatus( $Status, $Redirect ){
    ?>
        <style>
           #Report {
            margin-top: 6em;
            margin-left:auto;
            margin-right:auto;
            color:#990000;
            text-align:center;
            font-size:large;
            border-width:1px; 
            border-color:black; 
            background-color:#ffffee; 
            border-style:solid; 
            border-radius: 20px; 
            border-collapse: collapse; 
            width: 80%; 
            -moz-border-radius: 20px; 
            padding: 15px; 
           }
        </style>
        <html>
            <head>
                <title><?php echo $Auth_ProjectTitle; ?></title>
                <meta http-equiv="refresh" content="10;URL=<?php echo $Redirect;; ?>">
            </head>
            <body>
                <div id="Report">
                    <?php echo $Status; ?>
                </div>
                <center>
                    <p>
                    <p>
                    If this page doesn't redirect in 5 seconds, <a href="javascript: window.location.replace('<?php echo $Redirect; ?>');"> click here </a>
                </center>
            </body>
        </html>
    <?php
    }
?>
