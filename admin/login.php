<?php
session_start();

include('include/connexion.php');
$pdo = connexion();
 
if(isset($_POST['submit_signin'])) {
   $mailconnect = htmlspecialchars($_POST['email']);
   $mdpconnect = sha1($_POST['password']);
   if(!empty($mailconnect) AND !empty($mdpconnect)) {
      $requser = $pdo->prepare("SELECT * FROM membres WHERE mail = ? AND motdepasse = ?");
      $requser->execute(array($mailconnect, $mdpconnect));
      $userexist = $requser->rowCount();
      if($userexist == 1) {
         $userinfo = $requser->fetch();
         $_SESSION['id'] = $userinfo['id'];
         $_SESSION['pseudo'] = $userinfo['pseudo'];
         $_SESSION['mail'] = $userinfo['mail'];
         header("Location: index.php");
      } else {
         $erreur = "Mauvais mail ou mot de passe !";
      }
   } else {
      $erreur = "Tous les champs doivent être complétés !";
   }
}
?>

<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8" />
        <meta http-equiv="X-UA-Compatible" content="IE=edge" />
        <meta name="viewport" content="width=device-width, initial-scale=1.0" />
        <meta name="google-signin-client_id" content="AIzaSyCso1vrQDWi-kHJKYKJUXUfQh6W-Pi57cs">
        <title>Sign In</title>
        <link rel="preconnect" href="https://fonts.googleapis.com" />
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
        <link
        href="https://fonts.googleapis.com/css2?family=Hind+Madurai:wght@300;400;500;600;700&family=Lora:ital,wght@0,400;0,500;0,600;0,700;1,400;1,500;1,600;1,700&family=Montserrat:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;1,100;1,200;1,300;1,400;1,500;1,600;1,700&family=Poppins:ital,wght@0,300;0,400;0,500;0,600;0,700;1,300;1,400;1,500;1,600&display=swap"
        rel="stylesheet"
        />
        <link rel="stylesheet" href="assets/css/style.css" />
        <script src="https://cdn.tailwindcss.com"></script>
        <script src="https://apis.google.com/js/api.js"></script>
        <script src="https://apis.google.com/js/client:platform.js?onload=renderButton" async defer></script>
        <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>
        <script src="assets/js/main.js"></script>
        <script src="assets/js/api_facebook.js"></script>
        <script src="assets/js/api_google.js"></script>
    </head>
    <body>
        <header>

        </header>
        <main class="h-screen flex justify-center items-center">
            <section class="">
                <h1 class="text-2xl font-bold text-center">Hey, Bon retour! 👋</h1>
                <form action="" method="post" class="">
                    <div class="m-[1rem]">
                        <label for="email" class="text-[#695C5C]">Email</label>
                        <input type="email" name="email" id="email" placeholder="exemple@gmail.com" class="w-full rounded-xl border px-[1rem] border-{rgba(0, 0, 0, 0.4)} leading-[3rem]">
                    </div>
                    <div class="m-[1rem]">
                        <label for="password" class="text-[#695C5C]">Mot de Passe</label>
                        <input type="password" name="password" id="password" placeholder="Entrez un Mot de Passe" class="w-full rounded-xl border px-[1rem] border-{rgba(0, 0, 0, 0.4)} leading-[3rem]">
                    </div>
                    <div class="m-[1rem] flex flex-row justify-between">
                        <div>
                            <input type="checkbox" name="remember_me" id="remember_me">
                            <label for="remember_me">Se souvenir de moi</label>
                        </div>
                        <a href="" class="text-red-600">Mot de passe oublié ?</a>
                    </div>
                    <div class="text-center">
                        <input type="submit" value="Se connecter" name="submit_signin" class="bg-[#0E64D2] text-white w-full h-[3rem] rounded-md font-semibold hover:cursor-pointer signin">
                    </div>
                </form>
            </section>
        </main>
        <footer>
            <?php
            if(isset($erreur)) {
                echo '<font color="red">'.$erreur."</font>";
            }
            ?>
        </footer>
    </body>
</html>