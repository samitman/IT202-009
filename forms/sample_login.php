 * Kubernetes 1.19 is out! Get it in one command with:

     sudo snap install microk8s --channel=1.19 --classic

   https://microk8s.io/ has docs and details.

This system has been minimized by removing packages and content that are
not required on a system that users do not log into.

To restore this content, you can run the 'unminimize' command.

20 updates can be installed immediately.
0 of these updates are security updates.
To see these additional updates run: apt list --upgradable

  GNU nano 4.8                                      sample_login.php                                       Modified  
    //for password matching, we can't use this, every time it's ran it'll be a different value
    //so will never log us in!
    //$hash = password_hash($password, PASSWORD_BCRYPT);
    //instead we'll want to run password_verify
    //TODO pretend we got our use from the DB
    $password_hash_from_db = '$2y$10$nyogxGqrfQYEg8mG4nnHJ./t/na9m3HHePyVy5yegJ2zJRQ23PDEm';//placeholder, you can c>
    //otherwise it'll always be false
    
    //note it's raw password, saved hash as the parameters
    if(password_verify($password, $password_hash_from_db)){
     echo "<br>Welcome! You're logged in!<br>"; 
    }
    else{
     echo "<br>Invalid password, get out!<br>"; 
    }
  }
  else{
   echo "There was a validation issue"; 
  }
}
?>

