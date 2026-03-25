<?php
session_start();

$USE_REAL_FB   = false;
$FB_PAGE_ID    = 'barangay.dologon';
$FB_TOKEN      = 'YOUR_PAGE_ACCESS_TOKEN_HERE';

$fbPosts = [];
$hasFreshPost = false;

if ($USE_REAL_FB) {
    $url = "https://graph.facebook.com/v19.0/{$FB_PAGE_ID}/posts"
         . "?fields=message,story,created_time,full_picture,permalink_url"
         . "&limit=3&access_token={$FB_TOKEN}";
    $ctx = stream_context_create(['http'=>['timeout'=>8]]);
    $raw = @file_get_contents($url, false, $ctx);
    if ($raw) {
        $json = json_decode($raw, true);
        if (!empty($json['data'])) {
            foreach ($json['data'] as $post) {
                $ts   = strtotime($post['created_time']);
                $fresh = (time() - $ts) < 86400;
                if ($fresh) $hasFreshPost = true;
                $fbPosts[] = [
                    'message'   => $post['message'] ?? ($post['story'] ?? 'No caption.'),
                    'time'      => $ts,
                    'image'     => $post['full_picture'] ?? '',
                    'url'       => $post['permalink_url'] ?? '#',
                    'fresh'     => $fresh,
                ];
            }
        }
    }
}

$postsJson   = json_encode($fbPosts);
$freshJson   = json_encode($hasFreshPost);
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Ato ni! Barangay Projects Portal</title>
<link rel="icon" type="image/png" href="cityengineerlogo.jpg">
<link rel="icon" type="image/x-icon" href="favicon_dologon.ico">
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700;800&display=swap" rel="stylesheet">

<style>
:root {
  --green-dark:  #002200;
  --green-mid:   #004400;
  --green-light: #006600;
  --white:       #ffffff;
  --glass-bg:    rgba(255,255,255,0.18);
  --glass-border:rgba(255,255,255,0.35);
  --shadow:      0 8px 35px rgba(0,0,0,0.25);
  --card-radius: 16px;
  --transition:  .35s cubic-bezier(.4,0,.2,1);
}

*{box-sizing:border-box;margin:0;padding:0;}

body{
  font-family:'Poppins',Arial,sans-serif;
  min-height:100vh;
  display:flex;
  flex-direction:column;
  justify-content:center;
  align-items:center;
  overflow-x:hidden;
  padding:20px 16px 40px;
}

#bgVideo{
  position:fixed;right:0;bottom:0;
  min-width:100%;min-height:100%;
  width:auto;height:auto;
  z-index:-1;object-fit:cover;
}

.hero{
  background:var(--glass-bg);
  backdrop-filter:blur(18px);
  -webkit-backdrop-filter:blur(18px);
  border-radius:22px;
  border:1px solid var(--glass-border);
  box-shadow:var(--shadow);
  padding:55px 45px 40px;
  text-align:center;
  max-width:540px;
  width:92%;
}

.logo-flip{width:115px;height:115px;margin:0 auto 18px;perspective:1000px;}
.logo-inner{
  width:100%;height:100%;position:relative;
  transform-style:preserve-3d;
  transition:transform 1.5s ease-in-out;
}
.logo-inner.show-back{transform:rotateY(180deg);}
.logo-front,.logo-back{
  position:absolute;width:100%;height:100%;
  backface-visibility:hidden;
}
.logo-front img,.logo-back img{
  width:115px;height:115px;
  border-radius:50%;object-fit:cover;
  box-shadow:0 4px 12px rgba(0,0,0,.25);
}
.logo-back{transform:rotateY(180deg);}

.welcome-title{font-size:34px;font-weight:800;margin-bottom:10px;display:inline-block;}
.green-gradient{
  background:linear-gradient(45deg,#003300,#005500);
  -webkit-background-clip:text;-webkit-text-fill-color:transparent;
}
.black-text{color:black;}
.hero p{font-size:16px;color:black;margin-bottom:15px;font-weight:500;}

.buttons{display:flex;gap:16px;justify-content:center;flex-wrap:wrap;margin-bottom:20px;}
.buttons a{
  padding:14px 32px;border-radius:30px;text-decoration:none;
  font-weight:600;font-size:15px;transition:.3s ease;
}
.btn-primary{background:black;color:#fff;}
.btn-primary:hover{transform:translateY(-2px);background:#222;}
.btn-outline{border:2px solid black;color:black;}
.btn-outline:hover{background:black;color:#fff;}

.footer-links{display:flex;justify-content:center;gap:20px;flex-wrap:wrap;margin-top:10px;}
.footer-links a{color:black;text-decoration:none;font-weight:600;font-size:13px;cursor:pointer;}
.footer-links a:hover{text-decoration:underline;}

.modal{
  display:none;position:fixed;z-index:99999;
  left:0;top:0;width:100%;height:100%;
  background:rgba(0,0,0,.6);
  backdrop-filter:blur(4px);
  animation:modalFadeIn .25s ease both;
}
@keyframes modalFadeIn{from{opacity:0;}to{opacity:1;}}

.modal-content{
  background:#fff;
  margin:5% auto;
  padding:35px 30px;
  width:90%;max-width:800px;
  border-radius:16px;
  max-height:80vh;overflow-y:auto;
  box-shadow:0 20px 60px rgba(0,0,0,.35);
  animation:modalSlideIn .3s ease both;
}
@keyframes modalSlideIn{
  from{transform:translateY(-20px);opacity:0;}
  to{transform:translateY(0);opacity:1;}
}
.modal h2{color:#002200;margin-top:0;font-size:20px;}
.modal h3{color:#004400;font-size:15px;margin:18px 0 6px;}
.modal p,.modal li{font-size:14px;color:#333;line-height:1.7;}
.modal ul{padding-left:20px;}

.close-btn{
  float:right;font-size:24px;cursor:pointer;
  font-weight:bold;color:#666;
  line-height:1;transition:color .2s;
}
.close-btn:hover{color:red;}

@media(max-width:540px){
  .hero{padding:40px 24px 30px;}
  .welcome-title{font-size:26px;}
}

#cornerImage {
  position: fixed;
  bottom: 10px;   /* gap gikan sa ubos */
  right: 10px;    /* gap gikan sa right */
  width: 100px;   /* adjust size */
  height: auto;
  z-index: 9999;  /* para ibabaw sa tanan */
  opacity: 0.9;   /* optional */
}

</style>
</head>
<body>

<video autoplay muted loop playsinline id="bgVideo">
  <source src="vid.mp4" type="video/mp4">
</video>

<div class="hero" id="heroCard">

  <div class="logo-flip">
    <div class="logo-inner" id="logoInner">
      <div class="logo-front">
        <img src="engineeringofficelogo.jpeg" alt="Barangay Dologon Logo">
      </div>
      <div class="logo-back">
        <img src="valcitylogo.jpg" alt="Maramag Logo">
      </div>
    </div>
  </div>

 <h1 class="welcome-title">
  <span class="green-gradient">Ato ni!</span>
  <span class="black-text">Barangay Projects Portal</span>
</h1>

  <p>A Real-time Barangay Infrastructure Projects Dashboard and Analytics System.</p>
  <br>

   <p>City Government of Valencia, Bukidnon.</p>
   
   <br>

  <div class="buttons">
    
    <a href="login.php"    class="btn-outline">Sign In</a>
  </div>

  <br>

  <div class="footer-links">
    <a onclick="openModal('privacyModal')">Privacy Policy</a>
    <a onclick="openModal('termsModal')">Terms of Service</a>
    <a onclick="openModal('contactModal')">Contact Support</a>
  </div>

</div>

<!-- Privacy Policy Modal -->
<div id="privacyModal" class="modal">
  <div class="modal-content">
    <span class="close-btn" onclick="closeModal('privacyModal')">&times;</span>
    <h2>Privacy Policy</h2>
    <p>The Ato ni! Barangay Projects Portal respects your privacy and ensures that all personal information collected through it is handled securely and responsibly.</p>
    <h3>Information We Collect</h3>
    <ul>
      <li>Full Name</li><li>Email Address</li><li>Contact Number</li>
      <li>Purok / Address</li><li>Request details and uploaded documents</li>
    </ul>
    <h3>Purpose of Collection</h3>
    <ul>
      <li>Processing city service requests</li>
      <li>Resident verification</li>
      <li>Status updates and communication</li>
      <li>Official record-keeping</li>
    </ul>
    <h3>Data Protection</h3>
    <p>All collected data is protected in accordance with applicable Philippine Data Privacy regulations and Official LGU security protocols.</p>
    <p><strong>Email:</strong> support@atoni!barangayprojectsportal.gov.ph</p>
  </div>
</div>

<!-- Terms of Service Modal -->
<div id="termsModal" class="modal">
  <div class="modal-content">
    <span class="close-btn" onclick="closeModal('termsModal')">&times;</span>
    <h2>Terms of Service</h2>
    <p>By accessing Ato ni! Barangay Projects Portal, you agree to comply with these terms established by City Government of Valencia, Bukidnon.</p>
    <h3>Lawful Use</h3>
    <p>The platform must be used only for legitimate city transactions.</p>
    <h3>Accuracy of Information</h3>
    <p>Users must provide accurate and truthful information.</p>
    <h3>Intellectual Property</h3>
    <p>The platform is the official property of City Government of Valencia, Bukidnon and may not be copied or redistributed without authorization.</p>
    <h3>Limitation of Liability</h3>
    <p>The LGU shall not be liable for system interruptions or technical issues beyond reasonable control.</p>
  </div>
</div>

<!-- Contact Support Modal -->
<div id="contactModal" class="modal">
  <div class="modal-content">
    <span class="close-btn" onclick="closeModal('contactModal')">&times;</span>
    <h2>Contact Support</h2>
    <p>For technical concerns and inquiries regarding Ato ni! Barangay Projects Portal:</p>
    <p><strong>Email Support:</strong><br>support@atoni!barangayprojectsportal.gov.ph</p>
    <p><strong>Technical Hotline:</strong><br>09067896101</p>
    <p><strong>Office Address:</strong><br>
      City Government of Valencia<br> Bukidnon (8709)<br>Philippines</p>
  </div>
</div>

<script>
function openModal(id) {
  document.getElementById(id).style.display = 'block';
}
function closeModal(id) {
  document.getElementById(id).style.display = 'none';
}
window.addEventListener('click', e => {
  document.querySelectorAll('.modal').forEach(m => {
    if (e.target === m) m.style.display = 'none';
  });
});

// Logo flip animation
const logoInner = document.getElementById('logoInner');
setInterval(() => logoInner.classList.toggle('show-back'), 2000);
</script>

<img src="QR.png" id="cornerImage">
</body>
</html>