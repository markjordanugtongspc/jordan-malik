<?php
session_start();

if (!isset($_SESSION['captcha_passed'])) {
    header("Location: ../captcha/captcha_verify.php");
    exit();
}

if (isset($_SESSION['authenticated'])) {
    header("Location: ../../dashboard.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Security Questions</title>
    <style>
        :root{--dashboard-primary:#7c3aed;--dashboard-primary-dark:#5b21b6;--dashboard-background:#0f172a;--dashboard-surface:#1e293b;--dashboard-text:#f8fafc;--dashboard-text-secondary:#94a3b8;--dashboard-error:#ef4444;-success:#10b981;--dashboard-border:#334155}body{font-family:'Segoe UI',Tahoma,Geneva,Verdana,sans-serif;background-color:var(--dashboard-background);display:flex;justify-content:center;align-items:center;height:100vh;margin:0;color:var(--dashboard-text)}.container{background:var(--dashboard-surface);padding:30px;border-radius:10px;box-shadow:0 0 15px rgb(0 0 0 / .1);width:100%;max-width:500px}.text{font-size:20px;margin-bottom:20px;color:var(--dashboard-primary)}input[type="text"]{width:100%;padding:12px;border:1px solid var(--dashboard-border);border-radius:5px;margin-bottom:20px;font-size:16px;background-color:rgb(30 41 59 / .7);color:var(--dashboard-text)}.error{color:var(--dashboard-error);margin-bottom:10px}input[type="submit"]{background-color:var(--dashboard-success);color:var(--dashboard-text);padding:10px 15px;border:none;border-radius:5px;cursor:pointer}input[type="submit"]:hover{background-color:#0f9d68}.ws{position:fixed;font-size:20px;color:rgb(124 58 237 / .7);display:none;font-weight:100;opacity:.5}.top-left{top:10px;left:10px}.top-right{top:10px;right:10px}.bottom-left{bottom:10px;left:10px}.bottom-right{bottom:10px;right:10px}
    </style>
</head>
<body>
    <div class="container">
        <h1 class="text">Answer the Security Questions</h1>
        <?php if (isset($_GET['error'])): ?>
            <div class="error">
                <?php
                switch($_GET['error']) {
                    case 'empty':
                        echo 'Please answer both questions.';
                        break;
                    case 'invalid':
                        echo 'Incorrect answers. Please try again.';
                        break;
                }
                ?>
            </div>
        <?php endif; ?>
        <form action="../../../backend/verification/security/process_security_question.php" method="POST">
            <label for="security_dog">What is the name of your dog?</label><br>
            <input type="text" name="security_dog" required placeholder="Enter the name of your dog"><br>

            <label for="security_school">What is the name of your school?</label><br>
            <input type="text" name="security_school" required placeholder="Enter the name of your school"><br>

            <input type="submit" value="Submit">
        </form>
    </div>

    <div class="ws top-left">@Wizzy_</div>
    <div class="ws top-right">@Wizzy_</div>
    <div class="ws bottom-left">@Wizzy_</div>
    <div class="ws bottom-right">@Wizzy_</d>

    <script>
        function sW(){const wsElements=document.querySelectorAll('.ws');let index=0;setInterval(()=>{wsElements.forEach(ws=>ws.style.display='none');wsElements[index].style.display='block';index=(index+1)%wsElements.length},6000)}
        sW()
    </script>
</body>
</html>