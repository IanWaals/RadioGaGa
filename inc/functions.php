<?php

// Function to create the HTML header
function htmlHead($pagetitle){
    // Get navigation items
    $navItems = getNavigation();
    ?>
    <!DOCTYPE html>
    <html lang="en">
        <head>
            <title><?php echo $pagetitle ?></title>
            <meta charset="utf-8">
            <meta name="viewport" content="width=device-width, initial-scale=1">
            <link rel="stylesheet" href="css/style.css" type="text/css" />
        </head>
        <body>
            <nav class="navContainer">
                <!-- Navigation links -->
                <a href="index.php" class="navImg"><img src="images/logo.png" width="160"></a>
                <?php
                foreach ($navItems as $navItem) {
                    ?>
                    <a href="<?php echo $navItem['pageURL'];?> " class="navLink"><?php echo $navItem['pageTitle']; ?></a>
                    <?php
                }
                ?>
            </nav>
    <?php
}

// Function to create the HTML footer
function htmlFoot(){
    ?>
        <footer class="footer">&copy; 2024 Ian Waals ROC-teraa</footer>
        </body>
    </html>
    <?php
}

// Function to connect to the database
function dbConnect(){
    // Database connection parameters
    $servername = "localhost";
    $username = "root";
    $password = "";
    $dbname = "radiogaga";

    // Create a new mysqli connection
    $conn = new mysqli($servername, $username, $password, $dbname);
    
    // Check if the connection works
    if ($conn->connect_error){
        die("connection failed: " . $conn->connect_error);
    }

    return $conn;
}

// Function to fetch the albums from the database
function getAlbums(){
    $conn = dbConnect();

    $sql = "SELECT * FROM album";

    // Query the database and get results
    $resource = $conn->query($sql) or die($conn->error);

    // Collecting all rows as separate arrays
    $albums = $resource->fetch_all(MYSQLI_ASSOC);

    return $albums;
}

// Function to fetch the tracks from the database
function getTracks(){
    $conn = dbConnect();

    $sql = "SELECT * FROM tracks";

    // Query the database and get results
    $resource = $conn->query($sql) or die($conn->error);

    // Collecting all rows as separate arrays
    $tracks = $resource->fetch_all(MYSQLI_ASSOC);

    return $tracks;
}

// Function to fetch the artists from the database
function getArtists(){
    $conn = dbConnect();

    $sql = "SELECT * FROM artists";

    // Query the database and get results
    $resource = $conn->query($sql) or die($conn->error);

    // Collecting all rows as separate arrays
    $artists = $resource->fetch_all(MYSQLI_ASSOC);

    return $artists;
}

// Function to fetch the top 2000 songs from the database
function getTop2000(){
    $conn = dbConnect();

    $sql = "SELECT * FROM brpj_top2000_2023 ORDER BY songPosition";

    // Query the database and get results
    $resource = $conn->query($sql) or die($conn->error);

    // Collecting all rows as separate arrays
    $topSongs = $resource->fetch_all(MYSQLI_ASSOC);

    return $topSongs;
}

// Function to get navigation items
function getNavigation(){
    $conn = dbConnect();

    $sql = "SELECT * FROM navigation";

    // Query the database and get results
    $resource = $conn->query($sql) or die($conn->error);

    // Collecting all rows as separate arrays
    $navItems = $resource->fetch_all(MYSQLI_ASSOC);

    return $navItems;
}

// Function to display the body of the radio page
function displayAlbums(){
    // Get albums and tracks
    $albums = getAlbums();
    $tracks = getTracks();

    // Display the division with the album images
    echo "<div class='Playlists'>";
    foreach($albums as $album){
        ?>
    <div class="albums" id="<?php echo $album['albumStyle']; ?>">
        <a href="?album=<?php echo $album['albumId']; ?>"><img src="images/<?php echo $album['albumImage']; ?>" width="145" /></a>
    </div>
    <?php
    }
    echo "</div>";

    // Check if an album has been clicked
    $selectedAlbumId = isset($_GET['album']) ? $_GET['album'] : null;

    foreach($albums as $album){
        // Display the clicked album
        if ($album['albumId'] == $selectedAlbumId) {
            ?>
            <div class="Inside">
                <a href="<?php echo $album['albumLink']; ?>" target="_blank"><h2><?php echo $album['albumTitle']; ?></h2></a>
                <video width="430" controls>
                    <source src="videos/<?php echo $album['albumVideo']; ?>" type="video/mp4">
                </video>
                <table>
                    <!-- Table header -->
                    <tr>
                        <th>Track</th>
                        <th>Title</th>
                        <th>Duration</th>
                        <th>Play</th>
                    </tr>
                    <?php
                    $albumId = $album['albumId'];
                    $albumTracks = array_filter($tracks, function($track) use ($albumId) {
                        return $track['albumId'] == $albumId;
                    });

                    // Display the rest of the track info in the table
                    foreach($albumTracks as $track){ ?>
                        <tr>
                            <td><?php echo $track['trackId']; ?></td>
                            <td><?php echo $track['trackTitle']; ?></td>
                            <td><?php echo $track['trackDuration']; ?></td>
                            <td>
                                <audio class="songs" controls>
                                    <source src="audios/<?php echo $track['trackFile']; ?>" type="audio/mpeg">
                                </audio>
                            </td>
                        </tr>
                    <?php } ?>
                </table>
            </div>
            <?php
            break;
        }
    }
}

// Function to display artists
function displayArtists(){
    // Retrieve the artists info
    $artists = getArtists();
    ?>
    <h1>Artists</h1>
    <div class="artistsContainer">
        <?php
        foreach($artists as $artist) {
            ?>
            <div class="artistDiv" id="<?php echo $artist['artistStyleID']; ?>">
                <a href="<?php echo $artist['artistLink']; ?>" target="_blank"><h2><?php echo $artist['artistName']; ?></h2></a>
                <?php echo $artist['artistDetails']; ?>
                <img src="images/<?php echo $artist['artistImage']; ?>" alt="An image of <?php echo $artist['artistName']; ?>" class="artistPhoto" width="200">
                <ol>
                    <li><?php echo $artist['artistSong1'] ?></li>
                    <li><?php echo $artist['artistSong2'] ?></li>
                    <li><?php echo $artist['artistSong3'] ?></li>
                </ol>
            </div>
            <?php
        }
        ?>
    </div>
    <?php
}

// Function to display the top 2000 songs
function top2000() {
    session_start(); // Start the session

    // Initialize $antiTaylor if it's not already set
    if (!isset($_SESSION['antiTaylor'])) {
        $_SESSION['antiTaylor'] = "no";
    }

    $search = "";

    if (isset($_POST['search'])) {
        $search = strval($_POST["search"]);
    }

    $topSongs = getTop2000();
    $buttonText = ($_SESSION['antiTaylor'] == "no") ? "add all Taylor Swift songs" : "remove all Taylor Swift songs";
    ?>
    <h1>Top 2000</h1>
    <div class="tableContainer">
        <div class="tableDiv">
            <form action="" method="post">
                <input type="text" name="search" class="formInputSmall" placeholder="Search by artist"><br>
                <input type="submit" name="submitSearch" class="enterButton" value="search">
                <input type="submit" name="resetSearch" class="enterButton" value="reset"><br><br>
            </form>
            <form action="" method="post">
                <input type="hidden" name="antiTaylor" value="<?php echo $_SESSION['antiTaylor'] ?>">
                <button class="noTaylor" type="submit"><?php echo $buttonText ?></button>
            </form>
            <?php
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                // Toggle the value of antiTaylor
                $_SESSION['antiTaylor'] = ($_SESSION['antiTaylor'] == "no") ? "yes" : "no";
                // Update $buttonText after toggling
                $buttonText = ($_SESSION['antiTaylor'] == "no") ? "add all Taylor Swift songs" : "remove all Taylor Swift songs";
            }
            ?>
            <table>
                <tr>
                    <th>Song position</th>
                    <th>Song title</th>
                    <th>Song artist</th>
                    <th>Song year</th>
                </tr>
                <?php
                foreach ($topSongs as $topSong) {
                    // If antiTaylor is yes, and song artist is Taylor Swift, skip this song
                    if ($_SESSION['antiTaylor'] == "yes" && $topSong['songArtist'] == "Taylor Swift") {
                        continue;
                    }
                    if (isset($_POST['submitSearch'])) {
                        if ($search != $topSong['songArtist']) {
                            continue;
                        }
                    }
                    ?>
                    <tr>
                        <td><?php echo $topSong['songPosition'] ?></td>
                        <td><?php echo $topSong['songTitle'] ?></td>
                        <td><?php echo $topSong['songArtist'] ?></td>
                        <td><?php echo $topSong['songYear'] ?></td>
                    </tr>
                    <?php
                }
                ?>
            </table>
        </div>
    </div>

    <?php
}

// Function to display contact form
function contactHTML(){
    $firstName = "";
    $lastName = "";
    $resultContent = "";

    // Check if form submitted
    if (isset($_POST["firstName"]) && isset($_POST["lastName"]) && isset($_POST["message"])) {
        $firstName = strval($_POST["firstName"]);
        $lastName = strval($_POST["lastName"]);
    }

    ?>
    <h1>Contact</h1>
    <div class="contactContainer">
        <div class="contactForm">
            <!-- create the form -->
            <form method="post" name="contact" action="">
                <label for="firstName" for="lastName">Your full name</label><br>
                <input type="text" name="firstName" class="formInputSmall" placeholder="Your first name">
                <input type="text" name="lastName" class="formInputSmall" placeholder="Your last name"><br>
                <label for="mailAdress" for="phoneNumber">Your e-mail adress or phone number (fill out either one)</label><br>
                <input type="email" name="mailAdress" class="formInputSmall" placeholder="Your email adress">
                <input type="tel" name="phoneNumber" class="formInputSmall" placeholder="Your phone number"><br>
                <label for="message">Type your message below</label><br>
                <textarea name="message" id="formInputBig" placeholder="message" cols="54" rows="8"></textarea><br>
                <input type="submit" name="submitForm" class="enterButton" value="submit form">
                <input type="submit" name="resetForm" class="enterButton" value="reset form">
            </form>

            <?php 
            // Display result
            if (!empty($resultContent)) {
                echo $resultContent;
            }
            else {
                if(!empty($firstName) || !empty($lastName)) {
                    echo "Thank you " . $firstName . " " . $lastName . " for submitting a message.";
                }
            }
            ?>
        </div>
    </div>
    
    <?php
}

?>