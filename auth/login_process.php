<?php

// Prevent browser caching
        header("Cache-Control: no-cache, no-store, must-revalidate"); // HTTP 1.1
        header("Pragma: no-cache"); // HTTP 1.0
        header("Expires: 0"); // Proxies

        
    session_start();

    

    if (basename($_SERVER['PHP_SELF']) == 'nicetry.php') {
    
        die("Access Denied: You are not permitted to view this page.");
    }
    if (isset($_SESSION['is_blocked']) && $_SESSION['is_blocked'] === true) {
        
        die("Access Denied: You are not permitted to view this page.");
    }

    include '../config/database.php';

    if ($_SERVER["REQUEST_METHOD"] == "POST") {

        $email = htmlspecialchars($_POST['email']);
        $password = htmlspecialchars($_POST['password']);

    //antiscript
        $antiscript = '/(&amp;|&quot;|&#039;|&lt;|&gt;)/';
            if( preg_match($antiscript, $email) || 
                preg_match($antiscript, $password) ){

                $_SESSION['is_blocked'] = true;
                header("Location: nicetry.php?status=nicetrylol");
                exit();
            }

    //antispace
        $nospace = '/\S/'; 
            if (!preg_match($nospace, $email) ||
                !preg_match($nospace, $password) ||
                empty($email) || 
                empty($password) ) {
                
                $_SESSION['errorMessage'] = "Please fill out the form.";
                header("Location: login.php?status=unsuccessful");
                exit();
            }

    //query
        $stmt = $conn->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();

        $user = $result->fetch_assoc();

        if (!$user) {
            $_SESSION['errorMessage'] = "Account does not exist.";
            header("Location: login.php?status=unsuccessful");
            exit();
        }

        if (!password_verify($password, $user['password'])) {
                $_SESSION['errorMessage'] = "Incorrect password.";
                header("Location: login.php?status=unsuccessful");
                exit();
        }


        if (password_verify($_POST['password'], $user['password'])) {

            // Regenerate ID to prevent session fixation attacks
                session_regenerate_id(true);
            
                $_SESSION['id'] = $user['id'];
                $_SESSION['fullname'] = $user['fullname'];
                $_SESSION['email'] = $user['email'];
                $_SESSION['role'] = $user['role'];
                $_SESSION['photo'] = $user['photo'];
                

                if($_SESSION['role'] === "admin"){
                    header("Location: ../dashboard/admin_dashboard.php");
                exit();
                } elseif($_SESSION['role'] === 'artist'){
                    header("Location: ../dashboard/artwork_feed.php");
                    exit();
                } elseif($_SESSION['role'] === 'commissioner'){
                    header("Location: ../dashboard/artwork_feed.php");
                    exit();
                }


            }  else {

                $_SESSION['errorMessage'] = "Login failed.";
                header("Location: login.php?status=unsuccessful");
                exit();;
            }
        }
    

?>
