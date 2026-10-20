
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Home</title>

    <link rel="stylesheet" href="css/style.css">
</head>

<body>

    <!-- Navigation -->
    <header class="navbar">

        <a href="index.php" class="logo">
            Pawcoms
        </a>

        <nav class="nav-links">
            <a href="index.php">Home</a>
            <a href="auth/login.php">Login</a>
            <a href="auth/register.php" class="register-nav">
                Register
            </a>
        </nav>

    </header>


    <!-- Main Hero -->
    <main class="hero">

        <section class="hero-content">

            <p class="subtitle">
                WELCOME TO
            </p>

            <h1>Pawcoms</h1>

            <p class="description">
                Join Pawcoms today and discover talented artists, commission unique artwork, and bring your creative ideas to life!
            </p>

            <div class="hero-buttons">

                <a href="auth/register.php" class="btn btn-primary">
                    Create an Account
                </a>

                <a href="auth/login.php" class="btn btn-secondary">
                    Login
                </a>

            </div>

        </section>

    </main>


    <!-- Information Section -->
    <section class="info-section">

        <div class="info-card">

            <h2>Create an Account</h2>

            <p>
                Register for an account and become part of our pawcoms.
            </p>

        </div>


        <div class="info-card">

            <h2>Access Your Account</h2>

            <p>
                Log in securely and access the features available to you.
            </p>

        </div>


        <div class="info-card">

            <h2>Manage Your Profile</h2>

            <p>
                Keep your account information updated anytime.
            </p>

        </div>

    </section>


    <!-- Footer -->
    <footer>

        <p>
            &copy; <?php echo date('Y'); ?> MyWebsite. All rights reserved.
        </p>

    </footer>

</body>
</html>

