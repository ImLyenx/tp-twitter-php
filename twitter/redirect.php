<?php

try {
    $db = new PDO('mysql:host=localhost;dbname=twitter','root', '');
} catch (PDOException $e) {
    print "Error : " . $e->getMessage() . "<br/>";
    die();
}

// $requete = $db->prepare("SELECT * FROM Users");
// $requete->execute();
// $users = $requete->fetchAll(PDO::FETCH_ASSOC);

// foreach ($users as $user) {
//     echo $user['username'] .' '. $user['password'] .' '. $user['createdAt'] . "<br/>";
// }

// insert like 10 dummy users 
// for ($i = 0; $i < 10; $i++) {
//     $username = "user" . $i;
//     $password = "password" . $i;
//     $email = $username . "@gmail.com";
//     $requete = $db->prepare("INSERT INTO Users (email, username, password) VALUES (:email, :username, :password)");
//     $requete->bindValue(':email', $email);
//     $requete->bindValue(':username', $username);
//     $requete->bindValue(':password', $password);
//     $requete->execute();
// }

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $_POST['form'] === 'signup') {
    if (empty($_POST['username']) || empty($_POST['password'])) {
        echo "Please fill all the fields";
        return;
    }

    // Retrieve form data
    $username = $_POST['username'];
    $password = $_POST['password'];

    $username = htmlspecialchars($username, ENT_QUOTES, 'UTF-8');
    $password = htmlspecialchars($password, ENT_QUOTES, 'UTF-8');
    if (!preg_match("/^[a-zA-Z0-9_]+$/", $username)) {
        echo "Invalid username. Only letters, numbers, and underscores are allowed.";
        return;
    }

    // checks if username already exists
    $requete = $db->prepare("SELECT * FROM Users WHERE username = :username");
    $requete->bindValue(':username', $username);
    $requete->execute();
    $user = $requete->fetch(PDO::FETCH_ASSOC);
    if ($user) {
        echo "Username already exists";
        return;
    }

    // Insert form data into the database
    $requete = $db->prepare("INSERT INTO Users (username, password) VALUES (:username, :password)");
    $requete->bindValue(':username', $username);
    $requete->bindValue(':password', $password);
    $requete->execute();
    echo "User created successfully";
    session_start();
    $_SESSION['username'] = $username;
    header('Location: home.php');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $_POST['form'] === 'tweet') {
    session_start();
    if (empty($_SESSION['username']) || empty(trim($_POST['tweet']))) {
        echo "Please fill all the fields";
        return;
    }

    // Retrieve form data
    $username = $_SESSION['username'];
    $tweet = $_POST['tweet'];

    $tweet = htmlspecialchars($tweet, ENT_QUOTES, 'UTF-8');

    // get userid from username
    $requete = $db->prepare("SELECT id FROM Users WHERE username = :username");
    $requete->bindValue(':username', $username);
    $requete->execute();
    $user = $requete->fetch(PDO::FETCH_ASSOC);
    $userid = $user['id'];

    // Insert form data into the database
    $requete = $db->prepare("INSERT INTO posts (userId, content) VALUES (:userid, :content)");
    $requete->bindValue(':userid', $userid);
    $requete->bindValue(':content', $tweet);
    $requete->execute();
    echo "Tweet posted successfully";
    header('Location: home.php');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $_POST['form'] === 'login') {
    if (empty($_POST['username']) || empty($_POST['password'])) {
        echo "Please fill all the fields";
        return;
    }

    // Retrieve form data
    $username = $_POST['username'];
    $password = $_POST['password'];

    $username = htmlspecialchars($username, ENT_QUOTES, 'UTF-8');
    $password = htmlspecialchars($password, ENT_QUOTES, 'UTF-8');

    // checks if username already exists
    $requete = $db->prepare("SELECT * FROM Users WHERE username = :username AND password = :password");
    $requete->bindValue(':username', $username);
    $requete->bindValue(':password', $password);
    $requete->execute();
    $user = $requete->fetch(PDO::FETCH_ASSOC);
    if ($user) {
        echo "Login successful";
        session_start();
        $_SESSION['username'] = $username;
        header('Location: home.php');
    } else {
        echo "Invalid credentials";
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $_POST['form'] === 'logout') {
    session_start();
    session_destroy();
    header('Location: login.php');
}

// handle delete post
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $_POST['form'] === 'delete') {
    session_start();
    if (empty($_SESSION['username']) || empty($_POST['postId'])) {
        echo "Please fill all the fields";
        return;
    }

    // Retrieve form data
    $username = $_SESSION['username'];
    $postId = $_POST['postId'];
    $username = htmlspecialchars($username, ENT_QUOTES, 'UTF-8');
    $postId = htmlspecialchars($postId, ENT_QUOTES, 'UTF-8');

    // get userid from username
    $requete = $db->prepare("SELECT id FROM Users WHERE username = :username");
    $requete->bindValue(':username', $username);
    $requete->execute();
    $user = $requete->fetch(PDO::FETCH_ASSOC);
    $userid = $user['id'];

    // check if user is the author of the post
    $requete = $db->prepare("SELECT * FROM posts WHERE id = :postId AND userId = :userid");
    $requete->bindValue(':postId', $postId);
    $requete->bindValue(':userid', $userid);
    $requete->execute();
    $post = $requete->fetch(PDO::FETCH_ASSOC);
    if ($post) {
        // delete post
        $requete = $db->prepare("DELETE FROM posts WHERE id = :postId");
        $requete->bindValue(':postId', $postId);
        $requete->execute();
        echo "Post deleted successfully";
        header('Location: home.php');
    } else {
        echo "You are not the author of this post";
    }
}

// handle search
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $_POST['form'] === 'search') {
    if (empty($_POST['search'])) {
        echo "Please fill all the fields";
        return;
    }

    if (strpos($_POST['search'], '@') === 0) {
        // handle search for all tweets from user
        $user = substr($_POST['search'], 1);
        $requete = $db->prepare("SELECT id FROM Users WHERE username = :username");
        $requete->bindValue(':username', $user);
        $requete->execute();
        $user = $requete->fetch(PDO::FETCH_ASSOC);
        if ($user) {
            $userid = $user['id'];
            $requete = $db->prepare("SELECT * FROM posts WHERE userId = :userid ORDER BY createdAt DESC");
            $requete->bindValue(':userid', $userid);
            $requete->execute();
            $posts = $requete->fetchAll(PDO::FETCH_ASSOC);
            foreach ($posts as $post) {
                echo $post['content'] . "<br/>";
            }
        } else {
            echo "User not found";
        }

    } else {
        // handle search for all tweets containing the search term
    }
}

?>