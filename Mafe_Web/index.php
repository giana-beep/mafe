<?php
require_once "config.php";
$page=$_GET['page']??'home';
$allowed=['home','products','product','compare','ingredients','routines','routine','shelves','community','profile','login','register','logout','scanner','admin'];
if(!in_array($page,$allowed,true)) $page='home';

if($page==='logout'){ session_destroy(); header("Location: index.php"); exit; }
if($page==='register' && $_SERVER['REQUEST_METHOD']==='POST'){
    $name=trim($_POST['name']); $email=trim($_POST['email']); $pass=$_POST['password'];
    if($name && filter_var($email,FILTER_VALIDATE_EMAIL) && strlen($pass)>=6){
        $st=db()->prepare("INSERT INTO users(name,email,password) VALUES(?,?,?)");
        $hash=password_hash($pass,PASSWORD_DEFAULT); $st->bind_param("sss",$name,$email,$hash);
        if($st->execute()){ $_SESSION['user_id']=$st->insert_id; header("Location:?page=profile"); exit; }
        $error="Email sudah terdaftar.";
    } else $error="Isi data dengan benar. Password minimal 6 karakter.";
}
if($page==='login' && $_SERVER['REQUEST_METHOD']==='POST'){
    $email=trim($_POST['email']); $pass=$_POST['password'];
    $st=db()->prepare("SELECT id,password FROM users WHERE email=?"); $st->bind_param("s",$email); $st->execute(); $u=$st->get_result()->fetch_assoc();
    if($u && password_verify($pass,$u['password'])){ $_SESSION['user_id']=$u['id']; header("Location:?page=home"); exit; }
    $error="Email atau password salah.";
}
if($page==='profile' && logged_in() && $_SERVER['REQUEST_METHOD']==='POST'){
    $skin=$_POST['skin_type']; $conc=trim($_POST['concerns']);
    $st=db()->prepare("UPDATE users SET skin_type=?, concerns=? WHERE id=?"); $st->bind_param("ssi",$skin,$conc,$_SESSION['user_id']); $st->execute();
    $saved=true;
}
include "partials/header.php";
include "pages/$page.php";
include "partials/footer.php";
?>