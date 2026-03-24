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
    "ponsecakathy@gmail.com" => "admin",
    "jyanson@aclcbukidnon.com" => "client" // <-- add this line
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
    case "client":          header("Location: client_dashboard.php"); break; // <-- new case
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
<title>Barangay Projects Portal</title>
<link rel="icon" type="image/x-icon" href="favicon_dologon.ico">
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

<style>
*, *::before, *::after { margin: 0; padding: 0; box-sizing: border-box; }

body {
    font-family: 'Poppins', sans-serif;
    height: 100vh;
    overflow: hidden;
    position: relative;
}

/* ===== FULL-SCREEN BG VIDEO ===== */
.bg-video {
    position: fixed;
    inset: 0;
    width: 100%;
    height: 100%;
    object-fit: cover;
    z-index: 0;
}

/* Dark overlay over entire video */
.bg-overlay {
    position: fixed;
    inset: 0;
    background: rgba(0, 0, 0, 0.50);
    z-index: 1;
}

/* ===== MAIN LAYOUT ===== */
.container {
    position: relative;
    z-index: 2;
    display: flex;
    width: 100%;
    height: 100vh;
    align-items: center;
}

/* ===== LEFT — BRANDING ===== */
.left {
    flex: 1;
    display: flex;
    flex-direction: column;
    justify-content: center;
    align-items: center;
    text-align: center;
    padding: 60px 40px;
    color: white;
}

/* Logo */
.logo-wrap {
    width: 200px; /* bigger logo */
    height: 200px;
    margin: 0 auto 28px;
    perspective: 1000px;
}
.logo-wrap img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    border-radius: 50%;
    border: 4px solid rgba(255,255,255,0.85);
    box-shadow: 0 0 30px rgba(255,255,255,0.25);
    animation: flip 15s infinite linear; /* slower rotation */
}
@keyframes flip {
    0%   { transform: rotateY(0deg); }
    50%  { transform: rotateY(180deg); }
    100% { transform: rotateY(360deg); }
}

/* Portal title */
.portal-title {
    font-size: 34px;
    font-weight: 800;
    line-height: 1.2;
    letter-spacing: 0.5px;
    text-shadow: 0 2px 12px rgba(0,0,0,0.5);
    margin-bottom: 14px;
}
.portal-title span {
    display: block;
    color: #6ee7a0;
}

.portal-sub {
    font-size: 14px;
    font-weight: 300;
    color: rgba(255,255,255,0.75);
    max-width: 300px;
    line-height: 1.7;
    text-shadow: 0 1px 6px rgba(0,0,0,0.4);
}

/* Decorative divider */
.divider-line {
    width: 60px;
    height: 3px;
    background: linear-gradient(to right, #6ee7a0, transparent);
    border-radius: 999px;
    margin: 18px auto;
}

/* ===== RIGHT — GLASS SIGN IN ===== */
.right {
    flex: 1;
    display: flex;
    justify-content: center;
    align-items: center;
    padding: 40px 20px;
}

/* GLASS CARD */
.login-box {
    width: 100%;
    max-width: 400px;
    padding: 44px 38px 38px;
    background: rgba(255, 255, 255, 0.10);
    backdrop-filter: blur(22px) saturate(180%);
    -webkit-backdrop-filter: blur(22px) saturate(180%);
    border-radius: 22px;
    border: 1px solid rgba(255, 255, 255, 0.25);
    box-shadow:
        0 8px 40px rgba(0, 0, 0, 0.40),
        inset 0 1px 0 rgba(255,255,255,0.20);
    animation: fadeUp 0.65s cubic-bezier(.16,1,.3,1) both;
}

@keyframes fadeUp {
    from { opacity: 0; transform: translateY(28px); }
    to   { opacity: 1; transform: translateY(0); }
}

.login-box h2 {
    margin-bottom: 8px;
    font-size: 26px;
    font-weight: 700;
    color: #ffffff;
    letter-spacing: 0.3px;
    text-shadow: 0 1px 6px rgba(0,0,0,0.35);
}

.login-box .subtitle {
    font-size: 13px;
    color: rgba(255,255,255,0.55);
    margin-bottom: 28px;
}

.error-msg {
    color: #fca5a5;
    font-size: 13px;
    margin-bottom: 14px;
    display: flex;
    align-items: center;
    gap: 6px;
}

/* Form */
.form-group { margin-bottom: 18px; }
.form-group label {
    font-size: 12.5px;
    font-weight: 500;
    color: rgba(255,255,255,0.80);
    display: block;
    margin-bottom: 7px;
    letter-spacing: 0.3px;
}

.input-wrapper { position: relative; }
.input-wrapper i {
    position: absolute;
    left: 13px; top: 50%;
    transform: translateY(-50%);
    color: rgba(255,255,255,0.55);
    font-size: 14px;
    pointer-events: none;
}
.input-wrapper input {
    width: 100%;
    padding: 12px 14px 12px 40px;
    background: rgba(255, 255, 255, 0.12);
    border: 1px solid rgba(255, 255, 255, 0.25);
    border-radius: 10px;
    outline: none;
    color: #ffffff;
    font-family: 'Poppins', sans-serif;
    font-size: 14px;
    transition: all 0.3s ease;
}
.input-wrapper input::placeholder { color: rgba(255,255,255,0.40); }
.input-wrapper input:focus {
    border-color: rgba(110,231,160,0.70);
    background: rgba(255, 255, 255, 0.18);
    box-shadow: 0 0 0 3px rgba(110,231,160,0.12);
}

/* Button */
.btn-login {
    width: 100%;
    padding: 13px 16px;
    background: linear-gradient(135deg, #1e7f45, #14532d);
    color: white;
    border: 1px solid rgba(255,255,255,0.15);
    border-radius: 12px;
    font-weight: 600;
    font-size: 15px;
    letter-spacing: 0.5px;
    cursor: pointer;
    margin-top: 18px;
    transition: all 0.3s ease;
    box-shadow: 0 4px 18px rgba(0,0,0,0.30);
    font-family: 'Poppins', sans-serif;
}
.btn-login:hover {
    background: linear-gradient(135deg, #239b56, #166534);
    transform: translateY(-2px);
    box-shadow: 0 6px 24px rgba(0,0,0,0.40);
}
.btn-login:active { transform: scale(0.98); }
.btn-login:disabled { opacity: 0.45; cursor: not-allowed; transform: none; }

/* Terms */
.terms-wrapper { margin-top: 22px; font-size: 12.5px; color: rgba(255,255,255,0.70); }
.custom-checkbox-modern { display: flex; align-items: flex-start; gap: 10px; cursor: pointer; user-select: none; line-height: 1.5; }
.custom-checkbox-modern input[type="checkbox"] { display: none; }
.checkmark-modern {
    width: 18px; height: 18px;
    border: 2px solid rgba(255,255,255,0.45);
    border-radius: 5px;
    background: rgba(255,255,255,0.08);
    position: relative; flex-shrink: 0;
    margin-top: 1px;
    transition: all 0.3s ease;
}
.custom-checkbox-modern:hover .checkmark-modern { box-shadow: 0 0 0 3px rgba(110,231,160,0.15); }
.custom-checkbox-modern input[type="checkbox"]:checked + .checkmark-modern { background: #1e7f45; border-color: #1e7f45; }
.custom-checkbox-modern input[type="checkbox"]:checked + .checkmark-modern::after {
    content: ""; position: absolute;
    left: 4px; top: 0px;
    width: 5px; height: 10px;
    border: solid white; border-width: 0 2px 2px 0;
    transform: rotate(45deg);
}
.terms-text a { color: #6ee7a0; text-decoration: underline; font-weight: 500; }
.terms-info { font-size: 11.5px; color: #fca5a5; margin-top: 6px; display: none; }

/* Modal */
.modal { display:none; position:fixed; z-index:9999; inset:0; background:rgba(0,0,0,0.65); }
.modal-content { background:#fff; margin:5% auto; padding:30px; width:90%; max-width:800px; border-radius:14px; max-height:80vh; overflow-y:auto; box-shadow:0 10px 30px rgba(0,0,0,0.3); }
.close-btn { float:right; font-size:22px; cursor:pointer; font-weight:bold; }
.close-btn:hover { color:red; }

/* ===== RESPONSIVE ===== */

/* Tablet landscape & small desktops */
@media(max-width: 1024px) {
    .portal-title { font-size: 28px; }
    .portal-sub { font-size: 13px; }
    .logo-wrap { width: 160px; height: 160px; }
    .login-box { padding: 36px 28px 30px; }
}

/* Tablet portrait */
@media(max-width: 768px) {
    body { overflow-y: auto; }

    .container {
        flex-direction: column;
        justify-content: flex-start;
        align-items: center;
        height: auto;
        min-height: 100vh;
        padding: 40px 20px 50px;
        gap: 0;
    }

    .left {
        flex: none;
        width: 100%;
        padding: 30px 20px 24px;
        flex-direction: row;
        justify-content: center;
        align-items: center;
        gap: 18px;
        text-align: left;
    }

    .logo-wrap {
        width: 100px;
        height: 100px;
        flex-shrink: 0;
        margin: 0;
    }

    .left-text { display: flex; flex-direction: column; }

    .portal-title {
        font-size: 20px;
        margin-bottom: 0;
    }
    .portal-title span { font-size: 14px; }

    .divider-line { display: none; }
    .portal-sub { font-size: 12px; margin-top: 4px; max-width: 240px; }

    .right {
        flex: none;
        width: 100%;
        padding: 10px 20px 30px;
    }

    .login-box {
        max-width: 100%;
        padding: 30px 24px 26px;
        border-radius: 18px;
    }

    .login-box h2 { font-size: 22px; }
    .login-box .subtitle { font-size: 12px; margin-bottom: 22px; }

    .form-group { margin-bottom: 14px; }
    .input-wrapper input { font-size: 14px; padding: 11px 14px 11px 38px; }
    .btn-login { padding: 12px; font-size: 14px; margin-top: 14px; }
    .terms-wrapper { font-size: 12px; margin-top: 18px; }
}

/* Small phones */
@media(max-width: 480px) {
    .container { padding: 24px 14px 40px; }

    .left {
        flex-direction: column;
        text-align: center;
        gap: 12px;
        padding: 24px 14px 20px;
    }

    .logo-wrap { width: 120px; height: 120px; margin: 0 auto; }

    .portal-title { font-size: 21px; text-align: center; }
    .portal-title span { font-size: 15px; }
    .divider-line { display: block; margin: 10px auto 8px; }
    .portal-sub { font-size: 12px; text-align: center; max-width: 260px; margin: 0 auto; }

    .right { padding: 8px 14px 30px; }
    .login-box { padding: 26px 18px 22px; border-radius: 16px; }
    .login-box h2 { font-size: 20px; }
    .input-wrapper input { font-size: 13px; }
    .btn-login { font-size: 13px; }
    .terms-wrapper { font-size: 11.5px; }
}

/* Very small phones (e.g. iPhone SE) */
@media(max-width: 360px) {
    .portal-title { font-size: 18px; }
    .login-box { padding: 22px 14px 20px; }
    .login-box h2 { font-size: 18px; }
}
</style>
</head>
<body>

<!-- FULL-SCREEN VIDEO BG -->
<video class="bg-video" autoplay muted loop playsinline>
    <source src="eng.mp4" type="video/mp4">
</video>
<div class="bg-overlay"></div>

<div class="container">

    <!-- LEFT — BRANDING -->
    <div class="left">
        <div class="logo-wrap">
            <img src="cityengineerlogo.jpg" alt="Barangay Logo">
        </div>
        <div class="left-text">
            <h1 class="portal-title">
                Ato ni!
                <span>Barangay Projects Portal</span>
            </h1>
            <div class="divider-line"></div>
            <p class="portal-sub">Transparently tracking every project, every peso — for our community.</p>
        </div>
    </div>

    <!-- RIGHT — SIGN IN -->
    <div class="right">
        <div class="login-box">
            <h2>Sign In</h2>
            <p class="subtitle">Welcome back! Please enter your details.</p>

            <?php if($error) echo "<p class='error-msg'><i class='fas fa-exclamation-circle'></i> $error</p>"; ?>

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
                <p class="terms-info">You must agree to the Terms and Privacy Policy to proceed.</p>
            </div>
        </div>
    </div>

</div>

<!-- Modals -->
<div id="termsModal" class="modal">
    <div class="modal-content">
        <span class="close-btn" onclick="closeModal('termsModal')">&times;</span>
        <h3>Terms of Service</h3>
        <p style="margin-top:12px;color:#4b5563;font-size:14px;">Your terms of service content goes here.</p>
    </div>
</div>

<div id="privacyModal" class="modal">
    <div class="modal-content">
        <span class="close-btn" onclick="closeModal('privacyModal')">&times;</span>
        <h3>Privacy Policy</h3>
        <p style="margin-top:12px;color:#4b5563;font-size:14px;">Your privacy policy content goes here.</p>
    </div>
</div>

<script>
function openModal(id){ document.getElementById(id).style.display = "block"; }
function closeModal(id){ document.getElementById(id).style.display = "none"; }
window.onclick = function(e){
    document.querySelectorAll('.modal').forEach(m => { if(e.target === m) m.style.display = 'none'; });
}

const agreeCheckbox = document.getElementById('agreeTerms');
const loginBtn      = document.getElementById('loginBtn');
const termsInfo     = document.querySelector('.terms-info');

agreeCheckbox.addEventListener('change', function(){
    loginBtn.disabled = !this.checked;
    termsInfo.style.display = this.checked ? 'none' : 'block';
});
</script>

</body>
</html>