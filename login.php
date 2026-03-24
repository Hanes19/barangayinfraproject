<?php
session_start();

$error = "";
$success = "";

// Handle email verified message
if(isset($_SESSION['verified'])){
    $success = $_SESSION['verified'];
    unset($_SESSION['verified']);
}

// -------------------- HARD-CODED LOGIN (NO DB) --------------------
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email    = trim($_POST['email']);
    $password = trim($_POST['password']); // optional

    if (empty($email) || empty($password)) {
        $error = "All fields are required.";
    } else {
        // Hardcoded email-to-role map
        $roles = [
            "rhealenepedrosa22@gmail.com" => "barangaycaptain",
            "godsentgracesalazar@gmail.com" => "secretary",
            "fheviealivio@gmail.com" => "collector",
            "courier@example.com" => "courier" // replace with actual courier email
        ];

        $role = $roles[$email] ?? "resident";

        // Set session variables
        $_SESSION['user_id'] = 1; // dummy ID
        $_SESSION['full_name'] = ucfirst(explode("@", $email)[0]);
        $_SESSION['email'] = $email;
        $_SESSION['role'] = $role;

        // Redirect based on role
        switch($role) {
            case "barangaycaptain": header("Location: barangaycaptain.php"); break;
            case "secretary": header("Location: secretary.php"); break;
            case "collector": header("Location: collector.php"); break;
            case "courier": header("Location: CourierPage.php"); break;
            default: header("Location: resident.php"); break;
        }
        exit();
    }
}

// -------------------- SOCIAL LOGIN FLAGS --------------------
$oauth_configured = false;
$google_enabled = true;   // keep true to show Google button
$facebook_enabled = true; // keep true to show Facebook button
$microsoft_enabled = true;// keep true to show Microsoft button

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
/* -------------------- CSS -------------------- */
*{margin:0;padding:0;box-sizing:border-box;}
body {font-family: 'Poppins', sans-serif;height: 100vh;display: flex;margin: 0;}
.btn-social{width: 100%;padding: 12px 16px;background: white;color: black;border: 1.5px solid #e5e7eb;border-radius: 12px;font-weight: 500;display: flex;align-items: center;justify-content: center;gap: 10px;cursor: pointer;text-decoration: none;transition: all 0.3s ease;box-shadow: 0 4px 14px rgba(0,0,0,0.08);margin-top: 10px;}
.btn-facebook i{color: #1877F2;}
.btn-social:hover{transform: translateY(-2px);box-shadow: 0 6px 20px rgba(0,0,0,0.15);}
.btn-social i{font-size: 18px;}
.left{flex:1;background: url('CityHall.jpg') no-repeat center center;background-size: cover;color:white;display:flex;justify-content:center;align-items:center;text-align:center;padding:40px;}
.container{display:flex;width:100%;}
.left-content h1{font-size:30px;margin:20px 0 10px;}
.left-content p{font-size:14px;opacity:0.9;margin-bottom:15px;}
.logo{width:130px;height:130px;margin:auto;perspective:1000px;}
.logo img{width:100%;height:100%;object-fit:cover;border-radius:50%;border:4px solid white;animation:flip 4s infinite linear;}
@keyframes flip{0%{transform:rotateY(0);}50%{transform:rotateY(180deg);}100%{transform:rotateY(360deg);}}
.right{flex:1;background:#f9fafb;display:flex;justify-content:center;align-items:center;}
.login-box{width:100%;max-width:380px;}
.login-box h2{margin-bottom:25px;color:#14532d;}
.form-group{margin-bottom:18px;}
.form-group label{font-size:14px;font-weight:500;}
.input-wrapper{position:relative;}
.input-wrapper i{position:absolute;left:12px;top:50%;transform:translateY(-50%);color:#14532d;}
.input-wrapper input{width:100%;padding:12px 12px 12px 38px;border:2px solid #e5e7eb;border-radius:8px;outline:none;transition:0.3s;}
.input-wrapper input:focus{border-color:#14532d;}
.btn-login{width:100%;padding:14px 16px;background: linear-gradient(135deg, #1e7f45, #14532d);color:white;border:none;border-radius:12px;font-weight:600;font-size:15px;letter-spacing:0.5px;cursor:pointer;margin-top:14px;transition: all 0.3s ease;box-shadow: 0 4px 12px rgba(20, 83, 45, 0.3);}
.btn-login:hover{background: linear-gradient(135deg, #239b56, #166534);transform: translateY(-2px);box-shadow: 0 6px 18px rgba(20, 83, 45, 0.4);}
.btn-login:active{transform: scale(0.98);box-shadow: 0 3px 8px rgba(20, 83, 45, 0.3);}
.divider{text-align:center;margin:20px 0;font-size:14px;color:gray;}
.btn-google{width:100%;padding:12px 16px;background:white;color:black;border:none;border-radius:12px;font-weight:500;display:flex;align-items:center;justify-content:center;gap:10px;cursor:pointer;text-decoration:none;outline:none;transition: all 0.3s ease;box-shadow: 0 4px 14px rgba(0,0,0,0.08);}
.btn-google:hover{transform: translateY(-2px);box-shadow: 0 6px 20px rgba(0,0,0,0.15);}
.btn-google img{width:18px;}
/* RESPONSIVE */
@media(max-width:768px){.container{flex-direction:column;}.left{height:35vh;padding:20px;}.left-content h1{font-size:24px;}.left-content p{font-size:12px;}.logo{width:90px;height:90px;}.right{padding:20px;}.login-box{max-width:100%;}.login-box h2{font-size:20px;}.input-wrapper input{padding:10px 10px 10px 35px;}.btn-login, .btn-google{padding:10px;font-size:14px;}}
/* MODAL & TERMS */
.modal {display: none;position: fixed;z-index: 9999;left: 0; top: 0;width: 100%;height: 100%;background: rgba(0,0,0,0.6);}
.modal-content {background: #fff;margin: 5% auto;padding: 30px;width: 90%;max-width: 800px;border-radius: 12px;max-height: 80vh;overflow-y: auto;box-shadow: 0 10px 30px rgba(0,0,0,0.3);}
.close-btn {float: right;font-size: 22px;cursor: pointer;font-weight: bold;}
.close-btn:hover {color: red;}
.terms {margin-top: 18px;padding: 10px 14px;font-size: 12.5px;line-height: 1.6;color: #6b7280;text-align: center;background: rgba(255,255,255,0.55);backdrop-filter: blur(6px);border-radius: 8px;border: 1px solid rgba(0,0,0,0.05);font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;}
.terms a {color: #1f2937;font-weight: 600;text-decoration: none;position: relative;padding: 2px 0;transition: all .3s ease;}
.terms a::after {content: "";position: absolute;width: 0;height: 1.5px;bottom: -2px;left: 0;background: #1f2937;transition: width .3s ease;}
.terms a:hover::after {width: 100%;}
.terms a:hover {color: #000;}
.terms-wrapper {margin-top: 20px;font-size: 14px;color: #4b5563;}
.custom-checkbox-modern {display: flex;align-items: flex-start;gap: 10px;cursor: pointer;user-select: none;line-height: 1.5;}
.custom-checkbox-modern input[type="checkbox"] {display: none;}
.checkmark-modern {width: 20px;height: 20px;border: 2px solid #14532d;border-radius: 6px;background-color: white;position: relative;flex-shrink: 0;transition: all 0.3s ease;}
.custom-checkbox-modern:hover .checkmark-modern {box-shadow: 0 0 0 3px rgba(20,83,45,0.2);}
.custom-checkbox-modern input[type="checkbox"]:checked + .checkmark-modern::after {content: "";position: absolute;left: 5px;top: 1px;width: 6px;height: 12px;border: solid white;border-width: 0 2px 2px 0;transform: rotate(45deg);background-color: #14532d;}
.terms-text a {color: #14532d;text-decoration: underline;font-weight: 500;}
.terms-info {font-size: 12px;color: #b91c1c;margin-top: 6px;display: none;}
.auth-footer {text-align: center;font-size: 0.95rem;color: #000000;margin-top: 20px;font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;}
.auth-footer a {color: #000000;text-decoration: none;font-weight: 600;transition: color 0.3s ease;}
.auth-footer a:hover {color: #333333;cursor: pointer;}
</style>
</head>
<body>

<div class="container">
    <!-- LEFT HALF -->
    <div class="left">
        <div class="left-content">
            <div class="logo"></div>
            <h1></h1>
            <p></p>
        </div>
    </div>

    <!-- RIGHT HALF -->
    <div class="right">
        <div class="login-box">
            <h2>Sign In</h2>

            <?php if($error) echo "<p style='color:red;'>$error</p>"; ?>

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
                <button type="submit" class="btn-login">Sign In</button>
            </form>

            <p class="auth-footer">Don't have an account? <a href="register.php">Sign Up here</a></p>

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
                <p class="terms-info">You must agree to the Terms and Privacy Policy to proceed with login.</p>
            </div>
        </div>
    </div>
</div>

<script>
// Modal JS
function openModal(id){document.getElementById(id).style.display = "block";}
function closeModal(id){document.getElementById(id).style.display = "none";}
window.onclick = function(e){
    document.querySelectorAll('.modal').forEach(modal=>{if(e.target===modal) modal.style.display='none';});
}

// Terms checkbox
const agreeCheckbox = document.getElementById('agreeTerms');
const loginBtn = document.querySelector('.btn-login');
const termsInfo = document.querySelector('.terms-info');

agreeCheckbox.addEventListener('change', function(){
    loginBtn.disabled = !this.checked;
    termsInfo.style.display = this.checked ? 'none' : 'block';
});
</script>

</body>
</html>