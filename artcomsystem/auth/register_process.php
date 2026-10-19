<?php 

// Prevent browser caching
    header("Cache-Control: no-cache, no-store, must-revalidate"); // HTTP 1.1
    header("Pragma: no-cache"); // HTTP 1.0
    header("Expires: 0"); // Proxies

session_start();



    //block potential hackers
                if (basename($_SERVER['PHP_SELF']) == 'nicetry.php') {
                
                    die("Access Denied: You are not permitted to view this page.");
                }
                if (isset($_SESSION['is_blocked']) && $_SESSION['is_blocked'] === true) {
                    
                    die("Access Denied: You are not permitted to view this page.");
                }

include '../config/database.php';    

if ($_SERVER["REQUEST_METHOD"] == "POST") {


    $email = htmlspecialchars(trim($_POST['email'] ?? ''));
    $password = htmlspecialchars($_POST['password'] ?? '');

    //check
    if(!filter_var($email, FILTER_VALIDATE_EMAIL)) {

        $_SESSION['errorMessage'] = 'Please enter a valid email address.';
        header("Location: register.php");
        exit();
    }
    if(
        empty($email) ||
        empty($password)
        ) {
            $_SESSION['errorMessage'] = 'Please fill out the form.';
            header("Location: register.php");
            exit();
            }
    

    //antiscript
                $antiscript = '/(&amp;|&quot;|&#039;|&lt;|&gt;)/';
                if( 
                    preg_match($antiscript, $email) || 
                    preg_match($antiscript, $password) 
                    ){

                        $_SESSION['is_blocked'] = true;
                        header("Location: nicetry.php?status=nicetrylol");
                        exit();
                    }
                //antispace
                $nospace = '/\S/'; 
                if (
                    !preg_match($nospace, $email) ||
                    !preg_match($nospace, $password) 
                    ) {
                    
                        $_SESSION['message'] = "Please fill out the form.";
                        header("Location: register.php?status=unsuccessful");
                        exit();
                    }


    //existing accounts check
            $checkStmt = $conn->prepare("SELECT 1 FROM users WHERE email = ? LIMIT 1");
            $checkStmt->bind_param("s", $email);
            $checkStmt->execute();
            $result = $checkStmt->get_result();

            
            if ($result->num_rows > 0) {

                $_SESSION['message'] = "Register unsuccessful. Account already exists.";
                
                $checkStmt->close();
                header("Location: register.php");
                exit();
            }
            $checkStmt->close();

            // Store credentials temporarily in session variables
        $_SESSION['temp_email'] = $email;
        // Remember to hash the password securely before saving later
        $_SESSION['temp_password'] = password_hash($password, PASSWORD_DEFAULT);

        // Redirect to Step 2
        header('Location: register_process2.php');
        exit;


    //hash password
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

    //query
    $stmt = $conn->prepare("INSERT INTO users (fullname, email, password, role) 
                            VALUES (?, ?, ?, ?)");

    //send variables to query
    $stmt->bind_param("ssss", $fullname, $email, $hashedPassword, $role);

    //execute query
    if ($stmt->execute()) {

        $stmt->close();
        $_SESSION['message'] = "Account Registered Succesfully.";
        

        exit(); 
    } else {

        $_SESSION['message'] = "Registration failed: " . $stmt->error;
        $stmt->close(); 
        header("Location: register.php");
        exit();
    }


    }   

    $conn->close();
?>