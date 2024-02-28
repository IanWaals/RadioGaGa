<?php
    include("inc/functions.php");

    htmlHead("Homepage");

    ?>
    <h1>Homepage</h1>
    <div class="homepageContainer">
        <div  id="homepageTitle">
            welcome to RadioGaGa <br><br>
        </div>
        <div id="homepageText">
            click the button below to listen to some tunes
        </div>
        <a id="homepageRadioButton" href="radio.php">
            <div>Go to the radioooo!</div>
        </a>
    </div>

    <?php

    htmlFoot();
?>