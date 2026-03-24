<?php
session_start();

$error = "";
$success = "";

if(isset($_SESSION['verified'])){
    $success = $_SESSION['verified'];
    unset($_SESSION['verified']);
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email    = trim($_POST['email']);
    $password = trim($_POST['password']);

    if (empty($email) || empty($password)) {
        $error = "All fields are required.";
    } else {
        $roles = [
            "rhealenepedrosa22@gmail.com" => "barangaycaptain",
            "godsentgracesalazar@gmail.com" => "secretary",
            "fheviealivio@gmail.com" => "collector",
            "courier@example.com" => "courier",
            "ponsecakathy@gmail.com" => "admin"
        ];

        $role = $roles[$email] ?? "resident";

        $_SESSION['user_id'] = 1;
        $_SESSION['full_name'] = ucfirst(explode("@", $email)[0]);
        $_SESSION['email'] = $email;
        $_SESSION['role'] = $role;

        switch($role) {
            case "barangaycaptain": header("Location: barangaycaptain.php"); break;
            case "secretary":       header("Location: secretary.php"); break;
            case "collector":       header("Location: collector.php"); break;
            case "courier":         header("Location: CourierPage.php"); break;
            case "admin":           header("Location: admin_dashboard.php"); break;
            default:                header("Location: resident.php"); break;
        }
        exit();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Login</title>
<link rel="icon" type="image/x-icon" href="favicon_dologon.ico">
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

<style>
* { margin: 0; padding: 0; box-sizing: border-box; }

body {
    font-family: 'Poppins', sans-serif;
    background: #f5f5f5;
    min-height: 100vh;
    display: flex;
    flex-direction: column;
    align-items: center;
}

/* ── TOP IMAGE DIVIDER ── */
.top-image {
    width: 100%;
    height: 260px;
    object-fit: cover;
    object-position: center;
    display: block;
    flex-shrink: 0;
}

/* ── FORM CARD ── */
.login-card {
    width: 100%;
    max-width: 480px;
    background: #fff;
    border-radius: 24px 24px 0 0;
    margin-top: -24px;
    padding: 32px 28px 40px;
    flex: 1;
    position: relative;
    z-index: 2;
    box-shadow: 0 -4px 24px rgba(0,0,0,0.08);
}

/* Desktop */
@media (min-width: 769px) {
    body { justify-content: center; }
    .top-image {
        max-width: 480px;
        border-radius: 16px 16px 0 0;
        height: 280px;
    }
    .login-card {
        border-radius: 0 0 24px 24px;
        box-shadow: 0 8px 40px rgba(0,0,0,0.13);
    }
}

h2 {
    font-size: 26px;
    font-weight: 700;
    color: #14532d;
    margin-bottom: 26px;
}

.form-group { margin-bottom: 18px; }

.form-group label {
    display: block;
    font-size: 13px;
    font-weight: 600;
    color: #1f2937;
    margin-bottom: 7px;
}

.input-wrapper { position: relative; }

.input-wrapper i {
    position: absolute;
    left: 14px;
    top: 50%;
    transform: translateY(-50%);
    color: #14532d;
    font-size: 15px;
}

.input-wrapper input {
    width: 100%;
    padding: 13px 14px 13px 42px;
    border: 1.8px solid #e5e7eb;
    border-radius: 12px;
    font-family: 'Poppins', sans-serif;
    font-size: 14px;
    color: #111;
    outline: none;
    transition: border-color 0.3s;
    background: #fff;
}

.input-wrapper input:focus { border-color: #14532d; }

.btn-login {
    width: 100%;
    padding: 14px;
    background: linear-gradient(135deg, #1e7f45, #14532d);
    color: white;
    border: none;
    border-radius: 12px;
    font-family: 'Poppins', sans-serif;
    font-weight: 600;
    font-size: 15px;
    letter-spacing: 0.4px;
    cursor: pointer;
    margin-top: 8px;
    transition: all 0.3s ease;
    box-shadow: 0 4px 14px rgba(20, 83, 45, 0.3);
}

.btn-login:disabled {
    opacity: 0.55;
    cursor: not-allowed;
    transform: none !important;
}

.btn-login:not(:disabled):hover {
    background: linear-gradient(135deg, #239b56, #166534);
    transform: translateY(-2px);
    box-shadow: 0 6px 20px rgba(20, 83, 45, 0.4);
}

.btn-login:active { transform: scale(0.98); }

.auth-footer {
    text-align: center;
    font-size: 14px;
    color: #374151;
    margin-top: 18px;
}

.auth-footer a {
    color: #14532d;
    font-weight: 700;
    text-decoration: none;
}

.auth-footer a:hover { text-decoration: underline; }

.divider {
    display: flex;
    align-items: center;
    gap: 12px;
    margin: 22px 0 16px;
    color: #9ca3af;
    font-size: 13px;
}

.divider::before,
.divider::after {
    content: '';
    flex: 1;
    height: 1px;
    background: #e5e7eb;
}

.btn-social {
    width: 100%;
    padding: 13px 16px;
    background: #fff;
    color: #111;
    border: 1.5px solid #e5e7eb;
    border-radius: 12px;
    font-family: 'Poppins', sans-serif;
    font-weight: 500;
    font-size: 14px;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 10px;
    cursor: pointer;
    text-decoration: none;
    transition: all 0.3s ease;
    box-shadow: 0 2px 10px rgba(0,0,0,0.06);
    margin-bottom: 10px;
}

.btn-social:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 18px rgba(0,0,0,0.12);
}

.btn-social img { width: 18px; }
.btn-social .fa-facebook { color: #1877F2; font-size: 18px; }

.terms-wrapper {
    margin-top: 22px;
    font-size: 13px;
    color: #4b5563;
}

.custom-checkbox-modern {
    display: flex;
    align-items: flex-start;
    gap: 10px;
    cursor: pointer;
    user-select: none;
    line-height: 1.5;
}

.custom-checkbox-modern input[type="checkbox"] { display: none; }

.checkmark-modern {
    width: 20px;
    height: 20px;
    border: 2px solid #14532d;
    border-radius: 6px;
    background: white;
    position: relative;
    flex-shrink: 0;
    transition: all 0.3s ease;
    margin-top: 1px;
}

.custom-checkbox-modern input[type="checkbox"]:checked + .checkmark-modern {
    background: #14532d;
}

.custom-checkbox-modern input[type="checkbox"]:checked + .checkmark-modern::after {
    content: "";
    position: absolute;
    left: 5px;
    top: 1px;
    width: 6px;
    height: 12px;
    border: solid white;
    border-width: 0 2px 2px 0;
    transform: rotate(45deg);
}

.terms-text a {
    color: #14532d;
    font-weight: 600;
    text-decoration: underline;
}

.terms-info {
    font-size: 12px;
    color: #b91c1c;
    margin-top: 6px;
    display: none;
}

.error-msg {
    background: #fef2f2;
    color: #b91c1c;
    border: 1px solid #fecaca;
    border-radius: 8px;
    padding: 10px 14px;
    font-size: 13px;
    margin-bottom: 16px;
}
</style>
</head>
<body>

<!-- TOP IMAGE DIVIDER -->
<img src="CityHall.jpg" class="top-image" alt="Barangay Hall">

<!-- SIGN IN CARD -->
<div class="login-card">
    <h2>Sign In</h2>

    <?php if($error): ?>
        <div class="error-msg">
            <i class="fas fa-circle-exclamation" style="margin-right:6px;"></i>
            <?= htmlspecialchars($error) ?>
        </div>
    <?php endif; ?>

    <form method="POST" action="">
        <div class="form-group">
            <label>Email Address</label>
            <div class="input-wrapper">
                <i class="fas fa-envelope"></i>
                <input type="email" name="email" placeholder="Enter your email" required>
            </div>
        </div>
        <div class="form-group">
            <label>Password</label>
            <div class="input-wrapper">
                <i class="fas fa-lock"></i>
                <input type="password" name="password" placeholder="Enter your password" required>
            </div>
        </div>
        <button type="submit" class="btn-login" id="loginBtn" disabled>Sign In</button>
    </form>

    <p class="auth-footer">
        Don't have an account? <a href="register.php">Sign Up here</a>
    </p>

    <div class="divider">or</div>

    <a href="#" class="btn-social">
        <img src="https://www.gstatic.com/firebasejs/ui/2.0.0/images/auth/google.svg" alt="Google">
        Sign in with Google
    </a>

    <a href="#" class="btn-social">
        <i class="fab fa-facebook"></i>
        Sign in with Facebook
    </a>

    <div class="terms-wrapper">
        <label class="custom-checkbox-modern">
            <input type="checkbox" id="agreeTerms">
            <span class="checkmark-modern"></span>
            <span class="terms-text">
                By continuing, you agree to our
                <a href="#" onclick="openModal('termsModal')">Terms of Service</a>
                and have read the
                <a href="#" onclick="openModal('privacyModal')">Privacy Policy</a>.
            </span>
        </label>
        <p class="terms-info" id="termsInfo">You must agree to the Terms and Privacy Policy to proceed.</p>
    </div>
</div>

<script>
function openModal(id){ const m = document.getElementById(id); if(m) m.style.display = "block"; }
function closeModal(id){ const m = document.getElementById(id); if(m) m.style.display = "none"; }
window.onclick = function(e){
    document.querySelectorAll('.modal').forEach(modal => {
        if(e.target === modal) modal.style.display = 'none';
    });
}

const agreeCheckbox = document.getElementById('agreeTerms');
const loginBtn      = document.getElementById('loginBtn');
const termsInfo     = document.getElementById('termsInfo');

agreeCheckbox.addEventListener('change', function(){
    loginBtn.disabled = !this.checked;
    termsInfo.style.display = this.checked ? 'none' : 'block';
});
</script>

</body>
</html>
