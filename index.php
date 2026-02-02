<?php ?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Signal Breach</title>

<link href="https://fonts.googleapis.com/css2?family=Share+Tech+Mono&display=swap" rel="stylesheet">

<style>
*{
    margin:0;
    padding:0;
    box-sizing:border-box;
    font-family:'Share Tech Mono', monospace;
}

body{
    background:#040000;
    color:#ff2a2a;
    min-height:100vh;
    display:flex;
    flex-direction:column;
    align-items:center;
    padding:40px 15px;
    overflow-x:hidden;
}

/* BREATHING RED DARKNESS */
body::before{
    content:"";
    position:fixed;
    width:200%;
    height:200%;
    top:-50%;
    left:-50%;
    background:radial-gradient(circle, rgba(255,0,0,0.15), transparent 60%);
    animation:pulse 7s infinite alternate;
    z-index:-1;
}
@keyframes pulse{
    from{transform:scale(1);opacity:.4;}
    to{transform:scale(1.25);opacity:.9;}
}

/* SCANLINES */
body::after{
    content:"";
    position:fixed;
    width:100%;
    height:100%;
    background:repeating-linear-gradient(
        0deg,
        rgba(255,0,0,.05),
        rgba(255,0,0,.05) 1px,
        transparent 1px,
        transparent 3px
    );
    pointer-events:none;
    z-index:0;
}

.container{
    width:100%;
    max-width:900px;
    text-align:center;
    z-index:2;
}

/* GLITCH TITLE */
.glitch{
    font-size:clamp(36px, 8vw, 70px);
    font-weight:bold;
    position:relative;
    letter-spacing:5px;
    text-shadow:0 0 10px red, 0 0 40px darkred;
    margin-bottom:10px;
}

.glitch::before,
.glitch::after{
    content:attr(data-text);
    position:absolute;
    left:0;
    width:100%;
    overflow:hidden;
}

.glitch::before{
    left:2px;
    text-shadow:-2px 0 #ff0000;
    animation:glitchTop 2s infinite linear alternate-reverse;
}
.glitch::after{
    left:-2px;
    text-shadow:-2px 0 #990000;
    animation:glitchBottom 1.5s infinite linear alternate-reverse;
}

@keyframes glitchTop{
    0%{clip-path: inset(0 0 80% 0);}
    50%{clip-path: inset(0 0 10% 0);}
    100%{clip-path: inset(0 0 65% 0);}
}
@keyframes glitchBottom{
    0%{clip-path: inset(80% 0 0 0);}
    50%{clip-path: inset(40% 0 0 0);}
    100%{clip-path: inset(60% 0 0 0);}
}

/* WARNING */
.warn{
    font-size:clamp(16px,4vw,22px);
    margin-bottom:25px;
    text-shadow:0 0 10px red;
}

/* TERMINAL BOX */
.terminal{
    margin-top:20px;
    padding:20px;
    border:1px solid rgba(255,0,0,.3);
    background:rgba(20,0,0,.6);
    text-align:left;
    font-size:18px;
    line-height:1.8;
    color:#ff6b6b;
    backdrop-filter:blur(4px);
    box-shadow:0 0 20px rgba(255,0,0,.2);
    word-wrap:break-word;
}

/* FLICKER */
.flicker{
    animation:flick 3s infinite;
}
@keyframes flick{
    0%,18%,22%,25%,53%,57%,100%{opacity:1;}
    20%,24%,55%{opacity:.3;}
}

/* CREDIT SECTION */
.credit{
    margin-top:50px;
    padding:15px;
    border-top:1px solid rgba(255,0,0,.3);
    text-align:center;
    font-size:14px;
    backdrop-filter:blur(3px);
    z-index:2;
}

.credit-title{
    color:#ff2a2a;
    text-shadow:0 0 10px red;
    font-size:16px;
    margin-bottom:6px;
    letter-spacing:2px;
}

.credit-sub{
    color:#990000;
    font-size:13px;
    letter-spacing:2px;
}

/* MOBILE */
@media (max-width:600px){
    body{padding:25px 10px;}
    .terminal{font-size:16px;}
}
</style>
</head>

<body>

<div class="container flicker">
    <div class="glitch" data-text="LAST WARNING">LAST WARNING</div>
    <div class="warn">⚠ SIGNAL COMPROMISED MR 3XPLOITER ⚠</div>
    <div class="warn">⚠ OUR REACH IS INFINITE ⚠</div>
    <div class="terminal" id="terminal"></div>
</div>

<!-- CREDIT -->
<div class="credit">
    <div class="credit-title">Knight Cyber Security Lab Singapore</div>
    <div class="credit-sub">SYSTEM SECURITY DIVISION</div>
</div>

<script>
const lines = [
">> Ei developer ke kaj e rakhle ngmhs er data leak korte badho hobo",
">> ei site kono kaj er na , kono security nai ",
">> ekhon o kintu kono data ami nei nai ",
">> developer Duronto bahir kore den SCL theke",
">> আসসালামু আলাইকুম নজিপুর সরকারি মডেল উচ্চ বিদ্যালয়ের অধ্যক্ষ , High Skilled developer লাগবে |",
">> Notun developer khujen ei developer kono website build korte pare na , ui corrupted ",
">> email me penguinjellyfish@gmail.com : warning dear developer faltu ekta server . age valobhabe website banano shikhen",
];

let i = 0;
let j = 0;
let currentLine = "";
const term = document.getElementById("terminal");

function typeEffect(){
    if(i < lines.length){
        if(j < lines[i].length){
            currentLine += lines[i][j];
            term.innerHTML = currentLine + "_";
            j++;
            setTimeout(typeEffect, 25);
        } else {
            currentLine += "<br>";
            term.innerHTML = currentLine;
            i++; 
            j=0;
            setTimeout(typeEffect, 400);
        }
    }
}
typeEffect();
</script>

</body>
</html>
