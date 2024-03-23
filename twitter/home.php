<!-- tweet form -->

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="global.css">
    <title>Document</title>
</head>
<body>
    <form id="postform" class="form" action="redirect.php" method="POST">
        <input type="hidden" name="form" value="tweet">
        <label for="tweet">Tweet</label>
        <textarea name="tweet" id="tweet" maxlength="255" placeholder="What's happening ?"></textarea>
        <button type="submit">Tweet</button>
    </form>
    <form id="searchform" class="form" action="home.php" method="POST">
        <input type="hidden" name="form" value="search">
        <label for="search">Search</label><br>
        <span class="tip">To search for a specific user's tweets, use `@username`</span>
        <input type="text" name="search" id="search" placeholder="Search for tweets or a user">
        <span id="error"></span>
        <button type="submit">Search</button>
        <span class="tip">To reset search, just press the search button again !</span>
    </form>
    <?php
        session_start();
        if (isset($_SESSION['username'])) {
            echo '<form action="redirect.php" method="POST" id="logoutform">
                <input type="hidden" name="form" value="logout">
                <label for="logout">Logged in as @' . $_SESSION['username'] . '. <br /></label>
                <button type="submit">Logout</button>
                </form>';
        } else {
            header('Location: login.php');
        }

        try {
            $db = new PDO('mysql:host=localhost;dbname=twitter','root', '');
        } catch (PDOException $e) {
            print "Error : " . $e->getMessage() . "<br/>";
            die();
        }
        echo '<div id="posts">';
        $requete = $db->prepare("SELECT * FROM posts ORDER BY createdAt DESC");
        $requete->execute();
        $posts = $requete->fetchAll(PDO::FETCH_ASSOC);
        foreach ($posts as $post) {
            $userid = $post['userId'];
            $requete = $db->prepare("SELECT username FROM Users WHERE id = :userid");
            $requete->bindValue(':userid', $userid);
            $requete->execute();
            $user = $requete->fetch(PDO::FETCH_ASSOC);
            // if user is currently logged in, add a delete button
            if (isset($_SESSION['username']) && $_SESSION['username'] === $user['username']) {
                echo '<div><b>' . $user['username'] . '</b> : <form action="redirect.php" method="POST" class="deleteform">
                <input type="hidden" name="form" value="delete">
                <input type="hidden" name="postId" value="' . $post['id'] . '">
                <button type="submit"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 448 512"><!--!Font Awesome Free 6.5.1 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license/free Copyright 2024 Fonticons, Inc.--><path d="M135.2 17.7L128 32H32C14.3 32 0 46.3 0 64S14.3 96 32 96H416c17.7 0 32-14.3 32-32s-14.3-32-32-32H320l-7.2-14.3C307.4 6.8 296.3 0 284.2 0H163.8c-12.1 0-23.2 6.8-28.6 17.7zM416 128H32L53.2 467c1.6 25.3 22.6 45 47.9 45H346.9c25.3 0 46.3-19.7 47.9-45L416 128z"/></svg></button>
                </form>  <span class="timestamp" data-tooltip="' . $post['createdAt'] . '">' . time_elapsed_string($post['createdAt']) . '</span><br /> ' . $post['content'] . "</div>";
            } else {
                echo '<div><b>' . $user['username'] . '</b> : <span class="timestamp" data-tooltip="' . $post['createdAt'] . '">' . time_elapsed_string($post['createdAt']) . '</span><br /> ' . $post['content'] . '</div>';
            }
    
        }
        echo '</div>';

        // function that calculates the time elapsed since the post was created
        // not done by me, thank you stackoverflow
        function time_elapsed_string($datetime, $full = false) {
            $timezone = new DateTimeZone('Europe/Paris');

            $now = new DateTime;
            $now->setTimezone($timezone);
            // echo $now->format('Y-m-d H:i:s T') . 'now <br>';

            $ago = new DateTime($datetime);
            $ago->setTimezone($timezone);
            date_modify($ago, '-1 hour');
            // echo $ago->format('Y-m-d H:i:s T') . 'ago <br>';

            $diff = $now->diff($ago);
        
            $diff->w = floor($diff->d / 7);
            $diff->d -= $diff->w * 7;
        
            $string = array(
                'y' => 'year',
                'm' => 'month',
                'w' => 'week',
                'd' => 'day',
                'h' => 'hour',
                'i' => 'minute',
                's' => 'second',
            );
            foreach ($string as $k => &$v) {
                if ($diff->$k) {
                    $v = $diff->$k . ' ' . $v . ($diff->$k > 1 ? 's' : '');
                } else {
                    unset($string[$k]);
                }
            }
        
            if (!$full) $string = array_slice($string, 0, 1);
            return $string ? implode(', ', $string) . ' ago' : 'just now';
        }
        
        if (isset($_POST['search'])) {
            $search = $_POST['search'];
            if (strpos($search, '@') === 0) {
                $user = substr($search, 1);
                $requete = $db->prepare("SELECT id FROM Users WHERE username = :username");
                $requete->bindValue(':username', $user);
                $requete->execute();
                $userId = $requete->fetch(PDO::FETCH_ASSOC);
                if ($userId) {
                    $userid = $userId['id'];
                    $requete = $db->prepare("SELECT * FROM posts WHERE userId = :userid ORDER BY createdAt DESC");
                    $requete->bindValue(':userid', $userid);
                    $requete->execute();
                    $posts = $requete->fetchAll(PDO::FETCH_ASSOC);
                    if (empty($posts)) {
                        echo "<script>document.getElementById('error').innerHTML = 'No results found';</script>";
                    } else {
                        echo '<script>document.getElementById("posts").remove();</script>';
                        echo '<div id="posts">';
                            foreach ($posts as $post) {
                                if (isset($_SESSION['username']) && $_SESSION['username'] === $user) {
                                    echo '<div><b>' . $user . '</b> : <form action="redirect.php" method="POST" class="deleteform">
                                    <input type="hidden" name="form" value="delete">
                                    <input type="hidden" name="postId" value="' . $post['id'] . '">
                                    <button type="submit"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 448 512"><!--!Font Awesome Free 6.5.1 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license/free Copyright 2024 Fonticons, Inc.--><path d="M135.2 17.7L128 32H32C14.3 32 0 46.3 0 64S14.3 96 32 96H416c17.7 0 32-14.3 32-32s-14.3-32-32-32H320l-7.2-14.3C307.4 6.8 296.3 0 284.2 0H163.8c-12.1 0-23.2 6.8-28.6 17.7zM416 128H32L53.2 467c1.6 25.3 22.6 45 47.9 45H346.9c25.3 0 46.3-19.7 47.9-45L416 128z"/></svg></button>
                                    </form>  <span class="timestamp" data-tooltip="' . $post['createdAt'] . '">' . time_elapsed_string($post['createdAt']) . '</span><br /> ' . $post['content'] . "</div>";
                                } else {
                                    echo '<div><b>' . $user . '</b> : <span class="timestamp" data-tooltip="' . $post['createdAt'] . '">' . time_elapsed_string($post['createdAt']) . '</span><br /> ' . $post['content'] . '</div>';
                                }
                            }
                            echo '</div>';
                        }
                } else {
                    echo "<script>document.getElementById('error').innerHTML = 'User not found';</script>";
                }
            } else {
                $search = htmlspecialchars($search, ENT_QUOTES, 'UTF-8');
                $requete = $db->prepare("SELECT * FROM posts WHERE content LIKE :search ORDER BY createdAt DESC");
                $requete->bindValue(':search', '%' . $search . '%');
                $requete->execute();
                $posts = $requete->fetchAll(PDO::FETCH_ASSOC);
                if (empty($posts)) {
                    echo "<script>document.getElementById('error').innerHTML = 'No results found';</script>";
                } else {
                    echo '<script>document.getElementById("posts").remove();</script>';
                    echo '<div id="posts">';
                    foreach ($posts as $post) {
                        $userid = $post['userId'];
                        $requete = $db->prepare("SELECT username FROM Users WHERE id = :userid");
                        $requete->bindValue(':userid', $userid);
                        $requete->execute();
                        $user = $requete->fetch(PDO::FETCH_ASSOC);
                        if (isset($_SESSION['username']) && $_SESSION['username'] === $user['username']) {
                            echo '<div><b>' . $user['username'] . '</b> : <form action="redirect.php" method="POST" class="deleteform">
                            <input type="hidden" name="form" value="delete">
                        <input type="hidden" name="postId" value="' . $post['id'] . '">
                        <button type="submit"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 448 512"><!--!Font Awesome Free 6.5.1 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license/free Copyright 2024 Fonticons, Inc.--><path d="M135.2 17.7L128 32H32C14.3 32 0 46.3 0 64S14.3 96 32 96H416c17.7 0 32-14.3 32-32s-14.3-32-32-32H320l-7.2-14.3C307.4 6.8 296.3 0 284.2 0H163.8c-12.1 0-23.2 6.8-28.6 17.7zM416 128H32L53.2 467c1.6 25.3 22.6 45 47.9 45H346.9c25.3 0 46.3-19.7 47.9-45L416 128z"/></svg></button>
                        </form>  <span class="timestamp" data-tooltip="' . $post['createdAt'] . '">' . time_elapsed_string($post['createdAt']) . '</span><br /> ' . $post['content'] . "</div>";
                    } else {
                        echo '<div><b>' . $user['username'] . '</b> : <span class="timestamp" data-tooltip="' . $post['createdAt'] . '">' . time_elapsed_string($post['createdAt']) . '</span><br /> ' . $post['content'] . '</div>';
                    }
                    }
                    echo '</div>';  
                }
            }
        }
    ?>
</body>
</html>